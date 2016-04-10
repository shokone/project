<?php
//comprobamos si hemos declarado la contante PS_HEADER
if(!defined('PS_HEADER')){
    exit("No se permite el acceso al script");
}
/**
 * clase psEmail
 * clase destinada al control del envío de emails a los usuarios de la web
 * 
 * @name c.email.php
 * @author Iván Martínez Tutor
 */
class psEmail{
    protected $em_info = [];//datos para enviar el email
    protected $em_subject;
    protected $em_head;
    protected $em_body;
    protected $em_to;
    protected $em_error;//guardaremos aquí el error en caso de existir
    
    /**
     * @funcionalidad obtenemos los datos para el envio del correo
     * @param type $datos obtenemos los datos
     * @param type $type obtenemos el tipo de correo
     */
    public function psEmail($datos,$type){
        $this->em_info = [
            'ref' => $type,
            'data' => $datos
        ];
    }
    
    /**
     * @funcionalidad cambiamos los datos del email
     * @param type $to destinatario del email
     * @param type $subject asunto del email
     * @param type $body cuerpo del email
     */
    public function setEmail($to,$subject,$body){
        $this->em_to = $to;
        $this->em_subject = $subject;
        $this->em_head = $this->setEmHead();
        $this->em_body = $body;
    }
    
    /**
     * @funcionalidad imprimimos la cabecera por defecto para los correos
     * @global type $psCore cargamos la variable global de la clase psCore
     * @return type devolvemos el string generado
     */
    public function setEmHead(){
        global $psCore;
        $remitente = $psCore->settings['titulo']."<no-reply@".$psCore->settings['domain'].">";
        
        $head = "From: ".$remitente."<br>";
        $head .= "Return-path: ".$remitente."<br>";
        $head .= "Reply-to: ".$remitente."<br>";
        return $head;
    }
    
    /**
     * @funcionalidad comprobamos los datos y enviamos el email
     * @return boolean devolvemos un booleano si todo va bien devolverá true
     */
    public function sendEmail(){
        if(mail($this->em_to, $this->em_subject, $this->em_body, $this->em_head)){
            return true;
        }
        return false;
    }
}

