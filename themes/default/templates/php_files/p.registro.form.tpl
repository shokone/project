1:
<div id="registroForm">
	<div class="pasoUno">
		<div class="form-line">
			<label for="nick">Ingresa tu usuario</label>
			<input name="nick" type="text" id="nick" tabindex="1" title="Ingrese un nombre de usuario" onfocus="registro.focus(this)" onblur="registro.blur(this)" onkeydown="registro.clear_time(this.name)" onkeyup="registro.set_time(this.name)" autocomplete="off" /> 
			<div class="help"><span><em></em></span></div>
		</div>

		<div class="form-line">
			<label for="password">Ingresa tu contrase&ntilde;a</label>
			<input name="password" type="password" id="password" tabindex="2" title="Ingresa una contrase&ntilde;a segura" onfocus="registro.focus(this)" onblur="registro.blur(this)" autocomplete="off" />
			<div class="help"><span><em></em></span></div>
		</div>

		<div class="form-line">
			<label for="password2">Confirma tu contrase&ntilde;a</label>
			<input name="password2" type="password" id="password2" tabindex="3" title="Vuelve a ingresar la contrase&ntilde;a" onfocus="registro.focus(this)" onblur="registro.blur(this)" autocomplete="off" />
			<div class="help"><span><em></em></span></div>
		</div>

		<div class="form-line">
			<label for="email">E-mail</label>
			<input name="email" type="text" id="email" tabindex="4" title="Ingresa tu direcci&oacute;n de email" onfocus="registro.focus(this)" onblur="registro.blur(this)" onkeydown="registro.clear_time(this.name)" onkeyup="registro.set_time(this.name)" autocomplete="off" />
			<div class="help"><span><em></em></span></div>
		</div>

		<div class="form-line">
			<label>Fecha de Nacimiento</label>
			<select id="dia" name="dia" tabindex="5" onblur="registro.blur(this)" onfocus="registro.focus(this)" autocomplete="off" title="Ingrese d&iacute;a de nacimiento">
                <option value="">D&iacute;a</option>
	            {section name=dias start=1 loop=32}
	                <option value="{$smarty.section.dias.index}">{$smarty.section.dias.index}</option>
	            {/section}
			</select>
			<select id="mes" name="mes" tabindex="6" onblur="registro.blur(this)" onfocus="registro.focus(this)" autocomplete="off" title="Ingrese mes de nacimiento">
				<option value="">Mes</option>
	            {foreach from=$psMeses key=mid item=mes}
	                <option value="{$mid}">{$mes}</option>
	            {/foreach}	
            </select>
			<select id="anio" name="anio" tabindex="7" onblur="registro.blur(this)" onfocus="registro.focus(this)" autocomplete="off" title="Ingrese a&ntilde;o de nacimiento">
				<option value="">A&ntilde;o</option>
	            {section name=year start=$psMinYear loop=$psMinYear step=-1 max=$psMaxYear}
	                 <option value="{$smarty.section.year.index}">{$smarty.section.year.index}</option>
	            {/section}
			</select>
			<div class="help"><span><em></em></span></div>
		</div>
	</div>
	<div class="pasoDos">
		<div class="form-line">
			<label for="sexo">Sexo</label>
			<label class="list-label" for="sexo_m">Masculino</label>
			<input class="radio" type="radio" id="sexo_m" tabindex="8" name="sexo" value="1" onblur="registro.blur(this)" onfocus="registro.focus(this)" autocomplete="off" title="Selecciona tu g&eacute;nero" />
			<label class="list-label" for="sexo_f">Femenino</label>
			<input class="radio" type="radio" id="sexo_f" tabindex="8" name="sexo" value="0" onblur="registro.blur(this)" onfocus="registro.focus(this)" autocomplete="off" title="Selecciona tu g&eacute;nero" />
			<div class="help"><span><em></em></span></div>
		</div>

		<div class="form-line">
			<label for="pais">Pa&iacute;s</label>
			<select id="pais" name="pais" tabindex="9" onblur="registro.blur(this)" onchange="registro.blur(this)" onfocus="registro.focus(this)" autocomplete="off" title="Ingrese su pa&iacute;s">
				<option value="">Pa&iacute;s</option>
	            {foreach from=$psPaises key=code item=pais}
	                <option value="{$code}">{$pais}</option>
	            {/foreach}
			</select>
			<div class="help"><span><em></em></span></div>
		</div>

		<div class="form-line">
			<label for="estado">Regi&oacute;n</label>
			<select title="Ingrese su estado" autocomplete="off" onfocus="registro.focus(this)" onchange="registro.blur(this)" onblur="registro.blur(this)" tabindex="10" name="estado" id="estado">
				<option value="">Regi&oacute;n</option>
			</select>
			<div class="help"><span><em></em></span></div>
		</div>

		<div class="footerReg">
			<div class="form-line">
				<input type="checkbox" class="checkbox" id="terminos" name="terminos" tabindex="14" onblur="registro.blur(this)" onfocus="registro.focus(this)" title="Acepta los T&eacute;rminos y Condiciones?" />
				<label class="list-label" for="terminos">Acepto los <a href="/pages/terminos-y-condiciones/" target="_blank">T&eacute;rminos de uso</a></label>
				<div class="help"><span><em></em></span></div>
			</div>
		</div>
	</div>
</div>
<script>
//
//$.getScript("{$psConfig.js}/registro.js{literal}", function(){
$(document).ready(function(){
	registro.change_paso(1);
	myActions.final_cargando(); 
});
//
</script> 
{/literal}