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

if( is_dir('../../phpgwapi/inc') )
	define('PHPGW_API_INC','../../phpgwapi/inc');
else
	define('PHPGW_API_INC','../../../phpgwapi/inc');

require_once( PHPGW_API_INC . '/class.common.inc.php');

class LdapIM
{
	private $attribute;
	private $common;
	private $hostsJabber;
	private $ldap;
	private $ldap_context;
	private $ldap_user;
	private $ldap_host;
	private $ldap_org;
	private $ldap_pass;
	private $max_result;
	
	public final function __construct()
	{
		$this->ldap_host 	= $_SESSION['phpgw_info']['jabberit_messenger']['server_ldap_jabberit'];
		$this->ldap_context	= $_SESSION['phpgw_info']['jabberit_messenger']['context_ldap_jabberit'];
		$this->ldap_user	= $_SESSION['phpgw_info']['jabberit_messenger']['user_ldap_jabberit'];
		$this->ldap_pass	= $_SESSION['phpgw_info']['jabberit_messenger']['password_ldap_jabberit'];

		// Hosts Jabber
		$this->hostsJabber = unserialize($_SESSION['phpgw_info']['jabberit_messenger']['map_org_realm_jabberit']);
		
		// Result Ldap
		$this->max_result = 15;
		
		if ( file_exists('inc/attributeLdap.php') )
		{
			require_once('attributeLdap.php');
			$this->attribute = trim($attributeTypeName);
		}
		else
			$this->attribute = "uid";
	}

	public final function __destruct()
	{
		if( $this->ldap )
			@ldap_close($this->ldap);
	}	

	private final function ldapConn()
	{
		$this->common = new common();		
		
		$GLOBALS['phpgw_info']['server']['ldap_version3'] = true;
		
		if( $this->ldap_user && $this->ldap_pass )
			$this->ldap = $this->common->ldapConnect( $this->ldap_host, $this->ldap_user . "," . $this->ldap_context , $this->ldap_pass, false );
		else
			$this->ldap = $this->common->ldapConnect( $this->ldap_host, $this->ldap_context , "", false );
	}
	
	private final function ldapRoot()
	{
		$this->ldap_host 	= $_SESSION['phpgw_info']['jabberit_messenger']['server_ldap_jabberit'];
		$this->ldap_context	= $_SESSION['phpgw_info']['jabberit_messenger']['context_ldap_jabberit'];
		$this->ldap_user	= $_SESSION['phpgw_info']['jabberit_messenger']['user_ldap_jabberit'];
		$this->ldap_pass	= $_SESSION['phpgw_info']['jabberit_messenger']['password_ldap_jabberit'];

		$this->ldapConn();
	}

	private final function ldapCatalog()
	{
		$version3 = true;
		$refer	= true;

		if(!function_exists('ldap_connect'))
			return false;
		
		if(!$conn = ldap_connect($this->ldap_host))
			return false;

		if( $version3 )
			if( !ldap_set_option($conn,LDAP_OPT_PROTOCOL_VERSION,3) )
				$version3 = false;

		ldap_set_option($conn, LDAP_OPT_REFERRALS, $refer);

		// Bind as Admin
		if($this->ldap_user && $this->ldap_pass && !ldap_bind($conn, $this->ldap_user . "," .$this->ldap_context, $this->ldap_pass))
			return false;
		
		// Bind as Anonymous
		if(!$this->ldap_user && !$this->ldap_pass && !@ldap_bind($conn))
			return false;

		return $conn;
	}
	
	private function getLdapHost()
	{
		return	$_SESSION['phpgw_info']['jabberit_messenger']['server_ldap_jabberit'];
	}

	public final function getGroupsLdap($pData)
	{
		$result_groups = "";
		
		if( $pData['serverLdap'] == $this->ldap_host || $pData['serverLdap'] == 'localhost' )
		{
			$this->ldapRoot();
		}
		else
		{
			$confHosts	= $this->hostsJabber;

            $confHosts_count = count($confHosts);
			for($i = 0; $i < $confHosts_count; ++$i )
			{
				if( $pData['serverLdap'] == $confHosts[$i]['serverLdap'] )
				{
					$this->ldap_host 	= $confHosts[$i]['serverLdap'];
					$this->ldap_context = $confHosts[$i]['contextLdap'];
					$this->ldap_user	= $confHosts[$i]['user'];
					$this->ldap_org		= $confHosts[$i]['org'];
					$this->ldap_pass	= $confHosts[$i]['password'];
				
					$this->ldap = $this->ldapCatalog();
				}
			}
		}		

		if( $this->ldap )	
		{
			if( !$pData['search'] && $pData['ou'] != "-1" )
			{
				$filter = "(&(phpgwAccountType=g)(objectClass=posixGroup))";
				$justthese = array("cn","gidNumber");
				$search = ldap_list( $this->ldap, $pData['ou'] , $filter, $justthese );
				$entry = ldap_get_entries( $this->ldap, $search );
			}
			
			if( $pData['search'] )
			{
				$filter = "(&(phpgwAccountType=g)(&(objectClass=posixGroup)(cn=".$pData['search']."*)))";
				$justthese = array("cn","gidNumber");
				$search = ldap_search( $this->ldap, $this->ldap_context , $filter, $justthese );
				$entry = ldap_get_entries( $this->ldap, $search );
			}
			
			if( $entry && $entry['count'] > 0 )
			{					
				array_shift($entry);

				foreach($entry as $tmp)
					$groups[] = $tmp['cn'][0]."/".$tmp['gidnumber'][0];
				
				natsort($groups);
				
				$result_groups = "<ldap>";
				foreach($groups as $gtmp)
				{
					$tmp = explode("/",$gtmp);	
					$result_groups .= "<org><cn>".$tmp[0]."</cn><gid>".$tmp[1]."</gid></org>";
				}
				$result_groups .= "</ldap>";
			}
		}

		return $result_groups;
	}

	public final function getMaxResults()
	{
		return $this->max_result;
	}

	public final function getMembers( $pMembers, $pServers )
	{
		$members = $pMembers;
		
		foreach( $pServers as $servers => $groups )
		{
			if( $servers == $this->getLdapHost() || $servers == 'localhost')
			{
				$this->ldapRoot();
				
				$count = count($members[$servers]);
				
				for( $i = 0; $i < $count; ++$i )
				{
					if ( ! $this->getMemberUid($groups, $members[$servers][$i]['uid'] ) )
						unset( $members[$servers][$i] );
				}

				if( $this->ldap )
					@ldap_close($this->ldap);
			}
			else
			{
				$confHosts	= $this->hostsJabber;

                $confHosts_count = count($confHosts);
				for($i = 0; $i < $confHosts_count; ++$i )
				{
					if( $this->ldap )
						@ldap_close($this->ldap);
						
					if( trim($servers) === trim($confHosts[$i]['serverLdap']) )
					{
						$this->ldap_host 	= $confHosts[$i]['serverLdap'];
						$this->ldap_context = $confHosts[$i]['contextLdap'];
						$this->ldap_user	= $confHosts[$i]['user'];
						$this->ldap_org		= $confHosts[$i]['org'];
						$this->ldap_pass	= $confHosts[$i]['password'];
						$this->ldap 		= $this->ldapCatalog();

						$count = count($members[$servers]);
						
						for( $i = 0; $i < $count; $i++ )
						{
							if ( ! $this->getMemberUid($groups, $members[$servers][$i]['uid'] ) )
								unset( $members[$servers][$i] );
						}
		
						if( $this->ldap )
							@ldap_close($this->ldap);
					}
				}
			}
		}
		
		return $members;
	}

	private function getMemberUid( $pGidNumber, $pMemberUid )
	{
		$filter		= "(&(phpgwAccountType=g)(|{$pGidNumber})(memberuid={$pMemberUid}))";
		$justthese	= array("memberuid");
	
		if( $this->ldap )
		{
			$search = ldap_search($this->ldap, $this->ldap_context, $filter, $justthese );
			$result = ldap_get_entries($this->ldap,$search);
			if( $result["count"] )
				return true;
		}

		return false;
	}

	public final function getGroupsMemberUid( $pGroup, $pLdap )
	{
		if( $pLdap == $this->ldap_host || $pLdap == 'localhost' )
		{
			$this->ldapRoot();
			
			if( $this->ldap )
			{
				$filter = "(&(objectclass=posixgroup)(|".$pGroup."))";
				if( strpos($pGroup, "gidnumber") === false )
					$filter = "(&(objectclass=posixgroup)(cn=".$pGroup."))";
					
				$justthese = array("dn","memberuid","gidnumber");
				$search = ldap_search($this->ldap, $this->ldap_context, $filter, $justthese);
				$result = ldap_get_entries($this->ldap,$search);
			}
		}
		else
		{
			$confHosts	= $this->hostsJabber;

            $confHosts_count = count($confHosts);
			for($i = 0; $i < $confHosts_count; ++$i )
			{
				if( $this->ldap )
					@ldap_close($this->ldap);
					
				if( trim($pLdap) === trim($confHosts[$i]['serverLdap']) )
				{
					$this->ldap_host 	= $confHosts[$i]['serverLdap'];
					$this->ldap_context = $confHosts[$i]['contextLdap'];
					$this->ldap_user	= $confHosts[$i]['user'];
					$this->ldap_org		= $confHosts[$i]['org'];
					$this->ldap_pass	= $confHosts[$i]['password'];
					$this->ldap = $this->ldapCatalog();
					
					if( $this->ldap )
					{
						$filter = "(&(objectclass=posixgroup)(cn=".$pGroup."))";
						$justthese = array("dn","memberuid","gidnumber");
						$search = ldap_search($this->ldap,$this->ldap_context,$filter, $justthese);
						$result = ldap_get_entries($this->ldap,$search);
					}
					
				}
			}
		}

		if( $result['count'] > 0 )
			return $result;

		return false;
	}

	public final function getOrganizationsLdap($pLdap_host)
	{

		if( $pLdap_host == $this->ldap_host || $pLdap_host == 'localhost' )
		{
			$this->ldapRoot();
		}
		else
		{
			$confHosts	= $this->hostsJabber;

            $confHosts_count = count($confHosts);
			for($i = 0; $i < $confHosts_count; ++$i )
			{
				if( $pLdap_host == $confHosts[$i]['serverLdap'] )
				{
					$this->ldap_host 	= $confHosts[$i]['serverLdap'];
					$this->ldap_context = $confHosts[$i]['contextLdap'];
					$this->ldap_user	= $confHosts[$i]['user'];
					$this->ldap_org		= $confHosts[$i]['org'];
					$this->ldap_pass	= $confHosts[$i]['password'];
				
					$this->ldap = $this->ldapCatalog();
				}
			}
		}
		
		if( $this->ldap )
		{
			$filter = "(objectClass=organizationalUnit)";
			$justthese = array("dn");
			$search = ldap_search($this->ldap, $this->ldap_context, $filter, $justthese);
			$info = ldap_get_entries($this->ldap, $search);
		
			for ($i=0; $i<$info["count"]; ++$i)
			{
				$a_sectors[] = $info[$i]['dn'];
			}	
		}

		// Retiro o count do array info e inverto o array para ordenação.
		foreach ($a_sectors as $context)
		{
			$array_dn = ldap_explode_dn ( $context, 1 );
			$array_dn_reverse  = array_reverse ( $array_dn, true );
			array_pop ( $array_dn_reverse );
			$inverted_dn[$context] = implode ( "#", $array_dn_reverse );
		}
		
		// Ordenação
		natcasesort($inverted_dn);

		foreach ( $inverted_dn as $dn=>$invert_ufn )
		{
            $display = '';

            $array_dn_reverse = explode ( "#", $invert_ufn );
            $array_dn  = array_reverse ( $array_dn_reverse, true );

            $level = count( $array_dn ) - (int)(count(explode(",", $this->ldap_context)) + 1);

            if ($level == 0)
                    $display .= '+';
            else 
            {
				for( $i = 0; $i < $level; ++$i)
					$display .= '---';
            }

            reset ( $array_dn );
            $display .= ' ' . (current ( $array_dn ) );
			
			$dn = trim(strtolower($dn));
			$options[$dn] = $display;
    	}

	    return $options;

	}

	public final function getPhotoUser( $_uid )
	{
		$uid 	= substr($_uid, 0, strpos($_uid, "@"));
		$host	= substr($_uid, (strpos($_uid, "@") + 1));
		
		if( count($this->hostsJabber) )
		{
			$confHosts	= $this->hostsJabber;	

            $confHosts_count = count($confHosts);
			for( $i = 0; $i < $confHosts_count; ++$i )
			{
				if( trim($host) === trim($confHosts[$i]['jabberName']) )
				{
					$this->ldap_host 	= $confHosts[$i]['serverLdap'];
					$this->ldap_context = $confHosts[$i]['contextLdap'];
					$this->ldap_user	= $confHosts[$i]['user'];
					$this->ldap_org		= $confHosts[$i]['org'];
					$this->ldap_pass	= $confHosts[$i]['password'];
					$this->ldap 		= $this->ldapCatalog();
				}
			}
			
			if( !$this->ldap )
				$this->ldapRoot();								
		}
		else
		{
			$this->ldapRoot();
		}

		if( $this->ldap )
		{
			$filter     = "(&(phpgwaccounttype=u)(".$this->attribute."=".$uid."))";
			$justthese	= array($this->attribute, "uidNumber", "phpgwAccontVisible", "dn", "jpegPhoto");
			$search		= ldap_search( $this->ldap, $this->ldap_context, $filter, $justthese, 0, $this->max_result + 1);
			$entry		= ldap_get_entries( $this->ldap, $search );
			
			for( $i = 0 ; $i < $entry['count']; ++$i )
			{
				if( $entry[$i]['jpegphoto'][0] && $entry[$i]['phpgwaccountvisible'][0] != '-1' )
				{
					$filterPhoto	= "(objectclass=*)";
					$photoLdap		= ldap_read($this->ldap, $entry[$i]['dn'], $filterPhoto, array("jpegPhoto"));
					$firstEntry 	= ldap_first_entry($this->ldap, $photoLdap);
					$photo			= ldap_get_values_len($this->ldap, $firstEntry, "jpegPhoto");

					if( $this->ldap )
						ldap_close($this->ldap);

					return $photo[0];		
				}
			}
		}

		return false;
	}

	public final function getUsersLdapCatalog( $search, $pLdap = false, $uid = false )
	{
		$confHosts	= $this->hostsJabber;
		$result = array();
		$return	= array();
		$conn	= "";

        $confHosts_count = count($confHosts);
		for( $i = 0; $i < $confHosts_count; ++$i )
		{
			if( $pLdap && $pLdap == $confHosts[$i]['serverLdap'] )
			{
				$this->ldap_host 	= $confHosts[$i]['serverLdap'];
				$this->ldap_context = $confHosts[$i]['contextLdap'];
				$this->ldap_user	= $confHosts[$i]['user'];
				$this->ldap_org		= $confHosts[$i]['org'];
				$this->ldap_pass	= $confHosts[$i]['password'];
				$this->ldap 		= $this->ldapCatalog();

				if( $this->ldap )
				{
					$filter 	= ( $uid ) ? "(&(phpgwaccounttype=u)(|".$uid.")(".$search ."))" : "(&(phpgwaccounttype=u)(".$search ."))";
					$justthese	= array( $this->attribute ,"uidNumber" ,"cn" ,"mail" ,"phpgwAccountVisible" ,"dn" ,"jpegPhoto" );								
					$searchRoot	= ( $this->ldap_org != "*" ) ? "ou=".$this->ldap_org.",".$this->ldap_context : $this->ldap_context;
					$search1	= @ldap_search($this->ldap, $searchRoot, $filter, $justthese, 0, $this->max_result + 1);
					$entry1		= @ldap_get_entries( $this->ldap, $search1 );
					$result 	= $this->resultArray( $entry1, $this->ldap, $this->ldap_org );
	
					if( count($return) > 0 )
			          	$return = array_merge($return, $result);
					else
						$return = $result;
				}
				
				if( $this->ldap )
					ldap_close($this->ldap);
			}
		}
		
		return $return;
	}

	public final function getUsersLdapRoot( $search, $uidnumber = false, $ous = false )
	{
		
		$result = array();
		$this->ldapRoot();

		if( $this->ldap )
		{
			$searchRoot	= ( $ous ) ? $ous.",".$this->ldap_context : $this->ldap_context ;
			$filter		= ($uidnumber) ? "(&(phpgwaccounttype=u)(|".$uidnumber.")(".$search ."))" : "(&(phpgwaccounttype=u)(".$search ."))";
			$justthese	= array( $this->attribute, "uidNumber", "cn", "mail", "phpgwAccountVisible", "dn", "jpegPhoto" );								
			$search		= @ldap_search( $this->ldap, $searchRoot, $filter, $justthese, 0, $this->max_result + 1);
			$entry		= @ldap_get_entries( $this->ldap, $search );
			$result		= $this->resultArray( $entry, $this->ldap );
		}		

		return $result;
	}
	
	private final function resultArray($pArray, $pConn, $pOrg = false)
	{
		$entry	= $pArray;
		$result	= array();
		$j		= 0;
		
		for( $i = 0 ; $i < $entry['count']; ++$i )
		{
			if ( $entry[$i]['phpgwaccountvisible'][0] != '-1' )
			{
				$result[$j]['uidnumber']	= $entry[$i]['uidnumber'][0];			
				$result[$j]['mail']			= $entry[$i]['mail'][0];
				$result[$j]['uid']			= $entry[$i][$this->attribute][0];
				$result[$j]['jid']			= $entry[$i][$this->attribute][0];
				
				$ou = explode("dc=", $entry[$i]['dn']);
				$ou = explode("ou=",$ou[0]);
				$ou = array_pop($ou);
				$result[$j]['ou']	= strtoupper(substr($ou,0,strlen($ou)-1));
				
				if( $pOrg === "*" )
					$result[$j]['ouAll'] = "*";
										
				if( $entry[$i]['jpegphoto'][0] )
				{
					$result[$j]['photo'] = "1";
					$filterPhoto = "(objectclass=*)";
					$photoLdap = ldap_read($pConn, $entry[$i]['dn'], $filterPhoto, array("jpegPhoto"));
					$firstEntry = ldap_first_entry($pConn, $photoLdap);
					$photo = ldap_get_values_len($pConn, $firstEntry, "jpegPhoto");
					$_SESSION['phpgw_info']['jabberit_messenger']['photo'][trim($result[$j]['ou'])][trim($result[$j]['uid'])] = $photo[0];
				}
				else
					$result[$j]['photo'] = "0";

				$result[$j++]['cn']	= $entry[$i]['cn'][0];
			}
		
			$organization = $this->attr_org;
		}
		
		return $result;
	}
}

?>