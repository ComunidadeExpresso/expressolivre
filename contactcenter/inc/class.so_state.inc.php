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

	class so_state extends so_main 
	{

		function so_state ( $id = false )
		{
			$this->init();
			
			$this->main_fields = array(
				'id_state' => array(
					'name'  => 'id_state',
					'type'  => 'primary',
					'state' => 'empty',
					'value' => &$this->id
				),
				'id_country' => array(
					'name'  => 'id_country',
					'type'  => 'foreign',
					'association' => array(
						'table' => 'phpgw_common_country_list',
						'field' => 'id_country'
					),
					'state' => 'empty',
					'value' => false
				),
				'state_name' => array(
					'name'  => 'state_name',
					'type'  => false,
					'state' => 'empty',
					'value' => false
				),
				'state_symbol' => array(
					'name'  => 'state_symbol',
					'type'  => false,
					'state' => 'empty',
					'value' => false
				)
			);


			$this->db_tables = array(
				'phpgw_cc_state' => array(
					'type'   => 'main',
					'keys'   => array(
						'primary' => array(&$this->main_fields['id_state']),
						'foreign' => array(&$this->main_fields['id_country'])
					),
					'fields' => & $this->main_fields
				)
			);
			
			if($id)
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
		

		/*********************************************************************\
		 *                   Methods to Get Information                      *
		\*********************************************************************/
		
		/*!
		
			@function get_id_country
			@abstract Returns the ID of the Country where this
				State is located
			@author Raphael Derosso Pereira
		
		*/
		function get_id_country (  )
		{
			return $this->main_fields['id_country']['value'];
		}
	
		/*!
		
			@function get_state_name
			@abstract Returns the Name of the State
			@author Raphael Derosso Pereira
		
		*/
		function get_state_name (  )
		{
			return $this->main_fields['state_name']['value'];
		}

		/*!
		
			@function get_state_symbol
			@abstract Returns the State Symbol
			@author Raphael Derosso Pereira
		
		*/
		function get_state_symbol (  )
		{
			return $this->main_fields['state_symbol']['value'];
		}


		/*********************************************************************\
		 *                   Methods to Alter Information                    *
		\*********************************************************************/
		
		/*!
		
			@function set_id_country
			@abstract Sets the State's Country ID
			@author Raphael Derosso Pereira
		
			@param string $id_country The Country ID
		*/
		function set_id_country ( $id_country )
		{
			$this->main_fields['id_country']['value'] = $id_country;
			$this->manage_fields($this->main_fields['id_country'], 'changed');
		}
		
		/*!
		
			@function set_state_name
			@abstract Sets the State's Name
			@author Raphael Derosso Pereira
		
			@param string $name The State Name
		*/
		function set_state_name ( $name )
		{
			$this->main_fields['state_name']['value'] = $name;
			$this->manage_fields($this->main_fields['state_name'], 'changed');
		}
		
		/*!
		
			@function set_state_symbol
			@abstract Sets the State's Symbol
			@author Raphael Derosso Pereira
		
			@param string $symbol The State Symbol
		*/
		function set_state_symbol ( $symbol )
		{
			$this->main_fields['state_symbol']['value'] = $symbol;
			$this->manage_fields($this->main_fields['state_symbol'], 'changed');
		}
	}
?>