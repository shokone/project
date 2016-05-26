<?php
//si la constante PS_HEADER no esta definida no se accede al script
if (!defined('PS_HEADER')) {
    exit("No se permite el acceso al script");
}

/**
 * Controlador ajax para la cuenta live de los usuarios
 *
 * @name ajax.live.php
 * @author Iván Martínez Tutor
 */

//niveles de acceso y plantilla de cada acción
$niveles = array(
	'live-stream' => array('n' => 2, 'p' => 'stream'),
       'live-vcard' => array('n' => 0, 'p' => 'vcard'),
);

//variables locales
$psPage = 'php_files/p.live.' . $niveles[$action]['p'];
$psLevel = $niveles[$action]['n'];
$psAjax = empty($niveles[$action]['p']) ? 1 : 0;

//comprobamos el nivel de acceso del usuario
$mensaje = $psCore->setLevel($psLevel, true);
if($mensaje != 1){
	echo $mensaje['mensaje'];
	die;
}
//obtenemos las clases necesarias del archivo header.php
switch($action){
	case 'live-stream':
        //notificaciones
        if($_POST['nots'] != 'OFF') {
            $stream = $psMonitor->getNotificaciones(true);
            $smarty->assign("psStream", $stream);
        }
        //mensajes
        if($_POST['mps'] != 'OFF') {
            $psMensaj = $psMensajes->getMensajes(1, true, 'live'); // Edit: 21/02/2014
            $smarty->assign("psMensajes", $psMensaj);   
        }
		break;
	case 'live-vcard':
        $uid = $_REQUEST['uid'];
        $smarty->assign("tsData", $psUser->getUserInfo($uid));
		break;
    default:
        die('0: Este archivo no existe.');
        break;
}