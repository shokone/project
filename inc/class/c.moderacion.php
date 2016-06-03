<?php
//comprobamos si hemos declarado la contante PS_HEADER
if(!defined('PS_HEADER')){
    exit("No se permite el acceso al script");
}
/**
 * clase psModeracion
 * clase destinada al control de las funciones de la moderacion
 *
 * @name c.moderacion.php
 * @author Iván Martínez Tutor
 */
class psModeracion{

	/**
     * @funcionalidad instanciamos la clase y la guardamos en una variable estática
     * @staticvar psModeracion $instancia instancia de la clase
     * @return \psModeracion devolvemos una instancia de la clase
     */
    public static function &getInstance(){
        static $instancia;
        if(is_null($instancia)){
            $instancia = new psModeracion();
        }
        return $instancia;
    }

    /**
     * @funcionalidad obtenemos los datos de todos los usuarios con rango moderador
     * @return type devolvemos un array con todos los datos obtenidos
     */
    public function getModeradores(){
        global $psDb;
        $consulta = "SELECT user_id, user_name FROM u_miembros WHERE user_rango = :rango ORDER BY user_id";
        $valores = array('rango' => 2);
        return $psDb->resultadoArray($psDb->db_execute($consulta, $valores));
    }

    /**
     * @funcionalidad realizamos la búsqueda de contenido en la web desde la sección moderación
     * @return type devolvemos un array con los datos obtenidos
     */
    public function getContenido(){
        global $psDb, $psUser, $psCore;
        $texto = filter_input(INPUT_GET, 'texto');
        $type = filter_input(INPUT_GET, 't');
        $method = filter_input(INPUT_GET, 'm');
        if(empty($texto)){
            $psCore->redirectTo($psCore->settings['url'].'/moderacion/buscador');
        }
        //creamos las consultas
        //muro
        $consulta = "SELECT m.pub_id, m.p_user, m.p_user_pub, m.p_id, m.p_date, m.p_body, u.user_id, u.user_name FROM u_muro AS m LEFT JOIN u_miembros AS u ON m.p_user_pub = u.user_id WHERE ";
        //usuarios
        $consulta2 = "SELECT user_id, user_name, user_last_ip, user_lastlogin, user_lastactive FROM u_miembros WHERE ";
        //post
        $consulta3 = "SELECT p.post_id, p.post_user, p.post_title, p.post_date, p.post_ip, u.user_name, c.c_nombre, c.c_seo, c.c_img FROM p_posts AS p LEFT JOIN u_miembros AS u ON p.post_user = u.user_id LEFT JOIN p_categorias AS c ON c.cid = p.post_category WHERE ";
        //fotos
        $consulta4 = "SELECT f.foto_id, f.f_title, f.f_user, f.f_date, f.f_ip, u.user_name FROM f_fotos AS f LEFT JOIN u_miembros AS u ON f.f_user = u.user_id WHERE ";
        //comentarios post
        $consulta5 = "SELECT u.user_id, u.user_name, c.* FROM p_comentarios AS c LEFT JOIN u_miembros AS u ON u.user_id = c.c_user WHERE ";
        //comentarios fotos
        $consulta6 = "SELECT u.user_id, u.user_name, f.*, c.* FROM f_comentarios AS c LEFT JOIN u_miembros AS u ON u.user_id = c.c_user LEFT JOIN f_fotos AS f ON f.foto_id = c.c_foto_id WHERE ";
        //comprobamos la forma de búsqueda
        if($metodo == 1){//que contenga parte del contenido
            if($type == 1){
                $consulta .= "m.p_id LIKE '%:texto%'";
                $consulta2 .= "user_last_ip LIKE '%:texto%'";
                $consulta3 .= "p.post_ip LIKE '%:texto%'";
                $valores3 = array('texto' => $texto);
                $consulta4 .= "f.f_ip LIKE '%:texto%'";
                $consulta5 .= "c.c_ip LIKE '%:texto%'";
                $consulta6 .= "c.c_ip LIKE '%:texto%'";
            }else{
                $consulta .= "m.p_body LIKE '%:texto%'";
                $consulta2 .= "user_name LIKE '%:texto%'";
                $consulta3 .= "p.post_title LIKE '%:texto%' OR p.post_body LIKE '%:texto2%'";
                $valores3 = array('texto' => $texto, 'texto2' => $texto);
                $consulta4 .= "f.f_title LIKE '%:texto%' OR f.f_description LIKE '%:texto2%'";
                $consulta5 .= "c.c_user LIKE '%:texto%' OR c.c_body LIKE '%:texto2%'";
                $consulta6 .= "c.c_user LIKE '%:texto%' OR c.c_body LIKE '%:texto2%'";
            }
        }else{//buscar por el texto exacto
            if($type == 1){
                $consulta .= "m.p_id = :texto";
                $consulta2 .= "user_last_ip = :texto";
                $consulta3 .= "p.post_ip = :texto";
                $valores3 = array('texto' => $texto);
                $consulta4 .= "f.f_ip = :texto";
                $consulta5 .= "c.c_ip = :texto";
                $consulta6 .= "c.c_ip = :texto";
            }else{
                $consulta .= "m.p_body = :texto";
                $consulta2 .= "user_name = :texto";
                $consulta3 .= "p.post_title = :texto OR p.post_body = :texto2";
                $valores3 = array('texto' => $texto, 'texto2' => $texto);
                $consulta4 .= "f.f_title = :texto OR f.f_description = :texto2";
                $consulta5 .= "c.c_user = :texto OR c.c_body = :texto2";
                $consulta6 .= "c.c_user = :texto OR c.c_body = :texto2";
            }
        }
        //obtenemos los datos del muro
        $valores = array('texto' => $texto);
        $datos['muro'] = $psDb->resultadoArray($psDb->db_execute($consulta, $valores));
        $datos['m_total'] = count($datos['muro']);
        //obtenemos los datos de los usuarios
        $consulta2 .= " ORDER BY user_lastactive DESC";
        $datos['usuarios'] = $psDb->resultadoArray($psDb->db_execute($consulta2, $valores));
        $datos['u_total'] = count($datos['usuarios']);
        //obtenemos los datos de los post
        $datos['posts'] = $psDb->resultadoArray($psDb->db_execute($consulta3, $valores3));
        $datos['p_total'] = count($datos['posts']);
        //obtenemos los datos de las fotos
        $datos['fotos'] = $psDb->resultadoArray($psDb->db_execute($consulta4, $valores3));
        $datos['f_total'] = count($datos['fotos']);
        //obtenemos los datos de los comentarios de los post
        $datos['p_comentarios'] = $psDb->resultadoArray($psDb->db_execute($consulta5, $valores3));
        $datos['c_p_total'] = count($datos['p_comentarios']);
        //obtenemos los datos de los comentarios de las fotos
        $datos['f_comentarios'] = $psDb->resultadoArray($psDb->db_execute($consulta6, $valores3));
        $datos['c_f_total'] = count($datos['f_comentarios']);
        //ahora guardamos el contenido, el tipo y el método
        $datos['contenido'] = $texto;
        $datos['metodo'] = $method;
        $datos['tipo'] = $type;
        return $datos;
    }

    /**
     * @funcionalidad obtenemos una vista previa del post antes de evaluarlo
     * @param  [type] $pid obtenemos el id del post del que obtener la vista previa
     * @return type devolvemos un array con los datos obtenidos
     */
    public function getVistaPrevia($pid){
        global $psDb, $psCore;
        $consulta = "SELECT post_title, post_body FROM p_posts WHERE post_id = :pid";
        $valores = array('pid' => $pid);
        $datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
        return array('titulo' => $datos['post_title'], 'cuerpo' => $psCore->badWords($datos['post_body']));
    }

    /**
     * @funcionalidad ocultamos a los usuarios el post seleccionado
     * @param  [type] $pid id del post
     * @param  [type] $razon razon por la que se oculta el post
     * @return type devolvemos un string con el resultado obtenido
     */
    public function ocultarPost($pid, $razon){
        global $psDb, $psUser;
        //comprobamos los permisos
        if($psUser->admod || $psUser->permisos['moop']){
            //comprobamos si el post está oculto
            $consulta = "SELECT post_id FROM p_posts WHERE post_id = :pid AND post_status = :status";
            $valores = array('pid' => $pid, 'status' => 3);
            if(!$psDb->db_execute($consulta, $valores)){
                $update = "UPDATE p_posts SET post_status = :status WHERE post_id = :pid";
                $valores2 = array('status' => 3, 'pid' => $pid);
                if($psDb->db_execute($update, $valores2)){
                    $insert = "INSERT INTO w_historial (pofid, action, type, mod, reason, date, mod_ip) VALUES (:id, :action, :type, :mod, :reason, :dat, :ip)";
                    $valores3 = array(
                        'id' => $pid,
                        'action' => 3,
                        'type' => 1,
                        'mod' => $psUser->user_id,
                        'reason' => $razon,
                        'dat' => time(),
                        'ip' => $_SERVER['REMOTE_ADDR']
                    );
                    if($psDb->db_execute($insert, $valores3)){
                        $update2 = "UPDATE w_stats SET stats_posts = stats_posts - :stat WHERE stats_no = :no";
                        $valores4 = array('stat' => 1, 'no' => 1);
                        $psDb->db_execute($update2, $valores4);
                        return '1: El post fue ocultado correctamente';
                    }else{
                        return '0: Ocurri&oacute; un error al guardar los datos en el historial';
                    }
                }else{
                    return '0: Ocurri&oacute; un error al intentar ocular el post';
                }
            }else{
                return '0: Ese post ya est&aacute; oculto';
            }
        }else{
            return '0: No tienes permisos para hacer eso';
        }
    }

    /**
     * @funcionalidad restauramos el post
     * @param  [type] $pid obtenmos el id del post
     * @return type devolvemos un string con el resultado obtenido
     */
    public function rebootPost($pid){
        global $psDb, $psUser;
        //comprobamos los permisos del usuario
        if($psUser->admod || $psUser->permisos['mocdp']){
            //comprobamos si el post está oculto
            $consulta = "SELECT post_id, post_status FROM p_posts WHERE post_id = :pid";
            $valores = array('pid' => $pid);
            $datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
            if($datos['post_status'] == 3){
                $delete = "DELETE FROM w_historial WHERE pofid = :pid AND type = :type AND action = :action";
                $valores2 = array('pid' => $pid, 'type' => 1, 'action' => 3);
                if(!$psDb->db_execute($delete, $valores2)){
                    return '0: Ocurri&oacute; un error al borrar el post del historial';
                }
            }else{
                $delete = "DELETE FROM w_denuncias WHERE obj_id = :pid AND d_type = :type";
                $valores2 = array('pid' => $pid, 'type' => 1);
                if(!$psDb->db_execute($delete, $valores2)){
                    return '0: Ocurri&oacute; un error al borrar las denuncias del post';
                }
            }
            //restauramos el post
            $update = "UPDATE p_posts SET post_status = :status WHERE post_id = :pid";
            $valores3 = array('pid' => $pid);
            if($psDb->db_execute($update, $consulta3)){
                $update2 = "UPDATE w_stats SET stats_posts = stats_posts + :stat WHERE stats_no = :no";
                $valores4 = array('stat' => 1, 'no' => 1);
                $psDb->db_execute($update2, $valores4);
                return '1: El post fue restaurado satisfactoriamente';
            }else{
                return '0: Ocurri&oacute; un error al restaurar el post';
            }
        }else{
            return '0: No tienes permisos para hacer eso';
        }
    }

    /**
     * @funcionalidad borramos las denuncias de la foto
     * @param  [type] $fid obtenmos el id de la foto
     * @return type devolvemos un string con el resultado obtenido
     */
    public function rebootFoto($fid){
        global $psDb, $psUser;
        //comprobamos
        if($psUser->admod || $psUser->permisos['mocdf']){
            $delete = "DELETE FROM w_denuncias WHERE obj_id = :fid AND d_type = :type";
            $valores = array('fid' => $fid, 'type' => 4);
            if($psDb->db_execute($delete, $valores)){
                $update = "UPDATE f_fotos SET f_status = :status WHERE foto_id = :fid";
                $valores2 = array('status' => 0, 'fid' => $fid);
                $psDb->db_execute($update, $valores);
                return '1: Denuncia eliminada correctamente';
            }else{
                return '0: Ocurri&oacute; un error al borrar la denuncia';
            }
        }else{
            return '0: No tienes permisos para hacer eso';
        }
    }

    /**
     * @funcionalidad borramos las denuncias de los mensajes privados
     * @param  [type] $mid obtenemos el id del mensaje
     * @return type devolvemos un string con el resultado obtenido
     */
    public function rebootMensajesPrivados($mid){
        global $psDb, $psUser;
        //comprobamos
        if($psUser->admod || $psUser->permisos['mocdm']){
            $delete = "DELETE FROM w_denuncias WHERE obj_id = :mid AND d_type = :type";
            $valores = array('mid' => $mid, 'type' => 2);
            if($psDb->db_execute($delete, $valores)){
                $update = "UPDATE u_mensajes SET mp_del_to = :to, mp_del_from = :fro WHERE mp_id = :mid";
                $valores2 = array('to' => 0, 'fro' => 0, 'mid' => $mid);
                $psDb->db_execute($update, $valores);
                return '1: Denuncia eliminada correctamente';
            }else{
                return '0: Ocurri&oacute; un error al borrar la denuncia';
            }
        }else{
            return '0: No tienes permisos para hacer eso';
        }
    }

    /**
     * @funcionalidad borramos las denuncias del usuario y quitamos su estado de suspensión de cuenta
     * @param  [type] $uid obtenemos el id del usuario
     * @param  [type] $type obtenemos el tipo de acción
     * @return type devolvemos un string con el resultado obtenido
     */
    public function rebootUser($uid, $type = 'unban'){
        global $psUser, $psDb;
        if($psUser->admod || $psUser->permisos['modu']){
            //borramos las denuncias
            $delete = "DELETE FROM w_denuncias WHERE obj_id = :uid AND d_type = :type";
            $valores = array('uid' => $uid, 'type' => 3);
            $psDb->db_execute($delete, $valores);
            //comprobamos si hay que quitar la suspensión
            if($type == 'unban'){
                //obtenemos los datos del moderador o administrador que lo suspendió
                $consulta = "SELECT susp_mod FROM u_suspension WHERE user_id = :uid";
                $valores2 = array('uid' => $uid);
                $datos = $psDb->db_execute($consulta, $valores2, 'fetch_assoc');
                //comprobamos
                if(empty($datos)){
                    return '0: El usuario no estaba suspendido';
                }
                if($psUser->admod == 1 || $datos['sus_mod'] == $psUser->user_id){
                    $delete2 = "DELETE FROM u_suspension WHERE user_id = :uid";
                    $psDb->db_execute($delete2, $valores2);
                    $update = "UPDATE u_miembros SET user_baneado = :ban WHERE user_id = :uid";
                    $valores3 = array('ban' => 0, 'uid' => $uid);
                    $psDb->db_execute($update, $valores3);
                    $update2 = "UPDATE w_stats SET stats_miembros = stats_miembros + :stat WHERE stats_no = :no";
                    $valores4 = array('stat' => 1, 'no' => 1);
                    $psDb->db_execute($update2, $valores4);
                    return '1: El usuario fue reactivado correctamente';
                }else{
                    return '0: No puedes activar a un usuario que suspendi&oacute; otro moderador';
                }
            }else{
                return '1: Las denuncias del usuario fueran eliminadas satisfactoriamente';
            }
        }else{
            return '0: No tienes permisos para hacer eso';
        }
    }

    /**
     * @funcionalidad borramos el post seleccionado
     * @param  [type] $pid obtenemos el id del post
     * @return type devolvemos un string con el resultado obtenido
     */
    public function borrarPost($pid){
        global $psDb, $psUser, $psMonitor;
        if($psUser->admod || $psUser->permisos['moep']){
            //obtenemos la razón de borrar el post
            $razon = filter_input(INPUT_POST, 'razon');
            //obtenemos la descripción de la razón
            $descripcion = filter_input(INPUT_POST, 'razon_desc');
            //obtenemos la razón para la db
            $rdb = $razon != 13 ? $razon : $descripcion;
            $update = "UPDATE p_posts SET post_status = :status WHERE post_id = :pid";
            $valores = array('status' => 2, 'pid' => $pid);
            if($psDb->db_execute($update, $valores)){
                //borramos las denuncias si las hay
                $delete = "DELETE FROM w_denuncias WHERE obj_id = :obj AND d_type = :type";
                $valores2 = array('obj' => $pid, 'type' => 1);
                $psDb->db_execute($delete, $valores2);
                //obtenemos los datos
                $consulta = "SELECT p.post_user, p.post_title, p.post_body, p.post_tags, p.post_category, u.user_name, u.user_email FROM p_posts AS p LEFT JOIN u_miembros AS u ON p.post_user = u.user_id WHERE p.post_id = :pid";
                $valores3 = array('pid' => $pid);
                $datos = $psDb->db_execute($consulta, $valores3, 'fetch_assoc');
                //si la razón es un valor numérico obtenemos el texto de dicha razón
                if(is_numeric($rdb)){
                    include ('../extra/datos.php');
                    $rdb = $psDenuncias['posts'][$rdb];
                }
                //actualizamos las estadísticas
                $update2 = "UPDATE w_stats SET stats_posts = stats_posts - :stat WHERE stats_no = :no";
                $valores4 = array('stat' => 1, 'no' => 1);
                $psDb->db_execute($update2, $valores4);
                //comprobamos si hemos activado la casilla de mandar al borrador del usuario
                if($_POST['send_b'] == 'yes'){
                    //añadimos el post a los borradores del usuario
                    $insert = "INSERT INTO p_borradores (b_user, b_date, b_title, b_body, b_tags, b_category, b_status, b_causa) VALUES (:user, :dat, :title, :body, :tags, :cat, :status, :causa)";
                    $valores5 = array(
                        'user' => $datos['post_user'],
                        'dat' => time(),
                        'title' => $datos['post_title'],
                        'body' => $datos['post_body'],
                        'tags' => $datos['post_tags'],
                        'cat' => $datos['post_category'],
                        'status' => 1,
                        'causa' => $rdb
                    );
                    $psDb->db_execute($insert, $valores5);
                }
                //mandamos el aviso al usuario
                $aviso = 'Hola <strong>' . $datos['user_name'] . "</strong><br> Lamentamos decirle que su post titulado <b>" .
                    $datos['post_title'] . "</b> ha sido eliminado.<br> Causa: <strong>" . $rdb .
                    '</strong><br>Te recomendamos leer el <a href="' . $psCore->settings['url'] .
                    '/pages/protocolo">Protocolo</a> para evitar futuras sanciones.<br> Muchas gracias por entenderlo!';
                $psMonitor->setAviso($datos['post_user'], 'Post eliminado', $aviso, 1);
                //añadimos la acción al historial
                $historial = $this->setHistorial('borrar', 'post', $pid);
                if($historial == true){
                    return '1: El post fue eliminado satisfactoriamente';
                }
            }else{
                return '0: Ocurri&oacute; un error al borrar el post';
            }
        }else{
            return '0: No tienes permisos para hacer eso';
        }
    }

    /**
     * @funcionalidad borramos la foto seleccionada
     * @param  [type] $fid obtenemos el id de la foto
     * @return type devolvemos un string con el resultado obtenido
     */
    public function borrarFoto($fid){
        global $psDb, $psUser, $psCore, $psMonitor;
        if($psUser->admod || $psUser->permisos['moadf'] || $psUser->permisos['moef']){
            //obtenemos la razón de borrar el post
            $razon = filter_input(INPUT_POST, 'razon');
            //obtenemos la descripción de la razón
            $descripcion = filter_input(INPUT_POST, 'razon_desc');
            //obtenemos la razón para la db
            $rdb = $razon != 8 ? $razon : $descripcion;
            $update = "UPDATE f_fotos SET f_status = :status WHERE foto_id = :fid";
            $valores = array('status' => 2, 'fid' => $fid);
            if($psDb->db_execute($update, $valores)){
                //obtenemos los datos
                $consulta = "SELECT f.f_user, f.f_title, u.user_name FROM f_fotos AS f LEFT JOIN u_miembros AS u ON f.f_user = u.user_id WHERE f.foto_id = :fid";
                $valores2 = array('fid' => $fid);
                $datos = $psDb->db_execute($consulta, $valores2, 'fetch_assoc');
                //actualizamos las estadísticas
                $update2 = "UPDATE w_stats SET stats_fotos = stats_fotos - :stat WHERE stats_no = :no";
                $valores3 = array('stat' => 1, 'no' => 1);
                //obtenemos el texto de la denuncia si su valor es numérico
                if(is_numeric($rdb)){
                    include '../extra/datos.php';
                    $rdb = $psDenuncias['fotos'][$rdb];
                }
                //mandamos un aviso al usuario
                $aviso = 'Hola <strong>' . $datos['user_name'] . '</strong><br> Lamentamos decirle que su foto titulada <strong>' .
                    $datos['f_title'] . '</strong> ha sido eliminada.<br> Causa: <strong>' . $rdb . '</b><br> Te recomendamos leer el <a href="' .
                    $psCore->settings['url'] . '/pages/protocolo/">Protocolo</a> para evitar futuras sanciones.<br> Muchas gracias por entenderlo!';
                $psMonitor->setAviso($datos['f_user'], 'Foto eliminada', $aviso, 1);
                //eliminamos las denuncias
                $delete = "DELETE FROM w_denuncias WHERE obj_id = :obj AND d_type = :type";
                $valores4 = array('obj' => $fid, 'type' => 4);
                $psDb->db_execute($delete, $valores4);
                //actualizamos el historial
                $this->setHistorial('borrar', 'foto', $fid);
            }else{
                return '0: Ocurrió un error al intentar actualizar el estado de la foto';
            }
        }else{
            return '0: No tienes permisos para hacer eso';
        }
    }

    /**
     * @funcionalidad eliminamos el mensaje
     * @param  [type] $mid obtenemos el id del mensaje
     * @return type devolvemos un string con el resultado obtenido
     */
    public function borrarMensajePrivado($mid){
        global $psDb, $psUser, $psCore, $psMonitor;
        if($psUser->admod || $psUser->permisos['moadm']){
            //obtenemos los datos
            $consulta = "SELECT m.mp_from, m.mp_subject, u.user_name FROM u_mensajes AS m LEFT JOIN u_miembros AS u ON m.mp_from = u.user_id WHERE m.mp_id = :mid";
            $valores = array('mid' => $mid);
            if($psDb->db_execute($consulta, $valores)){
                $datos = $psDb->resultadoArray($psDb->db_execute($consulta, $valores));
                //mandamos el aviso
                $aviso = 'Hola <strong>' . $datos['user_name'] . '</strong><br> Lamentamos decirle que su mensaje privado <strong>' .
                    $datos['mp_subject'] . '</strong> ha sido eliminado.<br> Te recomendamos leer el <a href="' .
                    $psCore->settings['url'] . '/pages/protocolo/">Protocolo</a> para evitar futuras sanciones.<br> Muchas gracias por entenderlo!';
                $psMonitor->setAviso($datos['mp_from'], 'Mensaje eliminado', $aviso, 1);
                //eliminamos las denuncias
                $delete = "DELETE FROM w_denuncias WHERE obj_id = :mid AND d_type = :type";
                $valores2 = array('mid' => $mid, 'type' => 2);
                $psDb->db_execute($delete, $valores2);
                //eliminamos los mensajes del panel del usuario
                $delete2 = "DELETE FROM u_respuestas WHERE mp_id = :mid";
                $delete3 = "DELETE FROM u_mensajes WHERE mp_id = :mid";
                $psDb->db_execute($delete2, $valores);
                $psDb->db_execute($delete3, $valores);
                return '1: El mensaje fue eliminado correctamente';
            }else{
                return '0: Ocurri&oacute; un error al borrar el mensaje';
            }
        }else{
            return '0: No tienes permisos para hacer eso';
        }
    }

    /**
     * @funcionalidad obtenemos la papelera de reciclaje de posts
     * @return type devolvemos un array con los datos obtenidos
     */
    public function getPostPapelera(){
        global $psDb, $psUser, $psCore;
        //obtenemos el máximo a mostrar por página
        $max = 25;
        //obtenemos el límite
        $limite = $psCore->setPagLimite($max, true);
        //obtenemos el total de post para obtener las páginas
        $consulta = "SELECT COUNT(*) FROM p_posts AS p LEFT JOIN u_miembros AS u ON u.user_id = p.post_user LEFT JOIN w_historial AS h ON h.pofid = p.post_id LEFT JOIN p_categorias AS c ON c.cid = p.post_category WHERE h.type = :type AND h.action = :action";
        $valores = array('type' => 1, 'action' => 2);
        list($total) = $psDb->db_execute($consulta, $valores, 'fetch_num');
        $datos['pages'] = $psCore->inicioPages($psCore->settings['url'].'/moderacion/pospelera?', $_GET['start'], $total, $max);
        //obtenemos los datos
        $consulta2 = "SELECT u.user_id, u.user_name, h.*, p.post_id, p.post_title, c.c_seo, c.c_nombre FROM p_posts AS p LEFT JOIN u_miembros AS u ON u.user_id = p.post_user LEFT JOIN w_historial AS h ON h.pofid = p.post_id LEFT JOIN p_categorias AS c ON c.cid = p.post_category WHERE h.type = :type AND h.action = :action AND p.post_status = :status LIMIT :limite";
        $valores2 = array('type' => 1, 'action' => 2, 'status' => 2, 'limite' => $limite);
        //obtenemos los textos de las denuncias
        include '../extra/datos.php';
        while($row = $psDb->db_execute($consulta2, $valores2, 'fetch_assoc')){
            $row['mod_name'] = $psUser->getUserName($row['mod']);
            $row['reason'] = is_numeric($row['reason']) ? $psDenuncias['posts'][$row['reason']] : $row['reason'];
            $datos['data'][] = $row;
        }
        return $datos;
    }

    /**
     * @funcionalidad obtenemos la papelera de reciclaje de fotos
     * @return type devolvemos un array con los datos obtenidos
     */
    public function getFotoPapelera(){
        global $psDb, $psUser, $psCore;
        //obtenemos el máximo a mostrar por página
        $max = 25;
        //obtenemos el límite
        $limite = $psCore->setPagLimite($max, true);
        //obtenemos el total de fotos para obtener las páginas
        $consulta = "SELECT COUNT(*) FROM f_fotos AS f LEFT JOIN u_miembros AS u ON u.user_id = f.f_user LEFT JOIN w_historial AS h ON h.pofid = f.foto_id  WHERE h.type = :type AND h.action = :action AND f.f_status = :status";
        $valores = array('type' => 2, 'action' => 2, 'status' => 2);
        list($total) = $psDb->db_execute($consulta, $valores, 'fetch_num');
        $datos['pages'] = $psCore->inicioPages($psCore->settings['url'].'/moderacion/fopelera?', $_GET['start'], $total, $max);
        //obtenemos los datos
        $consulta2 = "SELECT u.user_id, u.user_name, h.*, f.foto_id, f.f_title, f.f_user FROM f_fotos AS f LEFT JOIN u_miembros AS u ON u.user_id = f.f_user LEFT JOIN w_historial AS h ON h.pofid = p.post_id  WHERE h.type = :type AND h.action = :action AND f.f_status = :status LIMIT :limite";
        $valores2 = array('type' => 2, 'action' => 2, 'status' => 2, 'limite' => $limite);
        //obtenemos los textos de las denuncias
        include '../extra/datos.php';
        while($row = $psDb->db_execute($consulta2, $valores2, 'fetch_assoc')){
            $row['mod_name'] = $psUser->getUserName($row['mod']);
            $row['reason'] = is_numeric($row['reason']) ? $psDenuncias['fotos'][$row['reason']] : $row['reason'];
            $datos['data'][] = $row;
        }
        return $datos;
    }


    /**
     * @funcionalidad obtenemos la denuncia del tipo seleccionado
     * @param  [type] $type tipo de denuncia, por defecto será posts
     * @return type devolvemos un array con los datos obtenidos
     */
    public function getDenuncia($type = 'posts'){
        global $psCore, $psDb;
        $objeto = htmlspecialchars(filter_input(INPUT_GET, 'obj'));
        //comprobamos el tipo de denuncia
        switch($type){
            //posts
            case 'posts':
                $tipo = 1;
                $consulta = "SELECT p.post_id, p.post_title, p.post_status, c.c_nombre, c.c_seo, c.c_img, u.user_name FROM p_posts AS p LEFT JOIN p_categorias AS c ON p.post_category = c.cid LEFT JOIN u_miembros AS u ON p.post_user = u.user_id WHERE p.post_id = :pid";
                $valores = array('pid' => $objeto);
                $datos['data'] = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
                break;
            //mensajes privados
            case 'mps':
                $tipo = 2;
                $consulta = "SELECT user_id, user_name FROM u_miembros WHERE user_id = :uid";
                $valores = array('uid' => $objeto);
                $datos['data'] = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
                break;
            //users
            case 'users':
                $tipo = 3;
                $consulta = "SELECT user_id, user_name FROM u_miembros WHERE user_id = :uid";
                $valores = array('uid' => $objeto);
                $datos['data'] = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
                break;
            //fotos
            case 'fotos':
                $tipo = 4;
                $consulta = "SELECT f.foto_id, f.f_title, f.f_status, u.user_name FROM f_fotos AS f LEFT JOIN u_miembros AS u ON f.f_user = u.user_id WHERE f.foto_id = :fid";
                $valores = array('fid' => $objeto);
                $datos['data'] = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
                break;
        }
        //ahora obtenemos las denuncias
        $consulta2 = "SELECT d.*, u.user_id, u.user_name FROM w_denuncias AS d LEFT JOIN u_miembros AS u ON d.d_user = u.user_id WHERE d.obj_id = :obj AND d.d_type = :tipo";
        $valores2 = array('obj' => $objeto, 'tipo' => $tipo);
        $datos['denuncia'] = $psDb->resultadoArray($psDb->db_execute($consulta2, $valores2));
        return $datos;
    }

    /**
     * @funcionalidad obtenemos las denuncias del tipo obtenido por parámetro
     * @param  [type] $type tipo de denuncia, por defecto será posts
     * @return type devolvemos un array con los datos obtenidos
     */
    public function getDenuncias($type = 'posts'){
        //obtenemos la denuncia según el tipo
        global $psDb;
        switch($type){
            //posts
            case 'posts':
                $consulta = "SELECT d.*, SUM(d.d_total) AS total, p.post_id, p.post_title, p.post_status, c.c_nombre, c.c_seo, c.c_img FROM w_denuncias AS d LEFT JOIN p_posts AS p ON d.obj_id = p.post_id LEFT JOIN p_categorias AS c ON p.post_category = c.cid WHERE d.d_type = :type AND p.post_status < :status GROUP BY d.obj_id ORDER BY total DESC, d.d_date DESC";
                $valores = array('type' => 1, 'status' => 2);
                $datos = $psDb->resultadoArray($psDb->db_execute($consulta, $valores));
                break;
            //mensaje privados
            case 'mps':
                $consulta = "SELECT d.*, m.mp_id, m.mp_to, m.mp_from, m.mp_subject, m.mp_preview, m.mp_date FROM w_denuncias AS d LEFT JOIN u_mensajes AS m ON d.obj_id = m.mp_id WHERE d_type = :type GROUP BY d.obj_id ORDER BY d.d_date DESC";
                $valores = array('type' => 2);
                $datos = $psDb->resultadoArray($psDb->db_execute($consulta, $valores));
                break;
            //users
            case 'users':
                $consulta = "SELECT d.*, SUM(d.d_total) AS total, u.user_name FROM w_denuncias AS d LEFT JOIN u_miembros AS u ON d.obj_id = u.user_id WHERE d_type = :type AND u.user_baneado = :ban GROUP BY d.obj_id ORDER BY total, d.d_date DESC";
                $valores = array('type' => 3, 'ban' => 0);
                $datos = $psDb->resultadoArray($psDb->db_execute($consulta, $valores));
                break;
            //fotos
            case 'fotos':
                $consulta = "SELECT d.*, SUM(d.d_total) AS total, f.foto_id, f.f_title, f.f_status, u.user_id, u.user_name FROM w_denuncias AS d LEFT JOIN f_fotos AS f ON d.obj_id = f.foto_id LEFT JOIN u_miembros AS u ON f.f_user = u.user_id  WHERE d_type = :type && f.f_status < :status GROUP BY d.obj_id ORDER BY total DESC, d.d_date DESC";
                $valores = array('type' => 4, 'status' => 2);
                $datos = $psDb->resultadoArray($psDb->db_execute($consulta, $valores));
                break;

        }
        return $datos;
    }

    /**
     * @funcionalidad obtenemos el historial de acciones del moderador
     * @param  [type] $type obtenemos el tipo de accion a comprobar (post o foto)
     * @return type devolvemos un array con los datos obtenidos
     */
    public function getHistorial($type){
        global $psUser, $psDb, $psCore;
        if($type == 1){
            $consulta = "SELECT u.user_id, u.user_name, h.*, p.post_id, p.post_title FROM p_posts AS p LEFT JOIN u_miembros AS u ON u.user_id = p.post_user LEFT JOIN w_historial AS h ON h.pofid = p.post_id WHERE h.type = :type ORDER BY h.id DESC LIMIT :limite";
            $valores = array('type' => 1, 'limite' => 25);
        }else{
            $consulta = "SELECT u.user_id, u.user_name, h.*, f.foto_id, f.f_title, f.f_user FROM f_fotos AS f LEFT JOIN u_miembros AS u ON u.user_id = f.f_user LEFT JOIN w_historial AS h ON h.pofid = f.foto_id WHERE h.type = :type ORDER BY h.id DESC LIMIT :limite";
            $valores = array('type' => 2, 'limite' => 25);
        }
        //obtenemos los textos de las denuncias
        include '../extra/datos.php';
        while($row = $psDb->db_execute($consulta, $valores, 'fetch_assoc')){
            $row['mod_name'] = $psUser->getUserName($row['mod']);
            $row['reason'] = is_numeric($row['reason']) ? $psDenuncias['posts'][$row['reason']] : $row['reason'];
            $datos[] = $row;
        }
        return $datos;
    }

    /**
     * @funcionalidad añadimos una nueva acción en el historial
     * @param  [type] $action acción a realizar
     * @param  [type] $type tipo a comprobar (post o foto)
     * @param  [type] $datos datos del post o de la foto
     * @return type devolvemos un valor booleano si todo ha salido correcto
     */
	public function setHistorial($action, $type, $datos){
        global $psUser, $psDb, $psCore, $psMonitor;
        //comprobamos el tipo de historial
        if($type == 'post'){
            //comprobamos la acción
            switch($action){
                case 'editar':
                    $aviso = 'Hola <strong>' . $psUser->getUserName($datos['autor']) . '</strong><br> Te informamos que tu post <strong>' .
                        $datos['title'] . '</b> ha sido editado por <a href="#" class="hovercard" uid="' .
                        $psUser->user_id . '">' . $psUser->nick . '</a><br> Causa: <strong>' . $datos['razon'] .
                        '</strong><br> Te recomendamos leer el <a href=\"' . $psCore->settings['url'] .
                        '/pages/protocolo/">Protocolo</a> para evitar futuras sanciones.<br> Muchas gracias por entenderlo!';
                    $psMonitor->setAviso($datos['autor'], 'Post editado', $aviso, 2);
                    if (!filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP)){
                        return '0: Su ip no pudo validarse correctamente';
                    }
                    $consulta = "INSERT INTO w_historial (pofid, action, type, mod, reason, date, mod_ip) VALUES (:id, :action, :type, :mod, :reason, :dat, :ip)";
                    $valores = array(
                        'id' => $datos['post_id'],
                        'action' => 1,
                        'type' => 1,
                        'mod' => $psUser->user_id,
                        'reason' => $datos['razon'],
                        'dat' => time(),
                        'ip' => $_SERVER['REMOTE_ADDR']
                    );
                    $psDb->db_execute($consulta, $valores);
                    return true;
                case 'borrar':
                    //obtenemos los datos de la razon
                    $razon = $_POST['razon'] != 13 ? filter_input(INPUT_POST, 'razon') : filter_input(INPUT_POST, 'razon_desc');
                    //obtenemos los datos
                    $consulta = "SELECT post_id, post_title, post_body, post_user, post_category FROM p_posts WHERE post_id = :pid";
                    $valores = array('pid' => $datos);
                    $borrar = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
                    //comprobamos e insertamos los datos
                    if($borrar['post_user'] != $psUser->user_id){
                        $insert = "INSERT INTO w_historial (pofid, action, type, mod, reason, date, mod_ip) VALUES (:id, :action, :type, :mod, :reason, :dat, :ip)";
                        $valores2 = array(
                            'id' => $borrar['post_id'],
                            'action' => 2,
                            'type' => 1,
                            'mod' => $psUser->user_id,
                            'reason' => $razon,
                            'dat' => time(),
                            'ip' => $_SERVER['REMOTE_ADDR']
                        );
                        $psDb->db_execute($insert, $valores2);
                    }
                    return true;
            }
        }elseif($type == 'foto'){
            //obtenemos los datos
            $consulta = "SELECT foto_id, f_title, f_description, f_user FROM f_fotos WHERE foto_id = :fid";
            $valores = array('fid' => $datos);
            $foto = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
            switch($action){
                case 'borrar':
                    //obtenemos la razón de borrar la foto
                    $razon = $_POST['razon'] != 8 ? filter_input(INPUT_POST, 'razon') : filter_input(INPUT_POST, 'razon_desc');
                    //insertamos los  datos en el historial
                    $insert = "INSERT INTO w_historial (pofid, action, type, mod, reason, date, mod_ip) VALUES (:id, :action, :type, :mod, :reason, :dat, :ip)";
                    $valores2 = array(
                        'id' => $foto['foto_id'],
                        'action' => 2,
                        'type' => 2,
                        'mod' => $psUser->user_id,
                        'reason' => $razon,
                        'dat' => time(),
                        'ip' => $_SERVER['REMOTE_ADDR']
                    );
                    $psDb->db_execute($insert, $valores2);
                    return true;
            }
        }
    }

    /**
     * @funcionalidad cambiamos un post a sticky(patrocinado) y viceversa
     * @param  [type] $pid obtenemos el id del post
     * @return type devolvemos un string con el resultado obtenido
     */
    public function setSticky($pid){
        global $psDb, $psUser;
        if($psUser->admod || $psUser->permisos['most']){
            //obtenemos los datos
            $consulta = "SELECT post_sticky FROM p_posts WHERE post_id = :pid";
            $valores = array('pid' => $pid);
            $datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
            //comprobamos el estado del sticky
            if($datos['post_sticky'] == 1){
                $update = "UPDATE p_posts SET post_sticky == :sticky WHERE post_id = :pid";
                $valores2 = array('sticky' => 0, 'pid' => $pid);
                $psDb->db_execute($update, $valores2);
                return '1: El post fue eliminado de los patrocinados en la home';
            }else{
                $update = "UPDATE p_posts SET post_sticky == :sticky WHERE post_id = :pid";
                $valores2 = array('sticky' => 1, 'pid' => $pid);
                $psDb->db_execute($update, $valores2);
                return '1: El post fue puesto como patrocinado en la home';
            }
        }else{
            return '0: No tienes permisos para hacer eso';
        }
    }

    /**
     * @funcionalidad cambiamos el estado de un post de abierto a cerrado y viceversa
     * @param  [type] $pid obtenemos el id del post
     * @return type devolvemos un string con el resultado obtenido
     */
    public function setOpenClosedPost($pid){
        global $psDb, $psUser;
        if($psUser->admod || $psUser->permisos['most']){
            //obtenemos los datos
            $consulta = "SELECT post_block_comments FROM p_posts WHERE post_id = :pid";
            $valores = array('pid' => $pid);
            $datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
            //comprobamos el estado del sticky
            if($datos['post_block_comments'] == 1){
                $update = "UPDATE p_posts SET post_block_comments == :block WHERE post_id = :pid";
                $valores2 = array('block' => 0, 'pid' => $pid);
                $psDb->db_execute($update, $valores2);
                return '1: El post fue abierto de nuevo';
            }else{
                $update = "UPDATE p_posts SET post_block_comments == :block WHERE post_id = :pid";
                $valores2 = array('block' => 1, 'pid' => $pid);
                $psDb->db_execute($update, $valores2);
                return '1: El post fue cerrado de nuevo';
            }
        }else{
            return '0: No tienes permisos para hacer eso';
        }
    }

    /**
     * @funcionalidad obtenemos los usuarios baneados
     * @return type devolvemos un array con los datos obtenidos
     */
    public function getUserSuspendidos(){
        global $psDb, $psUser, $psCore;
        if($psUser->admod || $psUser->permisos['movub']){
            //obtenemos el máximo a mostrar por página
            $max = 25;
            $limite = $psCore->setPagLimite($max, true);
            //obtenemos a partir de que ordenar
            if ($_GET['order'] == 'inicio'){
                $orden = 's.susp_date';
            }elseif ($_GET['order'] == 'fin'){
                $orden = 's.susp_termina';
            }elseif ($_GET['order'] == 'mod'){
                $orden = 's.susp_mod';
            }else{
                $orden = 's.susp_id';
            }
            //obtenemos la forma de ordenar
            if ($_GET['m'] == 'a'){
                $method = 'ASC';
            }else{
                $method = 'DESC';
            }
            //obtenemos los datos
            $consulta = "SELECT s.*, u.user_name FROM u_suspension AS s LEFT JOIN u_miembros AS u ON s.user_id = u.user_id ORDER BY :order :method LIMIT :limite";
            $valores = array('order' => $orden, 'method' => $method, 'limite' => $limite);
            $datos['baneados'] = $psDb->resultadoArray($psDb->db_execute($consulta, $valores));
            //obtenemos las páginas
            $consulta2 = "SELECT COUNT(*) FROM u_suspension WHERE user_id > :uid";
            $valores2 = array('uid' => 0);
            list($total) = $psDb->db_execute($consulta2, $valores2, 'fetch_num');
            $datos['pages'] = $psCore->inicioPages($psCore->settings['url'].'/moderacion/banusers?order='.$_GET['order'].'&m='.$_GET['m'], $_GET['start'], $total, $max);
        }else{
            return '0: No tienes permisos para hacer eso';
        }
        return $datos;
    }

    /**
     * @funcionalidad baneamos al usuario seleccionado
     * @param  [type] $uid obtenemos el id del usuario
     * @return type devolvemos un string con el resultado obtenido
     */
    public function banearUser($uid){
        global $psDb, $psUser, $psCore;
        //obtenemos los datos del formulario
        $time = filter_input(INPUT_POST, 'b_time');
        $cant = empty($_POST['b_cant']) ? 1 : filter_input(INPUT_POST, 'b_cant');
        $causa = filter_input(INPUT_POST, 'b_causa');
        $tiempos = array(0, 1, 3600, 86400);//tiempos ahora, 1 segundo, 1 hora, 1 día
        //comprobamos el horario
        if($cant < 1 || !is_numeric($cant)){
            return '0: Debe introducir una cantidad de al menos 60 minutos';
        }
        //comprobamos los rangos
        $consulta = "SELECT user_rango, user_baneado FROM u_miembros WHERE user_id = :uid";
        $valores = array('uid' => $uid);
        $datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
        //comprobamos si el usuario está baneado
        if($datos['user_baneado'] == 0){
            //comprobamos a quien queremos suspender
            //el rango debe ser inferior al mío
            if(($psUser->admod < $datos['user_rango'] && $psUser->admod > 0) || $psUser->permisos['mosu'] && $datos['user_rango'] >= 2){
                //obtenemos el tiempo
                $acaba = $time >= 2 ? (time() + ($cant * $tiempos[$time])) : $cant * $tiempos[$time];
                //comprobamos la ip
                if(!filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_URL)){
                    return '0: Su ip no pudo validarse correctamente';
                }
                //actualizamos datos
                $update = "UPDATE u_miembros SET user_baneado = :ban WHERE user_id = :uid";
                $valores2 = array('ban' => 1, 'uid' => $uid);
                $psDb->db_execute($update, $valores2);
                //ahora insertamos los nuevos datos
                $insert = "INSERT INTO u_suspension (user_id, susp_causa, susp_date, susp_termina, susp_mod, susp_ip) VALUES (:uid, :causa, :dat, :termina, :mod, :ip)";
                $valores3 = array(
                    'uid' => $uid,
                    'causa' => $causa,
                    'dat' => time(),
                    'termina' => $acaba,
                    'mod' => $psUser->user_id,
                    'ip' => $_SERVER['REMOTE_ADDR']
                );
                if($psDb->db_execute($insert, $valores3)){
                    //borramos las denuncias
                    $delete = "DELETE FROM w_denuncias WHERE obj_id = :uid AND d_type = :type";
                    $valores4 = array('uid' => $uid, 'type' => 3);
                    $psDb->db_execute($delete, $valores4);
                    //actualizamos las estadísticas
                    $update2 = "UPDATE w_stats SET stats_miembros = stats_miembros - :stat WHERE stats_no = :no";
                    $valores5 = array('stat' => 1, 'no' => 1);
                    $psDb->db_execute($update2, $valores5);
                    //obtenemos el tiempo que durará la suspensión
                    if($time < 2){
                        $tiempo = $time == 0 ? 'indefinidamente' : 'permanentemente';
                    }else{
                        $tiempo = 'hasta el <strong>'.date('d/m/Y H:i:s', $acaba);
                    }
                    return '1: El usuario ha sido suspendido <strong>'.$tiempo.'</strong>';
                }else{
                    return '0: Ocurri&oacute; un error al intentar suspender al usuario';
                }
            }else{
                return '0: S&oacute;lo puedes suspender a usuarios con un rango inferior al tuyo';
            }
        }else{
            return '0: Este usuario ya est&aacute; suspendido';
        }
    }

    /**
     * @funcionalidad obtenemos un listado con los posts
     * @return type devolvemos un array con los datos obtenidos
     */
    public function getModerarPost(){
        global $psUser, $psDb, $psCore;
        //obtenemos el máximo a mostrar por página
        $max = 25;
        $limite = $psCore->setPagLimite($max, true);
        //obtenemos el total de comentarios para obtener el total de páginas
        $consulta = "SELECT COUNT(*) FROM p_posts AS p LEFT JOIN u_miembros AS u ON u.user_id = p.post_user WHERE p.post_status = :status";
        $valores = array('status' => 3);
        list($total) = $psDb->db_execute($consulta, $valores, 'fetch_num');
        $datos['pages'] = $psCore->inicioPages($psCore->settings['url'].'/moderacion/revposts?', $_GET['start'], $total, $max);
        //obtenemos los datos
        $consulta2 = "SELECT u.user_id, u.user_name, h.*, p.post_id, p.post_title, c.c_seo, c.c_nombre FROM p_posts AS p LEFT JOIN w_historial AS h ON h.pofid = p.post_id LEFT JOIN p_categorias AS c ON c.cid = p.post_category LEFT JOIN u_miembros AS u ON u.user_id = h.mod WHERE h.type = :type AND h.action = :action AND p.post_status = :status LIMIT :limite";
        $valores2 = array('type' => 1, 'action' => 3, 'status' => 3, 'limite' => $limite);
        $datos['data'] = $psDb->resultadoArray($psDb->db_execute($consulta2, $valores2));
        return $datos;
    }

    /**
     * @funcionalidad obtenemos un listado con los comentarios
     * @return type devolvemos un array con los datos obtenidos
     */
    public function getModerarComentarios(){
        global $psUser, $psDb, $psCore;
        //obtenemos el máximo a mostrar por página
        $max = 25;
        $limite = $psCore->setPagLimite($max, true);
        //obtenemos el total de comentarios para obtener el total de páginas
        $consulta = "SELECT COUNT(*) FROM p_comentarios AS c LEFT JOIN u_miembros AS u ON u.user_id = c.c_user WHERE c.c_status = :status";
        $valores = array('status' => 1);
        list($total) = $psDb->db_execute($consulta, $valores, 'fetch_num');
        $datos['pages'] = $psCore->inicioPages($psCore->settings['url'].'/moderacion/comentarios?', $_GET['start'], $total, $max);
        //obtenemos los datos
        $consulta2 = "SELECT u.user_id, u.user_name, c.cid, c.c_user, c.c_post_id, c.c_date, c.c_body, c.c_ip, p.post_id, p.post_title, ca.c_seo, ca.c_nombre FROM p_comentarios AS c LEFT JOIN p_posts AS p ON c.c_post_id = p.post_id LEFT JOIN p_categorias AS ca ON ca.cid = p.post_category LEFT JOIN u_miembros AS u ON u.user_id = c.c_user WHERE c.c_status = :status ORDER BY c.c_date DESC LIMIT :limite";
        $valores2 = array('status' => 1, 'limite' => $limite);
        $datos['data'] = $psDb->resultadoArray($psDb->db_execute($consulta2, $valores2));
        return $datos;
    }
}
