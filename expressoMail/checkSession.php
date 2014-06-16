<?php

if ( isset( $_COOKIE[ 'sessionid' ] ) )
    session_id( $_COOKIE[ 'sessionid' ] );

if( !isset($_SESSION) )
    session_start( );

$return['status'] = false;

if(isset($_SESSION['wallet']['security']['REMOTE_ADDR']) && !empty($_SESSION['phpgw_session']['session_id']))
{
    if($_SESSION['wallet']['security']['REMOTE_ADDR'] === $_SERVER['REMOTE_ADDR'])
        $return['status'] = true;
}

echo json_encode($return);