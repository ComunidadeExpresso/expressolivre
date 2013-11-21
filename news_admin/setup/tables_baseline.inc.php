<?php
	/**************************************************************************\
	* eGroupWare - Webpage news admin                                          *
	* http://www.egroupware.org                                                *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	* --------------------------------------------                             *
	* This program was sponsered by Golden Glair productions                   *
	* http://www.goldenglair.com                                               *
	\**************************************************************************/


   // table array for news_admin
	$phpgw_baseline = array(
		'webpage_news' => array(
			'fd' => array(
				'news_id' => array('type' => 'auto','nullable' => False),
				'news_date' => array('type' => 'int', 'precision' => 4,'nullable' => True),
				'news_subject' => array('type' => 'varchar', 'precision' => 255,'nullable' => True),
				'news_submittedby' => array('type' => 'varchar', 'precision' => 255,'nullable' => True),
				'news_content' => array('type' => 'blob','nullable' => True),
				'news_status' => array('type' => 'varchar', 'precision' => 16,'nullable' => True)
			),
			'pk' => array('news_id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
	);
?>
