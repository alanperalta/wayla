<?php
    require_once('./includes/ConfigItrisWS.php');
    session_start();
    
    $contrato = $_POST['contrato'];
    $dni = str_replace('.', '', $_POST['dni']);
    $client = new SoapClient($ws);
    
    $data = array();
    $data['error'] = '';
    $paramData = array('UserSession' => $_SESSION['userSession'],
                            'ItsClassName' => '_TUR_PASAJEROS',
                            'RecordCount' => 1,
                            'SQLFilter' => "FK_TUR_CONTRATOS = '".$contrato."' AND NUM_DOC = '".number_format($dni, 0, '', '.')."'",
                            'SQLSort' => ''

            );
        $get_data = $client->ItsGetData($paramData);
        if(!$get_data->ItsGetDataResult){
            $getDataResult = simplexml_load_string($get_data->XMLData);
            
            if(count($getDataResult->ROWDATA->ROW) > 0){
                $data['encontrado'] = true;
            }else{
                $data['encontrado'] = false;
            }
        }else{
            $data['error'] = ItsError($client, $_SESSION['userSession']);
        }
    
    echo json_encode($data);
    
    
