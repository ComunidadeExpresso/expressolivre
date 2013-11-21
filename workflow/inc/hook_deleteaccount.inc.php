<?php
  /**************************************************************************\
  * eGroupWare                                                               *
  * http://www.egroupware.org                                                *
  * Written by Mark Peters <skeeter@phpgroupware.org>                        *
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
	// Delete all records for a user
	if((int)$_POST['new_owner'] == 0)
	{
		//remove user from role mappings
		ExecMethod('workflow.workflow_rolemanager.remove_user',(int)$_POST['account_id']);
		//remove user from user/owner/next_user of instances
		ExecMethod('workflow.workflow_instancemanager.remove_user',(int)$_POST['account_id']);
		//remove user from default_next_user of activities
		ExecMethod('workflow.workflow_activitymanager.remove_user',(int)$_POST['account_id']);
	}
	else
	{
		ExecMethod('workflow.workflow_rolemanager.transfer_user', 
			Array(
				'old_user'	=> (int)$_POST['account_id'],
				'new_user'	=> (int)$_POST['new_owner']
			)
		);
		ExecMethod('workflow.workflow_instancemanager.transfer_user', 
			Array(
				'old_user'	=> (int)$_POST['account_id'],
				'new_user'	=> (int)$_POST['new_owner']
			)
		);
		ExecMethod('workflow.workflow_activitymanager.transfer_user', 
			Array(
				'old_user'	=> (int)$_POST['account_id'],
				'new_user'	=> (int)$_POST['new_owner']
			)
		);
	}
?>
