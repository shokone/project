<?php
//si la constante PS_HEADER no esta definida no se accede al script
if (!defined('PS_HEADER')) {
    exit("No se permite el acceso al script");
}

/**
 * Controlador ajax para el muro
 *
 * @name ajax.muro.php
 * @author Iván Martínez Tutor
 */

//niveles de acceso y plantilla de cada acción
$niveles = array(
	'muro-stream' => array('n' => 2, 'p' => 'stream'),
    'muro-likes' => array('n' => 2, 'p' => '')
);

//variables locales
$psPage = 'php_files/p.muro.' . $niveles[$action]['p'];
$psLevel = $niveles[$action]['n'];
$psAjax = empty($niveles[$action]['p']) ? 1 : 0;

//comprobamos el nivel de acceso del usuario
$mensaje = $psCore->setLevel($psLevel, true);
if($mensaje != 1){
	echo $mensaje['mensaje'];
	die;
}
//obtenemos las clases necesarias del archivo header.php
// CLASS
include("../class/c.muro.php");
$psMuro =& psMuro::getInstance();

switch($action){
    case 'muro-likes':
        if(empty($_GET['do'])){
            echo $psCore->setJson($psMuro->meGustaPub());
        }else{
            echo $psCore->setJson($psMuro->mostrarLikes());
        }
        break;
    case 'muro-stream':
        $do = $_GET['do'];
        if($do == 'check'){
            echo $psMuro->comprobarEnlaces();
            $psAjax = 1;
        }elseif($do == 'post'){
            $psStream = $psMuro->pubMuro();
            if(!is_array($psStream) && substr($psStream, 0, 1) == '0') {
                echo $psStream;
                $psAjax = 1;
            }else{
                $psWall['data'][1] = $psStream;
                $smarty->assign("psMuro", $psWall);
                $psPrivacidad['mf']['v'] = true;
                $smarty->assign("psPrivacidad", $psPrivacidad);  
            } 
        }elseif($do == 'repost'){
            $psPage = 'php_files/p.muro.stream.comments';
            $psRepub = $psMuro->rePubMuro();
            if(!is_array($psRepub)){
                echo $psRepub;
                $psAjax = 1;   
            }else{
                $psComments['data'][1] = $psRepub;
                $smarty->assign("psComments", $psComments);  
            } 
        }elseif($do == 'more'){
            //añadimos la clase cuenta
            include("../class/c.cuenta.php");
            $psCuenta =& tsCuenta::getInstance();
            // VARIABLES
            $user_id = $_POST['pid'];
            $start = $_POST['start'];
            //obtenemos la privacidad del usuario
            $privacidad = $psMuro->getPrivacidad($user_id, 'null', $psCuenta->siguiendo($user_id));
            $smarty->assign("psPrivacidad",$privacidad);
            if($_GET['type'] == 'wall'){
                $psStream = $psMuro->getMuro($user_id, $start);
            }else if($_GET['type'] == 'news'){
                $psStream = $psMuro->getNovedades($start);
            }
            //comprobamos y asignamos datos a smarty
            if(!is_array($psStream)) {
                echo $psStream;
                $psAjax = 1;   
            }else{
                $smarty->assign("psMuro", $psStream);            
            }  
        }elseif($do == 'more_comments'){
            $psPage = 'php_files/p.muro.stream.comments'; 
            $psComments = $psMuro->getComentarios();
            if(!is_array($psComments)) {
                echo $psComments;
                $psAjax = 1;
            }else{
                $smarty->assign("psComments",$psComments);
            }
        }elseif($do == 'delete'){
            echo $psMuro->borrarPubMuro();
            $psAjax = 1;
        }
        break;
    default:
        die('0: Este archivo no existe.');
        break;
}