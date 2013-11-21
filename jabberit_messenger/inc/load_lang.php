<?php

if ( file_exists(($fn = dirname(__FILE__) . '/../setup/phpgw_pt-br.lang')) )
	if ( ($fp = fopen($fn,'r')) )
	{
		while ($data = fgets($fp,16000))
		{
			list($message_id, $app_name, $null, $content) = explode("\t",substr($data,0,-1));
			$LANG_IM[$message_id] = $content;
		}
		fclose($fp);
	}

$script  = '<script>function jabberitGetLang(pKey){';
$script .= 'var lang = [];';

foreach ( $LANG_IM as $key => $value )
   $script .= "lang['" . strtolower(addslashes($key)) . "'] = '" . addslashes($value) . "';";

$script .= "return lang[pKey.toLowerCase()] || '* ' + pKey;}</script>";

echo $script;

unset($LANG_IM);
?>
