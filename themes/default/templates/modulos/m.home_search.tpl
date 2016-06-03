<div id="search_box" class="new-search posts">
    <div class="bar-options">
        <ul class="clearfix">
            <li class="web-tab"><a>Google</a></li>
            <li class="posts-tab selected"><a>Posts</a></li>
        </ul>
    </div>
    <div class="search-body clearfix">
        <form action="{$psConfig.url}/buscador/" name="search" gid="{$psConfig.ads_search}">
            <div class="input-search-left"></div>
            <input type="text" autocomplete="on" value="Buscar" name="q" class="input-search-middle"/>
            <input type="hidden" name="e" value="web" />
            <div class="input-search-right"></div>
            <a class="btn-search-home" href="javascript:$('form[name=search]').submit()"></a>
        </form>
    </div>
</div>
<div class="both"></div>
