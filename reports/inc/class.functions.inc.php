<?php
	/**********************************************************************************\
	*Expresso Relatório                  									      *
	*by Elvio Rufino da Silva (elviosilva@yahoo.com.br, elviosilva@cepromat.mt.gov.br) *
	* --------------------------------------------------------------------------------*
	*  This program is free software; you can redistribute it and/or modify it		  *
	*  under the terms of the GNU General Public License as published by the		  *
	*  Free Software Foundation; either version 2 of the License, or (at your		  *
	*  option) any later version.													 *
	**********************************************************************************/
	
	include_once('class.db_functions.inc.php');
	include_once(PHPGW_API_INC.'/class.config.inc.php');

	class functions
	{
		var $public_functions = array
		(
			'make_array_acl' 			=> True,
			'check_acl'					=> True,
			'read_acl'					=> True,
			'exist_account_lid'			=> True,
			'exist_email'				=> True,
			'array_invert'				=> True,
			'Paginate_user'				=> True,
			'Paginate_cota'				=> True,
			'Paginate_shareAccount'			=> True,
			'Paginate_institutionalAccount'		=> True,
			'Paginate_user_logon'		=> True,
			'get_list_all'				=> True,
			'get_groups_list'			=> True,
			'get_list_ou_user_logon'	=> True,
			'show_access_log'			=> True,
			'get_sectors_list'			=> True
		);
		
		var $nextmatchs;
		var $sectors_list = array();
		var $current_config;
		
		function functions()
		{
			$this->db_functions = new db_functions;
			$GLOBALS['phpgw']->db = $this->db_functions->db;

			$c = new config;
			$c->read_repository();
			$this->current_config = $c->config_data;
		}

		// Account and type of access. Return: Have access ? (true/false)
		function check_acl($account_lid, $access)
		{
			$acl = $this->read_acl($account_lid);
			$array_acl = $this->make_array_acl($acl['acl']);
			
			switch($access)
			{
				case list_users:
					if ($array_acl['acl_add_users'] || $array_acl['acl_edit_users'] || $array_acl['acl_delete_users'] || $array_acl['acl_change_users_password'] || $array_acl['acl_change_users_quote'] || $array_acl['acl_edit_sambausers_attributes'] || $array_acl['acl_view_users'] || $array_acl['acl_manipulate_corporative_information'] || $array_acl['acl_edit_users_phonenumber'] )
						return true;
					break;
				case report_users:
					if ($array_acl['acl_change_users_quote'] || $array_acl['acl_view_users'])
						return true;
					break;
				case list_groups:
					if ($array_acl['acl_add_groups'] || $array_acl['acl_edit_groups'] || $array_acl['acl_delete_groups'])
						return true;
					break;
				case list_maillists:
					if ($array_acl['acl_add_maillists'] || $array_acl['acl_edit_maillists'] || $array_acl['acl_delete_maillists'])
						return true;
					break;
				case list_sectors:
//					if ($array_acl[acl_create_sectors] || $array_acl[acl_edit_sectors] || $array_acl[acl_delete_sectors])
					if ($array_acl['acl_view_users'])
						return true;
					break;
				case list_computers:
					if ($array_acl['acl_create_computers'] || $array_acl['acl_edit_computers'] || $array_acl['acl_delete_computers'])
						return true;
					break;

				case display_groups:
					if ( $array_acl['acl_edit_users'] || $array_acl['acl_view_users'] || ($array_acl['acl_edit_sambausers_attributes'] && ($this->current_config['expressoAdmin_samba_support'] == 'true')) )
						return true;
					break;
				case display_emailconfig:
					if ($array_acl['acl_edit_users'] || $array_acl['acl_view_users'])
						return true;
					break;
				case display_applications:
					if ($array_acl['acl_edit_users'] || $array_acl['acl_view_users'])
						return true;
					break;
				case display_emaillists:
					if ($array_acl['acl_edit_users'] || $array_acl['acl_view_users'])
						return true;
					break;

				case list_institutional_accounts:
					if ($array_acl['acl_add_institutional_accounts'] || $array_acl['acl_edit_institutional_accounts'] || $array_acl['acl_delete_institutional_accounts'])
						return true;
					break;


				default:
					return $array_acl["acl_$access"];
			}
			return false;
		}

		
		// Read acl from db
		function read_acl($account_lid)
		{ 
			$acl = $this->db_functions->read_acl($account_lid);
			
			$result['acl'] = $acl[0]['acl'];
			$result['manager_lid'] = $acl[0]['manager_lid'];
			$result['raw_context'] = $acl[0]['context'];
			
			$all_contexts = split("%", $acl[0]['context']);
			foreach ($all_contexts as $index=>$context)
			{
				$result['contexts'][] = $context;
				$result['contexts_display'][] = str_replace(", ", ".", ldap_dn2ufn( $context ));
			}
			
			return $result;
		}
		
		function make_array_acl($acl)
		{
			$array_acl_tmp = array();
			$tmp = array(		"acl_add_users",
							 	"acl_edit_users",
							 	"acl_delete_users",
							 	"acl_EMPTY1",
							 	"acl_add_groups",
							 	"acl_edit_groups",
							 	"acl_delete_groups",
							 	"acl_change_users_password",
							 	"acl_add_maillists",
							 	"acl_edit_maillists",
							 	"acl_delete_maillists",
							 	"acl_EMPTY2",
							 	"acl_create_sectors",
							 	"acl_edit_sectors",
							 	"acl_delete_sectors",
							 	"acl_edit_sambausers_attributes",
							 	"acl_view_global_sessions",
							 	"acl_view_logs",
							 	"acl_change_users_quote",
							 	"acl_set_user_default_password",
							 	"acl_create_computers",
							 	"acl_edit_computers",
							 	"acl_delete_computers",
							 	"acl_rename_users",
							 	"acl_edit_sambadomains",
							 	"acl_view_users",
							 	"acl_edit_email_groups",
							 	"acl_empty_user_inbox",
							 	"acl_manipulate_corporative_information",
							 	"acl_edit_users_picture",
							 	"acl_edit_scl_email_lists",
							 	"acl_edit_users_phonenumber",
							 	"acl_add_institutional_accounts",
							 	"acl_edit_institutional_accounts",
							 	"acl_remove_institutional_accounts"
							 	);
			
			foreach ($tmp as $index => $right)
			{
				$bin = '';
				for ($i=0; $i<$index; ++$i)
				{
					$bin .= '0';
				}
				$bin = '1' . $bin;
				
				$array_acl[$right] = $this->safeBitCheck(bindec($bin), $acl);
			}
			return $array_acl;
		}
		
		function get_inactive_users($contexts)
		{
			$retorno = array();
			$tempUsers = array();
			//Pego no LDAP todos os usuários dos contextos em questão.
			$usuariosLdap = $this->get_list('accounts','',$contexts);
			foreach($usuariosLdap as $usuarioLdap) 
			{
				$tempUsers[$usuarioLdap["account_id"]] = $usuarioLdap["account_lid"];
			}
			$ids = implode(",",array_keys($tempUsers)); //Consigo a lista de uids daquele contexto para mandar na query para o banco.
			
			//Pego nas configurações do expresso o número de dias necessários para inatividade.
			$timeToExpire = $GLOBALS['phpgw_info']['server']['time_to_account_expires'];
			
			
			$ultimoTsValido = time() - ($timeToExpire * 86400); //O último timestamp válido é dado pelo de agora menos o número de dias para expirar vezes a quantidade de segundos existente em 1 dia.
			$query = "select account_id,max(li) as last_login from phpgw_access_log where account_id in (".$ids.") group by account_id having max(li) < ".$ultimoTsValido." order by max(li)";

			$GLOBALS['phpgw']->db->query($query);
			while($GLOBALS['phpgw']->db->next_record())
			{
				$result = $GLOBALS['phpgw']->db->row();
				array_push($retorno,array("uidNumber"=>$result["account_id"],"login"=> $tempUsers[$result["account_id"]],"li"=>$result["last_login"]));
			}
			
			return $retorno;
		}

		function safeBitCheck($number,$comparison)
		{
        	$binNumber = base_convert($number,10,2);
	        $binComparison = strrev(base_convert($comparison,10,2));
			$str = strlen($binNumber);
	        
	        if ( ($str <= strlen($binComparison)) && ($binComparison{$str-1}==="1") )
        	{
				return '1';
	        }
			else
	        {
				return '0';
			}
		}
		
		function get_list_all($type, $query, $contexts,$sizelimit)
		{
			$dn			= $GLOBALS['phpgw_info']['server']['ldap_root_dn'];
			$passwd		= $GLOBALS['phpgw_info']['server']['ldap_root_pw'];
			$ldap_conn	= ldap_connect($GLOBALS['phpgw_info']['server']['ldap_host']);
			ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);
			ldap_bind($ldap_conn,$dn,$passwd);
			
			if ($type == 'accounts')
			{
				$justthese = array("uidnumber", "uid", "cn", "mail");
				$filter="(&(phpgwAccountType=u)(|(ou=*".$query."*)(uid=*".$query."*)(sn=*".$query."*)(cn=*".$query."*)(givenName=*".$query."*)(mail=$query*)(mailAlternateAddress=$query*)))";
								
				$tmp = array();
				foreach ($contexts as $index=>$context)
				{
					$search=ldap_search($ldap_conn, $context, $filter, $justthese, 0, $sizelimit);
					$info = ldap_get_entries($ldap_conn, $search);
					
					for ($i=0; $i < $info['count']; ++$i)
					{
						$tmp[$info[$i]['uid'][0]]['account_id']	 = $info[$i]['uidnumber'][0]; 
						$tmp[$info[$i]['uid'][0]]['account_lid'] = $info[$i]['uid'][0];
						$tmp[$info[$i]['uid'][0]]['account_cn']	 = $info[$i]['cn'][0];
						$tmp[$info[$i]['uid'][0]]['account_mail']= $info[$i]['mail'][0];
						$sort[] = $info[$i]['uid'][0];
					}
				}
				ldap_close($ldap_conn);
				
				if (count($sort))
				{
					natcasesort($sort);
					foreach ($sort as $user_uid)
						$return[$user_uid] = $tmp[$user_uid];
				}
				
				return $return;
			}
			elseif($type == 'groups')
			{
				$filter="(&(phpgwAccountType=g)(cn=*$query*))";
				$justthese = array("gidnumber", "cn", "description");
				
				$tmp = array();
				foreach ($contexts as $index=>$context)
				{
					$search=ldap_search($ldap_conn, $context, $filter, $justthese);
					$info = ldap_get_entries($ldap_conn, $search);
					for ($i=0; $i < $info['count']; ++$i)
					{
						$tmp[$info[$i]['cn'][0]]['cn']= $info[$i]['cn'][0];
						$tmp[$info[$i]['cn'][0]]['description']= $info[$i]['description'][0];
						$tmp[$info[$i]['cn'][0]]['gidnumber']= $info[$i]['gidnumber'][0];
						$sort[] = $info[$i]['cn'][0];
					}
				}
				ldap_close($ldap_conn);
				
				natcasesort($sort);
				foreach ($sort as $group_cn)
					$return[$group_cn] = $tmp[$group_cn];
				
				return $return;
			}
			elseif($type == 'maillists')
			{
				$filter="(&(phpgwAccountType=l)(|(cn=*".$query."*)(uid=*".$query."*)(mail=*".$query."*)))";
				$justthese = array("uidnumber", "cn", "uid", "mail");

				$tmp = array();
				foreach ($contexts as $index=>$context)
				{
					$search=ldap_search($ldap_conn, $context, $filter, $justthese);
					$info = ldap_get_entries($ldap_conn, $search);
					
					for ($i=0; $i < $info['count']; ++$i)
					{
						$tmp[$info[$i]['uid'][0]]['uid']		= $info[$i]['uid'][0];
						$tmp[$info[$i]['uid'][0]]['name']		= $info[$i]['cn'][0];
						$tmp[$info[$i]['uid'][0]]['uidnumber']	= $info[$i]['uidnumber'][0];
						$tmp[$info[$i]['uid'][0]]['email']		= $info[$i]['mail'][0];
						$sort[] = $info[$i]['uid'][0];
					}
				}
				ldap_close($ldap_conn);
				
				natcasesort($sort);
				foreach ($sort as $maillist_uid)
					$return[$maillist_uid] = $tmp[$maillist_uid];
				
				return $return;
			}
			elseif($type == 'computers')
			{
				$filter="(&(objectClass=sambaSAMAccount)(|(sambaAcctFlags=[W          ])(sambaAcctFlags=[DW         ])(sambaAcctFlags=[I          ])(sambaAcctFlags=[S          ]))(cn=*".$query."*))";
				$justthese = array("cn","uidNumber","description");

				$tmp = array();
				foreach ($contexts as $index=>$context)
				{
					$search=ldap_search($ldap_conn, $context, $filter, $justthese);
					$info = ldap_get_entries($ldap_conn, $search);
					for ($i=0; $i < $info['count']; ++$i)
					{
						$tmp[$info[$i]['cn'][0]]['cn']			= $info[$i]['cn'][0];
						$tmp[$info[$i]['cn'][0]]['uidNumber']	= $info[$i]['uidnumber'][0];
						$tmp[$info[$i]['cn'][0]]['description']	= utf8_decode($info[$i]['description'][0]);
						$sort[] = $info[$i]['cn'][0];
					}

				}
				ldap_close($ldap_conn);
				
				if (!empty($sort))
				{
					natcasesort($sort);
					foreach ($sort as $computer_cn)
						$return[$computer_cn] = $tmp[$computer_cn];
				}
				
				return $return;
			}
		}

		function get_list($type, $query, $contexts)
		{
			$dn			= $GLOBALS['phpgw_info']['server']['ldap_root_dn'];
			$passwd		= $GLOBALS['phpgw_info']['server']['ldap_root_pw'];
			$ldap_conn	= ldap_connect($GLOBALS['phpgw_info']['server']['ldap_host']);
			ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);
			ldap_bind($ldap_conn,$dn,$passwd);
			
			if ($type == 'accounts')
			{
				$justthese = array("uidnumber", "uid", "cn", "mail");
				$filter="(&(phpgwAccountType=u)(|(uid=*".$query."*)(sn=*".$query."*)(cn=*".$query."*)(givenName=*".$query."*)(mail=$query*)(mailAlternateAddress=$query*)))";

				$tmp = array();
				foreach ($contexts as $index=>$context)
				{
					$search=ldap_search($ldap_conn, $context, $filter, $justthese);
					$info = ldap_get_entries($ldap_conn, $search);
					
					for ($i=0; $i < $info['count']; ++$i)
					{
						$tmp[$info[$i]['uid'][0]]['account_id']	 = $info[$i]['uidnumber'][0]; 
						$tmp[$info[$i]['uid'][0]]['account_lid'] = $info[$i]['uid'][0];
						$tmp[$info[$i]['uid'][0]]['account_cn']	 = $info[$i]['cn'][0];
						$tmp[$info[$i]['uid'][0]]['account_mail']= $info[$i]['mail'][0];
						$sort[] = $info[$i]['uid'][0];
					}
				}
				ldap_close($ldap_conn);
				
				if (count($sort))
				{
					natcasesort($sort);
					foreach ($sort as $user_uid)
						$return[$user_uid] = $tmp[$user_uid];
				}
				
				return $return;
			}
			elseif($type == 'groups')
			{
				$filter="(&(phpgwAccountType=g)(cn=*$query*))";
				$justthese = array("gidnumber", "cn", "description");
				
				$tmp = array();
				foreach ($contexts as $index=>$context)
				{
					$search=ldap_search($ldap_conn, $context, $filter, $justthese);
					$info = ldap_get_entries($ldap_conn, $search);
					for ($i=0; $i < $info['count']; ++$i)
					{
						$tmp[$info[$i]['cn'][0]]['cn']= $info[$i]['cn'][0];
						$tmp[$info[$i]['cn'][0]]['description']= $info[$i]['description'][0];
						$tmp[$info[$i]['cn'][0]]['gidnumber']= $info[$i]['gidnumber'][0];
						$sort[] = $info[$i]['cn'][0];
					}
				}
				ldap_close($ldap_conn);
				
				natcasesort($sort);
				foreach ($sort as $group_cn)
					$return[$group_cn] = $tmp[$group_cn];
				
				return $return;
			}
			elseif($type == 'maillists')
			{
				$filter="(&(phpgwAccountType=l)(|(cn=*".$query."*)(uid=*".$query."*)(mail=*".$query."*)))";
				$justthese = array("uidnumber", "cn", "uid", "mail");

				$tmp = array();
				foreach ($contexts as $index=>$context)
				{
					$search=ldap_search($ldap_conn, $context, $filter, $justthese);
					$info = ldap_get_entries($ldap_conn, $search);
					
					for ($i=0; $i < $info['count']; ++$i)
					{
						$tmp[$info[$i]['uid'][0]]['uid']		= $info[$i]['uid'][0];
						$tmp[$info[$i]['uid'][0]]['name']		= $info[$i]['cn'][0];
						$tmp[$info[$i]['uid'][0]]['uidnumber']	= $info[$i]['uidnumber'][0];
						$tmp[$info[$i]['uid'][0]]['email']		= $info[$i]['mail'][0];
						$sort[] = $info[$i]['uid'][0];
					}
				}
				ldap_close($ldap_conn);
				
				natcasesort($sort);
				foreach ($sort as $maillist_uid)
					$return[$maillist_uid] = $tmp[$maillist_uid];
				
				return $return;
			}
			elseif($type == 'computers')
			{
				$filter="(&(objectClass=sambaSAMAccount)(|(sambaAcctFlags=[W          ])(sambaAcctFlags=[DW         ])(sambaAcctFlags=[I          ])(sambaAcctFlags=[S          ]))(cn=*".$query."*))";
				$justthese = array("cn","uidNumber","description");

				$tmp = array();
				foreach ($contexts as $index=>$context)
				{
					$search=ldap_search($ldap_conn, $context, $filter, $justthese);
					$info = ldap_get_entries($ldap_conn, $search);
					for ($i=0; $i < $info['count']; ++$i)
					{
						$tmp[$info[$i]['cn'][0]]['cn']			= $info[$i]['cn'][0];
						$tmp[$info[$i]['cn'][0]]['uidNumber']	= $info[$i]['uidnumber'][0];
						$tmp[$info[$i]['cn'][0]]['description']	= utf8_decode($info[$i]['description'][0]);
						$sort[] = $info[$i]['cn'][0];
					}

				}

				ldap_close($ldap_conn);
				
				if (!empty($sort))
				{
					natcasesort($sort);
					foreach ($sort as $computer_cn)
						$return[$computer_cn] = $tmp[$computer_cn];
				}
				
				return $return;
			}
		}

		function get_organizations($context, $selected='', $referral=false, $show_invisible_ou=true, $master=false)
		{
			$s = CreateObject('phpgwapi.sector_search_ldap');
			$sectors_info = $s->get_organizations($context, $selected, $referral, $show_invisible_ou, $master);
			return $sectors_info;
		}		
				
		function get_sectors($selected='', $referral=false, $show_invisible_ou=true)
		{
			$s = CreateObject('phpgwapi.sector_search_ldap');
			$sectors_info = $s->get_sectors($selected, $referral, $show_invisible_ou);
			return $sectors_info;
		}		
 
		// Get list of levels (0). 
		function get_groups_list($contexts,$filtro)
		{
			$a_sectors = array();
			
			$dn			= $GLOBALS['phpgw_info']['server']['ldap_root_dn'];
			$passwd		= $GLOBALS['phpgw_info']['server']['ldap_root_pw'];
			$ldap_conn	= ldap_connect($GLOBALS['phpgw_info']['server']['ldap_host']);
			
			ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);
			ldap_bind($ldap_conn,$dn,$passwd);
			
			$justthese = array("dn");
			$filter = "(ou=".$filtro.")";
			
			$systemName = strtolower($GLOBALS['phpgw_info']['server']['system_name']);
			if ($systemName != '')
				$filter = "(&$filter(phpgwSystem=$systemName))";
			
			foreach ($contexts as $context)
			{
				$search=ldap_search($ldap_conn, $context, $filter, $justthese);
        		$info = ldap_get_entries($ldap_conn, $search);

		        for ($i=0; $i<$info["count"]; ++$i)
    		    {
    		    	$a_sectors[] = $info[$i]['dn'];	
    		    }
			}
        	
			ldap_close($ldap_conn);

			// Retiro o count do array info e inverto o array para ordenação.
	        foreach ($a_sectors as $context)
    	    {
				$array_dn = ldap_explode_dn ( $context, 1 );

                $array_dn_reverse  = array_reverse ( $array_dn, true );

				// Retirar o indice count do array.
				array_pop ( $array_dn_reverse );

				$inverted_dn[$context] = implode ( "#", $array_dn_reverse );
			}

			// Ordenação
			natcasesort($inverted_dn);
			
			// Construção do select
			$level = 0;
			$options = array();
			foreach ($inverted_dn as $dn=>$invert_ufn)
			{
                $display = '';

                $array_dn_reverse = explode ( "#", $invert_ufn );
                $array_dn  = array_reverse ( $array_dn_reverse, true );
                $level = count( $array_dn ) - (int)(count(explode(",", $GLOBALS['phpgw_info']['server']['ldap_context'])) + 1);

                if ($level == 0)
				{
                    $display.= ' ';
	                reset ( $array_dn );
    	            $display .= ' ' . (current ( $array_dn ) );
					$dn = trim(strtolower($dn));
					$options[$dn] = $display;
                }
        	}
    	    return $options;
		}
		
		// Get list of levels (0), value DN
		function get_groups_list_dn($contexts,$filtro)
		{
			$a_sectors = array();
			
			$dn			= $GLOBALS['phpgw_info']['server']['ldap_root_dn'];
			$passwd		= $GLOBALS['phpgw_info']['server']['ldap_root_pw'];
			$ldap_conn	= ldap_connect($GLOBALS['phpgw_info']['server']['ldap_host']);
			
			ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);
			ldap_bind($ldap_conn,$dn,$passwd);
			
			$justthese = array("dn");
			$filter = "(ou=".$filtro.")";
			
			$systemName = strtolower($GLOBALS['phpgw_info']['server']['system_name']);
			if ($systemName != '')
				$filter = "(&$filter(phpgwSystem=$systemName))";
			
			foreach ($contexts as $context)
			{
				$search=ldap_search($ldap_conn, $context, $filter, $justthese);
        		$info = ldap_get_entries($ldap_conn, $search);
		        for ($i=0; $i<$info["count"]; ++$i)
    		    {
    		    	$a_sectors[] = $info[$i]['dn'];	
    		    }
			}
        	
			ldap_close($ldap_conn);

			// Retiro o count do array info e inverto o array para ordenação.
	        foreach ($a_sectors as $context)
    	    {
				$array_dn = ldap_explode_dn ( $context, 1 );

                $array_dn_reverse  = array_reverse ( $array_dn, true );

				// Retirar o indice count do array.
				array_pop ( $array_dn_reverse );

				$inverted_dn[$context] = implode ( "#", $array_dn_reverse );
			}

			// Ordenação
			natcasesort($inverted_dn);
			
			// Construção do select
			$level = 0;
			$options = array();
			foreach ($inverted_dn as $dn=>$invert_ufn)
			{
                $display = '';

                $array_dn_reverse = explode ( "#", $invert_ufn );
                $array_dn  = array_reverse ( $array_dn_reverse, true );
                $level = count( $array_dn ) - (int)(count(explode(",", $GLOBALS['phpgw_info']['server']['ldap_context'])) + 1);

                if ($level == 0)
				{
                    $display.= ' ';
	                reset ( $array_dn );
    	            $display .= ' ' . trim(strtolower($dn));
					$dn = trim(strtolower($dn));
					$options[] = $display;
                }
        	}
    	    return $options;
		}

		// Get list of all levels.
		function get_sectors_list($contexts,$contextdn)
		{
			$a_sectors = array();
			$dn			= $GLOBALS['phpgw_info']['server']['ldap_root_dn'];
			$passwd		= $GLOBALS['phpgw_info']['server']['ldap_root_pw'];
			$ldap_conn	= ldap_connect($GLOBALS['phpgw_info']['server']['ldap_host']);
			
			ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);
			ldap_bind($ldap_conn,$dn,$passwd);
			
			$justthese = array("ou", "dn");
			$filter = "(&(objectClass=organizationalUnit)(|(objectClass=organizationalUnit)))";
			
			foreach ($contexts as $context)
			{
				$search=ldap_search($ldap_conn, $contextdn, $filter, $justthese);
        		$info = ldap_get_entries($ldap_conn, $search);
		        for ($i=0; $i<$info["count"]; ++$i)
    		    {
    		    	$a_sectors[] = trim(strtoupper($info[$i]['dn']));
    		    }
			}

			ldap_close($ldap_conn);

			// Retiro o count do array info e inverto o array para ordenação.
	        foreach ($a_sectors as $context)
    	    {

				$array_dn = ldap_explode_dn ( $context, 1 );

                $array_dn_reverse  = array_reverse ( $array_dn, true );

				// Retirar o indice count do array.
				array_pop ( $array_dn_reverse );
				$inverted_dn[$context] = implode ( "#", $array_dn_reverse );

			}

			// Ordenação
			natcasesort($inverted_dn);

			// seleciona os setores do grupo escolhido
			$level = 0;
			$options = array();

			foreach ($inverted_dn as $dn=>$invert_ufn)
			{
				$display = '';
				$array_dn_completo = '';
				$ii = 0;			
				
				$array_dn_reverse = explode ( "#", $invert_ufn );
				$array_dn  = array_reverse ( $array_dn_reverse, true );

				$valorgrupo = (int)(count(explode(",", $GLOBALS['phpgw_info']['server']['ldap_context'])));
				$valorsubgrupo = (count($array_dn)-1);
				$level = count( $array_dn ) - (int)(count(explode(",", $GLOBALS['phpgw_info']['server']['ldap_context'])) + 1);

				reset ( $array_dn );
				
				if ($level > 0)
				{
					for ($i=0; $i<$level; ++$i)
					{
						$ii = $ii +1;
						$display .= ' --';

						if ($ii==1)
						{
							$array_dn_completo .= $array_dn[$valorgrupo + $ii]; 						
						}
						else
						{
							$array_dn_completo .= " | ".$array_dn[$valorgrupo + $ii]; 
						}

					}
				}
				else
				{
//					$array_dn_completo .= (current($array_dn)); 											
					$array_dn_completo .= ' '; 											
				}

				$display .= ' '. (current($array_dn)).'#'.trim(strtolower($dn)).'#'.$array_dn_completo;
				$options[] = $display;

        	}
    	    return $options;
		}

		function get_list_usersgroups_sector($query, $contexts,$sizelimit)
		{
			$dn			= $GLOBALS['phpgw_info']['server']['ldap_root_dn'];
			$passwd		= $GLOBALS['phpgw_info']['server']['ldap_root_pw'];
			$ldap_conn	= ldap_connect($GLOBALS['phpgw_info']['server']['ldap_host']);
			ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);
			ldap_bind($ldap_conn,$dn,$passwd);

			$filter = "(&(phpgwAccountType=g)(cn=*))";
			$justthese = array("gidnumber", "cn", "description");

			$tmp = array();
			foreach ($contexts as $index=>$context)
			{				
				$search = ldap_search($ldap_conn, $query, $filter, $justthese, 0, $sizelimit);
				$info = ldap_get_entries($ldap_conn, $search);				
				
				for ($i=0; $i < $info['count']; ++$i)
				{
					$tmp[$info[$i]['cn'][0]]['id']				= $info[$i]['gidnumber'][0];
					$tmp[$info[$i]['cn'][0]]['name']			= $info[$i]['cn'][0];
					$tmp[$info[$i]['cn'][0]]['description']		= $info[$i]['description'][0];
					$sort[] = $info[$i]['cn'][0];
				}
			}
			ldap_close($ldap_conn);
			
			if (count($sort))
			{			
				natcasesort($sort);
				foreach ($sort as $usersgroups_gid)
					$return[$usersgroups_gid] = $tmp[$usersgroups_gid];
			}
			
			return $return;
		}		
		
		function get_list_maillists_sector($query, $contexts,$sizelimit)
		{
			$dn			= $GLOBALS['phpgw_info']['server']['ldap_root_dn'];
			$passwd		= $GLOBALS['phpgw_info']['server']['ldap_root_pw'];
			$ldap_conn	= ldap_connect($GLOBALS['phpgw_info']['server']['ldap_host']);
			ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);
			ldap_bind($ldap_conn,$dn,$passwd);

			$filter="(&(phpgwAccountType=l)(|(uid=*)))";
			$justthese = array("uidnumber", "cn", "uid", "mail");

			$tmp = array();
			foreach ($contexts as $index=>$context)
			{
				$search = ldap_search($ldap_conn, $query, $filter, $justthese, 0, $sizelimit);
				$info = ldap_get_entries($ldap_conn, $search);				
				
				for ($i=0; $i < $info['count']; ++$i)
				{
					$tmp[$info[$i]['uid'][0]]['uid']		= $info[$i]['uid'][0];
					$tmp[$info[$i]['uid'][0]]['name']		= $info[$i]['cn'][0];
					$tmp[$info[$i]['uid'][0]]['uidnumber']	= $info[$i]['uidnumber'][0];
					$tmp[$info[$i]['uid'][0]]['email']		= $info[$i]['mail'][0];
					$sort[] = $info[$i]['uid'][0];
				}
			}
			ldap_close($ldap_conn);
			
			if (count($sort))
			{			
				natcasesort($sort);
				foreach ($sort as $maillist_uid)
					$return[$maillist_uid] = $tmp[$maillist_uid];
			}
			
			return $return;
		}		
		
		function get_list_user_sector($query, $contexts,$sizelimit)
		{
			$dn			= $GLOBALS['phpgw_info']['server']['ldap_root_dn'];
			$passwd		= $GLOBALS['phpgw_info']['server']['ldap_root_pw'];
			$ldap_conn	= ldap_connect($GLOBALS['phpgw_info']['server']['ldap_host']);
			ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);
			ldap_bind($ldap_conn,$dn,$passwd);

			$filter="(&(phpgwAccountType=u)(|(uid=*)))";
			$justthese = array("uidnumber", "uid", "cn", "mail","accountstatus","dn","createtimestamp","telephoneNumber");
			
			$tmp = array();
		
			foreach ($contexts as $index=>$context)
			{

				$search = ldap_search($ldap_conn, $query, $filter, $justthese, 0, $sizelimit);
				$info = ldap_get_entries($ldap_conn, $search);

				for ($i=0; $i < $info['count']; ++$i)
				{
					$tmp[$info[$i]['uid'][0]]['account_id']	 = $info[$i]['uidnumber'][0]; 
					$tmp[$info[$i]['uid'][0]]['account_lid'] = $info[$i]['uid'][0];
					$tmp[$info[$i]['uid'][0]]['account_cn']	 = $info[$i]['cn'][0];
					$tmp[$info[$i]['uid'][0]]['account_mail']= $info[$i]['mail'][0];
					$tmp[$info[$i]['uid'][0]]['account_phone']= $info[$i]['telephonenumber'][0];
					$tmp[$info[$i]['uid'][0]]['account_accountstatus']= $info[$i]['accountstatus'][0];
					$tmp[$info[$i]['uid'][0]]['createtimestamp']= $info[$i]['createtimestamp'][0];					
					$sort[] = $info[$i]['uid'][0];
				}
			}
			
			ldap_close($ldap_conn);
				
			if (count($sort))
			{
				natcasesort($sort);
				foreach ($sort as $user_uid)
					$return[$user_uid] = $tmp[$user_uid];
			}
				
			return $return;
		}

                function get_list_cota_sector($query, $contexts,$sizelimit)
                {
                        $dn                     = $GLOBALS['phpgw_info']['server']['ldap_root_dn'];
                        $passwd         = $GLOBALS['phpgw_info']['server']['ldap_root_pw'];
                        $ldap_conn      = ldap_connect($GLOBALS['phpgw_info']['server']['ldap_host']);
                        ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
                        ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);
                        ldap_bind($ldap_conn,$dn,$passwd);

                        $filter="(|(phpgwAccountType=u)(|(phpgwAccountType=s)))";
                        $justthese = array("uidnumber", "uid", "cn", "mail","accountstatus","dn","createtimestamp","telephoneNumber");

                        $tmp = array();

                        foreach ($contexts as $index=>$context)
                        {

                                $search = ldap_search($ldap_conn, $query, $filter, $justthese, 0, $sizelimit);
                                $info = ldap_get_entries($ldap_conn, $search);

                                for ($i=0; $i < $info['count']; ++$i)
                                {
                                        $tmp[$info[$i]['uid'][0]]['account_id']  = $info[$i]['uidnumber'][0];
                                        $tmp[$info[$i]['uid'][0]]['account_lid'] = $info[$i]['uid'][0];
                                        $tmp[$info[$i]['uid'][0]]['account_cn']  = $info[$i]['cn'][0];
                                        $tmp[$info[$i]['uid'][0]]['account_mail']= $info[$i]['mail'][0];
                                        $tmp[$info[$i]['uid'][0]]['account_phone']= $info[$i]['telephonenumber'][0];
                                        $tmp[$info[$i]['uid'][0]]['account_accountstatus']= $info[$i]['accountstatus'][0];
                                        $tmp[$info[$i]['uid'][0]]['createtimestamp']= $info[$i]['createtimestamp'][0];
                                        $sort[] = $info[$i]['uid'][0];
                                }
                        }

                        ldap_close($ldap_conn);

                        if (count($sort))
                        {
                                natcasesort($sort);
                                foreach ($sort as $user_uid)
                                        $return[$user_uid] = $tmp[$user_uid];
                        }

                        return $return;

                }


                function get_list_shareAccounts_sector($query, $contexts,$sizelimit)
                {
                        $dn		= $GLOBALS['phpgw_info']['server']['ldap_root_dn'];
                        $passwd         = $GLOBALS['phpgw_info']['server']['ldap_root_pw'];
                        $ldap_conn      = ldap_connect($GLOBALS['phpgw_info']['server']['ldap_host']);
                        ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
                        ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);
                        ldap_bind($ldap_conn,$dn,$passwd);

                        $filter="(phpgwAccountType=s)";
                        $justthese = array("uid", "cn", "mail","accountstatus");

                        $tmp = array();

                        foreach ($contexts as $index=>$context)
                        {

                                $search = ldap_search($ldap_conn, $query, $filter, $justthese, 0, $sizelimit);
                                $info = ldap_get_entries($ldap_conn, $search);

                                for ($i=0; $i < $info['count']; ++$i)
                                {
                                        $tmp[$info[$i]['uid'][0]]['account_lid'] = $info[$i]['uid'][0];
                                        $tmp[$info[$i]['uid'][0]]['account_cn']  = $info[$i]['cn'][0];
                                        $tmp[$info[$i]['uid'][0]]['account_mail']= $info[$i]['mail'][0];
                                        $tmp[$info[$i]['uid'][0]]['account_accountstatus']= $info[$i]['accountstatus'][0];
                                        $sort[] = $info[$i]['uid'][0];
                                }
                        }

                        ldap_close($ldap_conn);

                        if (count($sort))
                        {
                                natcasesort($sort);
                                foreach ($sort as $user_uid)
                                        $return[$user_uid] = $tmp[$user_uid];
                        }

                        return $return;
                }

                function get_list_institutionalAccounts_sector($query, $contexts,$sizelimit)
                {
                        $dn             = $GLOBALS['phpgw_info']['server']['ldap_root_dn'];
                        $passwd         = $GLOBALS['phpgw_info']['server']['ldap_root_pw'];
                        $ldap_conn      = ldap_connect($GLOBALS['phpgw_info']['server']['ldap_host']);
                        ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
                        ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);
                        ldap_bind($ldap_conn,$dn,$passwd);

                        $filter="(phpgwAccountType=i)";
                        $justthese = array("uid", "cn", "mail","accountstatus","mailForwardingAddress");

                        $tmp = array();

                        foreach ($contexts as $index=>$context)
                        {

                                $search = ldap_search($ldap_conn, $query, $filter, $justthese, 0, $sizelimit);
                                $info = ldap_get_entries($ldap_conn, $search);

                                for ($i=0; $i < $info['count']; ++$i)
                                {
                                        $tmp[$info[$i]['uid'][0]]['account_cn']  = $info[$i]['cn'][0];
                                        $tmp[$info[$i]['uid'][0]]['account_mail']= $info[$i]['mail'][0];
                                        $tmp[$info[$i]['uid'][0]]['account_accountstatus']= $info[$i]['accountstatus'][0];
					$tmp[$info[$i]['uid'][0]]['account_mailforwardingaddress']= $info[$i]['mailforwardingaddress']; 
                                        $sort[] = $info[$i]['uid'][0];
                                }
                        }

                        ldap_close($ldap_conn);

                        if (count($sort))
                        {
                                natcasesort($sort);
                                foreach ($sort as $user_uid)
                                        $return[$user_uid] = $tmp[$user_uid];
                        }

                        return $return;
                }

		function get_count_user_sector($query, $contexts,$sizelimit)
		{
			$dn			= $GLOBALS['phpgw_info']['server']['ldap_root_dn'];
			$passwd		= $GLOBALS['phpgw_info']['server']['ldap_root_pw'];
			$ldap_conn	= ldap_connect($GLOBALS['phpgw_info']['server']['ldap_host']);
			ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);
			ldap_bind($ldap_conn,$dn,$passwd);			
			// counting users by sector.
			foreach ($contexts as $index=>$context)	{
				if($context == $GLOBALS['phpgw_info']['server'] ["ldap_context"]) {
					$contexts[$index] = null;
					$justthese = array("dn");
					$filter="(objectClass=OrganizationalUnit)";
					$search = ldap_list($ldap_conn, $context, $filter, $justthese);
					$entries = ldap_get_entries($ldap_conn, $search);
					$contexts = array();
					}
				}

                        $filter="(phpgwAccountType=u)";
                        $justthese = array("dn");
                        $search = ldap_search($ldap_conn, $context, $filter, $justthese, 0, $sizelimit);
                        $entries = ldap_get_entries($ldap_conn, $search);
                        $total_count = $entries["count"];

                        ldap_close($ldap_conn);

                        return $total_count;
			}	
			
                function get_count_cota_sector($query, $contexts,$sizelimit)
                {
                        $dn                     = $GLOBALS['phpgw_info']['server']['ldap_root_dn'];
                        $passwd         = $GLOBALS['phpgw_info']['server']['ldap_root_pw'];
                        $ldap_conn      = ldap_connect($GLOBALS['phpgw_info']['server']['ldap_host']);
                        ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
                        ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);
                        ldap_bind($ldap_conn,$dn,$passwd);
                        // counting users by sector.
			foreach ($contexts as $index=>$context)	{				
                                if($context == $GLOBALS['phpgw_info']['server'] ["ldap_context"]) {
                                        $contexts[$index] = null;
                                        $justthese = array("dn");
                                        $filter="(objectClass=OrganizationalUnit)";
                                        $search = ldap_list($ldap_conn, $context, $filter, $justthese);
                                        $entries = ldap_get_entries($ldap_conn, $search);
                                        $contexts = array();
                                }
                        }

                        $filter="(|(phpgwAccountType=u)(|(phpgwAccountType=s)))";
                        $justthese = array("dn");
				$search = ldap_search($ldap_conn, $context, $filter, $justthese, 0, $sizelimit);
                        $entries = ldap_get_entries($ldap_conn, $search);
                        $total_count = $entries["count"];

                        ldap_close($ldap_conn);

                        return $total_count;
			}

                function get_count_shareAccount_sector($query, $contexts,$sizelimit)
                {
                        $dn             = $GLOBALS['phpgw_info']['server']['ldap_root_dn'];
                        $passwd         = $GLOBALS['phpgw_info']['server']['ldap_root_pw'];
                        $ldap_conn      = ldap_connect($GLOBALS['phpgw_info']['server']['ldap_host']);
                        ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
                        ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);
                        ldap_bind($ldap_conn,$dn,$passwd);
                        // counting users by sector.
                        foreach ($contexts as $index=>$context) {
                                if($context == $GLOBALS['phpgw_info']['server'] ["ldap_context"]) {
                                        $contexts[$index] = null;
                                        $justthese = array("dn");
                                        $filter="(objectClass=OrganizationalUnit)";
                                        $search = ldap_list($ldap_conn, $context, $filter, $justthese);
                                        $entries = ldap_get_entries($ldap_conn, $search);
                                        $contexts = array();
                                }
                        }

                        $filter="(phpgwAccountType=s)";
                        $justthese = array("dn");
			$search = ldap_search($ldap_conn, $context, $filter, $justthese, 0, $sizelimit);
			$entries = ldap_get_entries($ldap_conn, $search);
			$total_count = $entries["count"];

                        ldap_close($ldap_conn);
                        
			return $total_count;
                }

                function get_count_institutionalAccount_sector($query, $contexts,$sizelimit)
                {
                        $dn             = $GLOBALS['phpgw_info']['server']['ldap_root_dn'];
                        $passwd         = $GLOBALS['phpgw_info']['server']['ldap_root_pw'];
                        $ldap_conn      = ldap_connect($GLOBALS['phpgw_info']['server']['ldap_host']);
                        ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
                        ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);
                        ldap_bind($ldap_conn,$dn,$passwd);
                        // counting users by sector.
                        foreach ($contexts as $index=>$context) {
                                if($context == $GLOBALS['phpgw_info']['server'] ["ldap_context"]) {
                                        $contexts[$index] = null;
                                        $justthese = array("dn");
                                        $filter="(objectClass=OrganizationalUnit)";
                                        $search = ldap_list($ldap_conn, $context, $filter, $justthese);
                                        $entries = ldap_get_entries($ldap_conn, $search);
                                        $contexts = array();
                                }
                        }
                        
			$filter="(phpgwAccountType=i)";
                        $justthese = array("dn");
                        $search = ldap_search($ldap_conn, $context, $filter, $justthese, 0, $sizelimit);
                        $entries = ldap_get_entries($ldap_conn, $search);
                        $total_count = $entries["count"];

			ldap_close($ldap_conn);

			return $total_count;
		}
		
		function get_num_users_sector($query, $contexts) {
			$dn			= $GLOBALS['phpgw_info']['server']['ldap_root_dn'];
			$passwd		= $GLOBALS['phpgw_info']['server']['ldap_root_pw'];
			$ldap_conn	= ldap_connect($GLOBALS['phpgw_info']['server']['ldap_host']);
			ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);
			ldap_bind($ldap_conn,$dn,$passwd);
			
			$filter="(phpgwAccountType=u)";
			$justthese = array("uidnumber");
			$count = 0;
			foreach ($contexts as $index=>$context) {
				$search = ldap_search($ldap_conn, $query, $filter, $justthese);
				$count+=ldap_count_entries($ldap_conn, $search);
			}
			return $count;
		}
		
		function get_list_user_sector_logon($query, $contexts,$sizelimit,$numacesso)
		{
			$dn			= $GLOBALS['phpgw_info']['server']['ldap_root_dn'];
			$passwd		= $GLOBALS['phpgw_info']['server']['ldap_root_pw'];
			$ldap_conn	= ldap_connect($GLOBALS['phpgw_info']['server']['ldap_host']);
			ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);
			ldap_bind($ldap_conn,$dn,$passwd);

			$filter="(phpgwAccountType=u)";
			$justthese = array("uidnumber", "uid", "cn", "mail","accountstatus","dn","createtimestamp");
			
			$tmp = array();
		
			foreach ($contexts as $index=>$context)
			{

				$field = 'cn';
				$search = ldap_search($ldap_conn, $query, $filter, $justthese, 0, $sizelimit);
				$info = ldap_get_entries($ldap_conn, $search);

				for ($i=0; $i < $info['count']; ++$i)
				{
					$access_log =  $this->show_access_log($info[$i]['uidnumber'][0]);

					$access_log_array = explode("#",$access_log);

					if ($access_log_array[1] >= $numacesso or $numacesso == $access_log_array[0]) {

						$tmp[$info[$i]['uid'][0]]['account_id']	 = $info[$i]['uidnumber'][0]; 
						$tmp[$info[$i]['uid'][0]]['account_lid'] = $info[$i]['uid'][0];
						$tmp[$info[$i]['uid'][0]]['account_cn']	 = $info[$i]['cn'][0];
						$tmp[$info[$i]['uid'][0]]['account_mail']= $info[$i]['mail'][0];
						$tmp[$info[$i]['uid'][0]]['account_accountstatus']= $info[$i]['accountstatus'][0];
						$tmp[$info[$i]['uid'][0]]['createtimestamp']= $info[$i]['createtimestamp'][0];					
						$sort[] = $info[$i]['uid'][0];
					}
				}
			}

			ldap_close($ldap_conn);
				
			if (count($sort))
			{
				natcasesort($sort);
				foreach ($sort as $user_uid)
					$return[$user_uid] = $tmp[$user_uid];
			}
				
			return $return;
		}
		
		function exist_account_lid($account_lid)
		{
			$conection = $GLOBALS['phpgw']->common->ldapConnect();
			$sri = ldap_search($conection, $GLOBALS['phpgw_info']['server']['ldap_context'], "uid=" . $account_lid);
			$result = ldap_get_entries($conection, $sri);
			return $result['count'];
		}
		
		function exist_email($mail)
		{
			$conection = $GLOBALS['phpgw']->common->ldapConnect();
			$sri = ldap_search($conection, $GLOBALS['phpgw_info']['server']['ldap_context'], "mail=" . $mail);
			$result = ldap_get_entries($conection, $sri);
			ldap_close($conection);
			
			if ($result['count'] == 0)
				return false;
			else
				return true;
		}
		
		function array_invert($array)
		{
			$result[] = end($array);
			while ($item = prev($array))
				$result[] = $item;
			return $result; 
		}

		function make_list_app($account_lid, $user_applications='', $disabled='')
		{
			// create list of ALL available apps
			$availableAppsGLOBALS = $GLOBALS['phpgw_info']['apps'];
			
			// create list of available apps for the user
			$query = "SELECT * FROM phpgw_expressoadmin_apps WHERE manager_lid = '".$account_lid."'";
			$GLOBALS['phpgw']->db->query($query);
			while($GLOBALS['phpgw']->db->next_record())
			{
				$availableApps[] = $GLOBALS['phpgw']->db->row();
			}
			
			// Retira alguns modulos
			if (count($availableApps))
			{
				foreach ($availableApps as $key => $value)
				{
					if ($value['app'] != 'phpgwapi')
						$tmp[] = $availableApps[$key];
				}
			}
			$availableApps = $tmp;
			
			// Cria um array com as aplicacoes disponiveis para o manager, com as atributos das aplicacoes.
			$availableAppsUser = array();
			if (count($availableApps))
			{
				foreach($availableApps as $app => $title)
				{
					if ($availableAppsGLOBALS[$title['app']])
						$availableAppsUser[$title['app']] = $availableAppsGLOBALS[$title['app']];
				}
			}
			
			// Loop para criar dinamicamente uma tabela com 3 colunas, cada coluna com um aplicativo e um check box.
			$applications_list = '';
			$app_col1 = '';
			$app_col2 = '';
			$app_col3 = '';
			$total_apps = count($availableAppsUser);
			$i = 0;
			foreach($availableAppsUser as $app => $data)
			{
				// 1 coluna 
				if (($i +1) % 3 == 1)
				{
					$checked = $user_applications[$app] ? 'CHECKED' : '';
					$app_col1 = sprintf("<td>%s</td><td width='10'><input type='checkbox' name='apps[%s]' value='1' %s %s></td>\n",
					$data['title'],$app,$checked, $disabled);
					if ($i == ($total_apps-1))
						$applications_list .= sprintf('<tr bgcolor="%s">%s</tr>','#DDDDDD', $app_col1);
				}
				
				// 2 coluna
				if (($i +1) % 3 == 2)
				{
					$checked = $user_applications[$app] ? 'CHECKED' : '';
					$app_col2 = sprintf("<td>%s</td><td width='10'><input type='checkbox' name='apps[%s]' value='1' %s %s></td>\n",
					$data['title'],$app,$checked, $disabled);
					
					if ($i == ($total_apps-1))
						$applications_list .= sprintf('<tr bgcolor="%s">%s%s</tr>','#DDDDDD', $app_col1,$app_col2);
				}
				// 3 coluna 
				if (($i +1) % 3 == 0)
				{
					$checked = $user_applications[$app] ? 'CHECKED' : '';
					$app_col3 = sprintf("<td>%s</td><td width='10'><input type='checkbox' name='apps[%s]' value='1' %s %s></td>\n",
					$data['title'],$app,$checked, $disabled);
					
					// Cria nova linha
					$applications_list .= sprintf('<tr bgcolor="%s">%s%s%s</tr>','#DDDDDD', $app_col1, $app_col2, $app_col3);					
				}
				++$i;
			}
			return $applications_list;
		}
		
		function exist_attribute_in_ldap($dn, $attribute, $value)
		{
			$connection = $GLOBALS['phpgw']->common->ldapConnect();
			$search = ldap_search($connection, $dn, $attribute. "=" . $value);
			$result = ldap_get_entries($connection, $search);
			ldap_close($connection);
			//_debug_array($result);
			if ($result['count'] == 0)
				return false;
			else
				return true;	
		}
		
		function lang($key)
		{
			if ($_SESSION['phpgw_info']['expressoAdmin']['lang'][$key])
				return $_SESSION['phpgw_info']['expressoAdmin']['lang'][$key];
			else
				return $key . '*';
		}
		
		function make_lang($ram_lang)
		{
			$a_lang = split("_", $ram_lang);
			$a_lang_reverse  = array_reverse ( $a_lang, true );
			array_pop ( $a_lang_reverse );
			$a_lang  = array_reverse ( $a_lang_reverse, true );
			$a_new_lang = implode ( " ", $a_lang );
			return lang($a_new_lang);
		}

		function make_dinamic_lang($template_obj, $block)
		{
			$tpl_vars = $template_obj->get_undefined($block);
			$array_langs = array();
			
			foreach ($tpl_vars as $atribute)
			{
				$lang = strstr($atribute, 'lang_');
				if($lang !== false)
				{
					//$template_obj->set_var($atribute, $this->make_lang($atribute));
					$array_langs[$atribute] = $this->make_lang($atribute);
				}
			}
			return $array_langs;
		}

		function paginate_usersgroups($query, $contexts, $field, $order = 'asc', $page = null, $perPage = null )
		{
			$dn			= $GLOBALS['phpgw_info']['server']['ldap_root_dn'];
			$passwd		= $GLOBALS['phpgw_info']['server']['ldap_root_pw'];
			$ldap_conn	= ldap_connect($GLOBALS['phpgw_info']['server']['ldap_host']);
			ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);
			ldap_bind($ldap_conn,$dn,$passwd);
			
			$filter="(&(phpgwAccountType=g)(cn=*))";
			$justthese = array("gidnumber", "cn", "description");										
							
			foreach ($contexts as $index=>$context)
			{				
				$search=ldap_search($ldap_conn, $query, $filter, $justthese);
	
				$rConnection = $ldap_conn;
				$rSearch = $search;
				$sOrder = $order;
				$iPage = $page;
				$iPerPage = $perPage;
				$sField = $field;
						
				$iTotalEntries = ldap_count_entries( $rConnection, $rSearch );
				
				if ( $iPage === null || $iPerPage === null )
				{
					# fetch all in one page
					$iStart = 0;
					$iEnd = $iTotalEntries - 1;
				}
				else
				{
					# calculate range of page
					$iFimPage = ( ceil( $iTotalEntries / $iPerPage ) - 1 ) * $iPage;
					
					$iStart = ( ceil( ($iPage -1) * $iPerPage ));
					$iEnd = $iPage * $iPerPage;

					if ( $sOrder === "desc" )
					{
						# revert range
						$iStart = $iTotalEntries - 1 - $iEnd;
						$iEnd = $iStart + $iPerPage - 1;
					}
				}
				
				/********* Importante Mostra o resultado da paginação **********
				var_dump( $iStart . " " . $iEnd );
				****************** Só descomentar ******************************/
				
				 # fetch entries
			    ldap_sort( $rConnection, $rSearch, $sField );

				$aList = array();
				for (
					$iCurrent = 0, $rEntry = ldap_first_entry( $rConnection, $rSearch );
					$iCurrent <= $iEnd && is_resource( $rEntry );
					++$iCurrent, $rEntry = ldap_next_entry( $rConnection, $rEntry )
					)
				{
					if ( $iCurrent >= $iStart )
					{
						array_push( $aList, ldap_get_attributes( $rConnection, $rEntry ));
					}
				}
			}

			ldap_close($ldap_conn);

			# if order is desc revert page's entries
			return $sOrder === "desc" ? array_reverse( $aList ) : $aList;
		}		
		
		function paginate_maillists($query, $contexts, $field, $order = 'asc', $page = null, $perPage = null )
		{
			$dn			= $GLOBALS['phpgw_info']['server']['ldap_root_dn'];
			$passwd		= $GLOBALS['phpgw_info']['server']['ldap_root_pw'];
			$ldap_conn	= ldap_connect($GLOBALS['phpgw_info']['server']['ldap_host']);
			ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);
			ldap_bind($ldap_conn,$dn,$passwd);
			
			$filter="(&(phpgwAccountType=l)(|(uid=*)))";
			$justthese = array("uidnumber", "cn", "uid", "mail");											
							
			foreach ($contexts as $index=>$context)
			{
				$search=ldap_search($ldap_conn, $query, $filter, $justthese);
	
				$rConnection = $ldap_conn;
				$rSearch = $search;
				$sOrder = $order;
				$iPage = $page;
				$iPerPage = $perPage;
				$sField = $field;
						
				$iTotalEntries = ldap_count_entries( $rConnection, $rSearch );
				
				if ( $iPage === null || $iPerPage === null )
				{
					# fetch all in one page
					$iStart = 0;
					$iEnd = $iTotalEntries - 1;
				}
				else
				{
					# calculate range of page
					$iFimPage = ( ceil( $iTotalEntries / $iPerPage ) - 1 ) * $iPage;
					
					$iStart = ( ceil( ($iPage -1) * $iPerPage ));
					$iEnd = $iPage * $iPerPage;

					if ( $sOrder === "desc" )
					{
						# revert range
						$iStart = $iTotalEntries - 1 - $iEnd;
						$iEnd = $iStart + $iPerPage - 1;
					}
				}
				
				/********* Importante Mostra o resultado da paginação **********
				var_dump( $iStart . " " . $iEnd );
				****************** Só descomentar ******************************/
				
				 # fetch entries
			    ldap_sort( $rConnection, $rSearch, $sField );

				$aList = array();
				for (
					$iCurrent = 0, $rEntry = ldap_first_entry( $rConnection, $rSearch );
					$iCurrent <= $iEnd && is_resource( $rEntry );
					++$iCurrent, $rEntry = ldap_next_entry( $rConnection, $rEntry )
					)
				{
					if ( $iCurrent >= $iStart )
					{
						array_push( $aList, ldap_get_attributes( $rConnection, $rEntry ));
					}
				}
			}

			ldap_close($ldap_conn);

			# if order is desc revert page's entries
			return $sOrder === "desc" ? array_reverse( $aList ) : $aList;
		}		
		
		function Paginate_user($type, $query, $contexts, $Field, $Order = 'asc', $Page = null, $PerPage = null )

		{
			$dn			= $GLOBALS['phpgw_info']['server']['ldap_root_dn'];
			$passwd		= $GLOBALS['phpgw_info']['server']['ldap_root_pw'];
			$ldap_conn	= ldap_connect($GLOBALS['phpgw_info']['server']['ldap_host']);
			ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);
			ldap_bind($ldap_conn,$dn,$passwd);
			
			$filter="(&(phpgwAccountType=u))";
			$justthese = array("uidnumber", "uid", "cn", "mail","accountstatus","dn","createtimestamp","telephoneNumber");											
							
			foreach ($contexts as $index=>$context)
			{
				$search=ldap_search($ldap_conn, $query, $filter, $justthese);
	
				$rConnection = $ldap_conn;
				$rSearch = $search;
				$sOrder = $Order;
				$iPage = $Page;
				$iPerPage = $PerPage;
				$sField = $Field;
						
				$iTotalEntries = ldap_count_entries( $rConnection, $rSearch );

				if ( $iPage === null || $iPerPage === null )
				{
					# fetch all in one page
					$iStart = 0;
					$iEnd = $iTotalEntries - 1;
				}
				else
				{
					# calculate range of page
					$iFimPage = ( ceil( $iTotalEntries / $iPerPage ) - 1 ) * $iPage;
					
					$iStart = ( ceil( ($iPage -1) * $iPerPage ));
					$iEnd = $iPage * $iPerPage;

					if ( $sOrder === "desc" )
					{
						# revert range
						$iStart = $iTotalEntries - 1 - $iEnd;
						$iEnd = $iStart + $iPerPage - 1;
					}
				}
				
				/********* Importante Mostra o resultado da paginação **********
				var_dump( $iStart . " " . $iEnd );
				****************** Só descomentar ******************************/
				
				 # fetch entries
			    ldap_sort( $rConnection, $rSearch, $sField );

				$aList = array();
				for (
					$iCurrent = 0, $rEntry = ldap_first_entry( $rConnection, $rSearch );
					$iCurrent <= $iEnd && is_resource( $rEntry );
					++$iCurrent, $rEntry = ldap_next_entry( $rConnection, $rEntry )
					)
				{
					if ( $iCurrent >= $iStart )
					{
						array_push( $aList, ldap_get_attributes( $rConnection, $rEntry ));
					}
				}
			}

			ldap_close($ldap_conn);

			# if order is desc revert page's entries
			return $sOrder === "desc" ? array_reverse( $aList ) : $aList;
		}

		function Paginate_cota($type, $query, $contexts, $Field, $Order = 'asc', $Page = null, $PerPage = null )
                {
                        $dn                     = $GLOBALS['phpgw_info']['server']['ldap_root_dn'];
                        $passwd         = $GLOBALS['phpgw_info']['server']['ldap_root_pw'];
                        $ldap_conn      = ldap_connect($GLOBALS['phpgw_info']['server']['ldap_host']);
                        ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
                        ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);
                        ldap_bind($ldap_conn,$dn,$passwd);

                        $filter="(|(phpgwAccountType=u)(|(phpgwAccountType=s)))";
                        $justthese = array("uidnumber", "uid", "cn", "mail","accountstatus","dn","createtimestamp","telephoneNumber");                                 

                        foreach ($contexts as $index=>$context)
                        {
                                $search=ldap_search($ldap_conn, $query, $filter, $justthese);

                                $rConnection = $ldap_conn;
                                $rSearch = $search;
                                $sOrder = $Order;
                                $iPage = $Page;
                                $iPerPage = $PerPage;
                                $sField = $Field;

                                $iTotalEntries = ldap_count_entries( $rConnection, $rSearch );

                                if ( $iPage === null || $iPerPage === null )
                                {
                                        # fetch all in one page
                                        $iStart = 0;
                                        $iEnd = $iTotalEntries - 1;
                                }
                                else
                                {
                                        # calculate range of page
                                        $iFimPage = ( ceil( $iTotalEntries / $iPerPage ) - 1 ) * $iPage;

                                        $iStart = ( ceil( ($iPage -1) * $iPerPage ));
                                        $iEnd = $iPage * $iPerPage;


                                        if ( $sOrder === "desc" )
                                        {
                                                # revert range
                                                $iStart = $iTotalEntries - 1 - $iEnd;
                                                $iEnd = $iStart + $iPerPage - 1;
                                        }
                                }

                                /********* Importante Mostra o resultado da paginação **********
                                var_dump( $iStart . " " . $iEnd );
                                ****************** Só descomentar ******************************/

                                 # fetch entries
	                        ldap_sort( $rConnection, $rSearch, $sField );

                                $aList = array();
                                for (
                                        $iCurrent = 0, $rEntry = ldap_first_entry( $rConnection, $rSearch );
                                        $iCurrent <= $iEnd && is_resource( $rEntry );
                                        ++$iCurrent, $rEntry = ldap_next_entry( $rConnection, $rEntry )
                                        )
                                {
                                        if ( $iCurrent >= $iStart )
                                        {
                                                array_push( $aList, ldap_get_attributes( $rConnection, $rEntry ));
                                        }
                                }
                        }

                        ldap_close($ldap_conn);

                        # if order is desc revert page's entries
                        return $sOrder === "desc" ? array_reverse( $aList ) : $aList;
                }

                function Paginate_shareAccount($type, $query, $contexts, $Field, $Order = 'asc', $Page = null, $PerPage = null )

                {
                        $dn                     = $GLOBALS['phpgw_info']['server']['ldap_root_dn'];
                        $passwd         = $GLOBALS['phpgw_info']['server']['ldap_root_pw'];
                        $ldap_conn      = ldap_connect($GLOBALS['phpgw_info']['server']['ldap_host']);
                        ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
                        ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);
                        ldap_bind($ldap_conn,$dn,$passwd);

                        $filter="(phpgwAccountType=s)";
                        $justthese = array("uid", "cn", "mail","accountstatus");                                 

                        foreach ($contexts as $index=>$context)
                        {
                                $search=ldap_search($ldap_conn, $query, $filter, $justthese);

                                $rConnection = $ldap_conn;
                                $rSearch = $search;
                                $sOrder = $Order;
                                $iPage = $Page;
                                $iPerPage = $PerPage;
                                $sField = $Field;

                                $iTotalEntries = ldap_count_entries( $rConnection, $rSearch );

                                if ( $iPage === null || $iPerPage === null )
                                {
                                        # fetch all in one page
                                        $iStart = 0;
                                        $iEnd = $iTotalEntries - 1;
                                }
                                else
                                {
                                        # calculate range of page
                                        $iFimPage = ( ceil( $iTotalEntries / $iPerPage ) - 1 ) * $iPage;

                                        $iStart = ( ceil( ($iPage -1) * $iPerPage ));
                                        $iEnd = $iPage * $iPerPage;

                                        if ( $sOrder === "desc" )
                                        {
                                                # revert range
                                                $iStart = $iTotalEntries - 1 - $iEnd;
                                                $iEnd = $iStart + $iPerPage - 1;
                                        }
                                }

                                 # fetch entries
				ldap_sort( $rConnection, $rSearch, $sField );

                                $aList = array();
                                for (
                                        $iCurrent = 0, $rEntry = ldap_first_entry( $rConnection, $rSearch );
                                        $iCurrent <= $iEnd && is_resource( $rEntry );
                                        ++$iCurrent, $rEntry = ldap_next_entry( $rConnection, $rEntry )
                                        )
                                {
                                        if ( $iCurrent >= $iStart )
                                        {
                                                array_push( $aList, ldap_get_attributes( $rConnection, $rEntry ));
                                        }
                                }
                        }

                        ldap_close($ldap_conn);

                        # if order is desc revert page's entries
                        return $sOrder === "desc" ? array_reverse( $aList ) : $aList;
                }

                function Paginate_institutionalAccount($type, $query, $contexts, $Field, $Order = 'asc', $Page = null, $PerPage = null )
                {
                        $dn                     = $GLOBALS['phpgw_info']['server']['ldap_root_dn'];
                        $passwd         = $GLOBALS['phpgw_info']['server']['ldap_root_pw'];
                        $ldap_conn      = ldap_connect($GLOBALS['phpgw_info']['server']['ldap_host']);
                        ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
                        ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);
                        ldap_bind($ldap_conn,$dn,$passwd);

                        $filter="(phpgwAccountType=i)";
                        $justthese = array("uid", "cn", "mail","accountstatus","mailforwardingaddress");

                        foreach ($contexts as $index=>$context)
                        {
                                $search=ldap_search($ldap_conn, $query, $filter, $justthese);

                                $rConnection = $ldap_conn;
                                $rSearch = $search;
                                $sOrder = $Order;
                                $iPage = $Page;
                                $iPerPage = $PerPage;
                                $sField = $Field;

                                $iTotalEntries = ldap_count_entries( $rConnection, $rSearch );

                                if ( $iPage === null || $iPerPage === null )
                                {
                                        # fetch all in one page
                                        $iStart = 0;
                                        $iEnd = $iTotalEntries - 1;
                                }
                                else
                                {
                                        # calculate range of page
                                        $iFimPage = ( ceil( $iTotalEntries / $iPerPage ) - 1 ) * $iPage;

                                        $iStart = ( ceil( ($iPage -1) * $iPerPage ));
                                        $iEnd = $iPage * $iPerPage;

                                        if ( $sOrder === "desc" )
                                        {
                                                # revert range
                                                $iStart = $iTotalEntries - 1 - $iEnd;
                                                $iEnd = $iStart + $iPerPage - 1;
                                        }
                                }

                                 # fetch entries
                                ldap_sort( $rConnection, $rSearch, $sField );

                                $aList = array();
                                for (
                                        $iCurrent = 0, $rEntry = ldap_first_entry( $rConnection, $rSearch );
                                        $iCurrent <= $iEnd && is_resource( $rEntry );
                                        ++$iCurrent, $rEntry = ldap_next_entry( $rConnection, $rEntry )
                                        )
                                {
                                        if ( $iCurrent >= $iStart )
                                        {
                                                array_push( $aList, ldap_get_attributes( $rConnection, $rEntry ));
                                        }
                                }
                        }

                        ldap_close($ldap_conn);

                        # if order is desc revert page's entries
                        return $sOrder === "desc" ? array_reverse( $aList ) : $aList;
                }


		function get_list_ou_user_logon($query,$contexts,$sizelimit)
		{
			$dn			= $GLOBALS['phpgw_info']['server']['ldap_root_dn'];
			$passwd		= $GLOBALS['phpgw_info']['server']['ldap_root_pw'];
			$ldap_conn	= ldap_connect($GLOBALS['phpgw_info']['server']['ldap_host']);
			ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);
			ldap_bind($ldap_conn,$dn,$passwd);

			$filter="(&(phpgwAccountType=u)(|(uid=*".$query."*)))";
			$justthese = array("ou");											
			$tmp = array();
		
			foreach ($contexts as $index=>$context)
			{

				$field = 'cn';
				$search=ldap_search($ldap_conn, $context, $filter, $justthese, 0, $sizelimit);
				$info = ldap_get_entries($ldap_conn, $search);

				for ($i=0; $i < $info['count']; ++$i)
				{
					$a_sectors[] = $info[$i]['dn'];
				}
			}
			
			ldap_close($ldap_conn);
				
			// Retiro o count do array info e inverto o array para ordenação.
	        foreach ($a_sectors as $context)
    	    {
				$array_dn = ldap_explode_dn ( $context, 1 );

                $array_dn_reverse  = array_reverse ( $array_dn, true );

				// Retirar o indice count do array.
				array_pop ( $array_dn_reverse );

				$inverted_dn[$context] = implode ( "#", $array_dn_reverse );
			}

			// Ordenação
			natcasesort($inverted_dn);

			// Construção do select
			$level = 0;
			$options = array();
			foreach ($inverted_dn as $dn=>$invert_ufn)
			{
                $display = '';

                $array_dn_reverse = explode ( "#", $invert_ufn );
                $array_dn  = array_reverse ( $array_dn_reverse, true );

                $level = count( $array_dn ) - (int)(count(explode(",", $GLOBALS['phpgw_info']['server']['ldap_context'])) + 1);

				$display.= ' ';
				reset ( $array_dn );
				$display .= ' ' . ($array_dn[$level]);
				$dn = trim(strtolower($dn));
				$options[$dn] = $display;
        	}

    	    return $options;
		}

		function get_list_context_logon($query_user,$contexts,$sizelimit)
		{
			$dn			= $GLOBALS['phpgw_info']['server']['ldap_root_dn'];
			$passwd		= $GLOBALS['phpgw_info']['server']['ldap_root_pw'];
			$ldap_conn	= ldap_connect($GLOBALS['phpgw_info']['server']['ldap_host']);
			ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);
			ldap_bind($ldap_conn,$dn,$passwd);

			// Le BD para pegar os administradors.
			$query = "SELECT manager_lid,context FROM phpgw_expressoadmin WHERE manager_lid='".$query_user."' ORDER by manager_lid";
			$GLOBALS['phpgw']->db->query($query);
			while($GLOBALS['phpgw']->db->next_record())
			{
				$managers[] = $GLOBALS['phpgw']->db->row();
			}

			// Loop para listar as dn
			if (count($managers))
			{
				foreach($managers as $array_managers)
				{
					$display = '';
					$managers_context = "";
					$a_managers_context = explode("%", $array_managers['context']);

					// Ordenação
					natcasesort($a_managers_context);

					$options = array();
					$level = 0;
												
					foreach ($a_managers_context as $dn=>$invert_ufn)
					{
						$dn_explode = explode (",",$invert_ufn);

						// Construção do select
						$array_dn  = array_reverse ( $dn_explode, true );

						$level = count( $dn_explode ) - (int)(count(explode(",", $GLOBALS['phpgw_info']['server']['ldap_context'])) + 1);
						$display = ' ';
						reset ( $array_dn );
//						$display = str_replace("ou=", "",($array_dn[$level]));
						$display = str_replace("ou=", "",($array_dn[0]));
						$display = str_replace("dc=", "",($display));
						$dn = trim(strtolower($dn));
						$options[$dn] = trim(strtoupper($display));
					}
				}
			}

   	    	return $options;
		}

		function get_list_groups_dn($query_user,$contexts,$sizelimit)
		{
			$dn			= $GLOBALS['phpgw_info']['server']['ldap_root_dn'];
			$passwd		= $GLOBALS['phpgw_info']['server']['ldap_root_pw'];
			$ldap_conn	= ldap_connect($GLOBALS['phpgw_info']['server']['ldap_host']);
			ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);
			ldap_bind($ldap_conn,$dn,$passwd);

			// Le BD para pegar os administradors.
			$query = "SELECT manager_lid,context FROM phpgw_expressoadmin WHERE manager_lid='".$query_user."' ORDER by manager_lid";
			$GLOBALS['phpgw']->db->query($query);
			while($GLOBALS['phpgw']->db->next_record())
			{
				$managers[] = $GLOBALS['phpgw']->db->row();
			}

			// Loop para listar as dn
			if (count($managers))
			{
				foreach($managers as $array_managers)
				{
					$display = '';
					$managers_context = "";
					$options = explode("%", $array_managers['context']);

					// Ordenação
					natcasesort($options);
				}
			}

   	    	return $options;
		}

		function sort_by($field, &$arr, $sorting=SORT_ASC, $case_insensitive=true){
			if(is_array($arr) && (count($arr)>0) && ( ( is_array($arr[0]) && isset($arr[0][$field]) ) || ( is_object($arr[0]) && isset($arr[0]->$field) ) ) ){
				if($case_insensitive==true) $strcmp_fn = "strnatcasecmp";
				else $strcmp_fn = "strnatcmp";
		
				if($sorting==SORT_ASC){
					$fn = create_function('$a,$b', '
						if(is_object($a) && is_object($b)){
							return '.$strcmp_fn.'($a->'.$field.', $b->'.$field.');
						}else if(is_array($a) && is_array($b)){
							return '.$strcmp_fn.'($a["'.$field.'"], $b["'.$field.'"]);
						}else return 0;
					');
				}else{
					$fn = create_function('$a,$b', '
						if(is_object($a) && is_object($b)){
							return '.$strcmp_fn.'($b->'.$field.', $a->'.$field.');
						}else if(is_array($a) && is_array($b)){
							return '.$strcmp_fn.'($b["'.$field.'"], $a["'.$field.'"]);
						}else return 0;
					');
				}
				usort($arr, $fn);
				return true;
			}else{
				return false;
			}
		}	

		function show_access_log($account_id)
		{	
			$manager_account_lid = $GLOBALS['phpgw']->accounts->data['account_lid'];
			$tmp = $this->read_acl($manager_account_lid);
			$manager_context = $tmp[0]['context'];
			
			// Verifica se tem acesso a este modulo
			if ((!$this->check_acl($manager_account_lid,'edit_users')) && (!$this->check_acl($manager_account_lid,'change_users_password')))
			{
				$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/reports/inc/access_denied.php'));
			}

			// Le BD para pegar os [li].
			$query = "select li from phpgw_access_log WHERE account_id=".$account_id." order by li desc LIMIT 1 OFFSET 0";
			$GLOBALS['phpgw']->db->query($query);
			while($GLOBALS['phpgw']->db->next_record())
			{
				$managers[] = $GLOBALS['phpgw']->db->row();
			}

			if (count($managers))
			{
				// contar intervalo
				 $data_atual = date("Y/m/d", time());

				 $data_antes = date("Y/m/d",$managers['0']['li']);
				
				 $datainicio=strtotime("$data_antes"); // Data de Hoje
				 $datafim =strtotime("$data_atual"); // Data no próximo ano
				
				 $rdata =($datafim-$datainicio)/86400; //transformação do timestamp em dias 

				$access_li = date("d/m/Y",$managers['0']['li'])."#".$rdata;

				return $access_li;

			}else{

				$access_li = "Nunca logou#0"; 

				return $access_li;
			}
		}

		function Paginate_user_logon($type, $query, $contexts, $Field, $Order = 'asc', $Page = null, $PerPage = null, $numacesso)
		{
			$dn			= $GLOBALS['phpgw_info']['server']['ldap_root_dn'];
			$passwd		= $GLOBALS['phpgw_info']['server']['ldap_root_pw'];
			$ldap_conn	= ldap_connect($GLOBALS['phpgw_info']['server']['ldap_host']);
			ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);
			ldap_bind($ldap_conn,$dn,$passwd);
			
			$filter="(&(phpgwAccountType=u)(|(uid=*)))";
			$justthese = array("uidnumber", "uid", "cn", "mail","accountstatus","dn","createtimestamp");											
							
			foreach ($contexts as $index=>$context)
			{
				$search=ldap_search($ldap_conn, $query, $filter, $justthese);
	
				$rConnection = $ldap_conn;
				$rSearch = $search;
				$sOrder = $Order;
				$iPage = $Page;
				$iPerPage = $PerPage;
				$sField = $Field;
						
				$iTotalEntries = ldap_count_entries( $rConnection, $rSearch );

				if ( $iPage === null || $iPerPage === null )
				{
					# fetch all in one page
					$iStart = 0;
					$iEnd = $iTotalEntries - 1;
				}
				else
				{
					# calculate range of page
					$iFimPage = ( ceil( $iTotalEntries / $iPerPage ) - 1 ) * $iPage;
					
					$iStart = ( ceil( ($iPage -1) * $iPerPage ));
					$iEnd = $iPage * $iPerPage;

					if ( $sOrder === "desc" )
					{
						# revert range
						$iStart = $iTotalEntries - 1 - $iEnd;
						$iEnd = $iStart + $iPerPage - 1;
					}
				}
				
				/********* Importante Mostra o resultado da paginação **********
				var_dump( $iStart . " " . $iEnd );
				****************** Só descomentar ******************************/
				
				 # fetch entries
			    ldap_sort( $rConnection, $rSearch, $sField );

				$aList = array();
				for (
					$iCurrent = 0, $rEntry = ldap_first_entry( $rConnection, $rSearch );
					$iCurrent <= $iEnd && is_resource( $rEntry );
					++$iCurrent, $rEntry = ldap_next_entry( $rConnection, $rEntry )
					)
				{
					if ( $iCurrent >= $iStart )
					{
						array_push( $aList, ldap_get_attributes( $rConnection, $rEntry ));
					}
				}
			}

			ldap_close($ldap_conn);

			# if order is desc revert page's entries
			return $sOrder === "desc" ? array_reverse( $aList ) : $aList;
		}
	}
// ****************** fim classe Functions ***********************
?>