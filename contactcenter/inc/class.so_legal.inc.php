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
	
	class so_company_legal extends so_main 
	{
	
		function so_company_legal ( $id = false )
		{
			$this->init();
			
			$this->main_fields = array(
				'id_company_legal' => array(
					'name'  => 'id_company_legal',
					'type'  => 'primary',
					'state' => 'empty',
					'value' => &$this->id
				),
				'id_typeof_company_legal' => array(
					'name'  => 'id_typeof_company_legal',
					'type'  => 'foreign',
					'association' => array(
						'table' => 'phpgw_cc_typeof_co_legals',
						'field' => 'id_typeof_company_legal'
					),
					'state' => 'empty',
					'value' => false
				),
				'id_company' => array(
					'name'  => 'id_company',
					'type'  => 'foreign',
					'association' => array(
						'table' => 'phpgw_cc_company',
						'field' => 'id_company'
					),
					'state' => 'empty',
					'value' => false
				),
				'legal_info_name' => array(
					'name'  => 'legal_info_name',
					'type'  => false,
					'state' => 'empty',
					'value' => false
				),
				'legal_info_value' => array(
					'name'  => 'legal_info_value',
					'type'  => false,
					'state' => 'empty',
					'value' => false
				)
			);

			$this->db_tables = array(
				'phpgw_cc_company_legals' => array(
					'type'   => 'main',
					'keys'   => array(
						'primary' => array(&$this->main_fields['id_company_legal']),
						'foreign' => array(
							&$this->main_fields['id_typeof_company_legal'],
							&$this->main_fields['id_company']
						)
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
		
			@function get_id_type
			@abstract Returns the ID of the Type of this Legal
			@author Raphael Derosso Pereira
		
		*/
		function get_id_type (  )
		{
			return $this->main_fields['id_type']['value'];
		}
	
		/*!
		
			@function get_name
			@abstract Returns the Legal Name
			@author Raphael Derosso Pereira
		
		*/
		function get_name (  )
		{
			return $this->main_fields['legal_name']['value'];
		}
	
		/*!
		
			@function get_value
			@abstract Returns the Legal Value
			@author Raphael Derosso Pereira
		
		*/
		function get_value (  )
		{
			return $this->main_fields['legal_value']['value'];
		}
	


		/*********************************************************************\
		 *                   Methods to Get Information                      *
		\*********************************************************************/
		
		/*!
		
			@function set_id_type
			@abstract Sets the Type ID of this Legal
			@author Raphael Derosso Pereira
			
			@param integer $id_type The Type ID
		
		*/
		function set_id_type ( $id_type )
		{
			$this->main_fields['id_type']['value'] = $id_type; 
			$this->manage_fields($this->main_fields['id_type'], 'changed');
		}
	
		/*!
		
			@function set_name
			@abstract Sets the Legal Name
			@author Raphael Derosso Pereira
		
			@param string $name The Legal Name
		
		*/
		function set_name ( $name )
		{
			$this->main_fields['legal_info_name']['value'] = $name; 
			$this->manage_fields($this->main_fields['legal_info_name'], 'changed');
		}
	
		/*!
		
			@function set_value
			@abstract Sets the Legal value
			@author Raphael Derosso Pereira
		
			@param string $value The Legal Value
		
		*/
		function set_value ( $value )
		{
			$this->main_fields['legal_info_value']['value'] = $value; 
			$this->manage_fields($this->main_fields['legal_info_value'], 'changed');
		}
	
	}
?>
