<?php
//si la constante PS_HEADER no esta definida no se accede al script
if (!defined('PS_HEADER')) {
    exit("No se permite el acceso al script");
}

/**
 * Clase realizada para el control de la actividad
 *
 * @name c.actividad.php
 * @author Iván Martínez Tutor
 */
class psActividad{
    private $actividad = [];

    /**
     * @funcionalidad instanciar la clase
     * @staticvar psActividad $instance
     * @return \psActividad
     */
    public static function &getInstance(){
        static $instance;
        if(is_null($instance)){
            $instance = new psActividad;
        }
        return $instance;
    }

    /**
     * constructor
     */
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
                $valores = ['obj_uno' => $datos['obj_uno']];
                $array = [
                    0 => "SELECT p.post_id, p.post_title, c.c_seo FROM p_posts AS p LEFT JOIN p_categorias AS c ON p.post_category = c.cid WHERE p.post_id = :obj_uno LIMIT 1",
                    1 => $valores
                ];
                return $array;
            case 8:
                //el usuario está siguiendo a
                $valores = ['obj_uno' => $datos['obj_uno']];
                $array = [
                    0 => "SELECT user_id AS avatar, user_name FROM u_miembros WHERE user_id = :obj_uno LIMIT 1",
                    1 => $valores
                ];
                return $array;
            case 9:
                //el usuario subió una foto
                $valores = ['obj_uno' => $datos['obj_uno']];
                $array = [
                    "SELECT f.foto_id, f.f_title, u.user_name FROM f_fotos AS f LEFT JOIN u_miembros AS u ON f.f_user = u.user_id WHERE f.foto_id = :obj_uno LIMIT 1",
                    $valores
                ];
            case 10:case 11:
                //publicaciones en el muro y likes
                $valores = ['obj_uno' => $datos['obj_uno']];
                if($datos['obj_dos'] == 0 || $datos['obj_dos'] == 2){
                    $array = [
                        "SELECT p.pub_id, u.user_name FROM u_muro AS p LEFT JOIN u_miembros AS u ON p.p_user = u.user_id WHERE p.pub_id = :obj_uno LIMIT 1",
                        $valores
                    ];
                    return $array;
                }else{
                    $array = [
                        "SELECT c.pub_id, c.c_body, u.user_name FROM u_muro_comentarios AS c LEFT JOIN u_muro AS p ON c.pub_id = p.pub_id LEFT JOIN u_miembros AS u ON p.p_user = u.user_id WHERE cid = :obj_uno LIMIT 1",
                        $valores
                    ];
                    return $array;
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
        global $psDb;
        //creamos las variables globales
        global $psUser, $psCore;
        //creamos la variable local de tiempo
        $actividad_fecha = time();
        //buscamos las actividades en la base de datos
        $valores = [
            'user_id' => $psUser->uid
        ];
        $consulta = $psDb->db_execute("SELECT ac_id FROM u_actividad WHERE user_ id = :user_id ORDER BY ac_date DESC",$valores);
        $resultado = $psDb->resultadoArray($consulta);

        //obtenemos el total de notificaciones de actividad en curso
        $acTotal = count($resultado);
        //obtenemos el id de la notificación de actividad más vieja
        $lastNot = $resultado[$acTotal -1]['ac_id'];

        //comprobamos si hemos llegado al límite de notificaciones de actividad
        //si es así borramos la última
        if($acTotal >= $psCore->settings['c_max_acts']){
            $psDb->db_execute("DELETE FROM u_actividad WHERE ac_id = ".$lastNot);
        }
        $valores = [
            'user_id' => $psUser->uid,
            'obj_uno' => $var1,
            'obj_dos' => $var2,
            'ac_type' => $type,
            'ac_date' => $actividad_fecha
        ];
        //insertamos los datos
        $consulta2 = $psDb->db_execute("INSERT INTO u_actividad (user_id, obj_uno, obj_dos, ac_type, ac_date) VALUES(:user_id, :obj_uno, :obj_doc, :ac_type, :ac_date)",$valores);
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
        global $psDb;
        $actividad = [
            'total' => count($datos),
            'datos' => [
                'hoy' => ['title' => 'Hoy','datos' => []],
                'ayer' => ['title' => 'Ayer','datos' => []],
                'semana' => ['title' => 'D&iacute;as anteriores','datos' => []],
                'mes' => ['title' => 'Semanas anteriores','datos' => []],
                'historico' => ['title' => 'Actividad m&aacute;s antigua','datos' => []]
            ]
        ];
        //creamos una consulta para cada valor obtenido
        foreach($datos as $indice => $valor){
            //creamos la consulta
            $consulta1 = $this->obtenerConsulta($valor);
            //consultamos con la base de datos
            $resultado = $psDb->db_execute($consulta1[0], $consulta1[1], 'fetch_assoc');
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
        global $psDb;
        //primero creamos la actividad
        $this->crearActividad();
        //obtenemos el tipo de actividad
        if($type != 0){
            $type2 = "AND ac_type = :type2";
            $valores = [
                'user_id' => $u_id,
                'type' => $type2,
                'type2' => $type,
                'comienzo' => $comienzo
            ];
        }else{
            $type = "";
            $valores = [
                'user_id' => $u_id,
                'type' => $type,
                'comienzo' => $comienzo
            ];
        }
        //realizamos la consulta en la base de datos
        $consulta = $psDb->db_execute("SELECT ac_id, user_id, obj_uno, obj_dos, ac_type, ac_date FROM u_actividad WHERE user_id = :user_id :type ORDER BY ac_date DESC LIMIT :comienzo, 25", $valores);
        $resultado = $psDb->resultadoArray($consulta);

        //montamos y devolvemos la actividad
        return $this->montarActividad($resultado);
    }

    /**
     * @funcionalidad esee metodo se encargará de eliminar la noticia de actividad seleccionada
     * @global type $psUser variable global de la clase psUser
     * @return string devolvemos un string dependiendo si la consulta se ha realizado correctamente
     */
    public function borrarActividad(){
        global $psUser,$psDb;
        $acid = filter_input(INPUT_POST,'acid');
        //ejecutamos la consulta
        $valores = [
            'ac_id' => (intval($acid))
        ];
        $consulta = "SELECT user_id FROM u_actividad WHERE ac_id = :ac_id";
        $resultado = $psDb->db_execute($consulta,$valores,'fetch_assoc');
        //comprobamos que es correcto
        if($datos['user_id'] == $psUser->uid){
            if($consulta){
                return "1: Actividad eliminada";
            }
        }
        return "0: La actividad seleccionada no puede eliminarse.";
    }

    /**
     * @funcionalidad obtenemos la actividad relacionada con el seguimiento de usuarios
     * @global type $psUser variable global de la clase psUser
     * @param type $com obtenemos por parametro a partir de que actividad empezamos
     * @return string devolvemos la actividad creada
     */
    public function obtenerActividadSeguida($com = 0){
        global $psUser,$psDb;
        $this->crearActividad();
        //mostraremos solo las últimas 90 actividades
        if($com > 90){
            return array('total' => -1);
        }
        $valores = ['user_id' => $psUser->uid];
        $consulta = $psDb->db_execute("SELECT f_id FROM u_follows WHERE f_user = :user_id AND f_type = 1",$valores);
        $resultado = $psDb->resultadoArray($consulta);

        //ordenamos el array de datos obtenido
        foreach($resultado as $indice => $valor){
            $seguidores[] = "'".$valor['f_id']."'";
        }
        //agregado en lista de seguidores
        $seguidores[] = $psUser->uid;
        //obtenemos un string mediante la conversión del array
        $seguidores = implode(', '.$seguidores);
        //consultamos a la bd por las últimas publicaciones
        $valores2 = ['seguidores' => $seguidores, 'start' => $com];
        $consulta2 = $psDb->db_execute("SELECT ua.*, u.user_name AS usuario FROM u_actividad AS ua LEFT JOIN u_miembros AS u ON ua.user_id = u.user_id WHERE ua.user_id IN :seguidores ORDER BY ua.ac_date DESC LIMIT :start, 25", $valores2);
        $resultado2 = $psDb->resultadoArray($consulta2);

        //montamos la actividad resultante
        if(empty($resultado2)){
            return "No hay actividad o no est&aacute;s siguiendo a ning&uacute;n usuario.";
        }
        $actividad = $this->montarActividad($resultado2);
        return $actividad;
    }
}
