<?php

/**
 * controlador del monitor
 * @requisitos: 
 * cargamos los datos necesarios para ejecutar la seccion de post
 * a su vez será también el home del monitor
 * verificamos el nivel de acceso a la pagina
 * establecemos las variables importantes al archivo
 * asignamos las tareas necesarias que se realizaran en la seccion post
 * asignamos el valor de las variables a smarty
 * 
 * @name monitor.php
 * @author Iván Martínez Tutor
 */

/**
 * definimos las variables importantes al archivo
 */
//plantilla para mostrar con el archivo
$psPage = "monitor";
//nivel de acceso a la página
$psLevel = 2;
//comprobamos si la respuesta se realiza por ajax
$psAjax = empty($_GET['ajax']) ? 0 : 1;
//creamos el booleano para comprobar si debemos continuar con el script
$psContinue = true;
//damos un nombre al titulo de la pagina
$psTitle = $psCore->settings['titulo'].' - '.$tsCore->settings['slogan'];

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
	$action = htmlspecialchars($_GET['action']);       

	if(empty($action)){
        $psMonitor->mostrarTipo = 2;
		$notificaciones = $psMonitor->getNotificaciones();
		$smarty->assign("psDatos", $notificaciones);
        $smarty->assign("psStatus", $_COOKIE);
	} else {
		$smarty->assign("psDatos", $psMonitor->getFollows($action));
	}
	$smarty->assign("psAction", $action);
}

//si todo ok y no vamos por ajax asignamos en smarty
if(empty($psAjax)){
    $smarty->assign('psTitle', $psTitle);
    include(PS_ROOT.'/footer.php');
}