{include file="secciones/main_header.tpl"}
{$psInstall}
	<div class="row">
		<div class="col-md-8 col-sm-10 col-xs-12">
      {include file='modulos/m.home_cats.tpl'}
			{include file='modulos/m.home_last_posts.tpl'}
		</div>
  		<div class="col-md-4 col-xs-12">
  		    {include file='modulos/m.home_search.tpl'}
          {include file='modulos/m.home_stats.tpl'}
          {include file='modulos/m.home_afiliados.tpl'}
          {include file='modulos/m.global_ads_300.tpl'}
  		</div>
	</div>
{include file="secciones/main_footer.tpl"}