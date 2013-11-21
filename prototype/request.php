<?php

// $properties = $_REQUEST['properties'];
// $limit = $_REQUEST['limit'];
// $offset = $_REQUEST['offset'];
// $group = $_REQUEST['group'];
// $order = $_REQUEST['join'];
// $filter = $_REQUEST['filter'];
// $URI = $_REQUEST['URI'];

$concept = isset( $_REQUEST['concept'] ) ? $_REQUEST['concept'] : false;
$id = isset( $_REQUEST['id'] ) ? $_REQUEST['id'] : false;
$filter = isset( $_REQUEST['filter'] ) ? $_REQUEST['filter'] : false;
$criteria =  isset( $_REQUEST['criteria'] ) ? $_REQUEST['criteria'] : false;

$criteria = $filter ? $criteria ?

	    array_merge( $criteria, array( 'filter' => $filter ) ):
 
	    array( 'filter' => $filter ):

	    $criteria;

$properties = ( $criteria && isset( $criteria['properties'] ) )? $criteria['properties']: false;

$service = ( $criteria && isset( $criteria['service'] ) )? $criteria['service']: false;

require_once 'api/controller.php';

///Conversor Para utf8 ante de codificar para json pois o json so funciona com utf8
function toUtf8($data)
{
    if(!is_array($data))
      return mb_convert_encoding( $data , 'UTF-8' , 'UTF-8 , ISO-8859-1' );

    $return = array();

    foreach ($data as $i => $v)
      $return[toUtf8($i)] = toUtf8($v);

    return $return;
}
////////////////////////////////////////////////////////////////////////////////////////


echo json_encode( toUtf8(Controller::call( $id ? 'read' : 'find',
				    Controller::URI( $concept, $id, $service ),
				    $properties,
				    $criteria )) );

Controller::closeAll();
