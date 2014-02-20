<?php
	/**************************************************************************\
	* eGroupWare - account administration                                      *
	* http://www.egroupware.org                                                *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/


	class boaccounts
	{
		var $so;
		var $public_functions = array(
			'add_group'    => True,
			'add_user'     => True,
			'delete_group' => True,
			'delete_user'  => True,
			'edit_group'   => True,
			'edit_user'    => True,
			'set_group_managers'	=> True
		);

		var $xml_functions = array();

		var $soap_functions = array(
			'add_user' => array(
				'in'  => array('int', 'struct'),
				'out' => array()
			)
		);

		function boaccounts()
		{
			$this->so = createobject('admin.soaccounts');
		}

		function DONTlist_methods($_type='xmlrpc')
		{
			/*
			  This handles introspection or discovery by the logged in client,
			  in which case the input might be an array.  The server always calls
			  this function to fill the server dispatch map using a string.
			*/
			if (is_array($_type))
			{
				$_type = $_type['type'] ? $_type['type'] : $_type[0];
			}
			switch($_type)
			{
				case 'xmlrpc':
					$xml_functions = array(
						'rpc_add_user' => array(
							'function'  => 'rpc_add_user',
							'signature' => array(array(xmlrpcStruct,xmlrpcStruct)),
							'docstring' => lang('Add a new account.')
						),
						'list_methods' => array(
							'function'  => 'list_methods',
							'signature' => array(array(xmlrpcStruct,xmlrpcString)),
							'docstring' => lang('Read this list of methods.')
						)
					);
					return $xml_functions;
					break;
				case 'soap':
					return $this->soap_functions;
					break;
				default:
					return array();
					break;
			}
		}

		function delete_group()
		{
			if (!@isset($_POST['account_id']) || !@$_POST['account_id'] || $GLOBALS['phpgw']->acl->check('group_access',32,'admin'))
			{
				ExecMethod('admin.uiaccounts.list_groups');
				return False;
			}
			
			$account_id = (int)$_POST['account_id'];

			$GLOBALS['phpgw']->db->lock(
				Array(
					'phpgw_accounts',
					'phpgw_app_sessions',
					'phpgw_acl'
				)
			);
				
			$old_group_list = $GLOBALS['phpgw']->acl->get_ids_for_location($account_id,1,'phpgw_group');

			@reset($old_group_list);
			while($old_group_list && $id = each($old_group_list))
			{
				$GLOBALS['phpgw']->acl->delete_repository('phpgw_group',$account_id,(int)$id[1]);
				$GLOBALS['phpgw']->session->delete_cache((int)$id[1]);
			}

			$GLOBALS['phpgw']->acl->delete_repository('%%','run',$account_id);

			if (! @rmdir($GLOBALS['phpgw_info']['server']['files_dir'].SEP.'groups'.SEP.$GLOBALS['phpgw']->accounts->id2name($account_id)))
			{
				$cd = 38;
			}
			else
			{
				$cd = 32;
			}

			$GLOBALS['phpgw']->accounts->delete($account_id);

			$GLOBALS['phpgw']->db->unlock();

			Header('Location: '.$GLOBALS['phpgw']->link('/index.php','menuaction=admin.uiaccounts.list_groups'));
			$GLOBALS['phpgw']->common->phpgw_exit();
		}

		function delete_user()
		{
			if (isset($_POST['cancel']) || $GLOBALS['phpgw']->acl->check('account_access',32,'admin'))
			{
				ExecMethod('admin.uiaccounts.list_users');
				return False;
			}
			elseif($_POST['delete_account'])
			{
				$accountid = $_POST['account_id'];
				settype($account_id,'integer');
				$account_id = get_account_id($accountid);
				// make this information also in hook available
				$lid = $GLOBALS['phpgw']->accounts->id2name($account_id);

				$GLOBALS['hook_values']['account_id'] = $account_id;
				$GLOBALS['hook_values']['account_lid'] = $lid;
				
				$singleHookValues = $GLOBALS['hook_values']+array('location' => 'deleteaccount');

				$db = $GLOBALS['phpgw']->db;
				$db->query('SELECT app_name,app_order FROM phpgw_applications WHERE app_enabled!=0 ORDER BY app_order',__LINE__,__FILE__);
				if($db->num_rows())
				{
					while($db->next_record())
					{
						$appname = $db->f('app_name');

						if($appname <> 'admin' || $appname <> 'preferences')
						{
							$GLOBALS['phpgw']->hooks->single($singleHookValues, $appname);
						}
					}
				}

				$GLOBALS['phpgw']->hooks->single('deleteaccount','preferences');
				$GLOBALS['phpgw']->hooks->single('deleteaccount','admin');

				$basedir = $GLOBALS['phpgw_info']['server']['files_dir'] . SEP . 'users' . SEP;

				if (! @rmdir($basedir . $lid))
				{
					$cd = 34;
				}
				else
				{
					$cd = 29;
				}

				ExecMethod('admin.uiaccounts.list_users');
				return False;
			}
		}

		function add_group()
		{
			if ($GLOBALS['phpgw']->acl->check('group_access',4,'admin'))
			{
				ExecMethod('admin.uiaccounts.list_groups');
				return False;
			}

			$temp_users = ($_POST['account_user']?$_POST['account_user']:Array());
			$account_user = Array();
			@reset($temp_users);
			while(list($key,$user_id) = each($temp_users))
			{
				$account_user[$user_id] = ' selected';
			}
			@reset($account_user);

			$group_permissions = ($_POST['account_apps']?$_POST['account_apps']:Array());
			$account_apps = Array();
			@reset($group_permissions);
			while(list($key,$value) = each($group_permissions))
			{
				if($value)
				{
					$account_apps[$key] = True;
				}
			}
			@reset($account_apps);

			$group_info = Array(
				'account_id'   => ($_POST['account_id']?(int)$_POST['account_id']:0),
				'account_name' => ($_POST['account_name']?$_POST['account_name']:''),
				'account_user' => $account_user,
				'account_apps' => $account_apps
			);

			$this->validate_group($group_info);

			$GLOBALS['phpgw']->db->lock(
				Array(
					'phpgw_accounts',
					'phpgw_nextid',
					'phpgw_preferences',
					'phpgw_sessions',
					'phpgw_acl',
					'phpgw_applications',
					'phpgw_app_sessions',
					'phpgw_hooks'
				)
			);

			$group = CreateObject('phpgwapi.accounts',$group_info['account_id'],'g');
			$group->acct_type = 'g';
			$account_info = array(
				'account_type'      => 'g',
				'account_lid'       => $group_info['account_name'],
				'account_passwd'    => '',
				'account_firstname' => $group_info['account_name'],
				'account_lastname'  => 'Group',
				'account_status'    => 'A',
				'account_expires'   => -1
//				'account_file_space' => $account_file_space_number . "-" . $account_file_space_type,
			);
			$group_info['account_id'] = $group->create($account_info);
			// do the following only if we got an id - the create succided
			if ($group_info['account_id'])
			{
				$apps = CreateObject('phpgwapi.applications',$group_info['account_id']);
				$apps->update_data(Array());
				reset($group_info['account_apps']);
				while(list($app,$value) = each($group_info['account_apps']))
				{
					$apps->add($app);
					$new_apps[] = $app;
				}
				$apps->save_repository();
	
				$acl = CreateObject('phpgwapi.acl',$group_info['account_id']);
				$acl->read_repository();
	
				@reset($group_info['account_user']);
				while(list($user_id,$dummy) = each($group_info['account_user']))
				{
					if(!$dummy)
					{
						continue;
					}
					$acl->add_repository('phpgw_group',$group_info['account_id'],$user_id,1);
	
					$docommit = False;
					$GLOBALS['pref'] = CreateObject('phpgwapi.preferences',$user_id);
					$t = $GLOBALS['pref']->read_repository();
					@reset($new_apps);
					while(is_array($new_apps) && list($app_key,$app_name) = each($new_apps))
					{
						if (!$t[($app_name=='admin'?'common':$app_name)])
						{
							$GLOBALS['phpgw']->hooks->single('add_def_pref', $app_name);
							$docommit = True;
						}
					}
					if ($docommit)
					{
						$GLOBALS['pref']->save_repository();
					}
				}
	
				$acl->save_repository();
	
				$basedir = $GLOBALS['phpgw_info']['server']['files_dir'] . SEP . 'groups' . SEP;
				$cd = 31;
				umask(000);
				if (! @mkdir ($basedir . $group_info['account_name'], 0707))
				{
					$cd = 37;
				}
	
				$GLOBALS['phpgw']->db->unlock();
			}
			ExecMethod('admin.uiaccounts.list_groups');
			
			return False;
		}

		function add_user()
		{
			if ($GLOBALS['phpgw']->acl->check('account_access',4,'admin'))
			{
				ExecMethod('admin.uiaccounts.list_users');
				return False;
			}
			
			$accountPrefix = '';
			if(isset($GLOBALS['phpgw_info']['server']['account_prefix']))
			{
				$accountPrefix = $GLOBALS['phpgw_info']['server']['account_prefix'];
			}

			if ($_POST['submit'])
			{
				$userData = array(
					'account_type'          => 'u',
					'account_lid'           => $accountPrefix.$_POST['account_lid'],
					'account_firstname'     => $_POST['account_firstname'],
					'account_lastname'      => $_POST['account_lastname'],
					'account_passwd'        => $_POST['account_passwd'],
					'status'                => ($_POST['account_status'] ? 'A' : ''),
					'account_status'        => ($_POST['account_status'] ? 'A' : ''),
					'old_loginid'           => ($_GET['old_loginid']?rawurldecode($_GET['old_loginid']):''),
					'account_id'            => ($_GET['account_id']?$_GET['account_id']:0),
					'account_primary_group'	=> $_POST['account_primary_group'],
					'account_passwd_2'      => $_POST['account_passwd_2'],
					'account_groups'        => $_POST['account_groups'],
					'anonymous'             => $_POST['anonymous'],
					'changepassword'        => $_POST['changepassword'],
					'account_permissions'   => $_POST['account_permissions'],
					'homedirectory'         => $_POST['homedirectory'],
					'loginshell'            => $_POST['loginshell'],
					'account_expires_never' => $_POST['never_expires'],
					'account_email'         => $_POST['account_email'],
					/* 'file_space' => $_POST['account_file_space_number'] . "-" . $_POST['account_file_space_type'] */
				);
				
				// add the primary group, to the users other groups, if not already added
				if(is_array($userData['account_groups']))
				{
					if(!in_array($userData['account_primary_group'],$userData['account_groups']))
					{
						$userData['account_groups'][] = (int)$userData['account_primary_group'];
					}
				}
				else
				{
					$userData['account_groups'] = array((int)$userData['account_primary_group']);
				}
				
				// when does the account expire
				if ($_POST['expires'] !== '' && !$_POST['never_expires'])
				{
					$jscal = CreateObject('phpgwapi.jscalendar',False);
					$userData += $jscal->input2date($_POST['expires'],False,'account_expires_day','account_expires_month','account_expires_year');
				}
				
				// do we have all needed data??
				if (!($errors = $this->validate_user($userData)) &&
					($userData['account_id'] = $account_id = $this->so->add_user($userData)))	// no error in the creation
				{
					if ($userData['anonymous']) 
					{
						$GLOBALS['phpgw']->acl->add_repository('phpgwapi','anonymous',$account_id,1);
					}
					else
					{
						$GLOBALS['phpgw']->acl->delete_repository('phpgwapi','anonymous',$account_id);
					}
					// make this information for the hooks available
					$GLOBALS['hook_values'] = $userData + array('new_passwd' => $userData['account_passwd']);
					$GLOBALS['phpgw']->hooks->process($GLOBALS['hook_values']+array(
						'location' => 'addaccount'
					),False,True);	// called for every app now, not only enabled ones

					ExecMethod('admin.uiaccounts.list_users');
					return False;
				}
				else
				{
					$ui = createobject('admin.uiaccounts');
					$ui->create_edit_user($userData['account_id'],$userData,$errors);
				}
			}
			else
			{
				ExecMethod('admin.uiaccounts.list_users');
				return False;
			}
		}

		function edit_group()
		{
			if ($GLOBALS['phpgw']->acl->check('group_access',16,'admin'))
			{
				ExecMethod('admin.uiaccounts.list_groups');
				return False;
			}

			$temp_users = ($_POST['account_user']?$_POST['account_user']:Array());
			$account_user = Array();
			@reset($temp_users);
			while($temp_users && list($key,$user_id) = each($temp_users))
			{
				$account_user[$user_id] = ' selected';
			}
			@reset($account_user);

			$group_permissions = ($_POST['account_apps']?$_POST['account_apps']:Array());
			$account_apps = Array();
			@reset($group_permissions);
			while(list($key,$value) = each($group_permissions))
			{
				if($value)
				{
					$account_apps[$key] = True;
				}
			}
			@reset($account_apps);

			$group_info = Array(
				'account_id'   => ($_POST['account_id']?(int)$_POST['account_id']:0),
				'account_name' => ($_POST['account_name']?$_POST['account_name']:''),
				'account_user' => $account_user,
				'account_apps' => $account_apps
			);

			$this->validate_group($group_info);

			// Lock tables
			$GLOBALS['phpgw']->db->lock(
				Array(
					'phpgw_accounts',
					'phpgw_preferences',
					'phpgw_config',
					'phpgw_applications',
					'phpgw_hooks',
					'phpgw_sessions',
					'phpgw_acl',
					'phpgw_app_sessions'
				)
			);

			$group = CreateObject('phpgwapi.accounts',$group_info['account_id'],'g');
			$old_group_info = $group->read_repository();

			// Set group apps
			$apps = CreateObject('phpgwapi.applications',$group_info['account_id']);
			$apps_before = $apps->read_account_specific();
			$apps->update_data(Array());
			$new_apps = Array();
			if(count($group_info['account_apps']))
			{
				reset($group_info['account_apps']);
				while(list($app,$value) = each($group_info['account_apps']))
				{
					$apps->add($app);
					if(!@$apps_before[$app] || @$apps_before == False)
					{
						$new_apps[] = $app;
					}
				}
			}
			$apps->save_repository();

			// Set new account_lid, if needed
			if($group_info['account_name'] && $old_group_info['account_lid'] <> $group_info['account_name'])
			{
				$group->data['account_lid'] = $group_info['account_name'];
				$group->data['firstname'] = $group_info['account_name'];

				$basedir = $GLOBALS['phpgw_info']['server']['files_dir'] . SEP . 'groups' . SEP;
				if (! @rename($basedir . $old_group_info['account_lid'], $basedir . $group_info['account_name']))
				{
					$cd = 39;
				}
				else
				{
					$cd = 33;
				}
			}
			else
			{
				$cd = 33;
			}

			// Set group acl
			$acl = CreateObject('phpgwapi.acl',$group_info['account_id']);
			$old_group_list = $acl->get_ids_for_location($group_info['account_id'],1,'phpgw_group');
			@reset($old_group_list);
			while($old_group_list && list($key,$user_id) = each($old_group_list))
			{
				$acl->delete_repository('phpgw_group',$group_info['account_id'],$user_id);
				if(!$group_info['account_user'][$user_id])
				{
					// If the user is logged in, it will force a refresh of the session_info
					$GLOBALS['phpgw']->db->query("update phpgw_sessions set session_action='' "
						."where session_lid='" . $GLOBALS['phpgw']->accounts->id2name($user_id)
						. '@' . $GLOBALS['phpgw_info']['user']['domain'] . "'",__LINE__,__FILE__);
					$GLOBALS['phpgw']->session->delete_cache($user_id);
				}
			}

			@reset($group_info['account_user']);
			while(list($user_id,$dummy) = each($group_info['account_user']))
			{
				if(!$dummy)
				{
					continue;
				}
				$acl->add_repository('phpgw_group',$group_info['account_id'],$user_id,1);

				// If the user is logged in, it will force a refresh of the session_info
				$GLOBALS['phpgw']->db->query("update phpgw_sessions set session_action='' "
					."where session_lid='" . $GLOBALS['phpgw']->accounts->id2name($user_id)
					. '@' . $GLOBALS['phpgw_info']['user']['domain'] . "'",__LINE__,__FILE__);
					
				$GLOBALS['phpgw']->session->delete_cache($user_id);
				
				// The following sets any default preferences needed for new applications..
				// This is smart enough to know if previous preferences were selected, use them.
				$docommit = False;
				if($new_apps)
				{
					$GLOBALS['pref'] = CreateObject('phpgwapi.preferences',$user_id);
					$t = $GLOBALS['pref']->read_repository();
					@reset($new_apps);
					while(list($app_key,$app_name) = each($new_apps))
					{
						if (!$t[($app_name=='admin'?'common':$app_name)])
						{
							$GLOBALS['phpgw']->hooks->single('add_def_pref', $app_name);
							$docommit = True;
						}
					}
				}
				if ($docommit)
				{
					$GLOBALS['pref']->save_repository();
				}
			}

			// This is down here so we are sure to catch the acl changes
			// for LDAP to update the memberuid attribute
			$group->save_repository();

			$GLOBALS['phpgw']->db->unlock();

			ExecMethod('admin.uiaccounts.list_groups');
			return False;
		}

		function edit_user()
		{
			if ($GLOBALS['phpgw']->acl->check('account_access',16,'admin'))
			{
				ExecMethod('admin.uiaccounts.list_users');
				return False;
			}
			
			$accountPrefix = '';
			if(isset($GLOBALS['phpgw_info']['server']['account_prefix']))
			{
				$accountPrefix = $GLOBALS['phpgw_info']['server']['account_prefix'];
			}

			if ($_POST['submit'])
			{
				$userData = array(
					'account_lid'           => $accountPrefix.$_POST['account_lid'],
					'firstname'             => $_POST['account_firstname'],
					'lastname'              => $_POST['account_lastname'],
					'account_passwd'        => $_POST['account_passwd'],
					'status'                => ($_POST['account_status'] ? 'A' : ''),
					'account_status'        => ($_POST['account_status'] ? 'A' : ''),
					'old_loginid'           => ($_GET['old_loginid']?rawurldecode($_GET['old_loginid']):''),
					'account_id'            => ($_GET['account_id']?$_GET['account_id']:0),
					'account_passwd_2'      => $_POST['account_passwd_2'],
					'account_groups'        => $_POST['account_groups'],
					'account_primary_group'	=> $_POST['account_primary_group'],
					'anonymous'             => $_POST['anonymous'],
					'changepassword'        => $_POST['changepassword'],
					'account_permissions'   => $_POST['account_permissions'],
					'homedirectory'         => $_POST['homedirectory'],
					'loginshell'            => $_POST['loginshell'],
					'account_expires_never' => $_POST['never_expires'],
					'email'                 => $_POST['account_email'],
					/* 'file_space' => $_POST['account_file_space_number'] . "-" . $_POST['account_file_space_type'] */
				);
				if ($userData['account_primary_group'] && (!isset($userData['account_groups']) || !in_array($userData['account_primary_group'],$userData['account_groups'])))
				{
					$userData['account_groups'][] = (int)$userData['account_primary_group'];
				}
				if ($_POST['expires'] !== '' && !$_POST['never_expires'])
				{
					$jscal = CreateObject('phpgwapi.jscalendar',False);
					$userData += $jscal->input2date($_POST['expires'],False,'account_expires_day','account_expires_month','account_expires_year');
				}
				if (!$errors = $this->validate_user($userData))
				{
					$this->save_user($userData);
					$GLOBALS['hook_values'] = $userData;
					$GLOBALS['phpgw']->hooks->process($GLOBALS['hook_values']+array(
						'location' => 'editaccount'
					),False,True);	// called for every app now, not only enabled ones)

					// check if would create a menu
					// if we do, we can't return to the users list, because
					// there are also some other plugins
					if (!ExecMethod('admin.uimenuclass.createHTMLCode','edit_user'))
					{
						ExecMethod('admin.uiaccounts.list_users');
						return False;
					}
					else
					{
						ExecMethod('admin.uiaccounts.edit_user',$_GET['account_id']);
						return False;
					}
				}
				else
				{
					$ui = createobject('admin.uiaccounts');
					$ui->create_edit_user($userData['account_id'],$userData,$errors);
				}
			}
		}

		function set_group_managers()
		{
			if($GLOBALS['phpgw']->acl->check('group_access',16,'admin') || $_POST['cancel'])
			{
				$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/index.php','menuaction=admin.uiaccounts.list_groups'));
				$GLOBALS['phpgw']->common->phpgw_exit();
			}
			elseif($_POST['submit'])
			{
				$acl = CreateObject('phpgwapi.acl',(int)$_POST['account_id']);
				
				$users = $GLOBALS['phpgw']->accounts->member($_POST['account_id']);
				@reset($users);
				while($managers && list($key,$user) = each($users))
				{
					$acl->add_repository('phpgw_group',(int)$_POST['account_id'],$user['account_id'],1);
				}
				$managers = $_POST['managers'];
				@reset($managers);
				while($managers && list($key,$manager) = each($managers))
				{
					$acl->add_repository('phpgw_group',(int)$_POST['account_id'],$manager,(1 + PHPGW_ACL_GROUP_MANAGERS));
				}
			}
			$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/index.php','menuaction=admin.uiaccounts.list_groups'));
			$GLOBALS['phpgw']->common->phpgw_exit();
		}

		function validate_group($group_info)
		{
			$errors = Array();
			
			$group = CreateObject('phpgwapi.accounts',$group_info['account_id'],'g');
			$group->read_repository();

			if(!$group_info['account_name'])
			{
				$errors[] = lang('You must enter a group name.');
			}

			if($group_info['account_name'] != $group->id2name($group_info['account_id']))
			{
				if ($group->exists($group_info['account_name']))
				{
					$errors[] = lang('Sorry, that group name has already been taken.');
				}
			}

		/*
			if (preg_match ("/\D/", $account_file_space_number))
			{
				$errors[] = lang ('File space must be an integer');
			}
		*/
			if(count($errors))
			{
				$ui = createobject('admin.uiaccounts');
				$ui->create_edit_group($group_info,$errors);
				$GLOBALS['phpgw']->common->phpgw_exit();
			}
		}

		/* checks if the userdata are valid
		 returns FALSE if the data are correct
		 otherwise the error array
		*/
		function validate_user(&$_userData)
		{
			$totalerrors = 0;

			if ($GLOBALS['phpgw_info']['server']['account_repository'] == 'ldap' && 
				(!$_userData['account_lastname'] && !$_userData['lastname']))
			{
				$error[$totalerrors] = lang('You must enter a lastname');
				++$totalerrors;
			}

			if (!$_userData['account_lid'])
			{
				$error[$totalerrors] = lang('You must enter a loginid');
				++$totalerrors;
			}
			
			if(!in_array($_userData['account_primary_group'],$_userData['account_groups']))
			{
				$error[$totalerrors] = lang('The groups must include the primary group');
				++$totalerrors;
			}
			
			if ($_userData['old_loginid'] != $_userData['account_lid']) 
			{
			   if ($GLOBALS['phpgw']->accounts->exists($_userData['account_lid']))
				{
				   if ($GLOBALS['phpgw']->accounts->exists($_userData['account_lid']) && $GLOBALS['phpgw']->accounts->get_type($_userData['account_lid'])=='g')
				   {
					  $error[$totalerrors] = lang('There already is a group with this name. Userid\'s can not have the same name as a groupid');
				   }
				   else
				   {
					  $error[$totalerrors] = lang('That loginid has already been taken');
				   }
				   ++$totalerrors;
				}
			}

			if ($_userData['account_passwd'] || $_userData['account_passwd_2']) 
			{
				if ($_userData['account_passwd'] != $_userData['account_passwd_2']) 
				{
					$error[$totalerrors] = lang('The two passwords are not the same');
					++$totalerrors;
				}
			}

			if (!count($_userData['account_permissions']) && !count($_userData['account_groups'])) 
			{
				$error[$totalerrors] = lang('You must add at least 1 permission or group to this account');
				++$totalerrors;
			}

			if ($_userData['account_expires_month'] || $_userData['account_expires_day'] || $_userData['account_expires_year'] || $_userData['account_expires_never'])
			{
				if($_userData['account_expires_never'])
				{
					$_userData['expires'] = -1;
					$_userData['account_expires'] = $_userData['expires'];
				}
				else
				{
					if (! checkdate($_userData['account_expires_month'],$_userData['account_expires_day'],$_userData['account_expires_year']))
					{
						$error[$totalerrors] = lang('You have entered an invalid expiration date');
						++$totalerrors;
					}
					else
					{
						$_userData['expires'] = mktime(2,0,0,$_userData['account_expires_month'],$_userData['account_expires_day'],$_userData['account_expires_year']);
						$_userData['account_expires'] = $_userData['expires'];
					}
				}
			}
			else
			{
				$_userData['expires'] = -1;
				$_userData['account_expires'] = $_userData['expires'];
			}

		/*
			$check_account_file_space = explode ('-', $_userData['file_space']);
			if (preg_match ("/\D/", $check_account_file_space[0]))
			{
				$error[$totalerrors] = lang ('File space must be an integer');
				++$totalerrors;
			}
		*/

			if ($totalerrors == 0)
			{
				return FALSE;
			}
			else
			{
				return $error;
			}
		}

		/* stores the userdata */
		function save_user($_userData)
		{
			$account = CreateObject('phpgwapi.accounts',$_userData['account_id'],'u');
			$account->update_data($_userData);
			$account->save_repository();
			if ($_userData['account_passwd'])
			{
				$auth = CreateObject('phpgwapi.auth');
				$auth->change_password($old_passwd, $_userData['account_passwd'], $_userData['account_id']);
				$GLOBALS['hook_values']['account_id'] = $_userData['account_id'];
				$GLOBALS['hook_values']['old_passwd'] = $old_passwd;
				$GLOBALS['hook_values']['new_passwd'] = $_userData['account_passwd'];

				$GLOBALS['phpgw']->hooks->process($GLOBALS['hook_values']+array(
					'location' => 'changepassword'
				),False,True);	// called for every app now, not only enabled ones)
			}

			$apps = CreateObject('phpgwapi.applications',array((int)$_userData['account_id'],'u'));

			$apps->account_id = $_userData['account_id'];
			if ($_userData['account_permissions'])
			{
				while($app = each($_userData['account_permissions'])) 
				{
					if($app[1]) 
					{
						$apps->add($app[0]);
					}
				}
			}
			$apps->save_repository();

			$account = CreateObject('phpgwapi.accounts',$_userData['account_id'],'u');
			$allGroups = $account->get_list('groups');

			if ($_userData['account_groups'])
			{
				reset($_userData['account_groups']);
				while (list($key,$value) = each($_userData['account_groups']))
				{
					$newGroups[$value] = $value;
				}
			}

			$acl = CreateObject('phpgwapi.acl',$_userData['account_id']);

			reset($allGroups);
			while (list($key,$groupData) = each($allGroups)) 
			{
				/* print "$key,". $groupData['account_id'] ."<br />";*/
				/* print "$key,". $_userData['account_groups'][1] ."<br />"; */

				if ($newGroups[$groupData['account_id']]) 
				{
					$acl->add_repository('phpgw_group',$groupData['account_id'],$_userData['account_id'],1);
				}
				else
				{
					$acl->delete_repository('phpgw_group',$groupData['account_id'],$_userData['account_id']);
				}
			}
			if ($_userData['anonymous']) 
			{
				$acl->add_repository('phpgwapi','anonymous',$_userData['account_id'],1);
			}
			else
			{
				$acl->delete_repository('phpgwapi','anonymous',$_userData['account_id']);
			}
			if ($_userData['changepassword']) 
			{
				$GLOBALS['phpgw']->acl->add_repository('preferences','changepassword',$_userData['account_id'],1);
			}
			else
			{
				$GLOBALS['phpgw']->acl->delete_repository('preferences','changepassword',$_userData['account_id']);
			}
			$GLOBALS['phpgw']->session->delete_cache((int)$_userData['account_id']);
		}

		function load_group_users($account_id)
		{
			$temp_user = $GLOBALS['phpgw']->acl->get_ids_for_location($account_id,1,'phpgw_group');
			if(!$temp_user)
			{
				return Array();
			}
			else
			{
				$group_user = $temp_user;
			}
			$account_user = Array();
			while (list($key,$user) = each($group_user))
			{
				$account_user[$user] = ' selected';
			}
			@reset($account_user);
			return $account_user;
		}

		function load_group_managers($account_id)
		{
			$temp_user = $GLOBALS['phpgw']->acl->get_ids_for_location($account_id,PHPGW_ACL_GROUP_MANAGERS,'phpgw_group');
			if(!$temp_user)
			{
				return Array();
			}
			else
			{
				$group_user = $temp_user;
			}
			$account_user = Array();
			while (list($key,$user) = each($group_user))
			{
				$account_user[$user] = ' selected';
			}
			@reset($account_user);
			return $account_user;
		}

		function load_group_apps($account_id)
		{
			$apps = CreateObject('phpgwapi.applications',(int)$account_id);
			$app_list = $apps->read_account_specific();
			$account_apps = Array();
			while(list($key,$app) = each($app_list))
			{
				$account_apps[$app['name']] = True;
			}
			@reset($account_apps);
			return $account_apps;
		}

		// xmlrpc functions

		function rpc_add_user($data)
		{
			exit;

			if (!$errors = $this->validate_user($data))
			{
				$result = $this->so->add_user($data);
			}
			else
			{
				$result = $errors;
			}
			return $result;
		}
	}
?>
