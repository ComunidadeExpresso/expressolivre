<?php
  /**************************************************************************\
  * phpGroupWare                                                             *
  * http://www.phpgroupware.org                                              *
  * Written by Joseph Engo <jengo@phpgroupware.org>                          *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/
	/**
	 * @package Workflow
	 * @license http://www.gnu.org/copyleft/gpl.html GPL
	 */
{
	/* only modify the $file and $title variables..... */
	$title = $appname;
	$file = array
	(
		'Default Config Values' => $GLOBALS['phpgw']->link('/index.php','menuaction=admin.uiconfig.index&appname=' . $appname)
	);

	/* do not modify below this line */
	display_section($appname, $title, $file);
}
?>
