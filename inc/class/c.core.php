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
            echo "noti cargado<br>";
        }
        //guardamos el mensaje del instalador y de los post pendientes de moderacion
        $this->settings['instalador'] = $this->existInstall();
        $this->settings['novemods'] = $this->getNovemodera();
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

    /**
     * @funcionalidad obtenemos los datos de una palabra para sustituirla
     * @param  [type]   $word -> obtenemos la palabra
     * @param  [type]   $type -> obtenemos un valor booleano para comprobar o no el tipo
     * @return type devolvemos el array de datos generado
     */
    function badWords($word, $type = false){
        global $psDb;
        $consulta = "SELECT word, swop, method, type FROM w_badwords :type";
        $valores = array(
            'type' => ($type ? '' : 'WHERE type = :0'),
            '0' => 0,
        );
        $query = $psDb->db_execute($consulta, $valores);
        foreach($query as $badWord){
            $search = empty($badWord['method']) ? $badWord['word'] : $badword['word']." ";
            $replace = $badWord['type'] == 1 ? '<img class="bwtype" title="'.$badword['word'].'" src="'.$badWord['swop'].'"/>' : $badWord['swop'].' ';
            $subject = $word;
            $word = str_ireplace($search, $replace, $subject)
        }
        return $word;
    }

    /**
     * @funcionalidad comprobamos que elementos se encuentran en moderacion
     * realizamos una suma del total de datos (post, comentarios, users, fotos)
     * @return type devolvemos un array con los datos de moderacion generados
     */
    public function getNovedadesMod(){
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
    public function getTheme(){
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
     * @funcionalidad convertimos los datos para actualizar en la db
     * @param type $datos obtenemos por parametro un array con los datos 
     * @param type $prefijo obtenemos el prefijo para colocar delante de cada dato
     * @return type devolvemos un array con los datos generados
     */
    function getDatos($datos, $prefijo = ''){
        //obtenemos los datos de las keys del array
        $keys = array_keys($datos);
        //obtenemos los valores del array
        $valores = array_values($datos);
        foreach($dato as $key => $valor){
            //añadimos el prefijo y rellenamos un array con cada dato
            $values[$key] = $prefijo.$keys[$key] . " = " . $valor . ", ";
        }
        return $values;
    }

    /**
     * @funcionalidad obtenemos la ip del usuario
     * @return type devolvemos la ip obtenida o unknow en caso de no poder obtenerla
     */
    function getIp(){
        //obtenemos la ip del usuario a través de diferentes peticiones
        $ip = $_SERVER['HTTP_CLIENT_IP'];
        if(!$ip && strcasecmp($ip, 'unknown')){
            return $ip;
        }
        $ip = $_SERVER["HTTP_X_FORWARDED_FOR"]; 
        if($ip &&  !​​strcasecmp($ip, 'unknown')){
            return $ip;
        }
        $ip = $_SERVER["REMOTE_ADDR"]; 
        si ( $ tmp &&  ! ​​Strcasecmp ( $ tmp ,  'unknown')){
            return $ip;
        }

        return 'unknown'; 
    }

    /**
     * @funcionalidad obtenemos el contenido de una url mediante curl o file
     * @param type $url obtenemos la url a través de la cual queremos obtener el contenido
     * @return type devolvemos los datos obtenidos
     */
    function getUrlContent($url){
        //comprobamos si podemos hacerlo mediante curl
        if(function_exists('curl_init')){
            $useragent = 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.31 (KHTML, like Gecko) Chrome/26.0.1410.43 Safari/537.31';
            //abrimos la conexion y obtenemos un resultado
            $curl_init = curl_init();
            curl_setopt($curl_init, CURLOPT_USERAGENT, $useragent);
            curl_setopt($curl_init, CURLOPT_URL, $url);
            curl_setopt($curl_init, CURLOPT_TIMEOUT, 90);
            curl_setopt($curl_init, CURLOPT_RETURNTRANSFER, 1);
            $resultado = curl_exec($curl_init);
            curl_close($curl_init);
        }else{//si no podemos con curl lo haremos mediante file
            $resultado = @file_get_contents($url);
        }
        return $resultado;
    }

    /*****************************************************************************************/
    /************************************ PAGINAS ********************************************/
    /*****************************************************************************************/

    /**
     * @funcionalidad actualizamos el limite de paginas
     * @param type $limit limite de paginas
     * @param type $start si su valor es true empezamos por la primera página
     * @param type $max ultima página
     * @return type devolvemos un string con comienzo, final
     */
    public function setPagLimite($limit,$start=false,$max=0){
        if($start == false){
            $start = empty($_GET['page']) ? 0 : (int) (($_GET['page'] - 1) * $limit);
        }else{
            $start = $_GET['start'];
            $continua = $this->setMax($limit, $max);
            if($continua == true){
                $start = 0;
            }
        }
        return $start.','.$limit;
    }

    /**
     * @funcionalidad establecemos el numero maximo de paginas para no excederlo
     * @param type $limit pasamos el limite actual
     * @param type $max pasamos el maximo actual
     * @return boolean si es correcto devolvemos true
     */
    public function setMax($limit, $max){
        //establecemos un maximo para no exceder el numero de paginas
        $var = ($_GET['page'] * $limit);
        if($max < $var){
            $var2 = $var - $limit;
            if($max < $var2){
                return true;
            }
        }
        return false;
    }

    /**
     * @funcionalidad obtener las paginas
     * @param type $total total de paginas
     * @param type $limit limite de paginas
     * @return type devolvemos un array con los valores de las paginas
     */
    function getPages($total, $limit){
        $psPages = ceil($total / $limit);
        //obtenemos la pagina
        $psPage = empty($_GET['page']) ? 1 : $_GET['page'];
        //guardamos las paginas en un array
        $page['current'] = $psPage;
        $page['pages'] = $psPages;
        $page['section'] = $psPages + 1;
        $page['prev'] = $psPage - 1;
        $page['next'] = $psPage + 1;
        $page['max'] = $this->setMax($limit, $total);
        //devolvemos el array page
        return $page;
    }

    /**
     * @funcionalidad obtener la paginacion y los elementos totales de cada página
     * @param type $total total de paginas
     * @param type $max_per_page limite de elementos por página
     * @return type devolvemos un array con los valores de las paginas
     */
    function getPagination($total, $max_per_page = 15){
        //obtenemos la pagina actual
        $page = empty($_GET['page']) ? 1 : (int)$_GET['page'];
        //obtenemos el numero total de pagina redondeando hacia el entero mas alto
        $total_pages = ceil($total / $max_per_page);
        //obtenemos la página siguiente
        $next = $page +1;
        $pages['next'] = ($page <= $total_pages) ? $next : 0;
        //obtenemos la página anterior
        $prev = $page - 1;
        $pages['prev'] = ($page > 0) ? $prev : 0;
        //obtenemos el limite para la consulta en la db
        $pages['limit'] = ($prev * $max_per_page).', '.$max_per_page;
        //obtenemosel total
        $pages['total'] = $total;
        return $pages;
    }

    /**
     * @funcionalidad obtenemos la paginación completa y la mostramos en pantalla
     * @param type $url url de la página
     * @param type $start página de inicio
     * @param type $max máximo de páginas
     * @param type $max_per_page máximo de elementos por página
     * @param type $change_start si = false, cambiaremos la url por la de la página actual
     * @return type 
     */
    function inicioPages($url, &$start, $max, $max_per_page, $change_start = false){
        global $psDb;
        //quitamos &start= para obtener la url
        $url = explode('&start=', $url);
        $url = $url[0];
        //comprobamos el valor de start 
        if($$start < 0){//si es menor que 0 lo igualamos a 0
            $start = 0;
        }else if($start >= $max){//si start vale más que el máximo de páginas 
            if(($max % $max_per_page) == 0){//comprobamos el resto entre el maximo de páginas y el de elementos por página
                $start = max(0, $max_per_page);//start vale el valor máximo
            }else{
                $start = $max % $max_per_page;//start vale el resto de la misma operación anterior
            }
        }else{
            //obtenemos el valor máximo entre 0 y start - el resto de start y max de elementos por página
            $start = max(0, ($start - ($start % $max_per_page)));
        }
        $nextPage = 2;
        //obtenemos el enlace
        $link = '<a class="menuPages" href="' . ($change_start ? $url : strtr($url, array('%' => '%%')) . '&start=%d') . '">%s</a>';
        //mostramos la primera página > 1 < ... 6 7 (8) 9 10 ... 15
        if($start > $max_per_page * 2){
            $inicio = sprintf($link, 0, '1');
        }else{
            $inicio = '';
        }
        //mostramos el hueco hasta la siguiente página 1 > ... < 6 7 
        if($start > $max_per_page * ($nextPage + 1)){
            $inicio = '<b> ... </b>';
        }
        //mostramos las páginas anteriores a la seleccionada 1 ... > 6 7 < (8) 9 10 ... 15
        for($a = $nextPage; $a >= 1; $a--){
            if($start >= $max_per_page * $a){
                $nStart = $start - $max_per_page * $a;
                $inicio = sprintf($link, $nStart, $nStart / $max_per_page + 1);
            }
        }
        //mostramos la página actual
        if(!($start < 0)){
            $inicio .= '[<b>' . ($start / $max_per_page + 1) . '</b>] ';
        }else{
            $inicio .= sprintf($link, $start, $start / $max_per_page + 1);
        }
        //mostramos las páginas siguientes a la actual
        $max_start = (int)($max - 1) / $max_per_page + 1;
        for($a = 1; $a <= $nextPage; $a++){
            if(($start + $max_per_page * $a) <= $max_start){
                $max_start = $start + $max_per_page * $a;
                $inicio .= sprintf($link, $max_start, $max_start / $max_per_page + 1);
            }
        }
        //mostramos el hueco hasta la última página
        if($start + $max_per_page * ($nextPage + 1) < $max_start){
            $inicio .= '<b> ... </b>';
        }
        //mostramos el número de la última página
        if($start + $max_per_page * $nextPage < $max_start){
            $inicio .= sprintf($link, $max_start, $max_start / $max_per_page + 1);
        }
        return $inicio;
    }
}
