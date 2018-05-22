<?php
    require_once('./includes/ConfigItrisWS.php');
    session_start();
    
    $contrato = $_POST['contrato'];
    $clave = $_POST['clave'];
    
    $do_login = ItsLogin();
    
    $data = array();
    $data['error'] = '';
    if(!$do_login['error']){
        $_SESSION['userSession'] = $do_login['usersession'];
        $getDataResult = ItsGetData($do_login['usersession'], '_TUR_CONTRATOS', '1', "ID like '%".str_pad($contrato, 12, '0', STR_PAD_LEFT)."' AND ESTADO = 'A' AND CLAVE = '".$clave."'");
        if(!$getDataResult['error']){
            
            if(count($getDataResult['data']) > 0){
                $data['contrato'] = (string)$getDataResult['data'][0]['ID'];
                $data['encontrado'] = true;
            }else{
                $data['encontrado'] = false;
                ItsLogout($_SESSION['userSession']);
            }
        }else{
            $data['error'] = $getDataResult['message'];
            ItsLogout($_SESSION['userSession']);
        }
    
    }else{
        $data['error'] = $do_login['message'];
        ItsLogout($_SESSION['userSession']);
    }
    
    echo json_encode($data);
    
    
