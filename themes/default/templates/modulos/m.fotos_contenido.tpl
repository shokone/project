{if $psFoto.f_status != 0 || $psFoto.user_activo == 0}
	<div class="emptyData">Esta foto no es visible
		{if $psFoto.f_status == 1} por acumulaci&oacute;n de denuncias u orden administrativa
		{elseif $psFoto.f_status == 2} porque est&aacute; eliminada
		{elseif $psFoto.user_activo != 1} porque la cuenta del due&ntilde;o se encuentra desactivada
		{/if}, pero puedes verla porque eres 
		{if $psUser->admod == 1}administrador
		{elseif $psUser->admod == 2}moderador{else}autorizado
		{/if}
	</div><br />
{/if}
<div class="foto">
    <div class="v_user">
        <div class="avatar-box">
            <a href="{$psConfig.url}/perfil/{$psFoto.user_name}"><img src="{$psConfig.url}/files/avatar/{$psFoto.f_user}_50.jpg"/></a>
        </div>
        <div class="v_info">
            <a href="{$psConfig.url}/perfil/{$psFoto.user_name}" class="user">{$psFoto.user_name}</a>
            <div class="links">
                <span><strong>{$psFoto.r_name}</strong></span>
                <span>{$psFoto.user_pais.1}</span>
                <span>{if $psFoto.user_sexo == 1}Hombre{else}Mujer{/if}</span>
                {if $psUser->member && $psUser->user_id != $psFoto.f_user}
                	<span><a href="#" onclick="mensaje.nuevo('{$psFoto.user_name}','','',''); return false;">Enviar Mensaje</a></span>
                {/if}
            </div>
            {if $psUser->user_id != $psFoto.f_user && $psUser->member}
	            <div class="v_follow">
	                <a class="btn_g unfollow_user_post" onclick="notifica.unfollow('user', {$psFoto.f_user}, notifica.userInPostHandle, $(this).children('span'))" {if $psFoto.follow == 0}style="display: none;"{/if}>
	                	<span class="icons unfollow">Dejar de seguir</span>
	                </a>
	                <a class="btn_g follow_user_post" onclick="notifica.follow('user', {$psFoto.f_user}, notifica.userInPostHandle, $(this).children('span'))" {if $psFoto.follow == 1}style="display: none;"{/if}><span class="icons follow">Seguir Usuario</span></a>
					<a onclick="denuncia.nueva('foto',{$psFoto.foto_id}, '{$psFoto.f_title}', '{$psFoto.user_name}'); return false;" class="btn_g" ><span class="icons denunciar_post">Denunciar</span></a>
			    </div>
            {/if}
        </div>
        <div class="clearBoth"></div>
    </div>
    <span class="spacer"></span>
    <div id="imagen">
        {if $psFoto.f_user == $psUser->user_id || $psUser->admod || $psUser->permisos.moef || $psUser->permisos.moedfo}
        <div class="tools">
        {if $psFoto.f_status != 2 && ($psUser->admod || $psUser->permisos.moef || $psFoto.f_user == $psUser->user_id)}<a href="#" onclick="{if $psUser->user_id == $psFoto.f_user}fotos.borrar({$psFoto.foto_id}, 'foto'); {else}mod.fotos.borrar({$psFoto.foto_id}, 'foto');  {/if}return false;">
		  <img alt="Borrar" src="{$psConfig.default}/images/borrar.png"/> Borrar</a>{/if}
        {if $psUser->admod || $psUser->permisos.moedfo || $psFoto.f_user == $psUser->user_id}<a href="#" onclick="location.href='{$psConfig.url}/fotos/editar.php?id={$psFoto.foto_id}'; return false">
		  <img alt="Editar" src="{$psConfig.default}/images/editar.png"/> Editar</a>{/if}
        </div>
        {/if}
        <img class="img" src="{$psFoto.f_url}" />
    </div>
    <h2 class="floatL">{$psFoto.f_title}</h2>
    <span class="floatR"><b>{$psFoto.f_date|date_format:"%d/%m/%Y"}</b></span>
    <div class="clearBoth"></div>
    <p>{$psFoto.f_description|nl2br}</p>
    <span class="spacer"></span>
    <div class="infoPost">
  		<div class="rateBox">
  			<strong class="title">Calificar:</strong>
            <span id="actions">
    			<a title="Votar positivo" class="thumbs thumbsUp" href="#" onclick="fotos.votar('pos'); return false;"></a>
    			<a title="Votar negativo" class="thumbs thumbsDown" href="#" onclick="fotos.votar('neg'); return false;"></a>
    		</span>
  		</div>
        <div class="rateBox">
  			<strong class="title">Positivos:</strong>
            <span class="color_green" id="votos_total_pos">{$psFoto.f_votos_pos}</span>
  		</div>
        <div class="rateBox">
  			<strong class="title">Negativos:</strong>
            <span class="color_red" id="votos_total_neg">{$psFoto.f_votos_neg}</span>
  		</div>
  		<div class="metaBox">
    		<strong class="title">Visitas:</strong>
  			<span>{$psFoto.f_hits}</span>
 		</div>											
		{if $psUser->admod}						
			<div class="metaBox">                 			
				<strong class="title">IP</strong>                 			
				<span><a href="{$psConfig.url}/moderacion/buscador/1/1/{$psFoto.f_ip}" class="geoip" target="_blank">{$psFoto.f_ip}</a></span>
			</div>
		{/if}					

  		<div class="both"></div>
    </div>
</div>
<div class="bajo" style="margin-top:5px">
    <div class="comments">
        <div class="comentarios-title">
            <a href="{$psConfig.url}/rss/comentarios.php?id={$psFoto.foto_id}&type=fotos">
                <span class="floatL iconos sRss"></span>
            </a>
            <h4 class="titulorespuestas floatL"><span id="ncomments">{$psFoto.f_comments}</span> Comentarios</h4>
           <div class="clearfix"></div>
           <hr />
        </div>
        <div id="mensajes">
            {if $psFotoComentarios}
	            {foreach from=$psFotoComentarios item=c}
		            <div class="item" id="div_cmnt_{$c.cid}">
		                <a href="{$psConfig.url}/perfil/{$c.user_name}">
		                    <img src="{$psConfig.url}/files/avatar/{$c.c_user}_50.jpg" width="50" height="50" class="floatL"/>
		                </a>
		                <div class="firma">
		                    <div class="options">
		                        {if $psFoto.f_user == $psUser->info.user_id || $psUser->admod || $psUser->permisos.moecf}
		                        <a href="#" onclick="fotos.borrar({$c.cid}, 'com'); return false" class="floatR">
		            			  <img title="Borrar Comentario" alt="borrar" src="{$psConfig.default}/images/borrar.png"/>
		                        </a>
		                        {/if}
		                    </div>
							<div class="info">
								<a href="{$psConfig.url}/fotos/{$c.user_name}">{$c.user_name}</a> 
								<span>@ {$c.c_date|date_format:"%d/%m/%Y"} {if $psUser->admod}(<span>IP:</span> 
								<a href="{$psConfig.url}/moderacion/buscador/1/1/{$c.c_ip}" class="geoip" target="_blank">{$c.c_ip}</a>){/if} dijo:</span>
							</div>
							{if !$c.user_activo}
								<div>Escondido por pertener a una cuenta desactivada
									<a href="#" onclick="$('#hdn_{$c.cid}').slideDown(); $(this).parent().slideUp(); return false;">Click para verlo</a>
								</div>
								<div id="hdn_{$c.cid}" class="nodisplay">
									<div class="clearfix">{$c.c_body|nl2br}</div>
								</div>
							{/if}
									
		                </div>
		                <div class="clearBoth"></div>
		            </div>
		            {/foreach}
            {elseif $psFoto.f_closed == 0 && ($psUser->admod || $psUser->permisos.gopcf)}
            	<div class="noComments">Esta foto no tiene comentarios, Se el primero!.</div>
            {/if}
        </div>
		{if $psUser->admod == 0 && $psUser->permisos.gopcf == false}
			<div class="noComments">No tienes permiso para comentar.</div>
        {elseif $psFoto.f_closed == 1}
        	<div class="noComments">La foto se encuentra cerrada y no se permiten comentarios.</div>
        {elseif $psUser->member}
	        <div class="form">
	            <div class="avatar-box">
	                <img src="{$psConfig.url}/files/avatar/{$psUser->uid}_50.jpg" width="50" height="50"/>
	            </div>
	            <form method="post" action="" name="firmar">
	                <label for="mensaje"><b>Mensaje</b></label>
	                <div class="error"></div>
	                <textarea name="mensaje" id="mensaje" rows="2" class="onblur_effect autorow" title="Escribe un mensaje." onblur="onblur_input(this)" onfocus="onfocus_input(this)">Escribe un mensaje.</textarea>
	                <input type="hidden" name="auser_post" value="{$psFoto.f_user}" />
	                <input type="button" id="btnComment" class="btn btn-success" value="Comentar" onclick="fotos.comentar()" />
	            </form>
	            <div class="clearBoth"></div>
	        </div>
        {else}
        	<div class="emptyData">Para poder comentar necesitas estar 
	        	<a onclick="registro_load_form(); return false" href="">Registrado.</a> O.. ya tienes usuario? 
	        	<a onclick="open_login_box('open')" href="#">Logueate!</a>
        	</div>
        {/if}
    </div>
</div>