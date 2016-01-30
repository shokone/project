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
$psAjax = empty($_GET['ajas']) ? 0 : 1;
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
    //incluimos la clase posts
    include(PS_CLASS."c.posts.php");
    $psPosts =& psPosts::getInstance();
    //obtenemos categoria
    $categoria = filter_input(INPUT_GET, 'cat');
    //instanciamos la clase afiliados
    include(PS_CLASS."c.afiliado.php");
    //comprobamos si ha sido referido
    if(!empty($_GET['ref'])){
        $psAfiliado->urlIn();
    }
    //post anterior, post siguiente y post aleatorio
    if($_GET['action'] == 'next' || $_GET['action'] == 'prev' || $_GET['action'] == 'randPost'){
        $psPosts->setModePost();
    }
    
    //realizamos las tareas principales
    if(!empty($_GET['post_id'])){
        //obtenemos los datos del post
        if($psPost['post_id'] > 0){
            //titulo del post
            //datos del autor
            //datos del rango
            //post relacionados
            //comentarios y paginas de comentarios
            //asignamos datos a smarty
        }else{
            
        }
    }
}