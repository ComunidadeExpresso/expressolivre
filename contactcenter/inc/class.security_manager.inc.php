<?php
  /***************************************************************************\
  * eGroupWare - Contacts Center                                              *
  * http://www.egroupware.org                                                 *
  * Written by:                                                               *
  *  - Raphael Derosso Pereira <raphaelpereira@users.sourceforge.net>         *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/

	class security_manager 
	{

		/*
		
			@function get_available_aliens
			@abstract Return an array indicating the ID's of the Available
				Aliens Catalogs to this user
			@author Raphael Derosso Pereira
		
		*/
		function get_available_aliens (  )
		{
		}

		function get_permissions ($type = false, $id_entry = false )
		{
			if ($type)
			{
				switch ($type)
				{
					case 'entry':
					
						if ($id_entry)
						{
							/* First check if this entry is owned by the requesting user */
							//TODO: The table must be passed as argument
							$id_entry = (int)$id_entry;
							
							$sql  = 'SELECT COUNT(id_contact) AS nfields FROM phpgw_cc_contact WHERE id_contact=\''.$id_entry;
							$sql .= '\' AND id_owner=\''.$GLOBALS['phpgw_info']['user']['account_id'].'\'';

							$result = $GLOBALS['phpgw']->db->query($sql);

							if ($result)
							{
								if ($GLOBALS['phpgw']->db->next_record())
								{
									$result = $GLOBALS['phpgw']->db->f('nfields');

									if ($result)
									{
										return array(
											'read'   => true,
											'edit'   => true,
											'delete' => true,
											'acl'    => true,
										);
									}
								}
							}
						}
						
						return array(
							'read'   => false,
							'edit'   => false,
							'delete' => false,
							'acl'    => false,
						);
				}
			}
			
			$return['read'] = true;
			$return['write'] = true;
			$return['create'] = true;
			$return['remove'] = true;
			$return['cities'] = array('c');
			$return['states'] = array('c');
			
			return $return;
		}

		function get_alien_permissions ( $id_owner_alien )
		{
		}
	
		function get_global_permissions (  )
		{
		}
	
		function set_access_permitions_to_global ( $id_user, $rights )
		{
		}
	
	}
?>
