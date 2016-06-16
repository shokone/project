<?php

/**
 * install.php

 * @author Iván Martínez Tutor
 */
//incluimos la clase db para realizar las consultas
include '../inc/class/c.db.php';
error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
session_start();
//
$version = 1;
$step = empty($_GET['step']) ? 0 : filter_input(INPUT_GET, 'step');
$step = htmlspecialchars(intval($step));
$next = true;

switch($step){
	case 0:
		$_SESSION['comienzo'] == false;
		$comienzo = file_get_contents('comienzo.txt');
		break;
	//obtenener los permisos
	case 1:
		if(isset($_POST['comienzo'])){
			//obtenemos los permisos actuales
			$permisos['f1'] = array('chmod' => substr(sprintf('%o', fileperms('../config.inc.php')), -3));
	        $permisos['d1'] = array('chmod' => substr(sprintf('%o', fileperms('../files/avatar/')), -3));
	        $permisos['d2'] = array('chmod' => substr(sprintf('%o', fileperms('../files/uploads/')), -3));
	        $permisos['d3'] = array('chmod' => substr(sprintf('%o', fileperms('../cache/')), -3));
	        //comprobamos si son correctos
	        foreach($permisos as $key => $valor){
	            $permisos[$key]['css'] = 'ok';
	            if($key == 'f1' && $valor['chmod'] != 666) {
	                $permisos[$key]['css'] = 'no';
	                $next = false;
	            }
	            elseif($key != 'f1' && $valor['chmod'] != 777) {
	                $permisos[$key]['css'] = 'no';
	                $next = false;
	            }
	        }
	        $_SESSION['comienzo'] = true;
	    }else{
				header("Location: index.php");
	    }
	    break;
	//comprobamos la base de datos
	case 2:
		//paso 2
		$next = false;
		if(isset($_POST['guardardb'])){
			$db['host'] = filter_input(INPUT_POST, 'host');
			$db['database'] = filter_input(INPUT_POST, 'name');
			$db['user'] = filter_input(INPUT_POST, 'user');
			$db['pass'] = filter_input(INPUT_POST, 'pass');

			//comprobamos la conexión con los nuevos datos
			$psDb = new psDb($db);
			if(is_null($psDb->conectar())){
				$mensaje = 'Tus datos de conexi&oacute;n son incorrectos.';
				$next = false;
			}else{
				//comprobamos si existe una instalación anterior
				if($psDb->db_execute("SHOW TABLES", null, 'fetch_num') == true){
					$mensaje = 'Ya existe una instalaci&oacute;n anterior, por favor limpia tu base de datos para poder realizar la instalaci&oacute;n';
				}else{
					//guardamos los datos de conexión
					$conf = file_get_contents('../config.inc.php');
					$conf = str_replace(array('pshost', 'psdatabase', 'psuser', 'pspass'), array($db['host'], $db['database'], $db['user'], $db['pass']), $conf);
					//cambiamos los datos en el archivo de configuración
					file_put_contents('../config.inc.php', $conf);
					//insertamos la base de datos
					$sql = file_get_contents('proyecto.sql');
					$sqll = $psDb->executeSqlFile($sql);
					if(empty($sqll)){
						header("Location: index.php?step=3");
					}else{
						print_r($psDb->executeSqlFile($sql));
						$mensaje = "Lo sentimos ocurri&oacute; un error al intentar importar la base de datos";
					}
				}
			}
		}
		break;
	//información de la página web
	case 3:
		$next = false;
		if(isset($_POST['guardardb'])){
			$inname = htmlspecialchars(filter_input(INPUT_POST, 'inname'));
			$inslogan = htmlspecialchars(filter_input(INPUT_POST, 'inslogan'));
			$inurl = htmlspecialchars(filter_input(INPUT_POST, 'inurl'));
			if(empty($inname) || empty($inslogan) || empty($inurl)){
				$mensaje = 'Debe rellenar todos los campos';
			}else{
				include '../config.inc.php';
				$psDb = new psDb($db);
				//comprobamos si la url comienza por el protocolo correspondiente
				if(substr($inurl, 0, 7) != 'http://' && substr($inurl, 0, 7) != 'https://'){
					$inurl = 'http://'.$inurl;
				}
				//actualizamos los datos
				$consulta = "UPDATE w_configuracion SET titulo = :titulo, slogan = :slogan, url = :url WHERE script_id = :id";
				$valores = array('titulo' => $inname, 'slogan' => $inslogan, 'url' => $inurl, 'id' => 1);
				if($psDb->db_execute($consulta, $valores)){
					header("Location: index.php?step=4");
				}else{
					$mensaje = "Lo sentimos ocurri&oacute; un error al intentar actualizar los datos";
				}
			}
		}
		break;
	//Información del administrador de la página web
	case 4:
		$next = false;
		if(isset($_POST['guardardb'])){
			$aname = htmlspecialchars(filter_input(INPUT_POST, 'aname'));
			$apass = htmlspecialchars(filter_input(INPUT_POST, 'apass'));
			$apass2 = htmlspecialchars(filter_input(INPUT_POST, 'apass2'));
			$aemail = htmlspecialchars(filter_input(INPUT_POST, 'aemail'));
			if(empty($aname) || empty($apass) || empty($apass2) || empty($aemail)){
				$mensaje = 'Debe rellenar todos los campos';
			}else{
				if(!ctype_alnum($aname)){
					$mensaje = 'Debe introducir un nombre de usuario alfanum&eacute;rico';
				}else{
					if($apass != $apass2){
						$mensaje = 'Las contrase&ntilde;as no coinciden';
					}else{
						if(!filter_var($aemail, FILTER_VALIDATE_EMAIL)){
							$mensaje = 'Debe introducir un email correcto';
						}else{
							$password = md5($apass);
							$fecha = time();
							include '../config.inc.php';
							$psDb = new psDb($db);
							//añadimos los datos del administrador de la web
							$insert = "INSERT INTO u_miembros (user_name, user_password, user_email, user_rango, user_registro, user_puntosxdar, user_activo) VALUES (:name, :pass, :email, :rango, :registro, :puntos, :activo)";
							$valores = array(
								'name' => $aname,
								'pass' => $password,
								'email' => $aemail,
								'rango' => 1,
								'registro' => $fecha,
								'puntos' => 50,
								'activo' => 1
							);
							$psDb->db_execute($insert, $valores);
							$consulta = "SELECT user_id FROM u_miembros WHERE user_name = :name";
							$val = array('name' => $aname);
							$uuid = $psDb->db_execute($consulta, $val, 'fetch_assoc');
							$uid = $uuid['user_id'];
							//insertamos en las demás tablas
							$insert2 = "INSERT INTO u_perfil (user_id) VALUES (:uid)";
							$insert3 = "INSERT INTO u_portal (user_id) VALUES (:uid)";
							$valores2 = array('uid' => $uid);
							$psDb->db_execute($insert2, $valores2);
							$psDb->db_execute($insert3, $valores2);

							//añadimos el email del administrador en la configuración
							$update = "UPDATE w_configuracion SET email = :email";
							$valores3 = array('email' => $aemail);
							$psDb->db_execute($update, $valores3);
							//actualizamos tablas
							$update2 = "UPDATE p_posts SET post_date = :dat WHERE post_id = :pid";
							$valores4 = array('dat' => $fecha, 'pid' => 1);
							$update3 = "UPDATE w_stats SET stats_time_foundation = :dat, stats_time_upgrade = :dat2 WHERE stats_no = :no";
							$valores5 = array('dat' => $fecha, 'dat2' => $fecha, 'no' => 1);
							$psDb->db_execute($update2, $valores4);
							$psDb->db_execute($update3, $valores5);
							//una vez actualizada la db enviamos un email al usuario
							$asunto = "Su comunidad ha sido instalada correctamente.";
							$body = '<html><head><title>Su comunidad est&aacute; lista!</title></head><body><p>Estos son sus datos de acceso:</p><p>Usuario: '.$uname.'</p><p>Contrase&ntilde;a: '.$upass.'</p></body></html>';
							$header = "Content-type: text/html; charset=iso-8859-15";
							mail($aemail, $asunto, $body, $header);
							//redirigimos al último paso
							header("Location: index.php?step=5&uid=".$uid.'');
						}
					}
				}
			}
		}
		break;
	//Final de la instalación
	case 5:
		define('PS_HEADER', true);
		include '../config.inc.php';
		$psDb = new psDb($db);
		//obtenemos los datos
		$consulta = "SELECT titulo, slogan, url, version_code FROM w_configuracion WHERE script_id = :id";
		$valores = array('id' => 1);
		$datos = $psDb->db_execute($consulta, $valores, 'fetch_assoc');
		if(isset($_POST['guardardb'])){
			header("Location: ".$datos['url']."");
		}
		break;
}
?>

<!DOCTYPE html>
<html lang="es">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<script type="text-javascript" src="js/bootstrap.min.js"></script>
		<link rel="stylesheet" type="text/css" href="css/style.css"/>
		<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css"/>
		<link rel="stylesheet" type="text/css" href="css/bootstrap-responsive.min.css"/>
		<link rel="stylesheet" type="text/css" href="css/bootstrap-theme.min.css"/>
	</head>
	<body>
		<header class="container text-center">
			<h1><img src="logo.png"/></h1>
		</header>
		<div class="container panel panel-default contenedor">
			<div class="row panel-body">
				<h2 class="text-center">Proceso de instalaci&oacute;n de SocialIT</h2>
				<nav class="col-md-4 col-xs-12 panel panel-container">
					<h2>Pasos de instalaci&oacute;n</h2>
					<ul class="menu">
						<li <?php if($step > 0) echo ' class="ok"';?>>Comienzo de la instalaci&oacute;n</li>
						<li <?php if($step > 1) echo ' class="ok"';?>>Permisos de escritura</li>
						<li <?php if($step > 2) echo ' class="ok"';?>>Configuraci&oacute;n de la base de datos</li>
						<li <?php if($step > 3) echo ' class="ok"';?>>Informaci&oacute;n de la p&aacute;gina web</li>
						<li <?php if($step > 4) echo ' class="ok"';?>>Informaci&oacute;n del administrador</li>
						<li <?php if($step > 5) echo ' class="ok"';?>>Instalaci&oacute;n completada</li>
					</ul>
				</nav>
				<section class="col-md-8 col-xs-12">
					<div class="cuerpo">
						<div id="step_<?php echo $step; ?>">
							<h3><?php if($step) echo 'Paso ' . $step;?></h3>
		                    <?php if(!$step) { ?>
		                    <form action="index.php<?php if($next == true) echo '?step=1';?>" method="post" id="form">
			                    <fieldset>
			                        <legend>Comienzo de la instalaci&oacute;n</legend>
			                        <p>Bienvenido a la instalaci&oacute;n de SocialIT!</p>
			                        <textarea name="comienzo" rows="5" class="form-control"><?php echo $comienzo; ?></textarea><br>
			                        <input type="submit" value="Continuar" class="btn btn-success"/>
			                    </fieldset>
		                    </form>
		                    <?php } ?>
							<?php if($step == 1){ ?>
							<form action="index.php<?php if($next == true) echo '?step=2'; ?>" method="post" id="form">
								<fieldset>
									<legend>Permisos de escritura</legend>
									<p>Los siguientes archivos y directorios dependen de permisos especiales, debes cambiarlos desde tu cliente frp. Los archivos deben tener permisos de <strong>666</strong> y los directorios de <strong>777</strong></p>
									<!-- archivo de configuración -->
									<label for="">/config.inc.php</label>
									<span <?php if($permisos['f1']['css'] == 'ok') echo 'class="ok"'; else echo 'class="no"';?>><?php echo $permisos['f1']['css']; ?></span><br>
									<!-- directorio donde se guardan los avatares de los usuarios -->
									<label for="">/files/avatar/</label>
									<span <?php if($permisos['d1']['css'] == 'ok') echo 'class="ok"'; else echo 'class="no"';?>><?php echo $permisos['d1']['css']; ?></span><br>
									<!-- directorio donde se guardan las subidas de archivos -->
									<label for="">/files/uploads/</label>
									<span <?php if($permisos['d2']['css'] == 'ok') echo 'class="ok"'; else echo 'class="no"';?>><?php echo $permisos['d2']['css']; ?></span><br>
									<!-- directorio donde smarty guarda la cache generada -->
									<label for="">/cache/</label>
									<span <?php if($permisos['d3']['css'] == 'ok') echo 'class="ok"'; else echo 'class="no"';?>><?php echo $permisos['d3']['css']; ?></span><br>
									<input type="submit" value="<?php if($next == true) echo 'Continuar'; else echo 'Volver a verificar';?>" class="btn btn-success"/>
								</fieldset>
							</form>
							<?php }else if($step == 2){ ?>
							<form action="index.php?step=<?php if($next == true) echo '3'; else echo '2'; ?>" method="post" id="form" role="form">
								<fieldset>
									<legend>Configuraci&oacute;n de la base de datos</legend>
									<p>Ingresa tus datos de conexi&oacute;n a la base de datos</p>
									<?php if($mensaje) echo '<div class="error">'.$mensaje.'</div>'; ?>
									<div class="form-group">
										<!-- host -->
										<label for="b1">Direcci&oacute;n del servidor:</label>
										<input type="text" id="b1" name="host" value="<?php echo $host; ?>"/><br>
									</div>
									<div class="form-group">
										<!-- db name -->
										<label for="b2">Nombre de la base de datos:</label>
										<input type="text" id="b2" name="name" value="<?php echo $name; ?>"/><br>
									</div>
									<div class="form-group">
										<!-- user -->
										<label for="b3">Usuario de la base de datos:</label>
										<input type="text" id="b3" name="user" value="<?php echo $user; ?>"/><br>
									</div>
									<div class="form-group">
										<!-- pass -->
										<label for="b4">Password de la base de datos:</label>
										<input type="password" id="b4" name="pass" value="<?php echo $pass; ?>"/><br>
									</div>
									<!-- submit -->
									<input type="submit" name="guardardb" value="Continuar" class="btn btn-success"/>
								</fieldset>
							</form>
							<?php }else if($step == 3){ ?>
							<form action="index.php?step=<?php if($next == true) echo '4'; else echo '3'; ?>" method="post" id="form">
								<fieldset>
									<legend>Informaci&oacute;n de la p&aacute;gina web</legend>
									<?php if($mensaje) echo '<div class="error">'.$mensaje.'</div>'; ?>
									<!-- titulo -->
									<div class="form-group">
										<label for="in1">T&iacute;tulo de la web: </label>
										<input type="text" id="in1" name="inname" value=""/>
									</div>
									<!-- slogan -->
									<div class="form-group">
										<label for="in2">Slogan de la web: </label>
										<input type="text" id="in2" name="inslogan" value=""/>
									</div>
									<!-- url -->
									<div class="form-group">
										<label for="in3">Url de la web: </label>
										<input type="text" id="in3" name="inurl" value=""/>
									</div>
									<input type="submit" name="guardardb" value="Continuar" class="btn btn-success"/>
								</fieldset>
							</form>
							<?php }else if($step == 4){ ?>
							<form action="index.php?step=<?php if($next == true) echo '5'; else echo '4'; ?>" method="post" id="form">
								<fieldset>
									<legend>Informaci&oacute;n del administrador</legend>
									<?php if($mensaje) echo '<div class="error">'.$mensaje.'</div>'; ?>
									<!-- nombre de usuario -->
									<div class="form-group">
										<label for="ad1">Nombre de usuario: </label>
										<input type="text" id="ad1" name="aname" value=""/>
									</div>
									<!-- contraseña -->
									<div class="form-group">
										<label for="ad2">Contrase&ntilde;a: </label>
										<input type="password" id="ad2" name="apass" value=""/>
									</div>
									<!-- repetimos contraseña -->
									<div class="form-group">
										<label for="ad3">Confirmar contrase&ntilde;a: </label>
										<input type="password" id="ad3" name="apass2" value=""/>
									</div>
									<!-- email -->
									<div class="form-group">
										<label for="ad4">Email: </label>
										<input type="text" id="ad4" name="aemail" value=""/>
									</div>
									<input type="submit" name="guardardb" value="Continuar" class="btn btn-success"/>
								</fieldset>
							</form>
							<?php }else if($step == 5){ ?>
							<h2>Bienvenido a SocialIT!</h2>
							<form method="post" id="form">
								<div class="error">Accede desde tu ftp y borra o renombra la carpeta <strong>install</strong> antes de acceder para evitar problemas</div>
								<fieldset>
									<p>Gracias por instalar SocialIT!</p>
									<p>Pulsa sobre el bot&oacute;n finalizar para acceder a la página web.</p>
								</fieldset>
								<input type="hidden" name="clave" value="<?php echo $clave;?>"/>
								<input type="submit" value="Finalizar" name="guardardb" class="btn btn-success"/>
							</form>
							<?php } ?>
						</div>
					</div>
				</section>
				<footer>
				</footer>
			</div>
		</div>
	</body>
</html>
