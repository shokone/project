<?php
//si la constante PS_HEADER no esta definida no se accede al script
if (!defined('PS_HEADER')) {
    exit("No se permite el acceso al script");
}

/**
 * Clase realizada para el control del home del portal
 *
 * @name c.portal.php
 * @author Iván Martínez Tutor
 */
class psPortal{
    /**
     * @funcionalidad instanciamos la clase y la guardamos en una variable estática
     * @staticvar psPortal $instancia instancia de la clase
     * @return \psPortal devolvemos una instancia de la clase
     */
    public static function &getInstance(){
        static $instancia;
        if(is_null($instancia)){
            $instancia = new psPortal();
        }
        return $instancia;
    }

    /**
     * @funcionalidad obtenemos los últimos post para la home
     * @param  [type] $type tipo de post que queremos obtener 
     * @return [type] devolvemos un array con los datos obtenidos
     */
    function getLastPost($type = 'visited'){
        global $psDb, $psUser;
        //realizamos la consulta
        $consulta = "SELECT :last FROM u_portal WHERE user_id = :uid";
        $valores = array(
            'last' => 'last_posts_'.$type,
            'uid' => $psUser->user_id
        );
        $dato = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
        $visitado = unserialize($dato[$valores['last']]);
        ksort($visitado);//ordenamos el array
        foreach($visitado as $key => $valor){
            $consulta2 = "SELECT p.post_id, p.post_user, p.post_category, p.post_title, p.post_date, p.post_puntos, p.post_private, u.user_name, c.c_nombre, c.c_seo, c.c_img FROM p_posts AS p LEFT JOIN u_miembros AS u ON p.post_user = u.user_id LEFT JOIN p_categorias AS c ON p.post_category = c.cid WHERE p.post_status = :status AND p.post_id = :id";
            $valores2 = array('status' => 0, 'id' => $valor);
            $datos[] = $psDb->db_execute($consulta2, $valores2, 'fetch_assoc');
        }
        return $datos;
    }

    /**
     * @funcionalidad obtenemos los post del usuario logueado
     * @return [type] devolvemos un array con los datos si todo ha salido bien
     */
    function getPostPropios(){  
        global $psDb, $psUser, $psCore;
        $consulta = "SELECT last_posts_cats FROM u_portal WHERE user_id = :uid";
        $valores = array('uid' => $psUser->user_id);
        $datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
        //obtenemos las categorias 
        $cat = unserialize($datos['last_posts_cats']);
        //comprobamos
        if(is_array($cat)){
            $cat = implode(',', $cat);
            //obtenemos el total
            $consulta2 = "SELECT COUNT(p.post_id) AS total FROM p_posts AS p WHERE p.post_status = :status AND p.post_category IN (:cats)";
            $valores2 = array('status' => 0, 'cats' => '{$cat}');
            $total = $psDb->db_execute($consulta2, $valores2, 'fetch_assoc');
            //comprobamos
            if($total['total'] > 0){
                $pages = $psCore->getPagination($total['total'], 20);
            }else{
                return false;
            }
            //obtenemos datos de los post
            $consulta3 = "SELECT p.post_id, p.post_category, p.post_title, p.post_date, p.post_puntos, p.post_private, u.user_name, c.c_nombre, c.c_seo, c.c_img FROM p_posts AS p LEFT JOIN u_miembros AS u ON p.post_user = u.user_id LEFT JOIN p_categorias AS c ON c.cid = p.post_category WHERE p.post_status = :status AND p.post_category IN (:cats) ORDER BY p.post_id DESC LIMIT :limite";
            $valores3 = array(
                'status' => 0, 
                'cats' => '{$cat}',
                'limite' => $pages['limit']
            );
            $post['data'] = $psDb->resultadoArray($psDb->db_execute($consulta3, $valores3));
            $post['pages'] = $pages;
            return $post;
        }else{
            return false;
        }
    }

    /**
     * @funcionalidad guardamos la configuración de los post del usuario
     * @return [type] devolvemos un string con el resultado obtenido
     */
    function setConfigPost(){
        global $psDb, $psUser, $psCore;
        $cat = substr(filter_input(INPUT_POST, 'cids'), 0, -1);//quitamos la última coma del string
        $cat = explode(',', $cat);//separamos las categorías en un array
        $cat = serialize($cat);//serializamos el array para incluirlo en la db
        //realizamos la consulta
        $consulta = "UPDATE u_portal SET last_posts_cats = :cat WHERE user_id = :uid";
        $valores = array('cat' => $cat, 'uid' => $psUser->user_id);
        if($psDb->db_execute($consulta, $valores)){
            return 'Tus cambios se han guardado correctamente.';
        }else{
            return 'Ocurri&oacute; un error al intentar guardar los cambios. Por favor int&eacute;ntelo de nuevo m&aacute;s tarde.';
        }
    }

    /**
     * @funcionalidad obtenemos la configuración de los post del usuario
     * @return [type] devolvemos un array con los datos obtenidos
     */
    function getConfigPost(){
        global $psUser, $psDb, $psCore;
        $consulta = "SELECT last_posts_cats FROM u_portal WHERE user_id = :uid";
        $valores = array('uid' => $psUser->user_id);
        $datos = $psDb->db_execute($consulta, $valores, 'fetch_asso');
        //unserializamos el array de categorías
        $datos = unserialize($datos['last_posts_cats']);
        foreach($psCore->settings['categorias'] as $key => $valor){
            if(in_array($valor['cid'], $datos)){
                $valor['check'] = 1;
            }else{
                $valor['check'] = 0;
            }
            $categorias[] = $valor;
        }
        return $categorias;
    }

    /**
     * @funcionalidad obtenemos las noticias del muro
     * @return [type] devolvemos un array con las noticias
     */
    function getNoticias(){
        //obtenemos la clase muro
        include 'c.muro.php';
        $psMuro =& psMuro::getInstance();
        return $psMuro->getNovedades(0);
    }

    /**
     * @funcionalidad obtenemos los favoritos para la home
     * @return [type] devolvemos un array con los datos obtenidos
     */
    function getFavoritos(){
        global $psDb, $psCore, $psUser;
        $consulta = "SELECT COUNT(fav_id) AS total FROM p_favoritos WHERE fav_user = :user";
        $valores = array('user' => $psUser->user_id);
        $total = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
        //comprobamos
        if($total['total'] > 0){
            $pages = $psCore->getPagination($total['total'], 20);
        }else{
            return false;
        }
        $consulta2 = "SELECT f.fav_id, f.fav_date, p.post_id, p.post_title, p.post_date, p.post_puntos, p.post_category, p.post_private, COUNT(pc.c_post_id) AS post_comments, c.c_nombre, c.c_seo, c.c_img FROM p_favoritos AS f LEFT JOIN p_posts AS p ON p.post_id = f.fav_post_id LEFT JOIN p_categorias AS c ON c.cid = p.post_category LEFT JOIN p_comentarios AS pc ON p.post_id = pc.c_post_id WHERE pc.c_status = :status AND f.fav_user = :user AND p.post_status = :status2 GROUP BY pc.c_post_id ORDER BY f.fav_date DESC LIMIT :limite";
        $valores2 = array(
            'status' => 0,
            'user' => $psUser->user_id,
            'status2' => 0,
            'limite' => $pages['limit']
        );
        $datos['data'] = $psDb->resultadoArray($psDb->db_execute($consulta2, $valores2));
        $datos['pages'] = $pages;
        return $datos;
    }

    /**
     * @funcionalidad obtenemos las estadísticas principales del sitio
     * @return [type] devolvemos un array con los datos obtenidos
     */
    function getStats(){
        global $psCore, $psDb;
        //obtenemos las estadísticas
        $consulta = "SELECT stats_max_online, stats_max_time, stats_time, stats_time_cache, stats_miembros, stats_posts, stats_fotos, stats_comments, stats_foto_comments FROM w_stats WHERE stats_no = :stats";
        $valores = array('stats' => 1);
        $datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
        //obtenemos las estadísticas de cada tabla
        if($datos['stats_time_cache'] < (time() - ($psCore->settings['c_stats_cache'] * 60))){
            //usuarios
            $c1 = "SELECT COUNT(user_id) AS u FROM u_miembros WHERE user_activo = :activo AND user_baneado = :ban";
            $v1 = array('activo' => 1, 'ban' => 0);
            //posts
            $c2 = "SELECT COUNT(post_id) AS p FROM p_posts WHERE post_status = :status";
            //fotos
            $c3 = "SELECT COUNT(foto_id) AS f FROM f_fotos WHERE f_status = :status";
            //comentarios post
            $c4 = "SELECT COUNT(cid) AS c FROM p_comentarios WHERE c_status = :status";
            //comentarios fotos
            $c5 = "SELECT COUNT(cid) AS fc FROM f_comentarios";
            $v2 = array('status' => 0);
            //ejecutamos las consultas
            $q1 = $psDb->db_execute($c1, $v1, 'fetch_num');
            $q2 = $psDb->db_execute($c2, $v2, 'fetch_num');
            $q3 = $psDb->db_execute($c3, $v2, 'fetch_num');
            $q4 = $psDb->db_execute($c4, $v2, 'fetch_num');
            $q5 = $psDb->db_execute($c5, null, 'fetch_num');
            //pasamos los datos obtenidos al array datos
            $datos['stats_miembros'] = $q1[0];
            $datos['stats_posts'] = $q2[0];
            $datos['stats_fotos'] = $q3[0];
            $datos['stats_comments'] = $q4[0];
            $datos['stats_foto_comments'] = $q5[0];
            $new_valores = array(
                'stats_time' => time(),
                'stats_time_cache' => time(),
                'stats_miembros' => $datos['stats_miembros'],
                'stats_posts' => $datos['stats_posts'],
                'stats_fotos' => $datos['stats_fotos'],
                'stats_comments' => $datos['stats_comments'],
                'stats_foto_comments' => $datos['stats_foto_comments'],
            );
        }
        //obtenemos el tiempo para comprobar si el usuario está online
        $online = (time() - ($psCore->settings['c_last_active'] * 60));
        //comprobamos si contamos en las stats todos los usuarios o solo los registrados
        if(empty($psCore->settings['c_count_guests'])){
            $consulta2 = "SELECT COUNT(user_id) AS u FROM u_miembros WHERE user_lastactive > :user_lastactive";
            $valores2 = array('user_lastactive' => $online);
            $query = $psDb->db_execute($consulta2, $valores2, 'fetch_num');
        }else{
            $consulta2 = "SELECT COUNT(DISTINCT session_ip) AS s FROM u_sessions WHERE session_time > :session_time";
            $valores2 = array('session_time' => $online);
            $query = $psDb->db_execute($consulta2, $valores2, 'fetch_num');
        }   
        $datos['stats_online'] = $query[0];
        if($datos['stats_online'] > $datos['stats_max_online']){
            $consulta3 = "UPDATE w_stats SET stats_time = :stats_time, stats_time_cache = :stats_time_cache, stats_miembros = :stats_miembros, stats_posts = :stats_posts, stats_fotos = :stats_fotos, stats_comments = :stats_comments, stats_foto_comments = :stats_foto_comments, stats_max_online = :stats_max_online, stats_max_time = :stats_max_time, stats_max_online = :stats_max_online, stats_max_time = :stats_max_time";
            $new_valores['stats_max_online'] = $datos['stats_online'];
            $new_valores['stats_max_time'] = time();
        }else{
            $consulta3 = "UPDATE w_stats SET stats_time = :stats_time, stats_time_cache = :stats_time_cache, stats_miembros = :stats_miembros, stats_posts = :stats_posts, stats_fotos = :stats_fotos, stats_comments = :stats_comments, stats_foto_comments = :stats_foto_comments";
        }
        //actualizamos datos en la db
        $psDb->db_execute($consulta3, $new_valores);
        return $datos;
    }

    /**
     * @funcionalidad obtenemos las fotos
     * @return [type] devolvemos un array con las últimas fotos
     */
    function getFotos(){
        //obtenemos la clase fotos
        include 'c.fotos.php';
        $psFotos =& psFotos::getInstance();
        return $psFotos->getLastFotos();
    }
}