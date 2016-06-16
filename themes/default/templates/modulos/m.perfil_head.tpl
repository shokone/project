<div class="perfil-user container">
	<div class="box-perfil row">
		<div class="perfil-datos col-md-4 col-sm-8">
			<div class="perfil-avatar">
            	<a href="{$psConfig.url}/perfil/{$psInfo.nick}"><img alt="" src="{$psConfig.url}/files/avatar/{if $psInfo.p_avatar}{$psInfo.uid}_120{else}avatar{/if}.jpg"/></a>
            </div>
            <div class="perfil-info">
	        	<h1 class="nick">{$psInfo.nick}</h1>
	            <span class="username">{$psInfo.p_nombre}</span>
	            <span class="frase-user">{$psInfo.p_mensaje}</span>
	            <span class="perfil-user-data">
		            {if $psInfo.p_nombre != ''}{$psInfo.p_nombre} es {else}Es {/if}
		            {if $psInfo.user_sexo == 1}un hombre{else}una mujer{/if}. Vive en <span id="info_pais">{$psInfo.user_pais}</span> y se uni&oacute; a la familia de {$psConfig.titulo} el {$psInfo.user_registro|fecha:true}. 
		            {if $psInfo.p_empresa}Trabaja en {$psInfo.p_empresa}{/if}
	            </span>
	            {if $psUser->user_id != $psInfo.uid && $psUser->member}
	            <span class="opc_mod">
	                <a href="javascript:bloquear({$psInfo.uid}, {if $psInfo.block.bid}false{else}true{/if}, 'perfil')" id="bloquear_cambiar">
	                	{if $psInfo.block.bid}Desbloquear{else}Bloquear{/if}
	                </a>
	                <a href="#" onclick="denuncia.nueva('usuario',{$psInfo.uid}, '', '{$psInfo.nick}'); return false">Denunciar</a>
	                {if ($psUser->admod || $psUser->permisos.mosu) && !$psInfo.user_baneado}
	                	<a href="#" onclick="mod.users.action({$psInfo.uid}, 'ban', true); return false;">Suspender</a>
	                {/if}
					{if !$psInfo.user_activo || $psInfo.user_baneado}
						<span>Cuenta {if !$psInfo.user_activo}desactivada{else}baneada{/if}</span>
					{/if}
			   </span>
	            <br />
	            <a class="btn_g unfollow_user_post" onclick="notifica.unfollow('user', {$psInfo.uid}, notifica.userInPostHandle, $(this).children('span'))" {if $psInfo.siguiendo == 0}style="display: none;"{/if}><span class="iconos unfollow">Dejar de seguir</span></a>
	            <a class="btn_g follow_user_post" onclick="notifica.follow('user', {$psInfo.uid}, notifica.userInPostHandle, $(this).children('span'))" {if $psInfo.siguiendo == 1}style="display: none;"{/if}><span class="iconos follow">Seguir Usuario</span></a>
	            {/if}
	        </div>
		</div>
		<div class="perfil-level col-md-4 col-sm-8">
			<ul class="">
				<li style="position:relative;color:#{$psInfo.stats.r_color}; background-color:#FFF">
					<strong style="color:#{$psInfo.stats.r_color}">{$psInfo.stats.r_name}</strong>
					<span>Rango</span>
                    <span style="position:absolute;top:11px;right:6px">
                    	<span title="{$psInfo.status.t}" style="float: left;" class="qtip status {$psInfo.status.css}"></span>
                    </span>
				</li>
				<li>
					<strong>{$psInfo.stats.user_puntos}</strong>
					<span>Puntos</span>
				</li>
				<li>
					<strong>{$psInfo.stats.user_posts}</strong>
					<span>Posts</span>
				</li>
				<li>
					<strong>{$psInfo.stats.user_comentarios}</strong>
					<span>Comentarios</span>
				</li>
				<li>
					<strong>{$psInfo.stats.user_seguidores}</strong>
					<span>Seguidores</span>
				</li>
				<li>
					<strong>{$psInfo.stats.user_fotos}</strong>
					<span>Fotos</span>
				</li>

			</ul>
		</div>
	</div>
	</div>
	<div class="menu-perfil clearfix menuc">
    	<ul id="tabs_menu" class="nav nav-tabs">
            {if $psType == 'news' || $psType == 'story'}
            	<li class="active">
            		<a href="#" onclick="perfil.load_tab('news', this); return false">{if $psType == 'story'}Publicaci&oacute;n{else}Noticias{/if}</a>
            	</li>
            {/if}
            <li {if $psType == 'wall'}class="selected"{/if}>
            	<a href="#" onclick="perfil.load_tab('wall', this); return false">Muro</a>
            </li>
            <li><a href="#" onclick="perfil.load_tab('actividad', this); return false" id="actividad">Actividad</a></li>
            <li><a href="#" onclick="perfil.load_tab('info', this); return false" id="informacion">Informaci&oacute;n</a></li>
            <li><a href="#" onclick="perfil.load_tab('posts', this); return false">Posts</a></li>
            <li><a href="#" onclick="perfil.load_tab('seguidores', this); return false" id="seguidores">Seguidores</a></li>
            <li><a href="#" onclick="perfil.load_tab('siguiendo', this); return false" id="siguiendo">Siguiendo</a></li>
            <li><a href="#" onclick="perfil.load_tab('medallas', this); return false">Medallas</a></li>
			{if $psUser->user_id != $psInfo.uid}
	            <li class="enviar-mensaje">
	                {if $psUser->member}
	                <a href="#" onclick="mensaje.nuevo('{$psInfo.nick}','','',''); return false">
	                	<span style="float:none; height:14px;width:16px;" class="systemicons mensaje"></span>
	                </a>
	                {/if}
	            </li>
            {/if}
            {if $psInfo.p_socials.f}
	            <li class="floatR">
					<a target="_blank" href="http://www.facebook.com/{$psInfo.p_socials.f}" title="Facebook">
						<img height="14" width="14" src="{$psConfig.default}/images/icons/facebook.png"/>
					</a>
				</li>
            {/if}
            {if $psInfo.p_socials.t}
	            <li class="floatR">
					<a target="_blank" href="http://www.twitter.com/{$psInfo.p_socials.t}" title="Twitter">
						<img height="14" width="14" src="{$psConfig.default}/images/icons/twitter.png"/>
					</a>
				</li>
            {/if}
			{if $psUser->admod == 1}
	            <li class="floatR">
					<a href="#" onclick="location.href = '{$psConfig.url}/admin/users?act=show&amp;uid={$psInfo.uid}'">
						<img title="Editar a {$psInfo.nick}" src="{$psConfig.url}/themes/default/images/icons/editar.png" class="vctip"/>
						Editar a {$psInfo.nick}
					</a>
	            </li>
            {/if}
        </ul>
    </div>
