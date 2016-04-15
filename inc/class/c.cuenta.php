<?php
//comprobamos si hemos declarado la contante PS_HEADER
if(!defined('PS_HEADER')){
    exit("No se permite el acceso al script");
}
/**
 * clase psCuenta
 * clase destinada al control de la cuenta de los usuarios
 *
 * @name c.cuenta.php
 * @author Iván Martínez Tutor
 */
class psCuenta(){
	//instanciamos la clase
	public static &getInstance(){
		static $instancia;
		if(is_null($instancia)){
			$instancia = new psCuenta();
		}
		return $instancia;
	}

	/**
     * @funcionalidad obtenemos los datos del perfil del usuario
     * @param  [type]   $uid -> obtenemos el id del usuario
     * @return type devolvemos un array con los datos obtenidos
     */
	function cargarPerfil($uid){
		global $psDb, $psUser;
		if(empty($uid)){
			$uid = $psUser->user_id;
		}
		//realizamos la consulta en la db
		$consulta = "SELECT p.*, u.user_registro, u.user_lastactive FROM u_perfil AS p LEFT JOIN u_miembros AS u ON p.user_id = u.user_id WHERE p.user_id = :uid";
		$valores = array('uid' => $uid);
		$info = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
		$info = array(
			'p_gustos' => unserialize($info['p_gustos']),
			'p_tengo' => unserialize($info['p_tengo']),
			'p_idiomas' => unserialize($info['p_idiomas']),
			'p_socials' => array('f' => $info['p_socials'][0]),
			'p_socials' => array('t' => $info['p_socials'][1]),
			'p_configs' => unserialize($info['p_configs']),
			'p_total' => unserialize($info['p_total'])
		);
		$info['porcentaje'] = $this->getPorcentaje($info['p_total'], 40);
		return $info;
	}

	/**
     * @funcionalidad 
     * @return type 
     */
	function guardarPerfil(){
		global $psDb, $psUser, $psCore;
		$maxlength = 900; //máximo de caracteres
		$guardar = filter_input(INPUT_POST, 'save');//guardar los datos
		switch($guardar){
			case 1: //datos generales del usuario
				//obtenemos los datos antiguos
				$consulta = "SELECT user_dia, user_mes, user_ano, user_pais, user_estado, user_sexo, user_firma FROM u_perfil WHERE user_id = :uid";
				$valores = array('uid' => $psUser->user_id);
				$info = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
				//obtenemos los datos nuevos
				$perfil = array(
					'email' => filter_input(INPUT_POST, 'email'),
					'pais' => filter_input(INPUT_POST, 'pais'),
					'estado' => filter_input(INPUT_POST, 'estado'),
					'sexo' => ($_POST['sexo']) ? 0 : 1,
					'dia' => filter_input(INPUT_POST, 'dia'),
					'mes' => filter_input(INPUT_POST, 'mes'),
					'ano' => filter_input(INPUT_POST, 'ano'),
					'firma' => $psCore->badWords(filter_input(INPUT_POST, 'firma'), true)
				);
				$ano = date("Y", time());
				$email = $this->validarEmail($perfil['email']);
				//hacemos las comprobaciones oportunas
				if(!$email){//coprobamos el email
					$mensaje = array('field' => 'email', 'error' => 'El formato de email introducido no es v&aacute;lido.');
					//obtenemos el email anterior
					$perfil['email'] = $psUser->info['email'];
				}else if(!checkdate($perfil['dia'], $perfil['mes'], $perfil['ano']) || ($peril['ano'] > $ano || $perfil['ano'] < ($ano - 100))){
					//comprobamos la fecha de nacimiento
					$mensaje = array('field' => 'fecha', 'error' => 'La fecha de nacimiento introducida no es v&aacute;lido.');
					//obtenemos los datos anteriores que tenía el usuario
					$perfil['dia'] = $info['user_dia'];
					$perfil['mes'] = $info['user_mes'];
					$perfil['ano'] = $info['user_ano'];
				}else if($perfil['sexo'] > 2){//comprobamos el sexo
					$mensaje = array('field' => 'sexo', 'error' => 'Tienes que especificar un sexo.');
					$perfil['sexo'] = $info['user_sexo'];
				}else if(empty($perfil['pais'])){//comprobamos el país
					$mensaje = array('field' => 'pais', 'error' => 'Tienes que especificar tu pa&iacute;s.');
					$perfil['pais'] = $info['user_pais'];
				}else if(empty($perfil['estado'])){//comprobamos el estado
					$mensaje = array('field' => 'estado', 'error' => 'Tienes que especificar tu estado.');
					$perfil['estado'] = $info['user_estado'];
				}else if(strlen($perfil['firma']) > 250){//comprobamos la firma
					$mensaje = array('field' => 'firma', 'error' => 'La firma no puede exceder los 250 caracteres.');
					$perfil['firma'] = $info['user_firma'];
				}else if($psUser->info['user_email'] != $perfil['email']){//hacemos otra comprobación con el email
					$consulta = "SELECT user_id FROM u_miembros WHERE user_email = :email";
					$valores = array('email' =< $perfil['email']);
					$existe = $psDb->db_execute($consulta, $valores, 'rowCount');
					//comprobamos si el email ya existe
					if($existe){
						$mensaje = array('field' => 'email', 'error' => 'El email introducido ya existe.');
						$perfil['email'] = $psUser->info['user_email'];
					}else{
						$mensaje = array('field' => 'email', 'error' => 'Los cambios fueron aceptados correctamente excepto el email. Este debe de ser comprobado por un administrador para completar el cambio. Recibir&aacute; un correo electr&oacute; con los cambios realizados y su cambio de email cuando sea aprobado.');
					}
				}
				break;
			case 2: //gustos, estado, hijos y algunos datos más del usuario
				$sitio = input_filter(INPUT_POST, 'sitio');
				if(!empty($sitio)){
					if(substr($sitio, 0, 7) != 'http://'){
						$sitio = 'http://' . $sitio;
					}
				}
				$facebook = filter_input(INPUT_POST, 'facebook');
				$twitter = filter_input(INPUT_POST, 'twitter');
				$youtube = filter_input(INPUT_POST, 'youtube');
				for($a = 0; $a < 5; $a++){
					$gustos[$a] = filter_input(INPUT_POST, 'g_'.$a);
				}
				$perfil = array(
					'nombre' => filter_input(INPUT_POST, 'nombrez')
					'mensaje' => filter_input(INPUT_POST, 'mensaje'),
					'sitio' => $sitio,
					'socials' => serialize(array($facebook,$twitter,$youtube)),
					'gustos' => serialize($gustos),
					'estado' => filter_input(INPUT_POST, 'estado'),
					'hijos' => filter_input(INPUT_POST, 'hijos'),
					'vivo' => filter_input(INPUT_POST, 'vivo'),
				);
				//comprobamos que la url del sitio sea correcta
				if(!empty($perfil['sitio']) && !filter_var($perfil['sitio'], FILTER_VALIDATE_URL)){
					return array('field' => 'url', 'error' => 'La url introducida no es correcta.');
				}
				break;
			case 3: //datos físicos del usuario
				$tengo = array(filter_input(INPUT_POST, 't_0'), filter_input(INPUT_POST, 't_1'));
				$perfil = array(
					'altura' => filter_input(INPUT_POST, 'altura'),
					'peso' => $filter_input(INPUT_POST, 'peso'),
					'pelo' => filter_input(INPUT_POST, 'pelo_color'),
					'ojos' => filter_input(INPUT_POST, 'ojos_color'),
					'fisico' => filter_input(INPUT_POST, 'fisico'),
					'dieta' => filter_input(INPUT_POST, 'dieta'),
					'tengo' => serialize($tengo),
					'fumo' => filter_input(INPUT_POST, 'fumo'),
					'tomo' => filter_input(INPUT_POST, 'tomo_alcohol'),
				);
				break;
			case 4: //estudios, trabajo del usuario
				for($a = 0; $a < 7; $a++){//obtenemos los diferentes idiomas
					$idiomas[$a] = 'idioma_'.$i;
				} 
				$perfil = array(
					'estudios' => filter_input(INPUT_POST, 'estudios'),
					'idiomas' => serialize($idiomas),
					'profesion' => filter_input(INPUT_POST, 'profesion'),
					'empresa' => filter_input(INPUT_POST, 'empresa'),
					'sector' => filter_input(INPUT_POST, 'sector'),
					'ingresos' => filter_input(INPUT_POST, 'ingresos'),
					'int_prof' => substr(filter_input(INPUT_POST, 'intereses_profesionales'), 0, $maxlength),
					'hab_prof' => substr(filter_input(INPUT_POST, 'habilidades_profesionales'), 0, $maxlength),
				);
				break;
			case 5: //intereses y aficiones del usuario
				$perfil = array(
					'intereses' => $psCore->badWords(substr(filter_input(INPUT_POST, 'intereses'), 0, $maxlength)),
					'hobbies' => $psCore->badWords(substr(filter_input(INPUT_POST, 'hobbies'), 0, $maxlength)),
					'tv' => $psCore->badWords(substr(filter_input(INPUT_POST, 'tv'), 0, $maxlength)),
					'musica' => $psCore->badWords(substr(filter_input(INPUT_POST, 'musica'), 0, $maxlength)),
					'deportes' => $psCore->badWords(substr(filter_input(INPUT_POST, 'deportes'), 0, $maxlength)),
					'libros' => $psCore->badWords(substr(filter_input(INPUT_POST, 'libros'), 0, $maxlength)),
					'peliculas' => $psCore->badWords(substr(filter_input(INPUT_POST, 'peliculas'), 0, $maxlength)),
					'comida' => $psCore->badWords(substr(filter_input(INPUT_POST, 'comida'), 0, $maxlength)),
					'heroes' => $psCore->badWords(substr(filter_input(INPUT_POST, 'heroes'), 0, $maxlength)),
				);
				break;
			case 6: //cambio de password del usuario
				$pass = filter_input(INPUT_POST, 'passwd');
				$new_pass = filter_input(INPUT_POST, 'new_passwd');
				$new_pass2 = filter_input(INPUT_POST, 'confirm_passwd');
				if(empty($new_pass) || empty($new_pass2)){
					return array('field' => 'passwd', 'error' => 'Debe introducir una contrase&ntilde;a.');
				}else if(strlen($new_pass) < 5){
					return array('field' => 'new_passwd', 'error' => 'La contrase&ntilde;a debe tener al menos 5 caracteres.');
				}else if($new_pass != $new_pass2){
					return array('field' => 'confirm_passwd', 'error' => 'Las contrase&ntilde;as no son iguales.');
				}else{
					$password = md5($pass);
					if($pass != $psUser->info['user_password']){
						return array('field' => 'passwd', 'error' => 'Tu contrase&ntilde;a actual no es correcta.');
					}else{
						$new_password = md5($new_pass);
						$consultaPass = "UPDATE u_miembros SET user_password = :pass WHERE user_id = :uid";
						$valoresPass = array(
							'pass' => $new_password,
							'uid' => $psUser->user_id,
						);
						if($psDb->db_execute($consultaPass, $valoresPass)){
							return true;
						}
					}
				}
				break;
			case 7: //configuración del muro del usuario
				$firma = ($_POST['muro_firma'] > 4) ? 5 : filter_input(INPUT_POST, 'muro_firma');
				$mps = ($_POST['rec_mps'] > 6) ? 5 : filter_input(INPUT_POST, 'rec_mps');
				$hits = ($_POST['last_hits'] == 1 || $_POST['last_hits'] == 2) ? 0 : filter_input(INPUT_POST, 'last_hits');
                $datosMuro = array('m' => filter_input(INPUT_POST, 'muro'), 'mf' => $firma, 'rmp' => $mps, 'hits' => $hits);
                //
                $perfil['configs'] = serialize($datosMuro);
				break;
			case 8: //cambio de nick y email del usuario
				$new_nick = $psCore->badWords(filter_input(INPUT_POST, 'new_nick'));
				//realizamos las consultas
				$cn1 = "SELECT id FROM w_blacklist WHERE type = :type AND LOWER(value) = :value";
				$cn2 = "SELECT user_id FROM u_miembros WHERE LOWER(user_name) = :name";
				$cn3 = "SELECT id, user_id, time FROM u_nicks WHERE user_id = :uid AND estado = :estado";
				
				$valn1 = array('type' => 4, 'value' => $new_nick;
				$valn2 = array('name' => $new_nick);
				$valn3 = array('uid' => $new_nick, 'estado' => 0);
				
				//ejecutamos las consultas y realizamos las comprobaciones
				if($psDb->db_execute($cn1, $valn1, 'rowCount')){
					return array('field' => 'new_nick', 'error' => 'El nick introducido no est&aacute; permitido.');
				}else if($psDb->db_execute($cn2, $valn2, 'rowCount')){
					return array('field' => 'new_nick', 'error' => 'El nick introducido ya existe.');
				}
				$datos = $psDb->db_execute($cn3, $valn3, 'fetch_assoc');

				$cn4 = "UPDATE u_miembros SET user_name_changes = :changes WHERE user_id = :uid";
				$valn4 = array('changes' => 3, 'uid' => $datos['user_id']);

				if(!empty($datos['id'])){
					return array('field' => 'new_nick', 'error' => 'Ha realizado un petici&oacute;n de cambio de nick hace poco tiempo. Deber&aacute; esperar para poder realizar otro cambio.');
				}else if(time() - $datos['time'] >= 2592000){
					$psDb->db_execute($cn4, $valn4);
				}
				//comprobamos el pass
				$pass = md5(filter_input(INPUT_POST, 'password'));
				if($pass != $psUser->info['user_password']){
					return array('field' => 'password', 'error' => 'La contrase&ntilde;a introducida no es correcta.');
				}else{
					$email = $this->validarEmail(filter_input(INPUT_POST, 'pemail'));
					if(!$email){
						return array('field' => 'pemail', 'error' => 'El email introducido no es v&aacute;lido.');
					}
					$mail = empty($_POST['pemail']) ? $psUser->info['user_email'] : filter_input(INPUT_POST, 'pemail');
					if(strlen($new_nick) < 4 || strlen($new_nick) > 20){
						return array('field' => 'new_nick', 'error' => 'El nick introducido debe tener una longitud entre 4 y 20 caracteres.');
					}
					if(!preg_match('/^([A-Za-z0-9]+)$/')){
						return array('field' => 'new_nick', 'error' => 'El nick s&oacute;lo puede contener n&uacute;meros y letras.');
					}
					$pass = md5(filter_input(INPUT_POST, 'password'));
					if(!filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP)){
						return array('field' => 'ip', 'error' => 'Error. Su ip no pudo ser validada.');
					}
					$cn5 = "INSERT INTO u_nicks (user_id, user_email, name_1, name_2, hash, time, ip) VALUES (:uid, :email, :name1, :name2, :hash, :tiempo, :ip)";
					$valn5 = array(
						'uid' => $psUser->user_id,
						'email' => $email,
						'name1' => $psUser->nick,
						'name2' => $new_nick,
						'hash' => $pass,
						'tiempo' => time(),
						'ip' => $_SERVER['REMOTE_ADDR']
					);
					if($psDb->db_execute($cn5, $valn5)){
						return array('error' => 'El cambio ha sido enviado correctamente. Recibir&aacute; una respuesta cuando un administrador lo haya aprobado.');
					}
				}
				break;
		}
		//comprobamos el porcentaje del perfil completado por el usuario
		$total = array(6,8,9,8,9);//obtenemos la cantidad de campos en cada categoria
		$id = $guardar - 1;
		if($guardar > 1 && $guardar < 6){
			$total[$id] = $this->getTotalPorcentaje($perfil, $total[$id]);
			$cg1 = "SELECT p_total FROM u_perfil WHERE user_id = :uid";
			$vg1 = array('uid' => $psUser->user_id);
			$porcentaje = $psDb->db_execute($cg1, $vg1, 'fetch_assoc');
			$porcentaje = unserialize($porcenaje['p_total']);
			$porcentaje_now = $this->getPorcentaje($porcentaje, 40);
			$porcentaje = serialize($porcentaje);
			$cg2 = "UPDATE u_perfil SET p_total = :total WHERE user_id = :uid";
			$vg2 = array(
				'total' => $porcentaje,
				'uid' => $psUser->user_id
			);
			$psDb->db_execute($cg2, $vg2);
		}
		//actualizamos datos en la base de datos
		if($guardar == 1){
			$consultaSave = "UPDATE u_miembros SET user_email = :email WHERE user_id = :uid";
			$valoresSave = array(
				'email' => $perfil['email'],
				'uid' => $psUser->user_id
			);
			$psDb->db_execute($consultaSave, $valoresSave);
			$actualizaciones = $psCore->getDatos($perfil, 'user_');
			$datosActualizar = '';
			foreach($actualizaciones['values'] as $key => $valor){
				$datosActualizar .= $valor;
			}
			foreach($actualizaciones['values2'] as $key => $valor){
				$updates2[$key] = $valor;
			}
			$updates2['uid'] = $psUser->user_id;
			$consultaSave2 = "UPDATE u_perfil SET " . $datosActualizar . " WHERE user_id = :uid";
			if(!$psDb->db_execute($consultaSave2, $updates2)){
				return array('error' => 'Error al actualizar los datos del perfil en la base de datos');
			}
		}else{
			$actualizaciones = $psCore->getDatos($perfil, 'p_');
			$datosActualizar = '';
			foreach($actualizaciones['values'] as $key => $valor){
				$datosActualizar .= $valor;
			}
			$valores['datosActualizar'] = $datosActualizar;
			foreach($actualizaciones['values2'] as $key => $valor){
				$updates2[$key] = $valor;
			}
			$updates2['uid'] = $psUser->user_id;
			$consultaSave = "UPDATE u_perfil SET :datosActualizar WHERE user_id = :uid";
			if(!$psDb->db_execute($consultaSave, $updates2)){
				return array('error' => 'Error al actualizar los datos del perfil en la base de datos');
			}
		}
	}

	/**
     * @funcionalidad desactivamos la cuenta del usuario
     * @return type devolvemos un balor booleano con el resultado de la consulta
     */
	function desactivarCuenta(){
		global $psUser, $psDb, $psCore;
		$consulta = "UPDATE u_miembros SET user_activo = :activo WHERE user_id = :uid";
		$valores = array(
			'activo' => 0,
			'uid' => $psUser->user_id,
		);
		if($psDb->db_execute($consulta, $valores)){
			$psCore->redirectTo($psCore->settigns['url'] . '/logout.php');
			return true;
		}else{
			return 'Error, no pudo desactivarse la cuenta del usuario.';
		}
	}

	/**
     * @funcionalidad validamos el email con expresiones regulares
     * @param  [type]   $email obtenemos el email
     * @return type devolvemos un valor booleano con el resultado de la validación
     */
	function validarEmail($email){
		if(preg_match('/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,4})/$', $email)){
			return true;
		}else{
			return false;
		}
	}

	/**
     * @funcionalidad obtenemos el resultado de calcular el tanto por ciento
     * @param  [type]   $valores -> obtenemos un array con los datos a sumar
     * @param  [type]   $porcentaje -> obtenemos el porcentaje
     * @return type devolvemos el valor obtenido
     */
	function getPorcentaje($valores, $porcentaje = 40){
		$total = 0;
		for($a = 0; $a < $valores.length; $a++){
			$total += $valores[$a];
		}
		return round((100 * $total) / $porcentaje);
	}

	/**
     * @funcionalidad mediante una función recursiva obtenemos el porcentaje total
     * @param  [type]   $valores -> obtenemos un array con los datos a sumar
     * @param  [type]   $total -> obtenemos el total a partir del cual restar
     * @return type devolvemos el valor obtenido
     */
	function getTotalPorcentaje($valores, $total){
		foreach($valores as $key => $valor){
			$datos = unserialize($valor);
			if(is_array($datos)){
				$total2 = $this->gePorcentajeTotal($datos, count($datos));
				if(empty($total2)){
					$total--;
				}
			}else if(empty($valor)){
				$total--;
			}
		}
		return $total;
	}

	/**
     * @funcionalidad obtenemos la información del usuario
     * @param  [type]   $uid -> obtenemos el id del usuario
     * @return type devolvemos un array con todos los datos obtenidos
     */
	function cargarInfo($uid){
		global $psDb, $psCore, $psUser;
		//obtenemos la información general
		$consulta = "SELECT u.user_id, u.user_name, u.user_registro, u.user_lastactive, u.user_activo, u.user_baneado, p.user_sexo, p.user_pais, p.p_nombre, p.p_avatar, p.p_mensaje, p.p_socials, p.p_empresa, p.p_configs FROM u_miembros AS u, u_perfil AS p WHERE u.user_id = :uid AND p.user_id = :uid2";
		$valores = array(
			'uid' => $uid,
			'uid2' => $uid,
		);
		$datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
		//comprobamos algunos campos
		$datos['p_nombre'] = $psCore->badWords($datos['p_nombre'], true);
		$datos['p_mensaje'] = $psCore->badWords($datos['p_mensaje'], true);
		$datos['p_socials'] = unserialize($datos['p_socials']);
		$datos['p_socials']['f'] = $datos['p_socials'][0];
		$datos['p_socials']['t'] = $datos['p_socials'][1];
		$datos['p_configs'] = unserialize($datos['p_configs']);

		if($datos['p_configs']['hits'] == 0){
			$datos['can_hits'] = false;
		}elseif($datos['p_configs']['hits'] == 3 && ($this->seguidores($uid) || $psUser->admod)){
			$datos['can_hits'] = true;
		}elseif($datos['p_configs']['hits'] == 4 && ($this->siguiendo($uid) || $psUser->admod)){
			$datos['can_hits'] = true;
		}elseif($datos['p_configs']['hits'] == 5 && $psUser->member){
			$datos['can_hits'] = true;
		}elseif($datos['p_configs']['hits'] == 6){
			$datos['can_hits'] = true;
		}

		if($datos['can_hits']){
			$consulta2 = "SELECT w.*, u.user_id, u.user_name FROM w_visitas AS w LEFT JOIN u_miembros AS u ON w.user = u.user_id WHERE w.for = :uid AND w.type = :one AND user > :cero ORDER BY w.date DESC";
			$valores2 = array(
				'uid' => $uid,
				'one' => 1,
				'cero' => 0
			);
			$datos['visitas'] = $psDb->db_execute($consulta2, $valores2);
			$consulta3 = "SELECT COUNT(u.user_id) AS uid FROM w_visitas AS w LEFT JOIN u_miembros AS u ON w.user = u.user_id WHERE w.for = :uid AND w.type = :one";
			$valores3 = array(
				'uid' => $uid,
				'one' => 1
			);
			$datos2 = $psDb->db_execute($consulta3, $valores3, 'fetch_num');
			$datos['visitas_totales'] = $datos2[0];
		}

		//comprobamos si el usuario está bloqueado
		$consulta4 = "SELECT * FROM u_bloqueos WHERE b_user = :uid AND b_auser = :uid2";
		$valores4 = array(
			'uid' => $psUser->user_id,
			'uid2' => $uid
		);
		$datos['block'] = $psDb->db_execute($consulta4, $valores4, 'fetch_assoc');

		//comprobamos si el usuario ha recibido visitas
		$consulta5 = "SELECT * FROM w_visitas WHERE for = :uid AND type = :uno AND :member";
		$valores5 = array(
			'uid' => $uid,
			'uno' => 1,
		);
		if($psUser->member){//si es miembro comprobamos id e ip
			$valores5['member'] = '(user = :uid2 OR ip LIKE :server)';
			$valores5['uid2'] = $uid;
			$valores5['server'] = $_SERVER['REMOTE_ADDR'];
		}else{//si no lo es solo comprobamos ip
			$valores5['member'] = 'ip LIKE :server';
			$valores5['server'] = $_SERVER['REMOTE_ADDR'];
		}
		$is_visited = $psDb->db_execute($consulta5, $valores5, 'rowCount');
		if(($psUser->member && $is_visited == 0 && $psUser->user_id != $uid) || ($psCore->settings['c_hits_guets'] == 1 && !$psUser->member && !$is_visited)){//si todo ok insertamos nuevos datos
			$consulta6 = "INSERT INTO w_visitas (user, for, type, 'date', ip) VALUES (:user, :for, :type, :dates, :ip)";
			$valores6 = array(
				'user' => $psUser->user_id,
				'for' => $uid,
				'type' => 1,
				'date' => time(),
				'ip' => $_SERVER['REMOTE_ADDR']
			);
			$psDb->db_execute($consulta6, $valores6);
		}else{//si no actualizamos los ya existentes
			$consulta6 = "UPDATE w_visitas SET 'date' = :dates, ip = :ip WHERE for = :for && type = :type";
			$valores6 = array(
				'dates' => date(),
				'ip' => $_SERVER['REMOTE_ADDR'],
				'for' => 0,
				'type' => 1
			);
			$psDb->db_execute($consulta6, $valores6);
		}

		//obtenemos las estadísticas
		$consulta7 = "SELECT u.*, r.r_name, r.r_color FROM u_miembros AS u LEFT JOIN u_rangos AS r ON u.user_rango = r.rango_id WHERE u.user_id = :uid";
		$valores7 = array('uid' => $uid);
		$datos['stats'] = $psDb->db_execute($consulta7, $valores7, 'fetch_assoc');
		if($datos['stats']['user_cache'] < time() - ($psCore->settings['c_stats_cache'] * 60)){
			//posts
			$consulta8 = "SELECT COUNT(post_id) AS p FROM p_posts WHERE post_user = :uid AND post_status = :cero";
			//seguidores
			$consulta9 = "SELECT COUNT(follow_id) AS s FROM u_follows WHERE f_id = :uid AND f_type = :uno";
			//comentarios
			$consulta10 = "SELECT COUNT(cid) AS c FROM p_comentarios WHERE c_user = :uid AND c_status = :cero";
			$valores8 = array('uid' => $uid, 'cero' => 0);
			$valores9 = array('uid' => $uid, 'uno' => 1);
			//ejecutamos las consultas
			$query8 = $psDb->db_execute($consulta8, $valores8, 'fetch_num');
			$query9 = $psDb->db_execute($consulta9, $valores9, 'fetch_num');
			$query10 = $psDb->db_execute($consulta10, $valores8, 'fetch_num');
			//guardamos los datos 
			$datos['stats']['user_posts'] = $query8[0];
			$datos['stats']['user_seguidores'] = $query9[0];
			$datos['stats']['user_comentarios'] = $query10[0];
			//una vez obtenidos los datos actualizamos en la db
			$consulta11 = "UPDATE u_miembros SET user_posts = :posts, user_comentarios = :comentarios, user_seguidores = :seguidores, user_cache = :cache WHERE user_id = :uid";
			$valores11 = array(
				'posts' => $query8[0],
				'comentarios' => $query10[0],
				'seguidores' => $query9[0],
				'cache' => time(),
				'uid' => $uid
			);
			$psDb->db_execute($consulta11, $valores11);
			//ahora obtenemos las estadísticas de las fotos
			$consulta12 = "SELECT COUNT(foto_id) AS f FROM f_fotos WHERE f_user = :uid AND f_status = :cero";
			$query12 = $psDb->db_execute($consulta12, $valores8, 'fetch_num');
			$datos['stats']['user_fotos'] = $query12[0];
		}
	}

	/**
     * @funcionalidad obtenemos la información general del usuario
     * @param  [type]   $uid -> obtenemos el id del usuario
     * @return type devolvemos un array con todos los datos obtenidos
     */
	function cargarInfoGeneral($uid){
		global $psDb, $psCore;
		//obtenemos las ultimas fotos
		if(empty($_GET['pid'])){
			$consulta = "SELECT foto_id, f_title, f_url FROM f_fotos WHERE f_user = :uid ORDER BY foto_id DESC";
			$valores = array('uid' => $uid);
			$query = $psDb->db_execute($consulta, $valores);
			$datos['fotos'] = $psDb->resultadoArray($query);
			$datos['fotos_total'] = count($datos['fotos']);
		}
		//obtenemos los usuarios que siguen al usuario (seguidores)
		$consulta2 = "SELECT f.follow_id, u.user_id, u.user_name FROM u_follows AS f LEFT JOIN u_miembros AS u ON f.f_user = u.user_id WHERE f.f_id = :fid AND f.f_type = :type AND u.user_baneado = :ban ORDER BY f.f_date DESC";
		$valores2 = array(
			'fid' => $uid,
			'type' => 1,
			'ban' => 0
		);
		$seguidores = $psDb->db_execute($consulta2, $valores2);
		$datos['segs']['data'] = $psDb->resultadoArray($seguidores);
		$datos['segs']['total'] = count($datos['segs']['data']);
		//obtenemos los usuarios a los que sigue el usuario (siguiendo)
		$consulta3 = "SELECT f.follow_id, u.user_id, u.user_name FROM u_follows AS f LEFT JOIN u_miembros AS u ON f.f_id = u.user_id WHERE f.f_user = :fid AND f.f_type = :type AND u.user_activo = :activo AND u.user_baneado = :ban ORDER BY f.f_date DESC";
		$valores3 = array(
			'fid' => $uid,
			'type' => 1,
			'activo' => 1,
			'ban' => 0
		);
		$siguiendo = $psDb->db_execute($consulta2, $valores2);
		$datos['sigd']['data'] = $psDb->resultadoArray($siguiendo);
		$datos['sigd']['total'] = count($datos['sigd']['data']);
		//por último obtenemos las medallas
		$consulta4 = "SELECT m.*, a.* FROM w_medallas AS m LEFT JOIN w_medallas_asign AS a ON a.medal_id = m.medal_id WHERE a.medal_for = :for AND m.m_type = :type ORDER BY a.medal_date DESC";
		$valores4 = array(
			'for' => $uid,
			'type' => 1
		);
		$medallas = $psDb->db_execute($consulta4, $valores4);
		$datos['medallas'] = $psDb->resultadoArray($medallas);
		$datos['m_total'] = count($datos['medallas']);

		return $datos;
	}

	/**
     * @funcionalidad obtenemos las medallas y el total de ellas que tiene el usuario
     * @param  [type] $uid obtenemos el id del usuario
     * @return type devolvemos un array con los datos obtenidos
     */
	function cargarMedallas($uid){
		global $psDb;
		$consulta = "SELECT m.*, a.* FROM w_medallas AS m LEFT JOIN w_medallas_asign AS a ON m.medal_id = a.medal_id WHERE a.medal_for = :uid AND m.m_type = :type ORDER BY a.medal_date DESC";
		$valores = array(
			'uid' => $uid,
			'type' => 1
		);
		$query = $psDb->db_execute($consulta, $valores);
		$datos['medallas'] = $psDb->resultadoArray($query);
		$datos['total'] = count($datos['medallas']);
		return $datos;
	}

	/**
     * @funcionalidad obtenemos los post del usuario y el total de ellos
     * @param  [type]  $uid obtenemos el id del usuario
     * @return type devolvemos un array con los datos obtenidos
     */
	function cargarPosts($uid){
		global $psDb, $psUser;
		$consulta = "SELECT p.post_id, p.post_title, p.post_puntos, c.c_seo, c.c_img FROM p_posts AS p LEFT JOIN p_categorias AS c ON c.cid = p.post_category WHERE p.post_status = :status AND p.post_user = :uid ORDER BY p.post_date DESC";
		$valores = array(
			'status' => 0,
			'uid' => $uid,
		);
		$query = $psDb->db_execute($consulta, $valores);
		$datos['posts'] = $psDb->resultadoArray($query);
		$datos['total'] = count($datos['posts']);
		$datos['username'] = $psUser->getUserName($uid);
		return $datos;
	}

	/**
     * @funcionalidad comprobamos si el usuario tiene seguidores
     * @param  [type]   $uid -> obtenemos el id del usuario
     * @return type devolvemos un valor booleano con el resultado de si tiene o no seguidores
     */
	function seguidores($uid){
		global $psUser, $psDb;
		$consulta = "SELECT follow_id FROM u_follows WHERE f_id = :fid AND f_user = :uid AND f_type = :type";
		$valores = array(
			'fid' => $uid,
			'f_user' => $psUser->user_id,
			'type' => 1
		);
		$datos = $psDb->db_execute($consulta, $valores, 'rowCount');
		if($datos > 0){
			return true;
		}else{
			return false;
		}
	}

	/**
     * @funcionalidad comprobamos si el usuario está siguiendo a otro usuario
     * @param  [type]   $uid -> obtenemos el id del usuario
     * @return type devolvemos un valor booleano en función de si le sigue o no
     */
	function siguiendo($uid){
		global $psUser, $psDb;
		$consulta = "SELECT follow_id FROM u_follows WHERE f_id = :fid AND f_user = :uid AND f_type = :type";
		$valores = array(
			'fid' => $psUser->user_id,
			'f_user' => $uid,
			'type' => 1
		);
		$datos = $psDb->db_execute($consulta, $valores, 'rowCount');
		if($datos > 0){
			return true;
		}else{
			return false;
		}
	}

	/**
     * @funcionalidad añadimos una nueva imagen
     * @return type devolvemos un array con los datos obtenidos
     */
	function anadirImagen(){
		global $psDb, $psUser, $psCore;
		//obtenemos la url y comprobamos su longitud
		$url = $psCore->badWords($_POST['url'], true);
		$caption = filter_input(INPUT_POST, 'caption');
		//comprobamos e insertamos los datos
		if(empty($url) || $url.length < 10){
			return array('field' => 'url', 'error' => 'Por favor, ingrese la ruta correcta de la imagen');
		}else{
			$consulta = "INSERT INTO u_fotos (f_user, f_url, f_caption) VALUES (:user, :url, :caption)";
			$valores = array(
				'user' => $psUser->user_id,
				'url' => $url,
				'caption' => $caption
			);
			$psDb->db_execute($consulta, $valores);
			return array('id' => $psDb->getLastInsertId());
		}
	}

	/**
     * @funcionalidad obtenemos las imagenes del usuario
     * @param  [type]   $uid -> obtenemos el id del usuario
     * @return type devolvemos un array con los datos obtenidos
     */
	function cargarImagenes($uid){
		global $psDb, $psUser;
		if(empty($uid)){
			$uid = $psUser->user_id;
		}
		$consulta = "SELECT * FROM u_fotos WHERE f_user = :uid";
		$valores = array('uid' => $uid);
		$query = $psDb->db_execute($consulta, $valores);
		return $psDb->resultadoArray($query);
	}

	/**
     * @funcionalidad borramos la imagen seleccionada
     * @return type devolvemos el resultado de la consulta
     */
	function borrarImagen(){
		global $psDb, $psUser, $psCore;
		$consulta = "DELETE FROM u_fotos WHERE foto_id = :fid AND f_user = :uid";
		$valores = array(
			'fid' => filter_input(INPUT_POST, 'id'),
			'uid' =< $psUser->user_id
		);
		if($psDb->db_execute($consulta, $valores)){
			return true;
		}else{
			return 'Ocurri&oacute; un error al intentar borrar la imagen.';
		}
	}

	/**
     * @funcionalidad obtenemos los datos del usuario baneado
     * @return type debolvemos un array con los datos obtenidos
     */
	function cargarBaneos(){
		global $psDb, $psUser;
		$consulta = "SELECT b.*, u.user_name FROM u_miembros AS u LEFT JOIN u_bloqueos AS b ON u.user_id = b.b_auser WHERE b.b_user = :uid";
		$valores = array('uid' => $psUser->user_id);
		$query = $psDb->db_execute($consulta, $valores);
		return $psDb->resultadoArray($query);
	}

	/**
     * @funcionalidad combiamos el estado de baneo de un usuario
     * @return type devolvemos un string el estado del baneo
     */
	function cambiarBaneos(){
		global $psDb, $psUser, $psCore;
		$user = filter_input(INPUT_POST, 'user');
		$baneado = empty($_POST['bloquear']) ? 0 : 1;
		$existe = $psUser->getUserName($user);
		//comprobamos si el usuario existe y no es el mismo usuario
		if($existe && $psUser->user_id != $user){
			if($baneado == 1){
				//comprobamos si el usuario está bloqueado
				$consulta = "SELECT bid FROM u_bloqueos WHERE b_user = :uid AND b_auser = :user";
				$valores = array(
					'uid' => $psUser->user_id,
					'user' => $user
				);
				$no_existe = $psDb->db_execute($consulta, $valores, 'rowCount');
				if(empty($no_existe)){
					$consulta2 = "INSERT INTO u_bloqueos (b_user, b_auser) VALUES (:uid, :user)";
					if($psDb->db_execute($consulta2, $valores)){
						return 'El usuario fue baneado correctamente.';
					}
				}else{
					return 'El usuario seleccioando ya ha sido baneado.';
				}
			}else{
				$consulta3 = "DELETE FROM u_bloqueos WHERE b_user = :uid AND b_auser = :user";
				if($psDb->db_execute($consulta3, $valores)){
					return 'El baneo del usuario fue eliminado correctamente.';
				}
			}
		}else{
			return 'El usuario seleccionado no existe';
		}
	}
}