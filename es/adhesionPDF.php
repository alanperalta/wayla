<?php
session_start();

use \setasign\Fpdi;

require_once('../includes/fpdf/fpdf.php');
require_once('../includes/fpdi/autoload.php');
require_once('../includes/ConfigItrisWS.php');

$data = array( );
$do_login = ItsLogin();
if(!$do_login['error']){
    $userSession = $do_login['usersession'];
    $pasajeroPDF = encriptado($_GET["pasajero"], 'd');
    $getDataResultPasajero = ItsGetData($userSession, '_TUR_PASAJEROS', '1', "ID='".$pasajeroPDF."'");
    
    if(!$getDataResultPasajero['error']) {
        if(count($getDataResultPasajero['data']) > 0){
            $data['nombre'] = (string)$getDataResultPasajero['data'][0]['NOMBRE'];
            $data['fec_nac'] = (string)$getDataResultPasajero['data'][0]['FEC_NAC'];
            $data['dni'] = (string)$getDataResultPasajero['data'][0]['NUM_DOC'];
            $data['calle'] = (string)$getDataResultPasajero['data'][0]['CALLE'];
            $data['numero'] = (double)$getDataResultPasajero['data'][0]['NUMERO'];
            $data['piso'] = (string)$getDataResultPasajero['data'][0]['PISO'];
            $data['dpto'] = (string)$getDataResultPasajero['data'][0]['DEPTO'];
            $data['localidad'] = (string)$getDataResultPasajero['data'][0]['LOCALIDAD_EXT'];
            $data['tel'] = (string)$getDataResultPasajero['data'][0]['TEL1'];
            $data['email'] = (string)$getDataResultPasajero['data'][0]['EMAIL1'];
            
            $getDataResultContrato = ItsGetData($userSession, '_TUR_CONTRATOS', '1', "ID='".(string)$getDataResultPasajero['data'][0]['FK_TUR_CONTRATOS']."'");
            if(!$getDataResultContrato['error']) {
                if(count($getDataResultContrato['data']) > 0){
                    $data['contrato'] = (string)$getDataResultContrato['data'][0]['NUM_COM'];
                    $data['destino'] = (string)$getDataResultContrato['data'][0]['Z_FK_ERP_ARTICULOS'];
                    $data['colegio'] = (string)$getDataResultContrato['data'][0]['DESCRIPCION'];
                    $data['division'] = (string)$getDataResultContrato['data'][0]['DIVISION'];
                    $data['periodo'] = (string)$getDataResultContrato['data'][0]['Z_FK_TUR_FEC_COM'];
                }else{
                    echo 'El pasajero no tiene contrato activo. Consulte a la administraci&oacute;n';
                    ItsLogout($userSession);
                    exit();
                }
            }else{
                ItsLogout($userSession);
                echo 'Error en el sistema, intente mas tarde.';
                exit();
            }
        }else{
            ItsLogout($userSession);
            echo 'Acceso incorrecto al sistema de cuotas. Vuelva a ingresar';
            exit();
        }
    }else{
        ItsLogout($userSession);
        echo 'Error en el sistema, intente mas tarde.';
        exit();
    }
    ItsLogout($userSession);
}else{
    echo 'Error en el sistema, intente mas tarde.';
    exit();
}

$pdf = new Fpdi\Fpdi();
$paginas = $pdf->setSourceFile('resources/adhesionES.pdf');
$pdf->SetFont('Arial', '', 10);

for($pagina = 1; $pagina <= $paginas ; $pagina++){
    $template = $pdf->importPage($pagina);

    $pdf->AddPage();
    $pdf->useTemplate($template, ['adjustPageSize' => true]);
    if($pagina == 1){
        $pdf->SetXY(57, 34);
        $pdf->Cell(100, 4, $data['contrato']);
        $pdf->SetXY(37, 44);
        $pdf->Cell(100, 4, utf8_decode($data['destino']));
        
        $pdf->SetXY(57, 65);
        $pdf->CellFitScale(75, 4, utf8_decode($data['colegio']));
        $pdf->SetXY(155, 65);
        $pdf->Cell(60, 4, $data['division']);
        $pdf->SetXY(50, 70);
        $pdf->Cell(100, 4, $data['periodo']);
        
        $pdf->SetXY(55, 90);
        $pdf->Cell(100, 4, utf8_decode($data['nombre']));
        $pdf->SetXY(57, 97);
        $pdf->Cell(60, 4, $data['fec_nac']);
        $pdf->SetXY(155, 97);
        $pdf->Cell(60, 4, $data['dni']);
        $pdf->SetXY(28, 104);
        $pdf->Cell(80, 4, utf8_decode($data['calle']));
        $pdf->SetXY(107, 104);
        $pdf->Cell(60, 4, $data['numero']);
        $pdf->SetXY(147, 104);
        $pdf->Cell(60, 4, $data['piso']);
        $pdf->SetXY(187, 104);
        $pdf->Cell(60, 4, $data['dpto']);
        $pdf->SetXY(80, 111);
        $pdf->CellFitScale(60, 4, utf8_decode($data['localidad']));
        $pdf->SetXY(160, 111);
        $pdf->Cell(60, 4, $data['tel']);
        $pdf->SetXY(57, 118);
        $pdf->Cell(60, 4, $data['email']);       
    }
}

$pdf->Output('I', 'Adhesion.pdf');