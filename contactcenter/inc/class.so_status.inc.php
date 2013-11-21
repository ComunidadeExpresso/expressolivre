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
	
	class so_status extends so_main 
	{
	
		function so_status ( $id = false )
		{
			$this->init();
				
			$this->main_fields = array(
				'id_status' => array(
					'name'  => 'id_status',
					'type'  => 'primary',
					'state' => 'empty',
					'value' => &$this->id
				),
				'status_name' => array(
					'name'  => 'status_name',
					'type'  => false,
					'state' => 'empty',
					'value' => false
				),
			);

			$this->db_tables = array(
				'phpgw_cc_status' => array(
					'type'   => 'main',
					'keys'   => array(
						'primary' => array(&$this->main_fields['id_status']),
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
		
			@function get_status
			@abstract Returns Status name
			@author Raphael Derosso Pereira
		
		*/
		function get_status (  )
		{			
			return $this->main_fields['status_name']['value'];
		}
	


		/*********************************************************************\
		 *                   Methods to Set Information                      *
		\*********************************************************************/
		
		/*!
		
			@function set_status
			@abstract Sets the Status name
			@author Raphael Derosso Pereira
		
			@param string $status The Status
		*/
		function set_status ( $status )
		{
			$this->main_fields['status_name']['value'] = $status;
			$this->manage_fields($this->main_fields['status_name'],'changed');
		}
	
	}
?>
