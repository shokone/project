  <!-- El logotipo y el icono que despliega el menú se agrupan
       para mostrarlos mejor en los dispositivos móviles -->
  <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
          <span class="sr-only">Desplegar navegación</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="#">Logotipo</a>
  </div>
 
  <!-- Agrupar los enlaces de navegación, los formularios y cualquier
       otro elemento que se pueda ocultar al minimizar la barra -->
  <div class="collapse navbar-collapse navbar-ex1-collapse">
      <!-- menu izquierda -->
      <ul class="nav navbar-nav">
          {if $psConfig.c_allow_portal && $psUser->member == true}
              <li class="{if $psPage == 'mi'}active{else}{/if}">
                  <a title="Ir a mi portal" href="{$psConfig.url}/mi/">Mi</a>
              </li>
          {/if}
          <li class="{if $psPage == 'posts' || $psPage == 'home'}active{else}{/if}">
              <a class="dropdown-toggle" data-toggle="dropdown" title="Ir a posts" href="{$psConfig.url}/posts/">Posts <b class="caret"></b></a>
              <ul class="dropdown-menu">
                  <li><a title="Inicio posts" href="{$psConfig.url}">Inicio</a></li>
                  <li><a title="Buscador" href="{$psConfig.url}/buscador/">Buscador</a></li>
                  {if $psUser->member}
                      {if $psUser->admod || $psUser->permisos.gopp}
                          <li><a title="Crear post" href="{$psConfig.url}/agregar/">Crear post</a></li>
                      {/if}
                      <li><a title="Historial" href="{$psConfig.url}/mod-history">Historial</a></li>
                      {if $psUser->admod || $psUser->permisos.moacp}
                          <li><a title="Panel de moderador" href="{$psConfig.url}/moderacion/">Panel de moderador</a></li>
                      {/if}
                  {/if}
              </ul>
          </li>
          {if $psConfig.c_fotos_private == 1 && !$psUser->member}{else}
              <li class="{if $psPage == 'fotos'}active{else}{/if}">
                  <a class="dropdown-toggle" data-toggle="dropdown" title="Ir a fotos" href="{$psConfig.url}/fotos/">Fotos <b class="caret"></b></a>
                  <ul class="dropdown-menu">
                      <li><a title="Inicio fotos" href="{$tsConfig.url}/fotos/">Inicio</a></li>
                      {if $psAction == 'album' && $psFUser.0 != $psUser->user_id}
                          <li><a title="&Aacute;lbum de {$tsFUser.1}" href="{$tsConfig.url}/fotos/{$tsFUser.1}">&Aacute;lbum de {$tsFUser.1}</a></li>
                      {/if}
                      {if $psUser->admod || $psUser->permisos.gopf}
                          <li><a title="Agregar Foto" href="{$tsConfig.url}/fotos/agregar.php">Agregar Foto</a></li>
                      {/if}
                      
                      <li><a title="Mis Fotos" href="{$tsConfig.url}/fotos/{$tsUser->nick}">Mis Fotos</a></li>
                  </ul>
              </li>
          {/if}
          <li class="{if $psPage == 'top'}active{else}{/if}">
              <a class="dropdown-toggle" data-toggle="dropdown" title="Ir a tops" href="{$psConfig.url}/top/">Tops <b class="caret"></b></a>
              <ul class="dropdown-menu">
                  <li><a title="Top posts" href="{$tsConfig.url}/top/posts/">Top posts</a></li>
                  <li><a title="Top usuarios" href="{$tsConfig.url}/top/usuarios/">Top usuarios</a></li>
              </ul>
          </li>
          {if $psUser->member}
              {if $psUser->admod == 1}
                  <li class="{if $psPage == 'admin'}active{else}{/if}">
                      <a title="Ir a administraci&oacute;n" href="{$psConfig.url}/admin/">Admin </a>
                  </li>
              {/if}
          {else}
              <li class="registrate">
                  <a title="Registrate!" onclick="registro_load_form(); return false" href=""><b>Registrate!</b></a>
              </li>
          {/if} 
      </ul> 
      <!-- menu derecha -->
      <ul class="nav navbar-nav navbar-right">
          <li><a href="#">Enlace #3</a></li>
          <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">Menú #2 <b class="caret"></b></a>
              <ul class="dropdown-menu">
                  <li><a href="#">Acción #1</a></li>
                  <li><a href="#">Acción #2</a></li>
                  <li><a href="#">Acción #3</a></li>
                  <li class="divider"></li>
                  <li><a href="#">Acción #4</a></li>
              </ul>
          </li>
      </ul>
  </div>
