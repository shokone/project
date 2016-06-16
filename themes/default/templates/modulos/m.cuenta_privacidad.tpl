<div class="content-tabs privacidad nodisplay">
	<fieldset>
		<div class="alert-cuenta cuenta-7"></div>
		<h2 class="active">&iquest;Qui&eacute;n puede...</h2>
		<div class="field">
			<label>ver tu muro?</label>
			<div class="input-fake">
				<select name="muro" class="cuenta-save-7">
					{foreach from=$psPrivacidad item=p key=i}
					<option value="{$i}"{if $psPerfil.p_configs.m == $i} selected="true"{/if}>{$p}</option>
					{/foreach}
				</select>
			</div>
		</div>
		{$psPerfil.p_configs.muro}
		<div class="field">
			<label>firmar tu muro?</label>
			<div class="input-fake">			
				<select name="muro_firma" class="cuenta-save-7">                    
					{foreach from=$psPrivacidad item=p key=i}                    
						{if $i != 6}<option value="{$i}"{if $psPerfil.p_configs.mf == $i}selected{/if}>{$p}</option>{/if}                    
					{/foreach}				
				</select>
			</div>
		</div>
		<div class="field">
			<label>ver tus &uacute;ltimas visitas?</label>
			<div class="input-fake">
				<select name="last_hits" class="cuenta-save-7">
					{foreach from=$psPrivacidad item=p key=i}
						{if $i != 1 && $i != 2}<option value="{$i}"{if $psPerfil.p_configs.hits == $i}selected{/if}>{$p}</option>{/if}
					{/foreach}
				</select>
			</div>
		</div>
		{if !$psUser->admod}
			{if $psPerfil.p_configs.rmp != 8}
				<div class="field">
					<label>enviarte mensajes privados?</label>
					<div class="input-fake">
						<select name="rec_mps" class="cuenta-save-7">
							{foreach from=$psPrivacidad item=p key=i}
								{if $i != 6}<option value="{$i}"{if $psPerfil.p_configs.rmp == $i}selected{/if}>{$p}</option>{/if}
							{/foreach}
						</select>
					</div>
				</div>
			{else}
				<div class="mensajes error">Algunas opciones de su privacidad han sido deshabilitadas, contacte con la administraci&oacute;n.</div>
			{/if}
		{/if}
	</fieldset>
		{if !$psUser->admod}
			<a onclick="$('#primi').slideUp(); $('#passi').slideDown(); $('#informa').slideDown(); $('#btninforma').slideDown();" id="primi">Desactivar Cuenta</a>
			<p class="nodisplay" id="informa"> Si desactiva su cuenta, todo el contenido relacionado a usted dejar&aacute; de ser visible durante un tiempo. 
			Pasado ese tiempo, la administraci&oacute;n borrar&aacute; todo su contenido y no podr&aacute; recuperarlo.</p>
			<a onclick="desactivate()" id="btninforma"><input type="button" value="Lo s&eacute;, pero quiero desactivarla" class="btn btnDelete nodisplay"></a>
		{/if}
	<div class="buttons">
		<input type="button" value="Guardar" onclick="cuenta.save(7)" class="btn btnOk nodisplay">
	</div>
	<div class="clearfix"></div>
</div>