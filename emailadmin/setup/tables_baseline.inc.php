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
				'profileID'	=> array('type' => 'auto', 'nullable' => false),
				'smtpServer'	=> array('type' => 'varchar', 'precision' => 80),
				'smtpPort'	=> array('type' => 'int', 'precision' => 4),
				'smtpAuth'	=> array('type' => 'varchar', 'precision' => 3),
				'ldapServername' => array('type' => 'varchar', 'precision' => 80),
				'ldapBasedn'	=> array('type' => 'varchar', 'precision' => 200),
				'ldapAdmindn'	=> array('type' => 'varchar', 'precision' => 200),
				'ldapAdminpw'	=> array('type' => 'varchar', 'precision' => 30),
				'ldapUseDefault' => array('type' => 'varchar', 'precision' => 3),
				'description'	=> array('type' => 'varchar', 'precision' => 200)
			),
			'pk' => array('profileID'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		)
	);
?>
