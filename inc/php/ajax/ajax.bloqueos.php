<?php
//si la constante PS_HEADER no esta definida no se accede al script
if (!defined('PS_HEADER')) {
    exit("No se permite el acceso al script");
}

/**
 * Controlador ajax para los bloqueos de los usuarios
 *
 * @name ajax.bloqueos.php
 * @author Iván Martínez Tutor
 */

//niveles de acceso y plantillas para cada acción
$niveles = array(
	'bloqueos-cambiar' => array('n' => 2, 'p' => ''),
);
//la variable $action la obtenemos del archivo cuenta.php
$psPage = 'php_files/p.bloqueos.' . $niveles[$action]['p'];
$psLevel = $niveles[$action]['n'];
$psAjax = empty($niveles[$action]['p']) ? 1 : 0;

//comprobamos el nivel de acceso del usuario
$mensaje = $psCore->setLevel($psLevel, true);
if($mensaje != 1){
	echo $mensaje['mensaje'];
	die;
}
//obtenemos las clases necesarias
include '../class/c.cuenta.php';
$psCuenta =& psCuenta::getInstance();
switch($action){
	case 'bloqueos-cambiar':
		echo $psCuenta->cambiarBaneos();
		break;
}