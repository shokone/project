Raz&oacute;n para borrar este post:<br />
<select id="razon" onchange="if($(this).val() == 13) $('input[name=razon_desc]').slideDown();">
  {foreach from=$psDenuncias item=d key=denuncia}
      {if $d}<option value="{denuncia}">{$d}</option>{/if}
  {/foreach}
</select><br />
<input type="text" name="razon_desc" maxlength="150"  class="nodisplay" />
<br />
<label for="send_b">Enviar al borrador del usuario</label>
<input type="checkbox" id="send_b" name="send_b" value="1"/>
