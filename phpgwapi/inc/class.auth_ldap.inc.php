<?php
	/**************************************************************************\
	* eGroupWare API - Auth from LDAP                                          *
	* This file written by Lars Kneschke <lkneschke@linux-at-work.de>          *
	* and Joseph Engo <jengo@phpgroupware.org>                                 *
	* Authentication based on LDAP Server                                      *
	* Copyright (C) 2000, 2001 Joseph Engo                                     *
	* Copyright (C) 2002, 2003 Lars Kneschke                                   *
	* -------------------------------------------------------------------------*
	* This library is part of the eGroupWare API                               *
	* http://www.egroupware.org/api                                            * 
	* ------------------------------------------------------------------------ *
	* This library is free software; you can redistribute it and/or modify it  *
	* under the terms of the GNU Lesser General Public License as published by *
	* the Free Software Foundation; either version 2.1 of the License,         *
	* or any later version.                                                    *
	* This library is distributed in the hope that it will be useful, but      *
	* WITHOUT ANY WARRANTY; without even the implied warranty of               *
	* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     *
	* See the GNU Lesser General Public License for more details.              *
	* You should have received a copy of the GNU Lesser General Public License *
	* along with this library; if not, write to the Free Software Foundation,  *
	* Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA            *
	\**************************************************************************/

  
	class auth_
	{
		var $previous_login = -1;

		function authenticate($username, $passwd)
		{
			if (preg_match('/[()|&=*,<>!~]/',$username))
			{
				return False;
			}

			if(!$ldap = @ldap_connect($GLOBALS['phpgw_info']['server']['ldap_host']))
			{
				$GLOBALS['phpgw']->log->message('F-Abort, Failed connecting to LDAP server for authenication, execution stopped');
				$GLOBALS['phpgw']->log->commit();
				return False;
			}

			if($GLOBALS['phpgw_info']['server']['ldap_version3'])
			{
				ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
			}

			ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

			/* Login with the LDAP Admin. User to find the User DN.  */
			if(!@ldap_bind($ldap, $GLOBALS['phpgw_info']['server']['ldap_root_dn'], $GLOBALS['phpgw_info']['server']['ldap_root_pw']))
			{
				return False;
			}
			/* find the dn for this uid, the uid is not always in the dn */
			$attributes	= array('uid','dn','givenName','sn','mail','uidNumber','gidNumber');
			
			$filter = $GLOBALS['phpgw_info']['server']['ldap_search_filter'] ? $GLOBALS['phpgw_info']['server']['ldap_search_filter'] : '(uid=%user)';
			$filter = str_replace(array('%user','%domain'),array($username,$GLOBALS['phpgw_info']['user']['domain']),$filter);

			if ($GLOBALS['phpgw_info']['server']['account_repository'] == 'ldap')
			{
				$filter = "(&$filter(phpgwaccountstatus=A))";
			}

			$sri = ldap_search($ldap, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $attributes);
			$allValues = ldap_get_entries($ldap, $sri);

			if ($allValues['count'] > 0)
			{
				if($GLOBALS['phpgw_info']['server']['case_sensitive_username'] == true)
				{
					if($allValues[0]['uid'][0] != $username)
					{
						return false;
					}
				}
				/* we only care about the first dn */
				$userDN = $allValues[0]['dn'];
				/*
				generate a bogus password to pass if the user doesn't give us one 
				this gets around systems that are anonymous search enabled
				*/
				if (empty($passwd))
				{
					$passwd = crypt(microtime());
				}
				/* try to bind as the user with user suplied password */
				if (@ldap_bind($ldap, $userDN, $passwd))
				{
					if ($GLOBALS['phpgw_info']['server']['account_repository'] != 'ldap')
					{
						$account = CreateObject('phpgwapi.accounts',$username,'u');
						if (!$account->account_id && $GLOBALS['phpgw_info']['server']['auto_create_acct'])
						{
							// create a global array with all availible info about that account
							$GLOBALS['auto_create_acct'] = array();
							foreach(array(
								'givenname' => 'firstname',
								'sn'        => 'lastname',
								'uidnumber' => 'id',
								'mail'      => 'email',
								'gidnumber' => 'primary_group',
							) as $ldap_name => $acct_name)
							{
								$GLOBALS['auto_create_acct'][$acct_name] =
									$GLOBALS['phpgw']->translation->convert($allValues[0][$ldap_name][0],'utf-8');
							}
							return True;
						}
						$data = $account->read_repository();
						return $data['status'] == 'A';
					}
					return True;
				}
			}
			/* dn not found or password wrong */
			return False;
		}

		function change_password($old_passwd, $new_passwd, $_account_id='') 
		{
			if ('' == $_account_id)
			{
				$username = $GLOBALS['phpgw_info']['user']['account_lid'];
			}
			else
			{
				$username = $GLOBALS['phpgw']->accounts->id2name($_account_id);
			}
			$filter = $GLOBALS['phpgw_info']['server']['ldap_search_filter'] ? $GLOBALS['phpgw_info']['server']['ldap_search_filter'] : '(uid=%user)';
			$filter = str_replace(array('%user','%domain'),array($username,$GLOBALS['phpgw_info']['user']['domain']),$filter);

			// LDAP Replication mode. 
			if ( (!empty($GLOBALS['phpgw_info']['server']['ldap_master_host'])) &&
				 (!empty($GLOBALS['phpgw_info']['server']['ldap_master_root_dn'])) &&
			 	 (!empty($GLOBALS['phpgw_info']['server']['ldap_master_root_pw'])) )
			{
				$ds = $GLOBALS['phpgw']->common->ldapConnect(
											   $GLOBALS['phpgw_info']['server']['ldap_master_host'],
											   $GLOBALS['phpgw_info']['server']['ldap_master_root_dn'],
											   $GLOBALS['phpgw_info']['server']['ldap_master_root_pw']
											   );
			}
			else
			{
				$ds = $GLOBALS['phpgw']->common->ldapConnect();
			}

			$sri = ldap_search($ds, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter);
			$allValues = ldap_get_entries($ds, $sri);
			$entry['userpassword'] = $this->encrypt_password($new_passwd);
			$entry['phpgwlastpasswdchange'] = time();
			
			/* SAMBA Begin's*/
			foreach ($allValues[0]['objectclass'] as $objectclass)
			{
				if ($objectclass == 'sambaSamAccount')
				{
					$entry['sambaLMPassword'] = exec( "/home/expressolivre/mkntpwd -L '{$new_passwd}'" );
					$entry['sambaNTPassword'] = exec( "/home/expressolivre/mkntpwd -N '{$new_passwd}'" );
				}
			}
			/* SAMBA End's*/
			
			$dn = $allValues[0]['dn'];
			
			/* userPasswordRFC2617 Begin's*/
			$c = CreateObject('phpgwapi.config','expressoAdmin1_2');
			$c->read_repository();
			$current_config = $c->config_data;
			if ($current_config['expressoAdmin_userPasswordRFC2617'] == 'true')
			{
				$realm		= $current_config['expressoAdmin_realm_userPasswordRFC2617'];
				$uid		= $allValues[0]['uid'][0];
				$password	= $new_passwd;
				$passUserRFC2617 = $realm . ':      ' . md5("$uid:$realm:$password");

				if ($allValues[0]['userpasswordrfc2617'][0] != '')
					$entry['userPasswordRFC2617'] = $passUserRFC2617;
				else
				{
					$ldap_add['userPasswordRFC2617'] = $passUserRFC2617;
					if (!@ldap_mod_add($ds, $dn, $ldap_add)) 
					{
						return false;
					}
				}
			}
			/* userPasswordRFC2617 End's*/
			
			if (!@ldap_modify($ds, $dn, $entry))
			{
				return false;
			}
			$GLOBALS['phpgw']->session->appsession('password','phpgwapi',base64_encode($new_passwd));
			return $new_passwd;
		}

                function change_password_user ($old_passwd, $new_passwd, $dn, $referrals=false)
		{
                        if ( (!empty($GLOBALS['phpgw_info']['server']['ldap_master_host'])) &&
				 (!empty($GLOBALS['phpgw_info']['server']['ldap_master_root_dn'])) &&
			 	 (!empty($GLOBALS['phpgw_info']['server']['ldap_master_root_pw'])) )
			{
				$ds = $GLOBALS['phpgw']->common->ldapConnect($GLOBALS['phpgw_info']['server']['ldap_master_host'],
						$GLOBALS['phpgw_info']['server']['ldap_master_root_dn'],
						$GLOBALS['phpgw_info']['server']['ldap_master_root_pw']);
			}
			else
			{
				$ds = $GLOBALS['phpgw']->common->ldapConnect();
			}
			if (!$ds)
				{
				$this->auth_reason = ldap_errno($ldap);
				return False;
				}
			else
			{
				if ($referrals)
					{
					$this->passwd=$old_passwd;
					$this->dn=$dn;
					ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
					ldap_set_option($ds, LDAP_OPT_REFERRALS, 1);
					if($GLOBALS['phpgw_info']['server']['diretorioescravo'])
						{
						ldap_set_rebind_proc($ds, array($this, '_rebindProc'));
						}
					}
				$modify["userpassword"]=$new_passwd;
				if (!@ldap_bind($ds,$dn,$old_passwd))
					{
					if (!@ldap_mod_replace($ds,$dn,$modify))
						{
						$this->auth_reason = ldap_errno($ds);
						return false;
						}
						else
						{
                                                    $GLOBALS['phpgw']->session->appsession('password','phpgwapi',base64_encode($new_passwd));
						return $new_passwd;
						}
					$this->auth_reason = ldap_errno($ds);
					return False;
					}
					else
					{
					if (!ldap_mod_replace($ds,$dn,$modify))
						{
						$this->auth_reason = ldap_errno($ds);
						return False;
						}
						else
						{
                                                $GLOBALS['phpgw']->session->appsession('password','phpgwapi',base64_encode($new_passwd));
						return $new_passwd;
						}
					}
			}
		}


		function update_lastlogin($_account_id, $ip)
		{
			if ($GLOBALS['phpgw_info']['server']['account_repository'] == 'ldap')
			{
				$entry['phpgwaccountlastlogin']     = time();
				$entry['phpgwaccountlastloginfrom'] = $ip;
	
				$ds = $GLOBALS['phpgw']->common->ldapConnect();
				$sri = ldap_search($ds, $GLOBALS['phpgw_info']['server']['ldap_context'], 'uidnumber=' . (int)$_account_id);
				$allValues = ldap_get_entries($ds, $sri);
	
				$dn = $allValues[0]['dn'];
				$this->previous_login = $allValues[0]['phpgwaccountlastlogin'][0];
	
				@ldap_modify($ds, $dn, $entry);
			}
			else
			{
				$GLOBALS['phpgw']->db->query("select account_lastlogin from phpgw_accounts where account_id='$_account_id'",__LINE__,__FILE__);
				$GLOBALS['phpgw']->db->next_record();
				$this->previous_login = $GLOBALS['phpgw']->db->f('account_lastlogin');
	
				$GLOBALS['phpgw']->db->query("update phpgw_accounts set account_lastloginfrom='"
					. "$ip', account_lastlogin='" . time()
					. "' where account_id='$_account_id'",__LINE__,__FILE__);
			}
		}
	}
?>
