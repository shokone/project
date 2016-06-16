<?php

/**
 * controlador de los mensajes
 * @requisitos: 
 * cargamos los datos necesarios para mostrar el historial de cambios en post y fotos
 * 
 * @name mod-historial.php
 * @author Iván Martínez Tutor
 */

/**
 * definimos las variables importantes al archivo
 */
//plantilla para mostrar con el archivo
$psPage = "mod-historial";
//nivel de acceso a la página
$psLevel = 2;
//comprobamos si la respuesta se realiza por ajax
$psAjax = empty($_GET['ajax']) ? 0 : 1;
//creamos el booleano para comprobar si debemos continuar con el script
$psContinue = true;
include '../../header.php';
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
    include('../../footer.php');
}