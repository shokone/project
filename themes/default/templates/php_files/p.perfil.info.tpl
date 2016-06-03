1:
<div id="perfil_info" status="activo">
    <div class="widget big-info clearfix">
        <div class="title-w clearfix">
            <h3>Informaci&oacute;n de {$psUsername}</h3>
        </div>
        <ul>
            <li>
                <label>Pa&iacute;s</label><strong>{$psPais}</strong>
            </li>
			{if $psPerfil.p_sitio}
                <li>
                    <label>Sitio Web</label><strong><a rel="nofollow" href="{$psPerfil.p_sitio}">{$psPerfil.p_sitio}</a></strong>
                </li>
            {/if}			
            <li>
                <label>Es usuario desde </label><strong>{$psPerfil.user_registro|fecha:"d_Ms_a"}</strong>
            </li>
            <li>
                <label>&Uacute;ltima vez online </label><strong>{$psPerfil.user_lastactive|fecha}</strong>
            </li>
			{if $psPerfil.p_estudios}
                <li>
                    <label>Estudios</label><strong>{$psPData.estudios[$psPerfil.p_estudios]}</strong>
                </li>
            {/if}
			<li class="sep"><h4>Idiomas</h4></li>
            {if $psPData.idiomas}
            	{foreach from=$psPData.idiomas key=iid item=idioma}
                    {if $psPerfil.p_idiomas.$iid != 0}
                        <li>
                            <label>{$idioma}</label>
                            {foreach from=$psPData.inivel key=val item=text}
                                {if $psPerfil.p_idiomas.$iid == $val}
                                    <strong>{$text}</strong>
                                {/if}
                            {/foreach}
                        </li>
                    {/if}
                {/foreach}	
            {/if}														
			<li class="sep"><h4>Datos profesionales</h4></li>
			{if $psPerfil.p_profesion}
                <li><label>Profesi&oacute;n</label><strong>{$psPerfil.p_profesion}</strong></li>
            {/if}			
            {if $psPerfil.p_empresa}
                <li><label>Empresa</label><strong>{$psPerfil.p_empresa}</strong></li>
            {/if}			
            {if $psPerfil.p_sector}
                <li><label>Sector</label><strong>{$psPData.sector[$psPerfil.p_sector]}</strong></li>
            {/if}			
            {if $psPerfil.p_ingresos}
                <li><label>Ingresos</label><strong>{$psPData.ingresos[$psPerfil.p_ingresos]}</strong></li>
            {/if}			
            {if $psPerfil.p_int_prof}
                <li><label>Intereses profesionales</label><strong>{$psPerfil.p_int_prof}</strong></li>
            {/if}			
            {if $psPerfil.p_hab_prof}
                <li><label>Habilidades profesionales</label><strong>{$psPerfil.p_hab_prof}</strong></li>
            {/if}
			<li class="sep"><h4>Vida personal</h4></li>
			{if $psGustos == 'show'}
                <li>
                    <label>Le gustar&iacute;a</label>
                    <strong>
                        {foreach from=$psPData.gustos key=val item=text}
                            {if $psPerfil.p_gustos.$val == 1}{$text}, {/if}
                        {/foreach}
                    </strong>
                </li>
            {/if}			
            {if $psPerfil.p_estado}
                <li><label>Estado civil</label><strong>{$psPData.estado[$psPerfil.p_estado]}</strong></li>
            {/if}			
            {if $psPerfil.p_hijos}
                <li><label>Hijos</label><strong>{$psPData.hijos[$psPerfil.p_hijos]}</strong></li>
            {/if}			
            {if $psPerfil.p_vivo}
                <li><label>Vive con</label><strong>{$psPData.vivo[$psPerfil.p_vivo]}</strong></li>
            {/if}
			<li class="sep"><h4>&iquest;C&oacute;mo es?</h4></li>
			{if $psPerfil.p_altura}
                <li><label>Mide</label><strong>{$psPerfil.p_altura} centimetros</strong></li>
            {/if}			
            {if $psPerfil.p_peso}
                <li><label>Pesa</label><strong>{$psPerfil.p_peso} kilos</strong></li>
            {/if}			
            {if $psPerfil.p_pelo}
                <li><label>Su pelo es</label><strong>{$psPData.pelo[$psPerfil.p_pelo]}</strong></li>
            {/if}			
            {if $psPerfil.p_ojos}
                <li><label>Sus ojos son</label><strong>{$psPData.ojos[$psPerfil.p_ojos]}</strong></li>
            {/if}
            {if $psPerfil.p_fisico}
                <li><label>Su f&iacute;sico es</label><strong>{$psPData.fisico[$psPerfil.p_fisico]}</strong></li>
            {/if}
            {if $psPerfil.p_tengo.0 != 0 || $psPerfil.p_tengo.1 != 0}
                {foreach from=$psPData.tengo key=val item=text}
                    <li><label></label><strong>{if $psPerfil.p_tengo.$val == 1}Tiene{else}No tiene{/if} {$text}</strong></li>
                {/foreach}
            {/if}				
			<li class="sep"><h4>Habitos personales</h4></li>
			{if $psPerfil.p_dieta}
                <li><label>Mantiene una dieta</label><strong>{$psPData.dieta[$psPerfil.p_dieta]}</strong></li>
            {/if}			
            {if $psPerfil.p_fumo}
                <li><label>Fuma</label><strong>{$psPData.fumo_tomo[$psPerfil.p_fumo]}</strong></li>
            {/if}			
            {if $psPerfil.p_tomo}
                <li><label>Toma alcohol</label><strong>{$psPData.fumo_tomo[$psPerfil.p_tomo]}</strong></li>
            {/if}
                <li class="sep"><h4>Sus propias palabras</h4></li>
			{if $psPerfil.p_intereses}
                <li><label>Intereses</label><strong>{$psPerfil.p_intereses}</strong></li>
            {/if}
            {if $psPerfil.p_hobbies}
                <li><label>Hobbies</label><strong>{$psPerfil.p_hobbies}</strong></li>
            {/if}
            {if $psPerfil.p_tv}
                <li><label>Series de TV favoritas</label><strong>{$psPerfil.p_tv}</strong></li>
            {/if}			
            {if $psPerfil.p_musica}
                <li><label>M&uacute;sica favorita</label><strong>{$psPerfil.p_musica}</strong></li>
            {/if}
            {if $psPerfil.p_deportes}
                <li><label>Deportes y Equipos</label><strong>{$psPerfil.p_deportes}</strong></li>
            {/if}	
            {if $psPerfil.p_libros}
                <li><label>Libros favoritos</label><strong>{$psPerfil.p_libros}</strong></li>
            {/if}
            {if $psPerfil.p_peliculas}
                <li><label>Pel&iacute;culas favoritas</label><strong>{$psPerfil.p_peliculas}</strong></li>
            {/if}			
            {if $psPerfil.p_comida}
                <li><label>Comida favor&iacute;ta</label><strong>{$psPerfil.p_comida}</strong></li>
            {/if}
            {if $psPerfil.p_heroes}
                <li><label>Sus heroes favoritos son</label><strong>{$psPerfil.p_heroes}</strong></li>
            {/if}
        </ul>
    </div>
</div>