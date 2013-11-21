<?php
define('PHPGW_INCLUDE_ROOT','../');	
define('PHPGW_API_INC','../phpgwapi/inc');
include_once(PHPGW_API_INC.'/class.db.inc.php');

class db_functions
{	
	var $db;
	var $user_id;
	
	function db_functions(){
		
		if (is_array($_SESSION['phpgw_info']['expresso']['server']))
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
		$this->user_id = $_SESSION['phpgw_info']['expresso']['user']['account_id'];	
	}

	// BEGIN of functions.
	function read_acl($account_lid)
	{
		$query = "SELECT * FROM phpgw_expressoadmin WHERE manager_lid = '" . $account_lid . "'"; 
		$this->db->query($query);
		while($this->db->next_record())
			$result[] = $this->db->row();
		return $result;
	}
	
	/*
	function get_sectors($params)
	{
		$organization = strtolower($params['organization']);
		
		$result = array();
		// Pesquisa no BD os nomes setores no tabela phpgw_expressoadmin_sectors.
 		$query = "SELECT sector FROM phpgw_expressoadmin_sectors WHERE organization='$organization' ORDER by sector ASC";

        if (!$this->db->query($query))
        	return 'Erro em get_sectors:' . pg_last_error();

		while($this->db->next_record())
			$result[] = $this->db->row();

		return $result;
	}
	*/
	
	function get_next_id()
	{
		// Busco o ID dos accounts
		$query_accounts = "SELECT id FROM phpgw_nextid WHERE appname = 'accounts'";
        if (!$this->db->query($query_accounts))
        	return 'Erro em get_next_id:' . pg_last_error();
		while($this->db->next_record())
			$result_accounts[] = $this->db->row();
		$accounts_id = $result_accounts[0]['id'];
		
		// Busco o ID dos groups
		$query_groups = "SELECT id FROM phpgw_nextid WHERE appname = 'groups'";
        if (!$this->db->query($query_groups))
        	return 'Erro em get_next_id:' . pg_last_error();
		while($this->db->next_record())
			$result_groups[] = $this->db->row();
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
		if (!$this->db->query($sql))
			return 'Erro em increment_id:' . pg_last_error();
		else
			return true;
	}
	
	function add_user2group($gidnumber, $uidnumber)
	{
		$query = "SELECT acl_location FROM phpgw_acl WHERE acl_appname = 'phpgw_group' AND acl_location = '" . $gidnumber . "' AND acl_account = '" . $uidnumber . "'";
		if (!$this->db->query($query))
		{
			$result['status'] = false;
			$result['msg'] = 'Erro em add_user2group:' . pg_last_error();
			return $result;
		}
		while($this->db->next_record())
			$user_in_group[] = $this->db->row();
		
		if (count($user_in_group) == 0)
		{
			$sql = "INSERT INTO phpgw_acl (acl_appname, acl_location, acl_account, acl_rights) "
			. "VALUES('phpgw_group','" . $gidnumber . "','" . $uidnumber . "','1')";
			if (!$this->db->query($sql))
			{
				$result['status'] = false;
				$result['msg'] = 'Erro em add_user2group:' . pg_last_error();
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
			$result['msg'] = 'Erro em add_user2group:' . pg_last_error();
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
				$result['msg'] = 'Erro em add_user2group:' . pg_last_error();
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
			$result['msg'] = 'Erro em add_pref_changepassword:' . pg_last_error();
			return $result;
		}
		while($this->db->next_record())
			$user_pref_changepassword[] = $this->db->row();
		
		if (count($user_pref_changepassword) == 0)
		{
			$sql = "INSERT INTO phpgw_acl (acl_appname, acl_location, acl_account, acl_rights) "
			. "VALUES('preferences','changepassword','" . $uidnumber . "','1')";
			if (!$this->db->query($sql))
			{
				$result['status'] = false;
				$result['msg'] = 'Erro em add_pref_changepassword:' . pg_last_error();
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
			$result['msg'] = 'Erro em add_pref_changepassword:' . pg_last_error();
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
				$result['msg'] = 'Erro em remove_pref_changepassword:' . pg_last_error();
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
			foreach($apps as $app => $value)
			{
				$query = "SELECT * FROM phpgw_acl WHERE acl_appname = '".$app."' AND acl_location = 'run' AND acl_account = '" . $id . "'";
				if (!$this->db->query($query))
				{
					$result['status'] = false;
					$result['msg'] = 'Erro em add_id2apps: ' . pg_last_error();
					return $result;
				}
				
				while($this->db->next_record())
					$user_app[] = $this->db->row();
					
				if (count($user_app) == 0)
				{
					$sql = "INSERT INTO phpgw_acl (acl_appname, acl_location, acl_account, acl_rights) "
					. "VALUES('".$app."','run','" . $id . "','1')";
						
					if (!$this->db->query($sql))
					{
						$result['status'] = false;
						$result['msg'] = 'Erro em add_id2apps: ' . pg_last_error();
						return $result;
					}
					else
					{
						$this->write_log("Adicionado aplicativo $app ao id",$id,'','','');	
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
					$result['msg'] = 'Erro em remove_id2apps:' . pg_last_error();
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
						$result['msg'] = 'Erro em remove_id2apps:' . pg_last_error();
						return $result;
					}
					else
					{
						$this->write_log("Removido aplicativo $app do id",$id,'','','');	
					}
				}
			}
		}
		return $result;
	}


	function get_user_info($uidnumber)
	{
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
		while($this->db->next_record())
			$changepassword[] = $this->db->row();
		$return['changepassword'] = $changepassword[0]['acl_rights'];
		
		// Apps
		$query = "SELECT acl_appname FROM phpgw_acl WHERE acl_account = '".$uidnumber."' AND acl_location = 'run'";
		$this->db->query($query);
		while($this->db->next_record())
			$user_apps[] = $this->db->row();
			
		if ($user_apps)
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
				$result['msg'] = 'Erro em set_user_password: ' . pg_last_error();
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
	
	function delete_user($uidnumber)
	{
		// AGENDA
		$this->db->query('SELECT cal_id FROM phpgw_cal WHERE owner ='.$uidnumber);
		while($this->db->next_record())
		{
			$ids[] = $this->db->row();
		}
		if (count($ids))
		{
			foreach($ids as $i => $id)
			{
				$this->db->query('DELETE FROM phpgw_cal WHERE cal_id='.$id['cal_id']);
				$this->db->query('DELETE FROM phpgw_cal_user WHERE cal_id='.$id['cal_id']);
				$this->db->query('DELETE FROM phpgw_cal_repeats WHERE cal_id='.$id['cal_id']);
				$this->db->query('DELETE FROM phpgw_cal_extra WHERE cal_id='.$id['cal_id']);
			}
		}
			
		// CONATOS pessoais e grupos.
		$this->db->query('SELECT id_contact FROM phpgw_cc_contact WHERE id_owner ='.$uidnumber);
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
		$this->db->query('DELETE FROM phpgw_cc_groups WHERE owner='.$uidnumber);
			
		// PREFERENCIAS
		$this->db->query('DELETE FROM phpgw_preferences WHERE preference_owner='.$uidnumber);
			
		// ACL
		$this->db->query('DELETE FROM phpgw_acl WHERE acl_account='.$uidnumber);
		
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
	
	function write_log($action, $groupinfo='', $userinfo='', $appinfo='', $msg_log='')
	{
		$sql = "INSERT INTO phpgw_expressoadmin_log (date, manager, action, groupinfo, userinfo, appinfo, msg) "
		. "VALUES('now','" . $_SESSION['phpgw_info']['expresso']['user']['account_lid'] . "','" . strtolower($action) . "','" . strtolower($groupinfo) . "','" . strtolower($userinfo) . "','" . strtolower($appinfo) . "','" .strtolower($msg_log) . "')";
		$this->db->query($sql);
		return;
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

        function add_edit_user_data($edit_user)
	        {
		$query = "SELECT 'uidnumber' FROM phpgw_ldap_users WHERE uidnumber='".$edit_user['uidnumber']."'";
		$this->db->query($query);
		if($this->db->next_record())
			{
			$sql = "UPDATE phpgw_ldap_users SET uid='".$edit_user['uid']."',uidnumber='".$edit_user['uidnumber']."',givenname='".$edit_user['givenname']."',sn='".$edit_user['sn']."',cn='".$edit_user['cn']."',mail='".$edit_user['mail']."',mailalternateaddress='".$edit_user['mailalternateaddress']."' WHERE uid='".$edit_user['uid']."'";
			$this->db->query($sql);
                	}
			else
			{
			$sql = "INSERT INTO phpgw_ldap_users VALUES('".$edit_user['uid']."','".$edit_user['uidnumber']."','".$edit_user['sn']."','".$edit_user['cn']."','".$edit_user['givenname']."','".$edit_user['mail']."','".$edit_user['mailalternateaddress']."')";
			$this->db->query($sql);
			}
                return true;
	        }
/*        function get_user_list($search)
                {
		$users_ldap="";
                $query = "SELECT * FROM phpgw_ldap_users WHERE uidnumber LIKE '%".$search."%' OR uid LIKE '%".$search."%' OR cn LIKE '%".$search."%' OR sn LIKE '%".$search."%' OR givenname LIKE '%".$search."%' OR mail LIKE '%".$search."%' OR mailalternateaddress LIKE '%".$search."%'";
                $this->db->query($query);
		$i=0;
                while($this->db->next_record()) 
			{
			$users_ldap[$i]['uid']            	= $this->db->f(0);
			$users_ldap[$i]['uidnumber']      	= $this->db->f(1);
			$users_ldap[$i]['cn']        		= $this->db->f(3);
			$users_ldap[$i]['mail']        		= $this->db->f(5);
			++$i;
			}	     
		return $users_ldap;
                }
*/

}
?>
