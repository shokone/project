<div class="col-md-8 col-xs-12">
	<!--PUNTOS-->
	<div class="boxy xtralarge">
    	<div class="boxy-title">
            <h3>Top post con m&aacute;s puntos</h3>
            <span class="icon-noti puntos-n"></span>
        </div>
        <div class="boxy-content">
        	{if !$psTops.puntos}<div class="emptyData">Nada por aqui</div>
            {else}
        	<ol>
            	{foreach from=$psTops.puntos item=p}
            	<li class="categoriaPost clearfix"><a href="{$psConfig.url}/posts/{$p.c_seo}/{$p.post_id}/{$p.post_title|seo}.html">{$p.post_title|truncate:45}</a> <span>{$p.post_puntos}</span></li>
                {/foreach}
            </ol>
            {/if}
        </div>
    </div>
    <!--SEGUIDORES-->
	<div class="boxy xtralarge">
    	<div class="boxy-title">
            <h3>Top post m&aacute;s favoritos</h3>
            <span class="icon-noti favoritos-n"></span>
        </div>
        <div class="boxy-content">
        	{if !$psTops.favoritos}<div class="emptyData">Nada por aqui</div>
            {else}
        	<ol>
            	{foreach from=$psTops.favoritos item=p}
            	<li class="categoriaPost clearfix"><a href="{$psConfig.url}/posts/{$p.c_seo}/{$p.post_id}/{$p.post_title|seo}.html">{$p.post_title|truncate:45}</a> <span>{$p.post_favoritos}</span></li>
                {/foreach}
            </ol>
            {/if}
        </div>
    </div>
    <!--COMENTARIOS-->
	<div class="boxy xtralarge">
    	<div class="boxy-title">
            <h3>Top post m&aacute;s comentado</h3>
            <span class="icon-noti comentarios-n"></span>
        </div>
        <div class="boxy-content">
        	{if !$psTops.comments}<div class="emptyData">Nada por aqui</div>
            {else}
        	<ol>
            	{foreach from=$psTops.comments item=p}
            	<li class="categoriaPost clearfix"><a href="{$psConfig.url}/posts/{$p.c_seo}/{$p.post_id}/{$p.post_title|seo}.html">{$p.post_title|truncate:45}</a> <span>{$p.post_comments}</span></li>
                {/foreach}
            </ol>
            {/if}
        </div>
    </div>
    
    <!--SEGUIDORES-->
	<div class="boxy xtralarge">
    	<div class="boxy-title">
            <h3>Top post con m&aacute;s seguidores</h3>
            <span class="icon-noti follow-n"></span>
        </div>
        <div class="boxy-content">
        	{if !$psTops.seguidores}<div class="emptyData">Nada por aqui</div>
            {else}
        	<ol>
            	{foreach from=$psTops.seguidores item=p}
            	<li class="categoriaPost clearfix"><a href="{$psConfig.url}/posts/{$p.c_seo}/{$p.post_id}/{$p.post_title|seo}.html">{$p.post_title|truncate:45}</a> <span>{$p.post_seguidores}</span></li>
                {/foreach}
            </ol>
            {/if}
        </div>
    </div>
</div>