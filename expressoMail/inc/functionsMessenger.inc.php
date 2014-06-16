<?php

  /***************************************************************************\
  *  Expresso - Expresso Messenger                                            *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/

if( !isset($GLOBALS['phpgw_info']) )
{
	$GLOBALS['phpgw_info']['flags'] = array(
		'currentapp' => 'expressoMail',
		'nonavbar'   => true,
		'noheader'   => true
	);
}

require_once '../../header.inc.php';
require_once '../../phpgwapi/inc/class.common.inc.php';

if( session_id() )
{

	function groupsLdap( $param )
	{
		$ldap_host		= $GLOBALS['phpgw_info']['server']['ldap_host'];
		$ldap_root_dn	= $GLOBALS['phpgw_info']['server']['ldap_root_dn'];
		$ldap_root_pw	= $GLOBALS['phpgw_info']['server']['ldap_root_pw'];
		$ldap_context	= $GLOBALS['phpgw_info']['server']['ldap_context'];
		$result_groups	= '';

		//Organizations Ldap
		$organization = 'ou=' . $param .",". $ldap_context;
		
		if( $param == $ldap_context )
		{
		    $organization = $ldap_context;
		}
 		
 		//Commons Functions
 		$common = new Common();	

 		// Ldap Connection
		$ldap = $common->ldapConnect( $ldap_host, $ldap_root_dn, $ldap_root_pw );
		
		if( $ldap )	
		{
			$filter		= "(&(phpgwAccountType=g)(cn=grupo*-im))";
			$justthese	= array("cn","gidNumber");
			$search		= ldap_search( $ldap, $organization, $filter, $justthese );
			$entry		= ldap_get_entries( $ldap, $search );

			if( $entry )
			{					
				foreach($entry as $tmp)
				{
					if( $tmp['gidnumber'][0] != "" )
						$result_groups[] = $tmp['cn'][0].";".$tmp['gidnumber'][0];
				}
			}
			
			natsort( $result_groups );
		}
		
		return $result_groups;
	}

	if( isset( $_POST['organization'] ) )
	{
		echo json_encode( groupsLdap($_POST['organization']) );
	}
}

?>