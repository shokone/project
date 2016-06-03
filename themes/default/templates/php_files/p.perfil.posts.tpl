1:
<div id="perfil_posts" status="activo">
    <div class="widget w-posts clearfix">
    	<div class="title-w clearfix">
            <h3>&Uacute;ltimos Posts creados por {$psUsername}</h3>
            <span><a title="" href="/rss/posts-usuario/" class="iconos rss"></a></span>
        </div>
        {if $psGeneral.posts}
            <ul class="ultimos">
                {foreach from=$psGeneral.posts item=p}
            	<li class="categoriaPost">
                    <a title="{$p.post_title}" target="_self" href="{$psConfig.url}/posts/{$p.c_seo}/{$p.post_id}/{$p.post_title|seo}.html">{$p.post_title|truncate:45}</a>
                    <span>{$p.post_puntos} Puntos</span>
                </li>
                {/foreach}
                {if $psGeneral.total >= 18}
                    <li class="see-more"><a href="{$psConfig.url}/buscador/?autor={$psGeneral.username}">Ver m&aacute;s &raquo;</a></li>
                {/if}
            </ul>
        {else}
            <div class="emptyData">No hay posts</div>
        {/if}
    </div>
</div>