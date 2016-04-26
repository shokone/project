<?php
//si la constante PS_HEADER no esta definida no se accede al script
if (!defined('PS_HEADER')) {
    exit("No se permite el acceso al script");
}

/**
 * Controlador ajax para el login de los usuarios
 *
 * @name ajax.login.php
 * @author Iván Martínez Tutor
 */

//niveles de acceso y plantilla de cada acción
$niveles = array(
	'login-user' => array('n' => 1, 'p' => ''),
	'login-activar' => array('n' => 1, 'p' => ''),
	'login-salir' => array('n' => 1, 'p' => '')
);

//variables locales
$psPage = 'php_files/p.login.' . $niveles[$action]['p'];
$psLevel = $niveles[$action]['n'];
$psAjax = empty($niveles[$action]['p']) ? 1 : 0;

//comprobamos el nivel de acceso del usuario
$mensaje = $psCore->setLevel($psLevel, true);
if($mensaje != 1){
	echo $mensaje['mensaje'];
	die;
}
//obtenemos las clases necesarias del archivo header.php
switch($action){
	case 'login-user':
		$nick = filter_input(INPUT_POST, 'nick');
		$pass = filter_input(INPUT_POST, 'pass');
		$remember = ($_POST['rem'] == true) ? true : false;
		//comprobamos
		if(empty($user) || empty($pass)){
			echo '0: Por favor rellena todos los campos.';
		}else{
			echo $psUser->login($nick, $pass, $remember);
		}
		break;
	case 'login-activar':
		//llamamos a la función
		$activar = $psUser->activate();
		//y cmprobamos los datos
		if($activar['user_password']){
			//si todo ok el usuario accede y es redirigido a la pantalla de su cuenta
			$psUser->login($activar['user_nick'], $activar['user_password'], true, $psCore->settings['url'] . '/cuenta/');
		}else{
			//si no enviamos un mensaje de error al usuario
			$psPage = 'aviso';
			$psAjax = 0;
			$psAviso = array(
				'titutlo' => 'Ocurri&oacute; un error al activar tu cuenta', 
				'mensaje' => 'Lo sentimos, pero el c&oacute;digo de validaci&oacute;n es incorrecto.'
			);
			//asignamos a smarty
			$smarty->assign('psAviso', $psAviso);
		}
		break;
	case 'login-salir':
		//desconectamos de la sesión al usuario
		$psUser->logout($psUser->user_id, $psCore->settings['url']);
		break;
}