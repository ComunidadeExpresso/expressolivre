<?php
	/**************************************************************************\
	* eGroupWare - News                                                        *
	* http://www.egroupware.org                                                *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	* --------------------------------------------                             *
	\**************************************************************************/


	class soacl
	{
		var $db;

		function soacl()
		{
			copyobj($GLOBALS['phpgw']->db,$this->db);
		}

		function get_rights($location)
		{
			$result = array();
			$sql = "SELECT acl_account, acl_rights from phpgw_acl WHERE acl_appname = 'news_admin' AND acl_location = '$location'";
			$this->db->query($sql,__LINE__,__FILE__);
			while($this->db->next_record())
			{
				$result[$this->db->f('acl_account')] = $this->db->f('acl_rights');
			}
			return $result;
		}

		function remove_location($location)
		{
			$sql = "delete from phpgw_acl where acl_appname='news_admin' and acl_location='$location'";
			$this->db->query($sql,__LINE__,__FILE__);
		}

		function get_permissions($user, $inc_groups)
		{
			$groups = $GLOBALS['phpgw']->acl->get_location_list_for_id('phpgw_group', 1, $user);
			$result = array();
			$sql  = 'SELECT acl_location, acl_rights FROM phpgw_acl ';
			$sql .= "WHERE acl_appname = 'news_admin' ";
			if($inc_groups)
			{
				$sql .= 'AND acl_account IN('. (int)$user;
				$sql .= ($groups ? ',' . implode(',', $groups) : '');
				$sql .= ')';
			}
			else
			{
				$sql .= 'AND acl_account ='. (int)$user;
			}
			$this->db->query($sql,__LINE__,__FILE__);
			while ($this->db->next_record())
			{
				$result[$this->db->f('acl_location')] |= $this->db->f('acl_rights');
			}
			return $result;
		}
	}
