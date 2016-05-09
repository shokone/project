{include file='secciones/main_header.tpl'}
	{include file='modulos/m.top_sidebar.tpl'}
    {if $psAction == 'posts'}
		{include file='modulos/m.top_posts.tpl'}
    {elseif $psAction == 'usuarios'}
    	{include file='modulos/m.top_users.tpl'}
    {/if}                
{include file='secciones/main_footer.tpl'}