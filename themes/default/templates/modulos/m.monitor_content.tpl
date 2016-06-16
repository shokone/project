<div id="centroDerecha" class="col-md-8 col-xs-12">
    <div class="panel panel-default">
        <div class="panel-body">
        	<div>
                <h2>&Uacute;ltimas {$psDatos.total} notificaciones</h2>
            </div>
            {if $psDatos.data}
                <ul class="notificacion-detail listado-content list-group">
                	{foreach from=$psDatos.data item=noti}
                	<li class="list-group-item {if $noti.unread > 0} unread {/if}">
                    	<div class="avatar-box" style="z-index: 99;">
                        	<a href="{$psConfig.url}/perfil/{$noti.user}">
                        		<img height="32" width="32" src="{$psConfig.url}/files/avatar/{$noti.avatar}"/>
                            </a>
                        </div>
                        <div class="notificacion-info">
                        	<span>
                        		<span title="{$noti.date|fecha}" class="time">{$noti.date|fecha}</span>
                                {if $noti.total == 1}<a href="{$psConfig.url}/perfil/{$noti.user}">{$noti.user}</a>{/if} 
                            </span>
                            <br>
                            <span class="action">
                            	<span class="monac_icons ma_{$noti.style}"></span>{$noti.text}
                                <a href="{$noti.link}">{$noti.ltext}</a>
                            </span>
                        </div>
                    </li>
                    {/foreach}
                </ul>
            {else}
                <div class="emptyData">No tienes notificaciones</div>
            {/if}
        </div>
    </div>
</div>
