<?php
if(!isset($GLOBALS['phpgw_info'])){
	$GLOBALS['phpgw_info']['flags'] = array(
		'currentapp' => 'filemanager',
		'nonavbar'   => true,
		'noheader'   => true
	);
}
require_once '../header.inc.php';


	/**************************************************************************\
	* eGroupWare                                                               *
	* http://www.egroupware.org                                                *
	* Written by Joseph Engo <jengo@phpgroupware.org>                          *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

{
// Only Modify the $file and $title variables.....
	$title = $appname;
	$file = Array(
		'Site Configuration' 	=> $GLOBALS['phpgw']->link('/index.php','menuaction=admin.uiconfig.index&appname=' . $appname),
		'Folders management' 	=> $GLOBALS['phpgw']->link('/index.php','menuaction='.$appname.'.uiconfig.folders'),
		'Quota management' 		=> $GLOBALS['phpgw']->link('/index.php','menuaction='.$appname.'.uiconfig.quota'),
		'Permissions of groups and users' => $GLOBALS['phpgw']->link('/index.php','menuaction='.$appname.'.uiconfig.groups_users'),
		'Email notify uploads' 	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction='.$appname.'.uiconfig.notifyUploads')
	);
//Do not modify below this line
	display_section($appname,$title,$file);
}
?>
