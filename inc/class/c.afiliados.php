<?php
//comprobamos si hemos declarado la contante PS_HEADER
if(!defined('PS_HEADER')){
    exit("No se permite el acceso al script");
}
/**
 * clase psAfiliados
 * clase destinada al control de las webs afiliadas
 *
 * @name c.afiliados.php
 * @author Iván Martínez Tutor
 */
class psAfiliados{
	//primero instanciamos la clase
	public static function &getInstance(){
		static $instancia;
		if(is_null($instancia)){
			$instancia = new psAfiliados();
		}
		return $afiliados;
	}

	/**
     * @funcionalidad obtenemos los datos de un afiliado según la sección en la que nos encontremos
     * @param  [type]  $type       [description] comprobamos el tipo de sección
     * @return [type]              [description] devolvemos un array con los datos obtenidos
     */
	function getAfiliado($type = 'home'){
		global $psDb;
		if($type == 'home'){//comprobamos si estamos en la seccion home
			$consulta = "SELECT aid, a_titulo, a_url, a_banner, a_descripcion FROM w_afiliados WHERE aid = :aid";
			$valores = array('aid' => $_POST['ref']);
			$datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
		}else if($type == 'admin'){//comprobamos si estamos en la seccion admin
			$consulta = "SELECT aid, a_titulo, a_url, a_banner, a_descripcion FROM w_afiliados WHERE aid = :aid";
			$valores = array('aid' => $_POST['aid']);
			$datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
		}
		return $datos;
	}

	/**
     * @funcionalidad obtenemos los datos de los afiliados según la sección en la que nos encontremos
     * @param  [type]  $type       [description] comprobamos el tipo de sección
     * @return [type]              [description] devolvemos un array con los datos obtenidos
     */
	function getAfiliados($type = 'home'){
		global $psDb;
		echo 'hla';
		if($type == 'home'){//comprobamos si estamos en la seccion home
			$consulta = "SELECT aid, a_titulo, a_url, a_banner, a_descripcion FROM w_afiliados WHERE a_active = :active ORDER BY RAND() LIMIT :limite";
			$valores = array('active' => 1, 'limite' => 5);
			$datos = $psDb->db_execute($consulta, $valores);
		}else if($type == 'admin'){//comprobamos si estamos en la seccion admin
			$consulta = "SELECT aid, a_titulo, a_url, a_banner, a_descripcion, a_sid, a_hits_in, a_hits_out, a_date, a_active FROM w_afiliados";
			$datos = $psDb->db_execute($consulta, $valores);
		}
		return $psDb->resultadoArray($datos);
	}

	/**
     * @funcionalidad añadimos un nuevo afiliado y mandamos un mensaje al administrador y al usuario
     * @return [type]              [description] devolvemos un string con el mensaje a enviar o un string con el error 
     */
	function newAfiliado(){
		global $psCore, $psDb, $psMonitor;
		//obtenemos los datos internos del formulario
		$interno = array(
			'titulo' => htmlspecialchars($psCore->badWords($_POST['atitle'])),
			'url' => htmlspecialchars($psCore->badWords($_POST['aurl'])),
			'banner' => htmlspecialchars($psCore->badWords($_POST['aimg'])),
			'descripcion' => htmlspecialchars($psCore->badWords($_POST['atxt'])),
			'sid' => htmlspecialchars($_POST['aid'])
		);
		//comprobamos los campos
		if(!$interno['titulo'] || !$interno['url'] || $interno['url'] == 'http://' || !$interno['banner'] || $interno['banner'] == 'http://' || !$interno['descripcion']){
        	return 'Por favor, complete todos los datos para poder continuar.';
        }
        //comprobamos que la url sea correcta
        if(!filter_var($_REQUEST['url'], FILTER_VALIDATE_URL)){
        	return 'La url introducida no es correcta.';
        }
        //ahora realizamos la consulta en la base de datos y mostramos el aviso al usuario
        $consulta = "INSERT INTO w_afiliados (a_titulo, a_url, a_banner, a_descripcion, a_sid, a_date) VALUES (:titulo, :url, :banner, :descripcion, :sid, :dates)";
        $valores = array(
        	'titulo' => $interno['titulo'],
        	'url' => $interno['url'],
        	'banner' => $interno['banner'],
        	'descripcion' => $interno['descripcion'],
        	'sid' => intval($interno['sid']),
        	'dates' => time()
        );
        if($psDb->db_execute($consulta, $valores)){
        	$id = $psDb->getLastInsertId();
        	//si todo correcto mandamos el aviso al usuario admin
        	$aviso = '<div class="center">
	        		<a href="' . $interno['url'] . '" target="_blank">
	        			<img src="' . $interno['banner'] . '" title="' . $interno['titulo'] . '"/>
	        		</a>
	        	</div>
	        	<br><br>
	        	<p>' . $interno['titulo'] . ' quiere ser su afiliado, acceda a su panel de administraci&oacute;n para aceptarla o denegarla.</p>';
	        //mandamos el aviso al admin
	        $psMonitor->setAviso(1, 'Nueva afiliaci&oacute;n', $aviso, 0);
	        //ahora se lo mandamos al usuario, junto con la url con la que tiene que enlazarnos
	        $datos = array(
	        	'titulo' => $psCore->settings['titulo'],
	        	'url' => $psCore->settings['url'] . '/?ref=' . $id,
	        	'banner' => $psCore->settings['banner']
	        );
	        $mensaje = '<div class="afDatos">Tu afiliaci&oacute;n ha sido enviada!</div>
	        	<div>
	        		<p>El administrador ha sido notificado con tu afiliaci&oacute;n para que la aprueba, mientras tanto copia el siguiente c&oacute;digo y p&eacute;guelo en un lugar visible de su p&aacute;gina web. Ser&aacute; necesario para poder completar la afiliaci&oacute;n.</p>
	        		<div>
	        			<label for="aftitle">C&oacute;digo HTML</label>
	        			<textarea tabindex="5" rows="10" onclick="select(this)">
	        				<a href="' . $datos['url'] . '" target="_blank" title="' . $datos['titulo'] . '"><img src="' . $datos['banner'] . '" title="' . $datos['titulo'] . ' alt="' . $datos['titulo'] . '"/></a>
	        			</textarea>
	        		</div>
	        	</div>';
        }
        return $mensaje;
	}

	/**
     * @funcionalidad actualizamos los datos de un afilaido
     * @return [type]              [description] devolvemos un string con el resultado de la consulta
     */
	function editAfiliado(){
		global $psCore, $psDb;
		//obtenemos los datos
		$afiliado = array(
			'titulo' => $psCore->badWords($_POST['af_title']),
			'url' => $psCore->badWords($_POST['af_url']),
			'banner' => $psCore->badWords($_POST['af_banner']),
			'descripcion' => $psCore->badWords($_POST['af_desc']),
			'afiliado' => intval($_GET['aid']),
		);
		if(!$afiliado['afiliado'] || !$afiliado['titulo'] || !$afiliado['url'] || !$afiliado['banner'] || !$afiliado['descripcion']){
			return 'Por favor, complete todos los campos';
		}
		if(!filter_var($afiliado['url'], FILTER_VALIDATE_URL)){
			return 'La url introducida es incorrecta';
		}
		//una vez comprobados los campos realizamos la consulta en la base de datos
		$consulta = "UPDATE w_afiliados SET a_titulo = :titulo, a_url = :url, a_banner = :banner, a_descripcion = :descripcion WHERE aid = :afiliado";
		if($psDb->db_execute($consulta, $afiliado)){
			return 'Afiliado actualizado correctamente.';
		}else{
			return 'Ocurri&oacute; un error al intentar guardar los datos.';
		}
	}

	/**
     * @funcionalidad borramos un afiliado
     * @param  [type]  $aid        [description] id del afiliado
     * @return [type]              [description] 
     */
	function delAfiliado($aid){
		global $psUser, $psDb;
		if($psUser->admod == 1){
			$consulta = "DELETE FROM w_afiliados WHERE aid = :aid";
			$valores = array('aid' => (int)$aid,);
			if($psDb->db_execute($consulta, $valores)){
				return 'El afiliado ha sido eliminado correctamente.';
			}
		}else{
			return 'No tienes permisos para hacer eso.';
		}
	}

	/**
     * @funcionalidad cambiamos el estado del afiliado
     * @return [type]              [description] devolvemos un string con el resultado de la consulta
     */
	function setAccionAfiliado(){
		global $psUser, $psDb;
		$afiliado = intval($_POST['aid']);
		$consulta = "SELECT a_active FROM w_afiliados WHERE aid = :aid";
		$valores = array('aid' => (int)$afiliado);
		$query = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
		//comprobamos el estado del afiliado
		if($query['a_active'] == 1){
			$consulta2 = "UPDATE w_afiliados SET a_active = :valor WHERE aid = :aid";
			$valores2 = array('valor' => 0, 'aid' => (int)$afiliado);
			if($psDb->db_execute($consulta2, $valores2)){
				return 'Afiliado deshabilitado correctamente.';
			}else{
				return 'Ocurri&oacute un error al intentar deshabilitar el afiliado';
			}
		}else{
			$consulta2 = "UPDATE w_afiliados SET a_active = :valor WHERE aid = :aid";
			$valores2 = array('valor' => 1, 'aid' => (int)$afiliado);
			if($psDb->db_execute($consulta2, $valores2)){
				return 'Afiliado habilitado correctamente.';
			}else{
				return 'Ocurri&oacute un error al intentar habilitar el afiliado';
			}
		}
	}

	/**
     * @funcionalidad redireccionamos a la url externa 
     */
	function urlExterna(){
		global $psCore, $psDb;
		//realizamos la consulta
		$consulta = "SELECT a_url, a_sid FROM w_afiliados WHERE aid = :aid";
		$valores = array('aid' => intval($_GET['ref']));
		$datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
		//si la url es correcta aumentamos las visitas externas y redireccionamos
		if(isset($datos['url'])){
			$consulta2 = "UPDATE w_afiliados SET a_hits_out = :hits_out WHERE aid = :aid";
			$valores2 = array(
				'hits_out' => 'a_hits_out' + 1,
				'aid' => intval($_GET['ref']),
			);
			$psDb->db_execute($consulta2, $valores2);
			//ahora redireccionamos
			$ref = empty($datos['a_sid']) ? '/' : '/?ref='.$datos['a_sid'];
			$url = $datos['a_url'].$ref;
			$psCore->redirectTo($url);
		}else{
			$psCore->redirectTo($psCore->settings['url']);
		}
	}

	/**
     * @funcionalidad obtenemos y redireccionamos a nuestra url interna
     */
	function urlInterna(){
		global $psCore, $psDb;
		$ref = $_GET['ref'];
		if($ref > 0){
			$consulta = "UPDATE w_afiliados SET a_hits_in = :hits_in WHERE aid = :aid";
			$valores = array(
				'hits_in' => 'a_hits_in' + 1,
				'aid' => intval($_GET['ref']),
			);
			$psDb->db_execute($consulta, $valores);
		}
		$psCore->redirectTo($psCore->settings['url']);
	}
}
