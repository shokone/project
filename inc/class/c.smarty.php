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
    //variable principal de smarty para asignar valores
    var $_tpl_hooks;
    //variable de smarty para valores no multiples
    var $_tpl_hooks_no_multi = true;
    
    function psSmarty(){
        //creamos la variable global para el nucleo psCore
        global $psCore;
        //damos a smarty las rutas de los directorios principales
        $this->template_dir = PS_ROOT."/themes/".PS_TEMA."/templates/";
        $this->compile_dir = PS_ROOT."/cache/";
        $this->config_dir = PS_ROOT.'/inc/php/';
        $this->plugins_dir = PS_ROOT."/inc/smarty/plugins";
        $this->template_cb = array('url' => $psCore->settings['url'], 'title' => $psCore->settings['titulo']);
        $this->_tpl_hooks = array();
    }
    
    public static function &getInstance(){
        static $instancia;
        if(is_null($instancia)){
            $instancia = new psSmarty();
        }
        return $instancia;
    }

    function assign_hook($hook, $include){
        if(!isset($this->_tpl_hooks[$hook])){
          $this->_tpl_hooks[$hook] = array();
        }
        if($this->_tpl_hooks_no_multi && in_array($include, $this->_tpl_hooks[$hook])){
          return;
        }
        $this->_tpl_hooks[$hook][] = $include;
    }
}
