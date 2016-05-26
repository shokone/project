<?php
//si la constante PS_HEADER no esta definida no se accede al script
if (!defined('PS_HEADER')) {
    exit("No se permite el acceso al script");
}

/**
 * Clase realizada para el control del muro del usuario
 *
 * @name c.muro.php
 * @author Iván Martínez Tutor
 */
class psMuro{
    /**
     * @funcionalidad instanciamos la clase y la guardamos en una variable estática
     * @staticvar psMuro $instancia instancia de la clase
     * @return \psMuro devolvemos una instancia de la clase
     */
    public static function &getInstance(){
        static $instancia;
        if(is_null($instancia)){
            $instancia = new psMuro();
        }
        return $instancia;
    }

    /**
     * @funcionalidad obtenemos los datos de privacidad seleccionados por el usuario
     * @param  [type] $uid id del usuario
     * @param  [type] $uname nombre del usuario
     * @param  [type] $seguir id del usuario al que sigues o vas a seguir
     * @param  [type] $seguidor id del usuario que te sigue o te va a seguir
     * @return [type] devolvemos un array con los datos obtenidos
     */
    function getPrivacidad($uid, $uname, $seguir, $seguidor){
        global $psDb, $psUser;
        $private['m']['v'] = true;//ver muro
        $private['mf']['v'] = true;//ver y firmar muro
        $private['rmp']['v'] = true;//ver y responder muro
        $yo = ($psUser->user_id == $uid) ? true : false;//soy yo?
        //sigo al usuario o me sigue el usuario
        if($seguir == 0 && $seguidor == 0){
            $sigo_o_me_sigue = false;
        }else{
            $sigo_o_me_sigue = true;
        }
        //sigo al usuario y me sigue el usuario
        if($seguir == 1 && $seguidor == 1){
            $sigo_y_me_sigue = true;
        }else{
            $sigo_y_me_sigue = false;
        }
        //obtenemos datos de la db
        $consulta = "SELECT p_configs FROM u_perfil WHERE user_id = :uid";
        $valores = array('uid' => $uid);
        $datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
        $datos['p_configs'] = unserialize($datos['p_configs']);
        //comprobamos las acciones
        //ver muro
        switch($datos['p_configs']['m']){
            case 0:
                if(!$yo && !$psUser->admod){
                    $private['m']['v'] = false;
                }
                $private['m']['m'] = 'Lo sentimos pero '.$uname.' no permite a nadie ver su muro.';
                break;
            case 1:
                if(!$sigo_y_me_sigue && !$yo && !$psUser->admod){
                    $private['m']['v'] = false;
                }
                $private['m']['m'] = 'Debes seguir a '.$uname.' y &eacute;l debe seguirte para poder ver su muro.';
                break;
            case 2:
                if(!$sigo_o_me_sigue && !$yo && !$psUser->admod){
                    $private['m']['v'] = false;
                }
                $private['m']['m'] = 'Debes seguir a '.$uname.' o &eacute;l debe seguirte para poder ver su muro.';
                break;
            case 3:
                if($seguir == 0 && !$yo && !$psUser->admod){
                    $private['m']['v'] = false;
                }
                $private['m']['m'] = 'Debes seguir a '.$uname.' para poder ver su muro.';
                break;
            case 4:
                if($seguidor == 0 && !$yo && !$psUser->admod){
                    $private['m']['v'] = false;
                }
                $private['m']['m'] = $uname.' debe seguirte para que puedas ver su muro';
                break;
            case 5:
                if(!$psUser->member){
                    $private['m']['v'] = false;
                }
                $private['m']['m'] = 'Solo los usuarios <a onclick="registro_load_form();">registrados</a> pueden ver el muro de '.$uname;
                break;
        }
        //firmar muro
        switch($datos['p_configs']['mf']){
            case 0:
                if(!$yo && !$psUser->admod){
                    $private['mf']['v'] = false;
                }
                $private['mf']['m'] = 'Lo sentimos pero '.$uname.' no permite a nadie firmar en su muro.';
                break;
            case 1:
                if(!$sigo_y_me_sigue && !$yo && !$psUser->admod){
                    $private['mf']['v'] = false;
                }
                $private['mf']['m'] = 'Debes seguir a '.$uname.' y &eacute;l debe seguirte para poder firmar y comentar su muro.';
                break;
            case 2:
                if(!$sigo_o_me_sigue && !$yo && !$psUser->admod){
                    $private['mf']['v'] = false;
                }
                $private['mf']['m'] = 'Debes seguir a '.$uname.' o &eacute;l debe seguirte para poder firmar y comentar su muro.';
                break;
            case 3:
                if($seguir == 0 && !$yo && !$psUser->admod){
                    $private['mf']['v'] = false;
                }
                $private['mf']['m'] = 'Debes seguir a '.$uname.' para poder firmar y comentar su muro.';
                break;
            case 4:
                if($seguidor == 0 && !$yo && !$psUser->admod){
                    $private['mf']['v'] = false;
                }
                $private['mf']['m'] = $uname.' debe seguirte para que puedas firmar y comentar su muro';
                break;
            case 5:
                if(!$psUser->member){
                    $private['mf']['v'] = false;
                }
                $private['mf']['m'] = 'Solo los usuarios <a onclick="registro_load_form();">registrados</a> pueden firmar el muro de '.$uname;
                break;
        }
        return $private;
    }

    /**
     * @funcionalidad creamos una nueva publicación en el muro
     * @return [type] devolvemos un array con los datos obtenidos si todo ok, sino devolvemos un string con el error obtenido
     */
    public function pubMuro(){
        global $psDb, $psCore, $psMonitor, $psUser, $psActividad;
        //obtenemos los datos del formulario
        $pid = filter_input(INPUT_POST, 'pid');
        $datos = filter_input(INPUT_POST, 'data');
        $adjunto = filter_input(INPUT_POST, 'adj');
        $type = filter_input(INPUT_GET, 'type');
        //comprobamos si el usuario permite compartir en su muro
        //añadimos la clase cuenta para obtener los datos de seguidos y seguidores
        include 'c.cuenta.php';
        $psCuenta =& psCuenta::getInstance();
        $privado = $this->getPrivacidad($pid, $psUser->getUserName($pid), $psCuenta->siguiendo($pid), $psCuenta->seguidores($pid));
        //comprobamos si permite firmar en su muro
        if($privado['mf']['v'] == false){
            return '0: '.$privado['mf']['m'];
        }
        //realizamos la acción según el tipo de publicación
        switch($type){
            //publicar estado 
            case 'status':
                //reemplazamos los saltos de línea y las tabulaciones
                $texto = str_replace(array('\n', '\t', ' '), '', $datos);
                //comprobamos si está vacío el campo
                if(strlen($texto) == 0){
                    return '0: Tu publicaci&oacute;n no puede estar vac&iacute;a';
                }
                //cargamos el antiflood para evitar sobrecargar el servidor con demasiadas peticiones en poco tiempo
                $psCore->antiFlood();
                //ahora insertamos los datos
                $insert = "INSERT INTO u_muro (p_user, p_user_pub, p_body, p_date, p_type, p_ip) VALUES (:user, :pub, :body, :dat, :type, :ip)";
                $valores = array(
                    'user' => $pid,
                    'pub' => $psUser->user_id,
                    'body' => $texto,
                    'dat' => time(),
                    'type' => 1,
                    'ip' => $_SERVER['REMOTE_ADDR']
                );
                if($psDb->db_execute($insert, $valores)){
                    $pub = $psDb->getLastInsertId();
                    //obtenemos el tipo, estado o publicacion en el muro
                    $type = $pid == $psUser->user_id ? 'status' : 'mpub';
                    $datos = array(
                        'pub_id' => $pub, 
                        'p_user' => $pid, 
                        'p_user_pub' => $psUser->user_id, 
                        'p_body' => $psCore->badWords($psCore->setMenciones($datos), true), 
                        'p_date' => time(), 
                        'p_likes' => 0, 
                        'p_type' => 1, 
                        'likes' => array('link' => 'Me gusta esto!')
                    );
                }
                break;
            //publicar un enlace
            case 'enlace':
                //comprobamos el enlace
                $enlace = $this->comprobarEnlaces(true, $adjunto);
                //comprobamos si nos devuelve un error o no
                if(substr($enlace, 0, 1) == '0'){
                    return $enlace;
                }
                //cargamos el antiflood para evitar sobrecargar el servidor con demasiadas peticiones en poco tiempo
                $psCore->antiFlood();
                //ahora insertamos los datos
                $insert = "INSERT INTO u_muro (p_user, p_user_pub, p_body, p_date, p_type, p_ip) VALUES (:user, :pub, :body, :dat, :type, :ip)";
                $valores = array(
                    'user' => $pid,
                    'pub' => $psUser->user_id,
                    'body' => $datos,
                    'dat' => time(),
                    'type' => 3,
                    'ip' => $_SERVER['REMOTE_ADDR']
                );
                if($psDb->db_execute($insert, $valores)){
                    $pub = $psDb->getLastInsertId();
                    //ahora añadimos en la tabla adjuntos el enlace
                    $insert2 = "INSERT INTO u_muro_adjuntos (pub_id, a_title, a_url) VALUES (:pid, :title, :url)";
                    $valores2 = array('pid' => $pub, 'title' => $enlace['title'], 'url' => $enlace['url']);
                    if($psDb->db_execute($insert2, $valores2)){
                        //obtenemos el tipo
                        $type = 'menlace';
                        $datos = array(
                            'pub_id' => $pub, 
                            'p_user' => $pid, 
                            'p_user_pub' => $psUser->user_id, 
                            'p_body' => $psCore->setMenciones($datos), 
                            'p_date' => time(), 
                            'p_likes' => 0, 
                            'p_type' => 3, 
                            'likes' => array('link' => 'Me gusta esto!'),
                            'a_title' => $enlace['title'],
                            'a_url' => $enlace['url']
                        );
                    }
                }
                break;
            //publicar una foto
            case 'foto':
                //comprobamos la foto
                $foto = $this->comprobarEnlaces(true, $adjunto);
                //comprobamos si nos devuelve un error o no
                if(substr($foto, 0, 1) == '0'){
                    return $foto;
                }
                //cargamos el antiflood para evitar sobrecargar el servidor con demasiadas peticiones en poco tiempo
                $psCore->antiFlood();
                //ahora insertamos los datos
                $insert = "INSERT INTO u_muro (p_user, p_user_pub, p_body, p_date, p_type, p_ip) VALUES (:user, :pub, :body, :dat, :type, :ip)";
                $valores = array(
                    'user' => $pid,
                    'pub' => $psUser->user_id,
                    'body' => $datos,
                    'dat' => time(),
                    'type' => 2,
                    'ip' => $_SERVER['REMOTE_ADDR']
                );
                if($psDb->db_execute($insert, $valores)){
                    $pub = $psDb->getLastInsertId();
                    //ahora añadimos en la tabla adjuntos la foto
                    $insert2 = "INSERT INTO u_muro_adjuntos (pub_id, a_url, a_img) VALUES (:pid, :url, :img)";
                    $valores2 = array('pid' => $pub, 'url' => $foto, 'img' => $foto);
                    if($psDb->db_execute($insert2, $valores2)){
                        //obtenemos el tipo
                        $type = 'mfoto';
                        $datos = array(
                            'pub_id' => $pub, 
                            'p_user' => $pid, 
                            'p_user_pub' => $psUser->user_id, 
                            'p_body' => $psCore->setMenciones($datos), 
                            'p_date' => time(), 
                            'p_likes' => 0, 
                            'p_type' => 2, 
                            'likes' => array('link' => 'Me gusta esto!'),
                            'a_url' => $foto,
                            'a_img' => $foto
                        );
                    }
                }
                break;
            //publicar un video
            case 'video':
                //comprobamos el video
                $video = $this->comprobarEnlaces(true, $adjunto);
                //comprobamos si nos devuelve un error o no
                if(substr($video, 0, 1) == '0'){
                    return $video;
                }
                //cargamos el antiflood para evitar sobrecargar el servidor con demasiadas peticiones en poco tiempo
                $psCore->antiFlood();
                //ahora insertamos los datos
                $insert = "INSERT INTO u_muro (p_user, p_user_pub, p_body, p_date, p_type, p_ip) VALUES (:user, :pub, :body, :dat, :type, :ip)";
                $valores = array(
                    'user' => $pid,
                    'pub' => $psUser->user_id,
                    'body' => $datos,
                    'dat' => time(),
                    'type' => 4,
                    'ip' => $_SERVER['REMOTE_ADDR']
                );
                if($psDb->db_execute($insert, $valores)){
                    $pub = $psDb->getLastInsertId();
                    //ahora añadimos en la tabla adjuntos el video
                    $insert2 = "INSERT INTO u_muro_adjuntos (pub_id, a_title, a_url, a_img, a_desc) VALUES (:pid, :title, :url, :img, :des)";
                    $valores2 = array(
                        'pid' => $pub, 
                        'title' => $video['title'], 
                        'url' => $video['id'],
                        'img' => '',
                        'des' => $psCore->badWords($video['desc'])
                    );
                    if($psDb->db_execute($insert2, $valores2)){
                        //obtenemos el tipo
                        $type = 'menlace';
                        $datos = array(
                            'pub_id' => $pub, 
                            'p_user' => $pid, 
                            'p_user_pub' => $psUser->user_id, 
                            'p_body' => $psCore->setMenciones($datos), 
                            'p_date' => time(), 
                            'p_likes' => 0, 
                            'p_type' => 4, 
                            'likes' => array('link' => 'Me gusta esto!'),
                            'a_title' => $video['title'],
                            'a_url' => $video['id'],
                            'a_desc' => $video['desc']
                        );
                    }
                }
                break;
            default:
                $datos = '0: El campo <strong>tipo</strong> es requerido para esta operaci&oacute;n';
        }
        $datos['user_name'] = $psUser->nick;
        //mandamos la notificacion
        $psMonitor->setNotificacion(12, $pid, $psUser->user_id, $pub);
        //creamos la actividad
        $yo = $pid == $psUser->user_id ? 0 : 2;
        $psActividad->setActividad(10, $pub, $yo);
        return $datos;
    }

    /**
     * @funcionalidad 
     * @return [type]  
     */
    function rePubMuro(){
        global $psDb, $psMonitor, $psUser, $psCore, $psActividad;
        //obtenemos los datos del formulario
        $datos = $psCore->badWords(filter_input(INPUT_POST, 'data'));
        $pid = filter_input(INPUT_POST, 'pid');
        //ahora obtenemos los datos de la base de datos
        $consulta = "SELECT p_user, p_user_pub FROM u_muro WHERE pub_id = :pid";
        $valores = array('pid' => $pid);
        $publicacion = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
        //comprobamos
        if($publicacion['p_user'] > 0){
            //reemplazamos los saltos de línea y las tabulaciones
            $texto = str_replace(array('\n', '\t', ' '), '', $datos);
            //comprobamos si está vacío el campo
            if(strlen($texto) == 0){
                return '0: Tu respuesta no puede estar vac&iacute;a';
            }
            //cargamos el antiflood para evitar sobrecargar el servidor con demasiadas peticiones en poco tiempo
            $psCore->antiFlood();
            //ahora insertamos los datos
            $insert = "INSERT INTO u_muro_comentarios (pub_id, c_user, c_date, c_body, c_ip) VALUES (:pid, :user, :dat, :body, :ip)";
            $valores2 = array(
                'pid' => $pid,
                'user' => $psUser->user_id,
                'dat' => time(),
                'body' => $texto,
                'ip' => $_SERVER['REMOTE_ADDR']
            );
            if($psDb->db_execute($insert, $valores2)){
                $cid = $psDb->getLastInsertId();
                //mandamos la notificación
                $psMonitor->setMuroRespond($pid, $publicacion['p_user'], $publicacion['p_user_pub']);
                //creamos la actividad
                $yo = $publicacion['p_user'] == $psUser->user_id ? 1 : 3;
                $psActividad->setActividad(10, $cid, $yo);
                //actualizamos en la base de datos
                $update = "UPDATE u_muro SET p_comments = p_comments + :com WHERE pub_id = :pid";
                $valores3 = array('com' => 1, 'pid' => $pid);
                $psDb->db_execute($update, $valores3);
                return array(
                    'cid' => $cid,
                    'c_body' => $psCore->badWords($texto, true),
                    'c_date' => time(), 
                    'c_user' => $psUser->user_id,
                    'c_likes' => 0,
                    'like' => 'Me gusta esto!',
                    'user_name' => $psUser->nick
                );
            }else{
                return '0: Error al guardar la respuesta';
            }
        }else{
            return '0: La publicaci&oacute;n seleccionada no existe';
        }
    }

    /**
     * @funcionalidad comprobamos la url introducida por el usuario ya sea una foto, un video o una url de una página
     * @param  [type] $ret devolvemos texto html si es false y un array con los datos si es true 
     * @param  [type] $urlin url introducida por el usuario
     * @return [type] devolvemos un array o un texto html según el valor de la variable $ret
     */
    function comprobarEnlaces($ret = false, $urlin = null){
        global $psDb, $psCore;
        //obtenemos el tipo de comprobación
        $type = filter_input(INPUT_GET, 'type');
        $url = empty($urlinterna) ? filter_input(INPUT_POST, 'url') : $urlin; 
        switch($type){
            //comprobamos una foto ingresada por url
            case 'foto':
                if(strlen($url) > 300){
                    return '0: La url de la imagen es demasiado larga. El m&aacute;ximo permitido es de 300 caracteres.';
                }
                //obtenemos el tamaño de la imagen
                $datos = getimagesize($url);
                //tamaño máximo y mínimo de la imagen
                $minw = 130;//ancho minimo
                $minh = 130;//alto minimo
                $maxw = 1280;//ancho maximo
                $maxh = 1280;//alto maximo
                //comprobamos
                if($empty($datos[0])){
                    return '0: La url ingresada no existe o no es v&aacute;lida.';
                }else if($datos[0] < $minw || $datos[1] < $minh){
                    return '0: El tama&ntilde;o m&iacute;nimo permitido es de 130 x 130 p&iacute;xeles';
                }else if($datos[0] > $maxw || $datos[1] > $maxh){
                    return '0: El tama&ntilde;o m&aacute;ximo permitido es de 1280 x 1280 p&iacute;xeles';
                }else{
                    if($ret == false){
                        return '1: <img src="' . $url . '"/>';
                    }else{
                        return $url;
                    }
                }
            //comprobamos el enlace ingresado
            case 'enlace':
                if(strlen($url) > 300){
                    return '0: La url ingresada es demasiado larga. El m&aacute;ximo permitido es de 300 caracteres.';
                }
                //obtenemos el contenido de la url
                $datos = $psCore->getUrlContent($url);
                //comprobamos los datos obtenidos
                if(!$datos){
                    return '0: El enlace que has ingresado no existe, no est&aacute; disponible o no es v&aacute;lido.';
                }
                //obtenemos los datos
                $titulo = explode('<title>', $datos);
                $titulo = explode('</title', $datos);
                $titulo = empty($titulo[0]) ? $url : $titulo[0];
                //comprobamos otra vez pero con el título obtenido
                if(!$titulo){
                    return '0: La url que has ingresado no es una web v&aacute;lida.';
                }
                //devolvemos los datos obtenidos en html
                if($ret == false){
                    return '1: <a href="'.$ul.'" target="_blank">'.$titulo.'</a><br><span>'.$url.'</span>';
                }else{
                    return array('title' => $titulo, 'url' => $url);
                }
            //comprobamos un video de youtube
            case 'video':
                $video = explode('watch?v=', $url);
                if(!is_array($video)){
                    return '0: La direcci&oacute;n introducida no es v&aacute;lida';
                }
                $video = substr($video[1], 0, 11);
                if(strlen($video) != 11){
                    return '0: La direcci&oacute;n introducida no es v&aacute;lida';
                }
                $datos = get_meta_tags('http://youtube.com/watch?v='.$video);
                if(empty($datos['title'])){
                    return '0: El video ha sido eliminado o la url es incorrecta.';
                }else{
                    $desc = str_replace('<br>', '', html_entity_decode($datos['description']));
                    //devolvemos los valores 
                    if($ret == false){
                        return '1: <div><img src="http://img.youtube.com/vi/'.$video_id.'/0.jpg" /><div><strong><a href="http://www.youtube.com/watch?v='.$video.'" target="_blank">'.$datos['title'].'</a></strong><div>'.$desc.'</div></div></div>';
                    }else{
                        return array('ID' => $video, 'title' => $datos['title'], 'desc' => substr($desc, 0, 160));
                    }
                }
            default:
                return '0: El campo <strong>tipo</strong> es obligatorio.';
        }
    }

    /**
     * @funcionalidad obtenemos las últimas novedades en el muro
     * @param  [type] $start primera novedad a mostrar
     * @param  [type] $limite limite de novedades a mostrar
     * @return [type] devolvemos un array con los datos obtenidos
     */
    function getNovedades($start = 0, $limite = 10){
        global $psDb, $psCore, $psUser;
        //mostraremos un máximo de 100 publicaciones
        if($start > 99){
            return array('total' => '-1');
        }
        //obtenemos los seguidores
        $consulta = "SELECT f_id FROM u_follows WHERE f_user = :uid AND f_type = :type";
        $valores = array('uid' => $psUser->user_id, 'type' => 1);
        $query = $psDb->resultadoArray($psDb->db_execute($consulta, $valores));
        //ordenamos los datos obtenidos
        foreach($query as $key => $valor){
            //comprobamos si tenemos permiso para ver sus publicaciones
            $privado = $this->getPrivacidad($valor['f_id'], null, true);
            if($privado['m']['v'] == true){
                $seguidores[] = $valor['f_id'];
            }
        }
        $seguidores[] = $psUser->user_id;
        //unimos el array formando un único string
        $seguidores = implode(', ', $seguidores);
        //obtenemos los datos de la db
        $consulta2 = "SELECT m.*, u.user_name FROM u_muro AS m LEFT JOIN u_miembros AS u ON m.p_user_pub = u.user_id WHERE m.p_user IN(:seguidores) AND m.p_user = m.p_user_pub ORDER BY m.p_date DESC LIMIT :min, :max";
        $valores2 = array('seguidores' => $seguidores, 'min' => $start, 'max' => $limite);
        //comprobamos los datos obtenidos
        while($row = $psDb->db_execute($consulta2, $valores2, 'fetch_assoc')){
            //cargamos los comentarios
            if($row['p_comments'] > 0){
                $row['comments'] = $this->getExtrasPubMuro($row['pub_id'], 'comments', 2);
            }
            //cargamos me gustas
            if($row['p_likes'] > 0){
                $row['likes'] = $this->getExtrasPubMuro($row['pub_id'], 'likes', $row['p_likes']);
            }else{
                $row['likes'] = array('link' => 'Me gusta esto');
            }
            //cargamos adjuntos
            if($row['p_type'] != 1){
                $consulta3 = "SELECT * FROM u_muro_adjuntos WHERE pub_id = :pid";
                $valores3 = array('pid' => $row['pub_id']);
                $query2 = $psDb->db_execute($consulta3, $valores3, 'fetch_assoc');
                //combinamos los arrays
                $datos[] = array_merge($row, $query2);
            }else{
                $datos[] = $row;
            }
            //cargamos las menciones
            $row['p_body'] = $psCore->badWords($psCore->setMenciones($row['p_body']), true);
        }
        return array('total' => count($datos), 'data' => $datos);
    }

    /**
     * @funcionalidad obtenemos las últimas publicaciones del muro del usuario
     * @param  [type] $uid id del usuario 
     * @param  [type] $start primera publicación a mostrar
     * @return [type] devolvemos un array con los datos obtenidos
     */
    function getMuro($uid, $start = 0){
        global $psDb, $psCore;
        //obtenemos las publicaciones del usuario
        $consulta = "SELECT m.*, u.user_name FROM u_muro AS m LEFT JOIN u_miembros AS u ON m.p_user_pub = u.user_id WHERE m.p_user = :user ORDER BY m.pub_id DESC";
        $valores = array('user' => $uid);
        while($row = $psDb->db_execute($consulta, $valores, 'fetch_assoc')){
            //cargamos los comentarios
            if($row['p_comments'] > 0){
                $row['comments'] = $this->getExtrasPubMuro($row['pub_id'], 'comments', 2);
            }
            //cargamos me gustas
            if($row['p_likes'] > 0){
                $row['likes'] = $this->getExtrasPubMuro($row['pub_id'], 'likes', $row['p_likes']);
            }else{
                $row['likes'] = array('link' => 'Me gusta esto');
            }
            //cargamos adjuntos
            if($row['p_type'] != 1){
                $consulta3 = "SELECT * FROM u_muro_adjuntos WHERE pub_id = :pid";
                $valores3 = array('pid' => $row['pub_id']);
                $query2 = $psDb->db_execute($consulta3, $valores3, 'fetch_assoc');
                //combinamos los arrays
                $datos[] = array_merge($row, $query2);
            }else{
                $datos[] = $row;
            }
            //cargamos las menciones
            $row['p_body'] = $psCore->badWords($psCore->setMenciones($row['p_body']), true);
        }
        return array('total' => count($datos), 'data' => $datos);
    }

    /**
     * @funcionalidad obtenemos los datos de la publicacion seleccionada
     * @param  [type] $pid obtenemos el id de la publicación
     * @param  [type] $uid obtenemos el id del usuario
     * @return [type] devolvemos un array con los datos obtenidos
     */
    function getHistoria($pid, $uid){
        global $psUser, $psDb;
        //obtenemos datos de la base de datos
        $consulta = "SELECT m.*, u.user_name FROM u_muro AS m LEFT JOIN u_miembros AS u ON p.p_user_pub = u.user_id WHERE p.pub_id = :pid";
        $valores = array('pid' => $pid);exit('jaja');
        $publicacion = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
        //comprobamos los datos obtenidos
        if(empty($publicacion['pub_id'])){
            return 'La publicaci&oacute;n seleccionada no existe';
        }elseif($uid != $publicacion['p_user']){
            return 'La publicaci&oacute;n seleccionada no pertenece a <strong>'.$psUser->getUserName($uid).'</strong>';
        }
        //cargamos comentarios
        if($publicacion['p_comments'] > 0){
            $publicacion['comments'] = $this->getExtrasPubMuro($publicacion['pub_id'], 'comments');
        }
        //cargamos me gustas    
        if($publicacion['p_likes'] > 0){
            $publicacion['likes'] = $this->getExtrasPubMuro($publicacion['pub_id'], 'likes', $publicacion['p_likes']);
        }else{
            $publicacion['likes'] = array('link' => 'Me gusta esto');
        }
        //cargamos adjuntos
        if($publicacion['p_type'] != 1){
            $consulta2 = "SELECT * FROM u_muro_adjuntos WHERE pub_id = :pid";
            $valores2 = array('pid' => $pid);
            $datos = array_merge($publicacion, $psDb->db_execute($consulta2, $valores2, 'fetch_assoc'));
        }else{
            $datos = $publicacion;
        }
        //cargamos extras
        $publicacion['hide_more'] = true;
        return $datos;
    }

    /**
     * @funcionalidad obtenemos los comentarios realizados en una publicación
     * @return [type] devolvemos un array con los datos o un string con el resultado del error
     */
    function getComentarios(){
        global $psDb, $psCore;
        //obtenemos el id de la publicacion
        $pid = filter_input(INPUT_POST, 'pid');
        //realizamos la consulta
        $consulta = "SELECT p_user, p_comments FROM u_muro WHERE pub_id = :pid";
        $valores = array('pid' => $pid);
        $publicacion = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
        //comprobamos
        if(!empty($publicacion)){
            $datos['data'] = $this->getExtrasPubMuro($pid, 'comments');
            $datos['total'] = $publicacion['p_comments'];
            $datos['user'] = $publicacion['p_user'];
            return $datos;
        }else{
            return '0: La publicaci&oacute;n seleccionada no existe.';
        }
    }

    /**
     * @funcionalidad borramos una publicación completa o solo un comentario del muro
     * @return [type] devolvemos un string con el resultado obtenido
     */
    function borrarPubMuro(){
        global $psDb, $psUser, $psCore;
        //obtenemos el id y el tipo
        $pid = filter_input(INPUT_POST, 'pid');
        $type = $_POST['type'] == 'publicacion' ? 'publicacion' : 'comentario';
        switch($type){
            case 'publicacion':
                //obtenemos los datos
                $consulta = "SELECT p_user, p_user_pub FROM u_muro WHERE pub_id = :pid";
                $valores = array('pid' => $pid);
                $datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
                //comprobamos los datos obtenidos
                if(!empty($datos['p_user'])){
                    //comprobamos si el usuario es el dueño o si tiene permisos
                    if($datos['p_user'] == $psUser->user_id || $datos['p_user_pub'] == $psUser->user_id || $psUser->admod || $psUser->permisos['moepm']){
                        //si todo ok borramos la publicación
                        $delete = "DELETE FROM u_muro WHERE pub_id = :pid";
                        $psDb->db_execute($delete, $valores);
                        //borramos los likes de la publicación
                        $delete2 = "DELETE FROM u_muro_likes WHERE obj_id = :pid AND obj_type = :type";
                        $valores2 = array('pid' => $pid, 'type' => 1);
                        $psDb->db_execute($delete2, $valores2);
                        //borramos los likes de todos los comentarios
                        $delete3 = "SELECT cid FROM u_muro_comentarios WHERE pub_id = :pid";
                        $query = $psDb->resultadoArray($psDb->db_execute($delete3, $valores));
                        $delids = '';
                        //obtenemos todos los comentarios
                        foreach($query as $key => $valor){
                            $delids .= $valor['cid'];
                        }
                        //y ahora si los borramos
                        $delete4 = "DELETE FROM u_muro_likes WHERE obj_id IN(:obj) AND obj_type = :type";
                        $valores4 = array('obj' => $delids, 'type' => 2);
                        $psDb->db_execute($delete4, $valores4);
                        //ahora borramos los comentarios
                        $delete5 = "DELETE FROM u_muro_comentarios WHERE pub_id = :pid";
                        $psDb->db_execute($delete5, $valores);
                        //y borramos los adjuntos
                        $delete6 = "DELETE FROM u_muro_adjuntos WHERE pub_id = :pid";
                        $psDb->db_execute($delete6, $valores);
                        return '1: Publicaci&oacute;n borrada correctamente';
                    }else{
                        return '0: No tienes permisos suficientes para hacer eso';
                    }
                }else{
                    return '0: La publicaci&oacute;n seleccionada no existe';
                }
                break;
            case 'comentario':
                //obtenemos los datos del comentario
                $consulta = "SELECT c.cid, c.c_user, m.pub_id, m.p_user FROM u_muro_comentarios AS c LEFT JOIN u_muro AS m ON c.pub_id = m.pub_id WHERE c.cid = :pid";
                $valores = array('pid' => $pid);
                $datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
                //comprobamos
                if(!empty($datos['cid'])){
                    //comprobamos si es el dueño o si tiene permisos suficientes
                    if($datos['p_user'] == $psUser->user_id || $datos['p_user_pub'] == $psUser->user_id || $psUser->admod || $psUser->permisos['moepm']){
                        //si todo ok borramos el comentario
                        $delete = "DELETE FROM u_muro_comentarios WHERE cid = :pid";
                        $psDb->db_execute($delete, $valores);
                        //borramos sus me gusta
                        $delete2 = "DELETE FROM u_muro_likes WHERE obj_id = :obj AND obj_type = :type";
                        $valores2 = array('obj' => $pid, 'type' => 2);
                        $psDb->db_execute($delete2, $valores2);
                        //actualizamos la publicación
                        $update = "UPDATE u_muro SET p_comments = p_comments - :com WHERE pub_id = :pid";
                        $valores3 = array('com' => 1, 'pid' => $datos['pub_id']);
                        $psDb->db_execute($update, $valores3);
                        return '1: Comentario borrado correctamente';
                    }else{
                        return '0: No tienes permisos suficientes para hacer eso';
                    }
                }else{
                    return '0: El comentario seleccionado no existe o ha sido borrado';
                }
                break;
        }
    }

    /**
     * @funcionalidad mostramos los me gustas que tiene una publicación del muro
     * @return [type] devolvemos un array con los datos de la publicación
     */
    function mostrarLikes(){
        global $psDb;
        $id = filter_input(INPUT_POST, 'id');
        $type = ($_POST['type'] == 'com') ? 2 : 1;
        $consulta = "SELECT m.user_id, u.user_name FROM u_muro_likes AS m LEFT JOIN u_miembros AS u ON m.user_id = u.user_id WHERE obj_id = :id AND obj_type = :type";
        $valores = array('id' => $id, 'type' => $type);
        $datos = $psDb->resultadoArray($psDb->db_execute($consulta, $valores));
        if(empty($datos)){
            return array('status' => 0, 'data' => 'La publicaci&oacute;n seleccionada no existe');
        }
        return array('status' => 1, 'data' => $datos);
    }

    /**
     * @funcionalidad damos un me gusta a la publicación o al comentario
     * @return [type] devolvemos un string con el resultado obtenido
     */
    function meGustaPub(){
        global $psDb, $psCore, $psUser, $psMonitor, $psActividad;
        //obtenemos los datos del muro
        $pid = filter_input(INPUT_POST, 'pid');
        $type = $_POST['type'] == 'cmentario' ? 2 : 1;
        $status = 'ok';
        //comprobamos si la publicación existe
        if($type == 1){
            $consulta = "SELECT p_user AS user FROM u_muro WHERE pub_id = :pid";     
        }else{
            $consulta = "SELECT c_user AS user FROM u_muro_comentarios WHERE cid = :pid";
        }
        $valores = array('pid' => $pid);
        $existe = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
        if(empty($existe['user'])){
            return '0: La publicaci&oacute;n seleccioanda no existe o ha sido borarda';
        }
        //comprobamos si me gusta esto
        $consulta2 = "SELECT like_id, user_id FROM u_muro_likes WHERE obj_id = :obj AND obj_type = :type";
        $valores2 = array('obj' => $pid, 'type' => $type);
        $megustas = $psDb->resultadoArray($psDb->db_execute($consulta2, $valores2));
        //obtenemos el total
        $total = count($megustas);
        //comprobamos
        foreach($megustas as $key => $valor){
            if($valor['user_id'] == $psUser->user_id){
                $like = $valor['like_id'];
            }
        }
        //comprobamos
        //si me gusta borramos el like
        if(!empty($like)){
            $delete = "DELETE FROM u_muro_likes WHERE like_id = :likes";
            $valores3 = array('likes' => $like);
            if($psDb->db_execute($delete, $valores3)){
                //restamos like publicacion
                if($type == 1){
                    $update = "UPDATE u_muro SET p_likes = p_likes - :lik WHERE pub_id = :pid";
                    $valores4 = array('lik' => 1, 'pid' => $pid);
                    $psDb->db_execute($update, $valores4);
                }else{//restamos like comentario
                    $update = "UPDATE u_muro_comentarios SET c_likes = c_likes - :lik WHERE cid = :pid";
                    $valores4 = array('lik' => 1, 'pid' => $pid);
                    $psDb->db_execute($update, $valores4);
                }
            }else{
                $status = 'Error';
            }
        }else{//si no lo agregamos
            $insert = "INSERT INTO u_muro_likes (user_id, obj_id, obj_type) VALUES (:uid, :obj, :type)";
            $valores3 = array('uid' => $psUser->user_id, 'obj' => $pid, 'type' => $type);
            if($psDb->db_execute($insert, $valores3)){
                //sumamos like en publicación
                if($type == 1){
                    $update = "UPDATE u_muro SET p_likes = p_likes + :lik WHERE pub_id = :pid";
                    $valores4 = array('lik' => 1, 'pid' => $pid);
                    $psDb->db_execute($update, $valores4);
                    $atype = $existe['user'] == $psUser->user_id ? 0 : 2; 
                }else{//sumamos like en comentario
                    $update = "UPDATE u_muro_comentarios SET c_likes = c_likes + :lik WHERE cid = :pid";
                    $valores4 = array('lik' => 1, 'pid' => $pid);
                    $psDb->db_execute($update, $valores4);
                    $atype = $existe['user'] == $psUser->user_id ? 1 : 3; 
                }
                //enviamos la notificación
                $psMonitor->setNotificacion(14, $existe['user'], $psUser->user_id, $pid, $type);
                //obtenemos la actividad
                $psActividad->setActividad(11, $pid, $atype);
            }else{
                $status = 'Error';
            }
        }
        //ahora el mensaje obtenido
        if($type == 1){
            $likes = empty($like) ? $total+1 : $total-1;
            $datos = $this->getExtrasPubMuro($pid, 'likes', $likes);
            $enlace = $datos['link'];
            $texto = $datos['text'];
        }else{
            $likes = empty($like) ? $total+1 : $total-1;
            $elike = $likes > 1 ? 's' : '';
            $enlace = empty($like) ? 'Ya no me gusta esto!' : 'Me gusta esto!';
            $texto = $likes > 0 ? $likes.' persona'.$elike : '';
        }
        return array('status' => $status, 'link' => $enlace, 'text' => $texto);
    }

    /**
     * @funcionalidad obtenemos los me gustas y los comentarios de una publicación del muro
     * @param  [type] $pid id de la publicación
     * @param  [type] $type obtenemos el tipo de dato a obtener
     * @param  [type] $likes obtenemos el total de me gustas 
     * @return [type] devolvemos un array con los datos obtenidos
     */
    function getExtrasPubMuro($pid, $type = 'likes', $likes = 0){
        global $psDb, $psCore, $psUser;
        //realizamos la acción según el tipo
        switch($type){
            case 'comments':
                //obtenemos los datos
                $consulta = "SELECT c.*, u.user_name FROM u_muro_comentarios AS c LEFT JOIN u_miembros AS u ON c.c_user =  = u.user_id WHERE c.pub_id = :pid ORDER BY c.c_date DESC LIMIT :likes";
                $valores = array('pid' => $pid, 'likes' => $likes);
                while($row = $psDb->db_execute($consulta, $valores, 'fetch_assoc')){
                    $row['c_body'] = $psCore->badWords($psCore->setMenciones($row['c_body']), true);
                    $row['like'] = 'Me gusta';
                    //comprobamos si me gusta
                    if($row['c_likes'] > 0){
                        $consulta2 = "SELECT like_id FROM u_muro_likes WHERE user_id = :uid AND obj_id = :objid AND obj_type = :type";
                        $valores2 = array('uid' => $psUser->user_id, 'objid' => $row['cid'], 'type' => 2);
                        $megusta = $psDb->db_execute($consulta2, $valores2, 'rowCount');
                        if($megusta > 0){
                            $row['like'] = 'Ya no me gusta';
                        }
                    }
                    $datos[] = $row;
                }
                //ordenamos el array obtenido
                asort($datos);
                break;
            case 'likes':
                //comprobamos los likes
                if($likes == 0){
                    return array('link' => 'Me gusta!', 'text' => '');
                }
                $megusta = false;
                $datos['link'] = 'Me gusta!';
                //comprobamos los datos en la base de datos
                if($psUser->membre){
                    $consulta = "SELECT like_id FROM u_muro_likes WHERE user_id = :uid AND obj_id = :pid AND obj_type = :type";
                    $valores = array('uid' => $psUser->user_id, 'pid' => $pid, 'type' => 1);
                    $megusta = $psDb->db_execute($consulta, $valores, 'rowCount');
                }
                //obtenemos el texto según el total de likes
                if($likes == 1){
                    //si me gusta
                    if($megusta){
                        $datos['link'] = 'Ya no me gusta esto';
                        $datos['text'] = 'Me gusta esto!';
                    }else{//si no obtenemos de la base de datos a quién le gusta
                        $consulta2 = "SELECT u.user_name FROM u_muro_likes AS ml LEFT JOIN u_miembros AS u ON ml.user_id = u.user_id WHERE ml.obj_id = :pid AND ml.obj_type = :type";
                        $valores2 = array('pid' => $pid, 'type' => 1);
                        $like = $psDb->db_execute($consulta2, $valores2, 'fetch_assoc');
                        $datos['text'] = 'A <a href="' . $psCore->settings['url'].'/perfil/'.$like['user_name'].'">'.$like['user_name'].'</a> le gusta esto!';
                    }
                }elseif($likes == 2){
                    if($megusta){//si me gusta obtenemos los datos de a quién más le gusta
                        $datos['link'] = 'Ya no me gusta esto';
                        $consulta2 = "SELECT u.user_name FROM u_muro_likes AS ml LEFT JOIN u_miembros AS u ON ml.user_id = u.user_id WHERE ml.user_id = :uid AND ml.obj_id = :pid AND ml.obj_type = :type";
                        $valores2 = array('uid' => $psUser->user_id, 'pid' => $pid, 'type' => 1);
                        $like = $psDb->db_execute($consulta2, $valores2, 'fetch_assoc');
                        $datos['text'] = 'A <a href="' . $psCore->settings['url'].'/perfil/'.$like['user_name'].'">'.$like['user_name'].'</a> y a ti os gusta esto!';
                    }else{//si no mostramos a cuantas personas les gusta esto
                        $datos['text'] = 'A <a onclick="muro.show_likes('.$pid.', pub); return false;">'.$likes.' personas les gusta esto!';
                    }
                }elseif($likes > 2){
                    if($megusta){//si me gusta mostramos a mi y al total de personas más
                        $datos['link'] = 'Ya no me gusta esto';
                        $datos['text'] = 'A ti y a <a onclick="muro.show_likes('.$pid.', pub); return false;">'.$likes.' personas m&aacute;s les gusta esto!';
                    }else{//si no sólo a cuántas personas les gusta
                        $datos['text'] = 'A <a onclick="muro.show_likes('.$pid.', \'pub\'); return false;">'.$likes.' personas</a> les gusta esto.';
                    }
                }
                break;
        }
        //devolvemos un array con los datos
        return $datos;
    }
}