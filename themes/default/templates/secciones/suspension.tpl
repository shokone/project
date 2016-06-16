{include file='secciones/main_header.tpl'}
	<div class="user-suspendido col-md-12 col-xs-12">
        <h3>Usuario suspendido</h3>
        <p>Hola, <b>{$psUser->nick}</b> lamentamos informarte que has sido suspendido de <b>{$psConfig.titulo}</b></p>
        <h4>Raz&oacute;n:</h4>
        <div>{$psBaneado.susp_causa}</div>
        <h4>Tiempo de suspensi&oacute;n:</h4>
        <b>
            {if $psBaneado.susp_termina == 0}Indefinidamente
            {elseif $psBaneado.susp_termina == 1}Permanentemente
            {else}{$psBaneado.susp_termina|date_format:"%d/%m/%Y a las %H:%M:%S"}hs{/if}
        </b>
        <h4>Fecha actual:</h4>
        {$smarty.now|date_format:"%d/%m/%Y %H:%M:%S"}hs.
    </div>
{include file='secciones/main_footer.tpl'}
                        