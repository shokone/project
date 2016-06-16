{include file='secciones/main_header.tpl'}
<div class="col-md-8 col-xs-12">
	<div class="nodisplay" id="m-mensaje"></div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3>Mensajes</h3>
            <form method="get" action="{$psConfig.url}/mensajes/search/" class="floatR search-mensajes">
                <input type="text" name="qm" placeholder="Buscar en Mensajes" title="Buscar en Mensajes" value="{$psMensajes.texto}" class="search_mp onblur_effect"/>
                <button type="submit" class="btn btn-success" value="Buscar">
                    <span class="glyphicon glyphicon-search"></span>&nbsp;Buscar
                </button>
            </form>
        </div>
        <div class="panel-body" id="mensajes">
            {if $psAction == '' || $psAction == 'enviados' || $psAction == 'respondidos' || $psAction == 'search'}
                {include file='modulos/m.mensajes_lista.tpl'}
            {elseif $psAction == 'leer'}
                {include file='modulos/m.mensajes_leer.tpl'}
            {elseif $psAction == 'avisos'}
                {include file='modulos/m.mensajes_avisos.tpl'}
            {/if}
		</div>
    </div>
</div>
<div class="col-md-4 col-xs-12">{include file='modulos/m.mensajes_menu.tpl'}</div>
<div class="both"></div>
{include file='secciones/main_footer.tpl'}