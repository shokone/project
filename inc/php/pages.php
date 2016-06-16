<?php

/**
 * controlador de páginas como ayuda, contacto, protocolo...
 * @requisitos:
 * agregamos las variables por defecto de la pagina
 * verificaremos el nivel de acceso a la pagina
 * añadimos las instrucciones de codigo necesarias
 * agregamos los datos generados a smarty
 * 
 * @name pages.php
 * @author Iván Martínez Tutor
 */

/**
 * variables por defecto del archivo
 */
//plantilla para mostrar con el archivo
$psPage = "pages";

//nivel de acceso a la pagina
$psLevel = 0;

//comprobamos si la respuesta se realiza por ajax
$psAjax = empty($_GET['ajax']) ? 0 : 1;

//creamos la variable para comprobar si continuamos con el script
$psContinue = true;

//incluimos el header
include("../../header.php");

//cargamos el titulo de la pagina actual
$psTitle = $psCore->settings['titulo']." - ".$psCore->settigns['slogan'];

//verificamos el nivel de acceso configurado previamente
$psLevelVer = $psCore->setLevel($psLevel, true);
if($psLevelVer != 1){
    $psPage = "aviso";
    $psAjax = 0;
    $smarty->assign('psAviso',$psLevelVer);
    //no se puede continuar con el script
    $psContinue = false;
}

if($psContinue){
	$action = $_GET['action'];
	switch($action){
		case 'ayuda': break;
        case 'contacto': break;
        case 'protocolo': break;
        case 'terminos-y-condiciones': break;
        case 'privacidad': break;
        case 'dmca': break;
        default: $psCore->redirectTo($psCore->settings['url']); break;
	}
	$smarty->assign('psAction', $action);
}

//ahora agregamos los datos generados a smarty
if(empty($psAjax)){
    $smarty->assign("psTitle", $psTitle);
    include ('../../footer.php');
}
