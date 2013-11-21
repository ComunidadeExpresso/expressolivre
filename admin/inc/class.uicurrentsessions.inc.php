<?php
	/**************************************************************************\
	* eGroupWare - Administration                                              *
	* http://www.egroupware.org                                                *
	*  This file written by Joseph Engo <jengo@phpgroupware.org>               *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/


	class uicurrentsessions
	{
		var $template;
		var $bo;
		var $public_functions = array(
			'list_sessions' => True,
			'kill'          => True
		);

		function uicurrentsessions()
		{
			if ($GLOBALS['phpgw']->acl->check('current_sessions_access',1,'admin'))
			{
				$GLOBALS['phpgw']->redirect_link('/index.php');
			}
			$this->template   = createobject('phpgwapi.Template',PHPGW_APP_TPL);
			$this->bo         = createobject('admin.bocurrentsessions');
			$this->nextmatchs = createobject('phpgwapi.nextmatchs');
		}

		function header()
		{
			if(!@is_object($GLOBALS['phpgw']->js))
			{
				$GLOBALS['phpgw']->js = CreateObject('phpgwapi.javascript');
			}
			$GLOBALS['phpgw']->js->validate_file('jscode','openwindow','admin');
			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();
		}

		function store_location($info)
		{
			$GLOBALS['phpgw']->session->appsession('currentsessions_session_data','admin',$info);
		}

		function list_sessions()
		{
			$info = $GLOBALS['phpgw']->session->appsession('currentsessions_session_data','admin');
			if (! is_array($info))
			{
				$info = array(
					'start' => 0,
					'sort'  => 'asc',
					'order' => 'session_dla'
				);
				$this->store_location($info);
			}

			if ($GLOBALS['start'] || $GLOBALS['sort'] || $GLOBALS['order'])
			{
				if ($GLOBALS['start'] == 0 || $GLOBALS['start'] && $GLOBALS['start'] != $info['start'])
				{
					$info['start'] = $GLOBALS['start'];
				}

				if ($GLOBALS['sort'] && $GLOBALS['sort'] != $info['sort'])
				{
					$info['sort'] = $GLOBALS['sort'];
				}

				if ($GLOBALS['order'] && $GLOBALS['order'] != $info['order'])
				{
					$info['order'] = $GLOBALS['order'];
				}

				$this->store_location($info);
			}

			$GLOBALS['phpgw_info']['flags']['app_header'] = lang('Admin').' - '.lang('List of current users');
			$this->header();

			$this->template->set_file('current','currentusers.tpl');
			$this->template->set_block('current','list','list');
			$this->template->set_block('current','row','row');

			$can_view_action = !$GLOBALS['phpgw']->acl->check('current_sessions_access',2,'admin');
			$can_view_ip     = !$GLOBALS['phpgw']->acl->check('current_sessions_access',4,'admin');
			$can_kill        = !$GLOBALS['phpgw']->acl->check('current_sessions_access',8,'admin');

			$total = $this->bo->total();

			$this->template->set_var('bg_color',$GLOBALS['phpgw_info']['theme']['bg_color']);
			$this->template->set_var('left_next_matchs',$this->nextmatchs->left('/admin/currentusers.php',$info['start'],$total));
			$this->template->set_var('right_next_matchs',$this->nextmatchs->right('/admin/currentusers.php',$info['start'],$total));
			$this->template->set_var('th_bg',$GLOBALS['phpgw_info']['theme']['th_bg']);

			$this->template->set_var('sort_loginid',$this->nextmatchs->show_sort_order($info['sort'],'session_lid',$info['order'],
				'/admin/currentusers.php',lang('LoginID')));
			$this->template->set_var('sort_ip',$this->nextmatchs->show_sort_order($info['sort'],'session_ip',$info['order'],
				'/admin/currentusers.php',lang('IP')));
			$this->template->set_var('sort_login_time',$this->nextmatchs->show_sort_order($info['sort'],'session_logintime',$info['order'],
				'/admin/currentusers.php',lang('Login Time')));
			$this->template->set_var('sort_action',$this->nextmatchs->show_sort_order($info['sort'],'session_action',$info['order'],
				'/admin/currentusers.php',lang('Action')));
			$this->template->set_var('sort_idle',$this->nextmatchs->show_sort_order($info['sort'],'session_dla',$info['order'],
				'/admin/currentusers.php',lang('idle')));
			$this->template->set_var('lang_kill',lang('Kill'));

			$values = $this->bo->list_sessions($info['start'],$info['order'],$info['sort']);

			while (list(,$value) = @each($values))
			{
				$this->nextmatchs->template_alternate_row_color($this->template);

				$this->template->set_var('row_loginid',$value['session_lid']);

				$this->template->set_var('row_ip',$can_view_ip?$value['session_ip']:'&nbsp;');

				$this->template->set_var('row_logintime',$value['session_logintime']);
				$this->template->set_var('row_idle',$value['session_idle']);

				if ($value['session_action'] && $can_view_action)
				{
					$this->template->set_var('row_action',$GLOBALS['phpgw']->strip_html($value['session_action']));
				}
				else
				{
					$this->template->set_var('row_action','&nbsp;');
				}

				if ($value['session_id'] != $GLOBALS['phpgw_info']['user']['sessionid'] && $can_kill)
				{
					$this->template->set_var('row_kill','<a href="' . $GLOBALS['phpgw']->link('/index.php','menuaction=admin.uicurrentsessions.kill&ksession='
						. $value['session_id'] . '&kill=true') . '">' . lang('Kill').'</a>');
				}
				else
				{
					$this->template->set_var('row_kill','&nbsp;');
				}

				$this->template->parse('rows','row',True);
			}

			$this->template->pfp('out','list');
		}

		function kill()
		{
			if ($GLOBALS['phpgw']->acl->check('current_sessions_access',8,'admin'))
			{
				$GLOBALS['phpgw']->redirect_link('/index.php');
			}
			$GLOBALS['phpgw_info']['flags']['app_header'] = lang('Admin').' - '.lang('Kill session');
			$this->header();
			$this->template->set_file('form','kill_session.tpl');

			$this->template->set_var('lang_message',lang('Are you sure you want to kill this session ?'));
			$this->template->set_var('link_no','<a href="' . $GLOBALS['phpgw']->link('/index.php','menuaction=admin.uicurrentsessions.list_sessions') . '">' . lang('No') . '</a>');
			$this->template->set_var('link_yes','<a href="' . $GLOBALS['phpgw']->link('/index.php','menuaction=admin.bocurrentsessions.kill&ksession=' . $_GET['ksession']) . '">' . lang('Yes') . '</a>');

			$this->template->pfp('out','form');
		}
	}
