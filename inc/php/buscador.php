<?php

/**
 * controlador del buscador
 * @requisitos:
 * agregamos las variables por defecto de la pagina
 * verificaremos el nivel de acceso a la pagina
 * añadimos las instrucciones de codigo necesarias
 * agregamos los datos generados a smarty
 * 
 * @name buscador.php
 * @author Iván Martínez Tutor
 */

//primero creamos las variables por defecto
$psPage = "buscador";//plantilla a mostrar con este archivo
$psLevel = 0;//nivel de acceso
$psAjax = empty($_GET['ajax']) ? 0 : 1;//comprobamos si la respuesta se realiza por ajax
$psContinue = true;//comprobamos si continuamos ejecutando el script
//incluimos el header
include '../../header.php';
$psTitle = $psCore->settings['titulo'] . " - " . $psCore->settings['slogan'];

//verificamos el nivel de acceso
$psLevelVer = $psCore->setLevel($psLevel, true);
if($psLevelVer != 1){
	$psPage = "aviso";
	$psAjax = 0;
	$smarty->assign("psAviso", $psLevelVer);
	$psContinue = false;
}

/**
 * instrucciones de codigo
 * si podemos continuar cargamos los datos necesarios
 */
 if($psContinue){
 	$buscador = htmlspecialchars($_GET['q']);
 	$web = htmlspecialchars($_GET['e']);
 	$cat = htmlspecialchars($_GET['cat']);
 	$autor = htmlspecialchars($_GET['autor']);

 	include '../../class/c.posts.php');
	include '../../class/c.buscador.php');
	$psPosts =& psPosts::getInstance();
	$psBuscador =& psBuscador::getInstance();


 	if(!empty($buscador) || !empty($autor) && $web != 'google'){
 		$smarty->assign("psResults", $psBuscador->getQuery());
 	}	 

 	$smarty->assign("psQuery", $buscador);
    $smarty->assign("psEngine", $web);
    $smarty->assign("psCategory", $cat);
    $smarty->assign("psAutor", $autor);	
 }

//ahora agregamos los datos generados a smarty
if(empty($psAjax)){
	$smarty->assign("psTitle", $psTitle);
	include (PS_ROOT.'footer.php');
}