<!DOCTYPE html PUBLIC>
<html lang="es" xml:lang="es">
    <head>
        <meta http-equiv="Content-type" content="text/html" charset="utr-8"/>
        <title>[$psTitle}</title>
        
    </head>
    <body>
        <!-- header -->
        <header>
            <div id="cabecera">
                <a href="{$psConfig.url}" title="{$psConfig.titulo}">
                    <img src="" title="{$psConfig.titulo}" alt="{$psConfig.titulo}"/>
                </a>
            </div>
        </header>
        <!-- menu -->
        <nav>
            {include file="secciones/header_menu.tpl}
            {include file="secciones/header_submenu.tpl}
            {include file="secciones/header_noticias.tpl}
        </nav>
        <!-- cuerpo de la página -->
        <section>
            
        