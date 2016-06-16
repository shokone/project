<div id="post-comentarios">
    <!-- comentarios -->
	<!--{if $psUser->info.user_id == $psPost.post_user}
    	<div >
    		<span>Si hay usuarios que insultan o generan disturbios en tu post puedes bloquearlos haciendo click sobre la opci&oacute;n desplegable de su avatar.</span>
            <img alt="Bloquear Usuario" src="{$psConfig.default}/images/bloquear_usuario.png">
            <div class="both"></div>
        </div>
    {/if}-->
    <div class="comentarios-title">
        <h4 class="titulorespuestas floatL"><span id="ncomments">{$psPost.post_comments}</span> Comentarios</h4>
    </div>
    {if $psPost.post_comments > $psConfig.c_max_com}
        <div class="comentarios-title">
            <div class="paginadorCom"><!--HTML de las páginas--></div>
        </div>
    {/if}
    <br>
    <div id="comentarios">
        <script type="text/javascript">
        //{literal}
        $(document).ready(function(){
            /*//{/literal}*/
            comentario.cargar({$psPages.post_id}, 1, {$psPages.autor});
            /*//{literal}*/
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

    <div class="both"></div>
    <hr class="divider" />

    <!-- editor comentarios -->
    {if $psPost.post_block_comments == 1 && ($psUser->admod == 0 && $psUser->permisos.mocepc == false)}
        <div id="no-comments">El post se encuentra cerrado y no se permiten comentarios.</div>
    {elseif $psUser->admod == 0 && $psUser->permisos.gopcp == false}
        <div id="no-comments">No tienes permisos para comentar.</div>
    {elseif $psUser->member && ($psPost.post_block_comments != 1 || $psPost.post_user == $psUser->user_id || $psUser->admod || $psUser->permisos.gopcp) && $psPost.block == 0}
        <div class="miComentario">
            <div id="procesando"><div id="post"></div></div>
            <div class="floatL">
                <img src="{$psConfig.url}/files/avatar/{$psUser->uid}_50.jpg"/>
            </div>
            <div class="editor_coment">
                <textarea id="editor_comentarios" name="editor_comentarios" class="onblur_effect" tabindex="1" title="Escribir un comentario..." onfocus="onfocus_input(this)" onblur="onblur_input(this)"></textarea>
                <script type="text/javascript">
                //{literal}
                    $(document).ready(function(){
                        
                        /*CKEDITOR.replace('editor_comentarios', {
                            toolbarGroups: [
                                {"name":"basicstyles","groups":["basicstyles"]},
                                {"name":"links","groups":["links"]},
                                {"name":"paragraph","groups":["list","blocks"]},
                                {"name":"document","groups":["mode"]},
                                {"name":"insert","groups":["insert"]},
                                {"name":"about","groups":["about"]}
                            ],
                            // Remove the redundant buttons from toolbar groups defined above.
                            removeButtons: 'Underline,Strike,Subscript,Superscript,Anchor,Table,Specialchar'
                        });
                        editor_comentarios.resize('100%', '150');*/
                        //CKEDITOR.replace('editor_comentarios');
                    });
                //{/literal}
                </script>
                <br>
                <div class="buttons">
                    <div class="floatL">
                        <input type="hidden" id="auser_post" value="{$psPost.post_user}" />
                        <input type="button" onclick="comentario.nuevo('true')" class="btn btn-success" value="Enviar Comentario" tabindex="3" id="btnsComment"/>
                        &nbsp;<input type="button" onclick="comentario.preview('editor_comentarios','new')" class="btn btn-success" value="Vista Previa" tabindex="2" style="width:auto;" />
                    </div>
                </div>
            </div>
        </div>
    {/if}
</div>
