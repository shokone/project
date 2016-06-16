<h3 onclick="cuenta.chgsec(this)">2. Como soy f&iacute;sicamente</h3>
<fieldset class="nodisplay">
    <div class="alert-cuenta cuenta-3">
    </div>
    <div class="field">
        <label for="altura">Mi altura</label>
        <div class="input-fake">
            <input type="text" value="{if $psPerfil.p_altura}{$psPerfil.p_altura}{/if}" maxlength="3" name="altura" id="altura" class="text cuenta-save-3">&nbsp;cent&iacute;metros					</div>
    </div>
    <div class="field">
        <label for="peso">Mi peso</label>
        <div class="input-fake">
            <input type="text" value="{if $psPerfil.p_peso > 0}{$psPerfil.p_peso}{/if}" maxlength="3" name="peso" id="peso" class="text cuenta-save-3">&nbsp;kilogramos					
        </div>
    </div>
    <div class="field">
        <label for="fisico">Complexi&oacute;n</label>
        <div class="input-fake">
            <select class="cuenta-save-3" name="fisico" id="fisico">
                {foreach from=$psPerfilData.fisico key=val item=text}
                    <option value="{$val}" {if $psPerfil.p_fisico == $val}selected="selected"{/if}>{$text}</option>
                {/foreach}
            </select>
        </div>
    </div>
    <div class="field">
        <label for="pelo_color">Color de pelo</label>
        <div class="input-fake">
            <select class="cuenta-save-3" name="pelo_color" id="pelo_color">
            	{foreach from=$psPerfilData.pelo key=val item=text}
                    <option value="{$val}" {if $psPerfil.p_pelo == $val}selected="selected"{/if}>{$text}</option>
                {/foreach}
            </select>
        </div>
    </div>
    <div class="field">
        <label for="ojos_color">Color de ojos</label>
        <div class="input-fake">
            <select class="cuenta-save-3" name="ojos_color" id="ojos_color">
            	{foreach from=$psPerfilData.ojos key=val item=text}
                    <option value="{$val}" {if $psPerfil.p_ojos == $val}selected="selected"{/if}>{$text}</option>
                {/foreach}
            </select>
        </div>
    </div>
    <div class="field">
        <label for="dieta">Mi dieta es</label>
        <div class="input-fake">
            <select class="cuenta-save-3" name="dieta" id="dieta">
            	{foreach from=$psPerfilData.dieta key=val item=text}
                    <option value="{$val}" {if $psPerfil.p_dieta == $val}selected="selected"{/if}>{$text}</option>
                {/foreach}
            </select>
        </div>
    </div>
    <div class="field">
        <label>Tengo</label>
        <div class="input-fake">
            <ul>
                {foreach from=$psPerfilData.tengo key=val item=text}
                    <li><input type="checkbox" name="t_{$val}" class="cuenta-save-3" value="1" {if $psPerfil.p_tengo.$val == 1}checked="checked"{/if}>{$text}</li>
                {/foreach}
            </ul>
        </div>
    </div>
    <div class="field">
        <label for="fumo">Fumo</label>
        <div class="input-fake">
            <select class="cuenta-save-3" name="fumo" id="fumo">
            	{foreach from=$psPerfilData.fumo_tomo key=val item=text}
                    <option value="{$val}" {if $psPerfil.p_fumo == $val}selected="selected"{/if}>{$text}</option>
                {/foreach}
            </select>
        </div>
    </div>
    <div class="field">
        <label for="tomo_alcohol">Tomo alcohol</label>
        <div class="input-fake">
            <select class="cuenta-save-3" name="tomo_alcohol" id="tomo_alcohol">
            	{foreach from=$psPerfilData.fumo_tomo key=val item=text}
                    <option value="{$val}" {if $psPerfil.p_tomo == $val}selected="selected"{/if}>{$text}</option>
                {/foreach}
            </select>
        </div>
    </div>
    <div class="buttons">
        <input type="button" value="Guardar y seguir" onclick="cuenta.save(3, true)" class="btn btn-success">
    </div>
</fieldset>