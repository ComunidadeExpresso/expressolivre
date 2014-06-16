<?php

require_once __DIR__ . '/../prototype/library/fileDuck/FileDuck.php';

$config = array();
$config['provider'] = 'expresso';
$config['lang'] = isset($_GET['lang'])  ? $_GET['lang'] : 'pt_BR';
$configProvider = array();
$configProvider['module'] = 'expressoMail';

$fileDuck = new FileDuck( $config , $configProvider );

$fileDuck->add(__DIR__ .'/../prototype/plugins/freeow/style/freeow/freeow.css');
$fileDuck->add(__DIR__ .'/../phpgwapi/js/dftree/dftree.css');
$fileDuck->add(__DIR__ .'/../prototype/plugins/farbtastic/farbtastic.css');
$fileDuck->add(__DIR__ .'/../prototype/plugins/jqgrid/themes/prognusone/jquery-ui-1.8.2.custom.css');
$fileDuck->add(__DIR__ .'/../prototype/modules/mail/css/foldertree.css');
$fileDuck->add(__DIR__ .'/../prototype/modules/calendar/css/layout.css');
$fileDuck->add(__DIR__ .'/../prototype/plugins/jquery.spinner/jquery.spinner.css');
$fileDuck->add(__DIR__ .'/../prototype/plugins/fullcalendar/fullcalendar.css');
$fileDuck->add(__DIR__ .'/../prototype/plugins/fullcalendar/fullcalendar.print.css');
$fileDuck->add(__DIR__ .'/../prototype/plugins/icalendar/jquery.icalendar.css');
$fileDuck->add(__DIR__ .'/../prototype/plugins/timepicker/jquery-ui-timepicker-addon.css');

$fileDuck->renderFile( 'text/css' );