<?php
	/**************************************************************************\
	* eGroupWare API - Categories                                              *
	* This file written by Joseph Engo <jengo@phpgroupware.org>                *
	*                  and Bettina Gille [ceb@phpgroupware.org]                *
	* Category manager                                                         *
	* Copyright (C) 2000, 2001 Joseph Engo, Bettina Gille                      *
	* Copyright (C) 2002, 2003 Bettina Gille                                   *
	* ------------------------------------------------------------------------ *
	* This library is part of the eGroupWare API                               *
	* http://www.egroupware.org                                                *
	* ------------------------------------------------------------------------ *
	* This library is free software; you can redistribute it and/or modify it  *
	* under the terms of the GNU Lesser General Public License as published by *
	* the Free Software Foundation; either version 2.1 of the License,         *
	* or any later version.                                                    *
	* This library is distributed in the hope that it will be useful, but      *
	* WITHOUT ANY WARRANTY; without even the implied warranty of               *
	* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     *
	* See the GNU Lesser General Public License for more details.              *
	* You should have received a copy of the GNU Lesser General Public License *
	* along with this library; if not, write to the Free Software Foundation,  *
	* Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA            *
	\**************************************************************************/

	/*!
	@class categories
	@abstract class adds ability for applications to make use of categories
	@discussion examples can be found in notes app
	*/
	class categories
	{
		var $account_id;
		var $app_name;
		var $cats;
		var $db;
		var $total_records;
		var $grants;

		/*!
		@function categories
		@abstract constructor for categories class
		@param $accountid account id
		@param $app_name app name defaults to current app
		*/
		function categories($accountid = '',$app_name = '')
		{
			$account_id = get_account_id($accountid);

			if (! $app_name)
			{
				$app_name = $GLOBALS['phpgw_info']['flags']['currentapp'];
			}

			$this->account_id	= $account_id;
			$this->app_name		= $GLOBALS['phpgw']->db->db_addslashes($app_name);
			$this->db			= $GLOBALS['phpgw']->db;
			$this->db2			= $this->db;			
			$this->grants		= $GLOBALS['phpgw']->acl->get_grants($app_name);
		}

		/*!
		@function filter
		@abstract ?
		@param $type string
		@result string either subs or mains
		*/
		function filter($type)
		{
			switch ($type)
			{
				case 'subs':		$s = ' AND cat_parent != 0'; break;
				case 'mains':		$s = ' AND cat_parent = 0'; break;
				case 'appandmains':	$s = " AND cat_appname='" . $this->app_name . "' AND cat_parent =0"; break;
				case 'appandsubs':	$s = " AND cat_appname='" . $this->app_name . "' AND cat_parent !=0"; break;
				case 'noglobal':	$s = " AND cat_appname != '" . $this->app_name . "'"; break;
				case 'noglobalapp':	$s = " AND cat_appname = '" . $this->app_name . "' AND cat_owner != " . $this->account_id; break;
				default:			return False;
			}
			return $s;
		}

		/*!
		@function total
		@abstract returns the total number of categories for app, subs or mains
		@param $for one of either 'app' 'subs' or 'mains'
		@result integer count of categories
		*/
		function total($for = 'app')
		{
			switch($for)
			{
				case 'app':			$w = " WHERE cat_appname='" . $this->app_name . "'"; break;
				case 'appandmains':	$w = " WHERE cat_appname='" . $this->app_name . "' AND cat_parent =0"; break;
				case 'appandsubs':	$w = " WHERE cat_appname='" . $this->app_name . "' AND cat_parent !=0"; break;
				case 'subs':		$w = ' WHERE cat_parent != 0'; break;
				case 'mains':		$w = ' WHERE cat_parent = 0'; break;
				default:			return False;
			}

			$this->db->query("SELECT COUNT(cat_id) FROM phpgw_categories $w",__LINE__,__FILE__);
			$this->db->next_record();

			return $this->db->f(0);
		}

		/*!
		@funtion return_all_children
		@abstract returns array with id's of all children from $cat_id and $cat_id itself!
		@param $cat_id integer cat-id to search for
		@returns array of cat-id's
		*/
		function return_all_children($cat_id)
		{
			$all_children = array($cat_id);

			$children = $this->return_array('subs',0,False,'','','',True,$cat_id,-1,'id');
			if (is_array($children) && count($children))
			{
				foreach($children as $child)
				{
					$all_children = array_merge($all_children,$this->return_all_children($child['id']));
				}
			}
			//echo "<p>categories::return_all_children($cat_id)=(".implode(',',$all_children).")</p>\n";
			return $all_children;
		}

		/*!
		@function return_array
		@abstract return an array populated with categories
		@param $type string defaults to 'all'
		@param $start ?
		@param $limit ?
		@param $query string defaults to ''
		@param $sort string sort order, either defaults to 'ASC'
		@param $order order by
		@param $globals True or False, includes the global egroupware categories or not
		@param $parent_id
		@param $lastmod integer defaults to -1
		@param column string default to '' (All), includes the column returned.
		@result $cats array
		*/
		function return_array($type,$start,$limit = True,$query = '',$sort = '',$order = '',$globals = False, $parent_id = '', $lastmod = -1, $column = '')
		{
			return $this -> return_sorted_array( $start, $limit, $query, $sort, $order, $globals, $parent_id, NULL, $lastmod, $column);
		}
		/*!
		@function return_sorted_array
		@abstract return an array populated with categories
		@param $type string defaults to 'all'
		@param $start ?
		@param $limit ?
		@param $query string defaults to ''
		@param $sort string sort order, either defaults to 'ASC'
		@param $order order by
		@param $globals True or False, includes the global egroupware categories or not
		@param $parent_id string defaults to '', includes the parent category ID
		@param $group_id integer defaults to NULL, includes the gidNumber
		@param $lastmod integer defaults to -1
		@param column string default to '' (All), includes the column returned.
		@result $cats array
		*/			
		function return_sorted_array($start,$limit = True,$query = '',$sort = '',$order = '',$globals = False, $parent_id = '',$group_id = NULL,$lastmod = -1, $column = '')
		{
			//casting and slashes for security
			$start = (int)$start;
			$query = $this->db->db_addslashes($query);
			$sort  = $this->db->db_addslashes($sort);
			$order = $this->db->db_addslashes($order);
			$parent_id = (int)$parent_id;

			if ($globals && !$group_id)
			{
				$global_cats = " cat_appname='phpgw'";
			}
			
			if (!$sort)
			{
				$sort = 'ASC';
			}

			if (!empty($order) && preg_match('/^[a-zA-Z_, ]+$/',$order) && (empty($sort) || preg_match('/^(ASC|DESC|asc|desc)$/',$sort)))
			{
				$ordermethod = " ORDER BY $order $sort";
			}
			else
			{
				$ordermethod = ' ORDER BY cat_name ASC';
			}
									
			if($group_id){
				$grant_cats .= " cat_owner='".$group_id."' ";
			}
			else if ($this->account_id != '-1'){
				$grants = $this->grants;
				$groups = $GLOBALS['phpgw']->accounts->membership();
			
				if (is_array($this->grants))
				{
					
					foreach($grants as $idx => $user){												
						$public_user_list[$user] = $user;
					}
					if(is_array($groups)){
						foreach($groups as $idx => $group) {
							$public_user_list[$group['account_id']] = $group['account_id'];
						}
					}
					@reset($public_user_list);
					$grant_cats = " (cat_owner='" . $this->account_id . "' ".(is_array($public_user_list) ? "OR (cat_owner in(" . implode(',',$public_user_list) . ")  AND cat_access='public')" : "").") ";
					
				}
				else
				{
					$grant_cats = " cat_owner='" . $this->account_id . "' or cat_owner='-1' ";
				}
			}
			

			$parent_select = ' AND cat_parent=' . $parent_id;

			if ($query)
			{
				$querymethod = " AND (cat_name ILIKE '%$query%' OR cat_description ILIKE '%$query%') ";
			}

			if($lastmod && $lastmod >= 0)
			{
				$querymethod .= ' AND last_mod > ' . (int)$lastmod;
			}

			if($column)
			{
				switch($column)
				{
					case 'id': 			$table_column = ' cat_id '; break;
					case 'owner': 		$table_column = ' cat_owner '; break;
					case 'access': 		$table_column = ' cat_access '; break;
					case 'app_name': 	$table_column = ' cat_appname '; break;
					case 'main': 		$table_column = ' cat_main '; break;
					case 'parent': 		$table_column = ' cat_parent '; break;
					case 'name': 		$table_column = ' cat_name '; break;
					case 'description': $table_column = ' cat_description '; break;
					case 'data': 		$table_column = ' cat_data '; break;
					case 'last_mod':	$table_column = ' last_mod '; break;
					default:			$table_column = ' cat_id '; break;
				}
			}
			else
			{
				$table_column = ' * ';
			}

			$this->app_name = pg_escape_string($this->app_name);
			$sql = "SELECT".$table_column."FROM phpgw_categories WHERE (cat_appname='" . $this->app_name. "' ".
					($grant_cats ? " AND".$grant_cats : "") .($global_cats ? " OR".$global_cats: "").
					")".(isset($querymethod)?$querymethod:"");						
			
			$this->db2->query($sql . $parent_select,__LINE__,__FILE__);
			$total = $this->db2->num_rows();

			if ($limit)
			{
				$this->db->limit_query($sql . $parent_select . $ordermethod,$start,__LINE__,__FILE__);
			}
			else
			{
				$this->db->query($sql . $parent_select . $ordermethod,__LINE__,__FILE__);
			}

			$i = 0;
			while ($this->db->next_record())
			{
				$cats[$i]['id']          = (int)$this->db->f('cat_id');
				$cats[$i]['owner']       = (int)$this->db->f('cat_owner');
				if($cats[$i]['owner'] > 0){
				// 	Load Name Group.
					$group = $this->get_group($cats[$i]['owner']);
					$cats[$i]['owner'] = $group['name'];
				}
				$cats[$i]['access']      = $this->db->f('cat_access');
				$cats[$i]['app_name']    = $this->db->f('cat_appname');
				$cats[$i]['main']        = (int)$this->db->f('cat_main');
				$cats[$i]['level']       = (int)$this->db->f('cat_level');
				$cats[$i]['parent']      = (int)$this->db->f('cat_parent');
				$cats[$i]['name']        = $this->db->f('cat_name');
				$cats[$i]['description'] = $this->db->f('cat_description');
				$cats[$i]['data']        = $this->db->f('cat_data');
					
				++$i;
			}

			$num_cats = count($cats);
			for ($i=0;$i < $num_cats;++$i)
			{
				$sub_select = ' AND cat_parent=' . $cats[$i]['id'] . ' AND cat_level=' . ($cats[$i]['level']+1);
				$this->db->query($sql . $sub_select . $ordermethod,__LINE__,__FILE__);
				$total += $this->db->num_rows();

				$subcats = array();
				$j = 0;
				while ($this->db->next_record())
				{
					$subcats[$j]['id']          = (int)$this->db->f('cat_id');
					$subcats[$j]['owner']       = (int)$this->db->f('cat_owner');
					$subcats[$j]['access']      = $this->db->f('cat_access');
					$subcats[$j]['app_name']    = $this->db->f('cat_appname');
					$subcats[$j]['main']        = (int)$this->db->f('cat_main');
					$subcats[$j]['level']       = (int)$this->db->f('cat_level');
					$subcats[$j]['parent']      = (int)$this->db->f('cat_parent');
					$subcats[$j]['name']        = $this->db->f('cat_name');
					$subcats[$j]['description'] = $this->db->f('cat_description');
					$subcats[$j]['data']        = $this->db->f('cat_data');
					++$j;
				}

				$num_subcats = count($subcats);
				if ($num_subcats != 0)
				{
					$newcats = array();
					for ($k = 0; $k <= $i; ++$k)
					{
						$newcats[$k] = $cats[$k];
					}
					for ($k = 0; $k < $num_subcats; ++$k)
					{
						$newcats[$k+$i+1] = $subcats[$k];
					}
					for ($k = $i+1; $k < $num_cats; ++$k)
					{
						$newcats[$k+$num_subcats] = $cats[$k];
					}
					$cats = $newcats;
					$num_cats = count($cats);
				}
			}
			$this->total_records = $total;
			return $cats;
		}

		/*!
		@function return_single
		@abstract return single
		@param $id integer id of category
		@result $cats  array populated with
		*/
		function return_single($id = '')
		{
			$this->db->query('SELECT * FROM phpgw_categories WHERE cat_id=' . (int)$id,__LINE__,__FILE__);
			if ($this->db->next_record())
			{
				$cats[0]['id']          = $this->db->f('cat_id');
				$cats[0]['owner']       = $this->db->f('cat_owner');
				$cats[0]['access']      = $this->db->f('cat_access');
				$cats[0]['app_name']    = $this->db->f('cat_appname');
				$cats[0]['main']        = $this->db->f('cat_main');
				$cats[0]['level']       = $this->db->f('cat_level');
				$cats[0]['parent']      = $this->db->f('cat_parent');
				$cats[0]['name']        = $this->db->f('cat_name');
				$cats[0]['description'] = $this->db->f('cat_description');
				$cats[0]['data']        = $this->db->f('cat_data');
				if($cats[0]['owner'] > 0){
				// 	Load Group.
					$group = $this->get_group($cats[0]['owner']);
					$cats[0]['id_group']    = $group['id'];
					$cats[0]['name_group']  = $group['name'];					
				}				
			}
			return $cats;
		}

		/*!
		@function formated_list
		@abstract return into a select box, list or other formats
		@param $format currently supports select (select box) or list
		@param $type string - subs or mains
		@param $selected - cat_id or array with cat_id values
		@param $globals True or False, includes the global egroupware categories or not
		@result $s array - populated with categories
		*/
		function formatted_list($format,$type='',$selected = '',$globals = False,$site_link = 'site')
		{
			return $this->formated_list($format,$type,$selected,$globals,$site_link);
		}
		function get_group($id)
		{
			if (!IsSet($id))
				return "";
			return array("id" => $id, "name" => $GLOBALS['phpgw']->accounts->id2name($id));
		}

		function formated_list($format,$type='',$selected = '',$globals = False,$site_link = 'site')
		{
			if(is_array($format))
			{
				$temp_format = $format['format'];
				$type = ($format['type']?$format['type']:'all');
				$selected = (isset($format['selected'])?$format['selected']:'');
				$self = (isset($format['self'])?$format['self']:'');
				$globals = (isset($format['globals'])?$format['globals']:True);
				$site_link = (isset($format['site_link'])?$format['site_link']:'site');
				settype($format,'string');
				$format = ($temp_format?$temp_format:'select');
				unset($temp_format);
			}

			if (!is_array($selected))
			{
				$selected = explode(',',$selected);
			}

			if ($type != 'all')
			{
				$cats = $this->return_array($type,$start,False,$query,$sort,$order,$globals);
			}
			else
			{
				$cats = $this->return_sorted_array($start,False,$query,$sort,$order,$globals);
			}

			if($self)
			{
				for ($i=0;$i<count($cats);++$i)
				{
					if ($cats[$i]['id'] == $self)
					{
						unset($cats[$i]);
					}
				}
			}

			if ($format == 'select')
			{
				while (is_array($cats) && list(,$cat) = each($cats))
				{
					$s .= '<option value="' . $cat['id'] . '"';
					if (in_array($cat['id'],$selected))
					{
						$s .= ' selected';
					}
					$s .= '>';
					for ($j=0;$j<$cat['level'];++$j)
					{
						$s .= '&nbsp;';
					}
					$s .= $GLOBALS['phpgw']->strip_html($cat['name']);
					if ($cat['app_name'] == 'phpgw')
					{
						$s .= '&nbsp;&lt;' . lang('Global') . '&gt;';
					}
					if ($cat['owner'] == '-1')
					{
						$s .= '&nbsp;&lt;' . lang('Global') . '&nbsp;' . lang($this->app_name) . '&gt;';
					}
					$s .= '</option>' . "\n";
				}
				return $s;
			}

			if ($format == 'list')
			{
				$space = '&nbsp;&nbsp;';

				$s  = '<table border="0" cellpadding="2" cellspacing="2">' . "\n";

				if ($this->total_records > 0)
				{
                    $cats_count = count($cats);
					for ($i=0;$i<$cats_count;++$i)
					{
						$image_set = '&nbsp;';

						if (in_array($cats[$i]['id'],$selected))
						{
							$image_set = '<img src="' . PHPGW_IMAGES_DIR . '/roter_pfeil.gif">';
						}

						if (($cats[$i]['level'] == 0) && !in_array($cats[$i]['id'],$selected))
						{
							$image_set = '<img src="' . PHPGW_IMAGES_DIR . '/grauer_pfeil.gif">';
						}

						$space_set = str_repeat($space,$cats[$i]['level']);

						$s .= '<tr>' . "\n";
						$s .= '<td width="8">' . $image_set . '</td>' . "\n";
						$s .= '<td>' . $space_set . '<a href="' . $GLOBALS['phpgw']->link($site_link,'cat_id=' . $cats[$i]['id']) . '">'
							. $GLOBALS['phpgw']->strip_html($cats[$i]['name'])
							. '</a></td>' . "\n"
							. '</tr>' . "\n";
					}
				}
				$s .= '</table>' . "\n";
				return $s;
			}
		}

		/*!
		@function add
		@abstract add categories
		@param $cat_name category name
		@param $cat_parent category parent
		@param $cat_description category description defaults to ''
		@param $cat_data category data defaults to ''
		*/
		function add($values)
		{
			$values['id']		= (int)$values['id'];
			$values['parent']	= (int)$values['parent'];

			if ($values['parent'] > 0)
			{
				$values['level'] = $this->id2name($values['parent'],'level')+1;
				$values['main'] = $this->id2name($values['parent'],'main');
			}

			$values['descr'] = $this->db->db_addslashes($values['descr']);
			$values['name'] = $this->db->db_addslashes($values['name']);

			if ($values['id'] > 0)
			{
				$id_col = 'cat_id,';
				$id_val = $values['id'] . ',';
			}
			$this->db->query('INSERT INTO phpgw_categories (' . $id_col . 'cat_parent,cat_owner,cat_access,cat_appname,cat_name,cat_description,cat_data,'
				. 'cat_main,cat_level, last_mod) VALUES (' . $id_val . (int)$values['parent'] . ',' . ($values['group']!= 0 ? $values['group'] : $this->account_id) . ",'" . $values['access']
				. "','" . $this->app_name . "','" . $values['name'] . "','" . $values['descr'] . "','" . $values['data']
				. "'," . (int)$values['main'] . ',' . (int)$values['level'] . ',' . time() . ')',__LINE__,__FILE__);

			if ($values['id'] > 0)
			{
				$max = $values['id'];
			}
			else
			{
				$max = $this->db->get_last_insert_id('phpgw_categories','cat_id');
			}

			$max = (int)$max;
			if ($values['parent'] == 0)
			{
				$this->db->query('UPDATE phpgw_categories SET cat_main=' . $max . ' WHERE cat_id=' . $max,__LINE__,__FILE__);
			}
			return $max;
		}

		/*!
		@function delete
		@abstract delete category
		@param $cat_id int - category id
		*/
		/*function delete($cat_id,$subs = False)
		{
			$cat_id = (int)$cat_id;
			if ($subs)
			{
				$subdelete = ' OR cat_parent=' . $cat_id . ' OR cat_main=' . $cat_id;
			}

			$this->db->query('DELETE FROM phpgw_categories WHERE cat_id=' . $cat_id . $subdelete . " AND cat_appname='"
							. $this->app_name . "'",__LINE__,__FILE__);
		} */

		function delete($cat_id, $drop_subs = False, $modify_subs = False)
		{
			$cat_id = (int)$cat_id;
			if ($drop_subs)
			{
				$subdelete = ' OR cat_parent=' . $cat_id . ' OR cat_main=' . $cat_id;
			}

			if ($modify_subs)
			{
				$cats = $this->return_sorted_array('',False,'','','',False, $cat_id);

				$new_parent = $this->id2name($cat_id,'parent');

                $cats_count = count($cats);
				for ($i=0;$i<$cats_count;++$i)
				{
					if ($cats[$i]['level'] == 1)
					{
						$this->db->query('UPDATE phpgw_categories set cat_level=0, cat_parent=0, cat_main=' . (int)$cats[$i]['id']
							. ' WHERE cat_id=' . (int)$cats[$i]['id'] . " AND cat_appname='" . $this->app_name . "'",__LINE__,__FILE__);
						$new_main = $cats[$i]['id'];
					}
					else
					{
						if ($new_main)
						{
							$update_main = ',cat_main=' . $new_main;
						}

						if ($cats[$i]['parent'] == $cat_id)
						{
							$update_parent = ',cat_parent=' . $new_parent;
						}

						$this->db->query('UPDATE phpgw_categories set cat_level=' . ($cats[$i]['level']-1) . $update_main . $update_parent
							. ' WHERE cat_id=' . (int)$cats[$i]['id'] . " AND cat_appname='" . $this->app_name . "'",__LINE__,__FILE__);
					}
				}
			}

			$this->db->query('DELETE FROM phpgw_categories WHERE cat_id=' . $cat_id . $subdelete . " AND cat_appname='"
				. $this->app_name . "'",__LINE__,__FILE__);
		}

		/*!
		@function edit
		@abstract edit a category
		@param $cat_id int - category id
		@param $cat_parent category parent
		@param $cat_description category description defaults to ''
		@param $cat_data category data defaults to ''
		*/
		function edit($values)
		{
			$values['id']     = (int)$values['id'];
			$values['parent'] = (int)$values['parent'];
			$values['owner'] = (int)$values['group'];
			if($values['owner']){
				$owner = "cat_owner = ".$values['owner'].",";
			}
			if (isset($values['old_parent']) && (int)$values['old_parent'] != $values['parent'])
			{
				$this->delete($values['id'],False,True);
				return $this->add($values);
			}
			else
			{
				if ($values['parent'] > 0)
				{
					$values['main']  = (int)$this->id2name($values['parent'],'main');
					$values['level'] = (int)$this->id2name($values['parent'],'level') + 1;
				}
				else
				{
					$values['main']  = $values['id'];
					$values['level'] = 0;
				}
			}

			$values['descr'] = $this->db->db_addslashes($values['descr']);
			$values['name'] = $this->db->db_addslashes($values['name']);

			$sql = "UPDATE phpgw_categories SET $owner cat_name='" . $values['name'] . "', cat_description='" . $values['descr']
				. "', cat_data='" . $values['data'] . "', cat_parent=" . $values['parent'] . ", cat_access='"
				. $values['access'] . "', cat_main=" . $values['main'] . ', cat_level=' . $values['level'] . ',last_mod=' . time()
				. " WHERE cat_appname='" . $this->app_name . "' AND cat_id=" . $values['id'];

			$this->db->query($sql,__LINE__,__FILE__);
			return $values['id'];
		}

		function name2id($cat_name)
		{
			$this->db->query("SELECT cat_id FROM phpgw_categories WHERE cat_name='" . $this->db->db_addslashes($cat_name) . "' "
				."AND cat_appname='" . $this->app_name . "' AND (cat_owner=" . $this->account_id . ' OR cat_owner=-1)',__LINE__,__FILE__);

			if(!$this->db->num_rows())
			{
				return 0;
			}

			$this->db->next_record();

			return $this->db->f('cat_id');
		}

		function id2name($cat_id = '', $item = 'name')
		{
			$cat_id = (int)$cat_id;
			if($cat_id == 0)
			{
				return '--';
			}
			switch($item)
			{
				case 'owner':	$value = 'cat_owner'; break;
				case 'main':	$value = 'cat_main'; break;
				case 'level':	$value = 'cat_level'; break;
				case 'parent':	$value = 'cat_parent'; break;
				case 'name':	$value = 'cat_name'; break;
				default:		$value = 'cat_parent'; break;
			}

			$this->db->query("SELECT $value FROM phpgw_categories WHERE cat_id=" . $cat_id,__LINE__,__FILE__);
			$this->db->next_record();

			if ($this->db->f($value))
			{
				return $this->db->f($value);
			}
			else
			{
				if ($item == 'name')
				{
					return '--';
				}
			}
		}

		/*!
		@function return_name
		@abstract return category name given $cat_id
		@param $cat_id
		@result cat_name category name
		*/
		// NOTE: This is only a temp wrapper, use id2name() to keep things matching across the board. (jengo)
		function return_name($cat_id)
		{
			return $this->id2name($cat_id);
		}

		/*!
		@function exists
		@abstract used for checking if a category name exists
		@param $type subs or mains
		@param $cat_name category name
		@result boolean true or false
		*/
		function exists($type,$cat_name = '',$cat_id = '')
		{
			$cat_id = (int)$cat_id;
			$filter = $this->filter($type);

			if ($cat_name)
			{
				$cat_exists = " cat_name='" . $this->db->db_addslashes($cat_name) . "' ";
			}

			if ($cat_id)
			{
				$cat_exists = ' cat_parent=' . $cat_id;
			}

			if ($cat_name && $cat_id)
			{
				$cat_exists = " cat_name='" . $this->db->db_addslashes($cat_name) . "' AND cat_id != $cat_id ";
			}

			$this->db->query("SELECT COUNT(cat_id) FROM phpgw_categories WHERE $cat_exists $filter",__LINE__,__FILE__);

			$this->db->next_record();

			if ($this->db->f(0))
			{
				return True;
			}
			else
			{
				return False;
			}
		}
	}
?>
