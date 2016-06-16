<div id="user_box" class="post-autor vcard" >
	<div class="avatarBox" >
        <a href="{$psConfig.url}/perfil/{$psUser->nick}">
            <img title="Ver tu perfil" class="avatar" src="{$psConfig.url}/files/avatar/{$psUser->user_id}_120.jpg"/>
        </a>
	</div>
    <a href="{$psConfig.url}/perfil/{$psUser->nick}">
		<span class="given-name" style="color:#{$psUser->info.rango.r_color}">{$psUser->nick}</span>
	</a>
    <hr class="divider"/>
    <div class="tools">
        <a href="{$psConfig.url}/monitor/">Notificaciones (<strong>{$psNotificaciones}</strong>)</a><br>
        <a href="{$psConfig.url}/mensajes/">Mensajes nuevos (<strong>{$psMensajes}</strong>)</a>
        <hr class="divider"/>
        <a href="{$psConfig.url}/agregar/">Agregar post</a><br>
        <a href="{$psConfig.url}/fotos/agregar.php">Agregar foto</a>
        <hr class="divider"/>
        <a href="{$psConfig.url}/cuenta/">Editar mi cuenta</a><br>
        <a href="{$psConfig.url}/login-salir.php" class="salir">Cerrar sesi&oacute;n</a>
    </div>
</div>