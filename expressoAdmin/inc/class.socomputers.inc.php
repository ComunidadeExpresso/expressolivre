<?php
	/************************************************************************************\
	* Expresso Administração                 										     *
	* by Joao Alfredo Knopik Junior (joao.alfredo@gmail.com, jakjr@celepar.pr.gov.br)  	 *
	* -----------------------------------------------------------------------------------*
	*  This program is free software; you can redistribute it and/or modify it			 *
	*  under the terms of the GNU General Public License as published by the			 *
	*  Free Software Foundation; either version 2 of the License, or (at your			 *
	*  option) any later version.														 *
	\************************************************************************************/

	class socomputers
	{
		var $functions;
		var $ldap_connection;
		var $db_functions;
		
		function socomputers()
		{
			$this->functions = CreateObject('expressoAdmin.functions');
			$this->db_functions = CreateObject('expressoAdmin.db_functions');
			
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

		function write_ldap($dn, $info)
		{
			if (ldap_add($this->ldap_connection, $dn, $info))
			{
				ldap_close($this->ldap_connection);
				return true;
			}
			else
			{
				ldap_close($this->ldap_connection);
				return false;
			}
		}
		
		function exist_computer_uid($computer_cn)
		{
			$search = ldap_search($this->ldap_connection, $GLOBALS['phpgw_info']['server']['ldap_context'], "uid=" . $computer_cn . '$');
			$result = ldap_get_entries($this->ldap_connection, $search);
			ldap_close($this->ldap_connection);
			if ($result['count'] == 0)
				return false;
			else
				return true;
		}
		
		function get_computer_data($uidnumber)
		{
			$manager_acl = $this->functions->read_acl($_SESSION['phpgw_info']['expresso']['user']['account_lid']);
			$manager_contexts = $manager_acl['contexts'];
			
			foreach ($manager_contexts as $index=>$context)
			{
				$search = ldap_search($this->ldap_connection, $context, "uidNumber=$uidnumber");
				$result = ldap_get_entries($this->ldap_connection, $search);
			
				if ($result['count'])
				{
					// Recupera o DN
					$computer_data['dn'] = $result[0]['dn'];
			
					//Recupera o Nome do Computador (CN)
					$computer_data['computer_cn'] = $result[0]['cn'][0];

					//Recupera a flag SAMBA
					$computer_data['sambaAcctFlags'] = $result[0]['sambaacctflags'][0];
			
					// Recupera a descrição
					$computer_data['computer_description'] = utf8_decode($result[0]['description'][0]);
			
					// Recupera o contexto do email_list
					$tmp = explode(",", $computer_data['dn']);
                    $tmp_count = count($tmp);
					for ($i = 1; $i < $tmp_count; ++$i)
						$computer_data['context'] .= $tmp[$i] . ',';
					$computer_data['context'] = substr($computer_data['context'],0,(strlen($computer_data['context']) - 1));
			
					$a_tmp = explode("-", $result[0]['sambasid'][0]);
					array_pop($a_tmp);
					$computer_data['sambasid'] = implode("-", $a_tmp);
			
					return $computer_data;
				}
			}
		}
		
		function delete_computer_ldap($dn)
		{
			$result = @ldap_delete($this->ldap_connection, $dn);
			@ldap_close($this->ldap_connection);
			$this->db_functions->write_log('deleted computer',$dn);
			return $result;				
		}
		
		function rename_ldap($old_dn, $new_rdn, $new_context)
		{
			$result = ldap_rename($this->ldap_connection, $old_dn, $new_rdn, $new_context, true);
			ldap_close($this->ldap_connection);
			$this->db_functions->write_log('rename computer',$old_dn . '->' . $new_rdn);
			return $result;
		}
		
		function ldap_add_attribute($ldap_add_attribute, $dn)
		{
			$result = ldap_mod_add($this->ldap_connection, $dn, $ldap_add_attribute);
			ldap_close($this->ldap_connection);
			$this->db_functions->write_log('added ldap attributes from computer',$dn);						
			return $result;
		}
		
		function ldap_remove_attribute($ldap_remove_attribute, $dn)
		{
			$result = ldap_mod_del($this->ldap_connection, $dn, $ldap_remove_attribute);
			ldap_close($this->ldap_connection);
			$this->db_functions->write_log('removed ldap attributes from computer',$dn);						
			return $result;
		}

		function ldap_replace_attribute($ldap_replace_attribute, $dn)
		{
			$result = ldap_mod_replace($this->ldap_connection, $dn, $ldap_replace_attribute);
			ldap_close($this->ldap_connection);
			$this->db_functions->write_log('replace ldap attributes from computer',$dn);
			return $result;
		}
	}
?>