<div id="">
    <div>
        <div>Afiliados</div>
        <div>
            <ul>
            {foreach from=$psAfiliados item=af}
                <li><a href="#" onclick="afiliado.detalles({$af.aid}); return false;" title="{$af.a_titulo}">
                    <img src="{$af.a_banner}" width="190" height="40"/>
                </a></li>
            {/foreach}
            </ul>
        </div>
        <div class="floatR"><a onclick="afiliado.nuevo(); return false">Afiliate a {$psConfig.titulo}</a></div>
     </div>
</div>