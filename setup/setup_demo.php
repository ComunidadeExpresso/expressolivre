<?php
  /**************************************************************************\
  * eGroupWare                                                               *
  * http://www.egroupware.org                                                *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/


	// Little file to setup a demo install

	$GLOBALS['phpgw_info']['flags'] = array(
		'noheader'   => True,
		'nonavbar'   => True,
		'currentapp' => 'home',
		'noapi'      => True
	);
	include('./inc/functions.inc.php');

	// Authorize the user to use setup app and load the database
	// Does not return unless user is authorized
	if(!$GLOBALS['phpgw_setup']->auth('Config') || get_var('cancel',Array('POST')))
	{
		Header('Location: index.php');
		exit;
	}

	if(!get_var('submit',Array('POST')))
	{
		$tpl_root = $GLOBALS['phpgw_setup']->html->setup_tpl_dir('setup');
		$setup_tpl = CreateObject('setup.Template',$tpl_root);
		$setup_tpl->set_file(array(
			'T_head'       => 'head.tpl',
			'T_footer'     => 'footer.tpl',
			'T_alert_msg'  => 'msg_alert_msg.tpl',
			'T_login_main' => 'login_main.tpl',
			'T_login_stage_header' => 'login_stage_header.tpl',
			'T_setup_demo' => 'setup_demo.tpl'
		));
		$setup_tpl->set_block('T_login_stage_header','B_multi_domain','V_multi_domain');
		$setup_tpl->set_block('T_login_stage_header','B_single_domain','V_single_domain');

		$GLOBALS['phpgw_setup']->html->show_header(lang('Demo Server Setup'));

		$setup_tpl->set_var('action_url','setup_demo.php');
		$setup_tpl->set_var('description',lang('<b>This will create the Admin account</b>'));
		$setup_tpl->set_var('lang_deleteall',lang('Delete all existing SQL accounts, groups, ACLs and preferences (normally not necessary)?'));

		$setup_tpl->set_var('detailadmin',lang('Details for Admin account'));
		$setup_tpl->set_var('adminusername',lang('Admin username'));
		$setup_tpl->set_var('adminfirstname',lang('Admin first name'));
		$setup_tpl->set_var('adminlastname',lang('Admin last name'));
		$setup_tpl->set_var('adminpassword',lang('Admin password'));
		$setup_tpl->set_var('adminpassword2',lang('Re-enter password'));
		$setup_tpl->set_var('create_demo_accounts',lang('Create demo accounts'));

		$setup_tpl->set_var('lang_submit',lang('Save'));
		$setup_tpl->set_var('lang_cancel',lang('Cancel'));
		$setup_tpl->pparse('out','T_setup_demo');
		$GLOBALS['phpgw_setup']->html->show_footer();
	}
	else
	{
		/* Posted admin data */
		$passwd   = get_var('passwd',Array('POST'));
		$passwd2  = get_var('passwd2',Array('POST'));
		$username = get_var('username',Array('POST'));
		$fname    = get_var('fname2',Array('POST'));
		$lname    = get_var('lname',Array('POST'));

		if($passwd != $passwd2)
		{
			echo lang('Passwords did not match, please re-enter') . '.';
			exit;
		}
		if(!$username)
		{
			echo lang('You must enter a username for the admin') . '.';
			exit;
		}

		$GLOBALS['phpgw_setup']->loaddb();
		/* Begin transaction for acl, etc */
		$GLOBALS['phpgw_setup']->db->transaction_begin();

		if($_POST['delete_all'])
		{
			/* Now, clear out existing tables */
			$GLOBALS['phpgw_setup']->db->query('DELETE FROM phpgw_accounts');
			$GLOBALS['phpgw_setup']->db->query('DELETE FROM phpgw_preferences');
			$GLOBALS['phpgw_setup']->db->query('DELETE FROM phpgw_acl');
		}
		/* Create the demo groups */
		$defaultgroupid = (int)$GLOBALS['phpgw_setup']->add_account('Default','Default','Group',False,False);
		$admingroupid   = (int)$GLOBALS['phpgw_setup']->add_account('Admins','Admin','Group',False,False);
		
		if (!$defaultgroupid || !$admingroupid)
		{
			echo '<p><b>'.lang('Error in group-creation !!!')."</b></p>\n";
			echo '<p>'.lang('click <a href="index.php">here</a> to return to setup.')."</p>\n";
			$GLOBALS['phpgw_setup']->db->transaction_abort();
			exit;
		}

		/* Group perms for the default group */
		$GLOBALS['phpgw_setup']->add_acl(array('addressbook','calendar','infolog','email','preferences','manual'),'run',$defaultgroupid);

		// give admin access to all apps, to save us some support requests
		$all_apps = array();
		$GLOBALS['phpgw_setup']->db->query('SELECT app_name FROM phpgw_applications WHERE app_enabled<3');
		while ($GLOBALS['phpgw_setup']->db->next_record())
		{
			$all_apps[] = $GLOBALS['phpgw_setup']->db->f('app_name');
		}
		$GLOBALS['phpgw_setup']->add_acl($all_apps,'run',$admingroupid);

		function insert_default_prefs($accountid)
		{
			# Modificacoes para o Expresso Livre
			$defaultprefs = array(
				'common' => array(
					'maxmatchs'  		=> 20,
					'template_set'		=> 'celepar',
					'theme'			=> 'celepar',
					'navbar_format'		=> 'icons',
					'tz_offset'		=> 0,
					'dateformat'		=> 'd/m/Y',
					'timeformat'		=> '24',
					'country'		=> 'BR',
					'lang'			=> 'pt-br',
					'show_currentusers'	=> False,
					'currency'		=> 'R$',
					'account_selection'	=> 'selectbox',
					'account_display'	=> 'firstall',
					'show_help'		=> False,
					'start_and_logout_icons'=> 'yes',
					'max_icons'		=> '10',
					'auto_hide_sidebox'	=> True,
					'click_or_onmouseover'	=> 'click',
					'disable_slider_effects'=> True,
					'disable_pngfix'	=> False,
					'show_generation_time'	=> False,
				),
				'calendar' => array(
					'workdaystarts' => 7,
					'workdayends'   => 17,
					'interval'	=> '30',
					'weekdaystarts' => 'Monday',
					'defaultcalendar' => 'day',
					'planner_start_with_group' => $GLOBALS['defaultgroupid'],
				),
			);

			foreach ($defaultprefs as $app => $prefs)
			{
				$prefs = $GLOBALS['phpgw_setup']->db->db_addslashes(serialize($prefs));
				$GLOBALS['phpgw_setup']->db->query("INSERT INTO phpgw_preferences(preference_owner,preference_app,preference_value) VALUES($accountid,'$app','$prefs')",__FILE__,__LINE__);
			}
		}
		insert_default_prefs(-1);	// set some default prefs (Expresso Livre)

		/* Creation of the demo accounts is optional - the checkbox is on by default. */
		if(get_var('create_demo',Array('POST')))
		{
			// Create 3 demo accounts
			$GLOBALS['phpgw_setup']->add_account('demo','Demo','Account','guest');
			$GLOBALS['phpgw_setup']->add_account('demo2','Demo2','Account','guest');
			$GLOBALS['phpgw_setup']->add_account('demo3','Demo3','Account','guest');
		}

		/* Create records for administrator account, with Admins as primary and Default as additional group */
		$accountid = $GLOBALS['phpgw_setup']->add_account($username,$fname,$lname,$passwd,'Admins',True);
		if (!$accountid)
		{
			echo '<p><b>'.lang('Error in admin-creation !!!')."</b></p>\n";
			echo '<p>'.lang('click <a href="index.php">here</a> to return to setup.')."</p>\n";
			$GLOBALS['phpgw_setup']->db->transaction_abort();
			exit;
		}
		$GLOBALS['phpgw_setup']->add_acl('phpgw_group',$admingroupid,$accountid);
		$GLOBALS['phpgw_setup']->add_acl('phpgw_group',$defaultgroupid,$accountid);

		/* Clear the access log, since these are all new users anyway */
		$GLOBALS['phpgw_setup']->db->query('DELETE FROM phpgw_access_log');

		$GLOBALS['phpgw_setup']->db->transaction_commit();

		Header('Location: index.php');
		exit;
	}
?>
