<div>
	<div class="boxy">
        <div class="boxy-title">
            <h3>Men&uacute;</h3>
            <span></span>
        </div><!-- boxy-title -->
        <div class="boxy-content" id="admin_menu">
            <ul id="mp-menu" class="cat-list">
                <li class="mp_inbox{if $psAction == ''} active{/if}">
                    <span class="cat-title"><a href="{$psConfig.url}/mensajes/">Mensajes Recibidos</a></span>
                </li>
                <li class="mp_send{if $psAction == 'enviados'} active{/if}">
                    <a href="{$psConfig.url}/mensajes/enviados/">Mensajes Enviados</a>
                </li>
                <li class="mp_return{if $psAction == 'respondidos'} active{/if}">
                    <a href="{$psConfig.url}/mensajes/respondidos/">Mensajes Respondidos</a>
                </li>
                <hr class="divider"/>
                {if $psAction == 'search'}
                    <li class="mp_search active"><span class="cat-title"><a href="#">Resultados de b&uacute;squeda</a></span></li>
                {/if}                         
                <li class="mp_new"><a href="#" onclick="mensaje.nuevo('','','',''); return false;">Escribir Nuevo Mensaje</a></li>
                <hr class="divider"/>
                <li class="mp_avisos{if $psAction == 'avisos'} active{/if}">
                    <span class="cat-title"><a href="{$psConfig.url}/mensajes/avisos/">Avisos/Alertas</a></span>
                </li>
            </ul>
        </div><!-- boxy-content -->
    </div>
    {if $psMensajes}
    <br />
    <center>
    {include file='modulos/m.global_ads_160.tpl'}
    </center>
    {/if}
</div>
