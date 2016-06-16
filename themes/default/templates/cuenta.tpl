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
    	<div class="col-md-8 col-xs-12 menuc">
            <ul class="menu-cuenta nav nav-tabs">
                <li class="active"><a href="#" onclick="cuenta.chgtab(this)">Cuenta</a></li>
                <li><a href="#" onclick="cuenta.chgtab(this)">Perfil</a></li>    
                <li><a href="#" onclick="cuenta.chgtab(this)">Bloqueados</a></li>
                <li><a href="#" onclick="cuenta.chgtab(this)">Cambiar Clave</a></li>
				<li><a href="#" onclick="cuenta.chgtab(this)">Cambiar Nick</a></li>
                <li class="privacy" href="#"><a onclick="cuenta.chgtab(this)">Privacidad</a></li>
            </ul>
            <a name="alerta-cuenta"></a>
            <form class="horizontal" method="post" action="" name="editarcuenta">
            	{include file='modulos/m.cuenta_cuenta.tpl'}
                {include file='modulos/m.cuenta_perfil.tpl'}
                {include file='modulos/m.cuenta_block.tpl'}
                {include file='modulos/m.cuenta_pass.tpl'}
				{include file='modulos/m.cuenta_nick.tpl'}
                {include file='modulos/m.cuenta_privacidad.tpl'}
            </form>
        </div>
        <div class="col-md-4 col-xs-8">
            {include file='modulos/m.cuenta_sidebar.tpl'}
        </div>
    </div>
{include file='secciones/main_footer.tpl'}