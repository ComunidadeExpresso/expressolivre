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
            $fileName = '../prototype/library/' . str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

        require $fileName;
    }
);

$javaVersion = shell_exec("java -version 2>&1");
$filterManager = new FilterManager();
$compressor = new Yui\CssCompressorFilter('../prototype/library/yuicompressor/yuicompressor.jar');
$compressor->setCharset("ISO-8859-1");

$css = new AssetCollection(array(
            new FileAsset('../prototype/plugins/freeow/style/freeow/freeow.css'),
            new FileAsset('../phpgwapi/js/dftree/dftree.css'),
            new FileAsset('../prototype/plugins/farbtastic/farbtastic.css'),
            new FileAsset('../prototype/modules/mail/css/foldertree.css'),
            new FileAsset('../prototype/modules/calendar/css/layout.css'),
            new FileAsset('../prototype/plugins/jquery.spinner/jquery.spinner.css'),
            new FileAsset('../prototype/plugins/fullcalendar/fullcalendar.css'),
            new FileAsset('../prototype/plugins/fullcalendar/fullcalendar.print.css'),
            new FileAsset('../prototype/plugins/icalendar/jquery.icalendar.css'),
            new FileAsset('../prototype/plugins/timepicker/jquery-ui-timepicker-addon.css')  
    ), null, 'expressoMail-css'
);

if (strpos($javaVersion,"java version") !== false){
    $filterManager->set('yui_css', $compressor);    
    $css->ensureFilter($filterManager->get('yui_css'));    
}

$cssCache = new AssetCache($css,new FilesystemCache('/tmp'));
echo $cssCache->dump();

?>