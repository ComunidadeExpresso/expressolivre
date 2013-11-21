<?php
  /**************************************************************************\
  * eGroupWare - Setup                                                       *
  * http://www.egroupware.org                                                *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /**************************************************************************\
  * This file should be generated for you. It should never be edited by hand *
  \**************************************************************************/


  // table array for news_admin
	$phpgw_baseline = array(
		'phpgw_news' => array(
			'fd' => array(
				'news_id' => array('type' => 'auto','nullable' => False),
				'news_date' => array('type' => 'int','precision' => '4','nullable' => True),
				'news_subject' => array('type' => 'varchar','precision' => '255','nullable' => True),
				'news_submittedby' => array('type' => 'varchar','precision' => '255','nullable' => True),
				'news_content' => array('type' => 'blob','nullable' => True),
				'news_begin' => array('type' => 'int','precision' => '4','nullable' => True),
				'news_end' => array('type' => 'int','precision' => '4','nullable' => True),
				'news_cat' => array('type' => 'int','precision' => '4','nullable' => True),
				'news_teaser' => array('type' => 'varchar','precision' => '255','nullable' => True),
				'is_html' => array('type' => 'int','precision' => '2','nullable' => False,'default' => '0')
			),
			'pk' => array('news_id'),
			'fk' => array(),
			'ix' => array('news_date','news_subject'),
			'uc' => array()
		),
		'phpgw_news_export' => array(
			'fd' => array(
				'cat_id' => array('type' => 'int','precision' => '4','nullable' => False),
				'export_type' => array('type' => 'int','precision' => '2','nullable' => True),
				'export_itemsyntax' => array('type' => 'int','precision' => '2','nullable' => True),
				'export_title' => array('type' => 'varchar','precision' => '255','nullable' => True),
				'export_link' => array('type' => 'varchar','precision' => '255','nullable' => True),
				'export_description' => array('type' => 'text','nullable' => True),
				'export_img_title' => array('type' => 'varchar','precision' => '255','nullable' => True),
				'export_img_url' => array('type' => 'varchar','precision' => '255','nullable' => True),
				'export_img_link' => array('type' => 'varchar','precision' => '255','nullable' => True)
			),
			'pk' => array('cat_id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		)
	);
?>
