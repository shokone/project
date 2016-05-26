<?php
/**
 * cargamos la página
 * @name index.php
 * @autor Iván Martínez Tutor
 */

//validamos para mostra el home

//incluimos el header
include 'header.php';
//si estamos logueados mostramos la seccion portal/mi
if($psCore->settings['c_allow_portal'] == 1 && $psUser->member == true && $_GET['do'] == 'portal'){
    include('inc/php/portal.php');
}else{//sino mostramos el home
    include('inc/php/posts.php');
}
