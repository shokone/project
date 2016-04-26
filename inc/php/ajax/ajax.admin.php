<?php
//si la constante PS_HEADER no esta definida no se accede al script
if (!defined('PS_HEADER')) {
    exit("No se permite el acceso al script");
}

/**
 * Controlador ajax para la administración
 *
 * @name ajax.admin.php
 * @author Iván Martínez Tutor
 */

//niveles de acceso y plantillas para cada acción
$niveles = array(
	'admin-medalla-borrar' => array('n' => 4, 'p' => ''),
	'admin-medalla-asignar' => array('n' => 4, 'p' => ''),
	'admin-foto-borrar' => array('n' => 4, 'p' => ''),
	'admin-foto-setOpenClosed' => array('n' => 4, 'p' => ''),
	'admin-foto-setShowHide' => array('n' => 4, 'p' => ''),
	'admin-medallas-borrar-asignacion' => array('n' => 4, 'p' => ''),
	'admin-users-setInActivo' => array('n' => 4, 'p' => ''),
	'admin-users-sessions' => array('n' => 4, 'p' => ''),
	'admin-noticias-setInActive' => array('n' => 4, 'p' => ''),
	'admin-sesiones-borrar' => array('n' => 4, 'p' => ''),
	'admin-nicks-change' => array('n' => 4, 'p' => ''),
    'admin-blacklist-delete' => array('n' => 4, 'p' => ''),
    'admin-badwords-delete' => array('n' => 4, 'p' => ''),
);
//la variable $action la obtenemos del archivo admin.php
$psPage = 'php_files/p.admin.' . $niveles[$action]['p'];
$psLevel = $niveles[$action]['n'];
$psAjax = empty($niveles[$action]['p']) ? 1 : 0;

//comprobamos el nivel de acceso del usuario
$mensaje = $psCore->setLevel($psLevel, true);
if($mensaje != 1){
	echo $mensaje['mensaje'];
	die;
}
//cargamos las clases necesarias
include '../../class/c.medals.php';
include '../../class/c.admin.php';
$psMedallas =& psMedallas::getInstance();
$psAdmin =& psAdmin::getInstance();

switch($action){
	case 'admin-medalla-borrar':
        echo $psMedallas->borrarMedalla();
	break;
	case 'admin-medalla-asignar':
        echo $psMedallas->asignarMedalla();
	break;
	case 'admin-medallas-borrar-asignacion':
        echo $psMedallas->borrarAsignarMedalla();
	break;
	case 'admin-foto-borrar':
        echo $psAdmin->borrarFoto();
	break;
	case 'admin-foto-setOpenClosed':
        echo $psAdmin->setFotoComentarios();
	break;
	case 'admin-foto-setShowHide':
        echo $psAdmin->setFotoEstado();
	break;
	case 'admin-users-InActivo':
        echo $psAdmin->setUserInactivo();
	break;
	case 'admin-users-sessions':
        echo $psAdmin->delSesion();
	break;
	case 'admin-noticias-setInActive':
        echo $psAdmin->setNoticiaActiva();
	break;
	case 'admin-sesiones-borrar':
        echo $psAdmin->delSesion();
	break;
	case 'admin-nicks-change':
        echo $psAdmin->changeNick();
	break;
    case 'admin-blacklist-delete':
        echo $psAdmin->delBlockUser();
	break;
    case 'admin-badwords-delete':
        echo $psAdmin->delBadWord();
	break;
    default:
        exit('Este archivo no existe.');
    break;
}