<div class="frameForm row">
    <ul class="options clearfix">
        <li><span class="share">Compartir:</span></li>
        <li>
            <ul class="atta">
                <li selected="selected"><span class="elementPerfil">
                    <i class="stream {if $psInfo.uid == $psUser->user_id}status{else}mpub{/if}"></i>
                    <a href="#" class="a_green" onclick="muro.stream.load('status', this); return false;" id="stMain">
                        {if $psInfo.uid == $psUser->user_id}Estado{else}Publicaci&oacute;n{/if}
                    </a>
                    <span class="hidden">{if $psInfo.uid == $psUser->user_id}Estado{else}Publicaci&oacute;n{/if}</span>
                    <i class="nub"></i>
                </span></li>
                <li><span class="elementPerfil">
                    <i class="stream mfoto"></i>
                    <a href="#" class="a_green" onclick="muro.stream.load('foto', this); return false;">Foto</a>
                    <span class="hidden">Foto</span>
                    <i class="nub hidden"></i>
                </span></li>
                <li><span class="elementPerfil">
                    <i class="stream mlink"></i>
                    <a href="#" class="a_green" onclick="muro.stream.load('enlace', this); return false;">Enlace</a>
                    <span class="hidden">Enlace</span>
                    <i class="nub hidden"></i>
                </span></li>
                <li><span class="elementPerfil">
                    <i class="stream mvideo"></i>
                    <a href="#" class="a_green" onclick="muro.stream.load('video', this); return false;">Video</a>
                    <span class="hidden">Video</span>
                    <i class="nub hidden"></i>
                </span></li>
                <li class="streamLoader"><img width="16" height="11" alt="" src="" class="img"/></li>
            </ul>
        </li>
    </ul>
    <div class="attaFrame">
        <div id="attaContent">
            <div id="statusFrame">
                <textarea class="status" id="wall" onfocus="onfocus_input(this)" onblur="onblur_input(this)" title="{if $psInfo.uid == $psUser->user_id}&iquest;En qu&eacute; est&aacute;s pensando?{else}Comparte algo...{/if}">{if $psInfo.uid == $psUser->user_id}&iquest;En qu&eacute; est&aacute;s pensando?{else}Comparte algo...{/if}</textarea>
            </div>
            <div id="fotoFrame">
                <input type="text" class="itext" name="ifoto" value="{$psConfig.url}/images/ejemplo.jpg" title="{$psConfig.url}/images/ejemplo.jpg" onfocus="onfocus_input(this)" onblur="onblur_input(this)"/>
                <a href="#" class="btn_g adj" onclick="muro.stream.adjuntar(); return false;">Adjuntar</a>
            </div>
            <div id="enlaceFrame">
                <input type="text" class="itext" name="ienlace" value="{$psConfig.url}/blog/ejemplo.html" title="{$psConfig.url}/blog/ejemplo.html" onfocus="onfocus_input(this)" onblur="onblur_input(this)"/>
                <a href="#" class="btn_g adj" onclick="muro.stream.adjuntar(); return false;">Adjuntar</a>
            </div>
            <div id="videoFrame">
                <input type="text" class="itext" name="ivideo" value="https://www.youtube.com/watch?v=6jN2fnMVAKI" title="https://www.youtube.com/watch?v=6jN2fnMVAKI" onfocus="onfocus_input(this)" onblur="onblur_input(this)"/>
                <a href="#" class="btn_g adj" onclick="muro.stream.adjuntar(); return false;">Adjuntar</a>
            </div>
        </div>
        <div class="attaDesc">
            <div class="wrap">
                <textarea class="status" id="attaDesc" onfocus="onfocus_input(this)" onblur="onblur_input(this)" title="Escribe un comentario sobre esta foto..."></textarea>
            </div>
            <input type="button" class="btn btn-success shareBtn" value="Compartir" onclick="muro.stream.compartir();" />
            <div class="clearBoth"></div>
        </div>
    </div>
    <div class="btnStatus">
        <input type="button" class="btn btn-success shareBtn" value="Compartir" onclick="muro.stream.compartir();" />
        <div class="clearBoth"></div>
    </div>
</div>