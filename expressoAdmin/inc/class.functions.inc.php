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
	
	include_once('class.db_functions.inc.php');
	include_once(PHPGW_API_INC.'/class.config.inc.php');
	
	class functions
	{
		var $public_functions = array
		(
			'make_array_acl' 	=> True,
			'check_acl'			=> True,
			'read_acl'			=> True,
			'exist_account_lid'	=> True,
			'exist_email'		=> True,
			'array_invert'		=> True
		);
		
		var $nextmatchs;
		var $sectors_list = array();
		var $current_config;
		
		function functions()
		{
			$this->db_functions = new db_functions;
			$GLOBALS['phpgw']->db = $this->db_functions->db;
			
			//$c = CreateObject('phpgwapi.config','expressoAdmin');
			$c = new config;
			$c->read_repository();
			$this->current_config = $c->config_data;
		}

        function write_log($action, $about)
        {
            return $this->db_functions->write_log($action, $about);
        }
		
		// Account and type of access. Return: Have access ? (true/false)
		function check_acl($account_lid, $access)
		{
			$array_acl =  $this->db_functions->read_acl($account_lid);		
			switch($access)
			{
				case 'list_users':
					if ($array_acl['acl_add_users'] || $array_acl['acl_edit_users'] || $array_acl['acl_delete_users'] || $array_acl['acl_change_users_password'] || $array_acl['acl_change_users_quote'] || $array_acl['acl_edit_sambausers_attributes'] || $array_acl['acl_view_users'] || $array_acl['acl_manipulate_corporative_information'] || $array_acl['acl_edit_users_phonenumber'] )
						return true;
					break;
				case 'list_groups':
					if ($array_acl['acl_add_groups'] || $array_acl['acl_edit_groups'] || $array_acl['acl_delete_groups'])
						return true;
					break;
				case 'list_maillists':
					if ($array_acl['acl_add_maillists'] || $array_acl['acl_edit_maillists'] || $array_acl['acl_delete_maillists'])
						return true;
					break;
				case 'list_sectors':
					if ($array_acl['acl_create_sectors'] || $array_acl['acl_edit_sectors'] || $array_acl['acl_delete_sectors'])
						return true;
					break;
				case 'list_computers':
					if ($array_acl['acl_create_computers'] || $array_acl['acl_edit_computers'] || $array_acl['acl_delete_computers'])
						return true;
					break;

				case 'display_groups':
					if ( $array_acl['acl_edit_users'] || $array_acl['acl_view_users'] || ($array_acl['acl_edit_sambausers_attributes'] && ($this->current_config['expressoAdmin_samba_support'] == 'true')) )
						return true;
					break;
				case 'display_emailconfig':
					if ($array_acl['acl_edit_users'] || $array_acl['acl_view_users'])
						return true;
					break;
				case 'display_applications':
					if ($array_acl['acl_edit_users'] || $array_acl['acl_view_users'])
						return true;
					break;
				case 'display_emaillists':
					if ($array_acl['acl_edit_users'] || $array_acl['acl_view_users'])
						return true;
					break;

				case 'list_institutional_accounts':
					if ($array_acl['acl_add_institutional_accounts'] || $array_acl['acl_edit_institutional_accounts'] || $array_acl['acl_delete_institutional_accounts'])
						return true;
                    break;
                case 'list_shared_accounts':
					if ($array_acl['acl_add_shared_accounts'] || $array_acl['acl_edit_shared_accounts'] || $array_acl['acl_delete_shared_accounts'])
						return true;
					break;
                case 'configurations':
					if ($array_acl['acl_active_blocking_sending_email_to_shared_accounts'] || $array_acl['acl_add_blocking_sending_email_to_shared_accounts_exception'] || $array_acl['acl_edit_and_remove_blocking_sending_email_to_shared_accounts_exception'] || $array_acl['acl_edit_maximum_number_of_recipients_generally'] || $array_acl['acl_add_maximum_number_of_recipients_by_user'] || $array_acl['acl_edit_and_remove_maximum_number_of_recipients_by_user'] || $array_acl['acl_add_maximum_number_of_recipients_by_group'] || $array_acl['acl_edit_and_remove_maximum_number_of_recipients_by_group'])
						return true;
					break;
				case 'messages_size':
					if($array_acl['acl_add_messages_size_rule'] || $array_acl['acl_edit_messages_size_rule'] || $array_acl['acl_remove_messages_size_rule'])
						return true;
					break;
				default:
					return ( isset($array_acl["acl_$access"]) ? $array_acl["acl_$access"] : false );
			}
			return false;
		}

		/* OLD FUNCTION
		function check_acl($account_lid, $access)
		{
			$acl = $this->read_acl($account_lid);
			$array_acl = $this->make_array_acl($acl['acl']);
			
			//What access ?? In the IF, verify if have access.
			switch($access)
			{
				case list_users:
					if ($array_acl['acl_add_users'] || $array_acl['acl_edit_users'] || $array_acl['acl_delete_users'] || $array_acl['acl_change_users_password'] || $array_acl['acl_change_users_quote'] || $array_acl['acl_edit_sambausers_attributes'] || $array_acl['acl_view_users'] || $array_acl['acl_manipulate_corporative_information'])
						return true;
					break;
				case add_users:
					if ($array_acl['acl_add_users'])
						return true;
					break;
				case edit_users:
					if ($array_acl['acl_edit_users'])
						return true;
					break;
				case delete_users:
					if ($array_acl['acl_delete_users'])
						return true;
					break;
				case rename_users:
					if ($array_acl['acl_rename_users'])
						return true;
					break;
				case view_users:
					if ($array_acl['acl_view_users'])
						return true;
					break;
				case edit_users_picture:
					if ($array_acl['acl_edit_users_picture'])
						return true;
					break;
				case manipulate_corporative_information:
					if ($array_acl['acl_manipulate_corporative_information'])
						return true;
					break;
				case change_users_password:
					if ($array_acl['acl_change_users_password'])
						return true;
					break;
				case change_users_quote:
					if ($array_acl['acl_change_users_quote'])
						return true;
					break;
				case set_user_default_password:
					if ($array_acl['acl_set_user_default_password'])
						return true;
					break;
				case empty_user_inbox:
					if (($array_acl['acl_empty_user_inbox']) && ($array_acl['acl_edit_users']))
						return true;
					break;
				case edit_sambausers_attributes:				case list_maillists:
					if ($array_acl['acl_add_maillists'] || $array_acl['acl_edit_maillists'] || $array_acl['acl_delete_maillists'])
						return true;
					break;

					if ($array_acl['acl_edit_sambausers_attributes'])
						return true;
					break;
				case edit_sambadomains:
					if ($array_acl['acl_edit_sambadomains'])
						return true;
					break;
				
				case list_groups:
					if ($array_acl['acl_add_groups'] || $array_acl['acl_edit_groups'] || $array_acl['acl_delete_groups'])
						return true;
					break;
				case add_groups:
					if ($array_acl['acl_add_groups'])
						return true;
					break;
				case edit_groups:
					if ($array_acl['acl_edit_groups'])
						return true;
					break;
				case delete_groups:
					if ($array_acl['acl_delete_groups'])
						return true;
					break;
				case edit_email_groups:
					if ($array_acl['acl_edit_email_groups'])
						return true;
					break;
				
				case list_maillists:
					if ($array_acl['acl_add_maillists'] || $array_acl['acl_edit_maillists'] || $array_acl['acl_delete_maillists'])
						return true;
					break;
				case add_maillists:
					if ($array_acl['acl_add_maillists'])
						return true;
					break;
				case edit_maillists:
					if ($array_acl['acl_edit_maillists'])
						return true;
					break;
				case delete_maillists:
					if ($array_acl['acl_delete_maillists'])
						return true;
					break;

				case list_sectors:
					if ($array_acl['acl_create_sectors'] || $array_acl['acl_edit_sectors'] || $array_acl['acl_delete_sectors'])
						return true;
					break;
				case create_sectors:
					if ($array_acl['acl_create_sectors'])
						return true;
					break;
				case edit_sectors:
					if ($array_acl['acl_edit_sectors'])
						return true;
					break;
				case delete_sectors:
					if ($array_acl['acl_delete_sectors'])
						return true;
					break;

				case view_global_sessions:
					if ($array_acl['acl_view_global_sessions'])
						return true;
					break;

				case list_computers:
					if ($array_acl['acl_create_computers'] || $array_acl['acl_edit_computers'] || $array_acl['acl_delete_computers'])
						return true;
					break;
				case create_computers:
					if ($array_acl['acl_create_computers'])
						return true;
					break;
				case edit_computers:
					if ($array_acl['acl_edit_computers'])
						return true;
					break;
				case delete_computers:
					if ($array_acl['acl_delete_computers'])
						return true;
					break;

				case view_logs:
					if ($array_acl['acl_view_logs'])
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

				default:
					return $array_acl["acl_$access"];
			}
			return false;
		}
		*/
		
		// Read acl from db
		function read_acl($account_lid)
		{ 
			$acl = $this->db_functions->read_acl($account_lid);
			return $acl;
			}
			
		
		function get_inactive_users($contexts) {
			$retorno = array();
			$tempUsers = array();
			//Pego no LDAP todos os usuários dos contextos em questão.
			$usuariosLdap = $this->get_list('accounts','',$contexts);
			foreach($usuariosLdap as $usuarioLdap) {
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
        		return '1';
	        else
	        	return '0';
		}
		
		function get_list($type, $query, $contexts)
		{
			$dn			= $GLOBALS['phpgw_info']['server']['ldap_root_dn'];
			$passwd		= $GLOBALS['phpgw_info']['server']['ldap_root_pw'];
			$ldap_conn	= ldap_connect($GLOBALS['phpgw_info']['server']['ldap_host']);
			$return		= "";
			$sort		= array();

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
				
				if( isset($sort) )
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
				{
					$return[$maillist_uid] = $tmp[$maillist_uid];
				}
				
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
 		                 
                /*  
                        Funciona de maneira similar ao get_sectors_list, porém retorna a propria OU do contexto 
                        e monta o array de retorno de forma diferente, necessário para algumas mudanças implementadas 
                        no método admin.uisectors.list_sectors. 
                */ 
                function get_organizations2($contexts, $selected='', $referral=false, $show_invisible_ou=true) {                 
 
                        $dn                     = $GLOBALS['phpgw_info']['server']['ldap_root_dn']; 
                        $passwd         = $GLOBALS['phpgw_info']['server']['ldap_root_pw']; 
                        $ldap_conn      = ldap_connect($GLOBALS['phpgw_info']['server']['ldap_host']); 
                                                 
                        ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3); 
                         
                        if ($referral) 
                                ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 1); 
                        else 
                                ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0); 
                         
                        ldap_bind($ldap_conn,$dn,$passwd); 
                         
                        $justthese = array("dn","diskQuota","usersQuota","actualDiskQuota"); 
                        $filter = "(ou=*)"; 
                        foreach ($contexts as $context) { 
                                $search=ldap_search($ldap_conn, $context, $filter, $justthese); 
                                 
                                ldap_sort($ldap_conn, $search, "ou"); 
                                $info = ldap_get_entries($ldap_conn, $search); 
                                ldap_close($ldap_conn); 
         
                                // Retiro o count do array info e inverto o array para ordenaçãoo. 
                                for ($i=0; $i<$info["count"]; ++$i)
                                { 
                                        $dn = $info[$i]["dn"]; 
                                         
                                        // Necessário, pq em uma busca com ldapsearch ou=*, traz tb o próprio ou.  
                                        //if (strtolower($dn) == $context) 
                                                //continue; 
         
                                        $array_dn = ldap_explode_dn ( $dn, 1 ); 
         
                                        $array_dn_reverse  = array_reverse ( $array_dn, true ); 
         
                                        // Retirar o indice count do array. 
                                        array_pop ( $array_dn_reverse ); 
         
                                        $inverted_dn[implode ( "#", $array_dn_reverse )] = $info[$i]; 
                                } 
                        } 
                        // Ordenação por chave 
                        ksort($inverted_dn);                     
                         
                        // Construção do select 
                        $level = 0; 
                        $options = array(); 
                        foreach ($inverted_dn as $dn=>$info_ou) 
                        { 
                $display = ''; 
                                $info_retorno = array(); 
                $array_dn_reverse = explode ( "#", $dn ); 
                $array_dn  = array_reverse ( $array_dn_reverse, true ); 
 
                $level = count( $array_dn ) - (int)(count(explode(",", $GLOBALS['phpgw_info']['server']['ldap_context'])) + 1); 
 
                if ($level == 0) 
                        $display .= '+'; 
                else  
                { 
                                        for ($i=0; $i<$level; ++$i)
                                                $display .= '---'; 
                } 
 
                reset ( $array_dn ); 
                $display .= ' ' . urldecode( str_replace('\\', '%', current ( $array_dn ))); 
                                 
                                $info_retorno['display'] = $display; 
                                $info_retorno['dn'] = $info_ou['dn']; 
                                $info_retorno['diskquota'] = (isset($info_ou['diskquota'][0]) ? $info_ou['diskquota'][0] : "" ); 
                                $info_retorno['usersquota'] = (isset($info_ou['usersquota'][0]) ? $info_ou['usersquota'][0] : "" );
                                array_push($options,$info_retorno); 
                                 
                } 
                        return $options; 
                }        
                 
                function get_info($context, $referral = false) { 
                        $dn                     = $GLOBALS['phpgw_info']['server']['ldap_root_dn']; 
                        $passwd         = $GLOBALS['phpgw_info']['server']['ldap_root_pw']; 
                        $ldap_conn      = ldap_connect($GLOBALS['phpgw_info']['server']['ldap_host']); 
                         
                        ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3); 
                         
                        if ($referral) 
                                ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 1); 
                        else 
                                ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0); 
                         
                        ldap_bind($ldap_conn,$dn,$passwd); 
                         
                        $filter="(objectClass=organizationalUnit)"; 
                        $search=ldap_search($ldap_conn, $context, $filter); 
                        $result = ldap_get_entries($ldap_conn, $search); 
                        return $result; 
                                 
                } 
 
                function get_num_users($context,$selected='', $referral=false, $show_invisible_ou=true) { 
                        $dn                     = $GLOBALS['phpgw_info']['server']['ldap_root_dn']; 
                        $passwd         = $GLOBALS['phpgw_info']['server']['ldap_root_pw']; 
                        $ldap_conn      = ldap_connect($GLOBALS['phpgw_info']['server']['ldap_host']); 
                         
                        ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3); 
                         
                        if ($referral) 
                                ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 1); 
                        else 
                                ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0); 
                         
                        ldap_bind($ldap_conn,$dn,$passwd); 
                         
                        $justthese = array("dn"); 
                        $filter = "(objectClass=inetOrgPerson)"; 
                        $search=ldap_search($ldap_conn, $context, $filter, $justthese); 
                 
                $retorno = ldap_count_entries($ldap_conn, $search); 
                        ldap_close($ldap_conn); 
                         
                        return $retorno; 
                } 
                 
                //Checa se existe quota para mais um usuï¿½rio no setor... se existir retorna true, senï¿½o false.               
                function existe_quota_usuario($setor) { 
                        $num_users = $this->get_num_users($setor['dn']); 
                        //return $num_users . " --- " . $setor['usersquota'][0] 
                        if(($num_users>=$setor['usersquota'][0]) && ($setor['usersquota'][0]!=-1)) { 
                                return false; 
                        } 
                        return true; 
                } 
                 
                //Checa se existe quota em disco para mais um usuï¿½rio no setor... se existir retorna true, senï¿½o false. 
                function existe_quota_disco($setor,$quota_novo_usuario) { 
                        settype($quota_novo_usuario,"float");            
                        $quota_novo_usuario /= 1024; //A quota vï¿½m da interface em megabytes, deve se tornar gigabyte. 
 
                        $nova_quota = $this->get_actual_disk_usage($setor['dn']) + $quota_novo_usuario; 
                        if(( $nova_quota >= $setor['diskquota'][0] ) && ($setor['diskquota'][0] != -1)) { 
                                return false; 
                        } 
                        return true; 
                } 
                 
                // Soma as quotas de todos os usuï¿½rios daquele contexto. 
                function get_actual_disk_usage($context) { 
                        $quota_usada=0; 
                        $contexts = array($context); 
                        $usuarios = $this->get_list('accounts', '', $contexts); 
 
                        $imap_functions = new imap_functions(); 
                        foreach($usuarios as $usuario) { 
                                $temp = $imap_functions->get_user_info($usuario['account_lid']); 
                                if($temp['mailquota'] != -1) //Usuï¿½rio sem cota nï¿½o conta... 
                                        $quota_usada += ($temp['mailquota'] / 1024); 
                        } 
                        return $quota_usada; 
                } 


		function get_sectors($selected='', $referral=false, $show_invisible_ou=true)
		{
			$s = CreateObject('phpgwapi.sector_search_ldap');
			$sectors_info = $s->get_sectors($selected, $referral, $show_invisible_ou);
			return $sectors_info;
		}		
 
		// Get list of all levels, this function is used for sectors module.
		function get_sectors_list($contexts)
		{
			$a_sectors = array();
			
			$dn			= $GLOBALS['phpgw_info']['server']['ldap_root_dn'];
			$passwd		= $GLOBALS['phpgw_info']['server']['ldap_root_pw'];
			$ldap_conn	= ldap_connect($GLOBALS['phpgw_info']['server']['ldap_host']);
			
			ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);
			ldap_bind($ldap_conn,$dn,$passwd);
			
			$justthese = array("dn");
			$filter = "(ou=*)";
			
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


				$array_dn = ldap_explode_dn($context, 1 );
				foreach($array_dn as $key=>$value){
					$array_dn[$key]=preg_replace('/\\\([0-9A-Fa-f]{2})/e', "''.chr(hexdec('\\1')).''",$value);
				}	

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
                        $display .= '+';
                else 
                {
					for ($i=0; $i<$level; ++$i)
						$display .= '---';
                }

                reset ( $array_dn );
                $display .= ' ' . (current ( $array_dn ));
				
				$dn = trim(strtolower($dn));
				$options[$dn] = $display;
        	}
    	    return $options;
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
		
		function get_next_id()
		{
			// Busco o ID dos accounts
			$query_accounts = "SELECT id FROM phpgw_nextid WHERE appname = 'accounts'";
			$GLOBALS['phpgw']->db->query($query_accounts);
			while($GLOBALS['phpgw']->db->next_record())
			{
				$result_accounts[] = $GLOBALS['phpgw']->db->row();
			}			
			$accounts_id = $result_accounts[0]['id'];
			
			// Busco o ID dos groups
			$query_groups = "SELECT id FROM phpgw_nextid WHERE appname = 'groups'";
			$GLOBALS['phpgw']->db->query($query_groups);
			while($GLOBALS['phpgw']->db->next_record())
			{
				$result_groups[] = $GLOBALS['phpgw']->db->row();
			}			
			$groups_id = $result_groups[0]['id'];
			
			//Retorna o maior dos ID's
			if ($accounts_id >= $groups_id)
				return $accounts_id;
			else
				return $groups_id;
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
					$checked	= ((is_array($user_applications) && isset($user_applications[$app])) ? 'CHECKED' : '');
					$app_col1	= sprintf("<td>%s</td><td width='10'><input type='checkbox' name='apps[%s]' value='1' %s %s></td>\n",
					$data['title'],$app,$checked, $disabled);
					if( $i == ($total_apps-1) )
					{
						$applications_list .= sprintf('<tr bgcolor="%s">%s</tr>','#DDDDDD', $app_col1);
					}
				}
				
				// 2 coluna
				if (($i +1) % 3 == 2)
				{
					$checked	= ((is_array($user_applications) && isset($user_applications[$app])) ? 'CHECKED' : '');
					$app_col2	= sprintf("<td>%s</td><td width='10'><input type='checkbox' name='apps[%s]' value='1' %s %s></td>\n",
					$data['title'],$app,$checked, $disabled);
					if( $i == ($total_apps-1) )
					{
						$applications_list .= sprintf('<tr bgcolor="%s">%s%s</tr>','#DDDDDD', $app_col1,$app_col2);
					}
				}
				// 3 coluna 
				if (($i +1) % 3 == 0)
				{
					$checked = ((is_array($user_applications) && isset($user_applications[$app])) ? 'CHECKED' : '');
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
		
		function getReturnExecuteForm(){
			$response = $_SESSION['response'];
			$_SESSION['response'] = null;
			return $response;
		}

		function lang($key)
		{
			if (isset($_SESSION['phpgw_info']['expressoAdmin']['lang'][$key]))
				return $_SESSION['phpgw_info']['expressoAdmin']['lang'][$key];
			else
				return $key . '*';
		}
		
		
		function checkCPF($cpf)
		{
			$nulos = array("12345678909","11111111111","22222222222","33333333333",
        		       "44444444444","55555555555","66666666666","77777777777",
            		   "88888888888","99999999999","00000000000");

			/* formato do CPF */
			if (!(preg_match('/^[0-9]{3}[.][0-9]{3}[.][0-9]{3}[-][0-9]{2}$/',$cpf)))
				return false;

			/* Retira todos os caracteres que nao sejam 0-9 */
			$cpf = preg_replace('/[^0-9]/', '', $cpf);

			/*Retorna falso se houver letras no cpf */
			if (!(preg_match('/[0-9]/',$cpf)))
    			return false;

			/* Retorna falso se o cpf for nulo */
			if( in_array($cpf, $nulos) )
    			return false;

			/*Calcula o penúltimo dígito verificador*/
			$acum=0;
			for($i=0; $i<9; ++$i)
			{
  				$acum+= $cpf[$i]*(10-$i);
			}

			$x=$acum % 11;
			$acum = ($x>1) ? (11 - $x) : 0;
			/* Retorna falso se o digito calculado eh diferente do passado na string */
			if ($acum != $cpf[9]){
  				return false;
			}
			/*Calcula o último dígito verificador*/
			$acum=0;
			for ($i=0; $i<10; ++$i)
			{
  				$acum+= $cpf[$i]*(11-$i);
			}

			$x=$acum % 11;
			$acum = ($x > 1) ? (11-$x) : 0;
			/* Retorna falso se o digito calculado eh diferente do passado na string */
			if ( $acum != $cpf[10])
			{
  				return false;
			}
			/* Retorna verdadeiro se o cpf eh valido */
			return true;
		}
		
		function make_lang($ram_lang)
		{
			$a_lang = preg_split('/_/', $ram_lang);
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

     
         
         function normalize_calendar_acl($acl)
         {
             $return = '';

             if($this->safeBitCheck(1, $acl))
                $return .= '1-';
             if($this->safeBitCheck(2, $acl))
                $return .= '2-';
             if($this->safeBitCheck(4, $acl))
                $return .= '4-';
             if($this->safeBitCheck(8, $acl))
                $return .= '8-';
             if($this->safeBitCheck(16, $acl))
                $return .= '16-';
             
             return $return;
	}
}
	
	class sectors_object
	{
		var $sector_name;
		var $sector_context;
		var $sector_level;
		var $sector_leaf;
		var $sectors_list = array();
		var $level;
		
		function sectors_object($sector_name, $sector_context, $sector_level, $sector_leaf)
		{
			$this->sector_name = $sector_name;
			$this->sector_context = $sector_context;
			$this->sector_level = $sector_level;
			$this->sector_leaf = $sector_leaf;
		}
	}	
