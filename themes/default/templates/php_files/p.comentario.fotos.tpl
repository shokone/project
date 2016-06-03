1:
<div class="item" id="div_cmnt_{$psComment.0}">
    <a href="{$psConfig.url}/perfil/{$psUser->nick}">
        <img src="{$psConfig.url}/files/avatar/{$psUser->info.user_id}_50.jpg" width="50" height="50" class="floatL" />
    </a>
    <div class="firma">
        <div class="options">
            {if $psComment.3 == $psUser->user_id}
            <a href="#" onclick="fotos.borrar({$psComment.0}, 'com'); return false" class="floatR">
        <img title="Borrar Comentario" alt="borrar" src="{$psConfig.default}/images/borrar.png"/>
            </a>
            {/if}
        </div>
        <div class="info">
            <a href="{$psConfig.url}/fotos/{$psUser->nick}/">{$psUser->nick}</a> @ {$psComment.2|date_format:"%d/%m/%Y"} dijo:
        </div>
        <div class="clearfix">{$psComment.1|nl2br}</div>
    </div>
    <div class="both"></div>
</div>
