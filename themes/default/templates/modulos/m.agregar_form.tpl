{if $psUser->admod || $psUser->permisos.gopp}
	<div class="form-add-post col-md-12">
    	<form action="{$psConfig.url}/agregar.php{if $psAction == 'editar'}?action=editar&pid={$psPid}{/if}" method="post" name="newpost" autocomplete="off">
        	<input type="hidden" value="{$psBorrador.bid}" name="borrador_id"/>
            <div class="clearbeta">
                <div>
	                <label>T&iacute;tulo</label>
	                <span class="errormsg nodisplay"></span>
	                <input type="text" tabindex="1" name="titulo" maxlength="60" size="60" class="text-inp required" value="{$psBorrador.b_title}"/>
	                <div id="repost"></div>
                </div>
                <div>
	                <label>Tags</label>
	                <span class="errormsg nodisplay"></span>
	                <input type="text" tabindex="4" name="tags" maxlength="128" class="text-inp required" value="{$psBorrador.b_tags}"/><br>
	                Una lista de palabras separada por comas, que describa el contenido. Ejemplo: 
	                <b>baloncesto, ingleses, juego, consola, xbox, windows</b>
                </div>
                <div>
	                <a name="post"></a>
	                <label>Contenido del Post</label>
	                <span class="errormsg nodisplay"></span><br>
	                <textarea id="editorPost" name="cuerpo" tabindex="2" class="required2">{$psBorrador.b_body}</textarea>
                </div>
                <div class="col-md-4">
	                <div class="special-left clearbeta">
		                <label>Categor&iacute;a</label>
		                <span class="errormsg nodisplay"></span><br>
		                <select class="agregar required" tabindex="5" size="9" size="{$psConfig.categorias.total}" name="categoria">
		                	<option value="" selected="selected">Elegir una categor&iacute;a</option>
		                    {foreach from=$psConfig.categorias item=c}
		                    	<option value="{$c.cid}" {if $psBorrador.b_category == $c.cid}selected="selected"{/if}>{$c.c_nombre}</option>
		                    {/foreach}
		                </select>
	                </div>
                </div>
                <div class="col-md-4">
	                <div class="special-right clearbeta">
		                <label>Opciones</label>
		                <div class="option clearbeta">  
		                    <input type="checkbox" tabindex="6" name="privado" id="privado" class="floatL" {if $psBorrador.b_private == 1}checked="checked"{/if} />
		                    <p class="floatL">
		                        <label for="privado">S&oacute;lo usuarios registrados</label>
		                        Tu post ser&aacute; visto s&oacute;lo por los usuarios registrados en {$psConfig.titulo}
		                    </p>
		                </div>
		                <div class="option clearbeta">  
		                    <input type="checkbox" tabindex="8" name="visitantes" id="visitantes" class="floatL" {if $psBorrador.b_visitantes == 1}checked="checked"{/if} />
		                    <p class="floatL">
		                        <label for="visitantes">Mostrar visitantes recientes</label>
		                        Tu post mostrar&aacute; los &uacute;ltimos usuarios que lo han visitado
		                    </p>
		                </div>
		                <div class="option clearbeta">  
		                    <input type="checkbox" tabindex="7" name="sin_comentarios" id="sin_comentarios" class="floatL" {if $psBorrador.b_block_comments == 1}checked="checked"{/if}>
		                    <p class="floatL">
		                        <label for="sin_comentarios">Cerrar Comentarios</label>
		                        Si tu post es pol&eacute;mico ser&iacute;a mejor que no permitieras comentarlo
		                    </p>
		                </div>
		                {if $psUser->admod == 1}
			                <div class="option clearbeta">  
			                    <input type="checkbox" tabindex="9" name="patrocinado" id="patrocinado" class="floatL" {if $psBorrador.b_sponsored == 1}checked="checked"{/if} >
			                    <p class="floatL">
			                        <label for="patrocinado">Patrocinado</label>
			                        Resalta este post entre el resto
			                    </p>
			                </div>
		                {/if}
		                {if $psUser->admod || $psUser->permisos.most}
			                <div class="option clearbeta">  
			                    <input type="checkbox" tabindex="7" name="sticky" id="sticky" class="floatL" {if $psBorrador.b_sticky == 1}checked="checked"{/if} >
			                    <p class="floatL">
			                        <label for="sticky">Sticky</label>
			                        Colocar a este post fijo en la home de {$psConfig.titulo}
			                    </p>
			                </div>
		                {/if}
	                </div>
                </div>
                {if (($psUser->admod || $psUser->permisos.moedpo) && $psBorrador.b_title && $psBorrador.b_user != $psUser->user_id)}
					<div>
		                <label>Raz&oacute;n</label>
		                <span class="errormsg nodisplay"></span>
		                <input type="text" tabindex="8" name="razon" maxlength="150" size="60" class="text-inp" value=""/>
		               	Si has modificado el contenido de este post ingresa la raz&oacute;n por la cual lo modificaste.
	                </div>
                {/if}
            </div>
            <div class="end-form clearbeta both">
            	<input type="hidden" value="{if $psBorrador}Aplicar Cambios{else}Agregar post{/if}" id="botonPreview"/>
                <input type="button" tabindex="15" title="Guardar en borradores" value="Guardar en borradores" onclick="save_borrador()" class="btn btnOk floatL" id="borrador-save"/>
            	<input type="button" title="Previsualizar" value="Continuar" name="preview" class="btn btnGreen"/>
        		<div id="borrador-guardado"></div>
            </div>
        </form>
    </div>
{else}
	<div class="emptyData clearfix">Lo sentimos, pero no tienes los permisos necesarios para publicar un nuevo post.</div>
{/if}