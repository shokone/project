{if ($psAction == 'agregar' && ($psUser->permisos.gopf || $psUser->admod)) || ($psAction == 'editar' && ($psUser->permisos.moedfo || $psUser->admod))} 
    <div id="centroDerecha">
        <div class="">
            <h2 >{if $psAction == 'agregar'}Agregar nueva{else}Editar{/if} foto</h2>
        </div>
        <form name="add_foto" method="post" action="" enctype="multipart/form-data" id="foto_form" class="form-add-foto" autocomplete="off">
            <div class="loader">
                <img src="{$psConfig.default}/images/loading_bar.gif" /><br />
                <h2>Cargando foto, espere por favor....</h2>
            </div>
            <div class="fade_out">
                <ul class="clearbeta">
                    <li>
                        <label for="ftitle">T&iacute;tulo</label>
                        <span class="errormsg nodisplay"></span>
                        <input type="text" tabindex="1" name="titulo" id="ftitle" maxlength="40" class="text-inp required" value="{$psFoto.f_title}"/>
                    </li>
                    {if $psAction != 'editar'}
                        {if $psConfig.c_allow_upload == 1}
                            <li>
                                <label for="ffile">Archivo</label>
                                <input type="file" name="file" id="ffile" />
                            </li>
                        {else}
                            <li>
                                <label for="furl">URL</label>
                                <span class="errormsg nodisplay"></span>
                                <input type="text" tabindex="2" name="url" id="furl" maxlength="200" class="text-inp required" value="{$psFoto.f_url}"/>
                            </li>                            
                        {/if}
                    {/if}
                    <li>
                        <label for="fdesc">Descripci&oacute;n (<small>Max 500 car.</small>)</label>
                        <span class="errormsg nodisplay"></span>
                        <textarea name="desc" id="fdesc" cols="60" rows="5" onkeydown="return contarLetras(this);" onkeyup="return contarLetras(this);">{$psFoto.f_description}</textarea>
                    </li>
                    <li>
                        <label>Opciones</label>
                        <div class="option clearbeta">  
                            <input type="checkbox" class="floatL" id="sin_comentarios" name="closed"{if $psFoto.f_closed == 1} checked="true"{/if}/>
                            <p class="floatL">
                                <label for="sin_comentarios">Cerrar Comentarios</label>
                                Si no quieres recibir comentarios en tu foto.
                            </p>
                            <br><br>
                            <input type="checkbox" class="floatL" id="visitas" name="visitas"  {if $psFoto.f_visitas == 1} checked="true"{/if}/>
                            <p class="floatL">
                                <label for="visitas">&Uacute;ltimos visitantes</label>
                                Se mostrar&aacute;n los &uacute;ltimos visitantes.
                            </p>
                        </div>
                    </li>
                    </ul>
                {if $psUser->admod > 0 && $psAction == 'editar' && $psFoto.f_user  != $psUser->uid}
                <li class="both">
                    <label>Raz&oacute;n</label>
                    <input type="text" tabindex="8" name="razon" maxlength="150" size="60" class="text-inp" value=""/>
                    Si has modificado el contenido de esta foto, ingresa la raz&oacute;n.
                </li>
                {/if}
                <div class="end-form clearbeta">
                    <input type="button" class="btn btn-success" name="new" value="{if $psAction == 'agregar'}Agregar foto{else}Guardar cambios{/if}" onclick="fotos.agregar()"/>
                </div>
            </div>                    
        </form>
    </div>
{else}
    <div class="emptyData clearfix">
        Lo sentimos, pero no puedes {if $psAction == 'agregar'}agregar{else}editar{/if} una nueva foto.
    </div>
{/if}
