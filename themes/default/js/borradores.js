

/**
 * variable borradores
 * contendrá las diferentes funciones utilizadas para los borradores
 */
var borradores = {
	plantilla_borrador: '',
	bor_data: [],
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
		$.each(this.bor_data, function(val, borrador){
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
	//activamos el elemento
	active: function(elem){
		$(elem).parent().parent().parent().children('li').removeClass('active');
		$(elem).parent().parent().addClass('active');
	},
	//mostramos el post eliminado
	show_eliminado: function(id){
		myActions.show();
		myActions.title('Cargando Post');
		myActions.body('Cargando Post...', 200);
		myActions.buttons(true, true, 'Aceptar', 'close', true, true, false);
		myActions.style();
		myActions.inicio_cargando();
        $('#loading').fadeIn(200);
        //realizamos la petición ajax
		$.ajax({
			type: 'POST',
			url: global_data.url + '/borradores-get.php',
			data: 'borrador_id=' + id,
			success: function(valor){
				alert(valor);
				switch(valor.charAt(1)){
					case '0': //si ha ocurrido algún error
						myActions.alert('Error', valor.substring(4));
						break;
					case '1': //si todo ha salido bien
						myActions.title('Post');
						myActions.body(valor.substring(4), 540);
						myActions.buttons(true, true, 'Aceptar', 'close', true, true, false);
						myActions.style();
						break;
				}
                $('#loading').fadeOut(400);
			},
			error: function(){	
				myActions.alert('Error', 'Ocurri&oacute; un error al intentar procesar lo solicitado');
                $('#loading').fadeOut(400);
			},
			complete: function(){
				myActions.final_cargando();
                $('#loading').fadeOut(400);
			}
		});
	},
	//eliminamos el borrador seleccionado
	eliminar: function(id, accion){
		myActions.default();
		if(accion){
			myActions.show();
			myActions.title('Eliminar Borrador');
			myActions.body('&iquest;Seguro que deseas eliminar este borrador?');
			myActions.buttons(true, true, 'S&Iacute;', 'borradores.eliminar(' + id + ', '+false+')', true, false, true, 'NO', 'close', true, true);
			myActions.style();
		}else{
		  	$('#loading').fadeIn(200);
			$.ajax({
				type: 'POST',
				url: global_data.url + '/borradores-eliminar.php',
				data: 'borrador_id=' + id,
				success: function(valor){
					switch(valor.charAt(1)){
						case '0': //si ha ocurrido algún error
							myActions.alert('Error', valor.substring(4));
							break;
						case '1': //si ha salido todo bien
							$('li#borrador_id_' + id).fadeOut('normal', function(){ $(this).remove(); });
							//si era el último borrador imprimimos un mensaje diferente
							if(borradores_data.length==1){
								$('div#borradores div#res').html('<div class="emptyData">No tienes ning&uacute;n borrador ni post eliminado</div>');
							}
							//Lo elimino de la variable borradores_data
							for(var a=0; a<borradores_data.length; a++){
								if(borradores_data[a]['id']==id){
									//restamos 1 a los contadores
									borradores.counts['todos']--;
									borradores.counts[borradores_data[a]['tipo']]--;
									borradores.counts['categorias'][borradores_data[a]['categoria']]['count']--;
									borradores_data.splice(a, 1);
									break;
								}
							}
							//Lo elimino de borradores.r
							for(var a=0; a<borradores.bor_data.length; a++){
								if(borradores.bor_data[a]['id']==id){
									borradores.bor_data.splice(a, 1);
									break;
								}
							}
							//actualizamos los contadores
							borradores.printCounts();
							break;
					}
                    $('#loading').fadeOut(400);
				},
				error: function(){	
					myActions.alert('Error', 'Ocurri&oacute; un error al intentar procesar lo solicitado');
                    $('#loading').fadeOut(400);
				}
			});
		}
	},
	//funciones para el buscador
	//variable que guardará el valor de la búsqueda
	search_q: '',
	//variable que guardará el valor de la búsqueda anterior
	search_q_anterior: '',
	//función para el buscador
	search: function(q, event){
		tecla = (document.all) ? event.keyCode:event.which;
		//si pulsamos la tecla escape borramos
		if(tecla==27){
			q = '';
			$('#borradores-search').val('');
		}
		if(q == this.search_q){
			return;
		}
		//consultamos
		this.search_q = q;
		this.query();
	},
	//función para realizar las diferentes búsquedas y el orden de los borradores
	//utilizaremos jLinq para realizar las consultas más rápido
	query: function(force_no_parcial){
		//comprobamos si realizamos una búsqueda parcial o no
		var parcial = false;
		if(!force_no_parcial){
			//comprobamos el filtro
			if(this.filtro_anterior != this.filtro){
				parcial = (this.filtro_anterior == 'todos');
			}
			//comprobamos la categoría
			else if(this.categoria_anterior != this.categoria){
				parcial = (this.categoria_anterior == 'todas');
			}
			//comprobamos el orden
			else if(this.orden_anterior != this.orden){
				parcial = true;
			}
			//comprobamos la búsqueda
			else if(this.search_q_anterior != this.search_q){
				//si se hace una búsqueda parcial calculamos a partir de la búsqueda anterior
				var aux = new RegExp(this.search_q_anterior);
				parcial = aux.test(this.search_q);
			}
		}

		//comprobamos si está vacío
		//si es así no hacemos nada
		if((parcial && this.bor_data.length==0) || (!parcial && borradores_data.length == 0)){
			this.filtro_anterior = this.filtro;
			this.categoria_anterior = this.categoria;
			this.orden_anterior = this.orden;
			this.search_q_anterior = this.search_q;
			return;
		}
		//realizamos las comprobaciones
		//comprobamos
		if(parcial){
			this.bor_data = jLinq.from(this.bor_data);
		}else{
			this.bor_data = jLinq.from(borradores_data);
		}
		//comprobamos el filtro
		if(this.filtro != 'todos' && (!parcial || this.filtro_anterior != this.filtro)){
			this.bor_data = this.bor_data.equals('tipo', this.filtro);
		}
		//comprobamos la categoría
		if(this.categoria != 'todas' && (!parcial || this.categoria_anterior != this.categoria)){
			this.bor_data = this.bor_data.equals('categoria', this.categoria);
		}
		//comprobamos el campo buscar
		if(this.search_q.length == '' && (!parcial || this.search_q_anterior != this.search_q)){
			this.bor_data = this.bor_data.contains('titulo', this.search_q);
		}
		//obtenemos el orden
		if(!parcial || this.orden_anterior != this.orden){
			//this.bor_data = jLinq.orderBy(this.orden, this.bor_data);
		}
		//guardamos los datos
		this.bor_data = this.bor_data.select();
		this.filtro_anterior = this.filtro;
		this.categoria_anterior = this.categoria;
		this.orden_anterior = this.orden;
		this.search_q_anterior = this.search_q;
		//imprimimos los datos
		this.printResult();
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

//cargamos algunos datos al cargar la página
$(document).ready(function(){
	//guardamos la plantilla
	borradores.plantilla_borrador = $('#template-result-borrador').html();
	$('#template-result-borrador').remove();

	//inicializamos los contadores
	borradores.counts = {'todos': 0, 'borradores':0, 'eliminados':0, 'categorias': {}};

	//realizamos el primer conteo
	$.each(borradores_data, function(a, borrador){
		borradores.counts['todos']++;
		borradores.counts[borrador['tipo']]++;
		if(borradores.counts['categorias'][borrador['categoria']]){
			borradores.counts['categorias'][borrador['categoria']]['count']++;
		}else{
			borradores.counts['categorias'][borrador['categoria']] = {'name': borrador['categoria_name'], 'count':1};
		}
	});
	//ordenamos las categorias
	borradores.counts['categorias'] = sortObject(borradores.counts['categorias']);

	//mostramos los contadores
	borradores.printCounts(true);

	//realizamos la consulta inicial
	borradores.query(true);
});