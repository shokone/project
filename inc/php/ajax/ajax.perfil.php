<?php
//si la constante PS_HEADER no esta definida no se accede al script
if (!defined('PS_HEADER')) {
    exit("No se permite el acceso al script");
}

/**
 * Controlador ajax para el perfil
 *
 * @name ajax.perfil.php
 * @author Iván Martínez Tutor
 */

//niveles de acceso y plantilla de cada acción
$niveles = array(
	'perfil-wall' => array('n' => 0, 'p' => 'wall'),
    'perfil-actividad' => array('n' => 0, 'p' => 'actividad'),
	'perfil-info' => array('n' => 0, 'p' => 'info'),
    'perfil-posts' => array('n' => 0, 'p' => 'posts'),
    'perfil-seguidores' => array('n' => 0, 'p' => 'follows'),
    'perfil-siguiendo' => array('n' => 0, 'p' => 'follows'),
    'perfil-medallas' => array('n' => 0, 'p' => 'medallas'),
);

//variables locales
$psPage = 'php_files/p.perfil.' . $niveles[$action]['p'];
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
include("../class/c.cuenta.php");
$psCuenta =& psCuenta::getInstance();
// USER ID
$user_id = (int)$_POST['pid'];
if(empty($user_id)){
	die('0: El campo <b>user_id</b> es obligatorio.');
}
$username = $psUser->getUserName($user_id);
$smarty->assign("psUsername",$username);
switch($action){
    case 'perfil-wall':
    	//obtenemos los datos del muro del usuario
    	//añadimos la clase muro
        include("../class/c.muro.php");
        $psMuro =& psMuro::getInstance();
        // GENERAL
    	$psGeneral = $psCuenta->cargarInfoGeneral($user_id);
    	$smarty->assign("psGeneral", $psGeneral);
        //obtenemos los datos de privacidad del usuario
        $privacidad = $psMuro->getPrivacidad($user_id, $username, $psCuenta->siguiendo($user_id));
        if($priv['m']['v'] == true){
            $smarty->assign("psMuro", $psMuro->getMuro($user_id));
            $psInfo = array('uid' => $user_id, 'nick' => $username);
            $smarty->assign("psInfo", $psInfo);
        }
        $smarty->assign("psPrivacidad",$privacidad);
        break;
    case 'perfil-actividad':
    	//obtenemos los datos de las actividades del usuario
    	//obtenemos la acción
        $ac_do = $_POST['do'];
        //obtenemos el tipo de acción
        $ac_type = empty($_POST['ac_type']) ? 0 : (int)$_POST['ac_type'];
        //obtenemos el principio de la acción
        $start = empty($_POST['start']) ? 0 : (int)$_POST['start'];
        if($ac_do != 'borrar'){
            $actividad = $psActividad->getActividad($user_id, $ac_type, $start);
            $smarty->assign("psActividad", $actividad);
            $smarty->assign("psDo", $ac_do);
            $smarty->assign("psUserID", $user_id);
        }else{
            echo $psActividad->borrarActividad();
            die;
        }
        break;
	case 'perfil-info':
		//obtenemos la información del perfil del usuario
		//obtenemos el array con los textos
        include('../ext/datos.php');
		//información del perfil del usuario
        $psPerfil = $psCuenta->cargarPerfil($user_id);
		$smarty->assign("psPerfil",$psPerfil);
        //país del usuario
        $smarty->assign("psPais",$psPaises[$psPerfil['user_pais']]);
        //comprobamos si los gustos del usuario están vacíos
        $count = 0;
        foreach($psPerfil['p_gustos'] as $key => $valor){
            if(empty($valor)){
            	$count++;
            }
        }
        $psGustos = ($count > 0) ? 'hide': 'show';
        $smarty->assign("psGustos", $psGustos);
		//datos del perfil
		$smarty->assign("psPData", $psPerfilDatos);
		break;
    case 'perfil-posts':
    	//obtenemos los post del usuario
        $smarty->assign("psGeneral", $psCuenta->cargarPosts($user_id));
        break;
    case 'perfil-seguidores':
    	//obtenemos los seguidores del usuario
        $smarty->assign("psType", 'seguidores');
        $smarty->assign("psHide", $_GET['hide']);
        $smarty->assign("psData", $psMonitor->getFollows('seguidores', $user_id));
        break;
    case 'perfil-siguiendo':
    	//obtenemos los usuarios a los que sigue el usuario
        $smarty->assign("tsType",'siguiendo');
        $smarty->assign("tsHide",$_GET['hide']);
        $smarty->assign("tsData",$psMonitor->getFollows('siguiendo', $user_id));
        break;
    case 'perfil-medallas':
    	//obtenemos las medallas del usuario
        $smarty->assign("psMedallas", $psCuenta->cargarMedallas($user_id));
        break;
    default:
        die('0: Ocurri&oacute; un error, esto no existe!');
        break;
}
