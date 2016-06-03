{if $psComentarios.num > 0}
  {foreach from=$psComentarios.data item=c}
    <div id="div_cmnt_{$c.cid}" class="{if $psPost.autor == $c.c_user}especial1{elseif $c.c_user == $psUser->user_id}especial3{/if}">
      <span id="citar_comm_{$c.cid}" class="nodisplay">{$c.c_body}</span>
      <div class="comentario-post clearbeta">
        <div class="avatar-box" style="z-index: 99;">
          <a href="{$psConfig.url}/perfil/{$c.user_name}">
            <img width="48" height="48" title="Avatar de {$c.user_name} en {$psConfig.titulo}" src="{$psConfig.url}/files/avatar/{$c.c_user}_50.jpg" class="avatar-48 lazy">
          </a>
          {if $psUser->member && $psUser->info.user_id != $c.c_user}
            <ul class="nodisplay">
            <li class="enviar-mensaje"><a href="#" onclick="mensaje.nuevo('{$c.user_name}','','',''); return false">Enviar Mensaje <span></span></a></li>
            <li class="bloquear {if $psComentarios.block}des{/if}bloquear_1">
              <a href="javascript:bloquear({$c.c_user}, {if $psComentarios.block}false{else}true{/if}, 'comentarios')">{if $psComentarios.block}Desbloquear{else}Bloquear{/if}</a>
            </li>
            </ul>
          {/if}
        </div>

        <div class="comment-box" id="pp_{$c.cid}" {if $c.c_status == 1 || !$c.user_activo && $psUser->admod}style="opacity:0.5"{/if} >
          <div class="dialog-c"></div>
          <div class="comment-info clearbeta">
            <div class="floatL">
              <a href="{$psConfig.url}/perfil/{$c.user_name}" class="nick">{$c.user_name}</a> {if $psUser->admod}(<span style="color:red;">IP:</span> <a href="{$psConfig.url}/moderacion/buscador/1/1/{$c.c_ip}" class="geoip" target="_blank">{$c.c_ip}</a>){/if} dijo
              <span>{$c.c_date|hace} </span>
            </div>
            {if $psUser->member}
              <div class="floatR answerOptions" id="opt_{$c.cid}">
                <ul id="ul_cmt_{$c.cid}">
                  {*if $psUser->info.user_rango || $psUser->info.user_rango_post != 3*}
                    <li class="numbersvotes {if $c.c_votos == 0}nodisplay"{/if}>
                      <div class="overview">
                        <span class="{if $c.c_votos >= 0}positivo{else}negativo{/if}" id="votos_total_{$c.cid}">{if $c.c_votos != 0}{if $c.c_votos >= 0}+{/if}{$c.c_votos}{/if}</span>
                      </div>
                    </li>
                    {if $psUser->user_id != $c.c_user && $c.votado == 0 && ($psUser->permisos.govpp || $psUser->permisos.govpn || $psUser->admod)}
                      {if $psUser->permisos.govpp || $psUser->admod}
                        <li class="icon-thumb-up">
                          <a onclick="comentario.votar({$c.cid},1)">
                            <span class="voto-p-comentario"></span>
                          </a>
                        </li>
                      {/if}
                      {if $psUser->permisos.govpn || $psUser->admod}
                        <li class="icon-thumb-down">
                          <a onclick="comentario.votar({$c.cid},-1)">
                            <span class="voto-n-comentario"></span>
                          </a>
                        </li>
                      {/if}
                    {/if}
                  {*/if*}
                  {if $psUser->member}
                    <li class="answerCitar">
                      <a onclick="citar_comment({$c.cid}, '{$c.user_name}')" title="Citar">
                        <span class="citar-comentario"></span>
                      </a>
                    </li>
                    {if ($c.c_user == $psUser->user_id && $psUser->permisos.goepc) || $psUser->admod || $psUser->permisos.moedcopo}
                      <li>
                        <a onclick="comentario.editar({$c.cid}, 'show')" title="Editar comentario">
                          <span class="{if $c.c_user == $psUser->user_id}editar{else}moderar{/if}-comentario"></span>
                        </a>
                      </li>
                    {/if}
                    {if ($c.c_user == $psUser->user_id && $psUser->permisos.godpc) || $psUser->admod || $psUser->permisos.moecp}
                      <li class="iconDelete">
                        <a onclick="borrar_com({$c.cid}, {$c.c_user}, {$c.c_post_id})" title="Borrar">
                          <span class="borrar-comentario"></span>
                        </a>
                      </li>
                      {if $psUser->admod || $psUser->permisos.moaydcp}
                        <li class="iconHide">
                          <a onclick="ocultar_com({$c.cid}, {$c.c_user});" title="{if $c.c_status == 1}Mostrar/Ocultar{else}Ocultar/Mostrar{/if}">
                            <span class="moderar-comentario"></span>
                          </a>
                        </li>
                      {/if}
                    {/if}
                  {/if}
                </ul>
              </div>
            {/if}
          </div>
          <div id="comment-body-{$c.cid}" class="comment-content">
            {if $c.c_votos <= -3 || $c.c_status == 1 || !$c.user_activo || $c.user_baneado}
              <div>Escondido {if $c.c_status == 1}por un moderador{elseif $c.c_votos <= -3}por un puntaje bajo{elseif $c.user_activo == 0}por pertener a una cuenta desactivada{else}por pertenecer a una cuenta baneada{/if}.
                <a href="#" onclick="$('#hdn_{$c.cid}').slideDown(); $(this).parent().slideUp(); return false;">Click para verlo</a>.
              </div>
              <div id="hdn_{$c.cid}" class="nodisplay">
            {/if}
            {$c.c_html}
            {if $c.c_votos <= -3 || $c.c_status == 1 || !$c.user_activo}</div>{/if}
          </div>
        </div>
      </div>
    </div>
  {/foreach}
{else}
  <div id="no-comments">Este post no tiene comentarios, S&eacute; el primero!</div>
{/if}
<div id="nuevos"></div>
{literal}
  <script type="text/javascript">
    $(function(){
      var zIndex = 99;
      $('div.avatar-box').each(function(){
        $(this).css('zIndex', zIndex);
        zIndex -= 1;
      });
    });
  </script>
{/literal}
