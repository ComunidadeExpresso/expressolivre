<?php
	/**************************************************************************\
	* eGroupWare API - Applications manager functions                          *
	* This file written by Mark Peters <skeeter@phpgroupware.org>              *
	* Copyright (C) 2001 Mark Peters                                           *
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


	/*!
	@class applications
	@abstract functions for managing and installing apps
	@discussion Author: skeeter
	*/
	class applications
	{
		var $account_id;
		var $data = Array();
		var $db;
		var $public_functions = array(
			'list_methods' => True,
			'read'         => True
		);
		var $xmlrpc_methods = array();

		/**************************************************************************\
		* Standard constructor for setting $this->account_id                       *
		\**************************************************************************/

		/*!
		@function applications
		@abstract standard constructor for setting $this->account_id
		@param $account_id account id
		*/
		function applications($account_id = '')
		{
			$this->db = $GLOBALS['phpgw']->db;
			$this->account_id = get_account_id($account_id);

			$this->xmlrpc_methods[] = array(
				'name'        => 'read',
				'description' => 'Return a list of applications the current user has access to'
			);
		}

		function NOT_list_methods($_type='xmlrpc')
		{
			/*
			  This handles introspection or discovery by the logged in client,
			  in which case the input might be an array.  The server always calls
			  this function to fill the server dispatch map using a string.
			*/
			if (is_array($_type))
			{
				$_type = $_type['type'] ? $_type['type'] : $_type[0];
			}
			switch($_type)
			{
				case 'xmlrpc':
					$xml_functions = array(
						'read' => array(
							'function'  => 'read',
							'signature' => array(array(xmlrpcStruct)),
							'docstring' => lang('Returns struct of users application access')
						),
						'list_methods' => array(
							'function'  => 'list_methods',
							'signature' => array(array(xmlrpcStruct,xmlrpcString)),
							'docstring' => lang('Read this list of methods.')
						)
					);
					return $xml_functions;
					break;
				case 'soap':
					return $this->soap_functions;
					break;
				default:
					return array();
					break;
			}
		}

		/**************************************************************************\
		* These are the standard $this->account_id specific functions              *
		\**************************************************************************/

		/*!
		@function read_repository
		@abstract read from repository
		@discussion private should only be called from withing this class
		*/
		function read_repository()
		{
			if (!isset($GLOBALS['phpgw_info']['apps']) ||
				!is_array($GLOBALS['phpgw_info']['apps']))
			{
				$this->read_installed_apps();
			}
			$this->data = Array();
			if(!$this->account_id)
			{
				return False;
			}
			$apps = $GLOBALS['phpgw']->acl->get_user_applications($this->account_id);
			foreach($GLOBALS['phpgw_info']['apps'] as $app => $data)
			{
				if (isset($apps[$app]) && $apps[$app])
				{
					$this->data[$app] = array(
						'title'   => $GLOBALS['phpgw_info']['apps'][$app]['title'],
						'name'    => $app,
						'enabled' => True,
						'status'  => $GLOBALS['phpgw_info']['apps'][$app]['status'],
						'id'      => $GLOBALS['phpgw_info']['apps'][$app]['id']
					);
				}
			}
			return $this->data;
		}

		/*!
		@function read()
		@abstract read from the repository
		@discussion pubic function that is used to determine what apps a user has rights to
		*/
		function read()
		{
			if (!count($this->data))
			{
				$this->read_repository();
			}
			return $this->data;
		}
		/*!
		@function add
		@abstract add an app to a user profile
		@discussion
		@param $apps array containing apps to add for a user
		*/
		function add($apps)
		{
			if(is_array($apps))
			{
				foreach($apps as $app)
				{
					$this->data[$app] = array(
						'title'   => $GLOBALS['phpgw_info']['apps'][$app]['title'],
						'name'    => $app,
						'enabled' => True,
						'status'  => $GLOBALS['phpgw_info']['apps'][$app]['status'],
						'id'      => $GLOBALS['phpgw_info']['apps'][$app]['id']
					);
				}
			}
			elseif(gettype($apps))
			{
				$this->data[$apps] = array(
					'title'   => $GLOBALS['phpgw_info']['apps'][$apps]['title'],
					'name'    => $apps,
					'enabled' => True,
					'status'  => $GLOBALS['phpgw_info']['apps'][$apps]['status'],
					'id'      => $GLOBALS['phpgw_info']['apps'][$apps]['id']
				);
			}
			return $this->data;
		}
		/*!
		@function delete
		@abstract delete an app from a user profile
		@discussion
		@param $appname appname to remove
		*/
		function delete($appname)
		{
			if($this->data[$appname])
			{
				unset($this->data[$appname]);
			}
			return $this->data;
		}
		/*!
		@function update_data
		@abstract update the array(?)
		@discussion
		@param $data update the repository array(?)
		*/
		function update_data($data)
		{
			$this->data = $data;
			return $this->data;
		}
		/*!
		@function save_repository()
		@abstract save the repository
		@discussion
		*/
		function save_repository()
		{
			$num_rows = $GLOBALS['phpgw']->acl->delete_repository("%%", 'run', $this->account_id);
			foreach($this->data as $app => $data)
			{
				if(!$this->is_system_enabled($app))
				{
					continue;
				}
				$GLOBALS['phpgw']->acl->add_repository($app,'run',$this->account_id,1);
			}
			return $this->data;
		}

		/**************************************************************************\
		* These are the non-standard $this->account_id specific functions          *
		\**************************************************************************/

		function app_perms()
		{
			if (!count($this->data))
			{
				$this->read_repository();
			}
			foreach ($this->data as $app => $data)
			{
				$apps[] = $this->data[$app]['name'];
			}
			return $apps;
		}

		function read_account_specific()
		{
			if (!is_array($GLOBALS['phpgw_info']['apps']))
			{
				$this->read_installed_apps();
			}
			if ($app_list = $GLOBALS['phpgw']->acl->get_app_list_for_id('run',1,$this->account_id))
			{
				foreach($app_list as $app)
				{
					if ($this->is_system_enabled($app))
					{
						$this->data[$app] = array(
							'title'   => $GLOBALS['phpgw_info']['apps'][$app]['title'],
							'name'    => $app,
							'enabled' => True,
							'status'  => $GLOBALS['phpgw_info']['apps'][$app]['status'],
							'id'      => $GLOBALS['phpgw_info']['apps'][$app]['id']
						);
					}
				}
			}
			return $this->data;
		}

		/**************************************************************************\
		* These are the generic functions. Not specific to $this->account_id       *
		\**************************************************************************/

		/*!
		@function read_installed_apps()
		@abstract populate array with a list of installed apps
		*/
		function read_installed_apps()
		{
			//jakjr: so vai ao banco, caso o array de apps esteja vazio.
			if (empty($GLOBALS['phpgw_info']['apps']))
			{
				$this->db->query('select * from phpgw_applications where app_enabled != 3 and app_name!= \'rest\' order by app_order asc',__LINE__,__FILE__);
				if($this->db->num_rows())
				{
					while ($this->db->next_record())
					{
						$title = $app_name = $this->db->f('app_name');

						if (@is_array($GLOBALS['phpgw_info']['user']['preferences']) &&
						    ($t = lang($app_name)) != $app_name.'*')
						{
							$title = $t;
						}
						$GLOBALS['phpgw_info']['apps'][$this->db->f('app_name')] = Array(
							'title'   => $title,
							'name'    => $this->db->f('app_name'),
							'enabled' => True,
							'status'  => $this->db->f('app_enabled'),
							'id'      => (int)$this->db->f('app_id'),
							'order'   => (int)$this->db->f('app_order'),
							'version' => $this->db->f('app_version')
						);
					}
				}
			}
		}
		/*!
		@function is_system_enabled
		@abstract check if an app is enabled
		@param $appname name of the app to check for
		*/
		function is_system_enabled($appname)
		{
			if(!is_array($GLOBALS['phpgw_info']['apps']))
			{
				$this->read_installed_apps();
			}
			if ($GLOBALS['phpgw_info']['apps'][$appname]['enabled'])
			{
				return True;
			}
			else
			{
				return False;
			}
		}

		function id2name($id)
		{
			foreach($GLOBALS['phpgw_info']['apps'] as $appname => $app)
			{
				if((int)$app['id'] == (int)$id)
				{
					return $appname;
				}
			}
			return '';
		}

		function name2id($appname)
		{
			if(is_array($GLOBALS['phpgw_info']['apps'][$appname]))
			{
				return $GLOBALS['phpgw_info']['apps'][$appname]['id'];
			}
			return 0;
		}
	}
?>
