<?php
//TODO:Arrumar isso 
// define( 'ROOTPATH' , '/home/natan/expresso2.4' );

$REST = $_GET['q']; unset( $_GET['q'] );

if( !($REST = (isset($REST)) ? explode('/', $REST) : false) )
    return;

if( !(count($REST) % 2) )
    $id = array_pop($REST);
else
    $id = false;

$concept = array_pop($REST);

$parents = array();

while( $REST )
    $parents[ array_shift($REST) ] = array_shift($REST);

$accept = $_SERVER["HTTP_ACCEPT"];

$args = array();

if( $_SERVER["REQUEST_METHOD"] === "GET" )
{
    if( isset( $_GET["attr"] ) )
    {
	$args = $_GET["attr"];
    unset( $_GET["attr"] );
    }

    $method = $id ? "read" : "find";
}
else
{
    parse_str( file_get_contents('php://input'), $args );

    switch( $_SERVER["REQUEST_METHOD"] )
    {
	case "DELETE":
	    $method = $id ? "delete" : "deleteAll";
	break;
    case "PUT":
	    $method = $id ? "update" : "replace";
	break;
    case "POST":
	    $method = "create";
	break;
    }
}

require_once 'api/controller.php';

$URI = Controller::URI( $concept, $id );

$args = array_merge( $args, array('context'=>$parents));

echo json_encode( Controller::call( $method, $URI, $args, $_GET ) );

Controller::closeAll();

?>