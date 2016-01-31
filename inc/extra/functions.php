<?php

/* 
 * @requisitos
 * conexion a la base de datos
 * ejecucion de consultas
 * devolver resultados
 * mostrar errores de codigo
 */

//declaramos la variable $conexion
$conexion;
/**
 * realizamos la conexion con el servidor y la base de datos
 */
function conectar(){
    $opc = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");
    $dsn = "mysql:host=".$db['host'].";dbname=".$db['database'];
    try{
        $this->conexion = new PDO($dsn, $db['user'], $db['pass'], $opc);
        $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }catch(PDOException $e){
        exit("No se pudo establecer la conexion con la base de datos, error: ".$e.getMessage());
    }
    return $this->conexion;
}

/**
 * @funcionalidad: realizamos las consultas necesarias
 * @param type $type pasamos por parametro el tipo de consulta en string
 * @param type $sql consulta a realizar
 * @param type $valores valores a modificar
 */
function db_execute($sql, $valores=null,$type = ''){
    //comprobamos si la conexion es nula
    if($this->conexion == null){
        $this->conectar();
    }
    $con=$this->conexion;
    try{
        $consulta = $con->prepare($sql);
        $consulta->execute($valores);
        switch($type){
            case 'rowCount':
                return $consulta->rowCount();
            case 'fetch_num':
                return $consulta->fetch(PDO::FETCH_NUM);
            case 'fetch_assoc':
                return $consulta->fetch(PDO::FETCH_ASSOC);
        }
    }catch(PDOException $e){
        die("Error al realizar la consulta, error: ".$e.getMessage());
    }
    return $consulta;
}

/**
 * @funcionalidad: si el resultado de la consulta devuelve un array
 * llamamos a esta funcion para guardarlo y retornarlo
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
unset($db);