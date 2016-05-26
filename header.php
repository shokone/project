<?php
/**
 * cargamos las clases base y ejecutamos la solicitud
 * 
 * @name header.php
 * @author Iván Martínez Tutor
 */

/**
 * definimos las variables importantes
 */


if(defined('PS_HEADER')){
 	return;
}

// Sesión
if(!isset($_SESSION)){
	session_start();
}

//mostramos los errores de ejecución, variables y valores en desuso
error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE ^ E_DEPRECATED);
//activamos la directiva de configuración
ini_set('display_errors', TRUE);

// Límite de ejecución
set_time_limit(400);

// Variable $page
if(!isset($page)){
	$page = '';
}

/**
 * definimos las constantes
*/
define('PS_ROOT', realpath(__DIR__));
define('PS_HEADER', true);
define('PS_CLASS', PS_ROOT.'/inc/class/');
define('PS_EXTRA', PS_ROOT.'/inc/extra/');
define('PS_FILES', PS_ROOT.'/files/');
set_include_path(get_include_path().PATH_SEPARATOR.realpath('./'));

/**
 * agregamos los archivos globales
 */
//incluimos el archivo de configuración principal
require_once 'config.inc.php';

//si no hemos instalado el script lo instalamos
if($db['host'] == 'host'){
    header("Location: ./install/index.php");
}


//incluimos el archivo de funciones
require_once PS_CLASS.'c.db.php';

//incluimos el archivo del nucleo del script
include PS_CLASS.'c.core.php';

//incluimos el archivo de control de usuarios
include PS_CLASS.'c.user.php';

//incluimos el archivo del monitor del usuario
include PS_CLASS.'c.monitor.php';

//incluimos el archivo de actividad del usuario
include PS_CLASS.'c.actividad.php';

//incluimos el archivo de mensajes del usuario
include PS_CLASS.'c.mensajes.php';

//incluimos el archivo de smarty
include PS_CLASS.'c.smarty.php';

//clean requests

/**
 * inicializamos los objetos principales de nuestra pagina
 */
//limpiamos variables
//cargamos la clase base de datos
$psDb = new psDb($db);
//con este atributo evitamos el error con pdo al darle un limite LIMIT a la consulta
$psDb->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('MyPDOStatement'));
$psDb->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
//cargamos el nucleo
$psCore =& psCore::getInstance();
//cargamos los usuarios
$psUser =& psUser::getInstance();
//monitor de usuario
$psMonitor = new psMonitor();
// actividad del usuario
$psActividad =& psActividad::getInstance();
// mensajes de usuarios
$psMensajes = new psMensajes();

//definimos la plantilla a utilizar
$psTema = $psCore->settings['tema']['t_path'];
if(empty($psTema)){
    $psTema = 'default';
}
define('PS_TEMA', $psTema);

// cargamos smarty
$smarty =& psSmarty::getInstance();
/**
 * asignamos las variables
 */
//variables de configuracion
$smarty->assign('psConfig', $psCore->settings);

//objeto de usuario
$smarty->assign('psUser',$psUser);

//avisos 
$smarty->assign('psAvisos',$psMonitor->avisos);

//notificaciones
$smarty->assign('psNotificaciones',$psMonitor->notificaciones);

//mensajes
$smarty->assign('psMensajes',$psMensajes->mensajes);
/**
 * hacemos validaciones extra
 */

//baneo por ip
if(!filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP)){
	die('Su ip no se pudo validar.'); 
}
$consulta = "SELECT id FROM w_blacklist WHERE type = :type && value = :ip";
$valores = array('type' => 1, 'ip' => $_SERVER['REMOTE_ADDR']);
if($psDb->db_execute($consulta, $valores, 'rowCount')){
	die('Bloqueado');
}
//comprobamos si esta online u offline

if($psCore->settings['offline'] == 1 && ($psUser->admod != 1 && $psUser->permisos['govwm'] == false) && $_GET['action'] != 'login-user'){
	$smarty->assign('psTitle',$psCore->settings['titulo'].' -  '.$psCore->settings['slogan']);
    if(empty($_GET['action'])){ 
	   $smarty->display('secciones/mantenimiento.tpl');
    }else{
    	die('Espera un poco...');
    }
	exit();
//comprobamos si el usuario esta baneado
} elseif($psUser->banned){
    $baneado = $psUser->getBaned();
    if(!empty($baneado)){
        if(empty($_GET['action'])){
            $smarty->assign('psBaneado',$baneado);
            $smarty->display('secciones/suspension.tpl');
        }else{
        	die('<div class="emptyError">Usuario suspendido</div>');
        }
        exit;
    }
}
