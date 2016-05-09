{include file='secciones/main_header.tpl'}
				<a name="arriba"></a>
                {if $psPost.post_status > 0 || $psAutor.user_activo != 1}
                    <div class="emptyData">Este post se encuentra 
                    {if $psPost.post_status == 2}eliminado
                    {elseif $psPost.post_status == 1} inactivo por acumulaci&oacute;n de denuncias
                    {elseif $psPost.post_status == 3} en revisi&oacute;n
                    {elseif $psPost.post_status == 3} en revisi&oacute;n
                    {elseif $tsAutor.user_activo != 1} oculto porque pertenece a una cuenta desactivada
                    {/if}, 
                    t&uacute; puedes verlo porque 
                    {if $tsUser->is_admod == 1}eres un administrador
                    {elseif $tsUser->is_admod == 2}eres un moderador
                    {else}
                    tienes permiso{/if}.</div><br />
                {/if}
				<div>
                    <div class="col-md-8">
                    	{include file='modulos/m.posts_autor.tpl'}
                        {include file='modulos/m.posts_contenido.tpl'}
                        <a name="comentarios"></a>
                        {include file='modulos/m.posts_comentarios.tpl'}
                        <a name="comentarios-abajo"></a>
                        <br />
                       	{if !$psUser->member}
                            <div class="emptyData clearfix">
                            	Para poder comentar necesitas estar <a onclick="registro_load_form(); return false" href="">Registrado.</a> O.. ya tienes usuario? <a onclick="open_login_box('open')" href="#">Accede!</a>
                            </div>
                        {elseif $psPost.block > 0}
                            <div class="emptyData clearfix">
                            	{$psPost.user_name} te ha bloqueado y no podr&aacute;s comentar sus post.
                            </div>
                        {/if}
                    </div>
                    <div class="col-md-4">
                        {include file='modulos/m.posts_relacionados.tpl'}
                        {include file='modulos/m.posts_banner.tpl'}
                    </div>
                    <div class="center"><a class="irArriba" href="#arriba"><strong>Volver arriba</strong></a></div>
                </div>
{include file='secciones/main_footer.tpl'}