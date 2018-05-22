<?php
require_once('./includes/ConfigItrisWS.php');
session_start();

if(isset($_SESSION['userSession'])) {

    $userSession = $_SESSION['userSession'];
    $getDataResult = ItsGetData($userSession, 'ERP_LOCALIDADES', '20', "DESCRIPCION LIKE '%".$_POST['clave']."%' OR _ALIAS LIKE '%".$_POST['clave']."%'", 'DESCRIPCION ASC');
    if(!$getDataResult['error']) {
        $data = array();
        
        foreach ($getDataResult['data'] as $row){
            $data[] = array('ID' => (string)$row['ID'], 'DESCRIPCION' => (string)$row['DESCRIPCION'], 'PARTIDO' => ucfirst(strtolower((string)$row['Z_FK_ERP_PARTIDOS'])), 'PROVINCIA' => (string)$row['Z_FK_ERP_PROVINCIAS']);
        }
        $json = json_encode($data);
        echo $json;

    } else {
        echo $getDataResult['message'];
        exit();
    }
} else {
    echo ('Sesi&oacute;n finalizada, debe volver a loguearse.');
    exit();
}

