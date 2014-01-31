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
            $fileName = '../prototype/library/' . str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
        require $fileName;
    }
);

$javaVersion = shell_exec("java -version 2>&1");
$filterManager = new FilterManager();
$compressor = new Yui\JsCompressorFilter('../prototype/library/yuicompressor/yuicompressor.jar');
$compressor->setCharset("ISO-8859-1");

$plugins = new AssetCollection(array(
        new FileAsset('../prototype/plugins/json2/json2.js'),
        new FileAsset('../prototype/plugins/ejs/ejs.js'),
        new FileAsset('../prototype/plugins/scrollto/jquery.scrollTo.js'),
        new FileAsset('../prototype/plugins/timepicker/jquery-ui-timepicker-addon.js'),
        new FileAsset('../prototype/plugins/timepicker/localization/jquery-ui-timepicker-pt-BR.js'),
        new FileAsset('../prototype/plugins/jquery/i18n/jquery.ui.datepicker-pt-BR.js'),
        new FileAsset('../prototype/plugins/datejs/sugarpak.js'),
        new FileAsset('../prototype/plugins/datejs/parser.js'),
        new FileAsset('../prototype/plugins/block/jquery.blockUI.js'),
        new FileAsset('../prototype/plugins/jq-raty/js/jquery.raty.min.js'),
        new FileAsset('../prototype/plugins/jquery.jrating/jRating.jquery.js'),
        new FileAsset('../prototype/plugins/watermark/jquery.watermarkinput.js'),
        new FileAsset('../prototype/plugins/fileupload/jquery.iframe-transport.js'),
        new FileAsset('../prototype/plugins/qtip/jquery.qtip-1.0.0-rc3.min.js'),
        new FileAsset('../prototype/plugins/treeview/jquery.treeview.js'),
        new FileAsset('../prototype/plugins/jquery.cookie/jquery.cookie.js'),    
        new FileAsset('../prototype/plugins/scrollto/jquery.scrollTo.js'),
        new FileAsset('../prototype/plugins/jqgrid/js/i18n/grid.locale-pt-br.js'),
        new FileAsset('../prototype/plugins/jqgrid/js/jquery.jqGrid.min.js'),
        new FileAsset('../prototype/modules/mail/js/foldertree.js'),
        new FileAsset('../prototype/plugins/zebradialog/javascript/zebra_dialog.js'),
        new FileAsset('../prototype/plugins/alphanumeric/jquery.alphanumeric.js'),
        new FileAsset('../prototype/plugins/freeow/jquery.freeow.js'),
        new FileAsset('../prototype/plugins/widgets/combobox.js')
    )
);

/*Se o servidor possuir a jvm então minifique os arquivos*/
if (strpos($javaVersion,"java version") !== false){
    $filterManager->set('yui_js', $compressor);
    $plugins->ensureFilter($filterManager->get('yui_js'));
}

$pluginsCache = new AssetCache($plugins,new FilesystemCache('/tmp/js'));
echo $pluginsCache->dump();

$scripts = new AssetCollection(array(
    new FileAsset('js/modal/modal.js'),
    new FileAsset('js/folder.js'),
    new FileAsset('js/base64.js'),
    new FileAsset('js/QuickCatalogSearch.js'),
    new FileAsset('js/QuickAddTelephone.js'),
    new FileAsset('js/common_functions.js'),
    new FileAsset('js/abas.js'),
    new FileAsset('js/draw_api.js'),
    new FileAsset('../prototype/modules/calendar/js/desktop.notification.js'),
    new FileAsset('js/main.js'),
    new FileAsset('../prototype/modules/mail/js/followupflag.js'),
    new FileAsset('js/messages_controller.js'),
    new FileAsset('js/doiMenuData.js'),
    new FileAsset('js/rich_text_editor.js'),
    new FileAsset('../prototype/modules/filters/filters.js'),
    new FileAsset('../prototype/modules/mail/js/label.js'),
    new FileAsset('js/init.js')
));

if (strpos($javaVersion,"java version") !== false){
    $filterManager->set('yui_js', $compressor);
    $scripts->ensureFilter($filterManager->get('yui_js'));
}

$scriptsCache = new AssetCache($scripts,new FilesystemCache('/tmp/js'));
echo $scriptsCache->dump();
?>