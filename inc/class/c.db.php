<?php

/* 
 * @requisitos
 * conexion a la base de datos
 * ejecucion de consultas
 * devolver resultados
 * mostrar errores de codigo
 */

class db{
    //atributo privado de conexion
    private static $conexion;
    
    /**
     * @funcionalidad conecta con la base de datos usando PDO 
     * da valor al atributo privado y estatico $conexion de la clase
     * en caso de no conectarse abortamos directamente la app y mostramos un mensaje de alerta
     */
    private static function conectar(){
        $db['opc'] = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");
        $db['dsn'] = "mysql:host=localhost;dbname=ivan_dwes";
        $db['user'] = "ivan_ivan";
        $db['pass'] = "infenlaces";
        try{
            $conexion = new PDO($dsn, $user, $pass, $opc);
            $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Abortamos la aplicación, fallo al conectar a la base de datos, $e->getMessage())");
        }
        self::$conexion = $conexion;
    }
}

