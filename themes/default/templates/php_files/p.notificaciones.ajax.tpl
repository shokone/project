{if $psDatos}
  {foreach from=$psDatos item=noti}
    <li class="{if $noti.unread > 0} unread{/if}">
      <span class="monac_icons ma_{$noti.style}"></span>
      {if $noti.total == 1}
        <a href="{$psConfig.url}/perfil/{$noti.user}" title="{$noti.user}">{$noti.user}</a>
      {/if}
      {$noti.text}
      <a title="{$noti.title}" class="obj" href="{$noti.link}">{$noti.ltext}</a>
    </li>
  {/foreach}
{else}
  <li><b>No hay notificaciones nuevas</b></li>
{/if}
