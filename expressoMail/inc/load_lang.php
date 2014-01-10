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
		
// Load the lang of the module.
	if(isset($offline_language)) { //Expresso offline, quando o gears sincroniza com uma nova versão, não é dado acesso as sessões do usuário, e é preciso o nome do arquivo de linguagens na session abaixo.
		$_SESSION['phpgw_info']['expressomail']['user']['preferences']['common']['lang'] = $offline_language;
	}
	
	$array_keys = array();
	$fn = 'setup/phpgw_'.$_SESSION['phpgw_info']['expressomail']['user']['preferences']['common']['lang'].'.lang';			
	if (file_exists($fn)){
		$fp = fopen($fn,'r');
		while ($data = fgets($fp,16000)){
			list($message_id,$app_name,$null,$content) = explode("\t",substr($data,0,-1));
			$expressomaillang[$message_id] =  $content;
			$_SESSION['phpgw_info']['expressomail']['lang'][$message_id] = $content;
		}
		fclose($fp);
	}
	
	foreach($expressomaillang as $key => $value)
		$script .= "array_lang['".str_replace("'","\'",strtolower($key))."'] = '".str_replace("'","\'",$value)."';\n";
	echo "<script type='text/javascript'>$script</script>";
?>
