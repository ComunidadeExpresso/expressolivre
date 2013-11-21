<?php
	/***************************************************************************\
	* EGroupWare - EMailAdmin                                                   *
	* http://www.egroupware.org                                                 *
	* Written by : Lars Kneschke [lkneschke@linux-at-work.de]                   *
	* -------------------------------------------------                         *
	* This program is free software; you can redistribute it and/or modify it   *
	* under the terms of the GNU General Public License as published by the     *
	* Free Software Foundation; either version 2 of the License, or (at your    *
	* option) any later version.                                                *
	\***************************************************************************/

	include_once(PHPGW_SERVER_ROOT."/emailadmin/inc/class.defaultsmtp.inc.php");

	class postfixldap extends defaultsmtp
	{
		function addAccount($_hookValues)
		{
			$mailLocalAddress	= $_hookValues['account_lid']."@".$this->profileData['defaultDomain'];

			$ds = $GLOBALS['phpgw']->common->ldapConnect();
			
			$filter = "uid=".$_hookValues['account_lid'];

			$sri = @ldap_search($ds,$GLOBALS['phpgw_info']['server']['ldap_context'],$filter);
			if ($sri)
			{
				$allValues 	= ldap_get_entries($ds, $sri);
				$accountDN 	= $allValues[0]['dn'];
				$objectClasses	= $allValues[0]['objectclass'];
				
				unset($objectClasses['count']);
			}
			else
			{
				return false;
			}
			
			if(!in_array('qmailUser',$objectClasses) &&
				!in_array('qmailuser',$objectClasses))
			{
				$objectClasses[]	= 'qmailuser'; 
			}
			
			// the new code for postfix+cyrus+ldap
			$newData = array 
			(
				'mail'			=> $mailLocalAddress,
				'accountStatus'		=> 'active',
				'objectclass'		=> $objectClasses
			);

			ldap_mod_replace ($ds, $accountDN, $newData);
			#print ldap_error($ds);
		}

		function getAccountEmailAddress($_accountName)
		{
			$emailAddresses	= array();
			$ds = $GLOBALS['phpgw']->common->ldapConnect();
			$filter 	= sprintf("(&(uid=%s)(objectclass=posixAccount))",$_accountName);
			$attributes	= array('dn','mail','mailAlternateAddress');
			$sri = @ldap_search($ds, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $attributes);
			
			if ($sri)
			{
				$allValues = ldap_get_entries($ds, $sri);
				if(isset($allValues[0]['mail'][0]))
				{
					$emailAddresses[] = array
					(
						'name'		=> $GLOBALS['phpgw_info']['user']['fullname'],
						'address'	=> $allValues[0]['mail'][0],
						'type'		=> 'default'
					);
				}
				if($allValues[0]['mailalternateaddress']['count'] > 0)
				{
					$count = $allValues[0]['mailalternateaddress']['count'];
					for($i=0; $i < $count; ++$i)
					{
						$emailAddresses[] = array
						(
							'name'		=> $GLOBALS['phpgw_info']['user']['fullname'],
							'address'	=> $allValues[0]['mailalternateaddress'][$i],
							'type'		=> 'alternate'
						);
					}
				}
			}
			
			return $emailAddresses;
		}
	}
?>
