1:
<div id="perfil_medallas" class="widget" status="activo">
	<div class="title-w clearfix">
        <h2>Medallas de {$psUsername} ({$psMedallas.total})</h2>
    </div>
    {if $psMedallas.medallas}
        <ul class="listado">
            {foreach from=$psMedallas.medallas item=m}
            <li class="clearfix">
            	<div class="listado-content clearfix">
            		<div class="listado-avatar">
            			<img src="{$psConfig.tema.t_url}/images/icons/med/{$m.m_image}_32.png" class="qtip" title="{$m.medal_date|hace:true}" width="32" height="32"/>
            		</div>
            		<div class="txt">
            			<strong>{$m.m_title}</strong><br />
    					{$m.m_description}
            		</div>
            	</div>
            </li>
            {/foreach}
        </ul>
    {else}
        <div class="emptyData">{$psUsername} no tiene ninguna medalla todav&iacute;a</div>
    {/if}
</div>