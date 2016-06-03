{include file='secciones/main_header.tpl'}
  {literal}
  <script type="text/javascript">
    var buscador = {
      tipo: '{if !$psEngine}web{$psEngine}{/if}',
      select: function(tipo){
        if(this.tipo==tipo){
          return;
        }
        $('input[name="e"]').val(tipo);
        //Solo hago los cambios visuales si no envia consulta
        if(!this.buscadorLite){
          $('a#select_' + this.tipo).removeClass('here');
          $('a#select_' + tipo).addClass('here');
          $('img#buscador-logo-'+this.tipo).css('display', 'none');
          $('img#buscador-logo-'+tipo).css('display', 'inline');
        }
        //mostramos u ocultamos busqueda por google
        if(tipo=='google'){
          $('form[name="buscador"]').append('<input type="hidden" name="cx" value="{$psConfig.ads_search}" /><input type="hidden" name="cof" value="FORID:10" /><input type="hidden" name="ie" value="ISO-8859-1" />');
        }else if(this.tipo=='google'){
          $('input[name="cx"]').remove();
          $('input[name="cof"]').remove();
          $('input[name="ie"]').remove();
        }
        this.tipo = tipo;
      }
    }
  </script>
  {/literal}
  {if $psQuery || $psAutor}
    <div id="buscadorLite" class="col-md-4">
      <ul class="searchTabs">
        <li class="here"><a href="">Posts</a></li>
        <div class="both"></div>
      </ul>
      <div class="both"></div>
      <div class="searchCont">
        <form action="" method="GET" name="buscador">
          <div class="searchFil">
            <div>
              <img{if $psEngine != 'google'}class="nodisplay"{/if} alt="google-search-engine" src="http://www.google.com/images/poweredby_transparent/poweredby_FFFFFF.gif" id="buscador-logo-google"/>
              <img{if $psEngine != 'web'}class="nodisplay"{/if} alt="web-search-engine" src="{$psConfig.default}/images/socialitmin.gif" id="buscador-logo-web"/>
              <img{if $psEngine != 'tags'}class="nodisplay"{/if} alt="tags-search-engine" src="{$psConfig.default}/images/socialitmin.gif" id="buscador-logo-tags"/>
              <label class="searchWith floatL">
                <a href="javascript:buscador.select('google')" id="select_google"{if $psEngine == 'google'} class="here"{/if}>Google</a>
                <span class="sep">|</span>
                <a href="javascript:buscador.select('web')" id="select_web"{if !$psEngine || $psEngine == 'web'} class="here"{/if}>{$psConfig.titulo}</a>
                <span class="sep">|</span>
                <a href="javascript:buscador.select('tags')" id="select_tags"{if $psEngine == 'tags'} class="here"{/if}>Tags</a></label>
                <div class="clearfix"></div>
            </div>
            <div class="both"></div>
            <!-- inicio search engine -->
            <div class="box_search_engine">
            <strong>Filtrar:</strong>
              <div class="searchEngine">
                <input type="text" value="{$psQuery}" class="searchBar" size="25" name="q"/>
                <input type="submit" title="Buscar" value="Buscar" class="btn btnOk"/>
                <input type="hidden" name="e" value="{$psEngine}" />
                {if $psEngine == 'google'}
                  <input type="hidden" name="cx" value="{$psConfig.ads_search}" />
                  <input type="hidden" name="cof" value="FORID:10" />
                  <input type="hidden" name="ie" value="ISO-8859-1" />
                {/if}
              </div>
              <div class="filterSearch">
                <div class="floatL">
                  <label>Categoria</label><br/>
                  <select name="cat">
                    <option value="-1">Todas</option>
                      {foreach from=$psConfig.categorias item=c}
                        <option value="{$c.cid}"{if $psCategory == $c.cid} selected="true"{/if}>{$c.c_nombre}</option>
                      {/foreach}
                  </select><br>
                  <span id="filtro_autor">
                    <label>Usuario</label><br>
                    <input type="text" name="autor" value="{$psAutor}"/>
                  </span>
                </div>
                <div class="clearfix"></div>
              </div><!-- End SearchFill -->
              <div class="clearfix"></div>
            </div>
            <!-- final search engine -->
            <div class="clearfix"></div>
          </div><!-- End SearchFill -->
        </form>
      </div>
    </div>
    {if $psEngine == 'google'}
      <div id="cse-search-results"></div>
      <script type="text/javascript">
        var googleSearchIframeName = "cse-search-results";
        var googleSearchFormName = "cse-search-box";
        var googleSearchFrameWidth = '100';
        var googleSearchDomain = "www.google.com";
        var googleSearchPath = "/cse";
      </script>
      <script type="text/javascript" src="http://www.google.com/afsonline/show_afs_search.js"></script>
    {else}
      <div id="resultados" class="col-md-8">
        <div id="showResult">
          {$psConfig.ads_728}
          <table class="linksList">
            <thead>
              <tr>
                <th></th>
                <th>Mostrando <strong>{$psResults.total}</strong> de <strong>{$psResults.pages.total}</strong> resultados totales</th>
              </tr>
            </thead>
            <tbody>
              {foreach from=$psResults.data item=r}
              <tr id="div_{$r.post_id}">
                <td title="{$r.c_nombre}" style="background:url({$psConfig.tema.t_url}/images/icons/cat/{$r.c_img}) no-repeat center center;">&nbsp;</td>
                <td>
                  <a class="titlePost" href="{$psConfig.url}/posts/{$r.c_seo}/{$r.post_id}/{$r.post_title|seo}.html">{$r.post_title}</a>
                  <div class="info" >
                    <img alt="Creado hace" src="{$psConfig.tema.t_url}/images/icons/clock.png"/> <strong>{$r.post_date|hace:true}</strong> -
                    <img alt="Posts relacionados" src="{$psConfig.tema.t_url}/images/icons/relacionados.png"/>
                    <a href="{$psConfig.url}/buscador/?q={$r.post_title}&e={$psEngine}&cat={$psCategory}&autor={$psAutor}">Post Relacionados</a> -
                    <img alt="Creado por" src="{$psConfig.tema.t_url}/images/icons/autor.png"/>
                    <a href="{$psConfig.url}/perfil/{$r.user_name}">{$r.user_name}</a> |
                    <img alt="0 puntos" src="{$psConfig.tema.t_url}/images/icons/puntos.png"/> Puntos <strong>{$r.post_puntos}</strong> -
                    <img alt="0 puntos" src="{$psConfig.tema.t_url}/images/icons/favoritos.gif"/> <strong>{$r.post_favoritos}</strong> Favoritos -
                    <img alt="0 puntos" src="{$psConfig.tema.t_url}/images/icons/comentarios.gif"/> <strong>{$r.post_comments}</strong> Comentarios
                  </div>
                </td>
              </tr>
              {/foreach}
            </tbody>
          </table>
        </div>
        <div class="paginadorCom">
          {if $psResults.pages.prev != 0}
            <div class="floatL before">
              <a href="{$psConfig.url}/buscador/?page={$psResults.pages.prev}{if $psQuery}&q={$psQuery}{/if}{if $psEngine}&e={$psEngine}{/if}{if $psCategory}&cat={$psCategory}{/if}{if $psAutor}&autor={$psAutor}{/if}">&laquo; Anterior</a>
            </div>
          {/if}
          {if $psResults.pages.next != 0}
            <div class="floatR next">
              <a href="{$psConfig.url}/buscador/?page={$psResults.pages.next}{if $psQuery}&q={$psQuery}{/if}{if $psEngine}&e={$psEngine}{/if}{if $psCategory}&cat={$psCategory}{/if}{if $psAutor}&autor={$psAutor}{/if}">Siguiente &raquo;</a>
            </div>
          {/if}
        </div>
      </div>
      <div class="both"></div>
    {/if}
  {else}
    <div id="buscadorBig">
      <ul class="searchTabs">
        <li class="here"><a href="">Posts</a></li>
        <li class="clearfix"></li>
      </ul>
      <div class="both"></div>
      <div class="searchCont">
        <form action="" method="GET" name="buscador">
          <div class="searchFil">
            <div>
              <div class="logoMotorSearch">
                <img class="nodisplay" alt="google-search-engine" src="http://www.google.com/images/poweredby_transparent/poweredby_FFFFFF.gif" id="buscador-logo-google"/>
                <img alt="web-search-engine" src="{$psConfig.default}/images/socialitmin.gif" id="buscador-logo-web"/>
                <img class="nodisplay" alt="tags-search-engine" src="{$psConfig.default}/images/socialitmin.gif" id="buscador-logo-tags"/>
              </div>
              <label class="searchWith">
                <a href="javascript:buscador.select('google')" id="select_google">Google</a><span class="sep">|</span>
                <a href="javascript:buscador.select('web')" id="select_web" class="here">{$psConfig.titulo}</a><span class="sep">|</span>
                <a href="javascript:buscador.select('tags')" id="select_tags">Tags</a>
              </label>
              <div class="clearfix"></div>
            </div>
            <div class="clearfix"></div>
            <!-- inicio search engine -->
            <div class="box_search_engine">
            <strong>Filtrar:</strong>
              <div class="searchEngine">
                <input type="text" value="" class="searchBar" size="25" name="q"/>
                <input type="submit" title="Buscar" value="Buscar" class="btn btnOk"/>
                <input type="hidden" name="e" value="web" />
                <div class="clearfix"></div>
              </div>
              <div class="filterSearch">

                <div class="floatL">
                  <label>Categor&iacute;a</label><br>
                  <select name="cat">
                    <option value="-1">Todas</option>
                      {foreach from=$psConfig.categorias item=c}
                        <option value="{$c.cid}">{$c.c_nombre}</option>
                      {/foreach}
                  </select><br>
                  <span id="filtro_autor">
                    <label>Usuario</label><br>
                    <input type="text" name="autor" value="{$psAutor}"/>
                  </span>
                </div>
                <div class="clearfix"></div>
              </div>
              <div class="clearfix"></div>
            </div>
            <!-- inicio search engine -->
            <div class="clearfix"></div>
          </div>
        </form>
      </div>
    </div>
  {/if}
  <div class="both"></div>
{include file='secciones/main_footer.tpl'}
