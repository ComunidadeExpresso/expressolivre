<?php

 /**************************************************************************\
  * Expresso Livre - Voip - administration                                   *
  *															                 *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

define('PHPGW_INCLUDE_ROOT', '../');
define('PHPGW_API_INC','../phpgwapi/inc');
require_once( PHPGW_API_INC . '/class.common.inc.php' );

class sovoip
{
	private $db;
	private $common;
	private $ldap;
	private $ldap_host;
	private $ldap_root_dn;
	private $ldap_root_pw;
	private $ldap_context;

	final function __construct()
	{
		$this->ldap_host	 = $_SESSION['admin']['server']['ldap_host'];
		$this->ldap_root_dn	 = $_SESSION['admin']['server']['ldap_root_dn'];
		$this->ldap_root_pw  = $_SESSION['admin']['server']['ldap_root_pw'];
		$this->ldap_context  = $_SESSION['admin']['server']['ldap_context'];
		$this->common = new common();
	}

	final function __destruct()
	{
		if( $this->ldap )
			ldap_close($this->ldap);
	}

	public final function getOuLdap()
	{
		$this->ldap = $this->common->ldapConnect();
		
		if ( $this->ldap )	
		{
			$filter = "objectClass=organizationalUnit";
			$justthese = array("ou");
			$search = ldap_list($this->ldap, $this->ldap_context, $filter, $justthese);
			$entry = ldap_get_entries($this->ldap, $search);
		}

		//$result_ou[] = "";

		if( $entry['count'] > 0 )
		{
			foreach($entry as $tmp)
				if( $tmp['ou'][0] != "" )
					$result_ou[] = $tmp['ou'][0];
		}else{
		    $result_ou[] = $this->ldap_context;
		}
		
		natcasesort($result_ou);

		return (($result_ou) ? $result_ou : '');
	}	

	public final function getGroupsLdap($pOrg)
	{
		if($pOrg['ou'] == $this->ldap_context)
		    $organization = $this->ldap_context;
		else
		    $organization = 'ou=' . $pOrg['ou'] .",". $this->ldap_context;
 		
		$this->ldap = $this->common->ldapConnect($this->ldap_host,$this->ldap_root_dn,$this->ldap_root_pw);
		
		if( $this->ldap )	
		{
			$filter = "(&(phpgwAccountType=g)(objectClass=posixGroup))";
			$justthese = array("cn","gidNumber");
			$search = ldap_search($this->ldap, $organization, $filter, $justthese);
			$entry = ldap_get_entries( $this->ldap, $search );

			if( $entry )
			{					
				$idx = 0;
				foreach($entry as $tmp) {
					if( $tmp['gidnumber'][0] != "" ){
						$result_groups[$idx]['gid'] = $tmp['gidnumber'][0];
						$result_groups[$idx++]['cn'] = $tmp['cn'][0];						
					}
				}
			}
			
			natcasesort($result_groups);
		}
		
		return (($result_groups) ? $result_groups : '');
	}

	public final function setConfDB($pConf)
	{
		$this->db = $GLOBALS['phpgw']->db;
		if( $this->db )
		{
			foreach($pConf as $key => $tmp )
			{
				$query = "SELECT * FROM phpgw_config WHERE config_app = 'phpgwapi' and config_name ='".trim($key)."'";
				
				$this->db->query($query);
					
				if(!$this->db->next_record())
				{
					$query = "INSERT INTO phpgw_config(config_app,config_name,config_value) VALUES('phpgwapi','".trim($key)."','".$tmp."')";
					$this->db->query($query);
				}
				else
				{
					$query = "UPDATE phpgw_config SET config_value = '".$tmp."' WHERE config_app = 'phpgwapi' AND config_name = '".trim($key)."'";
					$this->db->query($query);
				}
			}
		}	
	}

	public final function getConfDB()
	{
		$this->db = $GLOBALS['phpgw']->db;

		if( $this->db )
		{
			$query = "SELECT * FROM phpgw_config WHERE config_app = 'phpgwapi' and config_name = 'voip_groups'";
			
			if(!$this->db->query($query))
				return null;
			
			while($this->db->next_record())
				$result[] = $this->db->row();		
		}

		return (($result) ? $result : '');		
	}
}
?>
