<?php
	/**************************************************************************\
	* phpGroupWare - preferences                                               *
	* http://www.phpgroupware.org                                              *
	* Written by Joseph Engo <jengo@phpgroupware.org>                          *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/
	$GLOBALS['phpgw_info']['flags'] = array(
		'noheader'   => True,
		'nonavbar'   => True,
		'currentapp' => 'preferences'
	);


	include('../header.inc.php');

	$GLOBALS['phpgw']->template->set_file(array(
		'form' => 'termo_aceite.tpl'
	));
	
	$termo = $GLOBALS['phpgw_info']['server']['agree_term'];
	
	$GLOBALS['phpgw']->template->set_var('accept_term',$termo);
	$GLOBALS['phpgw']->template->set_var('Yes',lang('Yes'));
	$GLOBALS['phpgw']->template->set_var('No',lang('No'));
	$GLOBALS['phpgw']->template->set_var('url_accept',lang('Change'));
	$GLOBALS['phpgw']->template->set_var('url_dont_accept','../logout.php');
	$GLOBALS['phpgw']->template->set_var('do you agree with the terms?',lang('Do you agree with the terms?'));
	


	if (isset($_POST['pass']))
	{
		$common = CreateObject('phpgwapi.common');
		// For Write operations in LDAP, the ldap master values (from Setup) must be verified.
		// If you dont use Ldap replication, ignore it.
		if ( (!empty($GLOBALS['phpgw_info']['server']['ldap_master_host'])) &&
		     (!empty($GLOBALS['phpgw_info']['server']['ldap_master_root_dn'])) &&
		     (!empty($GLOBALS['phpgw_info']['server']['ldap_master_root_pw']))) {

			$ldap =$common->ldapConnect($GLOBALS['phpgw_info']['server']['ldap_master_host'],
			$GLOBALS['phpgw_info']['server']['ldap_master_root_dn'],
			$GLOBALS['phpgw_info']['server']['ldap_master_root_pw'],false);
		}
		else
		// For Write operations in LDAP, use ldapConnect without parameters.
			$ldap =$common->ldapConnect();
		
				
		$ldap_mod_replace['phpgwAgreeTerm'] = 1;
		$dn = $GLOBALS['phpgw_info']['user']['account_dn'];

		ldap_mod_replace ( $ldap, $dn, $ldap_mod_replace );
		
		header("location: ../home.php?cd=yes");
	}
	else
	{
		$GLOBALS['phpgw_info']['flags']['app_header'] = lang('agree term');
		$GLOBALS['phpgw']->common->phpgw_header();
		echo parse_navbar();

		$GLOBALS['phpgw']->template->pfp('out','form');
		$GLOBALS['phpgw']->common->phpgw_footer();
	}
?>
