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
		
	if (empty($GLOBALS['phpgw_info']['server']['translation_system']))
	{
		$GLOBALS['phpgw_info']['server']['translation_system'] = 'sql';
	}
	include(PHPGW_API_INC.'/class.translation_' . $GLOBALS['phpgw_info']['server']['translation_system'].'.inc.php'); 
?>
