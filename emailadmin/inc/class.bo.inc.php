<?php
	/***************************************************************************\
	* eGroupWare                                                                *
	* http://www.egroupware.org                                                 *
	* http://www.linux-at-work.de                                               *
	* Written by : Lars Kneschke [lkneschke@linux-at-work.de]                   *
	* -------------------------------------------------                         *
	* This program is free software; you can redistribute it and/or modify it   *
	* under the terms of the GNU General Public License as published by the     *
	* Free Software Foundation; either version 2 of the License, or (at your    *
	* option) any later version.                                                *
	\***************************************************************************/

	class bo
	{
		var $sessionData;
		var $LDAPData;
		
		var $SMTPServerType = array();		// holds a list of config options
		
		var $imapClass;				// holds the imap/pop3 class
		var $smtpClass;				// holds the smtp class

		var $public_functions = array
		(
			'getFieldNames'		=> True,
			'getLDAPStorageData'	=> True,
			'getLocals'		=> True,
			'getProfile'		=> True,
			'getProfileList'	=> True,
			'getRcptHosts'		=> True,
			'getSMTPServerTypes'	=> True
		);

		function bo($_profileID=-1)
		{
			$this->soemailadmin = CreateObject('emailadmin.so');
			
			$this->SMTPServerType = array(
				'1' 	=> array(
					'fieldNames'	=> array(
						'smtpServer',
						'smtpPort',
						'smtpAuth',
						'smtpType'
					),
					'description'	=> lang('standard SMTP-Server'),
					'classname'	=> 'defaultsmtp'
				),
				'2' 	=> array(
					'fieldNames'	=> array(
						'smtpServer',
						'smtpPort',
						'smtpAuth',
						'smtpType',
						'smtpLDAPServer',
						'smtpLDAPAdminDN',
						'smtpLDAPAdminPW',
						'smtpLDAPBaseDN',
						'smtpLDAPUseDefault'
					),
					'description'	=> lang('Postfix with LDAP'),
					'classname'	=> 'postfixldap'
				)
			);

			$this->IMAPServerType = array(
				'1' 	=> array(
					'fieldNames'	=> array(
						'imapServer',
						'imapPort',
						'imapType',
						'imapLoginType',
						'imapTLSEncryption',
						'imapTLSAuthentication',
						'imapoldcclient'
					),
					'description'	=> lang('standard POP3 server'),
					'protocol'	=> 'pop3',
					'classname'	=> 'defaultpop'
				),
				'2' 	=> array(
					'fieldNames'	=> array(
						'imapServer',
						'imapPort',
						'imapType',
						'imapLoginType',
						'imapTLSEncryption',
						'imapTLSAuthentication',
						'imapoldcclient'
					),
					'description'	=> lang('standard IMAP server'),
					'protocol'	=> 'imap',
					'classname'	=> 'defaultimap'
				),
				'3' 	=> array(
					'fieldNames'	=> array(
						'imapServer',
						'imapPort',
						'imapDelimiter',
						'imapType',
						'imapLoginType',
						'imapTLSEncryption',
						'imapTLSAuthentication',
						'imapoldcclient',
						'imapEnableCyrusAdmin',
						'imapAdminUsername',
						'imapAdminPW',
						'imapEnableSieve',
						'imapSieveServer',
						'imapSievePort',
						'imapCreateSpamFolder',
						'imapCyrusUserPostSpam',
						'imapDefaultTrashFolder',
						'imapDefaultSentFolder',
						'imapDefaultDraftsFolder',
						'imapDefaultSpamFolder'
					),
					'description'	=> lang('Cyrus IMAP Server'),
					'protocol'	=> 'imap',
					'classname'	=> 'cyrusimap'
				)
			); 
			
			$this->restoreSessionData();
			
			if($_profileID >= 0)
			{
				$this->profileID	= $_profileID;
			
				$this->profileData	= $this->getProfile($_profileID);
			
				$this->imapClass	= $this->IMAPServerType[$this->profileData['imapType']]['classname'];
				$this->smtpClass	= $this->SMTPServerType[$this->profileData['smtpType']]['classname'];
			}
		}
		
		function addAccount($_hookValues)
		{
			if (!empty($this->imapClass))
			{
				ExecMethod("emailadmin.".$this->imapClass.".addAccount",$_hookValues,3,$this->profileData);
			}
			
			if (!empty($this->smtpClass))
			{
				ExecMethod("emailadmin.".$this->smtpClass.".addAccount",$_hookValues,3,$this->profileData);
			}
		}
		
		function deleteAccount($_hookValues)
		{
			if (!empty($this->imapClass))
			{
				ExecMethod("emailadmin.".$this->imapClass.".deleteAccount",$_hookValues,3,$this->profileData);
			}

			if (!empty($this->smtpClass))
			{
				ExecMethod("emailadmin.".$this->smtpClass.".deleteAccount",$_hookValues,3,$this->profileData);
			}
		}
		
		function deleteProfile($_profileID)
		{
			$this->soemailadmin->deleteProfile($_profileID);
		}
		
		function encodeHeader($_string, $_encoding='q')
		{
			switch($_encoding)
			{
				case "q":
					if(!preg_match("/[\x80-\xFF]/",$_string))
					{
						// nothing to quote, only 7 bit ascii
						return $_string;
					}
					
					$string = imap_8bit($_string);
					$stringParts = explode("=\r\n",$string);
					while(list($key,$value) = each($stringParts))
					{
						if(!empty($retString)) $retString .= " ";
						$value = str_replace(" ","_",$value);
						// imap_8bit does not convert "?"
						// it does not need, but it should
						$value = str_replace("?","=3F",$value);
						$retString .= "=?".strtoupper($this->displayCharset)."?Q?".$value."?=";
					}
					#exit;
					return $retString;
					break;
				default:
					return $_string;
			}
		}

		function getAccountEmailAddress($_accountName, $_profileID)
		{
			$profileData	= $this->getProfile($_profileID);
			
			$smtpClass	= $this->SMTPServerType[$profileData['smtpType']]['classname'];

			return empty($smtpClass) ? False : ExecMethod("emailadmin.$smtpClass.getAccountEmailAddress",$_accountName,3,$profileData);
		}
		
		function getFieldNames($_serverTypeID, $_class)
		{
			switch($_class)
			{
				case 'imap':
					return $this->IMAPServerType[$_serverTypeID]['fieldNames'];
					break;
				case 'smtp':
					return $this->SMTPServerType[$_serverTypeID]['fieldNames'];
					break;
			}
		}
		
#		function getIMAPClass($_profileID)
#		{
#			if(!is_object($this->imapClass))
#			{
#				$profileData		= $this->getProfile($_profileID);
#				$this->imapClass	= CreateObject('emailadmin.cyrusimap',$profileData);
#			}
#			
#			return $this->imapClass;
#		}
		
		function getIMAPServerTypes()
		{
			foreach($this->IMAPServerType as $key => $value)
			{
				$retData[$key]['description']	= $value['description'];
				$retData[$key]['protocol']	= $value['protocol'];
			}
			
			return $retData;
		}
		
		function getLDAPStorageData($_serverid)
		{
			$storageData = $this->soemailadmin->getLDAPStorageData($_serverid);
			return $storageData;
		}
		
		function getMailboxString($_folderName)
		{
			if (!empty($this->imapClass))
			{
				return ExecMethod("emailadmin.".$this->imapClass.".getMailboxString",$_folderName,3,$this->profileData);
			}
			else
			{
				return false;
			}
		}

		function getProfile($_profileID)
		{
			$profileData = $this->soemailadmin->getProfileList($_profileID);
			$fieldNames = $this->SMTPServerType[$profileData[0]['smtpType']]['fieldNames'];
			$fieldNames = array_merge($fieldNames, $this->IMAPServerType[$profileData[0]['imapType']]['fieldNames']);
			$fieldNames[] = 'description';
			$fieldNames[] = 'defaultDomain';
			$fieldNames[] = 'profileID';
			$fieldNames[] = 'organisationName';
			$fieldNames[] = 'userDefinedAccounts';
			
			return $this->soemailadmin->getProfile($_profileID, $fieldNames);
		}
		
		function getProfileList($_profileID='')
		{
			$profileList = $this->soemailadmin->getProfileList($_profileID);
			return $profileList;
		}
		
#		function getSMTPClass($_profileID)
#		{
#			if(!is_object($this->smtpClass))
#			{
#				$profileData		= $this->getProfile($_profileID);
#				$this->smtpClass	= CreateObject('emailadmin.postfixldap',$profileData);
#			}
#			
#			return $this->smtpClass;
#		}
		
		function getSMTPServerTypes()
		{
			foreach($this->SMTPServerType as $key => $value)
			{
				$retData[$key] = $value['description'];
			}
			
			return $retData;
		}
		
		function getUserData($_accountID, $_usecache)
		{
			if ($_usecache)
			{
				$userData = $this->userSessionData[$_accountID];
			}
			else
			{
				$userData = $this->soemailadmin->getUserData($_accountID);
				$bofelamimail = CreateObject('felamimail.bofelamimail');
				$bofelamimail->openConnection('','',true);
				$userQuota = 
					$bofelamimail->imapGetQuota($GLOBALS['phpgw']->accounts->id2name($_accountID));
				if(is_array($userQuota))
				{
					$userData['quotaLimit']	= $userQuota['limit'];
				}
				$bofelamimail->closeConnection();
				$this->userSessionData[$_accountID] = $userData;
				$this->saveSessionData();
			}
			return $userData;
		}

		function restoreSessionData()
		{
			global $phpgw;
		
			$this->sessionData = $phpgw->session->appsession('session_data');
			$this->userSessionData = $phpgw->session->appsession('user_session_data');
			
			#while(list($key, $value) = each($this->userSessionData))
			#{
			#	print "++ $key: $value<br />";
			#}
			#print "restored Session<br />";
		}
		
		function saveProfile($_globalSettings, $_smtpSettings, $_imapSettings)
		{
			if(!isset($_globalSettings['profileID']))
			{
				$this->soemailadmin->addProfile($_globalSettings, $_smtpSettings, $_imapSettings);
			}
			else
			{
				$this->soemailadmin->updateProfile($_globalSettings, $_smtpSettings, $_imapSettings);
			}
		}
		
		function saveSessionData()
		{
			global $phpgw;
			
			$phpgw->session->appsession('session_data','',$this->sessionData);
			$phpgw->session->appsession('user_session_data','',$this->userSessionData);
		}
		
		function saveUserData($_accountID, $_formData, $_boAction)
		{
			$this->userSessionData[$_accountID]['mailLocalAddress'] 	= $_formData["mailLocalAddress"];
			$this->userSessionData[$_accountID]['accountStatus'] 		= $_formData["accountStatus"];
			$this->userSessionData[$_accountID]['deliveryMode'] 		= $_formData["deliveryMode"];
			$this->userSessionData[$_accountID]['qmailDotMode'] 		= $_formData["qmailDotMode"];
			$this->userSessionData[$_accountID]['deliveryProgramPath'] 	= $_formData["deliveryProgramPath"];
			$this->userSessionData[$_accountID]['quotaLimit'] 		= $_formData["quotaLimit"];

			switch ($_boAction)
			{
				case 'add_mailAlternateAddress':
					if (is_array($this->userSessionData[$_accountID]['mailAlternateAddress']))
					{
						$count = count($this->userSessionData[$_accountID]['mailAlternateAddress']);
					}
					else
					{
						$count = 0;
						$this->userSessionData[$_accountID]['mailAlternateAddress'] = array();
					}
					
					$this->userSessionData[$_accountID]['mailAlternateAddress'][$count] = 
						$_formData['add_mailAlternateAddress'];
						
					$this->saveSessionData();
					
					break;
					
				case 'remove_mailAlternateAddress':
					$i=0;
					
					while(list($key, $value) = @each($this->userSessionData[$_accountID]['mailAlternateAddress']))
					{
						#print ".. $key: $value<br />";
						if ($key != $_formData['remove_mailAlternateAddress'])
						{
							$newMailAlternateAddress[$i]=$value;
							#print "!! $i: $value<br />";
							++$i;
						}
					}
					$this->userSessionData[$_accountID]['mailAlternateAddress'] = $newMailAlternateAddress;
					
					$this->saveSessionData();

					break;
					
				case 'add_mailRoutingAddress':
					if (is_array($this->userSessionData[$_accountID]['mailRoutingAddress']))
					{
						$count = count($this->userSessionData[$_accountID]['mailRoutingAddress']);
					}
					else
					{
						$count = 0;
						$this->userSessionData[$_accountID]['mailRoutingAddress'] = array();
					}
					
					$this->userSessionData[$_accountID]['mailRoutingAddress'][$count] = 
						$_formData['add_mailRoutingAddress'];
						
					$this->saveSessionData();

					break;
					
				case 'remove_mailRoutingAddress':
					$i=0;
					
					while(list($key, $value) = @each($this->userSessionData[$_accountID]['mailRoutingAddress']))
					{
						if ($key != $_formData['remove_mailRoutingAddress'])
						{
							$newMailRoutingAddress[$i]=$value;
							++$i;
						}
					}
					$this->userSessionData[$_accountID]['mailRoutingAddress'] = $newMailRoutingAddress;
					
					$this->saveSessionData();

					break;
					
				case 'save':
					$this->soemailadmin->saveUserData(
						$_accountID, 
						$this->userSessionData[$_accountID]);
					$bofelamimail = CreateObject('felamimail.bofelamimail');
					$bofelamimail->openConnection('','',true);
					$bofelamimail->imapSetQuota($GLOBALS['phpgw']->accounts->id2name($_accountID),
								    $this->userSessionData[$_accountID]['quotaLimit']);
					$bofelamimail->closeConnection();
					$GLOBALS['phpgw']->accounts->cache_invalidate($_accountID);
					
					
					break;
			}
		}

		function updateAccount($_hookValues)
		{
			if (!empty($this->imapClass))
			{
				ExecMethod("emailadmin.".$this->imapClass.".updateAccount",$_hookValues,3,$this->profileData);
			}

			if (!empty($this->smtpClass))
			{
				ExecMethod("emailadmin.".$this->smtpClass.".updateAccount",$_hookValues,3,$this->profileData);
			}
		}
		
	}
?>
