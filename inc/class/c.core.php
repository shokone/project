<?php
//comprobamos si hemos declarado la contante PS_HEADER
if(!defined('PS_HEADER')){
    exit("No se permite el acceso al script");
}
/**
 * clase psCore
 * clase destinada al control de las funciones del script
 * 
 * @name c.core.php
 * @author Iván Martínez Tutor
 */
class psCore{
    protected $settings; //configuraciones del sitio
    protected $consultas;//consultas del sitio
    
    public static function &getInstance(){
        static $instancia;
        if(is_null($instancia)){
            $instancia = new psCore();
        }
        return $instancia;
    }
    
    /**
     * @funcionalidad cargamos los datos de los ajustes guardados en la base de datos
     * @return type devolvemos el array de datos generado
     */
    public function getSettings(){
        $consulta = db_execute("SELECT * FROM w_configuration");
        return db_execute($consulta, null, 'fetch_assoc');
    }
    
    /**
     * @funcionalidad comprobamos que elementos se encuentran en moderacion
     * realizamos una suma del total de datos (post, comentarios, users, fotos)
     * @return type devolvemos un array con los datos de moderacion generados
     */
    public function getNovemodera(){
        $consulta = db_execute("SELECT (SELECT count(post_id) FROM p_posts WHERE post_status = \'3\') as revposts, (SELECT count(cid) FROM p_comentarios WHERE c_status = \'1\') as revcomentarios, (SELECT count(DISTINCT obj_id) FROM w_denuncias WHERE d_type = \'1\') as repposts, (SELECT count(DISTINCT obj_id) FROM w_denuncias WHERE d_type = \'2\') as repmps, (SELECT count(DISTINCT obj_id) FROM w_denuncias WHERE d_type = \'3\') as repusers, (SELECT count(DISTINCT obj_id) FROM w_denuncias WHERE d_type = \'4\') as repfotos, (SELECT count(susp_id) FROM u_suspension) as supusers, (SELECT count(post_id) FROM p_posts WHERE post_status = \'2\') as pospapelera, (SELECT count(foto_id) FROM f_fotos WHERE f_status = \'2\') as fospapelera");
        $datos = db_execute($consulta,null,'fetch_assoc');
        $datos['total'] = $datos['repposts'] + $datos['repfotos'] + $datos['repmps'] + $datos['repusers'] + $datos['revposts'] + $datos['revcomentarios'];
        return $datos;
    }
    
    /**
     * @funcionalidad obtenemos las categorias de la base de datos
     * @return type devolvemos un array con los datos generados
     */
    public function getCategorias(){
        $consulta = db_execute("SELECT cid, c_orden, c_nombre, c_seo, c_img FROM p_categorias ORDER BY c_orden");
        $resultado = resultadoArray($consulta);
        return $resultado;
    }
    
    /**
     * @funcionalidad obtenemos los temas de la base de datos
     * @return string devolvemos un array con los datos generados
     */
    public function getTema(){
        $consulta = db_execute("SELECT * FROM w_temas WHERE tid = ".$this->settings['tema_id']." LIMIT 1");
        $datos = db_execute($consulta,null,'fetch_assoc');
        $datos['t_url'] = $this->settings['url']."/themes/".$datos['t_path'];
        return $datos;
    }
    
    /**
     * @funcionalidad obtenemos las noticias de la base de datos
     * @return type devolvemos un array con los datos generados
     */
    public function getNoticias(){
        $consulta = db_execute("SELECT not_body FROM w_noticias WHERE not_active = \'1\' ORDER BY rand()");
        while($fila = db_execute($consulta,null,'fetch_assoc')){
            $fila['not_body'] = $this->parseBBCode($fila['not_body'],'news');
            $datos[] = $fila;
        }
        return $datos;
    }
    
    /**
     * @funcionalidad comprobamos si extiste la carpeta del instalador
     */
    public function existInstall(){
        $install = PS_ROOT.'/install/';
        if(is_dir($install)){
            return "<div id='message_install'>Debe eliminar la carpeta <strong>install</strong></div>";
        }
    }
    
    /**
     * @funcionalidad comprobamos el nivel de acceso del usuario
     * @global type $psUser obtenemos el usuario
     * @param type $psLevel obtenemos el nivel de acceso
     * @param type $message comprobamos si hemos insertado un mensaje diferente al definido por defecto
     * @return boolean devolvemos un array con el error establecido
     */
    public function setLevel($psLevel,$message=false){
        global $psUser;
        //comprobamos los niveles de acceso
        //acceso a cualquier usuario
        if($psLevel == 0){
            return true;
        }else if($psLevel == 1){
            //acceso solo visitantes
            if($psUser->is_member = 0){
                return true;
            }else{
                if($message){
                    $mensaje = 'Esta p&aacute;gina solo puede ser vista por visitantes.';
                }else{
                    $this->redirect('/');
                }
            }
        }else if($psLevel == 2){
            //acceso solo para miembros
            if($psUser->is_member == 1){
                return true;
            }else{
                if($message){
                    $mensaje = "Para acceder a esta secci&oacute;n debes iniciar sesi&oacute;n.";
                }else{
                    $this->redirect('/login/?r='.$this->currentUrl());
                }
            }
        }else if($psLevel == 3){
            //acceso solo a moderadores
            if($psUser->is_admod || $psUser->permisos['moacp']){
                return true;
            }else{
                if($message){
                    $mensaje = "Este &aacute;rea esta restringida a moderadores";
                }else{
                    $this->redirect('/login/?r='.$this->currentUrl());
                }
            }
        }else if($psLevel == 4){
            if($psUser->is_admod == 1){
                return true;
            }else{
                if($message){
                    $mensaje = "Esta secci&oacute;n solo es visible para administradores";
                }else{
                    $this->redirect('/login/?r='.$this->currentUrl());
                }
            }
        }
        return array('titulo' => 'Error', 'mensaje' => $mensaje);
    }
    
    public function parseBadWords(){
        
    }
    
    /**
     * @funcionalidad cargamos las configuraciones del nucleo del script
     */
    public function psCore(){
        //cargamos las configuraciones
        $this->settings = $this->getSettings();
        $this->settings['domain'] = str_replace('http://','',$this->settings['url']);
        $this->settings['tema'] = $this->getTema();
        $this->settings['default'] = $this->settings['url'].'/themes/default';
        $this->settings['categorias'] = $this->getCategorias();
        $this->settings['images'] = $this->settings['tema']['t_url'].'/images';
        $this->settings['css'] = $this->settings['tema']['t_url'].'/css';
        $this->settings['js'] = $this->settings['tema']['t_url'].'/js';
        //si estamos en la seccion portal o posts cargamos los datos nuevos
        if($_GET['do'] == 'portal' || $_GET['do'] == 'posts'){
            $this->settings['news'] = $this->getNoticias();
        }
        //guardamos el mensaje del instalador y de los post pendientes de moderacion
        $this->settings['instalador'] = $this->existInstall();
        $this->settings['novemods'] = $this->getNovemodera();
    }
}