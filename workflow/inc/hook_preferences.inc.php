<?php
  /**************************************************************************\
  * eGroupWare                                                               *
  * http://www.egroupware.org                                                *
  * Written first by Joseph Engo <jengo@phpgroupware.org>                    *
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
// Only Modify the $file and $title variables.....
	$title = $appname;
	$file = array(
		'Preferences' => $GLOBALS['phpgw']->link('/preferences/preferences.php','appname=workflow')
	);

//	$workflowPreferences = ExecMethod('workflow.bopreferences.getPreferences');
//Do not modify below this line
	display_section($appname,$title,$file);
}
?>
