<div class="before floatL">
    <a href="#ver-comentarios" {if $psPages.prev > 0}onclick="comentario.cargar({$psPages.post_id}, {$psPages.prev}, {$psPages.autor});"{else}class="desactivado"{/if}>
        <b>&laquo; Anterior</b>
    </a>
</div>
<div>
    <ul>
        {section name=page start=1 loop=$psPages.section}
            <li class="numbers">
                <a href="#ver-comentarios" {if $psPages.current == $smarty.section.page.index}class="here"{else}onclick="comentario.cargar({$psPages.post_id}, {$smarty.section.page.index}, {$psPages.autor});"{/if}>{$smarty.section.page.index}</a>
            </li>
        {/section}
    </ul>
</div>
<div class="floatR next">
    <a href="#ver-comentarios" {if $psPages.next <= $psPages.pages}onclick="comentario.cargar({$psPages.post_id}, {$psPages.next}, {$psPages.autor});"{else}class="desactivado"{/if}>
        <b>Siguiente &raquo;</b>
    </a>
</div>
<div class="both"></div>
