{include file='secciones/main_header.tpl'}
<button onclick="location.href = '{$psConfig.url}/mod-historial/'" value="Posts" {if !$psAction || $psAction == 'posts'}class="btn btn-success"{else}class="btn btn-warning"{/if}>Posts</button> 
<button onclick="location.href = '{$psConfig.url}/mod-historial/fotos/'" value="Fotos" {if $psAction == 'fotos'}class="btn btn-success"{else}class="btn btn-warning"{/if}>Fotos</button> 
<br /><br />
	<div id="resultados" class="col-md-12 col-xs-12 table-responsive">
    {if !$psAction || $psAction == 'posts'}
    	<table class="table">
            <thead>
    			<tr>
    				<th>Post</th>
    				<th>Acci&oacute;n</th>
    				<th>Moderador</th>
    				<th>Causa</th>
    			</tr>
    		</thead>
            <tbody>
                {foreach from=$psHistory item=h}
                <tr>
                    <td>
            			{$h.post_title}<br/>
            			Por <a href="{$psConfig.url}/perfil/{$h.user_name}">{$h.user_name}</a>
            		</td>
                    <td>
            			{if $h.action == 1}
                            <span>Editado</span>
                        {elseif $h.action == 2}
                            <span>Eliminado</span>
                        {else}
                            <span>Revisi&oacute;n</span>
                        {/if}
            		</td>
                    <td>
    					<a href="{$psConfig.url}/perfil/{$h.mod_name}">{$h.mod_name}</a>
    				</td>
                    <td>{if $h.reason == 'undefined'}Indefinida{else}{$h.reason}{/if}</td>
                </tr>
                {/foreach}
            </tbody>
        </table>
	{elseif $psAction == 'fotos'}
    	 <table class="table">
            <thead>
    			<tr>
    				<th>Foto</th>
    				<th>Acci&oacute;n</th>
    				<th>Moderador</th>
    				<th>Causa</th>
    			</tr>
    		</thead>
            <tbody>
                {foreach from=$psHistory item=h}
                <tr>
                    <td>
            			{$h.f_title}<br/>
            			Por <a href="{$psConfig.url}/perfil/{$h.user_name}">{$h.user_name}</a>
            		</td>
                    <td>
            			{if $h.action == 1}
                            <span>Editada</span>
                        {else}
                            <span>Eliminada</span>
                        {/if}
            		</td>
                    <td>
    					<a href="{$psConfig.url}/perfil/{$h.mod_name}">{$h.mod_name}</a>
    				</td>
                    <td>{if $h.reason == 'undefined'}Indefinida{else}{$h.reason}{/if}</td>
                </tr>
                {/foreach}
            </tbody>
        </table>
	{/if}
</div>
<div class="both"></div>
{include file='secciones/main_footer.tpl'}