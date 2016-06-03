<div class="post-body">
	<div class="post-title">
		<h1>{$psPost.post_title}</h1>
		<ul class="post-cat-date">
    		<li><strong>Categor&iacute;a:</strong> <a href="{$psConfig.url}/posts/{$psPost.categoria.c_seo}/">{$psPost.categoria.c_nombre}</a></li>
        <li><strong>Creado:</strong><span> {$psPost.post_date}.</span></li>
    </ul>
		<a title="Post Anterior (m&aacute;s viejo)" class="icons anterior" href="{$psConfig.url}/posts/prev?id={$psPost.post_id}"></a>
		<a class="aleatorio" href="{$psConfig.url}/posts/fortuitae"><img title="Post aleatorio" src="{$psConfig.tema.t_url}/images/arrow-join.png"/></a>
		<a title="Post Siguiente (m&aacute;s nuevo)" class="icons siguiente" href="{$psConfig.url}/posts/next?id={$psPost.post_id}"></a>
	    {if $psPost.puntos && ($psPost.post_user == $psUser->user_id || $psUser->admod)}
		    <div class="nodisplay" id="ver_puntos">
	    		{foreach from=$psPost.puntos item=p}
	         		<a href="{$psConfig.url}/perfil/{$p.user_name}" ><img src="{$psConfig.url}/files/avatar/{$p.user_id}_50.jpg" class="vctip" title="{$p.user_name} ha dejado {$p.cant} puntos" width="32" height="32"/></a>
	    		{/foreach}
    		</div>
    		<img title="Puntos entregados" onclick="$('#ver_puntos').slideToggle(); return false" src=""/>
    	{/if}
    </div>
	<div class="post-contenido">
		{if !$psUser->is_member}
			{include file='modulos/m.global_ads_728.tpl'}
		{/if}
		{if $psPost.post_user == $psUser->uid && $psUser->is_admod == 0 && $psUser->permisos.most == false && $psUser->permisos.moayca == false && $psUser->permisos.moo == false && $psUser->permisos.moep ==  false && $psUser->permisos.moedpo == false}
			<div class="floatR">
				<a title="Borrar Post" onclick="borrar_post(); return false;" href="" class="btnActions">
					<img alt="Borrar" src="{$psConfig.images}/borrar.png"/> Borrar</a>
				<a title="Editar Post" onclick="location.href='{$psConfig.url}/posts/editar/{$psPost.post_id}'; return false" href="" class="btnActions">
					<img alt="Editar" src="{$psConfig.images}/editar.png"/> Editar</a>
	        </div>
		{elseif ($psUser->admod && $psPost.post_status == 0) || $psUser->permisos.most || $psUser->permisos.moayca || $psUser->permisos.moop || $psUser->permisos.moep || $psUser->permisos.moedpo}
			<div class="mod-actions inline">
				<strong>Moderar Post:</strong>
				{if $psUser->is_admod || $psUser->permisos.most}
					<a href="#" onclick="mod.reboot({$psPost.post_id}, 'posts', 'sticky', false); if($(this).text() == 'Poner Sticky') $(this).text('Quitar Sticky'); else $(this).text('Poner Sticky'); return false;" class="sticky">
					{if $psPost.post_sticky == 1}Quitar{else}Poner{/if} Sticky</a>
				{/if}
				{if $psUser->admod || $psUser->permisos.moayca}
					<a href="#" onclick="mod.reboot({$psPost.post_id}, 'posts', 'openclosed', false); if($(this).text() == 'Cerrar Post') $(this).text('Abrir Post'); else $(this).text('Cerrar Post'); return false;" class="openclosed">
					{if $psPost.post_block_comments == 1}Abrir{else}Cerrar{/if} Post</a>
				{/if}
				{if $psUser->admod || $psUser->permisos.moop}
					<a id="desaprobar" href="#" onclick="$('#desapprove').slideToggle(); $(this).fadeOut().remove();" class="des_approve">Ocultar Post</a>
				{/if}

				{if $psUser->admod || $psUser->permisos.moedpo || $psAutor.user_id == $psUser->uid}
					<a href="{$psConfig.url}/posts/editar/{$psPost.post_id}" class="edit">Editar</a>
				{/if}

				{if $psUser->admod || $psUser->permisos.moep || $psAutor.user_id == $psUser->uid}
					<a href="#" onclick="{if $psAutor.user_id != $psUser->uid}mod.posts.borrar({$psPost.post_id}, 'posts', null);{else}borrar_post();{/if} return false;" class="delete">Borrar</a>
				{/if}
	        </div>
			<div id="desapprove" style="display:none;">
				<span style="display: none;" class="errormsg"></span>
				<input type="text" id="d_razon" name="d_razon" maxlength="150" size="60" class="text-inp" placeholder="Raz&oacute;n de la revisi&oacute;n" style="width:578px"/ required>
				<input type="button" class="mBtn btnDelete" name="desapprove" value="Continuar" href="#" onclick="mod.posts.ocultar('{$psPost.post_id}'); return false;"/>
			</div>
		{/if}
		<span>
			{$psPost.post_body}
		</span>
		{if $psPost.user_firma && $psConfig.c_allow_firma}
		<hr class="divider" />
		<span>
			{$psPost.user_firma}
		</span>
		{/if}
		<div>
			<div></div>
			<div></div>
			<ul>
				<li>
					<a href="http://twitter.com/share" class="twitter-share-button" data-count="vertical" data-via="{$psConfig.titulo}" data-lang="es">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
				</li>
				<li>
					<a name="fb_share" share_url="{$psConfig.url}/posts/{$psPost.categoria.c_seo}/{$psPost.post_id}/{$psPost.post_title|seo}.html" type="box_count" href="http://www.facebook.com/sharer.php">Compartir</a><script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" type="text/javascript"></script>
				</li>
				<li>
					<span class="share-t-count">{$psPost.post_shared}</span>
					<a href="{if !$psUser->member}javascript:registro_load_form(); return false{else}javascript:notifica.sharePost({$psPost.post_id}){/if}" class="share-t"></a>
				</li>
			</ul>
		</div>
		{include file='modulos/m.global_ads_728.tpl'}
	</div>
    {include file='modulos/m.post_puntos.tpl'}
</div>
