<?php

/**
 * controlador de la admin
 * @requisitos:
 * agregamos las variables por defecto de la pagina
 * verificaremos el nivel de acceso a la pagina
 * añadimos las instrucciones de codigo necesarias
 * agregamos los datos generados a smarty
 * 
 * @name admin.php
 * @author Iván Martínez Tutor
 */

/**
 * variables por defecto del archivo
 */
//plantilla para mostrar con el archivo
$psPage = "admin";

//nivel de acceso a la pagina
$psLevel = 4;

//comprobamos si la respuesta se realiza por ajax
$psAjax = empty($_GET['ajax']) ? 0 : 1;

//creamos la variable para comprobar si continuamos con el script
$psContinue = true;

//incluimos el header
include("../../header.php");

//cargamos el titulo de la pagina actual
$psTitle = 'Administraci&oacute;n de '.$psCore->settings['titulo']." - ".$psCore->settigns['slogan'];

//verificamos el nivel de acceso configurado previamente
$psLevelVer = $psCore->setLevel($psLevel, true);
if($psLevelVer != 1){
    $psPage = "aviso";
    $psAjax = 0;
    $smarty->assign('psAviso',$psLevelVer);
    //no se puede continuar con el script
    $psContinue = false;
}

/**
 * establecemos las variables principales
 * solo si el script puede continuar
 */
if($psContinue){
	// acción
	$action = htmlspecialchars($_GET['action']);
	//segunda acción
	$act = htmlspecialchars($_GET['act']);
	//obtenemos la clase admin
	include("../class/c.admin.php");
	$psAdmin =& psAdmin::getInstance();

	//comprobamos los datos y realizamos la acción que toque
	if($action == ''){
		//estadísticas y administradores
		$smarty->assign("psAdmins",$psAdmin->getAdmins());
        $smarty->assign("psInst",$psAdmin->getStatsIns());
	}elseif($action == 'creditos'){
		//créditos del script y versión
		$smarty->assign('psVersion', $psAdmin->getVersiones());
	}elseif($action == 'news'){
		//noticias
		if(empty($act)){
			$smarty->assign("psNews", $psAdmin->getNoticias());
        }elseif($act == 'nuevo' && !empty($_POST['not_body'])){
            if($psAdmin->newNoticia()){
            	$psCore->redirectTo($psCore->settings['url'].'/admin/news?save=true');
            }
        } elseif($act == 'editar'){
            if (!empty($_POST['not_body'])) {
                if($psAdmin->editNoticia()){
                	$psCore->redirectTo($psCore->settings['url'].'/admin/news?save=true');
                }
            } else {
            	$smarty->assign("psNew",$psAdmin->getNoticia());
        	}
        }elseif($act == 'borrar'){
			if($psAdmin->delNoticia()){
				$psCore->redirectTo($psCore->settings['url'].'/admin/news?borrar=true');
			}
		}
	}elseif($action == 'ads'){
		//publicidad
		if(!empty($_POST['save'])){
			if($psAdmin->savePublicidad()){
				$psCore->redirectTo($psCore->settings['url'].'/admin/ads?save=true');
			}
		}
	}elseif($action == 'posts'){
		//posts
		if(!$act) {
			$smarty->assign("psAdminPosts", $psAdmin->getPostsAdmin());
		}
	}elseif($action == 'fotos'){
		//fotos
		if(!$act) {
			$smarty->assign("psAdminFotos", $psAdmin->getFotosAdmin());
		}
	}elseif($action == 'stats'){
		//estadísticas del sitio
		$smarty->assign("psAdminStats", $psAdmin->getStatsAdmin());
	}elseif($action == 'temas'){
		//temas del site
		//ver los temas
		if(empty($act)){
			$smarty->assign("psTemas", $psAdmin->getThemes());
		}elseif($act == 'editar'){
			//editar tema
			if(!empty($_POST['save'])){
				if($psAdmin->saveTheme){
					$psCore->redirectTo($psCore->settings['url'].'/admin/temas?save=true');
				}
			}else{
				$smarty->assign("psTema", $psAdmin->getTheme());
			}
		}elseif($act == 'usar'){
			//utilizar tema por defecto
			if(!empty($_POST['confirm'])) {
				if($psAdmin->changeTheme()){
					$psCore->redirectTo($psCore->settings['url'].'/admin/temas?save=true');
				}
			}
			$smarty->assign("theme_title", $_GET['tt']);
		}elseif($act == 'nuevo'){
			//añadimos un nuevo tema
			if(!empty($_POST['path'])) {
				$nuevo = $psAdmin->newTheme();
				if($nuevo == 1){
					$psCore->redirectTo($psCore->settings['url'].'/admin/temas?save=true');
				}else{
					$smarty->assign("psError", $nuevo);
				}
			}
		}elseif($act == 'borrar'){
			//borramos el tema
			if(!empty($_POST['confirm'])) {
				if($psAdmin->delTheme()){
					$psCore->redirectTo($psCore->settings['url'].'/admin/temas?save=true');
				}
			}
			$smarty->assign("theme_title",$_GET['tt']);
		}
	}elseif($action == 'nicks'){
		//cambios de nombre de los usuarios
		if(!$act) {
			//lista de peticiones de cambios de nick
			$smarty->assign("psAdminNicks",$psAdmin->getChangeNick());
		}elseif($act == 'realizados'){
			//lista de cambios de nick realizados
			$smarty->assign("psAdminNicks",$psAdmin->getChangeNickAll());
		}
	}elseif($action == 'sesiones'){
		//usuarios online
		if(!$act) {
			$smarty->assign("psAdminSesions",$psAdmin->getSesions());
		}
	}elseif($action == 'blacklist'){
		//lista negra de usuarios
		if(!$act) {
			$smarty->assign("psBlackList", $psAdmin->getBlackList());
		}elseif($act == 'editar'){
			if($_POST['edit']){
		    	$guardar = $psAdmin->saveBlockUser();
				if($guardar == 1){
					$psCore->redirectTo($psCore->settings['url'].'/admin/blacklist?save=true');
				}else{
					$smarty->assign("psError", $guardar); 
					$smarty->assign("psBlock", array('value' => $_POST['value'], 'type' => $_POST['type']));
				}
			}else{
				$smarty->assign("psBlock",$psAdmin->getBlockUser());
			}
		}elseif($act == 'nuevo'){
			if($_POST['new']){
			    $nuevo = $psAdmin->newBlockUser();
				if($nuevo == 1){
					$psCore->redirectTo($psCore->settings['url'].'/admin/blacklist?save=true');
				}else{
					$smarty->assign("psError", $nuevo); 
					$smarty->assign("psBlock", array('value' => $_POST['value'], 'type' => $_POST['type'], 'reason' => $_POST['reason']));
				}
			}
		}
	}elseif($action == 'badwords'){
		//lista negra de palabras
		if(!$act) {
			$smarty->assign("psBadWords",$psAdmin->getBadWords());
		}elseif($act == 'editar'){
			if($_POST['edit']){
				$editar = $psAdmin->guardarBadWord();
				if($editar == 1){
					$psCore->redirectTo($psCore->settings['url'].'/admin/badwords?save=true');
				}else{
					$smarty->assign("psError",$editar); 
					$smarty->assign("psBadWord", array(
						'word' => $_POST['before'], 
						'swop' => $_POST['after'], 
						'method' => $_POST['method'], 
						'type' => $_POST['type'])
					);
				}
			}else{
				$smarty->assign("psBadWord",$psAdmin->getBadWord());
			}
		}elseif($act == 'nuevo'){
			if($_POST['new']){
				$nuevo = $psAdmin->newBadWord();
				if($nuevo == 1){
					$psCore->redirectTo($psCore->settings['url'].'/admin/badwords?save=true');
				}else{
					$smarty->assign("psError",$nuevo); 
					$smarty->assign("psBadWord", array(
						'word' => filter_input(INPUT_POST, 'before'), 
						'swop' => filter_input(INPUT_POST, 'after'), 
						'method' => filter_input(INPUT_POST, 'method'), 
						'type' => filter_input(INPUT_POST, 'type'), 
						'reason' => filter_input(INPUT_POST, 'reason')
					));
				}
			}
		}
	}elseif($action == 'users'){
		//usuarios
		if(empty($act)){
	       $smarty->assign("psMiembros", $psAdmin->getUsers());
	    }elseif($act == 'show'){
	       $do = $_GET['t'];
           $uid = $_GET['uid'];
           //comprobamos que hacer
           switch($do){
				case 5:
					//privacidad del usuario
	        	    if(!empty($_POST['save'])){
	        	        $update = $psAdmin->setUserPrivacidad($uid);
	        	        if($update == true){
	        	        	$psCore->redirectTo($psCore->settings['url'].'/admin/users?act=show&uid='.$uid.'&save=true');
	                    }else{
	                    	$smarty->assign("psError", $update);
	                    }
                    }
					include('../extra/datos.php');
                    $smarty->assign("psPerfil", $psAdmin->getUserPrivacidad());
					$smarty->assign("psPrivacidad", $psPrivacidad);
                	break;
                case 6:
                	//borrar contenido del usuario
        	       	if(!empty($_POST['save'])){
        	           	$delete = $psAdmin->deleteUserContent($uid);
        	           	if($delete == true){
        	           		$psCore->redirectTo($psCore->settings['url'].'/admin/users?act=show&uid='.$uid.'&save=true');
                       	}else{
                       		$smarty->assign("psError", $delete);
                       	}
                    }
					include('../extra/datos.php');
                    $smarty->assign("psPerfil", $psAdmin->getUserPrivacidad());
					$smarty->assign("psPrivacidad", $psPrivacidad);
                	break;
                case 7:
                	//cambiar rango del usuario
        	       if(!empty($_POST['save'])){
        	           	$update = $psAdmin->setUserRango($uid);
        	           	if($update == true){
        	           		$psCore->redirectTo($psCore->settings['url'].'/admin/users?act=show&uid='.$uid.'&save=true');
                       	}else{
                       		$smarty->assign("psError", $update);
                       	}
                    }
                    $smarty->assign("psUserRango", $psAdmin->getUserRango($uid));
                	break;
				case 8:
					//firma del usuario 
        	       if(!empty($_POST['save'])){
        	            $update = $psAdmin->serUserFirma($uid);
        	            if($update == true){
        	           		$psCore->redirectTo($psCore->settings['url'].'/admin/users?act=show&uid='.$uid.'&save=true');
                        }else{
                        	$smarty->assign("psError", $update);
                        }
                    }
					$smarty->assign("psUserFirma", $psAdmin->getUserDatos());
                	break;
                default:
                	//datos del usuario
                    if(!empty($_POST['save'])){
        	           	$update = $psAdmin->setUserDatos($uid);
        	           	if($update == true){
        	           		$psCore->redirectTo($psCore->settings['url'].'/admin/users?act=show&uid='.$uid.'&save=true');
                       	}else{
                       		$smarty->assign("psError", $update);
                       	}
                    }
    	           $smarty->assign("psUserDatos", $psAdmin->getUserDatos());
                break;
           }
           //asignamos los datos
           $smarty->assign("psType", $_GET['t']);
           $smarty->assign("psUserId", $uid);
           $smarty->assign("psUsername", $psUser->getUserName($user_id));
	   }
	}elseif($action == 'rangos'){
		//rangos
		//obtenemos un listado de los rangos
		if(empty($act)){
			$smarty->assign("psRangos", $psAdmin->getAllRangos());
		}elseif($act == 'list'){
			//listamos los usuarios del rango seleccionado
			$smarty->assign("psMiembros", $psAdmin->getRangoUsers());
		}elseif($act == 'nuevo'){
			//creamos un nuevo rango
			if(!empty($_POST['save'])){
				$save = $psAdmin->newRango();
				if($save == 1){
					$psCore->redirectTo($psCore->settings['url'].'/admin/rangos?save=true');
				}else {
					$smarty->assign("psError", $save); 
					$smarty->assign("psIconos", $psAdmin->getIconosExtra('ran'));
				}
			} else {
				$smarty->assign("psIconos", $psAdmin->getIconosExtra('ran'));
                $smarty->assign("psType", $_GET['t']);
			}
		}elseif($act == 'editar'){
			//editamos un rango ya existente
			if(!empty($_POST['save'])){
				if($psAdmin->guardarRango()){
					$psCore->redirectTo($psCore->settings['url'].'/admin/rangos?save=true');
				}
			} else {
				$smarty->assign("psRango", $psAdmin->getRango());
				$smarty->assign("psIconos", $psAdmin->getIconosExtra('ran'));
                $smarty->assign("psType", $_GET['t']);
			}
		}elseif($act == 'borrar'){
			//eliminamos un rango
			if(empty($_POST['save'])){
				$smarty->assign("psRangos", $psAdmin->getAllRangos());
			}else{
				if($psAdmin->delRango()){
					$psCore->redirectTo($psCore->settings['url'].'/admin/rangos?save=true');
				}
			}
		}elseif($act == 'setdefault'){
			//cambiamos el rango por defecto con un nuevo registro
			if($psAdmin->setRangoDefault()){
				$psCore->redirectTo($psCore->settings['url'].'/admin/rangos?save=true');
			}
		}
	}elseif($action == 'categorias'){
		//categorías
		//ordenar las categorias
		if(!empty($_GET['ordenar'])){
			$psAdmin->ordenCategorias();
		}elseif($act == 'nueva'){
			//creamos una nueva categoría
			if($_POST['save']){
				if($psAdmin->newCategoria()){
					$psCore->redirectTo($psCore->settings['url'].'/admin/cats?save=true');
				}
			} else {
				$smarty->assign("psType", $_GET['t']);
				$smarty->assign("psCid", $_GET['cid']);
				$smarty->assign("psIconos", $psAdmin->getIconosExtra());
			}
		}elseif($act == 'editar'){
			if($_POST['save']){
				//editamos la categoría
				if($psAdmin->guardarCategorias()){
					$psCore->redirectTo($psCore->settings['url'].'/admin/cats?save=true');
				}else{
					$smarty->assign("psType", $_GET['t']);
					$smarty->assign("psCategorias", $psAdmin->getCategorias());
					$smarty->assign("psIconos", $psAdmin->getIconosExtra());
				}
			}
		}elseif($act == 'borrar'){
			if($_POST['save']){
				//borramos la categoría
				if($_GET['t'] == 'cat'){
					$save = $psAdmin->delCategoria();
					if($save == 1){
						$psCore->redirectTo($psCore->settings['url'].'/admin/cats?save=true');
					}else{
						$smarty->assign("psError", $save); 
					}
				}
			}
			$smarty->assign("psType", $_GET['t']);
			$smarty->assign("psCid", $_GET['cid']);
		}elseif($act == 'change'){
			if($_POST['save']){
				//cambiamos el orden de las categorías
				if($psAdmin->moverCategoria()){
					$psCore->redirectTo($psCore->settings['url'].'/admin/cats?save=true');
				}
			}
		}
	}elseif($action == 'pconfigs'){
		//configuraciones del site
		if(!empty($_POST['save'])){
			if($psAdmin->guardarConfig()){
				$psCore->redirectTo($psCore->settings['url'].'/admin/pconfigs?save=true');
			}
		}
	}elseif($action == 'afs'){
		//afiliados
		//incluimos la clase
		include '../class/c.afiliados.php';
		$psAfiliado =& psAfiliado::getInstance();
		//comprobamos la acción a realizar
		if($act == ''){
			$smarty->assign("psAfiliados", $psAfiliado->getAfiliados('admin'));
		}elseif($act == 'editar'){
			if($_POST['editar']){
				if($psAfiliado->editAfiliado()){
					$psCore->redirectTo($psCore->settings['url'].'/admin/afs?act=editar&aid='.$_GET['aid'].'&save=true');
				}
            }
			$smarty->assign("psAfiliado", $psAfiliado->getAfiliado('admin'));
		}
	}elseif($action == 'medallas'){
		//medallas
		//añadimos la clase
		include '../class/c.medallas.php';
		$psMedallas =& psMedallas::getInstance();
		if(empty($act)){
			//obtenemos las medallas
			$smarty->assign("psMedallas", $psMedallas->getMedallas());
		}elseif($act == 'nueva'){
			if($_POST['save']){
				$agregar = $psMedallas->newMedalla();
				if($agregar == 1){
					$psCore->redirectTo($psCore->settings['url'].'/admin/medals?save=true');
				}else{
					$smarty->assign("psError", $agregar); 
					$smarty->assign("psMedalla", array(
						'm_title' => filter_input(INPUT_POST, 'med_title'), 
						'm_description' => filter_input(INPUT_POST, 'med_desc'), 
						'm_image' => filter_input(INPUT_POST, 'med_img'), 
						'm_cant' => filter_input(INPUT_POST, 'med_cant'), 
						'm_type' => filter_input(INPUT_POST, 'med_type'), 
						'm_cond_user' => filter_input(INPUT_POST, 'med_cond_user'), 
						'm_cond_user_rango' => filter_input(INPUT_POST, 'med_cond_user_rango'), 
						'm_cond_post' => filter_input(INPUT_POST, 'med_cond_post'), 
						'm_cond_foto' => filter_input(INPUT_POST, 'med_cond_foto')
						)
					);
				}
            }
			//obtenemos los iconos para las medallas
			$smarty->assign("psIconos",$psAdmin->getIconosExtra('med', 16));
			//obtenemos los rangos
			$smarty->assign("psRangos",$psAdmin->getAllRangos());
		}elseif($act == 'showassign'){
			//mostramos las medallas asignadas
			$smarty->assign("psAsignaciones", $psMedallas->getAssignMedallas());
		}elseif($act == 'editar'){
			//editamos la medalla
			if($_POST['editar']){
                $editar = $psMedallas->editarMedalla();
				if($editar == 1){
					$psCore->redirectTo($psCore->settings['url'].'/admin/medals?act=editar&mid='.$_GET['mid'].'&save=true');
				}else{
					$smarty->assign("psError",$editar); 
					$smarty->assign("psMedalla", array(
						'm_title' => $_POST['med_title'], 
						'm_description' => $_POST['med_desc'], 
						'm_image' => $_POST['med_img'], 
						'm_cant' => $_POST['med_cant'], 
						'm_type' => $_POST['med_type'], 
						'm_cond_user' => $_POST['med_cond_user'], 
						'm_cond_user_rango' => $_POST['med_cond_user_rango'], 
						'm_cond_post' => $_POST['med_cond_post'], 
						'm_cond_foto' => $_POST['med_cond_foto']
						)
					);
				}
            }else{
            	$smarty->assign("psMedalla",$psMedallas->getMedalla());  
        	}
			//obtenemos los iconos para las medallas
			$smarty->assign("psIconos",$psAdmin->getIconosExtra('med', 16));
			//obtenemos los rangos
			$smarty->assign("psRangos",$psAdmin->getAllRangos());
		}
	}

	//asignamos la acción a smarty
	$smarty->assign("psAction", $action);
	$smarty->assign("psAct", $act);
}

//si todo ok y no vamos por ajax asignamos en smarty
if(empty($psAjax)){
    $smarty->assign('psTitle', $psTitle);
    include(PS_ROOT.'/footer.php');
}