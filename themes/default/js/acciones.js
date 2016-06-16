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
			switch(valor.charAt(1)){
				case '0': //comprobamos el nick
					$(usuario['error']).html(valor.substring(3)).show();
					$(usuario['nick']).focus();
					$(usuario['button']).removeAttr('disabled').removeClass('disabled');
					break;
				case '1'://comprobamos a donde debemos redireccionar
					if(form != 'registro-logueo'){
						close_login();
					}
					if(valor.substr(4) == 'Home'){
						location.href = global_data.url+'/';
					}else if(valor.substr(4) == 'Cuenta'){
						location.href = global_data.url+'/cuenta/';
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
					myActions.default_button = true,
					myActions.show(true);
					myActions.title('Registrar');
					myActions.body('<br />', 350);
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
			$('#myActions').css('display', 'block');
			$('#myActions').html('<div id="myAction"><div id="title"></div><div id="cuerpo"><div id="procesando"><div id="mensaje"></div></div><div id="myActionsBody"></div><div id="buttons"></div></div></div>');
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

	},
	title: function(title){//titulo
		$('#myActions #title').html(title);
	},
	style: function(){//damos tamaños a la caja que contiene el mensaje
		if($('#myActions #myAction').width() < '400px'){
			$('#myActions #myAction').css('position', 'absolute');
			$('#myActions #myAction').css('padding', '10px');
			$('#myActions #myAction').css('z-index', '5');
			$('#myActions #myAction').css('top', $(window).height()/2);
			$('#myActions #myAction').css('left', $(window).width()/2);
			$('#myActions #myAction').css('background-color', '#fff');
			$('#myActions #myAction').css('boxBorder', '(3px, #009900, solid)');
			$('#myActions #myAction').css('boxRadius', '(10px, 10px, 10px, 10px)');
		}else{
			$('#myActions #myAction').css('width', '100%');
			$('#myActions #myAction').css('top', '0');
			$('#myActions #myAction').css('left', '0');
		}
	},
	buttons: function(display, display1, val1, action1, enabled1, focus1, display2, val2, action2, enabled2, focus2){
		if(!display){
			$('#myActions #buttons').css('display', 'none').html('');
			return;
		}
		if(action1 == 'close'){
			action1 = 'myActions.default()';
		}
		if(action2 == 'close' || !val2){
			action2 = 'myActions.default()';
		}
		if(!val2){
			val2 = 'Cancelar';
			enabled2 = true;
		}

		var html = '';
		if(display1){
			html += '<input type="button" class="btn btn-success'+(enabled1?'':' disabled')+'" style="display:'+(display1?'inline-block':'none')+'"'+(display1?' value="'+val1+'"':'')+(display1?' onclick="'+action1+'"':'')+(enabled1?'':' disabled')+' />';
		}
		if(display2){
			html += ' <input type="button" class="btn btn-danger'+(enabled1?'':' disabled')+'" style="display:'+(display2?'inline-block':'none')+'"'+(display2?' value="'+val2+'"':'')+(display2?' onclick="'+action2+'"':'')+(enabled2?'':' disabled')+' />';
		}
		$('#myActions #buttons').html(html).css('display', 'inline-block');

		if(focus1){
			$('#myActions #buttons .btn.btn-success').focus();
		}else if(focus2){
			$('#myActions #buttons .btn.btn-danger').focus();
		}
	},
	buttons_enabled: function(boton1, boton2){//activamos o desactivamos los botones
		if($('#myActions #buttons .btn.btn-success')){
			if(boton1){
				$('#myActions #buttons .btn.btn-success').removeClass('disabled').removeAttr('disabled');
			}else{
				$('#myActions #buttons .btn.btn-success').addClass('disabled').attr('disabled', 'disabled');
			}
		}
		if($('#myActions #buttons .btn.btn-danger')){
			if(boton2){
				$('#myActions #buttons .btn.btn-danger').removeClass('disabled').removeAttr('disabled');
			}else{
				$('#myActions #buttons .btn.btn-danger').addClass('disabled').attr('disabled', 'disabled');
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
	alert: function(title, body, reload){
		this.show();
		this.title(title);
		this.body(body);
		this.buttons(true, true, 'Aceptar', 'myActions.default();' + (reload ? 'location.reload();' : 'close'), true, true, false);
		this.style();
	},
	default: function(){
		//volvemos los datos a su valor por defecto
		this.class_aux = '';
		this.box_close = true;
		this.close_button = false;
		this.is_show = false;
		$('#box').css('display', 'none');
		$('#myActions').css('display', 'none');
		$('#myActions #myAction').fadeOut(250, function(){$(this).remove();});
		this.final_cargando();
	},
	body: function(body, width, height){
		$('#myActions #myAction').width(width?width:'').height(height?height:'');
		$('#myActions #myActionsBody').html(body);
	},
	error: function(reintentar){
		setTimeout(function(){
			myActions.procesando_fin();
			myActions.show();
			myActions.title('Error');
			myActions.body(lang['error_rocesar']);
			myActions.buttons(true, true, 'Reintentar', 'myActions.default();'+reintentar, true, true, true, 'Cancelar', 'close', true, false);
			myActions.style();
		}, 200);
	}
};

/**
 * con esta función obtenemos los datos del usuario para añadir a la url
 */
function globalget(datos, ampersand){
	var data = datos + '=';
	if(!ampersand)
		data = '&'+data;
	switch(datos){
		case 'key':
			if(global_data.user_key != '')
				return data+global_data.user_key;
			break;
		case 'postid':
			if(global_data.postid != '')
				return data+global_data.postid;
			break;
		case 'fotoid':
			if(global_data.fotoid != '')
				return data+global_data.fotoid;
			break;
		case 'temaid':
			if(global_data.temaid != '')
				return data+global_data.temaid;
			break;
	}
	return '';
}

//función al pulsar la tecla intro
function keypress_intro(event){
  tecla = (document.all)?event.keyCode:event.which;
  return (tecla == 13);
}

//aplicar efecto focus
function onfocus_input(obj){
	if($(obj).val()==$(obj).attr('title')){
		$(obj).val('');
		$(obj).removeClass('onblur_effect');
	}
}

//aplicar efecto blur
function onblur_input(obj){
	if($(obj).val()==$(obj).attr('title') || $(obj).val()==''){
		$(obj).val($(obj).attr('title'));
		$(obj).addClass('onblur_effect');
	}
}

/**
 * panel afiliados
 */
var afiliado = {
    vars: Array(),
    nuevo: function(){
        //creamos el formulario
        var form = '';
        form += '<div id="afiliate"><span>Ingresa los datos de tu web para afiliarte.</span></div>'
        form += '<div style="padding:0 35px;" id="AFormInputs">'
        form += '<div class="form-line">'
        form += '<label for="atitle">T&iacute;tulo</label>'
        form += '<input type="text" tabindex="1" name="atitle" id="atitle" maxlength="35"/>'
  		form += '</div>'
        form += '<div class="form-line">'
        form += '<label for="aurl">Direcci&oacute;n</label>'
        form += '<input type="text" tabindex="2" name="aurl" id="aurl" value="http://"/>'
  		form += '</div>'
        form += '<div class="form-line">'
        form += '<label for="aimg">Banner <small>(216x42px)</small></label>'
        form += '<input type="text" tabindex="3" name="aimg" id="aimg" value="http://"/>'
  		form += '</div>'
        form += '<div class="form-line">'
        form += '<label for="atxt">Descripci&oacute;n</label>'
        form += '<textarea tabindex="4" rows="10" name="atxt" id="atxt" style="height:60px; width:295px"></textarea>'
  		form += '</div>'
        form += '</div>'
        //
        myActions.class_aux = 'registro';
        myActions.box_close = false;
        myActions.default_button = true;
		myActions.show(true);
		myActions.title('Nueva Afiliaci&oacute;n');
		myActions.body(form);
		myActions.buttons(true, true, 'Enviar', 'afiliado.enviar(0)', true, true, true, 'Cancelar', 'close', true, false);
		myActions.style();
    },

    enviar: function(){
        var inputs = $('#AFormInputs :input');
        var status = true;
        var parametros = '';
        //
        inputs.each(function(){
            var val = $(this).val();
            // EL CAMPO AID NO ES NECESARIO
            if($(this).attr('name') == 'aID') val = '0';
            // COMPROBAMOS CAMPOS VACIOS
          /*  if((val == '') && status == true) {
                var campo = $(this).parent().find('label');
                $('#AFStatus > span').fadeOut().text('No has completado el campo ' + campo.text()).fadeIn();
                status = false;
            } else*/ if(status == true){
                // JUNTAMOS LOS DATOS
                parametros += $(this).attr('name') + '=' + val + '&';
            }
		});
        //
        if(status == true){
            myActions.inicio_cargando('Enviando...', 'Nueva Afiliaci&oacute;n');
            afiliado.enviando(parametros);
        }
    },
    enviando: function(parametros){
    	//
        $('#loading').fadeIn(250);
    	$.ajax({
    		type: 'POST',
    		url: global_data.url + '/afiliado-nuevo.php',
    		data: parametros,
    		success: function(valor){
    		  myActions.final_cargando();
    		  switch(valor.charAt(0)){
    		      case '0':
                $('#AFStatus > span').fadeOut().text('La URL es incorrecta').fadeIn();
                   // myActions.buttons(true, true, 'Aceptar', 'myActions.default()', true, true);
                  break;
                  case '1':
                    myActions.body(valor.substring(3));
                    myActions.buttons(true, true, 'Aceptar', 'myActions.default()', true, true);
                  break;
                     case '2':
                $('#AFStatus > span').fadeOut().text('Faltan datos').fadeIn();
                   // myActions.buttons(true, true, 'Aceptar', 'myActions.default()', true, true);
                  break;
    		  }
              myActions.style();
              $('#loading').fadeOut(350);
    		}
    	});
    },
    detalles: function(id){
        $('#loading').fadeIn(250);
    	$.ajax({
    		type: 'POST',
    		url: global_data.url + '/afiliado-detalles.php',
    		data: 'ref=' + id,
    		success: function(valor){
    		    myActions.class_aux = '';
        		myActions.show(true);
        		myActions.title('Detalles');
        		myActions.body(valor);
                myActions.buttons(true, true, 'Aceptar', 'myActions.default()', true, true);
                myActions.style();
                $('#loading').fadeOut(350);

    		}
    	});
    }
}

function registro_load_form(data){
	if (typeof data == 'undefined') {
		var data = '';
	}
	myActions.class_aux = 'registro';
	myActions.box_close = false;
	myActions.default_button = true;
	myActions.show(true);
	myActions.title('Nuevo Registro');
	myActions.body('<br /><br />');
	myActions.buttons(false);
	myActions.inicio_cargando('Cargando...', 'Registro');
	myActions.style();
    $('#loading').fadeIn(500);
	$.ajax({
		type: 'POST',
		url: global_data.url + '/registro-form.php?ps=false',
		data: data,
		success: function(valor){
			switch(valor.charAt(1)){
				case '0': //ocurrió un error
					myActions.final_cargando();
					myActions.alert('Error', valor.substring(3));
					break;
				case '1': //el usuario ya es miembro
					myActions.body(valor.substring(3));
					break;
			}
            $('#loading').fadeOut(500);
			myActions.style();
		},
		error: function(){
			myActions.final_cargando();
			myActions.error("registro.load_form("+data+")");
            $('#loading').fadeOut(500);
		}
	});
}

$(document).ready(function(){
	/* función para el botón volver arriba */
	$('.irArriba').click(function(){
		$('body, html').animate({
			scrollTop: '0px'
		}, 300);
	});

	$(window).scroll(function(){
		if( $(this).scrollTop() > 0 ){
			$('.irArriba').slideDown(300);
		} else {
			$('.irArriba').slideUp(300);
		}
	});

});

//función para ocultar un div
function hidediv(id){
  if((document.getElementById) / DOM3){
    document.getElementById(id).style.display = 'none';
  }else{
    if(document.layers){//Netscape
      document.id.display = 'none';
    }else{//IE
      document.all.id.style.display = 'none';
    }
  }
}

//funcion para mostrar un div
function showdiv(id){
  if((document.getElementById) / DOM3){
    document.getElementById(id).style.display = 'block';
  }else{
    if(document.layers){//Netscape
      document.id.display = 'block';
    }else{//IE
      document.all.id.style.display = 'block';
    }
  }
}

/**************************************/
/***************** live ***************/
/**************************************/
var live = {
	update_time: 30000,
    hide_time: 20000,
    status: {'nots': 'ON', 'mps' : 'ON', 'sound' : 'ON'},
    focus: true,
    n_total: 0,
    m_total: 0,
    //iniciar la sesión
    inicializar: function(){
        //notificaciones del usuario
        live.status['nots'] = $.cookie('live_nots')
        if(live.status['nots'] == null) $.cookie('live_nots', 'ON', {expires: 90})
        //mensajes del usuario
        live.status['mps'] = $.cookie('live_mps')
        if(live.status['mps'] == null) $.cookie('live_mps', 'ON', {expires: 90})
        //sonidos
        live.status['sound'] = $.cookie('live_sound')
        if(live.status['sound'] == null) $.cookie('live_sound', 'ON', {expires: 90})
        //si no mostramos nada retornamos
        if(live.status['nots'] == 'OFF' && live.status['mps'] == 'OFF'){
            return true;
        }else{ //actualizamos a los 30 segundos de máximo
            setTimeout(function(){ live.update(); }, live.update_time);
        }
    },
    //cargamos las notificaciones
    print: function(id){
        //cargamos el contenido
        $('#js').html(id);
        //obtenemos el total
        var n_total = parseInt($('#live-stream').attr('ntotal'));
        var m_total = parseInt($('#live-stream').attr('mtotal'));
        live.n_total = live.n_total + n_total;
        live.m_total = live.m_total + m_total;
        var total_notis = live.n_total + live.m_total;
        //comprobamos
        if(total_notis > 0){
            var live_stream_html = $('#live-stream').html();
            //cargamos
            $('#BeeperBox').html(live_stream_html)
            //mostramos los datos
            $('.UIBeeper_Full').fadeIn(1200);
            $('#BeeperBox').slideToggle(1000);
            //cargamos los eventos del raton
            this.mouse_events();
            //comprobamos si estamos viendo la página
            if(live.focus == true){
                //ocultamos las notificaciones
               setTimeout(function(){ live.hide(); }, live.hide_time);
            } else {
                $(document).attr('title', global_data.s_title + ' (' + total_notis + ') - ' + global_data.s_slogan);
                var sound_type = (live.m_total > 0) ? 'newMessage' : 'newAlert';
                if(live.status['sound'] == 'ON'){
                    $('#swf').html('<embed width="1px" height="1px" wmode="transparent" allowscriptaccess="always" quality="high" bgcolor="#ffffff" src="' + global_data.url + '/inc/extra/' + sound_type + '.swf" type="application/x-shockwave-flash">');
                }
                notifica.popup(live.n_total);
                mensaje.popup(live.m_total);
            }
        }
    },
    //actualizamos
    update: function(){
        $('#loading').fadeIn(500);
		$.ajax({
			type: 'POST',
			url: global_data.url + '/live-stream.php',
            data: 'nots=' + live.status['nots'] + '&mps=' + live.status['mps'],
			success: function(h){
                live.print(h);
                $('#loading').fadeOut(500);
			},
			complete: function(){
				setTimeout(function(){ live.update(); }, live.update_time);
                $('#loading').fadeOut(500);
			}
		});
    },
    //ocultamos las notificaciones
    hide: function(){
        var divs = $('.UIBeeper_Full')
        var total = divs.length;
        setTimeout(function() {
            if(total > 0){
                if($(divs[0]).hasClass('UIBeep_Paused') == false) {
                    $(divs[0]).fadeOut().remove();
                    live.hide();
                }
            }
        }, 1000);
    },
    //comprobamos si está desactivado el sonido
    ch_status: function(type){
        live.status[type] = (live.status[type] == 'ON') ? 'OFF' : 'ON';
        $.cookie('live_' + type, live.status[type], {expires: 90});
    },
    //activamos los eventos del ratón
    mouse_events: function(){
        $('.UIBeep').mouseover(function(){
            $(this).addClass('UIBeep_Selected');
            $(this).parent().parent().addClass('UIBeep_Paused');
        }).mouseout(function(){
            $(this).removeClass('UIBeep_Selected');
            $(this).parent().parent().removeClass('UIBeep_Paused')
            live.hide();
        })
    },
}
//cookies
jQuery.cookie = function(name, value, options){
	if (typeof value != 'undefined') {
        options = options || {};
        if (value === null) {
            value = '';
            options.expires = -1;
        }
        var expires = '';
        if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
            var date;
            if (typeof options.expires == 'number') {
                date = new Date();
                date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
            } else {
                date = options.expires;
            }
            expires = '; expires=' + date.toUTCString();
        }
        var path = options.path ? '; path=' + (options.path) : '';
        var domain = options.domain ? '; domain=' + (options.domain) : '';
        var secure = options.secure ? '; secure' : '';
        document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
    } else { // only name given, get cookie
        var cookieValue = null;
        if (document.cookie && document.cookie != '') {
            var cookies = document.cookie.split(';');
            for (var i = 0; i < cookies.length; i++) {
                var cookie = jQuery.trim(cookies[i]);
                // Does this cookie string begin with the name we want?
                if (cookie.substring(0, name.length + 1) == (name + '=')) {
                    cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                    break;
                }
            }
        }
        return cookieValue;
    }
};
// READY
$(document).ready(function(){
    $('.beeper_x').on("click",function(){
        var bid = $(this).attr('bid');
        $('#beep_' + bid).fadeOut().remove();
        return false;
    });
    live.inicializar();
    $(window).focus(function(){
        live.focus = true;
    }).blur(function(){
        live.focus = false;
    })
});

//calculamos la edad
function edad(mes,dia,anio){
	ahora = new Date();
	nace = new Date(anio, mes*1-1, dia);
	years = Math.floor((ahora.getTime() - nace.getTime()) / (365 * 24 * 60 * 60 * 1000));
	return years;
}

//función para las páginas de comentarios en los post
function com_page(pid, page, autor){
	$('#com_gif').show();
    $('#loading').fadeIn(250);
	$.ajax({
		type: 'POST',
		url: global_data.url + '/comentario-ajax.php?page=' + page,
		data: 'postid=' + pid + '&autor=' + autor,
		success: function(valor){
			$('#comentarios').html(valor);
			set_pages(pid, page, autor);
            $('#loading').fadeOut(350);
		}
	});
	return false;
}

//función para establecer las páginas en los comentarios de los post
function set_pages(pid, page, autor){
	var total = parseInt($('#ncomments').text());
    $('#loading').fadeIn(250);
	$.ajax({
		type: 'POST',
		url: global_data.url + '/comentario-pages.php?page=' + page,
		data: 'postid=' + pid + '&autor=' + autor + '&total=' + total,
		success: function(valor){
			$('.paginadorCom').html(valor);
			$('#com_gif').fadeOut();
            $('#loading').fadeOut(350);
		}
	});
}

//función para convertir valores numéricos en string
function my_number_format(numero){
  return Number(numero).toLocaleString();
}

//función para bloquear un usuario
function bloquear(user, bloqueado, lugar, aceptar){
  if(!aceptar && bloqueado){
    myActions.show();
    myActions.title('Bloquear usuario');
    myActions.body('&iquest;Realmente deseas bloquear a este usuario?');
    myActions.buttons(true, true, 'SI', "bloquear('"+user+"', true, '"+lugar+"', true)", true, false, true, 'NO', 'close', true, true);
    myActions.style();
    return;
  }
  if(bloqueado){
    myActions.inicio_cargando('Procesando...', 'Bloquear usuario');
    $('#loading').fadeIn(250);
  }
  $.ajax({
    type: 'POST',
    url: global_data.url + '/bloqueos-cambiar.php',
    data: 'user='+user+(bloqueado ? '&bloquear=1' : '')+globalget('key'),
    success: function(valor){
      myActions.alert('Bloquear Usuarios', valor.substring(3));
      if(valor.charAt(1) == 1){
        switch(lugar){
          case 'perfil':
            if(bloqueado){
              $('#bloquear_cambiar').html('Desbloquear').removeClass('bloquearU').addClass('desbloquearU').attr('href', "javascript:bloquear('"+user+"', false, '"+lugar+"')");
            }else{
              $('#bloquear_cambiar').html('Bloquear').removeClass('desbloquearU').addClass('bloquearU').attr('href', "javascript:bloquear('"+user+"', true, '"+lugar+"')");
            }
            break;
          case 'respuestas': case 'comentarios':
            if(bloqueado){
              $('li.desbloquear_'+user).show();
              $('li.bloquear_'+user).hide();
            }else{
              $('li.bloquear_'+user).show();
              $('li.desbloquear_'+user).hide();
            }
            break;
          case 'mis_bloqueados':
            if(bloqueado){
              $('.bloquear_usuario_'+user).attr('title', 'Desbloquear Usuario').removeClass('bloqueadosU').addClass('desbloqueadosU').html('Desbloquear').attr('href', "javascript:bloquear('"+user+"', false, '"+lugar+"')");
            }else{
              $('.bloquear_usuario_'+user).attr('title', 'Bloquear Usuario').removeClass('desbloqueadosU').addClass('bloqueadosU').html('Bloquear').attr('href', "javascript:bloquear('"+user+"', true, '"+lugar+"')");
            }
            break;
          case 'mensajes':
            if(bloqueado){
              $('#bloquear_cambiar').html('Desbloquear').attr('href', "javascript:bloquear('"+user+"', false, '"+lugar+"')");
            }else{
              $('#bloquear_cambiar').html('Bloquear').attr('href', "javascript:bloquear('"+user+"', true, '"+lugar+"')");
            }
            break;
        }
      }
      $('#loading').fadeOut(350);
    },
    error: function(){
      myActions.error("bloquear('"+user+"', '"+bloqueado+"', '"+lugar+"', true)");
      $('#loading').fadeOut(350);
    },
    complete: function(){
      myActions.final_cargando();
      $('#loading').fadeOut(350);
    }
  });
}

//función para comprobar el estado del muro
function muro_status(mensajeid, userid, borrar){
  $('#loading').fadeIn(250);
  $.ajax({
    type: 'POST',
    url: '/muro-status.php',
    data: 'msgid='+mensajeid + (userid ? '&userid='+userid : '') + globalget('key') + (borrar ? '&borrar=1' : ''),
    success: function(valor){
      switch(valor.charAt(1)){
        case '0': //si ha ocurrido un error
          myActions.alert('Error', valor.substring(4));
          break;
        case '1': //si ha salido todo bien
          myActions.alert('OK', valor.substring(4));
          break;
      }
      $('#loading').fadeOut(350);
    },
    error: function(){
      myActions.error("muro_status('"+mensajeid+"', '"+userid+"', '"+borrar+"')");
      $('#loading').fadeOut(350);
    }
  });
}

//función para añadir mensaje en el muro
function muro_add(uid){
  $('.muro #add #error').hide();
  if($('#muro-mensaje').val()==$('#muro-mensaje').attr('title')){
    $('#muro-mensaje').focus();
    return;
  }
  $('#loading').fadeIn(250);
  $.ajax({
    type: 'POST',
    url: '/muro-agregar.php',
    data: 'userid='+uid+'&mensaje='+encodeURIComponent($('#muro-mensaje').val())+globalget('key'),
    success: function(valor){
      switch(h.charAt(1)){
        case '0': //si ha ocurrido algún error
          $('.muro #add #error').html(valor.substring(4)).show();
          break;
        case '1': //si ha salido todo bien
          myActions.alert('OK', valor.substring(4));
          break;
      }
      $('#loading').fadeOut(350);
    },
    error: function(){
      myActions.error("muro_add('"+userid+"')");
      $('#loading').fadeOut(350);
    }
  });
}

//función para controlar la acción al pulsar el botón flecha abajo en el teclado
document.onkeydown = function(e){
  if(e==null){
    key = event.keyCode
  }else{
    key = e.which;
  }
  //si pulsamos la tecla esc volvemos por defecto
  if(key == 27){
    myActions.default();
  }
};

//en esta variable guardaremos los datos para el tiempo
//control del mismo y mensajes a mostrar
var timelib = {
  current: false,
  iupd: 60,
  timetowords: function (timeto) {
    if (!this.current) return r;
    var r = false;
    var txt = {
      s: {
        year: 'M&aacute;s de 1 a&ntilde;o',
        month: 'M&aacute;s de 1 mes',
        day: 'Ayer',
        hour: 'Hace 1 hora',
        minute: 'Hace 1 minuto',
        second: 'Menos de 1 minuto'
      },
      p: {
        year: 'M&aacute;s de $1 a&ntilde;os',
        month: 'M&aacute;s de $1 meses',
        day: 'Hace $1 d&iacute;as',
        hour: 'Hace $1 horas',
        minute: 'Hace $1 minutos',
        second: 'Menos de 1 minuto'
      }
    };
    var now = this.current - timeto;
    var dat = {year: 31536000, month: 2678400, day: 86400, hour: 3600, minute: 60, second: 1 };
    for (key in dat) {
      if (now >= dat[key]) {
        var con = Math.floor(now / dat[key]);
        if (con == 1){
          r = txt.s[key];
        }else if(con > 1){
          r = txt.p[key].replace('$1', con);
        }else{
          r = 'Hace mucho tiempo';
        }
        break;
      }
    }
    return r ? r : 'Hace unos instantes';
  },
  upd: function () {
    setTimeout(function(){
      if (this.current) {
        timelib.current = timelib.current + timelib.iupd;
        $('span[ps]').each(function(){ $(this).html(timelib.timetowords($(this).attr('ps'))); });
      }
      timelib.upd()
    }, this.iupd * 1000);
  }
}

/***************** comentarios *****************************/

/**
 * @funcionalidad variable que contendrá las principales funciones generales para los comentarios
 * @type {Object}
 */
var comentario = {
	cache: new Array(),
	cargado: false,
	//cargar los comentarios
  	cargar: function(postid, page, autor){
    	$('#com_gif').show();
    	$('div#comentarios').css('opacity', 0.4);
		//comprobamos la cache
		if(typeof comentario.cache['c_' + page] == 'undefined'){
			$('#loading').fadeIn(250);
			//realizamos la petición ajax para cargar los comentarios
			$.ajax({
			type: 'POST',
			url: global_data.url + '/comentario-ajax.php?page=' + page,
			data: 'postid=' + postid + '&autor=' + autor,
			success: function(valor){
			comentario.cache['c_' + page] = valor;
			$('#comentarios').html(valor);
			comentario.set_pages(postid, page, autor);
			$('#loading').fadeOut(350);
			}
			});
    	} else {
			$('#comentarios').html(comentario.cache['c_' + page]);
			$('.paginadorCom').html(comentario.cache['p_' + page]);
			$('#com_gif').hide();
			$('div#comentarios').css('opacity', 1);
    	}
  	},
	//obtenener el total de páginas de comentarios
	set_pages: function(postid, page, autor){
		//obtenemos el total de páginas
		var total = parseInt($('#ncomments').text());
		$('#loading').fadeIn(250);
		//realizamos la petición ajax
		$.ajax({
			type: 'POST',
			url: global_data.url + '/comentario-pages.php?page=' + page,
			data: 'postid=' + postid + '&autor=' + autor + '&total=' + total,
			success: function(valor){
				comentario.cache['p_' + page] = valor;
				$('.paginadorCom').html(valor);
				$('#com_gif').hide();
				$('div#comentarios').css('opacity', 1);
				$('#loading').fadeOut(350);
			}
		});
	},
  //creamos un nuevo comentario
  nuevo: function(mostrar_resp, comentarionum){
    //evitamos el antiflood
    $('#btnsComment').attr({'disabled':'disabled'});
    //obtenemos el textarea
    var textarea = $('#editor_comentarios');
    var text = textarea.val();
    //comprobamos el textarea
    if(text == '' || text == textarea.attr('title')){
      alert('Tu comentario no puede estar vac&iacute;o');
      textarea.focus();
      $('#btnsComment').removeAttr('disabled');
      return;
    }else if(text.length > 1000){
      alert("Tu comentario no puede ser mayor a 1000 caracteres.");
      textarea.focus();
      $('#btnsComment').removeAttr('disabled');
      return;
    }
    $('.miComentario #gif_cargando').show();
    var auser = $('#auser_post').val();
    $('#loading').fadeIn(250);
    //realizamos la petición ajax
    $.ajax({
      type: 'POST',
      url: global_data.url + '/comentario-agregar.php',
      data: 'comentario=' + encodeURIComponent(text) + '&postid=' + globalget('postid') + '&mostrar_resp=' + mostrar_resp + '&auser=' + auser,
      success: function(valor){
        switch(valor.charAt(1)){
          case '0': //si ha ocurrido algún error
            $('.miComentario .error').html(valor.substring(4)).show('slow');
            $('#btnsComment').removeAttr('disabled');
            break;
          case '1': //si ha salido todo bien
            $("#nuevos").slideUp(1);
            $('#preview').remove();
            $('#nuevos').html(valor.substring(4)).slideDown('slow', function () {
              //$('#no-comments').hide('slow');
              //$('.miComentario').html('<div class="emptyData">'+text+'</div>');
            });
            //aumentamos en 1 el total de comentarios
            var ncomments = parseInt($('#ncomments').text());
            $('#ncomments').text(ncomments + 1);
            break;
        }
        $('#loading').fadeOut(400);
        $('.miComentario #gif_cargando').hide();
        myActions.default();
      },
    });
  },
  //obtenemos la vista previa del comentario
  preview: function(id, type){
    var textarea = (type == 'new') ? $('#' + id) : $('#edit-comment-' + id);
    var text = textarea.val();
    var btn_text = (type == 'new') ? 'Enviar comentario' : 'Guardar';
    var btn_fn = (type == 'new') ? "comentario.nuevo('true')" : 'comentario.editar(' + id + ', \'send\')';
    //comprobamos el textarea
    if(text == '' || text == textarea.attr('title')){
      textarea.focus();
      return;
    }else if(text.length > 1000){
      alert("Tu comentario no puede ser mayor a 1000 caracteres.");
      textarea.focus();
      return;
    }
    var auser = $('#auser_post').val();
    $('.miComentario #gif_cargando').show();
    //
    myActions.class_aux = 'preview';
    myActions.show(true);
    myActions.title('Vista previa ');
    myActions.body('Cargando vista previa....<br><br><img src="' + global_data.url + '/themes/default/images/loading_bar.gif">');
    myActions.style();
    //
    $('#loading').fadeIn(250);
    $.ajax({
      type: 'POST',
      url: global_data.url + '/comentario-preview.php?type=' + type,
      data: 'comentario=' + encodeURIComponent(text) + '&auser=' + auser,
      success: function(valor){
        switch(valor.charAt(1)){
          case '0': //si ha ocurrido algún error
            if(type == 'new'){
              $('.miComentario .error').html(valor.substring(4)).show('slow');
            }else{
              $('#edit-error-' + id).css('color','red').html(valor.substring(4));
              myActions.default();
            }
            $('.miComentario #gif_cargando').hide();
            break;
          case '1': //si ha salido todo bien
            myActions.body(text);
            myActions.buttons(true, true, btn_text, btn_fn, true, true, true, 'Cancelar', 'close', true, false);
            myActions.style();
            $('.miComentario #gif_cargando').hide();
            $('.miComentario .error').html('');
            break;
        }
        $('#loading').fadeOut(350);
        myActions.style();
      }
    });
  },
  //votamos el comentario seleccionado
  votar: function(cid, voto){
    var voto_com = $('#votos_total_' + cid);
    var total_votos = parseInt(voto_com.text());
    total_votos = (isNaN(total_votos)) ? 0 : total_votos;
    voto = (voto == 1) ? 1 : -1;
    $('#loading').fadeIn(250);
    //realizamos la petición ajax
    $.ajax({
      type: 'POST',
      url: global_data.url + '/comentario-votar.php',
      data: 'voto=' + voto + '&cid=' + cid + '&postid=' + globalget('postid'),
      success: function(valor){
        switch(valor.charAt(1)){
          case '0': //si ha ocurrido algún error
            myActions.alert("Error al votar",valor.substring(4));
            break;
          case '1': //si ha salido todo bien
            total_votos = total_votos + voto;
            if(total_votos > 0){
              total_votos = '+' + total_votos;
            }
            var resultado = (total_votos < 0) ? 'negativo' : 'positivo'; // CLASS
            //mostramos si es visible o no y añadimos la clase
            $('#ul_cmt_' + cid + ' > .numbersvotes').show();
            voto_com.text(total_votos).removeClass('positivo, negativo').addClass(resultado);
            $('#ul_cmt_' + cid).find('.icon-thumb-up, .icon-thumb-down').hide();
            break;
        }
        $('#loading').fadeOut(350);
      }
    });
  },
  //citar el comentario de otro usuario
  citar: function(id, nick){
    var textarea = $('#editor_comentarios');
    textarea.focus();
    textarea.val(((textarea.val()!='') ? textarea.val() + '\n' : '') + nick + ':\n' + $('#citar_comm_'+id).html() + '\n');
  },
  //editamos el comentario seleccionado (solo si es nuestro o soy moderador o administrador)
  editar: function(id, step){
    switch(step){
      case 'show'://mostrar los datos
        var data_coment = $('#citar_comm_'+id).html();
        var html = '<textarea id="edit-comment-' + id + '" class="textarea-edit" title="Escribir un comentario..." onfocus="onfocus_input(this)" onblur="onblur_input(this)">' + data_coment + '</textarea><br/><input type="button" class="btn btn-success btnEdit" onclick="comentario.preview(\'' + id + '\', \'edit\')" value="Continuar &raquo;"/> <strong id="edit-error-' + id + '"></strong>';
        $('#comment-body-' + id).html(html);
        $('#edit-comment-' + id).css('max-height', '300px');
        break;
      case 'send'://enviar los datos actualizados
        var cid = $('#edit-cid-' + id).val();
        var comment = $('#edit-comment-' + id).val();
        $('#loading').fadeIn(250);
        $.ajax({
          type: 'POST',
          url: global_data.url + '/comentario-editar.php',
          data: 'comentario=' + encodeURIComponent(comment) + '&cid=' + id,
          success: function(valor){
            switch(valor.charAt(1)){
              case '0': //si ha ocurrido algún error
                $('#edit-error-' + id).css('color','red').html(valor.substring(4));
                break;
              case '1': //si ha salido todo bien
                $('#comment-body-' + id).html($('#new-com-html').html());
                var data_coment = $('#new-com').html();
                $('#citar_comm_'+id).html(data_coment);
                break;
            }
            $('#loading').fadeOut(350);
            myActions.default();
          }
        });
        location.reload();
        break;
    }
  }
}

/**
 * @funcionalidad función para citar el comentario de otro usuario
 * @param  {[type]} id   id del usuario
 * @param  {[type]} nick nick del usuario
 */
function citar_comment(id, nick){
  var textarea = $('#editor_comentarios');
  textarea.focus();
  textarea.val(((textarea.val()!='') ? textarea.val() + '\n' : '') + nick + ':\n' + $('#citar_comm_'+id).html() + '\n');
}

/**
 * @funcionalidad función para actualizar por ajax el listado de comentarios
 * @param  {[type]} cat categoria
 * @param  {[type]} nov nuevos
 */
function actualizar_comentarios(cat, nov){
  $('#loading').fadeIn(250);
  $('#ult_comm, #ult_comm > ol').slideUp(150);
  $.ajax({
    type: 'GET',
    url: global_data.url + '/posts-last-comentarios.php',
    cache: false,
    data: 'cat='+cat+'&nov='+nov,
    success: function(valor){
      $('#ult_comm').html(valor);
      $('#ult_comm > ol').hide();
      $('#ult_comm, #ult_comm > ol:first').slideDown( 500, 'easeInOutElastic');
      $('#loading').fadeOut(350);
    },
    error: function(){
      $('#ult_comm, #ult_comm > ol:first').slideDown({duration: 500, easing: 'easeOutBounce'});
      $('#loading').fadeOut(350);
    }
  });
}

/**
 * @funcionalidad función para ocultar un comentario
 * @param  {[type]} comid  id del comentario
 * @param  {[type]} autor  autor del comentario
 * @param  {[type]} postid id del post en el que se encuentra el comentario
 * @return {[type]}        devolvemos el resultado de la petición ajax
 */
function ocultar_com(comid, autor, postid){
  myActions.default();
  $('#loading').fadeIn(250);
  $.ajax({
    type: 'POST',
    url: global_data.url +'/comentario-ocultar.php',
    data: 'comid=' + comid + '&autor=' + autor + '&post_id=' + postid + globalget('postid'),
    success: function(valor){
      switch(valor.charAt(1)){
        case '0': //si ha ocurrido algún error
          myActions.alert('Error', valor.substring(4));
          break;
        case '1'://si ha salido todo bien mostramos
          $('#comentario_' +comid).css('opacity', 1);
          $('#pp_' +comid).css('opacity', 0.5);
          break;
        case '2'://si ha salido todo bien ocultamos
          $('#comentario_' +comid).css('opacity', 0.5);
          $('#pp_' +comid).css('opacity', 1);
          break;
      }
      $('#loading').fadeOut(350);
    },
    error: function(){
      myActions.error("borrar_com('"+comid+"')");
    }
  });
}

/**
 * @funcionalidad función para borrar un comentario
 * @param  {[type]} comid  [description]
 * @param  {[type]} autor  [description]
 * @param  {[type]} postid [description]
 * @param  {[type]} gew    [description]
 * @return {[type]}        [description]
 */
function borrar_com(comid, autor, postid, gew){
  myActions.default();
  if(!postid){ 
  	var postid = globalget('postid');
  }
  if(!gew){
    myActions.show();
    myActions.title('Borrar Comentarios');
    myActions.body('&#191;Est&aacute;s seguro de querer eliminar este comentario?');
    myActions.buttons(true, true, 'S&iacute;', 'borrar_com(' + comid + ', ' + autor + ', ' + postid + ', 1)', true, false, true, 'No', 'close', true, true);
    myActions.style();
  }else{
    $('#loading').fadeIn(250);
    $.ajax({
      type: 'POST',
      url: global_data.url +'/comentario-borrar.php',
      data: 'comid=' + comid + '&autor=' + autor + '&postid=' + postid,
      success: function(valor){
        switch(valor.charAt(1)){
          case '0': //si ha ocurrido algún error
            myActions.alert('Error', valor.substring(4));
            break;
          case '1'://si ha salido todo bien
            var ncomments = parseInt($('#ncomments').text());
            $('#ncomments').text(ncomments - 1);
            $('#div_cmnt_'+comid).slideUp(500).remove();
            $('#loading').fadeOut(400);
            break;
        }
      },
      error: function(){
        myActions.error("borrar_com('"+comid+"')");
        $('#loading').fadeOut(350);
      }
    });
  }
}

/****************** post ************************/

//variable para comprobar si un post ha sido votado
var votar_post_votado = false;
/**
 * @funcionalidad función para mostrar si el post ha sido votado
 * @param  {[type]} force forzar (valor booleano)
 * @return {[type]}            [description]
 */
function show_votar_post(force){
  if(votar_post_votado){
    return;
  }
  if(!force && $('.post-metadata .dar_puntos').css('display') == 'none'){
    $('.post-metadata .dar_puntos').show();
  }else{
    $('.post-metadata .dar_puntos').hide();
  }
}

/**
 * @funcionalidad función para votar el post realizando una petición ajax
 * @param  {[type]} puntos cantidad de puntos que damos al post
 * @return {[type]}        devolvemos el resultado de la petición ajax
 */
function votar_post(puntos){
  if(votar_post_votado){
    return;
  }
  if(puntos == null || isNaN(puntos) != false || puntos < 1) {
    myActions.alert('Error', 'Debe introducir n&uacute;meros');
    return false;
  }
  votar_post_votado = true;
  $('#loading').fadeIn(250);
  $.ajax({
    type: 'POST',
    url: global_data.url + '/posts-votar.php',
    data: 'puntos=' + puntos + globalget('postid'),
    success: function(valor){
      show_votar_post(true);
      $('.dar-puntos').slideUp();
      switch(valor.charAt(1)){
        case '0': //si ocurrió algún error
          $('.post-metadata .mensajes').addClass('error').html(valor.substring(4)).slideDown();
          break;
        case '1': //si todo ha salido bien
          $('.post-metadata .mensajes').addClass('ok').html(valor.substring(4)).slideDown();
          $('#puntos_post').html(my_number_format(parseInt($('#puntos_post').html().replace(".", "")) + parseInt(puntos), 0, ',', '.'));
          break;
      }
      $('#loading').fadeOut(350);
    },
    error: function(){
      votar_post_votado = false;
      myActions.error("votar_post('"+puntos+"')");
      $('#loading').fadeOut(350);
    }
  });
}

//variable para comprobar si el post ha sido agregado ya a favoritos
var add_favoritos_agregado = false;
/**
 * @funcionalidad añadimos el post a los favoritos del usuario
 */
function add_favoritos(){
  if(add_favoritos_agregado){
    return;
  }
  if(!globalget('key')){
    myActions.alert('Login', 'Tienes que estar logueado para realizar esta operaci&oacute;n');
    return;
  }
  add_favoritos_agregado = true;
  $('#loading').fadeIn(250);
  $.ajax({
    type: 'POST',
    url: global_data.url + '/favoritos-agregar.php',
    data: globalget('postid', true),
    success: function(valor){
      switch(valor.charAt(1)){
        case '0': //si ha ocurrido algún error
          $('.post-metadata .mensajes').addClass('error').html(valor.substring(4)).slideDown();
          break;
        case '1': //si ha salido todo bien
          $('.post-metadata .mensajes').addClass('ok').html(valor.substring(4)).slideDown();
          $('.favoritos_post').html(my_number_format(parseInt($('.favoritos_post').html().replace(".", "")) + 1, 0, ',', '.'));
          break;
      }
      $('#loading').fadeOut(350);
    },
    error: function(){
      add_favoritos_agregado = false;
      myActions.error("add_favoritos()");
      $('#loading').fadeOut(250);
    },
    complete: function(){
    	myActions.alert('Favoritos', 'El post fue agregado a favoritos correctamente');
    	$('#loading').fadeOut(350);
    }
  });
}

/**
 * @funcionalidad borramos el post seleccionado
 * @param  {[type]} aceptar valor booleano para confirmar la acción
 * @return {[type]}         [description]
 */
function borrar_post(aceptar){
  if(!aceptar){
      myActions.show();
      myActions.title('Borrar Post');
      myActions.body('&iquest;Seguro que deseas borrar este post?');
      myActions.buttons(true, true, 'SI', 'borrar_post(1)', true, false, true, 'NO', 'close', true, true);
      myActions.style();
      return;
  }else if(aceptar==1){
      myActions.show();
      myActions.title('Borrar Post');
      myActions.body('Te pregunto de nuevo... &iquest;Est&aacute;s seguro de borrar este post?');
      myActions.buttons(true, true, 'SI', 'borrar_post(2)', true, false, true, 'NO', 'close', true, true);
      myActions.style();
      return;
  }
  myActions.inicio_cargando('Eliminando...', 'Borrar Post');
  $('#loading').fadeIn(250);
  $.ajax({
    type: 'POST',
    url: global_data.url + '/posts-borrar.php',
    data: globalget('postid', true),
    success: function(valor){
      switch(valor.charAt(1)){
        case '0': //si ha ocurrido algún error
          myActions.alert('Error', valor.substring(4));
          break;
        case '1'://si ha salido todo bien
          myActions.alert('Post Borrado', valor.substring(4), true);
          break;
      }
      $('#loading').fadeOut(350);
    },
    error: function(){
      myActions.error("borrar_post(2)");
      $('#loading').fadeOut(350);
    },
    complete: function(){
      myActions.final_cargando();
      $('#loading').fadeOut(350);
    }
  });
}

/**
 * @funcionalidad función para reestablecer la contraseña
 * @param  {[type]} bool valor booleano para comprobar si hemos accedido o no
 * @return {[type]}     [description]
 */
function remind_password(bool){
  close_login_box();
  if(!bool){
    //creamos el formulario
    var form = '';
    form += '<div style="padding:0 35px;" id="AFormInputs">';
    form += '<div class="form-line">';
    form += '<label for="r_email">Correo electr&oacute;nico:</label>';
    form += '<input type="text" tabindex="1" name="r_email" id="r_email" maxlength="35"/>';
    form += '</div>';
    form += '</div>';
    //mostramos el formulario
    myActions.class_aux = 'registro';
    myActions.show(true);
    myActions.title('Recuperar Contrase&ntilde;a');
    myActions.body(form);
    myActions.buttons(true, true, 'Continuar', 'javascript:remind_password(true)', true, true, true, 'Cancelar', 'close', true, false);
    myActions.style();
  }else{
    var r_email = $('#r_email').val();
    $.post(global_data.url + '/recover-pass.php', 'r_email=' + r_email, function(valor){
      myActions.alert((valor.charAt(1) == '0' ? 'Ouch!' : 'Hecho'), valor.substring(4), false);
      myActions.style();
    });
  }
}

/**
 * @funcionalidad función para enviar de nuevo el correo de validación de la cuenta del usuario
 * @param  {[type]} bool valor booleano para comprobar si hemos accedido o no
 * @return {[type]}     [description]
 */
function resend_validation(bool){
  close_login_box();
  if(!bool){
    //creamos el formulario
    var form = '';
    form += '<div style="padding:0 35px;" id="AFormInputs">';
    form += '<div class="form-line">';
    form += '<label for="r_email">Correo electr&oacute;nico:</label>';
    form += '<input type="text" tabindex="1" name="r_email" id="r_email" maxlength="35"/>';
    form += '</div>';
    form += '</div>';
    //y lo mostramos en pantalla
    myActions.class_aux = 'registro';
    myActions.show(true);
    myActions.title('Reenviar validaci&oacute;n');
    myActions.body(form);
    myActions.buttons(true, true, 'Reenviar', 'javascript:resend_validation(true)', true, true, true, 'Cancelar', 'close', true, false);
    myActions.style();
  }else{
    var r_email = $('#r_email').val();
    $('#loading').fadeIn(250);
    $.post(global_data.url + '/recover-validation.php', 'r_email=' + r_email, function(valor){
      myActions.alert((valor.charAt(1) == '0' ? 'Ouch!' : 'Hecho'), valor.substring(4), false);
      myActions.style();
      $('#loading').fadeOut(350);
    });
  }
}

/**
 * @funcionalidad función para el slider de noticias
 */
var news = {
  total: 0,
  count: 1,
  slider: function(){
    if(news.total > 1){
      if(news.count < news.total){
        news.count++;
      }else{
        news.count = 1;
      }
      $('#top_news > li').hide();
      $('#new_' + news.count).fadeIn();
      setTimeout("news.slider()",5000);
    }
  }
}

/**
 * variable notifica
 * @funcionalidad obtendrá las funciones necesarias para la visualización del panel de notificaciones
 * @type {Object}
 */
var notifica = {
  cache: new Array(),
  retry: new Array(),
  //mostrar notificaciones
  show: function(){
    if (typeof notifica.cache.last != 'undefined'){
      $('#alerta_mon').remove();
      $('a[name=Monitor]').parent('li').addClass('monitor-notificaciones');
      $('a[name=Monitor]').children('span').removeClass('spinner');
      $('#monitor_lista').show().children('ul').html(notifica.cache.last);
    }
  },
  //función para controlar las peticiones por ajax de las diferentes notificaciones
  ajax: function (param, val, obj) {
    //comprobamos si ya se ha asignado la clase
    //si es así terminamos
    if ($(obj).hasClass('spinner')){
      return;
    }
    notifica.retry.push(param);
    notifica.retry.push(val);
    var error = param[0] != 'action=count';
    $(obj).addClass('spinner');
    $('#loading').fadeIn(200);
    //realizamos la petición ajax
    $.ajax({
      url: global_data.url + '/notificaciones-ajax.php',
      type: 'post',
      data: param.join('&')+globalget('key'),
      success: function (valor) {
        $(obj).removeClass('spinner');
        val(valor, obj);
        $('#loading').fadeOut(400);
      },
      error: function () {
        if (error){
          myActions.error('notifica.ajax(notifica.retry[0], notifica.retry[1])');
        }
        $('#loading').fadeOut(400);
      }
    });
  },
  //notificación al compartir post
  sharePost: function(id){
    myActions.show();
    myActions.title('Recomendar');
    myActions.body('¿Quieres recomendar este post a tus seguidores?');
    myActions.buttons(true, true, 'Recomendar', 'notifica.spam('+id+', notifica.spamPostHandle)', true, true, true, 'Cancelar', 'close', true, false);
    myActions.style();
  },
  //última notificación
  last: function(){
    var val = parseInt($('#alerta_mon > a > span').html());
    mensaje.close();
    if($('#monitor_lista').css('display') != 'none') {
      $('#monitor_lista').fadeOut();
      $('a[name=Monitor]').parent('li').removeClass('monitor-notificaciones');
    }else {
      if(($('#monitor_lista').css('display') == 'none' && val > 0) || typeof notifica.cache.last == 'undefined'){
        $('a[name=Monitor]').children('span').addClass('spinner');
        $('a[name=Monitor]').parent('li').addClass('monitor-notificaciones');
        $('#monitor_lista').slideDown();
        notifica.ajax(Array('action=last'), function (value) {
          notifica.cache['last'] = value;
          notifica.show();
        });
      }else{
        notifica.show();
      }
    }
  },
  //comprobamos total notificaciones
  check: function(){
    notifica.ajax(Array('action=count'), notifica.popup);
  },
  //abrimos el popup de notificación
  popup: function(valor){
    var val = parseInt($('#alerta_mon > a > span').html());
    if (valor != val && valor > 0) {
      if (valor != 1){
        var total = ' notificaciones';
      }else{
        var total = ' notificaci&oacute;n';
      }
      if (!$('#alerta_mon').length){
        $('div.userInfoLogin > ul > li.monitor').append('<div class="alertas" id="alerta_mon"><a title="' + valor + total + '"><span></span></a></div>');
      }
      $('#alerta_mon > a > span').html(valor);
      $('#alerta_mon').animate({ top: '-=5px'}, 100, null, function(){ $('#alerta_mon').animate({ top: '+=5px' }, 100)});
    }else if (valor == 0){
      $('#alerta_mon').remove();
    }
  },
  //función para desplegar el popup del menu
  userMenuPopup: function (obj) {
    //obtenemos las variables
    var id = $(obj).attr('userid');
    var cache_id = 'following_'+id, list = $(obj).children('ul');
    $(list).children('li.check').slideUp();
    //comprobaos si tenemos que mostrar u ocultar
    if (this.cache[cache_id] == 1) {
      $(list).children('li.follow').slideUp();
      $(list).children('li.unfollow').slideDown();
    }else {
      $(list).children('li.unfollow').slideUp();
      $(list).children('li.follow').slideDown();
    }
  },
  //menú de usuario
  userMenuHandle: function (obj) {
    var val = obj.split('-');
    if (val.length == 3 && val[0] == 0) {
      var cid = 'following_'+val[1];
      notifica.cache[cid] = parseInt(val[0]);
      $('div.avatar-box').children('ul').hide();
    }else if(val.length == 4){
      myActions.alert('Notificaciones', val[4]);
    }
  },
  //post
  inPostHandle: function (value) {
    var val = value.split('-');
    if (val.length == 3 && val[0] == 0) {
      $('button.follow_post, button.unfollow_post').parent('li').toggle();
      $('ul.post-estadisticas > li > span.monitor').html(my_number_format(parseInt(val[2])));
    }else if(val.length == 4){
      myActions.alert('Notificaciones', val[3]);
    }
  },
  //usuario y post
  userInPostHandle: function (value) {
    var val = value.split('-');
    if (val.length == 3 && val[0] == 0) {
      $('a.follow_user_post, a.unfollow_user_post').toggle();
      $('div.metadata-usuario > span.nData.user_follow_count').html(my_number_format(parseInt(val[2])));
      notifica.userMenuHandle(value);
    }else if(val.length == 4){
      myActions.alert('Notificaciones', val[3]);
    }
  },
  //monitor de usuario
  userInMonitorHandle: function (value, obj) {
    var val = value.split('-');
    if (val.length == 3 && val[0] == 0){
      $(obj).fadeOut(function(){
        $(obj).remove();
      });
    }else if(val.length == 4){
      myActions.alert('Notificaciones', val[3]);
    }
  },
  //usuario mencionado
  userInMencionHandle: function(value){
    var val = value.split('-');
    if (val.length == 3 && val[0] == 0) {
      var fid = val[1];
      $('a.mf_' + fid +', a.mf_' + fid).each(function(){
        $(this).toggle();
      });
      $('.mft_' + fid).html(my_number_format(parseInt(val[2])));
    }else if(val.length == 4){
      myActions.alert('Notificaciones', val[3]);
    }
  },
  //usuario en sección admin
  ruserInAdminHandle: function (value) {
    var val = value.split('-');
    if (val.length == 3 && val[0] == 0){
      $('.ruser'+val[1]).toggle();
    }else if(val.length == 4){
      myActions.alert('Notificaciones', val[3]);
    }
  },
  //listas en sección admin
  listInAdminHandle: function (value) {
    var val = value.split('-');
    if (val.length == 3 && val[0] == 0) {
      $('.list'+val[1]).toggle();
      $('.list'+val[1]+':first').parent('div').parent('li').children('div:first').fadeTo(0, $('.list'+val[1]+':first').css('display') == 'none' ? 0.5 : 1);
    }else if(val.length == 4){
      myActions.alert('Notificaciones', val[3]);
    }
  },
  //spameo en la web
  spam: function (id, value) {
    this.ajax(Array('action=spam', 'postid='+id), value);
  },
  //spameo de post
  spamPostHandle: function (value) {
    var val = value.split('-');
    if (val.length == 2){
      myActions.alert('Notificaciones', val[1]);
    }else{
      myActions.default();
    }
  },
  //función para filtrar la actividad
  filter: function (val, obj) {
    $.ajax({
      url: global_data.url + '/notificaciones-filtro.php',
      type: 'post',
      data: 'fid=' + val,
    });
    var v = $(obj).attr('checked') ? 1 : 0;
  },
  //notificación cuando se sigue
  follow: function (type, id, val, obj) {
    this.ajax(new Array('action=follow', 'type='+type, 'obj='+id), val, obj);
  },
  //notificación cuando se ha dejado de seguir
  unfollow: function (type, id, val, obj) {
    this.ajax(new Array('action=unfollow', 'type='+type, 'obj='+id), val, obj);
  },
  //función para cerrar la caja de notificaciones
  close: function(){
    $('#monitor_lista').hide();
    $('a[name=Monitor]').parent('li').removeClass('monitor-notificaciones');
  }
}

/**
 * variable mensaje
 * @funcionalidad obtendrá las funciones necesarias para la visualización del panel de mensajes
 * @type {Object}
 */
var mensaje = {
	cache: new Array(),
	vars: new Array(),
	//mostrar los mensajes
	show: function(){
		if(typeof mensaje.cache.last != 'undefined') {
			$('#alerta_mps').remove();
			$('a[name=Mensajes]').parent('li').addClass('monitor-notificaciones');
			$('a[name=Mensajes]').children('span').removeClass('spinner');
			$('#mensajes_lista').show().children('ul').html(mensaje.cache.last);
		}
	},
	//realizará todas las peticiones por ajax
	ajax: function(action, params, fun){
		$('#loading').fadeIn(200);
		$.ajax({
			type: 'POST',
			url: global_data.url + '/mensajes-' + action + '.php',
			data: params,
			success: function(valor){
				fun(valor);
				$('#loading').fadeOut(400);
				myActions.final_cargando();
			}
		});
	},
	//obtenemos el formulario de mensajes
	form: function (){
		var html = '';
		if(this.vars['error']){
		 	html += '<div class="emptyData">' + this.vars['error'] + '</div><div class="both"></div>';
		}
		html += '<div class="m-col1">Para:</div>';
		html += '<div class="m-col2"><input type="text" value="' + this.vars['to'] + '" maxlength="16" tabindex="0" size="20" id="msg_to" name="msg_to"/>';
		html += '<span>(Ingrese el nombre de usuario)</span></div><div class="both"></div>';
		html += '<div class="m-col1">Asunto:</div>';
		html += '<div class="m-col2"><input type="text" value="' + this.vars['sub'] + '" maxlength="100" tabindex="0" size="50" id="msg_subject" name="msg_subject"/></div><br /><div class="both"></div>';
		html += '<div class="m-col1">Mensaje:</div>';
		html += '<div class="m-col2"><textarea tabindex="0" rows="10" id="msg_body" name="msg_body" style="height:100px; width:350px">' + this.vars['msg'] + '</textarea></div><div class="both"></div>';
		return html;
	},
	//comprobamos el formulario
	checkform: function (valor){
		if(parseInt(valor) == 0){
			mensaje.enviar(1);
		}else if(parseInt(valor) == 1){
			mensaje.nuevo(mensaje.vars['to'], mensaje.vars['sub'], mensaje.vars['msg'], 'No es posible enviarse mensajes a s&iacute; mismo.');
		}else if(parseInt(valor) == 2){
			mensaje.nuevo(mensaje.vars['to'], mensaje.vars['sub'], mensaje.vars['msg'], 'Este usuario no existe. Por favor, verif&iacute;calo.');
		}
	},
	//creamos un nuevo mensaje
	nuevo: function (para, asunto, body, error){
		//guardamos los datos
		this.vars['to'] = para;
		this.vars['sub'] = asunto;
		this.vars['msg'] = body;
		this.vars['error'] = error;
		//creamos la caja para mostrarlos
		myActions.final_cargando();
		myActions.show(true);
		myActions.title('Nuevo mensaje');
		myActions.body(this.form());
		myActions.buttons(true, true, 'Enviar', 'mensaje.enviar(0)', true, true, true, 'Cancelar', 'close', true, false);
		myActions.style();
	},
	//mostramos el mensaje
	mostrar: function(show, obj){
		$('.tabset a').removeClass('here');
		if(show == 'all'){
			$('#mensajes div').show();
			$(obj).addClass('here');
		}else if(show == 'unread'){
			$('#mensajes div.threadRow').hide();
			$('#mensajes table.unread').parent().show();
			$(obj).addClass('here');
		}
	},
	//comprobamos el campo seleccionado
	select: function(act){
		var inputs = $('#mensajes .threadRow :input');
		inputs.each(function(){
		if(act == 'all'){
			$(this).attr({checked: 'checked'});
		}else if(act == 'read'){
			if($(this).attr('class') != 'inread'){
				$(this).attr({checked: 'checked'});
			}else{
				$(this).attr({checked: ''});
			}
		}else if(act == 'unread'){
			if($(this).attr('class') == 'inread'){
				$(this).attr({checked: 'checked'});
			}else{
				$(this).attr({checked: ''});
			}
		}else if(act == 'none'){
			$(this).attr({checked: ''});
		}
		});
	},
	//modificamos el mensaje seleccionado
	modificar: function(act){
		var inputs = $('#mensajes .threadRow :input');
		var ids = new Array();
		var cont = 0;
		inputs.each(function(){
			var este = $(this).attr('checked');
			if(este != false){
				//agregamos el id
				ids[cont] = $(this).val();
				cont++;
				//obtenemos el cid para los estilos
				var cid = $(this).val().split(':');
				//comprobamos
				//marcar como leído
				if(act == 'read'){
					$('#' + cid[0]).removeClass('unread');
					$(this).removeClass('inread');
				}else if(act == 'unread'){
					//marcar como no leído
					$('#' + cid[0]).addClass('unread');
					$(this).addClass('inread');
				}else if(act == 'delete'){
					//eliminar el mensaje
					$('#' + cid[0]).parent().remove();
				}
			}
		});
		//enviamos por ajax los cambios realizados
		if(ids.length > 0){
			var params = ids.join(',');
			mensaje.ajax(
				'editar',
				'ids=' + params + '&act=' + act,
				function(valor){}
			);
		}
	},
	//eliminamos el mensaje seleccionado
	eliminar: function(id,type){
		mensaje.ajax('editar','ids=' + id + '&act=delete',function(valor){
			if(type == 1){
				var cid = id.split(':');
				$('#mp_' + cid[0]).remove();
			}else if(type == 2){
				location.href = global_data.url + '/mensajes/';
			}
		});
	},
	//cambiamos el estado del mensaje de leído a no leído y viceversa
	marcar: function(id, val, type, obj){
		var act = (val == 0) ? 'read' : 'unread';
		var show = (act == 'read') ? 'unread' : 'read';
		mensaje.ajax(
			'editar',
			'ids=' + id + '&act=' + act,
			function(valor){
				//cambiamos si ha sido leído o no
				if(type == 1){
					var cid = id.split(':');
					if(act == 'read'){
						$('#mp_' + cid[0]).removeClass('unread');
					}else{
						$('#mp_' + cid[0]).addClass('unread');
					}
					$(obj).parent().find('a').hide();
					$(obj).parent().find('.' + show).show();
				}else{
					location.href = global_data.url + '/mensajes/';
				}
			}
		);
	},
	//mostramos un alert
	alert: function(valor){
		myActions.final_cargando();
		myActions.alert('Aviso','<div class="emptyData">' + valor + '</div>');
	},
	//enviamos los datos obtenidos
	enviar: function (enviar){
		//obtenemos los datos
		this.vars['to'] = $('#msg_to').val();
		this.vars['sub'] = encodeURIComponent($('#msg_subject').val());
		this.vars['msg'] = encodeURIComponent($('#msg_body').val());
		//comprobamos si todo esta bien
		if(enviar == 0){
			if(this.vars['to'] == ''){
				mensaje.nuevo(mensaje.vars['to'], mensaje.vars['sub'], mensaje.vars['msg'], 'Por favor, especific&aacute; el destinatario.');
			}
			if(this.vars['msg'] == ''){
				mensaje.nuevo(mensaje.vars['to'], mensaje.vars['sub'], mensaje.vars['msg'], 'El mensaje no puede estar vac&iacute;o.');
			}
			myActions.inicio_cargando('Comprobando...', 'Nuevo Mensaje');
			this.ajax('validar', 'para=' + this.vars['to'], mensaje.checkform);
		}else if(enviar == 1){
			myActions.inicio_cargando('Enviando...', 'Nuevo Mensaje');
			this.ajax('enviar',
				'para=' + mensaje.vars['to'] + '&asunto=' + mensaje.vars['sub'] + '&mensaje=' + mensaje.vars['msg'],
				mensaje.alert
			);
		}
	},
	//responder a un mensaje
	responder: function(mp_id){
		//obtenemos los datos del mensaje a responder
		this.vars['mp_id'] = $('#mp_id').val();
		this.vars['mp_body'] = encodeURIComponent($('#respuesta').val());
		//comprobamos el cuerpo
		if(this.vars['mp_body'] == ''){
			$('#respuesta').focus();
			return;
		}
		//enviamos la petición ajax
		this.ajax(
			'respuesta',
			'id=' + this.vars['mp_id'] + '&body=' + this.vars['mp_body'],
			function(valor){
				$('#respuesta').val('');
				switch(valor.charAt(1)){
					case '0'://si ha ocurrido algún error
					myActions.alert("Error", valor.substring(4));
					break;
				case '1'://si ha salido todo bien
					$('#historial').append($(valor.substring(4)).fadeIn('slow'));
					break;
				}
				$('#respuesta').focus();
			}
		);
	},
	//creamos la ventana para desplegar los mensajes
	popup: function (mps) {
		var val = parseInt($('#alerta_mps > a > span').html());
		if (mps != val && mps > 0) {
			if(mps != 1){
				var total = ' mensajes';
			}else{
				var total = ' mensaje';
			}
			if(!$('#alerta_mps').length){
				$('div.userInfoLogin > ul > li.mensajes').append('<div class="alertas" id="alerta_mps"><a title="' + mps + total + '"><span></span></a></div>');
			}
			$('#alerta_mps > a > span').html(mps);
			$('#alerta_mps').animate({
				top: '-=5px'
			}, 100, null, function(){
				$('#alerta_mps').animate({ top: '+=5px' }, 100)
			});
		}else if(mps == 0){
			$('#alerta_mps').remove();
		}
	},
	//obtenemos los últimos mensajes
	last: function () {
		var val = parseInt($('#alerta_mps > a > span').html());
		notifica.close();
		if($('#mensajes_lista').css('display') != 'none'){
			$('#mensajes_lista').hide();
			$('a[name=Mensajes]').parent('li').removeClass('monitor-notificaciones');
		}else{
			if(($('#mensajes_lista').css('display') == 'none' && val > 0) || typeof mensaje.cache.last == 'undefined') {
				$('a[name=Mensajes]').children('span').addClass('spinner');
				$('a[name=Mensajes]').parent('li').addClass('monitor-notificaciones');
				$('#mensajes_lista').show();
				mensaje.ajax('lista', '', function (value) {
					mensaje.cache['last'] = value;
					mensaje.show();
				});
			}else{
				mensaje.show();
			}
		}
	},
	//cerramos la ventana de mensajes
	close: function(){
		$('#mensajes_lista').slideUp();
		$('a[name=Mensajes]').parent('li').removeClass('monitor-notificaciones');
	}
}

/************************ buscador ****************************/

//función para cambiar los datos de búsqueda
function search_set(obj, q) { 
    $('div.search-in > a').removeClass('search_active'); 
    $(obj).addClass('search_active');
    $('input[name="e"]').val(q);  
    //id de google
    var gid = $('form[name=top_search_box]').attr('gid');
    //mostramos u ocultamos los input google
	if(q == 'google'){ 
        //Ahora es google {/literal}
		$('form[name=top_search_box]').append('<input type="hidden" name="cx" value="' + gid + '" /><input type="hidden" name="cof" value="FORID:10" /><input type="hidden" name="ie" value="ISO-8859-1" />');
        // {literal}
	}else { //El anterior fue google
		$('input[name="cx"]').remove();
		$('input[name="cof"]').remove();
		$('input[name="ie"]').remove();
	}
    $('#ibuscadorq').focus();
}

//cargamos diferentes funciones al cargar la página
$(document).ready(function(){
	$('.avatar-box').mouseout(function(){
		$('.lista-avatar-com').css('display', 'none');
	});
	$('.avatar-box').mouseover(function(){
		$('.lista-avatar-com').css('display', 'block');
	});
    var location_box_more = false;
    $('.location-box-more').click(function(){
        if (location_box_more) {
            $('.location-box ul').css('height', '170px');
            $(this).html("Ver más");
            location_box_more = false;
        }
        else {
            $('.location-box ul').css('height', '170%');
            $(this).html("Ver menos");
            location_box_more = true;
        }
    });
	/*$('body').click(function(e){ 
	   if ($('#mon_list').css('display') != 'none' && $(e.target).closest('#mon_list').length == 0 && $(e.target).closest('a[name=Monitor]').length == 0) notifica.last();
       if ($('#mp_list').css('display') != 'none' && $(e.target).closest('#mp_list').length == 0 && $(e.target).closest('a[name=Mensajes]').length == 0) mensaje.last(); 
    });*/
	$('.userInfoLogin a[class!=ver-mas], .comOfi, .post-compartir img, div.action > div.btn_follow > a[title], .dot-online-offline, .qtip');
	/*$('div.avatar-box').live("mouseenter",function(){ 
		$(this).children('ul').show(); }).live("mouseleave",function(){ 
			$(this).children('ul').hide();
		});*/
	var zIndex = 99;
	$('div.avatar-box').each(function(){
		$(this).css('zIndex', zIndex);
		zIndex -= 1;
	});
	$('div.new-search > div.bar-options > ul > li > a').bind('click', function(){
		var at = $(this).parent('li').attr('class').split('-')[0];
		$('div.new-search > div.bar-options > ul > li.active').removeClass('active');
		$(this).parent('li').addClass('active');
		$('div.new-search').attr('class', 'new-search '+at);
        at = (at == 'web') ? 'google' : 'web';
        $('input[name="e"]').val(at);
        //id de google
        var gid = $('form[name="search"]').attr('gid');
        //mostramos u ocultamos los input google
		if(at == 'google'){ 
            //Ahora es google {/literal}
			$('form[name="search"]').append('<input type="hidden" name="cx" value="' + gid + '" /><input type="hidden" name="cof" value="FORID:10" /><input type="hidden" name="ie" value="ISO-8859-1" />');
            $('#search-home-cat-filter, #sh_options').hide();
            // {literal}
		}else { //El anterior fue google
			$('input[name="cx"]').remove();
			$('input[name="cof"]').remove();
			$('input[name="ie"]').remove();
            $('#search-home-cat-filter, #sh_options').css('display','');
		}
	});
	$('div.new-search > div.search-body > form > input[name=q]').bind('focus', function(){
		if ($(this).val() == 'Buscar') { $(this).val(''); }
		$(this).css('color', '#000');
	}).bind('blur', function(){
		if ($.trim($(this).val()) == '') { $(this).val('Buscar'); }
		$(this).css('color', '#999');
	});

});

/**
 * var denuncia
 * contendrá las funciones necesarias para que el usuario pueda realizar una denuncia
 */
var denuncia = {
	//creamos una nueva denuncia
	nueva: function(type, obj_id, obj_title, obj_user){
		$('#loading').fadeIn(250); 
		//realizamos la petición ajax
		$.ajax({
			type: 'POST',
			url: global_data.url + '/denuncia-' + type + '.php',
			data: 'obj_id=' + obj_id + '&obj_title=' + obj_title + '&obj_user=' + obj_user,
			success: function(valor){
				denuncia.set_actions(valor, obj_id, type);
				$('#loading').fadeOut(350);                                 
			}
		});
	},
	//creamos la caja que contendrá los datos para enviar la denuncia
	set_actions: function(html, obj_id, type){
		var title = 'Denunciar ' + type;
		myActions.box_close = false;
		myActions.close_button = true;		                                        
		myActions.show();
		myActions.title(title);
		myActions.body(html);
		myActions.buttons(true, true, 'Enviar', "denuncia.enviar(" + obj_id + ", '" + type + "')", true, true, true, 'Cancelar', 'close', true, false);
		myActions.style();
	},
	//enviamos la denuncia
	enviar: function(obj_id, type){
		var razon = $('select[name=razon]').val();
		var extras = $('textarea[name=extras]').val();
		$('#loading').fadeIn(200);    
		//realizamos la petición ajax                     
		$.ajax({
			type: 'POST',
			url: global_data.url + '/denuncia-' + type + '.php',
			data: 'obj_id=' + obj_id + '&razon=' + razon + '&extras=' + extras,
			success: function(valor){
				switch(valor.charAt(1)){
					case '0'://si ha ocurrido algún error
						myActions.alert("Error",'<div class="emptyData">' + valor.substring(4) +  '</div>');
						break;
					case '1'://si ha salido todo bien
						myActions.alert("Bien", '<div class="emptyData">' + valor.substring(4) + '</div>');
						break;
				}
				$('#loading').fadeOut(400);                                                 
			}
		});
	}
}