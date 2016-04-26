<?php
//si la constante PS_HEADER no esta definida no se accede al script
if (!defined('PS_HEADER')) {
    exit("No se permite el acceso al script");
}

/**
 * Controlador ajax para las denuncias  
 *
 * @name ajax.denuncias.php
 * @author Iván Martínez Tutor
 */

//niveles de acceso y plantillas para cada acción
$niveles = array(
	'denuncia-usuario' => array('n' => 2, 'p' => 'form'),
	'denuncia-post' => array('n' => 2, 'p' => 'form'),
	'denuncia-foto' => array('n' => 2, 'p' => 'form'),
    'denuncia-mensaje' => array('n' => 2, 'p' => 'form'),
);
$psPage = 'php_files/p.denuncias.' . $niveles[$action]['p'];
$psLevel = $niveles[$action]['n'];
$psAjax = empty($niveles[$action]['p']) ? 1 : 0;

//comprobamos el nivel de acceso del usuario
$mensaje = $psCore->setLevel($psLevel, true);
if($mensaje != 1){
	echo $mensaje['mensaje'];
	die;
}

//obtenemos las clases necesarias
include '../class/c.denuncias.php';
$psDenuncias =& psDenuncias::getInstance();
switch ($action) {
	case 'denuncia-usuario':
		//creamos la denuncia
		if($_POST['razon']){
			$psAjax = 1;
			echo $psDenuncias->setDenuncia(filter_input(INPUT_POST, 'obj_id'), 'usuarios');
		}
		//incluimos el archivo con los datos de las denuncias
		$smarty->assign('psDatos', array('nick' => filter_input(INPUT_POST, 'obj_user')));
		$smarty->assign('psDenuncias', $psDenuncias['users']);
		break;
	case 'denuncia-post':
		//creamos la denuncia
		if($_POST['razon']){
			$psAjax = 1;
			echo $psDenuncias->setDenuncia(filter_input(INPUT_POST, 'obj_id'), 'posts');
		}else{
			$datos = array(
				'obj_id' => filter_input(INPUT_POST, 'obj_id'),
				'obj_title' => filter_input(INPUT_POST, 'obj_title'),
				'obj_user' => filter_input(INPUT_POST, 'obj_user')
			);
			//incluimos el archivo con los datos de las denuncias
			include '../extra/datos.php';
			$smarty->assign('psDatos', $datos);
			$smarty->assign('psDenuncias', $psDenuncias['posts']);
		}
		break;
	case 'denuncia-foto':
		//creamos la denuncia
		if($_POST['razon']){
			$psAjax = 1;
			echo $psDenuncias->setDenuncia(filter_input(INPUT_POST, 'obj_id'), 'fotos');
		}else{
			$datos = array(
				'obj_id' => filter_input(INPUT_POST, 'obj_id'),
				'obj_title' => filter_input(INPUT_POST, 'obj_title'),
				'obj_user' => filter_input(INPUT_POST, 'obj_user')
			);
			//incluimos el archivo con los datos de las denuncias
			include '../extra/datos.php';
			$smarty->assign('psDatos', $datos);
			$smarty->assign('psDenuncias', $psDenuncias['fotos']);
		}
		break;
	case 'denuncia-mensaje':
		if($_POST['razon']){
			$psAjax = 1;
			echo $psDenuncias->setDenuncia(filter_input(INPUT_POST, 'obj_id'), 'mensajes');
		}
		break;
}
//una vez declaradas las diferentes acciones asignamos la variable action
$smarty->assign('psAction', $action);