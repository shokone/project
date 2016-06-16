/**
 * variable mod (moderación)
 * contendrá todas las funciones necesarias para que un moderador
 * pueda realizar sus funciones en la página web
 * podrá gestionar los post, usuarios, fotos y mensaje privados
 */
var mod = {
    //moderación de posts
    posts : {
        //vemos el post
        view: function(pid){
            $('#loading').fadeIn(200);
            //realizamos la petición ajax
            $.ajax({
                type: 'post',
                url: global_data.url + '/moderacion-posts.php?do=view',
                data: 'postid=' + pid,
                success: function(valor) {
                    myActions.class_aux = 'preview';
                    myActions.show(true);
                    myActions.title('Ver post');
                    myActions.body(valor);
                    myActions.buttons(true, true, 'Cerrar', 'close', true, false);
                    myActions.style();
                    $('#loading').fadeOut(350);
                }
            });
        },
        //ocultamos el post a los usuarios
        ocultar: function(pid){
            var text = $('#d_razon').val();
            if(text.length < 1){
                myActions.error('Debe introducir una raz&oacute;n para ocultar el post');
                text.focus();
                return;
            }else if(text.length > 50){
                myActions.alert('Error', 'La raz&oacute;n no puede tener m&aacute;s de 50 caracteres.');
                text.focus();
                return;
            }else{
                $.post(global_data.url + '/moderacion-posts.php?do=ocultar', 'razon=' + text + '&pid=' + pid, 
                    function(valor){
                        myActions.alert((valor.charAt(1) == '0' ? 'Ouch!' : 'Bien!'), valor.substring(4), true);
                        myActions.style();
                    }
                );
            }
        },
        //borrar post
        borrar:function(pid, redirect, aceptar){
            if(!aceptar){
                $('#loading').fadeIn(250);
                //realizamos la petición ajax
                $.ajax({
                    type: 'POST',
                    url: global_data.url + '/moderacion-posts.php?do=borrar',
                    success: function(valor){
                        myActions.show();
                        myActions.title('Borrar Post');
                        myActions.body(valor);
                        myActions.buttons(true, true, 'Borrar', 'mod.posts.borrar(' + pid + ", '" + redirect + "', 1);", true, false, true, 'Cancelar', 'close', true, true);
                        myActions.center();
                        $('#loading').fadeOut(350);
                        return;	  
                    }
                });
            } else {
                myActions.inicio_cargando('Eliminando...', 'Borrar Post');
                var razon = $('#razon').val();//obtenemos la razón
                var razon_desc = $('input[name=razon_desc]').val();//y la descripción de la razón

                if($('#send_b').attr('checked')){
                    var send_b = 'yes';
                }
                $('#loading').fadeIn(250);
                //realizamos la petición ajax
                $.ajax({
                    type: 'POST',
                    url: global_data.url + '/moderacion-posts.php?do=borrar',
                    data: 'postid=' + pid + '&razon=' + razon + '&razon_desc=' + razon_desc + '&send_b=' + send_b,
                    success: function(valor){
                        switch(valor.charAt(1)){
                            case '0': //si ha ocurrido algún error
                                myActions.alert('Error', valor.substring(4));
                                break;
                            case '1': //si ha salido todo bien
                                if(redirect == 'true'){
                                    mod.redirect("/moderacion/posts", 1000);
                                } else if(redirect == 'posts') {
                                    myActions.alert('Aviso', valor.substring(4));
                                    mod.redirect("/posts/", 2000);
                                } else {
                                    myActions.default();
                                    $('#report_' + pid).slideUp();   
                                }
                                break;
                        }
                        $('#loading').fadeOut(350);
                    },
                    complete: function(){
                        myActions.final_cargando();
                        $('#loading').fadeOut(350);
                    }
                });
            }
        },
        //volvemos al valor por defecto
        reboot: function(id, type, hdo, redirect){
            $('#loading').fadeIn(250);
            //realizamos la petición ajax
            $.ajax({
                type: 'post',
                url: global_data.url + '/moderacion-' + type +'.php?do=' + hdo,
                data: 'id=' + id,
                success: function(valor) {
                    switch(valor.charAt(1)){
                        case '0'://si ha ocurrido algún error
                            myActions.alert("Error", valor.substring(4));
                        break;
                        case '1'://si ha salido todo bien
                            myActions.alert("Aviso", '<div class="action_box">' + valor.substring(4) + '</div>');
                            $('#report_' + id).fadeOut();
                            if(redirect) {
                                if(redirect) {
                                    mod.redirect("/moderacion/" + type, 1200);
                                }
                            }else {
                                $('#report_' + id).slideUp();
                            }
                        break;
                    }
                    $('#loading').fadeOut(350);
                }
            });
        },
    },
    //moderación de usuarios
    users: {
        //acción a realizar
        action: function(uid, action, redirect){
            var btn_txt = (action == 'aviso') ? 'Enviar' : 'Suspender';
            var titulo = (action == 'aviso') ? 'Enviar Aviso/Alerta' : 'Suspender usuario';
            mod.load_action('/moderacion-users.php?do=' + action, 'uid=' + uid, titulo, btn_txt, 'mod.users.set_' + action + '(' + uid + ', ' + redirect + ');');
        },
        //mandamos un aviso
        set_aviso: function(uid, redirect){
            var type = $('#mod_type').val();
            var subject = $('#mod_subject').val();
            var body = $('#mod_body').val();
            mod.send_data('/moderacion-users.php?do=aviso', 'uid=' + uid + '&av_type=' + type + '&av_subject=' + subject + '&av_body=' + body, uid, redirect);
        },
        //banear al usuario
        set_ban: function(uid, redirect){
            var time = $('#mod_time').val();
            var cant = $('#mod_cant').val();
            var causa = $('#mod_causa').val();
            mod.send_data('/moderacion-users.php?do=ban', 'uid=' + uid + '&b_time=' + time + '&b_cant=' + cant + '&b_causa=' + causa, uid, "'" + redirect + "'");
        },
        //cargamos la acción a realizar
        load_action: function(url_get, url_data, titulo, btn, fn){
            $('#loading').fadeIn(250);
            //realizamos la petición ajax
            $.ajax({
                type: 'POST',
                url: global_data.url + url_get,
                data: url_data,
                success: function(valor){
                    myActions.show();
                    myActions.title(titulo);
                    myActions.body(valor);
                    myActions.buttons(true, true, btn, fn, true, false, true, 'Cancelar', 'close', true, true);
                    $('#loading').fadeOut(350);
                }, complete: function(){
                  myActions.style();
                }
            });
        },
        //enviamos los datos
        send_data: function(url_post, url_data, id, redirect){
            $('#loading').fadeIn(250);
            myActions.inicio_cargando('Procesando...', 'Espere');
            //realizamos la petición ajax
            $.ajax({
                type: 'POST',
                url: global_data.url + url_post,
                data: url_data,
                success: function(valor){
                    switch(valor.charAt(1)){
                        case '0': //si ha ocurrido algún error
                            myActions.alert('Error', valor.substring(4));
                            break;
                        case '1': //si ha salido todo bien
                            myActions.alert('Aviso', valor.substring(4));
                            if(redirect == 'true') {
                                mod.redirect("/moderacion/" + type, 1000);
                            } else if(redirect == 'false') {
                                $('#report_' + id).slideUp(); 
                            }
                            break;
                    }
                    $('#loading').fadeOut(350);
                },
                complete: function(){
                    myActions.final_cargando();
                    $('#loading').fadeOut(350);
                }
            });
        },
        //volvemos al valor por defecto
        reboot: function(id, type, hdo, redirect){
            $('#loading').fadeIn(250);
            //realizamos la petición ajax
            $.ajax({
                type: 'post',
                url: global_data.url + '/moderacion-' + type +'.php?do=' + hdo,
                data: 'id=' + id,
                success: function(valor) {
                    switch(valor.charAt(1)){
                        case '0'://si ha ocurrido algún error
                            myActions.alert("Error", valor.substring(4));
                        break;
                        case '1'://si ha salido todo bien
                            myActions.alert("Aviso", '<div class="action_box">' + valor.substring(4) + '</div>');
                            $('#report_' + id).fadeOut();
                            if(redirect) {
                                if(redirect) {
                                    mod.redirect("/moderacion/" + type, 1000);
                                }
                            }else {
                                $('#report_' + id).slideUp();
                            }
                        break;
                    }
                    $('#loading').fadeOut(350);
                }
            });
        },
        //redireccionamos a la url solicitada
        redirect: function(url_ref, time){
            setTimeout(function(){document.location.href = global_data.url + url_ref;}, time);
        }
    },
    //moderación de fotos
    fotos : {
        // borramos la foto
        borrar:function(fid, redirect, aceptar){
            if(!aceptar){
                //realizamos la petición ajax
                $.ajax({
                    type: 'POST',
                    url: global_data.url + '/moderacion-fotos.php?do=borrar',
                    success: function(valor){
                        myActions.show();
                        myActions.title('Borrar Foto');
                        myActions.body(valor);
                        myActions.buttons(true, true, 'Borrar', 'mod.fotos.borrar(' + fid + ", '" + redirect + "', 1);", true, false, true, 'Cancelar', 'close', true, true);
                        $('#modalBody').css('padding', '20px 10px 0');
                        myActions.style();
                        return;   
                    }
                });
            } else {
                myActions.inicio_cargando('Eliminando...', 'Borrar Foto');
                var razon = $('#razon').val();
                var razon_desc = $('input[name=razon_desc]').val();
                $('#loading').fadeIn(250);
                $.ajax({
                    type: 'POST',
                    url: global_data.url + '/moderacion-fotos.php?do=borrar',
                    data: 'fid=' + fid + '&razon=' + razon + '&razon_desc=' + razon_desc,
                    success: function(valor){
                        switch(valor.charAt(1)){
                            case '0': //si ha ocurrido algún error
                                myActions.alert('Error', valor.substring(4));
                                break;
                            case '1': //si ha salido todo bien
                                    if(redirect == 'true') mod.redirect("/moderacion/fotos", 1200);
                                    else if(redirect == 'fotos') {
                                        myActions.alert('Aviso', valor.substring(4));
                                        mod.redirect("/fotos/", 2000);
                                    } else {
                                        myActions.default();
                                        $('#report_' + fid).slideUp();   
                                    }
                                break;
                        }
                        $('#loading').fadeOut(350);
                    },
                    complete: function(){
                        myActions.final_cargando();
                        $('#loading').fadeOut(350);
                    }
                });
            }
        },
    },
    //moderación de mensajes privados
    mps : {
        //borrar mensaje
        borrar:function(mid, confirmar){
            if(!confirmar){
                myActions.show();
                myActions.title('Borrar Mensaje');
                myActions.body('&#191;Seguro que quieres eliminar <b>toda</b> el mensaje?');
                myActions.buttons(true, true, 'S&iacute;', 'mod.mps.borrar(' + mid + ', 1)', true, false, true, 'No', 'close', true, true);
                myActions.style();
            }else{
                $('#loading').fadeIn(250);
                $.post(global_data.url + '/moderacion-mps.php?do=borrar', 'mpid=' + mid, 
                function(valor){
                    myActions.alert((valor.charAt(1) == '0' ? 'Ouch!' : 'Bien!'), valor.substring(4), false);
                    myActions.style();
                    $('#report_' + mid).fadeOut(); 
                    $('#loading').fadeOut(350);
                });
            }
        },
    },
}