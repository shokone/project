<div>
    <div class="categoriaList">
        <h6>Fotos de {$psFoto.user_name}</h6>
        <ul id="v_album">
            {foreach from=$psUserFotos item=f}
            <li><a href="{$psConfig.url}/fotos/{$f.user_name}/{$f.foto_id}/{$f.f_title|seo}.html"><img src="{$f.f_url}" title="{$f.f_title}" width="120" height="90" /><span class="time">{$f.f_date|date_format:"%d/%m/%Y"}</span></a></li>
            {/foreach}
        </ul>
        <a href="{$psConfig.url}/fotos/{$psFoto.user_name}" class="fb_foot">Ver m&aacute;s</a>
    </div>
        <div class="categoriaList">
        <h6 class="center">Seguidores</h6>
        <ul id="v_album">
            {if $psFriendFotos}
                {foreach from=$psFriendFotos item=f}
                    <li><a href="{$psConfig.url}/fotos/{$f.user_name}/{$f.foto_id}/{$f.f_title|seo}.html">
                        <img src="{$f.f_url}" title="{$f.f_title}" width="120" height="90" /></a><br /><a href="{$psConfig.url}/perfil/{$f.user_name}"><strong>{$f.user_name}</strong></a>
                    </li>
                {/foreach}
            {else}
                <li class="emptyData"><u>{$psFoto.user_name}</u> no sigue usuarios o no han subido fotos.</li>
            {/if}
        </ul>
        {if $psFriendFotos}<a href="{$psConfig.url}/fotos/{$psFoto.user_name}" class="fb_foot">Ver todas</a>{/if}
    </div>
    <div class="categoriaList estadisticasList">
        <h6>Estad&iacute;sticas</h6>
        <ul>
            <li class="clearfix"><a href="{$psConfig.url}/fotos/{$psFoto.user_name}"><span class="floatL">Fotos subidas</span><span class="floatR number">{$psFoto.user_fotos}</span></a></li>
            <li class="clearfix"><a href="#"><span class="floatL">Comentarios</span><span class="floatR number">{$psFoto.user_foto_comments}</span></a></li>
        </ul>
    </div>
    {if $psFotoVisitas}
    <div class="categoriaList">
        <h6 class="center">Visitas recientes</h6>
        <ul id="v_album">
            {foreach from=$psFotoVisitas item=v}
              <a href="{$psConfig.url}/perfil/{$v.user_name}" class="hovercard" uid="{$v.user_id}">
                <img src="{$psConfig.url}/files/avatar/{$v.user_id}_50.jpg" class="vctip" title="{$v.date|hace:true}" width="32" height="32"/>
              </a>
            {/foreach}
        </ul>
    </div>
    {/if}
    <div class="categoriaList">
        <h6 class="center">Medallas</h6>
        <ul id="v_album"> 
            {if $psFotoMedallas}
                {foreach from=$psFotoMedallas item=m}
                    <img src="{$psConfig.tema.t_url}/images/icons/med/{$m.m_image}_16.png" class="qtip" title="{$m.m_title} - {$m.m_description}"/>
                {/foreach}
            {else}
                <li class="emptyData">Esta foto no tiene medallas</li>
            {/if}
        </ul>
    </div>
</div>