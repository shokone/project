<div id="post-comentarios">
    <!-- editor comentarios -->
    {if $psPost.post_block_comments == 1 && ($psUser->admod == 0 && $psUser->permisos.mocepc == false)}
        <div id="no-comments">El post se encuentra cerrado y no se permiten comentarios.</div>
    {elseif $psUser->admod == 0 && $psUser->permisos.gopcp == false}
        <div id="no-comments">No tienes permisos para comentar.</div>
    {elseif $psUser->member && ($psPost.post_block_comments != 1 || $psPost.post_user == $psUser->uuser_id || $psUser->admod || $psUser->permisos.gopcp) && $psPost.block == 0}
        <div class="miComentario">
            <div id="procesando"><div id="post"></div></div>
            <div class="">
                <img src="{$psConfig.url}/files/avatar/{$psUser->uid}_50.jpg"/>
                <div id="gif_cargando">
                    <img src="{$psConfig.images}/tload.gif"/>
                </div>
            </div>
            <div class="">
                <div class="">
                <div class=""></div>
                <script type="text/javascript">
                //{literal}
                    //$(document).ready(function(){
                        //CKEDITOR.replace('editor_comentarios');
                    //});
                //{/literal}
                </script>
                <textarea id="editor_comentarios" name="editor_comentarios" class="onblur_effect autogrow" tabindex="1" title="Escribir un comentario..." onfocus="onfocus_input(this)" onblur="onblur_input(this)">Escribir un comentario...</textarea>
                <div class="buttons">
                    <div class="floatL">
                        <input type="hidden" id="auser_post" value="{$psPost.post_user}" />
                        <input type="button" onclick="comentario.nuevo('true')" class="btn btnOk" value="Enviar Comentario" tabindex="3" id="btnsComment"/>
                        &nbsp;<input type="button" onclick="comentario.preview('editor_comentarios','new')" class="btn btnGreen" value="Vista Previa" tabindex="2" style="width:auto;" />
                    </div>
                </div>
                </div>
            </div>
        </div>
    {/if}
    <!-- comentarios -->
	{if $psUser->info.user_id == $psPost.post_user}
	<div >
		<span>Si hay usuarios que insultan o generan disturbios en tu post puedes bloquearlos haciendo click sobre la opci&oacute;n desplegable de su avatar.</span>
        <img alt="Bloquear Usuario" src="{$psConfig.default}/images/bloquear_usuario.png">
        <div class="both"></div>
    </div>
    {/if}
    <div class="comentarios-title">
        <h4 class="titulorespuestas floatL"><span id="ncomments">{$psPost.post_comments}</span> Comentarios</h4>
        <img src="{$psConfig.images}/tload.gif" class="floatR" id="com_gif"/>
    </div>
    {if $psPost.post_comments > $psConfig.c_max_com}
        <div class="comentarios-title">
            <div class="paginadorCom"><!--HTML de las páginas--></div>
        </div>
    {/if}
    <div id="comentarios" onload="comentario.cargar({$psPages.post_id}, 1, {$psPages.autor});">
        <script type="text/javascript">
        //{literal}
        $(document).ready(function(){
            //{/literal}
            comentario.cargar({$psPages.post_id}, 1, {$psPages.autor});
            //{literal}
        });
        //{/literal}
        </script>
        <div id="no-comments">Cargando comentarios espera un momento...</div>
    </div>
    {if $psPost.post_comments > $psConfig.c_max_com}
        <div class="comentarios-title">
            <div class="paginadorCom"></div>
        </div>
    {/if}
</div>
