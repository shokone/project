<?php
//si la constante PS_HEADER no esta definida no se accede al script
if (!defined('PS_HEADER')) {
    exit("No se permite el acceso al script");
}

/**
 * clase psTops
 * clase creada para el control de los tops
 * 
 * @name c.tops.php
 * @author Iván Martínez Tutor
 */

class psTops{
    
    /**
     * @funcionalidad instanciamos la clase psTops si no se ha 
     * iniciado previamente
     * @staticvar psTops $instance
     * @return \psTops
     */
    public static function &getInstance(){
        static $instance;
        if(is_null($instance)){
            $instance = new psTops();
        }
        return $instance;
    }
    
    /*******************************************************
     * funciones para los tops y las estadisticas del script
     *******************************************************/
    /**
     * @funcionalidad: obtenemos el top de post para incluirlo en el home
     * @return type devolvemos el valor del array $datos
     */
    public function getHomeTopPosts(){
        //hoy
        $datos['hoy'] = $this->getHomeTopPostsConsulta($this->setTime(1));
        //ayer
        $datos['ayer'] = $this->getHomeTopPostsConsulta($this->setTime(2));
        //semana
        $datos['semana'] = $this->getHomeTopPostsConsulta($this->setTime(3));
        //mes
        $datos['mes'] = $this->getHomeTopPostsConsulta($this->setTime(4));
        //historico
        $datos['historico'] = $this->getHomeTopPostsConsulta($this->setTime(5));
        return $datos;
    }
    
    /**
     * @funcionalidad: obtenemos los datos del top de usuarios para el home
     * @return type devolvemos el valor del array $datos
     */
    public function getHomeTopUsers(){
        //hoy
        $datos['hoy'] = $this->getHomeTopUsersConsulta($this->setTime(1));
        //ayer
        $datos['ayer'] = $this->getHomeTopUsersConsulta($this->setTime(2));
        //semana
        $datos['semana'] = $this->getHomeTopUsersConsulta($this->setTime(3));
        //mes
        $datos['mes'] = $this->getHomeTopUsersConsulta($this->setTime(4));
        //historico
        $datos['historico'] = $this->getHomeTopUsersConsulta($this->setTime(5));
        return $datos;
    }
    
    /**
     * @funcionalidad obtenemos los datos de los usuarios para el top
     * @param type $fecha obtenemos la fecha seleccionada
     * @param type $cat obtenemos la categoria seleccionada
     * @return type devolvemos los datos en el array $top
     */
    public function getTopUsers($fecha, $cat){
        //obtenemos la categoria de post escogida
        $datos = $this->setTime($fecha);
        $categoria = empty($cat) ? '' : 'AND post_category = '.$cat;
        //top de usuarios por puntos
        $consulta1 = db_execute("SELECT SUM(p.post_puntos) AS total, u.user_id, u.user_name FROM p_posts AS p LEFT JOIN u_miembros AS u ON p.post_user = u.user_id WHERE p.post_status = 0 AND p.post_date BETWEEN ".$datos['start']." AND ".$datos['end']." ".$categoria." GROUP BY p.post_user ORDER BY total DESC LIMIT 10");
        $top['puntos'] = resultadoArray($consulta1);
        //top de usuarios por seguidores
        $consulta2 = db_execute("SELECT COUNT(f.follow_id) AS total, u.user_id, u.user_name FROM u_follows AS f LEFT JOIN u_miembros AS u ON f.f_id = u.user_id WHERE f.f_type = 1 AND f.f_date BEWEEN ".$datos['start']." AND ".$datos['end']." GROUP BY f.f_id ORDER BY total DESC LIMIT 10");
        $top['seguidores'] = resultadoArray($consulta2);
        //top de usuarios por medallas
        $consulta3 = db_execute("SELECT COUNT(m.medal_for) AS total, u.user_id, u.user_name, wm.medal_id FROM w_medallas_assign AS m LEFT JOIN u_miembros AS u ON m.medal_for = u.user_id LEFT JOIN w_medallas AS wm ON wm.medal_id = m.medal_id WHERE wm.m_type = \'1\' AND m.medal_date BETWEEN ".$datos['start']." AND ".$datos['end']." GROUP BY m.medal_for ORDER BY total DESC LIMIT 10");
        $top['medallas'] = resultadoArray($consulta3);
        return $top;
    }
    
    /**
     * @funcionalidad obtenemos los datos de los post para el top
     * @param type $fecha obtenemos la fecha seleccionada
     * @param type $cat obtenemos la categoria seleccionada
     * @return type devolvemos los datos en el array $datos
     */
    public function getTopPosts($fecha,$cat){
        //puntos de los post
        $datos['puntos'] = $this->getVarsTopPost($fecha, $cat, "puntos");
        //seguidores de los post
        $datos['seguidores'] = $this->getVarsTopPost($fecha, $cat, "seguidores");
        //comentarios de los post
        $datos['comentarios'] = $this->getVarsTopPost($fecha, $cat, "comments");
        //favoritos de los post
        $datos['favoritos'] = $this->getVarsTopPost($fecha, $cat, "favoritos");
        return $datos;
    }
    
    /**
     * @funcionalidad obtenemos el tipo para el top de post
     * @param type $fecha pasamos la fecha por parametro
     * @param type $cat pasamos la categoria por parametro
     * @param type $tipo pasamos el tipo por parametro
     * @return type devolvemos la consulta con los datos pasados
     */
    public function getVarsTopPost($fecha, $cat, $tipo){
        $datos = $this->setTime($fecha);
        if(!empty($cat)){
            $datos['scat'] = "AND c.cid = ".$cat;
        }
        $datos['type'] = "p.post_".$tipo;
        return $this->getTopPostsConsulta($datos);
    }
    
    /**
     * @funcionalidad realizamos la consulta con la bd para el top de post
     * @param type $datos pasamos los datos necesarios para la consulta
     * @return type devolvemos el array $resultado
     */
    public function getTopPostsConsulta($datos){
        $consulta = db_execute("SELECT p.post_id, p.post_category,".$datos['type'].", p.post_puntos, p.post_title, c.c_seo, c.c_img FROM p_posts AS p LEFT JOIN p_categorias AS c ON c.cid = p.post_category WHERE p.post_status = \'0\' AND p.post_date BETWEEN ".$datos['start']." AND ".$datos['end']." ".$datos['scat']." ORDER BY ".$datos['type']." DESC LIMIT 10");
        $resultado = resultadoArray($consulta);
        return $resultado;
    }
    
    /**
     * @funcionalidad realizamos la consulta con la bd para el top de post en el home
     * @param type $datos pasamos los datos necesarios para la consulta
     * @return type devolvemos el array $resultado
     */
    public function getHomeTopPostsConsulta($datos){
        $consulta = db_execute("SELECT p.post_id, p.post_category, p.post_title, p.post_puntos, c.c_seo FROM p_post AS p LEFT JOIN p_categorias AS c ON c.cid = p.post_category WHERE p.post_status = 0 AND p.post_date BETWEEN \'".$datos['datos']."\' AND \'".$datos['end']."\' ORDER BY p.post_puntos DESC LIMIT 15");
        $resultado = resultadoArray($consulta);
        return $resultado;
    }
    
    /**
     * @funcionalidad realizamos la consulta con la bd para el top de usuarios en el home
     * @param type $datos pasamos los datos necesarios para la consulta
     * @return type devolvemos el array $resultado
     */
    public function getHomeTopUsersConsulta($datos){
        $consulta = db_execute("SELECT SUM(p.post_puntos) AS total, u.user_id, u.user_name FROM p_posts AS p LEFT JOIN u_miembros AS u ON p.post_user = u.user_id WHERE p.post_status = 0 AND p.post_date BETWEEN \'".$datos['start']."\' AND ".$datos['end']."\' GROUP BY p.post_user ORDER BY total DESC LIMIT 10");
        $resultado = resultadoArray($consulta);
        return $resultado;
    }
    
    /**
     * @funcionalidad calculamos la fecha escogida 
     * @param type $fecha pasamos por parametro la fecha
     * @return type devolvemos el array $data con los datos de inicio y final de la fecha escogida
     */
    public function setTime($fecha){
        //ahora
        $tiempo = time();
        $dia = (int)date("d",$tiempo);
        $hora = (int)date("G",$tiempo);
        $min = (int)date("i",$tiempo);
        $seg = (int)date("s",$tiempo);
        
        //calculamos la fecha restando horas, minutos y segundos
        $resultado = $this->setSegs($hora,'hor') + $this->setSegs($min, 'min') + $seg;
        
        //realizamos la transformacion
        switch($fecha){
            //calculamos para la fecha de hoy
            case 1:
                $data['start'] = $tiempo - $resultado;
                $data['end'] = $tiempo;
                break;
            //calculamos para la fecha de ayer
            case 2:
                $result2 = $resultado + $this->setSegs(1, 'dia') + $this->setSegs(1, 'hor');
                $data['start'] = $tiempo - $result2;
                $data['end'] = $tiempo - $resultado;
                break;
            //calculamos fecha para la semana
            case 3:
                $result2 = $resultado + $this->setSegs(1, 'sem') + $this->setSegs(1, 'hor');
                $data['start'] = $tiempo - $result2;
                $data['end'] = $tiempo - $resultado;
                break;
            //calculamos fecha para el mes
            case 4:
                $result2 = $resultado + $this->setSegs(1, 'mes') + $this->setSegs(1, 'hor');
                $data['start'] = $tiempo - $result2;
                $data['end'] = $tiempo - $resultado;
                break;
            case 5:
                //calculamos con el historico (todo el tiempo)
                $data['start'] = 0;
                $data['end'] = $tiempo;
                break;
        }
        return $data;
    }
    
    /**
     * @funcionalidad calculamos el tiempo en segundos
     * @param type $tiempo pasamos el valor del tiempo
     * @param type $tipo pasamos el tipo de tiempo en el que lo queremos calcular
     * @return type devolvemos el tiempo calculado en segundos
     */
    public function setSegs($tiempo, $tipo){
        switch($tipo){
            case 'min':
                $segundos = $tiempo * 60;
                break;
            case 'hor':
                $segundos = $tiempo * 3600;
                break;
            case 'dia':
                $segundos = $tiempo * 86400;
                break;
            case 'sem':
                $segundos = $tiempo * 604800;
                break;
            case 'mes':
                $segundos = $tiempo * 2592000;
                break;
        }
        return $segundos;
    }
}

