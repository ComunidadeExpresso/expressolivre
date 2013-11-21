<?php

require_once 'api/controller.php';

$concept = isset($_GET['concept']) ? $_GET['concept'] : false;

$links = Controller::links( $concept );

if( $concept )
    $links = array( $concept => $links );

$return = array();

foreach( $links as $target => $link )
{
    $concepts = array();
    $nestedLinks = array();
    $hasOne = array();

    foreach( $link as $linkName => $linkTarget )
    {
	 if( Controller::isConcept( $concept, $linkName ) )
	    $concepts[ $linkName ] = true;

	 if( Controller::hasOne( $concept, $linkName ) )
	    $hasOne[ $linkName ] = true;

	  $nestedLinks[ $linkName ] = Controller::links( $concept, $linkName );
    }

    $return[ $target ] = array( 'concepts' => $concepts, 'links' => $link, 'nestedLinks' => $nestedLinks, 'hasOne' => $hasOne );
}

echo json_encode( $concept ? $return[ $concept ] : $return );
