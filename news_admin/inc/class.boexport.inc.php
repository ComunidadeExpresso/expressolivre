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


	class boexport
	{
		var $start = 0;
		var $query = '';
		var $sort  = '';
		var $total = 0;
		var $catbo;
		var $cats;
		var $so;

		var $debug;
		var $use_session = False;

		function boexport($session=False)
		{
			$this->so = CreateObject('news_admin.soexport');
			$this->debug = False;
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
				$this->cats = $this->catbo->return_array('all',$this->start,True,$this->query,$this->sort,'cat_name',True);
			}
		}

		function save_sessiondata()
		{
				
			$data = array(
				'start' => $this->start,
				'query' => $this->query,
				'sort'  => $this->sort,
				'order' => $this->order,
			);
			if($this->debug) { echo '<br>Save:'; _debug_array($data); }
			$GLOBALS['phpgw']->session->appsession('session_data','news_admin_export',$data);
		}

		function read_sessiondata()
		{
			$data = $GLOBALS['phpgw']->session->appsession('session_data','news_admin_export');
			if($this->debug) { echo '<br>Read:'; _debug_array($data); }

			$this->start  = $data['start'];
			$this->query  = $data['query'];
			$this->sort   = $data['sort'];
			$this->order  = $data['order'];
		}

		function readconfig($cat_id)
		{
			return $this->so->readconfig($cat_id);
		}

		function saveconfig($cat_id,$config)
		{
			$this->so->saveconfig($cat_id,$config);
		}
	}
