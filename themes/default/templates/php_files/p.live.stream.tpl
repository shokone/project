    <div id="live-stream" ntotal="{if !$psStream.total}0{else}{$psStream.total}{/if}" mtotal="{$psMensajes.total}">
	{foreach from=$psStream.data item=noti key=id}
    <div class="UIBeeper_Full" id="beep_{$id}">
        <div class="Beeps">
            <div class="UIBeep">
                <a href="{$noti.link}" class="UIBeep_NonIntentional">
                    <div class="UIBeep_Icon action">
                        <span class="monac_icons ma_{$noti.style}"></span>
                    </div>
                    <span class="beeper_x" bid="{$id}">&nbsp;</span>
                    <div class="UIBeep_Title">
                        <span class="blueName">{if $noti.total == 1}{$noti.user}{/if}</span> {$noti.text} <span class="blueName">{$noti.ltext}</span>
                    </div>
                </a>
            </div>
        </div>
    </div>
    {/foreach}
    {foreach from=$psMensajes.data item=mp key=id}
    <div class="UIBeeper_Full" id="beep_m{$id}">
        <div class="Beeps">
            <div class="UIBeep">
                <a href="{$psConfig.url}/mensajes/leer/{$mp.mp_id}" class="UIBeep_NonIntentional">
                    <div class="UIBeep_Icon">
                        <span class="iconos mps"></span>
                        <img src="{$psConfig.url}/files/avatar/{$mp.mp_from}_50.jpg" width="16" height="16"/>
                    </div>
                    <span class="beeper_x" bid="m{$id}">&nbsp;</span>
                    <div class="UIBeep_Title">
                        <b>Nuevo mensaje</b><br />                    
                        <span class="blueName">{$mp.user_name}</span> {$mp.mp_preview}
                    </div>
                </a>
            </div>
        </div>
    </div>
    {/foreach}
    </div>