<div class="floatL col-md-4">
	<div class="boxy">
    	<div class="boxy-title">
        	<h3>Filtrar</h3>
            <span class="icon-noti"></span>
        </div>
        <div class="boxy-content">
        	<h4>Categor&iacute;a</h4>
            <select onchange="location.href='{$psConfig.url}/top/{$psAction}/?fecha={$psFecha}&cat='+$(this).val()">
            <option value="0">Todas</option>
            {foreach from=$psConfig.categorias item=c}
                <option value="&cat={$c.cid}" {if $psCat == $c.cid}selected="selected"{/if}>{$c.c_nombre}</option>
            {/foreach}
            </select>
            <hr/>
            <h4>Per&iacute;odo</h4>
            <ul>
                <li><a href="{$psConfig.url}/top/{$psAction}/?fecha=2&cat={$psCat}&sub={$psSub}" {if $psFecha == 2}class="selected"{/if}>Ayer</a></li>
                <li><a href="{$psConfig.url}/top/{$psAction}/?fecha=1&cat={$psCat}&sub={$psSub}" {if $psFecha == 1}class="selected"{/if}>Hoy</a></li>
                <li><a href="{$psConfig.url}/top/{$psAction}/?fecha=3&cat={$psCat}&sub={$psSub}" {if $psFecha == 3}class="selected"{/if}>&Uacute;ltimos 7 d&iacute;as</a></li>
                <li><a href="{$psConfig.url}/top/{$psAction}/?fecha=4&cat={$psCat}&sub={$psSub}" {if $psFecha == 4}class="selected"{/if}>Del mes</a></li>
                <li><a href="{$psConfig.url}/top/{$psAction}/?fecha=5&cat={$psCat}&sub={$psSub}" {if $psFecha == 5}class="selected"{/if}>Todos los tiempos</a></li>
            </ul>
        </div>
    </div>
</div>