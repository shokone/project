{include file='secciones/main_header.tpl'}
<div id="resultados" class="resultadosFull">
    <div class="filterBy filterFull col-md-4">
        <div class="block floatL">
            <form action="" method="get" class="clear">
                <span class="xResults">Filtrar:</span>
                <ul>
                    <li{if $psFiltro.online == 'true'} class="selected"{/if}>
                        <label><input type="checkbox" name="online" value="true" {if $psFiltro.online == 'true'}checked="true"{/if}/>En linea</label>
                    </li>
                    <li{if $psFiltro.avatar == 'true'} class="selected"{/if}>
                        <label><input type="checkbox" value="true" name="avatar" {if $psFiltro.avatar == 'true'}checked="true"{/if}/>Con foto</label>
                    </li>
                    <li{if $psFiltro.sex == 'm'} class="selected"{/if}>
                        <label><input type="radio" name="sexo" value="m" {if $psFiltro.sex == 'm'}checked="true"{/if}/>Hombre</label>
                    </li>
                    <li{if $psFiltro.sex == 'f'} class="selected"{/if}>
                        <label><input type="radio" name="sexo" value="f" {if $psFiltro.sex == 'f'}checked="true"{/if}/> Mujer</label>
                    </li>
                    <li{if $psFiltro.sex == ''} class="selected"{/if}>
                        <label><input type="radio" name="sexo" value="" {if $psFiltro.sex == ''}checked="true"{/if}/>Ambos</label>
                    </li>
                    <li{if $psFiltro.pais} class="selected"{/if}>
                        <label class="select">
                            <select name="pais" id="pais">
                                <option value="">Todos los Pa&iacute;ses...</option>
                                {foreach from=$psPaises key=code item=pais}
                                    <option value="{$code}" {if $psFiltro.pais == $code}selected="true"{/if}>{$pais}</option>
                                {/foreach}
                            </select>
                        </label>
                    </li>
                    <li{if $psFiltro.rango} class="selected"{/if}>
                        <label class="select">
                            <select name="rango" id="rango">
                                <option value="">Todos los Rangos...</option>
                                {foreach from=$psRangos item=r}
                                    <option value="{$r.rango_id}" {if $psFiltro.rango == $r.rango_id}selected="true"{/if}>{$r.r_name}</option>
                                {/foreach}
                            </select>
                        </label>
                    </li>
                    <li><input type="submit" class="btn btnOk" value="Filtrar" /></li>
                </ul>
            </form>
        </div>
    	<div class="floatL xResults">
    		Mostrando <strong>{$psTotal}</strong> resultados de <strong>{$psPages.total}</strong>
    	</div>
        <div class="center">{$psConfig.ads_160}</div>
        <div class="both"></div>
    </div>
    <div id="showResult" class="resultFull col-md-8">
            <ul class="clearfix">
            {if $psUsers}
                {foreach from=$psUsers item=u}
                    <li class="resultBox clearfix">
            			<h4 style="padding:0">
                            <span class="rango qtip" style="background-image:url({$psConfig.default}/images/icons/ran/{$u.rango.image});" title="{$u.rango.title}">&nbsp;</span>
                            <a href="{$psConfig.url}/perfil/{$u.user_name}">{$u.user_name}</a>
                        </h4>
            			<div class="floatL avatarBox" >
            				<a href="{$psConfig.url}/perfil/{$u.user_name}"><img width="75" height="75" src="{$psConfig.url}/files/avatar/{$u.user_id}_120.jpg" class="av"/></a>
            			</div>
            			<div class="floatL infoBox">
            				<ul>
                                {if $u.p_mensaje}<li>{$u.p_mensaje}</li>{/if}
                                <li>Sexo: <strong>{if $u.user_sexo == 0}Mujer{else}Hombre{/if}</strong> - Pa&iacute;s: <strong>{$psPaises[$u.user_pais]}</strong></li>
            					<li>Posts: <strong>{$u.user_posts}</strong> - Puntos: <strong>{$u.user_puntos}</strong> - Comentarios: <strong>{$u.user_comentarios}</strong></li>
                                {if $u.user_id != $psUser->uid}<li><a href="#" onclick="{if !$psUser->is_member}registro_load_form();{else}mensaje.nuevo('{$u.user_name}','','','');{/if}return false">Enviar Mensaje</a></li>{/if}
                                <li>Estado: {$u.status.t} <strong class="status {$u.status.css}" style="display:inline-block">&nbsp;</strong></li>
            				</ul>
            			</div>
            		</li>
                {/foreach}
            {else}
                <div class="emptyData">No se encontraro usuarios con los filtros seleccionados.</div>
            {/if}
            </ul>
            <div class="paginador">
        		{if $psPages.prev != 0}
                    <div style="text-align:left" class="floatL">
                        <a href="{$psConfig.url}/usuarios/?page={$psPages.prev}{if $psFiltro.online == 'true'}&online=true{/if}{if $psFiltro.avatar == 'true'}&avatar=true{/if}{if $psFiltro.sex}&sex={$psFiltro.sex }{/if}{if $psFiltro.pais}&pais={$psFiltro.pais}{/if}{if $psFiltro.rango}&rango={$psFiltro.rango}{/if}">&laquo; Anterior</a>
                    </div>
                {/if}
        		{if $psPages.next != 0}
                    <div style="text-align:right" class="floatR">
                        <a href="{$psConfig.url}/usuarios/?page={$psPages.next}{if $psFiltro.online == 'true'}&online=true{/if}{if $psFiltro.avatar == 'true'}&avatar=true{/if}{if $psFiltro.sex}&sex={$psFiltro.sex }{/if}{if $psFiltro.pais}&pais={$psFiltro.pais}{/if}{if $psFiltro.rango}&rango={$psFiltro.rango}{/if}">Siguiente &raquo;</a>
                    </div>
                {/if}
                <div class="both"></div>
            </div>
    </div>
</div>
<div class="both"></div>
{include file='secciones/main_footer.tpl'}
