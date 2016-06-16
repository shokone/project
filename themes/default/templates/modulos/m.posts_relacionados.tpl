<div class="post-relacionados">
	<h3>Otros posts que te van a interesar:</h3>
    <ul>
    	{if $psRelacionados}
        {foreach from=$psRelacionados item=p}
    	<li class="categoriaPost">
			<a class="{if $p.post_private}categoria privado{/if}" title="{$p.post_title}" href="{$psConfig.url}/posts/{$p.c_seo}/{$p.post_id}/{$p.post_title|seo}.html" rel="dc:relation">{$p.post_title}</a>
		</li>
        {/foreach}
        {else}
        <li>No se encontraron posts relacionados</li>
        {/if}
    </ul>
</div>