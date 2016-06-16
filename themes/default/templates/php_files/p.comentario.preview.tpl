1:
<hr class="divider" />
{if $psType == 'new'}
    <div id="div_cmnt_{$psComentario.0}" class="{if $psComentario.4 == $psUser->user_id}especial1{else}especial3{/if} panel panel-default">
        <span id="citar_comm_{$psComentario.0}" class="nodisplay">{$psComentario.2}</span>
        <div class="comentario-post panel-body">
            <div class="avatar-box floatL">
                <a href="{$psConfig.url}/perfil/{$psUser->info.user_name}">
                    <img width="48" height="48" title="{$psUser->info.user_name}" src="{$psConfig.url}/files/avatar/{$psUser->user_id}_50.jpg" class="avatar-48"/>
                </a>
            </div>
            <div class="comment-box panel panel-success">
                <div class="comment-info panel-heading">
                    <div class="panel-title">
                        <a href="{$psConfig.url}/perfil/{$psUser->nick}" class="nick">{$psUser->nick}</a>
                        {if $psUser->admod}
                            (<span style="color:red;">IP:</span> <a href="{$psConfig.url}/moderacion/buscador/1/1/{$psComentario.6}" class="geoip" target="_blank">{$psComentario.6}</a>)
                        {/if} dijo
                        <span>{$psComentario.3|hace}</span> :
                    </div>
                    <div class="floatR answerOptions bot-list-option">
                        <ul class="list-group">
                            {if $psComentario.0 > 0}
                                {if $psUser->member}
                                    <li class="answerCitar">
                                      <button onclick="citar_comment({$psComentario.0}, '{$psUser->user_id}')" title="Citar" class="btn btn-default">
                                        <span class="glyphicon glyphicon-comment"></span>
                                      </button>
                                    </li>
                                    {if $psUser->admod || $psUser->permisos.moedcopo}
                                      <li>
                                        <button onclick="comentario.editar({$psComentario.0}, 'show')" title="Editar comentario" class="btn btn-info">
                                          <span class="glyphicon glyphicon-edit"></span>
                                        </button>
                                      </li>
                                    {/if}
                                    {if $psUser->admod || $psUser->permisos.moecp}
                                      <li>
                                        <button onclick="borrar_com({$psComentario.0}, {$psUser->user_id})" title="Borrar" class="btn btn-danger">
                                          <span class="glyphicon glyphicon-trash"></span>
                                        </button>
                                      </li>
                                      {if $psUser->admod || $psUser->permisos.moaydcp}
                                        <li>
                                          <button onclick="ocultar_com({$psComentario.0}, {$psUser->user_id});" class="btn {if $c.c_status == 1} btn-danger {else} btn-success {/if}" title="{if $c.c_status == 1}Mostrar/Ocultar{else}Ocultar/Mostrar{/if}">
                                            <span class="glyphicon {if $c.c_status == 1}glyphicon-eye-open{else}glyphicon-eye-close{/if}"></span>
                                          </button>
                                        </li>
                                      {/if}
                                    {/if}
                                  {/if}
                            {/if}
                        </ul>
                    </div>
                </div>
                <div class="panel-body">
                    <div id="comment-body-{$psComentario.0}" class="comment-content">{$psComentario.1|nl2br}</div>
                </div>
            </div>
        </div>
    </div>
{elseif $psType == 'edit'}
    <div id="preview" class="box_cuerpo">
        <div id="new-com-html">{$psComentario.1|nl2br}</div>
    </div>
{/if}
