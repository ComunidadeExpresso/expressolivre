<?php
	/**********************************************************************************\
	* Expresso Administração                 									      *
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
	
	class maillist
	{
		var $ldap_functions;
		var $db_functions;
		var $imap_functions;
		var $functions;
		var $current_config;
		
		
		function maillist()
		{
			$this->ldap_functions = new ldap_functions;
			$this->db_functions = new db_functions;
			$this->imap_functions = new imap_functions;
			$this->functions = new functions;
			$this->current_config = $_SESSION['phpgw_info']['expresso']['expressoAdmin']; 
		}
		
		function validate_fields($params)
		{
			return $this->ldap_functions->validate_fields_maillist($params);
		}
		
		function create($params)
		{
			// Verifica o acesso do gerente
			if (!$this->functions->check_acl($_SESSION['phpgw_session']['session_lid'], 'add_maillists'))
			{
				$return['status'] = false;
				$return['msg'] = lang('You do not have access to add email lists') . '.';
				return $return;
			}
			
			$return['status'] = true;
			
			//Retira os mailForwardingAddress duplicados, se existir algum.
			$array_tmp = array();
			$array_tmp = array_unique($params['mailForwardingAddress']);
			$params['mailForwardingAddress'] = $array_tmp;
			
			// Leio o ID a ser usado na criação do objecto.
			$next_id = ($this->db_functions->get_next_id('accounts'));
			if ((!is_numeric($next_id['id'])) || (!$next_id['status']))
			{
				$return['status'] = false;
				$return['msg'] = lang('problems getting user id') . ".\n" . $id['msg'];
				return $return;
			}
			else
			{
				$id = $next_id['id'];
			}			
			
			// Cria array para incluir no LDAP
			$dn = 'uid=' . $params['uid'] . ',' . $params['context'];			
			
			$maillist_info = array();
			$maillist_info['uid']						= $params['uid'];  
			$maillist_info['givenName']					= 'MailList';
			$maillist_info['sn']						= $params['uid'];
			$maillist_info['cn']						= $params['cn'];
			
			$maillist_info['homeDirectory']				= '/home/false';
			$maillist_info['loginShell']				= '/bin/false';
			$maillist_info['mail']						= $params['mail'];
			$maillist_info['objectClass'][0]			= 'posixAccount';
			$maillist_info['objectClass'][1]			= 'inetOrgPerson';
			$maillist_info['objectClass'][2]			= 'shadowAccount';
			$maillist_info['objectClass'][3]			= 'qmailuser';
			$maillist_info['objectClass'][4]			= 'phpgwAccount';
			$maillist_info['objectClass'][5]			= 'top';
			$maillist_info['objectClass'][6]			= 'person';
			$maillist_info['objectClass'][7]			= 'organizationalPerson';			
			$maillist_info['phpgwAccountExpires']		= '-1';
			$maillist_info['phpgwAccountType']			= 'l';
			$maillist_info['uidnumber']					= $id;
			$maillist_info['gidnumber']					= '0';
			$maillist_info['userPassword']				= '';
			$maillist_info['deliveryMode']				= 'forwardOnly';
			
			if ($params['accountStatus'] == 'on')
				$maillist_info['accountStatus'] = 'active';
			
			if ($params['phpgwAccountVisible'] == 'on')
				$maillist_info['phpgwAccountVisible'] = '-1';
						
			$maillist_info['mailForwardingAddress'] = $params['mailForwardingAddress'];
			
			if (!empty($params['description']))
				$maillist_info['description'] = utf8_encode($params['description']);
			
			$result = $this->ldap_functions->ldap_add_entry($dn, $maillist_info);
			if (!$result['status'])
			{
				$return['status'] = false;
				$return['msg'] .= $result['msg'];
			}
			
			/* log */
			if ($return['status'] == true)
			{
				$this->db_functions->write_log('created email list',$dn);
				
				foreach($params['mailForwardingAddress'] as $index=>$mail)
				{
					$this->db_functions->write_log("added user on email list creation", $params['cn'].':' . $mail);
				}
			}
			
			return $return;
		}
		
		function save($new_values)
		{
			// Verifica o acesso do gerente
			if (!$this->functions->check_acl($_SESSION['phpgw_session']['session_lid'], 'edit_maillists'))
			{
				$return['status'] = false;
				$return['msg'] = lang('You do not have access to edit email lists') . '.';
				return $return;
			}

			$return['status'] = true;

			$old_values = $this->get_info($new_values['uidnumber'], $new_values['manager_context']);
			$diff = array_diff($new_values, $old_values);
			
			$dn = 'uid=' . $old_values['uid'] . ',' . $old_values['context'];
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// RENAME
			if ($diff['context'] || $diff['uid'])
			{
				if ( (strcasecmp($old_values['uid'], $new_values['uid']) != 0) || (strcasecmp($old_values['context'], $new_values['context']) != 0) )
				{
					$newrdn = 'uid=' . $new_values['uid'];
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
						$old_dn = $old_values['uid'];
						$this->db_functions->write_log("renamed list login",$old_dn . ' -> ' . $dn);
					}
				}
			}
			
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// REPLACE MAIL || CN || SN
			if ($new_values['mail'] != $old_values['mail'])
			{
				$ldap_mod_replace['mail'] = $new_values['mail'];
				$this->db_functions->write_log('modified list email', $dn . ': ' . $old_values['mail'] . '->' . $new_values['mail']);
			}
			if ($new_values['cn'] != $old_values['cn'])
			{
				$ldap_mod_replace['cn'] = $new_values['cn'];
				$this->db_functions->write_log('modified list name', $old_values['cn'] . '->' . $new_values['cn']);
			}
			if ($diff['uid'])
			{
				$ldap_mod_replace['sn'] = $new_values['uid'];
			}
			
			/* Always replace description */
			if (empty($new_values['description']))
				$ldap_mod_replace['description'] = array();
			else
				$ldap_mod_replace['description'] = utf8_encode($new_values['description']);
				
			if (count($ldap_mod_replace))
			{
				$result = $this->ldap_functions->replace_user_attributes($dn, $ldap_mod_replace);
				if (!$result['status'])
				{
					$return['status'] = false;
					$return['msg'] .= $result['msg'];
				}
			}
			
			//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// REMOVE ATTRS
			if (($old_values['accountStatus'] == 'active') && ($new_values['accountStatus'] != 'on'))
				$ldap_remove['accountStatus']	= array();
			
			if (($old_values['phpgwAccountVisible'] == '-1') && ($new_values['phpgwAccountVisible'] != 'on'))
				$ldap_remove['phpgwAccountVisible']	= array();
			
			if (count($ldap_remove))
			{
				$result = $this->ldap_functions->remove_user_attributes($dn, $ldap_remove);
				if (!$result['status'])
				{
					$return['status'] = false;
					$return['msg'] .= $result['msg'];
				}
			}
			
			//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// ADD ATTRS
			if (($old_values['accountStatus'] != 'active') && ($new_values['accountStatus'] == 'on'))
				$ldap_add['accountStatus']	= 'active';
			
			if (($old_values['phpgwAccountVisible'] != '-1') && ($new_values['phpgwAccountVisible'] == 'on'))
				$ldap_add['phpgwAccountVisible'] = '-1';
			
			if (count($ldap_add))
			{
				$result = $this->ldap_functions->add_user_attributes($dn, $ldap_add);
				if (!$result['status'])
				{
					$return['status'] = false;
					$return['msg'] .= $result['msg'];
				}
			}
			
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// USERS
			/*
			echo '<pre>';
			print_r($new_values['mailForwardingAddress']);
			*/
			if (!$new_values['mailForwardingAddress'])
				$new_values['mailForwardingAddress'] = array();
			if (!$old_values['mailForwardingAddress'])
				$old_values['mailForwardingAddress'] = array();

			$add_users = array_diff($new_values['mailForwardingAddress'], $old_values['mailForwardingAddress']);
			$remove_users = array_diff($old_values['mailForwardingAddress'], $new_values['mailForwardingAddress']);
			
			if (count($add_users)>0)
			{
				sort($add_users);
				$result_add_users = $this->ldap_functions->add_user2maillist($new_values['uid'], $add_users);
				
				if (!$result_add_users['status'])
				{
					$return['status'] = false;
					$return['msg'] .= $result_add_users['msg'];
				}
				else
				{
					foreach($add_users as $index=>$mail)
					{
						$this->db_functions->write_log("added user to list", "$dn: $mail");
					}
				}
			}
			
			if (count($remove_users)>0)
			{
				sort($remove_users);
				$result_remove_users = $this->ldap_functions->remove_user2maillist($new_values['uid'], $remove_users);
				
				if (!$result_remove_users['status'])
				{
					$return['status'] = false;
					$return['msg'] .= $result_remove_users['msg'];
				}
				else
				{
					foreach($remove_users as $index=>$mail)
					{
						$this->db_functions->write_log("removed user from list", "$dn: $mail");
					}
				}
			}
			
			return $return;
		}		
		
		function save_scl($new_values)
		{
			// Verifica o acesso do gerente
			if (!$this->functions->check_acl($_SESSION['phpgw_session']['session_lid'], 'edit_scl_email_lists'))
			{
				$return['status'] = false;
				$return['msg'] = lang('You do not have access to edit email lists SCL') . '.';
				return $return;
			}
			
			$return['status'] = true;

			//Retira os uids duplicados se existir
			$array_tmp = array();
			$array_tmp = @array_unique($new_values['members']);
			$new_values['members'] = $array_tmp;
			
			$old_values = $this->get_scl_info($new_values['uidnumber'], $new_values['manager_context']);
			
			$diff = array_diff($new_values, $old_values);
			$dn = $old_values['dn'];
			
			//echo '<pre>';
			//print_r($new_values['participantCanSendMail']);
			//print_r($old_values['participantCanSendMail']);
			
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// ADD ATTRS
			if (($new_values['participantCanSendMail'] == 'on') && ($old_values['participantCanSendMail'] == ''))
			{
				$ldap_add['participantCanSendMail'] = "TRUE";
				$this->db_functions->write_log("turned on participantCanSendMail",$new_values['mail']);
			}
			if (($new_values['accountRestrictive'] == 'on') && ($old_values['accountRestrictive'] == ''))
			{
				$ldap_add['accountRestrictive'] = "mailListRestriction";
				$ldap_add['accountDeliveryMessage']	= 'OK';
				$this->db_functions->write_log("turned on mailListRestriction", $new_values['mail']);
			}
			if (count($ldap_add))
			{
				$result = $this->ldap_functions->add_user_attributes($dn, $ldap_add);
				if (!$result['status'])
				{
					$return['status'] = false;
					$return['msg'] .= $result['msg'];
				}
			}
			
			//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// REMOVE ATTRS
			if (($new_values['participantCanSendMail'] != 'on') && ($old_values['participantCanSendMail'] == 'TRUE'))
			{
				$ldap_remove['participantCanSendMail']	= array();
				$this->db_functions->write_log("turned off participantCanSendMail",$new_values['mail']);
			}
			if (($new_values['accountRestrictive'] != 'on') && ($old_values['accountRestrictive'] == 'mailListRestriction'))
			{
				$ldap_remove['accountRestrictive']	= array();
				$ldap_remove['accountDeliveryMessage']	= array();
				$this->db_functions->write_log("turned off mailListRestriction",$new_values['mail']);
			}
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
				sort($add_users);
				$result = $this->ldap_functions->add_user2maillist_scl($dn, $add_users);
				
				/* log */
				if (!$result['status'])
				{
					$return['status'] = false;
					$return['msg'] .= $result['msg'];
				}
				else
				{
					foreach($add_users as $index=>$mail)
					{
						$this->db_functions->write_log("added user to SCL","$dn: $mail",'','','');
					}
				}
			}
			
			if (count($remove_users)>0)
			{
				sort($remove_users);
				$result = $this->ldap_functions->remove_user2maillist_scl($dn, $remove_users);
				
				/* log */
				if (!$result['status'])
				{
					$return['status'] = false;
					$return['msg'] .= $result['msg'];
				}
				else
				{
					foreach($add_users as $index=>$mail)
					{
						$this->db_functions->write_log("removed user from SCL","$dn: $mail",'','','');
					}
				}
			}
			
			return $return;
		}				
		
		function get_info($uidnumber)
		{
			$maillist_info_ldap = $this->ldap_functions->get_maillist_info($uidnumber);
			return $maillist_info_ldap;
		}

		function get_scl_info($uidnumber)
		{
			$maillist_info_ldap = $this->ldap_functions->get_maillist_scl_info($uidnumber);
			return $maillist_info_ldap;
		}
		
		function delete($params)
		{
			// Verifica o acesso do gerente
			if (!$this->functions->check_acl($_SESSION['phpgw_session']['session_lid'], 'delete_maillists'))
			{
				$return['status'] = false;
				$return['msg'] = lang('You do not have access to delete email lists') . '.';
				return $return;
			}

			$return['status'] = true;

			$uidnumber = $params['uidnumber'];
			$uid = $this->ldap_functions->uidnumber2uid($uidnumber);
			$mail = $this->ldap_functions->uidnumber2mail($uidnumber);

			//LDAP
			$result_ldap = $this->ldap_functions->delete_maillist($uidnumber, $mail);
			if (!$result_ldap['status'])
			{
				$return['status'] = false;
				$return['msg'] .= $result_ldap['msg'];
			}
			
			if ($return['status'] == true)
			{
				$this->db_functions->write_log('deleted email list',$uid);
			}
			
			return $return;	
		}
	}
?>