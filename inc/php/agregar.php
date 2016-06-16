<?php

/**
 * controlador de la página agregar
 * @requisitos:
 * cargamos los datos necesarios para ejecutar la seccion de post
 * a su vez será también la página de agregar post
 * verificamos el nivel de acceso a la pagina
 * establecemos las variables importantes al archivo
 * asignamos las tareas necesarias que se realizaran en la seccion post
 * asignamos el valor de las variables a smarty
 *
 * @name agregar.php
 * @author Iván Martínez Tutor
 */

/**
 * definimos las variables importantes al archivo
 */
//plantilla para mostrar con el archivo
$psPage = "agregar";
//nivel de acceso a la página
$psLevel = 2;
//comprobamos si la respuesta se realiza por ajax
$psAjax = empty($_GET['ajax']) ? 0 : 1;
//creamos el booleano para comprobar si debemos continuar con el script
$psContinue = true;

include '../../header.php';
//damos un nombre al titulo de la pagina
$psTitle = $psCore->settings['titulo'].' - '.$psCore->settings['slogan'];

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
	$action = filter_input(INPUT_GET, 'action');
	//comprobamos
	if(is_numeric($action)){
		include '../class/c.boradores.php';
		$psBorradores =& psBorradores::getInstance();
		$psBorrador = $psBorradores->getBorrador();
		$smarty->assign("psBorrador", $psBorrador);
	}elseif($action == 'editar'){
		include '../class/c.posts.php';
		$psPosts =& psPosts::getInstance();
		//guardamos
		if(!empty($_POST['titulo'])){
		  	$post_save = $psPosts->guardarPost();
			if($post_save == 1) {
				$post = filter_input(INPUT_GET, 'pid');
				$cat = filter_input(INPUT_POST, 'categoria');
				$consulta = "SELECT c_seo FROM p_categorias WHERE cid = :cat";
				$valores = array('cat' => $cat);
				$cat = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
				//obtenemos la url del post
				$post_url = "{$psCore->settings['url']}/posts/{$cat['c_seo']}/$post/{$psCore->setSeo($_POST['titulo'])}.html";
				//redireccionamos al post
				$psCore->redirectTo($post_url);
			}else{
                $psPage = 'aviso';
                $smarty->assign("psAviso", array('titulo' => 'Error!', 'mensaje' => $post_save, 'but' => 'Volver', 'link' => 'javascript:history.go(-1)'));
			}
		//editar el post
		} else {
            $borrador = $psPosts->getEditarPost();
            if(!is_array($borrador)){
                $psPage = 'aviso';
                $smarty->assign("tsAviso",array('titulo' => 'Error!', 'mensaje' => $borrador, 'but' => 'Ir a pagina principal', 'link' => "{$psCore->settings['url']}"));
            }else{
            	$smarty->assign("psBorrador", $borrador);
            }
		}
		$smarty->assign("psAction", filter_input(INPUT_GET, 'action'));
		$smarty->assign("psPid", filter_input(INPUT_GET, 'pid'));

	}elseif($_POST['titulo']){
		include '../class/c.posts.php';
		$psPosts =& psPosts::getInstance();
		$psPost = $psPosts->nuevoPost();
		$psPage = 'aviso';
		$psAjax = 0;
		if($psPost > 0) {
			$cat = filter_input(INPUT_POST, 'categoria');
			$consulta = "SELECT c_seo FROM p_categorias WHERE cid = :cat";
			$valores = array('cat' => $cat);
			$cat = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
			//asignamos los datos para el aviso
			$smarty->assign("psAviso", array(
				'titulo' => 'Genial!',
				'mensaje' => 'El post <strong>'.filter_input(INPUT_POST, 'titulo').'</strong> fue agregado correctamente. '.(!$psUser->admod && ($psUser->permisos['gorpap'] == true || $psCore->settings['c_desapprove_post'] == 1) ? 'Deber&aacute; esperar a que un administrador o moderador lo apruebe' : '').' ',
				'but' => 'Acceder al post',
				'link' => "{$psCore->settings['url']}/posts/{$cat['c_seo']}/$psPost/{$psCore->setSeo($_POST['titulo'])}.html")
			);
		}elseif($psPost == -1){
			$smarty->assign("psAviso", array(
				'titulo' => 'Anti Flood',
				'mensaje' => "No puedes realizar tantas acciones en tan poco tiempo. Por favor vuelva a intentarlo en unos segundos",
				'but' => 'Volver',
				'link' => "javascript:history.go(-1)"));
		}else{
			$smarty->assign("psAviso", array(
				'titulo' => 'Error!',
				'mensaje' => "Ocurri&oacute; un error, por favor int&eacute;ntalo de nuevo m&aacute;s tarde.<br><strong>Error</strong>: ".$psPost,
				'but' => 'Volver',
				'link' => 'javascript:history.go(-1)'));
		}
	}
}

//si todo ok y no vamos por ajax asignamos en smarty
if(empty($psAjax)){
    $smarty->assign('psTitle', $psTitle);
    include(PS_ROOT.'/footer.php');
}
