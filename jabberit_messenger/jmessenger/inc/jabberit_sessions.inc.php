<?php

  /***************************************************************************\
  *  Expresso - Expresso Messenger                                            *
  *  	- Alexandre Correia / Rodrigo Souza							          *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/

		$attributeLdap = PHPGW_SERVER_ROOT . '/jmessenger/inc/attributeLdap.php';

		//User
		$_SESSION['phpgw_info']['jabberit_messenger']['user_jabber'] = $GLOBALS['phpgw_info']['user']['account_lid'];
		if ( file_exists($attributeLdap) )	
		{
			require_once($attributeLdap);
			if( trim($attributeTypeName) == 'description' )
			{
				$description = explode("@", $GLOBALS['phpgw_info']['user']['email']);
        		$_SESSION['phpgw_info']['jabberit_messenger']['user_jabber'] = $description[0];
			}
		}

		$_SESSION['phpgw_info']['jabberit_messenger']['user_id']		= $GLOBALS['phpgw_info']['user']['account_id'];		
        $_SESSION['phpgw_info']['jabberit_messenger']['passwd']			= $GLOBALS['phpgw_info']['user']['passwd'];
		$_SESSION['phpgw_info']['jabberit_messenger']['mail']			= $GLOBALS['phpgw_info']['user']['email'];
		$_SESSION['phpgw_info']['jabberit_messenger']['fullname']		= $GLOBALS['phpgw_info']['user']['fullname'];
		$_SESSION['phpgw_info']['jabberit_messenger']['account_dn']		= $GLOBALS['phpgw_info']['user']['account_dn'];

		// User Lang 
		$_SESSION['phpgw_info']['jabberit_messenger']['applet_lang']	= $GLOBALS['phpgw_info']['user']['preferences']['common']['lang'];

		//Members Group
		$_SESSION['phpgw_info']['jabberit_messenger']['membership']		= $GLOBALS['phpgw']->accounts->membership();

		//Groups Locked
		$_SESSION['phpgw_info']['jabberit_messenger']['groups_locked']	= $GLOBALS['phpgw_info']['server']['groups_locked_jabberit'];

		//Groups Search Ldap
		$_SESSION['phpgw_info']['jabberit_messenger']['groups_search']	= $GLOBALS['phpgw_info']['server']['groups_search_jabberit'];

		//Server http or https 
		$_SESSION['phpgw_info']['jabberit_messenger']['use_https']		= $GLOBALS['phpgw_info']['server']['use_https'];

		//Organization Ldap
		$_SESSION['phpgw_info']['jabberit_messenger']['account_dn']		= $GLOBALS['phpgw_info']['user']['account_dn'];

		//Ldap
		$_SESSION['phpgw_info']['jabberit_messenger']['server_ldap_jabberit']   = $GLOBALS['phpgw_info']['server']['server_ldap_jabberit'];
        $_SESSION['phpgw_info']['jabberit_messenger']['context_ldap_jabberit']  = $GLOBALS['phpgw_info']['server']['context_ldap_jabberit'];
        $_SESSION['phpgw_info']['jabberit_messenger']['user_ldap_jabberit']     = $GLOBALS['phpgw_info']['server']['user_ldap_jabberit'];
        $_SESSION['phpgw_info']['jabberit_messenger']['password_ldap_jabberit'] = $GLOBALS['phpgw_info']['server']['password_ldap_jabberit'];
        
        //DB
        $_SESSION['phpgw_info']['jabberit_messenger']['server']['db_name']	 = $GLOBALS['phpgw_info']['server']['db_name'];
        $_SESSION['phpgw_info']['jabberit_messenger']['server']['db_host']	 = $GLOBALS['phpgw_info']['server']['db_host'];
        $_SESSION['phpgw_info']['jabberit_messenger']['server']['db_port']	 = $GLOBALS['phpgw_info']['server']['db_port'];
        $_SESSION['phpgw_info']['jabberit_messenger']['server']['db_user']	 = $GLOBALS['phpgw_info']['server']['db_user'];
        $_SESSION['phpgw_info']['jabberit_messenger']['server']['db_pass']	 = $GLOBALS['phpgw_info']['server']['db_pass'];
        $_SESSION['phpgw_info']['jabberit_messenger']['server']['db_type'] 	 = $GLOBALS['phpgw_info']['server']['db_type'];

		//Jabberd
        $_SESSION['phpgw_info']['jabberit_messenger']['name_jabberit']					= $GLOBALS['phpgw_info']['server']['name_jabberit'];
        $_SESSION['phpgw_info']['jabberit_messenger']['ip_server_jabberit']				= $GLOBALS['phpgw_info']['server']['ip_server_jabberit'];
        $_SESSION['phpgw_info']['jabberit_messenger']['port_1_jabberit']				= $GLOBALS['phpgw_info']['server']['port_1_jabberit'];
        $_SESSION['phpgw_info']['jabberit_messenger']['port_2_jabberit']				= $GLOBALS['phpgw_info']['server']['port_2_jabberit'];        
        $_SESSION['phpgw_info']['jabberit_messenger']['resource_jabberit']				= $GLOBALS['phpgw_info']['server']['resource_jabberit'];
        $_SESSION['phpgw_info']['jabberit_messenger']['group_chat_jabberit']			= $GLOBALS['phpgw_info']['server']['group_chat_server_jabberit'];
		$_SESSION['phpgw_info']['jabberit_messenger']['name_company']					= $GLOBALS['phpgw_info']['server']['name_company_applet_jabberit'];
		$_SESSION['phpgw_info']['jabberit_messenger']['use_attribute_jabberit']			= $GLOBALS['phpgw_info']['server']['use_attribute_jabberit'];
		$_SESSION['phpgw_info']['jabberit_messenger']['map_org_realm_jabberit']			= $GLOBALS['phpgw_info']['server']['map_org_realm_jabberit'];
		
?>