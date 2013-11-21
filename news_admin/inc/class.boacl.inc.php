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


	class boacl
	{
		var $acl;
		var $start = 0;
		var $query = '';
		var $sort  = '';
		var $total = 0;
		var $accounts;
		var $cats;

		var $debug;
		var $use_session = False;

		function boacl($session=False)
		{
			$this->so = CreateObject('news_admin.soacl');
			$this->accounts = $GLOBALS['phpgw']->accounts->get_list('groups');
			$this->debug = False;
			//all this is only needed when called from uiacl. not from ui,
			if($session)
			{
				$this->read_sessiondata();
				$this->use_session = True;
				foreach(array('start','query','sort','order') as $var)
				{
					if (isset($_POST[$var]))
					{
						$this->$var = $_POST[$var];
					}
					elseif (isset($_GET[$var]))
					{
						$this->$var = $_GET[$var];
					}
				}
				$this->save_sessiondata();
				$this->catbo = createobject('phpgwapi.categories');
//				$main_cat = array(array('id' => 0, 'name' => lang('Global news')));
//				$this->cats = array_merge($main_cat,$this->catbo->return_array('all',$this->start,True,$this->query,$this->sort,'cat_name',True));
				$this->cats = $this->catbo->return_array('all',$this->start,True,$this->query,$this->sort,'cat_name',True);
			}
			$this->permissions = $this->get_permissions(True);
		}

		function save_sessiondata()
		{
			$data = array(
				'start' => $this->start,
				'query' => $this->query,
				'sort'  => $this->sort,
				'order' => $this->order,
				'limit' => $this->limit,
			);
			if($this->debug) { echo '<br>Read:'; _debug_array($data); }
			$GLOBALS['phpgw']->session->appsession('session_data','news_admin_acl',$data);
		}

		function read_sessiondata()
		{
			$data = $GLOBALS['phpgw']->session->appsession('session_data','news_admin_acl');
			if($this->debug) { echo '<br>Read:'; _debug_array($data); }

			$this->start  = $data['start'];
			$this->query  = $data['query'];
			$this->sort   = $data['sort'];
			$this->order  = $data['order'];
			$this->limit = $data['limit'];
		}

		function get_rights($cat_id)
		{
			return $this->so->get_rights('L'.$cat_id);
		}

		function is_permitted($cat_id,$right)
		{
			return $this->permissions['L'.$cat_id] & $right;
		}

		function is_readable($cat_id)
		{
			return $this->is_permitted($cat_id,PHPGW_ACL_READ);
		}

		function is_writeable($cat_id)
		{
			return $this->is_permitted($cat_id,PHPGW_ACL_ADD);
		}

		function set_rights($cat_id,$read,$write)
		{
			$readcat = $read ? $read : array();
			$writecat = $write ? $write : array();

			$this->so->remove_location('L' . $cat_id);
			reset($this->accounts);
			while (list($null,$account) = each($this->accounts))
			{
				$account_id = $account['account_id'];
				//write implies read
				$rights = in_array($account_id,$writecat) ?
					(PHPGW_ACL_READ | PHPGW_ACL_ADD) :
					(in_array($account_id,$readcat) ? PHPGW_ACL_READ : False);
				if ($rights)
				{
					$GLOBALS['phpgw']->acl->add_repository('news_admin','L'.$cat_id,$account_id,$rights);
				}
			}
		}

		//access permissions for current user
		function get_permissions($inc_groups = False)
		{
			return $this->so->get_permissions($GLOBALS['phpgw_info']['user']['account_id'], $inc_groups);
		}
	}
