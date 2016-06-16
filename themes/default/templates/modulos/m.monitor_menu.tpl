<div class="menu-tabs clearfix">
    <ul>
        <li{if $psAction == 'seguidores'} class="selected"{/if}><a href="{$psConfig.url}/monitor/seguidores">Seguidores</a></li>
        <li{if $psAction == 'siguiendo'} class="selected"{/if}><a href="{$psConfig.url}/monitor/siguiendo">Siguiendo</a></li>
        <li{if $psAction == 'posts'} class="selected"{/if}><a href="{$psConfig.url}/monitor/posts">Posts</a></li>
    </ul>
</div>