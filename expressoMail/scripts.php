<?php

require __DIR__ . '/../prototype/library/fileDuck/FileDuck.php';

$config = array();
$config['provider'] = 'expresso';
$config['lang'] = isset($_GET['lang'])  ? $_GET['lang'] : 'pt_BR';


$configProvider = array();
$configProvider['module'] = 'expressoMail';

$fileDuck = new FileDuck( $config , $configProvider );


$fileDuck->add(__DIR__ .'/js/connector.js' ,'ISO-8859-1');
$fileDuck->add(__DIR__ .'/../phpgwapi/js/dftree/dftree.js','ISO-8859-1');
$fileDuck->add(__DIR__ .'/../phpgwapi/js/wz_dragdrop/wz_dragdrop.js');
$fileDuck->add(__DIR__ .'/../phpgwapi/js/dJSWin/dJSWin.js');
$fileDuck->add(__DIR__ .'/../phpgwapi/js/x_tools/xtools.js');
$fileDuck->add(__DIR__ .'/js/DropDownContacts.js');
$fileDuck->add(__DIR__ .'/../prototype/library/fancybox/jquery.fancybox-1.3.4.pack.js');
$fileDuck->add(__DIR__ .'/../prototype/modules/mail/js/label.js' , 'ISO-8859-1');
$fileDuck->add(__DIR__ .'/../prototype/plugins/datejs/date-pt-BR.js');
$fileDuck->add(__DIR__ .'/../prototype/plugins/dateFormat/dateFormat.js');
$fileDuck->add(__DIR__ .'/../prototype/modules/calendar/js/timezone.js' , 'ISO-8859-1');
$fileDuck->add(__DIR__ .'/../prototype/modules/calendar/js/calendar.date.js' , 'ISO-8859-1');
$fileDuck->add(__DIR__ .'/../prototype/modules/calendar/js/calendar.codecs.js' , 'ISO-8859-1');
$fileDuck->add(__DIR__ .'/../prototype/modules/calendar/js/calendar.alarms.js' , 'ISO-8859-1');
$fileDuck->add(__DIR__ .'/../prototype/modules/calendar/js/helpers.js', 'ISO-8859-1');
$fileDuck->add(__DIR__ .'/../prototype/plugins/json2/json2.js');
$fileDuck->add(__DIR__ .'/../prototype/plugins/ejs/ejs.js');
$fileDuck->add(__DIR__ .'/../prototype/plugins/ejs/view.js');
$fileDuck->add(__DIR__ .'/../prototype/plugins/scrollto/jquery.scrollTo.js');
$fileDuck->add(__DIR__ .'/../prototype/plugins/timepicker/jquery-ui-timepicker-addon.js');
$fileDuck->add(__DIR__ .'/../prototype/plugins/timepicker/localization/jquery-ui-timepicker-pt-BR.js', 'ISO-8859-1');
$fileDuck->add(__DIR__ .'/../prototype/plugins/jquery/i18n/jquery.ui.datepicker-pt-BR.js');
$fileDuck->add(__DIR__ .'/../prototype/plugins/datejs/sugarpak.js');
$fileDuck->add(__DIR__ .'/../prototype/plugins/datejs/parser.js');
$fileDuck->add(__DIR__ .'/../prototype/plugins/block/jquery.blockUI.js');
$fileDuck->add(__DIR__ .'/../prototype/plugins/jq-raty/js/jquery.raty.min.js');
$fileDuck->add(__DIR__ .'/../prototype/plugins/jquery.jrating/jRating.jquery.js');
$fileDuck->add(__DIR__ .'/../prototype/plugins/watermark/jquery.watermarkinput.js');

$fileDuck->add(__DIR__ .'/../prototype/plugins/fileupload/jquery.iframe-transport.js');
$fileDuck->add(__DIR__ .'/../prototype/plugins/qtip/jquery.qtip-1.0.0-rc3.min.js');
$fileDuck->add(__DIR__ .'/../prototype/plugins/treeview/jquery.treeview.js');
$fileDuck->add(__DIR__ .'/../prototype/plugins/jquery.cookie/jquery.cookie.js');
$fileDuck->add(__DIR__ .'/../prototype/plugins/scrollto/jquery.scrollTo.js');
$fileDuck->add(__DIR__ .'/../prototype/plugins/jqgrid/js/i18n/grid.locale-pt-br.js' , 'ISO-8859-1');
$fileDuck->add(__DIR__ .'/../prototype/plugins/jqgrid/js/jquery.jqGrid.min.js');
$fileDuck->add(__DIR__ .'/../prototype/modules/mail/js/foldertree.js', 'ISO-8859-1');
$fileDuck->add(__DIR__ .'/../prototype/plugins/zebradialog/javascript/zebra_dialog.js');
$fileDuck->add(__DIR__ .'/../prototype/plugins/alphanumeric/jquery.alphanumeric.js');
$fileDuck->add(__DIR__ .'/../prototype/plugins/freeow/jquery.freeow.js');
$fileDuck->add(__DIR__ .'/../prototype/plugins/widgets/combobox.js', 'ISO-8859-1');
$fileDuck->add(__DIR__ .'/js/modal/modal.js' );
$fileDuck->add(__DIR__ .'/js/folder.js');
$fileDuck->add(__DIR__ .'/js/base64.js');
$fileDuck->add(__DIR__ .'/js/QuickCatalogSearch.js' , 'ISO-8859-1');
$fileDuck->add(__DIR__ .'/js/common_functions.js' , 'ISO-8859-1');
$fileDuck->add(__DIR__ .'/js/abas.js' , 'ISO-8859-1');
$fileDuck->add(__DIR__ .'/js/draw_api.js' , 'ISO-8859-1');
$fileDuck->add(__DIR__ .'/../prototype/modules/calendar/js/desktop.notification.js' , 'ISO-8859-1');
$fileDuck->add(__DIR__ .'/js/main.js' , 'ISO-8859-1');
$fileDuck->add(__DIR__ .'/../prototype/modules/mail/js/followupflag.js', 'ISO-8859-1');
$fileDuck->add(__DIR__ .'/js/messages_controller.js' , 'ISO-8859-1');
$fileDuck->add(__DIR__ .'/js/doiMenuData.js' , 'ISO-8859-1');
$fileDuck->add(__DIR__ .'/js/rich_text_editor.js' , 'ISO-8859-1');
$fileDuck->add(__DIR__ .'/../prototype/modules/filters/filters.js');
$fileDuck->add(__DIR__ .'/../prototype/modules/mail/js/label.js' , 'ISO-8859-1');
$fileDuck->add(__DIR__ .'/js/searchEmails.js');
$fileDuck->add(__DIR__ .'/js/init.js' , 'ISO-8859-1' );
$fileDuck->add(__DIR__ .'/js/checkSession.js' );
$fileDuck->add(__DIR__ .'/../prototype/plugins/farbtastic/farbtastic.js');
// Messenger
$fileDuck->add(__DIR__ .'/../prototype/plugins/wijmo/jquery.wijmo.min.js');
$fileDuck->add(__DIR__ .'/../prototype/plugins/wijmo/jquery.wijmo.wijdialog.js');	
$fileDuck->add(__DIR__ .'/../prototype/plugins/jquery-xmpp/APIAjax.js');
$fileDuck->add(__DIR__ .'/../prototype/plugins/jquery-xmpp/jquery.xmpp.js');
$fileDuck->add(__DIR__ .'/../prototype/plugins/messenger/lang/messages.js');	
$fileDuck->add(__DIR__ .'/../prototype/plugins/messenger/im.js');

$fileDuck->renderFile( 'text/javascript' );
