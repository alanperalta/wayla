<?php
session_start();

require_once('../includes/fpdf/code128.php');
require_once('../includes/ConfigItrisWS.php');

$data = array();
$do_login = ItsLogin();
if(!$do_login['error']){
    $userSession = $do_login['usersession'];
    $pasajeroPDF = encriptado($_GET["pasajero"], 'd');
    $getDataResult = ItsGetData($userSession, '_TUR_CUOTAS_INF', '1', "NUM_COM='".$_GET['numero']."' AND FK_TUR_PASAJEROS=".$pasajeroPDF);
    if(!$getDataResult['error']) {
        if(count($getDataResult['data']) > 0){
            $data['numero'] = (string)$getDataResult['data'][0]['NUM_COM'];
            $data['colegio'] = (string)$getDataResult['data'][0]['COLEGIO'];
            $data['pasajero'] = (string)$getDataResult['data'][0]['NOMBRE'];
            $data['vencimiento'] = (string)$getDataResult['data'][0]['FEC_VEN_1'];
            $data['importe'] = (double)$getDataResult['data'][0]['IMP_PEN'];
            $data['vencimiento2'] = (string)$getDataResult['data'][0]['FEC_VEN_2'];
            $data['importe2'] = (double)$getDataResult['data'][0]['IMP_CON_REC'];
            $data['concepto'] = (string)$getDataResult['data'][0]['Z_TIPO'];
            $data['cod_bar'] = (string)$getDataResult['data'][0]['COD_BAR'];
            $data['cuota'] = (string)$getDataResult['data'][0]['CUOTA'];
        }else{
            echo 'Acceso incorrecto al sistema de cuotas. Vuelva a ingresar';
            ItsLogout($userSession);
            exit();
        }
    }
    ItsLogout($userSession);
}

$pdf = new PDF_Code128();
$pdf->SetMargins(5, 5, 5);
$pdf->AddPage();

//Rectangulo principal
$pdf->Rect(5, 5, 200, 100);
$pdf->Line(105, 5, 105, 105);

for ($i = 0; $i < 2; $i++) {
    if($i == 0){
        $X = 10;
    }else{
        $X = 110;
    }
    //Logo
    $pdf->Image('../resources/wayla.png', $X, 5, 40);
    
    //Cabecera
    $pdf->SetFont('Arial','',8);
    $pdf->SetXY($X+40, 10);
    $pdf->Cell(50, 4, "LEGAJO 13444 Dis. 2297", 0, 2, 'R');
    $pdf->Cell(50, 4, "CUIT: 30-71509621-4", 0, 2, 'R');
    $pdf->Cell(50, 4, "IIBB: 902-571782-1", 0, 2, 'R');
    $pdf->Cell(50, 4, utf8_decode("Bureau Leloir - Av.Pte.Perón 8725"), 0, 2, 'R');
    $pdf->Cell(50, 4, "Parque Leloir", 0, 1, 'R');
    
    //Cuerpo
    $pdf->SetXY($X, 40);
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(50, 6, utf8_decode("TALON DE PAGO Nº").":         ".$data['numero'], 0, 2, 'L');
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(50, 6, "COLEGIO:        ".utf8_decode($data['colegio']), 0, 2, 'L');
    $pdf->Cell(50, 6, utf8_decode($data['pasajero']), 0, 2, 'L');
    $pdf->SetFont('Arial', 'B', '12');
    $pdf->Cell(50, 6, $data['vencimiento']."                                         $ ".number_format($data['importe'], 2, ",", "."), 0, 2, 'L');
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(50, 6, "2do. Vencimiento", 0, 2, 'L');     
    $pdf->Cell(50, 6, $data['vencimiento2']."                                                        $ ".number_format($data['importe2'], 2, ",", "."), 0, 2, 'L');
    $pdf->Cell(50, 6, "Concepto:                                                 ".utf8_decode(utf8_decode($data['concepto'])), 0, 2, 'L');

}

//Pie 1
$pdf->SetFont('Arial','B',8);
$pdf->SetXY(20, 88);
$pdf->MultiCell(70, 4, utf8_decode("Ticket no válido como constancia de pago sin el comprobante de la Entidad Recaudadora adjunto."), 0, 'C');
$pdf->SetXY(40, 99);
$pdf->SetFont('Arial','',9);
$pdf->Cell(50, 6, "Recibo para el cliente", 0, 2, 'L');   

//Pie 2
$pdf->Code128(112,83,$data['cod_bar'],90,12);
$pdf->SetXY(120, 94);
$pdf->SetFont('Arial','',8);
$pdf->Cell(50, 6, $data['cod_bar'], 0, 2, 'L'); 
$pdf->SetXY(130, 99);
$pdf->SetFont('Arial','',9);
$pdf->Cell(50, 6, "Recibo para entidad recaudadora", 0, 2, 'L'); 

$pdf->Output('I', 'Cuota_'.$data['cuota'].'.pdf');