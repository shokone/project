<?php
//si la constante PS_HEADER no esta definida no se accede al script
if (!defined('PS_HEADER')) {
    exit("No se permite el acceso al script");
}
/**
 * con el footer mostramos el final de la plantilla
 * 
 * @name footer.php
 * @author Iván Martínez Tutor
 */

/**
 * realizamos las tareas necesarias para mostrar la plantilla
 */
//pagina solicitada
$smarty->assign("psPage", $psPage);
$smnext = false;
//comprobamos si existe la plantilla seleccionada, sino existe mostraremos la que esté por defecto
if(!$smarty->template_exists("$psPage.tpl")){
	$smarty->template_dir = PS_ROOT.'/themes/default/templates/';
	if($smarty->template_exists("$psPage.tpl")){
		$smnext = true;
	}
}else{
	$smnext = true;
}
//ahora la mostramos
 if($smnext == true){
 	$smarty->display("$psPage.tpl");
 }else{
 	die("0: Lo sentimos, se produjo un error al cargar la plantilla '$psPage.tpl'.");
 }