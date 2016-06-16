<div class="mpRContent">
    <div class="mpHeader">
        <h2>{$psMensajes.msg.mp_subject}</h2>
    </div>
    <div class="mpUser">
        <span class="info">Entre <a href="{$psConfig.url}/perfil/{$psUser->nick}">T&uacute;</a> y <a href="{$psConfig.url}/perfil/{$psMensajes.ext.user}">{$psMensajes.ext.user}</a></span>
    </div>
    <ul class="mpHistory list-group" id="historial">
        {if $psMensajes.res}{foreach from=$psMensajes.res item=mp}
        <li class="list-group-item">
            <div class="main clearBoth">
                <div class="floatL mensaje-img">
                    <a href="{$psConfig.url}/perfil/{$mp.user_name}" class="autor-image">
                        <img src="{$psConfig.url}/files/avatar/{$mp.mr_from}_50.jpg" />
                    </a>
                </div>
                <div class="mensaje">
                    <div class="rbody">
					<div><a href="{$psConfig.url}/perfil/{$mp.user_name}" class="autor-name">{$mp.user_name}</a> {if $psUser->admod}<a href="{$psConfig.url}/moderacion/buscador/1/1/{$mp.mr_ip}"><span class="mp-date">{$mp.mr_ip}</span></a> <br />{/if} <span class="mp-date">{$mp.mr_date|fecha}</span></div>
                    <div>{$mp.mr_body|nl2br}</div>
					</div>
                </div>
            </div>
        </li>
        {/foreach}{else}
        <li class="emptyData">No se pudieron cargar los mensajes.</li>
        {/if}
    </ul>
    {if $psUser->admod || ($psMensajes.msg.mp_del_to == 0 && $psMensajes.msg.mp_del_from == 0 && $psMensajes.ext.can_read == 1)}
        <div class="mpForm">
            <div class="form">
                <textarea id="respuesta" onfocus="onfocus_input(this)" onblur="onblur_input(this)" title="Escribe una respuesta..." class="autogrow onblur_effect">Escribe una respuesta...</textarea>
                <input type="hidden" id="mp_id" value="{$psMensajes.msg.mp_id}" />
                <a class="btn_g resp" onclick="mensaje.responder(); return false;">Responder</a>
            </div>
        </div>
    {else}
        <li class="emptyData">Un participante abandon&oacute; la conversaci&oacute;n o no tienes permiso para responder</li>
    {/if}
</div>
<div class="mpOptions">
    <div class="info"><h2>Acciones</h2></div>
    <ul class="actions-list">
        <li><a href="#" onclick="mensaje.marcar('{$psMensajes.msg.mp_id}:{$psMensajes.msg.mp_type}', 1, 2, this); return false;">Marcar como no le&iacute;do</a></li>
        <li class="div"></li>
        <li><a href="#" onclick="mensaje.eliminar('{$psMensajes.msg.mp_id}:{$psMensajes.msg.mp_type}',2); return false;">Eliminar</a></li>
        <li><a href="#" onclick="denuncia.nueva('mensaje',{$psMensajes.msg.mp_id}, '', ''); return false;">Marcar como correo no deseado...</a></li>
        <li class="div"></li>
        <li><a href="#" onclick="denuncia.nueva('usuario',{if $psMensajes.msg.mp_from != $psUser->uid}{$psMensajes.msg.mp_from}{else}{$psMensajes.msg.mp_to}{/if}, '', '{if $psMensajes.msg.mp_from != $psUser->uid}{$psMensajes.msg.user_name}{else}{$psUser->getUsername($psMensajes.msg.mp_to)}{/if}'); return false">Denunciar a este usuario...</a></li>
        <li><a href="javascript:bloquear({$psMensajes.ext.uid}, {if $psMensajes.ext.block}false{else}true{/if}, 'mensajes')" id="bloquear_cambiar">{if $psMensajes.ext.block}Desbloquear{else}Bloquear{/if} a <u>{$psMensajes.ext.user}</u>...</a></li>
        <li class="div"></li>
        <li><a href="{$psConfig.url}/mensajes/">&laquo; Volver a mensajes</a></li>
    </ul>
</div>
<div class="clearBoth"></div>
