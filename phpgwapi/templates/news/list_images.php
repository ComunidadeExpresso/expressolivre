<?php

$dir = "src_images/";

$dh  = opendir($dir);

$files = array();

$finfo = finfo_open(FILEINFO_MIME_TYPE); 

while (false !== ($filename = readdir($dh)) )
{
    if( is_file($dir . $filename) && preg_match("/^image/", finfo_file( $finfo, $dir . $filename ) ) )
    {
   		$filename = basename($filename);
		
    	$files[$filename]['name'] = "phpgwapi/templates/news/" . $dir . $filename;
    }
}

finfo_close($finfo);

// Mistura os elementos de um array
$keys = array_keys($files);

shuffle($keys);

foreach($keys as $key)
{
    $newArray[$key] = $files[$key];
}

$filesImages = $newArray;

$conf_ini = parse_ini_file('config_images.ini', true);

foreach( $conf_ini as $ini )
{
	if( isset($filesImages[$ini['name']]) )
	{	
		$filesImages[$ini['name']]['text']	= ( $ini['text'] ) ? utf8_encode($ini['text']) : "";
		$filesImages[$ini['name']]['title']	= ( $ini['title'] ) ? utf8_encode($ini['title']) : "";
		$filesImages[$ini['name']]['link']	= ( $ini['link'] ) ? $ini['link'] : "";
	}	
}

echo json_encode($filesImages);

?>