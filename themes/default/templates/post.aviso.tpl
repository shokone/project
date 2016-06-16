{include file='secciones/main_header.tpl'}
<div class="post-{$psAviso.0}">
    <h3>{$psAviso.1}</h3>
    Todav&iacute;a puedes encontrar lo que buscas. Visita los ...
    <h4>Post Relacionados</h4>
    <ul>
        {if $psRelacionados}
            {foreach from=$psRelacionados item=p}
                <li class="categoriaPost {$p.c_seo}">
                    <a class="{if $p.post_private}categoria privado{/if}" title="{$p.post_title}" href="{$psConfig.url}/posts/{$p.c_seo}/{$p.post_id}/{$p.post_title|seo}.html" rel="dc:relation">{$p.post_title}</a>
                </li>
            {/foreach}
        {else}
            <li>No se encontr&oacute; ning&uacute;n post relacionado.</li>
        {/if}
    </ul>
</div>
{include file='secciones/main_footer.tpl'}