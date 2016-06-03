1:
{if $psHide != 'true'}
    <div id="perfil_{$psType}" class="widget" status="activo">
{/if}
<div class="title-w clearfix">
    <h2>{if $psType == 'seguidores'}Usuarios que siguen a {$psUsername}{else}Usuarios que {$psUsername} sigue{/if}</h2>
</div>
{if $psData.data}
    <ul class="listado">
        {foreach from=$psData.data item=u}
        <li class="clearfix">
        	<div class="listado-content clearfix">
        		<div class="listado-avatar">
        			<a href="{$psConfig.url}/perfil/{$u.user_name}"><img src="{$psConfig.url}/files/avatar/{$u.user_id}_50.jpg" width="32" height="32"/></a>
        		</div>
        		<div class="txt">
        			<a href="{$psConfig.url}/perfil/{$u.user_name}">{$u.user_name}</a><br>
        			<img src="{$psConfig.images}/flags/{$u.user_pais|lower}.png"/> <span class="grey">{$u.p_mensaje}</span>
        		</div>
        	</div>
        </li>
        {/foreach}
        <li class="listado-paginador clearfix">
    		{if $psData.pages.prev != 0}
                <a href="#" onclick="perfil.follows('{$psType}', {$psData.pages.prev}); return false;" class="anterior-listado floatL">Anterior</a>
            {/if}
    		{if $psData.pages.next != 0}
                <a href="#" onclick="perfil.follows('{$psType}', {$psData.pages.next}); return false;" class="siguiente-listado floatR">Siguiente</a>
            {/if}
        </li>
    </ul>
{else}
    <div class="emptyData">{if $psType == 'seguidores'}No tiene seguidores{else}No sigue usuarios{/if}</div>
{/if}    
{if $psHide != 'true'}</div>{/if}