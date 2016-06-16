<?php
//comprobamos si hemos declarado la contante PS_HEADER
if(!defined('PS_HEADER')){
    exit("No se permite el acceso al script");
}
/**
 * clase psPosts
 * clase destinada al control de las funciones de los posts
 *
 * @name c.posts.php
 * @author Iván Martínez Tutor
 */
class psPosts{
    /**
     * @funcionalidad instanciamos la clase psPosts
     * @return \psPosts
     */
    public static function &getInstance(){
        $instancia;
        if(is_null($instancia)){
            $instancia = new psPosts();
        }
        return $instancia;
    }

    /**
     * @funcionalidad obtenemos todos los datos necesarios del post
     * @return type devolvemos un array con todos los datos obtenidos
     */
    function getPost(){
        global $psDb, $psCore, $psUser;
        $pid = filter_input(INPUT_GET, 'post_id');
        //comprobamos si el post existe
        if(empty($pid)){
            return array('deleted', 'Lo sentimos. Este post no existe o fue eliminado.');
        }
        //damos la medalla si procede
        $this->darMedalla($pid);
        //obtenemos los datos del post
        $consulta = "SELECT p.*, pu.*, u.user_id FROM p_posts AS p LEFT JOIN u_miembros AS u ON u.user_id = p.post_user LEFT JOIN u_perfil AS pu ON p.post_user = pu.user_id WHERE post_id = :pid ";
        $valores['pid'] = $pid;
        if($psUser->admod && $psCore->settings['c_see_mod'] == 1){
        }else{
            $consulta .= 'AND u.user_activo = :activo';
            $valores['activo'] = 1;
            $consulta .= ' AND u.user_baneado = :ban';
            $valores['ban'] = 0;
        }
        $post = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
        //comprobamos que los datos sean correctos
        if(empty($post['post_id'])){
            $consulta2 = "SELECT b_title FROM p_borradores WHERE b_post_id = :pid";
            $valores2 = array('pid' => $pid);
            $borrador = $psDb->db_execute($consulta2, $valores2, 'fetch_assoc');
            if(!empty($borrador['b_title'])){
                return array('deleted', 'Lo sentimos. Este post no existe o fue eliminado.');
            }else{
                return array('deleted', 'Lo sentimos. Este post fue eliminado.');
            }
        }else if($post['post_status'] == 1 && (!$psUser->admod && $psUser->permisos['moacp'] == false)){
            return array('denunciado', 'Lo sentimos. Este post se encuentra en revisi&oacute;n por acumulaci&oacute;n de denuncias.');
        }else if($post['post_status'] == 2 && (!$psUser->admod && $psUser->permisos['morp'] == false)){
            return array('deleted', 'Lo sentimos. Este post fue eliminado.');
        }else if($post['post_status'] == 3 && (!$psUser->admod && $psUser->permisos['mocp'] == false)){
            return array('denunciado', 'Lo sentimos. Este post se encuentra en revisi&oacute;n a la espera de su publicaci&oacute;n.');
        }else if(!empty($post['post_private']) && empty($psUser->member)){
            return array('privado', $post['post_title']);
        }
        //obtenemos las estadísticas y las actualizamos
        if($post['post_cache'] < time() - ($psCore->settings['c_stats_cache'] * 60)){
            //comentarios
            $consulta3 = "SELECT COUNT(u.user_name) AS n FROM u_miembros AS u LEFT JOIN p_comentarios AS c ON u.user_id = c.c_user WHERE c.c_post_id = :pid AND c.c_status = :status AND u.user_activo = :activo AND u.user_baneado = :ban";
            $valores3 = array(
                'pid' => $pid,
                'status' => 0,
                'activo' => 1,
                'ban' => 0
            );
            $query3 = $psDb->db_execute($consulta3, $valores3, 'fetch_num');
            $post['post_comments'] = $query3[0];
            //seguidores
            $consulta4 = "SELECT COUNT(u.user_name) AS s FROM u_miembros AS u LEFT JOIN u_follows AS f ON u.user_id = f.f_user WHERE f.f_type = :type AND f.f_id = :fid AND u.user_activo = :activo AND u.user_baneado = :ban";
            $valores4 = array(
                'type' => 2,
                'fid' => $pid,
                'activo' => 1,
                'ban' => 0
            );
            $query4 = $psDb->db_execute($consulta4, $valores4, 'fetch_num');
            $post['post_seguidores'] = $query4[0];
            //veces compartido
            $consulta5 = "SELECT COUNT(follow_id) AS m FROM u_follows WHERE f_type = :type AND f_id = :fid";
            $valores5 = array('type' => 3, 'fid' => $pid);
            $query5 = $psDb->db_execute($consulta5, $valores5, 'fetch_num');
            $post['post_shared'] = $query5[0];
            //favoritos
            $consulta6 = "SELECT COUNT(fav_id) AS f FROM p_favoritos WHERE fav_post_id = :fid";
            $valores6 = array('fid' => $pid);
            $query6 = $psDb->db_execute($consulta6, $valores6, 'fetch_num');
            $post['post_favoritos'] = $query6[0];
            //ahora actualizamos las estadísticas
            $consulta7 = "UPDATE p_posts SET post_comments = :com, post_seguidores = :seg, post_shared = :sha, post_favoritos = :favo, post_cache = :cache WHERE post_id = :pid";
            $valores7 = array(
                'com' => $query3[0],
                'seg' => $query4[0],
                'sha' => $query5[0],
                'favo' => $query6[0],
                'cache' => time(),
                'pid' => $pid
            );
            $psDb->db_execute($consulta7, $valores7);
        }
        //obtenemos los seguidores
        if($post['post_seguidores'] > 0){
            $consulta8 = "SELECT COUNT(follow_id) AS f FROM u_follows WHERE f_id = :fid AND f_user = :user AND f_type = :type";
            $valores8 = array(
                'fid' => $post['post_id'],
                'user' => $psUser->user_id,
                'type' => 2
            );
            $query8 = $psDb->db_execute($consulta8, $valores8, 'fetch_num');
            $post['follow'] = $query8[0];
        }
        //obtenemos los últimos visitantes
        if($postData['post_visitantes']){
            $consulta9 = "SELECT v.*, u.user_id, u.user_name FROM w_visitas AS v LEFT JOIN u_miembros AS u ON v.user = u.user_id WHERE v.for = :for AND v.type = :type AND v.user > :user ORDER BY v.date DESC LIMIT :limite";
            $valores9 = array(
                'for' => $post['post_id'],
                'type' => 2,
                'user' => 0,
                'limite' => 10
            );
            $post['visitas'] = $psDb->resultadoArray($psDb->db_execute($consulta9, $valores9));
        }
        //obtenemos las categorías
        $consulta10 = "SELECT c_nombre, c_seo FROM p_categorias  WHERE cid = :cid";
        $valores10 = array('cid' => $post['post_category']);
        $post['categoria'] = $psDb->db_execute($consulta10, $valores10, 'fetch_assoc');
        //obtenemos los puntos
        if($post['post_user'] == $psUser->user_id || $psUser->admod){
            $consulta11 = "SELECT p.*, u.user_id, u.user_name FROM p_votos AS p LEFT JOIN u_miembros AS u ON p.tuser = u.user_id WHERE p.tid = :pid AND p.type = :type ORDER BY p.cant DESC";
            $valores11 = array('pid' => $post['post_id'], 'type' => 1);
            $post['puntos'] = $psDb->resultadoArray($psDb->db_execute($consulta11, $valores11));
        }
        //obtenemos las medallas
        $consulta12 = "SELECT m.*, a.* FROM w_medallas AS m LEFT JOIN w_medallas_assign AS a ON a.medal_id = m.medal_id WHERE a.medal_for = :for AND m.m_type = :type ORDER BY a.medal_date";
        $valores12 = array(
            'for' => $post['post_id'],
            'type' => 2
        );
        $post['medallas'] = $psDb->resultadoArray($psDb->db_execute($consulta12, $valores12));
        $post['m_total'] = count($post['medallas']);
        //comprobamos si está bloqueado
        $consulta13 = "SELECT bid FROM u_bloqueos WHERE b_user = :user AND b_auser = :buser";
        $valores13 = array('user' => $post['post_user'], 'buser' => $psUser->user_id);
        $post['block'] = $psDb->db_execute($consulta13, $valores13, 'rowCount');
        //obtenemos los tags
        $post['post_tags'] = explode(",", $post['post_tags']);
        $post['n_tags'] = count($post['post_tags']) - 1;
        //obtenemos la fecha
        $post['post_date'] = strftime("%d.%m.%Y a las %H:%M hs", $post['post_date']);
        //comprobamos si hay una nueva visita
        $consulta14 = "SELECT id FROM w_visitas WHERE `for` = :for AND `type` = :type";
        $valores14['for'] = $pid;
        $valores14['type'] = 2;
        if($psUser->member){
            $consulta14 .= ' AND `user` = :user';
            $valores14['user'] = $psUser->user_id;
            $consulta14 .= ' OR `ip` LIKE :ip';
            $valores14['ip'] = $_SERVER['REMOTE_ADDR'];
        }else{
            $consulta14 .= ' AND `ip` LIKE :ip';
            $valores14['ip'] = $_SERVER['REMOTE_ADDR'];
        }
        $visitado = $psDb->db_execute($consulta14, $valores14, 'rowCount');
        //si es miembro y no lo ha visitado
        if($psUser->member && $visitado == 0) {
            $consulta15 = "INSERT INTO w_visitas (user, `for`, type, date, ip) VALUES (:uid, :for, :type, :dates, :ip)";
            $valores15 = array(
                'uid' => $psUser->user_id,
                'for' => $pid,
                'type' => 2,
                'dates' => time(),
                'ip' => $_SERVER['REMOTE_ADDR']
            );
            $psDb->db_execute($consulta15, $valores15);

            $consulta16 = "UPDATE p_posts SET post_hits = :hits WHERE post_id = :pid AND post_user != :user";
            $valores16 = array(
                'hits' => 'post_hits'+1,
                'pid' => $pid,
                'user' => $psUser->user_id
            );
            $psDb->db_execute($consulta16, $valores16);
        }else{//si no es miembro o no lo ha visitado
            $consulta15 = "UPDATE w_visitas SET date = :dates, ip = :ip WHERE `for` = :for AND `type` = :type AND user = :user";
            $valores15 = array(
                'dates' => time(),
                'ip' => $psCore->getIp(),
                'for' => $pid,
                'type' => 2,
                'user' => $psUser->user_id
            );
            $psDb->db_execute($consulta15, $valores15);
        }
        if($psCore->settings['c_hits_guest'] == 1 && !$psUser->member && !$visitado) {
            $consulta17 = "INSERT INTO w_visitas (user, for, type, date, ip) VALUES (:user, :for, :type, :dates, :ip)";
            $valores17 = array(
                'user' => $psUser->user_id,
                'for' => $pid,
                'type' => 2,
                'dates' => time(),
                'ip' => $_SERVER['REMOTE_ADDR']
            );
            $psDb->db_execute($consulta17, $valores17);
            $consulta18 = "UPDATE p_posts SET post_hits = :hits WHERE post_id = :pid";
            $valores18 = array('hits' => 'post_hits'+1, 'pid' => $pid);
            $psDb->db_execute($consulta18, $valores18);
        }
        //agregamos al portal a los post visitados
        if($psCore->settings['c_allow_portal']){
            $consulta19 = "SELECT last_posts_visited FROM u_portal WHERE user_id = :uid";
            $valores19 = array('uid' => $psUser->user_id);
            $datos = $psDb->db_execute($consulta19, $valores19, 'fetch_assoc');

            $visitado = unserialize($datos['last_posts_visited']);
            if(!is_array($visitado)){
                $visitado = array();
            }
            $total = count($visitado);
            if($total > 10){
                //eliminamos una parte del array
                array_splice($visitado, 0, 1);
            }
            //
            if(!in_array($post['post_id'], $visitado)){
                array_push($visitado, $post['post_id']);
            }
            $visitado = serialize($visitado);
            $consulta20 = "UPDATE u_portal SET last_posts_visited = :visit WHERE user_id = :uid";
            $valores20 = array('visit' => $visitado, 'uid' => $psUser->user_id);
            $psDb->db_execute($consulta20, $valores20);
        }

        //parseamos los bbcodes del texto
        $post['post_body'] = $psCore->badWords($post['post_smileys'] == 0  ? $post['post_body'] : $post['post_body'], true);
        $post['user_firma'] = $psCore->badWords($post['user_firma'],true);
        //devolvemos los datos obtenidos
        return $post;
    }

    /**
     * @funcionalidad obtenemos un listado con los últimos post publicados
     * @param type $cat categoría en la que buscar
     * @param type $sticky comprobamos si está patrocinado
     * @return type devolvemos un array con los datos obtenidos
     */
    function getLastPosts($cat, $sticky){
        global $psDb, $psCore, $psUser;
        //comprobamos si la categoría existe
        if(!empty($cat)){
            $consulta = "SELECT cid FROM p_categorias WHERE c_seo = :cat";
            $valores = array('cat' => $cat);
            $dcat = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
        }
        //comprobamos si está como sticky
        if($sticky){
            $stic1 = ' AND p.post_sticky = :stic2';
            $stic2 = 1;
            $order = 'p.post_sponsored';
            $start = 0;
            $fin = 10;
        }else{
            $stic1 = ' AND p.post_sticky = :stic2';
            $stic2 = 0;
            $order = 'p.post_id';
            // TOTAL DE POSTS
            $consulta2 = "SELECT COUNT(p.post_id) AS total FROM p_posts AS p LEFT JOIN u_miembros AS u ON p.post_user = u.user_id WHERE ";
            if($psUser->admod && $psCore->settings['c_see_mod'] == 1){
                $consulta2 .= 'p.post_id > :id';
                $valores2['id'] = 0;
            }else{
                $consulta2 .= 'u.user_activo = :activo ';
                $valores2['activo'] = 1;
                $consulta2 .= ' AND u.user_baneado = :ban';
                $valores2['ban'] = 0;
                $consulta2 .= ' AND p.post_status = :status';
                $valores2['status'] = 0;
            }
            if(!empty($dcat) && $dcat['cid'] > 0){
                $consulta2 .= ' AND p.post_category = :cat2';
                $valores2['cat2'] = $dcat['cid'];
            }
            $consulta2 .= $stic1;
            $valores2['stic2'] = $stic2;
            $q1 = $psDb->db_execute($consulta2, $valores2, 'fetch_num');
            $posts['total'] = $q1[0];
            $start = $psCore->setPagLimite($psCore->settings['c_max_posts'], false, $posts['total']);
            $start = explode(',',$start);
            $fin = $start[1];
            $start = $start[0];
            $lastPosts['pages'] = $psCore->getPages($posts['total'], $psCore->settings['c_max_posts']);
        }
        //ahora realizamos la consulta importante de la que sacamos todos los datos necesarios
        $consulta3 = "SELECT p.post_id, p.post_user, p.post_category, p.post_title, p.post_date, p.post_comments, p.post_puntos, p.post_private, p.post_sponsored, p.post_status, p.post_sticky, u.user_id, u.user_name, u.user_activo, u.user_baneado, c.c_nombre, c.c_seo, c.c_img FROM p_posts AS p LEFT JOIN u_miembros AS u ON p.post_user = u.user_id LEFT JOIN p_categorias AS c ON c.cid = p.post_category WHERE ";
        if($psUser->admod && $psCore->settings['c_see_mod'] == 1){
            $consulta3 .= 'p.post_id > :id';
            $valores3['id'] = 0;
        }else{
            $consulta3 .= 'p.post_status = :status';
            $valores3['status'] = 0;
            $consulta3 .= ' AND u.user_activo = :activo';
            $valores3['activo'] = 1;
            $consulta3 .= ' AND u.user_baneado = :ban';
            $valores3['ban'] = 0;
        }
        if(!empty($dcat) && $dcat['cid'] > 0){
            $consulta3 .= ' AND p.post_category = :cat2';
            $valores3['cat2'] = $dcat['cid'];
        }
        $consulta3 .= $stic1;
        $valores3['stic2'] = $stic2;
        $consulta3 .= ' GROUP BY p.post_id ORDER BY ';
        //$consulta3 .= $order.' DESC';
        $consulta3 .= $order.' DESC LIMIT :start, :fin';
        $valores3['start'] = (int)$start;
        $valores3['fin'] = (int)$fin;
        //$lastPosts['data'] = $psDb->resultadoArray($psDb->db_execute($consulta3, $valores3));echo 'jajaja';
        $lastPosts['data'] = $psDb->db_execute($consulta3, $valores3);
        return $lastPosts;
    }

    /**
     * @funcionalidad creamos un nuevo post
     * @return type devolvemos un string con el resultado
     */
    function nuevoPost(){
        global $psCore, $psUser, $psDb, $psMonitor, $psActividad;
        if($psUser->admod || $psUser->permisos['gopp']){
            $post = array(
                'date' => time(),
                'title' => $psCore->badWords(filter_input(INPUT_POST, 'titulo')),
                'body' => filter_input(INPUT_POST, 'cuerpo'),
                'tags' => $psCore->badWords(filter_input(INPUT_POST, 'tags')),
                'category' => filter_input(INPUT_POST, 'categoria'),
            );
            //comprobamos si algún campo está vacío
            foreach($post as $key => $valor){
                $valor = trim(preg_replace('/[^ A-Za-z0-9]/', '', $valor));
                $valor = str_replace(' ', '', $valor);
                if(empty($valor)) return false;
            }
            //comprobamos los tags
            $tags = $this->validarTags($post['tags']);
            if(empty($tags)){
                return 'Tienes que ingresar por lo menos <b>4</b> tags.';
            }
            // obtenemos más datos
            $post['visitantes'] = empty($_POST['visitantes']) ? 0 : 1;
            $post['smileys'] = empty($_POST['smileys']) ? 0 : 1;
            $post['private'] = empty($_POST['privado']) ? 0 : 1;
            $post['block_comments'] = empty($_POST['sin_comentarios']) ? 0 : 1;
            // estos sólo están disponibles para administradores y modderadores
            if(empty($psUser->admod)  && $psUser->permisos['most'] == false) {
                $post['sponsored'] = 0;
                $post['sticky'] = 0;
            } else {
                $post['sponsored'] = empty($_POST['patrocinado']) ? 0 : 1;
                $post['sticky'] = empty($_POST['sticky']) ? 0 : 1;
            }
            //antiflood
            $antiflood = 2;
            $consulta = "SELECT COUNT(post_id) AS few FROM p_posts WHERE post_body = :body";
            $valores = array('body' => $post['body']);
            $query = $psDb->db_execute($consulta, $valores, 'fetch_num');
            if($query[0]){
                return 'Ocurri&oacute; un error y el post no pudo ser agregado.';
            }
            if($psUser->info['user_lastpost'] < (time() - $antiflood)){
                //comprobamos si existe la categoría
                $consulta2 = "SELECT cid FROM p_categorias WHERE cid = :cid";
                $valores2 = array('cid' => $post['category']);
                if(!$psDb->db_execute($consulta2, $valores2, 'rowCount')){
                    return 'La categor&iacute;a seleccioanda no existe.';
                }
                //validamos la ip
                /*if(!filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_URL)){
                    if(!filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_URL)){
                        return 'Su ip no pudo validarse correctamente.';
                    }
                }*/
                //insertamos los datos
                $consulta3 = "INSERT INTO p_posts (post_user, post_category, post_title, post_body, post_date, post_tags, post_ip, post_private, post_block_comments, post_sponsored, post_sticky, post_smileys, post_visitantes, post_status) VALUES (:user, :cat, :title, :body, :dates, :tags, :ip, :private, :bcom, :sponsored, :sticky, :smileys, :visitantes, :status)";
                $valores3 = array(
                    'user' => $psUser->user_id,
                    'cat' => $post['category'],
                    'title' => $post['title'],
                    'body' => $post['body'],
                    'dates' => $post['date'],
                    'tags' => $post['tags'],
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'private' => $post['private'],
                    'bcom' => $post['block_comments'],
                    'sponsored' => $post['sponsored'],
                    'sticky' => $post['sticky'],
                    'smileys' => $post['smileys'],
                    'visitantes' => $post['visitantes'],
                    'status' => (!$psUser->admod && ($psCore->settings['c_desapprove_post'] == 1 || $psUser->permisos['gorpap'] == true) ? 3 : 0)
                );
                if($psDb->db_execute($consulta3, $valores3)){
                    $pid = $psDb->getLastInsertId();
                    //comprobamos si está oculto, y lo añadimos al historial
                    //print_r($psCore->settings);
                    if(!$psUser->admod && ($psCore->settings['c_desapprove_post'] == 1 || $psUser->permisos['gorpap'] == true)){
                        $consulta4 = "INSERT INTO w_historial (pofid, action, type, mod, reason, `date`, mod_ip) VALUES (:id, :action, :type, :mod, :reason, :dates, :ip)";
                        $valores4 = array(
                            'id' => $pid,
                            'action' => 3,
                            'type' => 1,
                            'mod' => $psUser->user_id,
                            'reason' => 'Revisi&oacute;n para su publicaci&oacute;n',
                            'dates' => time(),
                            'ip' => $_SERVER['REMOTE_ADDR']
                        );
                        $psDb->db_execute($consulta4, $valores4);
                    }
                    //actualizamos las estadísticas
                    $consulta5 = "UPDATE w_stats SET stats_posts = :stats WHERE stats_no = :no";
                    $valores5 = array('stats' => 'stats_posts'+1, 'no' => 1);
                    $psDb->db_execute($consulta5, $valores5);
                    //actualizamos los datos del último post del usuario
                    $consulta6 = "UPDATE u_miembros SET user_lastpost = :last WHERE user_id = :uid";
                    $valores6 = array('last' => time(), 'uid' => $psUser->user_id);
                    $psDb->db_execute($consulta6, $valores6);
                    //añadimos al monitor de notificaciones
                    $psMonitor->setFollowNotificaciones(5, 1, $psUser->user_id, $pid);
                    //añadimos la actividad
                    $psActividad->setActividad(1, $pid);
                    //comprobamos si el usuario sube de rango
                    $this->subirRango($psUser->user_id);
                    return $pid;
                }else{
                    return 'Error al intentar insertar los datos en la db.';
                }
            }else{
                return 'Error al crear el nuevo post.';
            }
        }else{
            return 'No tienes permisos para poder crear un post.';
        }
    }

    /**
     * @funcionalidad guardamos los datos del post
     * @return type devolvemos un string con el resultado de la consulta o actualizamos el historial de moderación si tenemos el rango
     */
    function guardarPost(){
        global $psDb, $psCore, $psUser;
        $pid = filter_input(INPUT_GET, 'pid');
        //obtenemos los datos del post
        $consulta = "SELECT post_user, post_sponsored, post_sticky, post_status FROM p_posts WHERE post_id = :pid";
        $valores = array('pid' => $pid);
        $datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
        //comprobamos si podemos editarlo
        if($datos['post_status'] != 0 && !$psUser->admod && !$psUser->permisos['moedpo']){
            return 'Lo sentimos, pero no puedes editar el post';
        }
        //obtenemos los datos del post del formulario
        $post = array(
            'title' => $psCore->badWords(filter_input(INPUT_POST, 'titulo'), true),
            'body' => filter_input(INPUT_POST, 'cuerpo'),
            'tags' => $psCore->badWords(filter_input(INPUT_POST, 'tags')),
            'category' => filter_input(INPUT_POST, 'categoria'),
        );
        //comprobamos si algún campo está vacío
        foreach($post as $key => $valor){
            $valor = trim(preg_replace('/[^ A-Za-z0-9]/', '', $valor));
            $valor = str_replace(' ', '', $valor);
            if(empty($valor)) return false;
        }
        //comprobamos los tags
        $tags = $this->validarTags($post['tags']);
        if(empty($tags)) return 'Tienes que ingresar al menos <strong>4</strong> tags.';
        // obtenemos más datos
        $post['visitantes'] = empty($_POST['visitantes']) ? 0 : 1;
        $post['smileys'] = empty($_POST['smileys']) ? 0 : 1;
        $post['private'] = empty($_POST['privado']) ? 0 : 1;
        $post['block_comments'] = empty($_POST['sin_comentarios']) ? 0 : 1;
        // estos sólo están disponibles para administradores y modderadores
        if(empty($psUser->admod)  && $psUser->permisos['most'] == false) {
            $post['sponsored'] = $datos['post_sponsored'];
            $post['sticky'] = $datos['post_sticky'];
        } else {
            $post['sponsored'] = empty($_POST['patrocinado']) ? 0 : 1;
            $post['sticky'] = empty($_POST['sticky']) ? 0 : 1;
        }
        //ahora actualizamos los datos en la base de datos
        if($psUser->user_id == $datos['post_user'] || !empty($psUser->admod) || !empty($psUser->permisos['moedpo'])){
            $consulta2 = "UPDATE p_posts SET post_title = :title, post_body = :body, post_tags = :tags, post_category = :cat, post_private = :private, post_block_comments = :block, post_sponsored = :sponsored, post_smileys = :smileys, post_visitantes = :visitantes, post_sticky = :sticky WHERE post_id = :pid";
            $valores2 = array(
                'title' => $post['title'],
                'body' => $post['body'],
                'tags' => $post['tags'],
                'cat' => $post['category'],
                'private' => $post['private'],
                'block' => $post['block_comments'],
                'sponsored' => $post['sponsored'],
                'smileys' => $post['smileys'],
                'visitantes' => $post['visitantes'],
                'sticky' => $post['sticky'],
                'pid' => $pid
            );
            if($psDb->db_execute($consulta2, $valores2)){
                //ahora guardamos los datos en el historial de moderación
                if($psUser->admod || ($psUser->permisos['moedpo'] && $psUser->user_id != $datos['post_user'] && $_POST['razon'])){
                    include 'c.moderacion.php';
                    $psModeracion =& psModeracion::getInstance();
                    return $psModeracion->setHistorial('editar', 'post', array(
                        'post_id' => $pid,
                        'title' => $post['title'],
                        'autor' => $datos['post_user'],
                        'razon' => filter_input(INPUT_POST, 'razon')
                    ));
                }else{
                    return 1;
                }
            }else{
                return 'Ocurri&oacute; un error al intentar actualizar los datos en la base de datos.';
            }
        }else{
            return 'Error. No tienes permisos para hacer eso.';
        }
    }

    /**
     * @funcionalidad obtenemos los datos para editar el post
     * @return type devolvemos un array con los datos obtenidos
     */
    function getEditarPost(){
        global $psDb, $psUser, $psCore;
        $pid = filter_input(INPUT_GET, 'pid');
        $consulta = "SELECT * FROM p_posts WHERE post_id = :pid";
        $valores = array('pid' => $pid);
        $datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
        //comprobamos datos
        if(empty($datos['post_id'])){
            return 'El post seleccionado no existe.';
        }else if($datos['post_status'] != 0 && $psUser->admod == 0 && $psUser->permisos['moedpo'] == false){
            return 'El post seleccionado no puede ser editado o no tienes permisos suficientes.';
        }else if($psUser->user_id != $datos['post_user'] &&$psUser->admod == 0 && $psUser->permisos['moedpo'] == false){
            return 'No puedes editar un post a no ser que sea tuyo.';
        }
        //cambiamos el prefijo de los datos para que sea más fácil guardar en borradores
        foreach($datos as $key => $valor){
            $aux = str_replace('post_', 'b_', $key);
            $dato[$aux] = $valor;
        }
        return $dato;
    }

    /**
     * @funcionalidad eliminamos un post
     * @return type devolvemos un string con el resultado de las operaciones
     */
    function borrarPost(){
        global $psDb, $psCore, $psUser;
        $pid = filter_input(INPUT_POST, 'postid');
        //comprobamos si intenta borrar un post suyo
        $consulta = "SELECT post_id, post_title, post_user, post_body, post_category FROM p_posts WHERE post_id = :pid AND post_user = :user";
        $valores = array('pid' => $pid, 'user' => $psUser->user_id);
        $datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
        //actualizamos las tablas
        $consulta2 = "UPDATE w_stats SET stats_posts = :stats WHERE stats_no = :no";
        $valores2 = array('stats' => 'stats_posts'-1, 'no' => 1);
        $psDb->db_execute($consulta2, $valores2);
        $consulta3 = "UPDATE u_miembros SET user_posts = :user WHERE user_id = :uid";
        $valores3 = array('user' => 'user_posts'-1, 'uid' => $datos['post_user']);
        $psDb->db_execute($consulta3, $valores3);
        //comprobamos si el post es mio o tengo el rango de admin o mod
        if(!empty($datos['post_id']) || !empty($psUser->admod)){
            //si el post es mio lo borramos y lo mandamos al borrador
            $consulta4 = "DELETE FROM p_posts WHERE post_id = :pid";
            $valores4 = array('pid' => $pid);
            if($psDb->db_execute($consulta4, $valores4)){
                $consulta5 = "DELETE FROM p_comentarios WHERE c_post_id = :pid";
                if($psDb->db_execute($consulta5, $valores4)){
                    $consulta6 = "INSERT INTO p_borradores (b_user, b_date, b_title, b_body, b_tags, b_category, b_status, b_causa) VALUES (:user, :dates, :title, :body, :tags, :category, :status, :causa)";
                    $valores6 = array(
                        'user' => $psUser->user_id,
                        'dates' => time(),
                        'title' => $datos['post_title'],
                        'body' => $datos['post_body'],
                        'tags' => '',
                        'category' => $datos['post_category'],
                        'status' => 2,
                        'causa' => ''
                    );
                    if($psDb->db_execute($consulta6, $valores6)){
                        return 'El post fue eliminado correctamente. Podr&aacute;s verlo en tus borradores.';
                    }
                }else{
                    return 'Ocurri&oacute; un error al intentar borrar los comentarios del post.';
                }
            }else{//si no lo borramos sin guardar el borrador
                $consulta5 = "UPDATE p_posts SET post_status = :status WHERE post_id = :pid";
                $valores5 = array('status' => 2, 'pid' => $pid);
                if($psDb->db_execute($consulta5, $valores5)){
                    return 'El post fue eliminado correctamente.';
                }
            }
        }else{
            return 'Est&aacute;s intentando algo no permitido.';
        }
    }

    /**
     * @funcionalidad eliminamos un post desde la sección admin
     * @return type devolvemos un string con el resultado de las operaciones
     */
    function borrarPostAdmin(){
        global $psDb, $psUser;
        if($psUser->admod == 1){
            $consulta = "SELECT post_id FROM p_posts WHERE post_id = :pid AND post_status = :status";
            $valores = array('pid' => filter_input(INPUT_POST, 'postid'), 'status' => 2);
            if($psDb->db_execute($consulta, $valores, 'rowCount')){
                $consulta2 = "DELETE FROM p_posts WHERE post_id = :pid";
                $valores2 = array('pid' => filter_input(INPUT_POST, 'postid'));
                if($psDb->db_execute($consulta2, $valores2)){
                    $consulta3 = "DELETE FROM p_comentarios WHERE c_post_id = :pid";
                    if($psDb->db_execute($consulta3, $valores2)){
                        $consulta4 = "UPDATE w_stats SET stats_posts = :stats WHERE stats_no = :no";
                        $valores4 = array('stats' => 'stats_posts'-1, 'no' => 1);
                        $psDb->db_execute($consulta4, $valores4);
                        return 'El post fue eliminado correctamente.';
                    }else{
                        return 'Ocurri&oacute; un error al intentar eliminar los comentarios del post.';
                    }
                }else{
                    return 'Ocurri&oacute; un error intentando eliminar el post.';
                }
            }else{
                return 'El post que intentas eliminar ya ha sido eliminado.';
            }
        }else{
            return 'No tienes permisos para hacer eso.';
        }
    }

    /**
     * @funcionalidad obtenemos los datos de la categoria
     * @return type obtenemos un array con los datos generados
     */
    function getDatosCategoria(){
        global $psDb, $psCore;
        $categoria = filter_input(INPUT_GET, 'cat');
        $consulta = "SELECT c_nombre, c_seo FROM p_categorias WHERE c_seo = :seo";
        $valores = array('seo' => $categoria);
        return $psDb->db_execute($consulta, $valores, 'fetch_assoc');
    }

    /**
     * @funcionalidad obtenemos los datos del autor del post
     * @param type $uid id del usuario
     * @return type devolvemos un array con los datos obtenidos
     */
    function getAutor($uid){
        global $psDb, $psCore, $psUser;
        //obtenemos los datos del autor
        $consulta = "SELECT u.user_id, u.user_name, u.user_rango, u.user_puntos, u.user_lastactive, u.user_last_ip, u.user_activo, u.user_baneado, p.user_pais, p.user_sexo, p.user_firma FROM u_miembros AS u LEFT JOIN u_perfil AS p ON u.user_id = p.user_id WHERE u.user_id = :uid";
        $valores = array('uid' => $uid);
        $datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
        //obtenemos seguidores
        $consulta2 = "SELECT follow_id FROM u_follows WHERE f_id = :fid AND f_type = :type";
        $valores2 = array('fid' => $uid, 'type' => 1);
        $datos['user_seguidores'] = $psDb->db_execute($consulta2, $valores2, 'rowCount');
        //obtenemos comentarios
        $consulta3 = "SELECT cid FROM p_comentarios WHERE c_user = :uid AND c_status = :status";
        $valores3 = array('uid' => $uid, 'status' => 0);
        $datos['user_comentarios'] = $psDb->db_execute($consulta3, $valores3, 'rowCount');
        //obtenemos posts
        $consulta4 = "SELECT post_id FROM p_posts WHERE post_user = :uid AND post_status = :status";
        $valores4 = array('uid' => $uid, 'status' => 0);
        $datos['user_posts'] = $psDb->db_execute($consulta4, $valores4, 'rowCount');
        //obtenemos los datos del rango del usuario
        $consulta5 = "SELECT r_name, r_color, r_image FROM u_rangos WHERE rango_id = :rid";
        $valores5 = array('rid' => $datos['user_rango']);
        $datos['rango'] = $psDb->db_execute($consulta5, $valores5, 'fetch_assoc');
        //ahora comprobamos el estado del usuario
        $online = time() - ($psCore->settings['c_last_active'] * 60);
        $inactive = $online * 2;
        if($datos['user_lastactive'] > $online){
            $datos['status'] = array('t' => 'Usario online', 'css' => 'online');
        }else if($datos['user_lastactive'] > $inactive){
            $datos['status'] = array('t' => 'Usuario inactivo', 'css' => 'inactive');
        }else{
            $datos['status'] = array('t' => 'Usuario offline', 'css' => 'offline');
        }
        $datos['pais'] = array('icon' => strtolower($datos['user_pais']), 'name' => $psPaises[$datos['user_pais']]);
        //seguidores
        if($datos['user_seguidores'] > 0){
            $consulta6 = "SELECT follow_id FROM u_follows WHERE f_id = :fid AND f_user = :user AND f_type = :type";
            $valores6 = array('fid' => $uid, 'user' => $psUser->user_id, 'type' => 1);
            $datos['follows'] = $psDb->db_execute($consulta6, $valores6, 'rowCount');
        }
        return $datos;
    }

    /**
     * @funcionalidad obtenemos los puntos permitidos para dar el usuario
     * @return type devolvemos un array con los datos obtenidos
     */
    function getPuntos(){
        global $psCore, $psUser;
        if($psCore->settings['c_allow_points'] > 0){
            $datos['rango'] = $psCore->settings['c_allow_points'];
        }else if($psCore->settings['c_allow_points'] == '-1'){
            $datos['rango'] = $psUser->info['user_puntosxdar'];
        }else{
            $datos['rango'] = $psUser->permisos['gopfp'];
        }
        return $datos;
    }

    /**
     * @funcionalidad dara al usuario la posibilidad de seleccionar un post aleatorio,
     *  el post siguiente o el post anterior al que se encuentra
     */
    public function setModePost(){
        global $psUser, $psCore, $psDb;
        $action = filter_input(INPUT_GET,'action');
        if($action == 'fortuitae'){
            // TOTAL DE POSTS
            $consulta2 = "SELECT COUNT(p.post_id) AS total FROM p_posts AS p LEFT JOIN u_miembros AS u ON p.post_user = u.user_id WHERE ";
            if($psUser->admod && $psCore->settings['c_see_mod'] == 1){
                $consulta2 .= 'p.post_id > :id';
                $valores2['id'] = 0;
            }else{
                $consulta2 .= 'u.user_activo = :activo ';
                $valores2['activo'] = 1;
                $consulta2 .= ' AND u.user_baneado = :ban';
                $valores2['ban'] = 0;
                $consulta2 .= ' AND p.post_status = :status';
                $valores2['status'] = 0;
            }
            $q1 = $psDb->db_execute($consulta2, $valores2, 'fetch_num');
            $total = $q1[0];
            $consulta = "SELECT p.post_id, p.post_user, p.post_category, p.post_title, u.user_name, c.c_nombre, c.c_seo FROM p_posts AS p LEFT JOIN u_miembros AS u ON p.post_user = u.user_id LEFT JOIN p_categorias AS c ON c.cid = p.post_category WHERE p.post_id = ";
            $consulta .= ''.rand(0, $total).'';
            if(!$psDb->db_execute($consulta,$valores,'rowCount')){
                $psCore->redirectTo($psCore->settings['url']."/posts/");
            }
            $resultado = $psDb->db_execute($consulta,$valores,'fetch_assoc');
        }else {
            $action = $action == 'prev' ? '<' : '>';
            $postid = (isset($_GET['id'])) ? filter_input(INPUT_GET,'id') : 1;
            $consulta = "SELECT p.post_id, p.post_user, p.post_category, p.post_title, u.user_name, c.c_nombre, c.c_seo FROM p_posts AS p LEFT JOIN u_miembros AS u ON p.post_user = u.user_id LEFT JOIN p_categorias AS c ON c.cid = p.post_category WHERE p.post_status = :status";
            $valores['status'] = 0;
            if(!$psUser->admod && $psCore->settings['c_see_mod'] != 1){
                $consulta .= " AND u.user_activo = :activo";
                $valores['activo'] = 1;
                $consulta .= " AND u.user_baneado = :ban";
                $valores['ban'] = 0;
            }
            $consulta .= " AND p.post_id ".$action.' '.$postid;
            $consulta .= " ORDER BY p.post_id";
            if($action == '<'){
                $consulta .= " DESC";
            }else{
                $consulta .= " ASC";
            }
            if(!$psDb->db_execute($consulta, $valores, 'rowCount')){
                $psCore->redirectTo($psCore->settings['url']."/posts/");
            }
            $resultado = $psDb->db_execute($consulta,$valores,"fetch_assoc");
        }
        $psCore->redirectTo($psCore->settings['url']."/posts/".$resultado['c_seo']."/".$resultado['post_id']."/".$psCore->setSeo($resultado['post_title']).".html");
    }

    /**
     * @funcionalidad obtenemos la vista previa del post
     * @return type devolvemos un array con los datos a mostrar en la vista
     */
    function getPreview(){
        global $psCore;
        $title = filter_input(INPUT_POST, 'titulo');
        $body = filter_input(INPUT_POST, 'cuerpo');
        return array('titulo' => $title, 'cuerpo' => $psCore->badWords($body));
    }

    /**
     * @funcionalidad votamos el post
     * @return type devolvemos un string con el resultado de la consulta
     */
    function votarPost(){
        global $psDb, $psCore, $psUser, $psMonitor, $psActividad;
        //comprobamos el rango y los permisos del usuario
        if($psUser->admod || $psUser->permisos['godp']){
            //validamos que el valor de puntos sea numérico
            if(!ctype_digit($_POST['puntos'])){
                return 'Para votar debes introducir caracteres num&eacute;ricos.';
            }
            //validamos la ip y comprobamos si el post ha sido votado desde la misma ip
            /*if(!filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_URL)){
                return 'Lo sentimos su ip no pudo validarse.';
            }*/
            if($psUser->admod != 1){
                $consulta = "SELECT user_id FROM u_miembros WHERE user_last_ip = :ip AND user_id != :uid";
                $valores = array('ip' => $_SERVER['REMOTE_ADDR'], 'uid' => $psUser->user_id);
                $consulta2 = "SELECT session_id FROM u_sessions WHERE session_ip = :ip AND session_user_id != :uid";
                if($psDb->db_execute($consulta, $valores, 'rowCount') || $psDb->db_execute($consulta2, $valores, 'rowCount')){
                    return '0: Has votado con otra cuenta desde esta misma ip. Por favor contacta con tu administrador para solucionar el problema.';
                }
            }
            //obtenemos datos de algunos campos
            $pid = filter_input(INPUT_POST, 'postid');
            $puntos = abs(filter_input(INPUT_POST, 'puntos'));//si el valor es negativo lo cambiamos a positivo
            //obtenemos los datos del post para sumar los puntos
            $consulta3 = "SELECT post_user FROM p_posts WHERE post_id = :pid";
            $valores3 = array('pid' => $pid);
            $datos = $psDb->db_execute($consulta3, $valores3, 'fetch_assoc');
            //comprobamos si el post es mío
            $mipost = ($datos['post_user'] == $psUser->user_id) ? true : false;
            if(!$mipost){
                //comprobamos si ya hemos votado el post con esta cuenta
                $consulta4 = "SELECT tid FROM p_votos WHERE tid = :tid AND tuser = :user AND type = :type";
                $valores4 = array('tid' => $pid, 'user' => $psUser->user_id, 'type' => 1);
                if(!$psDb->db_execute($consulta4, $valores4, 'fetch_num')){
                    //comprobamos cuantos puntos nos quedan para dar
                    if($psCore->settings['c_allow_points'] == 1){
                        $max = $psCore->settings['c_allow_points'];
                    }else if($psCore->settings['c_allow_points'] == '-1'){//podemos dar todos los puntos disponibles
                        $max = $psUser->info['user_puntosxdar'];
                    }else if($psCore->settings['c_allow_points'] == '-2'){//podemos dar todos los puntos que queramos
                        $max = 99999999;
                    }else{
                        $max = $psUser->permisos['gopfp'];
                    }
                    //comprobamos si tenemos suficientes puntos
                    if($psUser->info['user_puntosxdar'] >= $puntos){
                        if($puntos > 0){
                            if($puntos <= $max){
                                //ahora realizamos las consultas
                                //sumamos al post
                                $consulta5 = "UPDATE p_posts SET post_puntos = post_puntos + :puntos WHERE post_id = :id";
                                $valores5 = array(
                                    'puntos' => $puntos,
                                    'id' => $pid
                                );
                                $psDb->db_execute($consulta5, $valores5);
                                //sumamos al dueño del post
                                $consulta6 = "UPDATE u_miembros SET user_puntos = user_puntos + :puntos WHERE user_id = :id";
                                $valores6 = array(
                                    'puntos' => $puntos,
                                    'id' => $datos['post_user']
                                );
                                $psDb->db_execute($consulta6, $valores6);
                                //restamos los puntos del usuario que vota
                                $consulta7 = "UPDATE u_miembros SET user_puntosxdar = user_puntosxdar - :puntos WHERE user_id = :id";
                                $valores7 = array(
                                    'puntos' => $puntos,
                                    'id' => $datos['post_user']
                                );
                                exit('son '.$puntos);
                                $psDb->db_execute($consulta7, $valores7);
                                //insertamos en la tabla de votos
                                $consulta8 = "INSERT INTO p_votos (tid, tuser, cant, type, `date`) VALUES (:tid, :user, :cant, :type, :dates)";
                                $valores8 = array(
                                    'tid' => $pid,
                                    'user' => $psUser->user_id,
                                    'cant' => $puntos,
                                    'type' => 1,
                                    'dates' => time()
                                );
                                $psDb->db_execute($consulta8, $valores8);
                                //agregamos al monitor de notificaciones
                                $psMonitor->setNotificacion(3, $datos['post_user'], $psUser->user_id, $pid, $puntos);
                                //agregamos la actividad
                                $psActividad->setActividad(3, $pid, $puntos);
                                //comprobamos si el usuario sube de rango
                                $this->subirRango($datos['post_user'], $pid);
                                return '1: Los puntos han sido agregados correctamente.';
                            }else{
                                return '0: Error. No puedes dar '.$puntos.' puntos, el m&aacute;ximo es '.$max;
                            }
                        }else{
                            return '0: Tienes que marcar algún punto.';
                        }
                    }else{
                        return '0: Error. No puedes dar '.$puntos.' puntos, s&oacute; tienes '.$psUser->info['user_puntosxdar'];
                    }
                }else{
                    return '0: Ya has votado este post.';
                }
            }else{
                return '0: No puedes votar un post tuyo.';
            }
        }else{
            return '0: No tienes permiso para hacer lo que intentas.';
        }
    }

    /**
     * @funcionalidad obtenemos los post relacionados a partir del título
     * @param type obtenemos el texto del post a partir del cuál comparar
     * @return type devolvemos un array con los datos obtenidos
     */
    function postRelacionados($dato){
        global $psDb, $psCore, $psUser;
        $consulta = "SELECT p.post_id, p.post_title, c.c_seo FROM p_posts AS p LEFT JOIN u_miembros AS u ON u.user_id = p.post_user LEFT JOIN p_categorias AS c ON c.cid = p.post_category WHERE p.post_status = :status ";
        $valores['status'] = 0;
        if(!$psUser->admod && $psCore->settings['c_see_mod'] != 1){
            $consulta .= 'AND u.user_activo = :activo AND u.user_baneado = :ban';
            $valores['activo'] = 1;
            $valores['ban'] = 0;
        }
        $consulta .= " AND MATCH(p.post_title) AGAINST(:dato IN BOOLEAN MODE) ORDER BY :rand DESC";
        $valores['dato'] = $dato;
        $valores['rand'] = RAND();
        $datos = $psDb->resultadoArray($psDb->db_execute($consulta, $valores));
        return $datos;
    }

    /**
     * @funcionalidad obtenemos los post relaciondos a través de los tags
     * @param type obtenemos normalmente un array con los tag
     * @return type devolvemos un array con los datos obtenidos
     */
    function getRelacionados($tags){
        global $psDb, $psCore, $psUser;
        //si es un array lo convertimos en cadena
        if(is_array($tags)){
            $tags = implode(', ', $tags);
        }else{
            str_replace('-', ', ', $tags);
        }
        $consulta = "SELECT DISTINCT p.post_id, p.post_title, p.post_category, p.post_private, c.c_seo, c.c_img FROM p_posts AS p LEFT JOIN p_categorias AS c ON c.cid = p.post_category WHERE MATCH(p.post_tags) AGAINST (:tags IN BOOLEAN MODE) AND p.post_status = :status AND p.post_sticky = :sticky ORDER BY :rand";
        $valores = array(
            'tags' => $tags,
            'status' => 0,
            'sticky' => 0,
            'rand' => rand(),
        );
        $datos = $psDb->resultadoArray($psDb->db_execute($consulta, $valores));
        return $datos;
    }

    /*****************************************************************************************/
    /******************************** TAGS ***************************************************/
    /*****************************************************************************************/

    /**
     * @funcionalidad comprobamos si los tags están escritos correctamente y si se cumple con el mínimo establecido
     * @param type $tags obtenemos los tags del formulario
     * @return type devolvemos un valor booleano con el resultado de las comprobaciones
     */
    function validarTags($tags){
        //eliminamos los espacios en blanco del texto obtenido
        $tags = trim(preg_replace('/[^ A-Za-z0-9,]/', '', $tags));
        $tags = str_replace(' ', '', $tags);
        if(empty($tags)){
            return false;
        }else{
            $tags = explode(',', $tags);
            if(count($tags) < 4){
                return false;
            }
            foreach($tags as $valor){
                if(empty($valor)){
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * @funcionalidad si los tags no han sido añadidos los creamos automáticamente a partir del texto
     * @param type $tags obtenemos el texto
     * @return type devolvemos un string con los tags separados por comas
     */
    function generarTags($texto){
        //eliminamos los espacios en blanco del texto obtenido
        $tags = trim(preg_replace("/[^ A-Za-z0-9]/", '', $texto));
        $txt = preg_replace('/ {2,}/', '', $tags);
        //separamos los tags
        $separar = explode(' ', $txt);
        $total = count($separar);
        $tag = '';
        $contador = 0;
        foreach($separar as $valor){
            $contador++;
            //añadimos la coma si no es el último tag
            $coma = ($contador < $total) ? ', ' : ' ';
            $tag .= (strlen($valor) >= 4 && strlen($valor) <= 8) ? $valor . $coma : '';
        }
        $tag = strtolower($tag);
        return $tag;
    }

    /*****************************************************************************************/
    /******************************** COMENTARIOS ********************************************/
    /*****************************************************************************************/

    /**
     * @funcionalidad obtenemos los comentarios
     * @param type $pid obtenemos el id del post
     * @return type devolvemos un array con los datos obtenidos
     */
    function getComentarios($pid){
        global $psDb, $psCore, $psUser;
        $start = $psCore->setPagLimite($psCore->settings['c_max_com']);
        $consulta = "SELECT u.user_name, u.user_activo, u.user_baneado, c.* FROM u_miembros AS u LEFT JOIN p_comentarios AS c ON u.user_id = c.c_user WHERE c.c_post_id = :pid ";
        $consulta2 = "SELECT cid FROM p_comentarios WHERE c_post_id = :cid";
        $valores['pid'] = (int)$pid;
        $valores2['cid'] = (int)$pid;
        if($psUser->admod){//si es admin no consultamos más datos
            $consulta .= ' ORDER BY c.cid DESC';
        }else{
            $consulta .= 'AND c.c_status = :status AND u.user_activo = :activo AND u.user_baneado = :baneado';
            $valores['status'] = 0;
            $valores['activo'] = 1;
            $valores['baneado'] = 0;
            $consulta2 .= ' AND c_status = :status';
            $valores2['status'] = 0;
            $consulta .= ' ORDER BY c.cid';
        }
        $query = $psDb->db_execute($consulta, $valores);
        $comentarios = $psDb->resultadoArray($query);
        $datos['num'] = count($comentarios);
        $i = 0;
        foreach($comentarios as $comentario){
            if($comentario['c_votos'] != 0){
                $consulta3 = 'SELECT voto_id FROM p_votos WHERE tid = :cid AND tuser = :user AND type = :type';
                $valores3 = array(
                    'cid' => $comentario['cid'],
                    'user' => $psUser->user_id,
                    'type' => 2,
                );
                $query = $psDb->db_execute($consulta3, $valores3, 'rowCount');

            }else{
                $votado = 0;
            }
            // comprobamos si está bloqueado el usuario
            $consulta4 = "SELECT bid, b_user, b_auser FROM u_bloqueos WHERE b_user = :user AND b_auser = :buser";
            $valores4 = array(
                'user' => $comentario['c_user'],
                'buser' => $psUser->user_id
            );
            $datos['block'] = count($psDb->db_execute($consulta4, $valores4));
            $datos['data'][$i] = $comentario;
            $datos['data'][$i]['votado'] = $votado;
            $datos['data'][$i]['c_html'] = $psCore->badWords($datos['data'][$i]['c_body'], true);
            $i++;
        }
        return $datos;
    }

    /**
     * @funcionalidad obtenemos los últimos comentarios
     * @return type devolvemos un array con los datos obtenidos
     */
    function getLastComentarios(){
        global $psDb, $psCore, $psUser;
        if($psUser->admod && $psCore->settigns['c_see_mod'] == 1){
            $consulta = "SELECT cm.cid, cm.c_status, u.user_name, u.user_activo, u.user_baneado, p.post_id, p.post_title, p.post_status, c.c_seo FROM p_comentarios AS cm LEFT JOIN u_miembros AS u ON cm.c_user = u.user_id LEFT JOIN p_posts AS p ON p.post_id = cm.c_post_id LEFT JOIN p_categorias AS c ON c.cid = p.post_category ORDER BY c.cid DESC";
        }else{
            $consulta = "SELECT cm.cid, cm.c_status, u.user_name, u.user_activo, u.user_baneado, p.post_id, p.post_title, p.post_status, c.c_seo FROM p_comentarios AS cm LEFT JOIN u_miembros AS u ON cm.c_user = u.user_id LEFT JOIN p_posts AS p ON p.post_id = cm.c_post_id LEFT JOIN p_categorias AS c ON c.cid = p.post_category WHERE p.post_status = :status AND cm.c_status = :cstatus AND u.user_activo = :activo AND u.user_baneado = :ban ORDER BY c.cid DESC";
            $valores['status'] = 0;
            $valores['cstatus'] = 0;
            $valores['activo'] = 1;
            $valores['ban'] = 0;
        }
        $query = $psDb->db_execute($consulta, $valores);
        if(!$query){
            exit('Error al ejecutar la consulta de obtenci&oacute;n de los $uacute;ltimos comentarios.');
        }
        $datos = $psDb->resultadoArray($query);
        return $datos;
    }

    /**
     * @funcionalidad agregamos un nuevo comentario al post
     * @return type devolvemos un string con el resultado de la inserción del comentario
     */
    function nuevoComentario(){
        global $psDb, $psCore, $psUser, $psActividad;
        //ponemos un límite al total de caracteres del comentario
        $comentario = substr(filter_input(INPUT_POST, 'comentario'), 0, 1000);//1000 de límite, espero que nadie llegue a tanto
        $pid = filter_input(INPUT_POST, 'postid');
        //obtenemos los datos del dueño del post
        $consulta = "SELECT post_user, post_block_comments FROM p_posts WHERE post_id = :pid";
        $valores = array('pid' => $pid);
        $datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
        $mostrar_resp = filter_input(INPUT_POST, 'mostrar_resp');
        $fecha = time();
        //comprobamos
        if($datos['post_user']){
            if($datos['post_block_comments'] != 1 || $datos['post_user'] == $psUser->user_id || $psUser->admod || $psUser->permisos['mocepc']){
                //comprobamos rango y permisos
                if(empty($psUser->admod) && $psUser->permisos['gopcp'] == false){
                    return 'Est&aacute;s intentando algo que no est&aacute; permitido.';
                }
                //comprobamos la ip
                if(!filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_URL)){
                    if(!filter_var($_SERVER['HTTP_REFERER'], FILTER_VALIDATE_URL)){
                        return 'Lo sentimos, su ip no pudo validarse correctamente.';
                    }
                }
                $consulta2 = "INSERT INTO p_comentarios (c_post_id, c_user, c_date, c_body, c_ip) VALUES (:pid, :user, :cdate, :body, :ip)";
                $valores2 = array(
                    'pid' => $pid,
                    'user' => $psUser->user_id,
                    'cdate' => $fecha,
                    'body' => $comentario,
                    'ip' => $_SERVER['REMOTE_ADDR']
                );
                if($psDb->db_execute($consulta2, $valores2)){
                    $cid = $psDb->getLastInsertId();
                    //aumentamos en 1 las estadísticas
                    $update = "UPDATE w_stats SET stats_comments = :stats WHERE stats_no = :no";
                    $vupdate = array('stats' => 'stats_comments'+1, 'no' => 1);
                    $psDb->db_execute($update, $vupdate);
                    //aumentamos en 1 los comentarios en el post
                    $update2 = "UPDATE p_posts SET post_comments = :pc WHERE post_id = :pid";
                    $vupdate2 = array('pc' => 'post_comments'+1,'pid' => $pid);
                    $psDb->db_execute($update2, $vupdate2);
                    //aumentamos en 1 los comentarios realizados por el usuario
                    $update3 = "UPDATE u_miembros SET user_comentarios = :uc WHERE user_id = :uid";
                    $vupdate3 = array('uc' => 'user_comentarios'+1, 'uid' => $psUser->user_id);
                    $psDb->db_execute($update3, $vupdate3);
                    //notificamos al usuario del post, al usuario si fue citado y a los que siguen el post
                    $this->citaComentarioNotificacion($pid, $datos['post_user'], $cid, $comentario);
                    //realizamos la actividad
                    $psActividad->setActividad(5, $pid);
                    if(!empty($mostrar_resp)){//comprobamos si está activada la opción de mostrar la respuesta del comentario
                        return array(
                            $cid,
                            $psCore->badWords($comentario, true),
                            $comentario,
                            $fecha,
                            filter_input(INPUT_POST, 'auser'),
                            '',
                            $_SERVER['REMOTE_ADDR']);
                    }else{
                        return '1: Tu comentario fue agregado correctamente.';
                    }
                }else{
                    return '0: Ocurri&oacute; un error al añadir el comentario, por favor int&eacute;ntelo de nuevo m&aacute;s tarde.';
                }
            }else{
                return '0: El post se encuentra cerrado y no est&aacute; permitido realizar comentarios.';
            }
        }else{
            return '0: El post no existe';
        }
    }

    /**
     * @funcionalidad editamos el comentario
     * @return type devolvemos un string con el resultado de las consultas
     */
    function editarComentario(){
        global $psDb, $psCore, $psUser;
        $cid = filter_input(INPUT_POST, 'cid');
        $comentario = $psCore->badWords(substr(filter_input(INPUT_POST, 'comentario'), 0, 1000), true);
        //realizamos las consultas
        $consulta = "SELECT c_user FROM p_comentarios WHERE cid = :cid";
        $valores = array('cid' => $cid);
        $datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
        //comprobamos los datos
        if($psUser->admod || ($psUser->user_id == $datos['c_user'] && $psUser->permisos['goepc']) || $psUser->permisos['moedcopo']){
            $consulta2 = "UPDATE p_comentarios SET c_body = :body WHERE cid = :cid";
            $valores2 = array('body' => $comentario, 'cid' => $cid);
            if($psDb->db_execute($consulta2, $valores2)){
                return '1: El comentario fu&eacute; editado correctamente.';
            }else{
                return '0: Ocurri&oacute; un error al editar el comentario. Por favor int&eacute;ntelo de nuevo m&aacute;s tarde.';
            }
        }else{
            return '0: No puedes editar un comentario que no has hecho t&uacute;.';
        }
    }

    /**
     * @funcionalidad borramos el comentario
     * @return type devolvemos un string con el resultado de las consultas
     */
    function borrarComentario(){
        global $psDb, $psCore, $psUser;
        $comid = filter_input(INPUT_POST, 'comid');
        $autor = filter_input(INPUT_POST, 'autor');
        $pid = filter_input(INPUT_POST, 'postid');
        $consulta = "SELECT cid FROM p_comentarios WHERE cid = :cid";
        $valores = array('cid' => $comid);
        //comprobamos si el comentario existe en la db
        if(!$psDb->db_execute($consulta, $valores, 'rowCount')){
            return 'El comentario seleccionado no existe. Por favor, recargue la p&aacute;gina e int&eacute;ntelo de nuevo.';
        }
        //realizamos la consulta para comprobar si el comentario es de un post mio
        $consulta2 = "SELECT post_id FROM p_posts WHERE post_id = :pid AND post_user = :user";
        $valores2 = array('pid' => $pid, 'user' => $psUser->user_id);
        $mipost = $psDb->db_execute($consulta2, $valores2, 'rowCount');
        //realizamos la consulta para comprobar si el comentario es mío
        $consulta3 = "SELECT cid FROM p_comentarios WHERE cid = :cid AND c_user = :user";
        $valores3 = array('cid' => $comid, 'user' => $psUser->user_id);
        $micomentario = $psDb->db_execute($consulta3, $valores3, 'rowCount');
        //ahora realizamos las comprobaciones
        if(!empty($mipost) || (!empty($micomentario) && !empty($psUser->permisos['godpc'])) || !empty($psUser->admod) || !empty($psUser->permisos['moecp'])){
            $consulta4 = "DELETE FROM p_comentarios WHERE cid = :cid AND c_user = :user AND c_post_id = :pid";
            $valores4 = array('cid' => $comid, 'user' => $psUser->user_id, 'pid' => $pid);
            if($psDb->db_execute($consulta4, $valores4)){
                //borramos los votos
                $consulta5 = "DELETE FROM p_votos WHERE tid = :tid";
                $valores5 = array('tid' => $comid);
                $psDb->db_execute($consulta5, $valores5);
                //reducimos en 1 las estadísticas de comentarios
                $update = "UPDATE w_stats SET stats_comments = stats_comments - :stats WHERE stats_no = :no";
                $vupdate = array('stats' => 1, 'no' => 1);
                $psDb->db_execute($update, $vupdate);
                //reducimos en 1 los comentarios en el post
                $update2 = "UPDATE p_posts SET post_comments = post_comments - :pc WHERE post_id = :pid";
                $vupdate2 = array('pc' => 1,'pid' => $pid);
                $psDb->db_execute($update2, $vupdate2);
                //reducimos en 1 los comentarios realizados por el usuario
                $update3 = "UPDATE u_miembros SET user_comentarios = user_comentarios - :uc WHERE user_id = :uid";
                $vupdate3 = array('uc' => 1, 'uid' => $autor);
                $psDb->db_execute($update3, $vupdate3);
                return '1: Comentario borrado correctamente.';
            }else{
                return '0: Ocurri&oacute; un error al realizar la consulta. Por favor int&eacute;ntelo de nuevo m&aacute;s tarde.';
            }
        }else{
            return '0: No tienes permisos para hacer esto.';
        }
    }

    /**
     * @funcionalidad
     * @param type
     * @return type
     */
    function ocultarComentario(){
        global $psDb, $psCore, $psUser;
        if($psUser->admod || $psUser->permisos['moaydcp']){
            //obtenemos los datos
            $consulta = "SELECT cid, c_user, c_post_id, c_status FROM p_comentarios WHERE cid = :cid";
            $valores = array('cid' => filter_input(INPUT_POST, 'comid'));
            $datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
            //actualizamos las tablas en la db
            $operador = ($datos['c_status'] == 1) ? '+' : '-';
            //estadisticas
            $consulta2 = "UPDATE w_stats SET stats_comments = :stats WHERE stats_no = :no";
            $valores2 = array('stats' => 'stats_comments'.$operador.'1', 'no' => 1);
            $psDb->db_execute($consulta2, $valores2);
            //posts
            $consulta3 = "UPDATE p_posts SET post_comments = :post WHERE post_id = :pid";
            $valores3 = array('post' => 'post_comments'.$operador.'1', 'pid' => $datos['c_post_id']);
            $psDb->db_execute($consulta3, $valores3);
            //usuarios
            $consulta4 = "UPDATE u_miembros SET user_comentarios = :user WHERE user_id = :uid";
            $valores4 = array('user' => 'user_comentarios'.$operador.'1', 'uid' => $datos['c_user']);
            $psDb->db_execute($consulta4, $valores4);
            //ocultamos o mostramos los comentarios
            $consulta5 = "UPDATE p_comentarios SET c_status = :status WHERE cid = :cid";
            $valores5 = array('status' => (($datos['c_status'] == 1) ? 0 : 1), 'cid' => filter_input(INPUT_POST, 'comid'));
            if($psDb->db_execute($consulta5, $valores5)){
                if($datos['c_status'] == 1){
                    return '2: El comentario fue habilitado';
                }else{
                    return '1: El comentario fue deshabilitado';
                }
            }else{
                return '0: Ocurri&oacute; un error al realizar la consulta. Por favor int&eacute;ntelo de nuevo m&aacute;s tarde.';
            }
        }else{
            return '0: No tienes permisos para hacer esto.';
        }
    }

    /**
     * @funcionalidad votamos el comentario
     * @return type devolvemos un string con el resultado de la consulta
     */
    function votarComentario(){
        global $psDb, $psCore, $psUser, $psMonitor, $psActividad;
        //obtenemos los datos del voto
        $cid = filter_input(INPUT_POST, 'cid');
        $pid = filter_input(INPUT_POST, 'postid');
        $val = ($_POST['voto'] == 1) ? 1 : 0;
        $voto = ($val == 1) ? '+1' : '-1';
        //comprobamos los permisos
        if($val == 1 && ($psUser->admod || $psUser->permisos['govpp']) || ($val == 0 && ($psUser->admod || $psUser->permisos['govpn']))){
            $consulta = "SELECT c_user FROM p_comentarios WHERE cid = :cid";
            $valores = array('cid' => $cid);
            $datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
            //comprobamos si es mi propio comentario
            if($datos['c_user'] == $psUser->user_id){
                //comprobamos si lo hemos votado anteriormente
                $consulta2 = "SELECT tid FROM p_votos WHERE tid = :tid AND tuser = :user AND type = :type";
                $valores2 = array('tid' => $cid, 'user' => $psUser->user_id, 'type' => 2);
                $votado = $psDb->db_execute($consulta2, $valores2, 'rowCount');
                if(empty($votado)){
                    //sumamos 1 a sus votos
                    $consulta3 = "UPDATE p_comentarios SET c_votos = :votos WHERE cid = :cid";
                    $valores3 = array('votos' => 'c_votos'.$voto, 'cid' => $cid);
                    $psDb->db_execute($consulta3, $valores3);
                    //insertamos los datos en la db
                    $consulta4 = "INSERT INTO p_votos (tid, tuser, type) VALUES (:tid, :user, :type)";
                    if($psDb->db_execute($consulta4, $valores2)){
                        if($val == 1 && $psCore->settings['c_allow_sump'] == 1){
                            $consulta5 = "UPDATE u_miembros SET user_puntos = :puntos WHERE user_id = :uid";
                            $valores5 = array('puntos' => 'user_puntos+'.'1', 'uid' => $datos['c_user']);
                            $psDb->db_execute($consulta5, $valores5);
                            //comprobamos si el usuario sube de rango al votar el comentario
                            $this->subirRango($datos['c_user']);
                        }
                        //añadimos la notificación y la actividad
                        $psMonitor->setNotificacion(8, $datos['c_user'], $psUser->user_id, $pid, $cid, $val);
                        $psActividad->setActividad(6, $pid, $val);
                    }else{
                        return 'Ocurri&oacute; un error al añadir los datos del voto.';
                    }
                }else{
                    return 'Ya has votado este comentario.';
                }
            }else{
                return 'No puedes votar tu propio comentario.';
            }
        }else{
            return 'No tienes permisos para hacer esto.';
        }
    }

    /**
     * @funcionalidad enviamos la notificación cuando se cita el comentario
     * @param type $pid id del post
     * @param type $puser usuario dueño del post
     * @param type $cid id del comentario
     * @param type $comentario el texto del comentario
     * @return type devolvemos un valor booleano con el resultado de la operación
     */
    function citaComentarioNotificacion($pid, $puser, $cid, $comentario){
        global $psCore, $psUser, $psMonitor;
        $id = array();
        $total = 0;
        preg_match_all('/\[quote=(.*?)\]/i', $comentario, $users);
        if(!empty($users[1])) {
            foreach($users[1] as $user){
                //obtenemos los datos
                $datos = explode('|',$user);
                $user = empty($datos[0]) ? $user : $datos[0];
                $ncid = empty($datos[1]) ? $cid : (int)$datos[1];
                //comprobamos
                if($user != $psUser->nick){
                    $uid = $psUser->getUid($psCore->setSecure($user));
                    if(!empty($uid) && $uid != $psUser->user_id && !in_array($uid, $id)){
                        $ids[] = $uid;
                        $psMonitor->setNotificacion(9, $uid, $psUser->user_id, $pd, $ncid);
                    }
                    ++$total;
                }
            }
        }
        //si no fue citado agregamos al monitor al usuario dueño del post
        if(!in_array($puser, $id)){
            $psMonitor->setNotificacion(2, $puser, $psUser->user_id, $pid);
        }
        //enviamos notificaciones a los que siguen el post
        $psMonitor->setNotificacion(7, 2, $psUser->user_id, $pid, 0, $id);
        return true;
    }

    /*****************************************************************************************/
    /******************************** FUNCIONES EXTRA ****************************************/
    /*****************************************************************************************/

    /**
     * @funcionalidad asignamos la medalla al elemento seleccionado
     * @param type obtenemos el id del elemento al que dar la medalla
     */
    function darMedalla($pid){
        global $psDb;
        //obtenemos los datos de los post
        $consulta = "SELECT post_id, post_user, post_puntos, post_hits FROM p_posts WHERE post_id = :pid";
        $valores = array('pid' => $pid);
        $datos = $psDb->db_execute($consulta, $valores);
        //de las demás sólo contamos cuantos hay
        //siguiendo
        $c1 = "SELECT COUNT(follow_id) AS si FROM u_follows WHERE f_id = :fid AND f_type = :type";
        $v1 = array('fid' => $pid, 'type' => 3);
        $q1 = $psDb->db_execute($c1, $v1, 'fetch_num');
        //seguidores
        $c2 = "SELECT COUNT(follow_id) AS se FROM u_follows WHERE f_id = :fid AND f_type = :type";
        $v2 = array('fid' => $pid, 'type' => 2);
        $q2 = $psDb->db_execute($c2, $v2, 'fetch_num');
        //comentarios post
        $c3 = "SELECT COUNT(cid) AS c FROM p_comentarios WHERE c_post_id = :pid AND c_status = :status";
        $v3 = array('pid' => $pid, 'status' => 0);
        $q3 = $psDb->db_execute($c3, $v3, 'fetch_num');
        //favoritos post
        $c4 = "SELECT COUNT(fav_id) AS f FROM p_favoritos WHERE fav_post_id = :fid";
        $v4 = array('fid' => $pid);
        $q4 = $psDb->db_execute($c4, $v4, 'fetch_num');
        //denuncias
        $c5 = "SELECT COUNT(did) AS d FROM w_denuncias WHERE obj_id = :pid AND d_type = :type";
        $v5 = array('pid' => $pid, 'type' => 1);
        $q5 = $psDb->db_execute($c5, $v5, 'fetch_num');
        //medallas
        $c6 = "SELECT COUNT(wm.medal_id) AS m FROM w_medallas AS wm LEFT JOIN w_medallas_assign AS ma ON wm.medal_id = ma.medal_id WHERE wm.m_type = :type AND ma.medal_for = :for";
        $v6 = array('type' => 2, 'for' => $pid);
        $q6 = $psDb->db_execute($c6, $v6, 'fetch_num');
        //ahora obtenemos los datos de las medallas
        $consulta2 = "SELECT medal_id, m_cant, m_cond_post FROM w_medallas WHERE m_type = :type ORDER BY m_cant DESC";
        $valores2 = array('type' => 2);
        $datosMedallas = $psDb->resultadoArray($psDb->db_execute($consulta2, $valores2));

        //obtenemos los datos para dar la medalla
        foreach($datosMedallas as $medalla){
            // DarMedalla
            if($medalla['m_cond_post'] == 1 && !empty($data['post_puntos']) && $medalla['m_cant'] > 0 && $medalla['m_cant'] <= $data['post_puntos']){
                $new = $medalla['medal_id'];
            }elseif($medalla['m_cond_post'] == 2 && !empty($q1[0]) && $medalla['m_cant'] > 0 && $medalla['m_cant'] <= $q1[0]){
                $new = $medalla['medal_id'];
            }elseif($medalla['m_cond_post'] == 3 && !empty($q2[0]) && $medalla['m_cant'] > 0 && $medalla['m_cant'] <= $q2[0]){
                $new = $medalla['medal_id'];
            }elseif($medalla['m_cond_post'] == 4 && !empty($q3[0]) && $medalla['m_cant'] > 0 && $medalla['m_cant'] <= $q3[0]){
                $new = $medalla['medal_id'];
            }elseif($medalla['m_cond_post'] == 5 && !empty($q4[0]) && $medalla['m_cant'] > 0 && $medalla['m_cant'] <= $q4[0]){
                $new = $medalla['medal_id'];
            }elseif($medalla['m_cond_post'] == 6 && !empty($data['post_hits']) && $medalla['m_cant'] > 0 && $medalla['m_cant'] <= $data['post_hits']){
                $new = $medalla['medal_id'];
            }elseif($medalla['m_cond_post'] == 7 && !empty($q5[0]) && $medalla['m_cant'] > 0 && $medalla['m_cant'] <= $q5[0]){
                $new = $medalla['medal_id'];
            }elseif($medalla['m_cond_post'] == 8 && !empty($q6[0]) && $medalla['m_cant'] > 0 && $medalla['m_cant'] <= $q6[0]){
                $new = $medalla['medal_id'];
            }
            //si hay medalla nueva realizamos las consultas en la db
            if(!empty($new)){
                $cn = "SELECT id FROM w_medallas_asign WHERE medal_id = :mid AND medal_for = :for";
                $vn = array('mid' => $new, 'for' => $pid);
                if(!$psDb->db_execute($cn, $vn, 'rowCount')){//si no existe
                    //añadimos en la tabla de medallas asignadas
                    $cn1 = "INSERT INTO w_medallas_asign (medal_id, medal_for, medal_date, medal_ip) VALUES (:mid, :mfor, :mdate, :mip)";
                    $vn1 = array(
                        'mid' => $new,
                        'mfor' => $pid,
                        'mdate' => time(),
                        'mip' => $_SERVER['REMOTE_ADDR']
                    );
                    //añadimos en la tabla de notificaciones
                    $cn2 = "INSERT INTO u_monitor (user_id, obj_uno, obj_dos, not_type, not_date) VALUES (:user, :uno, :dos, :type, :ndate)";
                    $vn2 = array(
                        'user' => $datos['post_user'],
                        'uno' => $new,
                        'dos' => $pid,
                        'type' => 16,
                        'ndate' => time()
                    );
                    //actualizamos en la tabla de medallas
                    $cn3 = "UPDATE w_medallas SET m_total = :total WHERE medal_id = :mid";
                    $vn3 = array('total' => 'm_total'+1, 'mid' => $new);
                    $psDb->db_execute($cn1, $vn1);
                    $psDb->db_execute($cn2, $vn2);
                    $psDb->db_execute($cn3, $vn3);
                }
            }
        }
    }

    /**
     * @funcionalidad subinmos del rango al usuario en funciones de la configuración seleccionada
     * @param type $uid id del usuario
     * @param type $pid valor booleano para saber si se sube sólo con post o con todas las acciones
     * @return type devolvemos un valor booleano con el resultado de la consulta
     */
    function subirRango($uid, $pid = false){
        global $psDb, $psUser, $psCore;
        //obtenemos el rango del usario
        $consulta = "SELECT u.user_puntos, u.user_rango, r.r_type FROM u_miembros AS u LEFT JOIN u_rangos AS r ON u.user_rango = r.rango_id WHERE u.user_id = :uid";
        $valores = array('uid' => $uid);
        $datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
        //si es un rango especial no hacemos nada
        if(empty($datos['r_type']) && $datos['user_rango'] != 3){
            return true;
        }
        //comprobamos si se puede subir por todo o por un post
        if(!empty($pid) && $psCore->settings['c_newr_type'] == 0){
            $conPost = "SELECT post_puntos FROM p_posts WHERE post_id = :pid";
            $valPost = array('pid' => $pid);
            $puntos = $psDb->db_execute($conPost, $valPost, 'fetch_assoc');
            $datos['user_puntos'] = $puntos['post_puntos'];
        }
        //obtenemos los post
        $c1 = "SELECT COUNT(post_id) AS p FROM p_posts WHERE post_user = :user AND post_status = :status";
        $v1 = array('user' => $uid, 'status' => 0);
        $posts = $psDb->db_execute($c1, $v1, 'fetch_num');
        //obtenemos las fotos
        $c2 = "SELECT COUNT(foto_id) AS f FROM f_fotos WHERE f_user = :user AND f_status = :status";
        $fotos = $psDb->db_execute($c2, $v1, 'fetch_num');
        //obtenemos los comentarios
        $c3 = "SELECT COUNT(cid) AS c FROM p_comentarios WHERE c_user = :user AND c_status = :status";
        $comentarios = $psDb->db_execute($c3, $v1, 'fetch_num');
        //obtenemos los rangos
        $c4 = "SELECT rango_id, r_cant, r_type FROM u_rangos WHERE r_type > :type ORDER BY r_cant";
        $v4 = array('type' => 0);
        $rango = $psDb->db_execute($c4, $v4, 'fetch_assoc');
        while($rango){
            //subimos al usuario de rango
            if(!empty($rango['r_cant']) && $rango['r_type'] == 1 && $rango['r_cant'] <= $datos['user_puntos']){
                $new = $rango['rango_id'];
            }elseif(!empty($rango['r_cant']) && $rango['r_type'] == 2 && $rango['r_cant'] <= $posts[0]){
                $new = $rango['rango_id'];
            }elseif(!empty($rango['r_cant']) && $rango['r_type'] == 3 && $rango['r_cant'] <= $fotos[0]){
                $new = $rango['rango_id'];
            }elseif(!empty($rango['r_cant']) && $rango['r_type'] == 4 && $rango['r_cant'] <= $comentarios[0]){
                $new = $rango['rango_id'];
            }
        }
        //si el usuario ha subido de rango lo actualizamos en la db
        if(!empty($new) && $new != $datos['user_rango']){
            $cup = "UPDATE u_miembros SET user_rango = :rango WHERE user_id = :uid";
            $vup = array('rango' => $new, 'uid' => $uid);
            if($psDb->db_execute($cup, $vup)){
                return true;
            }
        }
    }

    /**
     * @funcionalidad obtenemos los favoritos de un usuario
     * @return type devolvemos un string con los datos obtenidos
     */
    function getFavoritos(){
        global $psDb, $psCore, $psUser;
        //realizamos la consulta de datos
        $consulta = "SELECT f.fav_id, f.fav_date, p.post_id, p.post_title, p.post_date, p.post_puntos, COUNT(pc.c_post_id) AS post_comments, c.c_nombre, c.c_seo, c.c_img FROM p_favoritos AS f LEFT JOIN p_posts AS p ON p.post_id = f.fav_post_id LEFT JOIN p_categorias AS c ON c.cid = p.post_category LEFT JOIN p_comentarios AS pc ON p.post_id = pc.c_post_id AND pc.c_status = :status WHERE f.fav_user = :user AND p.post_status = :pstatus GROUP BY pc.c_post_id";
        $valores = array(
            'status' => 0,
            'user' => $psUser->user_id,
            'pstatus' => 0
        );
        $datos = $psDb->resultadoArray($psDb->db_execute($consulta, $valores));
        //creamos un string con los datos
        $favoritos = '';
        foreach($datos as $key => $favorito){
            $favoritos .= '{"fav_id":"'.$favorito['fav_id']
            .'","post_id":"'.$favorito['post_id']
            .'","titulo":"'.$favorito['post_title']
            .'","categoria":"'.$favorito['c_seo']
            .'","categoria_name":"'.$favorito['c_nombre']
            .'","imagen":"'.$favorito['c_img']
            .'","url":"'.$psCore->settings['url']
            .'/posts/'.$favorito['c_seo']
            .'/'.$favorito['post_id']
            .'/'.$psCore->setSEO($favorito['post_title'])
            .'.html","fecha_creado":'.$favorito['post_date']
            .',"fecha_creado_formato":"'.strftime("%d\/%m\/%Y a las %H:%M:%S hs", $favorito['post_date'])
            .'.","fecha_creado_palabras":"'.$psCore->setHaceTiempo($favorito['post_date'], true)
            .'","fecha_guardado":'.$favorito['fav_date']
            .',"fecha_guardado_formato":"'.strftime("%d\/%m\/%Y a las %H:%M:%S hs", $favorito['fav_date'])
            .'.","fecha_guardado_palabras":"'.$psCore->setHaceTiempo($favorito['fav_date'], true)
            .'","puntos":'.$favorito['post_puntos']
            .',"comentarios":'.$favorito['post_comments']
            .'},';
        }
        return $favoritos;
    }

    /**
     * @funcionalidad guardamos un nuevo favorito
     * @return type devolvemos un string con el resultado de la consulta
     */
    function guardarFavorito(){
        global $psDb, $psCore, $psUser, $psMonitor, $psActividad;
        //obtenemos el id del post
        $pid = filter_input(INPUT_POST, 'postid');
        //obtenemos la fecha
        $fecha = empty($_POST['reactivar']) ? time() : filter_input(INPUT_POST, 'reactivar');
        //comprobamos de quien es el post
        $consulta = "SELECT post_user FROM p_posts WHERE post_id = :pid";
        $valores = array('pid' => $pid);
        $datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
        //ahora comprobamos los datos
        if($datos['post_user'] != $psUser->user_id){
            //comprobamos si ya lo tenemos guardado en favoritos
            $consulta2 = "SELECT fav_id FROM p_favoritos WHERE fav_post_id = :pid AND fav_user = :user";
            $valores2 = array('pid' => $pid, 'user' => $psUser->user_id);
            $mi_favorito = $psDb->db_execute($consulta2, $valores2, 'rowCount');
            if(empty($mi_favorito)){
                //insertamos el nuevo favorito en la db
                $consulta3 = "INSERT INTO p_favoritos (fav_user, fav_post_id, fav_date) VALUES (:fuser, :fid, :fdate)";
                $valores3 = array(
                    'fuser' => $psUser->user_id,
                    'fid' => $pid,
                    'fdate' => $fecha
                );
                if($psDb->db_execute($consulta3, $valores3)){
                    //si todo ok agregamos al monitor y a las actividades
                    $psMonitor->setNotificacion(1, $datos['post_user'], $psUser->user_id, $pid);
                    $psActividad->setActividad(2, $pid);
                    return 'Este post ha sido agregado correctamente a tus favoritos.';
                }else{
                    return 'Ocurri&oacute; un error al intentar guardar el favorito en la base de datos.';
                }
            }else{
                return 'Este post ya lo tienes agregado en tus favoritos.';
            }
        }else{
            return 'Lo sentimos, no puedes agregar a favoritos tus propios post.';
        }
    }

    /**
     * @funcionalidad borramos el favorito seleccionado
     * @return type devolvemos un string con el resultado de las consultas
     */
    function borrarFavorito(){
        global $psDb, $psCore, $psUser;
        $fid = filter_input(INPUT_POST, 'fav_id');
        $consulta = "SELECT fav_post_id FROM p_favoritos WHERE fav_id = :fid AND fav_user = :fuser";
        $valores = array('fid' => $fid, 'fuser' => $psUser->user_id);
        $datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
        $mi_favorito = $psDb->db_execute($consulta, $valores, 'rowCount');
        //comprobamos si el favorito es mío
        if(!empty($datos['fav_post_id'])){
            $consulta2 = "DELETE FROM p_favoritos WHERE fav_id = :fid AND fav_user = :fuser";
            if($psDb->db_execute($consulta2, $valores)){
                return 'El favorito ha sido borrado correctamente.';
            }else{
                return 'No pudo borrarse el favorito.';
            }
        }else{
            return 'No pudo borrarse, el favorito no es tuyo.';
        }
    }

    /**
     * @funcionalidad realizamos la consulta en la base de datos en función de los parámetros escogidos
     * @return type devolvemos un array con los datos obtenidos en la consulta
     */
    function getBuscador(){
        global $psDb, $psUser, $psCore;
        $texto = $_GET['q'];
        $cat = $_GET['cat'];
        $autor = $_GET['autor'];
        $extra = $_GET['e'];

        //establecemos los filtros para las consultas
        $consulta = "SELECT COUNT(p.post_id) AS total FROM p_posts AS p WHERE p.post_status = :status";
        $consulta2 = "SELECT p.post_id, p.post_user, p.post_category, p.post_title, p.post_date, p.post_comments, p.post_favoritos, p.post_puntos, u.user_name, c.c_seo, c.c_nombre, c.c_img FROM p_posts AS p LEFT JOIN u_miembros AS u ON u.user_id = p.post_user LEFT JOIN p_categorias AS c ON c.cid = p.post_category WHERE p.post_status = :status";
        $valores['status'] = 0;
        if($cat > 0){
            $consulta .= ' AND p.post_category = :cat';
            $consulta2 .= ' AND p.post_category = :cat';
            $valores['cat'] = (int)$cat;
        }
        //seleccionamos usuario
        if(empty($autor)){
            $consulta .= ' AND MATCH(';
            $consulta2 .= ' AND MATCH(';
            if($extra == 'tags'){
                $consulta .= 'p.post_tags';
                $consulta2 .= 'p.post_tags';
            }else{
                $consulta .= 'p.post_title';
                $consulta2 .= 'p.post_title';
            }
            $consulta .= ") AGAINST(:txt IN BOOLEAN MODE)";
            $consulta2 .= ") AGAINST(:txt IN BOOLEAN MODE)";
            $valores['txt'] = $texto;
        }else{
            $uid = $psUser->getUid($autor);
            //buscamos en los post del usuario sin criterio de búsqueda
            if(empty($texto) && $uid > 0){
                $consulta .= ' AND p.post_user = :puser';
                $consulta2 .= ' AND p.post_user = :puser';
                $valores['puser'] = $uid;
            }else if($uid >= 1){//buscamos en los post del usuario con criterio de búsqueda
                $consulta .= ' AND p.post_user = :puser';
                $consulta2 .= ' AND p.post_user = :puser';
                $valores['puser'] = $uid;
            }
        }
        //obtenemos las páginas
        $consulta .= " ORDER BY p.post_date DESC";
        $consulta2 .= " ORDER BY p.post_date DESC LIMIT :limite, :limite2";
        $total = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
        $total = $total['total'];
        $datos['pages'] = $psCore->getPagination($total, 15);
        $aux = explode(', ',$datos['pages']['limit']);
        $valores['limite'] = (int)$aux[0];
        $valores['limite2'] = (int)$aux[1];
        //obtenemos los datos de la búsqueda
        $datos['data'] = $psDb->resultadoArray($psDb->db_execute($consulta2, $valores));
        //obtenemos los actuales
        $total = explode(',', $datos['pages']['limit']);
        $datos['total'] = $total[0] + count($datos['data']);
        return $datos;
    }

}//cierre de clase
