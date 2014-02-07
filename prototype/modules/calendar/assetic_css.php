<?php
header("Content-Type: text/css");
use Assetic\Asset\AssetCache;
use Assetic\Cache\FilesystemCache;
use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Assetic\Asset\GlobAsset;
use Assetic\Filter\Yui;
use Assetic\FilterManager;
use Assetic\Filter;

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
$compressor = new Yui\CssCompressorFilter('../../library/yuicompressor/yuicompressor.jar');
$compressor->setCharset("ISO-8859-1");

$css = new AssetCollection(array(
        //new FileAsset(MODULESURL.'/css/reset.css'),
        //new FileAsset(PLUGINSURL.'/fullcalendar/fullcalendar.css'),
        //new FileAsset(PLUGINSURL.'/fullcalendar/fullcalendar.print.css'),
        //new FileAsset(PLUGINSURL.'/jquery/jquery-ui.css'),
        new FileAsset(PLUGINSURL.'/icalendar/jquery.icalendar.css'),
        //new FileAsset(PLUGINSURL.'/fgmenu/fg.menu.css'),
        new FileAsset(PLUGINSURL.'/fileupload/jquery.fileupload-ui.css'),
        new FileAsset(PLUGINSURL.'/jquery.pagination/pagination.css'),
        new FileAsset(PLUGINSURL.'/jpicker/css/jPicker-1.1.6.min.css'),
        new FileAsset(PLUGINSURL.'/jpicker/jPicker.css'),
        //new FileAsset(PLUGINSURL.'/farbtastic/farbtastic.css'),
        new FileAsset(PLUGINSURL.'/timepicker/jquery-ui-timepicker-addon.css'),
        //new FileAsset(PLUGINSURL.'/zebradialog/css/zebra_dialog.css'),
        new FileAsset(PLUGINSURL.'/jquery.spinner/jquery.spinner.css')
        //new FileAsset(MODULESURL.'/css/layout.css'),
        //new FileAsset(MODULESURL.'/css/style.css')
    ), null, 'calendar-css'
);

if (strpos($javaVersion,"java version") !== false){
    $filterManager->set('yui_css', $compressor);
    $css->ensureFilter($filterManager->get('yui_css'));
}

$cssCache = new AssetCache($css, new FilesystemCache('/tmp'));
echo $cssCache->dump();

?>