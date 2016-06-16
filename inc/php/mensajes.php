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
$psPage = "mensajes";
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
	$action = htmlspecialchars($_GET['action']);
	$sinleer = !empty($_GET['qt']) ? true : false;
	//realizamos la acción
	switch($action){
		case '':
            $smarty->assign("psMensajes",$psMensajes->getMensajes(2, $sinleer));
        break;
        case 'enviados':
            $smarty->assign("psMensajes",$psMensajes->getMensajes(3));
        break;
        case 'respondidos':
            $smarty->assign("psMensajes",$psMensajes->getMensajes(4));
        break;
		case 'search':
            $smarty->assign("psMensajes",$psMensajes->getMensajes(5));
        break;
        case 'leer':
            $smarty->assign("psMensajes",$psMensajes->leerMensaje());
        break;
        case 'avisos':
            //obtenemos los textos para los avisos
            if(empty($_GET['aid']) && empty($_GET['did'])){
                $smarty->assign("psMensajes",$psMonitor->getAviso());
            } elseif($_GET['aid']) {
                $smarty->assign("psMensaje", $psMonitor->leerAviso($_GET['aid']));
            } elseif($_GET['did']){
                $borrado = $psMonitor->borrarAviso($_GET['did']);
                if($borrado == true){
                	$psCore->redirectTo($psCore->settings['url'].'/mensajes/avisos/');
                }
            }
        break;
	}
	$smarty->assign("psSinLeer", filter_input(INPUT_GET, 'qt'));
	$smarty->assign("psAction", $action);
}

//si todo ok y no vamos por ajax asignamos en smarty
if(empty($psAjax)){
    $smarty->assign('psTitle', $psTitle);
    include(PS_ROOT.'/footer.php');
}
