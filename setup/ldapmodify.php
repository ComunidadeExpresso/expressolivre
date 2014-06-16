<?php
  /**************************************************************************\
  * eGroupWare - Setup                                                       *
  * http://www.egroupware.org                                                *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/


	$phpgw_info = array();
	$phpgw_info["flags"] = array(
		'noheader'   => True,
		'nonavbar'   => True,
		'currentapp' => 'home',
		'noapi'      => True
	);
	include('./inc/functions.inc.php');

	/* Authorize the user to use setup app and load the database */
	if(!$GLOBALS['phpgw_setup']->auth('Config'))
	{
		Header('Location: index.php');
		exit;
	}
	/* Does not return unless user is authorized */

	class phpgw
	{
		var $common;
		var $accounts;
		var $applications;
		var $db;
	}
	$phpgw = new phpgw;
	$phpgw->common = CreateObject('phpgwapi.common');

	$common = $phpgw->common;
	$GLOBALS['phpgw_setup']->loaddb();
	copyobj($GLOBALS['phpgw_setup']->db,$phpgw->db);

	$tpl_root = $GLOBALS['phpgw_setup']->html->setup_tpl_dir('setup');
	$setup_tpl = CreateObject('setup.Template',$tpl_root);
	$setup_tpl->set_file(array(
		'ldap'   => 'ldap.tpl',
		'T_head' => 'head.tpl',
		'T_footer' => 'footer.tpl',
		'T_alert_msg' => 'msg_alert_msg.tpl'
	));

	$GLOBALS[ 'phpgw_setup' ] -> db -> query(
		"SELECT config_name, config_value FROM phpgw_config"
		." WHERE config_app = 'phpgwapi' and config_name LIKE 'ldap%' OR config_name='account_repository'",
		__LINE__, __FILE__
	);

	while ( $GLOBALS[ 'phpgw_setup' ] -> db -> next_record( ) )
		$config[ $GLOBALS[ 'phpgw_setup' ] -> db -> f( 'config_name' ) ] = $GLOBALS[ 'phpgw_setup' ] -> db -> f( 'config_value' );

	$phpgw_info['server']['ldap_host']          = $config['ldap_host'];
	$phpgw_info['server']['ldap_context']       = $config['ldap_context'];
	$phpgw_info['server']['ldap_group_context'] = $config['ldap_group_context'];
	$phpgw_info['server']['ldap_root_dn']       = $config['ldap_root_dn'];
	$phpgw_info['server']['ldap_root_pw']       = $config['ldap_root_pw'];
	$phpgw_info['server']['account_repository'] = $config['account_repository'];
	$phpgw_info['server']['ldap_version3']      = $config['ldap_version3'];

	$phpgw->accounts = CreateObject('phpgwapi.accounts');
	$acct            = $phpgw->accounts;

	// connect to ldap server
	if ( ! $ldap = $common -> ldapConnect( ) )
	{
		Header( 'Location: config.php?error=badldapconnection' );
		exit;
	}

	// Take the users from LDAP.
	$sr = ldap_search( $ldap, $config[ 'ldap_context' ], '(objectClass=posixAccount)', array( 'cn', 'givenname', 'uid', 'uidnumber', 'objectClass' ) );
	$info = ldap_get_entries( $ldap, $sr );
	$tmp = '';

	$account_info = array( );
	for ( $i = 0; $i < $info[ 'count' ]; ++$i )
		if ( ! array_key_exists( $info[ $i ][ 'uid' ][ 0 ], $phpgw_info[ 'server' ][ 'global_denied_users' ] ) )
			$account_info[ $info[ $i ][ 'dn' ] ] = $info[ $i ];

	$group_info = array( );
	if ( array_key_exists( 'ldap_group_context', $phpgw_info[ 'server' ] ) && count( $phpgw_info['server']['global_denied_groups'] ) )
	{
		$sr = ldap_search( $ldap, $config[ 'ldap_group_context' ], '(objectClass=posixGroup)',
			array( 'gidnumber', 'cn', 'memberuid', 'objectclass', 'phpgwaccountstatus', 'phpgwaccounttype', 'phpgwaccountexpires' )
		);
		$info = ldap_get_entries( $ldap, $sr );
		$tmp = '';

		for ( $i = 0; $i < $info[ 'count' ]; ++$i )
			if ( ! array_key_exists( $info[ $i ][ 'cn' ][ 0 ], $phpgw_info[ 'server' ][ 'global_denied_groups' ] ) )
				$group_info[ $info[ $i ][ 'dn' ] ] = $info[ $i ];
	}

	$GLOBALS[ 'phpgw_setup' ] -> db -> query(
		"SELECT app_name FROM phpgw_applications WHERE app_enabled!='0' AND app_enabled!='3' ORDER BY app_name",
		__LINE__, __FILE__
	);

	while( $GLOBALS[ 'phpgw_setup' ] -> db -> next_record( ) )
		$apps[ $GLOBALS[ 'phpgw_setup' ] -> db -> f( 'app_name' ) ] = lang( $GLOBALS[ 'phpgw_setup' ] -> db -> f( 'app_name' ) );

	if ( $cancel )
	{
		Header( 'Location: ldap.php' );
		exit;
	}

	$GLOBALS[ 'phpgw_setup' ] -> html -> show_header(
		lang('LDAP Modify'),
		false,
		'config',
		"{$GLOBALS[ 'phpgw_setup' ] -> ConfigDomain} ( {$phpgw_domain[ $GLOBALS[ 'phpgw_setup' ] -> ConfigDomain ][ 'db_type' ]} )"
	);

	if ( array_key_exists( 'submit', $_POST ) )
	{
		$acl = CreateObject('phpgwapi.acl');
		copyobj( $GLOBALS[ 'phpgw_setup' ] -> db, $acl -> db );

		if ( array_key_exists( 'ldapgroups', $_POST ) && is_array( $_POST[ 'ldapgroups' ] ) )
		{
			$groups = CreateObject( 'phpgwapi.accounts' );
			copyobj( $GLOBALS[ 'phpgw_setup' ] -> db, $groups -> db );

			foreach ( $_POST[ 'ldapgroups' ] as $groupid )
			{
				if ( ! array_key_exists( $groupid, $group_info ) )
				{
					echo "Has occurred some problem in the group : {$groupid}<br />\n";
					continue;
				}

				$entry = array( );

				$thisacctid    = $group_info[ $groupid ][ 'gidnumber' ][ 0 ];
				$thisacctlid   = $group_info[ $groupid ][ 'cn' ][ 0 ];
				$thisfirstname = $group_info[ $groupid ][ 'cn' ][ 0 ];
				$thismembers   = $group_info[ $groupid ][ 'memberuid' ];
				$thisdn        = $group_info[ $groupid ][ 'dn' ];

				echo "Updating GROUPID : {$thisacctlid} ({$groupid})<br />\n";

				// Do some checks before we try to import the data.
				if ( ! empty( $thisacctid ) && ! empty( $thisacctlid ) )
				{
					$groups->account_id = ( int ) $thisacctid;

					reset( $group_info[ $groupid ][ 'objectclass' ] );

					$add = array( );

					if ( ! in_array( 'phpgwAccount', $group_info[ $groupid ][ 'objectclass' ] ) )
						$add[ 'objectclass'] = array( 'phpgwAccount' );

					if ( ! array_key_exists( 'phpgwaccountstatus', $group_info[ $groupid ] ) )
						$add[ 'phpgwaccountstatus'] = array( 'A' );

					if ( ! array_key_exists( 'phpgwaccounttype', $group_info[ $groupid ] ) )
						$add[ 'phpgwaccounttype' ] = array( 'g' );

					if ( ! array_key_exists( 'phpgwaccountexpires', $group_info[ $groupid ] ) )
						$add[ 'phpgwaccountexpires' ] = array( -1 );

					if ( count( $add ) )
						ldap_mod_add( $ldap, $thisdn, $add );

					// Now make the members a member of this group in phpgw.
					if ( is_array( $thismembers ) )
					{
						if ( array_key_exists( 'count', $thismembers ) )
							unset( $thismembers[ 'count' ] );

						foreach ( $thismembers as $key => $members )
						{
							echo "members: {$members}<br />\n";

							$tmpid = NULL;
							foreach ( $account_info as $info )
								if ( $members == $info[ 'uid' ][ 0 ] )
								{
									$tmpid = $info[ 'uidnumber' ][ 0 ];
									break;
								}

							// Insert acls for this group based on memberuid field.
							// Since the group has app rights, we don't need to give users
							// these rights. Instead, we maintain group membership here.
							if ( $tmpid )
							{
								echo "inserindo user_id: {$tmpid} em {$thisacctid}<br />\n";

								$acl -> account_id = ( int ) $tmpid;
								$acl -> read_repository( );

								$acl -> delete( 'phpgw_group', $thisacctid, 1 );
								$acl -> add( 'phpgw_group', $thisacctid, 1 );

								// Now add the acl to let them change their password
								$acl -> delete( 'preferences', 'changepassword', 1 );
								$acl -> add( 'preferences', 'changepassword', 1 );

								$acl -> save_repository( );
							}
						}
					}

					// Now give this group some rights
					$phpgw_info[ 'user' ][ 'account_id' ] = $thisacctid;

					$acl -> account_id = ( int ) $thisacctid;
					$acl -> read_repository( );

					foreach ( $_POST[ 's_apps' ] as $app )
					{
						$acl -> delete( $app, 'run', 1 );
						$acl -> add( $app, 'run', 1 );
					}

					$acl -> save_repository();
					$defaultgroupid = $thisacctid;
				}
				echo "----------------------------------------------<br />\n";
			}
		}

		if ( ( array_key_exists( 'users', $_POST ) && is_array( $_POST[ 'users' ] ) ) || ( array_key_exists( 'admins', $_POST ) && is_array( $_POST[ 'admins' ] ) ) )
		{
			$accounts = CreateObject( 'phpgwapi.accounts' );
			copyobj( $GLOBALS[ 'phpgw_setup' ] -> db, $accounts -> db );

			$users_process = 0;
			$new_uidnumber = 12011;

			foreach ( array( 'admins', 'users' ) as $type )
				if ( array_key_exists( $type, $_POST ) )
				{
					if ( $type == 'admins' )
					{
						// give admin access to all apps, to save us some support requests
						$all_apps = array();
						$GLOBALS[ 'phpgw_setup' ] -> db -> query( 'SELECT app_name FROM phpgw_applications ORDER BY app_name' );
						while ( $GLOBALS[ 'phpgw_setup' ] -> db -> next_record( ) )
							$all_apps[ ] = $GLOBALS[ 'phpgw_setup' ]  -> db -> f( 'app_name' );
					}

					foreach ( $_POST[ $type ] as $user_id )
					{
						$id_exist = 0;
						$thisacctid  = $account_info[ $user_id ][ 'uidnumber' ][ 0 ];
						$thisacctlid = $account_info[ $user_id ][ 'uid' ][ 0 ];
						$thisdn      = $account_info[ $user_id ][ 'dn'];

						echo "{$thisdn}<br />\nUpdating ({$type}) USERID : {$thisacctlid}<br />\n";

						// Do some checks before we try to import the data.
						if ( !empty($thisacctid) && !empty($thisacctlid))
						{
                            ++$users_process;

							$add = array( );
							$objectClass = array( );

							if ( ! in_array( 'qmailUser', $account_info[ $user_id ][ 'objectclass' ] ) )
								$objectclass[ ] = 'qmailUser';

							if ( ! in_array( 'phpgwAccount', $account_info[ $user_id ][ 'objectclass' ] ) )
							{
								$objectclass[ ] = 'phpgwAccount';
								$add[ 'phpgwAccountExpires' ] = array( '-1' );
								$add[ 'phpgwAccountStatus' ] = array( 'A' );
								$add[ 'phpgwAccountType' ] = array( 'u' );
								$add[ 'phpgwLastPasswdChange' ] = array( '1290632486' );
							}

							if ( count( $objectclass ) )
								$add[ 'objectclass' ] = $objectclass;

							if ( count( $add ) )
								ldap_mod_add( $ldap, $thisdn, $add );

							$accounts -> account_id = ( int ) $thisacctid;

							// Insert default acls for this user.
							$acl -> account_id = ( int ) $thisacctid;
							$acl -> read_repository( );

							// Now add the acl to let them change their password
							$acl -> delete( 'preferences', 'changepassword', 1 );
							$acl -> add( 'preferences', 'changepassword', 1 );

							// Add user to a default group, previous created
							//$acl -> add( 'phpgw_group', '12007', 1 );

							echo "Adding in ACL BD: {$thisacctid}<br /><br />\n";

							// Save these new acls.
							$acl -> save_repository( );

                            ++$new_uidnumber;
						}

						if ( $type == 'admins' )
						{
							$GLOBALS[ 'phpgw_setup' ] -> add_acl(array( 'admin', 'expressoAdmin' ), 'run', ( int ) $thisacctid );
							$GLOBALS[ 'phpgw_setup' ] -> db -> query( "INSERT INTO phpgw_expressoadmin VALUES ( '{$thisacctlid}', '{$config[ 'ldap_context' ]}', 2199023253495 )" );
							foreach ( $all_apps as $app )
								$GLOBALS[ 'phpgw_setup' ] -> db -> query( "INSERT INTO phpgw_expressoadmin_apps VALUES ( '{$thisacctlid}', '{$config[ 'ldap_context' ]}', '{$app}' )" );
						}
					}
				}
		}

		printf( "<br /><center>%s %s<br /></center>",
			lang( 'Modifications have been completed!' ),
			lang( 'Click <a href="index.php">here</a> to return to setup.' )
		);

		$GLOBALS['phpgw_setup' ] -> html -> show_footer( );
		exit;
	}

	if ( array_key_exists( 'error', $_GET ) )
		$GLOBALS[ 'phpgw_setup' ] -> html -> show_alert_msg( 'Error', $_GET[ 'error' ] );

	$setup_tpl->set_block('ldap','header','header');
	$setup_tpl->set_block('ldap','user_list','user_list');
	$setup_tpl->set_block('ldap','admin_list','admin_list');
	$setup_tpl->set_block('ldap','group_list','group_list');
	$setup_tpl->set_block('ldap','app_list','app_list');
	$setup_tpl->set_block('ldap','submit','submit');
	$setup_tpl->set_block('ldap','footer','footer');

	$user_list = array( );
	while ( list( $key, $account ) = each( $account_info ) )
		$user_list[ ] = '<option value="' . $account[ 'dn' ] . '">' . utf8_decode( $account[ 'cn' ][ 0 ] ) . " ({$account[ 'uid' ][ 0 ]})</option>";

	$user_list = $admin_list = implode( '', $user_list );

	$group_list = '';
	while( list( $key, $group ) = each( $group_info ) )
		$group_list .= '<option value="' . $group[ 'dn' ] . '">' . utf8_decode( $group[ 'cn' ][ 0 ] )  . '</option>';

	$app_list = '';
	while(list($appname,$apptitle) = each($apps))
	{
		if($appname == 'admin' ||
			$appname == 'skel' ||
			$appname == 'backup' ||
			$appname == 'netsaint' ||
			$appname == 'developer_tools' ||
			$appname == 'phpsysinfo' ||
			$appname == 'eldaptir' ||
			$appname == 'qmailldap')
		{
			$app_list .= '<option value="' . $appname . '">' . $apptitle . '</option>';
		}
		else
		{
			$app_list .= '<option value="' . $appname . '" selected>' . $apptitle . '</option>';
		}
	}

	$setup_tpl->set_var('action_url','ldapmodify.php');
	$setup_tpl->set_var('users',$user_list);
	$setup_tpl->set_var('admins',$admin_list);
	$setup_tpl->set_var('ldapgroups',$group_list);
	$setup_tpl->set_var('s_apps',$app_list);

	$setup_tpl->set_var('ldap_import',lang('LDAP Modify'));
	$setup_tpl->set_var('description',lang("This section will help you setup your LDAP accounts for use with eGroupWare").'.');
	$setup_tpl->set_var('select_users',lang('Select which user(s) will be modified'));
	$setup_tpl->set_var('select_admins',lang('Select which user(s) will also have admin privileges'));
	$setup_tpl->set_var('select_groups',lang('Select which group(s) will be modified (group membership will be maintained)'));
	$setup_tpl->set_var('select_apps',lang('Select the default applications to which your users will have access').'.');
	$setup_tpl->set_var('form_submit',lang('Modify'));
	$setup_tpl->set_var('cancel',lang('Cancel'));

	$setup_tpl->pfp('out','header');
	$setup_tpl->pfp('out','user_list');
	$setup_tpl->pfp('out','admin_list');
	$setup_tpl->pfp('out','group_list');
	$setup_tpl->pfp('out','app_list');
	$setup_tpl->pfp('out','submit');
	$setup_tpl->pfp('out','footer');

	$GLOBALS['phpgw_setup']->html->show_footer();
?>
