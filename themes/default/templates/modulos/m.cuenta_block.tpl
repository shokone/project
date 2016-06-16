<div class="content-tabs bloqueados nodisplay">
	<fieldset>
        <div class="field">
            {if $psBloqueos}
                <ul class="bloqueadosList">
                    {foreach from=$psBloqueos item=b}
                	   <li>
                            <a href="{$psConfig.url}/perfil/{$b.user_name}">{$b.user_name}</a>
                            <span><a title="Desbloquear Usuario" href="javascript:bloquear('{$b.b_auser}', false, 'mis_bloqueados')" class="desbloqueadosU bloquear_usuario_{$b.b_auser}">Desbloquear</a></span>
                        </li>
                    {/foreach}
                 </ul>
            {else}
                <div class="emptyData">No hay usuarios bloqueados</div>
            {/if}
         </div>
    </fieldset>
    <div class="clearfix"></div>
</div>