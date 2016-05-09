{include file='secciones/main_header.tpl'}		
	<div>
        <div class="box_title">
            <div >{$psAviso.titulo}</div>
            <div class="box_rss"><div class="box_rss2"></div></div>
        </div>
		<div align="center" class="box_cuerpo">
    		{$psAviso.mensaje}
            {if $psAviso.but}
                <input type="button" onclick="location.href='{if $psAviso.link}{$psAviso.link}{else}{$psConfig.url}{/if}'" value="{$psAviso.but}"/>
    		{/if}
            {if $psAviso.return}
                <input type="button" onclick="history.go(-{$psAviso.return})" title="Volver" value="Volver"/>
            {/if}
		</div>	
	</div>             
{include file='secciones/main_footer.tpl'}