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
include_once 'config.inc.php';

//si no hemos instalado el script lo instalamos
if($db['host'] == 'host'){
    header("Location: ./install/index.php");
}


//incluimos el archivo de funciones
include_once PS_CLASS.'c.db.php';

//incluimos el archivo del nucleo del script
include PS_CLASS.'c.core.php';

//incluimos el archivo de control de usuarios
include PS_CLASS.'c.user.php';

//incluimos el archivo del monitor del usuario
//include PS_CLASS.'c.monitor.php';

//incluimos el archivo de actividad del usuario
include PS_CLASS.'c.actividad.php';

//incluimos el archivo de mensajes del usuario
//include PS_CLASS.'c.mensajes.php';

//incluimos el arvchivo de smarty
include PS_CLASS.'c.smarty.php';

//clean requests

/**
 * inicializamos los objetos principales de nuestra pagina
 */
//limpiamos variables
echo "antes de cargar db <br>";
//cargamos la clase base de datos
$psDb =& psDb::getInstance($db);
echo "cargada db<br>";
//cargamos el nucleo
$psCore =& psCore::getInstance();
echo "cargada core<br>";
//cargamos los usuarios
$psUser =& psUser::getInstance();
echo "cargada clase user<br>";
//monitor de usuario
$psMonitor =& psMonitor::getInstance();
echo "cargada clase monitor<br>";
// actividad del usuario
$psActividad =& psActividad::getInstance();
echo "cargada clase actividad<br>";
// mensajes de usuarios
$psMensaje = new psMensajes();

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
$smary->assign('psConfig',$psCore->settings);

//objeto de usuario
$smarty->assign('psUser',$psUser);

//avisos 
$smary->assign('psAvisos',$psMonitor->avisos);

//notificaciones
$smary->assign('psNotificaciones',$psMonitor->notificaciones);

//mensajes
$smary->assign('psMensajes',$psMensajes->mensajes);

/**
 * hacemos validaciones extra
 */

//baneo por ip

//comprobamos si esta online u offline

//comprobamos si el usuario esta baneado