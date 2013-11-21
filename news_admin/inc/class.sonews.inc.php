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


	class sonews
	{
		var $db;

		function sonews()
		{
			copyobj($GLOBALS['phpgw']->db,$this->db);
		}

		function get_newslist($cat_id, $start, $order,$sort,$limit=0,$activeonly,&$total)
		{
			if(!empty($order))
			{
				$ordermethod = ' ORDER BY ' . $this->db->db_addslashes($order) . ' ' . $this->db->db_addslashes($sort);
			}
			else
			{
				$ordermethod = ' ORDER BY news_date DESC';
			}

			if(is_array($cat_id))
			{
				$filter = 'IN (' . implode(',',$cat_id) . ')';
			}
			else
			{
				$filter = '=' . (int)$cat_id;
			}

			$sql = 'SELECT * FROM phpgw_news WHERE news_cat ' . $filter;
			if($activeonly)
			{
				$now = time();
				$sql .= " AND news_begin<=$now AND news_end>=$now";
			}
			$sql .= $ordermethod;

			$this->db->query($sql,__LINE__,__FILE__);
			$total = $this->db->num_rows();
			$this->db->limit_query($sql,$start,__LINE__,__FILE__,$limit);

			$news = array();

			while($this->db->next_record())
			{
				$news[$this->db->f('news_id')] = array(
					'subject' => @htmlspecialchars($this->db->f('news_subject', True),ENT_COMPAT,$GLOBALS['phpgw']->translation->charset()),
					'submittedby' => $this->db->f('news_submittedby'),
					'date'    => $this->db->f('news_date'),
					'id'      => $this->db->f('news_id'),
					'begin'   => $this->db->f('news_begin'),
					'end'     => $this->db->f('news_end'),
					'teaser'  => @htmlspecialchars($this->db->f('news_teaser', True),ENT_COMPAT,$GLOBALS['phpgw']->translation->charset()),
					'content' => $this->db->f('news_content',True),
					'is_html' => ($this->db->f('is_html') ? True : False),
				);
			}
			return $news;
		}

		function get_all_public_news($limit=5)
		{
			$now = time();
			$this->db->limit_query("SELECT * FROM phpgw_news WHERE news_begin<=$now AND news_end>=$now ORDER BY news_date DESC",0,__LINE__,__FILE__,$limit);

			$news = array();

			while ($this->db->next_record())
			{
				$news[$this->db->f('news_id')] = array(
					'subject' => $this->db->f('news_subject', True),
					'submittedby' => $this->db->f('news_submittedby'),
					'date'    => $this->db->f('news_date'),
					'id'      => $this->db->f('news_id'),
					'teaser'  => $this->db->f('news_teaser', True),
					'content' => $this->db->f('news_content', True),
					'is_html' => ($this->db->f('is_html') ? True : False),
				);
			}
			return $news;
		}

		function add($news)
		{
			$add_array = array(
				'news_date'			=> (int)$news['date'],
				'news_submittedby'	=> $GLOBALS['phpgw_info']['user']['account_id'],
				'news_content'		=> $news['content'],
				'news_subject'		=> $news['subject'],
				'news_begin'		=> (int)$news['begin'],
				'news_end'			=> (int)$news['end'],
				'news_teaser'		=> $news['teaser'],
				'news_cat'			=> (int)$news['category'],
				'is_html'			=> (int)!!$news['is_html'],
			);
			$this->db->insert('phpgw_news', $add_array, '', __LINE__, __FILE__);

			return $this->db->get_last_insert_id('phpgw_news', 'news_id');
		}

		function edit($news)
		{
			$update_array = array(
				'news_content'	=> $news['content'],
				'news_subject'	=> $news['subject'],
				'news_teaser'	=> $news['teaser'],
				'news_begin'	=> $news['begin'],
				'news_end'		=> $news['end'],
				'news_cat'		=> $news['category'],
				'is_html'		=> $news['is_html'] ? 1 : 0,
			);
			$this->db->update('phpgw_news', $update_array, array('news_id' => (int)$news['id']), __LINE__, __FILE__);
		}

		function delete($news_id)
		{
			$this->db->query('DELETE FROM phpgw_news WHERE news_id=' . (int)$news_id,__LINE__,__FILE__);
		}

		function get_news($news_id)
		{
			$this->db->query('SELECT * FROM phpgw_news WHERE news_id=' . (int)$news_id,__LINE__,__FILE__);
			$this->db->next_record();

			$item = array(
				'id'       => $this->db->f('news_id'),
				'date'     => $this->db->f('news_date'),
				'subject'  => $this->db->f('news_subject', True),
				'submittedby' => $this->db->f('news_submittedby'),
				'teaser'   => $this->db->f('news_teaser', True),
				'content'  => $this->db->f('news_content', True),
				'begin'    => $this->db->f('news_begin'),
				'end'      => $this->db->f('news_end'),
				'category' => $this->db->f('news_cat'),
				'is_html'  => ($this->db->f('is_html') ? True : False),
			);
			return $item;
		}

// 		function getlist($order,$sort,$cat_id)
// 		{
// 			if ($order)
// 			{
// 				$ordermethod = ' ORDER BY ' . $this->db->db_addslashes($order) . ' ' . $this->db->db_addslashes($sort);
// 			}
// 			else
// 			{
// 				$ordermethod = ' ORDER BY news_date DESC';
// 			}

// 			$this->db->query('SELECT * FROM phpgw_news WHERE news_cat=' . (int)$cat_id . $ordermethod,__LINE__,__FILE__);
// 			while ($this->db->next_record())
// 			{
// 				$items[] = array(
// 					'id'          => $this->db->f('news_id'),
// 					'date'        => $this->db->f('news_date'),
// 					'subject'     => $this->db->f('news_subject'),
// 					'submittedby' => $this->db->f('news_submittedby'),
// 					'content'     => $this->db->f('news_content'),
// 					'status'      => $this->db->f('news_status'),
// 					'cat'         => $this->db->f('news_cat')
// 				);
// 			}
// 			return $items;
// 		}
	}
