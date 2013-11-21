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

require_once "class.db_im.inc.php";
require_once "class.ldap_im.inc.php";
require_once "jabberit_sessions.inc.php";

class bogroupsldap
{
	private $db;
	private $ldap;
	
	function __construct()
	{
		$this->db		= new db_im();
		$this->ldap		= new ldap_im();
	}

	public function getGroupsSearch()
	{
		return $this->db->getGroupsSearch();
	}

	public function getServerLdapInternal()
	{
		return $_SESSION['phpgw_info']['jabberit_messenger']['server_ldap_jabberit'];		
	}
	
	public function getServersLdapExternal()
	{
		return unserialize($this->db->getHostsJabber());		
	}
	
	public function getOrganizationsLdap($pHost)
	{
		return $this->ldap->getOrganizationsLdap($pHost);	
	}
	
	public function setAddGroups($pData)
	{
		$this->db->setAddGroupsSearch($pData);		
	}
}

?>