<div class="lastPosts">
	{if $psPostSticky}
		<div class="headerLast">
			<div class="caja_txt ult_post">Posts destacados en {$psConfig.titulo}</div>
		</div>
		<div>
			<ul class="list-group">
				{foreach from=$psPostSticky item=p}
					<li class="list-group-item 
						{if $p.post_status == 3} 
	                    {elseif $p.post_status == 1} 
	                    {elseif $p.post_status == 2} 
	                    {elseif $p.user_activo == 0} 
	                    {elseif $p.user_baneado == 1} 
	                    {/if} categoriaPost sticky{if $p.post_sponsored == 1} patrocinado{/if}">
						<a 
							{if $p.post_status == 3} class="" title="El post est&aacute; en revisi&oacute;n"
                            {elseif $p.post_status == 1} class="" title="El post se encuentra en revisi&oacute;n por acumulaci&oacute;n de denuncias"
                            {elseif $p.post_status == 2} class="" title="El post est&aacute; eliminado"
                            {elseif $p.user_activo == 0} class="" title="La cuenta del usuario est&aacute; suspendida"
                            {/if}  
                            href="{$psConfig.url}/posts/{$p.c_seo}/{$p.post_id}/{$p.post_title|seo}.html" title="{$p.post_title}" target="_self">{$p.post_title|truncate:70}</a><br>
                            <span>{$p.post_date|hace} &raquo; <a href="{$psConfig.url}/perfil/{$p.user_name}" uid="{$p.post_user}"><strong>@{$p.user_name}</strong></a> 
                            &middot; Puntos <strong>{$p.post_puntos}</strong> &middot; Comentarios <strong>{$p.post_comments}</strong></span>
		                <span class="floatR"><a href="{$psConfig.url}/posts/{$p.c_seo}/">{$p.c_nombre}</a></span>
					</li>
				{/foreach}
			</ul>
		</div>
	{/if}
	<div  class="headerLast">
		<div  class="caja_txt ult_post">&Uacute;ltimos posts en {$psConfig.titulo}</div>
	</div>

	<div class="body">
    	<ul class="list-group">
            {if $psPosts}
	            {foreach from=$psPosts item=p}
		            <li class="list-group-item 
		            {if $p.post_status == 3}
		            {elseif $p.post_status == 2}
		            {elseif $p.post_status == 1}
		            {elseif $p.user_activo == 0}
		            {elseif $p.user_baneado == 1}
		            {/if} ">
		                <a 
		                {if $p.post_status == 3}title="El post est&aacute; en revisi&oacute;n"
		                {elseif $p.post_status == 2}title="El post est&aacute; eliminado"
		                {elseif $p.post_status == 1}title="El post se encuentra en revisi&oacute;n por acumulaci&oacute;n de denuncias"
		                {elseif $p.user_activo == 0}title="La cuenta del usuario est&aacute; desactivada"
		                {elseif $p.user_baneado == 1}title="La cuenta del usuario est&aacute; suspendida"
		                {/if} class="title {if $p.post_private}categoria privado{/if}" alt="{$p.post_title}" title="{$p.post_title}" target="_self" href="{$psConfig.url}/posts/{$p.c_seo}/{$p.post_id}/{$p.post_title|seo}.html">{$p.post_title|truncate:70}</a><br>
		                <span>{$p.post_date|hace} &raquo; <a href="{$psConfig.url}/perfil/{$p.user_name}" uid="{$p.post_user}"><strong>@{$p.user_name}</strong></a> &middot; Puntos <strong>{$p.post_puntos}</strong> &middot; Comentarios <strong>{$p.post_comments}</strong></span>
		                <span class="floatR"><a href="{$psConfig.url}/posts/{$p.c_seo}/">{$p.c_nombre}</a></span>
		            </li>
	            {/foreach}
            {else}
            	<li class="emptyData">No hay ning&uacute;n post</li>
            {/if}
        </ul>
        <br clear="left"/>
    </div>
    <div class="footer size13">
        {if $psPages.prev > 0 && $psPages.max == false}
        	<a href="pagina{$psPages.prev}" class="floatL">&laquo; Anterior</a>
        {/if}
        {if $psPages.next <= $psPages.pages}
        	<a href="pagina{$psPages.next}" class="floatR">Siguiente &raquo;</a>
        {elseif $psPages.max == true}
        	<a href="pagina2">Siguiente &raquo;</a>
        {/if}
    </div>

</div>