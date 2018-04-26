<?php
        ini_set('max_execution_time', 0);
//        require_once('../includes/ItrisSDK.php');
	$ws = 'http://srv01.tgroup.com.ar/ITSWS/ItsCliSvrWS.asmx?WSDL';
        $user = 'ws';
        $db = 'WAYLA_PRUEBA';
        $password = 'tedei0332';
        
        function ItsError($client, $userSession){
            $error = $client->ItsGetLastError(array('UserSession' => $userSession));
            return $error->Error;
        }
        
        function encriptado( $string, $action = 'e' ) {
            
            $secret_key = 'my_simple_secret_key';
            $secret_iv = 'my_simple_secret_iv';

            $output = false;
            $encrypt_method = "AES-256-CBC";
            $key = hash( 'sha256', $secret_key );
            $iv = substr( hash( 'sha256', $secret_iv ), 0, 16 );

            if( $action == 'e' ) {
                $output = base64_encode( openssl_encrypt( $string, $encrypt_method, $key, 0, $iv ) );
            }
            else if( $action == 'd' ){
                $output = openssl_decrypt( base64_decode( $string ), $encrypt_method, $key, 0, $iv );
            }

            return $output;
        }