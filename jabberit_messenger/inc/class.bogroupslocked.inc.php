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
require_once "jabberit_sessions.inc.php";

class bogroupslocked
{
	private $db;
	
	function __construct()
	{
		$this->db = new db_im();
	}
	
	public final function setGroupsLocked($pGroups)
	{
		return $this->db->setGroupsLocked($pGroups);
	}	
}  

?>
