<?php
//si la constante PS_HEADER no esta definida no se accede al script
if (!defined('PS_HEADER')) {
    exit("No se permite el acceso al script");
}

/**
 * Clase realizada para el control de las notificaciones
 *
 * @name c.monitor.php
 * @author Iván Martínez Tutor
 */
class psMonitor{
    /**
     * @funcionalidad instanciamos la clase y la guardamos en una variable estática
     * @staticvar psMonitor $instancia instancia de la clase
     * @return \psMonitor devolvemos una instancia de la clase
     */
    public static function &getInstance(){
        static $instancia;
        if(is_null($instancia)){
            $instancia = new psMonitor();
        }
        return $instancia;
    }

    //creamos las variables por defecto
    public $monitor = array();//texto de cada notificación
    public $notificaciones = 0;//número de notificaciones nuevas
    public $avisos = 0;//número de avisos nuevos
    public $mostrarTipo = 1;//forma de mostrar las notificaciones (al actualizar la página o por ajax)

    /**
     * @funcionalidad comprobamos si el usuario está loguead o no
     * Si ha entrado a su cuenta cargamos sus notificaciones y avisos de la db
     */
    public function __construct(){
        global $psDb, $psUser;
        //comprobamos si el usuario se ha logueado o es un visitante
        if(empty($psUser->member)){
            //si es visitante no hacemos nada
            return false;
        }
        //si se ha logueado obtenemos los datos de la db
        //obtenemos las notificaciones
        $consulta = "SELECT COUNT(not_id) AS total FROM u_monitor WHERE user_id = :uid AND not_menubar > :not";
        $valores = array('uid' => $psUser->user_id, 'not' => 0);
        $datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
        $this->notificaciones = $datos['total'];
        //y ahora los avisos
        $consulta2 = "SELECT COUNT(av_id) AS total FROM u_avisos WHERE user_id = :uid AND av_read = :not";
        $datos2 = $psDb->db_execute($consulta2, $valores, 'fetch_assoc');
        $this->avisos = $datos2['total'];
    }

    /**
     * @funcionalidad creamos los diferentes textos para las notificaciones
     * se cargará principalmente para acelerar la carga de otras funciones
     */
    function crearTextoMonitor(){
        //creamos los diferentes textos para cada notificación
        $this->monitor = array(
            1 => array('text' => 'agreg&oacute; a favoritos tu', 'ln_text' => 'post', 'css' => 'starPost'),
            2 => array('text' => array('coment&oacute; tu','_DATO_ nuevos comentarios en tu'), 'ln_text' => 'post', 'css' => 'comment_post'),
            3 => array('text' => 'dej&oacute; _DATO_ puntos en tu', 'ln_text' => 'post', 'css' => 'points'),
            4 => array('text' => 'te est&aacute; siguiendo', 'ln_text' => 'Seguir a este usuario', 'css' => 'follow'),
            5 => array('text' => 'cre&oacute; un nuevo', 'ln_text' => 'post', 'css' => 'post'),
            6 => array('text' => array('te recomienda un', '_DATO_ usuarios te recomiendan un'), 'ln_text' => 'post', 'css' => 'share'),
            7 => array('text' => array('coment&oacute; en un', '_DATO_ nuevos comentarios en el'), 'ln_text' => 'post', 'extra' => 'que sigues', 'css' => 'blue_ball'),
            8 => array('text' => array('vot&oacute; _DATO_ tu', '_DATO_ nuevos votos a tu'), 'ln_text' => 'comentario', 'css' => 'voto_'),
            9 => array('text' => array('respondi&oacute; tu', '_DATO_ nuevas respuestas a tu'), 'ln_text' => 'comentario', 'css' => 'comment_resp'),
            10 => array('text' => 'subi&oacute; una nueva', 'ln_text' => 'foto', 'css' => 'photo'),
            11 => array('text' => array('coment&oacute; tu','_DATO_ nuevos comentarios en tu'), 'ln_text' => 'foto', 'css' => 'photo'),
            12 => array('text' => 'public&oacute; en tu', 'ln_text' => 'muro', 'css' => 'wall_post'),
            13 => array('text' => array('coment&oacute; ', '_DATO_ nuevos comentarios en'), 'ln_text' => 'publicaci&oacute;n', 'extra' => 'coment&oacute;', 'css' => 'w_comment'),
            14 => array('text' => array('le gusta tu', 'A _DATO_ personas les gusta tu'), 'ln_text' => array('publicaci&oacute;n','comentario'), 'css' => 'w_like'),
            15 => array('text' => 'Recibiste una medalla', 'css' => 'medal'),
            16 => array('text' => 'Tu post recibi&oacute; una medalla', 'css' => 'medal'),
            17 => array('text' => 'Tu foto recibi&oacute; una medalla', 'css' => 'medal'),
        );
    }

    /**
     * @funcionalidad obtenemos los avisos del usuario
     * @return [type]  devolvemos un array con los datos obtenidos
     */
    function getAviso(){
        global $psDb, $psUser;
        $consulta = "SELECT * FROM u_avisos WHERE user_id = :uid";
        $valores = array('uid' => $psUser->user_id);
        $query = $psDb->db_execute($consulta, $valores);
        $datos = $psDb->resultadoArray($query);
        return $datos;
    }

    /**
     * @funcionalidad insertamos un nuevo aviso en la db, si el usuario está baneado no hacemos nada
     * @param  [type] $uid id del usuario
     * @param  [type] $asunto asunto del aviso
     * @param  [type] $body cuerpo de texto del aviso
     * @param  [type] $type tipo de aviso
     * @return [type] devolvemos un valor booleano con el resultado de la consulta
     */
    function setAviso($uid, $asunto = 'Sin asunto', $body, $type = 0){
        global $psDb, $psCore;
        $consulta = "SELECT user_baneado FROM u_miembros WHERE user_id = :uid";
        $valores = array('uid' => $uid);
        $datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
        //si el usuario está baneado no le enviamos el aviso
        if($datos['user_baneado'] == 1){
            return true;
        }
        //si no realizamos la inserción del aviso en la db
        $consulta2 = "INSERT INTO u_avisos (user_id, av_subject, av_body, av_date, av_type) VALUES (:uid, :subject, :body, :dates, :type)";
        $valores2 = array(
            'uid' => $uid,
            'subject' => $asunto,
            'body' => $body,
            'dates' => time(),
            'type' => $type
        ); 
        if($psDb->db_execute($consulta2, $valores2)){
            return true;
        }else{
            return false;
        }
    }

    /**
     * @funcionalidad leemos el aviso y lo descontamos de los avisos sin leer
     * @param  [type] $avid obtenemos el id del aviso
     * @return [type]  devolvemos un array con los datos obtenidos
     */
    function leerAviso($aid){
        global $psDb, $psUser;
        $consulta = "SELECT * FROM u_avisos WHERE av_id = :aid";
        $valores = array('aid' => $aid);
        $datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
        //comprobamos los datos del aviso
        if(empty($datos['av_id']) || $datos['user_id'] != $psUser->user_id && !$psUser->admod == 1){
            return 'Este aviso no existe, sentimos el fallo.';
        }else{
            //si todo ok actualizamos el aviso a leido y lo restamos del contador de avisos
            $consulta2 = "UPDATE u_avisos SET av_read = :read WHERE av_id = :aid";
            $valores = array('read' => 1, 'aid' => $aid);
            $psDb->db_execute($consulta2, $valores2);
            $this->avisos -= 1;
            return $datos;
        }
    }

    /**
     * @funcionalidad borramos el aviso seleccionado
     * @param  [type] $avid obtenemos el id del aviso
     * @return [type]  devolvemos un valor booleano con el resultado de la operación
     */
    function borrarAviso($avid){
        global $psDb, $psUser;
        $consulta = "SELECT user_id FROM u_avisos WHERE av_id = :avid";
        $valores = array('avid' => $avid);
        $datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
        //comprobamos los datos del aviso
        if(empty($datos['user_id']) || $datos['user_id'] != $psUser->user_id && !$psUser->admod == 1){
            return false;
        }else{
            //si todo ok actualizamos el aviso a leido y lo restamos del contador de avisos
            $consulta2 = "DELETE FROM u_avisos WHERE av_id = :avid";
            $psDb->db_execute($consulta2, $valores);
            return true;
        }
    }

    /**
     * @funcionalidad obtenemos las notificaciones del usuario
     * @param  [type]  $sinleer comprobamos si hay notificaciones sin leer o no
     * @return [type]  devolvemos un array con los datos obtenidos
     */
    function getNotificaciones($sinleer = false){
        global $psDb, $psCore, $psUser;
        //si hay más de 5 notificaciones mostramos todas las que no se hayan leído
        if($this->mostrarTipo == 1){
            $ndel = ($sinleer == true) ? 1 : 0;
            if($this->notificaciones > 5 || $sinleer == true){
                $con_process = "SELECT m.*, u.user_name AS usuario FROM u_monitor AS m LEFT JOIN u_miembros AS u ON m.obj_user = u.user_id WHERE m.user_id = :uid AND m.not_menubar ";
                if ($sinleer == true) {
                    $con_process .= ' = :nview';
                    $nview = 2;
                }else{
                    $con_process .= ' > :nview';
                    $nview = 0;
                }
                $con_process .= " ORDER BY m.not_id DESC";
                $val_process = array('uid' => $psUser->user_id, 'nview' => $nview);
            }else{
                $con_process = "SELECT m.*, u.user_name AS usuario FROM u_monitor AS m LEFT JOIN u_miembros AS u ON m.obj_user = u.user_id WHERE m.user_id = :uid ORDER BY m.not_id DESC";
                $val_process = array('uid' => $psUser->user_id);
            }
        }else if($this->mostrarTipo == 2){//si por el contrario va al monitor actualizamos para que ya no se vea en el menu
            $con_process = "SELECT m.*, u.user_name AS usuario FROM u_monitor AS m LEFT JOIN u_miembros AS u ON m.obj_user = u.user_id WHERE m.user_id = :uid ORDER BY m.not_id DESC";
            $val_process = array('uid' => $psUser->user_id);
            //obtenemos las estadísticas
            //posts
            $consulta = "SELECT follow_id FROM u_follows WHERE f_user = :uid AND f_type = :type";
            $valores = array('uid' => $psUser->user_id, 'type' => 3);
            $datos['stats']['posts'] = $psDb->db_execute($consulta, $valores, 'rowCount');
            //seguidores
            $consulta2 = "SELECT follow_id FROM u_follows WHERE f_id = :uid AND f_type = :type";
            $valores2 = array('uid' => $psUser->user_id, 'type' => 1);
            $datos['stats']['seguidores'] = $psDb->db_execute($consulta2, $valores2, 'rowCount');
            //siguiendo
            $consulta3 = "SELECT follow_id FROM u_follows WHERE f_user = :uid AND f_type = :type";
            $datos['stats']['siguiendo'] = $psDb->db_execute($consulta3, $valores2, 'rowCount');
            //ahora cargamos los filtros
            $consulta4 = "SELECT c_monitor FROM u_portal WHERE user_id = :uid";
            $valores4 = array('uid' => $psUser->user_id);
            $filtro = $psDb->db_execute($consulta4, $valores4, 'fetch_assoc');
            $filtro = unserialize($filtro['c_monitor']);
            foreach($filtro as $key => $valor){
                $datos['filtro'][$valor] = true;
            }
        }
        //obtenemos los procesos
        $datos2 = $psDb->resultadoArray($psDb->db_execute($con_process, $val_process));
        //actualizamos los datos
        if($this->mostrarTipo == 1){
            $consulta5 = "UPDATE u_monitor SET not_menubar = :menu WHERE user_id = :uid AND not_menubar > :menu2";
            $valores5 = array('menu' => $ndel, 'uid' => $psUser->user_id, 'menu2' => 0);
            $psDb->db_execute($consulta5, $valores5);
        }else{
            $consulta5 = "UPDATE u_monitor SET not_menubar = :menu, not_monitor = :monitor WHERE user_id = :uid AND not_monitor = :monitor2";
            $valores5 = array('menu' => 0, 'monitor' => 0, 'uid' => $psUser->user_id, 'monitor2' => 1);
            $psDb->db_execute($consulta5, $valores5);
        }   
        //montamos textos y enlaces
        $datos['data'] = $this->montarNotificaciones($datos2);
        //obtenemos el total de notificaciones
        $datos['total'] = count($datos['data']);
        return $datos;
    }

    /**
     * @funcionalidad enviamos una notificación cuando un usuario siga un post o a otro usuario
     * @param  [type] $type tipo de la notificación
     * @param  [type] $follow tipo de seguimiento (post o user)
     * @param  [type] $uid id del usuario
     * @param  [type] $objuno id del post
     * @param  [type] $objdos segundo dato para notificar en caso de utilizarlo (aquí lo cargamos para hacer más fácil la carga en setNotificaciones())
     * @param  [type] $denegar array con los usuarios denegados
     * @return [type] devolvemos un valor booleano para terminar con la función
     */
    function setFollowNotificaciones($type, $follow, $uid, $pid, $objdos = 0, $denegar){
        global $psDb, $psCore;
        //obtenemos si se sigue un post o un user
        if($follow == 1){
            $id = $uid;//user
        }else if($follow == 2){
            $id = $pid;//post
        }
        //ahora buscamos en la db a los usuarios que sigan al post o usuario
        $consulta = "SELECT f_user FROM u_follows WHERE f_id = :fid AND f_type = :type";
        $valores = array('fid' => $id, 'type' => $follow);
        $datos = $psDb->resultadoArray($psDb->db_execute($consulta, $valores));
        foreach($datos as $key => $valor){
            //si el usuario no esta en los denegados le mandamos una notificación
            if(!in_array($valor['f_user'], $denegar)){
                $this->setNotificacion($type, $valor['f_user'], $uid, $pid, $objdos);
            }
        }
        return true;
    }

    /**
     * @funcionalidad guardamos los datos de las notificaciones en la db
     * @param  [type] $type tipo de la notificación
     * @param  [type] $uid id del usuario
     * @param  [type] $user usuario
     * @param  [type] $uno primer objeto (por defecto 0 por si no existe)
     * @param  [type] $dos segundo objeto (por defecto 0 por si no existe)
     * @param  [type] $tres tercer objeto (por defecto 0 por si no existe)
     * @return [type] devolvemos un valor booleano con el resultado de la operación
     */
    function setNotificacion($type, $uid, $user, $uno = 0, $dos = 0, $tres = 0){
        global $psDb, $psUser, $psCore;
        //comprobamos si el usuario es el mismo que está logueado, si es así no hacemos nada
        if($uid != $psUser->user_id){
            //verificamos si el usuario admite notificaciones del tipo obtenido por parámetro
            $allow = $this->permitirNotificaciones($type, $uid);
            if(empty($allow)) return true;
            //comprobamos cuantas notificaciones tenemos del mismo tipo y en un rango pequeño de tiempo
            $tiempo = time() - 3600;//obtenemos el tiempo de hace una hora
            $consulta = "SELECT not_id FROM u_monitor WHERE user_id = :uid AND obj_uno = :uno AND obj_dos = :dos AND obj_tres = :tres AND not_type = :type AND not_date > :tiempo AND not_menubar > :menu ORDER BY not_id DESC";
            $valores = array(
                'uid' => $uid,
                'uno' => $uno,
                'dos' => $dos,
                'tres' => $tres,
                'type' => $type,
                'tiempo' => $tiempo,
                'menu' => 0
            );
            $datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
            if(!empty($datos['not_id']) && $type != 4){//si los datos son correctos actualizamos
                $ntype = 'update';
            }else{//si no insertamos
                $ntype = 'insert';
            }
            //comprobamos el límite de notificaciones establecido
            $consulta2 = "SELECT not_id FROM u_monitor WHERE user_id = :uid ORDER BY not_id DESC";
            $valores2 = array('uid' => $uid);
            $datos2 = $psDb->resultadoArray($psDb->db_execute($consulta2, $valores2));
            $total = count($datos2);
            $lastid = $datos[$total - 1]['not_id'];
            //eliminamos las notificaciones si han superado el límite
            if($total > $psCore->settings['c_max_nots']){
                $consulta3 = "DELETE FROM u_monitor WHERE not_id = :nid";
                $valores3 = array('nid' => $lastid);
                $psDb->db_execute($consulta3, $valores3);
            }
            //realizamos la inserción o actualización de datos
            if($ntype == 'update'){
                $consulta4 = "UPDATE u_monitor SET obj_user = :user, not_date = :ndate, not_total = :total WHERE not_id = :nid";
                $valores4 = array(
                    'user' => $user,
                    'ndate' => time(),
                    'total' => 'not_total'+1,
                    'nid' => $datos['not_id']
                );
                if($psDb->db_execute($consulta4, $valores4)){
                    return true;
                }
            }else{
                $consulta4 = "INSERT INTO u_monitor (user_id, obj_user, obj_uno, obj_dos, obj_tres, not_type, not_date) VALUES (:uid, :user, :uno, :dos, :tres, :type, :ndate)";
                $valores4 = array(
                    'uid' => $uid,
                    'user' => $user,
                    'uno' => $uno,
                    'dos' => $dos,
                    'tres' => $tres,
                    'type' => $type,
                    'ndate' => time()
                 );
                if($psDb->db_execute($consulta4, $valores4)){
                    return true;
                }
            }
        }
    }

    /**
     * @funcionalidad realizamos la consulta y enviamos la notificación simplificandolo en una función más pequeña
     * @param  [type] $datos obtenemos un array con los datos necesarios para la notificación
     * @return [type] devolvemos un array con los datos obtenidos
     */
    function montarNotificaciones($datos){
        global $psDb;
        $this->crearTextoMonitor();
        foreach($datos as $key => $valor){
            //creamos la consulta
            $query = $this->crearConsulta($valor);
            if(is_array($query) && $query['consulta'] == null){
                $dat = $query;
            }else{
                $dat = $psDb->db_execute($query['consulta'], $query['valores'], 'fetch_assoc');
            }
            //combinamos los arrays
            $dat = array_merge($dat, $valor);
            //si el array tiene datos notificamos al usuario
            if($dat){
                $dato[] = $this->crearFrase($dat);
            }
        }
        return $dato;
    }

    /**
     * @funcionalidad obtenemos los datos del usuario que ha comentado y mandamos notificación al dueño y a los usuarios que han comentado
     * @param  [type] $pub id de la publicación
     * @param  [type] $user id del usuario
     * @param  [type] $pub_user usuario que ha publicado
     */
    function setMuroRespond($pub, $user, $pub_user){
        global $psDb, $psUser;
        //obtenemos los datos del usuario que ha comentado si no somos nosotros
        $consulta = "SELECT c_user FROM u_muro_comentarios WHERE pub_id = :pub AND c_user NOT IN (:uid, :uid2)";
        $valores = array(
            'pub' => $pub,
            'uid' => $psUser->user_id,
            'uid2' => $user
        );
        $datos = $psDb->resultadoArray($psDb->db_execute($consulta, $valores));
        //enviamos una notificación a los usuarios que han comentado
        $enviado = array();
        foreach($datos as $key => $valor){
            if(!in_array($valor['c_user'], $enviado)){
                $this->setNotificacion(13, $valor['c_user'], $psUser->user_id, $pub, 3);
                $enviado[] = $valor['c_user'];
            }
        }
        //enviamos una notificación al dueño del muro comentado
        $this->setNotificacion(13, $user, $psUser->user_id, $pub, 1);
        //ahora si el que publicó no fue el dueño le enviamos también una notificación
        if(($user != $pub_user) && !in_array($pub_user, $enviado)){
            $this->setNotificacion(13, $pub_user, $psUser->user_id, $pub, 2);
        }
    }

    /**
     * @funcionalidad creamos la consulta necesaria para la petición
     * @param  [type] obtenemos el tipo de consulta a realizar
     * @return [type] devolvemos un array con los datos necesarios para ejecutar la consulta
     */
    function crearConsulta($datos){
        global $psUser;
        //obtenemos el tipo de notificación para saber que consulta realizar
        switch($datos['not_type']){
            //posts
            case 1: case 2: case 3: case 5: case 6: case 7: case 8: case 9:
                $consulta = "SELECT p.post_id, p.post_user, p.post_title, c.c_seo FROM p_posts AS p LEFT JOIN p_categorias AS c ON p.post_category = c.cid WHERE p.post_id = :pid";
                $valores = array('pid' => $datos['obj_uno']);
                return array('consulta' => $consulta, 'valores' => $valores);
            //seguidos
            case 4:
                $seguido = $psUser->follow($datos['obj_user']);
                return array('follow' => $seguido);
            //publicación en tu muro
            case 12:
                $consulta = "SELECT p.pub_id, u.user_name FROM u_muro AS p LEFT JOIN u_miembros AS u ON p.p_user_pub = u.user_id WHERE p.pub_id = :pid";
                $valores = array('pid' => $datos['obj_uno']);
                return array('consulta' => $consulta, 'valores' => $valores);
            case 13:
                //comprobamos si hay alguna notificación más del mismo tipo
                $consulta = "SELECT p.pub_id, p.p_user, p.p_user_pub, u.user_name FROM u_muro AS p LEFT JOIN u_miembros AS u ON p.p_user = u.user_id WHERE p.pub_id = :pid";
                $valores = array('pid' => $datos['obj_uno']);
                $query = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
                $query['p_user_resp'] = $datos['obj_user'];//obtenemos el mensaje del usuario
                $query['p_user_name'] = $datos['user_name'];//obtenemos el dueño del muro
                $query['user_name'] = $psUser->getUserName($datos['obj_user']);//obtenemos el nombre del usuario que lo publicó
                return $query;
            //comentarios en el muro
            case 14:
                if($datos['obj_dos'] == 2){
                    $consulta = "SELECT pub_id AS obj_uno, c_body FROM u_muro_comentarios WHERE cid = :cid";
                    $valores = array('cid' => $datos['obj_uno']);
                    return array('consulta' => $consulta, 'valores' => $valores);
                }else{
                    return array('value' => 'hack');
                }
            //medallas
            case 15:
                $consulta = "SELECT medal_id, m_title, m_image FROM w_medallas WHERE medal_id = :mid";
                $valores = array('mid' => $datos['obj_uno']);
                return array('consulta' => $consulta, 'valores' => $valores);
            //asignar medallas
            case 16:
                $consulta = "SELECT p.post_id, p.post_title, c.c_seo, m.medal_id, m.m_title, m.m_image, a.medal_for FROM w_medallas_assign AS a LEFT JOIN p_posts AS p ON p.post_id = a.medal_for LEFT JOIN p_categorias AS c ON c.cid = p.post_category LEFT JOIN w_medallas AS m ON m.medal_id = a.medal_id WHERE m.medal_id = :mid AND p.post_id = :pid";
                $valores = array('mid' => $datos['obj_uno'], 'pid' => $datos['obj_dos']);
                return array('consulta' => $consulta, 'valores' => $valores);
            //fotos
            case 17:
                $consulta = "SELECT f.foto_id, f.f_title, f.f_user, m.medal_id, m.m_title, m.m_image, a.medal_for, u.user_id, u.user_name FROM w_medallas_assign AS a LEFT JOIN f_fotos AS f ON f.foto_id = a.medal_for LEFT JOIN u_miembros AS u ON u.user_id = f.f_user LEFT JOIN w_medallas AS m ON m.medal_id = a.medal_id WHERE m.medal_id = :mid AND f.foto_id = :fid";
                $valores = array('mid' => $datos['obj_uno'], 'fid' => $datos['obj_dos']);
                return array('consulta' => $consulta, 'valores' => $valores);
        }
    }

    /**
     * @funcionalidad construimos la frase para la notificación
     * @param  [type] $datos obtenemos un array con los datos necesarios para construir la frase
     * @return [type] devolvemos un array con los datos obtenidos
     */
    function crearFrase($datos){
        global $psDb, $psCore, $psUser;
        //variables locales a esta función
        $url = $psCore->settings['url'];
        $type = $datos['not_type'];
        $text = $this->monitor[$type]['ln_text'];
        $text = is_array($text) ? $text[$datos['obj_dos']-1] : $text;
        
        $text_extra = ($this->mostrarTipo == 1) ? '' : ' '.$this->monitor[$type]['ln_text'];
        //creamos el array para la frase
        $frase = array(
            'unread' => ($this->mostrarTipo == 1) ? $datos['not_menubar'] : $datos['not_monitor'],
            'style' => $this->monitor[$type]['css'],
            'date' => $datos['not_date'],
            'user' => $datos['usuario'],
            'avatar' => $datos['obj_user'].'_50.jpg',
            'total' => $datos['not_total'],
        );
        //escogemos la oración a construir en función del tipo
        switch($type){
            //posts
            case 1: case 3: case 5:
                $frase['text'] = $this->monitor[$type]['text'].$text_extra;
                if($type == 3){
                    $frase['text'] = str_replace('_DATO_', '<b>'.$datos['obj_dos'].'</b>', $frase['text']);
                }
                $frase['link'] = $url.'/posts/'.$datos['c_seo'].'/'.$datos['post_id'].'/'.$psCore->setSEO($datos['post_title']).'.html';
                $frase['ltext'] = ($this->mostrarTipo == 1) ? $text : $datos['post_title'];
                $frase['ltit'] = ($this->mostrarTipo == 1) ? $datos['post_title'] : '';
                break;
            //follows
            case 4:
                //formamos la frase
                $frase['text'] = $this->monitor[$type]['text'];
                if($datos['follow'] != true && $this->mostrarTipo == 2){
                    $frase['link'] = '#" onclick="notifica.follow(\'user\','.$datos['obj_user'].', notifica.userInMonitorHandle, this)';
                    $frase['ltext'] = $this->monitor[$type]['ln_text'];
                }
                break;
            //comentarios post
            case 2: case 6: case 7: case 8: case 9:
                //obtenemos el total
                $total = $datos['not_total'];
                //si hay más de 1 acción
                if($total > 1){
                    $txt = $this->monitor[$type]['text'][1].$text_extra;
                    $frase['text'] = str_replace('_DATO_', '<b>{$total}</b>', $txt);
                }else{
                    $frase['text'] = $this->monitor[$type]['text'][0].$text_extra;
                }
                //compruebo si el post mío
                if($datos['post_user'] == $psUser->user_id) {
                    $frase['text'] = str_replace('te recomienda un', 'ha recomendado tu', $frase['text']);
                }
                //comentario
                if($type == 8 || $type == 9){
                    $cid = '#div_comnt_'.$datos['obj_dos'];
                    //cargamos extras
                    if($type == 8){
                        $voto = ($datos['obj_tres'] == 0) ? 'negativo' : 'positivo';
                        $frase['text'] = str_replace('_DATO_', '<b>'.$voto.'</b>', $frase['text']);
                        $frase['style'] = 'voto_'.$voto;
                    }
                }
                //formamos la frase
                $frase['link'] = $url.'/posts/'.$datos['c_seo'].'/'.$datos['post_id'].'/'.$psCore->setSEO($datos['post_title']).'.html'.$cid;
                $frase['ltext'] = ($this->mostrarTipo == 1) ? $text : $datos['post_title'];
                $frase['ltit'] = ($this->mostrarTipo == 1) ? $datos['post_title'] : '';
                break;
            //publicaciones en el muro
            case 12:
                $frase['text'] = $this->monitor[$type]['text'].$text_extra;
                $frase['link'] = $url.'/perfil/'.$psUser->nick.'/'.$datos['obj_uno'];
                $frase['ltext'] = ($this->mostrarTipo == 1) ? $text : $psUser->nick;
                $frase['ltit'] = ($this->mostrarTipo == 1) ? $psUser->nick : '';
                break;
            case 13: 
                //comprobamos de quien es el comentario
                if($psUser->user_id == $datos['p_user']){
                    $from = 'tu';
                }else if($datos['p_user'] == $datos['p_user_resp']){
                    $from = 'su';
                }else{
                    $from = ' la publicaci&oacute;n de';
                }
                //ahora obtenemos el total
                $total = $datos['not_total'];
                if($total > 1){
                    $txt = $this->monitor[$type]['text'][1] . $from . $text_extra;
                    $frase['text'] = str_replace('_DATO_', '<b>' . $total . '</b>', $txt);
                }else{
                    $frase['text'] = $this->monitor[$type]['text'][0] . $from . $text_extra;
                }
                //formamos la frase
                $frase['link'] = $url.'/perfil/'.$datos['p_user_name'].'/'.$datos['pub_id'];
                $frase['ltext'] = ($this->mostrarTipo == 1) ? $text : $psUser->nick;
                $frase['ltit'] = ($this->mostrarTipo == 1) ? $psUser->nick : '';
                break;
            case 14: 
                $total = $datos['not_total'];
                // MAS DE UNA ACCION
                if($total > 1) {
                    $txt = $this->monitor[$type]['text'][1] . ' ' . $text;
                    $frase['text'] = str_replace('_DATO_', '<b>' . $total . '</b>', $txt);
                }
                else{
                    $frase['text'] = $this->monitor[$no_type]['text'][0];
                }
                //formamos la frase
                $frase['text'] = ($this->mostrarTipo == 1) ? $frase['text'] : $frase['text'] . ' ' . $text;
                $frase['link'] = $site_url . '/perfil/' . $psUser->nick . '/' . $datos['obj_uno'];
                $frase['ltext'] = ($this->mostrarTipo == 1) ? $text : substr($datos['c_body'], 0, 20) . '...';
                $frase['ltit'] = ($this->mostrarTipo == 1) ? substr($datos['c_body'], 0, 20) . '...' : '';
                break;
            //medallas
            case 15: 
            //nueva medalla
                $frase['text'] = 'Recibiste una nueva <span title="'.$datos['m_title'].'"><b>medalla</b> <img src="'.$url.'/themes/default/images/icons/med/'.$datos['m_image'].'_16.png"/></span>';
                break;
            case 16: 
                //nueva medalla en post
                $frase['text'] = 'Tu <a href="'.$url.'/posts/'.$datos['c_seo'].'/'.$datos['post_id'].'/'.$psCore->setSEO($datos['post_title']).'.html" title="'.$datos['post_title'].'"><b>post</b></a> tiene una nueva <span title="'.$datos['m_title'].'"><b>medalla</b> <img src="'.$url.'/themes/default/images/icons/med/'.$datos['m_image'].'_16.png"/></span>';
                break;
            case 17: 
                //nueva medalla en fotos
                $frase['text'] = 'Tu <a href="'.$url.'/fotos/'.$datos['user_name'].'/'.$datos['foto_id'].'/'.$psCore->setSEO($datos['f_title']).'.html" title="'.$datos['f_title'].'"><b>foto</b></a> tiene una nueva <span title="'.$datos['m_title'].'"><b>medalla</b> <img src="'.$url.'/themes/default/images/icons/med/'.$datos['m_image'].'_16.png"/></span>';
                break;
        }
        return $frase;
    }

    /**
     * @funcionalidad comprobamos si están permitidas las notificaciones para el usuario
     * @param  [type] $type tipo de notificación
     * @param  [type] $uid id del usuario
     * @return [type] devolvemos un valor booleano con el resultado de la operación
     */
    function permitirNotificaciones($type, $uid){
        global $psDb;
        $consulta = "SELECT c_monitor FROM u_portal WHERE user_id = :uid";
        $valores = array('uid' => $uid);
        $datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
        $fid = 'f'.$type;
        $filtros = unserialize($datos['c_monitor']);
        //comprobamos si es correcto
        if(in_array($fid, $filtros)){
            return false;
        }else{
            return true;
        }
    }
    
    /**
     * @funcionalidad seguimos al usuario
     * @return [type] devolvemos un string con el resultado obtenido
     */
    function setSeguir(){
        global $psDb, $psUser, $psCore, $psActividad;
        $type = 4;//notificación
        $all = $this->getAllFollow();
        //antiflood
        $antiFlood = $psCore->antiFlood(false, 'follow');
        if(strlen($antiFlood) > 1){
            $antiFlood = str_replace('0: ','',$antiFlood);
            return '1-'.$all['obj'].'-0-'.$antiFlood;
        }
        //comprobamos si ya existe
        $consulta = "SELECT follow_id FROM u_follows WHERE f_user = :fuid AND f_id = :fid AND f_type = :type";
        $valores = array('fuid' => $psUser->user_id, 'fid' => $all['obj'], 'type' => $all['type']);
        $datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
        if(empty($datos['follow_id'])){
            if($psUser->user_id == $all['obj'] && $all['type'] == 1){
                return '1-'.$all['obj'].'-0-No puedes seguirte a ti mismo.';
            }
            //insertamos los datos
            $consulta2 = "INSERT INTO u_follows (f_user, f_id, f_type, f_date) VALUES (:user, :fid, :type, :dates)";
            $valores2 = array(
                'user' => $psUser->user_id,
                'fid' => $all['obj'],
                'type' => $all['type'],
                'dates' => time()
            );
            if($psDb->db_execute($consulta2, $valores2)){
                //realizamos la notificación
                if($all['notUser'] > 0){
                    $this->setNotificacion($type, $all['notUser'], $psUser->user_id);
                }
                //obtenemos el total
                $consulta3 = "SELECT COUNT(follow_id) AS total FROM u_follows WHERE f_id = :fid AND f_type = :type";
                $valores3 = array('fid' => $all['obj'], 'type' => $all['type']);
                $total = $psDb->db_execute($consulta3, $valores3, 'fetch_assoc');
                //ahora mandamos la actividad
                $act_type = ($all['type'] == 1) ? 8 : 7;
                $psActividad->setActividad($act_type, $all['obj']);
                return '0: '.$all['obj'].'-'.$total['total'];
            }else{
                return '1: '.$all['obj'].'0: No se pudo completar la acci&oacute;n de seguir al usuario.';
            }
        }else{
            return '2: '.$all['obj'].'-0';
        }
    }

    /**
     * @funcionalidad dejamos del seguir al usuario
     * @return [type] devolvemos un string con el resultado obtenido 
     */
    function setDejarSeguir(){
        global $psDb, $psUser, $psCore;
        $type = 4;//notificación
        $all = $this->getAllFollow();
        //antiflood
        $antiFlood = $psCore->antiFlood(false, 'follow');
        if(strlen($antiFlood) > 1){
            $antiFlood = str_replace('0: ','',$antiFlood);
            return '1-'.$all['obj'].'-0-'.$antiFlood;
        }
        //dejamos de seguir al usuario
        $consulta = "DELETE FROM u_follows WHERE f_user = :user AND f_id = :fid AND f_type = :type";
        $valores = array('user' => $psUser->user_id, 'fid' => $all['obj'], 'type' => $all['type']);
        if($psDb->db_execute($consulta, $valores)){
            //obtenemos el total
            $consulta2 = "SELECT follow_id FROM u_follows WHERE f_id = :fid AND f_type = :type";
            $valores2 = array('fid' => $all['obj'], 'type' => $all['type']);
            $total = $psDb->db_execute($consulta2, $valores2, 'rowCount');
            return '0-'.$all['obj'].' - '.$total;
        }else{
            return '1-'.$all['obj'].'-0- No se pudo completar la acci&oacute;n de dejar de seguir al usuario.';
        }
    }

    /**
     * @funcionalidad obtenemos todos los seguidores de post o usuarios
     * @return [type] devolvemos un array con los datos obtenidos
     */
    function getAllFollow(){
        global $psCore;
        //obtenemos los datos del formulario
        $datos['sType'] = filter_input(INPUT_POST, 'type');
        $datos['obj'] = filter_input(INPUT_POST, 'obj');
        //obtenemos el tipo de seguidores
        switch($datos['sType']){
            case 'user': 
                $datos['type'] = 1; 
                $datos['notUser'] = $datos['obj'];
            break;
            case 'post': 
                $datos['type'] = 2; 
                $datos['notUser'] = 0;
            break;
        }
        return $datos;
    }

    /**
     * @funcionalidad obtenemos los seguidos y seguidores de un usuario
     * @param  [type] $type tipo de follows a obtener
     * @param  [type] $uid id del usuario
     * @return [type] devolvemos un array con los datos obtenidos
     */
    function getFollows($type, $uid){
        global $psDb, $psCore, $psUser;
        $uid = (empty($uid)) ? $psUser->user_id : $uid;
        //obtenemos los datos según el tipo
        switch($type){
            case 'posts':
                $consulta = "SELECT f.f_id, p.post_user, p.post_title, u.user_name, c.c_seo, c.c_nombre, c.c_img FROM u_follows AS f LEFT JOIN p_posts AS p ON f.f_id = p.post_id LEFT JOIN u_miembros AS u ON u.user_id = p.post_user LEFT JOIN p_categorias AS c ON c.cid = p.post_category WHERE f.f_user = :uid AND f.f_type = :type ORDER BY f.f_date DESC";
                $valores = array('uid' => $uid, 'type' => 2);
                $total = $psDb->db_execute($consulta, $valores, 'rowCount');
                //obtenemos las páginas
                $pages = $psCore->getPagination($total, 15);
                $datos['pages'] = $pages;
                $consulta .= ' LIMIT :limite';
                $valores['limite'] = $pages['limit'];
                //guardamos los datos
                $datos['data'] = $psDb->resultadoArray($psDb->db_execute($consulta, $valores));
                break;
            case 'siguiendo':
                $consulta = "SELECT u.user_id, u.user_name, p.user_pais, p.p_mensaje, f.follow_id FROM u_miembros AS u LEFT JOIN u_perfil AS p ON u.user_id = p.user_id LEFT JOIN u_follows AS f ON p.user_id = f.f_id WHERE f.f_user = :uid AND f.f_type = :type ORDER BY f.f_date DESC";
                $valores = array('uid' => $uid, 'type' => 1);
                $total = $psDb->db_execute($consulta, $valores, 'rowCount');
                //obtenemos las páginas
                $pages = $psCore->getPagination($total, 15);
                $datos['pages'] = $pages;
                //$consulta .= ' LIMIT :limite';
                //$valores['limite'] = $pages['limit'];
                //guardamos los datos
                $datos['data'] = $psDb->resultadoArray($psDb->db_execute($consulta, $valores));
                break;
            case 'seguidores':
                $consulta = "SELECT u.user_id, u.user_name, p.user_pais, p.p_mensaje, f.follow_id FROM u_miembros AS u LEFT JOIN u_perfil AS p ON u.user_id = p.user_id LEFT JOIN u_follows AS f ON p.user_id = f.f_user WHERE f.f_id = :uid AND f.f_type = :type ORDER BY f.f_date DESC";
                $valores = array('uid' => $uid, 'type' => 1);
                $total = $psDb->db_execute($consulta, $valores, 'rowCount');
                //obtenemos las páginas
                $pages = $psCore->getPagination($total, 15);
                $datos['pages'] = $pages;
                //$consulta .= ' LIMIT :limite';
                //$valores['limite'] = $pages['limit'];
                //guardamos los datos
                $dato = $psDb->resultadoArray($psDb->db_execute($consulta, $valores));
                //comprobamos si seguimos o nos siguen y rellenamos el array
                foreach($dato as $key => $valor){
                    $consulta2 = "SELECT follow_id FROM u_follows WHERE f_user = :uid AND f_id = :fid AND f_type = :type";
                    $valores2 = array('uid' => $uid, 'fid' => $valor['user_id'], 'type' => 1);
                    $siguiendo = $psDb->db_execute($consulta2, $valores2, 'fetch_assoc');
                    if(!empty($siguiendo['follow_id'])){
                        $valor['follow'] = 1;
                    }else{
                        $valor['follow'] = 0;
                    }
                    $datos['data'][] = $valor;
                }
                break;
        }
        return $datos;
    }

    /**
     * @funcionalidad enviamos recomendacion a los usuarios
     * @return [type] devolvemos un string con el resultado obtenido
     */
    function setRecomendaciones(){
        global $psDb, $psCore, $psUser, $psActividad;
        $pid = filter_input(INPUT_POST, 'postid');

        //comprobamos si tiene seguidores
        $consulta = "SELECT follow_id FROM u_follows WHERE f_id = :fid AND f_type = :type";
        $valores = array('fid' => $psUser->user_id, 'type' => 1);
        $seguidores = $psDb->db_execute($consulta, $valores, 'rowCount');
        if($seguidores < 1) return '0-Debes tener al menos un seguidor para poder recomendar el post.';

        //comprobamos si ya lo ha recomendado
        $consulta2 = "SELECT follow_id FROM u_follows WHERE f_id = :fid AND f_user = :user AND f_type = :type";
        $valores = array('fid' => $pid,'user' => $psUser->user_id, 'type' => 3);
        $recomendado = $psDb->db_execute($consulta2, $valores2, 'rowCount');
        if($recomendado > 0) return '0-No puedes recomendar el mismo post m&aacute;s de una vez.'; 

        //obtenemos el usuario del post
        $consulta3 = "SELECT post_user FROM p_posts WHERE post_id = :pid";
        $valores3 = array('pid' => $pid);
        $datos = $psDb->db_execute($consulta3, $valores3, 'fetch_assoc');

        //ahora comprobamos
        if($psUser->user_id != $datos['post_user']){
            //guardamos los datos
            $consulta4 = "INSERT INTO u_follows (f_id, f_user, f_type, f_date) VALUES (:fid, :user, :type, :dates)";
            $valores4 = array(
                'fid' => $pid,
                'user' => $psUser->user_id,
                'type' => 3,
                'dates' => time()
            );
            $psDb->db_execute($consulta4, $valores4);
            //notificamos al usuario
            if($this->setFollowNotificaciones(6, 1, $psUser->user_id, $pid)){
                $psActividad->setActividad(4, $pid);
                return '1-La recomendaci&oacute;n fue enviada con &eacute;xito.';
            }
        }else{
            return '0-No puedes recomendar tus propios post.';
        }
    }

    /**
     * @funcionalidad guardamos los nuevos filtros de actividad
     * @return [type] devolvemos un valor booleano si todo ha salido correcto
     */
    function setActFiltro(){
        global $psDb, $psUser;
        $fid = filter_input(INPUT_POST, 'fid');
        $fid = 'f.'.$fid;
        //obtenemos la configuración 
        $consulta = "SELECT c_monitor FROM u_portal WHERE user_id = :uid";
        $valores = array('uid' => $psUser->user_id);
        $datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
        $filtros = unserialize($datos['c_monitor']);
        //comprobamos
        if(in_array($fid, $filtros)){
            $id = array_search($fid, $filtros);//buscamos el id en el array
            array_splice($filtros, $id);//sustituimos
        }else{//si no lo encontramos añadimos el nuevo id
            $filtros[] = $fid;
        }
        //guardamos los nuevos filtros en la db
        $filtros = serialize($filtros);
        $consulta2 = "UPDATE u_portal SET c_monitor = :monitor WHERE user_id = :uid";
        $valores2 = array('monitor' => $filtros, 'uid' => $psUser->user_id);
        $psDb->db_execute($consulta2, $valores2);
        return true;
    }
}