<div class="post-body">
	<div class="btn-group btn-group-justified btn-group-lg btn-group-sm">
		<button type="button" class="btn btn-default width-3">
			<a title="Post Anterior (m&aacute;s viejo)" href="{$psConfig.url}/posts/prev?id={$psPost.post_id}">Post Anterior</a>
		</button>
		<button type="button" class="btn btn-default width-3">
			<a href="{$psConfig.url}/posts/fortuitae">Aleatorio</a>
		</button>
		<button type="button" class="btn btn-default width-3">
			<a title="Post Siguiente (m&aacute;s nuevo)" href="{$psConfig.url}/posts/next?id={$psPost.post_id}">Post Siguiente</a>
    	</button>
	</div>
	<div class="page-header">
		<h1>{$psPost.post_title}<br>
		<small><h5>
			Creado el {$psPost.post_date} by <a href="{$psConfig.url}/perfil/{$psAutor.user_name}">{$psAutor.user_name}</a> en la categor&iacute;a 
			<a href="{$psConfig.url}/posts/{$psPost.categoria.c_seo}/">{$psPost.categoria.c_nombre}</a></h5>
		</small></h1>
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
			{include file='modulos/m.global_ads_728.tpl'}
		
			{if $psPost.post_user == $psUser->user_id && $psUser->admod == 0 && $psUser->permisos.most == false && $psUser->permisos.moayca == false && $psUser->permisos.moo == false && $psUser->permisos.moep ==  false && $psUser->permisos.moedpo == false}
				<div class="panel panel-default"><div class="panel-body">
					<div class="btn-group">
						<button type="button" class="btn btn-danger" title="Borrar Post" onclick="borrar_post(); return false;">Borrar</button>
						<button type="button" class="btn btn-info" title="Editar Post" onclick="location.href='{$psConfig.url}/posts/editar/{$psPost.post_id}'; return false">Editar</button>
			        </div>
		        </div>
			{elseif ($psUser->admod && $psPost.post_status == 0) || $psUser->permisos.most || $psUser->permisos.moayca || $psUser->permisos.moop || $psUser->permisos.moep || $psUser->permisos.moedpo}
				<div class="panel panel-default"><div class="panel-body">
					<div class="mod-actions inline">
						<strong>Moderar Post:</strong><br>
						{if $psUser->admod || $psUser->permisos.most}
						<button type="button" class="btn {if $psPost.post_sticky == 1}btn-danger{else}btn-success{/if}" title="Editar Post">
							<a href="#" onclick="mod.posts.reboot({$psPost.post_id}, 'posts', 'sticky', false); if($(this).text() == 'Poner Sticky') $(this).text('Quitar Sticky'); else $(this).text('Poner Sticky'); return false;" class="sticky">
							{if $psPost.post_sticky == 1}Quitar{else}Poner{/if} Sticky</a>
							</button>
						{/if}
						{if $psUser->admod || $psUser->permisos.moayca}
							<button type="button" class="btn {if $psPost.post_block_comments == 1} btn-success{else} btn-danger{/if}" title="{if $psPost.post_block_comments == 1}Abrir{else}Cerrar{/if} comentarios Post">
								<a href="#" onclick="mod.posts.reboot({$psPost.post_id}, 'posts', 'openclosed', false); if($(this).text() == 'Cerrar Post') $(this).text('Abrir Post'); else $(this).text('Cerrar Post'); return false;">
								{if $psPost.post_block_comments == 1}Abrir{else}Cerrar{/if} Post</a>
							</button>
						{/if}
						<!--{if $psUser->admod || $psUser->permisos.moop}
							<button type="button" class="btn btn-danger" title="Ocultar Post">
								<a id="desaprobar" href="#" onclick="$('#desapprove').slideToggle();">Ocultar Post</a>
							</button>
						{/if}-->

						{if $psUser->admod || $psUser->permisos.moedpo || $psAutor.user_id == $psUser->user_id}
							<button type="button" class="btn btn-info" title="Editar Post">
								<a href="{$psConfig.url}/posts/editar/{$psPost.post_id}" class="edit">Editar</a>
							</button>
						{/if}

						{if $psUser->admod || $psUser->permisos.moep || $psAutor.user_id == $psUser->user_id}
							<button type="button" class="btn btn-danger" title="Borrar Post">
								<a href="#" onclick="{if $psAutor.user_id != $psUser->user_id}mod.posts.borrar({$psPost.post_id}, 'posts', null);{else}borrar_post();{/if} return false;" class="delete">Borrar</a>
							</button>
						{/if}
			        </div>
					<div id="desapprove" class="nodisplay">
						<span class="errormsg nodisplay"></span>
						<input type="text" id="d_razon" name="d_razon" maxlength="150" size="60" class="text-inp" placeholder="Raz&oacute;n de la revisi&oacute;n" required/>
						<input type="button" class="btn btn-danger" name="desapprove" value="Continuar" href="#" onclick="mod.posts.ocultar('{$psPost.post_id}'); return false;"/>
					</div>
				</div></div>
			{/if}
		<div class="panel panel-default">
			<div class="panel-body">
				{$psPost.post_body}
			</div>
		</div>
		{if $psPost.user_firma && $psConfig.c_allow_firma}
			<hr class="divider" />
			<div class="panel panel-default">
				<div class="panel-body">
					{$psPost.user_firma}
				</div>
			</div>
		{/if}
		<div class="panel panel-default">
			<div class="panel-body">
				<ul class="menu-compartir-post floatR">
					<li>
						<span class="share-it-count">{$psPost.post_shared}</span>
						<a href="{if !$psUser->member}javascript:registro_load_form(); return false{else}javascript:notifica.sharePost({$psPost.post_id}){/if}" class="share-it"></a>
					</li>
					<li>
						<a href="http://twitter.com/share" class="twitter-share-button" data-count="vertical" data-via="{$psConfig.titulo}" data-lang="es">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
					</li>
					<li>
						<a name="fb_share" share_url="{$psConfig.url}/posts/{$psPost.categoria.c_seo}/{$psPost.post_id}/{$psPost.post_title|seo}.html" type="box_count" href="http://www.facebook.com/sharer.php">Compartir</a><script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" type="text/javascript"></script>
					</li>
					
				</ul>
			</div>
		</div>
		{include file='modulos/m.global_ads_728.tpl'}
	</div>
    {include file='modulos/m.post_puntos.tpl'}
</div>
