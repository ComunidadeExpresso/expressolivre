<?php
/**************************************************************************\
* eGroupWare - Knowledge Base                                              *
* http://www.egroupware.org                                                *
* -----------------------------------------------                          *
*  This program is free software; you can redistribute it and/or modify it *
*  under the terms of the GNU General Public License as published by the   *
*  Free Software Foundation; either version 2 of the License, or (at your  *
*  option) any later version.                                              *
\**************************************************************************/
/**
 * @package Workflow
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 */

require_once 'common.inc.php';
require_once 'engine/config.egw.inc.php';

{
	$workflowACL = Factory::getInstance('workflow_acl');
	$userID = $GLOBALS['phpgw_info']['user']['account_id'];
	$apptitle = $GLOBALS['phpgw_info']['apps'][$appname]['title'];
	$isWorkflowAdmin = $workflowACL->checkWorkflowAdmin($userID);
	$isEgroupwareAdmin = $GLOBALS['phpgw']->acl->check('run',1,'admin');

	// Configuration
	$file = array();
	$menu_title = lang('%1 Configuration', $apptitle);

	// checking for workflow admin acl
	/* check if the user can administrate processes */
	if (($GLOBALS['phpgw']->acl->check('admin_workflow',1,'workflow')) || ($isWorkflowAdmin))
		$file['Admin Processes'] 	= $GLOBALS['phpgw']->link('/index.php','menuaction=workflow.ui_adminprocesses.form');

	/* check if the user can administrate at least on process (this is required for him to move instances) */
	if ($workflowACL->checkUserGroupAccessToType('PRO', $userID) || $isWorkflowAdmin)
		$file['Move Instances'] = $GLOBALS['phpgw']->link('/index.php','menuaction=workflow.ui_move_instances.form');

	/* check if the user can administrate the orgchart */
	if ($workflowACL->checkUserGroupAccessToType('ORG', $userID,0) || $isWorkflowAdmin)
		$file['Organization Chart']	= $GLOBALS['phpgw']->link('/index.php','menuaction=workflow.ui_orgchart.draw');

	if ($isWorkflowAdmin)
		$file['External Applications'] = $GLOBALS['phpgw']->link('/index.php','menuaction=workflow.ui_external_applications.draw');

	/* if the user is the egroupware admin, he can access some privileged areas */
	if ($isEgroupwareAdmin)
		$file['Default config values'] 	= $GLOBALS['phpgw']->link('/index.php',array(
			'menuaction' 	=> 'admin.uiconfig.index',
			'appname' 	=> $appname,
		));

	if ($isWorkflowAdmin) { 
		$file['Access Control List']= $GLOBALS['phpgw']->link('/index.php','menuaction=workflow.ui_adminaccess.form');
		$file['Reports']= $GLOBALS['phpgw']->link('/index.php','menuaction=workflow.reports.form');	
	}

	/* every user can access the preference area */
	$file['Workflow Preferences'] = $GLOBALS['phpgw']->link('/preferences/preferences.php','appname=workflow');

	/* display the current sidebox */
	display_sidebox($appname,$menu_title,$file);

	/* check if the user can monitor at least one process */
	if ($workflowACL->checkUserGroupAccessToType('MON', $userID) || $isWorkflowAdmin)
	{
		$file = array();
		$menu_title 		= lang('%1 Monitoring', $apptitle);
		$file['Monitors'] 	= $GLOBALS['phpgw']->link('/index.php','menuaction=workflow.ui_monitors.form');
		display_sidebox($appname,$menu_title,$file);
	}

	/* link to the main page of the Workflow module */
	$file = array();
	$menu_title = lang('%1 Menu', $apptitle);
	$file['User Interface']      = $GLOBALS['phpgw']->link('/workflow/index.php','');

	/* display the current sidebox */
	display_sidebox($appname,$menu_title,$file);
}
?>
