var text_data = [];
text_data['posts_url_categorias'] = 'posts';
text_data['error_procesar'] = 'Ocurri&oacute; un error al intentar procesar lo solicitado';
/* funcion para ir a las categorias */
function ir_categoria(cat){
	if(cat != 'root' && cat != 'linea'){
		if(cat == -1){
			document.localtion.href = global_data.url + '/';
		}else if(cat==-2){
            document.location.href = global_data.url + '/' + 'posts/';
		}else{
			document.location.href = global_data.url + '/' + text_data['posts_url_categorias'] + '/' + cat + '/';
		}
	}
}

function usuario(id){
	if(document.getElementById){
		return document.getElementById(id);
	}else if(window[id]){
		return window[id];
	}
	return null;
}

/*******************************************************/
/***************** login *******************************/
/*******************************************************/

function login_ajax(form, connect){
	//creamos las variables necesarias
	var usuario = new Array();
	var parametros = '';
	if (form == 'registro-logueo' || form == 'logueo-form') {
		usuario['nick'] = $('.reg-login .login-panel #nickname');
		usuario['pass'] = $('.reg-login .login-panel #password');
		usuario['error'] = $('.reg-login .login-panel #login_error');
		usuario['cargando'] = $('.reg-login .login-panel #login_cargando');
		usuario['cuerpo'] = $('.reg-login .login-panel .login_cuerpo');
		usuario['button'] = $('.reg-login .login-panel input[type="submit"]');
	} else {
		usuario['nick'] = $('#box_login #nickname');
		usuario['pass'] = $('#box_login #password');
		usuario['error'] = $('#box_login #login_error');
		usuario['cargando'] = $('#box_login #login_cargando');
		usuario['cuerpo'] = $('#box_login .body-login');
		usuario['button'] = $('#box_login input[type="submit"]');
	}
	if(typeof connect != 'undefined'){
	}else{
		//comprobamos si esta relleno el nick
		if ($(usuario['nick']).val() == '') {
			$(usuario['nick']).focus();
			return;
		}
		//comprobamos si esta relleno el pass
		if ($(usuario['pass']).val() == '') {
			$(usuario['pass']).focus();
			return;
		}
		$(usuario['error']).css('display', 'none');
		$(usuario['cargando']).css('display', 'block');
		$(usuario['button']).attr('disabled', 'disabled').addClass('disabled');
		var remember = ($('#rem').is(':checked')) ? 'true' : 'false';
		//codificamos los datos antes de enviarlos
		parametros = 'nick='+encodeURIComponent($(usuario['nick']).val())+'&pass='+encodeURIComponent($(usuario['pass']).val())+'&rem='+remember;
	}
	//mostramos la imagen ajax de cargando
	$('#loading').fadeIn(500);
	//hacemos la petición ajax
	$.ajax({
		type: 'POST', 
		url: global_data.url + '/login-user.php', 
		cache: false, 
		data: parametros,
		success: function(valor){
			alert(valor);
			switch(valor){
				case '0': //comprobamos el nick
					$(usuario['error']).html(valor.substring(3)).show();
					$(usuario['nick']).focus();
					$(usuario['button']).removeAttr('disabled').removeClass('disabled');
					break;
				case '1'://comprobamos a donde debemos redireccionar
					if(form != 'registro-logueo'){
						close_login();
					}
					if(valor.substr(3) == 'Home'){
						location.href = '/';
					}else if(valor.substr(3) == 'Cuenta'){
						location.href = '/cuenta/';
					}else{
						location.reload();
					}
					//escondemos la imagen de ajax
					$('#loading').fadeOut(500);
					break;
				case '2':
					$(usuario['cuerpo']).css('text-align', 'center').css('line-height', '120%').html(valor.substring(3));
					break;
				case '3':
					open_login();
					//acciones
					myActions.class_aux = 'registro';
					myActions.box_close = false;
					myActions.close_button = true,
					myActions.show(true);
					myActions.title('Registrar');
					myActions.buttons(false);
					myActions.inicio_cargando('Cargando...', 'Registro');
					myActions.style();
					//hacemos la petición
					$.ajax({
						type: 'POST',
						url: global_data.url + '/login-form.php',
						data: '',
						success: function(valor){
							myActions.final_cargando();
							switch(valor.charAt(0)){
								case '0': 
									myActions.alert('Error');
									break;
							}
							myActions.style();
						}
					});
					break;
			}
		},
		error: function(){
			$(usuario['error']).html(text_data['error_procesar']).show();
		},	
		complete: function(){
			$(usuario['cargando']).css('display', 'none');
		}
	});
}

/* funcion para abrir la caja de login */
function open_login(action){
	if($('#box_login').css('display') == 'block' && action != 'open'){
		close_login();
	}else{
		$('#login_error').css('display','none');
		$('#login_cargando').css('display','none');
		$('#box_login').fadeIn(500);
		$('#nickname').focus();
	}
}

/* función para cerrar la caja del login */
function close_login(){
	$('#box_login').slideUp(500);
}


/* guardará diferentes acciones a realizar en la página */
var myActions = {
	is_show: false,
	box_close: true, 
	close_button: false,
	class_aux: '',
	show: function(class_aux){
		//comprobamos si mostramos o no
		if(this.is_show){
			return;
		}else{
			this.is_show = true;
		}
		//creamos la caja
		if($('#myActions').html() == ''){
			$('#myActions').html('<div id="myAction"><div id="title"></div><div id="cuerpo"><div id="procesando"><div id="mensaje"></div></div><div id="modal"></div><div id="buttons"></div></div></div>');
		}
		//comprobamos si tiene clase auxiliar
		if(class_aux == true){
			$('#myActions').addClass(this.class_aux);
		}else if(this.class_aux != ''){
			$('#myActions').removeClass(this.class_aux);
			this.class_aux = '';
		}
		//comprobamos si cerramos la caja
		if(this.box_close){
			$('#box').click(function(){ myActions.default() });
		}else{
			$('#box').unbind('click');
		}
		//si esta activado cerramos y volvemos todo por defecto
		if(this.close_button){
			$('#myActions #myAction').append('<img class="close_myAction" src="'+ global_data.img +'images/close.gif" onclick="myActions.default()" />');
		}else{
			$('#myActions #myAction .close_dialog').remove();
		}

		$('#box').css({'width':$(document).width(),'height':$(document).height(),'display':'block'});
		//damos una posición según el navegador
		if(jQuery.browser.msie && jQuery.browser.version < 9){
			$('#myActions #myAction').css('position', 'absolute');
		}else{
			$('#myActions #myAction').css('position', 'fixed');
	        $('#myActions #myAction').fadeIn('fast');
	    }
	},
	title: function(title){//titulo
		$('#myActions #title').html(title);
	},
	style: function(){//damos tamaños a la caja que contiene el mensaje
		if($('#myActions #myAction').height() > $(window).height()-60){
			$('#myActions #myAction').css({'position':'absolute', 'top':20});
		}else{
			$('#myActions #myAction').css('top', $(window).height()/2 - $('#myActions #myAction').height()/2);
		}
		$('#myActions #myAction').css('left', $(window).width()/2 - $('#myActions #myAction').width()/2);
	},
	buttons: function(display, display1, val1, action1, enabled1, focus1, display2, val2, action2, enabled2, focus2){
		if(!display){
			$('#myActions #buttons').css('display', 'none').html('');
			return;
		}
		if(action1 == 'close')
			action1 = 'myActions.default()';
		if(action2 == 'close' || !val2)
			action2 = 'myActions.default()';
		if(!val2){
			val2 = 'Cancelar';
			enabled2 = true;
		}

		var html = '';
		if(display1)
			html += '<input type="button" class="btn btnOk'+(enabled1?'':' disabled')+'" style="display:'+(display1?'inline-block':'none')+'"'+(display1?' value="'+val1+'"':'')+(display1?' onclick="'+action1+'"':'')+(enabled1?'':' disabled')+' />';
		if(display2)
			html += ' <input type="button" class="btn btnCancel'+(enabled1?'':' disabled')+'" style="display:'+(display2?'inline-block':'none')+'"'+(display2?' value="'+val2+'"':'')+(display2?' onclick="'+action2+'"':'')+(enabled2?'':' disabled')+' />';
		$('#myActions #buttons').html(html).css('display', 'inline-block');

		if(btn1_focus)
			$('#myActions #buttons .btn.btnOk').focus();
		else if(btn2_focus)
			$('#myActions #buttons .btn.btnCancel').focus();
	},
	buttons_enabled: function(boton1, boton2){//activamos o desactivamos los botones
		if($('#myActions #buttons .btn.btnOk')){
			if(btn1_enabled){
				$('#myActions #buttons .btn.btnOk').removeClass('disabled').removeAttr('disabled');
			}else{
				$('#myActions #buttons .btn.btnOk').addClass('disabled').attr('disabled', 'disabled');
			}
		}
		if($('#myActions #buttons .btn.btnCancel')){
			if(btn2_enabled){
				$('#myActions #buttons .btn.btnCancel').removeClass('disabled').removeAttr('disabled');
			}else{
				$('#myActions #buttons .btn.btnCancel').addClass('disabled').attr('disabled', 'disabled');
			}
		}
	},
	inicio_cargando: function(valor, title){
		if(!this.is_show){
			this.show();
			this.title(title);
			this.buttons(false, false);
			this.style();
		}
		$('#myActions #procesando #mensaje').html('<img src="'+global_data.img+'images/loading.gif" />');
		$('#myActions #procesando').fadeIn(250);
	},
	final_cargando: function(){
		$('#myActions #procesando').fadeOut(250);
	},
	alert: function(title, reload){
		this.show();
		this.title(title);
		this.buttons(true, true, 'Aceptar', 'myActions.close();' + (reload ? 'location.reload();' : 'close'), true, true, false);
		this.style();
	},
	default: function(){
		//volvemos los datos a su valor por defecto
		this.class_aux = '';
		this.box_close = true;
		this.close_button = false;
		this.is_show = false;
		$('#box').css('display', 'none');
		$('#myActions #myAction').fadeOut(250, function(){$(this).remove();});
		this.final_cargando();
	}
};