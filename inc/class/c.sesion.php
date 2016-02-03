<?php
//comprobamos si hemos declarado la contante PS_HEADER
if(!defined('PS_HEADER')){
    exit("No se permite el acceso al script");
}
/**
 * clase psSesion
 * clase destinada al control de la sesion del usuario
 * 
 * @name c.sesion.php
 * @author Iván Martínez Tutor
 */
class psSesion{
    //declaramos las variables de la clase
    protected $id_sesion = "";//id de la sesion
    protected $ses_expira = 7200;//tiempo de expiración de la sesión
    protected $ses_ip = false;//está activado el login por ip?
    protected $ses_time_online = 300;//tiempo de sesión online
    protected $cookie_pref = 'ps__';//prefijo de la cookie
    protected $cookie_nombre = "";//nombre de la cookie
    protected $cookie_dominio = "";//dominio de la cookie
    protected $cookie_path = "/";//ruta de la cookie
    protected $userinfo;//información del usuario
    protected $ip;//ip del usuario
    protected $time;//hora actual
    
    /**
     * @funcionalidad instanciamos la clase y la guardamos en una variable estática
     * @staticvar psSesion $instancia instancia de la clase
     * @return \psSesion devolvemos una instancia de la clase
     */
    public static function &getInstance(){
        static $instancia;
        if(is_null($instancia)){
            $instancia = new psSesion();
        }
        return $instancia;
    }
    
    /**
     * @funcionalidad constructor para la sesion
     * @global type $psCore variable global del núcleo
     */
    public function __construct() {
        global $psCore;
        //obtenemos el tiempo
        $this->time = time();
        //obtenemos los datos para el dominio de la cookie
        $dom = parse_url($psCore->settings['url']);
        $dom = str_replace('www','',strtolower($dom['host']));
        //establecemos valores para las cookies
        $this->cookie_dominio = ($dom == 'localhost') ? '' : '.'.$dom;
        $this->cookie_nombre = $this->cookie_pref.$this->cookie_dominio;
        //obtenemos la ip del usuario
        //$this->ip =
        //si está activada la opción login por ip, 
        //iniciamos nueva sesión cuando el usuario cambie de ip
        //$this->ses_ip = empty($psCore->settings['c_allow_sess_ip']) ? false : true;
        //damos un tiempo para expirar la sesión
        //$this->ses_time_online = 
    }
    
    /**
     * @funcionalidad leemos la sesión actual de la bd
     * y eliminamos sesión si algo no es correcto
     * @return boolean obtenemos true o false si ha salido todo bien o no
     */
    public function leerSesion(){
        $this->id_sesion = $_COOKIE[$this->cookie_nombre];
        //comprobamos si el id es válido
        if(strlen($this->id_sesion) > 32){
            return false;
        }
        
        //obtenemos los datos de la sesión de la base de datos
        $valores = ['id' => $this->id_sesion];
        $consulta = "SELECT * FROM u_sessions WHERE session_id = :id";
        $resultado = db_execute($consulta, $valores, 'fetch_assoc');
        
        //comprobamos si existe en la base de datos y la destruimos
        if(!isset($resultado['session_id'])){
            $this->destruir_sesion();
            return false;
        }
        
        //comprobamos si es la misma sesión que hay actualmente
        if(($resultado['session_time'] + $this->ses_time_online) < $this->time AND empty($resultado['session_autologin'])){
            $this->destruir_sesion();
            return false;
        }
        
        //si está activada la opción de login al cambiar ip y ha cambiado eliminamos sesión
        if($this->ses_ip == true && $resultado['session_ip'] != $ip){
            $this->destruir_sesion();
            return false;
        }
        
        $this->userinfo = $resultado;
        //eliminamos los datos de la consulta por seguridad
        unset($resultado);
        return true;
    }
    
    /**
     * @funcionalidad eliminamos la sesión en la base de datos
     * reseteamos la cookie
     */
    public function destruir_sesion(){
        //eliminamos la sesión de la base de datos
        $valores = ['id' => $this->id_sesion];
        db_execute("DELETE FROM u_sessions WHERE session_id = :id",$valores);
        //reseteamos la cookie
        $this->set_cookie('cookproyecto', '', -604800);
    }
    
    /**
     * @funcionalidad generamos un id aleatorio para la sesion
     * @return type devolvemos el id generado
     */
    public function generar_sid(){
        $id = "";
        //generamos un id aleatorio
        while(strlen($id) < 32){
            $id .= rand(0,9);
        }
        $id .= $this->ip;
        return $id;
    }
    
    public function anadirSesion($uid = 0, $autologin = false, $forzarUpdate = false){
        //comprobamos si el tiempo de sesión es superior al actual y si no ha sido forzada la actualización
        if(($this->userinfo['session_time'] + $this->ses_time_online) >= $this->time && $forzarUpdate == false){
            //si todo bien, no añadimos nada y devolvemos un return vacío
            return;
        }
        
        //obtenemos los datos para su actualización
        $this->userinfo['session_user_id'] = empty($uid) ? $this->userinfo['session_user_id'] : $uid;
        $this->userinfo['session_ip'] = $this->ip;
        $this->userinfo['session_time'] = $this->time;
        //con autologin comprobamos 2 veces
        //la primera con la variable pasada por parametro
        $autologin = ($autologin == false) ? 0 : 1;
        //la segunda se hace la consulta con los datos obtenidos de la bd, se configura desde la administración
        $this->userinfo['session_autologin'] = empty($this->userinfo['session_autologin']) ? $autologin : $this->userinfo['session_autologin'];
       
        //ahora actualizamos en la base de datos
        $valores = ['suid' => $this->userinfo['session_user_id'],'sesip' => $this->userinfo['session_ip'],'stime' => $this->userinfo['session_time'],'sesauto' => $this->userinfo['session_autologin'],'id' => $this->id_sesion];
        db_execute("UPDATE u_sessions SET session_user_id = :suid, session_ip = :sesip, session_time = :stime, session_autologin = :sesauto WHERE session_id = :id",$valores);
        
        //ahora actualizamos la cookie
        if(!empty($this->userinfo['session_autologin'])){
            //si el usuario quiere mantener la sesión iniciada, el máximo será de 1 semana
            $expira = 604800;
        }else{
            $expira = $this->ses_expira;
        }
        //creamos la cookie para la sesión
        $this->setCookie('cookproyecto', $this->id_sesion, $expira);
    }
    
    /**
     * @funcionalidad creamos una cookie con los datos obtenidos por parametro
     * damos a la cookie el valor path y un valor de dominio
     * @param type $name nombre de la cookie
     * @param type $dato valor de la cookie
     * @param type $time tiempo de expiración
     */
    public function setCookie($name, $dato,$time){
        $cookieName = $this->cookie_nombre."_".$name;
        //establecemos la cookie
        setcookie($cookieName, $dato, ($this->time + $time), '/', $this->cookie_dominio);
    }
    
    /**
     * @funcionalidad añadimos la sesión a la base de datos y creamos la cookie con los valores de la sesión
     */
    public function createSesion(){
        //primero tenemos que generar un id para la sesión
        $this->id_sesion = $this->generar_sid();
        //guardamos los datos en la bd, si se inicia sesión se actualizarán estos datos
        $valores = ['id_sesion' => $this->id_sesion,'ip' => $this->ip,'time' => $this->time];
        db_execute("INSERT INTO u_sessions (session_id, session_user_id, session_ip, session_time) VALUES (:id_sesion, 0, :ip, :time)",$valores);
        //después creamos la cookie
        $this->setCookie('cookproyecto', $this->id_sesion, $this->ses_expira);
    }
}