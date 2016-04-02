<?php
//si la constante PS_HEADER no esta definida no se accede al script
if (!defined('PS_HEADER')) {
    exit("No se permite el acceso al script");
}

/**
 * Clase realizada para el control de la administración
 *
 * @name c.actividad.php
 * @author Iván Martínez Tutor
 */
class psAdmin{
    /**
     * @funcionalidad instanciamos la clase y la guardamos en una variable estática
     * @staticvar psAdmin $instancia instancia de la clase
     * @return \psAdmin devolvemos una instancia de la clase
     */
    public static function &getInstance(){
        static $instancia;
        if(is_null($instancia)){
            $instancia = new psAdmin();
        }
        return $instancia;
    }

    /**
     * @funcionalidad obtenemos la lista de administradores de la pagina
     * @return type devolvemos un array con los datos
     */
    public function getAdmins(){
        global $psDb;
        $consulta = $psDb->db_execute("SELECT user_id, user_name FROM u_miembros WHERE user_rango = \'1\' ORDER BY user_id");
        $datos = $psDb->resultadoArray($consulta);
        return $datos;
    }

    /**
     * @funcionalidad obtenemos los datos de creacion del sitio
     * @return type devolvemos los datos
     */
    public function getStatsIns(){
        global $psDb;
        $consulta = $psDb->db_execute("SELECT stats_time_foundation, stats_time_upgrade FROM w_stats WHERE stats_no = \'1\'", null, 'rowCount');
        return $consulta;
    }

    /**
     * @funcionalidad obtenemos datos relacionados con el servidor, bd, version php y gd
     * @return type devolvemos un array con los datos obtenidos
     */
    public function getVersiones(){
        global $psDb;
        //obtenemos la version de php
        $datos['php'] = PHP_VERSION;
        //obtenemos la version de mysql
        $consulta = "SELECT VERSION()";
        $datos['mysql'] = $psDb->db_execute($consulta,null,'fetch_num');
        //obtenemos el software del servidor
        $datos['server'] = $_SERVER['SERVER_SOFTWARE'];
        //obtenemos informacion de la gd (Graphics Library) instalada
        $temp = gd_info();
        $datos['gd'] = $temp['GD VERSION'];
        return $datos;
    }

    public function guardarConfig(){
        //creamos la variable global para el nucleo
        global $psCore, $psDb;
        //guardamos los valores para realizar después la consulta
        $valores = array(
            'titulo' => filter_input(INPUT_POST,'titulo'),
            'slogan' => filter_input(INPUT_POST,'slogan'),
            'url' => filter_input(INPUT_POST,'url'),
            'offline' => empty($_POST['offline']) ? 0 : 1,
            'offline_message' => filter_input(INPUT_POST,'offline_message'),
            'edad' => filter_input(INPUT_POST,'edad'),
            'active' => filter_input(INPUT_POST,'active'),
            'sess_ip' => empty($_POST['sess_ip']) ? 0 : 1,
            'count_guests' => filter_input(INPUT_POST,'count_guests'),
            'reg_active' => empty($_POST['reg_active']) ? 0 : 1,
            'reg_activate' => empty($_POST['reg_activate']) ? 0 : 1,
            'met_welcome' => filter_input(INPUT_POST,'met_welcome'),
            'message_welcome' => filter_input(INPUT_POST,'message_welcome'),
            'fotos_private' => empty($_POST['fotos_private']) ? 0 : 1,
            'hits_guest' => empty($_POST['hits_guest']) ? 0 : 1,
            'keep_points' => empty($_POST['keep_points']) ? 0 : 1,
            'allow_points' => filter_input(INPUT_POST,'allow_points'),
            'see_mod' => empty($_POST['see_mod']) ? 0 : 1,
            'stats_cache' => filter_input(INPUT_POST,'stats_cache'),
            'desapprove_post' => empty($_POST['desapprove_post']) ? 0 : 1,
            'firma' => empty($_POST['firma']) ? 0 : 1,
            'upload' => empty($_POST['upload']) ? 0 : 1,
            'portal' => empty($_POST['portal']) ? 0 : 1,
            'live' => empty($_POST['live']) ? 0 : 1,
            'max_nots' => filter_input(INPUT_POST,'max_nots'),
            'max_acts' => filter_input(INPUT_POST,'max_acts'),
            'max_posts' => filter_input(INPUT_POST,'max_posts'),
            'max_com' => filter_input(INPUT_POST,'max_com'),
            'sump' => empty($_POST['sump']),
            'newr' => empty($_POST['newr']),
        );

        //una vez cargados todos los datos actualizamos la base de datos
        $consulta = "UPDATE w_configuracion SET titulo = :titulo, slogan = :slogan, url = :url, c_last_active = :active, c_allow_sess_ip = :sess_ip, c_count_guests = :count_guests, c_reg_active = :reg_active, c_reg_activate = :reg_activate, c_met_welcome = :met_welcome, c_message_welcome = :message_welcome, c_fotos_private = :fotos_private, c_hits_guest = :hits_guest, c_keep_points = :keep_points, c_allow_points = :allow_points, c_see_mod = :see_mod, c_stats_cache = :stats_cache, c_desapprove_post = :desapprove_post, c_allow_edad = :edad, c_max_posts = :max_posts, c_max_com = :max_com, c_mas_nots = :max_nots, c_max_acts = :max_acts, c_allow_sump = :sump, c_newr_type = :newr, c_allow_firma = :firma, c_allow_upload = :upload, c_allow_portal = :portal, c_allow_live = :live, offline = :offline, offline_message = :offline_message WHERE script_id = \'1\'";
        if($psDb->db_execute($consulta,$valores)){
            return true;
        }else{
            exit("Error al ejecutar la consulta en la base de datos.");
        }
    }

    /*****************************************************************************************/
    /******************************** NOTICIAS ***********************************************/
    /*****************************************************************************************/

    /**
     * @funcionalidad obtenemos los datos de la noticia de la db
     * @return [type] [description] devolvemos un array asociativo con los datos
     */
    function getNoticia(){
        global $psCore, $psDb;
        //obtenemos el id de la noticia seleccionada
        $nid = $_GET['nid'];
        $consulta = "SELECT not_id, not_body, not_date, not_active FROM w_noticias WHERE not_id = :nid";
        $valores = array(
            'nid' => (int)$nid,
        );
        $datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
        return $datos;
    }

    /**
     * @funcionalidad obtenemos el listado de noticias de la db
     * @return [type] [description] devolvemos un array con los datos generados
     */
    function getNoticias(){
        global $psDb;
        //obtenemos todas las noticias de la base de datos
        $consulta = "SELECT u.user_id, u.user_name, n.* FROM w_noticias AS n LEFT JOIN u_miembros AS u ON n.not_autor = u.user_id WHERE n.not_id > \'0\' ORDER BY n.not_id DESC";
        return $psDb->resultadoArray($consulta);
    }

    /**
     * @funcionalidad creamos una nueva noticia
     * @return [type] [description] devolvemos un valor booleano según el resultado de la inserción en la db
     */
    function newNoticia(){
        global $psDb, $psCore, $psUser;
        //obtenemos el texto de la noticia y comprobamos si hay alguna palabra censurada
        $not_body = $psCore->badWords(substr($_POST['not_body'], 0, 190));
        //comprobamos si se activa la noticia
        $not_active = empty($_POST['not_active']) ? 0 : 1;
        if(!empty($not_body)){
            $consulta = "INSERT INTO w_noticias (not_body, not_autor, not_date, not_active) VALUES (:not_body, :not_autor, :not_date, :not_active)";
            $valores = array(
                'not_body' => $not_body,
                'not_autor' => $psUser->uid,
                'not_date' => time(),
                'not_active' => $not_active,
            );
            //si se inserta correctamente devolvemos true
            if($psDb->db_execute($consulta, $valores)){
                return true;
            }
            return false;
        }
    }

    /**
     * @funcionalidad editamos una noticia ya existente
     * @return [type] [description] devolvemos un valor booleano con el resultado de la actualización en la db
     */
    function editNoticia(){
        global $psDb, $psCore, $psUser;
        //obtenemos el id de la noticia
        $nid = intval($_GET['nid']);
        //obtenemos el texto de la noticia y comprobamos palabras censuradas
        $not_body = $psCore->badWords(substr($_POST['not_body'], 0, 190));
        //comprobamos si activamos la noticia o no
        $not_active = empty($_POST['not_active']) ? 0 : 1;
        if(!empty($not_body)){
            $consulta ="UPDATE w_noticias SET not_autor = :not_autor, not_body = :not_body, not_active = :not_active, WHERE not_id = :not_id";
            $valores = array(
                'not_autor' => $psUser->uid,
                'not_body' => $not_body,
                'not_active' => $not_active,
                'not_id' => $nid,
            );
            if($psDb->db_execute($consulta, $valores)){
                return true;
            }
            return false;
        }
    }

    /**
     * @funcionalidad eliminamos la noticia seleccionada
     * @return [type] [description]
     */
    function delNoticia(){
        global $psDb;
        //obtenemos el id de la noticia seleccionada
        $nid = $_GET['nid'];
        $consulta = "";
        $valores = array();
        if(!$psDb->db_execute($consulta, $valores, 'rowCount')){
            return 'El id correspondiente a la noticia seleccionada no existe';
        }
        $consulta2 = "DELETE FROM w_noticias WHERE not_id = :nid";
        $valores2 = array(
            'nid' => $nid,
        );
        $psDb->db_execute($consulta2, $valores2);
    }

    /*****************************************************************************************/
    /******************************** TEMAS **************************************************/
    /*****************************************************************************************/

    /**
     * @funcionalidad obtenemos los datos del tema seleccionado
     * @return [type] [description] devolvemos un array asociativo con los datos del tema
     */
    function getTheme(){
        global $psDb;
        $tid = $_GET['tid'];
        $consulta = "SELECT * FROM w_themes WHERE tid = :tid";
        $valores = array(
            'tid' => $tid,
        );
        return $psDb->db_execute($consulta, $valores, 'fetch_assoc');
    }

    /**
     * @funcionalidad obtenemos los datos de los temas
     * @return [type] [description]
     */
    function getThemes(){
        global $psDb;
        //realizamos la consulta oportuna
        $consulta = "SELECT * FROM w_themes";
        return $psDb->resultadoArray($consulta);
    }

    /**
     * @funcionalidad añadimos un nuevo tema al portal
     * @return [type] [description]
     */
    function newTheme(){
        global $psDb;
        $t_path = $_POST['path'];
        //obtenemos la ruta de instalación
        include("../../themes/" . $t_path . "/install.php");
        //obtenemos el valor de theme del archivo install.php del theme
        if(empty(theme)){
            return 'Revisa que la carpeta del tema sea correcta';
        }
        foreach($theme as $key => $valor)¨{
            if(empty($valor)){
                return 'El archivo de instalaci&oacute;n del tema no es correcto. Comprueba que no haya errores de c&oacute;digo';
            }else{
                $datos[$key] = $valor;
            }
        }
        //añadimos los datos del nuevo tema a la base de datos
        $consulta = "INSERT INTO w_themes (t_name, t_url, t_path, t_copy) VALUES (:t_name, :t_url, :t_path, :t_copy)";
        $valores = array(
            't_name' => $datos['nombre'],
            't_url' => $datos['url'],
            't_path' => $t_path,
            't_copy' => $datos['copy'],
        );
        if($psDb->db_execute($consulta, $valores)){
            return 1;
        }else{
            return 'Ocurri&oacute; un error durante la instalaci&ocaute;n del tema. Comprueba que el archivo de instalación no tenga errores de c&oacute;digo';
        }
    }

    /**
     * @funcionalidad eliminamos el tema seleccionado
     * @return [type] [description] devolvemos un balor booleano si ha tenido éxito o no
     */
    function delTheme(){
        global $psDb;
        $theme = $this->getTheme();
        if(!empty($theme['tid'])){
            $consulta = "DELETE FROM w_themes WHERE tid = :tid";
            $valores = array(
                'tid' => (int)$theme['tid'],
            );
            if($psDb->db_execute($consulta, $valores)){
                return true;
            }else{
                return false;
            }
    }

    /**
     * @funcionalidad cambiamos el tema a mostrar en el portal
     * @return [type] [description]
     */
    function changeTheme(){
        global $psDb, $psSmarty;
        $theme = $this->getTheme();
        if(!empty($theme['tid'])){
            $consulta = "UPDATE w_configuracion SET tema_id = :tid WHERE tscript_id = \'1\'";
            $valores = array(
                'tid' => (int)$theme['id'],
            );
            $psDb->db_execute($consulta, $valores);
            //llamamaos a la función de smarty para que nos compile el fichero
            $compile = $psSmarty->compile_dir;
            //abrimos el gestor de directorios
            $fichero = opendir($compile);
            //leemos cada archivo
            while(($new = readdir($fichero)) != false){
                if($new != '.' && $new != '..'){
                    //si se cumple la condición borramos el archivo
                    unlink($compile . DIRECTORY_SEPARATOR . $new);
                }
            }
            //cerramos el gestor de directorios
            closedir($fichero);
            return true;
        }else{
            return false;
        }
    }

    /**
     * @funcionalidad actualizamos los datos del tema
     * @return [type] [description] devolvemos un booleano con el resultado de la consulta
     */
    function saveTheme(){
        global $psDb;
        $tid = $_GET['tid'];
        $datos = array(
            'url' => $_POST['url'],
            'path' => $_POST['path'],
        );
        $consulta = "UPDATE w_themes SET t_url = :t_url, t_path = :t_path WHERE tid = :tid";
        $valores = array(
            't_url' => $datos['url'],
            't_path' => $datos['path'],
            'tid' => $tid,
        );
        if($psDb->db_execute($consulta, $valores)){
            return true;
        }else{
            return false;
        }
    }

    /**
     * @funcionalidad guardamos los datos de los ads de publicidad
     * @return [type] [description]
     */
    function savePublicidad(){
        global $psDb;
        //actualizamos los datos en la db
        $consulta = "UPDATE w_configuracion SET ads_300 = :ad300, ads_468 = :ad468, ads_160 = :ad160, ads_728 = :ad728, ads_search = :adSearch";
        $valores = array(
            'ad300' => $_POST['ad300'],
            'ad468' => $_POST['ad468'],
            'ad160' => $_POST['ad160'],
            'ad728' => $_POST['ad728'],
            'adSearch' => $_POST['adSearch'],
        );
        if($psDb->db_execute($consulta, $valores)){
            return true;
        }
    }

    /*****************************************************************************************/
    /******************************** CATEGORIAS *********************************************/
    /*****************************************************************************************/

    /**
     * @funcionalidad obtenemos las categorias de la db
     * @return [type] [description] devolvemos un array asociativo con los datos de las categorias
     */
    function getCategorias(){
        global $psDb;
        $consulta = "SELECT cid, c_orden, c_nombre, c_seo, c_img FROM p_categorias WHERE cid = :cid";
        $valores = array(
            'cid' => intval($_GET['cid']),
        );
        return $psDb->db_execute($consulta, $valores, 'fetch_assoc');
    }

    /**
     * @funcionalidad obtenemos el orden de las categorias y lo guardamos en la db
     * @return [type] [description]
     */
    function ordenCategorias(){
        global $psDb;
        $cid = $_POST['catid'];
        $cats = $_POST[$cid];
        $orden = 1;
        foreach($cats as $key => $id){
            if(!empty($id)){
                $consulta = "UPDATE p_categorias SET c_orden = :orden WHERE cid = :id";
                $valores = array(
                    'orden' => (int)$orden,
                    'id' => $id,
                );
                $psDb->db_execute($consulta, $valores);
                $orden++;
            }
        }
    }

    /**
     * @funcionalidad guardamos los cambios realizados en las categorias en la db
     * @return [type] [description] devolvemos un valor booleano con el resultado de la consulta
     */
    function guardarCategorias(){
        global $psDb, $psCore;
        $cid = $_POST['cid'];
        $c_nombre = $psCore->badWords($_POST['c_nombre']);
        $c_img = $_POST['c_img'];
        $consulta = "UPDATE p_categorias SET c_nombre = :nombre, c_seo = :seo, c_img = :img WHERE cid = :cid";
        $valores = array(
            'nombre' => $c_nombre,
            'seo' => $psCore->setSeo($c_nombre, true),
            'img' => $c_img,
            'cid' => (int)$cid,
        );
        if($psDb->db_execute($consulta, $valores)){
            return true;
        }
    }

    /**
     * @funcionalidad cambiamos el id de la categoría en los post al reordenarlas
     * @return [type] [description]
     */
    function moverCategoria(){
        global $psDb;
        $consulta = "UPDATE p_posts SET post_category = :newcid WHERE post_category = :oldcid";
        $valores = array(
            'newcid' => (int)$_POST['newcid'],
            'oldcid' => (int)$_POST['oldcid'],
        );
        if($psDb->db_execute($consulta, $valores)){
            return true;
        }
    }

    /**
     * @funcionalidad añadimos una nueva categoría a la db
     * @return [type] [description]
     */
    function newCategoria(){
        global $psDb, $psCore;
        $c_nombre = $psCore->badWords($_POST['c_nombre']);
        $c_img = $_POST['c_img'];
        //realizamos la consulta para obtener el id
        $consulta = "SELECT COUNT(cid) AS total FROM p_categorias";
        $orden = $psDb->db_execute($consulta, null, 'fetch_assoc');
        $orden = $orden['total'] + 1;
        //realizamos la inserción en la db
        $consulta2 = "INSERT INTO p_categorias (c_orden, c_nombre, c_seo, c_img) VALUES (:orden, :nombre, :seo, :img)";
        $valores2 = array(
            'orden' => $orden,
            'nombre' => $c_nombre,
            'seo' => $psCore->setSeo($c_nombre, true),
            'img' => $c_img,
        );
        if($psDb->db_execute($consulta2, $valores2)){
            return true;
        }
    }

    /**
     * @funcionalidad eliminamos la categoría
     * @return [type] [description] devolvemos true si todo es correcto o un error en caso de haberlo
     */
    function delCategoria(){
        global $psDb;
        //obtenemos el id de la categoria
        $cid = $_GET['cid'];
        //obtenemos el nuevo id que daremos a los post
        $new_cid = $_POST['ncid'];
        if(!empty($cid) && $ncid > 0){
            $consulta = "UPDATE p_posts SET post_category = :ncid WHERE post_category = :cid";
            $valores = array(
                'ncid' => $new_cid,
                'cid' => $cid,
            );
            if($psDb->db_execute($consulta, $valores)){
                $consulta2 = "DELETE FROM p_categorias WHERE cid = :cid";
                $valores = array('cid' => $cid,);
                if($psDb->db_execute($consulta2, $valores2)){
                    return 1;
                }
            }else{
                return 'Lo sentimos hubo un error al intentar actualizar la nueva categor&iacute;a de los post.';
            }
        }else{
            return 'Antes de eliminar una categor&iacute;a debes elegir en que categor&iacute;a se incluir&aacute;n los post.';
        }
    }

    /*****************************************************************************************/
    /************************************ RANGOS *********************************************/
    /*****************************************************************************************/

    function getRango(){

    }

    /**
     * @funcionalidad obtenemos los rangos y la cantidad de usuarios por cada uno de ellos
     * @return [type] [description]
     */
    function getRangos(){
        global $psDb;
        //obtenemos los rangos sin puntos
        $consulta = "SELECT * FROM u_rangos ORDER BY rango_id, r_cant";
        $row = $psDb->db_execute($consulta, null, 'fetch_assoc');
        while($row){
            $allow = unserialize($row['r_allows']);
            $datos[$row[r_type] == 0 ? 'regular' : 'post'][$row['rango_id']] = array(
                'id' => $row['rango_id'],
                'name' => $row['r_name'],
                'color' => $row['r_color'],
                'imagen' => $row['r_image'],
                'cant' => $row['r_cant'],
                'max_points' => $row['gopfp'],
                'user_puntos' => $row['gopfd'],
                'type' => $row['r_type'],
                'num_members' => 0,
            );
        }
        //liberamos memoria en la db
        $psDb->db_execute($consulta, null, 'closeCursor');
        //obtenemos el número de usuarios de cada rango
        if(!empty($datos['post'])){
            $consulta2 = "SELECT user_rango AS id_group, COUNT(user_id) AS num_members FROM u_miembros WHERE user_rango IN :user GROUP BY user_rango";
            $valores2 = array(
                'user' => implode(', ', array_keys($datos['post'])),
            );
            $row2 = $psDb->db_execute($consulta2, $valores2, 'fetch_assoc');
            while($row2){
                $datos['regular'][$row2['id_group']]['num_members'] += $row['num_members'];
                $psDb->db_execute($consulta2, $valores2, 'closeCursor');
            }
        }
        //obtenemos el número de usuarios en rangos regulares
        if(!empty($datos['regular'])){
            $consulta3 = "SELECT user_rango AS id_group, COUNT(user_id) AS num_members FROM u_miembros WHERE user_rango IN :user GROUP BY user_rango";
            $valores3 = array(
                'user' => implode(', ', array_keys($datos['regular'])),
            );
            $row3 = $psDb->db_execute($consulta3, $valores3);
            while($row){
                $datos['regular'][$row['id_group']]['num_members'] += $row['num_members'];
            }
            $psDb->db_execute($consulta3, $valores3, 'closeCursor');
        }
        return $datos;
    }

    function newRango(){

    }

    function delRango(){

    }
}
