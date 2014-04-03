<?php

$GLOBALS['phpgw_info']['flags'] = Array(   'currentapp'    =>      'expressoCalendar',
    'noheader'      =>      false,
    'nonavbar'      =>      false,
    'noappheader'   =>      true,
    'noappfooter'   =>      true,
    'nofooter'      =>      true  );

require_once( dirname(__FILE__).'/../prototype/api/config.php' );

require_once (dirname(__FILE__).'/../header.inc.php');

$_SESSION['flags']['currentapp'] = 'expressoCalendar';

define( 'MODULESURL' , '../prototype/modules/calendar' );
define( 'PLUGINSURL' , '../prototype/plugins' );

echo '<link rel="stylesheet" type="text/css" href="../prototype/modules/calendar/styles.php" />';

require  __DIR__ . '/../library/fileDuck/FileDuck.php';

$config = array();
$config['provider'] = 'expresso';
$config['lang'] = $GLOBALS['phpgw_info']['user']['preferences']['common']['lang'];

$configProvider = array();
$configProvider['module'] = 'expressoCalendar';

$fileDuck = new FileDuck( $config , $configProvider );

$fileDuck->add( __DIR__ .'/../prototype/modules/calendar/templates/index.ejs' );

echo mb_convert_encoding( $fileDuck->renderContent() , 'ISO-8859-1' , 'UTF-8' );

echo '<script type="text/javascript" src="../prototype/modules/calendar/scripts.php?lang='.$GLOBALS['phpgw_info']['user']['preferences']['common']['lang'].'" charset="UTF-8" ></script>';
