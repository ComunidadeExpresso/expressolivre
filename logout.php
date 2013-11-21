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


	$phpgw_info = array();
	$GLOBALS['phpgw_info']['flags'] = array(
		'disable_Template_class' => True,
		'currentapp'             => 'logout',
		'noheader'               => True,
		'nofooter'               => True,
		'nonavbar'               => True
	);
	include(dirname( __FILE__ ).'/header.inc.php');

	$GLOBALS['sessionid'] = get_var('sessionid',array('GET','COOKIE'));
	$GLOBALS['kp3']       = get_var('kp3',array('GET','COOKIE'));
	$account_id = $GLOBALS['phpgw_info']['user']['account_id'];
	$verified = $GLOBALS['phpgw']->session->verify();
	if ($verified)
	{
		if (file_exists($GLOBALS['phpgw_info']['server']['temp_dir'] . SEP . $GLOBALS['sessionid']))
		{
			$dh = opendir($GLOBALS['phpgw_info']['server']['temp_dir'] . SEP . $GLOBALS['sessionid']);
			while ($file = readdir($dh))
			{
				if ($file != '.' && $file != '..')
				{
					unlink($GLOBALS['phpgw_info']['server']['temp_dir'] . SEP . $GLOBALS['sessionid'] . SEP . $file);
				}
			}
			rmdir($GLOBALS['phpgw_info']['server']['temp_dir'] . SEP . $GLOBALS['sessionid']);
		}
		$GLOBALS['phpgw']->hooks->process('logout');
		$GLOBALS['phpgw']->session->destroy($GLOBALS['sessionid'],$GLOBALS['kp3']);
	}
	else
	{	
		if(is_object($GLOBALS['phpgw']->log) && $account_id != '')
		{
			$GLOBALS['phpgw']->log->write(array(
				'text' => 'W-VerifySession, could not verify session during logout',
				'line' => __LINE__,
				'file' => __FILE__
			));
		}
	}
	$GLOBALS['phpgw']->session->phpgw_setcookie('sessionid');
	$GLOBALS['phpgw']->session->phpgw_setcookie('kp3');
	$GLOBALS['phpgw']->session->phpgw_setcookie('domain');
	if($GLOBALS['phpgw_info']['server']['sessions_type'] == 'php4')
	{
		$GLOBALS['phpgw']->session->phpgw_setcookie(PHPGW_PHPSESSID);
	}

	$GLOBALS['phpgw']->redirect($GLOBALS['phpgw_info']['server']['webserver_url'].'/login.php?cd=1');
?>
