/**
 * archivo javascript para el control de acciones de la sección fotos
 * @name fotos.js
 */

//función para contar el total de letras mayúsculas en el texto
function countUpperCase(string) {
	var str = string.replace(/([A-Z])+/g, '').length;
	var str2 = string.replace(/([a-zA-Z])+/g, '').length;
	var total = (string.length  - str) / (string.length - str2) * 100;
	return total;
}

//función para mostrar el mensaje de error
function showError(obj, str) {
	$(obj).parent('li').addClass('error').children('span.errormsg').html(str).show();
	$.scrollTo($(obj).parent('li'), 500);
}

//función para volver a ocultar el mensaje de error
function hideError(obj) {
	$(obj).parent('li').removeClass('error').children('span.errormsg').html('').hide();
}

//función para contar el total de caracteres del textarea que guarda la descripción de la foto
function contarLetras(obj) {
    if (obj.value.length > 1500) {
        obj.value = obj.value.substr(0,1500);
        showError(obj, 'La descripci&oacute;n no debe exeder los 500 caracteres.');
    } else {
    	hideError(obj);
    }
}

//variable que contendrá las funciones principales de las fotos
var fotos = {
	//función para agregar una nueva foto
	agregar: function(){
        var error = false;
        $('.required').each(function(){
        	//comprobamos que todos los campos están rellenos
        	if (!$.trim($(this).val())) {alert('hola');
        		showError(this, 'Este campo es obligatorio');
        		$(this).parent('li').addClass('error');
        		error = true;
        		return false;
        	} else if($(this).attr('name') == 'url'){
        		//comprobamos que la url es correcta
        	    var val_url = fotos.validaUrl(this, $(this).val());
                if(val_url != true) {
                    error = true;
                    return false;
                } else {
                	error = false;
                }
        	}
        });
        if(error == true) {
			return false;
		} 
        //comprobamos la longitud de caracteres de la descripción
        if ($('textarea[name=desc]').val().length > 500) {
			showError($('textarea[name=desc]').get(0), 'La descripci&oacute;n no debe exeder los 500 caracteres.');
			return false;
		}
        $('.fade_out').fadeOut("slow",function(){
            $('.loader').fadeIn();  
        });
        alert('jaja');
        //submiteamos el formulario 
        $('form[name=add_foto]').submit();
    },
    //función para comentar una foto
	comentar: function(){
        //deshabilitamos el botón hasta completar la operación
        $('#btnComment').attr({'disabled':'disabled'});
        //comprobamos
        var textarea = $('#mensaje');
    	var text = textarea.val();
    	if(text == '' || text == textarea.attr('title')){
    		textarea.focus();
            $('#btnComment').attr({'disabled':''});
    		return;
    	}else if(text.length > 1000){
    		alert("Tu comentario no puede ser mayor a 1000 caracteres.");
    		textarea.focus();
            $('#btnComment').attr({'disabled':''});
    		return;
    	}
        //enviamos los datos
        var auser = $('input[name=auser_post]').val();
        $('#loading').fadeIn(250); 
       	$.ajax({
    		type: 'POST',
    		url: global_data.url + '/comentario-agregar.php?ps=true&do=fotos',
    		data: 'comentario=' + encodeURIComponent(text) + '&fotoid=' + globalget('fotoid') + '&auser=' + auser,
    		success: function(valor){
    			switch(valor.charAt(1)){
    				case '0': //si ha ocurrido algún error
    					$('.form .error').html(valor.substring(4)).show('slow');
                        $('#btnComment').attr({'disabled':''});
    					break;
    				case '1': //si ha salido todo bien
						$('#no-comments').hide();
						$('#mensajes').append(valor.substring(4));
                        $('.form').html('<div class="emptyData">Tu comentario fue agregado correctamente!</div>');
						//sumamos el comentario a las estadísticas
						var ncomments = parseInt($('#ncomments').text());
						$('#ncomments').text(ncomments + 1);
                        $('#btnComment').attr({'disabled':''});
                        //eliminamos el mensaje de no hay comentarios si era el primero
                        $('.noComments').remove();
    					break;
    			}
                $('#loading').fadeOut(250); 
    		}
		});
    },
    //función para votar una foto
	votar: function(voto){
        //comprobamos si el voto es positivo o negativo
        voto = (voto == 'pos') ? 'pos' : 'neg';
        //obtenemos el total de votos
    	var total_votos = parseInt($('#votos_total_' + voto).text());
        total_votos = (isNaN(total_votos)) ? 0 : total_votos;
        $('#loading').fadeIn(250); 
        //realizamos la petición ajax
    	$.ajax({
    		type: 'POST',
    		url: global_data.url + '/comentario-votar.php?do=fotos',
    		data: 'voto=' + voto + '&fotoid=' + globalget('fotoid'),
    		success: function(valor){
    			switch(valor.charAt(1)){
    				case '0': //si ha ocurrido algún error
                        myActions.alert('Votar Foto', valor.substring(4));
    					break;
    				case '1': //si ha salido todo bien
    					total_votos = total_votos + 1;
    					$('#actions').html(valor.substring(4)).fadeIn("fast");
    					$('#votos_total_' + voto).text(total_votos);
    					break;
    			}
                $('#loading').fadeOut(250); 
    		}
        });
    },
    //función para mandar un mensaje de confirmación al borrar una foto o comentario
	borrar:function(id, type){
        //obtenemos el tipo
        var ftype = (type == 'com') ? 'comentario' : 'foto';
        var faux = (type == 'com') ? 'este ' : 'esta ';
        //
        myActions.mask_close = false;
        myActions.show(true);
		myActions.title('Eliminar ' + ftype);
		myActions.body('¿Seguro que quieres eliminar ' + faux + ftype);
		myActions.buttons(true, true, 'Eliminar ' + ftype, 'fotos.del_' + ftype + '(' + id + ')', true, true, true, 'Cancelar', 'close', true, false);
		myActions.style();
    },
	//función para validar la url obtenida
	validaUrl: function(obj, url){
		var regex = /^(ht|f)tps?:\/\/\w+([\.\-\w]+)?\.([a-z]{2,3}|info|mobi|aero|asia|name)(:\d{2,5})?(\/)?((\/).+)?$/i;
		var ext = url.substr(-3);
		// url válida
		if(regex.test(url) == false){
			showError(obj, 'No es una direcci&oacute;n v&aacute;lida');
			return false;
		} else if(ext != 'gif' && ext != 'png' && ext != 'jpg'){
			showError(obj, 'S&oacute;lo se permiten im&aacute;genes .gif, .png y .jpg');
			return false; 
		} else {
			return true;
		}
    },
    //función para borrar una foto
	del_foto: function(fid){
        $('#loading').fadeIn(250); 
        //realizaremos una petición ajax
    	$.ajax({
    		type: 'POST',
    		url: global_data.url + '/fotos/borrar.php',
    		data: 'fid=' + fid,
    		success: function(valor){
    			switch(valor.charAt(1)){
    				case '0': //si ha ocurrido algún error
                        myActions.alert('Error:', valor.substring(4));
    					break;
    				case '1': //si ha salido todo bien
                        myActions.default();
                        //redireccionamos
                        location.href = global_data.url + '/fotos/';
    					break;
    			}
                $('#loading').fadeOut(250); 
    		}
        });
    },
    //función para eliminar el comentario de una foto
	del_comentario: function(cid){
        $('#loading').fadeIn(250); 
        //realizamos la petición ajax
    	$.ajax({
    		type: 'POST',
    		url: global_data.url + '/comentario-borrar.php?do=fotos',
    		data: 'cid=' + cid,
    		success: function(valor){
    			switch(valor.charAt(1)){
    				case '0': //si ha ocurrido algún error
                        myActions.alert('Error:', valor.substring(4));
    					break;
    				case '1': //si ha salido todo bien
						var ncomments = parseInt($('#ncomments').text());
						$('#ncomments').text(ncomments - 1);
						$('#div_cmnt_' + cid).slideUp( 1000, 'easeInOutElastic');
						$('#div_cmnt_' + cid).remove();
                        myActions.default();
    					break;
    			}
                $('#loading').fadeOut(250); 
    		}
        });
    },
}

//cargamos algunas funciones al cargar la página
$(function(){
    var width = 0;
    var left = 3;
    $('#imagen').hover(function(){
        if(width <= 0){
            width = $('#imagen .img').css("width");
            width = width.substring(-2);
            width = parseInt(width) - 6;
            left = ((568 - width) / 2);
            $('.tools').css({"width": width + 'px', "left": left + 'px'})
        }
    });
	//ocultamos los errores
	$('.required').bind('keyup change',function(){
		if ($.trim($(this).val())) {
			hideError(this);
		}
	});
	//comprobamos el título de la foto
	$('input[name=titulo]').bind('keyup',function(){
		if ($(this).val().length >= 5 && countUpperCase($(this).val()) > 10) {
			showError(this, 'El t&iacute;tulo no debe estar en may&uacute;sculas');
		}else {
			hideError(this);
		}
	});
});