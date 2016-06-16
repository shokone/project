<?php
//si la constante PS_HEADER no esta definida no se accede al script
if (!defined('PS_HEADER')) {
    exit("No se permite el acceso al script");
}

/**
 * Controlador ajax para las notificaciones
 *
 * @name ajax.notificaciones.php
 * @author Iván Martínez Tutor
 */

//niveles de acceso y plantilla de cada acción
$niveles = array(
	'notificaciones-ajax' => array('n' => 2, 'p' => 'ajax'),
    'notificaciones-filtro' => array('n' => 2, 'p' => ''),
);

//variables locales
$psPage = 'php_files/p.notificaciones.' . $niveles[$action]['p'];
$psLevel = $niveles[$action]['n'];
$psAjax = empty($niveles[$action]['p']) ? 1 : 0;

//comprobamos el nivel de acceso del usuario
$mensaje = $psCore->setLevel($psLevel, true);
if($mensaje != 1){
	echo $mensaje['mensaje'];
	die;
}
//obtenemos las clases necesarias del archivo header.php
//obtenemos una segunda acción del monitor para especificar la notificación
$action2 = filter_input(INPUT_POST, 'action');
switch($action){
	case 'notificaciones-ajax':
		$psAjax = 1;//activamos ajax
		//ahora obtenemos la acción específica
		switch($action2){
			case 'follow':
				echo $psMonitor->setSeguir();
				break;
			case 'unfollow':
				echo $psMonitor->setDejarSeguir();
				break;
			case 'last':
				//en este caso desactivamos ajax
				$psAjax = 0;
				//obtenemos las últimas notificaciones y las asignamos a smarty
				$notificaciones = $psMonitor->getNotificaciones();
				$smarty->assign('psDatos', $notificaciones['data']);
				break;
			case 'recomendaciones':
				echo $psMonitor->setRecomendaciones();
				break;
		}
		break;
	case 'notificaciones-filtro':
		echo $psMonitor->setActFiltro();
		break;
}
$_GET['ps'] = true;