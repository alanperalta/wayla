<?php
    include 'includes/ConfigItrisWS.php';
    
    $data = array(
      "ID" => 1,
      "DESCRIPCION" => "Prueba"
    );
    $login = ItsLogin();
//    $result = ItsModifyData($login['usersession'], 'ERP_PAISES', "1", $data);
    $result = ItsDeleteDataBy($login['usersession'], 'ERP_PAISES', "ID in ('86', '852')");
//    ItsLogout($login['usersession']);
    echo $login['usersession'];
    echo $result['message'];
?>
