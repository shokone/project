<div id="" >
	<div>
    	<ul>
			<li><a>Google</a></li>
   			<li><a>Posts</a></li>
        </ul>
    </div>
    <div>
        <form action="{$psConfig.url}/buscador/" name="search" gid="{$psConfig.ads_search}">
            <div></div>
            <input type="text" autocomplete="off" value="Buscar" name="q"/>
            <input type="hidden" name="e" value="web" />
            <div></div>
            <a href="javascript:$('form[name=search]').submit()"></a>
        </form>
    </div>
    <a class="options" id="sh_options" onclick="$('#search-home-cat-filter').show(); return false">Opciones</a>
</div>
<div class="clearBoth"></div>