<div id="album" >
	<div class="title-w clearfix">
        <h2>{if $psFotoUser.0 == $psUser->user_id}Mis fotos{else}Fotos de {$psFotoUser.1}{/if}</h2>
    </div>
    <ul class="fotos-detail listado-content">
        {foreach from=$psFotos.data item=f}
    	<li>
        	<div class="avatar-box">
            	<a href="{$psConfig.url}/fotos/{$f.user_name}/{$f.foto_id}/{$f.f_title|seo}.html">
            		<img height="100" width="100" src="{$f.f_url}"/>
                </a>
            </div>
            <div class="notification-info">
            	<span>
                    <a href="{$psConfig.url}/fotos/{$f.user_name}/{$f.foto_id}/{$f.f_title|seo}.html">{$f.f_title}</a><br /> 
            		<span title="{$f.f_date|date_format:"%d.%m.%y a las %H:%M hs."}" class="time"><strong>{$f.f_date|date_format:"%d.%m.%Y"}</strong> - Por <a href="{$psConfig.url}/perfil/{$f.user_name}">{$f.user_name}</a></span><hr />
                    <span class="time">{$f.f_description|truncate:100}</span>
                </span>
            </div>
        </li>
        {/foreach}
    </ul>
</div>
<div class="paginadorCom">
    {if $psFotos.pages.prev}
        <div class="floatL before"><a href="{$psConfig.url}/fotos/{$psFotoUser.1}/{$psFotos.pages.prev}">&laquo; Anterior</a></div>
    {/if}
    {if $psFotos.pages.next}
        <div class="floatR next"><a href="{$psConfig.url}/fotos/{$psFotoUser.1}/{$psFotos.pages.next}">Siguiente &raquo;</a></div>
    {/if}
</div>