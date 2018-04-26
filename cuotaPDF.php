<?php
session_start();

require_once('includes/fpdf/code128.php');
require_once('includes/ConfigItrisWS.php');
        
$client = new SoapClient($ws);
$parametros = array(
      'DBName' => $db,
      'UserName' => $user,
      'UserPwd' => $password,
      'LicType' => 'WS',
      'UserSession' => ''
  );

$data = array();
$do_login = $client->ItsLogin($parametros);
$error = $do_login->ItsLoginResult;
if($error <> 1){
    $userSession = $do_login->UserSession;
    $pasajeroPDF = encriptado($_GET["pasajero"], 'd');
    
    echo 'n: '.$_GET['numero']." p: ".$pasajeroPDF;
    exit();
    $paramData = array('UserSession' => $userSession,
 				'ItsClassName' => '_TUR_CUOTAS_INF',
 				'RecordCount' => 1,
 				'SQLFilter' => "NUM_COM = '".$_GET['numero']."' AND FK_TUR_PASAJEROS = ".$pasajeroPDF,
 				'SQLSort' => ''
                    
                            );
    $get_dataCuotas = $client->ItsGetData($paramData);
    if(!$get_dataCuotas->ItsGetDataResult) {
        $getDataResult = simplexml_load_string($get_dataCuotas->XMLData);
        if(count($getDataResult->ROWDATA->ROW) > 0){
            $data['numero'] = (string)$getDataResult->ROWDATA->ROW[0]['NUM_COM'];
            $data['colegio'] = (string)$getDataResult->ROWDATA->ROW[0]['COLEGIO'];
            $data['pasajero'] = (string)$getDataResult->ROWDATA->ROW[0]['Z_FK_TUR_PASAJEROS'];
            $data['vencimiento'] = (string)$getDataResult->ROWDATA->ROW[0]['FEC_VEN_1'];
            $data['importe'] = (double)$getDataResult->ROWDATA->ROW[0]['IMPORTE'];
            $data['vencimiento2'] = (string)$getDataResult->ROWDATA->ROW[0]['FEC_VEN_2'];
            $data['importe2'] = (double)$getDataResult->ROWDATA->ROW[0]['IMP_CON_REC'];
            $data['concepto'] = (string)$getDataResult->ROWDATA->ROW[0]['Z_TIPO'];
            $data['cod_bar'] = (string)$getDataResult->ROWDATA->ROW[0]['COD_BAR'];
            $data['cuota'] = (string)$getDataResult->ROWDATA->ROW[0]['CUOTA'];
        }else{
            echo 'Acceso incorrecto al sistema de cuotas. Vuelva a ingresar';
            $client->ItsLogout(array('UserSession' => $userSession));
            exit();
        }
    }
    $client->ItsLogout(array('UserSession' => $userSession));
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
    $pdf->Image('resources/wayla.png', $X, 5, 40);
    
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
    $pdf->Cell(50, 6, utf8_decode(utf8_decode($data['pasajero'])), 0, 2, 'L');
    $pdf->SetFont('Arial', 'B', '12');
    $pdf->Cell(50, 6, date('d/m/Y', strtotime($data['vencimiento']))."                                         $ ".number_format($data['importe'], 2, ",", "."), 0, 2, 'L');
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(50, 6, "2do. Vencimiento", 0, 2, 'L');     
    $pdf->Cell(50, 6, date('d/m/Y', strtotime($data['vencimiento2']))."                                                        $ ".number_format($data['importe2'], 2, ",", "."), 0, 2, 'L');
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