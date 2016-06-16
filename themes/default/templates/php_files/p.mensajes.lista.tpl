{if $psMensajes.data}
    {foreach from=$psMensajes.data item=mp}
        <li class="{if $mp.mp_read_to == 0 || $mp.mp_read_mon_to == 0} unread{/if}">
            <a href="{$psConfig.url}/mensajes/leer/{$mp.mp_id}" title="{$mp.mp_subject}">
                <div class="content clearfix">
                    <div class="subject">{$mp.mp_subject}</div>
                    <!--<div class="preview">{$mp.mp_preview}</div>-->
                    <div class="time"><span class="autor">{$mp.user_name}</span> | {$mp.mp_date|fecha}</div>
                </div>
            </a>
        </li>
    {/foreach}
{else}
    <div class="emptyData">No tienes ning&uacute;n mensaje</div>
{/if}
