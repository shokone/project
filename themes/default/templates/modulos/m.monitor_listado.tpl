<div class="col-md-8 col-xs-12 panel panel-default">
<div class="panel-body">
{if $psDatos}
    <ul class="listado">
    	{if $psAction == 'seguidores' || $psAction == 'siguiendo'}
        	{foreach from=$psDatos.data item=u}
                <li class="clearfix">
                    <div class="listado-content clearfix">
                        <div class="listado-avatar">
                            <a href="{$psConfig.url}/perfil/{$u.user_name}"><img src="{$psConfig.url}/files/avatar/{$u.user_id}_50.jpg"/></a>
                        </div>
                        <div class="txt">
                            <a href="{$psConfig.url}/perfil/{$u.user_name}">{$u.user_name}</a><br />
                            <img src="{$psConfig.default}/images/flags/{$u.user_pais|lower}.png"/> <span class="grey">{$u.p_mensaje}</span>
                        </div>
                    </div>
                    <div class="action">
                        <div{if $u.follow == 0 && $psAction != 'siguiendo'} style="display: none;"{/if} class="btn_follow ruser{$u.user_id}">
                            <a title="Dejar de seguir" onclick="notifica.unfollow('user', {$u.user_id}, notifica.ruserInAdminHandle, this)"><span class="remove_follow"></span></a>
                        </div>
                        <div{if $u.follow == 1 || $psAction == 'siguiendo'} style="display: none;"{/if} class="btn_follow ruser{$u.user_id}">
                            <a title="Seguir usuario" onclick="notifica.follow('user', {$u.user_id}, notifica.ruserInAdminHandle, this)"><span class="add_follow"></span></a>
                        </div>
                    </div>
                </li>
        	{/foreach}
        {elseif $psAction == 'posts'}
        	{foreach from=$psDatos.data item=p}
                <li class="clearfix">
                    <div class="listado-content clearfix">
                        <div class="listado-avatar">
                            <a href="{$psConfig.url}/perfil/{$p.user_name}"><img src="{$psConfig.url}/files/avatar/{$p.post_user}_50.jpg"/></a>
                        </div>
                        <div class="txt">
                            <a href="{$psConfig.url}/posts/{$p.c_seo}/{$p.f_id}/{$p.post_title|seo}.html">{$p.post_title}</a><br />
                            <img src="{$psConfig.images}/icons/cat/{$p.c_img}"/> <span class="grey">{$p.c_nombre}</span>
                        </div>
                    </div>
                    <div class="action">
                        <div class="btn_follow list{$p.f_id}">
                            <a title="Dejar de seguir" onclick="notifica.unfollow('post', {$p.f_id}, notifica.ruserInAdminHandle, this)"><span class="remove_follow"></span></a>
                        </div>
                        <div style="display: none;" class="btn_follow list{$p.f_id}">
                            <a title="Seguir post" onclick="notifica.follow('post', {$p.f_id}, notifica.ruserInAdminHandle, this)"><span class="add_follow"></span></a>
                        </div>
                    </div>
                </li>
        	{/foreach}
        {/if}
        {if $psDatos.pages}
            <li class="listado-paginador clearfix">
        		{if $psDatos.pages.prev != 0}
                    <a href="{$psConfig.url}/monitor/{$psAction}?page={$psDatos.pages.prev}" class="anterior-listado floatL">Anterior</a>
                {/if}
                {if $psDatos.pages.next != 0}
                    <a href="{$psConfig.url}/monitor/{$psAction}?page={$psDatos.pages.next}" class="siguiente-listado floatR">Siguiente</a>
                {/if}
        	</li>
        {/if}
    </ul>
{else}
    <div class="emptyData">Nada por aqu&iacute;</div>
{/if}
</div>
</div>