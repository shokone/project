/**
 * guardaremos el javascript necesario sólo para la sección portal
 * @name portal.js
 */

 //variable portal 
 //contendrá las funciones necesarias para el portal
 var portal = {
 	cache: new Array(),
 	//cargaremos la tabla seleccionada
 	load_tab:function(type, obj){
        $('#tabs_menu > li').removeClass('active');
        $(obj).parent().addClass('active');
        if(type == 'news'){
        	$('#portal_content').css('background-color', '#FFF');
        }else{
        	$('#portal_content').css('background-color', '#F9F9F9');
        }
        $('#portal_content > div.showHide').hide();
        //cargamos datos
        var status = $('#portal_' + type).attr('status');
        if(status == 'activo'){
            $('#portal_' + type).show();
        }else{
            $('#portal_' + type).show();
            portal.posts_page(type, 1, false);
        }
    },
    //obtenemos la paginación para el listado de post
    posts_page: function(type, page, scroll){
        $('#portal_' + type + '_content').html('<div class="center"><img src="' + global_data.img + '/images/fb-loading.gif" /></div>');
  		if(scroll == true) $.scrollTo('#cuerpocontainer', 200);
  		//si todo bien realizamos la petición ajax
        if(typeof portal.cache[type + '_' + page] == 'undefined'){
            $('#loading').fadeIn(200);
    		$.ajax({
    			type: 'GET',
    			url: global_data.url + '/portal-' + type + '_pages.php?page=' + page,
    			success: function(valor){
                    portal.cache[type + '_' + page] = valor;
                    $('#portal_' + type).attr('status', 'activo');
   				    $('#portal_' + type + '_content').html(valor);
                    $('#loading').fadeOut(400);
    			}
    		});
        } else {
        	//si no añadimos los datos guardados en la cache
            $('#portal_' + type + '_content').html(portal.cache[type + '_' + page]);
        }
    },
    //guardamos la configuración
    save_configs: function(){
		var inputs = $('#config_inputs :input');
        var cat_ids = '';        
		inputs.each(function(){
            if($(this).attr('checked')){ 
            	cat_ids += $(this).val() + ',';
            }
		});
        //enviamos los datos por ajax
        $('#loading').fadeIn(200);
        $.ajax({
        	type: 'POST',
        	url: global_data.url + '/portal-posts_config.php',
        	data: 'cids=' + cat_ids,
        	success: function(valor){
        		switch(valor.charAt(1)){
        			case '0': //si ha ocurrido algún error
                        myActions.alert('Error', valor.substring(4));
        				break;
        			case '1': //si nos ha salido todo bien
                        $('#config_posts').fadeOut(400);
                        portal.posts_page('posts',1, false);
        				break;
        		}
                $('#loading').fadeOut(400);
        	}
        });                
    },
 }