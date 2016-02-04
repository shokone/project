<?php
//comprobamos si hemos declarado la contante PS_HEADER
if(!defined('PS_HEADER')){
    exit("No se permite el acceso al script");
}
/**
 * clase psPosts
 * clase destinada al control de las funciones de los posts
 * 
 * @name c.posts.php
 * @author Iván Martínez Tutor
 */
class psPosts{
    /**
     * @funcionalidad instanciamos la clase psPosts
     * @return \psPosts
     */
    public static function &getInstance(){
        $instancia;
        if(is_null($instancia)){
            $instancia = new psPosts();
        }
        return $instancia;
    }
    
    /**
     * @funcionalidad obtenemos los post
     * @global type $psCore variable global del nucleo
     * @global type $psUser variable global de los usuarios
     * @return type 
     */
    public function getPosts(){
        global $psCore, $psUser;
        //obtenemos el id del post
        $post_id = intval(filter_input(INPUT_GET,'post_id'));
        //comprobamos que el id del post existe 
        if(empty($post_id)){
            return array('deleted','Lo sentimos. Este post fue eliminado o nunca ha existido.');
        }
        
    }
    
    /**
     * @funcionalidad dara al usuario la posibilidad de seleccionar un post aleatorio, 
     *  el post siguiente o el post anterior al que se encuentra
     * @global type $psUser variable global de la clase psUser
     * @global type $psCore variable global de la clase psCore
     */
    public function setModePost(){
        global $psUser, $psCore, $psDb;
        $action = filter_input(INPUT_GET,'action');
        if($action == 'randPost'){
            if($psUser->is_admod && $psCore->settings['c_see_mod'] == 1){
                $var = "";
            }else{
                $var = "AND u.user_activo = \'1\' && u.user_baneado = \'0\'";
            }
            $valores = ['var' => $var];
            $consulta = "SELECT p.post_id, p.post_user, p.post_category, p.post_title, u.user_name, c.c_nombre, c.c_seo FROM p_posts AS p LEFT JOIN u_mmiembros AS u ON p.post_user = u.user_id LEFT JOIN p_categorias AS c ON c.cid = p.post_category WHERE p.post_status = \'0\' :var ORDER BY RAND() DESC LIMIT 1";
            if(!$psDb->db_execute($consulta,$valores,'rowCount')){
                $psCore->redirectTo($psCore->settings['url']."/posts/");
            }
            $resultado = $psDb->db_execute($consulta,$valores,'fetch_assoc');
        }else {
            $action = $action == 'prev' ? '<' : '>';
            $postid = (isset($_GET['id'])) ? filter_input(INPUT_GET,'id') : 1;
            if($psUser->is_admod && $psCore->settings['c_see_mod'] == 1){
                $com = "";
            }else{
                $com = "AND u.user_activo = \'1\' && u.user_baneado = \'0\'";
            }
            if($action == '<'){
                $com2 = "DESC";
            }else{
                $com2 = "ASC";
            }
            $valores = [
                'var1' => $com,
                'action' => $action,
                'postid' => $postid,
                'var2' => $com2
            ];
            $consulta = "SELECT p.post_id, p.post_user, p.post_category, p.post_title, u.user_name, c.c_nombre, c.c_seo FROM p_posts AS p LEFT JOIN u_miembros AS u ON p.post_user = u.user_id LEFT JOIN p_categorias AS c ON c.cid = p.post_category WHERE p.post_status = \'0\' :var1 AND p.post_id :action :postid ORDER BY p.post_id :var2 LIMIT 1";
            if(!$psDb->db_execute($consulta, $valores, 'rowCount')){
                $psCore->redirectTo($psCore->settings['url']."/posts/");
            }
            $resultado = $psDb->db_execute($consulta,$valores,"fetch_assoc");
        }
        $psCore->redirectoTo($psCore->settings['url']."/posts/".$resultado['c_seo']."/".$resultado['post_id']."/".$psCore->setSeo($resultado['post_title']).".html");
    }//fin setModePost
    
}//cierre de clase