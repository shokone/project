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
    //lo primero instanciaremos la clase
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
        $consulta = db_execute("SELECT user_id, user_name FROM u_miembros WHERE user_rango = \'1\' ORDER BY user_id");
        $datos = resultadoArray($consulta);
        return $datos;
    }
    
    /**
     * @funcionalidad obtenemos los datos de creacion del sitio
     * @return type devolvemos los datos 
     */
    public function getStatsIns(){
        $consulta = db_execute("SELECT stats_time_foundation, stats_time_upgrade FROM w_stats WHERE stats_no = \'1\'");
        $datos = db_execute($consulta,null,'rowCount');
        return $datos;
    }
    
    /**
     * @funcionalidad obtenemos datos relacionados con el servidor, bd, version php y gd
     * @return type devolvemos un array con los datos obtenidos
     */
    public function getVersiones(){
        //obtenemos la version de php
        $datos['php'] = PHP_VERSION;
        //obtenemos la version de mysql
        $consulta = (db_execute("SELECT VERSION()"));
        $datos['mysql'] = db_execute($consulta,null,'fetch_num');
        //obtenemos el software del servidor
        $datos['server'] = $_SERVER['SERVER_SOFTWARE'];
        //obtenemos informacion de la biblioteca gd instalada
        $temp = gd_info();
        $datos['gd'] = $temp['GD VERSION'];
        return $datos;
    }
    
    public function guardarConfig(){
        //creamos la variable global para el nucleo
        global $psCore;
        
    }
}
