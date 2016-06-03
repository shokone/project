<?php

/**
 * controlador del portal
 * @requisitos:
 * cargamos los datos necesarios para ejecutar la seccion de post
 * a su vez será también el home del portal
 * verificamos el nivel de acceso a la pagina
 * establecemos las variables importantes al archivo
 * asignamos las tareas necesarias que se realizaran en la seccion post
 * asignamos el valor de las variables a smarty
 *
 * @name portal.php
 * @author Iván Martínez Tutor
 */

/**
 * definimos las variables importantes al archivo
 */
//plantilla para mostrar con el archivo
$psPage = "portal";
//nivel de acceso a la página
$psLevel = 0;
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
	//cargamos las clases necesarias
	include(PS_CLASS."c.portal.php");
    $psPortal =& psPortal::getInstance();
    // AFILIADOS
    include(PS_CLASS."c.afiliados.php");
    $psAfiliados =& psAfiliados::getInstance();
    // NOS HAN REFERIDO?
    if(!empty($_GET['ref'])){
    	$psAfiliados->urlInterna();
    }
    //asignamos datos a smarty
    $smarty->assign("psMuro", $psPortal->getNoticias());
    $smarty->assign("psInfo", array('uid' => $psUser->user_id));
    $smarty->assign("psType", "noticias");
    //
    $smarty->assign("psCategorias",$psPortal->getConfigPost());
    $psPosts = $psPortal->getPostPropios();
    $smarty->assign("psPosts", $psPosts['data']);
    $smarty->assign("psPages", $psPosts['pages']);
    //
    $smarty->assign("psLastPostVisit",$psPortal->getLastPost());
    $smarty->assign("psFavoritos",$psPortal->getFavoritos());
    // FOTOS
    $psImages = $psPortal->getFotos();
	$smarty->assign("psImages", $psImages);
    $smarty->assign("psImgTotal", count($psImages));
    // STATS
    $smarty->assign("psStats",$psPortal->getStats());
    // AFILIADOS
    //
    $smarty->assign("psAfiliados", $psAfiliados->getAfiliados());
}

//si todo ok y no vamos por ajax asignamos en smarty
if(empty($psAjax)){
    $smarty->assign('psTitle', $psTitle);
    include(PS_ROOT.'/footer.php');
}
