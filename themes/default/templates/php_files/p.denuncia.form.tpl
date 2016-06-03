{if $psAction == 'denuncia-post'}
    <div align="center">
        <b>Denunciar el post:</b><br>
        {$psData.obj_title}<br>
        <b>Creado por:</b>
        <a href="{$psConfig.url}/perfil/{$psData.obj_user}" target="_blank">{$psData.obj_user}</a><br />
        <b>Raz&oacute;n de la denuncia:</b><br />
        <select name="razon">
            {foreach from=$psDenuncias key=d item=denuncia}
              {if $denuncia}<option value="{$d}">{$denuncia}</option>{/if}
            {/foreach}
        </select><br />
        <b>Aclaraci&oacute;n y comentarios:</b><br />
        <textarea tabindex="6" rows="5" cols="40" name="extras"></textarea><br />
        <span class="size9">En el caso de ser Re-post debe indicarse el link del post original.</span>
    </div>
{elseif $psAction == 'denuncia-foto'}
    <div align="center">
        <b>Denunciar foto:</b><br />
        {$psData.obj_title}<br />
        <b>Raz&oacute;n de la denuncia:</b><br />
        <select name="razon">
        {foreach from=$psDenuncias key=d item=denuncia}
          {if $denuncia}<option value="{$d}">{$denuncia}</option>{/if}
        {/foreach}
        </select><br />
        <b>Aclaraci&oacute;n y comentarios:</b><br />
        <textarea tabindex="6" rows="5" cols="40" name="extras"></textarea><br />
        <span class="size9">Para atender tu caso r&aacute;pidamente, adjunta pruevas de tu denuncia.<br /> (capturas de pantalla) o añade el link de la foto junto con la razón de la denuncia</span>
    </div>
{elseif $psAction == 'denuncia-mensaje'}
    <div class="emptyData">Si reportas este mensaje ser&aacute; eliminado de tu bandeja. <br />&iquest;Realmente quieres denunciar este mensaje como correo no deseado?</div>
    <input type="hidden" name="razon" value="spam" />
{elseif $psAction == 'denuncia-usuario'}
    <div align="center">
        <b>Denunciar usuario:</b><br />
        {$psData.nick}<br /><br />
        <b>Raz&oacute;n de la denuncia:</b><br />
        <select name="razon">
            {foreach from=$psDenuncias key=d item=denuncia}
              {if $denuncia}<option value="{$d}">{$denuncia}</option>{/if}
            {/foreach}
        </select><br />
        <b>Aclaraci&oacute;n y comentarios:</b><br />
        <textarea tabindex="6" rows="5" cols="40" name="extras"></textarea><br />
        <span class="size9">Para atender tu caso r&aacute;pidamente, adjunta pruevas de tu denuncia.<br /> (capturas de pantalla)</span>
    </div>
{/if}
