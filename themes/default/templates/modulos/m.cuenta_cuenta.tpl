<div class="content-tabs cuenta">
	<div class="alert-cuenta cuenta-1"></div>
    <fieldset>
        <div class="field">
            <label for="email">E-Mail:</label>
            <div class="input-fake input-hide-email">
                {$psUser->info.user_email} (<a onclick="input_fake('email')">Cambiar</a>)
            </div>
            <input type="text" value="{$psUser->info.user_email}" maxlength="35" name="email" id="email" class="text cuenta-save-1 input-hidden-email nodisplay">
        </div>
        <div class="field">
            <label for="pais">Pa&iacute;s:</label>
            <select onchange="cuenta.chgpais()" class="cuenta-save-1" name="pais" id="pais">
                <option value="">Pa&iacute;s</option>
                {foreach from=$psPaises key=code item=pais}
                	<option value="{$code}" {if $code == $psPerfil.user_pais}selected="selected"{/if}>{$pais}</option>
                {/foreach}
			</select>
		</div>
        <div class="field">
            <label for="estado">Estado/Provincia:</label>
            <select name="estado" id="estado" class="cuenta-save-1">
                <option value="">Estado / Provincia</option>
                {foreach from=$psEstados key=code item=estado}
                    <option value="{$code+1}" {if $code+1 == $psPerfil.user_estado}selected="selected"{/if}>{$estado}</option>
                {/foreach}
            </select>
        </div>
        <div class="field">
            <label>Sexo</label>
            <ul class="fields">
                <li>
                    <label><input type="radio" value="m" name="sexo" class="radio cuenta-save-1" {if $psPerfil.user_sexo == '1'}checked="checked"{/if}/>Masculino</label>
                </li>
                <li>
                    <label><input type="radio" value="f" name="sexo" class="radio cuenta-save-1" {if $psPerfil.user_sexo == '0'}checked="checked"{/if}/>Femenino</label>
                </li>
            </ul>
        </div>
    </fieldset>
    <div class="field">
		<label>Nacimiento:</label>
		<select class="cuenta-save-1" name="dia">
            {section name=dias start=1 loop=32}
            <option value="{$smarty.section.dias.index}" {if $psPerfil.user_dia ==  $smarty.section.dias.index}selected="selected"{/if}>{$smarty.section.dias.index}</option>
            {/section}                            
		</select>
		<select class="cuenta-save-1" name="mes">
        	{foreach from=$psMeses key=mid item=mes}
            	<option value="{$mid}" {if $psPerfil.user_mes == $mid}selected="selected"{/if}>{$mes}</option>
            {/foreach}
		</select>
		<select class="cuenta-save-1" name="ano">
            {section name=year start=$psMinYear loop=$psMinYear step=-1 max=$psMaxYear}
            	 <option value="{$smarty.section.year.index}" {if $psPerfil.user_ano ==  $smarty.section.year.index}selected="selected"{/if}>{$smarty.section.year.index}</option>
            {/section}
		</select>
	</div>
    {if $psConfig.c_allow_firma}
    <div class="field">
        <label for="firma">Firma:<br /> <small>Max. 300 caracteres</small></label>
        <textarea name="firma" id="firma" class="cuenta-save-1">{$psPerfil.user_firma}</textarea>
    </div>
    {/if}
    <div class="buttons">
        <input type="button" value="Guardar" onclick="cuenta.save(1)" class="btn btn-success">
    </div>
    <div class="clearfix"></div>
</div>