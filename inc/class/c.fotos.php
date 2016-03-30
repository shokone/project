<?php
//comprobamos si la constante PS_HEADER ha sido declarada, en caso contrario no se puede acceder al script
if(!defined('PS_HEADER')){
  exit('No se permite el acceso directo al script');
}

/**
 * Clase fotos
 * destinada al control de las fotos en el script
 *
 * @name() c.fotos.php
 * @author  Iván Martínez Tutor
 */
class psFotos(){
  /**
   * @funcionalidad comprobamos si la clase ha sido instanciada
   * si no es así creamos un nuevo objeto para la clase psFotos
   * @return [type] [description]
   */
  public static function &getInstance(){
    static $instance;
    if(is_null($instance)){
      $instance = new psFotos();
    }
    return $instance;
  }


}
