<?php
require_once  (dirname(__FILE__).'/api/controller.php');
use prototype\api\Config as Config;

$me = Controller::read(array('concept' => 'user', 'service' => 'OpenLDAP'  , 'id' => Config::me('uidNumber')));

//
//if(isset($_POST['refreshToken'])){
//    
//    $ch = curl_init();
//
//    $restConf = parse_ini_file( __DIR__ . '/config/REST.ini', true );
//
//    $param  = 'grant_type=refresh_token';
//    $param .= '&client_id=' . $restConf['oauth']['client_id'];
//    $param .= '&client_secret=' . $restConf['oauth']['client_secret'];
//    $param .= '&refresh_token=' . $_SESSION['oauth']['refresh_token'];
//
//    // set URL and other appropriate options
//    curl_setopt($ch, CURLOPT_URL, $restConf['oauth']['url_token']);
//    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: */*'));
//    curl_setopt($ch, CURLOPT_POST, TRUE);
//    curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
//    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);  //configura para nao imprimir a saida na tela
//    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);//Passe um número long como parâmetro que contêm o limite de tempo, em segundos, que você permite as funções CURL levar. 
//
//    // grab URL and pass it to the browser
//    $res = curl_exec($ch);
//
//    // close cURL resource, and free up system resources
//    curl_close($ch);
//    $a = json_decode($res);
//
//    if ( isset($a->access_token) ) {
//	$_SESSION['oauth']['access_token'] = $a->access_token;
//	$_SESSION['oauth']['expires_in'] = $a->expires_in;
//	$_SESSION['oauth']['token_type'] = $a->token_type;
//	$_SESSION['oauth']['scope'] = $a->scope;
//	$_SESSION['oauth']['refresh_token'] = $a->refresh_token;
//	$_SESSION['oauth']['client_secret'] = $restConf['oauth']['client_secret'];
//    }  else {
//	echo json_encode(null);
//	return;
//    }
//}


$me['token'] = 'asdf1as5d1f56a1sdf1qw5e1q2we5qfq8ew';//$_SESSION['oauth']['access_token'];
echo json_encode( $me );

// if( !$me )
//     return;
// 
// $links = Controller::links();
// 
// $return = array();
// 
// foreach( $links as $concept => $link )
// {
//     $concepts = array();
// 
//     foreach( $link as $linkName => $linkTarget )
// 	 if( Controller::isConcept( $linkName ) )
// 	    $concepts[ $linkName ] = true;
// 
//      $return[ $concept ] = array( 'concepts' => $concepts, 'links' => $link );
// }
// 
// echo json_encode( array( 'me' => $me, 'links' => $return ) );
