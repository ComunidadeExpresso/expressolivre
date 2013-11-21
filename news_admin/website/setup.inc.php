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


	$path_to_header = '../../';
	$template_path  = $path_to_header . 'news_admin/website/templates/';
	$domain         = 'default';

	/* ********************************************************************\
	* Don't change anything after this line                                *
	\******************************************************************** */

	error_reporting(error_reporting() & ~E_NOTICE);

	function copyobj($a,&$b)
	{
		if(floor(phpversion()) > 4)
		{
			$b = $a->__clone();
		}
		else
		{
			$b = $a;
		}
		return;
	}

	$GLOBALS['phpgw_info']['flags']['noapi'] = True;
	include($path_to_header . 'header.inc.php');
	include(PHPGW_SERVER_ROOT . '/phpgwapi/inc/class.Template.inc.php');
	$tpl = new Template($template_path);
	include(PHPGW_SERVER_ROOT . '/phpgwapi/inc/class.db.inc.php');

	$GLOBALS['phpgw']->db = new db();
	$GLOBALS['phpgw']->db->Host     = $GLOBALS['phpgw_domain'][$domain]['server']['db_host'];
	$GLOBALS['phpgw']->db->Type     = $GLOBALS['phpgw_domain'][$domain]['db_type'];
	$GLOBALS['phpgw']->db->Database = $GLOBALS['phpgw_domain'][$domain]['db_name'];
	$GLOBALS['phpgw']->db->User     = $GLOBALS['phpgw_domain'][$domain]['db_user'];
	$GLOBALS['phpgw']->db->Password = $GLOBALS['phpgw_domain'][$domain]['db_pass'];

	include(PHPGW_SERVER_ROOT . '/news_admin/inc/class.sonews.inc.php');
	$news_obj = new sonews();

	include(PHPGW_SERVER_ROOT . '/news_admin/inc/class.soexport.inc.php');
	$export_obj = new soexport();
