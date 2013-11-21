<?php
	/**********************************************************************************\
	* Expresso Administraчуo                 									      *
	* by Joao Alfredo Knopik Junior (joao.alfredo@gmail.com, jakjr@celepar.pr.gov.br) *
	* --------------------------------------------------------------------------------*
	*  This program is free software; you can redistribute it and/or modify it		  *
	*  under the terms of the GNU General Public License as published by the		  *
	*  Free Software Foundation; either version 2 of the License, or (at your		  *
	*  option) any later version.													  *
	\**********************************************************************************/
	
	include_once('class.ldap_functions.inc.php');
	include_once('class.db_functions.inc.php');
	include_once('class.imap_functions.inc.php');
	include_once('class.functions.inc.php');
	
	class group
	{
		var $ldap_functions;
		var $db_functions;
		var $imap_functions;
		var $functions;
		var $current_config;
		
		function group()
		{
			$this->ldap_functions = new ldap_functions;
			$this->db_functions = new db_functions;
			$this->imap_functions = new imap_functions;
			$this->functions = new functions;
			$this->current_config = $_SESSION['phpgw_info']['expresso']['expressoAdmin']; 
		}
		
		function validate_fields($params)
		{
			return $this->ldap_functions->validate_fields_group($params);
		}
		
		function create($params)
		{
			// Verifica o acesso do gerente
			if (!$this->functions->check_acl($_SESSION['phpgw_session']['session_lid'], 'add_groups'))
			{
				$return['status'] = false;
				$return['msg'] = lang('You do not have access to create new groups') . '.';
				return $return;
			}
			
			$return['status'] = true;

			//Retira os uids duplicados se existir
			$array_tmp = array();
			$array_tmp = @array_unique($params['members']);
			$params['members'] = $array_tmp;

			// Leio o ID a ser usado na criaчуo do objecto.
			$next_id = ($this->db_functions->get_next_id('groups'));
			if ((!is_numeric($next_id['id'])) || (!$next_id['status']))
			{
				$return['status'] = false;
				$return['msg'] = lang('Problems getting  group ID') . ':' . $next_id['msg'];
				return $return;
			}
			else
			{
				$id = $next_id['id'];
			}
			
			// Cria array para incluir no LDAP
			$dn = 'cn=' . $params['cn'] . ',' . $params['context'];			
			
			$group_info = array();
			$group_info['cn']					= $params['cn'];
			$group_info['description']			= $params['description'];
			$group_info['gidNumber']			= $id;
			$group_info['objectClass'][]		= 'top';
			$group_info['objectClass'][]		= 'posixGroup';
			$group_info['objectClass'][]		= 'phpgwAccount';
			$group_info['phpgwAccountExpires']	= '-1';
			$group_info['phpgwAccountType']		= 'g';
			$group_info['userPassword']			= '';
			
			// E-mail for groups
			if ($params['email'] != '')
				$group_info['mail'] = $params['email'];
			
			if ( (count($params['members'])) && (is_array($params['members'])) )
			{
				foreach ($params['members'] as $index => $uidnumber)
				{
					$uid = $this->ldap_functions->uidnumber2uid($uidnumber);
					$group_info['memberuid'][] = $uid;
					
					// Chama funcao para incluir os uidnumbers dos usuarios no grupo
					$result = $this->db_functions->add_user2group($id, $uidnumber);
					
					$this->db_functions->write_log("Added user to group on user criation", $group_info['cn'] . ": " . $dn);
				}
			}
			
			// Suporte ao SAMBA
			if (($this->current_config['expressoAdmin_samba_support'] == 'true') && ($params['use_attrs_samba'] == 'on'))
			{
				$group_info['objectClass'][]  = 'sambaGroupMapping';
				$group_info['sambaSID']		  = $params['sambasid'] . '-' . (($id * 2) + 1001);
				$group_info['sambaGroupType'] = '2';
			}
			
			// ADD ATTRIBUTES
			if ($params['phpgwaccountvisible'] == 'on')
			{
				$group_info['phpgwaccountvisible'] = '-1';
			}
			
			$result = $this->ldap_functions->ldap_add_entry($dn, $group_info);
			if (!$result['status'])
			{
				$return['status'] = false;
				if ($result['error_number'] == '65')
				{
					$return['msg'] .= lang("It was not possible create the group because the LDAP schemas are not update") . "\n" .
									  lang("The administrator must update the directory /etc/ldap/schema/ and re-start LDAP") . "\n" .
									  lang("A updated version of these files can be found here") . ":\n" .
										"www.expressolivre.org -> Downloads -> schema.tgz";
				}
				else
					$return['msg'] .= $result['msg'];
			}
			
			// Chama funcao para incluir os aplicativos ao grupo
			$result = $this->db_functions->add_id2apps($id, $params['apps']);
			if (!$result['status'])
			{
				$return['status'] = false;
				$return['msg'] .= $result['msg'];
			}
			
			if ($return['status'] == true)
			{
				$this->db_functions->write_log("Created group",$dn);
			}
			
			return $return;
		}
		
		function save($new_values)
		{
			// Verifica o acesso do gerente
			if (!$this->functions->check_acl($_SESSION['phpgw_session']['session_lid'], 'edit_groups'))
			{
				$return['status'] = false;
				$return['msg'] = lang('You do not have access to edit groups') . '.';
				return $return;
			}
			
			$return['status'] = true;

			//Retira os uids duplicados se existir
			$array_tmp = array();
			$array_tmp = array_unique($new_values['members']);
			$new_values['members'] = $array_tmp;
						
			$old_values = $this->get_info($new_values['gidnumber'], $new_values['manager_context']);
			$diff = array_diff($new_values, $old_values);
			
			$dn = 'cn=' . $old_values['cn'] . ',' . $old_values['context'];			
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// RENAME
			if ($diff['context'] || $diff['cn'])
			{
				if ( (strcasecmp($old_values['cn'], $new_values['cn']) != 0) || (strcasecmp($old_values['context'], $new_values['context']) != 0) )
				{
					$newrdn = 'cn=' . $new_values['cn'];
					$newparent = $new_values['context'];
					$result =  $this->ldap_functions->change_user_context($dn, $newrdn, $newparent);
					if (!$result['status'])
					{
						$return['status'] = false;
						$return['msg'] .= $result['msg'];
					}
					else
					{
						$dn = $newrdn . ',' . $newparent;
						$this->db_functions->write_log('Renamed group', $old_values['cn'] . '->' . $dn);
					}
				}
			}
			
			$ldap_mod_replace = array();
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// REPLACE SAMBASID OF SAMBA
			if ( ($this->current_config['expressoAdmin_samba_support'] == 'true') && ($diff['sambasid']) && ($old_values['sambasid']))
			{
				$ldap_mod_replace['sambasid'] = $new_values['sambasid'] . '-' . ((2 * $new_values['gidnumber'])+1001);
				$this->db_functions->write_log('modified group samba domain', $dn . ': ' . $old_values['sambasid'] . '->' . $new_values['sambasid']);
				
			}
			
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// REPLACE DESCRIPTION
			if ($new_values['description'] != $old_values['description'])
			{
				$ldap_mod_replace['description'] = $new_values['description'];
				$this->db_functions->write_log('modified group description',$dn . ': ' . $old_values['description'] . '->' . $new_values['description'] );
			}

			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// REPLACE E-Mail
			if ((($old_values['email']) && ($diff['email'])) && 
				$this->functions->check_acl($_SESSION['phpgw_session']['session_lid'],'edit_email_groups'))
			{
				$ldap_mod_replace['mail'] = $new_values['email'];
				$this->db_functions->write_log('modified group email', $dn . ': ' . $old_values['email'] . '->' . $new_values['email']);
			}

			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// CALL LDAP_REPLACE FUNCTION
			if (count($ldap_mod_replace))
			{
				$result = $this->ldap_functions->replace_user_attributes($dn, $ldap_mod_replace);
				if (!$result['status'])
				{
					$return['status'] = false;
					if ($result['error_number'] == '65')
					{
						$return['msg'] .= lang("It was not possible create the group because the LDAP schemas are not update") . "\n" .
										  lang("The administrator must update the directory /etc/ldap/schema/ and re-start LDAP") . "\n" .
										  lang("A updated version of these files can be found here") . ":\n" .
											"www.expressolivre.org -> Downloads -> schema.tgz";
					}
					else
						$return['msg'] .= $result['msg'];
				}
			}			
			
			//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// REMOVE ATTRS OF SAMBA
			if (($this->current_config['expressoAdmin_samba_support'] == 'true') && ($old_values['sambaGroup']) && ($new_values['use_attrs_samba'] != 'on'))
			{
				$ldap_remove['objectclass'] 	= 'sambaGroupMapping';	
				$ldap_remove['sambagrouptype']	= array();
				$ldap_remove['sambaSID']		= array();
				
				$result = $this->ldap_functions->remove_user_attributes($dn, $ldap_remove);
				if (!$result['status'])
				{
					$return['status'] = false;
					$return['msg'] .= $result['msg'];
				}
				else
					$this->db_functions->write_log('removed group samba attributes',$dn);
			}

			//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// ADD ATTRS OF SAMBA
			if (($this->current_config['expressoAdmin_samba_support'] == 'true') && (!$old_values['sambaGroup']) && ($new_values['use_attrs_samba'] == 'on'))
			{
				$ldap_add['objectClass'][] 		= 'sambaGroupMapping';
				$ldap_add['sambagrouptype']		= '2';
				$ldap_add['sambasid']			= $new_values['sambasid'] . '-' . ((2 * $new_values['gidnumber'])+1001);
					
				$result = $this->ldap_functions->add_user_attributes($dn, $ldap_add);
				if (!$result['status'])
				{
					$return['status'] = false;
					$return['msg'] .= $result['msg'];
				}
				else
					$this->db_functions->write_log('Added samba attibutes to group',$dn);
			}
			
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// ADD ATTRIBUTES
			$ldap_add = array();
			if (($new_values['phpgwaccountvisible'] == 'on') && ($old_values['phpgwaccountvisible'] != '-1'))
			{
				$ldap_add['phpgwaccountvisible'] = '-1';
				$this->db_functions->write_log("added attribute phpgwAccountVisible to group",$dn);
			}
			if ((($new_values['email']) && (!$old_values['email'])) &&
				$this->functions->check_acl($_SESSION['phpgw_session']['session_lid'],'edit_email_groups'))
			{
				$ldap_add['mail'] = $new_values['email'];
				$this->db_functions->write_log("added attribute mail to group",$dn);
			}
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// CALL LDAP_ADD FUNCTION			
			if (count($ldap_add))
			{
				$result = $this->ldap_functions->add_user_attributes($dn, $ldap_add);
				if (!$result['status'])
				{
					$return['status'] = false;
					if ($result['error_number'] == '65')
					{
						$return['msg'] .= lang("It was not possible create the group because the LDAP schemas are not update") . "\n" .
										  lang("The administrator must update the directory /etc/ldap/schema/ and re-start LDAP") . "\n" .
										  lang("A updated version of these files can be found here") . ":\n" .
											   "www.expressolivre.org -> Downloads -> schema.tgz";
					}									
					else
						$return['msg'] .= $result['msg'];
				}
			}
						
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// REMOVE ATTRIBUTES
			$ldap_remove = array();
			if (($new_values['phpgwaccountvisible'] != 'on') && ($old_values['phpgwaccountvisible'] == '-1'))
			{
				$ldap_remove['phpgwaccountvisible'] = array();
				$this->db_functions->write_log("removed attribute phpgwAccountVisible from group",$dn);
			}
			if (((!$new_values['email']) && ($old_values['email'])) &&
				$this->functions->check_acl($_SESSION['phpgw_session']['session_lid'],'edit_email_groups'))
			{
				$ldap_remove['mail'] = array();
				$this->db_functions->write_log("removed attribute mail from group",$dn);
			}
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// CALL LDAP_REMOVED FUNCTION			
			if (count($ldap_remove))
			{
				$result = $this->ldap_functions->remove_user_attributes($dn, $ldap_remove);
				if (!$result['status'])
				{
					$return['status'] = false;
					$return['msg'] .= $result['msg'];
				}
			}

			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// USERS

			if (!$new_values['members'])
				$new_values['members'] = array();
			if (!$old_values['members'])
				$old_values['members'] = array();

			$add_users = array_diff($new_values['members'], $old_values['members']);
			$remove_users = array_diff($old_values['members'], $new_values['members']);

			if (count($add_users)>0)
			{
				$array_memberUids_add = array();
				foreach($add_users as $uidnumber)
				{
					if (is_numeric($uidnumber) && ($uidnumber != -1))
					{
						$this->db_functions->add_user2group($new_values['gidnumber'], $uidnumber);
						$user = $this->ldap_functions->uidnumber2uid($uidnumber);
						$array_memberUids_add[] = $user;
						$this->db_functions->write_log("included user to group","dn:$dn -> uid:$user");
					}
				}
				if (count($array_memberUids_add) > 0)
					$this->ldap_functions->add_user2group($new_values['gidnumber'], $array_memberUids_add);
			}
			if (count($remove_users)>0)
			{
				$array_memberUids_remove = array();
				foreach($remove_users as $uidnumber)
				{
					if ($uidnumber != -1)
					{
						$this->db_functions->remove_user2group($new_values['gidnumber'], $uidnumber);
						$user = $this->ldap_functions->uidnumber2uid($uidnumber);
						$array_memberUids_remove[] = $user;
						$this->db_functions->write_log("removed user from group","$dn: $user");
					}
				}
				if (count($array_memberUids_remove)>0)
					$this->ldap_functions->remove_user2group($new_values['gidnumber'], $array_memberUids_remove);
			}
			
			//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// APPS
			$new_values2 = array();
			$old_values2 = array();
			if (count($new_values['apps'])>0)
			{
				foreach ($new_values['apps'] as $app=>$tmp)
				{
					$new_values2[] = $app;
				}
			}
			if (count($old_values['apps'])>0)
			{
				foreach ($old_values['apps'] as $app=>$tmp)
				{
					$old_values2[] = $app;
				}
			}
			
			$add_apps    = array_flip(array_diff($new_values2, $old_values2));
			$remove_apps = array_flip(array_diff($old_values2, $new_values2));

			if (count($add_apps)>0)
			{
				$this->db_functions->add_id2apps($new_values['gidnumber'], $add_apps);
				
				foreach ($add_apps as $app => $index)
					$this->db_functions->write_log("added application to group","$app: $dn");
			}
			
			if (count($remove_apps)>0)
			{
				//Verifica se o gerente tem acesso a aplicaчуo antes de remove-la do usuario.
				$manager_apps = $this->db_functions->get_apps($_SESSION['phpgw_session']['session_lid']);
					
				foreach ($remove_apps as $app => $app_index)
				{
					if ($manager_apps[$app] == 'run')
						$remove_apps2[$app] = $app_index;
				}
				$this->db_functions->remove_id2apps($new_values['gidnumber'], $remove_apps2);
					
				foreach ($remove_apps2 as $app => $access)
					$this->db_functions->write_log("removed application from group","$app: $dn");
			}
			//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			
			return $return;
		}		
		
		
		function get_info($gidnumber)
		{
			$group_info_ldap = $this->ldap_functions->get_group_info($gidnumber);
			$group_info_db = $this->db_functions->get_group_info($gidnumber);
			
			$group_info = array_merge($group_info_ldap, $group_info_db);
			return $group_info;
		}

		function delete($params)
		{
			// Verifica o acesso do gerente
			if (!$this->functions->check_acl($_SESSION['phpgw_session']['session_lid'], 'delete_groups'))
			{
				$return['status'] = false;
				$return['msg'] = lang('You do not have acces to remove groups') . '.';
				return $return;
			}
			
			$return['status'] = true;
			
			$gidnumber = $params['gidnumber'];
			$cn = $params['cn'];
			
			//LDAP
			$result_ldap = $this->ldap_functions->delete_group($gidnumber);
			if (!$result_ldap['status'])
			{
				$return['status'] = false;
				$return['msg'] .= $result_ldap['msg'];
			}
			
			//DB
			$result_db = $this->db_functions->delete_group($gidnumber);
			if (!$result_db['status'])
			{
				$return['status'] = false;
				$return['msg'] .= $result_ldap['msg'];
			}
			
			if ($return['status'] == true)
			{
				$this->db_functions->write_log("deleted group","$cn");
			}
			
			return $return;	
		}
		
	}
?>