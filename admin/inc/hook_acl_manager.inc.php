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


	$GLOBALS['acl_manager']['admin']['site_config_access'] = array(
		'name' => 'Deny access to site configuration',
		'rights' => array(
			'List config settings'   => 1,
			'Change config settings' => 2
		)
	);	// added and working ralfbecker

	$GLOBALS['acl_manager']['admin']['account_access'] = array(
		'name' => 'Deny access to user accounts',
		'rights' => array(
			'Account list'    => 1,
			'Search accounts' => 2,
			'Add account'     => 4,
			'View account'    => 8,
			'Edit account'    => 16,
			'Delete account'  => 32,
			'change ACL Rights' => 64
		)
	);	// was already there and seems to work ralfbecker

	$GLOBALS['acl_manager']['admin']['group_access'] = array(
		'name' => 'Deny access to groups',
		'rights' => array(
			'Group list'    => 1,
			'Search groups' => 2,
			'Add group'     => 4,
//			'View group'    => 8,			// Will be added in the future
			'Edit group'    => 16,
			'Delete group'  => 32
		)
	);	// was already there and seems to work ralfbecker

/* not usable at the moment
	$GLOBALS['acl_manager']['admin']['peer_server_access'] = array(
		'name' => 'Deny access to peer servers',
		'rights' => array(
			'Peer server list'    => 1,
			'Search peer servers' => 2,
			'Add peer server'     => 4,
//			'View peer server'    => 8,		// there's no view-routine atm.
			'Edit peer server'    => 16,
			'Delete peer server'  => 32
		)
	);
*/
	$GLOBALS['acl_manager']['admin']['applications_access'] = array(
		'name' => 'Deny access to applications',
		'rights' => array(
			'Applications list' => 1,
			'Add application'   => 2,
			'Edit application'  => 4,
			'Delete application'  => 8,
			'Register application hooks' => 16
		)
	);	// added and working ralfbecker

	$GLOBALS['acl_manager']['admin']['global_categories_access'] = array(
		'name' => 'Deny access to global categories',
		'rights' => array(
			'Categories list'   => 1,
			'Search categories' => 2,
			'Add category'      => 4,
			'View category'     => 8,
			'Edit category'     => 16,
			'Delete category'   => 32,
			'Add sub-category'  => 64
		)
	);	// added and working ralfbecker

	$GLOBALS['acl_manager']['admin']['mainscreen_message_access'] = array(
		'name' => 'Deny access to mainscreen message',
		'rights' => array(
			'Main screen message' => 1,
			'Login message'       => 2
		)
	);	// added and working ralfbecker

	$GLOBALS['acl_manager']['admin']['current_sessions_access'] = array(
		'name' => 'Deny access to current sessions',
		'rights' => array(
			'List current sessions'   => 1,
			'Show current action'     => 2,
			'Show session IP address' => 4,
			'Kill session'            => 8
		)
	);	// checked and working ralfbecker

	$GLOBALS['acl_manager']['admin']['access_log_access'] = array(
		'name' => 'Deny access to access log',
		'rights' => array(
			'Show access log' => 1
		)
	);	// added and working ralfbecker

	$GLOBALS['acl_manager']['admin']['error_log_access'] = array(
		'name' => 'Deny access to error log',
		'rights' => array(
			'Show error log' => 1
		)
	);	// added and working ralfbecker

	$GLOBALS['acl_manager']['admin']['asyncservice_access'] = array(
		'name' => 'Deny access to asynchronous timed services',
		'rights' => array(
			'Asynchronous timed services' => 1
		)
	);	// added and working ralfbecker

	$GLOBALS['acl_manager']['admin']['info_access'] = array(
		'name' => 'Deny access to phpinfo',
		'rights' => array(
			'Show phpinfo()' => 1
 		)
 	);	// added and working ralfbecker

