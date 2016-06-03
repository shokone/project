{if $psDo == 'search' && $psPosts}
  <div class="emptyData">Parece que existen posts similares al que est&aacute;s intentando agregar, te recomendamos visitarlos antes para evitar un repost.</div>
  {foreach from=$psPosts item=p}
    <a href="{$psConfig.url}/posts/{$p.c_seo}/{$p.post_id}/{$p.post_title|seo}.html" target="_blank"><b>{$p.post_title}</b></a><br>
  {/foreach}
{else}
  {$psTags}
{/if}
