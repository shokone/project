<ul>
    {foreach from=$psGeneral.fotos item=f key=i}
	    {if $f.foto_id}
	    	<li>
	    		<div class="foto">
	    			<a href="{$psConfig.url}/fotos/{$psInfo.nick}/{$f.foto_id}/{$f.f_title|seo}.html" title="{$f.f_title}">
	    				<img border="0" src="{$f.f_url}"/>
	    			</a>
	    		</div>
	    	</li>
	    {else}
	    	<li><div class="foto">&nbsp;</div></li>
	    {/if}
    {/foreach}
</ul>