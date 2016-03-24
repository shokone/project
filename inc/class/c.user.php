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
}
