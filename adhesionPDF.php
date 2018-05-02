<?php
session_start();

use \setasign\Fpdi;

require_once('includes/fpdf/fpdf.php');
require_once('includes/fpdi/autoload.php');
require_once('includes/ConfigItrisWS.php');
        
$client = new SoapClient($ws);
$parametros = array(
      'DBName' => $db,
      'UserName' => $user,
      'UserPwd' => $password,
      'LicType' => 'WS',
      'UserSession' => ''
  );

$data = array( );
$do_login = $client->ItsLogin($parametros);
$userSession = $do_login->UserSession;
$error = $do_login->ItsLoginResult;
if($error <> 1){
    $pasajeroPDF = encriptado($_GET["pasajero"], 'd');
        $paramData = array('UserSession' => $userSession,
 				'ItsClassName' => '_TUR_PASAJEROS',
 				'RecordCount' => 1,
 				'SQLFilter' => "ID = '".$pasajeroPDF."'",
 				'SQLSort' => ''
                    
                );
    $get_dataPasajero = $client->ItsGetData($paramData);
    if(!$get_dataPasajero->ItsGetDataResult) {
        $getDataResultPasajero = simplexml_load_string($get_dataPasajero->XMLData);
        if(count($getDataResultPasajero->ROWDATA->ROW) > 0){
            $data['nombre'] = (string)$getDataResultPasajero->ROWDATA->ROW[0]['NOMBRE'];
            $data['fec_nac'] = date('d/m/Y', strtotime((string)$getDataResultPasajero->ROWDATA->ROW[0]['FEC_NAC']));
            $data['dni'] = (string)$getDataResultPasajero->ROWDATA->ROW[0]['NUM_DOC'];
            $data['calle'] = (string)$getDataResultPasajero->ROWDATA->ROW[0]['CALLE'];
            $data['numero'] = (double)$getDataResultPasajero->ROWDATA->ROW[0]['NUMERO'];
            $data['piso'] = (string)$getDataResultPasajero->ROWDATA->ROW[0]['PISO'];
            $data['dpto'] = (string)$getDataResultPasajero->ROWDATA->ROW[0]['DEPTO'];
            $data['cp'] = (string)$getDataResultPasajero->ROWDATA->ROW[0]['CP'];
            $data['localidad'] = (string)$getDataResultPasajero->ROWDATA->ROW[0]['Z_FK_ERP_LOCALIDADES'];
            $data['tel'] = (string)$getDataResultPasajero->ROWDATA->ROW[0]['TEL1'];
            $data['email'] = (string)$getDataResultPasajero->ROWDATA->ROW[0]['EMAIL1'];
            $data['nombre_RL'] = (string)$getDataResultPasajero->ROWDATA->ROW[0]['NOMBRE_RL'];
            $data['dni_RL'] = (string)$getDataResultPasajero->ROWDATA->ROW[0]['NUM_DOC_RL'];
            $data['tel_RL'] = (string)$getDataResultPasajero->ROWDATA->ROW[0]['TEL2'];
            $data['email_RL'] = (string)$getDataResultPasajero->ROWDATA->ROW[0]['EMAIL2'];
            
            $paramData = array('UserSession' => $userSession,
 				'ItsClassName' => '_TUR_CONTRATOS',
 				'RecordCount' => 1,
 				'SQLFilter' => "ID = '".(string)$getDataResultPasajero->ROWDATA->ROW[0]['FK_TUR_CONTRATOS']."'",
 				'SQLSort' => ''
                    
                );
            $get_dataContrato = $client->ItsGetData($paramData);
            if(!$get_dataContrato->ItsGetDataResult) {
                $getDataResultContrato = simplexml_load_string($get_dataContrato->XMLData);
                if(count($getDataResultContrato->ROWDATA->ROW) > 0){
                    $data['contrato'] = (string)$getDataResultContrato->ROWDATA->ROW[0]['NUM_COM'];
                    $data['destino'] = (string)$getDataResultContrato->ROWDATA->ROW[0]['Z_FK_TUR_PRODUCTOS'];
                    $data['colegio'] = (string)$getDataResultContrato->ROWDATA->ROW[0]['DESCRIPCION'];
                    $data['division'] = (string)$getDataResultContrato->ROWDATA->ROW[0]['DIVISION'];
                    $data['periodo'] = (string)$getDataResultContrato->ROWDATA->ROW[0]['Z_FK_TUR_FEC_COM'];
                }else{
                    echo 'El pasajero no tiene contrato activo. Consulte a la administraci&oacute;n';
                    $client->ItsLogout(array('UserSession' => $userSession));
                    exit();
                }
            }else{
                $client->ItsLogout(array('UserSession' => $userSession));
                echo 'Error en el sistema, intente mas tarde.';
                exit();
            }
        }else{
            $client->ItsLogout(array('UserSession' => $userSession));
            echo 'Acceso incorrecto al sistema de cuotas. Vuelva a ingresar';
            exit();
        }
    }else{
        $client->ItsLogout(array('UserSession' => $userSession));
        echo 'Error en el sistema, intente mas tarde.';
        exit();
    }
    $client->ItsLogout(array('UserSession' => $userSession));
}else{
    echo 'Error en el sistema, intente mas tarde.';
    exit();
}

$pdf = new Fpdi\Fpdi();
$paginas = $pdf->setSourceFile('resources/adhesion.pdf');
$pdf->SetFont('Arial', '', 10);

for($pagina = 1; $pagina <= $paginas ; $pagina++){
    $template = $pdf->importPage($pagina);

    $pdf->AddPage();
    $pdf->useTemplate($template, ['adjustPageSize' => true]);
    if($pagina == 1){
        $pdf->SetXY(57, 34);
        $pdf->Cell(100, 4, $data['contrato']);
        $pdf->SetXY(37, 44);
        $pdf->Cell(100, 4, $data['destino']);
        
        $pdf->SetXY(57, 65);
        $pdf->CellFitScale(75, 4, $data['colegio']);
        $pdf->SetXY(155, 65);
        $pdf->Cell(60, 4, $data['division']);
        $pdf->SetXY(50, 70);
        $pdf->Cell(100, 4, $data['periodo']);
        
        $pdf->SetXY(55, 90);
        $pdf->Cell(100, 4, $data['nombre']);
        $pdf->SetXY(57, 97);
        $pdf->Cell(60, 4, $data['fec_nac']);
        $pdf->SetXY(155, 97);
        $pdf->Cell(60, 4, $data['dni']);
        $pdf->SetXY(28, 104);
        $pdf->Cell(80, 4, $data['calle']);
        $pdf->SetXY(107, 104);
        $pdf->Cell(60, 4, $data['numero']);
        $pdf->SetXY(147, 104);
        $pdf->Cell(60, 4, $data['piso']);
        $pdf->SetXY(187, 104);
        $pdf->Cell(60, 4, $data['dpto']);
        $pdf->SetXY(34, 111);
        $pdf->Cell(60, 4, $data['cp']);
        $pdf->SetXY(80, 111);
        $pdf->CellFitScale(60, 4, $data['localidad']);
        $pdf->SetXY(160, 111);
        $pdf->Cell(60, 4, $data['tel']);
        $pdf->SetXY(57, 118);
        $pdf->Cell(60, 4, $data['email']);
        
        $pdf->SetXY(55, 190);
        $pdf->Cell(100, 4, $data['nombre_RL']);
        $pdf->SetXY(107, 197);
        $pdf->Cell(60, 4, "DNI: ".$data['dni_RL']);
        $pdf->SetXY(37, 204);
        $pdf->Cell(60, 4, $data['tel_RL']);
        $pdf->SetXY(160, 204);
        $pdf->CellFitScale(52, 4, $data['email_RL']);
       
    }
}

$pdf->Output('I', 'Adhesion.pdf');