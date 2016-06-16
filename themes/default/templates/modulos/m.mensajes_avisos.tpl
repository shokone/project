{if $psMensajes}
    <ul id="mpList" class="list-group">
        {foreach from=$psMensajes item=av}
            <li id="av_{$av.av_id}" class="list-group-item {if $av.av_read == 0} unread{/if}">
                <div class="table-responsive">
                <table class="uiGrid table table-hover" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="main_col">
                            <a href="{$psConfig.url}/mensajes/avisos/?aid={$av.av_id}">
                                <div class="mpContent both">
                                    <img src="{$psConfig.default}/images/icons/avtype_{$av.av_type}.png" />
                                </div>
                            </a>
                        </td>
                        <td class="main_col">
                            <a href="{$psConfig.url}/mensajes/avisos/?aid={$av.av_id}">
                                <div class="mpContent both">
                                    <div class="mp_desc">
                                        <div class="autor"><strong>{$psConfig.titulo}</strong></div>
                                        <div class="subject">{$av.av_subject}</div>
                                        <div class="preview">{$av.av_body|truncate:70}</div>
                                    </div>
                                </div>
                            </a>
                        </td>
                        <td class="main_col">
                            <a href="{$psConfig.url}/mensajes/avisos/?aid={$av.av_id}">
                                <div class="mpContent both floatR">
                                    <div class="mp_time">{$av.av_date|fecha}</div>
                                </div>
                            </a>
                        </td>
                        <td class="plm">&nbsp;</td>
                        <td class="pls">
                            <a href="{$psConfig.url}/mensajes/avisos/?did={$av.av_id}" class="qtip" title="Eliminar"><i class="delete"></i></a>
                        </td>
                    </tr>
                </table>
                </div>
            </li>
        {/foreach}
    </ul>
{elseif $psMensaje.av_id > 0}
    <div class="mpRContent">
        <div class="mpHeader">
            <h2>{$psMensaje.av_subject}</h2>
        </div>
        <div class="mpUser">
            <span class="info"><a href="{$psConfig.url}">{$psConfig.titulo}</a> <span class="floatR">{$psMensaje.av_date|fecha}</span></span>
        </div>
        <ul class="mpHistory" id="historial">
            <li>
                <div class="main both">
                    <div class="autor-image"><img src="{$psConfig.default}/images/icons/avtype_{$psMensaje.av_type}.png" /></div>
                    <div class="mensaje">
                        <div><a href="{$psConfig.url}/perfil/{$mp.user_name}" class="autor-name">{$mp.user_name}</a> </div>
                        <div>{$psMensaje.av_body|nl2br}</div>
                    </div>
                </div>
            </li>
        </ul>
        <div class="mpForm">
            <div class="form">
                <span>&nbsp;</span>
            </div>
        </div>
    </div>
    <div class="mpOptions">
        <div class="info"><h2>Acciones</h2></div>
        <ul class="actions-list">
            <li><a href="{$psConfig.url}/mensajes/avisos/?did={$psMensaje.av_id}">Eliminar</a></li>
            <li class="div"></li>
            <li><a href="{$psConfig.url}/mensajes/avisos/">&laquo; Volver a avisos</a></li>
        </ul>
    </div>
    <div class="both"></div>
{else}
    <div class="emptyMensajes">{if $psMensaje}{$psMensaje}{else}No hay avisos o alertas{/if}</div>
{/if}