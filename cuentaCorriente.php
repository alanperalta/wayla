<?php
  require_once('includes/ConfigItrisWS.php');
  $client = new SoapClient($ws);
  $parametros = array(
        'DBName' => $db,
        'UserName' => $user,
        'UserPwd' => $password,
        'LicType' => 'WS',
        'UserSession' => ''
    );
  
    if(!isset($_POST['dni'])){
        $_SESSION['msj'] = 'Vuelva a ingresar su DNI';
        header('location: index.php');
        exit();
    }
    
    
  try{
  $do_login = $client->ItsLogin($parametros);
  }  catch (Exception $e){
      echo "Error en el sistema: ".$e->getMessage();
      exit();
  }
  $userSession = $do_login->UserSession;
  $error = $do_login->ItsLoginResult;
    if($error <> 1){
        session_start();
        
        //Asigno cookies
        if(!empty($_POST["recordar"])) {
            setcookie ("dni", $_POST["dni"], time()+ (10 * 365 * 24 * 60 * 60));
            setcookie ("recordar", 1, time()+ (10 * 365 * 24 * 60 * 60));
	} else {
            setcookie ("dni", "", time()+ (10 * 365 * 24 * 60 * 60));
            setcookie ("recordar", "", time()+ (10 * 365 * 24 * 60 * 60));
	}
                        
        $_SESSION['login'] = TRUE;
        $_SESSION['user'] = $user;
        $_SESSION['db'] = $db;
        $_SESSION['password'] = $password;
        $_SESSION['userSession'] = $do_login->UserSession;
        
        $dni = number_format(str_replace('.', '', $_POST['dni']), 0, '', '.');
        $paramData = array('UserSession' => $userSession,
 				'ItsClassName' => '_TUR_PASAJEROS',
 				'RecordCount' => 1,
 				'SQLFilter' => "NUM_DOC = '".$dni."'",
 				'SQLSort' => ''
                    
                );
        $get_data = $client->ItsGetData($paramData);
        if(!$get_data->ItsGetDataResult) {
            $getDataResult = simplexml_load_string($get_data->XMLData);
            
            if(count($getDataResult->ROWDATA->ROW) > 0) {
                $pasajero = (string)$getDataResult->ROWDATA->ROW[0]['ID'];
                $pasajeroPDF = encriptado($pasajero);
                ?>
         <html>
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
                <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
                <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/js/bootstrap.min.js"></script>
                <script src="js/cuentaCorriente.js"></script>
                <link href="css/cuentaCorriente.css" rel="stylesheet">
                <link href="css/footer.css" rel="stylesheet">
            </head>
            <body>
                <link rel="stylesheet" href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css">
                <div class="container">
                    <div class="logo">
                        <img src="resources/wayla.png"/>
                    </div>
                    <div class="row">
                        <div class="col-md-offset-8 col-md-4 formularioAdhesion">
                            <button id="descargarFormulario" class="btn" onclick="descargarFormulario('<?=$pasajeroPDF?>');">Descargar Formulario de adhesi&oacuten</button>
                        </div>
                    </div>
                    <?php 
                        $paramData = array('UserSession' => $userSession,
 				'ItsClassName' => '_TUR_CUOTAS_INF',
 				'RecordCount' => 100,
 				'SQLFilter' => "FK_TUR_PASAJEROS = '".$pasajero."'",
 				'SQLSort' => 'FK_TUR_CONTRATOS DESC, CUOTA ASC'
                    
                            );
                        $get_dataCuotas = $client->ItsGetData($paramData);
                        if(!$get_dataCuotas->ItsGetDataResult) {
                            $getDataResultCuotas = simplexml_load_string($get_dataCuotas->XMLData);
                            $contratoActual = '';
                            $i = 1;
                            $listaCuotas = $getDataResultCuotas->ROWDATA;
                            if(count($getDataResultCuotas->ROWDATA->ROW) > 0){
                                foreach ($getDataResultCuotas->ROWDATA->ROW as $cuota) {
                                    if($contratoActual != (string)$cuota['FK_TUR_CONTRATOS']){?>
                                        <div class="panel panel-default panel-cuotas">
                                            <div class="panel-heading">
                                                <div class="row">
                                                    <div class="col-md-8"
                                                        <h3 class="panel-title">Contrato: <?=$cuota['FK_TUR_CONTRATOS']." - ".$cuota['DES_PRODUCTO']?></h3>
                                                    </div>
                                                    <?php if($cuota['ESTADO_GEN'] == 'H'){?>
                                                        <div class="col-md-4">
                                                            <button class="btn btn-chequera pull-right" onclick="descargarChequera('<?=$cuota['FK_TUR_CONTRATOS']."', '".$pasajeroPDF?>');">Descargar chequera</button>
                                                        </div>
                                                    <?php }?>
                                                </div>
                                            </div>   
                                            <ul class="list-group">
                                      <?php foreach ($listaCuotas->ROW as $cuotaDetalle) {
                                                $hoy = new DateTime();
                                                $vencimiento2 = new DateTime(date('Y-m-d', strtotime($cuotaDetalle['FEC_VEN_2'])));
                                                if((string)$cuotaDetalle['FK_TUR_CONTRATOS'] == (string)$cuota['FK_TUR_CONTRATOS']){?>
                                                    <li class="list-group-item <?=($cuotaDetalle['ESTADO'] == 'P')?'lista-verde':''?>">
                                                        <div class="row toggle" id="dropdown-detail-<?=$i?>" data-toggle="detail-<?=$i?>">
                                                            <div class="col-xs-10">
                                                                <?="Cuota: ".$cuotaDetalle['CUOTA']." - Vencimiento: ".date('d/m/Y', strtotime($cuotaDetalle['FEC_VEN_1']))?>
                                                            </div>
                                                            <div class="col-xs-2"><i class="fa fa-chevron-down pull-right"></i></div>
                                                        </div>
                                                        <div id="detail-<?=$i?>">
                                                            <hr></hr>
                                                            <div class="container">
                                                                <div class="row">
                                                                    <div class="col-xs-6 col-md-4">
                                                                        Importe:
                                                                    </div>
                                                                    <div class="col-xs-6 col-md-4">
                                                                        <?="$ ".$cuotaDetalle['IMPORTE']?>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-xs-6 col-md-4">
                                                                        2do Vencimiento:
                                                                    </div>
                                                                    <div class="col-xs-6 col-md-4">
                                                                        <?=date('d/m/Y', strtotime($cuotaDetalle['FEC_VEN_2']))?>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-xs-6 col-md-4">
                                                                        Importe 2do vencimiento:
                                                                    </div>
                                                                    <div class="col-xs-6 col-md-4">
                                                                        <?="$ ".$cuotaDetalle['IMP_CON_REC']?>
                                                                    </div>
                                                                        <div class="col-md-2 hidden-xs hidden-sm">
                                                                        <?php if(($hoy <= $vencimiento2) && $cuotaDetalle['ESTADO'] == 'H' && $cuotaDetalle['COD_BAR'] != '') {?>
                                                                            <button class="btn btn-imprimir" onclick="descargarCuota('<?=$cuotaDetalle['NUM_COM']."', '".$pasajeroPDF?>')">Imprimir cup&oacute;n</button>
                                                                        <?php }else if($cuotaDetalle['ESTADO'] == 'I' || ($cuotaDetalle['ESTADO'] == 'H' && ($hoy > $vencimiento2) && $cuotaDetalle['COD_BAR'] != '')){?>
                                                                            <div class="cuota-vencida">Cuota vencida o plan ca&iacute;do, contacte a la administraci&oacuten.</div>
                                                                        <?php } else if($cuotaDetalle['COD_BAR'] == ''){?>
                                                                            <div>Esta cuota solo se puede pagar personalmente o por transferencia bancaria</div>
                                                                        <?php }?>
                                                                        </div>
                                                                </div>
                                                                <div class="row cuota-saldo">
                                                                    <div class="col-xs-6 col-md-4">
                                                                        Resta pagar:
                                                                    </div>
                                                                    <div class="col-xs-6 col-md-4">
                                                                        <?="$ ".$cuotaDetalle['SALDO']?>
                                                                    </div>
                                                                </div>
                                                                    <div class="row">
                                                                        <div class="col-xs-8 col-xs-offset-2 hidden-md hidden-lg div-imprimir">
                                                                         <?php if(($hoy <= $vencimiento2) && $cuotaDetalle['ESTADO'] == 'H' && $cuotaDetalle['COD_BAR']) {?>
                                                                            <button class="btn btn-imprimir" onclick="descargarCuota('<?=$cuotaDetalle['NUM_COM']?>')">Imprimir cup&oacute;n</button>
                                                                        <?php }else if($cuotaDetalle['ESTADO'] == 'I' || ($cuotaDetalle['ESTADO'] == 'H' && ($hoy > $vencimiento2) && $cuotaDetalle['COD_BAR'] != '')){?>
                                                                            <div class="cuota-vencida">Cuota vencida o plan ca&iacute;do, contacte a la administraci&oacuten.</div>
                                                                        <?php } else if($cuotaDetalle['COD_BAR'] == ''){?>
                                                                            <div>Esta cuota solo se puede pagar personalmente o por transferencia bancaria</div>
                                                                        <?php }?>
                                                                        </div>
                                                                    </div>
                                                            </div>
                                                        </div>
                                                    </li>
                                          <?php $i++;}
                                             }?>
                                            </ul>
                                        </div>
                            <?php   }
                                    $contratoActual = (string)$cuota['FK_TUR_CONTRATOS'];
                                }
                            }else{?>
                                <div class="sin-cuotas">Cuotas a&uacute;n no generadas, antes debe descargar y presentar el formulario de adhesi&oacute;n firmado.</div>
                            <?php }
                            //include 'includes/footer.php';
                        }else {
                            $client->ItsLogout(array('UserSession' => $userSession));
                            $_SESSION['msj'] = ItsError($client, $userSession);
                            header('location: index.php');
                        } ?>
                </div>
            </body>
        </html>
<?php
            $client->ItsLogout(array('UserSession' => $userSession));
            }else{
                $client->ItsLogout(array('UserSession' => $userSession));
                $_SESSION['msj'] = 'DNI no asociado a una cuenta, debe registrarse primero';
                header('location: index.php');
            }
        } else {
                $client->ItsLogout(array('UserSession' => $userSession));
                $_SESSION['msj'] = ItsError($client, $userSession);
                header('location: index.php');
        }
    }else{
        $_SESSION['msj'] = ItsError($client, $userSession);
        header('location: index.php');
    }
        
    