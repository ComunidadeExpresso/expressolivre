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

require_once "class.sovoip.inc.php";

class bovoip
{
	private $so;

	final function __construct()
	{
		$this->so = new sovoip(); 	
	}

	public final function getConf()
	{
		$this->so->getConf();
	}

	public final function getOuLdap()
	{
		return $this->so->getOuLdap();
	}

	public final function getGroupsLdap($pOrg)
	{
		$groups = $this->so->getGroupsLdap($pOrg);
		
		$group = "<groups>";
	
		if(is_array($groups))
		{
			foreach($groups as $tmp)	
				$group .= "<group>" . $tmp['cn'].";".$tmp['gid'] . "</group>";				
		}

		$group .= "</groups>";
		
		return $group;
	}	

	public final function getConfDB()
	{
		return $this->so->getConfDB();
	}

	public final function setConfDB($pConf)
	{
		$this->so->setConfDB($pConf);	
	}
}
?>
