<?php
//si la constante PS_HEADER no esta definida no se accede al script
if (!defined('PS_HEADER')) {
    exit("No se permite el acceso al script");
}

/**
 * Controlador ajax para los comentarios de los usuarios
 *
 * @name ajax.comentarios.php
 * @author Iván Martínez Tutor
 */

//niveles de acceso y plantillas para cada acción
$niveles = array(
	'comentario-preview' => array('n' => 2, 'p' => 'preview'),
	'comentario-agregar' => array('n' => 2, 'p' => 'preview'),
    'comentario-editar' => array('n' => 2, 'p' => ''),
	'comentario-borrar' => array('n' => 2, 'p' => ''),
	'comentario-ocultar' => array('n' => 2, 'p' => ''),
	'comentario-votar' => array('n' => 2, 'p' => ''),
	'comentario-ajax' => array('n' => 0, 'p' => 'ajax'),
	'comentario-pages' => array('n' => 0, 'p' => 'pages'),
);
//la variable $action la obtenemos del archivo php
$psPage = 'php_files/p.comentarios.' . $niveles[$action]['p'];
$psLevel = $niveles[$action]['n'];
$psAjax = empty($niveles[$action]['p']) ? 1 : 0;

//comprobamos el nivel de acceso del usuario
$mensaje = $psCore->setLevel($psLevel, true);
if($mensaje != 1){
	echo $mensaje['mensaje'];
	die;
}
//obtenemos las clases necesarias
$do = $_GET['do'];
require('../class/c.posts.php');
$psPosts =& psPosts::getInstance();
if($do == 'fotos'){
    require('../class/c.fotos.php');
    $psFotos =& psFotos::getInstance();
}
//realizamos la acción
switch($action){
	case 'comentario-preview':
        $comentario = filter_input(INPUT_POST, 'comentario');
		$comentario = substr($comentario, 0, 1000);
        $text = preg_replace('# +#', "", $comentario);
        if(empty($text)){
        	die('0: El campo <b>Comentario</b> es requerido para esta operaci&oacute;n');
        }
		$auser = filter_input(INPUT_POST, 'auser');
		$preview = array(0, $comentario,'', time(), $auser, $comentario, $_SERVER['REMOTE_ADDR']);
		$smarty->assign("psComentario", $preview);
        $smarty->assign("tpType", $_GET['type']);
		break;
	case 'comentario-agregar':
        if(empty($do)){
			$psComentario = $psPosts->nuevoComentario();
            $smarty->assign("psType", 'new');
			if(is_array($psComentario)){
				$smarty->assign("psComentario", $psComentario);
			}else{
				die($psComentario);
			}
        } elseif($do == 'fotos'){
           $psComentario = $tpFotos->nuevoComentario();
			if(is_array($psComentario)){
				$smarty->assign("psComentario", $psComentario);
			}else{
				die($psComentario);
			}
            $psPage = 'php_files/p.comentario.fotos';
        }
		break;
	case 'comentario-editar':
        echo $psPosts->editarComentario();
		break;
	case 'comentario-borrar':
        if(empty($do)){
            echo $psPosts->borrarComentario();
        } elseif($do == 'fotos'){
            echo $psFotos->borrarComentario();
        }
		break;
	case 'comentario-ocultar':
        echo $psPosts->ocultarComentario();
		break;
	case 'comentario-votar':
        if(empty($do)){
            echo $psPosts->votarComentario();
        } elseif($do == 'fotos'){
            echo $psFotos->votarFoto();
        }
		break;
	case 'comentario-ajax':
		$psPost = filter_input(INPUT_POST, 'postid');
		$psAutor = filter_input(INPUT_POST, 'autor');
		$psComentarios = $psPosts->getComentarios($psPost);
		$psComentarios = array(
			'num' => $psComentarios['num'], 
			'data' => $psComentarios['data'], 
			'block' => $psComentarios['block'], 
			'autor' => $psAutor
		);
		$smarty->assign("psComentarios", $psComentarios);	
        $smarty->assign("psPost", array('postid' => $psPost, 'autor' => $psAutor));
		break;
	case 'comentario-pages':
        $_GET['ts'] = true;
		$total = filter_input(INPUT_POST, 'total']);
		$psPages = $psCore->getPages($total, $psCore->settings['c_max_com']);
		$psPages['post_id'] = filter_input(INPUT_POST, 'postid');
		$psPages['autor'] = filter_input(INPUT_POST, 'autor');
		$smarty->assign("psPages", $psPages);
		break;
}