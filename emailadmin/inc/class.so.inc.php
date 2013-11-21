<?php
	/***************************************************************************\
	* EGroupWare - EMailAdmin                                                   *
	* http://www.egroupware.org                                                 *
	* Written by : Lars Kneschke [lkneschke@egroupware.org]                     *
	* -------------------------------------------------                         *
	* This program is free software; you can redistribute it and/or modify it   *
	* under the terms of the GNU General Public License as published by the     *
	* Free Software Foundation; either version 2 of the License, or (at your    *
	* option) any later version.                                                *
	\***************************************************************************/

	class so
	{
		function so()
		{
			$this->db		= $GLOBALS['phpgw']->db;
			include(PHPGW_INCLUDE_ROOT.'/emailadmin/setup/tables_current.inc.php');
			$this->tables = &$phpgw_baseline;
			unset($phpgw_baseline);
			$this->table = &$this->tables['phpgw_emailadmin'];
		}
		
		function updateProfile($_globalSettings, $_smtpSettings, $_imapSettings)
		{
			$profileID = intval($_globalSettings['profileID']);
			$fields = $values = $query = '';

			foreach($_smtpSettings+$_globalSettings+$_imapSettings as $key => $value)
			{
				if($key == 'profileID')
					continue;

				if($fields != '')
				{
					$fields .= ',';
					$values .= ',';
					$query  .= ',';
				}
				switch($this->table['fd'][$key]['type'])
				{
					case 'int': case 'auto':
						$value = intval($value);
						break;
					default:
						$value = $this->db->db_addslashes($value);
						break;
				}
				$fields .= "$key";
				$values .= "'$value'";
				$query  .= "$key='$value'";
			}
			if ($profileID)
			{
				$query = "update phpgw_emailadmin set $query where profileID=$profileID";
			}
			else
			{
				$query = "insert into phpgw_emailadmin ($fields) values ($values)";
			}
			$this->db->query($query,__LINE__,__FILE__);
		}

		function addProfile($_globalSettings, $_smtpSettings, $_imapSettings)
		{
			unset($_globalSettings['profileID']);	// just in case

			$this->updateProfile($_globalSettings, $_smtpSettings, $_imapSettings);
		}

		function deleteProfile($_profileID)
		{
			$query = 'DELETE FROM phpgw_emailadmin WHERE profileID='.intval($_profileID);
			$this->db->query($query,__LINE__ , __FILE__);
		}

		function getProfile($_profileID, $_fieldNames)
		{
			$query = '';
			foreach($_fieldNames as $key => $value)
			{
				if(!empty($query))
				{
					$query .= ', ';
				}
				$query .= $value;
			}
			
			$query = "SELECT $query FROM phpgw_emailadmin WHERE profileID=".intval($_profileID);
			
			$this->db->query($query, __LINE__, __FILE__);
			
			if($this->db->next_record())
			{
				foreach($_fieldNames as $key => $value)
				{
					$profileData[$value] = $this->db->f($key);
				}

				return $profileData;
			}
			
			return false;
		}
		
		function getProfileList($_profileID='')
		{
			if(intval($_profileID) > 0)
			{
				$query = 'SELECT profileID,smtpServer,smtpType,imapServer,imapType,description FROM phpgw_emailadmin WHERE profileID='.intval($_profileID);
			}
			else
			{
				$query = 'SELECT profileID,smtpServer,smtpType,imapServer,imapType,description FROM phpgw_emailadmin';
			}
			$this->db->query($query);

			$i=0;
			while ($this->db->next_record())
			{
				$serverList[$i]['profileID']   = $this->db->f(0);
				$serverList[$i]['smtpServer']  = $this->db->f(1);
				$serverList[$i]['smtpType']    = $this->db->f(2);
				$serverList[$i]['imapServer']  = $this->db->f(3);
				$serverList[$i]['imapType']    = $this->db->f(4);
				$serverList[$i]['description'] = $this->db->f(5);
				++$i;
			}
			
			if ($i>0)
			{
				return $serverList;
			}
			else
			{
				return false;
			}
		}

		function getUserData($_accountID)
		{
			global $phpgw, $phpgw_info;

			$ldap = $phpgw->common->ldapConnect();
			$filter = "(&(uidnumber=$_accountID))";
			
			$sri = @ldap_search($ldap,$phpgw_info['server']['ldap_context'],$filter);
			if ($sri)
			{
				$allValues = ldap_get_entries($ldap, $sri);
				if ($allValues['count'] > 0)
				{
					#print "found something<br>";
					$userData["mailLocalAddress"]		= $allValues[0]["mail"][0];
					$userData["mailAlternateAddress"]	= $allValues[0]["mailalternateaddress"];
					$userData["accountStatus"]		= $allValues[0]["accountstatus"][0];
					$userData["mailRoutingAddress"]		= $allValues[0]["mailforwardingaddress"];
					$userData["qmailDotMode"]		= $allValues[0]["qmaildotmode"][0];
					$userData["deliveryProgramPath"]	= $allValues[0]["deliveryprogrampath"][0];
					$userData["deliveryMode"]		= $allValues[0]["deliverymode"][0];

					unset($userData["mailAlternateAddress"]["count"]);
					unset($userData["mailRoutingAddress"]["count"]);					

					return $userData;
				}
			}
			
			// if we did not return before, return false
			return false;
		}
		
		function saveUserData($_accountID, $_accountData)
		{
			$ldap = $GLOBALS['phpgw']->common->ldapConnect();
			// need to be fixed
			if(is_numeric($_accountID))
			{
				$filter = "uidnumber=$_accountID";
			}
			else
			{
				$filter = "uid=$_accountID";
			}

			$sri = @ldap_search($ldap,$GLOBALS['phpgw_info']['server']['ldap_context'],$filter);
			if ($sri)
			{
				$allValues 	= ldap_get_entries($ldap, $sri);
				$accountDN 	= $allValues[0]['dn'];
				$uid	   	= $allValues[0]['uid'][0];
				$homedirectory	= $allValues[0]['homedirectory'][0];
				$objectClasses	= $allValues[0]['objectclass'];
				
				unset($objectClasses['count']);
			}
			else
			{
				return false;
			}
			
			if(empty($homedirectory))
			{
				$homedirectory = "/home/".$uid;
			}
			
			// the old code for qmail ldap
			$newData = array 
			(
				'mail'			=> $_accountData["mailLocalAddress"],
				'mailAlternateAddress'	=> $_accountData["mailAlternateAddress"],
				'mailRoutingAddress'	=> $_accountData["mailRoutingAddress"],
				'homedirectory'		=> $homedirectory,
				'mailMessageStore'	=> $homedirectory."/Maildir/",
				'gidnumber'		=> '1000',
				'qmailDotMode'		=> $_accountData["qmailDotMode"],
				'deliveryProgramPath'	=> $_accountData["deliveryProgramPath"]
			);
			
			if(!in_array('qmailUser',$objectClasses) &&
				!in_array('qmailuser',$objectClasses))
			{
				$objectClasses[]	= 'qmailuser'; 
			}
			
			// the new code for postfix+cyrus+ldap
			$newData = array 
			(
				'mail'			=> $_accountData["mailLocalAddress"],
				'accountStatus'		=> $_accountData["accountStatus"],
				'objectclass'		=> $objectClasses
			);

			if(is_array($_accountData["mailAlternateAddress"]))
			{	
				$newData['mailAlternateAddress'] = $_accountData["mailAlternateAddress"];
			}
			else
			{
				$newData['mailAlternateAddress'] = array();
			}

			if($_accountData["accountStatus"] == 'active')
			{	
				$newData['accountStatus'] = 'active';
			}
			else
			{
				$newData['accountStatus'] = 'disabled';
			}

			if(!empty($_accountData["deliveryMode"]))
			{	
				$newData['deliveryMode'] = $_accountData["deliveryMode"];
			}
			else
			{
				$newData['deliveryMode'] = array();
			}


			if(is_array($_accountData["mailRoutingAddress"]))
			{	
				$newData['mailForwardingAddress'] = $_accountData["mailRoutingAddress"];
			}
			else
			{
				$newData['mailForwardingAddress'] = array();
			}
			
			#print "DN: $accountDN<br>";
			ldap_mod_replace ($ldap, $accountDN, $newData);
			#print ldap_error($ldap);
			
			// also update the account_email field in phpgw_accounts
			// when using sql account storage
			if($GLOBALS['phpgw_info']['server']['account_repository'] == 'sql')
			{
				$this->db->update('phpgw_accounts',array(
						'account_email'	=> $_accountData["mailLocalAddress"]
					),
					array(
						'account_id'	=> $_accountID
					),__LINE__,__FILE__
				);
			}
		}
	}
?>
