<?php
/**************************************************************************\
* eGroupWare                                                 			   *
* http://www.egroupware.org                                                *
* --------------------------------------------                             *
*  This program is free software; you can redistribute it and/or modify it *
*  under the terms of the GNU General Public License as published by the   *
*  Free Software Foundation; either version 2 of the License, or (at your  *
*  option) any later version.                                              *
\**************************************************************************/

/* Including common stuff to prepare workflow to run. A session start is needed */
require_once 'inc/common.inc.php';

/* if the menuaction variable is set, then let the expresso index deal with it */
if (isset($_GET['menuaction']))
{
	/* in case it's an Ajax call from the processes, then check for expired session */
	if (($_GET['menuaction'] == 'workflow.run_activity.goAjax'))
	{
		if (empty($_SESSION['phpgw_session']['session_id']))
		{
			/* the session is expired, return a NanoAjax exception */
			require_once dirname(__FILE__) . '/inc/nano/JSON.php';
			require_once dirname(__FILE__) . '/inc/nano/NanoJsonConverter.class.php';
			$nanoController = &Factory::newInstance('NanoController');
			$nanoController->throwErrorOnAllVirtualRequests('__NANOAJAX_SESSION_EXPIRED__');
			exit;
		}
	}
	chdir('..');
	require_once 'index.php';
}
else
{
	$phpgw_info = array();
	$GLOBALS['phpgw_info']['flags'] = array(
		'currentapp' => 'workflow',
		'noheader'   => True,
		'nonavbar'   => True
	);
	require_once '../header.inc.php';

	if (isset($_GET['start_tab']))
	{
		$start_tab = $_GET['start_tab'];
	}
	else
	{
		$GLOBALS['phpgw']->preferences->read_repository();
		$start_tab = $GLOBALS['phpgw_info']['user']['preferences']['workflow']['startpage'];
		if (is_null($start_tab))
			$start_tab = 1;
	}
	ExecMethod('workflow.ui_userinterface.draw', $start_tab);
}
?>
