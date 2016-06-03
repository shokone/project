1:
{if $psDo == ''}
	<div id="perfil_actividad" status="activo">
        <div class="widget big-info clearfix">
            <div class="title-w clearfix">
    			<h3>&Uacute;ltima actividad de {$psUsername}</h3>
    			<select onchange="actividad.cargar({$psUserID},'filtrar', this.value)" id="last-activity-filter">
    				<option value="0">Todas</option>
                    <option value="1">Posts nuevos</option>
                    <option value="2">Posts favoritos</option>
                    <option value="3">Posts votados</option>
                    <option value="4">Posts recomendados</option>
                    <option value="5">Comentarios nuevos</option>
                    <option value="6">Comentarios votados</option>
                    <option value="7">Siguiendo un post</option>
                    <option value="8">Siguiendo a un suario</option>
                    <option value="9">Foto nueva</option>
                    <option value="10">Publicaci&oacute;nes nuevas en el muro</option>
                    <option value="11">Le gusta</option>
    			</select>
    		</div>
            {if $psActividad.total > 0}
	            <div id="last-activity-container" class="last-activity">
	                {foreach from=$psActividad.data item=ad key=id}
		                {if $ad.data}
			                <div id="last-activity-date-{$id}" class="date-sep" active="true">
			                    <h3>{$ad.title}</h3>
			                    {foreach from=$ad.data item=ac}
				                    <div class="sep">
				                        <div class="ac_content">
				                            {if $ac.style != ''}
				                            	<span class="monac_icons ma_{$ac.style}"></span>
				                            {/if}
				                 			{$ac.text} <a href="{$ac.link}">{$ac.ltext}</a>
				                            {if $psUserID == $psUser->uid}
				                            	<span class="remove"><a onclick="actividad.borrar({$ac.id}, this); return false;">x</a></span>
				                            {/if}
				                        </div>
				                		<span class="time">{$ac.date|hace}</span>
				                	</div>
			                    {/foreach}
			                </div>
		                {/if}
	                {/foreach}
	                {if $psActividad.total > 0 && $psActividad.total >= 25}
	                <h3 id="last-activity-view-more">
	                    <a onclick="actividad.cargar({$psUserID},'more', 0); return false;" href="">Ver m&aacute;s actividad</a>
	                </h3>
	                {/if}
	            </div>
            {else}
            	<div class="emptyData">{$psUserName} no tiene actividad reciente.</div>
            {/if}
        </div>
    </div>
{else}
	{foreach from=$psActividad.data item=ad key=id}
	    {if $ad.data}
		    <div id="more-{$id}" nid="last-activity-date-{$id}" class="date-sep" active="false">
		        <h3 class="nodisplay">{$ad.title}</h3>
		        {foreach from=$ad.data item=ac}
			        <div class="sep">
			            <div class="ac_content">
			                {if $ac.style != ''}
			                	<span class="monac_icons ma_{$ac.style}"></span>
			                {/if}
			     			{$ac.text} <a href="{$ac.link}">{$ac.ltext}</a>
			                {if $psUserID == $psUser->uid}
			                	<span class="remove"><a onclick="actividad.borrar({$ac.id}, this); return false;">x</a></span>
			                {/if}
			            </div>
			    		<span class="time">{$ac.date|hace}</span>
			    	</div>
		        {/foreach}
		    </div>
	    {/if}
    {/foreach}
    {if $psActividad.total > 0 && $psActividad.total >= 25}
	    <h3 id="last-activity-view-more">
	        <a onclick="actividad.cargar({$psUserID},'more', 0); return false;" href="">Ver m&aacute;s actividad</a>
	    </h3>
    {/if}
    <div id="total_acts" val="{$psActividad.total}"></div>
{/if}
