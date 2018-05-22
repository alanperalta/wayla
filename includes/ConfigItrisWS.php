<?php
        ini_set('max_execution_time', 0);
        
        //Configuraciones de WS
        //-----------------------------------------------
	$ws = 'http://srv01.tgroup.com.ar:8080';
        $user = 'ws';
        $db = 'WAYLA_PRUEBA';
        $password = 'tedei0332';
        //------------------------------------------------
        
        
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
        
        function ItsLogin(){
            $url = $GLOBALS['ws']."/login";
            $ch = curl_init($url);
            $data = array(
                'database' => $GLOBALS['db'],
                'username' => $GLOBALS['user'],
                'password' => $GLOBALS['password']
            );
            $json = json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);

            curl_close($ch);
            
            return json_decode($result, TRUE);
        }
        
        function ItsLogout($usersession){
            $url = $GLOBALS['ws']."/logout";
            $ch = curl_init($url);
            $data = array(
                'usersession' => $usersession
            );
            $json = json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);

            curl_close($ch);
            
            return json_decode($result, TRUE);
        }
        
        function ItsGetData($usersession, $class, $cantidad = '', $filtro = '', $orden = ''){
            $url = $GLOBALS['ws']."/class?usersession=".$usersession."&class=".$class.(($cantidad != '')?"&recordCount=".$cantidad:"").(($filtro != '')?"&sqlFilter=".urlencode($filtro):"").(($orden != '')?"&sqlSort=".urlencode($orden):"");
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
            
            return json_decode(utf8_encode($result), TRUE);
        }
        
        function ItsPostData($usersession, $class, $data){
            $url = $GLOBALS['ws']."/class";
            $ch = curl_init($url);
            $datos = array(
                'usersession' => $usersession,
                'class' => $class,
                'data' => array($data)
            );
            $json = json_encode($datos);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);

            curl_close($ch);
            
            return json_decode($result, TRUE);
        }