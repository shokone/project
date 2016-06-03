<div id="perfil_wall" status="activo">
    <script type="text/javascript">
        muro.stream.total = {$psMuro.total};
    </script>
    {if $psGeneral.fotos_total > 0}
        <div id="perfil-foto-bar">
            {include file='modules/m.perfil_muro_fotos.tpl'}
        </div>
    {/if}
    <div id="perfil-form" class="widget">
    {if $psPrivacidad.mf.v == true}
        {include file='modulos/m.perfil_muro_form.tpl'}
    {else}
        <div class="">{$psPrivacidad.mf.m}</div>
    {/if}
    </div>
	<div class="widget" id="perfil-wall">
        <div id="wall-content">
            {include file='modulos/m.perfil_muro_history.tpl'}
        </div>
        {if $psMuro.total >= 10}
            <div class="more-pubs">
                <div class="content">
                <a href="#" onclick="muro.stream.loadMore('wall'); return false;" class="a_green">Publicaciones m&aacute;s antiguas</a>
                <span><img width="16" height="11" alt="" src=""/></span>
                </div>
            </div>
        {elseif $psMuro.total == 0 && $psUser->member}
            <div class="emptyData">Este usuario no tiene comentarios, s&eacute; el primero.</div>
        {/if}
    </div>
</div>