<?php
	/**************************************************************************\
	* eGroupWare - News                                                        *
	* http://www.egroupware.org                                                *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	* --------------------------------------------                             *
	\**************************************************************************/


	class soexport
	{
		var $db;

		function soexport()
		{
			$this->db = $GLOBALS['phpgw']->db;
		}

		function readconfig($cat_id)
		{
			$sql = "SELECT * FROM phpgw_news_export where cat_id = $cat_id";
			$this->db->query($sql,__LINE__,__FILE__);
			if ($this->db->next_record())
			{
				$result = array();
				foreach (array('type','itemsyntax','title','link','description','img_title','img_url','img_link') as $config)
				{
					$result[$config] = $this->db->f('export_'.$config);
				}
				return $result;
			}
			else
			{
				return false;
			}
		}

		function saveconfig($cat_id,$config)
		{
			$sql = "DELETE FROM phpgw_news_export where cat_id = $cat_id";
			$this->db->query($sql,__LINE__,__FILE__);
			$sql = "INSERT INTO phpgw_news_export " . 
				"(cat_id,export_type,export_itemsyntax,export_title,export_link,export_description,export_img_title,export_img_url,export_img_link) " .
				"VALUES ($cat_id,'" . $config['type']  . "','" . $config['itemsyntax'] . "','" . $config['title'] . 
				"','" . $config['link'] . "','" . $config['description'] . "','" . $config['img_title'] . 
				"','" . $config['img_url'] . "','" . $config['img_link'] . "')";
			$this->db->query($sql,__LINE__,__FILE__);
		}

	}
