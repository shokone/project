/********** perfil *********************/
var perfil = {
    cache: new Array(),
    //cargamos la tabla seleccionada
    load_tab:function(type, obj){
        $('#tabs_menu > li').removeClass('selected');
        $(obj).parent().addClass('selected');
        $('#perfil_content > div').fadeOut();
        $('#perfil_load').fadeIn();
        perfil.cargar(type);
    },
    //cargamos el contenido necesario
    cargar: function(type){
        var status = $('#perfil_' + type).attr('status');
        if(status == 'activo') {
            $('#perfil_load').hide();
            $('#perfil_' + type).fadeIn();
            return true;
        }
        $('#loading').slideDown(400);
        $.ajax({
            type: 'POST',
            url: global_data.url + '/perfil-' + type + '.php',
            data: 'pid=' + $('#info').attr('pid'),
            success: function(valor){
                switch(valor.charAt(1)){
                    case '0': //si ha ocurrido un error
                        myActions.alert('Error', valor.substring(4));
                        break;
                    case '1': //si ha salido todo bien
                        if(typeof perfil.cache[type] == 'undefined'){
                            $('#perfil_content').append(valor.substring(4));
                            $('#perfil_' + type).fadeIn();
                            perfil.cache[type] = true;
                        }
                        break;
                }
                $('#perfil_load').hide();
                $('#loading').slideUp(350);
            }
        });
    },
    // cargamos los seguidores
    follows:function(type, page){
        $.ajax({
            type: 'POST',
            url: global_data.url + '/perfil-' + type + '.php?hide=true&page=' + page,
            data: 'pid=' + $('#info').attr('pid'),
            success: function(valor){
                $('#perfil_' + type).html(valor.substring(4));
            }
        });
    }
}

/*************** muro ******************/
var muro = {
    maxWidth: 500, // ancho máximo para fotos y vídeos al visualizarlos
    stream: {
        total: 0, //total de publicaciones
        show: 10, // publicaciones a mostrar por página
        type: 'status', // tipo de publicación en la que estamos actualmente
        status: 0, // con esta variable controlaremos las veces que hace click el usuario en un periodo corto de tiempo
        adjunto: '', // datos del archivo adjunto
        //cargamos el tipo de publicación
        load: function(id, obj){
            muro.stream.type = id;
            var ftxt = (muro.stream.type == 'foto') ? 'a' : 'e';
            ftxt = 'Haz un comentario sobre est' + ftxt + ' ' + muro.stream.type + '...';
            if(id != 'status') {
                $('.btnStatus').hide();
                $('.attaDesc').show();
                $('#attaDesc').attr('title', ftxt).val(ftxt);
            } else {
                $('.btnStatus').show();
                $('.attaDesc').hide();
                $('.frameForm').css('border-bottom', '1px solid #E9E9E9');
            }
            //$('span.elementPerfil .nub, span.elementPerfil span').hide();
            //$('span.elementPerfil a').show();
            //$(obj).hide().parent().find('span, i').show();
            // escondemos el actual y mostramos el nuevo
            $('#attaContent > div').hide();
            $('#' + id + 'Frame').show();
            return false;
        },
        // adjuntamos el archivo externo, puede ser una url, una imagen o un video de youtube
        adjuntar: function(){
            if(muro.stream.status == 1){
                return false;
            }else{
                muro.stream.status = 1;
            }
            // cargamos
            muro.stream.loader(true);
            var inpt = $('input[name=i' + muro.stream.type + ']');
            inpt.attr('disabled', 'true');
            var valid = muro.stream.validar(inpt);
            //si ok adjuntamos el archivo
            if(valid == true){
                muro.stream.ajaxCheck(inpt.val(), inpt);
            } else {//si no mostramos un mensaje de error
                myActions.alert('Error al publicar', valid);
                muro.stream.loader(false);
                inpt.attr('disabled', '');
                muro.stream.status = 0;
            }
        },
        // compartir publicación
        compartir: function(){
            //comprobamos si ya hay una carga pendiente
            if(muro.stream.status == 1){
                return false;
            }else{
                muro.stream.status = 1;
            }
            // cargamos
            muro.stream.loader(true);
            var error_txt = 'Las publicaciones de estado y/o comentarios deben ser inferiores a 450 caracteres. Ya has ingresado ';
            //comprobamos si es un adjunto
            if(muro.stream.type != 'status'){
                if(muro.stream.adjunto != ''){
                    var val = $('#attaDesc').val();
                    // validamos el archivo adjunto
                    if(val.length > 450) {
                        myActions.alert('Ocurri&oacute; un error al publicar. ', error_txt + val.length + ' caracteres.');
                        muro.stream.loader(false);
                        muro.stream.status = 0;
                    }else{
                        //sino validamos la publicación
                        val = (val == $('#attaDesc').attr('title')) ? '' : val;
                        muro.stream.ajaxPost(val);
                    }
                }else{
                    //si está vacío mandamos un mensajede error
                    myActions.alert('Error al publicar', 'Ingresa la <b>URL</b> en el campo de texto y a continuaci&oacute;n haz clic en <b>Adjuntar</b>.');
                    muro.stream.loader(false);
                    muro.stream.status = 0;
                }
            // o si es una publicación normal
            } else if(muro.stream.type == 'status'){
                var status = $('#wall');
                var val = status.val();
                var error = false;
                //validamos los datos
                if(val == '' || val == status.attr('title')) {
                    status.blur();
                    error = true;
                    muro.stream.loader(false);
                    muro.stream.status = 0;
                    return false;
                }else if(val.length > 420){
                    error = error_txt + val.length + ' caracteres.';
                }
                //si no hubo errores enviamos la publicación
                if(error == false){
                    muro.stream.ajaxPost(val);
                }else{
                    myActions.alert('Ocurri&oacute; un error al intentar enviar la publicaci&oacute;n', error);
                    muro.stream.loader(false);
                    muro.stream.status = 0;
                }
            }
        },
        // comprobamos la url obtenida
        ajaxCheck: function(url, inp){
            $('#loading').fadeIn(400);
            $.ajax({
                type: 'POST',
                url: global_data.url + '/muro-stream.php?do=check&type=' + muro.stream.type,
                data: 'url=' + encodeURIComponent(url),
                success: function(valor){
                    switch(valor.charAt(1)){
                        case '0': //si ha ocurrido algun error
                            myActions.alert('Error al publicar', valor.substring(4));
                            inp.attr('disabled', '');
                            break;
                        case '1': //si todo ha salido bien
                            muro.stream.adjunto = inp.val();
                            $('#' + muro.stream.type + 'Frame').html(valor.substring(4));
                            break;
                    }
                    $('#loading').fadeOut(400);
                },
                complete: function (){
                    muro.stream.loader(false);
                    muro.stream.status = 0;
                    $('#loading').fadeOut(400);
                }
            });
        },
        // realizamos una publicación en el muro
        ajaxPost: function(datos){
            $('#loading').slideDown(400);
            $.ajax({
                type: 'POST',
                url: global_data.url + '/muro-stream.php?do=post&type=' + muro.stream.type,
                data: 'adj=' + muro.stream.adjunto +'&data=' + datos + '&pid=' + $('#info').attr('pid'),
                success: function(valor){
                    switch(valor.charAt(1)){
                        case '0': //si ha ocurrido un error
                            myActions.alert('Error al publicar', valor.substring(4));
                            break;
                        case '1': //si todo ha salido bien
                            //si es el primer comentario lo escondemos
                            if($('#wall-content .emptyData')){
                                $('#wall-content .emptyData').hide();
                            }
                            $('#wall-content, #news-content').prepend($(valor.substring(4)).fadeIn('slow'));
                            $('#wall').val('').focus();
                            muro.stream.load('status', $('#stMain'));
                            break;
                    }
                    $('#loading').slideUp(400);
                },
                complete: function (){
                    muro.stream.loader(false);
                    muro.stream.status = 0;
                    $('#loading').fadeOut(400);
                }
            });
        },
        // validamosla url introducida
        validar: function(input){
            var val = input.val();
            var regex = /^(ht|f)tps?:\/\/\w+([\.\-\w]+)?\.([a-z]{2,3}|info|mobi|aero|asia|name)(:\d{2,5})?(\/)?((\/).+)?$/i;
            //comprobamos la url
            if(val == '' || val == input.attr('title') || regex.test(val) == false){
                return 'La URL introducida no es v&aacute;lida.';
            }else{//si todo ok comprobamos según el tipo de dato obtenido de la url
                switch(muro.stream.type){
                    case 'video':
                        var video_id = val.split('watch?v=');
                        if(!video_id[1]){
                            return 'Al parecer la url del video no es v&aacute;lida. Recuerda que solo puedes incluir videos de YouTube.';
                        }
                    break;
                    case 'foto':
                        input.val(val.replace(' ', ''));
                        var typefile = input.val().substr(-3);
                        if(typefile != 'gif' && typefile != 'png' && typefile != 'jpg'){
                            return 'S&oacute;lo se permiten im&aacute;genes con formato .gif, .png o .jpg';
                        }
                    break;
                }
                return true;
            }
        },
        //cargar más publicaciones (siguiente página)
        loadMore: function(type){
            // comprobamos si ya hay una acción pendiente
            if(muro.stream.status == 1){
                return false;
            }else{
                muro.stream.status = 1;
            }
            $('.more-pubs a').hide();
            $('.more-pubs span').css('display','block');
            $('#loading').fadeIn(400);
            $.ajax({
                type: 'POST',
                url: global_data.url + '/muro-stream.php?do=more&type=' + type,
                data: 'pid=' + $('#info').attr('pid') + '&start=' + muro.stream.total,
                success: function(valor){
                    switch(valor.charAt(1)){
                        case '0': //si ha ocurrido un error
                            myActions.alert('Error al cargar', valor.substring(4));
                            break;
                        case '1': //si todo ha salido bien
                            //cargamos el elemento
                            $('#' + type + '-content').append(valor.substring(4));
                            //validamos
                            var ptotal = $('#total_pubs').attr('val');
                            ptotal = parseInt(total_pubs);
                            if(type == 'news' && total_pubs < 0){
                                var message =  'Solo puedes ver las &uacute;ltimas 100 publicaciones.';
                            }else{
                                var message =  'No hay m&aacute;s mensajes para mostrar.';
                            }
                            if(ptotal == 0 || ptotal < muro.stream.show){
                                $('.more-pubs').html(message).css('padding','10px');
                            }else{
                                muro.stream.total = muro.stream.total + parseInt(ptotal);
                            }
                            $('#ptotal').remove();
                            break;
                    }
                    $('#loading').fadeOut(400);
                },
                complete: function (){
                    $('.more-pubs a').show();
                    $('.more-pubs span').hide();
                    muro.stream.status = 0;
                    $('#loading').fadeOut(400);
                }
            });
        },
        // LOADER
        loader: function(active){
            if(active == true) $('.streamLoader').show();
            else if(active == false) $('.streamLoader').hide();
        }
    },
    //damos me gusta a una publicación
    like_this: function(id, type, obj){
        muro.stream.status = 1;
        //mandamos los datos
        $('#loading').slideDown(400);
        $.ajax({
            type: 'POST',
            url: global_data.url + '/muro-likes.php',
            dataType: 'json',
            data: 'id=' + id + '&type=' + type,
            success: function(valor){
               if(valor['status'] == 'ok'){
                   //obtenemos el valor de si me gusta o no
                   $(obj).text(valor['link']);
                   if(type == 'pub'){//si es una publicación
                        //obtenemos el campo
                       $('#lk_' + id).html(valor['text']);
                       if(valor['text'] != '') {
                           $('#lk_' + id).parent().parent().show();
                           $('#cb_' + id).show();
                       } else
                           $('#lk_' + id).parent().parent().hide();
                   }else{
                        $('#lk_cm_'+id).text(valor['text']);
                        if(valor['text'] == ''){
                            $('#lk_cm_'+id).parent().hide();
                        }else{
                            $('#lk_cm_'+id).parent().show();
                        }
                   }
               } else {
                   myActions.alert('Error:', valor['text'].substring(4));
               }
               $('#loading').slideUp(400);
            },
            complete: function (){
                muro.stream.status = 0;
            }
        });
    },
    //mostramos a cuantos usuarios les gusta la publicación
    show_likes: function(id, type){
        muro.stream.status = 1;
        //mandamos los datos
        $('#loading').fadeIn(400);
        $.ajax({
            type: 'POST',
            url: global_data.url + '/muro-likes.php?do=show',
            dataType: 'json',
            data: 'id=' + id + '&type=' + type,
            success: function(valor){
                switch(valor.status){
                    case 0: //si ha ocurrido un error
                        myActions.alert('Error', valor['data']);
                        break;
                    case 1: //si ha salido todo bien
                        var html = '<ul id="show_likes">';
                        for(var i = 0; i < valor.data.length; i++){
                            html += '<li>';
                            html += '<a href="' + global_data.url + '/perfil/' + valor.data[i].user_name + '">';
                            html += '<img src="' + global_data.url + '/files/avatar/' + valor.data[i].user_id + '_50.jpg" />';
                            html += '</a>';
                            html += '<div class="name">';
                            html += '<a href="' + global_data.url + '/perfil/' + valor.data[i].user_name + '">' + valor.data[i].user_name + '</a>';
                            html += '</div>';
                            html += '</li>';
                        }
                        html += '</ul>';
                        //mostramos los datos
                        myActions.show(true);
                        myActions.title('Personas a las que les gusta');
                        myActions.body(html);
                        myActions.buttons(true, true, 'Cerrar', 'close', true, true);
                        myActions.style();
                        break;
                }
                $('#loading').fadeOut(400);
            },
            complete: function (){
                muro.stream.status = 0;
            }
        });

    },
    //mostramos la caja de comentarios con efecto desplegable
    show_comment_box: function(id){
        $('#cb_' + id).slideDown()
    },
    //comentamos una publicación
    comentar: function(id){
        //obtenemos datos
        var val = $('#cf_' + id).val();
        muro.stream.status = 1;
        //comprobamos los datos obtenidos
        if(val == '' || val == $('#cf_' + id).attr('title')){
            $('#cf_' + id).focus();
            muro.stream.loader(false);
            muro.stream.status = 0;
            return false;
        }
        //realizamos una petición ajax para mandar los datos
        $('#loading').fadeIn(250);
        $.ajax({
            type: 'POST',
            url: global_data.url + '/muro-stream.php?do=repost',
            data: 'data=' + encodeURIComponent(val) + '&pid=' + id,
            success: function(valor){
                switch(valor.charAt(1)){
                    case '0': //si ha ocurrrido un error
                        myActions.alert('Error:', valor.substring(4));
                        break;
                    case '1': //si todo ha salido bien
                        $('#cl_' + id).append($(valor.substring(4)).fadeIn('slow'));
                        $('#cf_' + id).val('');
                        break;
                }
                $('#loading').fadeOut(250);
            },
            complete: function (){
                //reiniciamos el status a 0
                muro.stream.status = 0;
                $('#loading').fadeOut(350);
            }
        });
    },
    //cargar más comentarios
    more_comments: function(id, obj){
        muro.stream.status = 1;
        $(obj).parent().find('img').show();
        //mandamos una petición para obtener los datos
        $('#loading').fadeIn(400);
        $.ajax({
            type: 'POST',
            url: global_data.url + '/muro-stream.php?do=more_comments',
            data: 'pid=' + id,
            success: function(valor){
                switch(valor.charAt(1)){
                    case '0': //si ha ocurrido un error
                        myActions.alert('Error:', valor.substring(4));
                        break;
                    case '1': //si todo ha salido bien
                        $('#cl_' + id).html(valor.substring(4));
                        break;
                }
                $('#loading').fadeOut(350);
            },
            complete: function (){
                //reiniciamos el estado
                muro.stream.status = 0;
                $('#loading').fadeOut(500);
            }
        });
    },
    //cargamos datos del adjunto para mostrarlo
    load_atta: function(type, id, obj){
        //realizamos la acción según el tipo de archivo
        switch(type){
            case 'foto':
                var content = '<span class="center"><img src="' + id + '" style="max-width:' + this.maxWidth + 'px; max-height: 380px" /><span>';
                break;
            case 'video':
                var content = '<span class="center"><embed width="' + this.maxWidth + '" height="285"';
                content += 'flashvars="width=' + this.maxWidth + '&amp;height=285" wmode="opaque" salign="tl"';
                content += 'allowscriptaccess="never" allowfullscreen="false" scale="scale" quality="high" bgcolor="#FFFFFF"';
                content += 'src="http://www.youtube.com/v/' + id +'&amp;autoplay=1" type="application/x-shockwave-flash"></span>';
                break;
        }
        $(obj).parent().html(content);
    },
    //eliminar una publicación o un comentario
    del_pub: function(id, type){
        //obtenemos el tipo y si es masculino o femenino el término
        var ttype = (type == 1) ? 'publicaci&oacute;n' : 'comentario';
        var taux = (type == 1) ? 'esta ' : 'este ';
        //mostramos un mensaje de confirmación
        myActions.box_close = false;
        myActions.show(true);
        myActions.title('Eliminar ' + ttype);
        myActions.body('¿Seguro que quieres eliminar ' + taux + ttype);
        myActions.buttons(true, true, 'Eliminar ' + ttype, 'muro.eliminar(' + id + ', ' + type + ')', true, true, true, 'Cancelar', 'close', true, false);
        myActions.style();
    },
    //mandamos la petición ajax para eliminar la publicación o el comentario
    eliminar: function(id, type){
        //obtenemos los datos
        muro.stream.status = 1;
        var stype = (type == 1) ? 'pub' : 'cmt';
        //mandamos los datos
        $('#loading').slideDown(400);
        $.ajax({
            type: 'POST',
            url: global_data.url + '/muro-stream.php?do=delete',
            data: 'id=' + id + '&type=' + stype,
            success: function(valor){
                switch(valor.charAt(1)){
                    case '0': //si ha ocurrido un error
                        myActions.alert('Error:', valor.substring(4));
                        break;
                    case '1': //si ha salido todo bien
                        myActions.default();
                        $('#' + stype + '_' + id).hide().remove();
                        break;
                }
                $('#loading').slideUp(400);
            },
            complete: function (){
                muro.stream.status = 0;
                $('#loading').slideUp(400);
            }
        });
    }
}

/******* actividad del usuario *********/
var actividad = {
    total: 25,//máximo de actividades
    show: 25,//máximo de actividades a mostrar por página
    //cargamos la lista de actividades
    cargar: function(id, ac_do, ac_type){
        $('#last-activity-view-more').remove();
        if(ac_do == 'filtrar') actividad.total = 0;
        //enviamos los datos
        $.ajax({
            type: 'POST',
            url: global_data.url + '/perfil-actividad.php',
            data: 'pid=' + $('#info').attr('pid') + '&ac_type=' + ac_type + '&do=' + ac_do + '&start=' + actividad.total,
            success: function(valor){
                switch(valor.charAt(1)){
                    case '0': //si ha ocurrido un error
                        myActions.alert('Error', valor.substring(4));
                        break;
                    case '1': //si todo ha salido bien
                        if(ac_do == 'more'){
                            $('#last-activity-container').append(valor.substring(4));
                        }else{
                            $('#last-activity-container').html(valor.substring(4));
                        }
                        //obtenemos el total
                        var ptotal = $('#total_acts').attr('val');
                        actividad.total = actividad.total + parseInt(ptotal);
                        $('#total_acts').remove();
                        break;
                }
            }
        });
    },
    //borramos una actividad
    borrar: function(id, obj){
        //mandamos los datos
        $.ajax({
            type: 'POST',
            url: global_data.url + '/perfil-actividad.php',
            data: 'pid=' + $('#info').attr('pid') + '&acid=' + id + '&do=borrar',
            success: function(valor){
                switch(valor.charAt(1)){
                    case '0': //si ha ocurrido algun error
                        myActions.alert('Error', valor.substring(4));
                        break;
                    case '1': //si todo ha salido bien
                        $(obj).parent().parent().parent().remove();
                        break;
                }
            }
        });
    }
}

/*
 * con esta función vamos a controlar cuando se envíe una publicación
 * cuando se cargue un archivo adjunto
 * la obtención de las respuestas en las publicaciones
 */
$(function(){
    //muro
    $('#wall').focus(function(){
        $('.btnStatus').show();
        $('.frameForm').css('border-bottom', '1px solid #E9E9E9');
    });
    //enviamos la pubicación
    $('textarea[name=add_wall_comment]').on("keypress",function(valor){
        if(valor.which == 13){
            //obtenemos el id de la publicación
            var pid = $(this).attr('pid');
            muro.comentar(pid);
            return false;
        }
    });
    //adjuntamos el archivo
    $('.adj').click(function(){
        var aid = $(this).attr('aid');
    })
    //obtenemos las respuestas (comentarios en publicaciones)
    $('.comentar').css('max-height', '200px').css('height','14px');
    $('input[name=hack]').on("focus",function(){
        $(this).hide();
        $(this).parent().find('div.formulario').show();
        var pid = $(this).attr('pid');
        $('#cf_' + pid).focus()
    });
});
