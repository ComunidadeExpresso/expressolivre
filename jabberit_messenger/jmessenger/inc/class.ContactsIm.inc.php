<?php

require_once "class.DataBaseIM.inc.php";
require_once "class.LdapIM.inc.php";

class ContactsIm
{
	private $db;
	private $dn_User;
	private $hostsJabberLdap;	
	private $ldap;
	private $ou_User;
	private $serverJabber;
	private $serverLdap;
	private $attribute;
		
	function __construct()
	{
		$this->ldap	= new LdapIM();
		$this->db	= new DataBaseIM();

		// (DN) User
		$this->dn_User = $_SESSION['phpgw_info']['jabberit_messenger']['account_dn'];

		// (OU) User
		$this->ou_User = $this->dn_User;
		$this->ou_User = substr($this->ou_User, strpos($this->ou_User, "ou=") );
		$this->ou_User = strtoupper(substr($this->ou_User, 0, strpos($this->ou_User, ",dc=")));
		
		// Server Name Jabber
		$this->serverJabber = $_SESSION['phpgw_info']['jabberit_messenger']['name_jabberit'];
	
		// Server Name Ldap
		$this->serverLdap	= $_SESSION['phpgw_info']['jabberit_messenger']['server_ldap_jabberit'];
		
		// Hosts Jabber / Ldap
		$this->hostsJabberLdap = unserialize($_SESSION['phpgw_info']['jabberit_messenger']['map_org_realm_jabberit']);	
		
		if ( file_exists('attributeLdap.php') )
		{
			require_once('attributeLdap.php');
			$this->attribute = trim($attributeTypeName);
		}
		else
			$this->attribute = "uid";
		
	}

	public final function getListContacts($param)
	{
		$order		= array();
		$ou_User	= substr($this->ou_User, (strpos($this->ou_User,"=")+1));
		$return		= "<empty></empty>";
		$users		= $this->getUsersIm($param['name']);
		
		if( count($users) == 0  )
			return "<empty></empty>";
		
		if( $users === "manyresults" )
		{
			if( isset($_SESSION['phpgw_info']['jabberit_messenger']['photo']) )
				unset($_SESSION['phpgw_info']['jabberit_messenger']['photo']);
			
			return "<manyresults></manyresults>";
		}
				
		// Hosts Jabber
		$hostsJabber = unserialize($_SESSION['phpgw_info']['jabberit_messenger']['map_org_realm_jabberit']);
		
		if( is_array($users) )
		{
            $users_count = count($users);
			for($i = 0; $i < $users_count; ++$i)
			{
				if( is_array($hostsJabber) )
				{
					foreach($hostsJabber as $itens)
					{
						if( trim($users[$i]['ou']) === trim($itens['org']) && strpos($users[$i]['jid'],"@") === false )
						{
							$users[$i]['jid'] = $users[$i]['jid']."@".$itens['jabberName'];
						}
						
						if( array_key_exists('ouAll', $users[$i]) && trim($itens['org']) === "*" )
						{
							$users[$i]['jid'] = $users[$i]['jid']."@".$itens['jabberName'];
						}
					}
				}

				if( strpos($users[$i]['jid'],"@") === false )
				{
					$users[$i]['jid'] = $users[$i]['jid']."@".$this->serverJabber;
				}
			}

			foreach($users as $tmp)
			{
				if ( !array_key_exists($tmp['ou'], $order) )
					$order[$tmp['ou']] = array();

				$order[$tmp['ou']][] = '<data><ou>'.$tmp['ou'].'</ou><cn>'.$tmp['cn'].'</cn><mail>'.$tmp['mail'].'</mail><uid>'.$tmp['uid'].'</uid><jid>'.$tmp['jid'].'</jid><photo>'.$tmp['photo'].'</photo></data>';
			}
			
			ksort($order);
				
			$return	= '<uids>';
			foreach ( $order as $key => $val )
				$return .= '<'.$key.'>'.implode('',$val).'</'.$key.'>';
			$return .= '</uids>';
		}
		
		return $return;
	}

	private final function getUsersIm($pName)
	{   
		$array_uids		= array();
		$countUids		= 0;
        $members		= array();
        $result			= array();
        $uidType		= $this->attribute;
		$serversLdap	= unserialize( trim($_SESSION['phpgw_info']['jabberit_messenger']['groups_search']) );

		if( $serversLdap )
		{
			// Usa Grupos Ldap
			$filters = array( );
			
			foreach( $serversLdap as $servers => $groups )
			{
				$filter = '';
				foreach( unserialize($groups) as $group )
					$filter .= '(gidnumber' . strstr( $group, ':' ) . ')';

				$filters[ $servers ] = str_replace( ':', '=', $filter );
			}
			
			foreach( $serversLdap as $key => $tmp )
			{
				if( $key === $this->serverLdap )
				{
					$result[$key] = $this->ldap->getUsersLdapRoot("cn=*".$pName."*");
					$countUids += count($result[$key]);
				}
				else
				{
					if( !$this->groupsLocked() )
					{
						$result[$key] =  $this->ldap->getUsersLdapCatalog("cn=*".$pName."*", $key );
						$countUids += count($result[$key]);
					}
				}
			}
			
			if( $countUids >  $this->ldap->getMaxResults() )
			{
				return "manyresults";
			}
			
			$_RESULT = $this->ldap->getMembers($result, $filters);
			
			foreach( $_RESULT as $key => $value )
				$array_uids = array_merge($array_uids, $_RESULT[$key]);
 
		}
		else
		{
	        // Ldap Root
	        $result[] = $this->ldap->getUsersLdapRoot("cn=*".$pName."*");
	       
			// Ldap Catalog			
			if( count($this->hostsJabberLdap) )
			{
				foreach( $this->hostsJabberLdap as $conf )
				{
					$result[] = $this->ldap->getUsersLdapCatalog("cn=*".$pName."*", $conf['serverLdap'] );
				}
			}

	       	foreach( $result as $value )
	       	{
				$array_uids = array_merge($array_uids, $value );
	       	}
			
			if( count($array_uids) >  $this->ldap->getMaxResults() )
			{
				return "manyresults";
			}
		}

		if( $this->groupsLocked() )
		{
            $orgs[] 			= substr($this->ou_User, ( strpos($this->ou_User, "ou=") + 3 ) );
            $orgsGroupsLocked	= explode(",", $_SESSION['phpgw_info']['jabberit_messenger']['organizationsGroupsLocked']); 
            
			foreach( $orgsGroupsLocked as $tmp )
			{
				if( $tmp != "")
					$orgs[] = $tmp;
			}

            $orgs = array_unique($orgs);

			$_restrict = array();

            $orgs_count = count($orgs);
			for( $i = 0 ; $i < $orgs_count; ++$i )
			{
                $array_uids_count = count($array_uids);
				for( $j = 0 ; $j < $array_uids_count; ++$j )
				{
					if( trim($array_uids[$j]['ou']) === trim($orgs[$i]) )
					{
						$_restrict[] = $array_uids[$j];
					}	
				}	
			}
			
			return $_restrict;
		}
		else
		{	
			return $array_uids;
		}	
	}
	
	private final function groupsLocked()
	{
		$memberShip = array();
		$groupsLocked =  explode(";",$_SESSION['phpgw_info']['jabberit_messenger']['groups_locked']);
		
		foreach($_SESSION['phpgw_info']['jabberit_messenger']['membership'] as $tmp)
			$memberShip[] = $tmp['account_name'];
		
		foreach($groupsLocked as $tmp)
		{
			$groups = explode(":", $tmp);
			
			if( array_search($groups[1], $memberShip) !== False )
			{	
				$_SESSION['phpgw_info']['jabberit_messenger']['organizationsGroupsLocked'] = $groups[2]; 
				return true;
			}
		}
		
		return false;
	}
	
	private final function strallpos($haystack, $needle, $offset = 0)
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

	public final function verifyAddNewContact($pUid)
	{
		$groupsLocked	= explode(";",$_SESSION['phpgw_info']['jabberit_messenger']['groups_locked']);
		$gidNumbers		= array();
		$uid			= $pUid['uid'];
		$uid_User		= substr($this->dn_User, 0, strpos($this->dn_User, ","));
		$uid_User		= substr($uid_User, 4);
		
		foreach($groupsLocked as $tmp)
		{
			$groups = explode(":", $tmp);
			$gidNumbers[] = $groups[1];
		}

		$filter_gid = implode(")(gidnumber=",$gidNumbers);
	    $filter_gid = "(gidnumber=". $filter_gid. ")";
	
		$result = $this->ldap->getGroupsMemberUid( $filter_gid, "localhost" );

		if( $result && is_array($result) )
		{
			array_shift($result);
			$i = 0;
			
			foreach($result as $value)
			{
				$Groups[$i]['dn'] = $value['dn'];
				$Groups[$i]['gidnumber'] = $value['gidnumber'][0];
				if(array_key_exists('memberuid',$value))
				{
					array_shift($value['memberuid']);
					$Groups[$i++]['memberuid'] = $value['memberuid'];
				}
			}

			$search = array();
			$search_Gid = array();

			// Verifica Uid em Grupo Bloqueado
			foreach($Groups as $value)
			{			
				if( array_search( $uid , $value['memberuid'] ) !== false )
				{
					$ou = substr($value['dn'],strpos($value['dn'], "ou="));
					if( array_search($uid_User, $value['memberuid']) === false )
					{
						$search[] = strtoupper(substr($ou, 0, strpos($ou, ",dc=")));
						$search_Gid[] = $value['gidnumber'];
					}
				}
			}
		}
		
	
		if( $this->groupsLocked() )
		{
			if( count($search) > 0 )
			{
				// Verifica permissões do grupo
				foreach($groupsLocked as $value)
				{							
					$tpGroups = explode(":",$value);
					if( $tpGroups[1] == $search_Gid[0] )
					{
						$ousTp = explode(",",$tpGroups[2]);
						$ou_User = strtoupper(trim($this->dn_User));
						
						$posAll = $this->strallpos($ou_User, "OU=" );
						$orgs = array();

                        $posAll_count = count($posAll);
						for( $i = 0 ; $i < $posAll_count; ++$i )
						{
							$pos = strpos($ou_User, ",");
							$tmpString = substr($ou_User, $posAll[$i] + 3);
							$orgs[] = substr($tmpString, 0, strpos($tmpString, ","));
						}
				
						$ou_User = implode("/", array_reverse($orgs));

						if( array_search( $ou_User, $ousTp) !== false )
							return "true";
					}
				}
				return "false";
			}
			else
				return "true";
		} 
		else
		{		
			// Se Bloqueado verifica o Grupo	
			if( count($search) > 0 )
			{
				if( array_search($this->ou_User, $search) === false )
				{
					// Verifica permissões do grupo
					foreach($groupsLocked as $value)
					{							
						$tpGroups = explode(":",$value);
						
						if( $tpGroups[1] == $search_Gid[0] )
						{
							$ousTp = explode(",",$tpGroups[2]);
							$ou_User = strtoupper(trim($this->dn_User));
					
							$posAll = $this->strallpos($ou_User, "OU=" );
							$orgs = array();

                            $posAll_count = count($posAll);
							for( $i = 0 ; $i < $posAll_count; ++$i )
							{
								$pos = strpos($ou_User, ",");
								$tmpString = substr($ou_User, $posAll[$i] + 3);
								$orgs[] = substr($tmpString, 0, strpos($tmpString, ","));
							}
					
							$ou_User = implode("/", array_reverse($orgs));
							
							if( array_search( $ou_User, $ousTp) !== false )
								return "true";
						}
					}
					return "false";
				}
				return "true";
			}					
			return "true";
		}
	}

}

?>