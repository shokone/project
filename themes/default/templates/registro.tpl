{include file='secciones/main_header.tpl'}
<div class="post-deleted post-privado">
	<div class="content-splash">
		<h3>{if $psType == 'post'}Este post es privado, s&oacute;lo los usuarios registrados de {$psConfig.titulo} pueden acceder.{else}Registrate en {$psConfig.titulo}{/if}</h3>
        {if $psType == 'post'}Pero no te preocupes, tambi&eacute;n puedes formar parte de nuestra gran familia. <a title="Reg&iacute;strate!" onclick="registro_load_form(); return false" href=""><b>Reg&iacute;strate!</b></a>{/if}
				<div class="reg-login">
			<div class="login-panel">
				<h4>...O identif&iacute;cate</h4>
				<div class="login_cuerpo floatl">
					<span class="gif_cargando floatR" id="login_cargando"></span>
					<div id="login_error"></div>
					<form action="javascript:login_ajax('registro-logueo')" id="login-registro-logueo" method="POST">
						<input type="hidden" value="/registro" name="redirect">
						<label>Usuario</label>
						<input type="text" tabindex="20" class="ilogin" id="nickname" name="nick" maxlength="64"/>
						<label>Contrase&ntilde;a</label>
						<input type="password" tabindex="21" class="ilogin" id="password" name="pass" maxlength="64"/>
						<input type="submit" tabindex="22" title="Entrar" value="Entrar" class="mBtn btnOk"/>
						<divclass="floatR">
							<input type="checkbox"> Recordarme?
						</div>
					</form>
					<div class="login_footer">
						<a tabindex="23" href="#" onclick="remind_password();">&iquest;Olvidaste tu contrase&ntilde;a?</a> o 
						<a tabindex="23" href="#" onclick="resend_validation();">&iquest;Quieres activar tu cuenta?</a>
					</div>
				</div>
				<div>
					<strong>&iexcl;Atenci&oacute;n!</strong>
					<br>Antes de ingresar tus datos asegurate que la URL de esta p&aacute;gina pertenece a <strong>{$psConfig.titulo}</strong>
				</div>
			</div>
		</div>
	</div>
</div>
{include file='secciones/main_footer.tpl'}