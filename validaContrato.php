<?php
    require_once('./includes/ConfigItrisWS.php');
    session_start();
    
    $contrato = $_POST['contrato'];
    $clave = $_POST['clave'];
    $client = new SoapClient($ws);
    $parametros = array(
        'DBName' => $db,
        'UserName' => $user,
        'UserPwd' => $password,
        'LicType' => 'WS',
        'UserSession' => ''
    );
    
    $do_login = $client->ItsLogin($parametros);
    
    $data = array();
    $data['error'] = '';
    $do_login->ItsLoginResult;
    if($do_login->ItsLoginResult <> 1){
        $_SESSION['userSession'] = $do_login->UserSession;
        $paramData = array('UserSession' => $do_login->UserSession,
 				'ItsClassName' => '_TUR_CONTRATOS',
 				'RecordCount' => 1,
 				'SQLFilter' => "ID like '%".str_pad($contrato, 12, '0', STR_PAD_LEFT)."' AND ESTADO = 'A' AND CLAVE = '".$clave."'",
 				'SQLSort' => ''
                    
                );
        $get_data = $client->ItsGetData($paramData);
        if(!$get_data->ItsGetDataResult){
            $getDataResult = simplexml_load_string($get_data->XMLData);
            
            if(count($getDataResult->ROWDATA->ROW) > 0){
                $data['contrato'] = (string)$getDataResult->ROWDATA->ROW[0]['ID'];
                $data['encontrado'] = true;
            }else{
                $data['encontrado'] = false;
                $client->ItsLogout(array('UserSession' => $_SESSION['userSession']));
            }
        }else{
            $data['error'] = ItsError($client, $do_login->UserSession);
            $client->ItsLogout(array('UserSession' => $_SESSION['userSession']));
        }
    
    }else{
        $data['error'] = ItsError($client, $do_login->UserSession);
        $client->ItsLogout(array('UserSession' => $_SESSION['userSession']));
    }
    
    echo json_encode($data);
    
    
