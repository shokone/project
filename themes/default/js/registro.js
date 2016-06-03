function redireccionar() {
	location.href = global_data.url + '/cuenta/';
}

var registro = {
	//creamos las variables locales
	myAccion: true,
	paso_actual: 1,
	datos: new Array(),
	datos_status: new Array(),
	datos_text: new Array(),
	times: new Array(),
	times_sets: new Array(),
	no_requerido: new Array(),
	errores: new Array(),
	cache: new Array(),
	banned_pass: ['11111', '12345'],
	//tiempo
	time: function(name){
		var element = $('#registroForm #'+name);
		if(($(element).val()) == 0){
			this.show_status(element, 'info', $(element).attr('title'), true);
		}else{
			this.check_campo(element, false, true);
		}
	},
	//modificamos el tiempo
	set_time: function(name){
		if(this.times_sets[name]){
			return false;
		}
		this.times_sets[name] = true;
		this.times[name] = setTimeout("registro.time('"+name+"')", 1000);
	},
	//borramos el tiempo
	clear_time: function(name){
		if(!this.times_sets[name]){
			return false;
		}
		this.times_sets[name] = false;
		clearTimeout(this.times[name]);
	},
	//el elemento obtiene foco
	focus: function(element){
		var name = $(element).attr('name');
		switch(name){
			case 'password':
				$(element).select();
				var element2 = $('#registroForm #password2');
				//this.hide_status($('#registroForm #password2'), 'empty', 'El campo es requerido');
				$(element2).val('');
				break;
		}
		$(element).addClass('selected');
		this.show_status(element, 'info', $(element).attr('title'), true);
	},
	//el elemento pierde el foco
	blur: function(element){
		var name = $(element).attr('name');
		switch(name){
			case 'nick':
			case 'email':
				this.clear_time(name);
				$(element).removeClass('selected');
				this.check_campo(element, false, true);
				break;
			default:
				$(element).removeClass('selected');
				this.check_campo(element, false, true);
				break;
		}
	},
	//mostramos el estado
	show_status: function(element, status_aux, text, no_cache_data){
		var campo = $(element).attr('name');
		var status = (status_aux=='empty') ? 'error' : status_aux;
		//Si es recaptcha, lo busco directamente
		//if(campo == 'recaptcha_response_field'){
		//	element = $('#registroForm .pasoDos .help.recaptcha');
		//}else{ //Paso al siguiente elemento hasta encontrar un .help
			do{
				element = $(element).next();
			}while(!$(element).is('.help'));
		//}
		$(element).removeClass('ok').removeClass('error').removeClass('info').removeClass('loading').addClass(status).show().children().children().html(text);
		if(!no_cache_data){
			this.datos_status[campo] = status_aux;
			this.datos_text[campo] = text;
		}
		return (status == 'ok');
	},
	//ocultamos el estado
	hide_status: function(element, status, text){
		var campo = $(element).attr('name');
		//Si es recaptcha, lo busco directamente
		//if(campo == 'recaptcha_response_field'){
		//	element = $('#registroForm .pasoDos .help.recaptcha');
		//}else{ //Paso al siguiente elemento hasta encontrar un .help
			do{
				element = $(element).next();
			}while(!$(element).is('.help'));
		//}
		$(element).hide();
		this.datos_status[campo] = status;
		this.datos_text[campo] = text;
		return (status == 'ok');
	},
	//comprobamos los campos
	check_campo: function(element, no_empty, force){
		var campo = $(element).attr('name');
		var value = $(element).val();
		switch(campo){
			case 'nick':
				//comprobamos el estado
				if(!force && this.datos[campo] === value){
					if(this.datos_status[campo]=='empty'){
						if(no_empty){
							return this.show_status(element, this.datos_status[campo], this.datos_text[campo]);
						}else{
							return this.hide_status(element, this.datos_status[campo], this.datos_text[campo]);
						}
					}else{
						return this.show_status(element, this.datos_status[campo], this.datos_text[campo]);
					}
				}
				this.datos[campo] = value;
				//comprobamos si está vacío el campo
				if(value.length == 0){
					var status = 'empty';
					var text = 'El campo es requerido';
					if(no_empty){
						return this.show_status(element, status, text);
					}else{
						return this.hide_status(element, status, text);
					}
				}
				//comprobamos si tiene al menos 4 caracteres
				if(value.length < 4){
					return this.show_status(element, 'error', 'Debe tener al menos 4 caracteres');
				}
				//comprobamos si tiene como máximo 15 caracteres
				if(value.length > 15){
					return this.show_status(element, 'error', 'Debe tener como m&aacute;ximo 15 caracteres');
				}
				//comprobamos si sólo hay números
				if(!/[^0-9]/.test(value)){
					return this.show_status(element, 'error', 'No puede contener solo numeros');
				}
				//comprobamos los caracteres válidos
				if(/[^a-zA-Z0-9_]/.test(value)){
					return this.show_status(element, 'error', 'S&oacute;lo puede contener letras, n&oacute;meros y guiones(_)');
				}
				//comprobamos la cache
				var value_lower = value.toLowerCase();
				if(!this.cache[campo]){
					this.cache[campo] = new Array();
					this.cache[campo][value_lower] = new Array();
				}else if(this.cache[campo][value_lower]){
					if(this.cache[campo][value_lower]['status']){
						return registro.show_status(element, 'ok', this.cache[campo][value_lower]['text']);
					}else{
						return registro.show_status(element, 'error', this.cache[campo][value_lower]['text']);
					}
				}
				this.show_status(element, 'loading', 'Comprobando nick...');
                $('#loading').fadeIn(350);
				$.ajax({
					type: 'POST',
					url: global_data.url + '/registro-check-nick.php?t=nombre de usuario',
					data: 'nick='+value,
					success: function(valor){
						registro.cache[campo][value_lower] = new Array();
						registro.cache[campo][value_lower]['text'] = valor.substring(3);
						switch(valor.charAt(1)){
							case '0': //el nick ya está en uso
								registro.cache[campo][value_lower]['status'] = false;
								registro.show_status(element, 'error', valor.substring(3));
								break;
							case '1': //el nick está libre
								registro.cache[campo][value_lower]['status'] = true;
								registro.show_status(element, 'ok', valor.substring(3));
								break;
						}
                        $('#loading').fadeOut(350);
					},
					error: function(){
						registro.show_status(element, 'error', 'Hubo un error al intentar procesar lo solicitado');
						registro.datos[campo] = '';
					}
				});
				break;
			case 'password':
				//comprobamos el estado
				if(!force && this.datos[campo] === value){
					if(this.datos_status[campo]=='empty'){
						if(no_empty){
							return this.show_status(element, this.datos_status[campo], this.datos_text[campo]);
						}else{
							return this.hide_status(element, this.datos_status[campo], this.datos_text[campo]);
						}
					}else{
						return this.show_status(element, this.datos_status[campo], this.datos_text[campo]);
					}
				}
				//guardamos
				this.datos[campo] = value;

				//comprobamos si está vacío
				if(value.length == 0){
					var status = 'empty';
					var text = 'El campo es requerido';
					if(no_empty){
						return this.show_status(element, status, text);
					}else{
						return this.hide_status(element, status, text);
					}
				}
				//comprobamos si el pass está baneado
				if($.inArray(value.toLowerCase(),this.banned_passwords)!=-1){
					return this.show_status(element, 'error', 'Introduce una contrase&ntilde;a m&aacute;s segura');
				}
				//comprobamos si la pass y el nick son iguales
				if(value === this.datos['nick']){
					return this.show_status(element, 'error', 'La contrase&ntilde;a tiene que ser distinta del nick');
				}
				//comprobamos si es inferior a 5 caracteres
				if(value.length < 5){
					return this.show_status(element, 'error', 'No puede tener menos de 5 caracteres');
				}
				//comprobamos si es superior a 25 caracteres
				if(value.length > 25){
					return this.show_status(element, 'error', 'No puede tener m&aacute;s de 25 caracteres');
				}
				//si todo ok mostramos status okey
				return this.show_status(element, 'ok', 'OK');
				break;
			case 'password2':
				//comprobamos si el campo está vacío
				if(value.length == 0){
					var status = 'empty';
					var text = 'El campo es requerido';
					if(no_empty){
						return this.show_status(element, status, text);
					}else{
						return this.hide_status(element, status, text);
					}
				}
				//comprobamos si las pass son iguales en los dos campos
				if(value !== this.datos['password']){
					this.show_status($('#registroForm #password'), 'error', 'Las contrase&ntilde;as deben ser iguales');
					return this.show_status(element, 'error', 'Las contrase&ntilde;as deben ser iguales');
				}
				return this.show_status(element, 'ok', 'OK');
				break;
			case 'email':
				value = value.toLowerCase();
				//comprobamos el estado del campo
				if(!force && this.datos[campo] === value)
					if(this.datos_status[campo]=='empty'){
						if(no_empty){
							return this.show_status(element, this.datos_status[campo], this.datos_text[campo]);
						}else{
							return this.hide_status(element, this.datos_status[campo], this.datos_text[campo]);
						}
					}else{
						return this.show_status(element, this.datos_status[campo], this.datos_text[campo]);
					}

				this.datos[campo] = value;
				//comprobamos que no esté vacío
				if(value.length == 0){
					var status = 'empty';
					var text = 'El campo es requerido';
					if(no_empty){
						return this.show_status(element, status, text);
					}else{
						return this.hide_status(element, status, text);
					}
				}
				//comprobamos la longitud
				if(value.length > 35){
					return this.show_status(element, 'error', 'El email es demasiado largo');
				}

				//comprobamos el email con expresiones regulares
				if(!/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,3})$/.exec(value)){
					return this.show_status(element, 'error', 'El formato es incorrecto');
				}

				//comprobamos la cache
				if(!this.cache[campo]){
					this.cache[campo] = new Array();
					this.cache[campo][value] = new Array();
				}else if(this.cache[campo][value]){
					if(this.cache[campo][value]['status']){
						return registro.show_status(element, 'ok', this.cache[campo][value]['text']);
					}else{
						return registro.show_status(element, 'error', this.cache[campo][value]['text']);
					}
				}
				this.show_status(element, 'loading', 'Comprobando email...');
                $('#loading').fadeIn(250);
				$.ajax({
					type: 'POST',
					url: global_data.url + '/registro-check-email.php?t=email',
					data: 'email='+value,
					success: function(valor){
						registro.cache[campo][value] = new Array();
						registro.cache[campo][value]['text'] = valor.substring(3);
						switch(valor.charAt(1)){
							case '0': //ya se ha registrado el email
								registro.cache[campo][value]['status'] = false;
								registro.show_status(element, 'error', valor.substring(3));
								break;
							case '1': //aún no se ha registrado el email
								registro.cache[campo][value]['status'] = true;
								registro.show_status(element, 'ok', valor.substring(3));
								break;
						}
                        $('#loading').fadeOut(350);
					},
					error: function(){
						registro.show_status(element, 'error', 'Hubo un error al intentar procesar lo solicitado');
						registro.datos[campo] = '';
                        $('#loading').fadeOut(350);
					}
				});
				break;
			case 'dia': case 'mes': case 'anio':
				//obtenemos los datos
				this.datos['dia'] = $('#registroForm #dia').val();
				this.datos['mes'] = $('#registroForm #mes').val();
				this.datos['anio'] = $('#registroForm #anio').val();
				//comprobamos si está vacío
				if(value.length == 0){
					var status = 'empty';
					var text = 'El campo es requerido';
					if(no_empty){
						return this.show_status(element, status, text);
					}else{
						return this.hide_status(element, status, text);
					}
				}

				//Si todo ok comprobamos que no estén vacíos los campos
				if(this.datos['dia'] != 0 && this.datos['mes'] != 0 && this.datos['anio'] != 0){
					//comprobamos la fecha
					if(checkDate(this.datos['mes'], this.datos['dia'], this.datos['anio']) == null){
						return this.show_status(element, 'error', 'La fecha es incorrecta');
					}
					return this.show_status(element, 'ok', 'La fecha es correcta');
				}else{
					var status = 'empty';
					var text = 'El campo es requerido';
					if(no_empty){
						return this.show_status(element, status, text);
					}else{
						return this.hide_status(element, status, text);
					}
				}
				break;
			case 'sexo':
				if(!$('#registroForm #sexo_f').is(':checked') && !$('#registroForm #sexo_m').is(':checked')){
					value = '';
				}else if($('#registroForm #sexo_f').is(':checked')){
					value = 'f';
				}else{
					value = 'm';
				}
				//comprobamos el estado del campo
				if(this.datos[campo] === value){
					if(this.datos_status[campo]=='empty'){
						if(no_empty){
							return this.show_status(element, this.datos_status[campo], this.datos_text[campo]);
						}else{
							return this.hide_status(element, this.datos_status[campo], this.datos_text[campo]);
						}
					}else{
						return this.show_status(element, this.datos_status[campo], this.datos_text[campo]);
					}
				}
				this.datos[campo] = value;
				//comprobamos si está vacío
				if(value.length == 0){
					var status = 'empty';
					var text = 'El campo es requerido';
					if(no_empty){
						return this.show_status(element, status, text);
					}else{
						return this.hide_status(element, status, text);
					}
				}

				return this.show_status(element, 'ok', 'OK');
				break;
			case 'pais':
				//comprobamos el estado del campo
				if(!force && this.datos[campo] === value){
					if(this.datos_status[campo]=='empty'){
						if(no_empty){
							return this.show_status(element, this.datos_status[campo], this.datos_text[campo]);
						}else{
							return this.hide_status(element, this.datos_status[campo], this.datos_text[campo]);
						}
					}else{
						return this.show_status(element, this.datos_status[campo], this.datos_text[campo]);
					}
				}
				//guardamos los datos
				this.datos[campo] = value;
				this.datos['estado'] = '';
				//this.hide_status($('#registroForm #estado'), 'empty', 'OK');
				//$('#registroForm #estado').attr('disabled', 'disabled').val('');
				//comprobamos si está vacío
				if(value.length == 0){
					var status = 'empty';
					var text = 'El campo es requerido';
					if(no_empty){
						return this.show_status(element, status, text);
					}else{
						return this.hide_status(element, status, text);
					}
				}
				this.show_status(element, 'ok', 'OK');
				$('#registroForm .pasoDos #estado').html('').append('<option value="" selected="selected">Regi&oacute;n</option>');
				//Compruebo si ya esta en uso
				//this.show_status(($('#registroForm .pasoDos #estado')), 'loading', 'Obteniendo...');
				//
                $('#loading').fadeIn(350);
				$.ajax({
					type: 'GET',
					url: global_data.url + '/registro-geo.php',
					data: 'pais_code=' + value,
					success: function(valor){
						switch(valor.charAt(1)){
							case '0': //si ha ocurrido un error
								registro.show_status(element, 'error', valor.substring(4));
								break;
							case '1': //si los datos se han obtenido
								registro.no_requerido['estado'] = false;
								registro.show_status(element, 'ok', 'OK');
								$('#registroForm .pasoDos #estado').append(valor.substring(4));
								break;
						}
                        $('#loading').fadeOut(350);
					},
					error: function(){
						registro.show_status(element, 'error', 'Ocurri&oacute; un error al intentar procesar lo solicitado');
						registro.datos[campo] = '';
                        $('#loading').fadeOut(350);
					}
				});
				break;
			case 'estado':
				if(this.no_requerido[campo]){
					this.hide_status(element, this.datos_status[campo], this.datos_text[campo]);
					return true;
				}
				//comprobamos el estado del campo
				if(!force && this.datos[campo] === value){
					if(this.datos_status[campo]=='empty'){
						if(no_empty){
							return this.show_status(element, this.datos_status[campo], this.datos_text[campo]);
						}else{
							return this.hide_status(element, this.datos_status[campo], this.datos_text[campo]);
						}
					}else{
						return this.show_status(element, this.datos_status[campo], this.datos_text[campo]);
					}
				}
				this.datos['estado'] = value;
				if(value.length == 0){
					var status = 'empty';
					var text = 'El campo es requerido';
					if(no_empty){
						return this.show_status(element, status, text);
					}else{
						return this.hide_status(element, status, text);
					}
				}
				
				this.show_status(element, 'ok', 'OK');
				break;
			case 'terminos':
				var value = $(element).is(':checked');
				if(!force && this.datos[campo] === value){
					if(this.datos_status[campo]=='empty'){
						if(no_empty){
							return this.show_status(element, this.datos_status[campo], this.datos_text[campo]);
						}else{
							return this.hide_status(element, this.datos_status[campo], this.datos_text[campo]);
						}
					}else{
						return this.show_status(element, this.datos_status[campo], this.datos_text[campo]);
					}
				}
				this.datos[campo] = value;
				if(!value){
					var status = 'empty';
					var text = 'El campo es requerido';
					if(no_empty){
						return this.show_status(element, status, text);
					}else{
						return this.hide_status(element, status, text);
					}
				}

				return registro.show_status(element, 'ok', 'OK');
				break;
		}
	},
	//comprobamos el paso actual
	check_paso: function(){
		switch(this.paso_actual){
			case 1:
				var ok = true;
				//Ejecuto comprobacion de cada input dentro del paso
				var inputs = $('#registroForm .pasoUno :input');
				inputs.each(function(){
					if(!registro.check_campo(this, true)){
						ok = false;
					}
				});
				return ok;
				break;
			case 2:
				var ok = true;
				//Ejecuto comprobacion de cada input dentro del paso
				var inputs = $('#registroForm .pasoDos :input');
				inputs.each(function(){
					if(!registro.check_campo(this, true)){
						ok = false;
					}
				});
				return ok;				
				break;
		}
		return true;
	},
	//cambiamos el paso en el que estamos
	change_paso: function(paso, no_focus){
		//comprobamos si se puede pasar al paso siguiente
		if(paso > this.paso_actual && !this.check_paso()){
			return false;
		}
		switch(paso){
			//mostramos el paso 1
			case 1:
				$('#registroForm .pasoDos').hide();
				$('#registroForm .pasoUno').show();
				if(this.myAccion){
					myActions.buttons(true, true, 'Siguiente &raquo;', "registro.change_paso(2)", true, false, false);
				}else{
					$('.reg-login .registro #buttons #sig').css('display', 'inline-block');
					$('.reg-login .registro #buttons #term').hide();
				}
				if(!no_focus){
					$('#registroForm .pasoUno input:first').focus();
				}
				break;
			//mostramos el paso 2
			case 2:
				$('#registroForm .pasoUno').hide();
				$('#registroForm .pasoDos').show();
				if(this.myAccion){
					myActions.buttons(true, true, 'Terminar', 'registro.submit()', true, false, false);
					$('#myActions #buttons .btn.btnOk').removeClass('btnCancel').addClass('btnGreen');
				}else{
					$('.reg-login .registro #buttons #sig').hide();
					$('.reg-login .registro #buttons #term').css('display', 'inline-block');
				}
				if(!no_focus){
					$('#registroForm .pasoDos input:first').focus();
				}
				break
		}
		//Si es mi action, le damos estilo
		if(this.myAccion){
			myActions.style();
		}
		//Registro el paso actual
		this.paso_actual = paso;
	},
	//envíamos los datos y realizamos el registro
	submit: function(){
		//comprobamos los datos del paso 2
		if(!this.check_paso()){
			return false;
		}
		//ocultamos los mensajes de información
		$('#registroForm .help').hide();

		var parametros = '';//parametros necesarios
		var amp = '';//ampersand
		for(var campo in this.datos){
			parametros += amp + campo + '=' + encodeURIComponent(this.datos[campo]);
			amp = '&';
		}
		if(this.myAccion){
			myActions.inicio_cargando('Enviando...', 'Registro');
		}
        
        $('#loading').fadeIn(500);
		$.ajax({
			type: 'POST',
			url: global_data.url + '/registro-nuevo.php',
			data: parametros,
			success: function(valor){
				//Si hubo algun error, recargo recaptcha
				//var rnum = valor.substring(0, valor.indexOf(':'));
				/*if(rnum != '1' || rnum != '2'){
					registro.datos['recaptcha_response_field'] = '';
					Recaptcha.reload('t');
				}*/
				switch(valor.substring(0, valor.indexOf(':'))){
					case '0': //Error generico
						break;
					case 'nick': //Error nick
						registro.change_paso(1, true);
						registro.show_status($('#registroForm #nick'), 'error', valor.substring(valor.indexOf(':')+2));
						break;
					case 'password': //Password
						registro.change_paso(1, true);
						registro.show_status($('#registroForm #password'), 'error', valor.substring(valor.indexOf(':')+2));
						registro.datos['password'] = '';
						break;
					case 'email': //Email
						registro.change_paso(1, true);
						registro.show_status($('#registroForm #email'), 'error', valor.substring(valor.indexOf(':')+2));
						break;
					case 'nacimiento': //Dia|Mes|Año
						registro.change_paso(1, true);
						registro.show_status($('#registroForm #anio'), 'error', valor.substring(valor.indexOf(':')+2));
						break;
					case 'sexo': //Sexo
						registro.change_paso(2, true);
						registro.show_status($('#registroForm #sexo_f'), 'error', valor.substring(valor.indexOf(':')+2));
						break;
					case 'pais': //Pais
						registro.change_paso(2, true);
						registro.show_status($('#registroForm #pais'), 'error', valor.substring(valor.indexOf(':')+2));
						break;
					case 'estado': //Estado
						registro.change_paso(2, true);
						registro.show_status($('#registroForm #estado'), 'error', valor.substring(valor.indexOf(':')+2));
						break;
					//case 'recaptcha': //reCAPTCHA
					//	registro.change_paso(2, true);
					//	registro.show_status($('#registroForm #recaptcha_response_field'), 'error', valor.substring(valor.indexOf(':')+2));
					//	break;
					case '1':
						if(registro.myAccion){
							myActions.body(valor.substring(valor.indexOf(':')+2));
							myActions.buttons(true, true, 'Aceptar', 'myActions.close()', true, true);
							myActions.style();
							location.reload();
						}else{
							$('.reg-login .registro #registroForm').html(valor.substring(valor.indexOf(':')+2));
							$('.reg-login .registro #buttons').remove();
						}
						break;
					case '2':
						if(registro.myAccion){
							myActions.body(valor.substring(valor.indexOf(':')+2));
							myActions.buttons(true, true, 'Aceptar', 'redireccionar()', true, true);
							myActions.style();
						}else{
							$('.reg-login .registro #registroForm').html(valor.substring(valor.indexOf(':')+2));
							$('.reg-login .registro #buttons').remove();
						}
						break;
				}
                $('#loading').fadeOut(350);
			},
			error: function(){
				myActions.error("registro.submit()");
                $('#loading').fadeOut(350);

			},
			complete: function(){
				if(registro.myAccion){
					myActions.final_cargando();
					myActions.default();
				}
                $('#loading').fadeOut(450);
                location.reload();
			},
		});
	}
}

function checkDate(month, day, year){
  var d = new Date(year, month, day);
  return (d.getFullYear() == year && d.getMonth() == month && d.getDate() == day);
}