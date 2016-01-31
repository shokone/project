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
class psActividad{
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
    /*public function __construct(){
        //no es necesario hacer nada en el constructor
    }*/

    /**
     * @funcionalidad creamos el texto a mostrar para cada actividad que se realice en la web
     */
    private function crearActividad(){
        //actividad con formato | id => array(text, css_class)
        $this->actividad = [
            //posts
            1 => ['text' => 'Cre&oacute; un nuevo post', 'css' => 'post'],
            2 => ['text' => 'Agreg&oacute; a favoritos el post', 'css' => 'fav_post'],
            3 => ['text' => 'Dej&oacute; puntos en el post', 'css' => 'puntos_post'],
            4 => ['text' => 'Recomend&oacute; el post', 'css' => 'share_post'],
            5 => ['text' => 'Coment&oacute; el post', 'css' => 'comentario_post'],
            6 => ['text' => 'Vot&oacute; un comentario en el post', 'css' => 'com_voto'],
            7 => ['text' => 'Est&aacute; siguiendo el post', 'css' => 'seguir_post'],
            //seguidores
            8 => ['text' => 'Est&aacute siguiendo a ', 'css' => 'seguidor'],
            //fotos
            9 => ['text' => 'Subi&oacute; una nueva foto', 'css' => 'foto'],
            //muro del usuario
            10 => [
                0 => ['text' => 'Public&oacute; en su ', 'link' => 'muro', 'css' => 'estado_muro'],
                1 => ['text' => 'Coment&oacute; en su ', 'link' => 'publicaci&oacute;n', 'css' => 'muro_comentario'],
                2 => ['text' => 'Public&oacute; en el muro de ', 'css' => 'post_muro'],
                3 => ['text' => 'Coment&oacute; en la publicaci&oacute;n de', 'css' => 'pub_muro_comentario']
            ],
            //likes
            11 => [
                'text' => 'Le gusta',
                'css' => 'user_like',
                0 => ['text' => 'su', 'link' => 'publicaci&oacute;n'],
                1 => ['text' => 'su comentario'],
                2 => ['text' => 'la publicaci&oacute;n de'],
                3 => ['text' => 'el comentario']
            ]
        ];
    }

    /**
     * @funcionalidad obtenemos el tiempo que ha pasado desde el valor indicado
     * @param type $fecha pasamos la fecha de la actividad
     * @return string devolvemos un valor en función del tiempo de diferencia
     */
    private function obtenerFecha($fecha){
        $tiempo = time() - $fecha;
        $dias = rount($tiempo / 86400);
        //obtenemos valor dependiendo de los dias
        if($dias < 1){
            return 'hoy';
        }else if($dias < 2){
            return 'ayer';
        }else if($dias <=7){
            return 'semana';
        }else if($dias <= 30){
            return 'mes';
        }else{
            return 'historico';
        }
    }
    
    /**
     * @funcionalidad obtenemos la consulta a realizar dependiendo de la actividad que se realice
     * @param type $datos pasamos el tipo de actividad
     * @return type devolvemos la consulta a realizar
     */
    private function obtenerConsulta($datos){
        switch($datos['ac_type']){
            case 1:case 2:case 3:case 4:case 5:case 6:case 7:
                return "SELECT p.post_id, p.post_title, c.c_seo FROM p_posts AS p LEFT JOIN p_categorias AS c ON p.post_category = c.cid WHERE p.post_id = \'".$datos['obj_uno']."\' LIMIT 1";
            case 8:
                //el usuario está siguiendo a
                return "SELECT user_id AS avatar, user_name FROM u_miembros WHERE user_id = \'".$datos['obj_uno']."\' LIMIT 1";
            case 9:
                //el usuario subió una foto
                return "SELECT f.foto_id, f.f_title, u.user_name FROM f_fotos AS f LEFT JOIN u_miembros AS u ON f.f_user = u.user_id WHERE f.foto_id = \'".$datos['obj_uno']."\' LIMIT 1";
            case 10:case 11:
                //publicaciones en el muro y likes
                if($datos['obj_dos'] == 0 || $datos['obj_dos'] == 2){
                    return "SELECT p.pub_id, u.user_name FROM u_muro AS p LEFT JOIN u_miembros AS u ON p.p_user = u.user_id WHERE p.pub_id =\'".$datos['obj_uno']."\' LIMIT 1";    
                }else{
                    return "SELECT c.pub_id, c.c_body, u.user_name FROM u_muro_comentarios AS c LEFT JOIN u_muro AS p ON c.pub_id = p.pub_id LEFT JOIN u_miembros AS u ON p.p_user = u.user_id WHERE cid = \'".$datos['obj_uno']."\' LIMIT 1";
                }
        }
    }
    
    /**
     * @funcionalidad obtenemos las notificaciones e insertamos las nuevas
     * @global type $psUser variable global de la clase psUser
     * @global type $psCore variable global de la clase psCore
     * @param type $type tipo de actividad
     * @param type $var1 obtenemos el primer objeto (normalmente sera un id)
     * @param type $var2 obtenemos el segundo objeto
     * @return boolean devolvemos un booleano con el resultado de la consulta
     */
    public function setActividad($type, $var1, $var2 = 0){
        //creamos las variables globales
        global $psUser, $psCore;
        //creamos la variable local de tiempo
        $actividad_fecha = time();
        //buscamos las actividades en la base de datos
        $consulta = db_execute("SELECT ac_id FROM u_actividad WHERE user_ id = \'".$psUser->uid."\' ORDER BY ac_date DESC");
        $resultado = resultadoArray($consulta);
        
        //obtenemos el total de notificaciones de actividad en curso
        $acTotal = count($resultado);
        //obtenemos el id de la notificación de actividad más vieja
        $lastNot = $resultado[$acTotal -1]['ac_id'];
        
        //comprobamos si hemos llegado al límite de notificaciones de actividad
        //si es así borramos la última
        if($acTotal >= $psCore->settings['c_max_acts']){
            db_execute("DELETE FROM u_actividad WHERE ac_id = ".$lastNot);
        }
        //insertamos los datos
        $consulta2 = db_execute("INSERT INTO u_actividad (user_id, obj_uno, obj_dos, ac_type, ac_date) VALUES(".$psUser->uid.", ".$var1.", ".$var2.", ".$type.", ".$actividad_fecha.")");
        //comprobamos si la consulta se ha ejecutado correctamente
        if($consulta2){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * @funcionalidad obtenemos la frase a mostrar en la pagina dependiendo de la accion realizada
     * @global type $psCore obtenemos los datos del nucleo a traves de su variable global
     * @param type $datos obtenemos los datos de la actividad
     * @return string devolvemos la frase obtenida
     */
    private function obtenerFrase($datos){
        global $psCore;
        $type = $datos['ac_type'];
        $url = $psCore->settings['url'];
        $frase['ac_id'] = $datos['ac_id'];
        $frase['css'] = $this->actividad[$type]['css'];
        $frase['fecha'] = $datos['ac_date'];
        $frase['usuario'] = $datos['usuario'];
        $frase['user_id'] = $datos['user_id'];
        //dependiendo del tipo obtendremos una u otra frase
        switch($type){
            case 1:case 2:case 4:case 7:
                $frase['text'] = $this->actividad[$type]['text'];
                $frase['link'] = $url."/posts/".$datos['c_seo']."/".$datos['post_id']."/".$datos['post_title'].".html";
                $frase['title'] = $datos['post_title'];
                break;
            case 3:case 5:case 6://
                if($type == 3){//dejo puntos en el post
                    $etext = $datos['obj_dos'];
                }else if($type == 5){//comento el post
                    if($datos['obj_dos'] == 0){
                        $etext = "";
                    }else{//voto el post
                        $etext = ($datos['obj_dos']+1)." veces";
                    } 
                }else{
                    if($datos['obj_dos'] == 0){
                        $etext = "negativo";
                    }else{
                        $etext = "positivo";
                    }
                }
                //obtenemos el texto
                $frase['text'] = $this->actividad[$type][0]."<strong>{$etext}</strong>".$this->actividad[$type]['text'][1];
                //obtenemos la url
                $frase['link'] = $url."/posts/".$datos['c_seo']."/".$datos['post_id']."/".$datos['post_title'].".html";
                $frase['title'] = $datos['post_title'];
                //obtenemos el estilo
                if($type == 6){
                    $frase['css'] = 'com_voto'.$etext;
                }else{
                    $frase['css'] = $frase['css'];
                }
                break;
            case 8://subio un nuevo avatar
                //avatares
                $avatar1 = '<img src="'.$psCore->settings['url'].'/files/avatar/'.$datos['user_id'].'x16.jpg"/>';
                $avatar2 = '<img src="'.$psCore->settings['url'].'/files/avatar/'.$datos['avatar'].'x16.jpg"/>';
                //obtenemos la frase
                $frase['text'] = $avatar1." ".$this->actividad[$type]['text']." ".$avatar2;
                $frase['link'] = $url."/perfil/".$datos['user_name'];
                $frase['title'] = $datos['user_name'];
                $frase['css'] = '';
                break;
            case 9://subio una foto nueva
                $frase['text'] = $this->actividad[$type]['text'];
                $frase['link'] = $url."/fotos/".$datos['user_name']."/".$datos['foto_id']."/".$datos['f_title'].".html";
                $frase['title'] = $datos['f_title'];
                break;
            case 10://tipo de seccion
                $typeSeccion = $datos['obj_dos'];
                $texto_enlace = $this->actividad[$type][$typeSeccion]['link'];
                //obtenemos la frase
                $frase['text'] = $this->actividad[$type][$typeSeccion]['text'];
                $frase['link'] = $url."/perfil/".$datos['user_name']."/".$datos['pub_id'];
                if(empty($texto_enlace)){
                    $frase['title'] = $datos['user_name'];
                }else{
                    $frase['title'] = $texto_enlace;
                }
                $frase['css'] = $this->actividad[$type][$typeSeccion]['css'];
                break;
            case 11:
                //likes
                $typeSeccion = $datos['obj_dos'];
                $texto_enlace = $this->actividad[$type][$typeSeccion]['link'];
                //obtenemos la frase
                $frase['text'] = $this->actividad[$type]['text']." ".$this->actividad[$type][$typeSeccion]['text'];
                $frase['link'] = $url."/perfil/".$datos['user_name']."?pid=".$datos['pub_id'];
                if($datos['obj_dos'] == 0 || $datos['obj_dos'] == 2){
                    if(empty($texto_enlace)){
                        $frase['title'] = $datos['user_name'];
                    }else{
                        if(strlen($datos['c_body']) > 35){
                            $fin = "...";
                        }else{
                            $fin = "";
                        }
                        $frase['title'] = substr($datos['c_body'],0,30).$fin;
                    }
                }
                break;
        }
        return $frase;
    }
    
    /**
     * @funcionalidad obtenemos los datos y formamos la estructura de la actividad
     * @param type $datos pasamos los datos por parametro
     * @return type devolvemos un array con la actividad montada
     */
    private function montarActividad($datos){
        $actividad = [
            'total' => count($datos),
            'datos' => [
                'hoy' => [
                    'title' => 'Hoy',
                    'datos' => []
                ],
                'ayer' => [
                    'title' => 'Ayer',
                    'datos' => []
                ],
                'semana' => [
                    'title' => 'D&iacute;as anteriores',
                    'datos' => []
                ],
                'mes' => [
                    'title' => 'Semanas anteriores',
                    'datos' => []
                ],
                'historico' => [
                    'title' => 'Actividad m&aacute;s antigua',
                    'datos' => []
                ]
            ]
        ];
        //creamos una consulta para cada valor obtenido
        foreach($datos as $indice => $valor){
            //creamos la consulta
            $consulta1 = $this->obtenerConsulta($valor);
            //consultamos con la base de datos
            $consulta2 = db_execute($consulta1);
            $resultado = db_execute($consulta2, null, 'fetch_assoc');
            //comprobamos
            if(!empty($resultado)){
                //agregamos los datos al array original
                $resultado = array_merge($resultado,$valor);
                //obtenemos la frase de la actividad
                $frase = $this->obtenerFrase($resultado);
                //obtenemos la fecha de la actividad
                $fecha = $this->obtenerFecha($valor['ac_date']);
                //colocamos la actividad
                $actividad['datos'][$fecha]['datos'][] = $frase;
            }
        }
        return $actividad;
    }
    
    /**
     * @funcionalidad obtenemos la actividad a partir del usuario
     * @param type $u_id obtenemos el id del usuario
     * @param type $type obtenemos el tipo de actividad
     * @param type $comienzo obtenemos la fecha desde la que queremos empezar
     * @return type devolvemos la actividad montada
     */
    public function getActividad($u_id, $type = 0, $comienzo = 0){
        //primero creamos la actividad
        $this->crearActividad();
        //obtenemos el tipo de actividad
        if($type != 0){
            $type = " AND ac_type = \'".$type."\'";
        }else{
            $type = "";
        }
        //realizamos la consulta en la base de datos
        $consulta = db_execute("SELECT ac_id, user_id, obj_uno, obj_dos, ac_type, ac_date FROM u_actividad WHERE user_id = ".$u_id." ".$type." ORDER BY ac_date DESC LIMIT ".$comienzo.", 25");
        $resultado = resultadoArray($consulta);
        
        //montamos y devolvemos la actividad
        return $this->montarActividad($resultado);
    }
    
    
}