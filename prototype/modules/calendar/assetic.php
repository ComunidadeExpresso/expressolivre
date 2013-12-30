<?php
header("Content-Type: text/javascript");
use Assetic\Asset\AssetCache;
use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Assetic\Asset\GlobAsset;
use Assetic\Filter\Yui;
use Assetic\FilterManager;
use Assetic\Filter;
use Assetic\Cache\FilesystemCache;

spl_autoload_register(
    function($className)
    {
        $className = str_replace("_", "\\", $className);
        $className = ltrim($className, '\\');
        $fileName = '';
        $namespace = '';
        if ($lastNsPos = strripos($className, '\\'))
        {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $fileName = '../../library/' . str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
        require $fileName;
    }
);

define( 'MODULESURL' , '../../modules/calendar' );
define( 'PLUGINSURL' , '../../plugins' );
$javaVersion = shell_exec("java -version 2>&1");
$filterManager = new FilterManager();
$js = new AssetCollection(array(    
        new FileAsset(MODULESURL.'/js/debug.js'),
        //new FileAsset(PLUGINSURL.'/datejs/date-pt-BR.js'),    
        new FileAsset(PLUGINSURL.'/icalendar/jquery.icalendar.js'),
        new FileAsset(PLUGINSURL.'/jquery/jquery-ui.custom.min.js'),
        new FileAsset(PLUGINSURL.'/jquery/i18n/jquery.ui.datepicker-pt-BR.js'),
        new FileAsset(PLUGINSURL.'/timepicker/jquery-ui-timepicker-addon.js'),
        new FileAsset(PLUGINSURL.'/timepicker/localization/jquery-ui-timepicker-pt-BR.js'),       
        new FileAsset(PLUGINSURL.'/json2/json2.js'),
        new FileAsset(PLUGINSURL.'/store/jquery.store.js'),
        new FileAsset(PLUGINSURL.'/fileupload/jquery.fileupload.js'),
        new FileAsset(PLUGINSURL.'/fileupload/jquery.iframe-transport.js'),
        new FileAsset(PLUGINSURL.'/jquery.pagination/jquery.pagination.js'),
        //new FileAsset(PLUGINSURL.'/mask/jquery.maskedinput.js'),
        new FileAsset(PLUGINSURL.'/jquery.cookie/jquery.cookie.js'),            
        new FileAsset(PLUGINSURL.'/alphanumeric/jquery.alphanumeric.js'),
        new FileAsset(PLUGINSURL.'/watermark/jquery.watermarkinput.js'),
        new FileAsset(PLUGINSURL.'/encoder/encoder.js'),      
        //new FileAsset(PLUGINSURL.'/datejs/sugarpak.js'),
        //new FileAsset(PLUGINSURL.'/datejs/parser.js'),
        new FileAsset(PLUGINSURL.'/dateFormat/dateFormat.js'),
        new FileAsset(PLUGINSURL.'/fullcalendar/fullcalendar.js'),
        new FileAsset(PLUGINSURL.'/fullcalendar/gcal.js'),
        new FileAsset(PLUGINSURL.'/jquery.dateFormat/jquery.dateFormat.js'),  
        new FileAsset(PLUGINSURL.'/zebradialog/javascript/zebra_dialog.js'),
        new FileAsset(PLUGINSURL.'/scrollto/jquery.scrollTo.js'),
        new FileAsset(PLUGINSURL.'/ejs/ejs.js'),
        new FileAsset(PLUGINSURL.'/fgmenu/fg.menu.js'),
        new FileAsset(PLUGINSURL.'/qtip/jquery.qtip-1.0.0-rc3.min.js'),
        //new FileAsset(PLUGINSURL.'/contextmenu/jquery.contextMenu.js'),
        new FileAsset(PLUGINSURL.'/jquery.spinner/jquery.spinner.min.js'),
        //new FileAsset(PLUGINSURL.'/jpicker/jpicker-1.1.6.min.js'),
        //new FileAsset(PLUGINSURL.'/farbtastic/farbtastic.js'),
        new FileAsset(MODULESURL.'/js/base64.js'),
        new FileAsset(MODULESURL.'/js/map.disponibility.js'),
        //new FileAsset(MODULESURL.'/js/calendar.date.js'),
        new FileAsset(MODULESURL.'/js/activity.helpers.js'),
        new FileAsset(MODULESURL.'/js/task.helpers.js'),
        new FileAsset(MODULESURL.'/js/helpers.js'),
        new FileAsset(MODULESURL.'/js/calendar.shared.js'),
        new FileAsset(MODULESURL.'/js/timezone.js'),
        new FileAsset(MODULESURL.'/js/calendar.codecs.js'),
        new FileAsset(MODULESURL.'/js/load.js'),
        new FileAsset(MODULESURL.'/js/calendar.alarms.js'),
        new FileAsset(MODULESURL.'/js/I18n.js'),
        new FileAsset(MODULESURL.'/js/calendar.contentMenu.js'),
        new FileAsset(MODULESURL.'/js/init.js'),
        new FileAsset(MODULESURL.'/js/drag_area.js'),
        new FileAsset(MODULESURL.'/js/desktop.notification.js')
    )
);

$compressor = new Yui\JsCompressorFilter('../../library/yuicompressor/yuicompressor.jar');
$compressor->setCharset("ISO-8859-1");

if (strpos($javaVersion,"java version") !== false){
    $filterManager->set('yui_js', $compressor);
    $js->ensureFilter($filterManager->get('yui_js'));
}

$jsCache = new AssetCache($js,new FilesystemCache('/tmp'));
echo $jsCache->dump();

?>
