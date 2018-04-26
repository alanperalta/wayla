<?php
  require_once('includes/ConfigItrisWS.php');
  session_start();
  $client = new SoapClient($ws);
  $parametros = array(
        'DBName' => $db,
        'UserName' => $user,
        'UserPwd' => $password,
        'LicType' => 'WS',
        'UserSession' => ''
    );
  
  if(isset($_SESSION['userSession'])){
        $_SESSION['login'] = TRUE;
        $_SESSION['user'] = $user;
        $_SESSION['db'] = $db;
        $_SESSION['password'] = $password;
        
        $userSession = $_SESSION['userSession'];
        $dni = number_format(str_replace('.', '', $_POST['NUM_DOC']), 0, '', '.');
        
        $paramData = array('UserSession' => $userSession,
 				'ItsClassName' => '_TUR_PASAJEROS',
 				'RecordCount' => 1,
 				'SQLFilter' => "NUM_DOC = '".$dni."'",
 				'SQLSort' => ''
                    
                );
                $prepareAppend = $client->ItsPrepareAppend(array('UserSession' => $userSession, 'ItsClassName' => '_TUR_PASAJEROS'));

                if(!$prepareAppend->ItsPrepareAppendResult) {

                    $dataset = simplexml_load_string($prepareAppend->XMLData);

                    $campos = array_keys($_POST);
                    foreach ($campos as $campo) {
                        //Guardo en session por si hay algún error y tengo que rellenar el formulario
                        $_SESSION[$campo] = utf8_encode ($_POST[$campo]);
                        //No tiene que contemplar los campos que son solo descriptivos, el valor esta en un input hidden
                        if(!strpos($campo, 'DESC'))
                            $dataset->ROWDATA->ROW[$campo]= utf8_encode ($_POST[$campo]);
                    }

                    $dataSession = $prepareAppend->DataSession;
                    $set_data = $client->ItsSetData(array('UserSession' => $userSession, 'DataSession' => $dataSession, 'iXMLData' => $dataset->asXML()));
                    if(!$set_data->ItsSetDataResult) {

                        $post = $client->ItsPost(array('UserSession' => $userSession, 'DataSession' => $dataSession));
                        if(!$post->ItsPostResult) {
                            $client->ItsLogout(array('UserSession' => $userSession));
                            session_unset();
                            $_SESSION['msj'] = 'Cuenta creada. Ingrese con su DNI.';
                            //$_SESSION['dni'] = $dni;
                            header('location: index.php');
                        } else {
                            $_SESSION['msj'] = ItsError($client, $userSession);
                            $client->ItsLogout(array('UserSession' => $_SESSION['userSession']));
                            header('location: index.php');
                        }
                    } else {
                        $_SESSION['msj'] = ItsError($client, $userSession);
                        $client->ItsLogout(array('UserSession' => $_SESSION['userSession']));
                        header('location: index.php');
                    }
                } else {
                    $_SESSION['msj'] = ItsError($client, $userSession);
                    $client->ItsLogout(array('UserSession' => $_SESSION['userSession']));
                    header('location: index.php');
                }
            }
