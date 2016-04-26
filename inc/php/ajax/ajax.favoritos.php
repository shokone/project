<?php
//si la constante PS_HEADER no esta definida no se accede al script
if (!defined('PS_HEADER')) {
    exit("No se permite el acceso al script");
}

/**
 * Controlador ajax para los favoritos
 *
 * @name ajax.favoritos.php
 * @author Iván Martínez Tutor
 */

//niveles de acceso y plantilla de cada acción
$niveles = array(
	'favoritos' => array('n' => 2, 'p' => 'home'),
	'favoritos-agregar' => array('n' => 2, 'p' => ''),
	'favoritos-borrar' => array('n' => 2, 'p' => ''),
);

//variables locales
$psPage = 'php_files/p.favoritos.' . $niveles[$action]['p'];
$psLevel = $niveles[$action]['n'];
$psAjax = empty($niveles[$action]['p']) ? 1 : 0;

//comprobamos el nivel de acceso del usuario
$mensaje = $psCore->setLevel($psLevel, true);
if($mensaje != 1){
	echo $mensaje['mensaje'];
	die;
}
//obtenemos las clases necesarias
require '../class/c.posts.php';
$psPosts =& psPosts::getInstance();
switch($action){
	case 'favoritos':
		$smarty->assign("psFavoritos", $psPosts->getFavoritos());
		break;
	case 'favoritos-agregar':
		echo $psPosts->guardarFavorito();
		break;
	case 'favoritos-borrar':
		echo $psPosts->borrarFavorito();
		break;
}