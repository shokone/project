{include file='secciones/main_header.tpl'}		
	<div class="nodisplay" id="preview"></div>
	<script>
		//guardaremos el titulo del botón 
		//lo hacemos en un tpl para poder cargar las variables de smarty
		var button_title = "{if $psBorrador}Aplicar Cambios{else}Agregar post{/if}";
	</script>
	<script type="text/javascript" src="{$psConfig.js}/agregar.js"></script>
    <div id="form_div row">
   	    {include file='modulos/m.agregar_protocolo.tpl'}
       	{include file='modulos/m.agregar_form.tpl'}
    </div>
{include file='secciones/main_footer.tpl'}