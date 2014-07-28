<?php

require __DIR__ . '/../prototype/library/fileDuck/FileDuck.php';

$config = array();
$config['provider'] = 'expresso';
$config['lang'] = isset($_GET['lang'])  ? $_GET['lang'] : 'pt_BR';


$configProvider = array();
$configProvider['module'] = 'expressoAdmin';

$fileDuck = new FileDuck( $config , $configProvider );

$fileDuck->add(__DIR__ .'/../prototype/plugins/datejs/date-pt-BR.js',  'ISO-8859-1');
$fileDuck->add(__DIR__ .'/../prototype/plugins/datejs/sugarpak.js',  'ISO-8859-1');
$fileDuck->add(__DIR__ .'/../prototype/plugins/datejs/parser.js',  'ISO-8859-1');
$fileDuck->add(__DIR__ .'/../prototype/modules/calendar/js/timezone.js',  'ISO-8859-1');
$fileDuck->add(__DIR__ .'/../prototype/modules/calendar/js/calendar.codecs.js',  'ISO-8859-1');
$fileDuck->add(__DIR__ .'/../prototype/modules/calendar/js/load.js', 'ISO-8859-1');

$fileDuck->add(__DIR__ .'/js/jscode/assing_calendar.js' , 'ISO-8859-1');

$fileDuck->renderFile( 'text/javascript' );
