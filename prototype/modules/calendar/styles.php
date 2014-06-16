<?php

require_once __DIR__ . '/../../library/fileDuck/FileDuck.php';

$config = array();
$config['provider'] = 'expresso';
$config['lang'] = isset($_GET['lang'])  ? $_GET['lang'] : 'pt_BR';
$configProvider = array();
$configProvider['module'] = 'expressoCalendar';

$fileDuck = new FileDuck( $config , $configProvider );

$fileDuck->add( __DIR__ .'/../../plugins/icalendar/jquery.icalendar.css');
$fileDuck->add( __DIR__ .'/../../plugins/fileupload/jquery.fileupload-ui.css');
$fileDuck->add( __DIR__ .'/../../plugins/jquery.pagination/pagination.css');
$fileDuck->add( __DIR__ .'/../../plugins/jpicker/css/jPicker-1.1.6.min.css');
$fileDuck->add( __DIR__ .'/../../plugins/jpicker/jPicker.css');
$fileDuck->add( __DIR__ .'/../../plugins/timepicker/jquery-ui-timepicker-addon.css');
$fileDuck->add( __DIR__ .'/../../plugins/jquery.spinner/jquery.spinner.css');

$fileDuck->renderFile( 'text/css' );



