<div id="webStats">
    <div>
        <div>
            <span class="" title="Actualizado: {$psStats.stats_time|hace}">Estad&iacute;sticas</span>
        </div>
        <div class="box_cuerpo">
        <table>
            <tr>
            	<td><a class="usuarios_online" href="{$psConfig.url}/usuarios/?online=true">
                <span class="qtip" title="R&eacute;cord conectados: {$psStats.stats_max_online} {$psStats.stats_max_time|fecha}">{$psStats.stats_online} online</span></a>
                </td>
                <td><a href="{$psConfig.url}/usuarios/">{$psStats.stats_miembros} miembros</a></td>
            </tr>
            <tr>
                <td>{$psStats.stats_posts} posts</td>
                <td>{$psStats.stats_comments} comentarios</td>
            </tr>
            <tr>
                <td>{$psStats.stats_fotos} fotos</td>
                <td>{$psStats.stats_foto_comments} comentarios en fotos</td>
            </tr>
        </table>
        </div>
    </div>
</div>