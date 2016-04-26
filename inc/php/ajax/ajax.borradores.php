<?php
//si la constante PS_HEADER no esta definida no se accede al script
if (!defined('PS_HEADER')) {
    exit("No se permite el acceso al script");
}

/**
 * Controlador ajax para los borradores de los usuarios
 *
 * @name ajax.borradores.php
 * @author Iván Martínez Tutor
 */

//niveles de acceso y plantillas para cada acción
$niveles = array(
	'borradores' => array('n' => 2, 'p' => 'home'),
	'borradores-agregar' => array('n' => 2, 'p' => ''),
	'borradores-guardar' => array('n' => 2, 'p' => ''),
	'borradores-eliminar' => array('n' => 2, 'p' => ''),
	'borradores-get' => array('n' => 2, 'p' => ''),
);
//la variable $action la obtenemos del archivo cuenta.php
$psPage = 'php_files/p.borradores.' . $niveles[$action]['p'];
$psLevel = $niveles[$action]['n'];
$psAjax = empty($niveles[$action]['p']) ? 1 : 0;

//comprobamos el nivel de acceso del usuario
$mensaje = $psCore->setLevel($psLevel, true);
if($mensaje != 1){
	echo $mensaje['mensaje'];
	die;
}
//obtenemos las clases necesarias
include '../class/c.borradores.php';
$psBorradores =& psBorradores::getInstance();
switch($action){
	case 'borradores':
		$smarty->assign('psBorradores', $psBorradores->getBorradores());
		break;
	case 'borradores-agregar':
		echo $psBorradores->newBorrador();
		break;
	case 'borradores-guardar':
		echo $psBorradores->newBorrador(true);
		break;
	case 'borradores-eliminar':
		echo $psBorradores->delBorrador();
		break;
	case 'borradores-get':
		$_GET['action'] = filter_input(INPUT_POST, 'borrador_id');
		$borrador = $psBorradores->getBorrador(0);
		echo '1: <div>
			<label>T&iacute;tulo:</label>
			<input type="text" value="'.$borrador['b_title'].'" onfocus="this.select()" /><br />
			<label>Cuerpo:</label><br />
			<textarea onfocus="this.select()">'.$borrador['b_body'].'</textarea>
		</div>';
		break;
}