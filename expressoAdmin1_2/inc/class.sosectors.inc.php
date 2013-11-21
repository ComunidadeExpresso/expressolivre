<?php
	/**********************************************************************************\
	* Expresso Administração                 									        *
	* by Joao Alfredo Knopik Junior (joao.alfredo@gmail.com, jakjr@celepar.pr.gov.br)   *
	* ----------------------------------------------------------------------------------*
	*  This program is free software; you can redistribute it and/or modify it			*
	*  under the terms of the GNU General Public License as published by the			*
	*  Free Software Foundation; either version 2 of the License, or (at your			*
	*  option) any later version.														*
	\***********************************************************************************/

	class sosectors
	{
		var $functions;
		var $ldap_connection;
		var $db_functions;
		
		function sosectors()
		{
			$this->functions = createobject('expressoAdmin1_2.functions');
			$this->db_functions = CreateObject('expressoAdmin1_2.db_functions');
			
			if ( (!empty($GLOBALS['phpgw_info']['server']['ldap_master_host'])) &&
				 (!empty($GLOBALS['phpgw_info']['server']['ldap_master_root_dn'])) &&
				 (!empty($GLOBALS['phpgw_info']['server']['ldap_master_root_pw'])) )
			{
				$this->ldap_connection = $GLOBALS['phpgw']->common->ldapConnect($GLOBALS['phpgw_info']['server']['ldap_master_host'],
												   $GLOBALS['phpgw_info']['server']['ldap_master_root_dn'],
												   $GLOBALS['phpgw_info']['server']['ldap_master_root_pw']);
			}
			else
			{
				$this->ldap_connection = $GLOBALS['phpgw']->common->ldapConnect();
			}
		}
		
		function exist_sector_name($sector_name, $context)
		{
			$search = ldap_list($this->ldap_connection, $context, "ou=" . $sector_name);
			$result = ldap_get_entries($this->ldap_connection, $search);
			
			if ($result['count'] == 0)
				return false;
			else
				return true;
		}
		
		function write_ldap($dn, $info)
		{
			$info['ou'] = utf8_encode($info['ou']);

			if (ldap_add($this->ldap_connection, utf8_encode($dn), $info))
			{
				$this->db_functions->write_log("write on ldap", "$dn");
				ldap_close($this->ldap_connection);
				return true;
			}
			else
			{
				echo lang('Error written in LDAP, function write_ldap');
				ldap_close($this->ldap_connection);
				return false;
			}
		}
		
		function get_sector_users($context)
		{
			$justthese = array("cn", "uidNumber", "uid");
			$filter="(&(phpgwAccountType=u)(uid=*))";
			$search=ldap_search($this->ldap_connection, $context, $filter, $justthese);
			$result = ldap_get_entries($this->ldap_connection, $search);
			return $result;
		}
		
		function get_sector_groups($context)
		{
			$justthese = array("cn", "gidnumber");
			$filter="(&(phpgwAccountType=g)(cn=*))";
			$search=ldap_search($this->ldap_connection, $context, $filter, $justthese);
			$result = ldap_get_entries($this->ldap_connection, $search);
			return $result;
		}

		function get_sector_subsectors($context)
		{
			$justthese = array("ou");
			$filter="(objectClass=organizationalUnit)";
			$search=ldap_search($this->ldap_connection, $context, $filter, $justthese);
			$result = ldap_get_entries($this->ldap_connection, $search);
			return $result;
		}
		
		function delete_sector_ldap_recursively($connection, $dn)
		{
			//searching for sub entries
			$search=ldap_list($connection,$dn,"ObjectClass=*",array(""));
			$info = ldap_get_entries($connection, $search);
			
			for($i=0;$i<$info['count'];++$i)
			{
				//deleting recursively sub entries
				$result=$this->delete_sector_ldap_recursively($connection,$info[$i]['dn']);
					if(!$result)
					{
						//return result code, if delete fails
						return($result);
					}
			}
			return(ldap_delete($connection,$dn));
		}
		
		function get_info($context)
		{
			$filter="(objectClass=organizationalUnit)";
			$search=ldap_search($this->ldap_connection, $context, $filter);
			$result = ldap_get_entries($this->ldap_connection, $search);
			return $result;
		}
		
		function add_attribute($dn, $info)
		{
			if (ldap_mod_add($this->ldap_connection, $dn, $info))
			{
				ldap_close($this->ldap_connection);
				return true;
			}
			else
			{
				echo lang('Error written in LDAP, function add_attribute'). ldap_error($this->ldap_connection);
				ldap_close($this->ldap_connection);
				return false;
			}
		}

		function replace_attribute($dn, $info) 
                { 
                        $connection = $GLOBALS['phpgw']->common->ldapConnect(); 
                         
                        if (ldap_mod_replace($connection, $dn, $info)) 
                        { 
                                ldap_close($connection); 
                                return true; 
                        } 
                        else 
                        { 
                                echo 'Erro na escrita no LDAP, funcao replace_attribute: ' . ldap_error($connection); 
                                ldap_close($connection); 
                                return false; 
                        } 
                }             
   
		function remove_attribute($dn, $info)
		{
			if (ldap_mod_del($this->ldap_connection, $dn, $info))
			{
				ldap_close($this->ldap_connection);
				return true;
			}
			else
			{
				echo lang('Error written in LDAP, function remove_attribute'). ldap_error($this->ldap_connection);
				ldap_close($this->ldap_connection);
				return false;
			}
		}
	}
?>
