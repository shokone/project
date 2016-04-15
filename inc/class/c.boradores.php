<?php
//comprobamos si hemos declarado la contante PS_HEADER
if(!defined('PS_HEADER')){
    exit("No se permite el acceso al script");
}
/**
 * clase psBorradores
 * clase destinada al control de los borradores
 *
 * @name c.borradores.php
 * @author Iván Martínez Tutor
 */
class psBorradores(){
	//primero de todo instanciamos la clase
	public static function &getInstance(){
		static $instancia;
		if(is_null($instancia)){
			$instancia = new psBorradores();
		}
		return $instancia;
	}

	/**
     * @funcionalidad obtenemos los datos del borrador seleccioando
     * @param  [type]   $status -> obtenemos el estado del borrador
     * @return [type]           devolvemos el resultado de la consulta en un array asociativo
     */
	function getBorrador($status = 1){
		global $psDb, $psUser;
		$borrador_id = intval($_GET['action']);
		$consulta = "SELECT * FROM p_borradores WHERE bid = :bid AND b_user = :b_user AND b_status = :b_status";
		$valores = array(
			'bid' => $borrador_id,
			'b_user' => $psUser->info['user_id'],
			'b_status' => $status,
		);
		return $psDb->db_execute($consulta, $valores, 'fetch_assoc');
	}

	/**
     * @funcionalidad obtenemos los borradores del usuario logueado
     * @return [type]           devolvemos un array con los datos obtenidos
     */
	function getBorradores(){
		global $psDb, $psUser;
		$consulta = "SELECT c.cid, c.c_nombre, c.c_seo, c.c_img, b.bid, b.b_user, b.b_title, b.b_date, b.b_status, b.b_causa, b.b_category FROM p_categorias AS c LEFT JOIN p_borradores AS b ON c.cid = b.b_category WHERE b.b_user = :user ORDER BY b.b_date";
		$valores = array('user' => $psUser->info['user_id']);
		$query = $psDb->db_execute($consulta, $valores);
		$borradores = $psDb->resultadoArray($query);
		//creamos un string con los datos obtenidos para mostrarlos con smarty
		$tipos = array('eliminados', 'borradores');
		foreach($borradores as $valor){
			//comprobamos la causa, si no ha sido eliminado convertimos en html el valor de b_causa
			$causa = empty($valor['b_causa']) ? 'Eliminado por el autor.' : htmlspecialchars($valor['b_causa']);
			//creamos el string con todos los datos a generar, anidando todos los borradores
			$borrador .= '{"id":' . $valor['bid'] . ',"titulo":"' . $valor['b_title'] . '","categoria":"' . $valor['c_seo'] . '","imagen":"' . $valor['c_img'] . '","fecha_guardado":' . $valor['b_date'] . ',"status":'.$valor['b_status'].',"causa":"' . $causa . '","categoria_name":"' . $valor['c_nombre'] . '","tipo":"' . $tipos[$valor['b_status']] . '","url":"' . $psCore->settings['url'] . '/agregar/' . $valor['bid'] . '","fecha_print":"' . strftime("%d\/%m\/%Y a las %H:%M:%S hs",$valor['b_date']) . '"},';
		}
		return $borrador;
	}

	/**
     * @funcionalidad modificamos o creamos un borrador
     * @param  [type]   $guardar -> comprobamos si hay que guardar el borrador o crear uno nuevo
     * @return [type]           devolvemos el resultado de la consulta 
     */
	function newBorrador($guardar = false){
		global $psCore, $psDb, $psUser;
		//creamos un array para guardar todos los datos
		$borrador = array(
			'date' => time(),
			'title' => $psCore->badWords($_POST['titulo'], true),
			'body' => $_POST['cuerpo'],
			'tags' => $psCore->badWords($_POST['tags'], true),
			'category' => $_POST['categoria'],
			'private' => empty($_POST['privado']) ? 0 : 1,
			'block_comments' => empty($_POST['sin_comentarios']) ? 0 : 1,
			'sponsored' => empty($_POST['sponsored']) ? 0 : 1,
			'sticky' => empty($_POST['sticky']) ? 0 : 1,
			'smileys' => empty($_POST['smileys']) ? 0 : 1,
			'visitantes' => empty($_POST['visitantes']) ? 0 : 1,
		);
		//comprobamos los datos y realizamos las consultas oportunas
		if(!empty($borrador['title'])){
			if(!empty($borrador['category']) && $borrador['category'] > 0){
				if($guardar){//si guardar vale true actualizamos el borrador
					$borrador_id = intval($_POST['borrador_id']);
					$actualizaciones = $psCore->getDatos($borrador, 'b_');
					$datosActualizar = '';
					foreach($actualizaciones['values'] as $key => $valor){
						$datosActualizar .= $valor;
					}
					$valores['datosActualizar'] = $datosActualizar;
					foreach($actualizaciones['values2'] as $key => $valor){
						$valores[$key] = $valor;
					}
					$valores['bid'] = $borrador_id;
					$valores['user'] = $psUser->info['user_id'];
					$consulta = "UPDATE p_borradores SET :datosActualizar WHERE bid = :bid AND b_user = :user";
					if($psDb->db_execute($consulta, $valores)){
						return $borrador_id;
					}else{
						return 'Ocurri&oacute; un error al intentar actualizar el borrador.';
					}
				}else{//si guardar vale false insertamos una nueva fila en la tabla de borradores
					$consulta2 = "INSERT INTO p_borradores (b_user, b_date, b_title, b_body, b_tags, b_category, b_private, b_block_comments, b_sponsored, b_sticky, b_smileys, b_visitantes, b_status, b_causa) VALUES (:b_user, :b_date, :b_title, :b_body, :b_tags, :b_category, :b_private, :b_block_comments, :b_sponsored, :b_sticky, :b_smileys, :b_visitantes, :b_status, :b_causa)";
					$valores2 = array(
						'b_user' => $psUser->info['user_id'],
						'b_date' => $borrador['date'],
						'b_title' => $borrador['title'],
						'b_body' => $borrador['body'],
						'b_tags' => $borrador['tags'],
						'b_category' => $borrador['category'],
						'b_private' => $borrador['private'],
						'b_block_comments' => $borrador['block_comments'],
						'b_sponsored' => $borrador['sponsored'],
						'b_sticky' => $borrador['sticky'],
						'b_smileys' => $borrador['smileys'],
						'b_visitantes' => $borrador['visitantes'],
						'b_status' => 1,
						'b_causa' => '',
					);
					if($psDb->db_execute($consulta2, $valores2)){
						return $psDb->getLastInsertId();
					}else{
						return 'Error al insertar los datos del borrador en la base de datos.';
					}
				}
			}else{
				$campo = 'categor&iacute;a';
			}
		}else{
			$campo = 't&iacute;tulo';
		}
		return 'El campo <b>'.$campo.'</b> es obligatorio para poder guardar el borrador.';
	}

	/**
     * @funcionalidad eliminamos el borrador seleccionado
     * @return [type]           devolvemos un string con el resultado de la consulta
     */
	function delBorrador(){
		global $psUser, $psDb;
		$borrador = intval($_GET['borrador_id']);
		$consulta = "DELETE FROM p_borradores WHERE bid = :bid AND b_user = :user";
		$valores = array(
			'bid' => $borrador,
			'user' => $psUser->info['user_id'],
		);
		if($psDb->db_execute($consulta, $valores)){
			return 'El borrador ha sido eliminado.';
		}else{
			return 'Lo sentimos. Ocurri&oacute; un error al intentar eliminar el borrador.';
		}
	}
}