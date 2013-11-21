<?php
	/**********************************************************************************\
	* Expresso Administra��o                 									      *
	* by Joao Alfredo Knopik Junior (joao.alfredo@gmail.com, jakjr@celepar.pr.gov.br) *
	* --------------------------------------------------------------------------------*
	*  This program is free software; you can redistribute it and/or modify it		  *
	*  under the terms of the GNU General Public License as published by the		  *
	*  Free Software Foundation; either version 2 of the License, or (at your		  *
	*  option) any later version.													  *
	\**********************************************************************************/
	
	include_once('class.db_functions.inc.php');

	class functions
	{
		
		var $public_functions = array
		(
			'make_array_acl' 	=> True,
			'check_acl'		=> True,
			'read_acl'		=> True,
			'exist_account_lid'	=> True,
			'exist_email'		=> True,
			'array_invert'		=> True
		);
		
		var $nextmatchs;
		var $sectors_list = array();
		
		function functions()
		{
			$this->db_functions = new db_functions;
		}
	

		// Account and type of access. Return: Have access ? (true/false)
		function check_acl($account_lid, $access)
		{
			$acl = $this->read_acl($account_lid);
			$array_acl = $this->make_array_acl($acl[0][acl]);
			
			//What access ?? In the IF, verify if have access.
			switch($access)
			{
				case list_users:
					if ($array_acl['add_users'] || $array_acl['edit_users'] || $array_acl['delete_users'] || $array_acl['change_users_password'] || $array_acl['change_users_quote'] || $array_acl['edit_sambausers_attributes'] || $array_acl['view_users'])
						return true;
					break;
				case add_users:
					if ($array_acl['add_users'])
						return true;
					break;
				case edit_users:
					if ($array_acl['edit_users'])
						return true;
					break;
				case delete_users:
					if ($array_acl['delete_users'])
						return true;
					break;
				case rename_users:
					if ($array_acl['rename_users'])
						return true;
					break;
				case view_users:
					if ($array_acl['view_users'])
						return true;
					break;
				case change_users_password:
					if ($array_acl['change_users_password'])
						return true;
					break;
				case change_users_quote:
					if ($array_acl['change_users_quote'])
						return true;
					break;
				case set_user_default_password:
					if ($array_acl['set_user_default_password'])
						return true;
					break;
				case edit_sambausers_attributes:
					if ($array_acl['edit_sambausers_attributes'])
						return true;
					break;
				case edit_sambadomains:
					if ($array_acl['edit_sambadomains'])
						return true;
					break;
				
				case list_groups:
					if ($array_acl['add_groups'] || $array_acl['edit_groups'] || $array_acl['delete_groups'])
						return true;
					break;
				case add_groups:
					if ($array_acl['add_groups'])
						return true;
					break;
				case edit_groups:
					if ($array_acl['edit_groups'])
						return true;
					break;
				case delete_groups:
					if ($array_acl['delete_groups'])
						return true;
					break;
				case edit_email_groups:
					if ($array_acl['edit_email_groups'])
						return true;
					break;
				
				case list_maillists:
					if ($array_acl['add_maillists'] || $array_acl['edit_maillists'] || $array_acl['adm_maillists'] || $array_acl['delete_maillists'])
						return true;
					break;
				case add_maillists:
					if ($array_acl['add_maillists'])
						return true;
					break;
				case edit_maillists:
					if ($array_acl['edit_maillists'])
						return true;
					break;
				case adm_maillists:
					if ($array_acl['adm_maillists'])
						return true;
					break;
				case delete_maillists:
					if ($array_acl['delete_maillists'])
						return true;
					break;

				case list_sectors:
					if ($array_acl['create_sectors'] || $array_acl['edit_sectors'] || $array_acl['delete_sectors'])
						return true;
					break;
				case create_sectors:
					if ($array_acl['create_sectors'])
						return true;
					break;
				case edit_sectors:
					if ($array_acl['edit_sectors'])
						return true;
					break;
				case delete_sectors:
					if ($array_acl['delete_sectors'])
						return true;
					break;

				case view_global_sessions:
					if ($array_acl['view_global_sessions'])
						return true;
					break;

				case list_computers:
					if ($array_acl['create_computers'] || $array_acl['edit_computers'] || $array_acl['delete_computers'])
						return true;
					break;
				case create_computers:
					if ($array_acl['create_computers'])
						return true;
					break;
				case edit_computers:
					if ($array_acl['edit_computers'])
						return true;
					break;
				case delete_computers:
					if ($array_acl['delete_computers'])
						return true;
					break;

				case view_logs:
					if ($array_acl['view_logs'])
						return true;
					break;

				default:
					return false; 	
			}
			
			return false;
		}
		
		// Read acl from db
		function read_acl($account_lid)
		{ 
			$result = $this->db_functions->read_acl($account_lid);
			$context_array = ldap_explode_dn($result[0]['context'], 1);
			$result[0]['context_display'] = ldap_dn2ufn ( $result[0]['context'] );
			return $result;
		}
		
		// Make a array read humam
		// Last acl:	33.554.432, 67.108.864
		function make_array_acl($acl)
		{
			$array_acl['add_users'] = $acl & 1;
			$array_acl['edit_users'] = $acl & 2;
			$array_acl['delete_users'] = $acl & 4;
			$array_acl['rename_users'] = $acl & 8388608;
			$array_acl['view_users'] = $acl & 33554432;
			$array_acl['add_groups'] = $acl & 16;
			$array_acl['edit_groups'] = $acl & 32;
			$array_acl['delete_groups'] = $acl & 64;
			$array_acl['edit_email_groups'] = $acl & 67108864;
			$array_acl['change_users_password'] = $acl & 128;
			$array_acl['change_users_quote'] = $acl & 262144;
			$array_acl['set_user_default_password'] = $acl & 524288;
			$array_acl['edit_sambausers_attributes'] = $acl & 32768;
			$array_acl['edit_sambadomains'] = $acl & 16777216;
			$array_acl['add_maillists'] = $acl & 256;
			$array_acl['edit_maillists'] = $acl & 512;
			$array_acl['delete_maillists'] = $acl & 1024;
			$array_acl['adm_maillists'] = $acl & 2048;
			$array_acl['create_sectors'] = $acl & 4096;
			$array_acl['edit_sectors'] = $acl & 8192;
			$array_acl['delete_sectors'] = $acl & 16384;
			$array_acl['view_global_sessions'] = $acl & 65536;
			$array_acl['view_logs'] = $acl & 131072;
			$array_acl['create_computers'] = $acl & 1048576;
			$array_acl['edit_computers'] = $acl & 2097152;
			$array_acl['delete_computers'] = $acl & 4194304;
			return $array_acl;
		}

		function auto_list($type, $context, $admlista)
		{
			$common = new common();
			$ldap_conn = $GLOBALS['phpgw']->common->ldapConnect();

			if ($type == 'maillists')
			{
				
				//filtro de busca das listas de e-mail, busca apenas as listas permitidas ao usuario conectado;
				//$context = $GLOBALS['phpgw_info']['server']['ldap_context'];
				$filter="(&(phpgwAccountType=l)(admlista=$admlista)(|(cn=*)(uid=*)))";
				$justthese = array("uidnumber", "cn", "uid", "mail");
				$search=ldap_search($ldap_conn, $context, $filter, $justthese);
				ldap_sort($ldap_conn, $search, "uid");
				$info = ldap_get_entries($ldap_conn, $search);
				ldap_close($ldap_conn);

				$i = 0;
				$tmp = array();
				for ($i=0; $i < $info['count']; ++$i)
				{
					$tmp[$i]['uid'] 		= $info[$i]['uid'][0];
					$tmp[$i]['name'] 		= $info[$i]['cn'][0];
					$tmp[$i]['uidnumber']		= $info[$i]['uidnumber'][0];
					$tmp[$i]['email']		= $info[$i]['mail'][0];
				}
				return $tmp;
			}
		}

		function get_list($type, $query, $context, $admlista)
		{
			$common = new common();
			$ldap_conn = $GLOBALS['phpgw']->common->ldapConnect();//$common->ldapConnect();
			if ($type == 'accounts')
			{
				$justthese = array("uidnumber", "uid", "cn", "mail", "objectclass");
				//$filter="(&(phpgwAccountType=u)(|(uid=*".$query."*)(sn=*".$query."*)(cn=*".$query."*)(givenName=*".$query."*)(mail=$query*)(mailAlternateAddress=$query*)))";
				//$filter="(&(objectclass=".$GLOBALS['phpgw_info']['server']['atributousuarios'].")(|(uid=*".$query."*)(sn=*".$query."*)(cn=*".$query."*)(givenName=*".$query."*)(mail=$query*)))";
				$filter="(&(objectclass=".$GLOBALS['phpgw_info']['server']['atributousuarios'].")(|(uid=*".$query."*)(sn=*".$query."*)(cn=*".$query."*)(givenName=*".$query."*)(mail=$query*)))";
				$search=ldap_search($ldap_conn, $context, $filter, $justthese);
				ldap_sort($ldap_conn, $search, "cn");
				$info = ldap_get_entries($ldap_conn, $search);
				ldap_close($ldap_conn);
				
				$i = 0;
				$tmp = array();
				$ldap_found=false;
				for ($i=0; $i < $info['count']; ++$i)
					{
					$tmp[$i][account_id]			= $info[$i]['uidnumber'][0]; 
					$tmp[$i][account_lid] 			= $info[$i]['uid'][0];
					$tmp[$i][account_cn] 			= $info[$i]['cn'][0];
					$tmp[$i][account_mail] 			= $info[$i]['mail'][0];
					$tmp[$i][account_expresso] 		= false;
					$tmp[$i][account_deleted] 		= false;
					$ldap_found=true;
					foreach ($info[$i]['objectclass'] as $objectclass)
						{
						if (strcasecmp($objectclass, 'phpgwaccount') == 0)
							{
							$tmp[$i][account_expresso] = true;
							}
						}
					//$tmp[$i][account_expresso] = true;
					}
				// fazendo a pesquisa tambem no banco de dados
				// eh cpf?
				if ((strlen($query) == 11)and(!$ldap_found))
					{
					// inferindo o uidnumber
					$uidnumber=substr($query,0,strlen($query)-2);
					while (strpos($uidnumber,'0')===0)
						{
						$uidnumber=substr($uidnumber,1);
						}
					// consulta pelo uidnumber na tabela das acls					
					$sql="SELECT DISTINCT acl_account FROM phpgw_acl WHERE acl_account='".$uidnumber."'";
					$GLOBALS['phpgw']->db->query($sql);
                        		while($GLOBALS['phpgw']->db->next_record())
						{
						$find=false;
						foreach ($tmp as $ldap)
							{
							if ($ldap['account_id'] == $GLOBALS['phpgw']->db->f(0))
								{
								$find=true;
								}
							}
						if (!$find)
							{
							$tmp[$i][account_id]                    = $GLOBALS['phpgw']->db->f(0);
				                	$tmp[$i][account_lid]                   = $query;
				                	$tmp[$i][account_cn]                    = "Nao encontrado no RHDS";
				                	$tmp[$i][account_mail]                  = "Nao encontrado no RHDS";
							$tmp[$i][account_expresso]              = false;
					                $tmp[$i][account_deleted]               = true;
							++$i;
							}
						}
					}
                        							
				return $tmp;
			}
			elseif($type == 'groups')
			{
				$filter="(&(phpgwAccountType=g)(cn=*".$query."*))";
				$justthese = array("gidnumber", "cn", "description");
				$search=ldap_search($ldap_conn, $context, $filter, $justthese);
				ldap_sort($ldap_conn, $search, "cn");
				$info = ldap_get_entries($ldap_conn, $search);				
				ldap_close($ldap_conn);
				
				$i = 0;
				$tmp = array();
				for ($i=0; $i < $info['count']; ++$i)
				{
					$tmp[$i][cn] 			= $info[$i][cn][0];
					$tmp[$i][description]	= $info[$i][description][0];
					$tmp[$i][gidnumber]		= $info[$i][gidnumber][0];
				}
				return $tmp;
			}
			elseif($type == 'maillists')
			{

//				$filter="(&(phpgwAccountType=l)(|(cn=*".$query."*)(uid=*".$query."*)(mail=*".$query."*)))";
//				$filter="(&(phpgwAccountType=l)(|(cn=*".$query."*)(uid=*".$query."*)(mail=*".$query."*)(admlista=$admlista)))";

				//filtro de busca das listas de e-mail, busca apenas as listas permitidas ao usuario conectado;
				$context = $GLOBALS['phpgw_info']['server']['ldap_context'];
				$filter="(&(phpgwAccountType=l)(admlista=$admlista)(|(cn=*".$query."*)(uid=*".$query."*)))";
				$justthese = array("uidnumber", "cn", "uid", "mail");
				$search=ldap_search($ldap_conn, $context, $filter, $justthese);
				ldap_sort($ldap_conn, $search, "uid");
				$info = ldap_get_entries($ldap_conn, $search);
				ldap_close($ldap_conn);

				$i = 0;
				$tmp = array();
				for ($i=0; $i < $info['count']; ++$i)
				{
					$tmp[$i]['uid'] 		= $info[$i]['uid'][0];
					$tmp[$i]['name'] 		= $info[$i]['cn'][0];
					$tmp[$i]['uidnumber']		= $info[$i]['uidnumber'][0];
					$tmp[$i]['email']		= $info[$i]['mail'][0];
				}
				return $tmp;
			}
			elseif($type == 'computers')
			{
				$filter="(&(objectClass=sambaSAMAccount)(|(sambaAcctFlags=[W          ])(sambaAcctFlags=[DW         ])(sambaAcctFlags=[I          ])(sambaAcctFlags=[S          ]))(cn=*".$query."*))";
				$justthese = array("cn","uidNumber","description");
				$search=ldap_search($ldap_conn, $context, $filter, $justthese);
				ldap_sort($ldap_conn, $search, "cn");
				$info = ldap_get_entries($ldap_conn, $search);
				ldap_close($ldap_conn);
				$tmp = array();
				for ($i=0; $i < $info['count']; ++$i)
				{
					$tmp[$i]['cn'] 			= $info[$i]['cn'][0];
					$tmp[$i]['uidNumber']		= $info[$i]['uidnumber'][0];
					$tmp[$i]['description'] 	= utf8_decode($info[$i]['description'][0]);
				}
				return $tmp;
			}
		}
		
		//Criado nova fun��o bem mais eficiente!
		/*
		function get_organizations($context, $selected='')
		{
			//echo $selected;
			
			$first_sector_ufn = ldap_dn2ufn($context);
			$first_sector_string = preg_split('/,/', $first_sector_ufn);
			
			$s = CreateObject('phpgwapi.sector_search_ldap');
			$sectors_info = $s->sector_search($context);
			
			$sector_select = "";
			$sector_select .= "<option value='" .$context . "'";
			$sector_select .= ">".strtoupper($first_sector_string[0])."</option>\n";
			
			foreach($sectors_info as $sector)
			{
				if ($sector->sector_level == 1)
				{
					$sector->sector_name = '+ '.$sector->sector_name;
				}
				else
				{
					$sector_space = '';
					for ($i=1; $i < $sector->sector_level; ++$i)
						$sector_space = '---'.$sector_space;
					$sector->sector_name = $sector_space.' '.$sector->sector_name;
				}
				$sector_select .= '<option value="' . strtolower($sector->sector_context) . '"';
				
				if (trim(strtolower($sector->sector_context)) == $selected)
					$sector_select .= ' selected>' . $sector->sector_name . "</option>\n";
				else
					$sector_select .= '>' . $sector->sector_name . "</option>\n";
			}
			//$sector_select .= "</select>";
			return $sector_select;	
		}
		*/
		
		/*
		function get_organizations($context, $selected='', $show_invisible_ou=false)
		{
			$dn			= $GLOBALS['phpgw_info']['server']['ldap_root_dn'];
			$passwd		= $GLOBALS['phpgw_info']['server']['ldap_root_pw'];
			$ldap_conn	= ldap_connect($GLOBALS['phpgw_info']['server']['ldap_host']);
			
			ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);
			ldap_bind($ldap_conn,$dn,$passwd);
			
			$justthese = array("dn");
			$filter = $show_invisible_ou ? "(ou=*)" : "(& (ou=*) (!(phpgwAccountVisible=-1)) )";
			$search=ldap_search($ldap_conn, $context, $filter, $justthese);
        	
        	ldap_sort($ldap_conn, $search, "ou");
        	$info = ldap_get_entries($ldap_conn, $search);
			ldap_close($ldap_conn);

			// Retiro o count do array info e inverto o array para ordena��o.
	        for ($i=0; $i<$info["count"]; ++$i)
    	    {
				$dn = $info[$i]["dn"];
				
				// Necess�rio, pq em uma busca com ldapsearch ou=*, traz tb o pr�prio ou. 
				if (strtolower($dn) == $context)
					continue;

				$array_dn = ldap_explode_dn ( $dn, 1 );

                $array_dn_reverse  = array_reverse ( $array_dn, true );

				array_pop ( $array_dn_reverse );

				$inverted_dn[$dn] = implode ( ".", $array_dn_reverse );
			}

			// Ordena��o
			natcasesort($inverted_dn);
			
			// Constru��o do select
			$level = 0;
			foreach ($inverted_dn as $dn=>$invert_ufn)
			{
                $display = '';

                $array_dn_reverse = explode ( ".", $invert_ufn );
                $array_dn  = array_reverse ( $array_dn_reverse, true );

                $level = count( $array_dn ) - 4;

                if ($level == 0)
                        $display .= '+';
                else {
                        for ($i=0; $i<$level; ++$i)
                                $display .= '---';
                }

                reset ( $array_dn );
                $display .= ' ' . (current ( $array_dn ) );
				
				$dn = trim(strtolower($dn));
				if ( $dn == $selected )
					$select = ' selected';
				else
					$select = '';
                $options .= "<option value='$dn'$select>$display</option>";
        	}

			// Cria a primeira entrada na combo 
			$first_sector_ufn = ldap_dn2ufn($context);
			$first_sector_string = preg_split('/,/', $first_sector_ufn);
			$options = "<option value='$context'>" . strtoupper($first_sector_string[0]) . "</option>" . $options;

    	    return $options;
		}
		*/
		
		function get_organizations($context, $selected='', $referral=false, $show_invisible_ou=true)
		{
			$s = CreateObject('phpgwapi.sector_search_ldap');
			$sectors_info = $s->get_organizations($context, $selected, $referral, $show_invisible_ou);
			return $sectors_info;
		}		
		
		function get_sectors($selected='', $referral=false, $show_invisible_ou=true)
		{
			$s = CreateObject('phpgwapi.sector_search_ldap');
			$sectors_info = $s->get_sectors($selected, $referral, $show_invisible_ou);
			return $sectors_info;
		}		
 
		/*
		function get_sectors($context, $selected='')
		{
			$query = "SELECT sector FROM phpgw_expressoadmin_sectors WHERE organization = '" . $context . "'";
			$GLOBALS['phpgw']->db->query($query);
			while($GLOBALS['phpgw']->db->next_record())
			{
				$query_result[] = $GLOBALS['phpgw']->db->row();
			}
			
			if (count($query_result) > 0)
			{
				foreach ($query_result as $index=>$tmp)
				{
					$sector = $tmp['sector'];
					if ($sector == $selected)
						$sector_options .= "<option value='$sector' selected>$sector</option>";
					else
						$sector_options .= "<option value='$sector'>$sector</option>";
				}
				return $sector_options;
			}
			else
				return false;
		}
		*/
		
		// Get list of all levels, this function is used for sectors module.
		/*
		function get_sectors_list($context)
		{
			$connection = $GLOBALS['phpgw']->common->ldapConnect();
			ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($connection, LDAP_OPT_REFERRALS, 0);
	
			if ($connection)
			{				
				$bind=ldap_bind($connection);
				$filter="ou=*";
				$justthese = array("ou");
				$search=ldap_list($connection, $context, $filter, $justthese);
				ldap_sort($connection, $search, "ou");
				$info = ldap_get_entries($connection, $search);
				for ($i=0; $i<$info["count"]; ++$i)
				{
					$this->level++;
					$next_context[$i] = 'ou='.$info[$i]["ou"][0].','.$context;
					$obj = new sectors_object($info[$i]["ou"][0], $next_context[$i], $this->level, 'False');
					array_push($this->sectors_list, $obj);
					$this->get_sectors_list($next_context[$i]);
				}
			}
			ldap_close($connection);
			$this->level--;
			return $this->sectors_list;
		}
		*/
		
		// Get list of all levels, this function is used for sectors module.
		function get_sectors_list($context, $selected='', $referral=false ,$show_invisible_ou=false)
		{
			$dn			= $GLOBALS['phpgw_info']['server']['ldap_root_dn'];
			$passwd		= $GLOBALS['phpgw_info']['server']['ldap_root_pw'];
			$ldap_conn	= ldap_connect($GLOBALS['phpgw_info']['server']['ldap_host']);
			
			ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
			
			if ($referral)
				ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 1);
			else
				ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);
			
			ldap_bind($ldap_conn,$dn,$passwd);
			
			$justthese = array("dn");
			$filter = "(ou=*)";
			$search=ldap_search($ldap_conn, $context, $filter, $justthese);
        	
        	ldap_sort($ldap_conn, $search, "ou");
        	$info = ldap_get_entries($ldap_conn, $search);
			ldap_close($ldap_conn);

			// Retiro o count do array info e inverto o array para ordena��o.
	        for ($i=0; $i<$info["count"]; ++$i)
    	    {
				$dn = $info[$i]["dn"];
				
				// Necess�rio, pq em uma busca com ldapsearch ou=*, traz tb o pr�prio ou. 
				if (strtolower($dn) == $context)
					continue;

				$array_dn = ldap_explode_dn ( $dn, 1 );

                $array_dn_reverse  = array_reverse ( $array_dn, true );

				// Retirar o indice count do array.
				array_pop ( $array_dn_reverse );

				$inverted_dn[$dn] = implode ( "#", $array_dn_reverse );
			}

			// Ordena��o
			natcasesort($inverted_dn);
			
			// Constru��o do select
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
                $display .= ' ' . (current ( $array_dn ) );
				
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
		
		function increment_id($id, $type)
		{
			$sql = "UPDATE phpgw_nextid set id = '".$id."' WHERE appname = '" . $type . "'"; 
			$GLOBALS['phpgw']->db->query($sql);
		}
		
		function make_list_app($account_lid, $context, $user_applications, $disabled='')
		{
			// create list of ALL available apps
			$availableAppsGLOBALS = $GLOBALS['phpgw_info']['apps'];
			
			// create list of available apps for the user
			$query = "SELECT * FROM phpgw_expressoadmin_apps WHERE manager_lid = '".$account_lid."' AND context = '".$context."'";
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
		
		function getReturnExecuteForm(){
			$response = $_SESSION['response'];
			$_SESSION['response'] = null;
			return $response;
		}

		function write_log2($action, $groupinfo='', $userinfo='', $appinfo='', $msg_log='')
		{
			$sql = "INSERT INTO phpgw_expressoadmin_log (date, manager, action, groupinfo, userinfo, appinfo, msg) "
			. "VALUES('now','" . $_SESSION['phpgw_info']['expresso']['user']['account_lid'] . "','" . strtolower($action) . "','" . strtolower($groupinfo) . "','" . strtolower($userinfo) . "','" . strtolower($appinfo) . "','" .strtolower($msg_log) . "')";
			$GLOBALS['phpgw']->db->query($sql);
			return;
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
