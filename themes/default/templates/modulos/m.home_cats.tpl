<div class="floatR filterCategoria">
    <span>Filtrar por Categorías:</span>
    <select onchange="ir_categoria(this.value);">
        <option selected="selected" value="root">Seleccionar categoría</option>
        <option value="{if $psConfig.c_allow_portal == 0}-1{else}-2{/if}">Ver Todas</option>
        <option value="linea">-----</option>
		    {foreach from=$psConfig.categorias item=c}
            <option value="{$c.c_seo}" {if $psCategoria == '$c.c_seo'}selected="selected"{/if}>{$c.c_nombre}</option>
        {/foreach}
    </select>
</div>
