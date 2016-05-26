<?php

/**
 * controlador de la página aviso
 * @requisitos: 
 * cargamos los datos necesarios para ejecutar la seccion de post
 * a su vez será también la página de aviso post
 * verificamos el nivel de acceso a la pagina
 * establecemos las variables importantes al archivo
 * asignamos las tareas necesarias que se realizaran en la seccion post
 * asignamos el valor de las variables a smarty
 * 
 * @name aviso.php
 * @author Iván Martínez Tutor
 */

/**
 * definimos las variables importantes al archivo
 */
//plantilla para mostrar con el archivo
$psPage = "aviso";
//nivel de acceso a la página
$psLevel = 1;
//comprobamos si la respuesta se realiza por ajax
$psAjax = empty($_GET['ajax']) ? 0 : 1;
//creamos el booleano para comprobar si debemos continuar con el script
$psContinue = true;
//damos un nombre al titulo de la pagina
$psTitle = $psCore->settings['titulo'].' - '.$tsCore->settings['slogan'];

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
 * si el usuario tiene que validar la cuenta
 * comprobamos si la clave generada es correcta
 */
$email = $_GET['email'];
$type = intval($_GET['type']);
$hash = htmlspecialchars($_GET['hash']);
//consultamos a la base de datos
$consulta = "SELECT user_id, user_name, user_email FROM u_miembros WHERE user_email = :email";
$valores = array('email' => $email);
//comprobamos si existe el usuario
if(!$psDb->db_execute($consulta, $valores, 'rowCount')){
	$psAjax = 0;
	$smarty->assign("psAviso", array('titulo' => 'Error!', 'mensaje' => 'No existe ning&uacute;n usuario con ese email', 'but' => 'Ir a la p&aacute;gina principal'));
	$psContinue = false;
}

//borramos los contactos viejos
$consulta2 = "DELETE FROM w_contacts WHERE time < :tim";
$valores2 = array('tim' => (time() - 86400));
$psDb->db_execute($consulta2, $valores2);

//ahora comprobamos la clave hash
$consulta3 = "SELECT * FROM w_contacts WHERE hash = :hash AND user_email = :email AND type = :type ORDER BY id DESC";
$valores3 = array('hash' => $hash, 'email' => $email, 'type' => $type);
if(!$psDb->db_execute($consulta3, $valores3, 'rowCount')){
	$psAjax = 0;
	$smarty->assign("tsAviso",array('titulo' => 'Error!', 'mensaje' => 'La clave de validaci&oacute;n es incorrecta'));
	$psContinue = false;
}

/**
 * establecemos las variables principales
 * solo si el script puede continuar
 */
if($psContinue){
	$datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
	//comprobamos el tipo 
	if($type == 2){
		//actualizamos datos
		$consulta4 = "UPDATE u_miembros SET user_activo = :activo WHERE user_id = :uid";
		$valores4 = array('activo' => 1, 'uid' => $datos['user_id']);
		if($psDb->db_execute($consulta4, $valores4)){
			$consulta5 = "DELETE FROM w_contacts WHERE user_id = :uid";
			$valores5 = array('uid' => $datos['user_id']);
			$psDb->db_execute($consulta5, $valores5);
			$smarty->assign('psAviso', array(
				'titulo' => 'Bienvenido', 
				'mensaje' => 'Cuenta validada correctamente', 
				'but' => 'Ir a la p&aacute;gina principal')
			);
		}else{
			$smarty->assign('psAviso', array(
				'titulo' => 'Error!', 
				'mensaje' => 'Ha ocurrido un error', 
				'but' => 'Reintentar', 
				'link' => ''.$psCore->settings['url'].'/validar/'.$hash.'/2/'.$email.'')
			);
		}
	}else{
		//if($_POST['pass']){
			if(empty($_POST['pass'])){ 
				$smarty->assign('psAviso', array(
					'titulo' => 'Error!', 
					'mensaje' => 'Escriba una contrase&ntilde;a', 
					'but' => 'Volver', 
					'link' => ''.$psCore->settings['url'].'/password/'.$hash.'/1/'.$email.'')
				);
			}else{
				//actualizamos usuario
				$consulta6 = "UPDATE u_miembros SET user_password = :pass WHERE user_id = :uid";
				$valores6 = array('pass' => md5($_POST['pass']), 'uid' => $datos['user_id']);
				$psDb->db_execute($consulta6, $valores6);
				//borramos datos
				$consulta6 = "DELETE FROM w_contacts WHERE user_id = :uid";
				$valores6 = array('uid' => $datos['user_id']);
				$psDb->db_execute($consulta6, $valores6);
				$smarty->assign('psAviso', array(
					'titulo' => 'Bien!', 
					'mensaje' => 'Contrase&ntilde;a actualizada correctamente', 
					'but' => 'Ir a la p&aacute;gina principal')
				);
			}
		/*}else{
			$smarty->assign('psAviso', array(
				'titulo' => 'Actualizar contrase&ntilde;a', 
				'mensaje' => '<form method="post">Escribe tu nueva contrase&ntilde;a: <input type="password" name="pass" required="required"/><input type="submit" class="btn btnOk" value="Reestablecer contrase&ntilde;a"/></form>')
			);
		}*/
	}
}

//si todo ok y no vamos por ajax asignamos en smarty
if(empty($psAjax)){
    $smarty->assign('psTitle', $psTitle);
    include(PS_ROOT.'/footer.php');
}