<?php
  /**************************************************************************\
  * eGroupWare - Calendar                                                    *
  * http://www.egroupware.org                                                *
  * Based on Webcalendar by Craig Knudsen <cknudsen@radix.net>               *
  *          http://www.radix.net/~cknudsen                                  *
  * Modified by Mark Peters <skeeter@phpgroupware.org>                       *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/


	$phpgw_flags = Array(
		'currentapp'	=>	'calendar',
		'noheader'	=>	True,
		'nonavbar'	=>	True,
		'noappheader'	=>	True,
		'noappfooter'	=>	True,
		'nofooter'	=>	True
	);

	$GLOBALS['phpgw_info']['flags'] = $phpgw_flags;
	include('../header.inc.php');
	if(!is_object($GLOBALS['phpgw']->datetime))
	{
		$GLOBALS['phpgw']->datetime = CreateObject('phpgwapi.date_time');
	}

	$parms = Array(
# 		'menuaction'=> 'calendar.uicalendar.index',
		'date'		=> date('Ymd',$GLOBALS['phpgw']->datetime->users_localtime)
	);

	//echo 'Local DateTime: '.date('Ymd H:i:s',$GLOBALS['phpgw']->datetime->users_localtime).'<br>'."\n";

#	$GLOBALS['phpgw']->redirect_link('/index.php',$parms);
	ExecMethod('calendar.uicalendar.index',$parms);
	$GLOBALS['phpgw']->common->phpgw_exit();

?>
