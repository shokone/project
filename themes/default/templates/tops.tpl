{include file='secciones/main_header.tpl'}
	<div class="row">
	    {if $psAction == 'posts'}
			{include file='modulos/m.top_posts.tpl'}
	    {elseif $psAction == 'usuarios'}
	    	{include file='modulos/m.top_users.tpl'}
	    {/if}
	    {include file='modulos/m.top_sidebar.tpl'}
    </div>        
    <div class="clear-both"></div>
{include file='secciones/main_footer.tpl'}