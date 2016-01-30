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
    //instanciamos la clase posts
    public static function &getInstance(){
        $instancia;
        if(is_null($instancia)){
            $instancia = new psPosts();
        }
        return $instancia;
    }
    
    
    
    public function getPosts(){
        global $psCore, $psUser;
        //obtenemos el id del post
        $post_id = intval(filter_input(INPUT_GET,'post_id'));
        //comprobamos que el id del post existe 
        if(empty($post_id)){
            return array('deleted','Lo sentimos. Este post fue eliminado o nunca ha existido.');
        }
        
    }
    
    public function setModePost(){
        global $psUser, $psCore;
        $action = filter_input(INPUT_GET,'action');
        if($action == 'randPost'){
            $consulta = db_execute("SELECT p.post_id, p.post_user, p.post_category, p.post_title, u.user_name, c.c_nombre, c.c_seo FROM p_posts AS p LEFT JOIN u_mmiembros AS u ON p.post_user = u.user_id LEFT JOIN p_categorias AS c ON c.cid = p.post_category WHERE p.post_status = \'0\' ".($psUser->is_admod && $psCore->settings['c_see_mod'] == 1 ? "" : "AND u.user_activo = \'1\' && u.user_baneado = \'0\'")." ORDER BY RAND() DESC LIMIT 1");
            if(!db_execute($consulta,null,'num_rows')){
                $psCore->redirectTo($psCore->settings['url']."/posts/");
            }
            $resultado = db_execute($consulta,null,'fetch_assoc');
        }else {
            $action = $action == 'prev' ? '<' : '>';
            $postid = (isset($_GET['id'])) ? filter_input(INPUT_GET,'id') : 1;
            $consulta = db_execute("SELECT p.post_id, p.post_user, p.post_category, p.post_title, u.user_name, c.c_nombre, c.c_seo FROM p_posts AS p LEFT JOIN u_miembros AS u ON p.post_user = u.user_id LEFT JOIN p_categorias AS c ON c.cid = p.post_category WHERE p.post_status = \'0\' ".($psUser->is_admod && $psCore->settings['c_see_mod'] == 1 ? "" : "AND u.user_activo = \'1\' && u.user_baneado = \'0\'")." AND p.post_id ".$action." ".$postid." ORDER BY p.post_id ".($action == '<' ? "DESC" : "ASC")." LIMIT 1");
            if(!db_execute($consulta, null, 'num_rows')){
                $psCore->redirectTo($psCore->settings['url']."/posts/");
            }
            $resultado = db_execute($consulta,null,"fetch_assoc");
        }
        $psCore->redirectoTo($psCore->settings['url']."/posts/".$resultado['c_seo']."/".$resultado['post_id']."/".$psCore->setSeo($resultado['post_title']).".html");
    }
}//cierre de clase