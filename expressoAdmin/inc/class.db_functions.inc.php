<?php
		/*************************************************************************** 
		* Expresso Livre                                                           * 
		* http://www.expressolivre.org                                             * 
		* --------------------------------------------                             * 
		*  This program is free software; you can redistribute it and/or modify it * 
		*  under the terms of the GNU General Public License as published by the   * 
		*  Free Software Foundation; either version 2 of the License, or (at your  * 
		*  option) any later version.                                              * 
		\**************************************************************************/ 
		
if (!defined('PHPGW_INCLUDE_ROOT')) define('PHPGW_INCLUDE_ROOT','../');	
if (!defined('PHPGW_API_INC')) define('PHPGW_API_INC','../phpgwapi/inc');
include_once(PHPGW_API_INC.'/class.db.inc.php');

class db_functions
{	
	var $db;
	var $user_id;
	
	function db_functions()
	{
		if ( isset($_SESSION['phpgw_info']['expresso']['server']) && is_array($_SESSION['phpgw_info']['expresso']['server']) )
			$GLOBALS['phpgw_info']['server'] = $_SESSION['phpgw_info']['expresso']['server'];
		else
			$_SESSION['phpgw_info']['expresso']['server'] = $GLOBALS['phpgw_info']['server'];
		
		$this->db = new db();
		
		$this->db->Halt_On_Error = 'no';
		
		$this->db->connect(
			$_SESSION['phpgw_info']['expresso']['server']['db_name'], 
			$_SESSION['phpgw_info']['expresso']['server']['db_host'],
			$_SESSION['phpgw_info']['expresso']['server']['db_port'],
			$_SESSION['phpgw_info']['expresso']['server']['db_user'],
			$_SESSION['phpgw_info']['expresso']['server']['db_pass'],
			$_SESSION['phpgw_info']['expresso']['server']['db_type']
		);		
		
		if( isset($_SESSION['phpgw_info']['expresso']['user']['account_id']) )
		{
			$this->user_id = $_SESSION['phpgw_info']['expresso']['user']['account_id'];
		}
	}

	// BEGIN of functions.
	function read_acl($account_lid)
	{
		$query = "SELECT * FROM phpgw_expressoadmin_acls WHERE manager_lid = '" . $account_lid . "'";
		$this->db->query($query);

                $acls = array();
                $context = null;
		while($this->db->next_record())
                {
			$result = $this->db->row();
                        $acls[$result['acl_name']] = '1';
                        $context = $result['context'];
	}
	
                $all_contexts = preg_split('/%/', $context);
                foreach ($all_contexts as $index=>$context)
                {
                        $acls['contexts'][] = $context;
                        $acls['contexts_display'][] = str_replace(", ", ".", ldap_dn2ufn( $context ));
	}
	
                $acls['raw_context'] = $context;
		return $acls;
	}

	//returns true if cotas control property is set. 
 	function use_cota_control() { 
 	        $query = "select * from phpgw_config where config_name='expressoAdmin_cotasOu' and config_value='true'"; 
 	        $this->db->query($query); 
 	        if($this->db->next_record()) 
 	                return true; 
 	        return false; 
 	} 
 		         
	/*
	*	Reativa os usuários desabilitados por tempo inativo modificando o seu ultimo acesso para o dia atual.
	*/
	function reactivate_inactive_user($uidNumber) {
		
		$sql = "select * from phpgw_access_log where account_id=$uidNumber order by li desc limit 1";
	
		$this->db->query($sql);
		$this->db->next_record();
		$linha = $this->db->row();
		if(count($linha)>0) {
			$sql = "insert into phpgw_access_log (sessionid,loginid,ip,li,lo,account_id) values ('expirescontrol','".$linha["loginid"]."','0.0.0.0','".time()."','0','".$linha["account_id"]."')";
			
			$this->db->query($sql);
		}
	}
	
	function insert_log_inactive_user_control($uid,$uidNumber) {
			$sql = "insert into phpgw_access_log (sessionid,loginid,ip,li,lo,account_id) values ('expirescontrol','".$uid."','0.0.0.0','".time()."','0','".$uidNumber."')";
			
			$this->db->query($sql);
	}

	function copy_manager($params)
	{
		$manager = $params['manager'];
		$new_manager = $params['new_manager'];
		$manager_info = $this->read_acl($manager);
		
                 //Deleta todas as acls do Gerente
                $this->db->delete('phpgw_expressoadmin_acls',array('manager_lid' => $new_manager));
		
                foreach ($manager_info as $info => $value)
                {
                    $acl  = strstr($info, 'acl_');

                    if ($acl !== false)
                    {

                            $fields = array(
                                            'manager_lid' => $new_manager,
                                            'context' =>    $manager_info['raw_context'],
                                            'acl_name' => $acl,
                                           );

                            if(!$this->db->insert('phpgw_expressoadmin_acls', $fields))
		{
			echo lang('error in') . 'copy_manager: ' . pg_last_error();
			return false;
		}
                    }


                }
		
	
		
		//Pesquisa no Banco e pega os valores dos apps.
		$sql = "SELECT * FROM phpgw_expressoadmin_apps WHERE manager_lid = '" . $manager . "' AND context = '" . $manager_info['raw_context'] . "'";
		$this->db->query($sql);
		while($this->db->next_record())
		{
			$aplications[] = $this->db->row();
		}
		
		//Escrevre no Banco as aplicações que o gerente tem direito de disponibilizar aos seus usuarios.
        $aplications_count = count($aplications);
		for ($i=0; $i<$aplications_count; ++$i)
		{
			$sql = "INSERT INTO phpgw_expressoadmin_apps (manager_lid, context, app) "
			. "VALUES('" . $new_manager . "','" . $manager_info['raw_context'] . "','" . $aplications[$i]['app'] . "')";
			if (!$this->db->query($sql))
			{
				echo lang('error adding application to new manager') . ': '. pg_last_error();
				return false;
			}
		}
		return true;
	}

	function get_next_id($type)
	{
		$return['status'] = true;
		
		$current_config = $_SESSION['phpgw_info']['expresso']['expressoAdmin'];
		
		if( isset($current_config['expressoAdmin_nextid_db_host']) && $current_config['expressoAdmin_nextid_db_host'] != '' )
		{
			$this->db->disconnect();
			$host = $current_config['expressoAdmin_nextid_db_host'];
			$port = $current_config['expressoAdmin_nextid_db_port'];
			$name = $current_config['expressoAdmin_nextid_db_name'];
			$user = $current_config['expressoAdmin_nextid_db_user'];
			$pass = (isset($current_config['expressoAdmin_nextid_db_password'])) ? $current_config['expressoAdmin_nextid_db_password'] : "";
			
			$db = new db();
			$db->Halt_On_Error = 'no';
			$db->connect($name, $host, $port, $user, $pass, 'pgsql');
		}
		else
		{
			$db = $this->db;
		}
		
		// Busco o ID dos accounts
		$query_accounts_nextid = "SELECT id FROM phpgw_nextid WHERE appname = 'accounts'";
        if (!$db->query($query_accounts_nextid))
        {
        	$return['status'] = false;
			$result['msg'] = lang('Problems running query on DB') . '.';
			$db->disconnect();
        	return $return;
        }
        else
        {
        	$accounts_nextid = $db->Query_ID->fields[0];
        }
		
		// Busco o ID dos groups
		$query_groups = "SELECT id FROM phpgw_nextid WHERE appname = 'groups'";
        if (!$db->query($query_groups))
        {
        	$return['status'] = false;
			$result['msg'] = lang('Problems running query on DB') . '.';
			$db->disconnect();
        	return $return;
        }
        else
        {
        	$groups_nextid = $db->Query_ID->fields[0];
        }

		//Retorna o maior dos ID's incrementado de 1
		if ($accounts_nextid >= $groups_nextid)
			$id = $accounts_nextid;
		else
			$id = $groups_nextid;
		$return['id'] = (int)($id + 1);
		
		
		// Atualizo o BD
		$query_update_id = "UPDATE phpgw_nextid set id = '" . $return['id'] . "' WHERE appname = '" . $type . "'";
		if (!$db->query($query_update_id))
		{
        	$return['status'] = false;
			$result['msg'] = lang('Problems running query on DB') . '.';
		}
		$db->disconnect();
		return $return;
	}
	
	function add_user2group($gidnumber, $uidnumber)
	{
		$query 			= "SELECT acl_location FROM phpgw_acl WHERE acl_appname = 'phpgw_group' AND acl_location = '" . $gidnumber . "' AND acl_account = '" . $uidnumber . "'";
		$user_in_group	= array();

		if (!$this->db->query($query))
		{
			$result['status'] = false;
			$result['msg'] = lang('Error on function') . " db_functions->add_user2group.\n" . lang('Server returns') . ': ' . pg_last_error();
			return $result;
		}
		while($this->db->next_record())
		{
			$user_in_group[] = $this->db->row();
		}
		
		if( count($user_in_group) == 0 )
		{
			$sql = "INSERT INTO phpgw_acl (acl_appname, acl_location, acl_account, acl_rights) "
			. "VALUES('phpgw_group','" . $gidnumber . "','" . $uidnumber . "','1')";
			
			if (!$this->db->query($sql))
			{
				$result['status'] = false;
				$result['msg'] = lang('Error on function') . " db_functions->add_user2group.\n" . lang('Server returns') . ': ' . pg_last_error();
				return $result;
			}
		}
		
		$result['status'] = true;
		
		return $result;
	}

	function remove_user2group($gidnumber, $uidnumber)
	{
		$query = "SELECT acl_location FROM phpgw_acl WHERE acl_appname = 'phpgw_group' AND acl_location = '" . $gidnumber . "' AND acl_account = '" . $uidnumber . "'";
		if (!$this->db->query($query))
		{
			$result['status'] = false;
			$result['msg'] = lang('Error on function') . " db_functions->remove_user2group.\n" . lang('Server returns') . ': ' . pg_last_error();
			return $result;
		}
		while($this->db->next_record())
			$user_in_group[] = $this->db->row();
		
		if (count($user_in_group) > 0)
		{
			$sql = "DELETE FROM phpgw_acl WHERE acl_appname = 'phpgw_group' AND acl_location = '" . $gidnumber . "' AND acl_account = '".$uidnumber."'";
			if (!$this->db->query($sql))
			{
				$result['status'] = false;
				$result['msg'] = lang('Error on function') . " db_functions->remove_user2group.\n" . lang('Server returns') . ': ' . pg_last_error();
				return $result;
			}
		}
		$result['status'] = true;
		return $result;
	}

	function add_pref_changepassword($uidnumber)
	{
		$query = "SELECT * FROM phpgw_acl WHERE acl_appname = 'preferences' AND acl_location = 'changepassword' AND acl_account = '" . $uidnumber . "'";
		if (!$this->db->query($query))
		{
			$result['status'] = false;
			$result['msg'] = lang('Error on function') . " db_functions->add_pref_changepassword.\n" . lang('Server returns') . ': ' . pg_last_error();
			return $result;
		}
		while($this->db->next_record())
			$user_pref_changepassword[] = $this->db->row();
		
		if( isset($user_pref_changepassword) && count($user_pref_changepassword) == 0 )
		{
			$sql = "INSERT INTO phpgw_acl (acl_appname, acl_location, acl_account, acl_rights) "
			. "VALUES('preferences','changepassword','" . $uidnumber . "','1')";
			if (!$this->db->query($sql))
			{
				$result['status'] = false;
				$result['msg'] = lang('Error on function') . " db_functions->add_pref_changepassword.\n" . lang('Server returns') . ': ' . pg_last_error();
				return $result;
			}
		}
		$result['status'] = true;
		return $result;
	}	

	function remove_pref_changepassword($uidnumber)
	{
		$query = "SELECT * FROM phpgw_acl WHERE acl_appname = 'preferences' AND acl_location = 'changepassword' AND acl_account = '" . $uidnumber . "'";
		if (!$this->db->query($query))
		{
			$result['status'] = false;
			$result['msg'] = lang('Error on function') . " db_functions->remove_pref_changepassword.\n" . lang('Server returns') . ': ' . pg_last_error();
			return $result;
		}
		while($this->db->next_record())
			$user_pref_changepassword[] = $this->db->row();
		
		if (count($user_pref_changepassword) != 0)
		{
			$sql = "DELETE FROM phpgw_acl WHERE acl_appname = 'preferences' AND acl_location = 'changepassword' AND acl_account = '".$uidnumber."'";
			if (!$this->db->query($sql))
			{
				$result['status'] = false;
				$result['msg'] = lang('Error on function') . " db_functions->remove_pref_changepassword.\n" . lang('Server returns') . ': ' . pg_last_error();
				return $result;
			}
		}
		$result['status'] = true;
		return $result;
	}	
	
	function add_id2apps($id, $apps)
	{
		$result['status'] = true;

		if ($apps)
		{
			$user_app = array();

			foreach($apps as $app => $value)
			{
				$query = "SELECT * FROM phpgw_acl WHERE acl_appname = '".$app."' AND acl_location = 'run' AND acl_account = '" . $id . "'";
		
				if( !$this->db->query($query) )
				{
					$result['status'] = false;
					$result['msg'] = lang('Error on function') . " db_functions->add_id2apps.\n" . lang('Server returns') . ': ' . pg_last_error();
					return $result;
				}
				
				while($this->db->next_record())
					$user_app[] = $this->db->row();
					
				if( count($user_app) == 0 ) 
				{
					$sql = "INSERT INTO phpgw_acl (acl_appname, acl_location, acl_account, acl_rights) "
					. "VALUES('".$app."','run','" . $id . "','1')";

					if (!$this->db->query($sql))
					{
						$result['status'] = false;
						$result['msg'] = lang('Error on function') . " db_functions->add_id2apps.\n" . lang('Server returns') . ': ' . pg_last_error();
						return $result;
					}
					else
					{
						$this->write_log("Added application","$id:$app");	
					}
				}
			}
		}
		return $result;
	}

	function remove_id2apps($id, $apps)
	{
		$result['status'] = true;
		if ($apps)
		{
			foreach($apps as $app => $value)
			{
				$query = "SELECT acl_location FROM phpgw_acl WHERE acl_appname = '" . $app . "' AND acl_location = 'run' AND acl_account = '" . $id . "'";
				
				if (!$this->db->query($query))
				{
					$result['status'] = false;
					$result['msg'] = lang('Error on function') . " db_functions->remove_id2apps.\n" . lang('Server returns') . ': ' . pg_last_error();
					return $result;
				}
				while($this->db->next_record())
					$user_in_group[] = $this->db->row();
				
				if (count($user_in_group) > 0)
				{
					$sql = "DELETE FROM phpgw_acl WHERE acl_appname = '" . $app . "' AND acl_location = 'run' AND acl_account = '".$id."'";
					if (!$this->db->query($sql))
					{
						$result['status'] = false;
						$result['msg'] = lang('Error on function') . " db_functions->remove_id2apps.\n" . lang('Server returns') . ': ' . pg_last_error();
						return $result;
					}
					else
					{
						$this->write_log("Removed application from id","$id: $app");	
					}
				}
			}
		}
		return $result;
	}


	function get_user_info($uidnumber)
	{
		$user_app = array();
		// Groups
		$query = "SELECT acl_location FROM phpgw_acl WHERE acl_appname = 'phpgw_group' AND acl_account = '".$uidnumber."'";
		$this->db->query($query);
		while($this->db->next_record())
			$user_app[] = $this->db->row();

        $user_app_count = count($user_app);
		for ($i=0; $i<$user_app_count; ++$i)
			$return['groups'][] = $user_app[$i]['acl_location'];
		
		// ChangePassword
		$query = "SELECT acl_rights FROM phpgw_acl WHERE acl_appname = 'preferences' AND acl_location = 'changepassword' AND acl_account = '".$uidnumber."'";
		$this->db->query($query);
		$changepassword = array();
		while($this->db->next_record())
			$changepassword[] = $this->db->row();
		$return['changepassword'] = isset($changepassword[0]) ? $changepassword[0]['acl_rights'] : '';
		
		// Apps
		$query = "SELECT acl_appname FROM phpgw_acl WHERE acl_account = '".$uidnumber."' AND acl_location = 'run'";
		$this->db->query($query);
		while($this->db->next_record())
			$user_apps[] = $this->db->row();
			
		if (isset($user_apps) && $user_apps)
		{			
			foreach ($user_apps as $app)
			{
				$return['apps'][$app['acl_appname']] = '1';
			}
		}
		
		return $return;
	}
	
	function get_group_info($gidnumber)
	{
		// Apps
		$query = "SELECT acl_appname FROM phpgw_acl WHERE acl_account = '".$gidnumber."' AND acl_location = 'run'";
		$this->db->query($query);
		while($this->db->next_record())
			$group_apps[] = $this->db->row();
		
		if ($group_apps)
		{			
			foreach ($group_apps as $app)
			{
				$return['apps'][$app['acl_appname']] = '1';
			}
		}
		
		// Members
		$query = "SELECT acl_account FROM phpgw_acl WHERE acl_appname = 'phpgw_group' AND acl_location = '" . $gidnumber . "'";
		
		$this->db->query($query);
		while($this->db->next_record())
			$group_members[] = $this->db->row();

		if ($group_members)
		{
			foreach ($group_members as $member)
			{
				$return['members'][] = $member['acl_account'];
			}
		}
		else
			$return['members'] = array();

		return $return;
	}
	
	function default_user_password_is_set($uid)
	{
		$query = "SELECT uid FROM phpgw_expressoadmin_passwords WHERE uid = '" . $uid . "'";
		$this->db->query($query);
		while($this->db->next_record())
		{
			$userPassword[] = $this->db->row();
		}
		if (count($userPassword) == 0)
			return false;
		else
			return true;
	}
	
	function set_user_password($uid, $password)
	{
		$query = "SELECT uid FROM phpgw_expressoadmin_passwords WHERE uid = '" . $uid . "'";
		$this->db->query($query);
		while($this->db->next_record())
		{
			$user[] = $this->db->row();
		}
		if (count($user) == 0)
		{
			$sql = "INSERT INTO phpgw_expressoadmin_passwords (uid, password) VALUES('".$uid."','".$password."')";

			if (!$this->db->query($sql))
			{
				$result['status'] = false;
				$result['msg'] = lang('Error on function') . " db_functions->set_user_password.\n" . lang('Server returns') . ': ' . pg_last_error();
				return $result;
			}
		}
		return true;
	}
	
	function get_user_password($uid)
	{
		$query = "SELECT password FROM phpgw_expressoadmin_passwords WHERE uid = '" . $uid . "'";
		$this->db->query($query);
		while($this->db->next_record())
		{
			$userPassword[] = $this->db->row();
		}
		
		if (count($userPassword) == 1)
		{
			$sql = "DELETE FROM phpgw_expressoadmin_passwords WHERE uid = '" . $uid . "'";
			$this->db->query($sql);
			return $userPassword[0]['password'];
		}
		else
			return false;
	}
	
	function delete_user($user_info)
	{
		$ids = array();
		// AGENDA
		$this->db->query('SELECT calendar_id FROM calendar_signature WHERE user_uidnumber ='.$user_info['uidnumber'] . ' AND is_owner = 1' );
		while($this->db->next_record())
		{
			$ids[] = $this->db->row();
		}

		if (count($ids))
		{
			foreach($ids as $i => $id)
                $this->db->query('DELETE FROM calendar WHERE id = '.$id['calendar_id']);
		}
		
		// CONTATOS pessoais e grupos.
		$this->db->query('SELECT id_contact FROM phpgw_cc_contact WHERE id_owner ='.$user_info['uidnumber']);
		while($this->db->next_record())
		{
			$ids[] = $this->db->row();
		}

		if (count($ids))
		{
			foreach($ids as $i => $id_contact)
			{
				$this->db->query('SELECT id_connection FROM phpgw_cc_contact_conns WHERE id_contact='.$id_contact['id_contact']);
				while($this->db->next_record())
				{
					$id_conns[] = $this->db->row();
				}
				if (count($id_conns))
				{
					foreach($id_conns as $j => $id_conn)
					{
						$this->db->query('DELETE FROM phpgw_cc_connections WHERE id_connection='.$id_conn['id_connection']);
						$this->db->query('DELETE FROM phpgw_cc_contact_grps WHERE id_connection='.$id_conn['id_connection']);
					}
				}
					
				$this->db->query('SELECT id_address FROM phpgw_cc_contact_addrs WHERE id_contact='.$id_contact['id_contact']);
				while($this->db->next_record())
				{
					$id_addresses[] = $$this->db->row();
				}
				if (count($id_addresses))
				{
					foreach($id_addresses as $j => $id_addrs)
					{
						$this->db->query('DELETE FROM phpgw_cc_addresses WHERE id_address='.$id_addrs['id_address']);
					}
				}
				$this->db->query('DELETE FROM phpgw_cc_contact WHERE id_contact='.$id_contact['id_contact']);
				$this->db->query('DELETE FROM phpgw_cc_contact_conns WHERE id_contact='.$id_contact['id_contact']);
				$this->db->query('DELETE FROM phpgw_cc_contact_addrs WHERE id_contact='.$id_contact['id_contact']);
			}
		}
		$this->db->query('DELETE FROM phpgw_cc_groups WHERE owner='.$user_info['uidnumber']);
			
		// PREFERENCIAS
		$this->db->query('DELETE FROM phpgw_preferences WHERE preference_owner='.$user_info['uidnumber']);
			
		// ACL
		$this->db->query('DELETE FROM phpgw_acl WHERE acl_account='.$user_info['uidnumber']);
		
		// Corrigir
		$return['status'] = true;
		return $return;
	}

	function delete_group($gidnumber)
	{
		// ACL
		$this->db->query('DELETE FROM phpgw_acl WHERE acl_location='.$gidnumber);
		$this->db->query('DELETE FROM phpgw_acl WHERE acl_account='.$gidnumber);
		
		// Corrigir
		$return['status'] = true;
		return $return;
	}
	
	function write_log($action, $about)
	{
		$sql = "INSERT INTO phpgw_expressoadmin_log (date, manager, action, userinfo) "
		. "VALUES('now','" . $_SESSION['phpgw_info']['expresso']['user']['account_lid'] . "','" . strtolower($action) . "','" . strtolower($about) . "')";
		if (!$this->db->query($sql))
		{
			//echo pg_last_error();
			return false;
	}
	
		return true;
	}
	
	function get_sieve_info()
	{
		$this->db->query('SELECT profileID,imapenablesieve,imapsieveserver,imapsieveport FROM phpgw_emailadmin');
		
		$i=0;
		while($this->db->next_record())
		{
			$serverList[$i]['profileID']		= $this->db->f(0);
			$serverList[$i]['imapenablesieve']	= $this->db->f(1);
			$serverList[$i]['imapsieveserver']	= $this->db->f(2);
			$serverList[$i]['imapsieveport']	= $this->db->f(3);
			++$i;
		}
		
		return $serverList;
	}
	
	function get_apps($account_lid)
	{
		$this->db->query("SELECT * FROM phpgw_expressoadmin_apps WHERE manager_lid = '".$account_lid."'");
		
		while($this->db->next_record())
		{
			$tmp = $this->db->row();
			$availableApps[$tmp['app']] = 'run'; 
		}
			
		return $availableApps;
	}
	
	function get_sambadomains_list()
	{
		$query = "SELECT * FROM phpgw_expressoadmin_samba ORDER by samba_domain_name ASC"; 
		$this->db->query($query);
		while($this->db->next_record())
			$result[] = $this->db->row();
		return $result;
	}
	
	function exist_domain_name_sid($sambadomainname, $sambasid)
	{
		$query = "SELECT * FROM phpgw_expressoadmin_samba WHERE samba_domain_name='$sambadomainname' OR samba_domain_sid='$sambasid'"; 
		$this->db->query($query);
		while($this->db->next_record())
			$result[] = $this->db->row();
		
		if (count($result) > 0)
			return true;
		else
			return false;
	}
	
	function delete_sambadomain($sambadomainname)
	{
		$this->db->query("DELETE FROM phpgw_expressoadmin_samba WHERE samba_domain_name='$sambadomainname'");
		return;
	}
	
	function add_sambadomain($sambadomainname, $sambasid)
	{
		$sql = "INSERT INTO phpgw_expressoadmin_samba (samba_domain_name, samba_domain_sid) VALUES('$sambadomainname','$sambasid')";
		$this->db->query($sql);
		return;
	}
	
	function test_db_connection($params)
	{
		$host = $params['host'];
		$port = $params['port'];
		$name = $params['name'];
		$user = $params['user'];
		$pass = $params['pass'];
		
		$con_string = "host=$host port=$port dbname=$name user=$user password=$pass";
		if ($db = pg_connect($con_string))
		{
			pg_close($db);
			$result['status'] = true;
		}
		else
		{
			$result['status'] = false;
		}
			
		return $result;
	}
	
	function manager_lid_exist($manager_lid)
	{
		$query = "SELECT manager_lid FROM phpgw_expressoadmin_acls WHERE manager_lid = '" . $manager_lid . "'";
		$this->db->query($query);
		while($this->db->next_record())
			$result[] = $this->db->row();
		if (isset($result) && count($result) > 0)
			return true;
		else
			return false;
	}
	
	function delete_manager($uid, $uidNumber){
		if($this->manager_lid_exist($uid)){
			$this->db->query("DELETE FROM phpgw_expressoadmin_acls WHERE manager_lid = '".$uid."'");
			$this->db->query("DELETE FROM phpgw_expressoadmin_apps WHERE manager_lid = '".$uid."'");
			$this->db->query("DELETE FROM phpgw_acl WHERE acl_appname = 'expressoadmin' AND acl_account = '" . $uidNumber . "'");
		}
		$return['status'] = true;
		return $return;
	}
	
	function create_manager($params, $manager_acl)
	{

                //Insere novas regras
                foreach ($manager_acl as $acl)
                {
                    $fields = array(
                                    'manager_lid' => $params['ea_select_manager'],
                                    'context' =>    $params['context'],
                                    'acl_name' => $acl,
                                   );

                    $this->db->insert('phpgw_expressoadmin_acls', $fields);
                }

			
		//Escrevre no Banco as aplicações que o gerente tem direito de disponibilizar aos seus usuarios.
		if (count($_POST['applications_list']))
		{
			foreach($_POST['applications_list'] as $app=>$value)
			{
				$sql = "INSERT INTO phpgw_expressoadmin_apps (manager_lid, context, app) "
				. "VALUES('" . $_POST['manager_lid'] . "','" . $_POST['context'] . "','" . $app . "')";
				$this->db->query($sql);
			}
		}
		
		return;
	}
	
	function save_manager($params, $manager_acl)
	{


		$params['manager_lid'] = $params['hidden_manager_lid'];
		
                //Deleta todas as acls do Gerente
                $this->db->delete('phpgw_expressoadmin_acls',array('manager_lid' => $params['manager_lid'],'context' => $params['old_url_context']));

		//Insere novas regras
                foreach ($manager_acl as $acl)
                {
                    $fields = array(
                                    'manager_lid' => $params['manager_lid'],
                                    'context' =>    $params['context'],
                                    'acl_name' => $acl,
                                   );

                    $this->db->insert('phpgw_expressoadmin_acls', $fields);
                }
			
		//Deleta as aplicações e adiciona as novas.
		//Deleta
		$sql = "DELETE FROM phpgw_expressoadmin_apps WHERE manager_lid = '" . $params['manager_lid'] . "'";
		$this->db->query($sql);
					
		// Adiciona
		if (count($params['applications_list']))
		{
			foreach($params['applications_list'] as $app=>$value)
			{
				$sql = "INSERT INTO phpgw_expressoadmin_apps (manager_lid, context, app) "
				. "VALUES('" . $params['manager_lid'] . "','" . $params['context'] . "','" . $app . "')";
				$this->db->query($sql);
			}
		}
			
		return;
	}

         function save_calendar_acls($user,$acl,$owner)
         {


             $aclArray = explode('-', $acl);
             $rights = 0;

             foreach ($aclArray as $number)
                 $rights = $rights + $number;

             $this->db->delete('phpgw_acl', array('acl_appname' => 'calendar','acl_location' => $user,'acl_account' => $owner), null, null);

             if($rights > 0)
             {
                 if($this->db->insert('phpgw_acl', array('acl_appname' => 'calendar','acl_location' => $user,'acl_account' => $owner,'acl_rights' => $rights ), null, null, null))
                      return true;
		else
                     return false;
             }else
                 return true;
				
         }

         function get_calendar_acls($owner)
         {

                $colunas = array('acl_appname','acl_location','acl_account','acl_rights');
                $where = array('acl_appname' => 'calendar','acl_account' => $owner);
                $this->db->select('phpgw_acl', $colunas, $where, null, null);

                $return = array();

                include_once 'class.ldap_functions.inc.php';
                $ldap = new ldap_functions();
                include_once 'class.functions.inc.php';
                $function = new functions();


                while ($this->db->next_record())
                {
                    $row = $this->db->row();
                    $return[$ldap->uidnumber2uid($row['acl_location'])] = $function->normalize_calendar_acl($row['acl_rights']);
                    
                }
                return $return;
		
	}
	
}
?>
