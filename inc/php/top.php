<?php

/**
 * controlador de tops
 * @requisitos:
 * agregamos las variables por defecto de la pagina
 * verificaremos el nivel de acceso a la pagina
 * añadimos las instrucciones de codigo necesarias
 * agregamos los datos generados a smarty
 * 
 * @name top.php
 * @author Iván Martínez Tutor
 */

/**
 * variables por defecto del archivo
 */
//plantilla para mostrar con el archivo
$psPage = "tops";

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

/**
 * instrucciones de codigo y variables locales del archivo
 * si podemos continuar cargamos los datos necesarios
 */
if($psContinue){
    //incluimos la clase tops
    include(PS_CLASS."c.tops.php");
    $psTops =& psTops::getInstance();
    //comprobamos la fecha
    $fecha = empty(filter_input(INPUT_GET, 'fecha')) || filter_input(INPUT_GET, 'fecha') > 5 ? 5: (int)filter_input(INPUT_GET, 'fecha');
    //agregamos a smarty
    $smarty->assign("psFecha", $fecha);
    //categoria
    $cat = empty(filter_input(INPUT_GET, 'cat')) ? 0 : (int)filter_input(INPUT_GET, 'cat');
    //agregamos a smarty
    $smarty->assign("psCat",$cat);
    //obtenemos la accion a realizar
    $action = empty(filter_input(INPUT_GET, 'action')) ? 'posts' : (string)filter_input(INPUT_GET, 'action');
    //asignamos a smarty
    $smarty->assign("psAction",$action);
    
    //comprobamos la acción a seleccionar
    switch($action){
        //obtenemos el top de los post
        case 'posts':
            $smarty->assign("psTops",$psTops->getTopPosts($fecha,$cat));
            break;
        //obtenemos el top de los usuarios
        case 'usuarios':
            $smarty->assign("psTops",$psTops->getTopUsers($fecha,$cat));
            break;
    }
}

/**
 * agregamos los datos generados a smarty
 */
//si la peticion fue realizada por ajax detenemos el script
if(empty($psAjax)){
    //asignamos el titulo de la pagina actual
    $smarty->assign("psTitle",$psTitle);
    //incluimos el footer
    include(PS_ROOT.'/footer.php');
}