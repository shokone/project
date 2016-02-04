<?php
//si la constante PS_HEADER no esta definida no se accede al script
if (!defined('PS_HEADER')) {
    exit("No se permite el acceso al script");
}

/**
 * Clase realizada para el control de la administración
 *
 * @name c.actividad.php
 * @author 
 */
class psAdmin{
    /**
     * @funcionalidad instanciamos la clase y la guardamos en una variable estática
     * @staticvar psAdmin $instancia instancia de la clase
     * @return \psAdmin devolvemos una instancia de la clase
     */
    public static function &getInstance(){
        static $instancia;
        if(is_null($instancia)){
            $instancia = new psAdmin();
        }
        return $instancia;
    }
    
    /**
     * @funcionalidad obtenemos la lista de administradores de la pagina
     * @return type devolvemos un array con los datos
     */
    public function getAdmins(){
        global $psDb;
        $consulta = $psDb->db_execute("SELECT user_id, user_name FROM u_miembros WHERE user_rango = \'1\' ORDER BY user_id");
        $datos = $psDb->resultadoArray($consulta);
        return $datos;
    }
    
    /**
     * @funcionalidad obtenemos los datos de creacion del sitio
     * @return type devolvemos los datos 
     */
    public function getStatsIns(){
        global $psDb;
        $consulta = $psDb->db_execute("SELECT stats_time_foundation, stats_time_upgrade FROM w_stats WHERE stats_no = \'1\'", null, 'rowCount');
        return $consulta;
    }
    
    /**
     * @funcionalidad obtenemos datos relacionados con el servidor, bd, version php y gd
     * @return type devolvemos un array con los datos obtenidos
     */
    public function getVersiones(){
        global $psDb;
        //obtenemos la version de php
        $datos['php'] = PHP_VERSION;
        //obtenemos la version de mysql
        $consulta = "SELECT VERSION()";
        $datos['mysql'] = $psDb->db_execute($consulta,null,'fetch_num');
        //obtenemos el software del servidor
        $datos['server'] = $_SERVER['SERVER_SOFTWARE'];
        //obtenemos informacion de la biblioteca gd instalada
        $temp = gd_info();
        $datos['gd'] = $temp['GD VERSION'];
        return $datos;
    }
    
    public function guardarConfig(){
        //creamos la variable global para el nucleo
        global $psCore, $psDb;
        //guardamos los valores para realizar después la consulta
        $valores = [
            'titulo' => filter_input(INPUT_POST,'titulo'),
            'slogan' => filter_input(INPUT_POST,'slogan'),
            'url' => filter_input(INPUT_POST,'url'),
            'offline' => empty($_POST['offline']) ? 0 : 1,
            'offline_message' => filter_input(INPUT_POST,'offline_message'),
            'edad' => filter_input(INPUT_POST,'edad'),
            'active' => filter_input(INPUT_POST,'active'),
            'sess_ip' => empty($_POST['sess_ip']) ? 0 : 1,
            'count_guests' => filter_input(INPUT_POST,'count_guests'),
            'reg_active' => empty($_POST['reg_active']) ? 0 : 1,
            'reg_activate' => empty($_POST['reg_activate']) ? 0 : 1,
            'met_welcome' => filter_input(INPUT_POST,'met_welcome'),
            'message_welcome' => filter_input(INPUT_POST,'message_welcome'),
            'fotos_private' => empty($_POST['fotos_private']) ? 0 : 1,
            'hits_guest' => empty($_POST['hits_guest']) ? 0 : 1,
            'keep_points' => empty($_POST['keep_points']) ? 0 : 1,
            'allow_points' => filter_input(INPUT_POST,'allow_points'),
            'see_mod' => empty($_POST['see_mod']) ? 0 : 1,
            'stats_cache' => filter_input(INPUT_POST,'stats_cache'),
            'desapprove_post' => empty($_POST['desapprove_post']) ? 0 : 1,
            'firma' => empty($_POST['firma']) ? 0 : 1,
            'upload' => empty($_POST['upload']) ? 0 : 1,
            'portal' => empty($_POST['portal']) ? 0 : 1,
            'live' => empty($_POST['live']) ? 0 : 1,
            'max_nots' => filter_input(INPUT_POST,'max_nots'),
            'max_acts' => filter_input(INPUT_POST,'max_acts'),
            'max_posts' => filter_input(INPUT_POST,'max_posts'),
            'max_com' => filter_input(INPUT_POST,'max_com'),
            'sump' => empty($_POST['sump']),
            'newr' => empty($_POST['newr']),
        ];
        
        //una vez cargados todos los datos actualizamos la base de datos
        $consulta = "UPDATE w_configuracion SET titulo = :titulo, slogan = :slogan, url = :url, c_last_active = :active, c_allow_sess_ip = :sess_ip, c_count_guests = :count_guests, c_reg_active = :reg_active, c_reg_activate = :reg_activate, c_met_welcome = :met_welcome, c_message_welcome = :message_welcome, c_fotos_private = :fotos_private, c_hits_guest = :hits_guest, c_keep_points = :keep_points, c_allow_points = :allow_points, c_see_mod = :see_mod, c_stats_cache = :stats_cache, c_desapprove_post = :desapprove_post, c_allow_edad = :edad, c_max_posts = :max_posts, c_max_com = :max_com, c_mas_nots = :max_nots, c_max_acts = :max_acts, c_allow_sump = :sump, c_newr_type = :newr, c_allow_firma = :firma, c_allow_upload = :upload, c_allow_portal = :portal, c_allow_live = :live, offline = :offline, offline_message = :offline_message WHERE script_id = \'1\'";
        if($psDb->db_execute($consulta,$valores)){
            return true;
        }else{
            exit("Error al ejecutar la consulta en la base de datos.");
        }
    }
}
