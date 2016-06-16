<div class="container-fluid">
    <div id="search_box" class="new-search posts">
        <div class="bar-options">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#">Posts</a></li>
                <!--<li><a href="#">Google</a></li>-->
            </ul>
        </div>
        <div class="search-body clearfix">
            <form action="{$psConfig.url}/buscador/" name="search" gid="{$psConfig.ads_search}" class="form-inline" role="form">
                <input type="hidden" name="e" value="web" />
                <div class="form-group">
                    <input type="text" autocomplete="on" placeholder="Buscar" name="q" class="form-control"/>
                    <button type="submit" class="btn btn-default" value="Buscar">
                        <span class="glyphicon glyphicon-search"></span>&nbsp;Buscar
                    </button>
                </div>
                <a class="btn-search-home" href="javascript:$('form[name=search]').submit()"></a>
            </form>
        </div>
    </div>
    <div class="both"></div>
</div>