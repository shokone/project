<div class="post-metadata ">
	<div class="panel panel-default">
        <div class="panel-body">
        	<div class="mensajes"></div>
            {if ($psUser->admod || $psUser->permisos.godp) && $psUser->member == 1 && $psPost.post_user != $psUser->user_id && $psUser->info.user_puntosxdar >= 1}
                <div class="dar-puntos">
                {if $psPuntos.rango >= 50}
            		<div>
                        <input type="number" id="points" value="{$psPuntos.rango}" min="1" max="{$psPuntos.rango}"/>
                        <input type="submit" onclick="votar_post(document.getElementById('points').value); return false;" value="Votar" class="btn_g" >
                    </div>
                {else}
                    <span><strong>Dar Puntos: </strong>(tienes {$psUser->info.user_puntosxdar} disponibles)</span><br>
                    <div class="btn-group">
                        {section name=puntos start=1 loop=$psUser->info.user_puntosxdar+1 max=$psPuntos.rango}
                            <button type="button" class="btn btn-success" onclick="votar_post({$smarty.section.puntos.index}); return false;">
                                {$smarty.section.puntos.index}
                            </button>
                            {if $smarty.section.puntos.index == 10}<br><br>{/if}
                        {/section}
                    </div>
                {/if}
                </div>
            {/if}
            <hr class="divider" />
            <div class="post-acciones text-center">
            	<ul>
                    <li {if !$psPost.post_seguidores} style="display:none !important;" {/if}>
                        <button class="btn btn-danger unfollow_post" onclick="notifica.unfollow('post', {$psPost.post_id}, notifica.inPostHandle, $(this).children('span'))">
                            <span class="glyphicon glyphicon-eye-close"></span>
                            <span class="follow_post unfollow">Dejar de seguir</span>
                        </button>
                    </li>
                    <li {if $psPost.post_seguidores > 0} style="display:none !important;"{/if}>
                        <button class="btn btn-success follow_post" onclick="notifica.follow('post', {$psPost.post_id}, notifica.inPostHandle, $(this).children('span'))">
                            <span class="glyphicon glyphicon-eye-open"></span>
                            <span class="follow_post follow">Seguir Post</span>
                        </button>
                    </li>
    				<li>
                        <button onclick="{if !$psUser->member}registro_load_form(){else}add_favoritos(){/if}; return false" class="btn btn-warning">
                        <span class="glyphicon glyphicon-star"></span>
                        <span class="agregar_favoritos">Agregar a Favoritos</span></button>
                    </li>
    				<li>
                        <button onclick="denuncia.nueva('post',{$psPost.post_id}, '{$psPost.post_title}', '{$psPost.user_name}'); return false;" class="btn btn-danger">
                        <span class="glyphicon glyphicon-warning-sign"></span>
                        <span class="denunciar_post">Denunciar</span></button>
                    </li>
                </ul>
            </div>
            <hr class="divider" />
            <div>
                <ul class="post-estadisticas text-center">
        			<li><span class="medallas">{$psPost.m_total}</span><br />Medalla{if $psPost.m_total != 1}s{/if}</li>
                	<li><span class="favoritos_post">{$psPost.post_favoritos}</span><br />Favoritos</li>
                    <li><span class="visitas_post">{$psPost.post_hits}</span><br />Visitas</li>
                    <li><span id="puntos_post" class="puntos_post">{$psPost.post_puntos}</span><br />Puntos</li>
                    <li><span class="monitor">{$psPost.post_seguidores}</span><br />Seguidores</li>
                </ul>
                <div class="clearfix"></div>
                <div class="both"></div>
            </div>
        </div>
	</div>
</div>
