<?
/*
	$fn = './setup/phpgw_'.$_SESSION['phpgw_info']['expressoAdmin']['user']['preferences']['common']['lang'].'.lang';
	
	if (file_exists($fn))
	{
		$lang = array();
		
		$fp = fopen($fn,'r');
		while ($data = fgets($fp,16000))
		{
			list($message_id,$app_name,$null,$content) = explode("\t",substr($data,0,-1));			
			$lang[str_replace(" ", "_", (strtolower($message_id)) )] = $content;
		}
		fclose($fp);
		
		echo serialize($lang);
	}		
	exit;
*/

	$lang = array();
	foreach($_SESSION['phpgw_info']['expressoAdmin']['lang'] as $message_id=>$content)
	{
		$lang[str_replace(" ", "_", (strtolower($message_id)) )] = $content;
	}
	echo serialize($lang);
	exit;
?>