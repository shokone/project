<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="es" xml:lang="es">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>{$psTitle}</title>
		<link href="{$psConfig.tema.t_url}/style.css" rel="stylesheet" type="text/css" />


		<!-- AGREGAMOS UN ESTILO EXTRA SI EXISTE -->
		<link href="{$psConfig.css}/{$psPage}.css" rel="stylesheet" type="text/css" />
		<link href="{$psConfig.css}/bootstrap.min.css" rel="stylesheet" type="text/css" />
		<link href="{$psConfig.css}/bootstrap-theme.css" rel="stylesheet" type="text/css" />

		<link rel="shortcut icon" href="{$psConfig.images}/favicon.ico" type="image/x-icon" />

		<script type="text/javascript" src="{$psConfig.js}/jquery-2.2.3.min.js"></script>
		<script type="text/javascript" src="{$psConfig.js}/bootstrap.min.js"></script>
		<script type="text/javascript" src="{$psConfig.js}/npm.js"></script>
		<script type="text/javascript" src="{$psConfig.js}/acciones.js"></script>
		<script type="text/javascript" src="{$psConfig.js}/ckeditor/ckeditor.js"></script>
		{if $psUser->member == 0}
			<script type="text/javascript" src="{$psConfig.js}/registro.js"></script>
		{/if}
		<script type="text/javascript">
		// {literal}
		var global_data = {
		// {/literal}
			user_key: '{$psUser->user_id}',
			postid: '{$psPost.post_id}',
			fotoid: '{$psFoto.foto_id}',
			img: '{$psConfig.tema.t_url}/',
			url: '{$psConfig.url}',
			domain: '{$psConfig.domain}',
		    s_title: '{$psConfig.titulo}',
		    s_slogan: '{$psConfig.slogan}'
		// {literal}
		};
		// {/literal} 
		</script>
	</head>
	<body>
		<div id="loading"><img src="{$psConfig.tema.t_url}/images/ajax-loader.gif" width="50" height="50"/>Cargando...</div>
		<div id="myActions" class="nodisplay"></div>
		<div class="UIBeeper" id="BeeperBox"></div>
		<header>
			<div id="logo">
            	<a id="logoi" title="{$psConfig.titulo}" href="{$psConfig.url}">
                	<img title="{$psConfig.titulo}" alt="{$psConfig.titulo}" src="{$psConfig.images}/logo.png"/>
                </a>
            </div>
		</header>
		<nav class="navbar navbar-default" role="navigation">
			{include file='secciones/header_menu.tpl'}
	        {include file='secciones/header_noticias.tpl'}
		</nav>
		<section class="container">
		<span class="irArriba icon-arrow-up"></span>