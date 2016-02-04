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
    
    public function psUser(){
        
    }
    
}
