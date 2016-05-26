<?php

/**
 * controlador del perfil
 * @requisitos: 
 * cargamos los datos necesarios para ejecutar la seccion del perfil
 * verificamos el nivel de acceso a la pagina
 * establecemos las variables importantes al archivo
 * asignamos las tareas necesarias que se realizaran en la seccion post
 * asignamos el valor de las variables a smarty
 * 
 * @name perfil.php
 * @author Iván Martínez Tutor
 */

/**
 * definimos las variables importantes al archivo
 */
//plantilla para mostrar con el archivo
$psPage = "perfil";
//nivel de acceso a la página
$psLevel = 0;
//comprobamos si la respuesta se realiza por ajax
$psAjax = empty($_GET['ajax']) ? 0 : 1;
//creamos el booleano para comprobar si debemos continuar con el script
$psContinue = true;

include('../../header.php');
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
	$username = $_GET['user'];
	//obtenemos los datos de la db
	$consulta = "SELECT user_id, user_name, user_activo, user_baneado FROM u_miembros WHERE LOWER(user_name) = :uname";
	$valores = array('uname' => $username);
	$usuario = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
	//comprobamos si el usuario existe
	if(empty($usuario['user_id']) || ($usuario['user_activo'] != 1 && !$psUser->permisos['movcud'] && !$psUser->admod) || ($usuario['user_baneado'] != 0 && !$psUser->permisos['movcus'] && !$psUser->admod)){
		$psPage = 'aviso';
		$psAjax = 0;
		$smarty->assign("psAviso", array(
			'titulo' => 'Error!', 
			'mensaje' => (empty($usuario['user_id']) ? 'El usuario no existe' : 'La cuenta de '.$usuario['user_name'].' se encuentra suspendida' ), 
			'but' => 'Ir a la p&aacute;gina principal',
			'link' => "{$psCore->settings['url']}"
		));
	}else{
		include '../class/c.cuenta.php';
		$psCuenta =& psCuenta::getInstance();

		include '../extra/datos.php';
		$psInfo = $psCuenta->cargarInfo($usuario['user_id']);
	    $psInfo['uid'] = $usuario['user_id'];
		//comprobamos si el usuario está online
	    $online = (time() - ($psCore->settings['c_last_active'] * 60));
	    $inactive = ($online * 2); //inactivo será el doble del online
	    
	    if($psInfo['user_lastactive'] > $online){
	    	$psInfo['status'] = array('t' => 'Online', 'css' => 'online');
	    }elseif($psInfo['user_lastactive'] > $inactive){
	    	$psInfo['status'] = array('t' => 'Inactivo', 'css' => 'inactive');
	    }elseif($psInfo['user_baneado'] > 0){
	    	$psInfo['status'] = array('t' => 'Suspendido', 'css' => 'banned');
	    }else{
	    	$psInfo['status'] = array('t' => 'Offline', 'css' => 'offline');
	    }

		//cargamos la información general
		$psGeneral = $psCuenta->cargarInfoGeneral($usuario['user_id']);
	    $psInfo['nick'] = $psInfo['user_name'];
	    $psInfo = array_merge($psInfo, $psGeneral);
	    //obtenemos el país
		$psInfo['user_pais'] = $psPaises[$psInfo['user_pais']];
	    //comprobamos si lo estamos siguiendo
	    $psInfo['siguiendo'] = $psCuenta->siguiendo($usuario['user_id']);
		//comprobamos si nos está siguiendo
	    $psInfo['seguidores'] = $psCuenta->seguidores($usuario['user_id']);
	    //asignamos en smarty
		$smarty->assign("psInfo", $psInfo);
		$smarty->assign("psGeneral", $psGeneral);

	    //ahora obtenemos los datos del muro
	    include '../class/c.muro.php';
	    $psMuro =& psMuro::getInstance();
	    //comprobamos la privacidad del usuario
	    $privado = $psMuro->getPrivacidad($usuario['user_id'], $username, $psInfo['siguiendo'], $psInfo['seguidores']);
	    //comprobamos si podemos ver el muro
	    if($privado['m']['v'] == true){
	        // CARGAR HISTORIA
	        if(!empty($_GET['pid'])) {
	            $pub = $_GET['pid'];
	            $story = $psMuro->getHistoria($pub, $usuario['user_id']);
	            //
	            if(!is_array($story)){
	                $psPage = 'aviso';
	                $smarty->assign("psAviso", array(
	                	'titulo' => 'Error!', 
	                	'mensaje' => $story, 
	                	'but' => 'Ir a pagina principal', 
	                	'link' => "{$psCore->settings['url']}"
	                ));
	            }else{
	                $story['data'][1] = $story;
	                $smarty->assign("psMuro", $story);
	                $smarty->assign("psType","story");
	            }
	        }elseif($psCore->settings['c_allow_portal'] == 0 && $psInfo['uid'] == $psUser->user_id){
	            $smarty->assign("psMuro", $psMuro->getNovedades());
	            $smarty->assign("psType", "novedades");
	        }else{
	            $smarty->assign("psMuro", $psMuro->getMuro($usuario['user_id']));
	            $smarty->assign("psType", "muro");
	        }
	    }
	    $smarty->assign("psPrivacidad", $privado);
		// TITULO
		$psTitle = 'Perfil de '.$psInfo['nick'].' - '.$psTitle;
	}
}

//si todo ok y no vamos por ajax asignamos en smarty
if(empty($psAjax)){
    $smarty->assign('psTitle', $psTitle);
    include(PS_ROOT.'/footer.php');
}