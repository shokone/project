<?php
//si la constante PS_HEADER no esta definida no se accede al script
if (!defined('PS_HEADER')) {
    exit("No se permite el acceso al script");
}

//incluimos el archivo principal de smarty Smarty.class.php
require(PS_ROOT."/inc/smarty/Smarty.class.php");

/**
 * clase psSmarty
 * clase creada para el control de smarty
 * 
 * @name c.smarty.php
 * @author Iván Martínez Tutor
 */
class psSmarty extends Smarty{
    //asignamos variables
    //variable principal de smarty
    var $_tpl_var = [];
    //variable de smarty para valores no multiples
    var $_tpl_var_no_multiple = true;
    
    public function psSmarty(){
        //creamos la variable global para el nucleo psCore
        global $psCore;
        //damos a smarty las rutas de los directorios principales
        $this->template_dir = PS_ROOT."/themes/".PS_TEMA."/templates";
        $this->compile_dir = PS_ROOT."/cache";
    }
    
    public static function &getInstance(){
        static $instancia;
        if(is_null($instancia)){
            $instancia = new psSmarty();
        }
        return $instancia;
    }
}
