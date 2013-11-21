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


	include_once( "class.so_main.inc.php" );
	
	class so_suffix extends so_main 
	{
	
		function so_suffix ( $id = false )
		{
			$this->init();
				
			$this->main_fields = array(
				'id_suffix' => array(
					'name'  => 'id_suffix',
					'type'  => 'primary',
					'state' => 'empty',
					'value' => &$this->id
				),
				'suffix' => array(
					'name'  => 'suffix',
					'type'  => false,
					'state' => 'empty',
					'value' => false
				)
			);

			$this->db_tables = array(
				'phpgw_cc_suffixes' => array(
					'type'   => 'main',
					'keys'   => array(
						'primary' => array(&$this->main_fields['id_suffix']),
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
		
			@function get_suffix
			@abstract Returns the Sulfix
			@author Raphael Derosso Pereira
		
		*/
		function get_suffix (  )
		{
			return $this->main_fields['suffix']['value'];
		}
	
	

		/*********************************************************************\
		 *                   Methods to Get Information                      *
		\*********************************************************************/
		
		/*!
		
			@function set_suffix
			@abstract Sets the Sulfix
			@author Raphael Derosso Pereira
			
			@param string $suffix The Sulfix
		
		*/
		function set_suffix ( $suffix )
		{
			$this->main_fields['suffix']['value'] = $suffix;
			$this->manage_fields($this->main_fields['suffix'], 'changed');
		}
	
	}
?>