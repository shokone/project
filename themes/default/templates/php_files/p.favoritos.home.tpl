{include file='secciones/main_header.tpl'}
<script type="text/javascript" src="{$psConfig.js}/favoritos.js"></script>
<script type="text/javascript">
    var favoritos_data = [{$psFavoritos}];
</script>
<div class="comunidades">
    {if !$psFavoritos}
        <div class="emptyData">No agregaste ning&uacute;n post a favoritos todav&iacute;a</div>
    {else}
        <div id="izquierda">
            <label for="favoritos-search">Buscar</label>
            <input type="text" autocomplete="off" onblur="favoritos.search_blur()" onfocus="favoritos.search_focus()" onkeyup="favoritos.search(this.value, event)" value="" id="favoritos-search">
            <div class="categoriaList">
                <ul>
                    <li id="cat_-1">
                        <a href="" onclick="favoritos.active(this); favoritos.categoria = 'todas'; favoritos.query(); return false;" style="color:#FFF"><strong>Categor&iacute;as</strong></a>
                    </li>
                </ul>
            </div>
        </div>
        <div id="centroDerecha">
            <div id="resultados">
                <table class="linksList">
                    <thead>
                        <tr>
                            <th></th>
                            <th><a href="" onclick="favoritos.active2(this); favoritos.orden = 'titulo'; favoritos.query(); return false;">T&iacute;tulo</a></th>
                            <th><a href="" onclick="favoritos.active2(this); favoritos.orden = 'fecha_creado'; favoritos.query(); return false;">Creado</a></th>
                            <th><a href="" onclick="favoritos.active2(this); favoritos.orden = 'fecha_guardado'; favoritos.query(); return false;" class="here">Guardado</a></th>
                            <th><a href="" onclick="favoritos.active2(this); favoritos.orden = 'puntos'; favoritos.query(); return false;">Puntos</a></th>
                            <th><a href="" onclick="favoritos.active2(this); favoritos.orden = 'comentarios'; favoritos.query(); return false;">Comentarios</a></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    {/if}
</div>
<div class="both"></div>
{include file='secciones/main_footer.tpl'}
