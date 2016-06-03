<?php

/**
 * controlador de cuentas
 * @requisitos:
 * agregamos las variables por defecto de la pagina
 * verificaremos el nivel de acceso a la pagina
 * añadimos las instrucciones de codigo necesarias
 * agregamos los datos generados a smarty
 *
 * @name cuenta.php
 * @author Iván Martínez Tutor
 */

//primero creamos las variables por defecto
$psPage = "cuenta";//plantilla a mostrar con este archivo
$psLevel = 2;//nivel de acceso
$psAjax = empty($_GET['ajax']) ? 0 : 1;//comprobamos si la respuesta se realiza por ajax
$psContinue = true;//comprobamos si continuamos ejecutando el script
//incluimos el header
include ('../../header.php');
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
 	$action = filter_input(INPUT_GET, 'action');
	include(PS_CLASS."c.cuenta.php");
	$psCuenta =& psCuenta::getInstance();

	if(empty($action)){
		include('../extra/datos.php');
		include('../extra/estados.php');
		//comprobamos la edad del usuario
		$now = date("Y",time());
		$max = 100 - $psCore->settings['c_allow_edad'];
		$min = $now_year - $psCore->settings['c_allow_edad'];
		$smarty->assign("psMaxYear",$max);
		$smarty->assign("psEndYear",$min);
		//información del perfil
        $psPerfil = $psCuenta->cargarPerfil();
		$smarty->assign("psPerfil", $psPerfil);
		//datos del perfil del usuario
		$smarty->assign("psPerfilData", $psPerfilDatos);
        $smarty->assign("psPrivacidad", $psPrivacidad);
		//asignamoslos datos de pais y mes
		$smarty->assign("psPaises", $psPaises);
		$smarty->assign("psEstados", $estados[$psPerfil['user_pais']]);
		$smarty->assign("psMeses", $psMeses);
        //cargamos usuarios bloqueados
        $smarty->assign("psBloqueos", $psCuenta->cargarBaneos());

	} elseif($action == 'save'){
		echo $psCore->setJson($psCuenta->guardarPerfil());
	} elseif($action == 'desactivate'){
		if(!empty($_POST['validar'])){
			echo $psCuenta->desactivarCuenta();
		}
	}
 }

//ahora agregamos los datos generados a smarty
if(empty($psAjax)){
	$smarty->assign("psTitle", $psTitle);
	include (PS_ROOT.'footer.php');
}
