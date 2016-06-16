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
          {if $psConfig.c_allow_portal && $psUser->member == 1}
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
                      <li><a title="Historial" href="{$psConfig.url}/mod-historial/">Historial</a></li>
                  {/if}
              </ul>
          </li>
          {if $psConfig.c_fotos_private == 1 && $psUser->member == 0}{else}
              <li class="{if $psPage == 'fotos'}active{else}{/if}">
                  <a class="dropdown-toggle" data-toggle="dropdown" title="Ir a fotos" href="{$psConfig.url}/fotos/">Fotos <b class="caret"></b></a>
                  <ul class="dropdown-menu">
                      <li><a title="Inicio fotos" href="{$psConfig.url}/fotos/">Inicio</a></li>
                      {if $psAction == 'album' && $psFUser.0 != $psUser->user_id}
                          <li><a title="&Aacute;lbum de {$psFUser.1}" href="{$psConfig.url}/fotos/{$psFUser.1}">&Aacute;lbum de {$psFUser.1}</a></li>
                      {/if}
                      {if $psUser->admod || $psUser->permisos.gopf}
                          <li><a title="Agregar Foto" href="{$psConfig.url}/fotos/agregar.php">Agregar Foto</a></li>
                      {/if}

                      <li><a title="Mis Fotos" href="{$psConfig.url}/fotos/{$psUser->nick}">Mis Fotos</a></li>
                  </ul>
              </li>
          {/if}
          <li class="{if $psPage == 'top'}active{else}{/if}">
              <a class="dropdown-toggle" data-toggle="dropdown" title="Ir a tops" href="{$psConfig.url}/top/">Tops <b class="caret"></b></a>
              <ul class="dropdown-menu">
                  <li><a title="Top posts" href="{$psConfig.url}/top/posts/">Top posts</a></li>
                  <li><a title="Top usuarios" href="{$psConfig.url}/top/usuarios/">Top usuarios</a></li>
              </ul>
          </li>
          {if $psUser->member == 1}
            {if $psUser->admod || $psUser->permisos.moacp}
                <li><a title="Panel de moderador" href="{$psConfig.url}/moderacion/">Moderaci&oacute;n</a></li>
            {/if}
          {/if}
          {if $psUser->member == 1}
              {if $psUser->admod == 1}
                  <li class="{if $psPage == 'admin'}active{else}{/if}">
                      <a title="Ir a administraci&oacute;n" href="{$psConfig.url}/admin/">Administraci&oacute;n</a>
                  </li>
              {/if}
          {/if}
      </ul>
      <!-- menu derecha -->
      <ul class="nav navbar-nav navbar-right userInfoMenu">
          {if $psUser->member == 1}
              <li class="monitor">
                  <a href="{$psConfig.url}/monitor/" onclick="notifica.last(); return false" title="Monitor del usuario" name="Monitor">
                      <span class="iconos monitor"></span>
                  </a>
                  <div class="noti_lista nodisplay" id="monitor_lista">
                      <div>
                          <strong onclick="location.href='{$psConfig.url}/monitor/'">Notificaciones</strong>
                      </div>
                  <ul class="list-group"></ul>
                  <a href="{$psConfig.url}/monitor/" class="ver-mas">Ver m&aacute;s notificaciones</a>
                  </div>
              </li>
              <li class="mensajes">
                  <a href="{$psConfig.url}/mensajes/" onclick="mensaje.last(); return false" title="Mensajes" name="Mensajes">
                      <span class="iconos mensajes"></span>
                  </a>
                  <div class="noti_lista nodisplay" id="mensajes_lista">
                      <div>
                          <strong onclick="location.href='{$psConfig.url}/mensajes/'">Mensajes</strong>
                      </div>
                      <ul id="mensaje_box" class="list-group"></ul>
                      <a href="{$psConfig.url}/mensajes/">Ver todos los mensajes</a>
                  </div>
              </li>
              {if $psAvisos}
                  <li class="avisos">
                      <a title="Avisos" href="{$psConfig.url}/mensajes/avisos/">
                          <img src="{$psConfig.default}/images/icons/megaphone.png" />
                      </a>
                      <div id="alert_aviso" class="alertas"><a title="{$psAvisos} aviso{if $psAvisos != 1}s{/if}"><span>{$psAvisos}</span></a></div>
                  </li>
              {/if}
              <li>
                  <a title="Mis Favoritos" href="{$psConfig.url}/favoritos.php">
                      <span class="iconos favoritos"></span>
                  </a>
              </li>
              <li>
                  <a title="Mis Borradores" href="{$psConfig.url}/borradores.php">
                      <span class="iconos borradores"></span>
                  </a>
              </li>
              <li>
                  <a title="Mi cuenta" href="{$psConfig.url}/cuenta/">
                      <span class="iconos micuenta"></span>
                  </a>
              </li>
              <li class="userMenu">
                  <a title="Mi Perfil" href="{$psConfig.url}/perfil/{$psUser->info.user_name}">{$psUser->nick}</a>
              </li>
              <li class="logout">
                  <a href="{$psConfig.url}/login-salir.php" title="Desconectar">
                      <span class="iconos logout"></span>
                  </a>
              </li>
          {else}
              <li class="registrate">
                  <a title="Registrate!" onclick="registro_load_form(); return false" href=""><b>Registrate!</b></a>
              </li>
              <li>
                  <a title="Login" href="javascript:open_login();">Login </a>
                  <div id="box_login" class="nodisplay">
                      <div class="body-login">
                          <a title="Cerrar login" href="javascript:close_login();"><img src="" title="Cerrar login" alt="Cerrar login"/></a>
                          <span class="gif_cargando floatR nodisplay" id="login_cargando"></span>
                          <div id="login_error" class="nodisplay"></div>
                          <form action="javascript:login_ajax()" method="post">
                              <label>Usuario</label>
                              <input type="text" class="inlogin" id="nickname" name="nick" maxlength="64">
                              <label>Contraseña</label>
                              <input type="password" class="inlogin" id="password" name="pass" maxlength="64">
                              <label for="rem">Recordar usuario</label>
                              <input type="checkbox" id="rem" name="rem" value="true" checked="checked" />
                              <input type="submit" title="Entrar" value="Entrar" class="btn btnOk">
                          </form>
                          <div class="login_footer">
                              <a href="#" onclick="remind_password();">&#191;Olvidaste tu contrase&#241;a?</a><br/>
                              <a href="#" onclick="resend_validation();">&#191;No te ha llegado el correo de validaci&oacute;n?</a><br/>
                              <a onclick="open_login(); registro_load_form(); return false" href="#">
                                <strong>Registrarme!</strong>
                              </a>
                          </div>
                      </div>
                  </div>
              </li>
          {/if}
      </ul>
  </div>
