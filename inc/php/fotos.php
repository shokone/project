<?php

/**
 * controlador de tops
 * @requisitos:
 * agregamos las variables por defecto de la pagina
 * verificaremos el nivel de acceso a la pagina
 * añadimos las instrucciones de codigo necesarias
 * agregamos los datos generados a smarty
 * 
 * @name fotos.php
 * @author Iván Martínez Tutor
 */

//primero creamos las variables por defecto
$psPage = "fotos";//plantilla a mostrar con este archivo
$psLevel = 2;//nivel de acceso
$psAjax = empty($_GET['ajax']) ? 0 : 1;//comprobamos si la respuesta se realiza por ajax
$psContinue = true;//comprobamos si continuamos ejecutando el script
//incluimos el header
include ('../../header.php');
$psTitle = $psCore->settings['titulo'] . " - " . $psCore->settings['slogan'];

$action = htmlspecialchars($_GET['action']);	
if($psCore->settings['c_fotos_private'] == '0') {	
	if($action == '' || $action == 'ver'){
		$psLevel = 0;		
	}else{		
		$psLevel = 2;		
	}
}

//verificamos el nivel de acceso
$psLevelVer = $psCore->setLevel($psLevel, true);
if($psLevelVer != 1){
	$psPage = "aviso";
	$psAjax = 0;
	$smarty->assign("psAviso", $psLevelVer);
	$psContinue = false;
}

/**
 * instrucciones de codigo
 * si podemos continuar cargamos los datos necesarios
 */
if($psContinue){
 	include(PS_CLASS."c.fotos.php");
	$psFotos =& psFotos::getInstance();

	switch($action){
		//estadísticas
		case '':
            $smarty->assign("psLastFotos", $psFotos->getLastFotos());
            $smarty->assign("psLastComments", $psFotos->getLastComentarios());
            $consulta = "SELECT stats_miembros, stats_fotos, stats_foto_comments FROM w_stats WHERE stats_no = :no";
            $valores = array('no' => 1);
            $query = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
            $smarty->assign("psStats", $query);
        	break;
        //ver foto
        case 'ver':
            $psFoto = $psFotos->getFoto();
            $psTitle = $psFoto['foto']['f_title'].' - '.$psFoto['foto']['user_name'].' - '.$psCore->settings['titulo'];
			
			if($psFoto['foto']['f_status'] == 1 && (!$psUser->admod && $psUser->permisos['moacp'] == false)){
				$psPage = 'aviso';
	            $smarty->assign("psAviso", array('titulo' => 'Error', 'mensaje' => 'Esta foto se encuentra en revisi&oacute;n por acumulaci&oacute;n de denuncias', 'but' => 'Ir a Fotos', 'link' => "{$psCore->settings['url']}/fotos/"));
			}elseif($psFoto['foto']['exist'] == 0){
	            $psPage = 'aviso';
				$smarty->assign("psAviso",array('titulo' => 'Error', 'mensaje' => 'Esta foto no existe', 'but' => 'Ir a Fotos', 'link' => "{$psCore->settings['url']}/fotos/"));
			}else{
				$smarty->assign("psFoto", $psFoto['foto']);
	            $smarty->assign("psUserFotos", $psFoto['last']);
	            $smarty->assign("psFriendFotos", $psFoto['amigos']);
	            $smarty->assign("psFotoComentarios", $psFoto['comments']);
				$smarty->assign("psFotoVisitas", $psFoto['visitas']);
				$smarty->assign("psFotoMedallas", $psFoto['medallas']);
				$smarty->assign("psTtotalMedallas", $psFoto['m_total']);
			}
			break;
		//editar foto
		case 'editar':
            if(empty($_POST['titulo'])){
                $psFoto = $psFotos->getEditarFoto();
                if(!is_array($psFoto)){
                    $psPage = 'aviso';
                    $smarty->assign("psAviso",array('titulo' => 'Error', 'mensaje' => $psFoto, 'but' => 'Ir a Fotos', 'link' => "{$psCore->settings['url']}/fotos/"));
                }
                else{
                	$smarty->assign("psFoto", $psFoto);
                }
            } else {
                $psPage = 'aviso';
                $psFoto = $psFotos->editarFoto();
                $smarty->assign("psAviso",array('titulo' => 'Error', 'mensaje' => $psFoto, 'but' => 'Ir a Fotos', 'link' => "{$psCore->settings['url']}/fotos/"));
            }
        	break;
        //borrar foto
        case 'borrar':
            $psAjax = 1;
            echo $psFotos->borrarFoto();
        	break;
        //album de fotos del usuario
        case 'album':
            $username = filter_input(INPUT_GET, 'user');
            $user_id = $psUser->getUid($username);
            if(empty($user_id)){
                $psPage = 'aviso';
                $smarty->assign("psAviso",array('titulo' => 'Error', 'mensaje' => 'Este usuario no existe.', 'but' => 'Ir a Fotos', 'link' => "{$psCore->settings['url']}/fotos/"));
            }else{
                $psFotoAlbum = $psFotos->getFotos($user_id);
                $smarty->assign("psFotos", $psFotoAlbum);
                $smarty->assign("psFotoUser", array($user_id, $username));
            }
        	break;
        case 'agregar':
            if(!empty($_POST['titulo'])){
                $result = $psFotos->nuevaFoto();
                $psPage = 'aviso';
                if(!is_array($result) && $result > 0){
                    $titulo = $_POST['titulo'];
                    $smarty->assign("psAviso", array(
                        'titulo' => 'Foto Agregada',
                        'mensaje' => "La imagen <b>".$titulo."</b> fue agregada.",
                        'but' => 'Ver imagen',
                        'link' => "{$psCore->settings['url']}/fotos/{$psUser->nick}/{$result}/".$psCore->setSEO($titulo).".html"
                    ));
                } else {
                    $smarty->assign("psAviso", array(
                        'titulo' => 'Ouch',
                        'mensaje' => $result, 'but' => 'Volver',
                        'link' => "{$psCore->settings['url']}/fotos/agregar.php"
                    ));
                }
            }
            break;
	}
	$smarty->assign("psAction",$action);
}

//ahora agregamos los datos generados a smarty
if(empty($psAjax)){
	$smarty->assign("psTitle", $psTitle);
	include('../../footer.php');
}