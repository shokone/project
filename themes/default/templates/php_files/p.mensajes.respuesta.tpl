1:
<li>
    <div class="main both">
        <a href="{$psConfig.url}/perfil/{$psUser->nick}" class="autor-image"><img src="{$psConfig.url}/files/avatar/{$psUser->user_id}_50.jpg" /></a>
        <div class="mensaje">
            <div class="rbody">
                <div>
                    <a href="{$psConfig.url}/perfil/{$psUser->nick}" class="autor-name">{$psUser->nick}</a>
                    {if $psUser->admod}
                        <a href="{$psConfig.url}/moderacion/buscador/1/1/{$mp.mp_ip}"><span class="mp-date">{$mp.mp_ip}</span></a><br />
                    {/if}
                    <span class="mp-date">{$mp.mp_date|hace:true}</span>
                </div>
                <div>{$mp.mp_body|nl2br}</div>
            </div>
        </div>
    </div>
</li>
