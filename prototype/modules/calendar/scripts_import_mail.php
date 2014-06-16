<?php

require_once __DIR__ . '/../../library/fileDuck/FileDuck.php';

$config = array();
$config['provider'] = 'expresso';
$config['lang'] = $_GET['lang'];
$configProvider = array();
$configProvider['module'] = 'expressoCalendar';

$fileDuck = new FileDuck( $config , $configProvider );



$fileDuck->add(__DIR__ .'/js/load.js' , 'ISO-8859-1');
$fileDuck->add(__DIR__ .'/js/map.disponibility.js');
$fileDuck->add(__DIR__ .'/../../plugins/encoder/encoder.js');
$fileDuck->add(__DIR__ .'/js/helpers.js', 'ISO-8859-1');
$fileDuck->add(__DIR__ .'/js/task.helpers.js' , 'ISO-8859-1');
$fileDuck->add(__DIR__ .'/../../plugins/jquery.spinner/jquery.spinner.min.js');
$fileDuck->add(__DIR__ .'/../../plugins/fullcalendar/fullcalendar.js');

$fileDuck->renderFile( 'text/javascript' );