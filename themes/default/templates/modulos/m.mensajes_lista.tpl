{if $psMensajes.data}
    <ul id="mpList" class="list-group">
        {foreach from=$psMensajes.data item=mp}
        <li id="mp_{$mp.mp_id}" class="list-group-item {if $mp.mp_read_to == 0} unread{/if}">
            <div class="table-responsive">
            <table class="uiGrid table table-hover" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="main_col">
                        <a href="{$psConfig.url}/mensajes/leer/{$mp.mp_id}">
                            <div class="mpContent both">
                                <img src="{$psConfig.url}/files/avatar/{$mp.mp_from}_50.jpg" />
                            </div>
                        </a>
                    </td>
                    <td class="main_col">
                        <a href="{$psConfig.url}/mensajes/leer/{$mp.mp_id}">
                            <div class="mpContent both">
                                <div class="mp_time">{$mp.mp_date|fecha:'d_Ms_a'}</div>
                            </div>
                        </a>
                    </td>
                    <td class="main_col">
                        <a href="{$psConfig.url}/mensajes/leer/{$mp.mp_id}">
                            <div class="mpContent both">
                                <div class="mp_desc">
                                    <div class="autor"><strong>{$mp.user_name}</strong></div>
                                    <div class="subject">{$mp.mp_subject}</div>
                                    <div class="preview">{if $mp.mp_type == 1}<i class="return"></i>{/if}{$mp.mp_preview}</div>
                                </div>
                            </div>
                        </a>
                    </td>
                    <td class="plm">
                      	<a href="#" class="qtip read" title="Marcar como le&iacute;do" onclick="mensaje.marcar('{$mp.mp_id}:{$mp.mp_type}', 0, 1, this); return false;" {if $mp.mp_read_to == 1}style="display:none"{/if}><i class="read"></i></a>
                        <a href="#" class="qtip unread" title="Marcar como no le&iacute;do" onclick="mensaje.marcar('{$mp.mp_id}:{$mp.mp_type}', 1, 1, this); return false;" {if $mp.mp_read_to == 0}style="display:none"{/if}><i class="unread"></i></a>
                    </td>
                    <td class="pls">
                        <a href="#" class="qtip" title="Eliminar" onclick="mensaje.eliminar('{$mp.mp_id}:{$mp.mp_type}',1); return false;"><i class="delete"></i></a>
                    </td>
                </tr>
            </table>
            </div>
        </li>
        {/foreach}
    </ul>
{else}
    <div class="emptyMensajes">No hay mensajes</div>
{/if}
<div class="mpFooter">
    <div class="actions">
        {if $psAction == ''}
            <strong>Ver: </strong> 
            {if $psQT == ''}
                <a href="{$psConfig.url}/mensajes/?qt=unread">No le&iacute;dos</a>
            {else}
                <a href="{$psConfig.url}/mensajes/">Todos los mensajes</a>
            {/if}
        {/if}
    </div>
    <div class="paginador">
        {if $psMensajes.pages.prev != 0}
            <div class="text-left floatL">
                <a href="{$psConfig.url}/mensajes/{if $psAction}{$psAction}/{/if}?page={$psMensajes.pages.prev}{if $psQT != ''}&qt=unread{/if}">&laquo; Anterior</a>
            </div>
        {/if}
        {if $psMensajes.pages.next != 0}
            <div class="text-right floatR">
                <a href="{$psConfig.url}/mensajes/{if $psAction}{$psAction}/{/if}?page={$psMensajes.pages.next}{if $psQT != ''}&qt=unread{/if}">Siguiente &raquo;</a>
            </div>
        {/if}
    </div>
    <div class="both"></div>
</div>