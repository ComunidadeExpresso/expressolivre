<?php

if(!isset($GLOBALS['phpgw_info']))
{
        $GLOBALS['phpgw_info']['flags'] = array(
                'currentapp' => 'jabberit_messenger',
        );
}

require_once '../header.session.inc.php';

$request_method = '_' . $_SERVER['REQUEST_METHOD'];
switch ( $request_method )
{
	case '_GET' :
		$params = $_GET;
	break;
	case '_POST' :
		$params = $_POST;
	break;
	case '_HEAD' :
	case '_PUT' :
	default :
		echo "controller - request method not avaible";
		return false;
}

if( file_exists(dirname(__FILE__) . "/inc/Controller.class.php") )
{
	require_once dirname(__FILE__) . '/inc/Controller.class.php';

	$controller = new Controller();
	printf("%s", $controller->exec($$request_method, dirname(__FILE__)));
	exit(0);
}

?>
