<?php

$dir = "apresentacao/";

$dh  = opendir($dir);

while (false !== ($filename = readdir($dh)))
{
    if( is_file($dir . $filename) )
    {
    	$files[] = $filename;
    }
}

sort($files);

$conf_ini = parse_ini_file('config_images.ini', true);

for( $i = 0 ; $i < count($files); $i++ )
{
	foreach($conf_ini as $ini)
	{
		if( $ini['nome'] === $files[$i] )
		{
			$filesNew[$i]['nome']	= "phpgwapi/templates/news/" . $dir . $ini['nome'];
			$filesNew[$i]['texto']	= ( $ini['texto'] ) ? $ini['texto'] : "";
			$filesNew[$i]['titulo']	= ( $ini['titulo'] ) ? $ini['titulo'] : "";
			$filesNew[$i]['link']	= ( $ini['link'] ) ? $ini['link'] : "";
		}
		else
			$filesNew[$i]['nome'] = "phpgwapi/templates/news/" . $dir . $files[$i];
	}
}

echo json_encode($filesNew);

?>