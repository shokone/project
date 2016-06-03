{include file='secciones/main_header.tpl'}
        {if $psAction == 'ayuda'}
                <div>
                Hola <u>{$psUser->nick}</u>, S&iacute; necesitas ayuda, por favor cont&aacute;ctanos a trav&eacute;s del siguiente <a href="{$psConfig.url}/pages/contacto/">formulario</a>.
                </div>
        {elseif $psAction == 'protocolo'}
                {include file='modulos/m.pages_protocolo.tpl'}
        {elseif $psAction == 'terminos-y-condiciones'}
                {include file='modulos/m.pages_terminos.tpl'}
        {elseif $psAction == 'privacidad'}
                {include file='modulos/m.pages_privacidad.tpl'}
        {elseif $psAction == 'dmca'}
                {include file='modulos/m.pages_dmca.tpl'}
        {/if}                
{include file='secciones/main_footer.tpl'}