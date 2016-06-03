{include file='secciones/main_header.tpl'}
<script type="text/javascript" src="{$psConfig.js}/perfil.js"></script>
<div class="container">
    {include file='modulos/m.perfil_head.tpl'}
    <div class="perfil-main {$psGeneral.stats.user_rango.1}">
    	<div class="perfil-content general col-md-8">
            <div id="info" pid="{$psInfo.uid}"></div>
            <div id="perfil_content">
                {if $psPrivacidad.m.v == false}
                    <div id="perfil_wall" status="activo" class="widget">
                        <div class="emptyData">{$psPrivacidad.m.m}</div>
                        <script type="text/javascript">
                            perfil.load_tab('info', $('#informacion'));
                        </script>
                    </div>
                {elseif $psType == 'story'}
                    {include file='modulos/m.perfil_history.tpl'}
                {elseif $psType == 'news'}
                    {include file='modulos/m.perfil_noticias.tpl'}
                {else}
            	   {include file='modulos/m.perfil_muro.tpl'}
                {/if}
            </div>
            <div id="perfil_load"><img src="{$psConfig.images}/fb-loading.gif" /></div>
        </div>
        <div class="perfil-sidebar col-md-4">
            {include file='modulos/m.perfil_sidebar.tpl'}
        </div>
    </div>
</div>
{include file='secciones/main_footer.tpl'}