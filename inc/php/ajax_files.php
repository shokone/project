<?php

/**
 * controlador ajax files
 * @requisitos:
 * agregamos las variables por defecto de la pagina
 * verificaremos el nivel de acceso a la pagina
 * añadimos las instrucciones de codigo necesarias
 * agregamos los datos generados a smarty
 *
 * @name ajax_files.php
 * @author Iván Martínez Tutor
 */

//primero creamos las variables por defecto
$psPage = "";//plantilla a mostrar con este archivo
$psLevel = 0;//nivel de acceso
$psAjax = empty($_GET['ajax']) ? 0 : 1;//comprobamos si la respuesta se realiza por ajax
//incluimos el header
include '../../header.php';
$psTitle = $psCore->settings['titulo'] . " - " . $psCore->settings['slogan'];

//creamos las variables locales para este archivo
$action = htmlspecialchars($_GET['action']);
$type = explode('-', $action);
$type = $type[0];

//llamamos al archivo que necesitemos en cada momento
$archivo = './ajax/ajax.' . $type . '.php';
if(file_exists($archivo)){
	include($archivo);
}else{
	die('0: El archivo solicitado no existe o no ha sido posible encontrarlo.');
}

//ahora agregamos los datos generados a smarty
if(empty($psAjax)){
	$smarty->template_ps = false;
	$smarty->assign("psTitle", $psTitle);
	include('../../footer.php');
}
