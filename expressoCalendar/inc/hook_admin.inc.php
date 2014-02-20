<?php
	/***********************************************************************************\
	* Expresso Calendar                										   
	* 
	* ---------------------------------------------------------------------------------*
	*  This program is free software; you can redistribute it and/or modify it		   *
	*  under the terms of the GNU General Public License as published by the		   *
	*  Free Software Foundation; either version 2 of the License, or (at your		   *
	*  option) any later version.													   *
	\***********************************************************************************/

	// Only Modify the $file and $title variables.....
	$title = $appname;
	$file = Array(
		'ExpressoCalendar migration' => $GLOBALS['phpgw']->link('/index.php','menuaction=admin.uiconfig.index&amp;appname=' . $appname. '&amp;config=migra'),
        'Global Settings' => $GLOBALS['phpgw']->link('/index.php','menuaction=admin.uiconfig.index&amp;appname=' . $appname)
	);
	//Do not modify below this line
	display_section($appname,$title,$file);

?>