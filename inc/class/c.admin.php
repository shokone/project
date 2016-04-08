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
        foreach($theme as $key => $valor){
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

    /**
     * @funcionalidad obtenemos los datos de un rango de la db
     * @return [type] [description] devolvemos un array con los datos obtenidos
     */
    function getRango(){
        global $psDb;
        $consulta = "SELECT * FROM u_rangos WHERE rango_id = :rid";
        $valores = array(
            'rid' => (int)$_GET['rid'],
        );
        $query = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
        //unserializamos el valor de los permisos del rango
        $query['permisos'] = unserialize($query['r_allows']);
        return $query;
    }

    /**
     * @funcionalidad obtenemos los rangos y la cantidad de usuarios por cada uno de ellos
     * @return [type] [description] devolvemos un array con los datos obtenidos
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
        //obtenemos el número de usuarios de cada rango
        if(!empty($datos['post'])){
            $consulta2 = "SELECT user_rango AS id_group, COUNT(user_id) AS num_members FROM u_miembros WHERE user_rango IN :user GROUP BY user_rango";
            $valores2 = array(
                'user' => implode(', ', array_keys($datos['post'])),
            );
            $row2 = $psDb->db_execute($consulta2, $valores2, 'fetch_assoc');
            while($row2){
                $datos['regular'][$row2['id_group']]['num_members'] += $row['num_members'];
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
        }
        return $datos;
    }

    /**
     * @funcionalidad añadimos un nuevo rango a nuestro portal
     * @return [type] [description] devolvemos un valor booleano con el resultado de la inserción de datos
     */
    function newRango(){
        global $psDb, $psCore;
        //recogemos los datos del formulario
        $rango = array(
            'name' => $psCore->badWords($_POST['r_name']),
            'color' => $_POST['r_color'],
            'cant' => empty($_POST['global-cantidad-requerida']) ? 0 : $_POST['global-cantidad-requerida'],
            'img' => $_POST['r_img'],
            'type' => $_POST['global-type'] > 4 ? 0 : $_POST['global-type'],
        );
        //comprobamos los campos requeridos
        if(empty($rango['name']){
            return 'Debes ingresar un nombre para el nuevo rango';
        }
        if($_POST['global-puntos-por-posts'] > $_POST['global-puntos-por-dia']){
            return 'El rango no puede permitir dar m&aacute;s puntos. Establezca un m&aacute;ximo superior de puntos al d&iacute;a en el panel de administraci&oacute;n';
        }
        //guardamos un array con los datos de los permisos
        $permisos = array(
            'gopp' => $_POST['global-publicar-posts'],
            'gopcp' => $_POST['global-publicar-comentarios-posts'],
            'govpp' => $_POST['global-votar-positivo-post'],
            'govpn' => $_POST['global-votar-negativo-post'],
            'goepc' => $_POST['global-editar-propios-comentarios'],
            'godpc' => $_POST['global-eliminar-propios-comentarios'],
            'gopf' => $_POST['global-publicar-fotos'],
            'gopcf' => $_POST['global-publicar-comentarios-fotos'],
            'gorpap' => $_POST['global-revisar-posts'],
            'govwm' => $_POST['global-ver-mantenimiento'],
            'goaf' => $_POST['global-antiflood'],
            'goppp' => $_POST['global-puntos-por-posts'],
            'goppd' => $_POST['global-puntos-por-dia'],
            'suad' => $_POST['superadmin'],
            'sumo' => $_POST['supermod'],
            'moacp' => $_POST['mod-acceso-panel'],
            'mocdu' => $_POST['mod-cancelar-denuncias-usuarios'],
            'moadf' => $_POST['mod-aceptar-denuncias-fotos'],
            'mocdp' => $_POST['mod-cancelar-denuncias-posts'],
            'moadm' => $_POST['mod-aceptar-denuncias-mensajes'],
            'mocdm' => $_POST['mod-cancelar-denuncias-mensajes'],
            'movub' => $_POST['mod-ver-usuarios-baneados'],
            'moub' => $_POST['mod-usar-buscador'],
            'morp' => $_POST['mod-reciclar-posts'],
            'mocp' => $_POST['mod-contenido-posts'],
            'mocc' => $_POST['mod-contenido-comentarios'],
            'most' => $_POST['mod-sticky'],
            'moayca' => $_POST['mod-abrir-y-cerrar-ajax'],
            'movcud' => $_POST['mod-ver-cuentas-desactivadas'],
            'movcus' => $_POST['mod-ver-cuentas-suspendidas'],
            'mosu' => $_POST['mod-suspender-usuarios'],
            'modu' => $_POST['mod-desbanear-usuarios'],
            'moep' => $_POST['mod-eliminar-posts'],
            'moedpo' => $_POST['mod-editar-posts'],
            'moop' => $_POST['mod-ocultar-posts'],
            'mocepc' => $_POST['mod-comentar-post-cerrado'],
            'moedcopo' => $_POST['mod-editar-comentarios-posts'],
            'moaydcp' => $_POST['mod-desyaprobar-comentarios-posts'],
            'moecp' => $_POST['mod-eliminar-comentarios-posts'],
            'moef' => $_POST['mod-eliminar-fotos'],
            'moedfo' => $_POST['mod-editar-fotos'],
            'moecf' => $_POST['mod-eliminar-comentarios-fotos'],
            'moepm' => $_POST['mod-eliminar-publicacion-muro'],
            'moecm' => $_POST['mod-eliminar-comentario-muro'],
            'godp' => $_POST['global-dar-puntos'],
        );
        //serializamos el array de permisos
        $permisos = serialize($permisos);
        //realizamos la consulta en la base de datos
        $consulta = "INSERT INTO u_rangos (r_name, r_color, r_image, r_cant, r_allows, r_type) VALUES (:name, :color, :image, :cant, :allows, :type)";
        $valores = array(
            'name' => $rango['name'],
            'color' => $rango['color'],
            'image' => $rango['img'],
            'cant' => $rango['cant'],
            'allows' => $permisos,
            'type' => $rango['type'],
        );
        if($psDb->db_execute($consulta, $valores)){
            return true;
        }
    }

    /**
     * @funcionalidad eliminamos competamente un rango y damos a los usuarios que pertenecían un rango nuevo
     * @return [type] [description] devolvemos un valor con el resultado de la operación
     */
    function delRango(){
        global $psDb;
        $r_id = $_GET['rid'];
        if($r_id > 3){
            $consulta = "UPDATE u_miembros SET user_rango = :n_rango WHERE user_rango = :r_id";
            $valores = array(
                'n_rango' => $_POST['new_rango'],
                'r_id' => $r_id,
            );
            if($psDb->db_execute($consulta, $valores)){
                $consulta2 = "DELETE FROM u_rangos WHERE rango_id = :r_id";
                $valores2 = array('r_id' => $r_id,);
                if($psDb->db_execute($consulta2, $valores2)){
                    return true;
                }
            }
        }else{
            return 'Lo sentimos, ha ocurrido un error. Este rango no puede ser eliminado.';
        }
    }

    /**
     * @funcionalidad obtenemos los datos del rango del formulario y actualizamos los datos del rango en la db
     * @return [type] [description] devolvemos el resultado de la consulta
     */
    function guardarRango(){
        global $psDb;
        $r_id = $_GET['rid'];
        $rango = array(
            'name' => $psCore->badWords($_POST['r_name']),
            'color' => $_POST['r_color'],
            'cant' => empty($_POST['global-cantidad-requerida']) ? 0 : $_POST['global-cantidad-requerida'],
            'img' => $_POST['r_img'],
            'type' => $_POST['global-type'] > 4 ? 0 : $_POST['global-type'],
        );
        //comprobamos los campos requeridos
        if(empty($rango['name']){
            return 'Debes ingresar un nombre para el nuevo rango';
        }
        if($_POST['global-puntos-por-posts'] > $_POST['global-puntos-por-dia']){
            return 'El rango no puede permitir dar m&aacute;s puntos. Establezca un m&aacute;ximo superior de puntos al d&iacute;a en el panel de administraci&oacute;n';
        }
        //guardamos un array con los datos de los permisos
        $permisos = array(
            'gopp' => $_POST['global-publicar-posts'],
            'gopcp' => $_POST['global-publicar-comentarios-posts'],
            'govpp' => $_POST['global-votar-positivo-post'],
            'govpn' => $_POST['global-votar-negativo-post'],
            'goepc' => $_POST['global-editar-propios-comentarios'],
            'godpc' => $_POST['global-eliminar-propios-comentarios'],
            'gopf' => $_POST['global-publicar-fotos'],
            'gopcf' => $_POST['global-publicar-comentarios-fotos'],
            'gorpap' => $_POST['global-revisar-posts'],
            'govwm' => $_POST['global-ver-mantenimiento'],
            'goaf' => $_POST['global-antiflood'],
            'goppp' => $_POST['global-puntos-por-posts'],
            'goppd' => $_POST['global-puntos-por-dia'],
            'suad' => $_POST['superadmin'],
            'sumo' => $_POST['supermod'],
            'moacp' => $_POST['mod-acceso-panel'],
            'mocdu' => $_POST['mod-cancelar-denuncias-usuarios'],
            'moadf' => $_POST['mod-aceptar-denuncias-fotos'],
            'mocdp' => $_POST['mod-cancelar-denuncias-posts'],
            'moadm' => $_POST['mod-aceptar-denuncias-mensajes'],
            'mocdm' => $_POST['mod-cancelar-denuncias-mensajes'],
            'movub' => $_POST['mod-ver-usuarios-baneados'],
            'moub' => $_POST['mod-usar-buscador'],
            'morp' => $_POST['mod-reciclar-posts'],
            'mocp' => $_POST['mod-contenido-posts'],
            'mocc' => $_POST['mod-contenido-comentarios'],
            'most' => $_POST['mod-sticky'],
            'moayca' => $_POST['mod-abrir-y-cerrar-ajax'],
            'movcud' => $_POST['mod-ver-cuentas-desactivadas'],
            'movcus' => $_POST['mod-ver-cuentas-suspendidas'],
            'mosu' => $_POST['mod-suspender-usuarios'],
            'modu' => $_POST['mod-desbanear-usuarios'],
            'moep' => $_POST['mod-eliminar-posts'],
            'moedpo' => $_POST['mod-editar-posts'],
            'moop' => $_POST['mod-ocultar-posts'],
            'mocepc' => $_POST['mod-comentar-post-cerrado'],
            'moedcopo' => $_POST['mod-editar-comentarios-posts'],
            'moaydcp' => $_POST['mod-desyaprobar-comentarios-posts'],
            'moecp' => $_POST['mod-eliminar-comentarios-posts'],
            'moef' => $_POST['mod-eliminar-fotos'],
            'moedfo' => $_POST['mod-editar-fotos'],
            'moecf' => $_POST['mod-eliminar-comentarios-fotos'],
            'moepm' => $_POST['mod-eliminar-publicacion-muro'],
            'moecm' => $_POST['mod-eliminar-comentario-muro'],
            'godp' => $_POST['global-dar-puntos'],
        );
        //serializamos el array de permisos
        $permisos = serialize($permisos);
        //realizamos la consulta en la base de datos
        $consulta = "UPDATE u_rangos SET r_name = :name, r_color = :color, r_image = :image, r_cant = :cant, r_allows = :allows, r_type = :type WHERE rango_id = :r_id";
        $valores = array(
            'name' => $rango['name'],
            'color' => $rango['color'],
            'image' => $rango['img'],
            'cant' => $rango['cant'],
            'allows' => $permisos,
            'type' => $rango['type'],
            'r_id' => $r_id,
        );
        if($psDb->db_execute($consulta, $valores)){
            return true;
        }else{
            exit('Error al realizar la consulta en la base de datos');
        }
    }

    /**
     * @funcionalidad obtenemos los usuarios del rango seleccionado y las páginas totales que ocupan
     * @return [type] [description] devolvemos un array con los datos obtenidos
     */
    function getRangoUsers(){
        global $psDb, $psCore;
        $r_id = $_GET['rid'];
        $max = 10; //maximo de rangos a mostrar por pagina
        //obtenemos el tipo de búsqueda a realizar
        $type = $_GET['type'];
        //realizamos la consulta en la db
        $consulta = "SELECT u.user_id, u.user_name, u.user_email, u.user_registro, u.user_lastlogin FROM u_miembros AS u WHERE u.user_rango = :r_id LIMIT :limit";
        $valores = array(
            'r_id' => (int)$r_id,
            'limit' => $psCore->setPagLimite($max, true),
        );
        $query = $psDb->db_execute($consulta, $valores);
        $datos['data'] = $psDb->resultadoArray($query);
        //obtenemos las páginas
        $consulta = "SELECT COUNT(*) FROM u_miembros WHERE user_rango = :r_id";
        $valores = array('r_id' => (int)$r_id,);
        //asignamos el resultado como si fuera un array
        list($query2) = $psDb->db_execute($consulta, $valores, 'fetch_num');
        $datos['pages'] = $psCore->getPageIndex($psCore->settings(['url'].'/admin/rangos?act='.$query2.'&rid='.$r_id.'&type='.$type, $_GET['start'], $query2, $max));
        return $datos;
    }

    /**
     * @funcionalidad obtenemos el rango seleccionado y actualizamos los datos en la db
     * @return [type] [description] devolvemos un valor dependiendo del resultado de las consultas
     */
    function setRangoDefault(){
        global $psDb;
        $r_id = $_GET['rid'];
        $consulta = "SELECT rango_id, r_type FROM u_rangos WHERE rango_id = :r_id";
        $valores = array('r_id' => $r_id,);
        $rango = $psDb->db_execute($consulta, $valores);
        if(!empty($rango['rango_id']) && $rango['r_type'] == 0){
            $consulta2 = "UPDATE w_configuracion SET c_reg_rango = :r_id WHERE tscript_id = \'1\'";
            $valores2 = array('r_id' => $r_id);
            if($psDb->db_execute($consulta2, $valores2)){
                return true;
            }
        }else{
            return 'El rango seleccionado no existe';
        }
    }

    /**
     * @funcionalidad obtenemos un listado con todos los rangos
     * @return [type] [description] devolvemos un array con los datos generados
     */
    function getAllRangos(){
        global $psDb;
        //obtenemos un array con el listado de rangos
        $consulta = "SELECT rango_id, r_name, r_color FROM u_rangos";
        $query = $psDb->db_execute($consulta);
        $datos = $psDb->resultadoArray($query);
        return $datos;
    }

    /*****************************************************************************************/
    /************************************ Usuarios *******************************************/
    /*****************************************************************************************/

    /**
     * @funcionalidad obtenemos un listado de usuarios
     * @return [type] [description] devolvemos los datos generados
     */
    function getUsers(){
        global $psDb;
        $max = 25; //maximo de usuarios a mostrar por página
        //obtenemos la forma de ordenar el listado de usuarios
        if($_GET['order'] == 'estado'){//ordenamos por su estado de actividad
            $order = 'u.user_activo, u.user_baneado';
        }else if($_GET['order'] == 'correo'){//ordenamos por su email
            $order = 'u.user_email';
        }else if($_GET['order'] == 'ip'){//ordenamos por su ip
            $order = 'u.user_last_ip';
        }else if($_GET['order'] == 'lastactive'){//ordenamos por su ultima conexion
            $order = 'u.user_lastactive';
        }else{//ordenamos por su id (orden por defecto)
            $order = 'u.user_id';
        }
        //realizamos la consulta en la db
        $consulta = "SELECT u.*, r.*, p.* FROM u_perfil AS p LEFT JOIN u_miembros AS u ON u.user_id = p.user_id LEFT JOIN u_rangos AS r ON r.rango_id = u.user_rango ORDER BY :order :modo LIMIT :limit";
        $valores = array(
            'order' => $order,
            'modo' => $_GET['modo'] == 'asc' ? 'ASC' : 'DESC',
            'limit' => $psCore->inicioPages($max, true),
        );
        $query = $psDb->db_execute($consulta, $valores);
        $datos['data'] = $psDb->resultadoArray($query);
        //obtenemos el total de páginas 
        $consulta2 = "SELECT COUNT(*) FROM u_miembros WHERE user_id > \'0\'";
        list($query2) = $psDb->db_execute($consulta2, null, 'fetch_num');
        $datos['pages'] = $psCore->inicioPages($psCore->settings['url'].'/admin/users?order='.$_GET['order']."&modo=".$_GET['modo'],$_GET['start'],$query2, $max);
        return $datos;
    }

    /**
     * @funcionalidad obtenemos los datos del rango de un usuario
     * @param  [type]  $uid       [description] id del usuario
     * @return [type]              [description] devolvemos un array con los datos
     */
    function getUserRango($uid){
        global $psDb;
        //realizamos la consulta en la db
        $consulta = "SELECT u.user_rango, r.rango_id, r.r_name, r.r_color FROM u_miembros AS u LEFT JOIN u_rangos AS r ON u.user_rango = r.rango_id WHERE u.user_id = :uid";
        $valores = array('uid' => $uid,);
        $datos['user'] = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
        //obtenemos la lista de rangos disponibles
        $consulta2 = "SELECT rango_id, r_name, r_color FROM u_rangos";
        $query = $psDb->db_execute($consulta);
        $datos['rangos'] = $psDb->resultadoArray($query);
        return $datos;
    }

    /**
     * @funcionalidad cambiamos el rango del usuario
     * @param  [type]  $uid        [description] id del usuario al cual queremos cambiar el rango
     * @return [type]              [description] devolvemos el resultado de la comprobacion(true si todo ok y mensaje si hay algún error)
     */
    function setUserRango($uid){
        global $psDb, $psUser;
        //el rango de admin solo pueder dar o quitar un admin
        $new_rango = (int)$_POST['new_rango'];
        if($new_rango == $psUser->user_id){
            return 'Error. No puedes cambiarte el rango a ti mismo.';
        }else if($psUser->user_id != 1 && $new_rango == 1){
            return 'Error. S&oacute;lo el administrador principal puede crear m&aacute;s administradores.';
        }else{
            $consulta = "UPDATE u_miembros SET user_rango = :new_rango WHERE user_id = :uid";
            $valores = array(
                'new_rango' => $new_rango,
                'uid' => (int)$uid,
            );
            if($psDb->db_execute($consulta, $valores)){
                return true;
            }
        }
    }

    /**
     * @funcionalidad cambiamos la firma que el usuarios mostrará en sus post y comentarios
     * @param  [type]  $uid        [description] obtenemos el id del usuario
     * @return [type]              [description] devolvemos un valor booleano con el resultado de la consulta
     */
    function serUserFirma($uid){
        global $psDb;
        $consulta = "UPDATE u_perfil SET user_firma = :user_firma WHERE user_id = :uid";
        $valores = array(
            'user_firma' => $_POST['firma'],
            'uid' => (int)$uid,
        );
        if($psDb->db_execute($consulta, $valores)){
            return true;
        }
    }

    /**
     * @funcionalidad comprobamos si ha sido seleccionada la opción de borrar la cuenta o alguno de sus datos, después mandamos un aviso y un email al admin principal
     * @param  [type]  $uid        [description] obtenemos el id del usuario
     * @return [type]              [description] 
     */
    function deleteUserContent($uid){
        global $psUser, $psDb;
        //obtenemos los datos del usuario
        $consulta = "SELECT user_id FROM u_miembros WHERE user_id = :uid && user_password = :pass";
        $valores = array(
            'uid' => $uid,
            'pass' => md5($_POST['password']),
        );
        if($psDb->db_execute($consulta, $valores, 'rowCount')){
            $cuenta = $_POST['bocuenta'];//si = true borraremos todos los datos de la cuenta
            //realizamos las consultas oportunas
            $posts = "DELETE FROM p_posts WHERE post_user = :uid";//post
            $composts = "DELETE FROM p_comentarios WHERE c_user = :uid";//comentarios post
            $votosposts = "DELETE FROM p_votos WHERE tuser = :uid";//votos posts
            $fotos = "DELETE FROM f_fotos WHERE f_user = :uid";//fotos
            $comfotos = "DELETE FROM f_comentarios WHERE c_user = :uid";//comentarios fotos
            $votosfotos = "DELETE FROM f_votos WHERE v_user = :uid";//votos fotos
            $muro = "DELETE FROM u_muro WHERE p_user_pub = :uid";//estado muro
            $commuro = "DELETE FROM u_muro_comentarios WHERE c_user = :uid";//comentarios en el muro
            $likes = "DELETE FROM u_muro_likes WHERE user_id = :uid";//likes
            $seguidores = "DELETE FROM u_follows WHERE f_id = :uid AND f_type = :type";//seguidores
            $siguiendo = "DELETE FROM u_follows WHERE f_user = :uid AND f_type = :type";//siguiendo
            $favoritos = "DELETE FROM p_favoritos WHERE fav_user = :uid";//favoritos
            $actividad = "DELETE FROM u_actividad WHERE user_id = :uid";//actividad
            $avisos = "DELETE FROM u_avisos WHERE user_id = :uid";//avisos
            $bloqueos = "DELETE FROM u_bloqueos WHERE b_user = :uid";//bloqueos
            $mensajes = "DELETE FROM u_mensajes WHERE mp_from = :uid";//mensajes
            $mensajes2 = "DELETE FROM u_respuestas WHERE mr_from = :uid";//respuestas a los mensajes
            $sesiones = "DELETE FROM  WHERE = :uid";//sesiones
            $visitas = "DELETE FROM  WHERE = :uid";//visitas
            $valores2 = array('uid' => $uid);
            $valores3 = array('uid' => $uid, 'type' => 1);
            //comprobamos si borramos los post
            if($_POST['boposts'] || $cuenta){$psDb->db_execute($posts, $valores2);}
            //comprobamos si borramos los comentarios en los post
            if($_POST['bocomposts'] || $cuenta){$psDb->db_execute($composts, $valores2);}
            //comprobamos si borramos los votos de los post
            if($_POST['bovotosposts'] || $cuenta){$psDb->db_execute($votosposts, $valores3);}
            //comprobamos si borramos el estado en su muro
            if($_POST['boestados'] || $cuenta){$psDb->db_execute($muro, $valores2);}
            //comprobamos si borramos los comentarios en el muro
            if($_POST['bocomestados'] || $cuenta){$psDb->db_execute($commuro, $valores2);}
            //comprobamos si borramos fotos
            if($_POST['bofotos'] || $cuenta){$psDb->db_execute($fotos, $valores2);}
            //comprobamos si borramos los comentarios en las fotos
            if($_POST['bocomfotos'] || $cuenta){$psDb->db_execute($comfotos, $valores2);}
            //comprobamos si borramos los votos de las fotos
            if($_POST['bovotosfotos'] || $cuenta){$psDb->db_execute($votosfotos, $valores3);}
            //comprobamos si borramos las visitas
            if($_POST['bovisitas'] || $cuenta){$psDb->db_execute($visitas, $valores3);}
            //comprobamos si borramos los likes dados
            if($_POST['bolikes'] || $cuenta){$psDb->db_execute($likes, $valores2);}
            //comprobamos si borramos los seguidores
            if($_POST['boseguidores'] || $cuenta){$psDb->db_execute($seguidores, $valores3);}
            //comprobamos si borramos los usuarios que sigue
            if($_POST['bosiguiendo'] || $cuenta){$psDb->db_execute($siguiendo, $valores3);}
            //comprobamos si borramos la actividad
            if($_POST['boactividad'] || $cuenta){$psDb->db_execute($actividad, $valores3);}
            //comprobamos si borramos los avisos
            if($_POST['boavisos'] || $cuenta){$psDb->db_execute($avisos, $valores3);}
            //comprobamos si borramos los favoritos
            if($_POST['bofavoritos'] || $cuenta){$psDb->db_execute($favoritos, $valores3);}
            //comprobamos si borramos cualquier bloqueo de cuenta
            if($_POST['bobloqueos'] || $cuenta){$psDb->db_execute($bloqueos, $valores3);}
            //comprobamos si borramos los mensajes
            if($_POST['bomensajes'] || $cuenta){$psDb->db_execute($mensajes, $valores3);$psDb->db_execute($mensajes2, $valores3);}
            //comprobamos si borramos las sesiones
            if($_POST['bosesiones'] || $cuenta){$psDb->db_execute($sesiones, $valores3);}
            
            //obtenemos el nombre del usuario 
            $user_name = "SELECT user_name FROM u_miembros WHERE user_id = :uid";
            $u_name = $psDb->db_execute($user_name, $valores2);
            //obtenemos el email del administrador
            $email = "SELECT user_email FROM u_miembros WHERE user_id = :uno";
            $valores4 = array('uno' => 1);
            $admin = $psDb->db_execute($email, $valores4);

            if($cuenta && $psUser->user_id != $uid){
                //borramos el resto de datos del usuario
                $u = array(
                    'DELETE FROM u_miembros WHERE user_id = :uid',
                    'DELETE FROM u_perfil WHERE user_id = :uid',
                    'DELETE FROM u_portal WHERE user_id = :uid',
                    'DELETE FROM u_bloqueos WHERE b_auser = :uid',
                    'DELETE FROM u_mensajes WHERE mp_to = :uid',
                    'DELETE FROM w_denuncias WHERE d_user = :uid',
                );
                $u2 = 'DELETE FROM w_visitas WHERE for = :uid AND type = :type';
                foreach($u as $datos){
                    $psDb->db_execute($datos, $valores2);
                }
                $psDb->db_execute($u2, $valores3);
            }

            //enviamos un aviso al admin principal
            $c = "INSERT INTO u_avisos (user_id, av_subject, av_body, av_date, av_read, av_type) VALUES (:user_id, :av_subject, :av_body, :av_date, :av_read, :av_type)";
            $val = array(
                'user_id' => 1,
                'av_subject' => ($cuenta ? 'Cuenta eliminada' : 'Contenido eliminado'),
                'av_body' => 'Hola, le informamos que el administrador con nombre '.$psUser->nick.' y con id '.$psUser->user_id.' ha eliminado '.($cuenta ? 'la cuenta' : 'varios contenidos').' de '.$u_name[0],
                'av_date' => time(),
                'av_read' => 0,
                'av_type' => 1
            );
            $psDb->db_execute($c, $val);
            //mandamos un email al admin principal
            $body = "
                <html>
                    <head><title>".($cuenta ? 'Cuenta eliminada' : 'Contenido eliminado')."</title></head>
                    <body>
                        <p>Hola, le informamos que el administrador con nombre ".$psUser->nick." y con id ".$psUser->user_id." ha eliminado ".($cuenta ? 'la cuenta' : 'varios contenidos')." de ".$u_name[0]."</p>
                    </body>
                </html>
            ";
            mail($admin[0], ($cuenta ? 'Cuenta eliminada' : 'Contenido eliminado'), $body, 'Content-type: text/html; charset=iso-8859-15');
            return 'Todos los cambios han salido correctamente.';
        }else{
            return 'Ha ocurrido un error, por favor int&eacute;ntelo de nuevo m&aacute;s tarde o contacte con el administrador principal del portal.';
        }
    }

    /**
     * @funcionalidad obtenemos los datos del usuario, del rango y del perfil 
     * @return [type]              [description] devolvemos un array con los datos obtenidos
     */
    function getUserDatos(){
        global $psDb;
        $uid = $_GET['uid'];
        $consulta = "SELECT u.*, r.*, p.* FROM u_perfil AS p LEFT JOIN u_miembros AS u ON u.user_id = p.user_id LEFT JOIN u_rangos AS r ON r.rango_id = u.user_rango WHERE u.user_id = :uid";
        $valores = array('uid' => $uid,);
        $datos = $psDb->db_execute($consulta, $valorse, 'fetch_assoc');
        //unserializamos los datos obtenidos y guardamos el array
        $datos['p_configs'] = unserialize($datos['p_configs']);
        return $datos;
    }

    /**
     * @funcionalidad modificamos los datos de un usuario
     * @param  [type]   $uid -> obtenemos el id del usuario por parametro
     * @return [type]           devolvemos el resultado de la consulta (true si todo ok, sino un mensaje de error)
     */
    function setUserDatos($uid){
        global $psDb, $psUser, $psCore;
        $consulta = "SELECT user_name, user_email, user_password FROM u_miembros WHERE user_id = :uid";
        $valores = array(
            'uid' => $uid,
        );
        $query = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
        //obtenemos los datos del formulario
        $email = empty($_POST['email']) ? $query['user_email'] : $_POST['email'];
        $pass = $_POST['pwd'];
        $pass2 = $_POST['pwd2'];
        $nick = empty($_POST['nick']) ? $query['user_name'] : $_POST['nick'];
        $puntos = empty($_POST['puntos']) ? $query['user_puntos'] : $_POST['puntos'];
        $puntosxdar = empty($_POST['puntosxdar']) ? $query['user_puntos'] : $_POST['puntosxdar'];
        $change_nick = empty($_POST['changenick']) ? $query['user_name_changes'] : $_POST['changenick'];
        $userDatos['email'] = $email;
        //validamos los datos del formulario
        //comprobamos el email
        if(!preg_match('/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,4})/$', $email)){
            return 'El correo electr&oacute;nico introducido es incorrecto.';
        }
        //comprobamos los puntos
        if(is_numeric($puntos){
            $u_puntos = ', user_puntos = :puntos'
            $userDatos['puntos'] = (int)$puntos;
        }else{
            $userDatos['puntos'] = '';
            return 'El valor de los puntos debe ser num&eacute;rico.';
        }
        //comprobamos los puntos que puede dar
        if(is_numeric($puntosxdar)){
            $u_puntosxdar = ', user_puntosxdar = :user_puntosxdar';
            $userDatos['user_puntosxdar'] = $puntosxdar;
        }else{
            $userDatos['user_puntosxdar'] = '';
            return 'El valor de los puntos permitidos a dar debe ser num&eacute;rico.';
        }
        //cantidad disponible de cambios de nick que tiene al usuario
        if(is_numeric($change_nick)){
            $u_change_nick = ', user_name_changes = :name_changes';
            $userDatos['name_changes'] = $change_nick;
        }else{
            $userDatos['name_changes'] = '';
            return 'La cantidad de cambios de nick que tiene el usuario debe de ser un valor num&eacute;rico.';
        }
        //comprobamos el nick y el password
        if(!empty($pass) && !empty($pass2)){
            //comprobamos el nick
            if(strlen($nick) < 3){
                return 'El nick debe tener un m&iacute;nimo de 3 caracteres.';
            }
            if(!preg_match('/^[A-Za-z0-9-_]/$', $nick)){
                return 'El nick introducido no es v&aacute;lido.';
            }
            $new_nick = ', user_name = :user_name';
            $userDatos['new_nick'] = $nick;
            //ahora comprobamos el password
            if(strlen($pass) < 6){
                return 'La contrase&ntilde;a debe tener al menos 6 caracteres.';
            }
            if($pass != $pass2){
                return 'Las contrase&ntilde;as no coinciden.';
            }
            $new_pass = ', user_password = :user_password';
            $userDatos['user_password'] = $pass;
        }
        $userDatos['uid'] = $uid;
        //una vez comprobados los campos realizamos la consulta en la db
        $consulta = "UPDATE u_miembros SET user_email = :email :u_change_nick :new_nick :user_puntosxdar :user_puntos :user_password WHERE user_id = :uid";
        if($psDb->db_execute($consulta, $userDatos)){
            if($_POST['sendata']){
                //si todo es correcto, mandamos un correo al usuario afectado por el cambio
                mail($email, 'Nuevos datos de acceso a '.$psCore->settings['titulo'], 'Sus nuevos datos de acceso a '.$psCore->settings['titulo'].' han sido cambiados por un administrador.<br> Los nuevos datos de acceso ser&aacute;n los siguientes:<br>Usuario: '.$nick.'<br>Password: '.$pass.'<br>Disculpe las molestias.', 'From: '..$psCore->settings['titulo'].'<br>Este correo es enviado por un servidor de email autom&aacute;tico. Por favor no responda a este correo.<br><no-reply@'..$psCore->settings['domain'].'>'); 
            }
            return true;
        }
    }

    /**
     * @funcionalidad obtenemos los datos de privacidad del usuario
     * @return [type]              [description] devolvemos un array con los datos obtenidos
     */
    function getUserPrivacidad(){
        global $psDb;
        //realizamos la consulta con la db
        $consulta = "SELECT p_configs FROM u_perfil WHERE user_id = :uid";
        $valores = array('uid' => $_GET['uid']);
        $datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
        //unserializamos los datos 
        $datos['p_configs'] = unserialize($datos['p_configs']);
        return $datos;
    }

    /**
     * @funcionalidad actualizamos los datos de privacidad del usuario
     * @return [type]              [description] devolvemos un valor booleano con el resultado de la actualización
     */
    function setUserPrivacidad(){
        global $psDb, $psCore;
        $muro_firma = ($_POST['muro_firma'] > 4) ? 5 : $_POST['muro_firma'];
        $hits = ($_POST['last_hits'] == 1 || $_POST['last_hits'] == 2) ? 0 : $_POST['last_hits'];
        $datos = array(
            'm' => $_POST['muro'],
            'mf' => $muro_firma,
            'rmp' => $_POST['rec_mps'],
            'hits' => $hits,
        );
        //serializamosel array de datos para 
        $perfil['configs'] = serialize($datos);
        $datos_actualizar = $psCore->getDatos($perfil, 'p_');
        $consulta = "UPDATE u_perfil SET :datos WHERE user_id = :uid";
        $valores = array(
            'datos' => $datos_actualizar,
            'uid' => $_GET['uid'],
        );
        if($psDb->db_execute($consulta, $valores)){
            return true;
        }
    }
}
