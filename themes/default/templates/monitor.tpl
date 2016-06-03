{include file='secciones/main_header.tpl'}
{if $psAction == ''}
  {include file='modulos/m.monitor_content.tpl'}
  {include file='modulos/m.monitor_sidebar.tpl'}
{else}
  {include file='modulos/m.monitor_menu.tpl'}
  {include file='modulos/m.monitor_listado.tpl'}
{/if}
<div class="both"></div>
{include file='secciones/main_footer.tpl'}
