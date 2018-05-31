$(document).ready(function() {
    $('[id^=detail-]').hide();
    $('.toggle').click(function() {
        $input = $( this );
        $target = $('#'+$input.attr('data-toggle'));
        $target.slideToggle();
    });
});

function descargarCuota(numero, pasajero){
    window.open("cuotaPDF.php?numero="+numero+"&pasajero="+pasajero, '_blank');
}

function descargarChequera(contrato, pasajero){
    window.open("chequeraPDF.php?contrato="+contrato+"&pasajero="+pasajero, '_blank');
}

function descargarFormulario(pasajero){
    window.open("adhesionPDF.php?pasajero="+pasajero);
}

function infoCuota(){
    $('#infoCuota').dialog({
            width: 'auto',
            maxWidth: 600,
            height: 'auto',
            modal: true,
            fluid: true,
            resizable: false,
            draggable: false
        });
}