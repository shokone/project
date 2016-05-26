<?php
//si la constante PS_HEADER no esta definida no se accede al script
if (!defined('PS_HEADER')) {
    exit("No se permite el acceso al script");
}

/**
 * Controlador ajax para los posts
 *
 * @name ajax.posts.php
 * @author Iván Martínez Tutor
 */

//niveles de acceso y plantilla de cada acción
$niveles = array(
	'posts-preview' => array('n' => 2, 'p' => 'preview'),
	'posts-borrar' =>  array('n' => 2, 'p' => ''),
	'posts-admin-borrar' =>  array('n' => 2, 'p' => ''),
	'posts-votar' =>  array('n' => 2, 'p' => ''),
	'posts-last-comentarios' =>  array('n' => 0, 'p' => 'last-comentarios'),
	'posts-generar' => array('n' => 2, 'p' => 'generar'),
);

//variables locales
$psPage = 'php_files/p.posts.' . $niveles[$action]['p'];
$psLevel = $niveles[$action]['n'];
$psAjax = empty($niveles[$action]['p']) ? 1 : 0;

//comprobamos el nivel de acceso del usuario
$mensaje = $psCore->setLevel($psLevel, true);
if($mensaje != 1){
	echo $mensaje['mensaje'];
	die;
}
//obtenemos las clases necesarias del archivo header.php
require '../class/c.posts.php';
$psPosts =& psPosts::getInstance();
switch($action){
	case 'posts-preview':
		//asignamos la funcion getPreview a smarty
		$smarty->assign('psPreview', $psPosts->getPreview());
		break;
	case 'posts-borrar':
		//borramos el post, un moderador o un usuario que borre su propio post
		echo $psPosts->borrarPost();
		break;
	case 'posts-admin-borrar':
		//borramos el post desde la sección administración
		echo $psPosts->borrarPostAdmin();
		break;
	case 'posts-votar':
		//votamos el post
		echo $psPosts->votarPost();
		break;
	case 'posts-last-comentarios':
		//obtenemos los últimos comentarios
		$smarty->assign('psComentarios', $psPosts->getLastComentarios());
		break;
	case 'posts-generar':
		//obtenemos datos 
		$action = htmlspecialchars($_GET['do']);
		$datos = $_POST['q'];
		//si accion = search buscamos los post relacionados
		if($action == 'search'){
			$smarty->assign('psPosts', $psPosts->postRelacionados($datos));
		}else if($action == 'generador'){//si es igual a generador generamos los tags del post
			$tags = $psPosts->generarTags($datos);
			$smarty->assign('psAccion', $action);
		}
		$smarty->assign('psAccion', $action);
		break;
}