<?php
//comprobamos si la constante PS_HEADER ha sido declarada, en caso contrario no se puede acceder al script
if(!defined('PS_HEADER')){
  exit('No se permite el acceso directo al script');
}

/**
 * Clase fotos
 * destinada al control de las fotos en el script
 *
 * @name() c.fotos.php
 * @author  Iván Martínez Tutor
 */
class psFotos{
  /**
   * @funcionalidad comprobamos si la clase ha sido instanciada
   * si no es así creamos un nuevo objeto para la clase psFotos
   * @return [type] [description]
   */
  public static function &getInstance(){
    static $instance;
    if(is_null($instance)){
      $instance = new psFotos();
    }
    return $instance;
  }

  /**
   * @funcionalidad obtenemos todos los datos de una foto
   * @return [type] devolvemos un array con todos los datos obtenidos
   */
  function getFoto(){
    global $psDb, $psCore, $psUser;
    //obtenemos el id de la foto
    $fid = filter_input(INPUT_GET, 'fid');

    //obtenemos los datos de la foto
    if($psUser->admod || $psUser->permisos['moacp']){
      $consulta = "SELECT f.*, u.user_name, u.user_activo, p.user_pais, p.user_sexo, p.user_rango, r.r_name, r.r_color, r.r_image FROM f_fotos AS f LEFT JOIN u_miembros AS u ON u.user_id = f.f_user LEFT JOIN u_perfil AS p ON p.user_id = u.user_id LEFT JOIN u_rangos AS r ON u.user_rango = r.rango_id WHERE f.foto_id = :fid";
      $valores = array('fid' => $fid);
    }else{
      $consulta = "SELECT f.*, u.user_name, u.user_activo, p.user_pais, p.user_sexo, p.user_rango, r.r_name, r.r_color, r.r_image FROM f_fotos AS f LEFT JOIN u_miembros AS u ON u.user_id = f.f_user LEFT JOIN u_perfil AS p ON p.user_id = u.user_id LEFT JOIN u_rangos AS r ON u.user_rango = r.rango_id WHERE f.foto_id = :fid AND f.f_status = :status AND u.user_activo = :activo";
      $valores = array('fid' => $fid, 'status' => 0, 'activo' => 1);
    }
    $datos['foto'] = $psDb->db_execute($consulta, $valores, 'fetch_assoc');

    //obtenemos el total de comentarios
    $consulta2 = "SELECT COUNT(cid) FROM f_comentarios WHERE c_user = :user";
    $valores2 = array('user' => $datos['foto']['f_user']);
    $query2 = $psDb->db_execute($consulta2, $valores2, 'fetch_num');

    //obtenemos el total de fotos del usuario
    $consulta3 = "SELECT COUNT(foto_id) FROM f_fotos WHERE f_user = :user AND f_status = :status";
    $valores3 = array('user' => $datos['foto']['f_user'], 'status' => 0);
    $query3 = $psDb->db_execute($consulta3, $valores3, 'fetch_num');

    //guardamos los datos
    $datos['foto']['user_foto_comments'] = $query2[0];
    $datos['foto']['user_fotos'] = $query3[0];
    $datos['foto']['exist'] = $psDb->db_execute($consulta, $valores, 'rowCount');
    $datos['foto']['f_description'] = $datos['foto']['f_description'];

    //obtenemos los datos del país
    include '../extra/datos.php';
    $datos['foto']['user_pais'] = array($datos['foto']['user_pais'], $psPaises[$datos['foto']['user_pais']]);

    //obtenemos los usuarios a los que sigo
    $consulta4 = "SELECT follow_id FROM u_follows WHERE f_user = :user AND f_id = :fid AND f_type = :type";
    $valores4 = array('user' => $psUser->user_id, 'fid' => $datos['foto']['f_user'], 'type' => 1);
    $datos['foto']['follow'] = $psDb->db_execute($consulta4, $valores4, 'rowCount');

    //obtenemos a los usuarios que me siguen
    $consulta5 = "SELECT s.f_id, f.foto_id, f.f_title, f.f_url, u.user_name FROM u_follows AS s LEFT JOIN f_fotos AS f ON s.f_id = f.f_user LEFT JOIN u_miembros AS u ON f.f_user = u.user_id WHERE s.f_user = :user AND s.f_type = :type AND f.f_last = :last LIMIT 6";
    $valores5 = array('user' => $datos['foto']['f_user'], 'type' => 1, 'last' => 1);
    $datos['amigos'] = $psDb->resultadoArray($psDb->db_execute($consulta5, $valores5));

    //obtenemos los comentarios
    if($psUser->admod || $psCore->settings['c_see_mod']){
      $consulta6 = "SELECT c.*, u.user_name, u.user_activo FROM f_comentarios AS c LEFT JOIN u_miembros AS u ON c.c_user = u.user_id WHERE c.c_foto_id = :fid";
      $valores6 = array('fid' => $fid);
    }else{
      $consulta6 = "SELECT c.*, u.user_name, u.user_activo FROM f_comentarios AS c LEFT JOIN u_miembros AS u ON c.c_user = u.user_id WHERE c.c_foto_id = :fid AND u.user_activo = :activo AND u.user_baneado = :ban";
      $valores6 = array('fid' => $fid, 'activo' => 1, 'ban' => 0);
    }
    $comentarios = $psDb->resultadoArray($psDb->db_execute($consulta6, $valores6));
    foreach($comentarios as $key => $valor){
      $valor['c_body'] = $psCore->badWords($valor['c_body'], true);
      $datos['comments'][] = $valor;
    }
    $datos['foto']['f_comments'] = count($comentarios);

    //obtenemos a los usuarios que han visitado esta foto recientemente
    if($datos['foto']['f_visitas']){
      $consulta7 = "SELECT v.*, u.user_id, u.user_name FROM w_visitas AS v LEFT JOIN u_miembros AS u ON v.user = u.user_id WHERE v.for = :for AND v.type = :type AND v.user > :user ORDER BY v.date LIMIT :limite";
      $valores7 = array('for' => $fid, 'type' => 3, 'user' => 0, 'limite' => 10);
    }

    //obtenemos las medallas que tiene la foto
    $consulta8 = "SELECT m.*, a.* FROM w_medallas AS m LEFT JOIN w_medallas_assign AS a ON m.medal_id = a.medal_id WHERE a.medal_for = :for AND m.m_type = :type ORDER BY a.medal_date DESC LIMIT :limite";
    $valores8 = array('for' => $fid, 'type' => 3, 'limite' => 10);
    $datos['medallas'] = $psDb->resultadoArray($psDb->db_execute($consulta8, $valores8));
    $datos['m_total'] = count($datos['medallas']);

    //obtenemos las últimas fotos que subió el usuario
    if($psUser->admod || $psCore->settings['c_see_mod']){
      $consulta9 = "SELECT f.foto_id, f.f_title, f.f_date, f.f_status, f.f_url, u.user_name, u.user_activo FROM f_fotos AS f LEFT JOIN u_miembros AS u ON f.f_user = u.user_id WHERE f.f_user = :user ORDER BY f.foto_id DESC LIMIT :limite";
      $valores9 = array('user' => $datos['foto']['f_user'], 'limite' => 6);
    }else{
      $consulta9 = "SELECT f.foto_id, f.f_title, f.f_date, f.f_status, f.f_url, u.user_name, u.user_activo FROM f_fotos AS f LEFT JOIN u_miembros AS u ON f.f_user = u.user_id WHERE f.f_user = :user AND f.f_status = :status AND u.user_activo = :activo AND u.user_baneado = :ban ORDER BY f.foto_id DESC LIMIT :limite";
      $valores9 = array('user' => $datos['foto']['f_user'], 'status' => 0, 'activo' => 1, 'ban' => 0,'limite' => 6);
    }
    $datos['last'] = $psDb->resultadoArray($psDb->db_execute($consulta9, $valores9));
    //actualizamos la base de datos

    if($psUser->member){
      $consulta10 = "SELECT id FROM w_visitas WHERE for = :for AND type = :type AND (user = :user OR ip LIKE :ip) LIMIT :li,:lim";
      $valores10 = array('for' => $fid, 'type' => 3, 'user' => $psUser->user_id, 'ip' => $_SERVER['REMOTE_ADDR'], 'li' => 0, 'lim' => 100);
    }else{
      $consulta10 = "SELECT id FROM w_visitas WHERE for = :for AND type = :type AND  ip LIKE :ip LIMIT :li,:lim";
      $valores10 = array('for' => $fid, 'type' => 3, 'ip' => $_SERVER['REMOTE_ADDR'], 'li' => 0, 'lim' => 100);
    }
    $visitado = $psDb->db_execute($consulta10, $valores10, 'rowCount');
    //si el usuario es miembro y no ha sido visitada su foto insertamos y actualizamos datos
    if($psUser->member && $visitado == 0){
      $consulta11 = "INSERT INTO w_visitas (user, for, type, date, ip) VALUES (:user, :for, :type, :dat, :ip)";
      $valores11 = array('user' => $psUser->user_id, 'for' => $fid, 'type' => 3, 'dat' => time(), 'ip' => $_SERVER['REMOTE_ADDR']);
      $psDb->db_execute($consulta11, $valores11);
      $consulta12 = "UPDATE f_fotos SET f_hits = f_hits + :hits WHERE foto_id = :fid AND f_user != :user";
      $valores12 = array('hits' => 1, 'fid' => $fid, 'user' => $psUser->user_id);
      $psDb->db_execute($consulta12, $valores12);
    }else{//si ya ha sido visitada solo actualizamos
      $consulta11 = "UPDATE w_visitas SET date = :dat, ip = :ip WHERE for = :for AND type = :type";
      $valores11 = array('dat' => time(), 'ip' => $_SERVER['REMOTE_ADDR'], 'for' => $fid, 'type' => 3);
    }
    //comprobamos si tenemos que darle una medalla
    $this->darMedalla($fid);
    //devolvemos el array que hemos generado
    return $datos;
  }

  /**
   * @funcionalidad obtenemos las fotos del usuario 
   * @param  [type] $uid obtenemos el id del usuario
   * @return [type] devolvemos un array con los datos obtenidos
   */
  function getFotos($uid){
    global $psDb, $psUser, $psCore;
    //realizamos la consulta
    if($psUser->admod && $psCore->settings['c_see_mod'] == 1){
      $consulta = "SELECT f.foto_id, f.f_title, f.f_date, f.f_descripcion, f.f_url, f.f_status, u.user_name, u.user_activo FROM f_fotos AS f LEFT JOIN u_miembros AS u ON u.user_id = f.f_user WHERE f.f_user = :uid ORDER BY f.foto_id DESC";
      $valores = array('uid' => $uid);
    }else{
      $consulta = "SELECT f.foto_id, f.f_title, f.f_date, f.f_descripcion, f.f_url, f.f_status, u.user_name, u.user_activo FROM f_fotos AS f LEFT JOIN u_miembros AS u ON u.user_id = f.f_user WHERE f.f_user = :uid AND f.f_status = :status AND u.user_activo = :activo AND u.user_baneado = :ban ORDER BY f.foto_id DESC";
      $valores = array(
        'uid' => $uid,
        'status' => 0,
        'activo' => 1,
        'ban' => 0
      );
    }
    $total = $psDb->db_execute($consulta, $valores, 'rowCount');
    //obtenemos la paginación
    $pages = $psCore->getPagination($total, 15);
    $datos['pages'] = $pages;
    $consulta .= 'LIMIT :limite';
    $valores['limite'] = $pages['limit'];
    $datos['data'] = $psDb->resultadoArray($psDb->db_execute($consulta, $valores));
    return $datos;
  }

  /**
   * @funcionalidad obtenemos las últimas fotos y un listado con la paginación de todas ellas
   * @return [type] devolvemos un array con los datos obtenidos 
   */
  function getLastFotos(){
    global $psDb, $psCore, $psUser;
    $max = 15;//obtenemos el total de fotos a mostrar por página
    //realizamos una consulta para obtener el total de fotos
    if($psUser->admod && $psCore->settings['c_see_mod']){//si el usuario tiene permisos no restringimos 
      $consulta = "SELECT COUNT(f.foto_id) FROM f_fotos AS f LEFT JOIN u_miembros AS u ON u.user_id = f.f_user";
    }else{
      $consulta = "SELECT COUNT(f.foto_id) FROM f_fotos AS f LEFT JOIN u_miembros AS u ON u.user_id = f.f_user WHERE f.f_status = :status AND u.user_activo = :activo AND u.user_baneado = :ban";
      $valores = array(
        'status' => 0,
        'activo' => 1,
        'ban' => 0,
      );
    }
    list($total) = $psDb->db_execute($consulta, $valores, 'fetch_num');
    $datos['pages'] = $psCore->inicioPages($psCore->settings['url'] . '/fotos/?', $_GET['start'], $total, $max);
    //ahora obtenemos el límite de fotos por página junto con los datos de las fotos
    if($psUser->admod && $psCore->settings['c_see_mod']){
      $consulta2 = "SELECT f.foto_id, f.f_title, f.f_date, f.f_description, f.f_url, f.f_status, u.user_name, u.user_activo, u.user_baneado FROM f_fotos AS f LEFT JOIN u_miembros AS u ON u.user_id = f.f_user ORDER BY f.foto_id DESC";
      //$valores2 = array('limite' => $max);
    }else{
      $consulta2 = "SELECT f.foto_id, f.f_title, f.f_date, f.f_description, f.f_url, f.f_status, u.user_name, u.user_activo, u.user_baneado FROM f_fotos AS f LEFT JOIN u_miembros AS u ON u.user_id = f.f_user WHERE f.f_status = :status AND u.user_activo = :activo AND u.user_baneado = :ban ORDER BY f.foto_id DESC";
      $valores2 = array(
        'status' => 0,
        'activo' => 1,
        'ban' => 0,
      );
    }
    
    $datos['data'] = $psDb->resultadoArray($psDb->db_execute($consulta2, $valores2));
    return $datos;
  }

  /**
   * @funcionalidad añadimos una nueva foto
   * @return [type] si todo ha salido bien devolvemos el id de la foto, si no devolvemos un string con el error obtenido
   */
  function nuevaFoto(){
    global $psDb, $psUser, $psCore, $psMonitor, $psActividad;
    //comprobamos permisos del usuario y su estado
    if($psUser->member && $psUser->info['user_baneado'] == 0 && $psUser->info['user_activo'] == 1 && ($psUser->admod || $psUser->permisos['gopf'])){
      //obtenemos los datos del formulario
      $foto = array(
        'titulo' => $psCore->badWords(filter_input(INPUT_POST, 'titulo')),
        'foto' => array(
          'url' => filter_input(INPUT_POST, 'url'),
          'file' => $_FILES['file']
        ),
        'desc' => $psCore->badWords(substr(filter_input(INPUT_POST, 'desc'), 0, 1000)),
        'closed' => empty($_POST['closed']) ? 0 : 1,
        'visitas' => empty($_POST['visitas']) ? 0 : 1
      );
      //comprobamos los campos obligatorios
      if(empty($foto['titulo'])){
        $vacio['titulo'] = true;
      }      
      //comprobamos si está permitida la subida de archivos y cargamos la clase update
      require 'c.upload.php';
      $psUpload =& psUpload::getInstance();
      $psUpload->iscale = true;
      if($psCore->settings['c_allow_upload'] == 1){
        //comprobamos si está vacío
        if(empty($datos['foto']['url']) && empty($datos['foto']['file']['name'])){
          return '0: No has seleccioando ninguna foto.';
        }else{
          $upload_foto = $psUpload->newUpload(1);//tipo 1 de subida
        }
      }else{
        if(empty($datos['foto']['url'])){
          return '0: No has ingresado ninguna url.';
        }else{
          $psUpload->furl = $foto['foto']['url'];
          $upload_foto = $psUpload->newUpload(2);//tipo 2 de subida
        }
      }
      if($upload_foto[0][0] == 0){
        return $upload_foto[0][1];
      }else{
        $iurl = $upload_foto[0][1];
        //comprobamos la imagen
        if(empty($iurl)){
          return '0: Ocurri&oacute; un error al intentar subir la imagen.';
        }
        //actualizamos datos en la db
        $update = "UPDATE f_fotos SET f_last = :last WHERE f_user = :user AND f_last = :last2";
        $valores = array('last' => 0, 'user' => $psUser->user_id, 'last2' => 1);
        $psDb->db_execute($update, $valores);
        //comprobamos la ip del usuario
        if(!filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_URL)){
          return '0: Ocurri&oacute; un error al intentar validar tu ip.';
        }
        //si todo ok hasta ahora insertamos los datos de la nueva foto
        $insert = "INSERT INTO f_fotos (f_title, f_date, f_description, f_url, f_user, f_closed, f_visitas, f_last, f_ip) VALUES (:title, :dates, :descr, :url, :user, :closed, :visitas, :last, :ip)";
        $valores2 = array(
          'title' => $foto['titulo'],
          'dates' => time(),
          'descr' => $foto['desc'],
          'url' => $iurl,
          'user' => $psUser->user_id,
          'closed' => $foto['closed'],
          'visitas' => $foto['visitas'],
          'last' => 1,
          'ip' => $_SERVER['REMOTE_ADDR']
        );
        if($psDb->db_execute($insert, $valores2)){
          //obtenemos el id de la consulta anterior
          $fid = $psDb->getLastInsertId();
          //actualizamos las estadísticas
          $update2 = "UPDATE w_stats SET stats_fotos = stats_fotos + :stat WHERE stats_no = :no";
          $valores3 = array('stat' => 1, 'no' => 1);
          $psDb->db_execute($update2, $valores3);
          //actualizamos las fotos del usuario
          $update3 = "UPDATE u_miembros SET user_fotos = user_fotos + :us WHERE user_id = :uid";
          $valores4 = array('us' => 1, 'uid' => $psUser->user_id);
          $psDb->db_execute($update3, $valores4);
          //notificamos a los usuarios que me siguen
          $psMonitor->setFollowNotificaciones(10, 1, $psUser->user_id, $fid);
          //creamos la actividad
          $psActividad->setActividad(9, $fid);
          return $fid;
        }else{
          return '0: Ocurri&oacute; un error al insertar la nueva foto en la base de datos.';
        }
      }
    }else{
      return '0: Est&aacute;s intentado algo no permitido.';
    }
  }

  /**
   * @funcionalidad obtenemos los datos para editar la foto
   * @return [type] devolvemos un array con los datos si todo ha salido ok, sino devolvemos un string con el resultado obtenido
   */
  function getEditarFoto(){
    global $psDb, $psUser, $psCore;
    //obtenemos el id de la foto del formulario
    $fid = filter_input(INPUT_POST, 'fid');
    //obtenemos los datos de la foto
    $consulta = "SELECT * FROM f_fotos WHERE foto_id = :fid";
    $valores = array('fid' => $fid);
    $datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
    //comprobamos
    if(!empty($datos['f_user'])){
      //comprobamos si es el dueño de la foto o tiene permisos para editarla
      if($datos['f_user'] == $psUser->user_id || $psUser->admod || $psUser->permisos['moedfo']){
        return $datos;
      }else{
        return '0: No tienes los permisos necesarios para hacer eso.';
      }
    }else{
      return '0: Est&aacute;s intentando editar una foto que no existe.';
    }
  }

  /**
   * @funcionalidad editamos la foto seleccionada si tenemos los permisos apropiados o es nuestra
   * @return [type] devolvemos un string si ocurre algún fallo, sino, redireccionamos a la página de la foto
   */
  function editarFoto(){
    global $psDb, $psUser, $psCore, $psMonitor;
    //obtenemos el id de la foto
    $fid = filter_input(INPUT_POST, 'id');
    //obtenemos los datos de la foto
    $consulta = "SELECT f.foto_id, f.f_title, f.f_user, u.user_name FROM f_fotos AS f LEFT JOIN u_miembros AS u ON f.f_user = u.user_id WHERE f.foto_id = :fid";
    $valores = array('fid' => $fid);
    $datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
    //comprobamos
    if(!empty($datos['f_user'])){
      //comprobamos si es el dueño o si tiene permisos para editarla
      if($psUser->user_id == $datos['f_user'] || $psUser->admod || $psUser->permisos['moedfo']){
        //si todo ok obtenemos los datos del formulario
        $foto = array(
          'titulo' => $psCore->badWords(filter_input(INPUT_POST, 'titulo')),
          'desc' => $psCore->badWords(substr(filter_input(INPUT_POST, 'desc'), 0, 1000)),
          'privada' => empty($_POST['privada']) ? 0 : 1,
          'closed' => empty($_POST['closed']) ? 0 : 1,
          'visitas' => empty($_POST['visitas']) ? 0 : 1,
          'razon' => empty($_POST['razon']) ? 'Sin motivo' : filter_input(INPUT_POST, 'razon')
        );
        //actualizamos los datos
        $update = "UPDATE f_fotos SET f_title = :title, f_description = :descr, f_closed = :closed, f_visitas = :visitas WHERE foto_id = :fid";
        $valores2 = array(
          'title' => $foto['titulo'],
          'descr' => $foto['desc'],
          'closed' => $foto['closed'],
          'visitas' => $foto['visitas'],
          'fid' => $fid
        );
        $psDb->db_execute($update, $valores2);
        //comprobamos si el usuario que ha borrado la foto no es el dueño de la misma
        if($datos['f_user'] != $psUser->user_id){
          //mandamos un aviso al usuario de que se ha editado su foto
          $url = $psCore->settings['url'] . '/fotos/' . $datos['user_name'] . '/' . $datos['foto_id'] . '/' . $psCore->setSeo($datos['f_title']) . '.html';
          $aviso = '<p>Hola <strong>' . $psUser->getUserName($datos['f_user']) . '</strong>: </p><br>
          <p>Te informamos de que tu foto <a href="' . $url . '"><strong>' . $datos['f_title'] . '</strong></a> ha sido editada por ' . $psUser->nick . '</p><br><br>
          <p>Causa: ' . $foto['razon'] . '</p><br>
          <p>Te recomendamos que leas nuestro <a href="' . $psCore->settings['url'] . '/pages/protocolo/">Protocolo</a> para evitar cambios de este tipo en un futuro.</p><br>
          <p>Muchas gracias por su comprensi&oacute;n!</p><br>
          <p>El Staff de ' . $psCore->settings['titulo'] . '</p>';
          $psMonitor->setAviso($datos['f_user'], 'Foto modificada', $aviso, 2);
          //comprobamos la ip
          if(!filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_URL)){
            return 'Lo sentimos, su ip no pudo validarse correctamente.';
          }
          //si la ip ok insertamos los datos en el historial
          $insert = "INSERT INTO w_historial (pofid, action, type, mod, reason, date, mod_ip) VALUES (:fid, :action, :type, :mod, :reason, :dat, :ip)";
          $valores3 = array(
            'fid' => $datos['foto_id'],
            'action' => 1,
            'type' => 2,
            'mod' => $psUser->user_id,
            'reason' => $foto['razon'],
            'dat' => time(),
            'ip' => $_SERVER['REMOTE_ADDR']
          );
          $psDb->db_execute($insert, $valores3);
        }
        //redirigimos a la página de la foto
        $psCore->redirectTo($psCore->settings['url'] . '/fotos/' . $datos['user_name'] . '/' . $fid . '/' . $psCore->setSeo($foto['titulo']) . '.html');
      }else{
        return '0: La foto que intentas editar no es tuya o no tienes los permisos suficientes.';
      }
    }else{
      return '0: La foto que intentas editar no existe o no ha sido posible localizarla.';
    }
  }

  /**
   * @funcionalidad eliminamos la foto si tenemos los permisos necesarios para ello
   * @return [type] devolvemos un mensaje con el resultado obtenido
   */
  function borrarFoto(){
    global $psDb, $psUser, $psCore;
    //obtenemos el id de la foto del formulario
    $fid = filter_input(INPUT_POST, 'fid');
    //obtenemos los datos de la foto
    $consulta = "SELECT f_user FROM f_fotos WHERE foto_id = :fid";
    $valores = array('fid' => $fid);
    $datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
    //comprobamos
    if(!empty($datos['f_user'])){
      //comprobamos si la foto es nuestra o si tenemos permisos para borrarla
      if($datos['f_user'] == $psUser->user_id || $psUser->admod || $psUser->permisos['moef']){
        //borramos la foto
        $delete = "DELETE FROM f_fotos WHERE foto_id = :fid";
        if($psDb->db_execute($delete, $valores)){
          //borramos los comentarios de la foto
          $delete2 = "DELETE FROM f_comentarios WHERE c_foto_id = :fid";
          if($psDb->db_execute($delete2, $valores)){
            //boramos las estadísticas
            $delete3 = "DELETE FROM w_stats SET stats_fotos = stats_fotos - :stat WHERE stats_no = :no";
            $valores2 = array('stat' => 1, 'no' => 1);
            if($psDb->db_execute($delete3, $valores2)){
              return '1: La foto fue eliminada correctamente.';
            }else{
              return '0: Ocurri&oacute; un error al borrar las estad&iacute;sticas de la foto. Por favor int&eacute;ntalo de nuevo m&aacute;s tarde.';
            }
          }else{
            return '0: Ocurri&oacute; un error al intentar borrar los comentarios de la foto. Por favor int&eacute;ntalo de nuevo m&aacute;s tarde.';
          }
        }else{
          return '0: Ocurri&oacute; un error al borrar la foto. Por favor int&eacute;ntalo de nuevo m&aacute;s tarde.';
        }
      }else{
        return '0: Esta foto no es tuya o no tienes permisos para poder borrarla.';
      }
    }else{
      return '0: La foto que has seleccionado no existe o fue imposible encontrarla.';
    }
  }

  /**
   * @funcionalidad votamos la foto
   * @return [type] devolvemos un string con el resultado obtenido
   */
  function votarFoto(){
    global $psDb, $psUser, $psCore;
    //comprobamos si el usuario es miembro
    if($psUser->member){
      $fid = filter_input(INPUT_POST, 'fotoid');
      $voto = filter_input(INPUT_POST, 'voto');
      //obtenemos los datos de la foto
      $consulta = "SELECT f_user FROM f_fotos WHERE foto_id = :fid";
      $valores = array('fid' => $fid);
      $datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
      //comprobamos si la foto es mía o de otro usuario
      if($datos['f_user'] != $psUser->user_id){
        //compruebo si ya he votado la foto
        $consulta2 = "SELECT vid FROM f_fotos WHERE v_foto_id = :fid AND v_user = :user";
        $valores2 = array('fid' => $fid, 'user' => $psUser->user_id);
        $votado = $psDb->db_execute($consulta2, $valores2, 'rowCount');
        if(empty($votado)){
          if($voto == 'pos'){
            $update = "UPDATE f_fotos SET f_votos_pos = f_votos_pos + :pos WHERE foto_id = :fid";
            $vupdate = array('pos' => 1, 'fid' => $fid);
            $type = 0;
          }else{
            $update = "UPDATE f_fotos SET f_votos_neg = f_votos_neg - :neg WHERE foto_id = :fid";
            $vupdate = array('neg' => 1, 'fid' => $fid);
            $type = 1;
          }
          $psDb->db_execute($update, $uvalores);
          //ahora insertamos los datos del voto
          $insert = "INSERT INTO f_fotos (v_foto_id, v_user, v_type, v_date) VALUES (:fid, :user, :type, :dat)";
          $ivalores = array(
            'fid' => $fid, 
            'user' => $psUser->user_id, 
            'type' => $type, 
            'dat' => time()
          );
          if($psDb->db_execute($insert, $ivalores)){
            return '1: Foto votada. Gracias!';
          }
        }else{
          return '0: Ya has votado esta foto.';
        }
      }else{
        return '0: No puedes votar una foto tuya.';
      }
    }else{
      return '0: Lo sentimos, debes estar registrado para poder votar la foto.';
    }
  }

  /**
   * @funcionalidad obtenemos los últimos comentarios realizados
   * @return [type] devolvemos un array con todos los datos
   */
  function getLastComentarios(){
    global $psDb, $psCore, $psUser;
    //realizamos la consulta
    if($psUser->admod && $psCore->settings['c_see_mod']){
      $consulta = "SELECT c.cid, c.c_user, f.foto_id, f.f_title, f.f_status, u.user_name, u.user_activo FROM f_comentarios AS c LEFT JOIN f_fotos AS f ON c.c_foto_id = f.foto_id LEFT JOIN u_miembros AS u ON f.f_user = u.user_id ORDER BY c.c_date DESC LIMIT :limite";
      $valores = array('limite' => 10);
    }else{
      $consulta = "SELECT c.cid, c.c_user, f.foto_id, f.f_title, f.f_status, u.user_name, u.user_activo FROM f_comentarios AS c LEFT JOIN f_fotos AS f ON c.c_foto_id = f.foto_id LEFT JOIN u_miembros AS u ON f.f_user = u.user_id WHERE f.f_status = :status AND u.user_activo = :activo AND u.user_baneado = :ban ORDER BY c.c_date DESC LIMIT :limite";
      $valores = array(
        'status' => 0,
        'activo' => 1,
        'ban' => 0,
        'limite' => 10
      );
    }
    return $psDb->resultadoArray($psDb->db_execute($consulta, $valores));
  }

  /**
   * @funcionalidad añadimos un nuevo comentario a la foto seleccionada
   * @return [type] devolvemos un array con los datos si todo ha salido ok, si no mandamos un mensaje con el resultado obtenido
   */
  function nuevoComentario(){
    global $psDb, $psCore, $psUser, $psMonitor;
    //comprobamos si el usuario es miembro y los permisos que tiene
    if($psUser->member && $psUser->info['user_baneado'] == 0 && $psUser->info['user_activo'] == 1 && ($psUser->admod || $psUser->permisos['gopcf'])){
      //obtenemos el comentario, max 1000 caracteres
      $comentario = substr(filter_input(INPUT_POST, 'comentario'), 0, 1000);
      //y el id de la foto
      $fid = filter_input(INPUT_POST, 'fotoid');
      if($comentario = ''){
        return '0: El campo comentario es obligatorio para enviar el comentario.';
      }
      //obtenemos los datos del dueño de la foto
      $consulta = "SELECT f_user, f_closed FROM f_fotos WHERE foto_id = :fid";
      $valores = array('fid' => $fid);
      $datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
      //comprobamos los datos de la consulta
      if($datos['f_user']){
        if($datos['f_closed'] != 1 || $datos['f_user'] == $psUser->user_id){
          //comprobamos la ip del usuario
          if(!filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_URL)){
            return 'Lo sentimos, su ip no pudo validarse correctamente.';
          }
          //realizamos la inserción de datos
          $consulta2 = "INSERT INTO f_comentarios (c_foto_id, c_user, c_date, c_body, c_ip) VALUES (:fid, :user, :dat, :body, :ip)";
          $valores2 = array(
            'fid' => $fid,
            'user' => $psUser->user_id,
            'dat' => time(),
            'body' => $comentario,
            'ip' => $_SERVER['REMOTE_ADDR']
          );
          if($psDb->db_execute($consulta2, $valores2)){
            //obtenemos el id de la última consulta
            $cid = $psDb->getLastInsertId();
            //actualizamos las estadísticas
            $consulta3 = "UPDATE w_stats SET stats_foto_comments = stats_foto_comments + :stat WHERE stats_no = :no";
            $valores3 = array('stat' => 1, 'no' => 1);
            $psDb->db_execute($consulta3, $valores3);
            //notificamos al usuario
            $psMonitor->setNotificacion(11, $datos['f_user'], $psUser->user_id, $cid);
            return array($cid, $psCore->badWords($comentario, true), time(), filter_input(INPUT_POST, 'auser'));
          }else{
            return '0: Ocurri&oacute; un error al a&ntilde;adir el comentario. Por favor int&eacute;ntelo de nuevo m&aacute;s tarde.';
          }
        }else{
          return '0: La foto se encuentra cerrada y no est&aacute; permitido a&ntilde;adir nuevos comentarios.';
        }
      }else{
        return '0: La foto seleccionada no existe o no ha sido posible encontrarla.';
      }
    }else{
      return '0: No tienes permisos suficientes para realizar eso.';
    }
  }

  /**
   * @funcionalidad borramos el comentario de la foto si tenemos los permisos adecuados
   * @return [type] devolvemos un string con el resultado obtenido
   */
  function borrarComentario(){
    global $psDb, $psCore, $psUser;
    //obtenemos el id del comentario
    $cid = filter_input(INPUT_POST, 'cid');
    //obtenemos los datos de la db
    $consulta = "SELECT c.cid, c.c_user, f.foto_id, f.f_user FROM f_comentarios AS c LEFT JOIN f_fotos AS f ON c.c_foto_id = f.foto_id WHERE c.cid = :cid";
    $valores = array('cid' => $cid);
    $datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
    //realizamos comprobaciones
    if(!empty($datos['cid'])){
      //comprobamos si es el dueño de la foto o si tiene permisos
      if($datos['f_user'] == $psUser->user_id || $psUser->admod || $psUser->permisos['moecf']){
        //realizamos la consulta para borrar el comentario
        $consulta2 = "DELETE FROM f_comentarios WHERE cid = :cid";
        if($psDb->db_execute($consulta2, $valores)){
          //si todo ok actualizamos los datos 
          $consulta3 = "UPDATE w_stats SET stats_foto_comments = stats_foto_comments - :stats WHERE stats_no = :no";
          $valores3 = array('stats' => 1, 'no' => 1);
          $psDb->db_execute($consulta3, $valores3);
          return '1: El comentario ha sido eliminado satisfactoriamente.';
        }else{
          return '0: Ocurri&oacute; un error al intentar borrar el comentario.';
        }
      }else{
        return '0: No tienes permisos para hacer eso.';
      }
    }else{
      return '0: El comentario al que intentas acceder no existe o no se ha podido acceder a él.';
    }
  }

  /**
   * @funcionalidad asignamos la medalla a la foto
   * @param  [type] $fid obtenemos el id de la foto
   */
  function darMedalla($fid){
    global $psDb;
    //obtenemos los datos de la foto
    $consulta = "SELECT foto_id, f_user, f_votos_pos, f_votos_neg, f_hits FROM f_fotos WHERE foto_id = :fid";
    $valores = array('fid' => $fid);
    $datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
    //obtenemos el total de comentarios que tiene la foto
    $consulta2 = "SELECT COUNT(cid) FROM f_comentarios WHERE c_foto_id = :fid";
    $comentarios = $psDb->db_execute($consulta2, $valores, 'fetch_assoc');
    //obtenemos las medallas que tiene la foto
    $consulta3 = "SELECT COUNT(m.medal_id) FROM w_medallas AS m LEFT JOIN w_medallas_assign AS a ON m.medal_id = a.medal_id WHERE m.m_type = :type AND a.medal_for = :for";
    $valores3 = array('type' => 3, 'for' => $fid);
    $f_medal = $psDb->db_execute($consulta3, $valores3, 'fetch_assoc');
    //ahora obtenemos los datos de todas las medallas
    $consulta4 = "SELECT * FROM w_medallas WHERE m_type = :type ORDER BY m_cant DESC";
    $valores4 = array('type' => 3);
    $medallas = $psDb->resultadoArray($psDb->db_execute($consulta4, $valores4));
    //si hay alguna medalla obtenemos su id para asignarla después
    foreach($medallas as $medalla){
      //votos positivos
      if($medalla['m_cond_foto'] == 1 && !empty($datos['f_votos_pos']) && $medalla['m_cant'] > 0 && $medalla['m_cant'] <= $datos['f_votos_pos']){
        $new = $medalla['medal_id'];
        //votos negativos
      }elseif($medalla['m_cond_foto'] == 2 && !empty($datos['f_votos_neg']) && $medalla['m_cant'] > 0 && $medalla['m_cant'] <= $datos['f_votos_neg']){
        $new = $medalla['medal_id'];
        //total de comentarios
      }elseif($medalla['m_cond_foto'] == 3 && !empty($comentarios[0]) && $medalla['m_cant'] > 0 && $medalla['m_cant'] <= $comentarios[0]){
        $new = $medalla['medal_id'];
        //total de visitas
      }elseif($medalla['m_cond_foto'] == 4 && !empty($datos['f_hits']) && $medalla['m_cant'] > 0 && $medalla['m_cant'] <= $datos['f_hits']){
        $new = $medalla['medal_id'];
        //total de medallas
      }elseif($medalla['m_cond_foto'] == 5 && !empty($f_medal[0]) && $medalla['m_cant'] > 0 && $medalla['m_cant'] <= $f_medal[0]){
        $new = $medalla['medal_id'];
      }
      //si hemos obtenido una medalla, la asignamos a la foto
      if(!empty($new)){
        $consulta5 = "SELECT COUNT(id) FROM w_medallas_assign WHERE medal_id = :mid AND medal_for = :for";
        $valores5 = array('mid' => $new, 'for' => $fid);
        if($psDb->db_execute($consulta5, $valores5, 'fetch_num')){
          //asignamos la medalla
          $insert = "INSERT INTO w_medallas_assign (medal_id, medal_for, medal_date, medal_ip) VALUES (:mid, :for, :dat, :ip)";
          $ivalores = array('mid' => $new, 'for' => $fid, 'dat' => time(), 'ip' => $_SERVER['REMOTE_ADDR']);
          $psDb->db_execute($insert, $ivalores);
          //mandamos la notificación
          $insert2 = "INSERT INTO u_monitor (user_id, obj_uno, obj_dos, not_type, not_date) VALUES (:uid, :uno, :dos, :type, :dat)";
          $ivalores2 = array('uid' => $datos['f_user'], 'uno' => $new, 'dos' => $fid, 'type' => 17, 'dat' => time());
          $psDb->db_execute($insert2, $ivalores2);
          //sumamos la medalla al total de medallas
          $update = "UPDATE w_medallas SET m_total = m_total + :total WHERE medal_id = :mid";
          $uvalores = array('total' => 1, 'mid' => $new);
          $psDb->db_execute($update, $uvalores);
        }
      }
    }
  }
}
