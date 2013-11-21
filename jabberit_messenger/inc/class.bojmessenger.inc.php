<?php

require_once "class.db_im.inc.php";
require_once "class.ldap_im.inc.php";
require_once "jabberit_sessions.inc.php";

class bojmessenger
{
	private $db;
	private $ldap;

	function __construct()
	{
		$this->db	= new db_im();
		$this->ldap = new ldap_im();
	}
	
	public final function getGroupsJmessenger()
	{
		return $this->db->getGroupsJmessenger();
	}
	
	public final function getOrganizationsLdap($pHost)
	{
		return $this->ldap->getOrganizationsLdap($pHost);				
	}
	
	public final function setAddGroupsJmessenger($pData)
	{
		$this->db->setAddGroupsJmessenger($pData);		
	}
	
}

?>
