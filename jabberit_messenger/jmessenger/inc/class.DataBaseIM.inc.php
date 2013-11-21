<?php
  /***************************************************************************\
  *  Expresso - Expresso Messenger                                            *
  *  	- Alexandre Correia / Rodrigo Souza							          *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/

define('PHPGW_INCLUDE_ROOT','../../');	
define('PHPGW_API_INC','../../phpgwapi/inc');
require_once(PHPGW_API_INC . '/class.db.inc.php');
//require_once "class.fileDefine.inc.php";
	
class DataBaseIM
{	
	private $db;
	private $db_name;
	private $db_host;
	private $db_port;
	private $db_user;
	private $db_pass;
	private $db_type;
	private $user_id;
	private $fileD;
	
	public final function __construct()
	{
		//$this->fileD = new fileDefine();
		$this->db_name = $_SESSION['phpgw_info']['jabberit_messenger']['server']['db_name'];
		$this->db_host = $_SESSION['phpgw_info']['jabberit_messenger']['server']['db_host'];
		$this->db_port = $_SESSION['phpgw_info']['jabberit_messenger']['server']['db_port'];
		$this->db_user = $_SESSION['phpgw_info']['jabberit_messenger']['server']['db_user'];
		$this->db_pass = $_SESSION['phpgw_info']['jabberit_messenger']['server']['db_pass'];
		$this->db_type = $_SESSION['phpgw_info']['jabberit_messenger']['server']['db_type'];
		$this->user_id = $_SESSION['phpgw_info']['jabberit_messenger']['user_id'];		
		$this->connectDB();
	}

	private final function connectDB()
	{
		$this->db = new db();
		$this->db_name = ( !$this->db_name ) ? $_SESSION['phpgwinfo']['db_name'] : $this->db_name;
		$this->db_host = ( !$this->db_host ) ? $_SESSION['phpgwinfo']['db_host'] : $this->db_host;
		$this->db_port = ( !$this->db_port ) ? $_SESSION['phpgwinfo']['db_port'] : $this->db_port;
		$this->db_user = ( !$this->db_user ) ? $_SESSION['phpgwinfo']['db_user'] : $this->db_user;
		$this->db_pass = ( !$this->db_pass ) ? $_SESSION['phpgwinfo']['db_pass'] : $this->db_pass;
		$this->db_type = ( !$this->db_type ) ? $_SESSION['phpgwinfo']['db_type'] : $this->db_type;
		
		$this->db->connect($this->db_name,$this->db_host,$this->db_port,$this->db_user,$this->db_pass,$this->db_type);		
	}	

	public final function editHostJabber($pItem)
	{
		$hostsJabber = unserialize($this->getHostsJabber());
		$findHosts	= explode(":", $pItem['item']);
		$return = "";

        $hostsJabber_count = count($hostsJabber);
		for( $i = 0 ; $i < $hostsJabber_count; ++$i )
			if( $hostsJabber[$i]['org'] == $findHosts[0] && $hostsJabber[$i]['jabberName'] == $findHosts[1] )
			{
				$return = "org:" . $hostsJabber[$i]['org'] . ";" .
						  "jabberName:" . $hostsJabber[$i]['jabberName'] . ";" .						  	 
						  "serverLdap:" . $hostsJabber[$i]['serverLdap'] . ";" .
						  "contextLdap:" . $hostsJabber[$i]['contextLdap'] . ";" .
						  "user:" . $hostsJabber[$i]['user'] . ";" .
						  "password:" . $hostsJabber[$i]['password'] ;						  						  						  						  
			}

		return trim($return);
	}
	
	public final function getApplicationsEnabled()
	{
		
		$this->db->query("SELECT * FROM phpgw_config WHERE config_app = 'phpgwapi' and config_name ='apps_jabberit'");
		if($this->db->num_rows())
		{
			$tmp = "";
			while($this->db->next_record())
			{
				$tmp[]= $this->db->row();
			}
			return $tmp[0]['config_value'];
		}
		return false;
	}
	
	public final function getApplicationsList()
	{
		$this->db->query("SELECT * FROM phpgw_applications WHERE app_enabled = '1' order by app_name");
		if($this->db->num_rows())
		{
			while ($this->db->next_record())
			{
				$app = $this->db->f('app_name');
				$title = @$GLOBALS['phpgw_info']['apps'][$app]['title'];
				if (empty($title))
				{
					$title = lang($app) == $app.'*' ? $app : lang($app);
				}
				$apps[$app] = array(
					'title'  => $title,
					'name'   => $app,
					'status' => $this->db->f('app_enabled')
				);
			}
		}
		return $apps;
	}

	public final function get_accounts_acl()
	{
		$query  = "SELECT acl_account FROM phpgw_acl WHERE acl_location IN (SELECT CAST(acl_account AS varchar) FROM phpgw_acl WHERE acl_appname = 'jabberit_messenger') ";
		$query .= "UNION SELECT acl_account FROM phpgw_acl WHERE acl_appname = 'jabberit_messenger'";
		
		if( $this->db->query($query) )	
		{
			$users = array();
			$new_users = array();
			while($this->db->next_record())
				$users[] = $this->db->row();

			if(is_array($users))
				foreach($users as $tmp)
					$new_users[] = $tmp['acl_account'];
			
			return $new_users;
		}
		
		return false;
	}
	
	public final function getGroupsBlocked()
	{
		$return = "";
		
		if( $this->db )
		{
			$query = "SELECT * FROM phpgw_config WHERE config_app = 'phpgwapi' AND config_name = 'groups_locked_jabberit';";	
			
			if($this->db->query($query))
			{

				if ( $this->db->query($query) )
				{	
					while($this->db->next_record())
						$result[] = $this->db->row();
				}
				
				if( count($result) > 0 )
					$return = $result[0]['config_value'];
			}
		}
		
		return $return;
	}
	
	public final function getGroupsSearch()
	{
		$return = "";
	
		if( $this->db )
		{
			$query = "SELECT * FROM phpgw_config WHERE config_app = 'phpgwapi' AND config_name = 'groups_search_jabberit';";
			
			if($this->db->query($query))
			{
				while($this->db->next_record())
					$result[] = $this->db->row();				
			}

			if( count($result) > 0 )
				$return = $result[0]['config_value'];
		}
		
		return $return;
	}
	
	public final function getHostsJabber()
	{
		$return = "";
	
		if( $this->db )
		{
			$query = "SELECT * FROM phpgw_config WHERE config_app = 'phpgwapi' AND config_name = 'map_org_realm_jabberit';";
			
			if($this->db->query($query))
			{
				while($this->db->next_record())
					$result[] = $this->db->row();				
			}

			if( count($result) > 0 )
				$return = $result[0]['config_value'];
		}
		
		return $return;
	}
	
	public final function getPreferences()
	{
		$result = array();
		$query = "SELECT * FROM phpgw_preferences WHERE preference_owner = '".$this->user_id."' AND preference_app = 'jabberit_messenger'";
		
 		if ( $this->db->query($query) )
		{	
			while($this->db->next_record())
				$result[] = $this->db->row();
	
			if( count($result) > 0 )
			{
				$_return = unserialize($result[0]['preference_value']);
				
				if( is_array($_return) )
					return $_return['preferences'];
				else
					return $_return;
			}
		}

		return "openWindowJabberit:true;openWindowJabberitPopUp:false;flagAwayIM:5";
	}

	public final function setApplications($pApplications)
	{
		$apps = serialize($pApplications);
		
		if( $this->db )
		{
			$query = "SELECT * FROM phpgw_config WHERE config_app = 'phpgwapi' and config_name ='apps_jabberit'";
				
			$this->db->query($query);
					
			if(!$this->db->next_record())
			{
				$query = "INSERT INTO phpgw_config(config_app,config_name,config_value) VALUES('phpgwapi','apps_jabberit','".$apps."')";
				$this->db->query($query);
				return true;
			}
			else
			{
				$query = "UPDATE phpgw_config SET config_value = '".$apps."' WHERE config_app = 'phpgwapi' AND config_name = 'apps_jabberit'";
				$this->db->query($query);
				return true;
			}
		}
		return false;	
	}
	
	public final function setAttributesLdap($pAttributes)
	{
		$values = $pAttributes['conf'];
		$attributesOrg = "";		

		if( $this->db )
		{
			$query = "SELECT * from phpgw_config WHERE config_app = 'phpgwapi' and config_name = 'attributes_org_ldap_jabberit'";

			if ( $this->db->query($query) )
			{	
				while($this->db->next_record())
					$result[] = $this->db->row();
		
				if(count($result) > 0)
					$attributesOrg = $result[0]['config_value'];
			}

			if( trim($attributesOrg) == "" )
			{
				$query = "INSERT INTO phpgw_config(config_app,config_name,config_value) VALUES('phpgwapi','attributes_org_ldap_jabberit','".$values."')";
				$this->db->query($query);
				
				$attr = explode(";", $values);
				$values = "<return><ou attr='".$attr[1]."'>".$attr[0]."</ou></return>";
				return $values;
			}
			else
			{
				$org = explode(",", $attributesOrg);
				$newValue = explode(";", $values);
				
				foreach( $org as $tmp )
				{
					$attr = explode(";",$tmp);
					if( strtolower(trim($attr[0])) == strtolower(trim($newValue[0])) )
						return false;
				}

				$values = $values . "," . $attributesOrg;
				$query = "UPDATE phpgw_config SET config_value = '".$values."' WHERE config_app = 'phpgwapi' AND config_name = 'attributes_org_ldap_jabberit'";
				$this->db->query($query);

				$return = explode(",",$values);
				natcasesort($return);

				$values = "<return>";
				
				foreach($return as $tmp)
				{
					$attr = explode(";", $tmp);
					$values .= "<ou attr='" . $attr[1] . "'>" . $attr[0] . "</ou>";
				}
					
				$values .= "</return>";
				
				return $values;				
			}
		}
		return false; 
	}

	public final function setAddGroupsSearch($pData)
	{
		if( $pData)
		{
			if( $this->db )
			{
				$query = "SELECT * FROM phpgw_config WHERE config_app = 'phpgwapi' AND  config_name = 'groups_search_jabberit';";
				
				if( $this->db->query($query) )
				{
					while( $this->db->next_record())
						$result[] = $this->db->row();				
				}
				
				if( count($result) == 0 )
				{
					$query = "INSERT INTO phpgw_config(config_app,config_name,config_value) VALUES('phpgwapi','groups_search_jabberit','".serialize($pData)."');";
					$this->db->query($query);
					return true;
				}
				else
				{
					$keyLdap = array_keys($pData);
					$resultQuery = unserialize($result[0]['config_value']);					
					
					if( is_array(unserialize($pData[$keyLdap[0]])) )
						$resultQuery[$keyLdap[0]] = $pData[$keyLdap[0]];
					else
						unset($resultQuery[$keyLdap[0]]);

					if( count($resultQuery))
						$query = "UPDATE phpgw_config SET config_value = '".serialize($resultQuery)."' WHERE config_app = 'phpgwapi' AND config_name = 'groups_search_jabberit';";
					else
						$query = "DELETE FROM phpgw_config WHERE config_app = 'phpgwapi' AND config_name = 'groups_search_jabberit';";
						
					$this->db->query($query);
					return true;
				}
			}			
		}
	}

	public final function setGroupsLocked($pGroups)
	{
		$groups = "";
		
		if( is_array($pGroups) )
		{
			foreach($pGroups as $tmp)		
				if(trim($tmp) != "")
					$groups .= $tmp . ";";
		
			$groups = substr($groups, 0, strlen($groups) - 1 );		
		}
		
		if( $this->db )
		{
			$query = "SELECT * FROM phpgw_config WHERE config_app = 'phpgwapi' AND config_name = 'groups_locked_jabberit';";	
			
			if($this->db->query($query))
			{

				if ( $this->db->query($query) )
				{	
					while($this->db->next_record())
						$result[] = $this->db->row();
				}

				if( count($result) == 0 )
				{
					$query = "INSERT INTO phpgw_config(config_app,config_name,config_value) VALUES('phpgwapi','groups_locked_jabberit','".$groups."');";
					$this->db->query($query);
					return true;
				}
				else
				{
					$query = "UPDATE phpgw_config SET config_value = '".trim($groups)."' WHERE config_app = 'phpgwapi' AND config_name = 'groups_locked_jabberit';";
					$this->db->query($query);
					return true;
				}
			}
		}
		
		return false;
	}
	
	public final function setHostJabber($pParam)
	{
		$confHostsJabber =  array();

		foreach($pParam as $key => $itens)
			$confHostsJabber[$key] = ( $key === 'org' ) ? strtoupper($itens) : $itens;

		if( $this->db )
		{
			$query = "SELECT * FROM phpgw_config WHERE config_app = 'phpgwapi' AND config_name = 'map_org_realm_jabberit';";
			
			if( $this->db->query($query) )
			{
				while($this->db->next_record())
					$result[] = $this->db->row();				
			}
			
			if( count($result) == 0 )
			{
				$return = "<return><confServer ou='".strtoupper($confHostsJabber['org'])."' serverName='".$confHostsJabber['jabberName']."'>".strtoupper($confHostsJabber['org']).":".$confHostsJabber['jabberName']."</confServer></return>";				
				$hostsJabber[0] = $confHostsJabber;
				
				$this->fileD->ldapExternal($hostsJabber);
				 
				$query = "INSERT INTO phpgw_config(config_app, config_name, config_value) VALUES('phpgwapi','map_org_realm_jabberit','".serialize($hostsJabber)."')";
				$this->db->query($query);				
			}
			else
			{
				$resultQuery = unserialize($result[0]['config_value']);	
				$foundOrg = false;
				
				foreach($resultQuery as $key => $itens)
				{
					$foundString = array_search($confHostsJabber['org'], $itens);
					if( $foundString )
					{
						$foundOrg = $foundString;
						$ky = $key;
					}
				}	

				if( ! $foundOrg )
					$resultQuery[] = $confHostsJabber;	
				else
					$resultQuery[$ky] = $confHostsJabber;

				$return = "<return>";
				
				foreach( $resultQuery as $itens )
					$return .= "<confServer ou='".$itens['org']."' serverName='".$itens['jabberName']."'>".$itens['org'].":".$itens['jabberName']."</confServer>";
				
				$return .= "</return>";
				
				$this->fileD->ldapExternal($resultQuery);
				
				$query = "UPDATE phpgw_config SET config_value = '".serialize($resultQuery)."' WHERE config_name = 'map_org_realm_jabberit';";
				$this->db->query($query);
			}
			return $return;
		}	
		return false;
	}
	
	public final function setOuGroupsLocked($pGroup)
	{
		
		function strallpos($haystack, $needle, $offset = 0)
		{
		    $result = array();
		    for($i = $offset; $i< strlen($haystack); ++$i )
		    {
		        $pos = strpos($haystack,$needle,$i);
		        if($pos !== FALSE)
		        {
		            $offset =  $pos;
		            if($offset >= $i)
		                $result[] = $i = $offset;
		        }
		    }
	    	
	    	return $result;
		} 

		$group = $pGroup['group'];
		$gidnumber = $pGroup['gidnumber'];
		$organization = strtoupper(trim($pGroup['ou']));

		$posAll = strallpos($organization, "OU=" );
		$orgs = array();

        $posAll_count = count($posAll);
		for( $i = 0 ; $i < $posAll_count; ++$i )
		{
			$pos = strpos($organization, ",");
			$tmpString = substr($organization, $posAll[$i] + 3);
			$orgs[] = substr($tmpString, 0, strpos($tmpString, ","));
		}

		$organization = implode("/", array_reverse($orgs));

		if( $this->db )
		{
			$query = "SELECT * FROM phpgw_config WHERE config_app = 'phpgwapi' AND config_name = 'groups_locked_jabberit';";	
			
			if($this->db->query($query))
			{

				if ( $this->db->query($query) )
				{	
					while($this->db->next_record())
						$result[] = $this->db->row();
				}

				$groupsLocked = explode(";",$result[0]['config_value']);
					
				foreach( $groupsLocked as $tmp )
				{
					$aux = explode(":", $tmp);
					if(($group.":".$gidnumber) == ($aux[0].":".$aux[1]))
					{
						if( $aux[2] )
						{
							$ou_groups = explode(",",$aux[2]);
							natcasesort($ou_groups);
							$key = array_search($organization, $ou_groups);
							
							if( $key === false )
								array_push($ou_groups, $organization);
							
							$groups .= $group.":".$gidnumber.":";
							
							$return = "<return>";						
							
							foreach($ou_groups as $tmp)
							{
								$return .= "<ou attr='".$tmp."'>".$tmp."</ou>";
								$groups .= $tmp .",";	
							}
							
							$return .= "</return>";
							
							$groups  = substr($groups,0,strlen($groups)-1);
							$groups .= ";";
						}
						else
						{
							$groups .= $group.":".$gidnumber.":".$organization.";";
							$return = "<return><ou attr='".$organization."'>".$organization."</ou></return>";
						}
					}
					else
						$groups .= $tmp . ";" ;
				}

				$groups = substr($groups,0,strlen($groups)-1);

				$query = "UPDATE phpgw_config SET config_value = '".trim($groups)."' WHERE config_app = 'phpgwapi' AND config_name = 'groups_locked_jabberit';";
				$this->db->query($query);
				
				return $return;
			}
		}
		
		return false;
	}
	
	public final function removeAttributesLdap($pOrg)
	{
		$organization = $pOrg['org'];
		
		if( $this->db )
		{
			$query = "SELECT * from phpgw_config WHERE config_app = 'phpgwapi' and config_name = 'attributes_org_ldap_jabberit'";
				
			if ( $this->db->query($query) )
			{	
				while( $this->db->next_record() )
					$result[] = $this->db->row();
		
				if( count($result) > 0 )
					$attributesOrg = $result[0]['config_value'];
			}

			$attributesOrg = explode(",", $attributesOrg);
			$newValue = ""; 
			foreach($attributesOrg as $tmp)
			{
				$attr = explode(";",$tmp);
				 
				if( strtolower(trim($attr[0])) != strtolower(trim($organization)))
				{
					$newValue .= $attr[0] . ";" . $attr[1] . ",";
				}
			}
			
			$newValue = substr($newValue, 0,(strlen($newValue) -1 ));
			
			if( trim($newValue) != "")
				$query = "UPDATE phpgw_config SET config_value = '".$newValue."' WHERE config_app = 'phpgwapi' AND config_name = 'attributes_org_ldap_jabberit'";
			else
				$query = "DELETE from phpgw_config where config_name = 'attributes_org_ldap_jabberit'";
				
			if( $this->db->query($query))
				return true;
			else
				return false;
		}
		return false;	
	}

	public final function removeHostsJabber($pItem)
	{
		$query = "SELECT * FROM phpgw_config WHERE config_app = 'phpgwapi' AND config_name = 'map_org_realm_jabberit';";

		if( $this->db )
		{
			if($this->db->query($query))
			{
				while($this->db->next_record())
					$result[] = $this->db->row();
					
				if( count($result) > 0 )
				{
					$confHostsOrgs = unserialize($result[0]['config_value']);
					$hosts = explode(":", $pItem['item']);
					$key = "";

					if( count($confHostsOrgs) > 0 )
					{
                        $confHostsOrgs_count = count($confHostsOrgs);
						for( $i = 0; $i < $confHostsOrgs_count; ++$i)
							if( $confHostsOrgs[$i]['org'] == $hosts[0] && $confHostsOrgs[$i]['jabberName'] == $hosts[1])
								$key = $i;	

						array_splice($confHostsOrgs, $key, 1);
				
						if(count($confHostsOrgs) > 0)
						{					
							$this->fileD->ldapExternal($confHostsOrgs);
							$query = "UPDATE phpgw_config SET config_value = '".serialize($confHostsOrgs)."' WHERE config_name = 'map_org_realm_jabberit';";
						}
						else
						{
							$this->fileD->ldapExternal("");
							$query = "DELETE FROM phpgw_config WHERE config_name = 'map_org_realm_jabberit';";
						}
					}
					else
					{	
						$this->fileD->ldapExternal("");						
						$query = "DELETE FROM phpgw_config WHERE config_name = 'map_org_realm_jabberit';";
					}

					if( $this->db->query($query) )
						return "true";
				}		
			}			
		}
		return "false";
	}

	public final function removeOuGroupsLocked($pGroup)
	{
		$group = $pGroup['group'];
		$gidnumber = $pGroup['gidnumber'];
		$organization = strtoupper($pGroup['ou']);
		$return = false;
		
		if( $this->db )
		{
			$query = "SELECT * FROM phpgw_config WHERE config_app = 'phpgwapi' AND config_name = 'groups_locked_jabberit';";	
			
			if($this->db->query($query))
			{

				if ( $this->db->query($query) )
				{	
					while($this->db->next_record())
						$result[] = $this->db->row();
				}

				$groupsLocked = explode(";",$result[0]['config_value']);
				
				foreach( $groupsLocked as $tmp )
				{
					$aux = explode(":",$tmp);
					
					if(($group.":".$gidnumber) == ($aux[0].":".$aux[1]))
					{
						$ous = explode(",", $aux[2]);
						$key = array_search($organization, $ous);

						if( $key !== false )
							unset($ous[$key]);

						$groups .= $group.":".$gidnumber.":";
						
						foreach($ous as $ouTmp)
							$groups .= $ouTmp .",";	
						
						$groups  = substr($groups,0,strlen($groups)-1);
						$groups .= ";";
					}
					else
						$groups .= $tmp . ";" ;									
				}
					
				$groups  = substr($groups,0,strlen($groups)-1);
			
				$query = "UPDATE phpgw_config SET config_value = '".trim($groups)."' WHERE config_app = 'phpgwapi' AND config_name = 'groups_locked_jabberit';";

				if( $this->db->query($query))
					$return = true;
			}
		}	
		
		return $return;
	}

}
?>