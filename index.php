<?php session_start();?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
        <link href="css/login.css" rel="stylesheet">
        <script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
        <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/js/bootstrap.min.js"></script>
        <script src="js/notify.min.js"></script>
        <script src="includes/jQuery-ui/jquery-ui.min.js"></script>
        <link href="includes/jQuery-ui/jquery-ui.min.css" rel="stylesheet">
        <link href="includes/jQuery-ui/jquery-ui.theme.min.css" rel="stylesheet">
        <script src="js/login.js"></script>
        <script src="js/jquery.validate.min.js"></script>
        <script src="js/messages_es_AR.min.js"></script>
        <link href="includes/font-awesome/css/font-awesome.min.css" rel="stylesheet">
        <link href="css/footer.css" rel="stylesheet">
        <title>Sistema de pasajeros - Wayla</title>
        <?php if (isset($_SESSION['msj']) && $_SESSION['msj'] != ''){?>
        <script type="text/javascript">
            $(document).ready(function(){
                $.notify('<?=$_SESSION["msj"]?>',{
                    globalPosition: 'top left',
                    className: 'warm',
                    autoHide: false
                });
            });
        </script>
        <?php $_SESSION['msj'] = '';}?>
    </head>
    <body>
        <div class="container">
            <div class="row">
                <div class="logo">
                    <img src="resources/wayla.png"/>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 col-md-offset-3">
                    <div class="panel panel-login">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-6">
                                    <a href="#" <?=(!isset($_SESSION['FK_TUR_CONTRATOS']))?'class="active"':''?> id="login-form-link">Ingresar</a>
                                </div>
                                <div class="col-xs-6">
                                    <a href="#" <?=(isset($_SESSION['FK_TUR_CONTRATOS']))?'class="active"':''?> id="register-form-link">Registrarse</a>
                                </div>
                            </div>
                            <hr>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <form id="login-form" action="cuentaCorriente.php" method="post" role="form" <?=(isset($_SESSION['FK_TUR_CONTRATOS']))?'style="display: none;"':'style="display: block;"'?>>
                                        <div class="form-group">
                                            <input type="text" name="dni" id="dni" tabindex="1" class="form-control" placeholder="DNI del pasajero" value="<?=(isset($_COOKIE['dni']))?$_COOKIE['dni']:''?>" required>
                                        </div>
                                        <div class="form-group text-center">
                                            <input type="checkbox" tabindex="3" class="" name="recordar" id="recordar" <?=(isset($_COOKIE['recordar']))?'checked':''?>>
                                            <label for="recordar"> Recordarme</label>
                                        </div>
                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-sm-6 col-sm-offset-3">
                                                    <button onclick="ingresar(event);" id="login-submit" tabindex="4" class="form-control btn btn-login">Ingresar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    <form id="register-form" action="crearCuenta.php" method="post" role="form" <?=(!isset($_SESSION['FK_TUR_CONTRATOS']))?'style="display: none;"':'style="display: block;"'?>>
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <h4>Contrato</h4>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <input type="text" name="FK_TUR_CONTRATOS" id="FK_TUR_CONTRATOS" value="<?=(isset($_SESSION['FK_TUR_CONTRATOS']))?$_SESSION['FK_TUR_CONTRATOS']:''?>" tabindex="1" class="form-control" placeholder="Nro. de contrato" onkeypress="capturaTecla(event);" required>
                                        </div>
                                        <div class="form-group">
                                            <input type="text" name="CLAVE_DESC" id="CLAVE_DESC" tabindex="1" class="form-control" placeholder="Clave de verificaci&oacute;n" onkeypress="capturaTecla(event);" required autocomplete="off">
                                        </div>
                                        <div class="col-sm-6 col-sm-offset-3">
                                            <button name="btn-validar" id="btn-validar" tabindex="4" class="form-control btn btn-validar" onclick="validaContrato(event);">Validar contrato</button>
                                        </div>
                                        <div id="campos-registro" style="display:none">
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <h4>Datos del pasajero</h4>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <input type="text" name="NOMBRE" id="NOMBRE" value="<?=(isset($_SESSION['NOMBRE']))?$_SESSION['NOMBRE']:''?>" tabindex="1" class="form-control" placeholder="Apellido y nombre" required>
                                            </div>
                                            <div class="form-group">
                                                <input type="text" name="NUM_DOC" id="NUM_DOC" value="<?=(isset($_SESSION['NUM_DOC']))?$_SESSION['NUM_DOC']:''?>" tabindex="1" class="form-control" placeholder="Nro. documento" onblur="validaDNI()" required title="Ingrese un documento v&aacute;lido" pattern="^\d{1,3}[.]?\d{3}[.]?\d{3}$">
                                            </div>
                                            <div class="form-group">
                                                <input type="text" name="FECHA_DESC" placeholder="Fecha de nacimiento" class="form-control readonly" id="FECHA_DESC" required="" value="<?=(isset($_SESSION['FECHA_DESC']))?$_SESSION['FECHA_DESC']:''?>">
                                                <input type="hidden" name="FEC_NAC" id="FEC_NAC" value="<?=(isset($_SESSION['FEC_NAC']))?$_SESSION['FEC_NAC']:''?>">
                                            </div>
                                            <div class="form-group">
                                                <select name="SEXO" id="SEXO" tabindex="1" class="form-control" placeholder="Sexo" required>
                                                    <option value="M">Masculino</option>
                                                    <option <?=(isset($_SESSION['SEXO']) && $_SESSION['SEXO'] == 'F?')?'selected':''?>value="F">Femenino</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <input type="text" name="NUM_PAS" id="NUM_PAS" value="<?=(isset($_SESSION['NUM_PAS']))?$_SESSION['NUM_PAS']:''?>" tabindex="1" class="form-control" placeholder="Nro. de pasaporte">
                                            </div>
                                            <div class="form-group">
                                                <input type="text" name="CALLE" value="<?=(isset($_SESSION['CALLE']))?$_SESSION['CALLE']:''?>" id="DIRECCION" tabindex="1" class="form-control" placeholder="Calle">
                                            </div>
                                            <div class="form-group">
                                                <input type="text" name="NUMERO" id="NUMERO" value="<?=(isset($_SESSION['NUMERO']))?$_SESSION['NUMERO']:''?>" tabindex="1" class="form-control" placeholder="N&uacute;mero" required>
                                            </div>
                                            <div class="form-group">
                                                <input type="text" name="DEPTO" id="DEPTO" value="<?=(isset($_SESSION['DEPTO']))?$_SESSION['DEPTO']:''?>" tabindex="1" class="form-control" placeholder="Dpto">
                                            </div>
                                            <div class="form-group">
                                                <input type="text" name="PISO" id="PISO" value="<?=(isset($_SESSION['PISO']))?$_SESSION['PISO']:''?>" tabindex="1" class="form-control" placeholder="Piso">
                                            </div>
                                            <div class="form-group">
                                                <input type="text" onclick="popupLocalidades(event)" name="FK_ERP_LOCALIDADES_DESC" id="FK_ERP_LOCALIDADES_DESC" value="<?=(isset($_SESSION['FK_ERP_LOCALIDADES_DESC']))?$_SESSION['FK_ERP_LOCALIDADES_DESC']:''?>" tabindex="1" class="form-control form-localidad readonly" placeholder="Seleccione una localidad" required autocomplete="off">
                                            </div>
                                            <div class="form-group">
                                                <input type="text" name="CP" id="CP" value="<?=(isset($_SESSION['CP']))?$_SESSION['CP']:''?>" tabindex="1" class="form-control" placeholder="C&oacute;d. postal">
                                            </div>
                                            <div class="form-group">
                                                <input type="tel" name="TEL1" id="TEL1" value="<?=(isset($_SESSION['TEL1']))?$_SESSION['TEL1']:''?>" tabindex="1" class="form-control" placeholder="Tel&eacute;fono">
                                            </div>
                                            <div class="form-group">
                                                <input type="email" name="EMAIL1" id="EMAIL1" value="<?=(isset($_SESSION['EMAIL1']))?$_SESSION['EMAIL1']:''?>" tabindex="1" class="form-control" placeholder="E-mail" required>
                                            </div>
                                            <hr>
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <h4>Datos de facturación/Resp. Legal</h4>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <input type="text" name="NOMBRE_RL" id="NOMBRE_RL" value="<?=(isset($_SESSION['NOMBRE_RL']))?$_SESSION['NOMBRE_RL']:''?>" tabindex="1" class="form-control" placeholder="Apellido y nombre" required>
                                            </div>
                                            <div class="form-group">
                                                <input type="text" name="NUM_DOC_RL" id="NUM_DOC_RL" value="<?=(isset($_SESSION['NUM_DOC_RL']))?$_SESSION['NUM_DOC_RL']:''?>" tabindex="1" class="form-control" placeholder="Nro. documento" required title="Ingrese un documento v&aacute;lido" pattern="^\d{1,3}[.]?\d{3}[.]?\d{3}$">
                                            </div>
                                            <div class="form-group">
                                                <input type="text" name="CALLE_RL" id="CALLE_RL" value="<?=(isset($_SESSION['CALLE_RL']))?$_SESSION['CALLE_RL']:''?>" tabindex="1" class="form-control" placeholder="Calle" required>
                                            </div>
                                            <div class="form-group">
                                                <input type="text" name="NUMERO_RL" id="NUMERO_RL" value="<?=(isset($_SESSION['NUMERO_RL']))?$_SESSION['NUMERO_RL']:''?>" tabindex="1" class="form-control" placeholder="N&uacute;mero" required>
                                            </div>
                                            <div class="form-group">
                                                <input type="text" onclick="popupLocalidadesRL(event)" name="FK_ERP_LOCALIDADES_RL_DESC" id="FK_ERP_LOCALIDADES_RL_DESC" value="<?=(isset($_SESSION['FK_ERP_LOCALIDADES_RL_DESC']))?$_SESSION['FK_ERP_LOCALIDADES_RL_DESC']:''?>" tabindex="1" class="form-control form-localidad readonly" placeholder="Seleccione una localidad" required autocomplete="off">
                                            </div>
                                            <div class="form-group">
                                                <input type="text" name="CP_RL" id="CP_RL" value="<?=(isset($_SESSION['CP_RL']))?$_SESSION['CP_RL']:''?>" tabindex="1" class="form-control" placeholder="C&oacute;d. postal">
                                            </div>
                                            <div class="form-group">
                                                <input type="text" name="DEPTO_RL" id="DEPTO_RL" value="<?=(isset($_SESSION['DEPTO_RL']))?$_SESSION['DEPTO_RL']:''?>" tabindex="1" class="form-control" placeholder="Dpto">
                                            </div>
                                            <div class="form-group">
                                                <input type="text" name="PISO_RL" id="PISO_RL" value="<?=(isset($_SESSION['PISO_RL']))?$_SESSION['PISO_RL']:''?>" tabindex="1" class="form-control" placeholder="Piso">
                                            </div>
                                            <div class="form-group">
                                                <input type="tel" name="TEL2" id="TEL2" value="<?=(isset($_SESSION['TEL2']))?$_SESSION['TEL2']:''?>" tabindex="1" class="form-control" placeholder="Tel." required>
                                            </div>
                                            <div class="form-group">
                                                <input type="email" name="EMAIL2" id="EMAIL2" value="<?=(isset($_SESSION['EMAIL2']))?$_SESSION['EMAIL2']:''?>" tabindex="1" class="form-control" placeholder="E-mail" required>
                                            </div>
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-sm-6 col-sm-offset-3">
                                                        <button id="register-submit" onclick="registrar();" tabindex="4" class="form-control btn btn-register" disabled>Registrarse</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <input type="hidden" name="FK_ERP_TIP_DOC" id="FK_ERP_TIP_DOC" value="96"/>
                                        <input type="hidden" name="FK_ERP_TIP_DOC_RL" id="FK_ERP_TIP_DOC_RL" value="96"/>
                                        <input type="hidden" name="FK_ERP_LOCALIDADES" id="FK_ERP_LOCALIDADES" value="<?=(isset($_SESSION['FK_ERP_LOCALIDADES']))?$_SESSION['FK_ERP_LOCALIDADES']:''?>"/>
                                        <input type="hidden" name="FK_ERP_LOCALIDADES_RL" id="FK_ERP_LOCALIDADES_RL" value="<?=(isset($_SESSION['FK_ERP_LOCALIDADES_RL']))?$_SESSION['FK_ERP_LOCALIDADES_RL']:''?>"/>
                                        <!--Este campo es para determinar si el modal completa localidad o localidad_RL-->
                                        <input type="hidden" id="tipoLocalidad"/> 
                                        <input type="hidden" name="CARGA_WEB" id="CARGA_WEB" value="1"/>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php //include 'includes/footer.php';?>
        </div>
        
        <!-- Modal -->
        <div class="modal fade" id="modal-localidades" role="dialog">
          <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 style="color:red;">Seleccione la localidad</h4>
                </div>
                <div class="modal-body">
                    <input type="text" name="modal-input-localidad" placeholder="Ingrese localidad y presione enter..." class="form-control" id="modal-input-localidad" onkeypress="buscarLocalidad(event)">
                    <ul id="lista-localidades">

                    </ul>
                </div>
            </div>
          </div>
        </div>
    </body>
</html>
