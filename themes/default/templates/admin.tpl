{include file='secciones/main_header.tpl'}
    <script type="text/javascript" src="{$psConfig.js}/admin.js"></script>
    <div id="administracion" class="row">
        <div class="col-md-8">
            <div class="box" id="admin_panel">
            	{if $psAction == ''}
            	   {include file='admin/m.admin_welcome.tpl'}
                {elseif $psAction == 'creditos'}
            	   {include file='admin/m.admin_creditos.tpl'}
                {elseif $psAction == 'configs'}
            	   {include file='admin/m.admin_configs.tpl'}
                {elseif $psAction == 'temas'}
            	   {include file='admin/m.admin_temas.tpl'}
                {elseif $psAction == 'news'}
            	   {include file='admin/m.admin_noticias.tpl'}
                {elseif $psAction == 'ads'}
            	   {include file='admin/m.admin_publicidad.tpl'}
                {elseif $psAction == 'medals'}
            	   {include file='admin/m.admin_medallas.tpl'}
				{elseif $psAction == 'stats'}
            	   {include file='admin/m.admin_stats.tpl'}
				{elseif $psAction == 'posts'}
            	   {include file='admin/m.admin_posts.tpl'}
				{elseif $psAction == 'fotos'}
            	   {include file='admin/m.admin_fotos.tpl'}
                {elseif $psAction == 'afs'}
            	   {include file='admin/m.admin_afiliados.tpl'}
                {elseif $psAction == 'pconfigs'}
            	   {include file='admin/m.admin_posts_configs.tpl'}
                {elseif $psAction == 'cats'}
            	   {include file='admin/m.admin_cats.tpl'}
                {elseif $psAction == 'users'}
            	   {include file='admin/m.admin_users.tpl'}
				{elseif $psAction == 'sesiones'}
            	   {include file='admin/m.admin_sesiones.tpl'}
				{elseif $psAction == 'nicks'}
            	   {include file='admin/m.admin_nicks.tpl'}
                {elseif $psAction == 'blacklist'}
            	   {include file='admin/m.admin_blacklist.tpl'}
                {elseif $psAction == 'badwords'}
                    {include file='admin/m.admin_badwords.tpl'}
                {elseif $psAction == 'rangos'}
            	   {include file='admin/m.admin_rangos.tpl'}
                {/if}
            </div>
        </div>
        <div class="col-md-4">
            <div class="boxy">
                <div class="box-title">
                    <h3>Men&uacute;</h3>
                    <span></span>
                </div>
                <div class="box-content" id="admin_menu">
                    {include file='admin/m.admin_sidemenu.tpl'}
                </div>
            </div>
        </div>
    </div>
{include file='secciones/main_footer.tpl'}