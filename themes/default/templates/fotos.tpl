{include file='secciones/main_header.tpl'}
        <script type="text/javascript" src="{$psConfig.js}/fotos.js"></script>
        <div class="row">
                {if $psAction == ''}
                        <div class="col-md-8">{include file='modulos/m.fotos_home_content.tpl'}</div>
                        <div class="col-md-4">{include file='modulos/m.fotos_home_sidebar.tpl'}</div>
                {elseif $psAction == 'agregar' || $psAction == 'editar'}
                        <div class="col-md-12">
                        {include file='modulos/m.fotos_add_sidebar.tpl'}
                        {include file='modulos/m.fotos_add_form.tpl'}
                        </div>
                {elseif $psAction == 'ver'}
                        <div class="col-md-8">{include file='modulos/m.fotos_contenido.tpl'}</div>
                        <div class="col-md-4">{include file='modulos/m.fotos_right.tpl'}</div>
                {elseif $psAction == 'album'}
                        <div class="col-md-12">{include file='modulos/m.fotos_album.tpl'}</div>
                {/if}
        </div>
{include file='secciones/main_footer.tpl'}