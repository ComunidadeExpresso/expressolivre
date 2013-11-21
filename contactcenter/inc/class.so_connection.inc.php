<?php
  /***************************************************************************\
  * eGroupWare - Contacts Center                                              *
  * http://www.egroupware.org                                                 *
  *                                                                           *
  * Storage Object Classes                                                    *
  * Written by:                                                               *
  *  - Raphael Derosso Pereira <raphaelpereira@users.sourceforge.net>         *
  *  sponsored by Thyamad - http://www.thyamad.com                            *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/


	include_once("class.so_main.inc.php");
	
	class so_connection extends so_main 
	{
	
		function so_connection ( $id = false )
		{
			$this->init();
			
			$this->main_fields = array(
				'id_connection' => array(
					'name'  => 'id_connection',
					'type'  => 'primary',
					'state' => 'empty',
					'value' => &$this->id
				),
				'connection_name' => array(
					'name'  => 'connection_name',
					'type'  => false,
					'state' => 'empty',
					'value' => false
				),
				'connection_value' => array(
					'name'  => 'connection_value',
					'type'  => false,
					'state' => 'empty',
					'value' => false
				),
				'connection_is_default' => array(
					'name'  => 'connection_is_default',
					'type'  => false,
					'state' => 'empty',
					'value' => false
				)
			);

			$this->db_tables = array(
				'phpgw_cc_connections' => array(
					'type'   => 'main',
					'keys'   => array(
						'primary' => array(&$this->main_fields['id_connection']),
						'foreign' => false
					),
					'fields' => & $this->main_fields
				)
			);
			
			if ($id)
			{
				if (!$this->checkout($id))
				{
					$this->reset_values();
					$this->state = 'new';
				}
			}
			else
			{
				$this->state = 'new';
			}
		}



		/**********************************************************************\
		 *                    Methods to Get Information                      *
		\**********************************************************************/

		/*!
		
			@function get_name
			@abstract Returns the Connection Name 
			@author Raphael Derosso Pereira
			
		*/	
		function get_name (  )
		{
			return $this->main_fields['connection_name']['value'];
		}
	
		/*!
		
			@function get_value
			@abstract Return the Connection Value
			@author Raphael Derosso Pereira
		
		*/	
		function get_value (  )
		{
			return $this->main_fields['connection_value']['value'];
		}
	
		/*!
		
			@function is_default
			@abstract Return true if this is the default
				connection, else return false
			@author Raphael Derosso Pereira
		
		*/	
		function is_default (  )
		{
			switch (strtolower($this->main_fields['connection_is_default']['value']))
			{
				case 't':
				case 'true':
				case '1':
					return true;

				case 'f':
				case 'false':
				case '0':
					return false;
			}
		}
	


		/**********************************************************************\
		 *                    Methods to Set Information                      *
		\**********************************************************************/

		/*!
		
			@function set_name
			@abstract Sets the Connection Name
			@author Raphael Derosso Pereira
			
			@param string $name The Connection Name
		
		*/	
		function set_name ( $name )
		{
			$this->main_fields['connection_name']['value'] = $name;
			$this->manage_fields($this->main_fields['connection_name'], 'changed');
		}
	
		/*!
		
			@function set_value
			@abstract Sets the Connection Value
			@author Raphael Derosso Pereira
		
			@param string $value The Connection Value
		
		*/	
		function set_value ( $value )
		{
			$this->main_fields['connection_value']['value'] = $value;
			$this->manage_fields($this->main_fields['connection_value'], 'changed');
		}
	
		/*!
		
			@function set_default
			@abstract Sets the Connection Default trueness
			@author Raphael Derosso Pereira
		
			@param boolean $default The Connection Default trueness
		
		*/	
		function set_default ( $default )
		{
			if ($default)
			{
				$this->main_fields['connection_name']['value'] = 1;
			}
			else
			{
				$this->main_fields['connection_name']['value'] = 0;
			}
			$this->manage_fields($this->main_fields['connection_name'], 'changed');
		}
	
	}
?>
