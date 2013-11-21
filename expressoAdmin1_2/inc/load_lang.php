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
		
	$lang = array();
	if (!empty($_SESSION['phpgw_info']['expressoAdmin']['lang']))
	{
		foreach($_SESSION['phpgw_info']['expressoAdmin']['lang'] as $message_id=>$content)
		{
			$lang[str_replace(" ", "_", (strtolower($message_id)) )] = $content;
		}
	}
	echo serialize($lang);
	exit;
?>
