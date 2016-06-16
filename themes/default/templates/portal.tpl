{include file='secciones/main_header.tpl'}
<script type="text/javascript" src="{$psConfig.js}/perfil.js"></script>
<script type="text/javascript" src="{$psConfig.js}/portal.js"></script>
<div id="center_box" class="col-md-8">
    <div id="portal">
        <div class="tabs_menu box_title menuc">
            <ul id="tabs_menu" class="nav nav-tabs">
                <li class="active"><a href="#" onclick="portal.load_tab('news', this); return false" class="news">Noticias</a></li>
                <li><a href="#" onclick="portal.load_tab('activity', this); return false;" class="activity">Actividad</a></li>
                <li><a href="#" onclick="portal.load_tab('posts', this); return false;" class="posts">Posts</a></li>
                <li><a href="#" onclick="portal.load_tab('favs', this); return false;" class="favs">Favoritos</a></li>
            </ul>
            <div class="clearBoth"></div>
        </div>
        <div id="portal_content">
            {include file='modulos/m.portal_noticias.tpl'}
            {include file='modulos/m.portal_actividad.tpl'}
            {include file='modulos/m.portal_posts.tpl'}
            {include file='modulos/m.portal_posts_favoritos.tpl'}
        </div>
    </div>
</div>
<div id="right_box" class="col-md-4">
    {include file='modulos/m.portal_user.tpl'}
    {include file='modulos/m.home_stats.tpl'}
    {$psConfig.ads_300}
    {include file='modulos/m.portal_posts_visitados.tpl'}
    {include file='modulos/m.home_afiliados.tpl'}
</div>
<div class="both"></div>
{include file='secciones/main_footer.tpl'}
