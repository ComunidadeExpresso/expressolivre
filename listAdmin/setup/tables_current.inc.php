<?php
	/*******************************************************************************\
	*  This program is free software; you can redistribute it and/or modify it	*
	*  under the terms of the GNU General Public License as published by the	*
	*  Free Software Foundation; either version 2 of the License, or (at your	*
	*  option) any later version.							*
	\*******************************************************************************/

	$phpgw_baseline = array(
		'phpgw_expressoadmin' => array(
			'fd' => array(
				'manager_lid'	=> array('type' => 'varchar','precision' => 50,'nullable' => false),
				'context'		=> array('type' => 'varchar','precision' => 255,'nullable' => false),
				'acl'			=> array('type' => 'int','precision' => 8,'nullable' => false)
			),
			'pk' => array(),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		
		'phpgw_expressoadmin_apps' => array(
			'fd' => array(
				'manager_lid'	=> array('type' => 'varchar','precision' => 50,'nullable' => false),
				'context'		=> array('type' => 'varchar','precision' => 255,'nullable' => false),
				'app'			=> array('type' => 'varchar','precision' => 100,'nullable' => false)
			),
			'pk' => array(),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),

	);
?>
