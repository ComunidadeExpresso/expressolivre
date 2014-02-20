<?php
	/**************************************************************************\
	* eGroupWare API - Contacts manager for SQL                                *
	* This file written by Joseph Engo <jengo@phpgroupware.org>                *
	*   and Miles Lott <milosch@groupwhere.org>                                *
	* View and manipulate contact records using SQL                            *
	* Copyright (C) 2001 Joseph Engo                                           *
	* ------------------------------------------------------------------------ *
	* This library is part of the eGroupWare API                               *
	* http://www.egroupware.org/api                                            *
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
	
	/**
	* This class provides a contact database scheme. 
	* It attempts to be based on the vcard 2.1 standard, with mods as needed 
	* to make for more reasonable sql storage.
	* Note that changes here must also work in the LDAP version.
	* Syntax: CreateObject('phpgwapi.contacts');
	* Example1: $contacts = CreateObject('phpgwapi.contacts');
	*
	* @class contacts_
	* @abstract Contact Management System
	* @author jengo/Milosch
	* @license LGPL
	*/
	

	class contacts_
	{
		var $db = '';
		var $std_table='phpgw_addressbook';
		var $ext_table='phpgw_addressbook_extra';

		var $account_id = 0;
		var $total_records = 0;
		var $grants = '';

		/* The left side are the array elements used throughout phpgw, right side are the db field names. */
		var $stock_contact_fields = array(
			'fn'                  => 'fn',
			'n_given'             => 'n_given',
			'n_family'            => 'n_family',
			'n_middle'            => 'n_middle',
			'n_prefix'            => 'n_prefix',
			'n_suffix'            => 'n_suffix',
			'sound'               => 'sound',
			'bday'                => 'bday',
			'note'                => 'note',
			'tz'                  => 'tz',
			'geo'                 => 'geo',
			'url'                 => 'url',
			'pubkey'              => 'pubkey',
			'org_name'            => 'org_name',
			'org_unit'            => 'org_unit',
			'title'               => 'title',
			'adr_one_street'      => 'adr_one_street',
			'adr_one_locality'    => 'adr_one_locality',
			'adr_one_region'      => 'adr_one_region',
			'adr_one_postalcode'  => 'adr_one_postalcode',
			'adr_one_countryname' => 'adr_one_countryname',
			'adr_one_type'        => 'adr_one_type',
			'label'               => 'label',
			'adr_two_street'      => 'adr_two_street',
			'adr_two_locality'    => 'adr_two_locality',
			'adr_two_region'      => 'adr_two_region',
			'adr_two_postalcode'  => 'adr_two_postalcode',
			'adr_two_countryname' => 'adr_two_countryname',
			'adr_two_type'        => 'adr_two_type',
			'tel_work'            => 'tel_work',
			'tel_home'            => 'tel_home',
			'tel_voice'           => 'tel_voice',
			'tel_fax'             => 'tel_fax',
			'tel_msg'             => 'tel_msg',
			'tel_cell'            => 'tel_cell',
			'tel_pager'           => 'tel_pager',
			'tel_bbs'             => 'tel_bbs',
			'tel_modem'           => 'tel_modem',
			'tel_car'             => 'tel_car',
			'tel_isdn'            => 'tel_isdn',
			'tel_video'           => 'tel_video',
			'tel_prefer'          => 'tel_prefer',
			'email'               => 'email',
			'email_type'          => 'email_type',
			'email_home'          => 'email_home',
			'email_home_type'     => 'email_home_type'
		);

		var $non_contact_fields = array(
			'id'     => 'id',
			'lid'    => 'lid',
			'tid'    => 'tid',
			'cat_id' => 'cat_id',
			'access' => 'access',
			'owner'  => 'owner'
		);

		var $adr_types = array();

		/* Used to set preferred number field */
		var $tel_types = array(
			'work'  => 'work',
			'home'  => 'home',
			'voice' => 'voice',
			'fax'   => 'fax',
			'msg'   => 'msg',
			'cell'  => 'cell',
			'pager' => 'pager',
			'bbs'   => 'bbs',
			'modem' => 'modem',
			'car'   => 'car',
			'isdn'  => 'isdn',
			'video' => 'video'
		);

		/* Used to set email_type fields */
		var $email_types = array(
			'INTERNET'   => 'INTERNET',
			'CompuServe' => 'CompuServe',
			'AOL'        => 'AOL',
			'Prodigy'    => 'Prodigy',
			'eWorld'     => 'eWorld',
			'AppleLink'  => 'AppleLink',
			'AppleTalk'  => 'AppleTalk',
			'PowerShare' => 'PowerShare',
			'IBMMail'    => 'IBMMail',
			'ATTMail'    => 'ATTMail',
			'MCIMail'    => 'MCIMail',
			'X.400'      => 'X.400',
			'TLX'        => 'TLX'
		);

		function contacts_($useacl=True)
		{
//			$this->db = $GLOBALS['phpgw']->db;
			copyobj($GLOBALS['phpgw']->db,$this->db);
			if($useacl)
			{
				$this->grants = $GLOBALS['phpgw']->acl->get_grants('addressbook');
			}
			$this->account_id = $GLOBALS['phpgw_info']['user']['account_id'];

			/* Used to flag an address as being:
			   domestic AND/OR international(default)
			   parcel(default)
			   postal(default)
			*/
			$this->adr_types = array(
				'dom'    => lang('Domestic'),
				'intl'   => lang('International'),
				'parcel' => lang('Parcel'),
				'postal' => lang('Postal')
			);
		}

		/* send this the id and whatever fields you want to see */
		function read_single_entry($id,$fields='')
		{
			if (!$fields || empty($fields))
			{
				$fields = $this->stock_contact_fields;
			}
			list($stock_fields,$stock_fieldnames,$extra_fields) = $this->split_stock_and_extras($fields);

			if (count($stock_fieldnames))
			{
				$t_fields = ',' . implode(',',$stock_fieldnames);
				if ($t_fields == ',')
				{
					unset($t_fields);
				}
			}

			$this->db->query("SELECT id,lid,tid,owner,access,cat_id $t_fields FROM $this->std_table WHERE id=" . (int)$id);
			$this->db->next_record();

			$return_fields[0]['id']     = $this->db->f('id');
			$return_fields[0]['lid']    = $this->db->f('lid');
			$return_fields[0]['tid']    = $this->db->f('tid');
			$return_fields[0]['owner']  = $this->db->f('owner');
			$return_fields[0]['access'] = $this->db->f('access');
			$return_fields[0]['cat_id'] = $this->db->f('cat_id');
			$return_fields[0]['rights'] = (int)$this->grants[$this->db->f('owner')];

			if(@is_array($stock_fieldnames))
			{
				foreach($stock_fieldnames as $f_name)
				{
					$return_fields[0][$f_name] = $this->db->f($f_name);
				}
			}

			/* Setup address type fields for ui forms display */
			if ($this->db->f('adr_one_type'))
			{
				$one_type = $this->db->f('adr_one_type');
				foreach($this->adr_types as $name => $val)
				{
					eval("if (strstr(\$one_type,\$name)) { \$return_fields[0][\"one_\$name\"] = \"on\"; }");
				}
			}
			if ($this->db->f('adr_two_type'))
			{
				$two_type = $this->db->f('adr_two_type');
				foreach($this->adr_types as $name => $val)
				{
					eval("if (strstr(\$two_type,\$name)) { \$return_fields[0][\"two_\$name\"] = \"on\"; }");
				}
			}

			$this->db->query("SELECT contact_name,contact_value FROM $this->ext_table WHERE contact_id=" . (int)$this->db->f('id'),__LINE__,__FILE__);
			while ($this->db->next_record())
			{
				if ($extra_fields[$this->db->f('contact_name')])
				{
					$return_fields[0][$this->db->f('contact_name')] = $this->db->f('contact_value');
				}
			}
			return $return_fields;
		}

		function read_last_entry($fields='')
		{
			if (!$fields || empty($fields)) { $fields = $this->stock_contact_fields; }
			list($stock_fields,$stock_fieldnames,$extra_fields) =
				$this->split_stock_and_extras($fields);

			if (count($stock_fieldnames))
			{
				$t_fields = ',' . implode(',',$stock_fieldnames);
				if ($t_fields == ',')
				{
					unset($t_fields);
				}
			}

			$this->db->query('SELECT max(id) FROM '.$this->std_table,__LINE__,__FILE__);
			$this->db->next_record();

			$id = $this->db->f(0);

			$this->db->query("SELECT id,lid,tid,owner,access,cat_id $t_fields FROM $this->std_table WHERE id=" . (int)$id,__LINE__,__FILE__);
			$this->db->next_record();

			$return_fields[0]['id']     = $this->db->f('id');
			$return_fields[0]['lid']    = $this->db->f('lid');
			$return_fields[0]['tid']    = $this->db->f('tid');
			$return_fields[0]['owner']  = $this->db->f('owner');
			$return_fields[0]['access'] = $this->db->f('access');
			$return_fields[0]['cat_id'] = $this->db->f('cat_id');
			$return_fields[0]['rights'] = (int)$this->grants[$this->db->f('owner')];

			if (@is_array($stock_fieldnames))
			{
				foreach($stock_fieldnames as $f_name)
				{
					$return_fields[0][$f_name] = $this->db->f($f_name);
				}
			}

			/* Setup address type fields for ui forms display */
			if($this->db->f('adr_one_type'))
			{
				$one_type = $this->db->f('adr_one_type');
				foreach($this->adr_types as $name => $val)
				{
					eval("if (strstr(\$one_type,\$name)) { \$return_fields[0][\"one_\$name\"] = \"on\"; }");
				}
			}
			if($this->db->f('adr_two_type'))
			{
				$two_type = $this->db->f('adr_two_type');
				foreach($this->adr_types as $name => $val)
				{
					eval("if (strstr(\$two_type,\$name)) { \$return_fields[0][\"two_\$name\"] = \"on\"; }");
				}
			}

			$this->db->query("SELECT contact_name,contact_value FROM $this->ext_table WHERE contact_id=" . (int)$this->db->f('id'),__LINE__,__FILE__);
			while ($this->db->next_record())
			{
				if ($extra_fields[$this->db->f('contact_name')])
				{
					$return_fields[0][$this->db->f('contact_name')] = $this->db->f('contact_value');
				}
			}
			return $return_fields;
		}

		/* send this the range, query, sort, order and whatever fields you want to see */
		function read($start=0,$limit=0,$fields='',$query='',$filter='',$sort='',$order='', $lastmod=-1,$cquery='')
		{
			if(!$start)  { $start  = 0; }
			if(!$limit)  { $limit  = 0; }
			if(!$filter) { $filter = 'tid=n'; }

			if (!$fields || empty($fields)) { $fields = $this->stock_contact_fields; }
			$DEBUG = 0;

			list($stock_fields,$stock_fieldnames,$extra_fields) = $this->split_stock_and_extras($fields);
			if (count($stock_fieldnames))
			{
				$t_fields = ',' . implode(',',$stock_fieldnames);
				if ($t_fields == ',')
				{
					unset($t_fields);
				}
			}

			/* turn filter's a=b,c=d OR a=b into an array */
			if ($filter)
			{
				$check_stock = $this->stock_contact_fields + $this->non_contact_fields;

				if ($DEBUG) { echo 'DEBUG - Inbound filter is: #'.$filter.'#'; }

				$filterlist = array();
				foreach(explode(',',$filter) as $pair)
				{
					list($name,$value) = explode('=',$pair,2);
					if (!$name || !isset($check_stock[$name]))	// only use valid column-names
					{
						continue;
					}
					if ($DEBUG) { echo '<br />DEBUG - Filter intermediate strings 2: #'.$name.'# => #'.$value.'#'; }

					if (empty($value))
					{
						if ($DEBUG) { echo '<br />DEBUG - filter field "'.$name.'" is empty (NULL)'; }

						$filterlist[] = $name.' is NULL';
					}
					else
					{
						if($name == 'cat_id')
						{
							if (!(int)$value) continue;	// nothing to filter

							//$filterlist[] = "(" . $name . " LIKE '%," . (int)$value . ",%' OR " . $name."='".(int)$value."')";
							if (!is_object($GLOBALS['phpgw']->categories))
							{
								$GLOBALS['phpgw']->categories = CreateObject('phpgwapi.categories');
							}
							$cats = $GLOBALS['phpgw']->categories->return_all_children((int)$value);
							$cat_filter = '(cat_id IN ('.implode(',',$cats).')';
							foreach($cats as $cat)
							{
								$cat_filter .= " OR cat_id LIKE '%,$cat,%'";
							}
							$cat_filter .= ')';
							$filterlist[] = $cat_filter;
						}
						elseif(@is_int($value))
						{
							$filterlist[] = $name . '=' . $value;
						}
						elseif ($value == "!''")	// check for not empty
						{
							$filterlist[] = $name . "!=''";
						}
						else
						{
							$filterlist[] = $name . "='" . $this->db->db_addslashes($value) . "'";
						}
					}
				}
				$filterlist = implode(' AND ',$filterlist);

				if ($DEBUG)
				{
					echo '<br />DEBUG - Filter output string: #'.$filterlist.'#';
				}

				if ($filterlist)
				{
					$filtermethod = '('.$filterlist.') ';
					$fwhere = ' WHERE '; $fand = ' AND ';
				}
			}
			else
			{
				$filtermethod = " AND (tid='n' OR tid is null)";
			}

			if (!$filtermethod)
			{
				if($this->account_id)
				{
					$fwhere .= ' (owner=' . $this->account_id;
					$fand   .= ' (owner=' . $this->account_id;
				}
			}
			else
			{
				if($this->account_id)
				{
					$fwhere .= $filtermethod . ' AND (owner=' . $this->account_id;
					$fand   .= $filtermethod . ' AND (owner=' . $this->account_id;
				}
				else
				{
					$filtermethod = substr($filtermethod,0,-2);
					$fwhere .= $filtermethod;
					$fand   .= $filtermethod;
				}
			}

			if(@is_array($this->grants))
			{
				$grants = $this->grants;
				foreach($grants as $user => $_right)
				{
					$public_user_list[] = $user;
				}
				$fwhere .= " OR (access='public' AND owner in(" . implode(',',$public_user_list) . "))) ";
				$fand   .= " OR (access='public' AND owner in(" . implode(',',$public_user_list) . "))) ";
			}
			else
			{
				$fwhere .= ') '; $fand .= ') ';
			}

			if ($DEBUG && $filtermethod)
			{
				echo '<br />DEBUG - Filtering with: #' . $filtermethod . '#';
			}

			if (!$sort) { $sort = 'ASC'; }

			if (!empty($order) && preg_match('/^[a-zA-Z_0-9, ]+$/',$order) && (empty($sort) || preg_match('/^(DESC|ASC|desc|asc)$/',$sort)))
			{
				$ordermethod = "ORDER BY $order $sort ";
			}
			else
			{
				$ordermethod = "ORDER BY n_family,n_given,email ASC";
			}

			if ($DEBUG && $ordermethod)
			{
				echo "<br />DEBUG - $ordermethod";
			}

			if($lastmod >= 0 && $fwhere)
			{
				$fwhere .= " AND last_mod > ".(int)$lastmod.' ';
			}
			elseif($lastmod >= 0)
			{
				$fwhere = " WHERE last_mod > ".(int)$lastmod.' ';
			}

			if ($DEBUG && $last_mod_filter && $fwhere)
			{
				echo "<br />DEBUG - last_mod_filter added to fwhere: $fwhere";
			}

			$filtermethod = '';

			if($cquery)
			{
				$sql = 'SELECT * FROM ' . $this->std_table . ' WHERE (';
				$sqlcount = 'SELECT COUNT(id) FROM ' . $this->std_table  . ' WHERE (';
				foreach(array(
					'fn'       => 'cn',
					'n_family' => 'sn',
					'org_name' => 'o'
				) as $f => $x)
				{
					$cquery = strtoupper($this->db->db_addslashes($cquery));
					$sql .= " UPPER($f) LIKE '$cquery%' OR ";
					$sqlcount .= " UPPER($f) LIKE '$cquery%' OR ";
				}
				$sql = substr($sql,0,-3) . ') ' . $fand . $filtermethod . $ordermethod;
				$sqlcount = substr($sqlcount,0,-3) . ') ' . $fand . $filtermethod;
				unset($f); unset($x);
			}
			elseif($query)
			{
				if(is_array($query))
				{
					$sql = "SELECT * FROM $this->std_table WHERE (";
					$sqlcount = "SELECT COUNT(id) FROM $this->std_table WHERE (";
					foreach($query as $queryKey => $queryValue)
					{
						if (!preg_match('/^[a-zA-Z0-9_]+$/',$queryKey))
						{
							continue;	// this can be something nasty
						}
						// special handling of text columns for certain db's;
						if (in_array($f,array('note','pubkey','label')))
						{
							switch($this->db->Type)
							{
								case 'mssql':
									$queryKey = "CAST($queryKey AS varchar)";	// mssql cant use UPPER on text columns
									break;
							}
						}
						$queryValue  = strtoupper($this->db->db_addslashes($queryValue));
						$sql .= " UPPER($queryKey) LIKE '$queryValue' AND ";
						$sqlcount .= " UPPER($queryKey) LIKE '$queryValue' AND ";
					}
					$sql = substr($sql,0,-5) . ') ' . $fand . $filtermethod . $ordermethod;
					$sqlcount = substr($sqlcount,0,-5) . ') ' . $fand . $filtermethod;
					unset($queryKey); unset($queryValue);
				}
				else
				{
					$query = strtoupper($this->db->db_addslashes($query));

					$sql = "SELECT * FROM $this->std_table WHERE (";
					$sqlcount = "SELECT COUNT(id) FROM $this->std_table WHERE (";
					foreach($this->stock_contact_fields as $f => $x)
					{
						// special handling of text columns for certain db's;
						if (in_array($f,array('note','pubkey','label')))
						{
							switch($this->db->Type)
							{
								case 'mssql':
									$f = "CAST($f AS varchar)";	// mssql cant use UPPER on text columns
									break;
							}
						}
						$sql .= " UPPER($f) LIKE '%$query%' OR ";
						$sqlcount .= " UPPER($f) LIKE '%$query%' OR ";
					}
					$sql = substr($sql,0,-3) . ') ' . $fand . $filtermethod . $ordermethod;
					$sqlcount = substr($sqlcount,0,-3) . ') ' . $fand . $filtermethod;
					unset($f); unset($x);
				}
			}
			else
			{
				$sql = "SELECT id,lid,tid,owner,access,cat_id,last_mod $t_fields FROM $this->std_table " . $fwhere
					. $filtermethod . ' ' . $ordermethod;
				$sqlcount = "SELECT COUNT(id) FROM $this->std_table " . $fwhere
					. $filtermethod;
			}
			if($DEBUG)
			{
				echo '<br />COUNT QUERY' . $sqlcount;
				echo '<br />FULL  QUERY' . $sql;
			}

//			$db2 = $this->db;
			copyobj($this->db,$db2);

			/* Perhaps it is more efficient to count records for this query, which is all we need here */
			$this->db->query($sqlcount,__LINE__,__FILE__);
			$this->db->next_record();
			unset($sqlcount);
			$this->total_records = $this->db->f(0);

			if($start && $limit)
			{
				if($this->total_records <= $limit)
				{
					$this->db->query($sql,__LINE__,__FILE__);
				}
				else
				{
					$this->db->limit_query($sql,$start,__LINE__,__FILE__,$limit);
				}
			}
			elseif(!$limit)
			{
				$this->db->query($sql,__LINE__,__FILE__);
			}
			else
			{
				$this->db->limit_query($sql,$start,__LINE__,__FILE__);
			}

			$i = 0;
			while($this->db->next_record())
			{
				$return_fields[$i]['id']       = $this->db->f('id');
				$return_fields[$i]['lid']      = $this->db->f('lid');
				$return_fields[$i]['tid']      = $this->db->f('tid');
				$return_fields[$i]['owner']    = $this->db->f('owner');
				$return_fields[$i]['access']   = $this->db->f('access');
				$return_fields[$i]['cat_id']   = $this->db->f('cat_id');
				$return_fields[$i]['last_mod'] = $this->db->f('last_mod');
				$return_fields[$i]['rights']   = (int)$this->grants[$this->db->f('owner')];

				if(@is_array($stock_fieldnames))
				{
					foreach($stock_fieldnames as $f_name)
					{
						$return_fields[$i][$f_name] = $this->db->f($f_name);
					}
					reset($stock_fieldnames);
				}
				$db2->query("SELECT contact_name,contact_value FROM $this->ext_table WHERE contact_id="
					. (int)$this->db->f('id') . $filterextra,__LINE__,__FILE__);
				while($db2->next_record())
				{
					if($extra_fields[$db2->f('contact_name')])
					{
						$return_fields[$i][$db2->f('contact_name')] = $db2->f('contact_value');
					}
				}
				++$i;
			}
			return $return_fields;
		}

		function add($owner,$fields,$access=NULL,$cat_id=NULL,$tid=NULL)
		{
			$owner = (int)$owner;
			$lid   = array();
			// access, cat_id and tid can be in $fields now or as extra params
			foreach(array('access','cat_id','tid') as $extra)
			{
				if (!is_null($$extra))
				{
					$fields[$extra] = $$extra;
				}
			}
			if(empty($fields['tid']))
			{
				$fields['tid'] = 'n';
			}
			if(isset($fields['lid']))
			{
				//fix by pim
				//$lid = array('lid,' => $fields['lid']."','");
				$lid = array('lid,', $fields['lid'] . "','");
			}
			list($stock_fields,$stock_fieldnames,$extra_fields) = $this->split_stock_and_extras($fields);

			//this is added here so it is never tainted
			$this->stock_contact_fields['last_mod'] = 'last_mod';
			$stock_fields['last_mod'] = $GLOBALS['phpgw']->datetime->gmtnow;

			$sql = 'INSERT INTO ' . $this->std_table . " (owner,access,cat_id,tid," . $lid[0]
				. implode(',',$this->stock_contact_fields)
				. ') VALUES (' . $owner . ",'" . $fields['access'] . "','" . $fields['cat_id']
				. "','" . $fields['tid'] . "','" . $lid[1]
				. implode("','",$this->loop_addslashes($stock_fields)) . "')";
			$this->db->query($sql,__LINE__,__FILE__);

			$id = $this->db->get_last_insert_id($this->std_table, 'id');

			if(count($extra_fields))
			{
				foreach($extra_fields as $name => $value)
				{
					$this->db->query("INSERT INTO $this->ext_table VALUES (" . (int)$id . ",'" . $owner . "','"
						. $this->db->db_addslashes($name) . "','" . $this->db->db_addslashes($value) . "')",__LINE__,__FILE__);
				}
			}
			return ($id ? $id : False);
		}

		function field_exists($id,$field_name)
		{
			$this->db->query("SELECT COUNT(*) FROM $this->ext_table WHERE contact_id=" . (int)$id . " AND contact_name='"
				. $this->db->db_addslashes($field_name) . "'",__LINE__,__FILE__);
			$this->db->next_record();
			return $this->db->f(0);
		}

		function add_single_extra_field($id,$owner,$field_name,$field_value)
		{
			$this->db->query("INSERT INTO $this->ext_table VALUES (" . (int)$id . ",'".(int)$owner."','" . $this->db->db_addslashes($field_name)
				. "','" . $this->db->db_addslashes($field_value) . "')",__LINE__,__FILE__);
		}

		function delete_single_extra_field($id,$field_name)
		{
			$this->db->query("DELETE FROM $this->ext_table WHERE contact_id=" . (int)$id . " AND contact_name='"
				. $this->db->db_addslashes($field_name) . "'",__LINE__,__FILE__);
		}

		function update($id,$owner,$fields,$access=NULL,$cat_id=NULL,$tid=NULL)
		{
			$owner = (int)$owner;
			$id    = (int)$id;
			/* First make sure that id number exists */
			$this->db->query("SELECT COUNT(*) FROM $this->std_table WHERE id=$id",__LINE__,__FILE__);
			$this->db->next_record();
			if (!$this->db->f(0))
			{
				return False;
			}

			list($stock_fields,,$extra_fields) = $this->split_stock_and_extras($fields);
			// access, cat_id and tid can be in $fields now or as extra params
			foreach(array('access','cat_id','tid') as $extra)
			{
				if (!is_null($$extra))
				{
					$fields[$extra] = $$extra;
				}
				if (isset($fields[$extra]))
				{
					$stock_fields[$extra] = $fields[$extra];
				}
			}

			if (count($stock_fields))
			{
				foreach($stock_fields as $name => $value)
				{
					$ta[] = $name . "='" . $this->db->db_addslashes($value) . "'";
				}
				$ta[] = 'last_mod=' . $GLOBALS['phpgw']->datetime->gmtnow;
				$fields_s = implode(',',$ta);
				if ($field_s == ',')
				{
					unset($field_s);
				}
				$this->db->query($sql="UPDATE $this->std_table SET $fields_s WHERE "
					. "id=$id",__LINE__,__FILE__);
			}
			if (is_array($extra_fields))
			{
				foreach($extra_fields as $x_name => $x_value)
				{
					if ($this->field_exists($id,$x_name))
					{
						if (!$x_value)
						{
							$this->delete_single_extra_field($id,$x_name);
						}
						else
						{
							$this->db->query("UPDATE $this->ext_table SET contact_value='" . $this->db->db_addslashes($x_value)
								. "',contact_owner=$owner WHERE contact_name='" . $this->db->db_addslashes($x_name)
								. "' AND contact_id=$id",__LINE__,__FILE__);
						}
					}
					elseif($x_value)	// dont write emtpy extra-fields
					{
						$this->add_single_extra_field($id,$owner,$x_name,$x_value);
					}
				}
			}
			return True;
		}

		/* Used by admin to change ownership on account delete */
		function change_owner($old_owner,$new_owner)
		{
			$old_owner = (int) $old_owner;
			$new_owner = (int) $new_owner;
			if (!$new_owner || !$old_owner)
			{
				return False;
			}
			$this->db->query("UPDATE $this->std_table SET owner='$new_owner' WHERE owner=$old_owner",__LINE__,__FILE__);
			$this->db->query("UPDATE $this->ext_table SET contact_owner='$new_owner' WHERE contact_owner=$old_owner",__LINE__,__FILE__);
		}

		/* This is where the real work of delete() is done, shared class file contains calling function */
		function delete_($id)
		{
			$this->db->query("DELETE FROM $this->std_table WHERE id=" . (int)$id,__LINE__,__FILE__);
			$this->db->query("DELETE FROM $this->ext_table WHERE contact_id=" . (int)$id,__LINE__,__FILE__);
		}

		/* This is for the admin script deleteaccount.php */
		function delete_all($owner=0)
		{
			$owner = (int) $owner;
			if ($owner)
			{
				$this->db->query("DELETE FROM $this->std_table WHERE owner=$owner",__LINE__,__FILE__);
				$this->db->query("DELETE FROM $this->ext_table WHERE contact_owner=$owner",__LINE__,__FILE__);
			}
		}
	}
?>
