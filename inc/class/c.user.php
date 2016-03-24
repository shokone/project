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
                return '0: Tu cuenta todav&iacute;a no ha sido activada. Por favor revisa tu correo para poder acceder a la nuestra comunidad.';
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
}
