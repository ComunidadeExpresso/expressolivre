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
	
	class user
	{
		var $ldap_functions;
		var $db_functions;
		var $imap_functions;
		var $functions;
		var $current_config;
		
		function user()
		{
			$this->ldap_functions = new ldap_functions;
			$this->db_functions = new db_functions;
			$this->imap_functions = new imap_functions;
			$this->functions = new functions;
			$this->current_config = $_SESSION['phpgw_info']['expresso']['expressoAdmin']; 
		}
		
		function create($params)
		{
			$return['status'] = true;
		
			if($this->db_functions->use_cota_control()) { 
			                        //Verifica quota de usuários e disco             
			                        $setor = $this->functions->get_info($params['context']); 
			                        if (!$this->functions->existe_quota_usuario($setor[0])) { 
			                                $return['status'] = false; 
			                                $return['msg'] = $this->functions->lang("user cota exceeded");//TODO colocar valor de acordo com tabela de traduções. 
			                                return $return; 
			                        }  
			                        if (!$this->functions->existe_quota_disco($setor[0],$params['mailquota'])) { 
			                                $return['status'] = false; 
			                                $return['msg'] = $this->functions->lang("disk cota exceeded");//TODO colocar valor de acordo com tabela de traduções. 
			                                return $return;                          
			                        } 
			 } 
 		                 
			// Verifica o acesso do gerente
			if ($this->functions->check_acl($_SESSION['phpgw_session']['session_lid'], 'add_users'))
			{
				// Adiciona a organização na frente do uid.
				if ($this->current_config['expressoAdmin_prefix_org'] == 'true')
				{
					$context_dn = ldap_explode_dn(strtolower($GLOBALS['phpgw_info']['server']['ldap_context']), 1);
				
					$explode_dn = ldap_explode_dn(strtolower($params['context']), 1);
					$explode_dn = array_reverse($explode_dn);
					//$params['uid'] = $explode_dn[3] . '-' . $params['uid'];
					$params['uid'] = $explode_dn[$context_dn['count']] . '-' . $params['uid'];
				}
			
				// Leio o ID a ser usado na criação do objecto. Esta função já incrementa o ID no BD.
				$next_id = ($this->db_functions->get_next_id('accounts'));
				if ((!is_numeric($next_id['id'])) || (!$next_id['status']))
				{
					$return['status'] = false;
					$return['msg'] = $this->functions->lang('problems getting user id') . ".\n" . $id['msg'];
					return $return;
				}
				else
				{
					$id = $next_id['id'];
				}
			
				// Cria array para incluir no LDAP
				$dn = 'uid=' . $params['uid'] . ',' . $params['context'];
                $user_info = array();
				$user_info['accountStatus'] 			= $params['accountstatus'] == 1 ? 'active' : 'desactive';
				$user_info['cn']						= $params['givenname'] . ' ' . $params['sn'];
				$user_info['gidNumber']					= $params['gidnumber'];
				$user_info['givenName']					= $params['givenname'];
				$user_info['homeDirectory']				= '/home/' . $params['uid'];
				$user_info['mail']						= $params['mail'];
				$user_info['objectClass'][]				= 'posixAccount';
				$user_info['objectClass'][]				= 'inetOrgPerson';
				$user_info['objectClass'][]				= 'shadowAccount';
				$user_info['objectClass'][]				= 'qmailuser';
				$user_info['objectClass'][]				= 'phpgwAccount';
				$user_info['objectClass'][]				= 'top';
				$user_info['objectClass'][]				= 'person';
				$user_info['objectClass'][]				= 'organizationalPerson';
				$user_info['phpgwAccountExpires']		= '-1';
				$user_info['phpgwAccountType']			= 'u';
				$user_info['sn']						= $params['sn'];
				$user_info['uid']						= $params['uid'];
				$user_info['uidnumber']					= $id;
				$user_info['userPassword']				= '{md5}' . base64_encode(pack("H*",md5($params['password1'])));
				
				if (isset($params['passwd_expired']) && $params['passwd_expired'] == '1')
					$user_info['phpgwLastPasswdChange'] = '0';
				
				// Gerenciar senhas RFC2617
				if ( isset($this->current_config['expressoAdmin_userPasswordRFC2617']) && $this->current_config['expressoAdmin_userPasswordRFC2617'] == 'true' )
				{
					$realm		= $this->current_config['expressoAdmin_realm_userPasswordRFC2617'];
					$uid		= $user_info['uid'];
					$password	= $params['password1'];
					$user_info['userPasswordRFC2617'] = $realm . ':      ' . md5("$uid:$realm:$password");
				}
				
				if ($params['phpgwaccountstatus'] == '1')
					$user_info['phpgwAccountStatus'] = 'A';
			
				if ($params['departmentnumber'] != '')
					$user_info['departmentnumber']	= $params['departmentnumber'];
			
				if ($params['telephonenumber'] != '')
					$user_info['telephoneNumber']	= $params['telephonenumber'];
						
				// Cria user_info no caso de ter alias e forwarding email.
				foreach ($params['mailalternateaddress'] as $index=>$mailalternateaddress)
				{
					if ($mailalternateaddress != '')
						$user_info['mailAlternateAddress'][] = $mailalternateaddress;
				}
			
				foreach ($params['mailforwardingaddress'] as $index=>$mailforwardingaddress)
				{
					if ($mailforwardingaddress != '')
						$user_info['mailForwardingAddress'][] = $mailforwardingaddress;
				}
				
				if (isset($params['deliverymode']) && $params['deliverymode'])
					$user_info['deliveryMode'] = 'forwardOnly';
			
				//Ocultar da pesquisa e do catálogo
				if (isset($params['phpgwaccountvisible']) && $params['phpgwaccountvisible'])
					$user_info['phpgwAccountVisible'] = '-1';

				// Suporte ao SAMBA
				if (($this->current_config['expressoAdmin_samba_support'] == 'true') && ($params['use_attrs_samba'] == 'on'))
				{
					
					// Qualquer um que crie um usuário, deve ter permissão para adicionar a senha samba.
					// Verifica o acesso do gerente aos atributos samba
					//if ($this->functions->check_acl($_SESSION['phpgw_session']['session_lid'], 'edit_sambausers_attributes'))
					//{
						//Verifica se o binario para criar as senhas do samba exite.
						if (!is_file('/home/expressolivre/mkntpwd'))
						{
							$return['status'] = false;
							$return['msg'] .= 
									$this->functions->lang("the binary file /home/expressolivre/mkntpwd does not exist") . ".\\n" .
									$this->functions->lang("it is needed to create samba passwords") . ".\\n" . 
									$this->functions->lang("alert your administrator about this") . ".";
						}
						else
						{
							$user_info['objectClass'][] 		= 'sambaSamAccount';
							$user_info['loginShell']			= '/bin/bash';
	
							$user_info['sambaSID']				= $params['sambadomain'] . '-' . ((2 * $id)+1000);
							$user_info['sambaPrimaryGroupSID']	= $params['sambadomain'] . '-' . ((2 * $user_info['gidNumber'])+1001);

							$user_info['sambaAcctFlags']		= $params['sambaacctflags'];
			
							$user_info['sambaLogonScript']		= $params['sambalogonscript'];
							$user_info['homeDirectory']			= $params['sambahomedirectory'];
			
							$user_info['sambaLMPassword']		= exec('/home/expressolivre/mkntpwd -L "'.$params['password1'] . '"');
							$user_info['sambaNTPassword']		= exec('/home/expressolivre/mkntpwd -N "'.$params['password1'] . '"');
							
							$user_info['sambaPasswordHistory']	= '0000000000000000000000000000000000000000000000000000000000000000';
			
							$user_info['sambaPwdCanChange']		= strtotime("now");
							$user_info['sambaPwdLastSet']		= strtotime("now");
							$user_info['sambaPwdMustChange']	= '2147483647';
						}
					//}
				}
				
				// Verifica o acesso do gerente aos atributos corporativos
				if ($this->functions->check_acl($_SESSION['phpgw_session']['session_lid'], 'manipulate_corporative_information'))
				{
					//retira caracteres que não são números.
					$params['corporative_information_cpf'] = preg_replace('/[^0-9]/', '', $params['corporative_information_cpf']);
					//description
					$params['corporative_information_description'] = utf8_encode($params['corporative_information_description']);
					foreach ($params as $atribute=>$value)
					{
						$pos = strstr($atribute, 'corporative_information_');
						if ($pos !== false)
						{
							if ($params[$atribute])
							{
								$ldap_atribute = str_replace("corporative_information_", "", $atribute);
								$user_info[$ldap_atribute] = $params[$atribute];
							}
						}
					}
				}
				
				$result = $this->ldap_functions->ldap_add_entry($dn, $user_info);
				if (!$result['status'])
				{
					$return['status'] = false;
					$return['msg'] .= $result['msg'];
				}
			
				// Chama funcao para salvar foto no OpenLDAP.			
				if ( ($_FILES['photo']['name'] != '') && ($this->functions->check_acl($_SESSION['phpgw_session']['session_lid'], 'edit_users_picture')) )
				{
					$result = $this->ldap_functions->ldap_save_photo($dn, $_FILES['photo']['tmp_name']);
					if (!$result['status'])
					{
						$return['status'] = false;
						$return['msg'] .= $result['msg'];
					}
				}
			
				//GROUPS
				if ($params['groups'])
				{
					foreach ($params['groups'] as $gidnumber)
					{
						$result = $this->ldap_functions->add_user2group($gidnumber, $user_info['uid']);
						if (!$result['status'])
						{
							$return['status'] = false;
							$return['msg'] .= $result['msg'];
						}
						$result = $this->db_functions->add_user2group($gidnumber, $id);
						if (!$result['status'])
						{
							$return['status'] = false;
							$return['msg'] .= $result['msg'];
						}
					}
				}
			
				// Inclusao do Mail do usuário nas listas de email selecionadas.
				if (isset($params['maillists']) && $params['maillists'])
				{
					foreach($params['maillists'] as $uid)
	            	{
						$result = $this->ldap_functions->add_user2maillist($uid, $user_info['mail']);
						if (!$result['status'])
						{
							$return['status'] = false;
							$return['msg'] .= $result['msg'];
						}
	            	}
				}
                       
				// APPS
				if( isset($params['apps']) && count($params['apps']) )
				{
					$result = $this->db_functions->add_id2apps($id, $params['apps']);
					if (!$result['status'])
					{
						$return['status'] = false;
						$return['msg'] .= $result['msg'];
					}
				}

				// Chama funcao para incluir no pgsql as preferencia de alterar senha.
				if ($params['changepassword'])
				{
					$result = $this->db_functions->add_pref_changepassword($id);
					if (!$result['status'])
					{
						$return['status'] = false;
						$return['msg'] .= $result['msg'];
					}
				}					
							
				// Chama funcao para criar mailbox do usuario, no imap-cyrus.
				$result = $this->imap_functions->create($params['uid'], $params['mailquota']);
				if (!$result['status'])
				{
					$return['status'] = false;
				$return['msg'] .= $result['msg'];
				}

				$this->db_functions->write_log("created user",$dn);
			}

			return $return;
		}
		
		function save($new_values)
		{
			$return['status'] = true;
			
			$old_values = $this->get_user_info($new_values['uidnumber']);
			
			$dn = 'uid=' . $old_values['uid'] . ',' . strtolower($old_values['context']);

			//retira caracteres que não são números.
			$new_values['corporative_information_cpf'] = preg_replace('/[^0-9]/', '', $new_values['corporative_information_cpf']);

			$diff = @array_diff($new_values, $old_values);

			//Verifica quota de disco, como estou alterando, não preciso checar quota de usuários. 
            if( $this->db_functions->use_cota_control() )
            {            
                    $setor = $this->functions->get_info($new_values['context']); 
                    if (!$this->functions->existe_quota_disco($setor[0],$new_values['mailquota'])) { 
                            $return['status'] = false; 
                            $return['msg'] = "Quota em disco excedida...";//TODO colocar valor de acordo com tabela de traduções. 
                            return $return;                          
                    } 
            } 
 		 
			$manager_account_lid = $_SESSION['phpgw_session']['session_lid'];
			if ((!$this->functions->check_acl($manager_account_lid,'edit_users')) &&
				(!$this->functions->check_acl($manager_account_lid,'change_users_password')) &&
				(!$this->functions->check_acl($manager_account_lid,'edit_sambausers_attributes')) &&
				(!$this->functions->check_acl($manager_account_lid,'manipulate_corporative_information')) &&
				(!$this->functions->check_acl($manager_account_lid,'edit_users_phonenumber'))
				)
			{
				$return['status'] = false;
				$return['msg'] = $this->functions->lang('You do not have access to edit user informations') . '.';
				return $return;
			}

			// Check manager access
			if ($this->functions->check_acl($_SESSION['phpgw_session']['session_lid'], 'edit_users'))
			{
				////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				// Change user organization
				if( isset($diff['context']) )
				{
					if (strcasecmp($old_values['context'], $new_values['context']) != 0)
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
							$this->db_functions->write_log('modified user context', $dn . ': ' . $old_values['uid'] . '->' . $new_values['context']);
						}
					}
				}
			
				////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				// REPLACE some attributes
				if( isset($diff['givenname']) )
				{
					$ldap_mod_replace['givenname'] = $new_values['givenname'];
					$ldap_mod_replace['cn'] = $new_values['givenname'] . ' ' . $new_values['sn'];
					$this->db_functions->write_log("modified first name", "$dn: " . $old_values['givenname'] . "->" . $new_values['givenname']);
				}
				if( isset($diff['sn']) )
				{
					$ldap_mod_replace['sn'] = $new_values['sn'];
					$ldap_mod_replace['cn'] = $new_values['givenname'] . ' ' . $new_values['sn'];
					$this->db_functions->write_log("modified last name", "$dn: " . $old_values['sn'] . "->" . $new_values['sn']);
				}
				if( isset($diff['mail']) )
				{
					$ldap_mod_replace['mail'] = $new_values['mail'];
					$this->ldap_functions->replace_user2maillists($new_values['mail'], $old_values['mail']);
					$this->ldap_functions->replace_mail_from_institutional_account($new_values['mail'], $old_values['mail']);
					$this->db_functions->write_log("modified user email", "$dn: " . $old_values['mail'] . "->" . $new_values['mail']);
				}
				////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				// Passwd Expired - Com atributo
				if( ($old_values['passwd_expired'] != 0) && ($new_values['passwd_expired'] == '1') )
				{
					$ldap_mod_replace['phpgwlastpasswdchange'] = '0';
					$this->db_functions->write_log("Expired user password","$dn");
				}
			}
			
			if ( ($this->functions->check_acl($_SESSION['phpgw_session']['session_lid'], 'edit_users')) || 
			     ($this->functions->check_acl($_SESSION['phpgw_session']['session_lid'], 'change_users_password')) )
			{
				if( isset($diff['password1']) )
				{
					$ldap_mod_replace['userPassword'] = '{md5}' . base64_encode(pack("H*",md5($new_values['password1'])));
					
					// Suporte ao SAMBA
					if (($this->current_config['expressoAdmin_samba_support'] == 'true') && ($new_values['userSamba']) && ($new_values['use_attrs_samba'] == 'on'))
					{
						$ldap_mod_replace['sambaLMPassword'] = exec('/home/expressolivre/mkntpwd -L "'.$new_values['password1'] . '"');
						$ldap_mod_replace['sambaNTPassword'] = exec('/home/expressolivre/mkntpwd -N "'.$new_values['password1'] . '"');
					}
					
					// Gerenciar senhas RFC2617
					if ($this->current_config['expressoAdmin_userPasswordRFC2617'] == 'true')
					{
						$realm		= $this->current_config['expressoAdmin_realm_userPasswordRFC2617'];
						$uid		= $new_values['uid'];
						$password	= $new_values['password1'];
						$passUserRFC2617 = $realm . ':      ' . md5("$uid:$realm:$password");
						
						if ($old_values['userPasswordRFC2617'] != '')
							$ldap_mod_replace['userPasswordRFC2617'] = $passUserRFC2617;
						else
							$ldap_add['userPasswordRFC2617'] = $passUserRFC2617;
					}
					
					$this->db_functions->write_log("modified user password",$dn);
				}
			}

			if ( ($this->functions->check_acl($_SESSION['phpgw_session']['session_lid'], 'edit_users')) || 
			     ($this->functions->check_acl($_SESSION['phpgw_session']['session_lid'], 'edit_users_phonenumber')) )
			{
				if ( isset($diff['telephonenumber']) && (isset($old_values['telephonenumber']) && $old_values['telephonenumber'] != ''))
				{
				        $ldap_mod_replace['telephonenumber'] = $new_values['telephonenumber'];
				        $this->db_functions->write_log('modified user telephonenumber', $dn . ': ' . $old_values['telephonenumber'] . '->' . $new_values['telephonenumber']);
	                                $ldap_mod_replace['telephonenumber'] = $new_values['telephonenumber']; 
	                                $this->db_functions->write_log('modified user telephonenumber', $dn . ': ' . $old_values['telephonenumber'] . '->' . $new_values['telephonenumber']); 
				}
				else if ((isset($old_values['telephonenumber']) && $old_values['telephonenumber'] != '') && (isset($new_values['telephonenumber']) && $new_values['telephonenumber'] == ''))
				{
				        $ldap_remove['telephonenumber'] = array();
				        $this->db_functions->write_log("removed user phone",$dn);
				}
				else if ((isset($old_values['telephonenumber']) && $old_values['telephonenumber'] == '') && (isset($new_values['telephonenumber']) && $new_values['telephonenumber'] != ''))
				{
				        $ldap_add['telephonenumber'] = $new_values['telephonenumber'];
				        $this->db_functions->write_log("added user phone",$dn);
				}
			}
			
			// REPLACE, ADD & REMOVE COPORATIVEs ATRIBUTES
			// Verifica o acesso do gerente aos atributos corporativos
			
			if ( ($this->functions->check_acl($_SESSION['phpgw_session']['session_lid'], 'edit_users')) || 
			     ($this->functions->check_acl($_SESSION['phpgw_session']['session_lid'], 'manipulate_corporative_information')) )
			{
				foreach ($new_values as $atribute=>$value)
				{
					$pos = strstr($atribute, 'corporative_information_');
					if ($pos !== false)
					{
						$ldap_atribute = str_replace("corporative_information_", "", $atribute);
						// REPLACE CORPORATIVE ATTRIBUTES
						if ( isset($diff[$atribute]) && ($old_values[$atribute] != '') )
						{
							$ldap_atribute = str_replace("corporative_information_", "", $atribute);
							$ldap_mod_replace[$ldap_atribute] = utf8_encode($new_values[$atribute]);
							$this->db_functions->write_log('modified user attribute', $dn . ': ' . $ldap_atribute . ': ' . $old_values[$atribute] . '->' . $new_values[$atribute]);
						}
						//ADD CORPORATIVE ATTRIBUTES
						elseif (($old_values[$atribute] == '') && ($new_values[$atribute] != ''))
						{
							$ldap_add[$ldap_atribute] = utf8_encode($new_values[$atribute]);
							$this->db_functions->write_log('added user attribute', $dn . ': ' . $ldap_atribute . ': ' . $old_values[$atribute] . '->' . $new_values[$atribute]);
						}
						//REMOVE CORPORATIVE ATTRIBUTES
						elseif (($old_values[$atribute] != '') && ($new_values[$atribute] == ''))
						{
							$ldap_remove[$ldap_atribute] = array();
							$this->db_functions->write_log('removed user attribute', $dn . ': ' . $ldap_atribute . ': ' . $old_values[$atribute] . '->' . $new_values[$atribute]);	
						}
					}
				}
			}
			
			//Suporte ao SAMBA
			if ( ($this->functions->check_acl($_SESSION['phpgw_session']['session_lid'], 'edit_users')) || 
			     ($this->functions->check_acl($_SESSION['phpgw_session']['session_lid'], 'edit_sambausers_attributes')) )
			{
				
				if( isset($diff['gidnumber']) )
				{
					$ldap_mod_replace['gidnumber'] = $new_values['gidnumber'];
					$this->db_functions->write_log('modified user primary group', $dn . ': ' . $old_values['gidnumber'] . '->' . $new_values['gidnumber']);
				}
				
				if (($this->current_config['expressoAdmin_samba_support'] == 'true') && ($new_values['userSamba']) && ($new_values['use_attrs_samba'] == 'on'))
				{
					if ($diff['gidnumber'])
					{
						$ldap_mod_replace['sambaPrimaryGroupSID']	= $this->current_config['expressoAdmin_sambaSID'] . '-' . ((2 * $new_values['gidnumber'])+1001);
						$this->db_functions->write_log('modified user sambaPrimaryGroupSID', $dn);
					}
					
					if ($diff['sambaacctflags'])
					{
						$ldap_mod_replace['sambaacctflags'] = $new_values['sambaacctflags'];
						$this->db_functions->write_log("modified user sambaacctflags",$dn);
					}
					if ($diff['sambalogonscript'])
					{
						$ldap_mod_replace['sambalogonscript'] = $new_values['sambalogonscript'];
						$this->db_functions->write_log("modified user sambalogonscript",$dn);
					}
					if ($diff['sambahomedirectory'])
					{
						$ldap_mod_replace['homedirectory'] = $new_values['sambahomedirectory'];
						$this->db_functions->write_log("modified user homedirectory",$dn);
					}
					if ($diff['sambadomain'])
					{
						$ldap_mod_replace['sambaSID']				= $diff['sambadomain'] . '-' . ((2 * $old_values['uidnumber'])+1000);
						$ldap_mod_replace['sambaPrimaryGroupSID']	= $diff['sambadomain'] . '-' . ((2 * $old_values['gidnumber'])+1001);
						$this->db_functions->write_log('modified user samba domain', $dn . ': ' . $old_values['sambadomain'] . '->' . $new_values['sambadomain']);
					}
				}
			}
			
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// ADD or REMOVE some attributes
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// PHOTO
			if ($this->functions->check_acl($_SESSION['phpgw_session']['session_lid'], 'edit_users_picture'))
			{
				if( isset($new_values['delete_photo']) )
				{
					$this->ldap_functions->ldap_remove_photo($dn);
					$this->db_functions->write_log("removed user photo",$dn);
				}
				elseif ($_FILES['photo']['name'] != '')
				{
					
					if ($_FILES['photo']['size'] > 10000)
					{
						$return['status'] = false;
						$return['msg'] .= $this->functions->lang('User photo could not be save because is bigger the 10 kb') . '.';
					}
					else
					{
						if ($new_values['photo_exist'])
						{
							$photo_exist = true;
							$this->db_functions->write_log("modified user photo",$dn);
						}
						else
						{
							$photo_exist = false;
							$this->db_functions->write_log("added user photo",$dn);
						}				
						$this->ldap_functions->ldap_save_photo($dn, $_FILES['photo']['tmp_name'], $new_values['photo_exist'], $photo_exist);
					}
				}	
			}
			
			// Verifica o acesso ára adicionar ou remover tais atributos
			if( $this->functions->check_acl($_SESSION['phpgw_session']['session_lid'], 'edit_users') )
			{
				// Passwd Expired - Sem atributo
				if((isset($old_values['passwd_expired']) && $old_values['passwd_expired'] == '') && (isset($new_values['passwd_expired']) && $new_values['passwd_expired'] == '1'))
				{
					$ldap_add['phpgwlastpasswdchange'] = '0';
					$this->db_functions->write_log("expired user password",$dn);
				}
				if (($old_values['passwd_expired'] == '0') && ($new_values['passwd_expired'] == ''))
				{
					$ldap_remove['phpgwlastpasswdchange'] = array();
					$this->db_functions->write_log("removed expiry from user password",$dn);
				}
				////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				// PREF_CHANGEPASSWORD
				if ((isset($old_values['changepassword']) && $old_values['changepassword'] == '') && (isset($new_values['changepassword']) && $new_values['changepassword'] != ''))
				{
					$this->db_functions->add_pref_changepassword($new_values['uidnumber']);
					$this->db_functions->write_log("turn on changepassword",$dn);
				}
				if ((isset($old_values['changepassword']) && $old_values['changepassword'] != '') && (isset($new_values['changepassword']) && $new_values['changepassword'] == ''))
				{
					$this->db_functions->remove_pref_changepassword($new_values['uidnumber']);
					$this->db_functions->write_log("turn of changepassword",$dn);
				}
				////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				// ACCOUNT STATUS
				if (($old_values['phpgwaccountstatus'] == '') && ($new_values['phpgwaccountstatus'] != ''))
				{
					$ldap_add['phpgwaccountstatus'] = 'A';
					$this->db_functions->write_log("turn on user account",$dn);
				}
				if (($old_values['phpgwaccountstatus'] != '') && ($new_values['phpgwaccountstatus'] == ''))
				{
					$ldap_remove['phpgwaccountstatus'] = array();
					$this->db_functions->write_log("turn off user account",$dn);
				}

				if (isset($new_values['phpgwaccountexpired']) && $new_values['phpgwaccountexpired'] == '1') /////////////////////////
				{
					$this->db_functions->write_log("Reactivated blocked user by downtime",'',$dn,'','');
					$this->db_functions->reactivate_inactive_user($old_values['uidnumber']);
				}
				////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				// ACCOUNT VISIBLE
				if ((isset($old_values['phpgwaccountvisible']) && $old_values['phpgwaccountvisible'] == '') && (isset($new_values['phpgwaccountvisible']) && $new_values['phpgwaccountvisible'] != ''))
				{
					$ldap_add['phpgwaccountvisible'] = '-1';
					$this->db_functions->write_log("turn on phpgwaccountvisible",$dn);
				}
				if ((isset($old_values['phpgwaccountvisible']) && $old_values['phpgwaccountvisible'] != '') && (isset($new_values['phpgwaccountvisible']) && $new_values['phpgwaccountvisible'] == ''))
				{
					$ldap_remove['phpgwaccountvisible'] = array();
					$this->db_functions->write_log("turn off phpgwaccountvisible",$dn);
				}
				////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				// Mail Account STATUS
				if (($old_values['accountstatus'] == '') && ($new_values['accountstatus'] != ''))
				{
					$ldap_add['accountstatus'] = 'active';
					$this->db_functions->write_log("turn on user account email",$dn);
				}
				if (($old_values['accountstatus'] != '') && ($new_values['accountstatus'] == ''))
				{
					$ldap_remove['accountstatus'] = array();
					$this->db_functions->write_log("turn off user account email",$dn);
				}
				////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				// MAILALTERNATEADDRESS
				if (!$new_values['mailalternateaddress'])
					$new_values['mailalternateaddress'] = array();
				if (!$old_values['mailalternateaddress'])
					$old_values['mailalternateaddress'] = array();
				$add_mailalternateaddress = array_diff($new_values['mailalternateaddress'], $old_values['mailalternateaddress']);
				$remove_mailalternateaddress = array_diff($old_values['mailalternateaddress'], $new_values['mailalternateaddress']);
				foreach ($add_mailalternateaddress as $index=>$mailalternateaddress)
				{
					if ($mailalternateaddress != '')
					{
						$ldap_add['mailalternateaddress'][] = $mailalternateaddress;
						$this->db_functions->write_log("added mailalternateaddress","$dn: $mailalternateaddress");
					}
				}
				foreach ($remove_mailalternateaddress as $index=>$mailalternateaddress)
				{
					if ($mailalternateaddress != '')
					{
						if ($index !== 'count')
						{
							$ldap_remove['mailalternateaddress'][] = $mailalternateaddress;
							$this->db_functions->write_log("removed mailalternateaddress","$dn: $mailalternateaddress");
						}
					}
				}
				
				////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				// MAILFORWARDINGADDRESS
				if (!$new_values['mailforwardingaddress'])
					$new_values['mailforwardingaddress'] = array();
				if (!$old_values['mailforwardingaddress'])
					$old_values['mailforwardingaddress'] = array();
				$add_mailforwardingaddress = array_diff($new_values['mailforwardingaddress'], $old_values['mailforwardingaddress']);
				$remove_mailforwardingaddress = array_diff($old_values['mailforwardingaddress'], $new_values['mailforwardingaddress']);
				foreach ($add_mailforwardingaddress as $index=>$mailforwardingaddress)
				{
					if ($mailforwardingaddress != '')
					{
						$ldap_add['mailforwardingaddress'][] = $mailforwardingaddress;
						$this->db_functions->write_log("added mailforwardingaddress","$dn: $mailforwardingaddress");
					}
				}
				foreach ($remove_mailforwardingaddress as $index=>$mailforwardingaddress)
				{
					if ($mailforwardingaddress != '')
					{
						if ($index !== 'count')
						{
							$ldap_remove['mailforwardingaddress'][] = $mailforwardingaddress;
							$this->db_functions->write_log("removed mailforwardingaddress","$dn: $mailforwardingaddress");
						}
					}
				}
				
				////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				// Delivery Mode
				if ((isset($old_values['deliverymode']) && $old_values['deliverymode'] == '') && (isset($new_values['deliverymode']) && $new_values['deliverymode'] != ''))
				{
					$ldap_add['deliverymode'] = 'forwardOnly';
					$this->db_functions->write_log("added forwardOnly", $dn);
				}
				if ((isset($old_values['deliverymode']) && $old_values['deliverymode'] != '') && (isset($new_values['deliverymode']) && $new_values['deliverymode'] == ''))
				{
					$ldap_remove['deliverymode'] = array();
					$this->db_functions->write_log("removed forwardOnly", $dn);
				}
			}
			
			if ( ($this->functions->check_acl($_SESSION['phpgw_session']['session_lid'], 'edit_users')) || 
			     ($this->functions->check_acl($_SESSION['phpgw_session']['session_lid'], 'change_users_quote')) )
			{
				////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				// MAILQUOTA
				if ( ($new_values['mailquota'] != $old_values['mailquota']) && (is_numeric($new_values['mailquota'])) )
				{
					$result_change_user_quota = $this->imap_functions->change_user_quota($new_values['uid'], $new_values['mailquota']);
					
					if ($result_change_user_quota['status'])
					{
						$this->db_functions->write_log("modified user email quota" , $dn . ':' . $old_values['mailquota'] . '->' . $new_values['mailquota']);
					}
					else
					{
						$return['status'] = false;
						$return['msg'] .= $result_change_user_quota['msg'];
					}
				}
			}

			if ( ($this->functions->check_acl($_SESSION['phpgw_session']['session_lid'], 'edit_users')) || 
			     ($this->functions->check_acl($_SESSION['phpgw_session']['session_lid'], 'edit_sambausers_attributes')) )
			{
				//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				// REMOVE ATTRS OF SAMBA
				if (($this->current_config['expressoAdmin_samba_support'] == 'true') && ($new_values['userSamba']) && ($new_values['use_attrs_samba'] != 'on'))
				{
					$ldap_remove['objectclass'] 			= 'sambaSamAccount';	
					$ldap_remove['loginShell']				= array();
					$ldap_remove['sambaSID']				= array();
					$ldap_remove['sambaPrimaryGroupSID']	= array();
					$ldap_remove['sambaAcctFlags']			= array();
					$ldap_remove['sambaLogonScript']		= array();
					$ldap_remove['sambaLMPassword']			= array();
					$ldap_remove['sambaNTPassword']			= array();
					$ldap_remove['sambaPasswordHistory']	= array();
					$ldap_remove['sambaPwdCanChange']		= array();
					$ldap_remove['sambaPwdLastSet']			= array();
					$ldap_remove['sambaPwdMustChange']		= array();
					$this->db_functions->write_log("removed user samba attributes", $dn);
				}
				//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				// ADD ATTRS OF SAMBA
				if (($this->current_config['expressoAdmin_samba_support'] == 'true') && (!$new_values['userSamba']) && ($new_values['use_attrs_samba'] == 'on'))
				{
					if (!is_file('/home/expressolivre/mkntpwd'))
					{
						$return['status'] = false;
						$return['msg'] .= $this->functions->lang("The file /home/expressolivre/mkntpwd does not exist") . ".\n";
						$return['msg'] .= $this->functions->lang("It is necessery to create samba passwords") . ".\n";
						$return['msg'] .= $this->functions->lang("Inform your system administrator about this") . ".\n";
					}
					else
					{
						$ldap_add['objectClass'][] 			= 'sambaSamAccount';
						$ldap_mod_replace['loginShell']		= '/bin/bash';
						$ldap_add['sambaSID']				= $new_values['sambadomain'] . '-' . ((2 * $new_values['uidnumber'])+1000);
						$ldap_add['sambaPrimaryGroupSID']	= $new_values['sambadomain'] . '-' . ((2 * $new_values['gidnumber'])+1001);
						$ldap_add['sambaAcctFlags']			= $new_values['sambaacctflags'];
						$ldap_add['sambaLogonScript']		= $new_values['sambalogonscript'];
						$ldap_mod_replace['homeDirectory']	= $new_values['sambahomedirectory'];
						$ldap_add['sambaLMPassword']		= exec('/home/expressolivre/mkntpwd -L '.'senha');
						$ldap_add['sambaNTPassword']		= exec('/home/expressolivre/mkntpwd -N '.'senha');
						$ldap_add['sambaPasswordHistory']	= '0000000000000000000000000000000000000000000000000000000000000000';
						$ldap_add['sambaPwdCanChange']		= strtotime("now");
						$ldap_add['sambaPwdLastSet']		= strtotime("now");
						$ldap_add['sambaPwdMustChange']	= '2147483647';
						$this->db_functions->write_log("added user samba attribute", $dn);
					}
				}
			}
			
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// GROUPS
			if ($this->functions->check_acl($_SESSION['phpgw_session']['session_lid'], 'edit_groups')) 
			{
				// If the manager does not have the suficient access, the new_values.uid is empty. 
				if (empty($new_values['uid']))
					$user_uid = $old_values['uid'];
				else
					$user_uid = $new_values['uid'];
				
				if (!$new_values['groups'])
					$new_values['groups'] = array();
				if (!$old_values['groups'])
					$old_values['groups'] = array();
			
				$add_groups 	= array_diff($new_values['groups'], $old_values['groups']);
				$remove_groups	= array_diff($old_values['groups'], $new_values['groups']);

				if( count($add_groups) > 0 )
				{
					foreach($add_groups as $gidnumber)
					{
						$this->db_functions->add_user2group($gidnumber, $new_values['uidnumber']);
						$this->ldap_functions->add_user2group($gidnumber, $user_uid);
						$this->db_functions->write_log("included user to group", "uid:$user_uid -> gid:$gidnumber");
					}
				}
				
				if (count($remove_groups)>0)
				{
					foreach($remove_groups as $gidnumber)
					{
						foreach($old_values['groups_info'] as $group)
						{
							if (($group['gidnumber'] == $gidnumber) && ($group['group_disabled'] == 'false'))
							{
								$this->db_functions->remove_user2group($gidnumber, $new_values['uidnumber']);
								$this->ldap_functions->remove_user2group($gidnumber, $user_uid);
								$this->db_functions->write_log("removed user from group", "$dn: $gidnumber");
							}
						}
					}
				}
			}
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// LDAP_MOD_REPLACE
			if( isset($ldap_mod_replace) && count($ldap_mod_replace) )
			{
				$result = $this->ldap_functions->replace_user_attributes($dn, $ldap_mod_replace);
				if (!$result['status'])
				{
					$return['status'] = false;
					$return['msg'] .= $result['msg'];
				}
			}
			
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// LDAP_MOD_ADD
			if( isset($ldap_add) && count($ldap_add))
			{
				$result = $this->ldap_functions->add_user_attributes($dn, $ldap_add);
				if (!$result['status'])
				{
					$return['status'] = false;
					$return['msg'] .= $result['msg'];
				}
			}
			
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// LDAP_MOD_REMOVE			
			if( isset($ldap_remove) && count($ldap_remove) )
			{
				$result = $this->ldap_functions->remove_user_attributes($dn, $ldap_remove);
				if (!$result['status'])
				{
					$return['status'] = false;
					$return['msg'] .= $result['msg'];
				}
			}
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			if($this->functions->check_acl($_SESSION['phpgw_session']['session_lid'], 'edit_users')) 
			{
				////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				// MAILLISTS
				if( !isset($new_values['maillists']) )
				{
					$new_values['maillists'] = array();
				}
				
				if( !isset($old_values['maillists']) )
				{
					$old_values['maillists'] = array();
				}

				$add_maillists 		= array_diff($new_values['maillists'], $old_values['maillists']);
				$remove_maillists 	= array_diff($old_values['maillists'], $new_values['maillists']);
				
				if (count($add_maillists)>0)
				{
					foreach($add_maillists as $uid)
					{
						$this->ldap_functions->add_user2maillist($uid, $new_values['mail']);
						$this->db_functions->write_log("included user to maillist","$uid: $dn");
					}
				}

				if (count($remove_maillists)>0)
				{
					foreach($remove_maillists as $uid)
					{
						$this->ldap_functions->remove_user2maillist($uid, $new_values['mail']);
						$this->db_functions->write_log("removed user from maillist","$dn: $uid");
					}
				}
			
				//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				// APPS
				$new_values2 = array();
				$old_values2 = array();
				if( $new_values['apps'] && count($new_values['apps']) > 0 )
				{
					foreach ($new_values['apps'] as $app=>$tmp)
					{
						$new_values2[] = $app;
					}
				}
				if( $old_values['apps'] && count($old_values['apps']) > 0 )
				{
					foreach ($old_values['apps'] as $app=>$tmp)
					{
						$old_values2[] = $app;
					}
				}

				$add_apps    = array_flip(array_diff($new_values2, $old_values2));
				$remove_apps = array_flip(array_diff($old_values2, $new_values2));

				if( count($add_apps ) > 0 )
				{
					$this->db_functions->add_id2apps($new_values['uidnumber'], $add_apps);

					foreach ($add_apps as $app => $index)
						$this->db_functions->write_log("added application to user","$dn: $app");
				}
				
				if( count($remove_apps) > 0 )
				{
					//Verifica se o gerente tem acesso a aplicação antes de remove-la do usuario.
					$manager_apps = $this->db_functions->get_apps($_SESSION['phpgw_session']['session_lid']);
					
					foreach ($remove_apps as $app => $app_index)
					{
						if($manager_apps[$app] == 'run')
						{
							$remove_apps2[$app] = $app_index;
						}
					}
					
					$this->db_functions->remove_id2apps($new_values['uidnumber'], $remove_apps2);
					
					foreach ($remove_apps2 as $app => $access)
					{
						$this->db_functions->write_log("removed application to user","$dn: $app");
					}
				}
				//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			}			
			return $return;
		}		
		
		function get_user_info($uidnumber)
		{
			if (!$user_info_ldap = $this->ldap_functions->get_user_info($uidnumber))
				return false;
			$user_info_db1 = $this->db_functions->get_user_info($uidnumber);
			$user_info_db2 = $this->ldap_functions->gidnumbers2cn(isset($user_info_db1['groups'])?$user_info_db1['groups']:'');
			$user_info_imap = $this->imap_functions->get_user_info($user_info_ldap['uid']);
			$user_info = array_merge($user_info_ldap, $user_info_db1, $user_info_db2, $user_info_imap);
			return $user_info;
		}
		
		function set_user_default_password($params)
		{
			$return['status'] = 1;
			$uid = $params['uid'];
			$defaultUserPassword = '{md5}'.base64_encode(pack("H*",md5($this->current_config['expressoAdmin_defaultUserPassword'])));
			
			if (!$this->db_functions->default_user_password_is_set($uid))
			{
				$userPassword = $this->ldap_functions->set_user_password($uid, $defaultUserPassword);
				$this->db_functions->set_user_password($uid, $userPassword);
				$this->db_functions->write_log("inserted default password",$uid);
			}
			else
			{
				$return['status'] = 0;
				$return['msg'] = $this->functions->lang('default password already registered') . '!';
			}
			
			return $return;
		}

		function return_user_password($params)
		{
			$return['status'] = 1;
			$uid = $params['uid'];
			
			if ($this->db_functions->default_user_password_is_set($uid))
			{
				$userPassword = $this->db_functions->get_user_password($uid);
				$this->ldap_functions->set_user_password($uid, $userPassword);
			}
			else
			{
				$return['status'] = 0;
				$return['msg'] = $this->functions->lang('default password not registered') . '!';
			}
			
			$this->db_functions->write_log("returned user password",$uid);
			
			return $return;
		}
		
		function delete($params)
		{
			$return['msg'] = '';
			$return['status'] = true;
			$this->db_functions->write_log('delete user: start', $params['uid']);
			
			// Verifica o acesso do gerente
			if ($this->functions->check_acl($_SESSION['phpgw_session']['session_lid'], 'delete_users'))
			{
				$uidnumber = $params['uidnumber'];
				if (!$user_info = $this->get_user_info($uidnumber))
				{
					$this->db_functions->write_log('delete user: error getting users info', $user_info['uid']);
					$return['status'] = false;
					$return['msg'] = $this->functions->lang('error getting users info');
					return $return; 
				}

				//LDAP
				$result_ldap = $this->ldap_functions->delete_user($user_info);
				if (!$result_ldap['status'])
				{
					$return['status'] = false;
					$return['msg'] = 'user.delete(ldap): ' . $result_ldap['msg'];
					return $return;
				}
				else
				{

					$this->db_functions->write_log("deleted users data from ldap", $user_info['uid']);
					
					//DB
					$result_db = $this->db_functions->delete_user($user_info);
					if (!$result_db['status'])
					{
						$return['status'] = false;
						$return['msg'] .= 'user.delete(db): ' . $result_db['msg'];
					}
					else
					{
						$this->db_functions->write_log("deleted users data from DB", $user_info['uid']);
					}
					
					//IMAP
					$result_imap = $this->imap_functions->delete_mailbox($user_info['uid']);
					if (!$result_imap['status'])
					{
						$return['status'] = false;
						$return['msg'] .= $result_imap['msg'];
					}
					else
					{
						$this->db_functions->write_log("deleted users data from IMAP", $user_info['uid']);
					}
					
					//GERENTE
					$result_db_manager = $this->db_functions->delete_manager($user_info['uid'], $params['uidnumber']);
					if (!$result_db_manager['status'])
					{
						$return['status'] = false;
						$return['msg'] .= $result_imap['msg'];
					}
					else
					{
						$this->db_functions->write_log("deleted manager data from BD", $user_info['uid']);
					}
				}
			}
			else
			{
				$this->db_functions->write_log('delete user: manager does not have access', $params['uidnumber']);
			}
			
			$this->db_functions->write_log('delete user: end', $user_info['uid']);
			return $return;
		}


		function rename($params)
		{
			$return['status'] = true;
			
			// Verifica acesso do gerente (OU) ao tentar renomear um usuário.			
			if ( ! $this->ldap_functions->check_access_to_renamed($params['uid']) )
			{
				$return['status'] = false;
				$return['msg'] .= $this->functions->lang('You do not have access to delete user') . '.';
				return $return;
			}

			// Check if the new_uid is in use.			
			if ( ! $this->ldap_functions->check_rename_new_uid($params['new_uid']) )
			{
				$return['status'] = false;
				$return['msg'] = $this->functions->lang('New login already in use') . '.';
				return $return;
			}

			// Verifica o acesso do gerente
			if ($this->functions->check_acl($_SESSION['phpgw_session']['session_lid'], 'rename_users'))
			{
				$uid 		= $params['uid'];
				$new_uid	= $params['new_uid'];
				$defaultUserPassword = '{md5}'.base64_encode(pack("H*",md5($this->current_config['expressoAdmin_defaultUserPassword'])));
				$defaultUserPassword_plain = $this->current_config['expressoAdmin_defaultUserPassword'];

				$emailadmin_profiles = $this->db_functions->get_sieve_info();
				$sieve_enable = $emailadmin_profiles[0]['imapenablesieve'];
				$sieve_server = $emailadmin_profiles[0]['imapsieveserver'];
				$sieve_port   = $emailadmin_profiles[0]['imapsieveport'];

				$imap_admin		= $_SESSION['phpgw_info']['expresso']['email_server']['imapAdminUsername'];
				$imap_passwd	= $_SESSION['phpgw_info']['expresso']['email_server']['imapAdminPW'];
				$imap_server	= $_SESSION['phpgw_info']['expresso']['email_server']['imapServer'];
				$imap_port		= $_SESSION['phpgw_info']['expresso']['email_server']['imapPort'];
				$imapDelimiter	= $_SESSION['phpgw_info']['expresso']['email_server']['imapDelimiter'];
			
				//Verifica se está sendo usuado cyrus 2.2 ou superior
				$sk = fsockopen ($imap_server,$imap_port);
				$server_resp = fread($sk, 100);
        		$tmp = preg_split('/v2./', $server_resp);
	        	$cyrus_version = '2' . $tmp[1][0];
			
			    if ( $cyrus_version < intVal('2.2') )
    	    	{
					$return['status'] = false;
					$return['msg'] = "The rename user is only permitted with cyrus 2.2 or higher,";
					$return['msg'] .= "\nand with the option 'allowusermoves: yes' set in imapd.conf.";


					return $return;
	        	}

				// Renomeia UID no openldap
				$result = $this->ldap_functions->rename_uid($uid, $new_uid);
				if (!$result['status'])
				{
					$return['status'] = false;
					$return['msg'] = $this->functions->lang("Error rename user in LDAP") . '.';
					return $return;
				}
				
        		//Renomeia mailbox
   	    		$imap_rename_result = $this->imap_functions->rename_mailbox($uid, $new_uid);
				if (!$imap_rename_result['status'])
				{
					// Back user uid.
					$result = $this->ldap_functions->rename_uid($new_uid, $uid);
					
					$return['status'] = false;
					$return['msg']  = $this->functions->lang("Error renaming user mailboxes") . ".\n";
					$return['msg'] .= $imap_rename_result['msg'];
					return $return;
				}
        		

       			// Renomeia sieve script
       			include_once('sieve-php.lib.php');
       			//function sieve($host,         $port,       $user,    $pass,        $auth="",     $auth_types)
       			$sieve=new sieve($sieve_server, $sieve_port, $new_uid, $imap_passwd, $imap_admin, 'PLAIN');
        			
				if ($sieve->sieve_login())
				{
					$sieve->sieve_listscripts();
					$myactivescript=$sieve->response["ACTIVE"];
					$sieve->sieve_getscript($myactivescript);

					$script = '';
					if (!empty($sieve->response))
					{
						foreach($sieve->response as $result)
						{
							$script .= $result;
						}
					}
					
					if (!empty($script))
					{
	       				$scriptname = $new_uid;
						if($sieve->sieve_sendscript($new_uid,$script))
						{
							if ($sieve->sieve_setactivescript($new_uid))
							{
								if (!$sieve->sieve_deletescript($myactivescript))
								{
									$return['msg'] .= $result['msg'] . $this->functions->lang("Error renaming sieve script") . ".\\n";
									$return['msg'] .= $result['msg'] . $this->functions->lang("Problem deleting old script") . '.';
								}
							}
							else
							{
								$return['msg'] .= $result['msg'] . $this->functions->lang("Error renaming sieve script") . ".\\n";
								$return['msg'] .= $result['msg'] . $this->functions->lang("Problem activating sieve script") . '.';
							}
						}
						else 
						{
							$return['msg'] .= $result['msg'] . $this->functions->lang("Error renaming sieve script") . ".\\n";
							$return['msg'] .= $result['msg'] . $this->functions->lang("Problem saving sieve script") . '.';
						}
					}
					$sieve->sieve_logout();
				}
				else
				{
						$return['status'] = false;
						$return['msg'] .= $result['msg'] . $this->functions->lang("Error renaming sieve script") . ".\\n";
						$return['msg'] .= $result['msg'] . $this->functions->lang("Can not login sieve") . '.';
				}

				$this->db_functions->write_log("renamed user", "$uid -> $new_uid");

				$return['exec_return'] = "";

	        	return $return;
			}
		}
		
		function write_log_from_ajax($params)
		{
			$this->db_functions->write_log($params['_action'],'',$params['userinfo'],'','');
			return true;
		}
	}
?>
