<?php

/**
 * controlador de usuarios
 * @requisitos: 
 * agregamos las variables y archivos
 * verificamos el nivel de acceso del usuario
 * creamos las variables locales para el archivo
 * agregamos las instrucciones de codigos necesarias
 * agregamos los datos generados a smarty
 * 
 * @name usuarios.php
 * @author Iván Martínez Tutor
 */

/**
 * variables por defecto 
 */
//$psPage.tpl -> plantilla a mostrar con este archivo
$psPage = "usuarios";

//nivel de acceso a esta pagina
$psLevel = 0;

//comprobamos si la respuesta se realiza por ajax
$psAjax = empty(filter_input(INPUT_GET['ajax'])) ? 0 : 1;

//creamos la variable para comprobar si continuamos con el script
$psContinue = true;

//incluimos el archivo del header
include "../../header.php";

//cargamos el titulo de la pagina actual
$psTitle = $psCore->settings['titulo']." - ".$psCore->settigns['slogan'];

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
 * instrucciones de codigo
 * si podemos continuar cargamos los datos necesarios
 */
if($psContinue){
    //paises
    include(PS_EXTRA.'datos.php');
    //asignamos los paises a smarty
    $smarty->assign("psPaises",$psPaises);
    
    //usuarios
    $psUsers = $psUser->getUsuarios();
    //agregamos usuarios a smarty
    $smarty->assign("psUsers",$psUsers['data']);
    $smarty->assign("psPages",$psUsers['pages']);
    $smarty->assign("psTotal",$psUsers['total']);
    
    //filtros
    //obtenemos los datos online, avatar, sexo, pais y rango del usuario
    $smarty->assign("psFiltro", array(
        'online' => filter_input(INPUT_GET['online']), 
        'avatar' => filter_input(INPUT_GET['avatar']), 
        'sex' => filter_input(INPUT_GET['sexo']),
        'pais' => filter_input(INPUT_GET['pais']),
        'rango' => filter_input(INPUT_GET['rango']))
    );

    //obtenemos los rangos de los usuarios
    $consulta = $psDb->db_execute("SELECT rango_id, r_name FROM u_rangos ORDER BY rango_id");
    $smarty->assign("psRangos", resultadoArray($consulta));
}

/**
 * agregamos los datos generados a smarty
 */

//si la peticion se realizo por ajax detenemos el script
if(empty($psAjax)){
    $smarty->assign("psTitle",$psTitle);
    //incluimos el archivo del footer
    include(PS_ROOT.'/footer.php');
}