<div class="post-metadata floatL">
	<div>
    	<div class="mensajes"></div>
        {if ($psUser->is_admod || $psUser->permisos.godp) && $psUser->is_member == 1 && $psPost.post_user != $psUser->uid && $psUser->info.user_puntosxdar >= 1}
            <div class="dar-puntos">
                {if $psPunteador.rango >= 50}
            		<div>
                        <input type="number" id="points" value="{$psPunteador.rango}" min="1" max="{$psPunteador.rango}"/>
                        <input type="submit" onclick="votar_post(document.getElementById('points').value); return false;" value="Votar" class="btn_g" >
                    </div>
                {else}
                    <span>Dar Puntos:</span>
                    {section name=puntos start=1 loop=$psUser->info.user_puntosxdar+1 max=$psPunteador.rango}
                        <a href="#" onclick="votar_post({$smarty.section.puntos.index}); return false;">{$smarty.section.puntos.index}</a>
                        {if $smarty.section.puntos.index < $psPunteador.rango}-{/if}
                    {/section}
                {/if}
            <span>(de {$psUser->info.user_puntosxdar} disponibles)</span>
            </div>
        {/if}
        <div class="post-acciones">
        	<ul>
                {if !$psUser->member}
                    <li>
                        <a class="btn_g follow_user_post" href="#" onclick="registro_load_form(); return false">
                        <span class="icons follow_post follow">Seguir Post</span></a>
                    </li>
                {elseif $psPost.post_user != $psUser->user_id}
                    <li{if !$psPost.follow} class="nodisplay"{/if}>
                        <a class="btn_g unfollow_post" onclick="notifica.unfollow('post', {$psPost.post_id}, notifica.inPostHandle, $(this).children('span'))">
                        <span class="icons follow_post unfollow">Dejar de seguir</span></a>
                    </li>
                    <li{if $psPost.follow > 0} class="nodisplay"{/if}>
                        <a class="btn_g follow_post" onclick="notifica.follow('post', {$psPost.post_id}, notifica.inPostHandle, $(this).children('span'))">
                        <span class="icons follow_post follow">Seguir Post</span></a>
                    </li>
    				<li>
                        <a href="#" onclick="{if !$psUser->member}registro_load_form(){else}add_favoritos(){/if}; return false" class="btn_g">
                        <span class="icons agregar_favoritos">Agregar a Favoritos</span></a>
                    </li>
    				<li>
                        <a href="#" onclick="denuncia.nueva('post',{$psPost.post_id}, '{$psPost.post_title}', '{$psPost.user_name}'); return false;" class="btn_g">
                        <span class="icons denunciar_post">Denunciar</span></a>
                    </li>
                {/if}
                </ul>
        </div>
        <ul class="post-estadisticas">
			<li><span class="icons medallas">{$psPost.m_total}</span><br />Medalla{if $psPost.m_total != 1}s{/if}</li>
        	<li><span class="icons favoritos_post">{$psPost.post_favoritos}</span><br />Favoritos</li>
            <li><span class="icons visitas_post">{$psPost.post_hits}</span><br />Visitas</li>
            <li><span id="puntos_post" class="icons puntos_post">{$psPost.post_puntos}</span><br />Puntos</li>
            <li><span class="icons monitor">{$psPost.post_seguidores}</span><br />Seguidores</li>
        </ul>
        <div class="clearfix"></div>
        <hr class="divider"/>
        <div class="clearfix"></div>
	</div>
</div>
