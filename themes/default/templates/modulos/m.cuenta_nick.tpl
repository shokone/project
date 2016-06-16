<div class="content-tabs cambiar-nick nodisplay">
    {if $psUser->info.user_name_changes > 0}
    	<fieldset>
            <div class="alert-cuenta cuenta-8"></div>
    		<p>Hola {$psUser->nick}, le recordamos que dispone de {$psUser->info.user_name_changes} cambios este a&ntilde;o.
    		Recuerde que si su cambio no es aprobado, no se le devolver&aacute; la disponibilidad de otro cambio. Eliga con sabidur&iacute;a su nick elegido</p>
            <div class="field">
                <label for="new_nick">Nombre de usuario</label>
                <input type="text" value="{$psUser->nick}" maxlength="15" name="new_nick" id="new_nick" class="text cuenta-save-8"/>
            </div>
    		<div class="field">
                <label for="password">Contrase&ntilde;a actual:</label>
                <input type="password" maxlength="32" name="password" id="password" class="text cuenta-save-8"/>
            </div>
    		<div class="field">
                <label for="pemail">Recibir respuesta en</label>
                <div class="input-fake input-hide-pemail">
                    {$psUser->info.user_email} (<a onclick="input_fake('pemail')">Cambiar</a>)
            </div>
                <input type="text" value="{$psUser->info.user_email}" maxlength="35" name="pemail" id="pemail" class="text cuenta-save-8 input-hidden-pemail nodisplay">
            </div>
        </fieldset>
        <div class="buttons">
            <input type="button" value="Guardar" onclick="cuenta.save(8)" class="mBtn btnOk"/>
        </div>
	{else}
	   <p>Hola {$psUser->nick}, lamentamos informarle que no tiene disponibilidad de cambios de nick, contacte con la administraci&oacute;n para saber cuando tendr&aacute; disponible un nuevo cambio
	{/if}
    <div class="clearfix"></div>
</div>