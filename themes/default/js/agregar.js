/**
 * Cargaremos el javascript necesario para guardar el post como publicado o como borrador
 * @name agregar.js
 */
var textcuerpo = '';
//damos un tiempo máximo para realizar la acción del borrador
var borrador_setTimeout;
//variable que guardará los datos del último borrador
var borrador_ult = '';
//variable que guardará si está activada la opción de guardar borradores
var borrador_enabled = true;

//función para activar el botón de guardar borrador
function borrador_save_enabled(){
	if($('input#borrador-save')){
		$('input#borrador-save').removeClass('disabled').removeAttr('disabled');
	}
	borrador_enabled = true;
}
//función para desactivar el botón de guardar borrador
function borrador_save_disabled(){
	if($('input#borrador-save')){
		$('input#borrador-save').addClass('disabled').attr('disabled', 'disabled');
	}
	borrador_enabled = false;
}

//función para mostrar el mensaje de error
function showError(obj, txt){
	if (obj.tagName.toLowerCase() == 'textarea') {
		obj = $(obj).parent().parent().parent();
	}
	$(obj).parent('div').addClass('error').children('span.errormsg').html(txt).show();
	//$.scrollTo($(obj).parent('div'), 400);
}

//función para ocultar el mensaje de error
function hideError(obj) {
	if (obj.tagName.toLowerCase() == 'textarea') {
		obj = $(obj).parent().parent().parent();
	}
	$(obj).parent('div').removeClass('error').children('span.errormsg').html('').hide();
}

//función para contar el total de mayúsculas del string obtenido
function countUpperCase(string) {
	var long = string.length;
	strin = string.replace(/([A-Z])+/g, '').length; 
	strin2 = string.replace(/([a-zA-Z])+/g, '').length;
	total = (long  - strin) / (long - strin2) * 100;
	return total;
}

var confirm = true;
window.onbeforeunload = function(){
	if(confirm && ($('input[name=titulo]').val() || textcuerpo)){
		return "Este post no fue publicado y se perdera.";
	}
}

//función para submitear
function postSave() {
	confirm = false;
	document.forms.newpost.submit();
}

//función para guardar el post en borradores
function save_borrador(textcuerpo){
	//obtenemos los datos del formulario
	var params = 'titulo=' + encodeURIComponent($('input[name="titulo"]').val());
	params += '&cuerpo='+encodeURIComponent(textcuerpo);
	params += '&tags=' + encodeURIComponent($('input[name="tags"]').val());
	params += '&categoria=' + encodeURIComponent($('select[name="categoria"]').val());
	params += $('input[name="privado"]').is(':checked') ? '&privado=1' : '';
	params += $('input[name="sin_comentarios"]').is(':checked') ? '&sin_comentarios=1' : '';
	params += $('input[name="patrocinado"]').is(':checked') ? '&patrocinado=1' : '';
	params += $('input[name="sticky"]').is(':checked') ? '&sticky=1' : '';
	$('div#borrador-guardado').html('Guardando...');
	//deshabilitamos el botón hasta terminar la operación
	borrador_save_disabled();
	
	//realizamosla petición ajax para guardar el borrador
	if($('input[name="borrador_id"]').val() != ''){
		$.ajax({
			type: 'POST',
			url: global_data.url + '/borradores-guardar.php',
			data: params + '&borrador_id=' + encodeURIComponent($('input[name="borrador_id"]').val()),
			success: function(valor){
				alert(valor+' valor');
				switch(valor.charAt(1)){
					case '0': //si ha ocurrido algún error
						clearTimeout(borrador_setTimeout);
						borrador_setTimeout = setTimeout('borrador_save_enabled()', 5000);
						borrador_ult = valor.substring(4);
						$('div#borrador-guardado').html(borrador_ult);
						break;
					case '1': //si ha salido todo bien
						var now = new Date();
						borrador_ult = 'Guardado a las ' + now.getHours() + ':' + now.getMinutes() + ':' + now.getSeconds() + ' hs.';
						$('div#borrador-guardado').html(borrador_ult);
				}
			},
			error: function(){
				myActions.error('save_borrador(textcuerpo)');
			},
			complete: function(){
				//si se ha completado activamos de nuevo el botón
				borrador_save_enabled();
			}
		});
	}else{alert('hola2');
		//si no existe ya un borrador de ese post lo agregamos como nuevo
		$.ajax({
			type: 'POST',
			url: global_data.url + '/borradores-agregar.php',
			data: params,
			success: function(valor){alert(valor);
				switch(valor.charAt(1)){
					case '0': //Error
						clearTimeout(borrador_setTimeout);
						borrador_setTimeout = setTimeout('borrador_save_enabled()', 2000);
						borrador_ult = valor.substring(4);
						$('div#borrador-guardado').html(borrador_ult);
						break;
					case '1': //Creado
						$('input[name="borrador_id"]').val(valor.substring(4));
						var now = new Date();
						borrador_ult = 'Borrador guardado a las ' + now.getHours() + ':' + now.getMinutes() + ':' + now.getSeconds() + ' hs.';
						$('div#borrador-guardado').html(borrador_ult);
						borrador_setTimeout = setTimeout('borrador_save_enabled()', 2000);
						break;
				}
			},
			error: function(){
				myActions.error('save_borrador(textcuerpo)');
			}
		});
	}
}

//variable tags guardará un valor booleano si los tag son correctos o no
var tags = false;
//cargaremos las funciones al inicio de la carga de la página
$(document).ready(function(){
	//llamamos al editor ckeditor
    
    var editor = CKEDITOR.replace('cuerpo');
	//validamos el título
	$('input[name=titulo]').bind('keyup',function(){
		if($(this).val().length >= 5 && countUpperCase($(this).val()) > 90) {
			showError(this, 'El t&iacute;tulo no deber&iacute;a estar en may&uacute;sculas');
		}else{
			hideError(this);
		}
	});
    //comprobamos si ya existe un post con el mismo título
    $('input[name=titulo]').blur(function(){
        var q = $(this).val();
        //realizamos la petición ajax
		$.ajax({
			type: 'post',
			url: global_data.url + '/posts-generar.php?do=search',
			data: 'q=' + q,
			success: function(valor) {
                $('#repost').html(valor);
			}
		});
    });
    //generamos los tags automáticos
    $('input[name=tags]').click(function(){
        if(tags == true){
        	return true;
        }
        var title = $('input[name=titulo]').val();
        //realizamos la petición ajax para generarlos
		$.ajax({
			type: 'post',
			url: global_data.url + '/posts-generar.php?do=generador',
			data: 'q=' + title,
			success: function(valor){
                $('input[name=tags]').val(valor);
                tags = true;
			}
		});
    });
    $('input[name=borrador_buton_save]').bind('click',function(){
    	this.textcuerpo = editor.getData();
    	save_borrador(this.textcuerpo);
    });
	//obtenemos la vista previa del post
	$('input[name=preview]').bind('click',function(){
		textcuerpo = editor.getData();

		var error = false;
		//comprobamos los campos
		$('.required').each(function(){
			if (!$.trim($(this).val())){
				showError(this, 'Este campo es obligatorio');
				$(this).parent('div').addClass('error');
				error = true;
				return false;
			}
		});
		$('.required2').each(function(){
			if (textcuerpo == ''){
				showError(this, 'Este campo es obligatorio');
				$(this).parent('div').addClass('error');
				error = true;
				return false;
			}
		});
		//comprobamos si hay algún error
		if(error){
			return false;
		}
		//comprobamos la longitud del cuerpo del post
		if (textcuerpo.length > 65000) {
			showError($('textarea[name=cuerpo]').get(0), 'El post es demasiado largo. No debe exceder los 65000 caracteres.');
			return false;
		}
		//comprobamos que el cuerpo no esté vacío

		//obtenemos los tags
		var tags = $('input[name=tags]').val().split(',');
		//comprobamos si ha llegado al mínimo
		if(tags.length < 4) {
			showError($('input[name=tags]').get(0), 'Tienes que ingresar por lo menos 4 tags separados por coma.');
			return false;
		}else{
		    for(var i = 0; i < tags.length; i++){
		        if(tags[i] == '') {
		            showError($('input[name=tags]').get(0), 'Tienes que ingresar por lo menos 4 tags separados por coma.');
		            return false;
		        } else hideError($('input[name=tags]').get(0))
		    }
		}
		myActions.class_aux = 'preview';
		myActions.show(true);
		myActions.title('...');
		myActions.body('Cargando vista previa....<br><br><img src="' + global_data.url + '/themes/default/images/loading_bar.gif">');
        myActions.style();
        
        //realizamos la petición ajax
		$.ajax({
			type: 'post',
			url: global_data.url + '/posts-preview.php',
			data: 'titulo=' + encodeURIComponent($('input[name=titulo]').val()) + '&cuerpo=' + encodeURIComponent(textcuerpo),
			success: function(valor) {
				myActions.body(valor);
				myActions.buttons(true, true, button_title, 'postSave()', true, true, true, 'Cerrar previsualizaci&oacute;n', 'close', true, false);
				myActions.style();
				$('#myActions #buttons .btn.btnOk').removeClass('btnCancel').addClass('btnGreen');
				$.scrollTo(0, 500);
			}
		});
	});
    //mostramos u ocultamos el bloque de consejos de posteo
    $('a.consejos-view-more-button').bind('click',function(){
    		if($('div.consejos-view-more').css('display') == 'none'){
    			$('div.consejos-view-more').show();
    		}else{
    			$('div.consejos-view-more').hide();
    		}
		}
	);
	//ocultamos los errores que ya estén corregidos
	$('.required').bind('keyup change',function(){
		if ($.trim($(this).val())) {
			hideError(this);
		}
	});
});