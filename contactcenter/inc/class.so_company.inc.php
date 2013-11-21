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

	class so_company extends so_main {

		function so_company ( $id_company = false)
		{
			$this->init();
			
			$this->main_fields = array(
				'id_company'       => array(
					'name'  => 'id_company',
					'type'  => 'primary',
					'state' => 'empty',
					'value' => &$this->id
				),	
				'id_company_owner' => array(
					'name'  => 'id_company_owner',		
					'type'  => false,
					'state' => 'empty',
					'value' => false
				),
				'company_name'  => array(
					'name'  => 'company_name',
					'type'  => false,
					'state' => 'empty',
					'value' => false
				),
				'company_notes' => array(
					'name'  => 'company_notes',
					'type'  => false,
					'state' => 'empty',
					'value' => false
				),
			);
					
			$this->contacts = array(
				'id_contact' => array(
					'name'        => 'id_contact',
					'type'        => array('foreign', 'primary'),
					'association' => array(
						'table'   => 'phpgw_cc_contact',
						'field'   => 'id_contact'
					),
					'states'      => array(),
					'values'      => array()
				),
				'id_company' => array(
					'name'        => 'id_company',
					'type'        => array('foreign', 'primary'),
					'association' => array(
						'table'   => 'phpgw_cc_company',
						'field'   => 'id_company'
					),
					'states'      => array(),
					'values'      => array()
				),
				'title'      => array(
					'name'   => 'title',
					'type'   => false,
					'states' => array(),
					'values' => array()
				),
				'department' => array(
					'name'   => 'department',
					'type'   => false,
					'states' => array(),
					'values' => array()
				),
				'default_contact' => array(
					'name'   => 'default_contact',
					'type'   => false,
					'states' => array(),
					'values' => array()
				),
				'default_company' => array(
					'name'   => 'default_company',
					'type'   => false,
					'states' => array(),
					'values' => array()
				)
			);
						
			$this->relations = array(
				'id_company' => array(
					'name'        => 'id_company',
					'type'        => array('foreign', 'primary'),
					'association' => array(
						'table'   => 'phpgw_cc_company',
						'field'   => 'id_contact'
					),
					'states'      => array(),
					'values'      => array()
				),
				'id_related' => array(
					'name'        => 'id_related',
					'type'        => array('foreign', 'primary'),
					'association' => array(
						'table'   => 'phpgw_cc_company',
						'field'   => 'id_contact'
					),
					'states'      => array(),
					'values'      => array()
				),
				'id_typeof_company_relation' => array(
					'name'        => 'id_typeof_company_relation',
					'type'        => array('foreign', 'primary'),
					'association' => array(
						'table'   => 'phpgw_cc_typeof_co_rels',
						'field'   => 'id_typeof_company_relation'
					),
					'states'      => array(),
					'values'      => false
				)
			);
				
			$this->addresses = array(
				'id_company' => array(
					'name'        => 'id_company',
					'type'        => array('foreign', 'primary'),
					'association' => array(
						'table'   => 'phpgw_cc_company',
						'field'   => 'id_contact'
					),
					'states'      => array(),
					'values'      => array()
				),
				'id_address' => array(
					'name'        => 'id_address',
					'type'        => array('foreign', 'primary'),
					'association' => array(
						'table'   => 'phpgw_cc_addresses',
						'field'   => 'id_address'
					),
					'states'      => array(),
					'values'      => array()
				),
				'id_typeof_company_address' => array(
					'name'        => 'id_typeof_company_address',
					'type'        => 'foreign',
					'association' => array(
						'table'   => 'phpgw_cc_typeof_co_addrs',
						'field'   => 'id_typeof_company_address'
					),
					'states'      => array(),
					'values'      => array()
				)
			);
			
			$this->connections = array(
				'id_company' => array(
					'name'        => 'id_company',
					'type'        => array('foreign', 'primary'),
					'association' => array(
						'table'   => 'phpgw_cc_company',
						'field'   => 'id_contact'
					),
					'states'      => array(),
					'values'      => array()
				),
				'id_connection' => array(
					'name'        => 'id_connection',
					'type'        => array('foreign', 'primary'),
					'association' => array(
						'table'   => 'phpgw_cc_connections',
						'field'   => 'id_connection'
					),
					'states'      => array(),
					'values'      => array()
				),
				'id_typeof_company_connection' => array(
					'name'        => 'id_typeof_company_connection',
					'type'        => array('foreign', 'primary'),
					'association' => array(
						'table'   => 'phpgw_cc_typeof_co_conns',
						'field'   => 'id_typeof_company_connection'
					),
					'states'      => array(),
					'values'      => array()
				)
			);

			$this->legals = array(
				'id_company_legal' => array(
					'name'        => 'id_company_legal',
					'type'        => array('foreign', 'primary'),
					'association' => array(
						'table'   => 'phpgw_cc_company',
						'field'   => 'id_company'
					),
					'states'      => array(),
					'values'      => array()
				),
				'id_typeof_company_legal' => array(
					'name'        => 'id_typeof_company_legal',
					'type'        => 'foreign',
					'association' => array(
						'table'   => 'phpgw_cc_company_legal',
						'field'   => 'id_typeof_company_legal'
					),
					'states'      => array(),
					'values'      => array()
				),
				'id_company' => array(
					'name'        => 'id_company',
					'type'        => 'foreign',
					'association' => array(
						'table'   => 'phpgw_cc_company',
						'field'   => 'id_company'
					),
					'states'      => array(),
					'values'      => array()
				),
				'legal_info_name' => array(
					'name'   => 'legal_info_name',
					'type'   => false,
					'states' => array(),
					'values' => array()
				),
				'legal_info_value' => array(
					'name'   => 'legal_info_value',
					'type'   => false,
					'states' => array(),
					'values' => array()
				)
			);


			$this->db_tables = array(
				'phpgw_cc_company' => array(
					'type'   => 'main',
					'keys'   => array(
						'primary' => array(&$this->main_fields['id_company']),
						'foreign' => false
					),
					'fields' => & $this->main_fields
				),
				'phpgw_cc_contact_company' => array(
					'type'   => 'multi',
					'keys'   => array(
						'primary' => array(
							&$this->contacts['id_contact'],
							&$this->contacts['id_company']
						),
						'foreign' => array(
							&$this->contacts['id_contact'],
							&$this->contacts['id_company']
						)
					),
					'fields' => & $this->contacts
				),
				'phpgw_cc_company_rels' => array(
					'type'   => 'multi',
					'keys'   => array(
						'primary' => array(
							&$this->relations['id_company'],
							&$this->relations['id_related']
						),
						'foreign' => array(
							&$this->relations['id_company'],
							&$this->relations['id_related']
						)
					),
					'fields' => & $this->relations
				),
				'phpgw_cc_company_addrs' => array(
					'type'   => 'multi',
					'keys'   => array(
						'primary' => array(
							&$this->addresses['id_company'],
							&$this->addresses['id_address']
						),
						'foreign' => array(
							&$this->addresses['id_company'],
							&$this->addresses['id_address'],
							&$this->addresses['id_typeof_company_address']
						)
					),
					'fields' => & $this->addresses
				),
				'phpgw_cc_company_conns' => array(
					'type'   => 'multi',
					'keys'   => array(
						'primary' => array(
							&$this->connections['id_company'],
							&$this->connections['id_connection']
						),
						'foreign' => array(
							&$this->connections['id_company'],
							&$this->connections['id_connection'],
							&$this->connections['id_typeof_company_connection']
						)
					),
					'fields' => & $this->connections
				)
			);
					
			if ($id_company)
			{
				$this->id = $id_company;
				if (!$this->checkout($id_company))
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
		 *                Methods to Get Company Info                        *
		\*********************************************************************/

		/*!
		
			@function get_company_name
			@abstract Returns the Company Name
			@author Raphael Derosso Pereira
		
		*/
		function get_company_name()
		{
			return $this->main_fields['company_name']['value'];
		}
		
		/*!
		
			@function get_company_notes
			@abstract Returns the Company Notes
			@author Raphael Derosso Pereira
		
		*/
		function get_company_notes()
		{
			return $this->main_fields['company_notes']['value'];
		}
	
		/*!
		
			@function get_legals
			@abstract Returns the Company's Legals IDs
			@author Raphael Derosso Pereira
		
			@return array Format:
				$return = array(
					'<id_legal1>' => array(
						'id_type'          => '<id_type>',
						'legal_info_name'  => '<legal_info_name>',
						'legal_info_value' => '<legal_info_value>'
					),
					'<id_legal2>' => array(
						'id_type'          => '<id_type>',
						'legal_info_name'  => '<legal_info_name>',
						'legal_info_value' => '<legal_info_value>'
					),
					...
				);
		*/
		function get_legals (  )
		{
			$return = array();
			
			while(list(,$id_legal) = each($this->legals['id_legal']['values']) and
				list(,$id_type) = each($this->legals['id_typeof_company_legal']['values']) and
				list(,$legal_info_name) = each($this->legals['legal_info_name']['values']) and
				list(,$legal_info_value) = each($this->legals['legal_info_value']['values']))
			{
				$return[$id_legal] = array(
					'id_type'          => $id_type,
					'legal_info_name'  => $legal_info_name,
					'legal_info_value' => $legal_info_value
				);		
			}
			
			return $return;
		}
	
		/*!
		
			@function get_relations
			@abstract Returns the Company's Relations
			@author Raphael Derosso Pereira
		
			@return array Format:
				$return = array(
					'<id_related1>' => '<id_relation1_type>',
					'<id_related2>' => '<id_relation2_type>',
					...);
		*/
		function get_relations (  )
		{
			$return = array();
			
			while(list(,$id_related) = each($this->relations['id_related']['values']) and
				list(,$id_relation_type) = each($this->relations['id_typeof_company_relation']['values']))
			{
				$return[$id_related] = $id_relation_type;				
			}
			
			return $return;
		}
	
		/*!
		
			@function get_addresses
			@abstract Returns the Company's Addresses
			@author Raphael Derosso Pereira
		
			@return array Format:
				$return = array(
					'<id_address1>' => '<id_address1_type>',
					'<id_address2>' => '<id_address2_type>',
					...);
		*/
		function get_addresses (  )
		{
			$return = array();
			
			while(list(,$id_address) = each($this->addresses['id_address']['values']) and
				list(,$id_address_type) = each($this->addresses['id_typeof_company_address']['values']))
			{
				$return[$id_address] = $id_address_type;				
			}
			
			return $return;
		}
	
		/*!
		
			@function get_connections
			@abstract Returns the Company's Connections
			@author Raphael Derosso Pereira
		
			@return array Format:
				$return = array(
					'<id_connection1>' => '<id_connection1_type>',
					'<id_connection2>' => '<id_connection2_type>',
					...);
		*/
		function get_connections (  )
		{
			$return = array();
			
			while(list(,$id_connection) = each($this->connections['id_connection']['values']) and
				list(,$id_connection_type) = each($this->connections['id_typeof_company_connection']['values']))
			{
				$return[$id_connection] = $id_connection_type;				
			}
			
			return $return;
		}

		/*!
		
			@function get_contacts
			@abstract Returns the Company's Contacts IDs and
				bussiness info
			@author Raphael Derosso Pereira
		
			@return array Format:
				$return = array(
					'<id_contact1>' => array(
						'title'			=> '<title>',
						'department'	=> '<department>'
					),
					'<id_contact2>' => array(
						'title'			=> '<title>',
						'department'	=> '<department>'
					),
					...
				);
		*/
		function get_contacts (  )
		{
			$return = array();
			
			while(list(,$id_contact) = each($this->contacts['id_contact']['values']) and
				list(,$title) = each($this->contacts['title']['values']) and
				list(,$department) = each($this->contacts['department']['values']))
			{
				$return[$id_contact] = array(
					'title'			=> $title,
					'department'	=> $department);		
			}
			
			return $return;
		}
	
	
		/*********************************************************************\
		 *                   Methods to Alter Data                           *
		\*********************************************************************/

		/*!
		
			@function set_company_name
			@abstract Sets the Company's Name
			@author Raphael Derosso Pereira
			@param string $name The new Contact's Name 
		
		*/
		function set_company_name ( $name )
		{
			$this->main_fields['company_name']['value'] = $name;
			$this->manage_fields($this->main_fields['company_name'], 'changed');
		}
	
		/*!
		
			@function set_company_notes
			@abstract Sets the Company's Notes
			@author Raphael Derosso Pereira
			@param string $name The new Company's Name 
		
		*/
		function set_company_notes ( $notes )
		{
			$this->main_fields['company_notes']['value'] = $notes;
			$this->manage_fields($this->main_fields['company_notes'], 'changed');
		}

		/*!
		
			@function set_legal
			@abstract Sets on of this Company's Legal
			@author Raphael Derosso Pereira
			@param integer $id_legal The Company's Legal ID
			@param integer $id_type The new Company's Legal type ID
			@param string $legal_info The new Company's Legal information name
			@param string $legal_info_value The new Company's Legal information
				value 
		
		*/
		function set_legal ( $id_legal, $id_type, $legal_info_name, $legal_info_value )
		{
			if (($pos = array_search($id_legal, $this->legals['id_legal']['values'])))
			{
				$this->legals['id_typeof_company_legal']['values'][$pos] = $id_type;
				$this->legals['legal_info_name']['values'][$pos] = $legal_info_name;
				$this->legals['legal_info_value']['values'][$pos] = $legal_info_value;
				
				$this->manage_fields($this->legals['id_typeof_company_legal'], 'changed', $pos);
				$this->manage_fields($this->legals['legal_info'], 'changed', $pos);
				$this->manage_fields($this->legals['legal_value'], 'changed', $pos);
				
				return;
			}
			
			array_push($this->legals['id_company']['values'], &$this->id);
			array_push($this->legals['id_legal']['values'], $id_legal);
			array_push($this->legals['id_typeof_company_legal']['values'], $id_legal);
			array_push($this->legals['legal_info_name']['values'], $legal_info_name);
			array_push($this->legals['legal_info_value']['values'], $legal_info_value);
			
			$this->manage_fields($this->legals['id_company']['values'], 'changed', 'new');
			$this->manage_fields($this->legals['id_legal']['values'], 'changed', 'new');
			$this->manage_fields($this->legals['id_typeof_company_legal'], 'changed', 'new');
			$this->manage_fields($this->legals['legal_info'], 'changed', 'new');
			$this->manage_fields($this->legals['legal_value'], 'changed', 'new');
		}
	
		/*!
		
			@function set_relation
			@abstract Sets the company's Relation
			@author Raphael Derosso Pereira
			@param integer $id_related The new company's Relation type ID
			@param integer $id_type The new company's Relation ID 
		
		*/
		function set_relation ( $id_related, $id_type )
		{
			if (($pos = array_search($id_related, $this->relations['id_related']['values'])))
			{
				$this->relations['id_typeof_company_relation']['values'][$pos] = $id_type;
				$this->manage_fields($this->relations['id_typeof_company_relation'], 'changed', $pos);
				
				return;
			}
			
			array_push($this->relations['id_company']['values'], &$this->id);
			array_push($this->relations['id_related']['values'], $id_related);
			array_push($this->relations['id_typeof_company_relation']['values'], $id_type);
			
			$this->manage_fields($this->relations['id_company']['values'], 'changed', 'new');
			$this->manage_fields($this->relations['id_typeof_company_relation'], 'changed', 'new');
			$this->manage_fields($this->relations['id_related'], 'changed', 'new');
		}
	
		/*!
		
			@function set_address
			@abstract Sets the company's Address
			@author Raphael Derosso Pereira
			@param integer $id_address The new company's Address ID 
			@param integer $id_type The new company's Address type ID
		
		*/
		function set_address ( $id_address, $id_type )
		{
			if (($pos = array_search($id_address, $this->addresses['id_address']['values'])))
			{
				$this->addresses['id_typeof_company_address']['values'][$pos] = $id_type;
				$this->manage_fields($this->addresses['id_typeof_company_address'], 'changed', $pos);
				
				return;
			}
			
			array_push($this->addresses['id_company']['values'], &$this->id);
			array_push($this->addresses['id_address']['values'], $id_address);
			array_push($this->addresses['id_typeof_company_address']['values'], $id_type);
			
			$this->manage_fields($this->addresses['id_company']['values'], 'changed', 'new');
			$this->manage_fields($this->addresses['id_typeof_company_address'], 'changed', 'new');
			$this->manage_fields($this->addresses['id_address'], 'changed', 'new');
		}
	
		/*!
		
			@function set_connection
			@abstract Sets the company's Connection
			@author Raphael Derosso Pereira
			@param integer $id_connection The new company's Connection ID 
			@param integer $id_type The new company's Connection type ID
		
		*/
		function set_connection ( $id_connection, $id_type )
		{
			if (($pos = array_search($id_connection, $this->connections['id_connection']['values'])))
			{
				$this->connections['id_typeof_company_connection']['values'][$pos] = $id_type;
				$this->manage_fields($this->connections['id_typeof_company_connection'], 'changed', $pos);
				
				return;
			}
			
			array_push($this->connections['id_company']['values'], &$this->id);
			array_push($this->connections['id_connection']['values'], $id_connection);
			array_push($this->connections['id_typeof_company_connection']['values'], $id_type);
			
			$this->manage_fields($this->connections['id_company']['values'], 'changed', 'new');
			$this->manage_fields($this->connections['id_typeof_company_connection'], 'changed', 'new');
			$this->manage_fields($this->connections['id_connection'], 'changed', 'new');
		}
	
		/*!
		
			@function set_contact
			@abstract Sets the Company's Contacts information
			@author Raphael Derosso Pereira
			@param integer $id_contact The new Company's Contac ID
			@param string $title The new Contact's Title on this Company
			@param string $department The new Contact's Department on this 
				Company 
		
		*/
		function set_contact ( $id_contact, $title, $department )
		{
			if (($pos = array_search($id_contact, $this->contacts['id_contact']['values'])))
			{
				$this->contacts['title']['values'][$pos] = $title;
				$this->contacts['department']['values'][$pos] = $department;
				
				$this->manage_fields($this->contacts['title'], 'changed', $pos);
				$this->manage_fields($this->contacts['department'], 'changed', $pos);
				
				return;
			}
			
			array_push($this->contacts['id_company']['values'], &$this->id);
			array_push($this->contacts['id_contact']['values'], $id_contact);
			array_push($this->contacts['title']['values'], $title);
			array_push($this->contacts['department']['values'], $department);
			
			$this->manage_fields($this->contacts['id_company']['values'], 'changed', 'new');
			$this->manage_fields($this->contacts['title'], 'changed', 'new');
			$this->manage_fields($this->contacts['department'], 'changed', 'new');
		}
	


		/*********************************************************************\
		 *                   Methods to Remove Data                          *
		\*********************************************************************/

	
		/*!
		
			@function remove_relation
			@abstract Remove one Company's Relation
			@author Raphael Derosso Pereira
			@param integer $id_relation The ID of the relation to be removed 
		
		*/
		function remove_relation ( $id_related )
		{
			if (($pos = array_search($id_related, $this->relations['id_related']['values'])))
			{
				$this->manage_fields($this->relations['id_related'], 'deleted', $pos);
				$this->manage_fields($this->relations['id_typeof_company_relation'], 'deleted', $pos);
				
				return true;
			}
			
			return false;
		}
	
		/*!
		
			@function remove_address
			@abstract Remove one Company's Address
			@author Raphael Derosso Pereira
			@param integer $id_address The ID of the address to be removed 
		
		*/
		function remove_address ( $id_address )
		{
			if (($pos = array_search($id_address, $this->addresses['id_address']['values'])))
			{
				$this->manage_fields($this->addresses['id_address'], 'deleted', $pos);
				$this->manage_fields($this->addresses['id_typeof_company_address'], 'deleted', $pos);
				
				return true;
			}
			
			return false;
		}
	
		/*!
		
			@function remove_connection
			@abstract Remove one Company's Connection
			@author Raphael Derosso Pereira
			@param integer $id_connection The ID of the connection to be removed 
		
		*/
		function remove_connection ( $id_connection )
		{
			if (($pos = array_search($id_connection, $this->connections['id_connection']['values'])))
			{
				$this->manage_fields($this->connections['id_connection'], 'deleted', $pos);
				$this->manage_fields($this->connections['id_typeof_company_connection'], 'deleted', $pos);
				
				return true;
			}
			
			return false;
		}
	
		/*!
		
			@function remove_contact
			@abstract Remove one Company's Contact
			@author Raphael Derosso Pereira
			@param integer $id_contact The ID of the contact to be removed 
		
		*/
		function remove_contact ( $id_contact )
		{
			if (($pos = array_search($id_contact, $this->contacts['id_contact']['values'])))
			{
				$this->manage_fields($this->contacts['id_contact'], 'deleted', $pos);
				$this->manage_fields($this->contacts['title'], 'deleted', $pos);
				$this->manage_fields($this->contacts['department'], 'deleted', $pos);
				
				return true;
			}
			
			return false;
		}
	

		/*!
		
			@function remove_legal
			@abstract Remove one Company's Legal
			@author Raphael Derosso Pereira
			@param integer $id_company The ID of the legal information to be removed 
		
		*/
		function remove_legal ( $id_type )
		{
			if (($pos = array_search($id_type, $this->contacts['id_contact']['values'])))
			{
				$this->manage_fields($this->legals['id_company']['values'], 'deleted', $pos);
				$this->manage_fields($this->legals['id_legal']['values'], 'deleted', $pos);
				$this->manage_fields($this->legals['id_typeof_company_legal'], 'deleted', $pos);
				$this->manage_fields($this->legals['legal_info'], 'deleted', $pos);
				$this->manage_fields($this->legals['legal_value'], 'deleted', $pos);
				
				return true;
			}
			
			return false;
		}
	
	}
?>
