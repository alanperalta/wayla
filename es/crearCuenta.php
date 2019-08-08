<?php
  require_once('../includes/ConfigItrisWS.php');
  session_start();
  
  if(isset($_SESSION['userSession'])){
        
        $userSession = $_SESSION['userSession'];

                    $dataset = array();
                    $campos = array_keys($_POST);
                    foreach ($campos as $campo) {
                        //Guardo en session por si hay algún error y tengo que rellenar el formulario
                        $_SESSION[$campo] = utf8_encode($_POST[$campo]);
                        //No tiene que contemplar los campos que son solo descriptivos, el valor esta en un input hidden
                        if(!strpos($campo, 'DESC')){
                            if($campo == 'FK_ERP_TIP_DOC'){
                                $dataset[$campo] = (int)$_POST[$campo];
                            }elseif($campo == 'CARGA_WEB'){
                                $dataset[$campo] = (bool)$_POST[$campo];
                            }elseif ($campo == 'NUM_DOC') {
                                $dataset[$campo] = strtoupper($_POST[$campo]);
                            }
                            else{
                                $dataset[$campo]= utf8_encode($_POST[$campo]);
                            }
                        }
                    }

                        $post = ItsPostData($userSession, '_TUR_PASAJEROS', $dataset);
                        if(!$post['error']) {
                            ItsLogout($userSession);
                            session_unset();
                            $_SESSION['msj'] = 'Cuenta creada. Ingrese con su NIE.';
                            header('location: index.php');
                        } else {
                            $_SESSION['msj'] = $post['message'];
                            ItsLogout($_SESSION['userSession']);
                            header('location: index.php');
                        }
            }
