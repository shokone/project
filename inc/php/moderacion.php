<?php

/**
 * controlador de la moderacion
 * @requisitos:
 * cargamos los datos necesarios para ejecutar la seccion de moderacion
 * verificamos el nivel de acceso a la pagina
 * establecemos las variables importantes al archivo
 * asignamos las tareas necesarias que se realizaran en la seccion moderación
 * asignamos el valor de las variables a smarty
 *
 * @name moderacion.php
 * @author Iván Martínez Tutor
 */

/**
 * definimos las variables importantes al archivo
 */
//plantilla para mostrar con el archivo
$psPage = "moderacion";
//nivel de acceso a la página
$psLevel = 3;
//comprobamos si la respuesta se realiza por ajax
$psAjax = empty($_GET['ajax']) ? 0 : 1;
//creamos el booleano para comprobar si debemos continuar con el script
$psContinue = true;
//damos un nombre al titulo de la pagina
$psTitle = $psCore->settings['titulo'].' - '.$psCore->settings['slogan'];

include '../../header.php';

/**
 * validamos el nivel y los permisos de acceso
 */
$psLevelVer = $psCore->setLevel($psLevel, true);
if($psLevelVer != 1){
    $psPage = "aviso";
    $psAjax = 0;
    $smarty->assign("psAviso",$psLevelVer);
    $psContinue = false;
}

/**
 * establecemos las variables principales
 * solo si el script puede continuar
 */
if($psContinue){
	$action = htmlspecialchars($_GET['action']);
	$action2 = htmlspecialchars($_GET['act']);
	include '../class/c.moderacion.php';
	$psModeracion =& psModeracion::getInstance();

	if($action == ''){
		$smarty->assign("psModeraciones",$psModeracion->getModeradores());
	}elseif($action == 'posts' || $action == 'users' ||  $action == 'mps' ||  $action == 'fotos' ){
        //cargamos los textos de las denuncias
        include '../ext/datos.php';
		if(empty($action2)){
		  $smarty->assign("psReportes", $psModeracion->getDenuncias($action));
          $smarty->assign("psDenuncias", $psDenuncias[$action]);
		}elseif($action2 == 'info'){
          $smarty->assign("psDenuncia", $psModeracion->getDenuncia($action));
          $smarty->assign("psDenuncias", $psDenuncias[$action]);
		}
	}
    //usuarios baneados
    elseif($action == 'banusers'){
        $smarty->assign("psSuspendidos", $psModeracion->getUserSuspendidos());
    }
	//papelera de reciclaje de post
	elseif($action == 'pospelera'){
        $smarty->assign("psPostPapelera", $psModeracion->getPostPapelera());
    }
	elseif($action == 'fopelera'){//papelera de reciclaje de fotos
        $smarty->assign("psFotoPapelera", $psModeracion->getFotoPapelera());
    }
	//moderar comentarios
	elseif($action == 'comentarios'){
        $smarty->assign("psComentarios", $psModeracion->getModerarComentarios());
    }//moderar posts
	elseif($action == 'revposts'){
        $smarty->assign("psPosts", $psModeracion->getModerarPost());
    }
	//buscador de ip y de contenido en la web
    elseif($action == 'buscador'){
		if(!$action2){
			if($_POST['buscar']){
				$texto = filter_input(INPUT_POST, 'texto');
				$metodo = filter_input(INPUT_POST, 'm');
				$tipo = filter_input(INPUT_POST, 't');
				$psCore->redirectTo($psCore->settings['url'].'/moderacion/buscador/'.$metodo.'/'.$tipo.'/'.$texto);
			}
		}elseif($action2 == 'search'){
			if($_POST['buscar']){
				$texto = filter_input(INPUT_POST, 'texto');
				$metodo = filter_input(INPUT_POST, 'm');
				$tipo = filter_input(INPUT_POST, 't');
				$psCore->redirectTo($psCore->settings['url'].'/moderacion/buscador/'.$metodo.'/'.$tipo.'/'.$texto);
			}
	        $smarty->assign("psContenido", $psModeracion->getContenido());
		}
	}

	$smarty->assign("psAction", $action);
	$smarty->assign("psAction2", $action2);
}

//si todo ok y no vamos por ajax asignamos en smarty
if(empty($psAjax)){
    $smarty->assign('psTitle', $psTitle);
    include(PS_ROOT.'/footer.php');
}
