//función para desactivar la cuenta del usuario
function desactivate(cuenta) {
  if(!cuenta){
    myActions.show();
    myActions.title('Desactivar Cuenta');
    myActions.body('&#191;Seguro que quiere desativar su cuenta?');
    myActions.buttons(true, true, 'Desactivar', 'desactivate(true)', true, false, true, 'No', 'close', true, true);
    myActions.style();
  }else{
    var pass = $('#passi');
    $('#loading').fadeIn(200);
    $.ajax({
      type: 'post',
      url: global_data.url + '/cuenta.php?action=desactivate',
      data: 'validar=' + 'ajaxcontinue', 
      function(valor){
        myActions.alert((valor.charAt(1) == '0' ? 'Ouch!' : 'Hecho'), (valor.charAt(1) == '0' ? 'No se pudo desactivar la cuenta' : 'Cuenta desactivada'), true);
        myActions.style();
        $('#loading').fadeOut(400);
      }, 
    });
  }
}

//obtenemos el input del tipo obtenido por parametro y lo ocultamos o mostramos
function input_fake(type) {
  $('.input-hide-'+type).hide();
  $('.input-hidden-'+type).show().focus();
}

var cuenta = {
  ciudad_id: '',
  ciudad_text: '',
  no_requerido: new Array(),
  //mandamos un mensaje de alerta al usuario
  alert: function (section, title, body) {
    $('div.alert-cuenta.cuenta-'+section).html('<h2>'+title+'</h2>');
    $('div.alert-cuenta.cuenta-'+section).slideDown(100);
  },
  //cerramos el mensaje de alerta
  alert_close: function (section) {
    $('div.alert-cuenta.cuenta-'+section).html('');
    $('div.alert-cuenta.cuenta-'+section).slideUp(100);
  },
  //guardamos los datos de la cuenta del usuario
  save: function (section, next) {
    $('.ac_input, .cuenta-save-'+section).removeClass('input-incorrect');
    if (typeof next == 'undefined'){
      var next = false;
    }
    params = new Array();
    params.push('save='+section);

    $('.cuenta-save-'+section).each(function(){
      if(($(this).attr('type') != 'checkbox' && $(this).attr('type') != 'radio') || $(this).attr('checked')){
        params.push($(this).attr('name')+'='+encodeURIComponent($(this).val()));
      }
    });
    params = params.join('&');
    var url = global_data.url + '/cuenta.php?action=save&ajax=true';
    $('#loading').slideDown(200);
    //realizamos la petición ajax
    $.ajax({
      type: 'post', 
      url: url, 
      data: params, 
      success: function(valor){
        alert(valor);
        if(valor.error){
          if(valor.field){
            $('input[name='+valor.field+']').focus().addClass('input-incorrect');
          }
          cuenta.alert(section, valor.error);
        }else{
          if(next) {
            cuenta.next(section > 1 && section < 5);
          }
          cuenta.alert(section, 'Los cambios fueron aplicados correctamente');
          if(valor.porc != null) {
            $('#porc-completado-label').html('Perfil completo al ' + valor.porc + '%');
            $('#porc-completado-barra').css('width', valor.porc + '%');
          }
        }
        window.location.hash = 'alert-cuenta';
        $('#loading').slideUp(200); 
      }
    });
  },
  //mostramos un mensaje de error al usuario
  error: function(obj, str){
    var container = $(obj).next();
    if($(container).hasClass('errorstr')){
      $(container).show();
      $(container).html(str);
    }
  },
  //pasamos al siguiente elemento
  next: function (profile) {
    if (typeof profile == 'undefined'){
      var profile = false;
    }
    if (profile){
      $('div.content-tabs.perfil > h3.active').next().next().click();
    }else{
      $('div.menuc > ul.menu-cuenta > li.active').next().children().click();
    }
  },
  //mostramos u ocultamos las diferentes tablas
  chgtab: function (obj) {
    $('div.menuc > ul.menu-cuenta > li.active').removeClass('active');
    $(obj).parent().addClass('active');
    var active = $(obj).html().toLowerCase().replace(' ', '-');
    $('div.content-tabs').hide();
    $('div.content-tabs.'+active).show();
  },
  //realizamos el cambio de sección
  chgsec: function (obj) {
    $('div.content-tabs.perfil > h3').removeClass('active');
    $('div.content-tabs.perfil > fieldset').slideUp(200);
    if ($(obj).next().css('display') == 'none') {
      $(obj).addClass('active');
      $(obj).next().slideDown(200).addClass('active');
    }
  },
  //cambiamos los datos del país y el estado
  chgpais: function(){
    var pais = $('form[name=editarcuenta] select[name=pais]').val();
    var estado = $('form[name=editarcuenta] .content-tabs.cuenta select[name=estado]');

    //No se selecciono ningun pais.
    if(pais.length == ''){
      $('form[name=editarcuenta] select[name=estado]').addClass('disabled').attr('disabled', 'disabled').val('');
    }else{
      //obtenemos los estados
      $(estado).html('');
      $('#loading').fadeIn(250);
      //y realizamos la petición ajax
      $.ajax({
        type: 'GET',
        url: global_data.url + '/registro-geo.php',
        data: 'pais_code=' + pais,
        success: function(valor){
          switch(valor.charAt(1)){
            case '0': //si ha ocurrido algún error no hacemos nada
              break;
            case '1': //si ha salido todo bien
              cuenta.no_requerido['estado'] = false;
              $(estado).append(valor.substring(4)).removeAttr('disabled').val('').focus();
              break;
          }
          $('#loading').fadeOut(250); 
        },
      });
    }
  },
}

var avatar = {}