<?php
  /**************************************************************************\
  * eGroupWare                                                               *
  * http://www.egroupware.org                                                *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/


	$phpgw_baseline = array
	(
		'phpgw_filemanager_notification' => array
		(
			'fd' => array(
				'filemanager_id' => array('type' => 'auto','nullable' => False),
				'email_from' => array('type' => 'varchar','precision' => '100','nullable' => False),
				'email_to' => array('type' => 'text','nullable' => False)
			),
			'pk' => array('filemanager_id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		)
	);
?>
