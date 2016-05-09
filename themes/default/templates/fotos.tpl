{include file='secciones/main_header.tpl'}
        <script type="text/javascript" src="{$psConfig.js}/fotos.js"></script>
        {if $psAction == ''}
                {include file='modulos/m.fotos_home_content.tpl'}
                {include file='modulos/m.fotos_home_sidebar.tpl'}
        {elseif $psAction == 'agregar' || $psAction == 'editar'}
                {include file='modulos/m.fotos_add_form.tpl'}
                {include file='modulos/m.fotos_add_sidebar.tpl'}
        {elseif $psAction == 'ver'}
                {include file='modulos/m.fotos_ver_left.tpl'}
                {include file='modulos/m.fotos_ver_content.tpl'}
                {include file='modulos/m.fotos_ver_right.tpl'}
        {elseif $psAction == 'album'}
                {include file='modulos/m.fotos_album.tpl'}
        {/if}
{include file='secciones/main_footer.tpl'}