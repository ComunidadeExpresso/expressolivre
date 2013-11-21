<?php
  /***************************************************************************\
  * eGroupWare - Contacts Center                                              *
  * http://www.egroupware.org                                                 *
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

	class so_address extends so_main {

		function so_address ( $id = false )
		{
			$this->init();
			
			$this->main_fields = array(
				'id_address' => array(
					'name'  => 'id_address',
					'type'  => 'primary',
					'state' => 'empty',
					'value' => &$this->id
				),
				'id_city' => array(
					'name'  => 'id_city',
					'type'  => 'foreign',
					'association' => array(
						'table' => 'phpgw_cc_city',
						'field' => 'id_city'
					),
					'state' => 'empty',
					'value' => false
				),
				'id_state' => array(
					'name'  => 'id_state',
					'type'  => 'foreign',
					'association' => array(
						'table' => 'phpgw_cc_state',
						'field' => 'id_state'
					),
					'state' => 'empty',
					'value' => false
				),
				'id_country' => array(
					'name'  => 'id_country',
					'type'  => false,
					'state' => 'empty',
					'value' => false
				),
				'address1' => array(
					'name'  => 'address1',
					'type'  => false,
					'state' => 'empty',
					'value' => false
				),
				'address2' => array(
					'name'  => 'address2',
					'type'  => false,
					'state' => 'empty',
					'value' => false
				),
				'complement' => array(
					'name'  => 'complement',
					'type'  => false,
					'state' => 'empty',
					'value' => false
				),
				'address_other' => array(
					'name'  => 'address_other',
					'type'  => false,
					'state' => 'empty',
					'value' => false
				),
				'postal_code' => array(
					'name'  => 'postal_code',
					'type'  => false,
					'state' => 'empty',
					'value' => false
				),
				'po_box' => array(
					'name'  => 'po_box',
					'type'  => false,
					'state' => 'empty',
					'value' => false
				),
				'address_is_default' => array(
					'name'  => 'address_is_default',
					'type'  => false,
					'state' => 'empty',
					'value' => false
				)
			);
			

			$this->db_tables = array(
				'phpgw_cc_addresses' => array(
					'type'   => 'main',
					'keys'   => array(
						'primary' => array(&$this->main_fields['id_address']),
						'foreign' => array(&$this->main_fields['id_city'],
						                   &$this->main_fields['id_state'])
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
		
			@function get_id_city
			@abstract Returns the ID of the City where this
				address is located
			@author Raphael Derosso Pereira
		
		*/
		function get_id_city (  )
		{
			return $this->main_fields['id_city']['value'];
		}
	
		/*!
		
			@function get_id_state
			@abstract Returns the ID of the State where this
				address is located
			@author Raphael Derosso Pereira
		
		*/
		function get_id_state (  )
		{
			return $this->main_fields['id_state']['value'];
		}
	
		/*!
		
			@function get_id_country
			@abstract Returns the ID of the Country where this
				address is located
			@author Raphael Derosso Pereira
		
		*/
		function get_id_country (  )
		{
			return $this->main_fields['id_country']['value'];
		}
	
		/*!
		
			@function get_address1
			@abstract Returns the Address1
			@author Raphael Derosso Pereira
		
		*/
		function get_address1 (  )
		{
			return $this->main_fields['address1']['value'];
		}
	
		/*!
		
			@function get_address2
			@abstract Returns the Address2
			@author Raphael Derosso Pereira
		
		*/
		function get_address2 (  )
		{
			return $this->main_fields['address2']['value'];
		}
	
		/*!
		
			@function get_complement
			@abstract Returns the Address' Complement
			@author Raphael Derosso Pereira
		
		*/
		function get_complement (  )
		{
			return $this->main_fields['complement']['value'];
		}
	
		/*!
		
			@function get_address_other
			@abstract Returns the Other Address Info, like
				quarter, neighborhood, county, etc...
			@author Raphael Derosso Pereira
		
		*/
		function get_address_other (  )
		{
			return $this->main_fields['address_other']['value'];
		}
	
		/*!
		
			@function get_postal_code
			@abstract Returns the Postal Code of the Address
			@author Raphael Derosso Pereira
		
		*/
		function get_postal_code (  )
		{
			return $this->main_fields['postal_code']['value'];
		}
	
		/*!
		
			@function get_bo_box
			@abstract Returns the PO BOX number
			@author Raphael Derosso Pereira
		
		*/
		function get_po_box (  )
		{
			return $this->main_fields['po_box']['value'];
		}
	
		/*!
		
			@function is_default
			@abstract Returns true or false depending on the value
				of address_is_default
			@author Raphael Derosso Pereira
		
		*/
		function is_default (  )
		{
			switch(strtoupper($this->main_fields['address_is_default']['value']))
			{
				case 'T':
				case 'TRUE':
				case '1':
					return true;
					
				default:
					return false; 
			}
		}
	

		/*********************************************************************\
		 *                   Methods to Alter Information                    *
		\*********************************************************************/
		
		/*!
		
			@function set_id_city
			@abstract Sets the Address City ID
			@author Raphael Derosso Pereira
		
			@param integer $id_city The City ID
		*/
		function set_id_city ( $id_city )
		{
			$this->main_fields['id_city']['value'] = $id_city;
			$this->manage_fields($this->main_fields['id_city'], 'changed');
		}
	
		/*!
		
			@function set_id_state
			@abstract Sets the Address State ID
			@author Raphael Derosso Pereira
		
			@param integer $id_state The State ID
		*/
		function set_id_state ( $id_state )
		{
			$this->main_fields['id_state']['value'] = $id_state;
			$this->manage_fields($this->main_fields['id_state'], 'changed');
		}
	
		/*!
		
			@function set_id_country
			@abstract Sets the Address Country ID
			@author Raphael Derosso Pereira
		
			@param integer $id_country The Country ID
		*/
		function set_id_country ( $id_country )
		{
			$this->main_fields['id_country']['value'] = $id_country;
			$this->manage_fields($this->main_fields['id_country'], 'changed');
		}
	
		/*!
		
			@function set_address1
			@abstract Sets the Address 1
			@author Raphael Derosso Pereira
		
			@param string $address1 The Address
		
		*/
		function set_address1 ( $address1 )
		{
			$this->main_fields['address1']['value'] = $address1;
			$this->manage_fields($this->main_fields['address1'], 'changed');
		}
	
		/*!
		
			@function set_address2
			@abstract Sets the Address 2
			@author Raphael Derosso Pereira
		
			@param string $address2 The Address
		
		*/
		function set_address2 ( $address2 )
		{
			$this->main_fields['address2']['value'] = $address2;
			$this->manage_fields($this->main_fields['address2'], 'changed');
		}
	
		/*!
		
			@function set_postal_code
			@abstract Sets the Postal Code
			@author Raphael Derosso Pereira
		
			@param string $postal_code The Postal Code
		
		*/
		function set_postal_code ( $postal_code )
		{
			$this->main_fields['postal_code']['value'] = $postal_code;
			$this->manage_fields($this->main_fields['postal_code'], 'changed');
		}
	
		/*!
		
			@function set_po_box
			@abstract Sets the PO BOX
			@author Raphael Derosso Pereira
		
			@param string $po_box The PO BOX
		
		*/
		function set_po_box ( $po_box )
		{
			$this->main_fields['po_box']['value'] = $po_box;
			$this->manage_fields($this->main_fields['po_box'], 'changed');
		}
	
		/*!
		
			@function set_complement
			@abstract Sets the Complement
			@author Raphael Derosso Pereira
		
			@param string $complement The Complement
		
		*/
		function set_complement ( $complement )
		{
			$this->main_fields['complement']['value'] = $complement;
			$this->manage_fields($this->main_fields['complement'], 'changed');
		}
	
		/*!
		
			@function set_address_other
			@abstract Sets any other address information on
				the option of the user, like quarter, county, etc
			@author Raphael Derosso Pereira
		
			@param string $address_other The Address
		
		*/
		function set_address_other ( $address_other )
		{
			$this->main_fields['address_other']['value'] = $address_other;
			$this->manage_fields($this->main_fields['address_other'], 'changed');
		}
	
		/*!
		
			@function set_address_is_default
			@abstract Sets the default state of this Address
			@author Raphael Derosso Pereira
		
			@param boolean $default The default state
		
		*/
		function set_address_is_default ( $default )
		{
			if ($default)
			{
				$this->main_fields['address_is_default']['value'] = 1;
			}
			else
			{
				$this->main_fields['address_is_default']['value'] = 0;
			}
			$this->manage_fields($this->main_fields['address_is_default'], 'changed');
		}
	}
?>
