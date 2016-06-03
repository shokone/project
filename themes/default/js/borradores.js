

/**
 * variable borradores
 * contendrá las diferentes funciones utilizadas para los borradores
 */
var borradores = {
	plantilla_borrador: '',
	r: new Array(),
	counts: new Array(),
	//obtenemos el dato de las opciones por defecto
	filtro: 'todos',
	categoria: 'todas',
	orden: 'titulo',
	//por defecto la opción anterior estará vacía
	filtro_anterior: '',
	categoria_anterior: '',
	orden_anterior: '',
	//imprimiremos los datos del borrador
	printResult: function(){
		var element = $('ul#resultados-borradores');
		element.html('');
		$.each(this.r, function(val, borrador){
			var bor_replace = borradores.plantilla_borrador
				.replace('__id__', borrador['id'])
				.replace('__categoria__', borrador['categoria'])
				.replace('__imagen__', borrador['imagen'])
				.replace('__tipo__', borrador['tipo'])
				.replace('__categoria_name__', borrador['categoria_name'])
				.replace('__url__', borrador['url'])
				.replace('__titulo__', borrador['titulo'])
				.replace('__causa__', borrador['causa'])
				.replace('__fecha_guardado__', borrador['fecha_print'])
				.replace('__borrador_id__', borrador['id']);
			if(borrador['tipo']=='borradores'){
				bor_replace = bor_replace.replace('__url__', borrador['url']).replace('__onclick__', '');
			}else if(borrador['tipo']=='eliminados'){
				bor_replace = bor_replace.replace('__url__', '').replace('__onclick__', 'borradores.show_eliminado(' + borrador['id'] + '); return false;');
			}

			element.append(bor_replace);
			//eliminamos la causa de los que no son eliminados
			if(borrador['tipo']!='eliminados'){ 
				$('ul#resultados-borradores li#borrador_id_' + borrador['id'] + ' span.causa').remove();
			}
		});
	},
	//imprimimos los contadores
	printCounts: function(printCat){
		//obtenemos los filtros
		$('ul#borradores-filtros li#todos span.count').html(this.counts['todos']);
		$('ul#borradores-filtros li#borradores span.count').html(this.counts['borradores']);
		$('ul#borradores-filtros li#eliminados span.count').html(this.counts['eliminados']);
		//obtenemos las categorías
		$('ul#borradores-categorias li#todas span.count').html(this.counts['todos']);
		$.each(this.counts['categorias'], function(categoria, data){
			if(printCat){
				$('ul#borradores-categorias').append('<li id="' + categoria + '"><span class="cat-title"><a href="" onclick="borradores.active(this); borradores.categoria = \'' + categoria + '\'; borradores.query(); return false;">' + data['name'] + '</a></span> <span class="count">' + data['count'] + '</span></li>');
			}else{
				$('ul#borradores-categorias li#' + categoria + ' span.count').html(data['count']);
			}
		});
	},
};

//con esta función ordenaremos un array
function sortObject(obj){
	var sort = new Array();
	var key;
	var a = new Array();
	for(key in obj)
		if(obj.hasOwnProperty(key)){
			a.push(key);
		}
	a.sort();
	for(key = 0; key < a.length; key++){
		sort[a[key]] = obj[a[key]];
	}
	return sort;
}

