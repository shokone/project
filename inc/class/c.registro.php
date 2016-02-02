<?php
//comprobamos si hemos declarado la contante PS_HEADER
if(!defined('PS_HEADER')){
    exit("No se permite el acceso al script");
}
/**
 * clase psRegistro
 * clase destinada al control del registro de los usuarios en la web
 * 
 * @name c.registro.php
 * @author Iván Martínez Tutor
 */
class psRegistro{
    /**
     * @funcionalidad instanciamos la clase si no ha sido ya instanciada
     * @staticvar psRegistro $instancia guardamos la clase en una variable estatica
     * @return \psRegistro
     */
    public static function &getInstance(){
        static $instancia;
        if(is_null($instancia)){
            $instancia = new psRegistro();
        }
        return $instancia;
    }
    
    public function nuevoRegistro(){
        global $psCore, $psUser;
        //obtenemos los datos en un array
        $psDatos = [
            'user_nick' => filter_input(INPUT_POST,'nick'),
            'user_password' => filter_input(INPUT_POST,'password'),
            'user_email' => filter_input(INPUT_POST,'email'),
            'user_dia' => filter_input(INPUT_POST,'dia'),
            'user_mes' => filter_input(INPUT_POST,'mes'),
            'user_year' => filter_input(INPUT_POST,'year'),
            'user_sexo' => filter_input(INPUT_POST,'sexo') == 'f' ? '0' : 1,
            'user_pais' => strtoupper(filter_input(INPUT_POST,'pais')),
            'user_estado' => filter_input(INPUT_POST,'estado'),
            'user_terminos' => filter_input(INPUT_POST,'terminos'),
            //datos captcha cuando se haya implementado
            'user_registro' => time(),
        ];
        //errores en campos
        $errores = [
            'default' => 'Es necesario completar este campo.',
            'nick' => 'Ese nombre de usuario ya existe.',
            'password' => 'El nick y la contrase&ntilde;a no pueden ser iguales.',
            'password2' => 'Las dos contrase&ntilde;as deben ser iguales.',
            'email' => 'El formato del email debe ser nombre@dominio',
            'email2' => 'El email escogido ya se encuentra registrado.',
            'captcha' => 'El c&oacute;digo de verificaci&oacute;n es incorrecto.'
        ];
        //cargamos los datos del captcha
        
        //comprobamos que ningun campo se encuentre vacío
        foreach($psDatos as $indice => $valor){
            if($valor == ''){
                return $errores['default'];
            }
        }
        
        //comprobamos que el nick sea válido con expresiones regulares
        if(!preg_match("/^[a-zA-Z0-9_-]{4,16}$/", $psDatos['user_nick'])){
            exit('nick: Ese nombre de usuario no es v&aacute;lido.');
        }
        
        //comprobamos en la base de datos si el nick o el email existen
        $valores = ['nick' => $psDatos['user_nick'], 'email' => $psDatos['user_email']];
        $consulta = "SELECT user_name, user_email FROM u_miembros WHERE LOWER(user_name) = :nick OR LOWER(user_email) = :email LIMIT 1";
        if(db_execute($consulta,$valores,'rowCount') > 0 || !filter_var($psDatos['user_email'],FILTER_VALIDATE_EMAIL) || $psCore->settings['c_reg_active'] == 0){
            exit("0: Lo sentimos, no ha sido posible registrarle, puede haber campos vac&iacute;os, no v&aacute;lidos o no esta permitido el registro de usuarios en estos momentos.");
        }
    }
}

