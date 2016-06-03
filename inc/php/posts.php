<?php

/**
 * controlador de posts
 * @requisitos:
 * cargamos los datos necesarios para ejecutar la seccion de post
 * a su vez será también el home del portal
 * verificamos el nivel de acceso a la pagina
 * establecemos las variables importantes al archivo
 * asignamos las tareas necesarias que se realizaran en la seccion post
 * asignamos el valor de las variables a smarty
 *
 * @name posts.php
 * @author Iván Martínez Tutor
 */

/**
 * definimos las variables importantes al archivo
 */
//plantilla para mostrar con el archivo
$psPage = "posts";
//nivel de acceso a la página
$psLevel = 0;
//comprobamos si la respuesta se realiza por ajax
$psAjax = empty($_GET['ajax']) ? 0 : 1;
//creamos el booleano para comprobar si debemos continuar con el script
$psContinue = true;
//damos un nombre al titulo de la pagina
$psTitle = $psCore->settings['titulo'];

/**
 * validamos el nivel y los permisos de acceso
 */
$psLevelVer = $psCore->setLevel($psLevel, true);
if($psLevelVer != 1){
    $psPage = "aviso";
    $psAjax = 0;
    $smarty->assign("psAviso",$psLevelVer);
    $psContinue = false;
}

/**
 * establecemos las variables principales
 * solo si el script puede continuar
 */
if($psContinue){
    include(PS_CLASS."c.afiliados.php");
    $psAfiliados =& psAfiliados::getInstance();
    //incluimos la clase posts
    include(PS_CLASS."c.posts.php");
    $psPosts =& psPosts::getInstance();
    //obtenemos categoria
    $categoria = $_GET['cat'];
    //instanciamos la clase afiliados
    include(PS_CLASS."c.afiliado.php");
    //comprobamos si ha sido referido
    if(!empty($_GET['ref'])){
        $psAfiliados->urlInterna();
    }
    //post anterior, post siguiente y post aleatorio
    if($_GET['action'] == 'next' || $_GET['action'] == 'prev' || $_GET['action'] == 'randPost'){
        $psPosts->setModePost();
    }
    //realizamos las tareas principales
    if(!empty($_GET['post_id'])){
        //obtenemos los datos del post
        $psPost = $psPosts->getPost();
        //si el post se ha encontrado obtenemos los datos y los asignamos a smarty
        if($psPost['post_id'] > 0){
            //titulo del post
            $psTitle = $psPost['post_title'] . ' - ' . $psTitle;
            $smarty->assign('psPost', $psPost);
            //datos del autor
            $smarty->assign('psAutor', $psPosts->getAutor($psPost['post_user']));
            //obtenemos los puntos que puede dar el usuario
            $smarty->assign('psPuntos', $psPosts->getPuntos());
            //post relacionados
            $psRelacionados = $psPosts->getRelacionados($psPost['post_tags']);
            $smarty->assign('psRelacionados', $psRelacionados);
            //comentarios y paginas de comentarios
            //$psComentarios = $psPosts->getComentarios($psPost['post_id']);
            //$psComentarios = array('num' => $psComentarios['num'], 'data' => $psComentarios['data']);
            //$smarty->assign('psComentarios', $psComments);
            //y las páginas de los comentarios
            $total = $psPost['post_comments'];
            $psPages = $psCore->getPages($total, $psCore->settings['c_max_com']);
            $psPages['post_id'] = $psPost['post_id'];
            $psPages['autor'] = $psPost['post_user'];
            $smarty->assign('psPages', $psPages);
        }else{
            //sino comprobamos si el post está oculto y el usuario no está registrado
            if($psPost[0] == 'privado'){
                //si es privado enviamos al usuario a la página de registro
                $psTitle = $psPost[1] . ' - ' . $psTitle;
                $psPage = 'registro';
            }else{//si no ha sido encontrado mandamos un aviso de ello
                $psTitle = $psTitle . ' - ' . $psCore->settings['slogan'];
                //asignamos los nuevos datos a la página
                $psPage = 'post.aviso';
                $smarty->assign('psAviso', $psPost);
                //obtenemos el título y lo separamos en tags para mostrar post relacioandos
                $titulo = str_replace('-', ',', filter_input(INPUT_GET, 'title'));
                $titulo = explode(',', $titulo);
                $psRelacionados = $psPosts->getRelacionados($titulo);
                $smarty->assign('psRelacionados', $psRelacionados);
            }
        }
    }else{//si no hay ningún post seleccinado mostramos la home
        //obtenemos la pagina
        $psPage = 'home';
        //obtenemos el titulo
        $psTitle = $psTitle . ' - ' . $psCore->settings['slogan'];
        //cargamos las clases necesarias
        include(PS_CLASS . 'c.tops.php');
        include(PS_CLASS . 'c.portal.php');
        include(PS_CLASS . 'c.fotos.php');
        $psTops =& psTops::getInstance();
        $psPortal =& psPortal::getInstance();
        $psFotos =& psFotos::getInstance();
        //obtenemos los últimos post
        $psLastPosts = $psPosts->getLastPosts($categoria);
        $smarty->assign('psPosts', $psLastPosts['data']);
        $smarty->assign('psPages', $psLastPosts['pages']);
        //obtenemos los post fijos
        if($psLastPosts['pages']['current'] == 1){
            $psStickys = $psPosts->getLastPosts($categoria, true);
            $smarty->assign('psPostSticky', $psStickys['data']);
        }
        //obtenemos los últimos comentarios
        $smarty->assign('psComentarios', $psPosts->getLastComentarios());
        //obtenemos el top de post
        $smarty->assign('psTopPosts', $psTops->getHomeTopPosts());
        //obtenemos el top de usuarios
        $smarty->assign('psTopUsers', $psTops->getHomeTopUsers());
        //obtenemos las estadísticas
        $smarty->assign('psStats', $psPortal->getStats());
        //obtenemos las categorías
        $smarty->assign('psCategoria', $categoria);
        //obtenemos los datos de cada categoría
        if(!empty($categoria)){
            $datos = $psPosts->getDatosCategoria();
            $psTitle = $psCore->settings['titulo'] . ' - ' . $datos['c_nombre'];
            $smarty->assign('psDatosCat', $datos);
        }
        //obtenemos las fotos
        $psImages = $psFotos->getLastFotos();
        $smarty->assign("psImages",$psImages);
        $smarty->assign("psImagesTotal",count($psImages));
        //obtenemos los afiliados
        //$smarty->assign("psAfiliados", $psAfiliados->getAfiliados());
        //obtenemos el menu
        $smarty->assign("psDo", filter_input(INPUT_GET, 'do'));
    }
}

//si todo ok y no vamos por ajax asignamos en smarty
if(empty($psAjax)){
    $smarty->assign('psTitle', $psTitle);
    include(PS_ROOT.'/footer.php');
}
