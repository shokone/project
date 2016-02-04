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
        global $psCore, $psUser, $psDb;
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
            'email' => 'El formato del email debe ser nombre@ejemplo.com',
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
        if($psDb->db_execute($consulta,$valores,'rowCount') > 0 || !filter_var($psDatos['user_email'],FILTER_VALIDATE_EMAIL) || $psCore->settings['c_reg_active'] == 0){
            exit("0: Lo sentimos, no ha sido posible registrarle, puede haber campos vac&iacute;os, no v&aacute;lidos o no esta permitido el registro de usuarios en estos momentos.");
        }
        //insertamos los datos en la base de datos
        $pass = md5($psDatos['user_password']);
        $valores2 = ['nick' => $psDatos['user_nick'],'pass' => $pass, 'email' => $psDatos['user_email'], 'rango' => (empty($psCore->settings['c_reg_rango']) ? 3 : $psCore->settings['c_reg_rango']),'registro' => $psDatos['user_registro']];
        $consulta2 = "INSERT INTO u_miembros (user_name, user_password, user_email, user_rango, user_registro) VALUES (:nick, :pass, :email, :rango, :registro)";
        
        if($psDb->db_execute($consulta2,$valores2)){
            $psDatos['user_id'] = getLastInsertId();
            //insertamos los datos del perfil del usuario en la base de datos
            $valores3 = ['user_id' => $psDatos['user_nick'], 'user_dia' => $psDatos['user_dia'], 'user_mes' => $psDatos['user_mes'], 'user_ano' => $psDatos['user_year'], 'pais' => $psDatos['user_pais'],'estado' => $psDatos['user_estado'],'sexo' => $psDatos['user_sexo']];
            $psDb->db_execute("INSERT INTO u_perfil (user_id, user_dia, user_mes, user_ano, user_pais, user_estado, user_sexo) VALUES (:user_id, :user_dia, :user_mes, :user_ano, :pais, :estado, :sexo)",$valores3);
            $valores4 = ['user' => $psDatos['user_id']];
            $psDb->db_execute("INSERT INTO u_portal (user_id) VALUES (:user)",$valores4);
            
            //damos la bienvenida al usuario
            $this->darBienvenida($psCore,$psDatos,$psDb);
        }
        //si la validación no es automática enviamos un email al usuario para activar su cuenta
        $this->emailRegistro($psCore,$psDatos,$psUser,$psDb);
        
    }
    
    /**
     * @funcionalidad enviamos un mensaje de bienvenida al usuario al registrarse
     * valores para c_met_welcome
     * guardará la acción a realizar al registrarse el usuario (si manda y como el mensaje de bienvenida)
     * c_met_welcome = 0 -> no se da bienvenida
     * c_met_welcome = 1 -> se da la bienvenida en el muro
     * c_met_welcome = 2 -> se da la bienvenida por mensaje privado
     * c_met_welcome = 3 -> se da la bienvenida por aviso
     * @param type $psCore variable global de la clase psCore
     * @param type $psDatos pasamos un array con los datos necesarios
     */
    public function darBienvenida($psCore,$psDatos,$psDb){
        $bienvenido = $psCore->settings['c_met_welcome'];
        
        if($bienvenido > 0 && $bienvenido < 4){
            $mensaje = $psCore->settings['c_message_welcome'];
            $sexo = "Bienvenid".($psDatos['user_sexo'] == 1 ? "o" : "a");
            $men_bienvenido = $sexo.$psDatos['user_nick']." a ".$psCore-settings['titulo'];
            switch($bienvenido){
                case 1:
                    $valores = ['user_id' => $psDatos['user_id'],'date' => date(),'mensaje' => $men_bienvenido];
                    $psDb->db_execute("INSERT INTO u_muro (p_user,p_user_pub,p_date,p_body,p_type) VALUES (:user_id,\'1\',:date,:mensaje,\'1\')", $valores);
                    $id = getLastInsertId();
                    $valores2 = ['user_id' => $psDatos['user_id'],'id' => $id,];
                    $psDb->db_execute("INSERT INTO u_monitor (user_id,obj_user,obj_uno,not_type,not_total,not_menubar,not_monitor) VALUES (:user_id, \'1\' :id, \'12\',\'1\',\'1\',\'1\',\'1\')",$valores2);
                    break;
                case 2:
                    $valores3 = ['user_id' => $psDatos['user_id'],'sexo' => $sexo." a ".$psCore->settings['titulo'],'preview' => $men_bienvenido,'time' => time()]; 
                    $consulta = "INSERT INTO u_mensajes (mp_to, mp_from, mp_subject, mp_preview, mp_date) VALUES (:user_id, \'1\', :sexo, :preview , :time)";
                    if($psDb->db_execute($consulta,$valores3)){
                        $id = getLastInsertId();
                        $valores4 = ['id' => $id,'mensaje' => $men_bienvenido,'addr' => $_SERVER['REMOTE_ADDR'],'time' => time()];
                        $psDb->db_execute("INSERT INTO u_respuestas (mp_id, mr_from, mr_body, mr_ip, mr_date) VALUES (id, \'1\', :mensaje, :addr, :time)",$valores4);
                    }
                    break;
                case 3:
                    $valores5 = ['user_id' => $psDatos['user_id'],'sexo' => $sexo." a ".$psCore->settings['titulo'],'mensaje' => $men_bienvenido,'time' => time()];
                    $psDb->db_execute("INSERT INTO u_avisos (user_id, av_subject, av_body, av_date, av_type) VALUES (:user_id, :sexo, :mensaje, :time, \'3\')",$valores5);
                    break;
            }            
        }
    }
    
    /**
     * @funcionalidad si esta desactivado el registro automático enviaremos un correo de confirmación al usuario
     * @param type $psCore variable de la clase psCore
     * @param type $psDatos pasamos los datos por array
     * @param type $psUser variable de la clase psUsuarios
     * @return string si algo no va bien devolverá un mensaje de error
     */
    public function emailRegistro($psCore, $psDatos, $psUser,$psDb){
        if(empty($psCore->settings['c_reg_activate'])){
            $valores = ['user_id' => $psDatos['user_id'],'email' => $psDatos['user_email'],'time' => time()];
            $consulta = "INSERT INTO w_contacts ( user_id, user_email, time, type) VALUES (:user_id, :email, :time, \'2\')";
            if($psDb->db_execute($consulta,$valores)){
                include(PS_ROOT.PS_CLASS."c.email.php");
                $psEmail = new psEmail('activar', 'registro');
                $subject = "Active ahora su cuenta en ".$psCore->settings['titulo'];
                $body='<div style="">
                    <h1 style="">'.$psCore->settings['titulo'].'</h1>
                    <div style="">
                        <h2 style="">Hola '.$psDatos['user_nick'].'</h2>
                        <p>Para poder finalizar el proceso de registro, por favor confirma tu direcci&oacute;n de email pinchando en el siguiente <a href="'.$psCore->settings['url'].'/validar/2/'.$psDatos['user_email'].'">enlace</a></p>
                        <p>Si no puede acceder pinchando en el enlace copie y pegue la siguiente url: '.$psCore->settings['url'].'/validar/2/'.$psDatos['user_email'].'</p><br><br>
                        <p>Una vez haya confirmado su direcci&oacute;n de email, podr&aacute; acceder con las siguientes credenciales:</p>
                        <p>Usuario: '.$psDatos['user_nick'].'<br>Contrase&ntilde;a: '.$psDatos['user_password'].'</p><br>
                        <p>Antes de empezar a interactuar con los dem&aacute;s usuarios de la comunidad, te recomendamos visitar el <a href="'.$psCore->settings['url'].'/pages/protocolo">Protocolo</a>.</p>
                        <p>¡Te damos la bienvenida a '.$psCore->settings['titulo'].' y esperamos que disfrutes tu visita.</p>
                        <div style="">
                            <span>El staff de <strong>'.$psCore->settings['titulo'].' '.$psCore->settings['slogan'].'</strong></span>
                        </div>
                    </div>';
                $psEmail->setEmail($psDatos['user_email'],$subject,$body);
                $psEmail->sendEmail() or die('0: Hubo un error al intentar enviar el mensaje.');
            }else{
                return '0: <div>Ocurri&oacute; un error al intentar procesar el registro, por favor, int&eacute;ntelo de nuevo.</div>';
            }
        }else{
            //activamos la cuenta del usuario
            //iniciamos sesión en la cuenta del usuario
            //
        }
    }
}

