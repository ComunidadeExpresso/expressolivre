<?php
	/**************************************************************************\
	* EGroupWare                                                               *
	* http://www.egroupware.org                                                *
	* http://www.phpgw.de                                                      *
	* Author: lkneschke@phpgw.de                                               *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
 	\**************************************************************************/


	$phpgw_baseline = array(
		'phpgw_emailadmin' => array(
			'fd' => array(
				'profileID' => array('type' => 'auto','nullable' => False),
				'smtpServer' => array('type' => 'varchar','precision' => '80'),
				'smtpType' => array('type' => 'int','precision' => '4'),
				'smtpPort' => array('type' => 'int','precision' => '4'),
				'smtpDelimiter' => array('type' => 'varchar','precision' => '1'),
				'smtpAuth' => array('type' => 'varchar','precision' => '3'),
				'smtpLDAPServer' => array('type' => 'varchar','precision' => '80'),
				'smtpLDAPBaseDN' => array('type' => 'varchar','precision' => '200'),
				'smtpLDAPAdminDN' => array('type' => 'varchar','precision' => '200'),
				'smtpLDAPAdminPW' => array('type' => 'varchar','precision' => '30'),
				'smtpLDAPUseDefault' => array('type' => 'varchar','precision' => '3'),
				'imapServer' => array('type' => 'varchar','precision' => '80'),
				'imapType' => array('type' => 'int','precision' => '4'),
				'imapPort' => array('type' => 'int','precision' => '4'),
				'imapDelimiter' => array('type' => 'varchar','precision' => '1'),
				'imapLoginType' => array('type' => 'varchar','precision' => '20'),
				'imapTLSAuthentication' => array('type' => 'varchar','precision' => '3'),
				'imapTLSEncryption' => array('type' => 'varchar','precision' => '3'),
				'imapEnableCyrusAdmin' => array('type' => 'varchar','precision' => '3'),
				'imapAdminUsername' => array('type' => 'varchar','precision' => '40'),
				'imapAdminPW' => array('type' => 'varchar','precision' => '40'),
				'imapEnableSieve' => array('type' => 'varchar','precision' => '3'),
				'imapSieveServer' => array('type' => 'varchar','precision' => '80'),
				'imapSievePort' => array('type' => 'int','precision' => '4'),
				'description' => array('type' => 'varchar','precision' => '200'),
				'defaultDomain' => array('type' => 'varchar','precision' => '100'),
				'organisationName' => array('type' => 'varchar','precision' => '100'),
				'userDefinedAccounts' => array('type' => 'varchar','precision' => '3'),
				'imapCreateSpamFolder' => array('type' => 'varchar','precision' => '3'),
				'imapCyrusUserPostSpam' => array('type' => 'varchar','precision' => '30'),
				'imapoldcclient' => array('type' => 'varchar','precision' => '3'),
				'imapdefaulttrashfolder' => array('type' => 'varchar','precision' => '20'),
				'imapdefaultsentfolder' => array('type' => 'varchar','precision' => '20'),
				'imapdefaultdraftsfolder' => array('type' => 'varchar','precision' => '20'),
				'imapdefaultspamfolder' => array('type' => 'varchar','precision' => '20')
			),
			'pk' => array('profileID'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		)
	);
?>
