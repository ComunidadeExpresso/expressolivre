<?php

$GLOBALS['phpgw_info']['flags'] = array(
	'noapi'			=> true,
	'noheader'		=> true,
	'nonavbar'		=> true,
	'currentapp'	=> 'home',
);

include( './inc/functions.inc.php' );

// Authorize the user to use setup app and load the database
// Does not return unless user is authorized
if ( !$GLOBALS['phpgw_setup']->auth( 'Config' ) || get_var( 'cancel', ['POST'] ) )
{
	Header( 'Location: index.php' );
	exit;
}

if ( get_var( 'submit', ['POST'] ) )
{
	$passwd		= get_var( 'passwd', ['POST'] );
	$username	= get_var( 'username', ['POST'] );
	$fname		= get_var( 'fname2', ['POST'] );
	$lname		= get_var( 'lname', ['POST'] );
	
	if( $passwd != get_var('passwd2', ['POST'] ) )
	{
		echo lang( 'Passwords did not match, please re-enter' ) . '.';
		exit;
	}
	
	if( !$username )
	{
		echo lang( 'You must enter a username for the admin' ) . '.';
		exit;
	}
	
	// Begin transaction for acl, etc
	$GLOBALS['phpgw_setup']->loaddb();
	$GLOBALS['phpgw_setup']->db->transaction_begin();
	
	// Create the defalt groups
	$defaultgroupid = (int)$GLOBALS['phpgw_setup']->add_account( 'Default', 'Default', 'Group', false, false );
	$admingroupid = (int)$GLOBALS['phpgw_setup']->add_account( 'Admins', 'Admin', 'Group', false, false );
	if ( !$defaultgroupid || !$admingroupid )
	{
		echo '<p><b>'.lang( 'Error in group-creation !!!' )."</b></p>\n";
		echo '<p>'.lang( 'click <a href="index.php">here</a> to return to setup.' )."</p>\n";
		$GLOBALS['phpgw_setup']->db->transaction_abort();
		exit;
	}
	
	// Group perms for the default group
	$GLOBALS['phpgw_setup']->add_acl( array( 'addressbook', 'calendar', 'infolog', 'email', 'preferences', 'manual' ), 'run', $defaultgroupid );
	
	// Give admin access to all apps, to save us some support requests
	$all_apps = array();
	$GLOBALS['phpgw_setup']->db->query( 'SELECT app_name FROM phpgw_applications WHERE app_enabled < 3' );
	while ( $GLOBALS['phpgw_setup']->db->next_record() )
	{
		$all_apps[] = $GLOBALS['phpgw_setup']->db->f( 'app_name' );
	}
	$GLOBALS['phpgw_setup']->add_acl( $all_apps, 'run', $admingroupid );
	
	// Create records for administrator account, with Admins as primary and Default as additional group
	$accountid = $GLOBALS['phpgw_setup']->add_account( $username, $fname, $lname, $passwd, 'Admins', true );
	if ( !$accountid )
	{
		echo '<p><b>'.lang( 'Error in admin-creation !!!' )."</b></p>\n";
		echo '<p>'.lang( 'click <a href="index.php">here</a> to return to setup.' )."</p>\n";
		$GLOBALS['phpgw_setup']->db->transaction_abort();
		exit;
	}
	$GLOBALS['phpgw_setup']->add_acl( 'phpgw_group', $admingroupid, $accountid );
	$GLOBALS['phpgw_setup']->add_acl( 'phpgw_group', $defaultgroupid, $accountid );
	
	$GLOBALS['phpgw_setup']->db->transaction_commit();
	
	Header('Location: index.php');
	exit;
}

$tpl_root = $GLOBALS['phpgw_setup']->html->setup_tpl_dir( 'setup' );
$setup_tpl = CreateObject( 'setup.Template', $tpl_root );
$setup_tpl->set_file( array(
	'T_head'				=> 'head.tpl',
	'T_footer'				=> 'footer.tpl',
	'T_alert_msg'			=> 'msg_alert_msg.tpl',
	'T_login_main'			=> 'login_main.tpl',
	'T_login_stage_header'	=> 'login_stage_header.tpl',
	'T_admin'				=> 'admin.tpl'
));
$setup_tpl->set_block( 'T_login_stage_header', 'B_multi_domain', 'V_multi_domain' );
$setup_tpl->set_block( 'T_login_stage_header', 'B_single_domain', 'V_single_domain' );

$GLOBALS['phpgw_setup']->html->show_header( lang( 'Admin Account Setup' ) );

$setup_tpl->set_var( 'action_url','admin.php' );
$setup_tpl->set_var( 'description', lang( '<b>This will create the Admin account</b>' ) );

$setup_tpl->set_var( 'detailadmin', lang( 'Details for Admin account' ) );
$setup_tpl->set_var( 'adminusername', lang( 'Admin username' ) );
$setup_tpl->set_var( 'adminfirstname', lang( 'Admin first name' ) );
$setup_tpl->set_var( 'adminlastname', lang( 'Admin last name' ) );
$setup_tpl->set_var( 'adminpassword', lang( 'Admin password' ) );
$setup_tpl->set_var( 'adminpassword2', lang( 'Re-enter password' ) );

$setup_tpl->set_var( 'lang_submit', lang( 'Save' ) );
$setup_tpl->set_var( 'lang_cancel', lang( 'Cancel' ) );
$setup_tpl->pparse( 'out','T_admin' );
$GLOBALS['phpgw_setup']->html->show_footer();
