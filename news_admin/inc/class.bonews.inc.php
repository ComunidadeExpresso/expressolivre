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
	* This program was sponsered by Golden Glair productions                   *
	* http://www.goldenglair.com                                               *
	\**************************************************************************/


	class bonews
	{
		var $sonews;
		var $acl;
		var $start = 0;
		var $query = '';
		var $sort  = '';
		var $cat_id;
		var $total = 0;
		var $debug;
		var $use_session = False;
		var $unixtimestampmax;
		var $dateformat;
		var $cats = array();

		function bonews($session=False)
		{
			$this->acl = CreateObject('news_admin.boacl');
			$this->sonews = CreateObject('news_admin.sonews');
			$this->accounts = $GLOBALS['phpgw']->accounts->get_list('groups');
			$this->debug = False;
			if($session)
			{
				$this->read_sessiondata();
				$this->use_session = True;
				foreach(array('start','query','sort','order','cat_id') as $var)
				{
					$this->$var = get_var($var, array('POST', 'GET'), '');
				}

				$this->cat_id = $this->cat_id ? $this->cat_id : 'all';
				$this->save_sessiondata();
			}
			$this->catbo = createobject('phpgwapi.categories','','news_admin');
			$this->cats = $this->catbo->return_array('all',0,False,'','','cat_name',True);
			settype($this->cats,'array');
			//change this around 19 Jan 2038 03:14:07 GMT
			$this->unixtimestampmax = 2147483647;
			$this->dateformat = $GLOBALS['phpgw_info']['user']['preferences']['common']['dateformat'];
		}

		function save_sessiondata()
		{
			$data = array(
				'start' => $this->start,
				'query' => $this->query,
				'sort'  => $this->sort,
				'order' => $this->order,
				'cat_id' => $this->cat_id,
			);
			if($this->debug) { echo '<br>Save:'; _debug_array($data); }
			$GLOBALS['phpgw']->session->appsession('session_data','news_admin',$data);
		}

		function read_sessiondata()
		{
			$data = $GLOBALS['phpgw']->session->appsession('session_data','news_admin');
			if($this->debug) { echo '<br>Read:'; _debug_array($data); }

			$this->start  = $data['start'];
			$this->query  = $data['query'];
			$this->sort   = $data['sort'];
			$this->order  = $data['order'];
			$this->cat_id = $data['cat_id'];
		}

		function get_newslist($cat_id, $start=0, $order='',$sort='',$limit=0,$activeonly=False)
		{
			$charset = $GLOBALS['phpgw']->translation->charset();
			
			$cats = False;
			if ($cat_id == 'all')
			{
				foreach($this->cats as $cat)
				{
				   if ($this->acl->is_readable($cat['id']))
				   {
						$cats[] = $cat['id'];
				   }
			   }
			}
			elseif($this->acl->is_readable($cat_id))
			{
				$cats = $cat_id;
			}
			
			if($cats)
			{
				$news = $this->sonews->get_newslist($cats, $start,$order,$sort,$limit,$activeonly,$this->total);
				foreach($news as $id => $item)
				{
					$news[$id]['content'] = ($item['is_html'] ? 
									$item['content'] : 
									nl2br(@htmlspecialchars($item['content'],ENT_COMPAT,$charset)
								));
				}
				return $news;
			}
			else
			{
				return array();
			}
		}

		function get_all_public_news($limit = 5)
		{
			$charset = $GLOBALS['phpgw']->translation->charset();
			
			$news = $this->sonews->get_all_public_news($limit);
			foreach($news as $id => $item)
			{
				$news[$id]['content'] = ($item['is_html'] ? 
								$item['content'] : 
								nl2br(@htmlspecialchars($item['content'],ENT_COMPAT,$charset)
							));
			}
			return $news;
		}

		function delete($news_id)
		{
			$this->sonews->delete($news_id);
		}

		function add($news)
		{
			$enc = preg_replace('!\015\012|\015|\012!','',$news['content']);
			$news['content'] = $enc;
			return $this->acl->is_writeable($news['category']) ?
				$this->sonews->add($news) :
				false;
		}

		function edit($news)
		{
			$enc = preg_replace('!\015\012|\015|\012!','',$news['content']);
                        $news['content'] = $enc;
			$oldnews = $this->sonews->get_news($news['id']);
			return ($this->acl->is_writeable($oldnews['category']) && 
					$this->acl->is_writeable($news['category'])) ?
				$this->sonews->edit($news) :
				False;
		}

		function get_visibility(&$news)
		{
			$now = time();

			if ($news['end'] < $now)
			{
				return lang('Never');
			}
			else
			{
				if ($news['begin'] < $now)
				{
					if ($news['end'] == $this->unixtimestampmax)
					{
						return lang('Always');
					}
					else
					{
						return lang('until') . date($this->dateformat,$news['end']);
					}
				}
				else
				{
					if ($news['end'] == $this->unixtimestampmax)
					{
						return lang('from') . date($this->dateformat,$news['begin']);

					}
					else
					{
						return lang('from') . ' ' . date($this->dateformat,$news['begin']) . ' ' . 
							lang('until') . ' ' . date($this->dateformat,$news['end']);
					}
				}
			}
		}

		//return the selectboxes with calculated defaults, and change begin and end by sideaffect
		function get_options(&$news)
		{
			$now = time();
			//always is default
			if (!isset($news['begin']))
			{
				//these are only displayed values not necessarily the ones that will get stored
				$news['begin'] = $now;
				$news['end'] = $now;
				$from = 1;
				$until = 1;
			}
			//if enddate is in the past set option to never
			elseif ($news['end'] < $now)
			{
				$news['begin'] = $now;
				$news['end'] = $now;
				$from = 0;
				$until = 1;
			}
			else
			{
				if ($news['begin'] < $now)
				{
					$news['begin'] = $now;
					if ($news['end'] == $this->unixtimestampmax)
					{
						$news['end'] = $now;
						$from = 1;
						$until = 1;
					}
					else
					{
						$from = 0.5;
						$until = 0.5;
					}
				}
				else
				{
					if ($news['end'] == $this->unixtimestampmax)
					{
						$news['end'] = $now;
						$from = 0.5;
						$until = 1;
					}
					else
					{
						$from = 0.5;
						$until = 0.5;
					}
				}
			}
			$options['from'] = '<option value="1"' . (($from == 1) ? ' selected="selected"' : '') . '>' . lang('Always') . '</option>';
			$options['from'] .= '<option value="0"' . (($from == 0) ? ' selected="selected"' : '') . '>' . lang('Never') . '</option>';
			$options['from'] .= '<option value="0.5"' . (($from == 0.5) ? ' selected="selected"' : '') . '>' . lang('From') . '</option>';
			$options['until'] = '<option value="1"' . (($until == 1) ? ' selected="selected"' : '') . '>' . lang('Always') . '</option>';
			$options['until'] .= '<option value="0.5"' . (($until == 0.5) ? ' selected="selected"' : '') . '>' . lang('until') . '</option>';
			return $options;
		}

		//set the begin and end dates 
		function set_dates($from,$until,&$news)
		{
			switch($from)
			{
				//always
				case 1:
					$news['begin'] = $news['date'];
					$news['end'] = $this->unixtimestampmax;
					break;
				//never
				case 0:
					$news['begin'] = 0;
					$news['end'] = 0;
					break;
				default:
					$news['begin'] = mktime(0,0,0,(int)$news['begin_m'], (int)$news['begin_d'], (int)$news['begin_y']);
					switch($until)
					{
						case 1:
							$news['end'] = $this->unixtimestampmax;
							break;
						default:
							$news['end'] = mktime(0,0,0,(int)$news['end_m'], (int)$news['end_d'], (int)$news['end_y']);
					}
			}
		}

// 		function format_fields($fields)
// 		{
// 			$cat = createobject('phpgwapi.categories','news_admin');

// 			$item = array(
// 				'id'          => $fields['id'],
// 				'date'        => $GLOBALS['phpgw']->common->show_date($fields['date']),
// 				'subject'     => $GLOBALS['phpgw']->strip_html($fields['subject']),
// 				'submittedby' => $fields['submittedby'],
// 				'content'     => $fields['content'],
// 				'status'      => lang($fields['status']),
// 				'cat'         => $cat->id2name($fields['cat'])
// 			);
// 			return $item;
// 		}

		function get_news($news_id)
		{
			$news = $this->sonews->get_news($news_id);
			
			if ($this->acl->is_readable($news['category']))
			{
				$this->total = 1;
				$news['content'] = ($news['is_html'] ? 
							$news['content']: 
							nl2br(htmlspecialchars($news['content'],ENT_COMPAT,$GLOBALS['phpgw']->translation->charset())
						));
				return $news;
			}
			else
			{
				 return False;
			}
		}
	}
?>
