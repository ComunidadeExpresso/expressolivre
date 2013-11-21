<?php
		/*************************************************************************** 
		* Expresso Livre                                                           * 
		* http://www.expressolivre.org                                             * 
		* --------------------------------------------                             * 
		*  This program is free software; you can redistribute it and/or modify it * 
		*  under the terms of the GNU General Public License as published by the   * 
		*  Free Software Foundation; either version 2 of the License, or (at your  * 
		*  option) any later version.                                              * 
		\**************************************************************************/ 
		
require_once '../../header.session.inc.php';

/* This single file is used to increase upload_max_filesize and post_max_size using .htaccess*/
if(!isset($GLOBALS['phpgw_info'])){
	$GLOBALS['phpgw_info']['flags'] = array(
		'currentapp' => 'filemanager',
		'nonavbar'   => true,
		'noheader'   => true
	);
}
require_once '../../header.inc.php';

	$array_keys = array();
	$fn = dirname(__FILE__) . '/../setup/phpgw_'.$GLOBALS['phpgw_info']['user']['preferences']['common']['lang'].'.lang';

	if (file_exists($fn)){
			$fp = fopen($fn,'r');
		while ($data = fgets($fp,16000)){
			list($message_id,$app_name,$null,$content) = explode("\t",substr($data,0,-1));
			$script .= "array_lang['".str_replace("'","\'",strtolower($message_id))."'] = '".str_replace("'","\'",$content)."';\n";
		}
		fclose($fp);
	}
	
	echo "var array_lang = new Array();\n{$script}";
	
?>
