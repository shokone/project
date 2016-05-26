<?php

/**
 * controlador de los mensajes
 * @requisitos: 
 * cargamos los datos necesarios para ejecutar la seccion de post
 * a su vez será también el home de los mensajes
 * verificamos el nivel de acceso a la pagina
 * establecemos las variables importantes al archivo
 * asignamos las tareas necesarias que se realizaran en la seccion post
 * asignamos el valor de las variables a smarty
 * 
 * @name mensajes.php
 * @author Iván Martínez Tutor
 */

/**
 * definimos las variables importantes al archivo
 */
//plantilla para mostrar con el archivo
$psPage = "mod-history";
//nivel de acceso a la página
$psLevel = 2;
//comprobamos si la respuesta se realiza por ajax
$psAjax = empty($_GET['ajax']) ? 0 : 1;
//creamos el booleano para comprobar si debemos continuar con el script
$psContinue = true;
//damos un nombre al titulo de la pagina
$psTitle = $psCore->settings['titulo'].' - '.$psCore->settings['slogan'];

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
	include '../class/c.moderacion.php';
	$psModeracion =& psModeracion::getInstance();
	//obtenemos la acción
	$action = htmlspecialchars(filter_input(INPUT_GET, 'ver'));
    //obtenemos el historial
    if($action == 'fotos'){
    	$smarty->assign("psHistory", $psModeracion->getHistorial('fotos'));
	}else{
		$smarty->assign("psHistory", $psModeracion->getHistorial(1));
	}
	$smarty->assign("psAction", $action);
}

//si todo ok y no vamos por ajax asignamos en smarty
if(empty($psAjax)){
    $smarty->assign('psTitle', $psTitle);
    include(PS_ROOT.'/footer.php');
}