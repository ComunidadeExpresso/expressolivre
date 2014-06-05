<?php
	/**************************************************************************\
	* eGroupWare - PHPBrain                                                    *
	* http://www.egroupware.org                                                *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* Basic information about this app */
	$setup_info['workflow']['name']			= 'workflow';
	$setup_info['workflow']['title']		= 'Workflow Management';
	$setup_info['workflow']['version']		= '2.5.2';
	$setup_info['workflow']['app_order']	= 10;
	$setup_info['workflow']['enable']		= 1;
	$setup_info['workflow']['author']		= 'See changeLog for complete list of developers.';
	$setup_info['workflow']['note']			= 'Workflow Engine';
	$setup_info['workflow']['license']		= 'GPL';
	$setup_info['workflow']['description']		= 'Workflow Management';
	$setup_info['workflow']['maintainer']		= 'Mauricio Luiz Viani';
	$setup_info['workflow']['maintainer_email']	= 'viani@celepar.pr.gov.br';
	$setup_info['workflow']['tables']		= array(
								'egw_wf_activities',
								'egw_wf_activity_roles',
								'egw_wf_instance_activities',
								'egw_wf_instances',
								'egw_wf_processes',
								'egw_wf_roles',
								'egw_wf_transitions',
								'egw_wf_user_roles',
								'egw_wf_workitems',
								'egw_wf_process_config',
								'egw_wf_activity_agents',
								'egw_wf_agent_mail_smtp',
								'egw_wf_interinstance_relations',
								'egw_wf_external_application',
								'egw_wf_admin_access',
								'egw_wf_user_cache',
								'egw_wf_jobs',
								'egw_wf_job_logs'
							);

	/* The hooks this app includes, needed for hooks registration */
	$setup_info['workflow']['hooks'][] = 'about';
	$setup_info['workflow']['hooks'][] = 'admin';
	$setup_info['workflow']['hooks'][] = 'add_def_pref';
	$setup_info['workflow']['hooks'][] = 'config';
	$setup_info['workflow']['hooks'][] = 'manual';
	$setup_info['workflow']['hooks'][] = 'preferences';
	$setup_info['workflow']['hooks'][] = 'settings';
	$setup_info['workflow']['hooks'][] = 'sidebox_menu';
	$setup_info['workflow']['hooks'][] = 'acl_manager';
	$setup_info['workflow']['hooks'][] = 'deleteaccount';
	$setup_info['workflow']['hooks'][] = 'home';

	/* Dependencies for this app to work */
	$setup_info['workflow']['depends'][] = array(
		'appname' => 'phpgwapi',
		'versions' => Array('2.5.1.1')
	);
	$setup_info['workflow']['depends'][] = array(
		'appname' => 'preferences',
		'versions' => Array('2.5.1')
	);
?>
