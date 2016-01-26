<?php 
//si la constante PS_HEADER no esta definida no se accede al script
if (!defined('PS_HEADER')) {
    exit("No se permite el acceso al script");
}

/**
 * Clase realizada para el control de la actividad 
 *
 * @name c.actividad.php
 * @author 
 */

/**
 * ACTIVIDAD
 * POSTS
 * 1 => creo un nuevo post 
 * 2 => agrego a favoritos el post
 * 3 => dejo 10 puntos en el post
 * 4 => recomendo el post
 * 5 => comento el post
 * 6 => voto positivo/negativo un comentario en el post
 * 7 => esa siguiendo el post
 * follows
 * 8 => esta siguiendo a 
 * fotos
 * 9 => subio una nueva foto
 * muro
 * 10 => 
 *		0 => publico en su muro
 * 		1 => comento suu publicacion
 *		2 => publico en el muro de 
 *		3 => comento la publicacion de 
 * 11 => le gusta
 *		0 => su publicacion
 *		1 => su comentario
 *		2 => la publicacion de 
 *		3 => el comentario de
 */
class tsActividad{
	private $actividad = [];

	/**
	 * constructor
	 */
	public static function &getInstance(){
		static $instance;
		if(is_null($instance)){
			$instance = new tsActividad;
		}
		return $instance;
	}
	public function __construct(){
		//no es necesario hacer nada en el constructor
	}

	private function makeActividad(){
		//actividad con formato | id => array(text, link, css_class)
		$this->actividad = [
			//posts
			1 => array('text' => 'Cre&oacute; un nuevo post', 'css' => 'post'),
			2 => array('text' => 'Agreg&oacute; a favoritos el post', 'css' => 'star'),
			
		];
	}


}