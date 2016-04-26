<?php
//si la constante PS_HEADER no esta definida no se accede al script
if (!defined('PS_HEADER')) {
    exit("No se permite el acceso al script");
}

/**
 * Controlador ajax para los mensajes
 *
 * @name ajax.mensajes.php
 * @author Iván Martínez Tutor
 */

//niveles de acceso y plantilla de cada acción
$niveles = array(
	'mensajes-validar' => array('n' => 2, 'p' => ''),
    'mensajes-enviar' => array('n' => 2, 'p' => ''),
    'mensajes-respuesta' => array('n' => 2, 'p' => 'resp'),
    'mensajes-lista' => array('n' => 2, 'p' => 'lista'),
    'mensajes-editar' => array('n' => 2, 'p' => ''),
);

//variables locales
$psPage = 'php_files/p.mensajes.' . $niveles[$action]['p'];
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
	case 'mensajes-validar':
        echo $psMensaje->getValidUser();
		break;
    case 'mensajes-enviar':
        echo $psMensaje->nuevoMensaje();
		break;
    case 'mensajes-respuesta':
       $smarty->assign("mp",$psMensaje->nuevaRespuesta());
		break;
    case 'mensajes-lista':
        $smarty->assign("psMensajes",$psMensaje->getMensajes(1, false, 'monitor'));
		break;
    case 'mensajes-editar':
        echo $psMensaje->editarMensaje();
		break;
}