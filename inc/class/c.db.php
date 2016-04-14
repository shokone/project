<?php
//comprobamos si hemos declarado la contante PS_HEADER
if(!defined('PS_HEADER')){
    exit("No se permite el acceso al script");
}
/**
 * clase psDb
 * clase destinada al control de la base de datos
 *
 * @name c.db.php
 * @author Iván Martínez Tutor
 */
class psDb{
    //declaramos la variable $conexion
    private $conexion;
    protected $db;

    /**
     * @funcionalidad instanciamos la clase y la guardamos en una variable estática
     * @staticvar psDb $instancia instancia de la clase
     * @return \psDb devolvemos una instancia de la clase
     */
    public static function &getInstance($db){
        static $instancia;
        if(is_null($instancia)){
            $instancia = new psDb($db);
        }
        return $instancia;
    }

    /**
     * @funcionalidad constructor de la base de datos
     * cargaremos los datos necesarios para cargar la base de datos
     */
    public function __construct($db) {
        $this->db['host']=$db['host'];
        $this->db['database'] = $db['database'];
        $this->db['user'] = $db['user'];
        $this->db['pass'] = $db['pass'];
    }
    /**
     * realizamos la conexion con el servidor y la base de datos
     */
    function conectar(){
        $opc = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");
        $dsn = "mysql:host=".$this->db['host'].";dbname=".$this->db['database'];
        try{
            $this->conexion = new PDO($dsn, $this->db['user'], $this->db['pass'], $opc);
            $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }catch(PDOException $e){
            exit("No se pudo establecer la conexion con la base de datos, error: ".$e->getMessage());
        }
        return $this->conexion;
    }

    /**
     * @funcionalidad: realizamos las consultas necesarias
     * @param type $sql consulta a realizar
     * @param type $valores valores a modificar
     * @param type $type pasamos por parametro el tipo de consulta en string
     */
    function db_execute($sql, $valores=null,$type = ''){
        //comprobamos si la conexion es nula
        if($this->conexion == null){
            $this->conectar();
        }
        $con=$this->conexion;
        try{
            $consulta = $con->prepare($sql);
            if(!is_null($valores)){
                foreach($valores as $key => $valor){
                    $consulta->bindParam($key,$valor);
                }
            }
            //si nuestro proveedor de bases de datos tiene limitada la ejecución lanzamos esto para limpiar memoria y evitar este problema
            $consulta->closeCursor();
            $consulta->execute();
            switch($type){
                case 'rowCount':
                    return $consulta->rowCount();
                case 'fetch_num':
                    return $consulta->fetch(PDO::FETCH_NUM);
                case 'fetch_assoc':
                    return $consulta->fetch(PDO::FETCH_ASSOC);
            }
        }catch(PDOException $e){
            die("Error al realizar la consulta, error: ".$e->getMessage());
        }
        return $consulta;
    }

    /**
     * @funcionalidad obtenemos el último id autoincrementativo insertado en la base de datos
     * @return type devolvemos el id
     */
    function getLastInsertId(){
        return PDO::lastInsertId();
    }

    /**
     * @funcionalidad: si el resultado de la consulta devuelve un array
     * llamamos a esta funcion para guardarlo y retornarlo
     * Lo utilizaremos en momentos que tengamos claves enteras y strings en el mismo array
     * @param type $consulta resultado de la consulta pasado por parametro
     * @return type devolvemos un array con los datos obtenidos en la consulta
     */
    function resultadoArray($consulta){
        $array = [];
        if($consulta->fetch()){
            while($f = $consulta->fetch()){
                $array[]=$f;
            }
        }
        return $array;
    }

    //eliminamos la variable $db por seguridad
    //unset($db);
}

