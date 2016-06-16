
//variable favoritos
//contendrá todas las funciones necesarias para la sección favoritos
var favoritos = {
	template_favorito: '',
	template_categoria: '',
	r: {},
	counts: {},
	categoria: 'todas',
	orden: 'fecha_guardado',
	categoria_anterior: '',
	orden_anterior: '',
	//imprimimos el resultado
	printResult: function(){
		var element = $('div#resultados tbody');
		element.html('');
		$.each(this.r, function(i, favorito){
			var val = favoritos.template_favorito.replace(/__fav_id__/g, 
				favorito['fav_id']).replace(/__imagen__/g, 
				favorito['imagen']).replace(/__categoria_name__/g, 
				favorito['titulo']).replace(/__titulo__/g, 
				favorito['titulo']).replace(/__url__/g, 
				favorito['url']).replace(/__fecha_creado_formato__/g, 
				favorito['fecha_creado_formato']).replace(/__fecha_creado_palabras__/g, 
				favorito['fecha_creado_palabras']).replace(/__fecha_guardado_formato__/g, 
				favorito['fecha_guardado_formato']).replace(/__fecha_guardado_palabras__/g, 
				favorito['fecha_guardado_palabras']).replace(/__puntos__/g, 
				my_number_format(favorito['puntos'])).replace(/__comentarios__/g, 
				my_number_format(favorito['comentarios']));
			element.append(val);
		});
	},
	//contamos el total de categorias
	printCounts: function(printCategorias){
		//Categorias
		$.each(this.counts, function(categoria, data){
			if(printCategorias){
				$('div.categoriaList ul').append(
					favoritos.template_categoria.replace(/__categoria__/g, 
					categoria).replace(/__categoria_name__/g, 
					data['name']).replace(/__count__/g, 
					data['count']
				));
			}else{
				$('div.categoriaList ul li#cat_' + categoria + ' span.count').html(data['count']);
			}
		});
	},
	//realizamos la consulta
	query: function(force_no_parcial){
		//force 
		//true -> No hace la busqueda parcial. 
		//false -> Dependiendo del caso, determina si usa la busqueda parcial o no.
		//determinamos si es una búsqueda parcial o no
		var parcial = false;
		if(!force_no_parcial){
			//categoría del favoritos
			if(this.categoria_anterior != this.categoria){
				parcial = (this.categoria_anterior == 'todas');
			}
			//orden de muestra
			else if(this.orden_anterior != this.orden){
				parcial = true;
			}
			//buscador
			else if(this.search_q_anterior != this.search_q){
				var re = new RegExp(this.search_q_anterior);
				parcial = re.test(this.search_q);
			}
		}
		//si está vacío no consultamos nada
		if((parcial && this.r.length==0) || (!parcial && favoritos_data.length == 0)){
			this.categoria_anterior = this.categoria;
			this.orden_anterior = this.orden;
			this.search_q_anterior = this.search_q;
			return;
		}
		this.r = jLinq.from(parcial ? this.r : favoritos_data);
		//comprobamos categoría
		if(this.categoria != 'todas' && (!parcial || this.categoria_anterior != this.categoria)){
			this.r = this.r.equals('categoria', this.categoria);
		}
		//comprobamos buscador
		if(this.search_q != '' && (!parcial || this.search_q_anterior != this.search_q)){
			this.r = this.r.contains('titulo', this.search_q);
		}
		//comprobamos orden
		if(!parcial || this.orden_anterior != this.orden || this.eliminados_force_order){
			//this.r = this.r.orderBy((this.orden=='titulo' ? '' : '-') + this.orden); 
		}
		this.eliminados_force_order = false;
		this.r = this.r.select();
		this.categoria_anterior = this.categoria;
		this.orden_anterior = this.orden;
		this.search_q_anterior = this.search_q;
		this.printResult();
	},
	//si está activo
	active: function(element){
		return true;
	},
	//cambiamos el elemento activo
	active2: function(element){
		$(element).parent().parent().children('th').children('a').removeClass('active');
		$(element).addClass('active');
	},
	//buscador de favoritos
	search_q: '',//búsqueda actual
	search_q_anterior: '',//búsqueda anterior
	//función para buscar
	search: function(q, event){
		tecla = (document.all) ? event.keyCode:event.which;
		//pulsando la tecla escape salimos y borramos el input
		if(tecla==27){ 
			q = '';
			$('#favoritos-search').val('');
		}
		if(q == this.search_q){
			return;
		}
		this.search_q = q;
		this.query();
	},
	//efecto focus en el buscador
	search_focus: function(){
		$('label[for="favoritos-search"]').hide();
	},
	//efecto blur en el buscador
	search_blur: function(){
		if($('#favoritos-search').val() == ''){
			$('label[for="favoritos-search"]').show();
		}
	},
	//reactivamos un post en favoritos
	reactivar: function(fav_id, obj){
		//buscamos post_id y el fav_date
		for(var a=0, s=this.eliminados.length; a<s; ++a){
			if(this.eliminados[a]['fav_id'] == fav_id){
				var post_id = this.eliminados[a]['post_id'];
				var fav_date = this.eliminados[a]['fecha_guardado'];
				break;
			}
		}
		//comprobamos si lo ha encontrado
		if(a == s){
			return false;
		}
        //realizaremos la petición ajax
        $('#loading').fadeIn(250);
		$.ajax({
			type: 'POST',
			url: global_data.url + '/favoritos-agregar.php',
			data: 'postid=' + post_id + '&reactivar=' + fav_date + globalget('key'),
			success: function(valor){
				switch(valor.charAt(1)){
					case '0': //si ha ocurrido algún error
						myActions.alert('Error', valor.substring(4));
						break;
					case '1': //si ha salido todo bien
						//Lo elimino de favoritos eliminados
						for(var i=0, s=favoritos.eliminados.length; i<s; ++i){
							if(favoritos.eliminados[i]['fav_id'] == fav_id){
								var favorito = favoritos.eliminados[i];
								favoritos.eliminados.splice(i, 1);
								break;
							}
						}
						//sumamos uno al conteo de la categoria
						favoritos.counts[favorito['categoria']]['count']++;

						//cambiamos fav_id por el nuevo id
						favorito['fav_id'] = fav_id = h.substring(3);

						//lo guardamos
						favoritos_data.push(favorito);
						favoritos.r.push(favorito);

						//forzamos el ordenamiento en la próxima consulta, ya que al agregarlo al final lo pierde
						favoritos.eliminados_force_order = true;
						$(obj).children().attr({
							'src': global_data.img + 'images/borrar.png',
							'title': 'Borrar',
							'alt': 'Borrar'
						});
						$(obj).parent().parent().css('opacity', '1');
						$(obj).unbind('click').bind('click', function(){ favoritos.eliminar(fav_id, this); return false; });
						//actualizamos lon contadores mostrados
						favoritos.printCounts();
						break;
				}
                $('#loading').fadeOut(350);
			},
			error: function(){	
				myActions.alert('Error', 'Hubo un error al intentar procesar lo solicitado');
                $('#loading').fadeOut(350);
			}
		});
	},
	//forzamos la eliminacion del orden
	eliminados_force_order: false,
	//guardamos un listado de favoritos eliminados
	eliminados: new Array(), 
	//función para eliminar un favorito
	eliminar: function(fav_id, obj){
	   $('#loading').fadeIn(250);
	   //realizamos la petición ajax
		$.ajax({
			type: 'POST',
			url: global_data.url + '/favoritos-borrar.php',
			data: 'fav_id=' + fav_id + globalget('key'),
			success: function(valor){
				switch(valor.charAt(1)){
					case '0': //si ha ocurrido algún error
						myActions.alert('Error', valor.substring(4));
						break;
					case '1': //si nos ha salido todo bien
						for(var i=0, s=favoritos.r.length; i<s; ++i){
							if(favoritos.r[i]['fav_id'] == fav_id){
								favoritos.eliminados.push(favoritos.r[i]);
								favoritos.counts[favoritos.r[i]['categoria']]['count']--;
								favoritos.r.splice(i, 1);
								break;
							}
						}

						for(var i=0, s=favoritos_data.length; i<s; ++i){
							if(favoritos_data[i]['fav_id'] == fav_id){
								favoritos_data.splice(i, 1);
								break;
							}
						}

						$(obj).children().attr({
							'src': global_data.img + 'images/reactivar.png',
							'title': 'Reactivar',
							'alt': 'reactivar'
						});
						$(obj).parent().parent().css('opacity', '0.5');
						$(obj).removeAttr('onclick').unbind('click').bind('click', function(){ favoritos.reactivar(fav_id, this); return false; });
						//actualizamos los contadores mostrados
						favoritos.printCounts();
						break;
				}
                $('#loading').fadeOut(350);
			},
			error: function(){	
				myActions.alert('Error', 'Hubo un error al intentar procesar lo solicitado');
                $('#loading').fadeOut(350);
			}
		});
	},
}

//función para ordenar un objeto
function sortObject(obj){
	var sorted = {};
	var key;
	var a = [];
	for(key in obj){
		if(obj.hasOwnProperty(key)){
			a.push(key);
		}
	}
	a.sort();
	for(key = 0; key < a.length; key++){
		sorted[a[key]] = obj[a[key]];
	}
	return sorted;
}

//realizamos las acciones necesarias al cargar la página
$(document).ready(function(){
	//guardamos la plantilla a mostrar con cada favorito
	favoritos.template_favorito = '<tr id="favorito_id___fav_id__">';
	favoritos.template_favorito += '<td><img src="'+ global_data.img +'images/icons/cat/__imagen__" title="__categoria_name__"/></td>';
	favoritos.template_favorito += '<td style="text-align:left"><a class="titlePost" title="__titulo__" href="__url__">__titulo__</a></td>';
	favoritos.template_favorito += '<td title="__fecha_creado_formato__">__fecha_creado_palabras__</td>';
	favoritos.template_favorito += '<td title="__fecha_guardado_formato__">__fecha_guardado_palabras__</td>';
	favoritos.template_favorito += '<td class="color_green">__puntos__</td><td>__comentarios__</td>';
	favoritos.template_favorito += '<td><a id="change_status" href="" title ="Borrar favorito" alt="Borrar favorito" class="btn btn-danger" onclick="favoritos.eliminar(__fav_id__, this); return false;">';
	favoritos.template_favorito += 'Borrar</a></td>';
	favoritos.template_favorito += '</tr>';
	//guardamos la plantilla a mostrar con cada categoria
	favoritos.template_categoria = '<li id="cat___categoria__">';
	favoritos.template_categoria += '<a href="" onclick="favoritos.active(this); favoritos.categoria = \'__categoria__\'; favoritos.query(); return false;">';
	favoritos.template_categoria += '__categoria_name__</a> (<span class="count">__count__</span>)</li>';

	//hacemos un conteo inicial
	$.each(favoritos_data, function(i, favorito){
		if(favoritos.counts[favorito['categoria']]){
			favoritos.counts[favorito['categoria']]['count']++;
		}else{
			favoritos.counts[favorito['categoria']] = {'name': favorito['categoria_name'], 'count':1};
		}
	});
	favoritos.counts = sortObject(favoritos.counts);

	//imprimimos los contadores
	favoritos.printCounts(true);

	//realizamos la consulta por defecto
	favoritos.query(true);
});
