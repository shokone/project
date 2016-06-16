<div class="col-md-8 col-xs-12">
	<!--PUNTOS-->
	<div class="boxy xtralarge">
    	<div class="boxy-title">
            <h3>Top usuario con m&aacute;s puntos</h3>
            <span class="icon-noti puntos-n"></span>
        </div>
        <div class="boxy-content">
        	{if !$psTops.puntos}<div class="emptyData">Nada por aqui</div>
            {else}
        	<ol>
            	{foreach from=$psTops.puntos item=u}
            	<li class="categoriaUsuario clearfix">
                    <img width="16" height="16" src="{$psConfig.url}/files/avatar/{$u.user_id}_50.jpg"/>
                    <a href="{$psConfig.url}/perfil/{$u.user_name}">{$u.user_name}</a> <span>{$u.total}</span>
                </li>
                {/foreach}
            </ol>
            {/if}
        </div>
    </div>
    <!--SEGUIDORES-->
	<div class="boxy xtralarge">
    	<div class="boxy-title">
            <h3>Top usuario con m&aacute;s seguidores</h3>
            <span class="icon-noti follow-n"></span>
        </div>
        <div class="boxy-content">
        	{if !$psTops.seguidores}<div class="emptyData">Nada por aqui</div>
            {else}
        	<ol>
            	{foreach from=$psTops.seguidores item=u}
            	<li class="categoriaUsuario clearfix">
                    <img width="16" height="16" src="{$psConfig.url}/files/avatar/{$u.user_id}_50.jpg"/>
                    <a href="{$psConfig.url}/perfil/{$u.user_name}">{$u.user_name}</a> <span>{$u.total}</span>
                </li>
                {/foreach}
            </ol>
            {/if}
        </div>
    </div>
	<!--MEDALLAS-->
	<div class="boxy xtralarge">
    	<div class="boxy-title">
            <h3>Top usuario con m&aacute;s medallas</h3>
            <span class="icon-noti medallas-n"></span>
        </div>
        <div class="boxy-content">
        	{if !$psTops.medallas}<div class="emptyData">Nada por aqui</div>
            {else}
        	<ol>
            	{foreach from=$psTops.medallas item=u}
            	<li class="categoriaUsuario clearfix">
                    <img width="16" height="16" src="{$psConfig.url}/files/avatar/{$u.user_id}_50.jpg"/>
                    <a href="{$psConfig.url}/perfil/{$u.user_name}">{$u.user_name}</a> <span>{$u.total}</span>
                </li>
                {/foreach}
            </ol>
            {/if}
        </div>
    </div>
    <div class="clearBoth"></div>         
</div>