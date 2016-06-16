<script type="text/javascript">
    muro.maxWidth = 400;
    muro.stream.total = {$psMuro.total};
</script>				
<div id="portal_news" class="showHide" status="activo">
    <div id="info" pid="{$psInfo.uid}"></div>
    <div id="perfil-form" class="widget">
        {include file='modulos/m.perfil_muro_form.tpl'}
    </div>
	<div class="widget clearfix" id="perfil-news">
        <div id="news-content">
        {include file='modulos/m.perfil_muro_history.tpl'}                         
        </div>
        {if $psMuro.total >= 10}
            <div class="more-pubs">
                <div class="content">
                <a href="#" onclick="muro.stream.loadMore('news'); return false;" class="a_blue">Publicaciones m&aacute;s antiguas</a>
                <span><img width="15" height="15" alt="" src=""/></span>
                </div>
            </div>
        {elseif $psMuro.total == 0}
            <div class="emptyData">Hola <u>{$psUser->nick}</u>. &iquest;Por qu&eacute; no empiezas a seguir usuarios?</div>
        {/if}
    </div>
</div>