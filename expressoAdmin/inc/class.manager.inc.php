<?php
	/***********************************************************************************\
	* Expresso Administração                 										   *
	* by Joao Alfredo Knopik Junior (joao.alfredo@gmail.com, jakjr@celepar.pr.gov.br)  *
	* ---------------------------------------------------------------------------------*
	*  This program is free software; you can redistribute it and/or modify it		   *
	*  under the terms of the GNU General Public License as published by the		   *
	*  Free Software Foundation; either version 2 of the License, or (at your		   *
	*  option) any later version.													   *
	\***********************************************************************************/

	include_once('class.db_functions.inc.php');
	include_once('class.functions.inc.php');
	
	class manager
	{
		var $db_functions;
		var $functions;
		var $current_config;
		
		function manager()
		{
			$this->db_functions = new db_functions;
			$this->functions = new functions;
			$this->current_config = $_SESSION['phpgw_info']['expresso']['expressoAdmin']; 
		}

		function save($params)
		{
			$manager_acl = $this->make_manager_acl($params);
			 
			$this->db_functions->save_manager($params, $manager_acl);

			$return['status'] = 'true';
			$return['type'] = 'save';
			return $return;
		}

		function create($params)
		{
			$manager_acl = $this->make_manager_acl($params);
			 
			$this->db_functions->create_manager($params, $manager_acl);

			$return['status'] = 'true';
			$return['type'] = 'create';
			return $return;
		}

		function make_manager_acl($array_post)
		{
                    
                        $total_manager_acl = array();

			foreach ($array_post as $atribute=>$value)
			{
				$acl  = strstr($atribute, 'acl_');

				if ($acl !== false && $value = 'true')	
                                        array_push($total_manager_acl, $atribute);

			}
                        
			return $total_manager_acl;
		}

		function validate($params)
		{
			if (is_array($_SESSION['phpgw_info']['expresso']['server']))
				$GLOBALS['phpgw_info']['server'] = $_SESSION['phpgw_info']['expresso']['server'];
			else
				$_SESSION['phpgw_info']['expresso']['server'] = $GLOBALS['phpgw_info']['server'];
			
			$return['status'] = 'true';
			
			$contexts = preg_split('/%/', $params['contexts']);
			$manager_lid = $params['manager_lid'];
			$type = $params['type'];
			
			if ($params['contexts'] == '')
			{
				$return['status'] = 'false';
				$return['msg'] = $this->functions->lang('context field is empty') . '.';
				
				return $return;
			}
			if (($manager_lid == '') && ($type == 'add'))
			{
				$return['status'] = 'false';
				$return['msg'] = $this->functions->lang('select one manager') . '.';
				
				return $return;
			}
			
			// Verifica se o contexto existe.
			$dn			= $GLOBALS['phpgw_info']['server']['ldap_root_dn'];
			$passwd		= $GLOBALS['phpgw_info']['server']['ldap_root_pw'];
			$ldap_conn	= ldap_connect($GLOBALS['phpgw_info']['server']['ldap_host']);
			ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);
			ldap_bind($ldap_conn,$dn,$passwd);
			
			foreach ($contexts as $index=>$context)
			{
				$sr=@ldap_list($ldap_conn, $context, "cn=*");
				if (!$sr)
				{
					$return['status'] = 'false';
					$this->functions->lang('context does not exist') . ": $context";
					return $return;
				}				
			}
			
			if ($type == 'add')
			{
				include_once('class.db_functions.inc.php');
				$db = new db_functions();
				
				if ($db->manager_lid_exist($manager_lid))
				{
					$return['status'] = 'false';
					$return['msg'] = $this->functions->lang('manager already exist') . ".";
					
					return $return;
				}
			}
			
			return $return;
		}
		
		function manager_lid_exist($manager_lid)
		{
			$query = "SELECT manager_lid FROM phpgw_expressoadmin_acls WHERE manager_lid = '" . $manager_lid . "'";
			$this->db->query($query);
			while($this->db->next_record())
				$result[] = $this->db->row();
			if (count($result) > 0)
				return true;
			else
				return false;
		}
		
	}
?>
