<?php
//comprobamos si la constante PS_HEADER ha sido declarada, en caso contrario no se puede acceder al script
if(!defined('PS_HEADER')){
  exit('No se permite el acceso directo al script');
}

/**
 * Clase medallas
 * destinada al control de las medallas 
 *
 * @name() c.medallas.php
 * @author  Iván Martínez Tutor
 */
class psMedallas(){
  /**
   * @funcionalidad comprobamos si la clase ha sido instanciada
   * si no es así creamos un nuevo objeto para la clase psMedallas
   * @return [type] [description]
   */
  public static function &getInstance(){
    static $instance;
    if(is_null($instance)){
      $instance = new psMedallas();
    }
    return $instance;
  }

  /**
   * @funcionalidad obtenemos los datos de la medalla seleccionada
   * @return [type] devolvemos un array con los datos de la medalla
   */
  function getMedalla(){
    global $psDb;
    $consulta = "SELECT * FROM w_medallas WHERE medal_id = :mid";
    $valores = array('mid' => filter_input(INPUT_GET, 'mid'));
    $medalla = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
    return $medalla;
  }

  /**
   * @funcionalidad obtenemos un listado con todas las medallas y la paginación para la misma
   * @return [type] devolvemos un array con los datos obtenidos
   */
  function getMedallas(){
    global $psDb, $psCore;
    $max = 15; //maximas medallas a mostrar por página
    $limite = $psCore->setPagLimite($max, true);
    //realizamos la consulta
    $consulta = "SELECT u.user_id, u.user_name, m.* FROM w_medallas AS m LEFT JOIN u_miembros AS u ON m.m_autor = u.user_id ORDER BY m.medal_id DESC LIMIT :limite";
    $valores = array('limite' => $limite);
    $query = $psDb->db_execute($consulta, $valores);
    $datos['medallas'] = $psDb->resultadoArray($query);
    //obtenemos la paginación para el listado de medallas
    $consulta2 = "SELECT COUNT(*) FROM w_medallas WHERE medal_id > :id";
    $valores2 = array('id' => 0);
    list($total) = $psDb->db_execute($consulta2, $valores2, 'fetch_num');
    $datos['pages'] = $psCore->inicioPages($psCore->settings['url'] . '/admin/medallas?', $_GET['start'], $total, $max);
    return $datos;
  }

  /**
   * @funcionalidad editamos los datos de una medalla
   * @return [type] devolvemos el resultado de la consulta
   */
  function editarMedalla(){
    global $psDb, $psCore;
    $medalla = array(
      'titulo' => $psCore->badWords(filter_input(INPUT_POST, 'med_title')),
      'descripcion' => $psCore->badWords(filter_input(INPUT_POST, 'med_desc')),
      'imagen' => filter_input(INPUT_POST, 'med_img'),
      'tipo' => filter_input(INPUT_POST, 'med_type'),
      'cantidad' => filter_input(INPUT_POST, 'med_cant'),
      'cond_user' => filter_input(INPUT_POST, 'med_cond_user'),
      'cond_user_rango' => filter_input(INPUT_POST, 'med_cond_user_rango'),
      'cond_post' => filter_input(INPUT_POST, 'med_cond_post'),
      'cond_foto' => filter_input(INPUT_POST, 'med_cond_foto'),
    );
    //validamos los campos
    if(empty($medalla['titulo'])){
      return 'El campo t&iacute;tulo no puede estar vac&iacute;o.';
    }
    if(empty($medalla['descripcion'])){
      return 'El campo descripci&oacute;n no puede estar vac&iacute;o.';
    }
    if(is_numeric($medalla['tipo']) && is_numeric($medalla['cantidad']) && is_numeric($medalla['cond_user']) && is_numeric($medalla['cond_user_rango']) && is_numeric($medalla['cond_post']) && is_numeric($medalla['cond_foto'])){
      //comprobamos en la base de datos
      if($medalla['tipo'] == 1){
        $consulta = "SELECT medal_id FROM w_medallas WHERE m_type = :type AND m_cant = :cant AND m_cond_user = :user AND m_cond_user_rango = :rango AND medal_id != :mid";
        $valores = array(
          'type' => 1,
          'cant' => $medalla['cantidad'],
          'user' => $medalla['cond_user'],
          'rango' => $medalla['cond_user_rango'],
          'mid' => filter_input(INPUT_GET, 'mid')
        );
        if($psDb->db_execute($consulta, $valores, 'rowCount')){
          $continue = false;
        }else{
          $continue = true;
        }
      }else if($medalla['tipo'] == 2){
        $consulta = "SELECT medal_id FROM w_medallas WHERE m_type = :type AND m_cant = :cant AND m_cond_post = :post AND medal_id != :mid";
        $valores = array(
          'type' => 2,
          'cant' => $medalla['cantidad'],
          'post' => $medalla['cond_post'],
          'mid' => filter_input(INPUT_GET, 'mid')
        );
        if($psDb->db_execute($consulta, $valores, 'rowCount')){
          $continue = false;
        }else{
          $continue = true;
        }
      }else if($medalla['tipo'] == 3){
        $consulta = "SELECT medal_id FROM w_medallas WHERE m_type = :type AND m_cant = :cant AND m_cond_foto = :foto AND medal_id != :mid";
        $valores = array(
          'type' => 3,
          'cant' => $medalla['cantidad'],
          'foto' => $medalla['cond_foto'],
          'mid' => filter_input(INPUT_GET, 'mid')
        );
        if($psDb->db_execute($consulta, $valores, 'rowCount')){
          $continue = false;
        }else{
          $continue = true;
        }
      }
      //actualizamos los datos
      if($continue == true){
        $consulta2 = "UPDATE w_medallas SET m_title = :title, m_descripcion = :description, m_image = :imagen, m_cant = :cant, m_type = :type, m_cond_user = :user, m_cond_user_rango = :rango, m_cond_post = :post, m_cond_foto = :foto WHERE medal_id = :mid";
        $valores2 = array(
          'title' => $medalla['titulo'],
          'descripcion' => $medalla['descripcion'],
          'imagen' => $medalla['imagen'],
          'cant' => $medalla['cantidad'],
          'type' => $medalla['tipo'],
          'user' => $medalla['cond_user'],
          'rango' => $medalla['cond_user_rango'],
          'post' => $medalla['cond_post'],
          'foto' => $medalla['cond_foto'],
          'mid' => filter_input(INPUT_GET, 'mid')
        );
        if($psDb->db_execute($consulta2, $valores2)){
          return true;
        }else{
          return 'Ya existe otra medalla igual.';
        }
      }
    }else{
      return 'Por favor introduzca valores num&eacute;ricos.';
    }
  }

  /**
   * @funcionalidad añadimos una nueva medalla
   * @return [type] devolvemos un string con el resultado de la operación
   */
  function newMedalla(){
    global $psDb, $psCore, $psUser;
    $medalla = array(
      'titulo' => $psCore->badWords(filter_input(INPUT_POST, 'med_title')),
      'descripcion' => $psCore->badWords(filter_input(INPUT_POST, 'med_desc')),
      'imagen' => filter_input(INPUT_POST, 'med_img'),
      'tipo' => filter_input(INPUT_POST, 'med_type'),
      'cantidad' => filter_input(INPUT_POST, 'med_cant'),
      'cond_user' => filter_input(INPUT_POST, 'med_cond_user'),
      'cond_user_rango' => filter_input(INPUT_POST, 'med_cond_user_rango'),
      'cond_post' => filter_input(INPUT_POST, 'med_cond_post'),
      'cond_foto' => filter_input(INPUT_POST, 'med_cond_foto'),
    );
    //validamos los campos
    if(empty($medalla['titulo'])){
      return 'El campo t&iacute;tulo no puede estar vac&iacute;o.';
    }
    if(empty($medalla['descripcion'])){
      return 'El campo descripci&oacute;n no puede estar vac&iacute;o.';
    }
    if(is_numeric($medalla['tipo']) && is_numeric($medalla['cantidad']) && is_numeric($medalla['cond_user']) && is_numeric($medalla['cond_user_rango']) && is_numeric($medalla['cond_post']) && is_numeric($medalla['cond_foto'])){
      //comprobamos en la base de datos
      if($medalla['tipo'] == 1){
        $consulta = "SELECT medal_id FROM w_medallas WHERE m_type = :type AND m_cant = :cant AND m_cond_user = :user AND m_cond_user_rango = :rango";
        $valores = array(
          'type' => 1,
          'cant' => $medalla['cantidad'],
          'user' => $medalla['cond_user'],
          'rango' => $medalla['cond_user_rango'],
        );
        if($psDb->db_execute($consulta, $valores, 'rowCount')){
          $continue = false;
        }else{
          $continue = true;
        }
      }else if($medalla['tipo'] == 2){
        $consulta = "SELECT medal_id FROM w_medallas WHERE m_type = :type AND m_cant = :cant AND m_cond_post = :post";
        $valores = array(
          'type' => 2,
          'cant' => $medalla['cantidad'],
          'post' => $medalla['cond_post'],
        );
        if($psDb->db_execute($consulta, $valores, 'rowCount')){
          $continue = false;
        }else{
          $continue = true;
        }
      }else if($medalla['tipo'] == 3){
        $consulta = "SELECT medal_id FROM w_medallas WHERE m_type = :type AND m_cant = :cant AND m_cond_foto = :foto";
        $valores = array(
          'type' => 3,
          'cant' => $medalla['cantidad'],
          'foto' => $medalla['cond_foto'],
        );
        if($psDb->db_execute($consulta, $valores, 'rowCount')){
          $continue = false;
        }else{
          $continue = true;
        }
      }
      //actualizamos los datos
      if($continue == true){
        $consulta2 = "INSERT INTO w_medallas (m_autor, m_title, m_description, m_image, m_cant, m_type, m_cond_user, m_cond_user_rango, m_cond_post, m_cond_foto, m_date) VALUES(:autor, :title, :descripcion, :imagen, :cant, :type, :user, :rango, :post, :foto, :dates)";
        $valores2 = array(
          'autor' => $psUser->user_id,
          'title' => $medalla['titulo'],
          'descripcion' => $medalla['descripcion'],
          'imagen' => $medalla['imagen'],
          'cant' => $medalla['cantidad'],
          'type' => $medalla['tipo'],
          'user' => $medalla['cond_user'],
          'rango' => $medalla['cond_user_rango'],
          'post' => $medalla['cond_post'],
          'foto' => $medalla['cond_foto'],
          'dates' => time()
        );
        if($psDb->db_execute($consulta2, $valores2)){
          return true;
        }else{
          return 'Ocurri&oacute; un error al insertar la medalla.';
        }
      }else{
        return 'Ya existe una medalla igual a esta.';
      }
    }else{
      return 'Por favor introduzca valores num&eacute;ricos.';
    }
  }

  /**
   * @funcionalidad eliminamos la medalla seleccionada y sus asignaciones
   * @return [type] devolvemos un string con el resultado de la consulta
   */
  function borrarMedalla(){
    global $psDb;
    $medalla = filter_input(INPUT_POST, 'medal_id');
    //realizamos las consultas oportunas
    $consulta = "DELETE FROM w_medallas WHERE medal_id = :mid";
    $consulta2 = "DELETE FROM w_medallas_assign WHERE medal_id = :mid";
    $valores = array('mid' => $medalla);
    if($psDb->db_execute($consulta, $valores)){
      if($psDb->db_execute($consulta2, $valores)){
        return 'La medalla fue eliminada correctamente.';
      }else{
        return 'La medalla se borro correctamente pero, las asignaciones no pudieron eliminarse por completo.';
      }
    }else{
      return 'Ocurri&oacute; un error al intentar borrar la medalla.';
    }
  }

  /**
   * @funcionalidad asignamos la medalla al usuario, post o foto
   * @return [type] devolvemos un string con el resultado de la consulta en caso de producirse algún error
   */
  function asignarMedalla(){
    global $psDb, $psCore, $psUser;
    //obtenemos los datos del formulario
    $medalla = filter_input(INPUT_POST, 'mid');
    $username = strtolower(filter_input(INPUT_POST, 'm_usuario'));
    $post = filter_input(INPUT_POST, 'pid');
    $foto = filter_input(INPUT_POST, 'fid');
    $uid = $psUser->getUid($username);
    //realizamos comprobaciones
    if(!empty($medalla) && !empty($username) || !empty($post) || !empty($foto)){
      $valores['mid'] = $medalla;
      //comprobamos el tipo
      if($username){
        $valores['type'] = 'AND m_type = :type2';
        $valores['type2'] = 1;
      }elseif($post){
        $valores['type'] = 'AND m_type = :type2';
        $valores['type2'] = 2;
      }elseif($foto){
        $valores['type'] = 'AND m_type = :type2';
        $valores['type2'] = 3;
      }
      $consulta = "SELECT medal_id FROM w_medallas WHERE medal_id = :mid :type";
      if($psDb->db_execute($consulta, $valores)){
        if(filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_URL)){
          if($username){
            //si se asigna a un usuario comprobamos los datos y realizamos las consultas oportunas
            $conUser = "SELECT user_id FROM u_miembros WHERE LOWER(user_name) = :name";
            $valUser = array('name' => $username);
            if($psDb->db_execute($conUser, $valUser, 'rowCount')){
              $conUser2 = "SELECT id FROM w_medallas_assign WHERE medal_id = :mid AND medal_for = :for";
              $valUser2 = array(
                'mid' => $medalla, 
                'for' => $uid
              );
              if(!$psDb->db_execute($conUser2, $valUser2, 'rowCount')){
                $conUser3 = "INSERT INTO w_medallas_assign (medal_id, medal_for, medal_date, medal_ip) VALUES (:mid, :for, :dates, :ip)";
                $valUser3 = array(
                  'mid' => $medalla, 
                  'for' => $uid,
                  'dates' => time(),
                  'ip' => $_SERVER['REMOTE_ADDR']
                );
                if($psDb->db_execute($conUser3, $valUser3)){
                  $conUser4 = "INSERT INTO u_monitor (user_id, obj_uno, not_type, not_date) VALUES (:uid, :obj_uno, :type, :dates)";
                  $valUser4 = array(
                    'uid' => $uid, 
                    'obj_uno' => $medalla,
                    'type' => 15,
                    'dates' => time()
                  );
                  if($psDb->db_execute($conUser4, $valUser4)){
                    $continuar = true;
                  }else{
                    return 'Ocurri&oacute un error al notificar al usuario';
                  }
                }else{
                  return 'Ocurri&oacute un error al asignar la medalla al usuario';
                }
              }else{
                return 'El usuario ya tiene asignada esa medalla.';
              }
            }else{
              return 'El usuario seleccionado no existe.';
            }
          }else if($post){
            //si se asigna a un post comprobamos los datos y realizamos las consultas oportunas
            $conPost = "SELECT post_id, post_user FROM p_posts WHERE post_id = :pid";
            $valPost = array('pid' => $post);
            if($psDb->db_execute($conPost, $valPost, 'rowCount')){
              $datosPost = $psDb->db_execute($conPost, $valPost, 'fetch_assoc');
              $conPost2 = "SELECT id FROM w_medallas_assign WHERE medal_id = :mid AND medal_for = :for";
              $valPost2 = array(
                'mid' => $medalla, 
                'for' => $post
              );
              if(!$psDb->db_execute($conPost2, $valPost2, 'rowCount')){
                $conPost3 = "INSERT INTO w_medallas_assign (medal_id, medal_for, medal_date, medal_ip) VALUES (:mid, :for, :dates, :ip)";
                $valPost3 = array(
                  'mid' => $medalla, 
                  'for' => $post,
                  'dates' => time(),
                  'ip' => $_SERVER['REMOTE_ADDR']
                );
                if($psDb->db_execute($conPost3, $valPost3)){
                  $conPost4 = "INSERT INTO u_monitor (user_id, obj_uno, obj_dos, not_type, not_date) VALUES (:uid, :obj_uno, :obj_dos, :type, :dates)";
                  $valPost4 = array(
                    'uid' => $datosPost['post_user'], 
                    'obj_uno' => $medalla,
                    'obj_dos' => $post,
                    'type' => 16,
                    'dates' => time()
                  );
                  if($psDb->db_execute($conPost4, $valPost4)){
                    $continuar = true;
                  }else{
                    return 'Ocurri&oacute un error al notificar al usuario';
                  }
                }else{
                  return 'Ocurri&oacute un error al asignar la medalla al post';
                }
              }else{
                return 'El post ya tiene asignada esa medalla.';
              }
            }else{
              return 'El post seleccionado no existe.';
            }
          }else if($foto){
            //si se asigna a un foto comprobamos los datos y realizamos las consultas oportunas
            $conFoto = "SELECT foto_id, f_user FROM f_fotos WHERE foto_id = :fid";
            $valFoto = array('fid' => $foto);
            if($psDb->db_execute($conFoto, $valFoto, 'rowCount')){
              $datosFoto = $psDb->db_execute($conFoto, $valFoto, 'fetch_assoc');
              $conFoto2 = "SELECT id FROM w_medallas_assign WHERE medal_id = :mid AND medal_for = :for";
              $valFoto2 = array(
                'mid' => $medalla, 
                'for' => $foto
              );
              if(!$psDb->db_execute($conFoto2, $valFoto2, 'rowCount')){
                $conFoto3 = "INSERT INTO w_medallas_assign (medal_id, medal_for, medal_date, medal_ip) VALUES (:mid, :for, :dates, :ip)";
                $valFoto3 = array(
                  'mid' => $medalla, 
                  'for' => $foto,
                  'dates' => time(),
                  'ip' => $_SERVER['REMOTE_ADDR']
                );
                if($psDb->db_execute($conFoto3, $valFoto3)){
                  $conFoto4 = "INSERT INTO u_monitor (user_id, obj_uno, obj_dos, not_type, not_date) VALUES (:uid, :obj_uno, :obj_dos, :type, :dates)";
                  $valFoto4 = array(
                    'uid' => $datosFoto['f_user'], 
                    'obj_uno' => $medalla,
                    'obj_dos' => $foto,
                    'type' => 17,
                    'dates' => time()
                  );
                  if($psDb->db_execute($conFoto4, $valFoto4)){
                    $continuar = true;
                  }else{
                    return 'Ocurri&oacute un error al notificar al usuario';
                  }
                }else{
                  return 'Ocurri&oacute un error al asignar la medalla a la foto';
                }
              }else{
                return 'La foto ya tiene asignada esa medalla.';
              }
            }else{
              return 'La foto seleccionada no existe.';
            }
          }else{
            return 'Ha sido imposible validar el tipo de contenido al que asignar la medalla.';
          }
        }else{
          return 'Su ip es incorrecta o no pudo validarse.';
        }
      }else{
        return 'La medalla no pudo ser asignada porque no existe o no est&aacute; asignada.';
      }
    }else{
      return 'Por favor, revise los datos introducidos.';
    }
  }

  /**
   * @funcionalidad obtenemos un listado con las asignaciones de medallas
   * @return [type] devolvemos un array con los datos obtenidos
   */
  function getAssignMedallas(){
    global $psDb, $psCore;
    $max = 30; //maximas medallas a mostrar por página
    $limite = $psCore->setPagLimite($max, true);
    //realizamos la consulta
    $consulta = "SELECT u.user_id, u.user_name, a.*, p.post_id, p.post_title, c.c_nombre, c.c_seo, f.foto_id, f.f_title, m.* FROM w_medallas_assign AS a LEFT JOIN u_miembros AS u ON u.user_id = a.medal_for LEFT JOIN p_posts AS p ON p.post_id = a.medal_for LEFT JOIN p_categorias AS c ON c.cid = p.post_category LEFT JOIN f_fotos AS f ON f.foto_id = a.medal_for LEFT JOIN w_medallas AS m ON m.medal_id = a.medal_id ORDER BY a.medal_date DESC LIMIT :limite";
    $valores = array('limite' => $limite);
    $query = $psDb->db_execute($consulta, $valores);
    $datos['asignaciones'] = $psDb->resultadoArray($query);

    //obtenemos la paginación para el listado de medallas
    $consulta2 = "SELECT COUNT(*) FROM w_medallas_assign WHERE id > :id";
    $valores2 = array('id' => 0);
    list($total) = $psDb->db_execute($consulta2, $valores2, 'fetch_num');
    $datos['pages'] = $psCore->inicioPages($psCore->settings['url'] . '/admin/medallas?act=showassign', $_GET['start'], $total, $max);
    return $datos;
  }

  /**
   * @funcionalidad eliminamos la asignación de medalla seleccionada
   * @return [type] devolvemos un string con el resultado de las consultas
   */
  function borrarAsignarMedalla(){
    global $psDb;
    $asignacion = filter_input(INPUT_POST, 'aid');
    $medalla = filter_input(INPUT_POST, 'mid');
    $consulta = "SELECT id FROM w_medallas_assign WHERE id = :asig AND medal_id = :mid";
    $consulta2 = "DELETE FROM w_medallas_assign WHERE id = :asig";
    $consulta3 = "UPDATE w_medallas SET m_total = :total WHERE medal_id = :mid";
    $valores = array(
      'asig' => $asignacion,
      'mid' => $medalla
    );
    $valores2 = array('asig' => $asignacion);
    $valores3 = array(
      'total' => 'm_total'-1,
      'mid' => $medalla
    );
    if($psDb->db_execute($consulta, $valores)){
      if($psDb->db_execute($consulta2, $valores2)){
        if($psDb->db_execute($consulta3, $valores3)){
          return 'La asignaci&oacute;n de medalla fue eliminada correctamente.';
        }else{  
          return 'La asignaci&oacute;n de medalla fue eliminada pero, no pudo descontarse de las estad&iacute;sticas de medallas';
        }
      }else{
        return 'La asignaci&oacute;n de medalla seleccionada no pudo eliminarse correctamente.';
      }
    }else{
      return 'La asignaci&oacute;n de medalla seleccionada no existe o no pudo ser encontrada.';
    }
  }
}