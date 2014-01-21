<?php
  /**************************************************************************\
  * eGroupWare API - Session management                                      *
  * This file written by Dan Kuykendall <seek3r@phpgroupware.org>            *
  * and Joseph Engo <jengo@phpgroupware.org>                                 *
  * and Ralf Becker <ralfbecker@outdoor-training.de>                         *
  * Copyright (C) 2000, 2001 Dan Kuykendall                                  *
  * -------------------------------------------------------------------------*
  * This library is part of the phpGroupWare API                             *
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


	class sessions extends sessions_
	{
		function sessions()
		{
			$this->sessions_();
			//controls the time out for php4 sessions - skwashd 18-May-2003
			ini_set('session.gc_maxlifetime', $GLOBALS['phpgw_info']['server']['sessions_timeout']);
			session_name('sessionid');
		}

		function read_session()
		{
			if (!$this->sessionid)
			{
				return False;
			}
			session_id($this->sessionid);
			session_start();
			return $GLOBALS['phpgw_session'] = $_SESSION['phpgw_session'];
		}

		function set_cookie_params($domain)
		{
			session_set_cookie_params(0,'/',$domain);
		}

		function new_session_id()
		{
			session_start();

			return session_id();
		}

		function register_session($login,$user_ip,$now,$session_flags)
		{
			// session_start() is now called in new_session_id() !!!

			$GLOBALS['phpgw_session']['session_id'] = $this->sessionid;
			$GLOBALS['phpgw_session']['session_lid'] = $login;
			$GLOBALS['phpgw_session']['session_ip'] = $user_ip;
			$GLOBALS['phpgw_session']['session_logintime'] = $now;
			$GLOBALS['phpgw_session']['session_dla'] = $now;
			$GLOBALS['phpgw_session']['session_action'] = $_SERVER['PHP_SELF'];
			$GLOBALS['phpgw_session']['session_flags'] = $session_flags;
			// we need the install-id to differ between serveral installs shareing one tmp-dir
			$GLOBALS['phpgw_session']['session_install_id'] = $GLOBALS['phpgw_info']['server']['install_id'];

			$_SESSION['phpgw_session'] = $GLOBALS['phpgw_session'];
		}

		// This will update the DateLastActive column, so the login does not expire
		function update_dla()
		{
			if (@isset($_GET['menuaction']))
			{
				$action = $_GET['menuaction'];
			}
			else
			{
				$action = $_SERVER['PHP_SELF'];
			}

			// This way XML-RPC users aren't always listed as
			// xmlrpc.php
			if ($this->xmlrpc_method_called)
			{
				$action = $this->xmlrpc_method_called;
			}

			$GLOBALS['phpgw_session']['session_dla'] = time();
			$GLOBALS['phpgw_session']['session_action'] = $action;

			$_SESSION['phpgw_session'] = $GLOBALS['phpgw_session'];

			return True;
		}

		function destroy($sessionid, $kp3)
		{
			if (!$sessionid && $kp3)
			{
				return False;
			}

			$this->log_access($this->sessionid);	// log logout-time

			// Only do the following, if where working with the current user
			if ($sessionid == $GLOBALS['phpgw_info']['user']['sessionid'])
			{
				session_unset();
				//echo "<p>sessions_php4::destroy: session_destroy() returned ".(session_destroy() ? 'True' : 'False')."</p>\n";
				@session_destroy();
				if ($GLOBALS['phpgw_info']['server']['usecookies'])
				{
					$this->phpgw_setcookie(session_name());
				}
			}
			else
			{
				if(@opendir($path = ini_get('session.save_path'))){ 
					$session_file = $path."/sess_".$sessionid; 
					if (file_exists($session_file)) 
						@unlink($session_file); 
				}
			}

			return True;
		}

		/*************************************************************************\
		* Functions for appsession data and session cache                         *
		\*************************************************************************/
		function delete_cache($accountid='')
		{
			$account_id = get_account_id($accountid,$this->account_id);

			$GLOBALS['phpgw_session']['phpgw_app_sessions']['phpgwapi']['phpgw_info_cache'] = '';

			$_SESSION['phpgw_session'] = $GLOBALS['phpgw_session'];
		}

		function appsession($location = 'default', $appname = '', $data = '##NOTHING##')
		{
			if (! $appname)
			{
				$appname = $GLOBALS['phpgw_info']['flags']['currentapp'];
			}

			/* This allows the user to put '' as the value. */
			if ($data == '##NOTHING##')
			{
				return
					isset($GLOBALS['phpgw_session']['phpgw_app_sessions'][$appname][$location]['content'])?
						$GLOBALS['phpgw']->crypto->decrypt( $GLOBALS['phpgw_session']['phpgw_app_sessions'][$appname][$location]['content'] ) :
						false;
			}
			
			$GLOBALS['phpgw_session']['phpgw_app_sessions'][$appname][$location]['content'] = $GLOBALS['phpgw']->crypto->encrypt( $data );
			$_SESSION['phpgw_session'] = $GLOBALS['phpgw_session'];
			return $data;
		}

		function session_sort($a,$b)
		{
			$sign = strcasecmp($GLOBALS['phpgw']->session->sort_order,'ASC') ? 1 : -1;

			return strcasecmp(
				$a[$GLOBALS['phpgw']->session->sort_by],
				$b[$GLOBALS['phpgw']->session->sort_by]
			) * $sign;
		}

		/*!
		@function list_sessions
		@abstract get list of normal / non-anonymous sessions
		@note The data form the session-files get cached in the app_session phpgwapi/php4_session_cache
		@author ralfbecker
		*/
		function list_sessions($start,$order,$sort,$all_no_sort = False)
		{

			$values = array();
			$maxmatchs = $GLOBALS['phpgw_info']['user']['preferences']['common']['maxmatchs'];
			$dir = @opendir($path = ini_get('session.save_path'));
			if (!$dir)	// eg. openbasedir restrictions
			{
				return $values;
			}
			while ($file = readdir($dir))
			{
				if (substr($file,0,5) != 'sess_' || !is_readable($path. '/' . $file))
				{
					continue;
				}
				$session = ''; 
				if (($fd = fopen ($path . '/' . $file,'r'))) 
				{ 
					$session = ($size = filesize ($path . '/' . $file)) ? @fread ($fd, $size) : 0; 
					fclose ($fd); 
				} 
				$session = unserialize(substr($session,(strpos($session, 'phpgw_session|') + 14)));
                if(is_array($session) && isset($session['session_id']))
				    $values[$session['session_id']] = $session;
			}
			closedir($dir);

			if(!$all_no_sort)
			{
				$GLOBALS['phpgw']->session->sort_by = $sort;
				$GLOBALS['phpgw']->session->sort_order = $order;

				uasort($values,array('sessions','session_sort'));

				$i = 0;
				$start = (int)$start;
				foreach($values as $id => $data)
				{
					if($i < $start || $i > $start+$maxmatchs)
					{
						unset($values[$id]);
					}
					++$i;
				}
				reset($values);
			}

			return $values;
		}

		/*!
		@function total
		@abstract get number of normal / non-anonymous sessions
		@author ralfbecker
		*/
		function total()
		{
			return count($this->list_sessions(0,'','',True));
		}
	}
