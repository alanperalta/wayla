 $.datepicker.regional['es'] = {
    closeText: 'Cerrar',
    prevText: '< Ant',
    nextText: 'Sig >',
    currentText: 'Hoy',
    monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
    monthNamesShort: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
    dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
    dayNamesShort: ['Dom','Lun','Mar','Mié','Juv','Vie','Sáb'],
    dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sá'],
    weekHeader: 'Sm',
    dateFormat: 'dd/mm/yy',
    firstDay: 1,
    isRTL: false,
    showMonthAfterYear: false,
    yearSuffix: ''
 };
 $.datepicker.setDefaults($.datepicker.regional['es']);

$(function() {

    $('#login-form-link').click(function(e) {
		$("#login-form").delay(100).fadeIn(100);
 		$("#register-form").fadeOut(100);
		$('#register-form-link').removeClass('active');
		$(this).addClass('active');
		e.preventDefault();
	});
	$('#register-form-link').click(function(e) {
		$("#register-form").delay(100).fadeIn(100);
 		$("#login-form").fadeOut(100);
		$('#login-form-link').removeClass('active');
		$(this).addClass('active');
		e.preventDefault();
	});
        
        $(".readonly").on('keydown paste', function(e){
            e.preventDefault();
        });
        
        $( "#FEC_NAC" ).datepicker({
            dateFormat: "dd/mm/yy",
            changeMonth: true,
            changeYear: true,
            yearRange: "-100:+0"
        });

});

//Valido contrato apretando enter 
function capturaTecla(event){
    if(event.which === 13){
        terminos(event);
    }
}

function validaContrato(event){
    event.preventDefault();
    var parametros = {
        'contrato': $('#FK_TUR_CONTRATOS').val(),
        'clave': $('#CLAVE_DESC').val()
    };
    $.ajax({
        data:  parametros,
        url:   './validaContrato.php',
        type:  'post',
        dataType: 'json',
        beforeSend: function (xhr) {
            $('#btn-validar').html('Validando <i class="fa fa-refresh fa-spin" style="font-size:24px"></i>');
            $('#btn-validar').prop('disabled', true);
        }
    }).done( function (response) {
        if(response.error === ''){
            if(response.encontrado){
                $('#register-submit').prop('disabled', false);
                $('#FK_TUR_CONTRATOS').prop('readonly', true);
                $('#CLAVE_DESC').prop('readonly', true);
                $('#FK_TUR_CONTRATOS').val(response.contrato);
                $('#campos-registro').show();
                $('#btn-validar').hide();
            }else{
                $('#register-submit').prop('disabled', true);
                $('#FK_TUR_CONTRATOS').prop('readonly', false);
                $('#CLAVE_DESC').prop('readonly', false);
                $('#campos-registro').hide();
                $('#btn-validar').show();
                $.notify("Contrato no registrado en el sistema o clave incorrecta",{
                    globalPosition: 'top left',
                    className: 'error'
                });
                $('#btn-validar').prop('disabled', false);
                $('#btn-validar').html('Validar contrato');
            }
        }else $.notify(response.error,{
                    globalPosition: 'top left',
                    className: 'error'
                });
                $('#btn-validar').prop('disabled', false);
                $('#btn-validar').html('Validar contrato');
    });
}

function seleccionarLocalidad(){
    $('#FK_ERP_LOCALIDADES').val($('li.ui-selected').attr('id'));
    $('#FK_ERP_LOCALIDADES_DESC').val($('li.ui-selected').html());
    $('#modal-localidades').modal('hide');
}

function seleccionarLocalidadRL(){
    $('#FK_ERP_LOCALIDADES_RL').val($('li.ui-selected').attr('id'));
    $('#FK_ERP_LOCALIDADES_RL_DESC').val($('li.ui-selected').html());
    $('#modal-localidades').modal('hide');
}

//Si apreto enter en el campo empresa, no intenta submit, ejecuta la función para buscar empresas
function buscarLocalidad(event){
    if (event.keyCode === 10 || event.keyCode === 13){ 
        if($('#tipoLocalidad').val() == 'RL'){
            funcionLocalidad = 'seleccionarLocalidadRL()';
        }else{
            funcionLocalidad = 'seleccionarLocalidad()';
        }
        
        event.preventDefault();
        var parametros = {
            'clave':$('#modal-input-localidad').val()
        };
        $.ajax({
            data:  parametros,
            url:   './ERP_LOCALIDADES.php',
            type:  'post',
            dataType: 'json',
            beforeSend: function (xhr) {
                $('#lista-localidades').html(
                    '<div id="loading-localidades">Buscando... <i class="fa fa-refresh fa-spin" style="font-size:24px"></i></div>'
                );
            }
        }).done( function (response) {
            fila = '';
            //Recorro el JSON y agrego un item de lista por cada localidad encontrada
            $.each(response, function(i, member) {
                fila += '<li onclick="'+funcionLocalidad+'" class="ui-widget-content item-localidad" id="'+response[i].ID+'">'+response[i].DESCRIPCION+', '+response[i].PARTIDO+', '+response[i].PROVINCIA+'</li>';
            });
            $('#lista-localidades').html(fila);
            $('#lista-localidades').selectable({
                selected: function(e, ui){
                    seleccion = $('li.ui-selected').length;
                    if(seleccion === 1){
                        $('#btn-modal-localidad').prop("disabled", false);
                    }else $('#btn-modal-localidad').prop("disabled", true);
                }
            });
            $('#loading-localidad').hide();
            $('#modal-input-localidad').blur();
        });
    }
}

function validaDNI(){
    var parametros = {
        'contrato': $('#FK_TUR_CONTRATOS').val(),
        'dni': $('#NUM_DOC').val()
    };
    $.ajax({
        data:  parametros,
        url:   './validaDNI.php',
        type:  'post',
        dataType: 'json'
    }).done( function (response) {
        if(response.error === ''){
            if(response.encontrado){
                $('#NUM_DOC').notify("DNI ya registrado con ese nro. de contrato",{
                    position: 'bottom center',
                    className: 'error',
                    autoHide: false
                });
                $('#register-submit').prop('disabled', true);
            }else{
                $('#register-submit').prop('disabled', false);
                $('#NUM_DOC').notify('');
            }
        }else{ $.notify(response.error,{
                    globalPosition: 'top left',
                    className: 'error',
                    autoHide: false
                });
              $('#register-submit').prop('disabled', true);
        }
    });
}

//Muestro pop-up de localidades
function popupLocalidades(event){
    event.preventDefault();
    $('#modal-localidades').modal("show");
    $('#modal-input-localidad').val('');
    $('#lista-localidades').html('');
    $('#tipoLocalidad').val('P');
}

function popupLocalidadesRL(event){
    event.preventDefault();
    $('#modal-localidades').modal("show");
    $('#modal-input-localidad').val('');
    $('#lista-localidades').html('');
    $('#tipoLocalidad').val('RL');
}

function registrar(){
    validator = $("#register-form").validate();
    if (validator.form()) {
        $('#register-submit').html('Registrando <i class="fa fa-refresh fa-spin" style="font-size:24px"></i>');
        $('#register-submit').prop('disabled', true);
        $("#register-form").submit();
    }
}

function ingresar(){
    validator = $("#login-form").validate();
    if (validator.form()) {
        $('#login-submit').html('Cargando <i class="fa fa-refresh fa-spin" style="font-size:24px"></i>');
        $('#login-submit').prop('disabled', true);
        $("#login-form").submit();
    }
}

function terminos(event){
    event.preventDefault();
    $('#terminos').dialog({
            width: 'auto',
            maxWidth: 600,
            height: 'auto',
            modal: true,
            resizable: false,
            draggable: false,
           buttons: {
        "Acepto": function() {
          validaContrato(event);
           $( this ).dialog( "close" );
        },
        "No acepto": function() {
          $( this ).dialog( "close" );
        }
      }
        });
}