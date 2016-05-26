<?php
//si la constante PS_HEADER no esta definida no se accede al script
if (!defined('PS_HEADER')) {
    exit("No se permite el acceso al script");
}

/**
 * Controlador ajax para el registro de los usuarios
 *
 * @name ajax.registro.php
 * @author Iván Martínez Tutor
 */

//niveles de acceso y plantilla de cada acción
$niveles = array(
	'registro-form' => array('n' => 1, 'p' => 'form'),
	'registro-check-nick' => array('n' => 1, 'p' => ''),
	'registro-check-email' => array('n' => 1, 'p' => ''),
	'registro-geo' => array('n' => 0, 'p' => ''),
	'registro-nuevo' => array('n' => 1, 'p' => ''),
);

//variables locales
$psPage = 'php_files/p.registro.' . $niveles[$action]['p'];
$psLevel = $niveles[$action]['n'];
$psAjax = empty($niveles[$action]['p']) ? 1 : 0;

//comprobamos el nivel de acceso del usuario
$mensaje = $psCore->setLevel($psLevel, true);
if($mensaje != 1){
	echo $mensaje['mensaje'];
	die;
}
//obtenemos las clases necesarias 
require('../class/c.registro.php');
$psRegistro =& psRegistro::getInstance();
switch($action){
	case 'registro-form':
		//obtenemos el formulario de registro
		//comprobamos si está permitido el registro de nuevos usuarios
		if($psCore->settings['c_reg_active'] == 0){
			//activamos ajax y enviamos un mensaje de error
			$psAjax = 1;
			echo '0 <div>
				<p>En estos momentos el registro en <strong>'.$psCore->settings['titulo'].'</strong> no est&aacute; permitido.</p>
			</div>';
		}else{
			//obtenemos los datos de los años, países y meses
			$ahora = date('Y', time());//año actual
			$max = 100 - $psCore->settings['c_allow_edad'];//máximo de edad
			$min = $ahora - $psCore->settings['c_allow_edad'];//mínimo de edad
			//asignamos datos
			include '../extra/datos.php';
			$smarty->assign('psMaxYear', $max);
			$smarty->assign('psMinYear', $min);
			$smarty->assign('psMeses', $psMeses);//obtenemos los meses en español del archivo datos.php
			$smarty->assign('psPaises', $psPaises);//obtenemos los datos de los países del archivo datos.php
		}
		break;
	case 'registro-check-nick':
	case 'registro-check-email':
		//estos dos se cargan desde la misma función
		echo $psRegistro->validarEmailUser();
		break;
	case 'registro-geo':
		//añadimos los datos geográficos de los estados
		include '../extra/estados.php';
		$pais = htmlspecialchars($_GET['pais_code']);
		if($pais){
			$mensaje = '1: ';
		}else{
			$mensaje = '0: Te as dejado sin rellenar el campo <strong>pais</strong>.';
		}
		//obtenemos los option para el select de estados
		foreach($estados[$pais] as $key => $valor){
			$mensaje .= '<option value="' . $key . '">' . $valor . '</option>';
		}
		//comprobamos
		if(strlen($mensaje) > 3){
			echo $mensaje;
		}else{
			echo '0: El c&oacute;digo de pa&iacute;s es incorrecto.';
		}
		break;
	case 'registro-nuevo':
		echo $psRegistro->nuevoRegistro();
		break;
}