<?php
session_start();
include 'includes/classes/Download.class.php';

$pathURL = $_GET['arquivo']; 

$baseDir = '/tmp/';
$baseRef = 'true';

$pathFile = str_replace($baseDir , '', $pathURL);

$pathPartes = explode('/', $pathFile) ;

$pathDirFinal = $pathPartes[0];


$fileDown = '';
for($i = 1; $i < (count($pathPartes)); ++$i){
   $fileDown = ($fileDown != '') ? $fileDown . '/' : $fileDown ;
   $fileDown .= $pathPartes[$i];
}
 
$down = new Download($baseDir, $baseRef );
$down->doDownload($baseDir . $pathDirFinal);

if($down->getSysError() != ''){
    echo $down->getSysDebug();  
    echo $down->getSysError();
}


 ?>