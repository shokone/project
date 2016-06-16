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
    var $type = 1;//tipo de subida de archivo
    var $allow_types = array('png','gif','jpeg');//tipos de archivo permitidos
    var $max = 1048576;//tamaño máximo de archivo permitido
    var $auxiliar = 0; //variable auxiliar
    var $furl = ''; // url del archivo
    var $fsize = array(); // tamaño del archivo 
    var $isize = array('w' => 570, 'h' => 450);//tamaño de la imagen
    var $iscale = false;//escalar imagen

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

    function newUpload($type = 1){
        $this->type = $type;
        //subimos archivos
        if($this->type == 1){
            foreach($_FILES as $file){
                $return[] = $this->uploadFile($file);
            }
        //subimos desde url
        }elseif($this->type == 2) {
            $return[] = $this->uploadUrl();
        //cortamos la imagen antes de subirla
        } elseif($this->type == 3){
            if(empty($this->furl)) {
                foreach($_FILES as $file){
                    $return = $this->uploadFile($file);
                }
                if(empty($return['msg'])) {
                    return array('error' => $return[1]);
                }
            } else {
                $file = array(
                    'name' => substr($this->furl, -4),
                    'type' => 'image/url',
                    'tmp_name' => $this->furl,
                    'error' => 0,
                    'size' => 0
                );
                $return = $this->uploadFile($file, 'url');
                if(empty($return['msg'])) {
                    return array('error' => $return[1]);
                }
            }
        }
        //comprobamos 
        if($this->auxiliar == 0) {
            return array('error' => 'No se ha seleccionado archivo alguno.');
        }else {
            return $return;
        }
    }

    function uploadFile($file, $type = 'file'){
        $error = $this->validFile($file, $type);
        if(!empty($error)){
            return array(0, $error);
        }else{
            $type = explode('/', $file['type']);
            $ext = ($type[1] == 'jpeg' || $type[1] == 'url') ? 'jpg' : $type[1];
            $key = rand(0,1000);
            $newName = 'socialit_'.$key.'.'.$ext;
            if($this->type == 1){
                return array(1, $this->sendFile($file,$newName), $type[1]);
            }else{
                return array('msg' => $this->createImage($file,$newName), 'error' => '', 'key' => $key, 'ext' => $ext);
            }
        }
    }

    function uploadUrl(){
        $error = $this->validFile(null, 'url');
        if(!empty($error)) {
            return array(0, $error);
        }else {
            return array(1, urldecode($this->furl));
        }
    }

    function validFile($file, $type = 'file'){
        if($type == 'file'){
            //comprobamos si el archivo ha sido encontrado
            if(empty($file['name'])) {
                return 'No Found';
            }else {
                $this->auxiliar = $this->auxiliar + 1;
            }
            //
            $type = explode('/',$file['type']);
            if($file['size'] > $this->max) {
                return '#'.$this->auxiliar.' pesa mas de 1 MB.';
            } elseif(!in_array($type[1], $this->allow_types)) {
                return '#'.$this->auxiliar.' no es una imagen.';
            }
        } elseif($type == 'url'){
            $this->fsize = getimagesize($this->furl);
            //tamaño mínimo
            $min_w = 160;
            $min_h = 120;
            //tamaño máximo
            $max_w = 1024;
            $max_h = 1024;
            $this->auxiliar = 1;
            //
            if(empty($this->fsize[0])) {
                return 'La url ingresada no existe o no es una imagen v&aacute;lida.';
            } elseif($this->fsize[0] < $min_w || $this->fsize[1] < $min_h) {
                return 'Tu foto debe tener un tama&ntilde;o superior a 160x120 pixeles.';
            } elseif($this->fsize[0] > $max_w || $this->fsize[1] > $max_h) {
                return 'Tu foto debe tener un tama&ntilde;o menor a 1024x1024 pixeles.';
            }
        }
        return false;
    }

    function sendFile($file, $name){
        $url = $this->createImage($file,$name);
        //subimos el archivo
        $new_img = $this->getImagenUrl($this->uploadImagen($this->setParams($url)));
        //borramos el archivo
        $this->deleteFile($name);
        //devolvemos el archivo
        return $new_img;
    }

    function copyFile($file,$name){
        global $psCore;
        $root = PS_FILES.'uploads/'.$name;
        copy($file['tmp_name'],$root);
        return $psCore->settings['url'].'/files/uploads/'.$name;
    }

    function deleteFile(){}

    function createImage(){}

    function uploadImage(){}

    function getImageUrl(){}

}