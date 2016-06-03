<?php
//si la constante PS_HEADER no esta definida no se accede al script
if (!defined('PS_HEADER')) {
    exit("No se permite el acceso al script");
}

/**
 * Controlador ajax para el portal
 *
 * @name ajax.portal.php
 * @author Iván Martínez Tutor
 */

//niveles de acceso y plantilla de cada acción
$niveles = array(
  'portal-posts_config' => array('n' => 2, 'p' => ''),
  'portal-posts_pages' => array('n' => 2, 'p' => 'posts'),
  'portal-favs_pages' => array('n' => 2, 'p' => 'posts'),
  'portal-activity_pages' => array('n' => 2, 'p' => 'actividad'),
);

//variables locales
$psPage = 'php_files/p.portal.' . $niveles[$action]['p'];
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
include("../class/c.portal.php");
$psPortal =& psPortal::getInstance();
switch($action){
  case 'portal-posts_config':
    echo $psPortal->setConfigPost();
  break;
  case 'portal-posts_pages':
    $psPosts = $psPortal->getPostPropios();
    $smarty->assign("psPosts",$psPosts['data']);
    $smarty->assign("psPages",$psPosts['pages']);
    $smarty->assign("psType",'posts');
  break;
  case 'portal-favs_pages':
    $psPosts = $psPortal->getFavoritos();
    $smarty->assign("psPosts",$psPosts['data']);
    $smarty->assign("psPages",$psPosts['pages']);
    $smarty->assign("psType",'favs');
  break;
  case 'portal-activity_pages':
    $actividad = $psActividad->obtenerActividadSeguida();
    if(!is_array($actividad)){
      die('<div class="emptyData">'.$actividad.'</div>');
    }
    $smarty->assign("psActividad",$actividad);
    $smarty->assign("psUserID",$user_id);
  break;
      default:
          die('0: Este archivo no existe.');
      break;
}
