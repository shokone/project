<?php
//comprobamos si hemos declarado la contante PS_HEADER
if(!defined('PS_HEADER')){
    exit("No se permite el acceso al script");
}
/**
 * clase psUser
 * clase destinada al control de los usuarios
 *
 * @name c.user.php
 * @author Iván Martínez Tutor
 */
class psUser{
    //declaramos las variables de la clase
    protected $member = 0; //el usuario se ha logueado?
    protected $admod = 0; //el usuario es administrador?
    protected $baneado = 0; //el usuario está baneado?
    protected $info = []; //si el usuario está logueado obtenemos los datos de la bd
    protected $nick = 'Visitante'; //nombre a mostrar del usuario
    protected $user_id = 0; //id del usuario
    protected $error; //contendrá el número del error, si lo hay
    protected $sesion; //contendrá los datos del usuario

    /**
     * @funcionalidad instanciamos la clase y la guardamos en una variable estática
     * @staticvar psUser $instancia instancia de la clase
     * @return \psUser devolvemos una instancia de la clase
     */
    public static function &getInstance(){
        static $instancia;
        if(is_null($instancia)){
            $instancia = new psUser();
        }
        return $instancia;
    }

    /**
     * @funcionalidad cargamos la sesión del usuario
     * @return devolvemos la sesión del usuario cargada
     */
    public function psUser(){
        //cargamos las variables globales de las clases core y medallas
        global $psCore, $psMedallas;
        //cargamos la sesion del usuario
        $this->sesion = new psSesion();
        if(!$this->sesion->leerSesion()){
            $this->sesion->createSesion();
        }else{
            $this->loadUser();
        }
        //si es miembro actualizamos los puntos del usuario
        if($this->member){
            $this->actualizarPuntos();
        }
    }

    /**
     * @functionalidad cada 24 horas actualizaremos los puntos del usuario al valor establecido en la configuración
     * @return devolvemos un valor booleano confirmando el cambio
     */
    function actualizarPuntos(){
        global $psDb;
        //obtenemos la hora a la que recargar los puntos del usuario
        //0 = media noche en el servidor
        $ultima = $this->info['user_nextpuntos'];
        $tiempo = time();
        //si han pasado 24 horas recargamos los puntos
        if($ultima < $tiempo){
            $hora = date("G",$tiempo);
            $min = date("i",$tiempo);
            $seg = date("s",$tiempo);
            $siguiente = (((24 - $hora) * 3600) - ($min + $seg)) + $tiempo;
            //hacemos la consulta en la base de datos
            $consulta = "UPDATE u_miembros SET user_puntosxdar = :puntosxdar, user_nextpuntos = :siguiente WHERE user_id = :uid";
            $valores = [
                'puntosxdar' => $psCore->settings['c_keep_points'] == 0 ? $this->permisos['gopfd'] : 'user_puntosxdar' + $this->permisos['gopfd'],
                'siguiente' => $siguiente,
                'uid' => $this->user_id,
            ];
            $psDb->db_execute($consulta, $valores);
            return true;
        }
    }

    /**
     * @funcionalidad comprobamos en la base de datos cuando un usuario se hace login en la web
     * @param  [type]  $name       [description] nombre del usuario
     * @param  [type]  $pass       [description] password
     * @param  boolean $remember   [description] si ha activada la casilla recordar datos de acceso
     * @param  [type]  $redirectTo [description] si la cuenta ha sido o no activada
     * @return [type]              [description] devolvemos un valor booleano para terminar con la función
     */
    function login($name, $pass, $remember = false, $redirectTo = null){
        global $psCore, $psDb;
        //encriptamos la pass para comprobarla con la db
        $pass = md5($pass);
        //consultamos con la base de datos
        $u_pwtype = $psDb->db_execute((($psDb->db_execute("SHOW COLUMNS FROM u_miembros LIKE \'user_pwtype\'") == 1) ? 'user_pwtype' : ''),null, 'rowCount');
        $consulta = "SELECT user_id, user_password, :pwtype, user_activo, user_baneado FROM u_miembros WHERE LOWER(user_name) = :name";
        $valores = [
            'pwtype' => $u_pwtype,
            'name' => $name,
        ];
        $consulta2 = $psDb->db_execute($consulta, $valores);
        $datos = $psDb->db_execute($consulta2, null, 'fetch_assoc');
        //comprobamos si el usuario existe
        if(empty($datos)) return '0: El nombre de usuario con el que intenta aceder no existe';

        if($datos['user_pwtype']){
            //realizamos la consulta en la db
            $consulta3 = "UPDATE u_miembros SET user_password = :pass, user_pwtype = \'0\' WHERE user_id = :uid";
            $valores2 = [
                'pass' => $pass,
                'uid' => $datos['user_id'],
            ];
            $psDb->db_execute($consulta3, $valores2);
            $datos['user_password'] = $pass;
        }
        //comprobamos la contraseña
        if($datos['user_password'] != $pass){
            return '0: Error. Esa contrase&ntilde;a es incorrecta.';
        }else{
            if($datos['user_activo'] == 1){
                //actualizamos la sesión del usuario
                $this->sesion->anadirSesion($datos['user_id'], $remember, true);
                //cargamos la información del usuario
                $this->cargarUser(true);
                //comprobamos si tenemos que asignar alguna medalla al usuario
                $this->darMedalla();
                //redirigimos al usuario
                if($redirectTo != null){
                    $psCore->redirectTo($redirectTo);
                }else{
                    return true;
                }
            }else{
                return '0: Tu cuenta todav&iacute;a no ha sido activada. Por favor revisa tu correo para poder acceder a la comunidad.';
            }
        }
    }

    /**
     * @funcionalidad desconectamos al usuario de la web
     * @param  [type] $uid        [description] id del usuario
     * @param  string $redirectTo [description] redireccionamos al home
     * @return [type]             [description] devolvemos un valor booleano para terminar con la función
     */
    function logout($uid, $redirectTo = '/'){
        global $psCore, $psDb;
        //destruimos la sesión
        $this->sesion = new psSesion();
        $this->sesion->leerSesion();
        $this->sesion->destruirSesion();
        //limpiamos variables
        $this->member = 0;
        $this->info = '';
        //obtenemos los datos de la última conexión del usuario
        $ultimaConexion = (time() - (($psCore->settings['c_last_active'] * 60) * 3));
        //actualizamos los datos en la db
        $consulta = "UPDATE u_miembros SET user_lastactive \':lastactive\' WHERE user_id = :uid";
        $valores = [
            'lasactive' => $ultimaConexion,
            'uid' => (int)$uid,
        ];
        $psDb->db_execute($consulta,$valores);
        //redirigimos al usuario
        if($redirectTo != null){
            $psCore->redirectTo($redirectTo);
        }else{
            return true;
        }
    }

    /**
     * @funcionalidad cargamos el usuario mediante su id
     * @param  boolean $login [description] si ha iniciado sesión, por defecto no (false)
     * @return [type]         [description]
     */
    function load($login = false){
        global $psDb;
        //cargamos los datos de la base de datos
        $consulta = "SELECT u.*, s.* FROM u_miembros u, u_sessions s WHERE s.session_id = :id AND u.user_id = s.session_user_id";
        $valores = [
            'id' => $this->sesion->id,
        ];
        $query = $psDb->db_execute($consulta, $valores);
        //comprobamos si el usuario existe
        if(!isset($this->info['user_id'])){
            return false;
        }
        //realizamos las consultas necesarias para cargar los permisos del usuario
        $consulta2 = "SELECT r_name, r_color, r_image, r_allows FROM u_rangos WHERE rango_id = :uid";
        $valores2 = [
            'uid' => $this->info['user_id'],
        ];
        $this->info['rango'] = $pdDb->db_execute($consulta2, $valores2, 'fetch_assoc');

        $consulta3 = "SELECT r_allows FROM u_rangos WHERE rango_id = :u_rango";
        $valores3 = [
            'u_rango' => $this->info['user_rango'],
        ];
        $query3 = $psDb->db_execute($consulta3, $valores3);
        $this->permisos = unserialize($query3['r_allows']);
        //actualizamos la variable booleana member
        $this->member = 1;
        //comprobamos el rango del usuario
        if($this->permisos['sumo'] == false && $this->permisos['suad'] == true){
            $this->admod = 1;
        }else if($this->permisos['sumo'] == true && $this->permisos['suad'] == false){
            $this->admod = 2;
        }else if($this->permisos['sumo'] || $this->permisos['suad']){
            $this->admod = true;
        }else{
            $this->admod = 0;//no es admin
        }

        //obtenemos el nombre y nick y si esta baneado
        $this->nick = $this->info['user_name'];
        $this->user_id = $this->info['user_id'];
        $this->baneado = $this->info['user_baneado'];
        //actualizamos en la db el ultimo acceso
        $consulta4 = "UPDATE u_miembros SET user_lastactive = :tiempo WHERE user_id = :uid";
        $valores4 = [
            'tiempo' => time(),
            'uid' => $this->user_id,
        ];
        $psDb->db_execute($consulta4, $valores4);
        //comprobamos si el usuario ha iniciado sesión
        if($login){
            //actualizamos ultimo logeo
            $consulta5 = "UPDATE u_miembros SET user_lastlogin = :time_now WHERE user_id = :uid";
            $valores5 = [
                'time_now' => $this->sesion->time,
                'uid' => $this->user_id,
            ];
            $psDb->db_execute($consulta5, $valores5);
            //registramos la ip del usuario
            $consulta6 = "UPDATE u_miembros SET user_last_ip = :ip WHERE user_id = :uid";
            $valores6 = [
                'ip' => $this->sesion->ip,
                'uid' => $this->user_id,
            ];
            $psDb->db_execute($consulta6, $valores6);
        }
        //borramos la variable sesion por seguridad
        unset($this->sesion);
    }

    /**
     * @funcionalidad activamos la cuenta del usuario
     * @param  [type] $uid [description] id del usuario
     * @return [type]      [description]
     */
    function activate($uid){
        global $psDb;
        if(empty($uid)) $uid = (int)$_GET['uid'];
        //ejecutamos la consulta en la base de datos
        $consulta = "SELECT user_name, user_password, user_registro FROM u_miembros WHERE user_id = :uid";
        $valores = [
            'uid' => $uid,
        ];
        $query = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
        if($psDb->db_execute($consulta, $valores, 'rowCount') == 0){
            return false;
        }else{
            $consulta2 = "UPDATE u_miembros SET user_active = 1 WHERE user_id = :uid";
            $valores2 = [
                'uid' => $uid,
            ];
            if($psDb->db_execute($consulta2, $valores2)){
                return $query;
            }else{
                return false;
            }
        }
    }

    /**
     * @funcionalidad obtenemos el id del usuario obtenido por parámetro
     * @param  [type] $psUser [description] id del usuario
     * @return [type]         [description] devolvemos el id del usuario si todo ha salido correctamente
     */
    function getUid($psUser){
        global $psCore, $psDb;
        $username = strtolower($psUser);
        $consulta = "SELECT user_id FROM u_miembros WHERE LOWER(user_name) = :username";
        $valores = [
            'username' => $username,
        ];
        $psUser = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
        $psUserId = $psUser['user_id'];
        if(empty($psUserId)){
            return 0;
        }else{
            return $psUserId;
        }
    }

    /**
     * @funcionalidad comprobamos si el usuario está baneado a partir de la variable $user_id global
     * @return [type] [description] devolvemos false si el usuario no está baneado,
     *                              o los datos del usuario en caso de que sí lo esté
     */
    function getBaned(){
        global $psDb;
        //realizamos las consultas oportunas
        $consulta = "SELECT * FROM u_suspension WHERE user_id = :uid";
        $valores = [
            'uid' => $this->user_id,
        ];
        $datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
        //obtenemos la hora actual
        $tiempo = time();
        if($datos['susp_termina'] > 1 && $datos['sus_termina'] < $tiempo){
            $consulta2 = "UPDATE u_miembros SET user_baneado = 0 WHERE user_id = :uid";
            $valores2 = [
                'uid' => $this->user_id,
            ];
            $psDb->db_execute($consulta2, $valores2);
            $consulta3 = "DELETE FROM u_suspension WHERE user_id = :uid";
            $valores3 = [
                'uid' => $this->user_id,
            ];
            $psDb->db_execute($consulta3, $valores3);
            return false;
        }else{
            return $datos;
        }
    }

    /**
     * @funcionalidad obtenemos el nombre del usuario a partir de su id
     * @param  [type] $uid [description] id del usuario del cual se quiere obtener el nombre
     * @return [type]      [description] devolvemos el nombre del usuario
     */
    function getUserName($uid){
        global $psDb;
        $consulta = "SELECT user_name FROM u_miembros WHERE user_id = :uid";
        $valores = [
            'uid' => (int)$uid,
        ];
        $psUser = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
        return $psUser['user_name'];
    }

    /**
     * @funcionalidad comprobamos si estamos siguiendo al usuario obtenido por parametro
     * @param  [type] $uid [description] id del usuario a comprobar
     * @return [type]      [description] obtenemos un valor booleano con el resultado de la comprobación
     */
    function follow($uid){
        global $psDb;
        $consulta = "SELECT follow_id FROM u_follows WHERE f_id = :uid AND f_user = :uid2 AND f_type = :data";
        $valores = [
            'uid' => (int)$uid,
            'uid2' => $uid,
            'data' => 1,
        ];
        $datos = $psDb->db_execute($consulta, $valores, 'rowCount');
        if($datos > 0){
            return true;
        }
        else{
            return false;
        }
    }

    /**
     * @funcionalidad dar la medalla al usuario cuando realiza una acción concreta establecida
     * @return [type] [description]
     */
    function darMedalla(){
        global $psDb;
        //realizamos las consultas oportunas en la db
        //obtenemos id de medallas
        $consulta = "SELECT wm.medal_id FROM w_medallas AS wm LEFT JOIN w_medallas_asign AS w ON wm.medal_id = w.medal_id WHERE wm.m_type = \'1\' AND w.medal_id = :uid";
        $valores = ['uid' => $this->user_id,];
        $query = $psDb->db_execute($consulta, $valores, 'rowCount');
        //seguidores por id de usuario
        $consulta2 = "SELECT COUNT(follow_id) AS f FROM u_follows WHERE f_id = :uid AND f_type = \'1\'";
        $valores2 = ['uid' => $this->user_id,];
        $query2 = $psDb->db_execute($consulta2, $valores2, 'fetch_num');
        //seguidores por nombre de usuario
        $consulta3 = "SELECT COUNT(follow_id) AS f FROM u_follows WHERE f_user = :uid AND f_type = \'1\'";
        $valores3 = ['uid' => $this->user_id,];
        $query3 = $psDb->db_execute($consulta3, $valores3, 'fetch_num');
        //comentarios en post
        $consulta4 = "SELECT COUNT(cid) AS c FROM p_comentarios WHERE c_user = :uid AND c_status = \'0\'";
        $valores4 = ['uid' => $this->user_id,];
        $query4 = $psDb->db_execute($consulta4, $valores4, 'fetch_num');
        //comentarios en fotos
        $consulta5 = "SELECT COUNT(cid) AS c FROM f_comentarios WHERE c_user = :uid";
        $valores5 = ['uid' => $this->user_id,];
        $query5 = $psDb->db_execute($consulta5, $valores5, 'fetch_num');
        //fotos
        $consulta6 = "SELECT COUNT(foto_id) AS f FROM f_fotos WHERE f_status = \'0\' AND f_user = :uid";
        $valores6 = ['uid' => $this->user_id,];
        $query6 = $psDb->db_execute($consulta6, $valores6, 'fetch_num');
        //post
        $consulta7 = "SELECT COUNT(post_id) AS p FROM p_posts WHERE post_user = :uid AND post_status = \'0\'";
        $valores7 = ['uid' => $this->user_id,];
        $query7 = $psDb->db_execute($consulta7, $valores7, 'fecth_num');

        //obtenemos las medallas
        $consulta8 = "SELECT medal_id, m_cant, m_cond_user, m_cond_user_rango FROM w_medallas WHERE m_type = \'1\' ORDER BY m_cant DESC";
        $query8 = $psDb->db_execute($consulta8);
        $datos = $psDb->resultadoArray($query8);
        //damos las medallas a los usuarios
        foreach($datos as $medallas){
            if($medalla['m_cond_user'] == 1 && !empty($this->info['user_puntos']) && $medalla['m_cant'] > 0 && $medalla['m_cant'] <= $this->info['user_puntos']){
                $new = $medalla['medal_id'];
            }else if($medalla['m_cond_user'] == 2 && !empty($query2) && $medalla['m_cant'] > 0 && $medalla['m_cant'] <= $query2[0]){
                $new = $medalla['medal_id'];
            }else if($medalla['m_cond_user'] == 3 && !empty($query3) && $medalla['m_cant'] > 0 && $medalla['m_cant'] <= $query3[0]){
                $new = $medalla['medal_id'];
            }else if($medalla['m_cond_user'] == 4 && !empty($query4) && $medalla['m_cant'] > 0 && $medalla['m_cant'] <= $query4[0]){
                $new = $medalla['medal_id'];
            }else if($medalla['m_cond_user'] == 5 && !empty($query5) && $medalla['m_cant'] > 0 && $medalla['m_cant'] <= $query5[0]){
                $new = $medalla['medal_id'];
            }else if($medalla['m_cond_user'] == 6 && !empty($query6) && $medalla['m_cant'] > 0 && $medalla['m_cant'] <= $query6[0]){
                $new = $medalla['medal_id'];
            }else if($medalla['m_cond_user'] == 7 && !empty($query7) && $medalla['m_cant'] > 0 && $medalla['m_cant'] <= $query7[0]){
                $new = $medalla['medal_id'];
            }else if($medalla['m_cond_user'] == 8 && !empty($query) && $medalla['m_cant'] > 0 && $medalla['m_cant'] <= $query){
                $new = $medalla['medal_id'];
            }else if($medalla['m_cond_user'] == 9 && !empty($this->info['user_rango']) && $medalla['m_cant'] > 0 && $medalla['m_cond_user_rango'] == $this->info['user_rango']){
                $new = $medalla['medal_id'];
            }
            //Si hay una nueva medalla hacemos las consultas en la db
            if(!empty($new)){
                $consulta9 = "SELECT id FROM w_medallas_asign WHERE medal_id = :new AND medal_for = :uid";
                $valores9 = [
                    'new' => $new,
                    'uid' => $this->user_id,
                ];
                //comprobamos si la consulta ya se encuentra en la base de datos con ese usuario
                if($psDb->db_execute($consulta9, $valores9, 'rowCount')){
                    $con1 = "INSERT INTO w_medallas_asign (medal_id, medal_for, medal_date, medal_ip) VALUES (:id, :for, :dat, :ip";
                    $val1 = [
                        'id' => (int)$new,
                        'for' => $this->user_id,
                        'dat' => time(),
                        'ip' => $_SERVER['REMOTE_ADDR'],
                    ];
                    $con2 = "INSERT INTO u_monitor (user_id, obj_uno, not_type, not_date) VALUES (:uid, :new, :type, :tim)";
                    $val2 = [
                        'uid' => $this->user_id,
                        'new' => (int)$new,
                        'type' => 15,
                        'tim' => time(),
                    ];
                    $con3 = "UPDATE w_medallas SET m_total = m_total + 1 WHERE medal_id = :new";
                    $val3 = ['new' => (int)$new,];
                    $psDb->db_execute($con1, $val1);
                    $psDb->db_execute($con2, $val2);
                    $psDb->db_execute($con3, $val3);
                }
            }
        }
    }

    /**
     * @funcionalidad obtenemos la información básica del usuario, estadísticas, estado y comprobamos su seguimiento
     * @param  [type] $uid [description] id del usuario a comprobar
     * @return [type]      [description] devolvemos un array con los datos del usuario
     */
    function getUserInfo($uid){
        //creamos las variables globales
        global $psCore, $psDb;
        //creamos las variables locales
        $online = (time() - ($psCore->settings['c_last_active'] * 60));
        $inactive = ($online * 2);//tiempo inactivo = doble de online
        //obtenemos la info general del usuario
        $consulta = "SELECT u.user_id, u.user_name, u.user_lastactive, u.user_baneado, p.user_sexo, p.user_pais, p.p_nombre, p.p_mensaje, p.p_sitio FROM u_miembros AS u, u_perfil AS p WHERE u.user_id = :uid AND p.user_id = :uidd";
        $valores = [
            'uid' => (int)$uid,
            'uidd' => (int)$uid,
        ];
        $datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');

        //obtenemos el estado del usuario
        if($datos['user_lastactive'] > $online){
            $datos['status'] = array('t' => 'Online', 'css' => 'online',);
        }else if($datos['user_lastactive'] > $inactive){
            $datos['status'] = array('t' => 'Inactivo', 'css' => 'inactive',);
        }else {
            $datos['status'] = array('t' => 'Offline', 'css' => 'offline',);
        }

        //obtenemos las estadísticas del usuario
        $consulta = "SELECT u.user_puntos, r.r_name, r.r_color, r.r_image FROM u_miembros AS u LEFT JOIN u_rangos AS r ON u.user_rango = r.rango_id WHERE user_id = :uid";
        $valores = ['uid' => $uid,];
        $datos['stats'] = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
        $c1 = "SELECT COUNT(post_id) AS p FROM p_posts WHERE post_user = :uid AND post_status = \'0\'";
        $c2 = "SELECT COUNT(cid) AS c FROM p_comentarios WHERE c_user = :uid AND c_status = \'0\'";
        $c3 = "SELECT COUNT(follow_id) AS s FROM u_follows WHERE f_id = :uid AND f_type = \'1\'";
        $v1 = ['uid' => (int)$uid,];
        $q1 = $psDb->db_execute($c1, $v1);
        $q2 = $psDb->db_execute($c2, $v1);
        $q3 = $psDb->db_execute($c3, $v1);
        $datos['status']['user_posts'] = $q1[0];
        $datos['status']['user_comentarios'] = $q2[0];
        $datos['status']['user_seguidores'] = $q3[0];

        //comprobamos si estamos siguiendo al usuario
        $datos['follow'] = $this->follow($uid);

        //devolvemos el array datos con la info del usuario
        return $datos;
    }
}
