<?php
	/**************************************************************************\
	* eGroupWare - Webpage News Admin                                          *
	* http://www.egroupware.org                                                *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	* --------------------------------------------                             *
	* This program was sponsered by Golden Glair productions                   *
	* http://www.goldenglair.com                                               *
	\**************************************************************************/

{
// Only Modify the $file and $title variables.....
	$title = $appname;
	$file = array(
		'Preferences'     => $GLOBALS['phpgw']->link('/preferences/preferences.php','appname='.$appname),
	);
//Do not modify below this line
	display_section($appname,$title,$file);
}
?>
