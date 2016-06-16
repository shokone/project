<div class="body">
	<ul>
        {if $psPosts}
            {foreach from=$psPosts item=p}
                <li class="categoriaPost">
                    <a class="title {if $p.post_private}categoria privado{/if}" title="{$p.post_title}" target="_self" href="{$psConfig.url}/posts/{$p.c_seo}/{$p.post_id}/{$p.post_title|seo}.html">{$p.post_title|truncate:55}</a>
                    <span>{$p.post_date|hace} &raquo; <a href="{$psConfig.url}/perfil/{$p.user_name}"><strong>{$p.user_name}</strong></a> &middot; Puntos <strong>{$p.post_puntos}</strong> &middot; Comentarios <strong>{$p.post_comments}</strong></span>
                    <span class="floatR"><a href="{$psConfig.url}/posts/{$p.c_seo}/">{$p.c_nombre}</a></span>
                </li>
            {/foreach}
        {else}
            <li class="emptyData">
                No hay posts aqu&iacute;,
                {if $psType == 'posts'} 
                    <a onclick="$('#config_posts').slideDown();">configura</a> tus categor&iacute;as preferidas.
                {elseif $psType == 'favs'} puedes agregar un post a tus favoritos para visitarlo m&aacute;s tarde.
                {elseif $psType == 'shared'} los usuarios que sigues podr&aacute;n recomentarte posts.
                {/if}
            </li>
        {/if}
    </ul>
    <br/>
</div>
<div class="footer size13">
	{if $psPages.prev != 0}<div><a onclick="portal.posts_page('{$psType}', {$psPages.prev}, true); return false">&laquo; Anterior</a></div>{/if}
	{if $psPages.next != 0}<div><a onclick="portal.posts_page('{$psType}', {$psPages.next}, true); return false">Siguiente &raquo;</a></div>{/if}
</div>
<div class="both"></div>