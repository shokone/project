<div class="post-autor">
    	<div class="">
        	<div class=" ">Autor del post:</div>
            <!--<div class="">
            	<a href="{$psConfig.url}/rss/posts-usuario/{$psAutor.user_name}">
                	<span>
                    <img title="RSS con posts de {$psAutor.user_name}" alt="RSS con posts de Usuario" src="{$psConfig.images}/big1v12.png"/>
                    <img src="{$psConfig.images}/space.gif"/>
                    </span>
                 </a>
            </div>-->
        </div>
        <div class="">
        	<div class="">
                <a href="{$psConfig.url}/perfil/{$psAutor.user_name}">
                    <img title="Ver perfil de {$psAutor.user_name}" alt="Ver perfil de {$psAutor.user_name}" class="avatar" src="{$psConfig.url}/files/avatar/{$psAutor.user_id}_120.jpg"/>
                </a>    
    		</div>
            <a href="{$psConfig.url}/perfil/{$psAutor.user_name}">
    			<span class="given-name" style="color:#{$psAutor.rango.r_color}">{$psAutor.user_name}</span>
    		</a>
            <span class="title">{$psAutor.rango.r_name}</span>
            <img src="{$psConfig.default}/images/space.gif" class="status {$psAutor.status.css}" title="{$psAutor.status.t}"/>
            <img src="{$psConfig.default}/images/icons/ran/{$psAutor.rango.r_image}" title="{$psAutor.rango.r_name}" />
            <img src="{$psConfig.default}/images/icons/{if $psAutor.user_sexo == 0}female{else}male{/if}.png" title="{if $psAutor.user_sexo == 0}Mujer{else}Hombre{/if}" />
            <img src="{$psConfig.default}/images/flags/{$psAutor.pais.icon}.png" title="{$psAutor.pais.name}" />
            {if $psAutor.user_id != $psUser->user_id}
                <button class="btn btn-warning" onclick="{if !$psUser->member}registro_load_form();{else}mensaje.nuevo('{$psAutor.user_name}','','','');{/if}return false" title="Enviar mensaje privado">
                    <span classs="glyphicon glyphicon-envelope"></span>
                    Enviar mensaje
                </button>
            {/if}
            {if !$psUser->member}
            <hr class="divider"/>
            <a class="btn_g follow_user_post" href="#" onclick="registro_load_form(); return false"><span class="icons follow">Seguir Usuario</span></a>
            {elseif $psAutor.user_id != $psUser->uid}
            <hr class="divider"/>
            <a class="btn_g unfollow_user_post" onclick="notifica.unfollow('user', {$psAutor.user_id}, notifica.userInPostHandle, $(this).children('span'))" {if !$psAutor.follow}style="display: none;"{/if}><span class="icons unfollow">Dejar de seguir</span></a>
            <a class="btn_g follow_user_post" onclick="notifica.follow('user', {$psAutor.user_id}, notifica.userInPostHandle, $(this).children('span'))" {if $psAutor.follow > 0}style="display: none;"{/if}><span class="icons follow">Seguir Usuario</span></a>
            {/if}
            <hr class="divider"/>
            <div class="metadata-usuario">
            	<span class="nData user_follow_count">{$psAutor.user_seguidores}</span>
                <span class="txtData">Seguidores</span>
                <span class="nData" style="color: #0196ff">{$psAutor.user_puntos}</span>
                <span class="txtData">Puntos</span>
                <span class="nData">{$psAutor.user_posts}</span>
                <span class="txtData">Posts</span>
                <span  class="nData">{$psAutor.user_comentarios}</span>
                <span class="txtData">Comentarios</span>
            </div>
            {if $psUser->admod || $psUser->permisos.modu || $psUser->permisos.mosu}
            <hr class="divider"/>
        <div class="mod-actions">
            <b>Herramientas</b>
            <a href="{$psConfig.url}/moderacion/buscador/1/1/{$psPost.post_ip}" class="geoip" target="_blank">{$psPost.post_ip}</a>
            {if $psUser->admod == 1}
                <a href="{$psConfig.url}/admin/users?act=show&amp;uid={$psAutor.user_id}" class="edituser">Editar Usuario</a>
            {/if}
            {if $psAutor.user_id != $psUser->user_id} 
                <a href="#" onclick="mod.users.action({$psAutor.user_id}, 'aviso', false); return false;" class="alert">Enviar Aviso</a>
            {/if}
            {if $psAutor.user_id != $psUser->user_id && $psUser->admod || $psUser->permisos.modu || $psUser->permisos.mosu}
    			{if $psAutor.user_baneado}
                    {if $psUser->admod || $psUser->permisos.modu}
                        <a href="#" onclick="mod.reboot({$psAutor.user_id}, 'users', 'unban', false); $(this).remove(); return false;" class="unban">Desuspender Usuario</a>
                    {/if}
                    {else}
                    {if $psUser->admod || $psUser->permisos.mosu}<a href="#" onclick="mod.users.action({$psAutor.user_id}, 'ban', false); $(this).remove(); return false;" class="ban">Suspender Usuario</a>{/if}
                    {/if}
    			{/if}
            </div>
            {/if}
        </div>
    	
    	<br />
    	<div class="categoriaList estadisticasList{if $psPost.m_total == 0} nodisplay"{/if}>
        <h6>Medallas</h6>
        {if $psPost.medallas}
        	<ul>
        		{foreach from=$psPost.medallas item=m}
                    <img src="{$psConfig.tema.t_url}/images/icons/med/{$m.m_image}_16.png" class="qtip" title="{$m.m_title} - {$m.m_description}"/>
                {/foreach}
            </ul>
    	{else}
    	   <div class="emptyData">No tiene medallas</div>
        {/if}
    </div>

    {if $psPost.visitas}
    	<br />
    	<div class="categoriaList estadisticasList">
            <h6>&Uacute;ltimos visitantes</h6> 
    	    <ul style="margin-left:11px;">
                {foreach from=$psPost.visitas item=v}
                    <a href="{$psConfig.url}/perfil/{$v.user_name}" class="hovercard" uid="{$v.user_id}" ><img src="{$psConfig.url}/files/avatar/{$v.user_id}_50.jpg" class="vctip" title="{$v.date|hace:true}" width="32" height="32"/></a> 
                {/foreach}
            </ul>
    	</div>
    {/if}

</div>