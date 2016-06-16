<?php
//si la constante PS_HEADER no esta definida no se accede al script
if (!defined('PS_HEADER')) {
    exit("No se permite el acceso al script");
}

/**
 * Controlador ajax para la moderación
 *
 * @name ajax.moderación.php
 * @author Iván Martínez Tutor
 */

//niveles de acceso y plantilla de cada acción
$niveles = array(
	'moderacion-posts' => array('n' => 3, 'p' => 'main'),
	'moderacion-fotos' => array('n' => 3, 'p' => 'main'),
    'moderacion-users' => array('n' => 3, 'p' => 'main'),
	'moderacion-mps' => array('n' => 3, 'p' => 'main'),
);

//variables locales
$psPage = 'php_files/p.moderacion.' . $niveles[$action]['p'];
$psLevel = $niveles[$action]['n'];
$psAjax = empty($niveles[$action]['p']) ? 1 : 0;

//comprobamos el nivel de acceso del usuario
$mensaje = $psCore->setLevel($psLevel, true);
if($mensaje != 1){
	echo $mensaje['mensaje'];
	die;
}
//cargamos la clase
require('../class/c.moderacion.php');
$psModeracion =& psModeracion::getInstance();
//obtenemos la acción
$do = htmlspecialchars($_GET['do']);
// comprobamos la acción a realizar
switch($action){
	//moderación de post
	case 'moderacion-posts':
        $pid = (int)$_POST['postid'];
        //acciones secundarias
        switch($do){
            case 'view':
                $psPage = 'php_files/p.posts.preview';
                $preview = $psModeracion->getVistaPrevia($pid);
                $smarty->assign("psPreview", $preview);
            	break;
			case 'ocultar':
                $psAjax = 1;
                echo $psModeracion->ocultarPost($_POST['pid'], $_POST['razon']);
            	break;
            case 'reboot':
                $psAjax = 1;
                echo $psModeracion->rebootPost($_POST['id']);
            	break;
            case 'borrar':
                if($_POST['razon']){
                    $psAjax = 1;
                    echo $psModeracion->borrarPost($pid);
                }else {
                    include("../extra/datos.php");
                    $psPage = 'php_files/p.posts.mod';
                    $smarty->assign("psDenuncias", $psDenuncias['posts']);   
                }
            	break;
            case 'sticky':
                $psAjax = 1;
                echo $psModeracion->setSticky($_POST['id']);
            	break;
			case 'openclosed':
                $psAjax = 1;
                echo $psModeracion->setOpenClosedPost($_POST['id']);
            	break;
        }
		break;
	//moderación de usuarios
	case 'moderacion-users':
        $user_id = $_POST['uid'];
        $username = $psUser->getUserName($user_id);
        //acciones secundarias
        switch($do){
            case 'aviso':
                if($_POST['av_body']){
                    $psAjax = 1;
                    $aviso = $_POST['av_body']."\n\n".'Staff: <a href="#" class="hovercard" uid="'.$psUser->user_id.'">'.$psUser->nick.'</a>';
                    $aviso_resp = $psMonitor->setAviso($user_id, $_POST['av_subject'], $aviso, $_POST['av_type']);
                    if(!$aviso_resp) {
                    	echo '0: Error al enviar el aviso a <b>'.$username.'</b>.';
                    }else {
                    	echo '1: El aviso fue enviado con &eacute;xito a <b>'.$username.'</b>.';
                    }
                } else {
                	$smarty->assign("psUsername", $psUser->getUserName($user_id));
                }
            	break;
            case 'ban':
                if($_POST['b_causa']){
                    $psAjax = 1;
                    echo $psModeracion->banearUser($user_id);
                }  else {
                	$smarty->assign("psUsername", $psUser->getUserName($user_id));
                }
            	break;
            case 'unban':
                $psAjax = 1;
                echo $psModeracion->rebootUser($_POST['id'], 'unban');
            	break;
            case 'reboot':
                $psAjax = 1;
                echo $psModeracion->rebootUser($_POST['id'], 'reboot');
            	break;
        }
        $smarty->assign("psDo", $do);
		break;
	//moderación de mensajes privados
	case 'moderacion-mps':
        $mid = $_POST['mpid'];
        //acciones secundarias
        switch($do){
            case 'reboot':
                $psAjax = 1;
                echo $psModeracion->rebootMensajesPrivados($_POST['id']);
            	break;
            case 'borrar':
                $psAjax = 1;
                echo $psModeracion->borrarMensajePrivado($mid);
            	break;
        }
		break;
	//moderación de fotos
	case 'moderacion-fotos':
        $fid = (int)$_POST['fid'];
        //acciones secundarias
        switch($do){
            case 'reboot':
                    $psAjax = 1;
                    echo $psModeracion->rebootFoto($_POST['id']);
            	break;
            case 'borrar':
				if($_POST['razon']){
                    $psAjax = 1;
                    echo $psModeracion->borrarFoto($fid);
                }else {
                    include('../extra/datos.php');
                    $psPage = 'php_files/p.fotos.mod';
                    $smarty->assign("psDenuncias", $psDenuncias['fotos']);   
                }
            	break;
        }
		break;
}
