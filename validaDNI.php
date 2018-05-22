<?php
    require_once('./includes/ConfigItrisWS.php');
    session_start();
    
    $contrato = $_POST['contrato'];
    $dni = str_replace('.', '', $_POST['dni']);
    
    $data = array();
    $data['error'] = '';
        $getDataResult = ItsGetData($_SESSION['userSession'], '_TUR_PASAJEROS', '1', "FK_TUR_CONTRATOS = '".$contrato."' AND NUM_DOC = '".$dni."'");
        if(!$getDataResult['error']){
            
            if(count($getDataResult['data']) > 0){
                $data['encontrado'] = true;
            }else{
                $data['encontrado'] = false;
            }
        }else{
            $data['error'] = $getDataResult['message'];
        }
    
    echo json_encode($data);
    
    
