1:
{if $psType == 'new'}
    <div id="div_cmnt_{$psComment.0}" class="{if $psComment.4 == $psUser->user_id}especial1{else}especial3{/if}">
        <span id="citar_comm_{$psComment.0}" class="nodisplay">{$psComment.2}</span>
        <div class="comentario-post clearbeta">
            <div class="avatar-box" >
                <a href="{$psConfig.url}/perfil/{$psUser->info.user_name}">
                    <img width="48" height="48" title="{$psUser->info.user_name}" src="{$psConfig.url}/files/avatar/{$psUser->user_id}_50.jpg" class="avatar-48 lazy"/>
                </a>
            </div>
            <div class="comment-box">
                <div class="dialog-c"></div>
                <div class="comment-info clearbeta">
                    <div class="floatL">
                        <a href="{$psConfig.url}/perfil/{$psUser->nick}" class="nick">{$psUser->nick}</a>
                        {if $psUser->admod}
                            (<span style="color:red;">IP:</span> <a href="{$psConfig.url}/moderacion/buscador/1/1/{$psComment.6}" class="geoip" target="_blank">{$psComment.6}</a>)
                        {/if} dijo
                        <span>{$psComment.3|hace}</span> :
                    </div>
                    <div class="floatR answerOptions">
                        <ul>
                            {if $psComment.0 > 0}
                                {if $psUser->member}
                                    <li class="answerCitar">
                                        <a onclick="citar_comment({$psComment.0}, '{$psUser->nick}')">
                                            <span class="citar-comentario"></span>
                                        </a>
                                    </li>
                                {/if}
                                {if $psUser->admod || $psUser->permisos.goepc}
                                    <li>
                                        <a onclick="comentario.editar({$psComment.0}, 'show')" title="Editar">
                                            <span class="editar-comentario"></span>
                                        </a>
                                    </li>
                                {/if}
                                {if $psUser->admod || $psUser->permisos.godpc}
                                    <li class="iconDelete">
                                        <a onclick="borrar_com({$psComment.0}, {$psUser->user_id})">
                                            <span class="borrar-comentario"></span>
                                        </a>
                                    </li>
                                {/if}
                                {if $psUser->admod || $psUser->permisos.moaydcp}
                                    <li class="iconHide">
                                        <a onclick="ocultar_com({$psComment.0}, {$psUser->user_id});" title="Ocultar/Mostrar">
                                            <span class="moderar-comentario"></span>
                                        </a>
                                    </li>
                                {/if}
                            {else}
                                <li><a><span><b>VISTA PREVIA</b></span></a></li>
                            {/if}
                        </ul>
                    </div>
                </div>
                <div id="comment-body-{$psComment.0}" class="comment-content">{$psComment.1|nl2br}</div>
            </div>
        </div>
    </div>
{elseif $psType == 'edit'}
    <div id="preview" class="box_cuerpo">
        <div id="new-com-html">{$psComment.1|nl2br}</div>
        <div id="new-com-bbcode" class="nodisplay">{$psComment.5}</div>
    </div>
{/if}
