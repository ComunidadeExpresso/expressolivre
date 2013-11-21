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

	class bosectors
	{
		var $public_functions = array(
			'create_sector'	=> True,
			'save_sector'	=> True,
			'delete_sector'	=> True
		);
	
		var $so;
		var $functions;
		var $db_functions;
	
		function bosectors()
		{
			$this->so = createobject('expressoAdmin1_2.sosectors');
			$this->functions = $this->so->functions;
			$this->db_functions = $this->so->db_functions;
			$this->group = createobject('expressoAdmin1_2.group');
                        $this->user = createobject('expressoAdmin1_2.user');
		}

		function create_sector()
		{
			// Verifica o acesso do gerente
			if (!$this->functions->check_acl($_SESSION['phpgw_session']['session_lid'], 'create_sectors'))
			{
				$return['status'] = false;
				$return['msg'] = lang('you do not have access to create sectors') . '.';
				return $return;
			}

			// Cria array para incluir no LDAP
			$dn = 'ou=' . $_POST['sector'] . ',' . $_POST['context'];			
			$sector_info = array();
			$sector_info['ou']				= $_POST['sector'];  
			$sector_info['objectClass'][]	= 'top';
			$sector_info['objectClass'][]	= 'organizationalUnit';
			$sector_info['objectClass'][]   = 'phpgwQuotaControlled';
			/*Insere as informações sobre quota total por usuários e por gigabytes de espaço em disco  
              Se não vierem os dados, ele coloca 0 (Caso não exista controle de cota). 
             */
 	        $sector_info['diskQuota'] = isset($_POST['disk_quota']) ? (int)$_POST['disk_quota'] : 0; 
 	        $sector_info['usersQuota'] = isset($_POST['users_quota']) ? (int)$_POST['users_quota'] : 0; 

 	        if( isset($_POST['associated_domain']) ) 
 	        { 
	            if ( $_POST['associated_domain'] != "") { 
	                $sector_info['objectClass'][]  = 'domainRelatedObject'; 
	                $sector_info['associatedDomain'] = trim($_POST['associated_domain']); 
	            } 
 	        } 

			$systemName = $GLOBALS['phpgw_info']['server']['system_name'];
			if ($systemName != '')
				$sector_info['phpgwSystem'] = strtolower($systemName);
			
			if ($_POST['sector_visible'])
			{
				$sector_info['objectClass'][]	= 'phpgwAccount';
				$sector_info['phpgwaccountvisible'] = '-1';
			}
			
			// Chama funcao para escrever no OpenLDAP, case de erro, volta com msg de erro.
			if (!$this->so->write_ldap($dn, $sector_info))
			{
				$_POST['error_messages'] = lang('Error in OpenLDAP recording.');
				ExecMethod('expressoAdmin1_2.uisectors.add_sector');
				return false;
			}
			
			//Escreve no log
			$this->db_functions->write_log("created sector", "$dn");
			
			// Volta para o ListSectors
			$url = ($GLOBALS['phpgw']->link('/index.php','menuaction=expressoAdmin1_2.uisectors.list_sectors'));
			$GLOBALS['phpgw']->redirect($url);
		}

	function save_sector()
		{
		$context = utf8_encode($_POST['context']);

			$sector_info = $this->so->get_info($context);
			
			if (($_POST['sector_visible'] == 'on') && ($sector_info['phpgwaccountvisible'] != '-1'))
			{
				foreach ($sector_info[0]['objectclass'] as $objectClass)
				{
					if ($objectClass == 'phpgwAccount')
						$phpgwAccount = true;
					else
						$phpgwAccount = false;
				}
				
				if (!$phpgwAccount)
				{
					$ldap_mod_add['objectClass'][] = 'phpgwAccount';
				}
				
				$ldap_mod_add['phpgwaccountvisible'] = '-1';
				$this->so->add_attribute($sector_info[0]['dn'], $ldap_mod_add);
			}
			elseif($sector_info['phpgwaccountvisible'] == '-1') 
			{
				$ldap_mod_del['objectClass'] = 'phpgwAccount';
				$ldap_mod_del['phpgwaccountvisible'] = array();
				$this->so->remove_attribute($sector_info[0]['dn'], $ldap_mod_del);
			} 

			if(!in_array('phpgwQuotaControlled',$sector_info[0]['objectclass'] ))
			{
				$ldap_mod_add = array();
				$ldap_mod_add['objectClass'][] = 'phpgwQuotaControlled';
				$ldap_mod_add['diskQuota'] = isset($_POST['disk_quota']) ? (int)$_POST['disk_quota'] : 0;
				$ldap_mod_add['usersQuota'] = isset($_POST['users_quota']) ? (int)$_POST['users_quota'] : 0;

				$this->so->add_attribute($sector_info[0]['dn'], $ldap_mod_add);
			}
			else
			{
				$ldap_mod_replace = array(); 
				if(isset($_POST['disk_quota']))
					$ldap_mod_replace['diskQuota'] = (int)$_POST['disk_quota']; 

				if(isset($_POST['users_quota']))
					$ldap_mod_replace['usersQuota'] = (int)$_POST['users_quota']; 

				if(count($ldap_mod_replace) > 0)
					$this->so->replace_attribute($sector_info[0]['dn'], $ldap_mod_replace); 
			}
			// Volta para o ListSectors
			ExecMethod('expressoAdmin1_2.uisectors.list_sectors');
		}

		function delete_sector()
		{

			// Verifica o acesso do gerente
			if (!$this->functions->check_acl($_SESSION['phpgw_session']['session_lid'], 'delete_sectors'))
			{
				$return['status'] = false;
				$return['msg'] = lang('you do not have access to delete sectors') . '.';
				return $return;
			}

			$sector_dn = $_POST['dn'];
			$manager_context = $_POST['manager_context'];

			$sector_dn = $sector_dn;

			$sector_users = $this->so->get_sector_users($sector_dn);

            $sector_users_count = count($sector_users)-1;
			for ($i=0; $i<$sector_users_count; ++$i)
			{
				//_debug_array($user);
				// Pega o UID e os grupos que o usuario fz parte.
				$uid = $sector_users[$i]['uid'][0];
				$account_id = $sector_users[$i]['uidnumber'][0];
				$dn = $sector_users[$i]['dn'];
				$this->user->delete(Array('uid' =>  $uid , 'uidnumber' => $account_id));
			}


			$sector_groups = $this->so->get_sector_groups($sector_dn);
            $sector_groups_count = count($sector_groups)-1;
			for ($i=0; $i<$sector_groups_count; ++$i)
			{
 				$dn = $sector_groups[$i]['dn'];
 				$gidnumber = $sector_groups[$i]['gidnumber'][0];

				//Delete group
				$this->group->delete(Array('gidnumber' => $gidnumber, 'cn' => $dn));
			}
			
			if ( (!empty($GLOBALS['phpgw_info']['server']['ldap_master_host'])) &&
				 (!empty($GLOBALS['phpgw_info']['server']['ldap_master_root_dn'])) &&
				 (!empty($GLOBALS['phpgw_info']['server']['ldap_master_root_pw'])) )
			{
				$connection = $GLOBALS['phpgw']->common->ldapConnect($GLOBALS['phpgw_info']['server']['ldap_master_host'],
												   $GLOBALS['phpgw_info']['server']['ldap_master_root_dn'],
												   $GLOBALS['phpgw_info']['server']['ldap_master_root_pw']);
			}
			else
			{
				$connection = $GLOBALS['phpgw']->common->ldapConnect();
			}
			
			$this->so->delete_sector_ldap_recursively($connection, $sector_dn);
			ldap_close($connection);
			
			// Volta para o ListGroups
			$url = ($GLOBALS['phpgw']->link('/index.php','menuaction=expressoAdmin1_2.uisectors.list_sectors'));
			$GLOBALS['phpgw']->redirect($url);
		}
	}
?>
