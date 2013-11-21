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
	
	class so_prefix extends so_main 
	{
	
		function so_prefix ( $id = false )
		{
			$this->init();
			
			$this->main_fields = array(
				'id_prefix' => array(
					'name'  => 'id_prefix',
					'type'  => 'primary',
					'state' => 'empty',
					'value' => &$this->id
				),
				'prefix' => array(
					'name'  => 'prefix',
					'type'  => false,
					'state' => 'empty',
					'value' => false
				)
			);

			$this->db_tables = array(
				'phpgw_cc_prefixes' => array(
					'type'   => 'main',
					'keys'   => array(
						'primary' => array(&$this->main_fields['id_prefix']),
						'foreign' => false
					),
					'fields' => & $this->main_fields
				)
			);
			
			if ($id)
			{
				$this->id = $id;
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
	
		/*********************************************************************\
		 *                   Methods to Get Information                      *
		\*********************************************************************/
		
		/*!
		
			@function get_prefix
			@abstract Returns the Prefix
			@author Raphael Derosso Pereira
		
		*/
		function get_prefix (  )
		{
			return $this->main_fields['prefix']['value'];
		}
	

		/*********************************************************************\
		 *                   Methods to Set Information                      *
		\*********************************************************************/
		
		/*!
		
			@function set_prefix
			@abstract Sets the Prefix
			@author Raphael Derosso Pereira
			
			@param string $prefix The Prefix
		
		*/
		function set_prefix ( $prefix )
		{
			$this->main_fields['prefix']['value'] = $prefix;
			$this->manage_fields($this->main_fields['prefix']['value']);
		}
	
	}
?>
