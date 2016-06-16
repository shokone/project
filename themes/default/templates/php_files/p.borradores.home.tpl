{include file='secciones/main_header.tpl'}
<!-- cargamos un archivo javascript específico para los borradores -->
<script type="text/javascript" src="{$psConfig.js}/borradores.js"></script>
<div id="borradores" class="row">
	<script type="text/javascript">
	   var borradores_data = [{$psBorradores}];
	</script>
	<div class="clearfix">
        <div class="col-md-8 col-xs-12">
            <div class="boxy">
                <div class="boxy-title">
                    <h3>Posts</h3>
                    <!--<label class="floatR" for="borradores-search">Buscar</label>
                    <input class="floatR" type="text" id="borradores-search" value="" onKeyUp="borradores.search(this.value, event)" autocomplete="off" />-->
                </div>
                <div class="both"></div>
                <div id="res" class="boxy-content">
                    {if $psBorradores}
                        <ul id="resultados-borradores"></ul>
                    {else}
                        <div class="emptyData">No tienes ning&uacute;n borrador ni post que haya sido eliminado.</div>
                    {/if}
                </div>
            </div>
        </div>
    	<div class="col-md-4">
   			<div class="boxy">
                <div class="boxy-title">
                    <h3>Filtrar</h3>
                    <span></span>
                </div>
                <div class="boxy-content">
                    <h4>Mostrar</h4>
                    <ul class="cat-list" id="borradores-filtros">
                        <li id="todos" class="active">
                            <span class="cat-title">
                                <a href="" onclick="borradores.active(this); borradores.filtro = 'todos'; borradores.query(); return false;">Todos</a>
                            </span> 
                            <span class="count"></span>
                        </li>
                        <li id="borradores">
                            <span class="cat-title">
                                <a href="" onclick="borradores.active(this); borradores.filtro = 'borradores'; borradores.query(); return false;">Borradores</a>
                            </span> 
                            <span class="count"></span>
                        </li>
                        <li id="eliminados">
                            <span class="cat-title">
                                <a href="" onclick="borradores.active(this); borradores.filtro = 'eliminados'; borradores.query(); return false;">Eliminados</a>
                            </span> 
                            <span class="count"></span>
                        </li>
                    </ul>
                    <!--<h4>Ordenar por</h4>
    
                    <ul id="borradores-orden" class="cat-list">
                        <li class="active">
                            <span>
                                <a href="" onclick="borradores.active(this); borradores.orden = 'fecha_guardado'; borradores.query(); return false;">Fecha guardado</a>
                            </span>
                        </li>
                        <li>
                            <span>
                                <a href="" onclick="borradores.active(this); borradores.orden = 'titulo'; borradores.query(); return false;">T&iacute;tulo</a>
                            </span>
                        </li>
                        <li>
                            <span>
                                <a href="" onclick="borradores.active(this); borradores.orden = 'categoria'; borradores.query(); return false;">Categor&iacute;a</a>
                            </span>
                        </li>
                    </ul>
                    <h4>Categorias</h4>
    
                    <ul class="cat-list" id="borradores-categorias">
                        <li id="todas" class="active">
                            <span class="cat-title active">
                                <a href="" onclick="borradores.active(this); borradores.categoria = 'todas'; borradores.query(); return false;">Ver todas</a>
                            </span> 
                            <span class="count"></span>
                        </li>
                    </ul>-->
                </div>
            </div>
        </div>
        
    </div>
    <div id="template-result-borrador" class="nodisplay">
        <li id="borrador_id___id__">
            <a title="__categoria_name__" class="categoriaPost __categoria__ __tipo__" href="__url__" onclick="__onclick__">__titulo__</a>
            <span class="causa">Causa: __causa__</span>
            <span class="gray">&Uacute;ltima vez guardado el __fecha_guardado__</span> 
            <a href="" onclick="borradores.eliminar(__borrador_id__, true); return false;">
                <img src="" alt="eliminar" title="Eliminar Borrador" />
            </a>
        </li>
    </div>
</div>
<div class="both"></div>

{include file='secciones/main_footer.tpl'}