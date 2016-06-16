<?php
//comprobamos si la constante PS_HEADER ha sido declarada, en caso contrario no se puede acceder al script
if(!defined('PS_HEADER')){
  exit('No se permite el acceso directo al script');
}

/**
* Clase denuncias
* destinada al control de las denuncias que realicen los usuarios
*
* @name() c.denuncias.php
* @author  Iván Martínez Tutor
*/
class psDenuncias{
    /**
    * @funcionalidad comprobamos si la clase ha sido instanciada
    * si no es así creamos un nuevo objeto para la clase psDenuncias
    * @return [type] [description]
    */
    public static function &getInstance(){
      static $instance;
      if(is_null($instance)){
        $instance = new psDenuncias();
      }
      return $instance;
    }

    /**
    * @funcionalidad creamos una denuncia nueva
    * @param [type] $did => id del elemento denunciado
    * @param [type] $type => coprobamos si la denuncia es de posts, fotos, mensajes o usuarios
    * @return [type] [description] devolvemos el resultado de la denuncia
    */
    function setDenuncia($did, $type){
      global $psDb, $psCore, $psUser;
      //variables necesarias
      $razon = filter_input(INPUT_POST, 'razon');
      $extras = filter_input(INPUT_POST, 'extras');
      $date = time();
      switch($type){
         //comprobamos el tipo de elemento a denunciar
         case 'posts':
            //comprobamos si el post es del mismo usuario y si está patrocinado en el home (sticky)
            $consulta = "SELECT post_id, post_user, post_sticky FROM p_posts WHERE post_id = :pid";
            $valores = array('pid' => $did);
            $post = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
            if(empty($post['post_id'])){
               return '0: El post seleccionado no existe.';
            }
            if($post['post_user'] == $psUser->user_id){
               return '0: No puedes denunciar tus propios post.';
            }
            if($post['post_sticky'] == 1){
               return '0: No puedes denunciar un post patrocinado.';
            }
            if($psUser->admod == 1){
               return '0: No puedes denunciar siendo moderador, pero puedes atender las denuncias de los dem&aacute;s usuarios.';
            }
            //comprobamos si ya ha sido denunciado por el mismo usuario
            $consulta2 = "SELECT did FROM w_denuncias WHERE obj_id = :did AND d_user = :uid AND d_type = :type";
            $valores2 = array(
               'did' => $did,
               'uid' => $psUser->user_id,
               'type' => 1
            );
            $denuncia = $psDb->db_execute($consulta2, $valores2, 'rowCount');
            if(!empty($denuncia)){
               return '0: Ya has denunciado este post.';
            }
            //obtenemos el total de denuncias que lleva el post
            $consulta3 = "SELECT did FROM w_denuncias WHERE obj_id = :did AND d_type = :type";
            $valores3 = array(
               'did' => $did,
               'type' => 1
            );
            $denuncias = $psDb->db_execute($consulta3, $valores3, 'rowCount');
            //ocultamos el post cuando lleve más de 2 denuncias
            if($denuncias > 2){
               //actualizamos los datos en la db
               $consulta4 = "UPDATE p_posts SET post_status = :status WHERE post_id = :pid";
               $consulta5 = "UPDATE w_stats SET stats_posts = :post WHERE stats_no = :no";
               $valores4 = array(
                  'status' => 1,
                  'pid' => $did
               );
               $valores5 = array(
                  'post' => 'stats_posts'-1,
                  'no' => 1
               );
               $psDb->db_execute($consulta4, $valores4);
               $psDb->db_execute($consulta5, $valores5);
            }
            //ahora insertamos la nueva denuncia
            $consulta6 = "INSERT INTO w_denuncias (obj_id, d_user, d_razon, d_extra, d_type, d_date) VALUES (:did, :user, :razon, :extra, :type, :dates)";
            $valores6 = array(
                  'did' => $did,
                  'user' => $psUser->user_id,
                  'razon' => $razon,
                  'extra' => $extras,
                  'type' => 1,
                  'dates' => $date,
            );
            if($psDb->db_execute($consulta6, $valores6)){
               return '1: La denuncia del post fue enviada correctamente.';
            }else{
               return '0: Error al realizar la denuncia. Por favor, int&eacute;ntelo de nuevo m&aacute;s tarde.';
            }
            break;
         case 'mensajes':
            //comprobamos si el usuario ya ha denunciado este mensaje
            $consulta = "SELECT did FROM w_denuncias WHERE obj_id = :did AND d_user = :uid AND d_type = :type";
            $valores = array(
               'did' => $did,
               'uid' => $psUser->user_id,
               'type' => 2
            );
            $denuncia = $psDb->db_execute($consulta, $valores, 'rowCount');
            if(!empty($denuncia)){
               return '0: Ya has denunciado este mensaje. Deber&aacute;s esperar a que un admin o moderador lo compruebe para poder realizar otra denuncia.';
            }
            $consulta2 = "SELECT mp_id, mp_to, mp_from FROM u_mensajes WHERE mp_id = :mid";
            $valores2 = array('mid' => $did);
            $denuncia2 = $psDb->db_execute($consulta2, $valores2, 'fetch_assoc');
            if(empty($denuncia2['mp_id'])){
               return '0: Lo sentimos el mensaje seleccionado no existe.';
            }
            //comprobamos si el usuario era el emisor o el receptor del mensaje
            if($denuncia2['mp_to'] == $psUser->user_id){
               $tabla = 'mp_del_to';
            }else if($denuncia2['mp_from'] == $psUser->user_id){
               $tabla = 'mp_del_from';
            }
            //realizamos la inserción y actualización en la db
            $consulta3 = "INSERT INTO w_denuncias (obj_id, d_user, d_razon, d_extra, d_type, d_date) VALUES (:did, :user, :razon, :extra, :type, :dates)";
            $valores3 = array(
                  'did' => $did,
                  'user' => $psUser->user_id,
                  'razon' => 0,
                  'extra' => '',
                  'type' => 2,
                  'dates' => $date,
            );
            if($psDb->db_execute($consulta3, $valores3)){
               $consulta4 = "UPDATE u_mensajes SET :table = :table2 WHERE mp_id = :mid";
               $valores4 = array(
                  'table' => $table,
                  'table2' => 1,
                  'mid' => $did
               );
               $psDb->db_execute($consulta4, $valores4);
               return '1: La denuncia del mensaje ha sido realizada correctamente.';
            }else{
               return '0: Error al realizar la denuncia. Por favor, int&eacute;ntelo de nuevo m&aacute;s tarde.';
            }
            break;
         case 'usuarios':
            //comprobamos si el usuario ya ha denunciado a este usuario
            $consulta = "SELECT did FROM w_denuncias WHERE obj_id = :did AND d_user = :uid AND d_type = :type";
            $valores = array(
               'did' => $did,
               'uid' => $psUser->user_id,
               'type' => 3
            );
            $denuncia = $psDb->db_execute($consulta, $valores, 'rowCount');
            $name = $psUser->getUserName($did);
            if(!empty($denuncia)){
               return '0: Ya has denunciado a este usuario. Deber&aacute;s esperar a que un admin o moderador lo compruebe para poder realizar otra denuncia.';
            }
            if(empty($name)){
               return '0: Lo sentimos el usuario seleccionado no existe.';
            }
            //si todo ha salido bien insertamos la denuncia en la db
            $consulta2 = "INSERT INTO w_denuncias (obj_id, d_user, d_razon, d_extra, d_type, d_date) VALUES (:did, :user, :razon, :extra, :type, :dates)";
            $valores2 = array(
                  'did' => $did,
                  'user' => $psUser->user_id,
                  'razon' => $razon,
                  'extra' => $extras,
                  'type' => 3,
                  'dates' => $date,
            );
            if($psDb->db_execute($consulta2, $valores2)){
               //si se ha insertado correctamente ahora actualizamos la tabla usuarios
               $consulta3 = "UPDATE u_miembros SET user_bad_hits = :bad WHERE user_id = :uid";
               $valores3 = array(
                  'bad' => 'user_bad_hits' + 1,
                  'uid' => $did
               );
               $psDb->db_execute($consulta3, $valores3);
               return '1: La denuncia al usuario se ha realizado correctamente.';
            }else {
               return '0: Error al realizar la denuncia. Por favor, int&eacute;ntelo de nuevo m&aacute;s tarde.';
            }
            break;
         case 'fotos':
            //comprobamos si la foto es del usuario y si está oculta
            $consulta = "SELECT foto_id, f_user, f_status FROM f_fotos WHERE foto_id = :fid";
            $valores = array('fid' => $did);
            $foto = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
            //realizamos las comprobaciones
            if(empty($foto['foto_id'])){
               return '0: La foto seleccionada no existe.';
            }
            if($foto['f_user']){
               return '0: No puedes denunciar tus propias fotos.';
            }
            if($foto['f_status']){
               return '0: No puedes denunciar una foto que se encuentra oculta.';
            }
            //comprobamos si la foto ha sido ya denunciada
            $consulta2 = "SELECT did FROM w_denuncias WHERE obj_id = :did AND d_user = :uid AND d_type = :type";
            $valores2 = array(
               'did' => $did,
               'uid' => $psUser->user_id,
               'type' => 4
            );
            $denuncia = $psDb->db_execute($consulta2, $valores2, 'rowCount');
            if(!empty($denuncia)){
               return '0: Ya has denunciado esta foto. Deber&aacute;s esperar a que un admin o moderador lo compruebe para poder realizar otra denuncia.';
            }
            //comprobamos si ya lleva más de 2 denuncias
            $consulta3 = "SELECT did FROM w_denuncias WHERE obj_id = :did";
            $valores3 = array('did' => $did);
            $denuncias = $psDb->db_execute($consulta3, $valores3, 'rowCount');
            if($denuncias > 2){
               //actualizamos los datos en la db
               $consulta4 = "UPDATE f_fotos SET f_status = :status WHERE foto_id = :fid";
               $consulta5 = "UPDATE w_stats SET stats_fotos = :foto WHERE stats_no = :no";
               $valores4 = array(
                  'status' => 1,
                  'fid' => $did
               );
               $valores5 = array(
                  'foto' => 'stats_fotos'-1,
                  'no' => 1
               );
               $psDb->db_execute($consulta4, $valores4);
               $psDb->db_execute($consulta5, $valores5);
            }
            //ahora insertamos la nueva denuncia
            $consulta6 = "INSERT INTO w_denuncias (obj_id, d_user, d_razon, d_extra, d_type, d_date) VALUES (:did, :user, :razon, :extra, :type, :dates)";
            $valores6 = array(
                  'did' => $did,
                  'user' => $psUser->user_id,
                  'razon' => $razon,
                  'extra' => $extras,
                  'type' => 4,
                  'dates' => $date,
            );
            if($psDb->db_execute($consulta6, $valores6)){
               return '1: La denuncia de la foto fue enviada correctamente.';
            }else{
               return '0: Error al realizar la denuncia. Por favor, int&eacute;ntelo de nuevo m&aacute;s tarde.';
            }
            break;
      }
    }  
}