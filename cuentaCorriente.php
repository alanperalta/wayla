<?php
  require_once('includes/ConfigItrisWS.php');
  
    if(!isset($_POST['dni'])){
        $_SESSION['msj'] = 'Vuelva a ingresar su DNI';
        header('location: index.php');
        exit();
    }
    
    
  try{
  $do_login = ItsLogin();
  }  catch (Exception $e){
      echo "Error en el sistema: ".$e->getMessage();
      exit();
  }
    if(!$do_login['error']){
        session_start();
        $userSession = $do_login['usersession'];
        
        //Asigno cookies
        if(!empty($_POST["recordar"])) {
            setcookie ("dni", $_POST["dni"], time()+ (10 * 365 * 24 * 60 * 60));
	} else {
            setcookie ("dni", "", time()+ (10 * 365 * 24 * 60 * 60));
	}
        
        $dni = str_replace('.', '', $_POST['dni']);
        $getDataResult = ItsGetData($userSession, '_TUR_PASAJEROS', '1', "REPLACE(NUM_DOC, '.', '')='".$dni."'");
        if(!$getDataResult['error']) {
            
            if(count($getDataResult['data']) > 0) {
                $pasajero = (string)$getDataResult['data'][0]['ID'];
                $pasajeroPDF = encriptado($pasajero);
                ?>
         <html>
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
                <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
                <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/js/bootstrap.min.js"></script>
                <script src="includes/jQuery-ui/jquery-ui.min.js"></script>
                <link href="includes/jQuery-ui/jquery-ui.min.css" rel="stylesheet">
                <link href="includes/jQuery-ui/jquery-ui.theme.min.css" rel="stylesheet">
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
                        $getDataResultCuotas = ItsGetData($userSession, '_TUR_CUOTAS_INF', '100', "FK_TUR_PASAJEROS='".$pasajero."'", 'FK_TUR_CONTRATOS DESC, FEC_VEN_1 ASC');
                        if(!$getDataResultCuotas['error']) {
                            $contratoActual = '';
                            $i = 1;
                            $listaCuotas = $getDataResultCuotas['data'];
                            if(count($getDataResultCuotas['data']) > 0){
                                foreach ($getDataResultCuotas['data'] as $cuota) {
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
                                      <?php foreach ($listaCuotas as $cuotaDetalle) {
                                                $hoy = new DateTime(date('Y-m-d'));
                                                $vencimiento2 = new DateTime(date('Y-m-d', strtotime(str_replace("/", "-", $cuotaDetalle['FEC_VEN_2']))));
                                                if((string)$cuotaDetalle['FK_TUR_CONTRATOS'] == (string)$cuota['FK_TUR_CONTRATOS']){?>
                                                    <li class="list-group-item <?=($cuotaDetalle['ESTADO'] == 'P')?'lista-verde':''?>">
                                                        <div class="row toggle" id="dropdown-detail-<?=$i?>" data-toggle="detail-<?=$i?>">
                                                            <div class="col-xs-10">
                                                                <?=(($cuotaDetalle['CUOTA'] == 0 || $cuotaDetalle['CUOTA'] >= 50)?$cuotaDetalle['Z_TIPO']:"Cuota: ".$cuotaDetalle['CUOTA'])." - Vencimiento: ".$cuotaDetalle['FEC_VEN_1']?>
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
                                                                        <?=$cuotaDetalle['FEC_VEN_2']?>
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
                                                                    <?php } else if($cuotaDetalle['COD_BAR'] == '' && $cuotaDetalle['ESTADO'] == 'H'){?>
                                                                        <button class="btn btn-imprimir" onclick="infoCuota()">Ver info</button>
                                                                    <?php }?>
                                                                    </div>
                                                                </div>
                                                                
                                                                <div class="row">
                                                                    <?php if($cuotaDetalle['OBSERVACIONES'] != '' && $cuotaDetalle['TIPO'] != 'N'){?>
                                                                        <div class="col-xs-6 col-md-4">
                                                                            Observaciones:
                                                                        </div>
                                                                        <div class="col-xs-6 col-md-4">
                                                                            <?=$cuotaDetalle['OBSERVACIONES']?>
                                                                        </div>
                                                                    <?php }?>
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
                                                                            <button class="btn btn-imprimir" onclick="descargarCuota('<?=$cuotaDetalle['NUM_COM']."', '".$pasajeroPDF?>');">Imprimir cup&oacute;n</button>
                                                                        <?php }else if($cuotaDetalle['ESTADO'] == 'I' || ($cuotaDetalle['ESTADO'] == 'H' && ($hoy > $vencimiento2) && $cuotaDetalle['COD_BAR'] != '')){?>
                                                                            <div class="cuota-vencida">Cuota vencida o plan ca&iacute;do, contacte a la administraci&oacuten.</div>
                                                                        <?php } else if($cuotaDetalle['COD_BAR'] == '' && $cuotaDetalle['ESTADO'] == 'H'){?>
                                                                            <button class="btn btn-imprimir" onclick="infoCuota()">Ver info</button>
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
                            ItsLogout($userSession);
                            $_SESSION['msj'] = $getDataResultCuotas['message'];
                            header('location: index.php');
                        } ?>
                </div>
                
                <div id="infoCuota" title="Info de pago" style="display:none">
                    <p>
                        Abonar mediante transferencia bancaria</br>

                        Banco Galicia</br>

                        Cta. Cte. N° 1447-8186-3</br>

                        CBU 0070186620000001447839</br>

                        Banco Santander</br>

                        Cta. Cte. N° 523-16/1</br>

                        CBU 0720523120000000001618</br>

                        Titular: Wayla Turismo SRL</br>

                        Cuit: 30-71509621-4</br>

                        Enviar comprobante: pagos@waylaturismo.com.ar</br>

                        Indicando pasajero y colegio para su acreditación.</br>

                        Muchas gracias
                    </p>
                </div>
            </body>
        </html>
<?php
            ItsLogout($userSession);
            }else{
                ItsLogout($userSession);
                $_SESSION['msj'] = 'DNI no asociado a una cuenta, debe registrarse primero';
                header('location: index.php');
            }
        } else {
                ItsLogout($userSession);
                $_SESSION['msj'] = $getDataResult['message'];
                header('location: index.php');
        }
    }else{
        $_SESSION['msj'] = $do_login['message'];
        header('location: index.php');
    }
        
    