{include file='secciones/main_header.tpl'}
	<script type="text/javascript" src="{$psConfig.js}/cuenta.js"></script>
    {literal}
	<script type="text/javascript">
    $(document).ready(function(){
    	// {/literal}
        avatar.uid = '{$psUser->user_id}';
        avatar.current = '{$psConfig.url}/files/avatar/{if $psPerfil.p_avatar}{$psPerfil.user_id}{else}avatar{/if}.jpg';
    	// {literal}                
        if(typeof location.href.split('#')[1] != 'undefined') {
            $('ul.menu-cuenta > li > a:contains('+location.href.split('#')[1]+')').click();
        }
    });
    </script>
    {/literal}
    <div>
    	<div class="floatL">
            <ul class="menu-cuenta">
                <li class="active"><a onclick="cuenta.chgtab(this)">Cuenta</a></li>
                <li><a onclick="cuenta.chgtab(this)">Perfil</a></li>    
                <li><a onclick="cuenta.chgtab(this)">Bloqueados</a></li>
                <li><a onclick="cuenta.chgtab(this)">Cambiar Clave</a></li>
				<li><a onclick="cuenta.chgtab(this)">Cambiar Nick</a></li>
                <li class="privacy"><a onclick="cuenta.chgtab(this)">Privacidad</a></li>
            </ul>
            <a name="alerta-cuenta"></a>
            <form class="horizontal" method="post" action="" name="editarcuenta">
            	{include file='modulos/m.cuenta_cuenta.tpl'}
                {include file='modulos/m.cuenta_perfil.tpl'}
                {include file='modulos/m.cuenta_block.tpl'}
                {include file='modulos/m.cuenta_clave.tpl'}
				{include file='modulos/m.cuenta_nick.tpl'}
                {include file='modulos/m.cuenta_config.tpl'}
            </form>
        </div>
        <div class="floatR">
            {include file='modulos/m.cuenta_sidebar.tpl'}
        </div>
    </div>
{include file='secciones/main_footer.tpl'}