<?php
//comprobamos si la constante PS_HEADER ha sido declarada, en caso contrario no se puede acceder al script
if(!defined('PS_HEADER')){
  exit('No se permite el acceso directo al script');
}

/**
 * Clase mensajes
 * destinada al control de los mensajes
 *
 * @name() c.mensajes.php
 * @author  Iván Martínez Tutor
 */
class psMensajes{
	public $mensajes = 0;//mensajes sin leer

	/**
	 * @funcionalidad instanciamos la clase mensajes
	 */
	function psMensajes(){
		global $psDb, $psUser;
		//comprobamos si el usuario es visitante, si es así no hacemos nada
		if(empty($psUser->member)){
			return false;
		}
		//obtenemos los mensajes
		$consulta = "SELECT COUNT(mp_id) AS total FROM u_mensajes WHERE mp_to = :uid AND mp_read_mon_to < :to AND mp_del_to = :del";
		$valores = array('uid' => $psUser->user_id, 'to' => 2, 'del' => 0);
		$datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
		$this->mensajes = $datos['total'];
		//obtenemos las respuestas
		$consulta2 = "SELECT COUNT(mp_id) AS total FROM u_mensajes WHERE mp_answer = :answer AND mp_from = :mfrom AND mp_read_mon_from = :read AND mp_del_from = :del";
		$valores2 = array('answer' => 1, 'mfrom' => $psUser->user_id, 'read' => 2, 'del' => 0);
		$datos2 = $psDb->db_execute($consulta2, $valores2, 'fetch_assoc');
		$this->mensajes += $datos2['total'];
	}

	/**
     * @funcionalidad obtenemos los mensajes del usuario
     * definimos los tipos de mensaje
     * type = 1 => monitor de mensajes
     * type = 2 => recibidos
     * type = 3 => enviados
     * type = 4 => respondidos
     * type = 5 => buscador
     * @param  [type] $type tipo de accion a realizar
     * @param  [type] $unread comprobamos si hay mensajes sin leer
     * @param  [type] $modo si se realiza la actualización manual o no
     * @return [type] devolvemos un array con los datos de los mensajes
     */
	function getMensajes($type = 1, $unread = false, $modo = 'normal'){
		global $psDb, $psCore, $psUser;
		//monitor de mensajes
		if($type == 1){
			$consulta = "SELECT m.mp_id, m.mp_to, m.mp_from, m.mp_read_to, m.mp_read_mon_to, m.mp_subject, m.mp_preview, m.mp_date, u.user_name FROM u_mensajes AS m LEFT JOIN u_miembros AS u ON m.mp_from = u.user_id WHERE m.mp_to = :uid AND m.mp_del_to = :del";
			$valores['uid'] = $psUser->user_id;
			$valores['del'] = 0;
			if($this->mensajes > 0 || $unread = false){
				if($modo != 'live'){
					$consulta .= ' AND m.mp_read_mon_to < :toun UNION (SELECT m.mp_id, m.mp_to, m.mp_from, m.mp_read_from, m.mp_read_mon_from, m.mp_subject, m.mp_preview, m.mp_date, u.user_name FROM u_mensajes AS m LEFT JOIN u_miembros AS u ON m.mp_from = u.user_id WHERE m.mp_to = :uid2 AND m.mp_del_from = :del2 AND m.mp_answer = :ans';
					$valores['toun'] = 2;
					$valores['uid2'] = $psUser->user_id;
					$valores['del2'] = 0;
					$valores['ans'] = 1;
					$consulta .= ' AND m.mp_read_mon_from < :fromun)';
					$valores['fromun'] = 2;
				}else{
					$consulta .= 'AND m.mp_read_mon_to = :toun UNION (SELECT m.mp_id, m.mp_to, m.mp_from, m.mp_read_from, m.mp_read_mon_from, m.mp_subject, m.mp_preview, m.mp_date, u.user_name FROM u_mensajes AS m LEFT JOIN u_miembros AS u ON m.mp_from = u.user_id WHERE m.mp_to = :uid2 AND m.mp_del_from = :del2 AND m.mp_answer = :ans';
					$valores['toun'] = 0;
					$valores['uid2'] = $psUser->user_id;
					$valores['del2'] = 0;
					$valores['ans'] = 1;
					$consulta .= ' AND m.mp_read_mon_from = :fromun)';
					$valores['fromun'] = 0;
				}
			}
			$consulta .= " ORDER BY mp_id DESC";
			//realizamos la consulta
			$datos['total'] = 0;
			$rows = $psDb->resultadoArray($psDb->db_execute($consulta, $valores));
			foreach($rows as $row){
				$row['mp_from'] = ($row['mp_from'] == $psUser->user_id) ? $row['mp_to'] : $row['mp_from'];
		        $datos['data'][$row['mp_date']] = $row;
		        //actualizamos los datos en la db
		        $consulta2 = "UPDATE u_mensajes SET ";
		        if($psUser->user_id == $row['mp_to']){
		        	if($modo == 'live'){
		        		$consulta2 .= ' mp_read_mon_to = :update';
		        		$updates = 1;
		        	}else{
		        		$consulta2 .= ' mp_read_mon_to = :update';
		        		$updates = 2;
		        	}
		        }else{
		        	if($modo == 'live'){
		        		$consulta2 .= ' mp_read_mon_from = :update';
		        		$updates = 1;
		        	}else{
		        		$consulta2 .= ' mp_read_mon_from = :update';
		        		$updates = 2;
		        	}
		        }
		        $consulta2 .= " WHERE mp_id = :mid";
		        $valores2 = array('update' => $updates,'mid' => $row['mp_id']);
				$psDb->db_execute($consulta2, $valores2);
		        $datos['total']++;
			}
		}elseif($type == 2){//mensajes y respuestas recibidas
			//mostramos los mensajes no leídos
			$consulta = "SELECT m.mp_id, m.mp_to, m.mp_from, m.mp_read_to, m.mp_subject, m.mp_preview, m.mp_date, u.user_name FROM u_mensajes AS m LEFT JOIN u_miembros AS u ON m.mp_from = u.user_id WHERE m.mp_to = :uid AND m.mp_del_to = :del ";
			$valores['uid'] = $psUser->user_id;
			$valores['del'] = 0;
			if($unread == true){
				$consulta .= ' AND m.mp_read_to = :toun';
				$toun = 0;
				$valores['toun'] = $toun;
			}
			$consulta .= " UNION (SELECT m.mp_id, m.mp_to, m.mp_from, m.mp_read_from, m.mp_subject, m.mp_preview, m.mp_date, u.user_name FROM u_mensajes AS m LEFT JOIN u_miembros AS u ON m.mp_from = u.user_id WHERE m.mp_to = :uid2 AND m.mp_del_from = :del2 AND m.mp_answer = :ans ";
			$valores['uid2'] = $psUser->user_id;
			$valores['del2'] = 0;
			$valores['ans'] = 1;
			//consulta
			if($unread == true){
				$consulta .= ' AND m.mp_read_from = :fromun';
				$fromun = 0;
				$valores['fromun'] = $fromun;
			}
			$consulta .= ") ORDER BY mp_id DESC";
			$total = $psDb->db_execute($consulta, $valores, 'rowCount');
			//obtenemos la paginación
			$pages = $psCore->getPagination($total, 15);
			$datos['pages'] = $pages;
			$consulta .= ' LIMIT :limite, :limite2';
			$aux = explode(', ', $pages['limit']);
			$valores['limite'] = (int)$aux[0];
			$valores['limite2'] = (int)$aux[1];
			$rows = $psDb->resultadoArray($psDb->db_execute($consulta, $valores));
			foreach($rows as $row){
				$row['mp_type'] = ($row['mp_from'] != $psUser->user_id) ? 1 : 2;
                $row['mp_from'] = ($row['mp_from'] == $psUser->user_id) ? $row['mp_to'] : $row['mp_from'];
                $datos['data'][$row['mp_date']] = $row;
			}
		}else if($type == 3){//mensajes enviados
			$consulta = "SELECT m.mp_id, m.mp_to, m.mp_read_to, m.mp_subject, m.mp_preview, m.mp_date, u.user_name FROM u_mensajes AS m LEFT JOIN u_miembros AS u ON m.mp_to = u.user_id WHERE m.mp_from = :mpf ORDER BY m.mp_id DESC";
			$valores = array('mpf' => $psUser->user_id);
			//obtenemos la paginación
			$total = $psDb->db_execute($consulta, $valores, 'rowCount');
			$pages = $psCore->getPagination($total, 15);
			$datos['pages'] = $total;
			$consulta .= ' LIMIT :limite, :limite2';
			$aux = explode(', ', $pages['limit']);
			$valores['limite'] = $aux[0];
			$valores['limite2'] = $aux[1];
			$rows = $psDb->resultadoArray($psDb->db_execute($consulta, $valores));
			foreach($rows as $row){
				$row['mp_type'] = 2;
                $row['mp_from'] = $row['mp_to'];
                $row['mp_read_to'] = 1;
                $datos['data'][$row['mp_date']] = $row;
			}
		}else if($type == 4){//respuestas enviadas
			$consulta = "SELECT m.mp_id, m.mp_from, m.mp_read_from, m.mp_subject, m.mp_preview, m.mp_date, u.user_name FROM u_mensajes AS m LEFT JOIN u_miembros AS u ON m.mp_to = u.user_id WHERE m.mp_to = :mpt AND m.mp_answer = :ans ORDER BY m.mp_id DESC";
			$valores = array('mpt' => $psUser->user_id, 'ans' => 1);
			//obtenemos la paginación
			$total = $psDb->db_execute($consulta, $valores, 'rowCount');
			$pages = $psCore->getPagination($total, 15);
			$datos['pages'] = $total;
			$consulta .= ' LIMIT :limite, :limite2';
			$aux = explode(', ', $pages['limit']);
			$valores['limite'] = $aux[0];
			$valores['limite2'] = $aux[1];
			$rows = $psDb->resultadoArray($psDb->db_execute($consulta, $valores));
			foreach($rows as $row){
				$row['mp_type'] = 1;
                $row['mp_read_to'] = 1;
                $datos['data'][$row['mp_date']] = $row;
			}
		}else if($type == 5){//buscador de mensajes
			$consulta = "SELECT m.mp_id, m.mp_to, m.mp_from, m.mp_read_to, m.mp_subject, m.mp_preview, m.mp_date, u.user_name FROM u_mensajes AS m LEFT JOIN u_miembros AS u ON m.mp_from = u.user_id WHERE m.mp_to = :uid AND m.mp_del_to = :mpto AND mp_subject LIKE :qm UNION (SELECT m.mp_id, m.mp_to, m.mp_from, m.mp_read_from, m.mp_subject, m.mp_preview, m.mp_date, u.user_name FROM u_mensajes AS m LEFT JOIN u_miembros AS u ON m.mp_to = u.user_id WHERE m.mp_from = :uid2 AND m.mp_del_from = :mpfr AND m.mp_answer = :ans) ORDER BY mp_id DESC";
			$valores = array(
				'uid' => $psUser->user_id,
				'mpto' => 0,
				'qm' => '%'.filter_input(INPUT_GET, 'qm').'%',
				'uid2' => $psUser->user_id,
				'mpfr' => 0,
				'ans' => 1
			);
			//obtenemos la paginación
			$total = $psDb->db_execute($consulta, $valores, 'rowCount');
			$pages = $psCore->getPagination($total, 15);
			$datos['pages'] = $total;
			$consulta .= ' LIMIT :limite, :limite2';
			$aux = explode(', ', $pages['limit']);
			$valores['limite'] = $aux[0];
			$valores['limite2'] = $aux[1];
			$rows = $psDb->resultadoArray($psDb->db_execute($consulta, $valores));
			foreach($rows as $row){
				$row['mp_type'] = ($row['mp_from'] != $psUser->user_id) ? 1 : 2;
                $row['mp_from'] = ($row['mp_from'] == $psUser->user_id) ? $row['mp_to'] : $row['mp_from'];
                $datos['data'][$row['mp_date']] = $row;
			}
			$datos['texto'] = filter_input(INPUT_GET, 'qm');
		}
		//ordenamos el array obtenido
		krsort($datos['data']);
		return $datos;
	}

	/**
     * @funcionalidad enviamos un nuevo mensaje
     * @return [type] devolvemos un string con el resultado obtenido
     */
	function nuevoMensaje(){
		global $psDb, $psUser, $psCore, $psCuenta;
		if($psUser->member && $psUser->info['user_baneado'] == 0 && $psUser->info['user_activo'] == 1){
			//antiflood
			$antiflood = $psUser->permisos['goaf'] * 5;
			$mensaje = substr(filter_input(INPUT_POST, 'mensaje'), 0, 100);
			//realizamos la consulta
			$consulta = "SELECT mp_id FROM u_mensajes WHERE (mp_date > :dates AND mp_from = :mf) OR (mp_date > :dates2 AND mp_from = :mf2 AND mp_preview = :pre AND mp_subject = :subject) ORDER BY mp_id DESC";
			$valores = array(
				'dates' => time() - $antiflood,
				'mf' => $psUser->user_id,
				'dates2' => time() - $antiflood * 3600,
				'mf2' => $psUser->user_id,
				'pre' => $mensaje,
				'subject' => filter_input(INPUT_POST, 'asunto')
			);
			if($psDb->db_execute($consulta, $valores, 'rowCount')){
				return '0: Ha intentado realizar demasiadas acciones en poco tiempo. Por favor int&eacute;ntelo de nuevo en '.$antiflood.' segundos.';
			}
			$psCore->antiFlood(true, 'mps');
			//obtenemos los datos del mensaje
			$from = $_POST['para'];
			$subject = empty($_POST['asunto']) ? 'sin asunto' : $psCore->badWords($_POST['asunto']);
			$body = substr($_POST['mensaje'], 0, 1000);
			//comprobamos si el mensaje está vacío
			if(str_replace(array("\n","\t",' '), '', $body) == ''){
				return 'Debes ingresar el contenido de tu mensaje para poder enviarlo.';
			}
			//obtenemos el id del usuario
			$uid = $psUser->getUid($from);
			//comprobamos el usuario
			if(!empty($uid)){
				//primero comprobamos si el usuario al que mandamos el mensaje está bloqueado
				if(!$psUser->admod){
					$consulta2 = "SELECT bid FROM u_bloqueos WHERE (b_user = :user AND b_auser = :auser) OR (b_user = :user2 AND b_auser = :auser2";
					$valores2 = array(
						'user' => $uid,
						'auser' => $psUser->user_id,
						'user2' => $psUser->user_id,
						'auser2' => $uid
					);
					if($psDb->db_execute($consulta2, $valores2, 'rowCount')){
						return '0: El usuario '.$from.' est&aacute; bloqueado. No puedes enviar mensajes a un usuario bloqueado.';
					}
				}
				//obtenemos la vista previa
				$preview = substr($body, 0, 100);
				//realizamos la consulta en la db
				$consulta2 = "SELECT user_activo, user_baneado FROM u_miembros WHERE LOWER(user_name) = :user";
				$valores2 = array('user' => $from);
				$query = $psDb->db_execute($consulta2, $valores2, 'fetch_assoc');
				if($query['user_activo'] != 0 && $query['user_baneado'] != 1){
					//obtenemos los seguidos
					$consulta3 = "SELECT COUNT(follow_id) AS lesigo FROM u_follows WHERE f_id = :id AND f_user = :user AND f_type = :type";
					$valores3 = array(
						'id' => $uid,
						'user' => $psUser->user_id,
						'type' => 1
					);
					$siguiendo = $psDb->db_execute($consulta3, $valores3, 'fetch_assoc');
					//obtenemos los seguidores
					$consulta4 = "SELECT COUNT(follow_id) AS mesigue FROM u_follows WHERE f_id = :id AND f_user = :user AND f_type = :type";
					$valores3 = array(
						'id' => $psUser->user_id,
						'user' => $uid,
						'type' => 1
					);
					$seguidores = $psDb->db_execute($consulta4, $valores4, 'fetch_assoc');
					//comprobamos si es un admin
					$consulta5 = "SELECT COUNT(user_id) AS noesadmin FROM u_miembros WHERE user_id = :id AND user_rango < :rango";
					$valores5 = array('id' => $uid, 'rango' => 2);
					$noesadmin = $psDb->db_execute($consulta5, $valores5, 'fetch_assoc');
					//realizamos la compŕobación, si el usuario es admin nos la saltamos
					if(!$noesadmin['noesadmin']){
						//hacemos las comprobaciones de privacidad
						$consulta6 = "SELECT p_configs FROM u_perfil WHERE user_id = :uid";
						$valores6 = array('uid' => $uid);
						$datos = $psDb->db_execute($consulta6, $valores6, 'fetch_assoc');
						$datos['p_configs'] = unserialize($datos['p_configs']);
						//comprobamos según el caso
						switch($datos['p_configs']['rmp']){
							case 0: //el usuario no permite recibir mensajes
							case 8: //el usuario no puede utilizar los mensajes privados
								if($datos['p_configs']['rmp'] == 0 && !$psUser->admod){
									return '0: Lo sentimos pero '.$from.' no permite recibir mensajes.';
								}else if($datos['p_configs']['rmp'] == 8 && !$psUser->admod){
									return '0: Lo sentimos pero '.$from.' no puede utilizar los mensajes privados en estos momentos.';
								}
								break;
							case 1: //ambos usuarios debemos seguirnos para enviar el mensaje
							case 2: //al menos uno de los dos debe seguir al otro para enviar el mensaje
							case 3: //debes seguir al usuario para enviarle un mensaje
							case 4://debe seguirte el usuario para poder enviarle un mensaje
								//le sigo o me sigue
								if($seguidores['mesigue'] == 0 && $siguiendo['lesigo'] == 0){
									$lom = false;
								}else{
									$lom = true;
								}
								//le sigo y me sigue
                        		if($seguidores['mesigue'] == 1 && $siguiendo['lesigo'] == 1){
                        			$lym = true;
                        		}else{
                        			$lym = false;
                        		}
                        		if($datos['p_configs']['rmp'] == 1 && !$lym && !$psUser->admod) {
                        		  	return '0: Debes seguir a '.$para.' y &eacute;l debe seguirte para poder enviarle un mensaje.';
                        		}else if($datos['p_configs']['rmp'] == 2 && !$lom && !$psUser->admod) {
                        		  	return '0: Debes seguir a '.$para.' o &eacute;l debe seguirte para poder enviarle un mensaje.';
                        		}else if($datos['p_configs']['rmp'] == 3 && !$siguiendo['lesigo'] && !$psUser->admod) {
                        		  	return '0: Debes seguir a '.$para.' para poder enviarle un mensaje.';
                        		}else if($datos['p_configs']['rmp'] == 4 && !$seguidores['mesigue'] && !$psUser->admod){
                        		  	return '0: '.$para.' debe seguirte para que puedas enviarle un mensaje';
                        		}
								break;
						}
					}
					//insertamos los datos en la base de datos
					$consulta7 = "INSERT INTO u_mensajes (mp_to, mp_from, mp_subject, mp_preview, mp_date) VALUES (:to, :fro, :sub, :pre, :dat)";
					$valores7 = array(
						'to' => $uid,
						'fro' => $psUser->user_id,
						'sub' => $subject,
						'pre' => $preview,
						'dat' => time()
					);
					if($psDb->db_execute($consulta7, $valores7)){
						//obtenemos el id del mensaje
						$mid = $psDb->getLastInsertId();
						//hacemos comprobaciones
						if(empty($mid)){
							return '0: Ocurri&oacute; un error al enviar el mensaje. Por favor int&eacute;ntelo de nuevo m&aacute;s tarde.';
						}
						/*if(!filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_URL) || !filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_URL)){
							return '0: Ocurri&oacute; un error al intentar validar su ip.';
						}*/
						//si todo ok realizamos la consulta
						$consulta8 = "INSERT INTO u_respuestas (mp_id, mr_from, mr_body, mr_ip, mr_date) VALUES (:id, :fro, :body, :ip, :dat)";
						$valores8 = array(
							'id' => $mid,
							'fro' => $psUser->user_id,
							'body' => $body,
							'ip' => $_SERVER['REMOTE_ADDR'],
							'dat' => time()
						);
						if($psDb->db_execute($consulta8, $valores8)){
							return 'El mensaje a sido enviado con &eacute;xisto a <a href="'.$psCore->settings['url'].'/perfil/'.$from.'">'.$from.'</a>.<br><br><a href="'.$psCore->settings['url'].'/mensajes/leer/'.$mid.'">Ver el mensaje</a>';
						}else{
							return '0: Ocurri&oacute; un error al guardar los datos del mensaje.';
						}
					}else{
						return '0: Ocurri&oacute; un error al guardar el mensaje. Por favor int&eacute;ntelo de nuevo m&aacute;s tarde.';
					}
				}else{
					return '0: El usuario '.$from.' est&aacute; bloqueado y no puede recibir mensajes.';
				}
			}else{
				return '0: El usuario al que intentas enviar el mensaje no existe.';
			}
		}else{
			return '0: Debe tener una cuenta activa para poder enviar un nuevo mensaje.';
		}
	}

	/**
     * @funcionalidad enviamos una nueva respuesta
     * @return [type] devolvemos un array con los datos de la respuesta
     */
	function nuevaRespuesta(){
		global $psDb, $psCore, $psUser;
		$mid = filter_input(INPUT_POST, 'id');
		$mbody = substr(filter_input(INPUT_POST, 'body'), 0, 1000);//obtenemos el cuerpo del mensaje (le damos un máximo de 1000 caracteres)
		//comprobamos que el cuerpo no este vacío
		if(str_replace(array('\n', '\t', ' '), '', $mbody) == ''){
			return '0: Debes ingresar una respuesta para enviar el mensaje';
		}
		//realizamos la consulta
		$consulta = "SELECT mp_to, mp_from, mp_answer FROM u_mensajes WHERE mp_id = :mid";
		$valores['mid'] = $mid;
		if(!$psUser->admod){
			$consulta .= ' AND mp_del_to = :to AND mp_del_from = :dfrom';
			$valores['to'] = 0;
			$valores['dfrom'] = 0;
		}
		$respuesta = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
		//comprobamos si la respuesta está vacía o no
		if(!empty($respuesta)){
			//antiflood
			$psCore->antiFlood(true, 'mps');
			//comprobamos si está bloqueado
			if(!$psUser->admod){
				$consulta2 = "SELECT bid FROM u_bloqueos WHERE (b_user = :user AND b_auser = :auser) OR (b_user = :user2 AND b_auser = :auser2)";
				$valores2 = array(
					'user' => $respuesta['mp_to'],
					'auser' => $respuesta['mp_from'],
					'user2' => $respuesta['mp_from'],
					'auser2' => $respuesta['mp_to']
				);
				$blockeo = $psDb->db_execute($consulta2, $valores2, 'rowCount');
				if($bloqueo > 0 || ($psUser->user_id != $respuesta['mp_from'] && $psUser->user_id != $respuesta['mp_to'])){
					return '0: Lo sentimos, est&aacute;s bloqueado y no puedes responder este mensaje.';
				}
			}
			//obtenemos la vista previa del mensaje
			$preview = substr($mbody, 0, 100);//obtenemos la vista previa (sólo 100 caracteres)
			$consulta3 = "INSERT INTO u_respuestas (mp_id, mr_from, mr_body, mr_ip, mr_date) VALUES (:id, :mfrom, :body, :ip, :dates)";
			$valores3 = array(
				'id' => $mid,
				'mfrom' => $psUser->user_id,
				'body' => $mbody,
				'ip' => $_SERVER['REMOTE_ADDR'],
				'dates' => time()
			);
			if($psDb->db_execute($consulta3, $valores3)){
				//obtenemos los datos
				$valores4['preview'] = $preview;
				$valores4['dates'] = time();
				$consulta4 = "UPDATE u_mensajes SET mp_preview = :preview, mp_date = :dates";
				if($respuesta['mp_from'] != $psUser->user_id){
					$consulta4 .= ', mp_answer = :ans, mp_read_to = :to, mp_read_mon_to = :to2 , mp_read_from = :mpf, mp_read_mon_from = :mpf2, mp_del_from = :del';
                    $valores4['ans'] = 1;
                    $valores4['to'] = 1;
                    $valores4['to2'] = 2;
                    $valores4['mpf'] = 0;
                    $valores4['mpf2'] = 0;
                    $valores4['del'] = 0;
				}else{
                    $consulta4 .= ', mp_read_to = :to, mp_read_mon_to = :to2 , mp_read_from = :mpf, mp_read_mon_from = :mpf2, mp_del_to = :del';
                    $valores4['to'] = 0;
                    $valores4['to2'] = 0;
                    $valores4['mpf'] = 1;
                    $valores4['mpf2'] = 2;
                    $valores4['del'] = 0;
				}
				$valores4['mid'] = $mid;
				//actualizamos el mensaje
				$consulta4 .= " WHERE mp_id = :mid";
				$psDb->db_execute($consulta4, $valores4);
				$datos['mp_date'] = time();
				$datos['mp_ip'] = $_SERVER['REMOTE_ADDR'];
				$datos['mp_body'] = $psCore->badWords($mbody, true);
				return $datos;
			}
		}else{
			return '0: El mensaje no existe.';
		}
	}

	/**
     * @funcionalidad leemos el mensaje
     * @return [type] devolvemos un array con el historial de mensajes
     */
	function leerMensaje(){
		global $psDb, $psCore, $psUser;
		$mid = filter_input(INPUT_GET, 'id');
		//realizamos la consulta
		$consulta = "SELECT m.*, u.user_name FROM u_mensajes AS m LEFT JOIN u_miembros AS u ON m.mp_from = u.user_id WHERE m.mp_id = :mid";
		$valores['mid'] = $mid;
		if(!$psUser->admod){
			$consulta .= ' AND ((m.mp_to = :uid AND m.mp_del_to = :del) OR (m.mp_from = :uid2 AND m.mp_del_from = :del2))';
			$valores['uid'] = $psUser->user_id;
			$valores['del'] = 0;
			$valores['uid2'] = $psUser->user_id;
			$valores['del2'] = 0;
		}
		//si existe obtenemos los mensajes en un array
		$query = $psDb->db_execute($consulta, $valores, 'rowCount');
		if(!empty($query)){
			$datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
		}else{//si no redireccionamos
			$psCore->redirectTo($psCore->settings['url'].'/mensajes/');
		}
		//un usuario no puede leer los mensajes de otros usuarios
		//un administrador puede leer todos los mensajes de todos los usuarios
		$consulta2 = "SELECT obj_id FROM w_denuncias WHERE obj_id = :mid AND d_type = :type";
		$valores2 = array('mid' => $mid, 'type' => 2);
		if($psDb->db_execute($consulta2, $valores2, 'rowCount') && $psUser->admod){
			$view = true;
		}else{
			$view = false;
		}
		//si no tiene permiso para visualizar el mensaje lo redirigimos
		if($datos['mp_to'] != $psUser->user_id && $datos['mp_from'] != $psUser->user_id && !$view && $psUser->admod != 1){
			$psCore->redirectTo($psCore->settings['url'].'/mensajes/');
		}
		//obtenemos el mensaje
		$historial['msg'] = $datos;
		//y las respuestas
		$consulta3 = "SELECT r.*, u.user_name FROM u_respuestas AS r LEFT JOIN u_miembros AS u ON r.mr_from = u.user_id WHERE r.mp_id = :mid ORDER BY r.mr_id";
		$valores3 = array('mid' => $mid);
		$rows = $psDb->resultadoArray($psDb->db_execute($consulta3, $valores3));
		foreach($rows as $row){
			$row['mr_body'] = $psCore->badWords($row['mr_body'], true);
			$historial['res'][] = $row;
		}
		//comprobamos
		$respuestas = count($historial['res']);
		//obtenemos el último usuario en responder
		$from = $historial['res'][$respuesas-1]['mr_from'];
		//yo lo mando
		if($psUser->user_id == $datos['mp_to']) {
            $updates = ' mp_read_to = :m1, mp_read_mon_to = :m2';
            $valores5['m1'] = 1;
            $valores5['m2'] = 2;
            $historial['msg']['type'] = 1;
        }//el mensaje es para mi
        elseif($from == $datos['mp_to'] && $datos['mp_from'] == $psUser->user_id) {
            $updates = ' mp_read_from = :m1, mp_read_mon_from = :m2';
            $valores5['m1'] = 1;
            $valores5['m2'] = 2;
            $historial['msg']['type'] = 2;
        }//respondieron a mi mensaje
        elseif($from == $datos['mp_from']) {
            $updates = ' mp_read_from = :m1, mp_read_mon_from = :m2';
            $valores5['m1'] = 1;
            $valores5['m2'] = 2;
            $historial['msg']['type'] = 2;
        }
        //mensaje leido
        $consulta5 = "UPDATE u_mensajes SET ".$updates." WHERE mp_id = :mid";
        $valores5['mid'] = $mid;
        if(isset($updates)){
	        $psDb->db_execute($consulta5, $valores5);
	    }
        //comprobamos si puedo responder o estoy bloqueado
        $uid = ($datos['mp_from'] != $psUser->user_id) ? $datos['mp_from'] : $datos['mp_to'];
        $consulta6 = "SELECT bid FROM u_bloqueos WHERE b_user = :buser AND b_auser = :bauser";
        $valores6 = array('buser' => $datos['mp_to'], 'bauser' => $datos['mp_from']);
        $consulta7 = "SELECT bid FROM u_bloqueos WHERE b_user = :buser AND b_auser = :bauser";
        $valores7 = array('buser' => $datos['mp_from'], 'bauser' => $datos['mp_to']);
        if(!$psUser->admod && ($psDb->db_execute($consulta6, $valores6, 'rowCount') || $psDb->db_execute($consulta7, $valores7, 'rowCount'))){
        	$historial['ext']['can_read'] = 0;
        }else{
        	$historial['ext']['can_read'] = 1;
        }
        $historial['ext']['uid'] = $uid;
        $historial['ext']['user'] = $psUser->getUserName($uid);
        return $historial;
	}

	/**
     * @funcionalidad editamos el mensaje
     */
	function editarMensaje(){
		global $psDb, $psUser, $psCore;
		$ids = explode(',', filter_input(INPUT_POST, 'ids'));
		foreach($ids as $valor){
			$id = explode(':', $valor);
			$nids[$id[1]][] = $id[0];
		}
		//si estan vacios los ids acabamos ya
		if(empty($nids)){
			return false;
		}
		$action = filter_input(INPUT_POST, 'act');
		switch($action){
			case 'read':
				//actualizamos los datos en la db
				$consulta = "UPDATE u_mensajes SET mp_read_to = :read, mp_read_mon_to = :mon WHERE mp_id IN(:id) AND mp_to = :to";
				$valores = array(
					'read' => 1,
					'mon' => 2,
					'id' => implode(',', $nids[1]),
					'to' => $psUser->user_id
				);
				$psDb->db_execute($consulta, $valores);
				$consulta2 = "UPDATE u_mensajes SET mp_read_from = :read, mp_read_mon_from = :mon WHERE mp_id IN(:id) AND mp_from = :mfrom";
				$valores2 = array(
					'read' => 1,
					'mon' => 2,
					'id' => implode(',', $nids[2]),
					'mfrom' => $psUser->user_id
				);
				$psDb->db_execute($consulta2, $valores2);
				break;
			case 'unread':
				//actualizamos los datos en la db
				$consulta = "UPDATE u_mensajes SET mp_read_to = :read, mp_read_mon_to = :mon WHERE mp_id IN(:id) AND mp_to = :to";
				$valores = array(
					'read' => 0,
					'mon' => 1,
					'id' => implode(',', $nids[1]),
					'to' => $psUser->user_id
				);
				$psDb->db_execute($consulta, $valores);
				$consulta2 = "UPDATE u_mensajes SET mp_read_from = :read, mp_read_mon_from = :mon WHERE mp_id IN(:id) AND mp_from = :mfrom";
				$valores2 = array(
					'read' => 0,
					'mon' => 1,
					'id' => implode(',', $nids[2]),
					'mfrom' => $psUser->user_id
				);
				$psDb->db_execute($consulta2, $valores2);
				break;
			case 'delete':
				$consulta = "UPDATE u_mensajes SET mp_del_to = :del WHERE mp_id IN(:id) AND mp_to = :to";
				$valores = array(
					'del' => 1,
					'id' => implode(',', $nids[1]),
					'to' => $psUser->user_id
				);
				$psDb->db_execute($consulta, $valores);
				$consulta2 = "UPDATE u_mensajes SET mp_del_from = :del WHERE mp_id IN(:id) AND mp_from = :mfrom";
				$valores2 = array(
					'del' => 1,
					'id' => implode(',', $nids[2]),
					'mfrom' => $psUser->user_id
				);
				$psDb->db_execute($consulta2, $valores2);
				//si los dos lo han decidido lo eliminamos por completo
				$consulta3 = "SELECT mp_id FROM u_mensajes WHERE mp_del_to = :del AND mp_del_from = :del2 AND (mp_to = :uid OR mp_from = :uid2)";
				$valores3 = array(
					'del' => 1,
					'del2' => 1,
					'uid' => $psUser->user_id,
					'uid2' => $psUser->user_id
				);
				while($row = $psDb->db_execute($consulta3, $valores3, 'fetch_assoc')){
					$consulta4 = "DELETE FROM u_mensajes WHERE mp_id = :id";
					$valores4 = array('id' => $row['mp_id']);
					//borramos en la tabla mensajes
					if($psDb->db_execute($consulta4, $valores4)){
						//si todo ok borramos también en la tabla respuestas
						$consulta5 = "DELETE FROM u_respuestas WHERE mp_id = :id";
						$psDb->db_execute($consulta5, $valores4);
					}
				}
				break;
		}
	}

	/**
     * @funcionalidad comprobamos si elusuario ingresado es válido para enviar mensajes
     * @return [type] devolvemos un código con el resultado
     */
	function getValidUser(){
		global $psDb, $psCore, $psUser;
		$to = strtolower($_POST['para']);
		if($to == strtolower($psUser->nick)){
			return '1';
		}
		$consulta = "SELECT user_id FROM u_miembros WHERE LOWER(user_name) = :name";
		$valores = array('name' => $to);
		$datos = $psDb->db_execute($consulta, $valores, 'rowCount');
		if(empty($datos)){
			return '2';
		}else{
			return '0';
		}
	}
}
