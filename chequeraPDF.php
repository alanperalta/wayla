<?php
session_start();

require_once('includes/fpdf/code128.php');
require_once('includes/ConfigItrisWS.php');

$data = array( );
$do_login = ItsLogin();
if(!$do_login['error']){
    $userSession = $do_login['usersession'];
    $pasajeroPDF = encriptado($_GET["pasajero"], 'd');
    $getDataResult = ItsGetData($userSession, '_TUR_CUOTAS_INF', '100', "FK_TUR_CONTRATOS = '".$_GET['contrato']."' AND TIPO = 'N' AND SALDO > 0 AND FK_TUR_PASAJEROS = ".$pasajeroPDF, 'CUOTA ASC');
    if(!$getDataResult['error']) {
        if(count($getDataResult['data']) > 0){
            $i = 0;
            foreach ($getDataResult['data'] as $cuota) {
                $data[$i]['numero'] = (string)$cuota['NUM_COM'];
                $data[$i]['colegio'] = (string)$cuota['COLEGIO'];
                $data[$i]['pasajero'] = (string)$cuota['NOMBRE'];
                $data[$i]['vencimiento'] = (string)$cuota['FEC_VEN_1'];
                $data[$i]['importe'] = (double)$cuota['IMP_PEN'];
                $data[$i]['vencimiento2'] = (string)$cuota['FEC_VEN_2'];
                $data[$i]['importe2'] = (double)$cuota['IMP_CON_REC'];
                $data[$i]['concepto'] = (string)$cuota['Z_TIPO'];
                $data[$i]['cod_bar'] = (string)$cuota['COD_BAR'];
                $data[$i]['estado'] = (string)$cuota['ESTADO'];
                $i++;
            }
        }else{
            echo 'No existen cuotas del plan para abonar';
            ItsLogout($userSession);
            exit();
        }
    }else{
        ItsLogout($userSession);
        echo $getDataResult['message'];
        exit();
    }
    ItsLogout($userSession);
}

$pdf = new PDF_Code128();
$pdf->SetMargins(5, 5, 5);
$pdf->SetAutoPageBreak(false);
$iCuota = 0;
foreach ($data as $cuota) {
    if($iCuota % 3 == 0){
        $pdf->AddPage();
        $Y = 0;
    }
    //Rectangulo principal
    $pdf->Rect(5, 5+$Y, 200, 90);
    $pdf->Line(105, 5+$Y, 105, 95+$Y);

    for ($i = 0; $i < 2; $i++) {
        if($i == 0){
            $X = 10;
        }else{
            $X = 110;
        }
        //Logo
        $pdf->Image('resources/wayla.png', $X, 5+$Y, 40);

        //Cabecera
        $pdf->SetFont('Arial','',8);
        $pdf->SetXY($X+40, 10+$Y);
        $pdf->Cell(50, 4, "LEGAJO 13444 Dis. 2297", 0, 2, 'R');
        $pdf->Cell(50, 4, "CUIT: 30-71509621-4", 0, 2, 'R');
        $pdf->Cell(50, 4, "IIBB: 902-571782-1", 0, 2, 'R');
        $pdf->Cell(50, 4, utf8_decode("Bureau Leloir - Av.Pte.Perón 8725"), 0, 2, 'R');
        $pdf->Cell(50, 4, "Parque Leloir", 0, 1, 'R');

        //Cuerpo
        $pdf->SetXY($X, 35+$Y);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(50, 5, utf8_decode("TALON DE PAGO Nº").":         ".$cuota['numero'], 0, 2, 'L');
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(50, 5, "COLEGIO:        ".utf8_decode($cuota['colegio']), 0, 2, 'L');
        $pdf->Cell(50, 5, utf8_decode($cuota['pasajero']), 0, 2, 'L');
        $pdf->SetFont('Arial', 'B', '12');
        $pdf->Cell(50, 5, $cuota['vencimiento']."                                         $ ".number_format($cuota['importe'], 2, ",", "."), 0, 2, 'L');
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(50, 5, "2do. Vencimiento", 0, 2, 'L');     
        $pdf->Cell(50, 5, $cuota['vencimiento2']."                                                        $ ".number_format($cuota['importe2'], 2, ",", "."), 0, 2, 'L');
        $pdf->Cell(50, 5, "Concepto:                                                ".  utf8_decode($cuota['concepto']), 0, 2, 'L');

    }

    //Pie 1
    $pdf->SetFont('Arial','B',8);
    $pdf->SetXY(20, 78+$Y);
    $pdf->MultiCell(70, 4, utf8_decode("Ticket no válido como constancia de pago sin el comprobante de la Entidad Recaudadora adjunto."), 0, 'C');
    $pdf->SetXY(40, 89+$Y);
    $pdf->SetFont('Arial','',9);
    $pdf->Cell(50, 6, "Recibo para el cliente", 0, 2, 'L');   

    //Pie 2
    if($cuota['cod_bar'] != '' && $cuota['estado'] == 'H'){
        $pdf->Code128(112,73+$Y,$cuota['cod_bar'],90,12);
        $pdf->SetXY(120, 84+$Y);
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(50, 6, $cuota['cod_bar'], 0, 2, 'L'); 
        $pdf->SetXY(130, 89+$Y);
        $pdf->SetFont('Arial','',9);
        $pdf->Cell(50, 6, "Recibo para entidad recaudadora", 0, 2, 'L'); 
    }else{
        if($cuota['cod_bar'] != '' && $cuota['estado'] == 'P'){
            $msj = "Cuota ya abonada";
        }
        else if($cuota['estado'] == 'I'){
            $msj = "Esta cuota no se encuentra habilitada para abonar. Contacte a la administraci&oacute;n";
        }else{
            $msj = "Abonar en Banco Galicia por cajero de autoservicio o por ventanilla "
                ."con el nro de DNI del pasajero o con el nro de convenio: 4687";
        }
        $pdf->SetXY(120, 74+$Y);
        $pdf->SetFont('Arial','',9);
        $pdf->MultiCell(80, 6, utf8_decode($msj), 0, 'L'); 
    }
    
    $iCuota++;
    $Y += 90;
}

$pdf->Output('I', 'Chequera.pdf');