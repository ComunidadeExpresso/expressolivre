<?php

require_once __DIR__ . '/../../library/fileDuck/FileDuck.php';

$config = array();
$config['provider'] = 'expresso';
$config['lang'] = $_GET['lang'];
$configProvider = array();
$configProvider['module'] = 'expressoCalendar';

$fileDuck = new FileDuck( $config , $configProvider );

$fileDuck->add(__DIR__ .'/js/debug.js');
$fileDuck->add(__DIR__ .'/../../plugins/icalendar/jquery.icalendar.js');
$fileDuck->add(__DIR__ .'/../../plugins/jquery/jquery-ui.custom.min.js');
$fileDuck->add(__DIR__ .'/../../plugins/jquery/i18n/jquery.ui.datepicker-pt-BR.js');
$fileDuck->add(__DIR__ .'/../../plugins/timepicker/jquery-ui-timepicker-addon.js');
$fileDuck->add(__DIR__ .'/../../plugins/timepicker/localization/jquery-ui-timepicker-pt-BR.js' , 'ISO-8859-1');
$fileDuck->add(__DIR__ .'/../../plugins/json2/json2.js');
$fileDuck->add(__DIR__ .'/../../plugins/store/jquery.store.js');
$fileDuck->add(__DIR__ .'/../../plugins/fileupload/jquery.fileupload.js');
$fileDuck->add(__DIR__ .'/../../plugins/fileupload/jquery.iframe-transport.js');
$fileDuck->add(__DIR__ .'/../../plugins/jquery.pagination/jquery.pagination.js');
$fileDuck->add(__DIR__ .'/../../plugins/alphanumeric/jquery.alphanumeric.js');
$fileDuck->add(__DIR__ .'/../../plugins/watermark/jquery.watermarkinput.js');
$fileDuck->add(__DIR__ .'/../../plugins/encoder/encoder.js');
$fileDuck->add(__DIR__ .'/../../plugins/dateFormat/dateFormat.js');
$fileDuck->add(__DIR__ .'/../../plugins/fullcalendar/fullcalendar.js');
$fileDuck->add(__DIR__ .'/../../plugins/fullcalendar/gcal.js');
$fileDuck->add(__DIR__ .'/../../plugins/jquery.dateFormat/jquery.dateFormat.js');
$fileDuck->add(__DIR__ .'/../../plugins/zebradialog/javascript/zebra_dialog.js');
$fileDuck->add(__DIR__ .'/../../plugins/scrollto/jquery.scrollTo.js');
$fileDuck->add(__DIR__ .'/../../plugins/ejs/ejs.js');
$fileDuck->add(__DIR__ .'/../../plugins/fgmenu/fg.menu.js');
$fileDuck->add(__DIR__ .'/../../plugins/qtip/jquery.qtip-1.0.0-rc3.min.js');
$fileDuck->add(__DIR__ .'/../../plugins/jquery.spinner/jquery.spinner.min.js');
$fileDuck->add(__DIR__ .'/../../plugins/jquery/hack.js');
$fileDuck->add(__DIR__ .'/js/base64.js');
$fileDuck->add(__DIR__ .'/js/map.disponibility.js');
$fileDuck->add(__DIR__ .'/js/activity.helpers.js' , 'ISO-8859-1');
$fileDuck->add(__DIR__ .'/js/task.helpers.js' , 'ISO-8859-1');
$fileDuck->add(__DIR__ .'/js/helpers.js', 'ISO-8859-1');
$fileDuck->add(__DIR__ .'/js/calendar.date.js'  , 'ISO-8859-1');
$fileDuck->add(__DIR__ .'/js/calendar.shared.js'  , 'ISO-8859-1');
$fileDuck->add(__DIR__ .'/js/timezone.js'  , 'ISO-8859-1');
$fileDuck->add(__DIR__ .'/js/calendar.codecs.js' , 'ISO-8859-1');
$fileDuck->add(__DIR__ .'/js/load.js' , 'ISO-8859-1');
$fileDuck->add(__DIR__ .'/js/calendar.alarms.js' , 'ISO-8859-1');
$fileDuck->add(__DIR__ .'/js/I18n.js');
$fileDuck->add(__DIR__ .'/js/calendar.contentMenu.js');
$fileDuck->add(__DIR__ .'/js/init.js' , 'ISO-8859-1');
$fileDuck->add(__DIR__ .'/js/drag_area.js' , 'ISO-8859-1');
$fileDuck->add(__DIR__ .'/js/desktop.notification.js' , 'ISO-8859-1');

$fileDuck->renderFile( 'text/javascript' );