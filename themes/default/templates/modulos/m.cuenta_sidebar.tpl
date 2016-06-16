<div class="sidebar-tabs clearbeta">
	<h3>Mi Avatar</h3>
    <div class="avatar-big-cont">
    	<div class="avatar-loading nodisplay"></div>
        <img width="120" height="120" alt="" src="{$psConfig.url}/files/avatar/{if $psPerfil.p_avatar}{$psPerfil.user_id}_120{else}avatar{/if}.jpg?t={$smarty.now}" class="avatar-big" id="avatar-img"/>
    </div>
    <ul class="change-avatar nodisplay">
    	<li class="local-file">
        	<span><a onclick="avatar.chgtab(this)" id="avatar-local">Local</a></span>
            <div class="mini-modal nodisplay">
				<div class="dialog-m"></div>
				<span>Subir Archivo</span>
				<input type="file" name="file-avatar" id="file-avatar" size="15" class="browse"/><br/>
                <button onclick="avatar.upload(this)" class="avatar-next local">Subir</button>
			</div>
        </li>
    	<li class="url-file">
        	<span><a onclick="avatar.chgtab(this)" id="avatar-url">Url</a></span>
            <div class="mini-modal nodisplay">
                <div class="dialog-m"></div>
                {if $psConfig.c_allow_upload}
    				<span>Url de la imagen</span>
    				<input type="text" name="url-avatar" id="url-avatar" size="45"/><br/>
                    <button onclick="avatar.upload(this);" class="avatar-next url">Subir</button>
                {else}
                    <span>Lo sentimos por el momento solo puedes subir avatares desde tu PC.</span>
                {/if}
            </div>
        </li>
    </ul>
    <div class="clearfix"></div>
    <a onclick="avatar.edit(this)" class="edit" id="avatar-edit">Editar</a>
</div>
<div class="clearfix"></div>

<h3 id="porc-completado-label">Perfil completo al {$psPerfil.porcentaje}%</h3>
<div id="porc-completado" class="progress progress-striped active ">
    <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100" style="width: {$psPerfil.porcentaje}%;">
    <span class="sr-only">{$psPerfil.porcentaje}% completado</span>
        </div>
    </div>
</div>

<div id="prueba"></div>