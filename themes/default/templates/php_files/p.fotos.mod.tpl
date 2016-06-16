Raz&oacute;n para borrar esta foto:<br /><br />
<select id="razon" onchange="if($(this).val() == 8) $('input[name=razon_desc]').slideDown();">
	{foreach from=$psDenuncias item=denuncia key=ite}
	    {if $denuncia}<option value="{$ite}">{$denuncia}</option>{/if}
	{/foreach}
</select><br /><br />
<input type="text" name="razon_desc" maxlength="150" size="35" class="nodisplay"/>