<?php

$result = array();

require_once 'api/controller.php';
$URI = Controller::URI( 'attachment' );

foreach( $_FILES as $name => $file )
{
    $file['source'] = file_get_contents( $file['tmp_name'] );
    unset( $file['tmp_name'] );

    $result[$name] = Controller::create( $URI, $file );


    unset( $file['source'] );
}

echo json_encode( $result );