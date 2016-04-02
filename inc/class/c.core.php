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
    //declaramos las variables de la clase
    protected $settings; //configuraciones del sitio
    protected $consultas;//consultas del sitio

    /**
     * @funcionalidad instanciamos la clase y la guardamos en una variable estática
     * @staticvar psCore $instancia instancia de la clase
     * @return \psCore devolvemos una instancia de la clase
     */
    public static function &getInstance(){
        static $instancia;
        if(is_null($instancia)){
            $instancia = new psCore();
        }
        return $instancia;
    }

    /**
     * @funcionalidad cargamos las configuraciones del nucleo del script
     */
    public function psCore(){
        //cargamos las configuraciones
        $this->settings = $this->getSettings();
        echo "seting cargado<br>";
        $this->settings['domain'] = str_replace('http://','',$this->settings['url']);
        echo "dominio cargado<br>";
        $this->settings['tema'] = $this->getTema();
        echo "tema cargado<br>";
        $this->settings['default'] = $this->settings['url'].'/themes/default';
        $this->settings['categorias'] = $this->getCategorias();
        echo "cat cargado<br>";
        $this->settings['images'] = $this->settings['tema']['t_url'].'/images';
        $this->settings['css'] = $this->settings['tema']['t_url'].'/css';
        $this->settings['js'] = $this->settings['tema']['t_url'].'/js';
        //si estamos en la seccion portal o posts cargamos los datos nuevos
        if($_GET['do'] == 'portal' || $_GET['do'] == 'posts'){
            $this->settings['news'] = $this->getNoticias();
            echo "noti cargado<br>";
        }
        echo "completado";
        //guardamos el mensaje del instalador y de los post pendientes de moderacion
        $this->settings['instalador'] = $this->existInstall();
        echo "existe la instalacion<br>";
        $this->settings['novemods'] = $this->getNovemodera();
        echo "novedades moderacion<br>";
    }

    /**
     * @funcionalidad cargamos los datos de los ajustes guardados en la base de datos
     * @return type devolvemos el array de datos generado
     */
    public function getSettings(){
        global $psDb;
        $consulta = "SELECT * FROM w_configuracion";
        return $psDb->db_execute($consulta, null, 'fetch_assoc');
    }

    function badWords($word, $type = false){
        global $psDb;
        /*************************************************************/
/**************************  FALTAN COSAS HAY QUE TERMINARLO *******************************/
        /***************************************************************/
        $consulta = "SELECT word, swop, method, type FROM w_badwords :type";
        $valores = array(
            'type' =>
        );
    }

    /**
     * @funcionalidad comprobamos que elementos se encuentran en moderacion
     * realizamos una suma del total de datos (post, comentarios, users, fotos)
     * @return type devolvemos un array con los datos de moderacion generados
     */
    public function getNovemodera(){
        global $psDb;
        $consulta = "SELECT (SELECT count(post_id) FROM p_posts WHERE post_status = 3) as revposts, (SELECT count(cid) FROM p_comentarios WHERE c_status = 1) as revcomentarios, (SELECT count(DISTINCT obj_id) FROM w_denuncias WHERE d_type = 1) as repposts, (SELECT count(DISTINCT obj_id) FROM w_denuncias WHERE d_type = 2) as repmps, (SELECT count(DISTINCT obj_id) FROM w_denuncias WHERE d_type = 3) as repusers, (SELECT count(DISTINCT obj_id) FROM w_denuncias WHERE d_type = 4) as repfotos, (SELECT count(susp_id) FROM u_suspension) as supusers, (SELECT count(post_id) FROM p_posts WHERE post_status = 2) as pospapelera, (SELECT count(foto_id) FROM f_fotos WHERE f_status = 2) as fospapelera";
        $datos = $psDb->db_execute($consulta,null,'fetch_assoc');
        $datos['total'] = $datos['repposts'] + $datos['repfotos'] + $datos['repmps'] + $datos['repusers'] + $datos['revposts'] + $datos['revcomentarios'];
        return $datos;
    }

    /**
     * @funcionalidad obtenemos las categorias de la base de datos
     * @return type devolvemos un array con los datos generados
     */
    public function getCategorias(){
        global $psDb;
        $consulta = $psDb->db_execute("SELECT cid, c_orden, c_nombre, c_seo, c_img FROM p_categorias ORDER BY c_orden");
        $resultado = $psDb->resultadoArray($consulta);
        return $resultado;
    }

    /**
     * @funcionalidad obtenemos los temas de la base de datos
     * @return string devolvemos un array con los datos generados
     */
    public function getTema(){
        global $psDb;
        $valores = [
            'tema_id' => $this->settings['tema_id']
        ];
        $datos = $psDb->db_execute("SELECT * FROM w_temas WHERE tid = :tema_id LIMIT 1", $valores, 'fetch_assoc');
        $datos['t_url'] = $this->settings['url']."/themes/".$datos['t_path'];
        return $datos;
    }

    /**
     * @funcionalidad obtenemos las noticias de la base de datos
     * @return type devolvemos un array con los datos generados
     */
    public function getNoticias(){
        global $psDb;
        $consulta = $psDb->db_execute("SELECT not_body FROM w_noticias WHERE not_active = \'1\' ORDER BY rand()", null, 'fetch_assoc');
        while($fila = $consulta){
            $fila['not_body'] = $fila['not_body'].'news';
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
     * @funcionalidad obtenemos el dominio de nuestro script
     * @return string devolvemos el nombre del dominio
     */
    public function getDomain(){
        $dominio = explode('/',str_replace('http://','',$this->settings['url']));
        if(is_array($dominio)){
            $dominio = explode('.',$dominio[0]);
        }else{
            $dominio = explode('.',$dominio);
        }
        $nuevo = count($dominio);
        $dominio = $dominio[$nuevo - 2].'.'.$dominio[$nuevo - 1];
        return $dominio;
    }

    /**
     * @funcionalidad obtenemos los datos de la url actual
     * @return type devolvemos el valor de la url actual
     */
    public function currentUrl(){
        $domain = $_SERVER['HTTP_POST'];
        $path = $_SERVER['REQUEST_URI'];
        $querystring = $_SERVER['QUERY_STRING'];
        $currentUrl = "http://".$domain.$path;
        $currentUrl = urlencode($currentUrl);
        return $currentUrl;
    }

    /**
     * @funcionalidad obtendremos la ruta del directorio al que queremos redireccionar
     * @param type $psDir pasamos por parametro la ruta
     */
    public function redirectTo($psDir){
        $dir = urldecode($psDir);
        header("Location: $dir");
        exit();
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

    /**
     * @funcionalidad creamos el servicio json para guardar o cargar datos
     * @param type $datos pasamos los datos por parametro
     * @param type $type pasamos el tipo de accion a realizar
     * @return type devolvemos los datos codificados en json
     */
    public function setJson($datos, $type="encode"){
        //incluimos el archivo JSON.php
        require_once(PS_EXTRA.'JSON.php');
        //creamos el servicio JSON
        $json = new Services_JSON;
        if($type == "encode"){
            return $json->encode($datos);
        }else if($type == "decode"){
            return $json->decode($datos);
        }
    }

    /**
     * @funcionalidad comprobamos y validamos el string obtenido para ser válido en una url
     * @param [type]  $string [description] string a validar
     * @param boolean $max    [description] devolvemos el string validado
     */
    function setSeo($string, $maxConversion = false){
        //cambiamos las letras con acento y la ñ
        $acento = array('á', 'é', 'í', 'ó', 'ú', 'ñ');
        $valido = array('a', 'e', 'i', 'o', 'u', 'n');
        //comprobamos texto y validamos para la url
        $string = str_replace($acento, $valido, $string);
        $string = trim($string);
        //comprobamos con expresiones regulares
        //sustituimos cualquier carácter disinto de letras mayúsculas y minúsculas y numeros por -
        $string = trim(preg_replace('/[^ A-Za-z0-9_]/', '-', $string));
        //eliminamos los espacios restantes y los sustituimos por -
        $string = preg_replace('/[ \t\n\r]+/', '-', $string);
        $string = str_replace(' ', '-', $string);
        $string = preg_replace('/[ -]+/', '-', $string);
        //si $maxConversion = true
        //realizamos mas validaciones
        if($maxConversion){
            $string = str_replace('-', '', $string);
            $string = strtolower($string);
        }
        return $string;
    }

    /**
     * @funcionalidad actualizamos el limite de paginas
     * @param type $psLimit limite de paginas
     * @param type $start primera pagina
     * @param type $psMax ultima pagina
     * @return type devolvemos un string con comienzo, final
     */
    public function setPagLimite($psLimit,$start=false,$psMax=0){
        if($start == false){
            $psStart = empty($_GET['page']) ? 0 : (int) (($_GET['page'] - 1) * $psLimit);
        }else{
            $psStart = $_GET['s'];
            $continua = $this->setMax($psLimit, $psMax);
            if($continua == true){
                $psStart = 0;
            }
        }
        return $psStart.','.$psLimit;
    }

    /**
     * @funcionalidad establecemos el numero maximo de paginas para no excederlo
     * @param type $psLimit pasamos el limite actual
     * @param type $psMax pasamos el maximo actual
     * @return boolean si es correcto devolvemos true
     */
    public function setMax($psLimit, $psMax){
        //establecemos un maximo para no exceder el numero de paginas
        $var = ($_GET['page'] * $psLimit);
        if($psMax < $var){
            $var2 = $var - $psLimit;
            if($psMax < $var2){
                return true;
            }
        }
        return false;
    }

    /**
     * @funcionalidad obtener las paginas
     * @param type $psTotal total de paginas
     * @param type $psLimit limite de paginas
     * @return type devolvemos un array con los valores de las paginas
     */
    public function getPages($psTotal, $psLimit){
        $psPages = ceil($psTotal / $psLimit);
        //obtenemos la pagina
        $psPage = empty($_GET['page']) ? 1 : $_GET['page'];
        //guardamos las paginas en un array
        $page['current'] = $psPage;
        $page['pages'] = $psPages;
        $page['section'] = $psPages + 1;
        $page['prev'] = $psPage - 1;
        $page['next'] = $psPage + 1;
        $page['max'] = $this->setMax($psLimit, $psTotal);
        //devolvemos el array page
        return $page;
    }
}
