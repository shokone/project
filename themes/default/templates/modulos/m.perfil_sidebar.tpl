<div>
    {$psConfig.ads_300}
</div>  
<div class="widget w-medallas clearfix">
	<div class="title-w clearfix">
		<h3>Medallas</h3>
		<span>{$psGeneral.m_total}</span>
	</div>
    {if $psGeneral.m_total}
        <ul class="clearfix">
            {foreach from=$psGeneral.medallas item=m}
                <li>
                    <img src="{$psConfig.tema.t_url}/images/icons/med/{$m.m_image}_16.png" class="qtip" title="{$m.m_title} - {$m.m_description}"/>
                </li>
            {/foreach}
        </ul>
        {if $psGeneral.m_total >= 21}
            <a href="#medallas" onclick="perfil.load_tab('medallas', $('#medallas'));" class="see-more">Ver m&aacute;s &raquo;</a>
        {/if}
    {else}
        <div class="emptyData">No tiene medallas</div>
    {/if}
</div>
<div class="widget w-seguidores clearfix">
	<div class="title-w clearfix">
		<h3>Seguidores</h3>
		<span>{$psInfo.stats.user_seguidores}</span>
	</div>
    {if $psGeneral.segs.data}
        <ul class="clearfix">
            {foreach from=$psGeneral.segs.data item=s}
                <li>
                    <a href="{$psConfig.url}/perfil/{$s.user_name}" class="hovercard" uid="{$s.user_id}" style="display:inline-block;">
                        <img src="{$psConfig.url}/files/avatar/{$s.user_id}_50.jpg" width="32" height="32"/>
                    </a>
                </li>
            {/foreach}
        </ul>
        {if $psGeneral.segs.total >= 21}
            <a href="#seguidores" onclick="perfil.load_tab('seguidores', $('#seguidores'));" class="see-more">Ver m&aacute;s &raquo;</a>
        {/if}
    {else}
        <div class="emptyData">No tiene seguidores</div>
    {/if}
</div>
<div class="widget w-siguiendo clearfix">
    <div class="title-w clearfix">
        <h3>Siguiendo</h3>
        <span>{$psGeneral.sigd.total}</span>
    </div>
    {if $psGeneral.sigd.data}
    	<ul class="clearfix">
            {foreach from=$psGeneral.sigd.data item=s}
                <li>
                    <a href="{$psConfig.url}/perfil/{$s.user_name}" class="hovercard" uid="{$s.user_id}" style="display:inline-block;">
                        <img src="{$psConfig.url}/files/avatar/{$s.user_id}_50.jpg" width="32" height="32"/>
                    </a>
                </li>
            {/foreach}
    	</ul>
        {if $psGeneral.sigd.total >= 21}
            <a href="#siguiendo" onclick="perfil.load_tab('siguiendo', $('#siguiendo'));" class="see-more">Ver m&aacute;s &raquo;</a>
        {/if}
    {else}
        <div class="emptyData">No sigue usuarios</div>
    {/if}
</div>
{if $psInfo.can_hits}
<div class="widget w-visitas clearfix">
    <div class="title-w clearfix">
        <h3>&Uacute;ltimas visitas</h3>
        <span>{$psInfo.visitas_total}</span>
    </div>
    {if $psInfo.visitas}
        <ul class="clearfix">
            {foreach from=$psInfo.visitas item=v}
                <li>
                    <a href="{$psConfig.url}/perfil/{$v.user_name}" class="hovercard" uid="{$v.user_id}" style="display:inline-block;">
                        <img src="{$psConfig.url}/files/avatar/{$v.user_id}_50.jpg" class="vctip" title="{$v.date|hace:true}" width="32" height="32"/>
                    </a>
                </li>
            {/foreach}
        </ul>
    {else}
        <div class="emptyData">No tiene visitas</div>
    {/if}
</div>
{/if}
<div>
    {$psConfig.ads_300}
</div>  