<?php
//si la constante PS_HEADER no esta definida no se accede al script
if (!defined('PS_HEADER')) {
    exit("No se permite el acceso al script");
}

/**
 * Controlador ajax para los afiliados al portal
 *
 * @name ajax.afiliados.php
 * @author Iván Martínez Tutor
 */

//niveles de acceso y plantillas para cada acción
$niveles = array(
	'afiliado-nuevo' => array('n' => 0, 'p' => ''),
	'afiliado-borrar' => array('n' => 0, 'p' => ''),
	'afiliado-setaction' => array('n' => 0, 'p' => ''),
    'afiliado-url' => array('n' => 0, 'p' => ''),
    'afiliado-detalles' => array('n' => 0, 'p' => 'detalles'),
	'afiliado-editar' => array('n' => 0, 'p' => ''),
);
$psPage = 'php_files/p.afiliados.' . $niveles[$action]['p'];
$psLevel = $niveles[$action]['n'];
$psAjax = empty($niveles[$action]['p']) ? 1 : 0;

//comprobamos el nivel de acceso del usuario
$mensaje = $psCore->setLevel($psLevel, true);
if($mensaje != 1){
	echo $mensaje['mensaje'];
	die;
}

//obtenemos las clases necesarias
include '../class/c.afiliados.php';
$psAfiliado =& psAfiliado::getInstance();
//realizamos la acción según el tipo
switch($action){
	case 'afiliado-nuevo':
        echo $psAfiliado->newAfiliado();
		break;
	case 'afiliado-borrar':
		$id = filter_input(INPUT_POST, 'afid');
        echo $psAfiliado->delAfiliado($id);
	break;
	case 'afiliado-editar':
		$id = filter_input(INPUT_POST, 'a_id');
		$name = filter_input(INPUT_POST, 'a_name');
		$url = filter_input(INPUT_POST, 'a_url');
		$banner = filter_input(INPUT_POST, 'a_banner');
		$desc = filter_input(INPUT_POST, 'a_descripcion');
        echo $psAfiliado->editAfiliado($id, $name, $url, $banner, $desc);
		break;
	case 'afiliado-setactive':
        echo $psAfiliado->setAccionAfiliado();
		break;
	case 'afiliado-url':
        echo $psAfiliado->urlExterna();
		break;
	case 'afiliado-detalles':
        $smarty->assign("psAfiliado",$psAfiliado->getAfiliado());
	break;
    default:
        die('0: Este archivo no existe.');
    	break;
}