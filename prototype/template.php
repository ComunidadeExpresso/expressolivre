<?php

if( !isset($_GET['path']) || substr($_GET['path'], -4) !== '.ejs'  || !file_exists( __DIR__ .  $_GET['path']) ){
    header('HTTP/1.0 404 Not Found');
    echo "<h1>404 Not Found</h1>";
    echo "The page that you have requested could not be found.";
    exit();
}

require_once __DIR__ . '/library/fileDuck/FileDuck.php';

$moduleMap =  parse_ini_file( __DIR__ ."/config/moduleMap.ini", true );

$config = array();
$config['provider'] = 'expresso';
$config['lang'] = isset($_GET['lang'])  ? $_GET['lang'] : 'pt_BR';


$configProvider = array();
$configProvider['module'] = isset( $moduleMap[$_GET['module']] ) ?  $moduleMap[$_GET['module']] : 'phpgwapi' ;
$fileDuck = new FileDuck( $config , $configProvider );

$fileDuck->add( __DIR__ . $_GET['path'] , 'ISO-8859-1');
$fileDuck->renderFile( 'text/plain' );