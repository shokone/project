<?php
//si la constante PS_HEADER no esta definida no se accede al script
if (!defined('PS_HEADER')) {
    exit("No se permite el acceso al script");
}

/**
 * Clase realizada para el control las subidas de imágenes a la página
 *
 * @name c.upload.php
 * @author Iván Martínez Tutor
 */
class psUpload{
    //declaramos las variables de esta clase
    $type = 1;//tipo de subida de archivo
    $allow_types = array('png','gif','jpeg');//tipos de archivo permitidos
    $max = 1048576;//tamaño máximo de archivo permitido
    $auxiliar = 0; //variable auxiliar
    $furl = ''; // URL
    $fsize = array(); // TAMA�O DEL ARCHIVO REMOTO
    $isize = array('w' => 570, 'h' => 450);//tamaño de la imagen
    $iscale = false;//escalar imagen

    /**
     * @funcionalidad instanciamos la clase y la guardamos en una variable estática
     * @staticvar psUpload $instancia instancia de la clase
     * @return \psUpload devolvemos una instancia de la clase
     */
    public static function &getInstance(){
        static $instancia;
        if(is_null($instancia)){
            $instancia = new psUpload();
        }
        return $instancia;
    }

    function newUpload(){}

    function uploadFile(){}

    function uploadUrl(){}

    function validFile(){}

    function sendFile(){}

    function copyFile(){}

    function sendFile(){}

    function copyFile(){}

    function deleteFile(){}

    function createImage(){}

    function uploadImage(){}

    function getImageUrl(){}

}