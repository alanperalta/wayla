<?php
    include 'includes/ConfigItrisWS.php';
    
    $data = array(
      "ID" => 1,
      "DESCRIPCION" => "Prueba"
    );
    $login = ItsLogin();
//    $result = ItsModifyData($login['usersession'], 'ERP_PAISES', "1", $data);
    $result = ItsDeleteData($login['usersession'], 'ERP_PAISES', "886");
//    ItsLogout($login['usersession']);
    echo $login['usersession'];
    echo $result['message'];
?>
