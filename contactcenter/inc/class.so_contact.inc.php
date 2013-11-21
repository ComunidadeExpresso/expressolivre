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

	/**
	 * This class handles the Contact DB Table
	 *
	 */

	include_once("class.so_main.inc.php");

	class so_contact extends so_main {
		
		function so_contact ( $id_contact = false)
		{
			$this->init();
						
			$this->main_fields = array(
				'id_contact'	=> array(
					'name'		=> 'id_contact',
					'type'		=> 'primary',
					'state'		=> 'empty',
					'value'		=> &$this->id),
				'id_owner'		=> array(
					'name'		=> 'id_owner',		
					'type'		=> false,
					'state'		=> 'empty',
					'value'		=> false),
				'id_status'		=> array(
					'name'		=> 'id_status',
					'type'		=> 'foreign',
					'association'	=> array(
						'table'			=> 'phpgw_cc_status',
						'field'			=> 'id_status'),
					'state'		=> 'empty',
					'value'		=> false),
				'photo'			=> array(
					'name'		=> 'photo',
					'type'		=> false,
					'state'		=> 'empty',
					'value'		=> false),
				'alias'			=> array(
					'name'		=> 'alias',
					'type'		=> false,
					'state'		=> 'empty',
					'value'		=> false),
				'web_page'			=> array(
					'name'		=> 'web_page',
					'type'		=> false,
					'state'		=> 'empty',
					'value'		=> false),
				'corporate_name'			=> array(
					'name'		=> 'corporate_name',
					'type'		=> false,
					'state'		=> 'empty',
					'value'		=> false),
				'job_title'			=> array(
					'name'		=> 'job_title',
					'type'		=> false,
					'state'		=> 'empty',
					'value'		=> false),
				'department'			=> array(
					'name'		=> 'department',
					'type'		=> false,
					'state'		=> 'empty',
					'value'		=> false),
				'id_prefix'		=> array(
					'name'			=> 'id_prefix',
					'type'			=> 'foreign',
					'association'	=> array(
						'table'			=> 'phpgw_cc_prefixes',
						'field'			=> 'id_prefix'),
					'state'		=> 'empty',
					'value'		=> false),
				'given_names'	=> array(
					'name'		=> 'given_names',
					'type'		=> false,
					'state'		=> 'empty',
					'value'		=> false),
				'family_names'	=> array(
					'name'		=> 'family_names',
					'type'		=> false,
					'state'		=> 'empty',
					'value'		=> false),
				'names_ordered'	=> array(
					'name'		=> 'names_ordered',
					'type'		=> false,
					'state'		=> 'empty',
					'value'		=> false),
				'id_suffix'		=> array(
					'name'			=> 'id_suffix',
					'type'			=> 'foreign',
					'association'	=> array(
						'table'			=> 'phpgw_cc_suffixes',
						'field'			=> 'id_suffix'),
					'state'		=> 'empty',
					'value'		=> false),
				'birthdate'		=> array(
					'name'		=> 'birthdate',
					'type'		=> false,
					'state'		=> 'empty',
					'value'		=> false),
				'sex'			=> array(
					'name'		=> 'sex',
					'type'		=> false,
					'state'		=> 'empty',
					'value'		=> false),
				'pgp_key'		=> array(
					'name'		=> 'pgp_key',
					'type'		=> false,
					'state'		=> 'empty',
					'value'		=> false),
				'notes'			=> array(
					'name'		=> 'notes',
					'type'		=> false,
					'state'		=> 'empty',
					'value'		=> false),
				'is_global'		=> array(
					'name'		=> 'is_global',
					'type'		=> false,
					'state'		=> 'empty',
					'value'		=> false),
				'last_status'		=> array(
					'name'		=> 'last_status',
					'type'		=> false,
					'state'		=> 'empty',
					'value'		=> false),
				'last_update'		=> array(
					'name'		=> 'last_update',
					'type'		=> false,
					'state'		=> 'empty',
					'value'		=> false));
					
			$this->companies	= array(
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
						
			$this->addresses	= array(
				'id_contact'	=> array(
					'name'			=> 'id_contact',
					'type'			=> array('foreign', 'primary'),
					'association'	=> array(
						'table'			=> 'phpgw_cc_contact',
						'field'			=> 'id_contact'),
					'states' 		=>	array(),
					'values'		=> array()),
				'id_address'	=> array(
					'name'			=> 'id_address',
					'type'			=> array('foreign', 'primary'),
					'association'	=> array(
						'table'			=> 'phpgw_cc_addresses',
						'field'			=> 'id_address'),
					'states' 		=>	array(),
					'values'		=> array()),
				'id_typeof_contact_address'	=> array(
					'name'			=> 'id_typeof_contact_address',
					'type'			=> 'foreign',
					'association'	=> array(
						'table'			=> 'phpgw_cc_typeof_ct_addrs',
						'field'			=> 'id_typeof_contact_address'),
					'states' 		=>	array(),
					'values'		=> array()));
				
				
			$this->connections	= array(
				'id_contact'	=> array(
					'name'			=> 'id_contact',
					'type'			=> array('foreign', 'primary'),
					'association'	=> array(
						'table'			=> 'phpgw_cc_contact',
						'field'			=> 'id_contact'),
					'states' 		=>	array(),
					'values'		=> array()),
				'id_connection'	=> array(
					'name'			=> 'id_connection',
					'type'			=> array('foreign', 'primary'),
					'association'	=> array(
						'table'			=> 'phpgw_cc_connections',
						'field'			=> 'id_connection'),
					'states' 		=>	array(),
					'values'		=> array()),
				'id_typeof_contact_connection'	=> array(
					'name'			=> 'id_typeof_contact_connection',
					'type'			=> array('foreign', 'primary'),
					'association'	=> array(
						'table'			=> 'phpgw_cc_typeof_ct_conns',
						'field'			=> 'id_typeof_contact_connection'),
					'states' 		=>	array(),
					'values'		=> array()));
			
			$this->relations	= array(
				'id_contact'	=> array(
					'name'			=> 'id_contact',
					'type'			=> array('foreign', 'primary'),
					'association'	=> array(
						'table'			=> 'phpgw_cc_contact',
						'field'			=> 'id_contact'
					),
					'states' 		=>	array(),
					'values'	=> array()
				),
				'id_related'	=> array(
					'name'			=> 'id_related',
					'type'			=> array('foreign', 'primary'),
					'association'	=> array(
						'table'			=> 'phpgw_cc_contact',
						'field'			=> 'id_contact'
					),
					'states' 		=>	array(),
					'values'	=> array()
				),
				'id_typeof_contact_relation'	=> array(
					'name'			=> 'id_typeof_contact_relation',
					'type'			=> array('foreign', 'primary'),
					'association'	=> array(
						'table'			=> 'phpgw_cc_typeof_ct_rels',
						'field'			=> 'id_typeof_contact_relation'
					),
					'states' 		=>	array(),
					'values'	=> array()
				)
			);
			$this->relateds	= array(
					'id_related'	=> array(
						'name'			=> 'id_related',
						'type'			=> array('foreign', 'primary'),
						'association'	=> array(
							'table'			=> 'phpgw_cc_contact',
							'field'			=> 'id_contact'
						),
						'states' 		=>	array(),
						'values'	=> array()
					),
					'id_contact'	=> array(
						'name'			=> 'id_contact',
						'type'			=> array('foreign', 'primary'),
						'association'	=> array(
							'table'			=> 'phpgw_cc_contact',
							'field'			=> 'id_contact'
						),
						'states' 		=>	array(),
						'values'	=> array()
					),
					'id_typeof_contact_relation'	=> array(
						'name'			=> 'id_typeof_contact_relation',
						'type'			=> array('foreign', 'primary'),
						'association'	=> array(
							'table'			=> 'phpgw_cc_typeof_ct_rels',
							'field'			=> 'id_typeof_contact_relation'
						),
						'states' 		=>	array(),
						'values'	=> array()
					)
				);
				

			$this->db_tables = array(
				'phpgw_cc_contact'	=> array(
					'type'		=> 'main',
					'keys'		=> array(
						'primary' => array(&$this->main_fields['id_contact']),
						'foreign' => array(
							&$this->main_fields['id_status'],
							&$this->main_fields['id_prefix'],
							&$this->main_fields['id_suffix'])),
					'fields' 	=> & $this->main_fields),
				'phpgw_cc_contact_company'	=> array(
					'type'		=> 'multi',
					'keys'		=> array(
						'primary' => array(
							&$this->companies['id_contact'],
							&$this->companies['id_company']),
						'foreign' => array(
							&$this->companies['id_contact'],
							&$this->companies['id_company'])),
					'fields' 	=> & $this->companies),
				'phpgw_cc_contact_rels'	=> array(
					'type'		=> 'multi',
					'keys'		=> array(
						'primary' => array(
							&$this->relations['id_contact'],
							&$this->relations['id_related']),
						'foreign' => array(
							&$this->relations['id_contact'],
							&$this->relations['id_related'])),
					'fields' 	=> & $this->relations),
				'phpgw_cc_contact_addrs'	=> array(
					'type'		=> 'multi',
					'keys'		=> array(
						'primary' => array(
							&$this->addresses['id_contact'],
							&$this->addresses['id_address']),
						'foreign' => array(
							&$this->addresses['id_contact'],
							&$this->addresses['id_address'],
							&$this->addresses['id_typeof_contact_address'])),
					'fields' 	=> & $this->addresses),
				'phpgw_cc_contact_conns'	=> array(
					'type'		=> 'multi',
					'keys'		=> array(
						'primary' => array(
							&$this->connections['id_contact'],
							&$this->connections['id_connection']),
						'foreign' => array(
							&$this->connections['id_contact'],
							&$this->connections['id_connection'],
							&$this->connections['id_typeof_contact_connection'])),
					'fields' 	=> & $this->connections));
					

			if ($id_contact)
			{
				$this->id = $id_contact;
				if (!$this->checkout($id_contact))
				{
					$this->reset_values();
					$this->state = 'new';
				}
				$temp = $this->db_tables;
			
				$this->db_tables = array(); //Limpo o array para fazer checkout apenas dos contatos relacionados
				$this->db_tables['phpgw_cc_contact_rels'] =  array(
						'type'		=> 'multi',
						'keys'		=> array(
							'primary' => array(
								&$this->relateds['id_related']),
								&$this->relateds['id_contact'],
							'foreign' => array(
								&$this->relateds['id_related'])),
								&$this->relateds['id_contact'],
						'fields' 	=> & $this->relateds);
				if (!$this->checkout($id_contact))
				{
					$this->reset_values();
					$this->state = 'new';
				}
				$this->db_tables = $temp; //Coloco o valor do array antigo.
			}
			else
			{
				$this->state = 'new';
			}
						
		}
	
		/*********************************************************************\
		 *                   Methods to Obtain Data                          *
		\*********************************************************************/
	
		/*!
		
			@function get_photo
			@abstract Returns the Contact's photo binary string
			@author Raphael Derosso Pereira
		
		*/
		function get_photo (  )
		{
			return $this->main_fields['photo']['value'];
		}
	
		/*!
		
			@function get_alias
			@abstract Returns the Contact alias
			@author Raphael Derosso Pereira
		
		*/
		function get_alias (  )
		{
			return $this->main_fields['alias']['value'];
		}
	
		/*!
		
			@function get_prefix
			@abstract Returns the Contact prefix ID
			@author Raphael Derosso Pereira
		
		*/
		function get_prefix (  )
		{
			return $this->main_fields['id_prefix']['value'];
		}
		
		/*!
		
			@function get_corporate_name
			@abstract Returns the Contact corporate_name
			@author David Buarque
		
		*/
		function get_corporate_name (  )
		{
			return $this->main_fields['corporate_name']['value'];
		}
		
		/*!
		
			@function get_job_title
			@abstract Returns the Contact job_title
			@author David Buarque
		
		*/
		function get_job_title (  )
		{
			return $this->main_fields['job_title']['value'];
		}
		
		/*!
		
			@function get_department
			@abstract Returns the Contact department
			@author David Buarque
		
		*/
		function get_department (  )
		{
			return $this->main_fields['department']['value'];
		}
		
		/*!
		
			@function get_web_page
			@abstract Returns the Contact web_page
			@author David Buarque
		
		*/
		function get_web_page (  )
		{
			return $this->main_fields['web_page']['value'];
		}
	
		/*!
		
			@function get_given_names
			@abstract Returns the Contact's given names
			@author Raphael Derosso Pereira
		
		*/
		function get_given_names (  )
		{
			return $this->main_fields['given_names']['value'];
		}
	
		/*!
		
			@function get_family_names
			@abstract Returns the Contact's family names
			@author Raphael Derosso Pereira
		
		*/
		function get_family_names (  )
		{
			return $this->main_fields['family_names']['value'];
		}
	
		/*!
		
			@function get_names_ordered
			@abstract Returns the Contact's names ordered
			@author Raphael Derosso Pereira
		
		*/
		function get_names_ordered (  )
		{
			return $this->main_fields['names_ordered']['value'];
		}
	
		/*!
		
			@function get_suffix
			@abstract Returns the Contact's suffix
			@author Raphael Derosso Pereira
		
		*/
		function get_suffix (  )
		{
			return $this->main_fields['id_suffix']['value'];
		}
	
		/*!
		
			@function get_birthdate
			@abstract Returns the Contact's birthdata
			@author Raphael Derosso Pereira
		
		*/
		function get_birthdate (  )
		{
			return $this->main_fields['birthdate']['value'];
		}
	
		/*!
		
			@function get_sex
			@abstract Returns the Contact's sex
			@author Raphael Derosso Pereira
		
		*/
		function get_sex (  )
		{
			return $this->main_fields['sex']['value'];
		}
	
		/*!
		
			@function get_pgp_key
			@abstract Returns the Contact's PGP Key
			@author Raphael Derosso Pereira
		
		*/
		function get_pgp_key (  )
		{
			return $this->main_fields['pgp_key']['value'];
		}
	
		/*!
		
			@function get_notes
			@abstract Returns the Contact's notes
			@author Raphael Derosso Pereira
		
		*/
		function get_notes (  )
		{
			return $this->main_fields['notes']['value'];
		}
	
		/*!
		
			@function get_status
			@abstract Returns the Contact's status ID
			@author Raphael Derosso Pereira

		*/
		function get_status (  )
		{
			return $this->main_fields['status']['value'];
		}
	
		/*!
		
			@function get_relations
			@abstract Returns the Contact's Relations
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
			
			reset($this->relations['id_related']['values']);
			reset($this->relations['id_typeof_contact_relation']['values']);
			while(list(,$id_related) = each($this->relations['id_related']['values']) and
				list(,$id_relation_type) = each($this->relations['id_typeof_contact_relation']['values']))
			{
				$return[$id_related] = $id_relation_type;				
			}
			
			return $return;
		}
	
		function get_relateds() {
			$return = array();
			
			reset($this->relateds['id_contact']['values']);
			reset($this->relateds['id_typeof_contact_relation']['values']);
			while(list(,$id_relation) = each($this->relateds['id_contact']['values']) and
				list(,$id_relation_type) = each($this->relateds['id_typeof_contact_relation']['values']))
			{
				$return[$id_relation] = $id_relation_type;
			}
			return $return;
		}
	
		/*!
		
			@function get_addresses
			@abstract Returns the Contact's Addresses
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
			
			reset($this->addresses['id_address']['values']);
			reset($this->addresses['id_typeof_contact_address']['values']);
			while(list(,$id_address) = each($this->addresses['id_address']['values']) and
				list(,$id_address_type) = each($this->addresses['id_typeof_contact_address']['values']))
			{
				$return[$id_address] = $id_address_type;				
			}
			
			return $return;
		}
	
		/*!
		
			@function get_connections
			@abstract Returns the Contact's Connections
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
			
			reset($this->connections['id_connection']['values']);
			reset($this->connections['id_typeof_contact_connection']['values']);
			while(list(,$id_connection) = each($this->connections['id_connection']['values']) and
				list(,$id_connection_type) = each($this->connections['id_typeof_contact_connection']['values']))
			{
				$return[$id_connection] = $id_connection_type;				
			}
			
			return $return;
		}
	
		/*!
		
			@function get_companies
			@abstract Returns the Contact's Companies IDs and
				bussiness info
			@author Raphael Derosso Pereira
		
			@return array Format:
				$return = array(
					'<id_company1>' => array(
						'title'			=> '<title>',
						'department'	=> '<department>'
					),
					'<id_company2>' => array(
						'title'			=> '<title>',
						'department'	=> '<department>'
					),
					...
				);
				
		*/
		function get_companies (  )
		{
			$return = array();
			
			reset($this->companies['id_company']['values']);
			reset($this->companies['title']['values']);
			reset($this->companies['department']['values']);
			reset($this->companies['default_company']['values']);
			reset($this->companies['default_contact']['values']);
			while(list(,$id_company) = each($this->companies['id_company']['values']) and
				list(,$title) = each($this->companies['title']['values']) and
				list(,$department) = each($this->companies['department']['values']) and
				list(,$default_company) = each($this->companies['default_company']['values']) and
				list(,$default_contact) = each($this->companies['default_contact']['values']))
			{
				$return[$id_company] = array(
					'title'           => $title,
					'department'      => $department,
					'default_company' => $default_company,
					'default_contact' => $default_contact,
				);
			}
			
			return $return;
		}
	
		/*********************************************************************\
		 *                   Methods to Alter Data                           *
		\*********************************************************************/

	
		/*!
		
			@function set_photo
			@abstract Sets the Contact's Photo binary string
			@author Raphael Derosso Pereira
			@param string $photo The binary photo string 
		
		*/
		function set_photo ( $photo )
		{
			$this->main_fields['photo']['value'] = $photo;
			$this->manage_fields($this->main_fields['photo'], 'changed');
		}
	
		/*!
		
			@function set_alias
			@abstract Sets the Contact's Alias
			@author Raphael Derosso Pereira
			@param string $alias The new Contact alias 
		
		*/
		function set_alias ( $alias )
		{
			$this->main_fields['alias']['value'] = $alias;
			$this->manage_fields($this->main_fields['alias'], 'changed');
		}
		
		/*!
		
			@function set_corporate_name
			@abstract Sets the Contact's corporate_name
			@author David Buaque
			@param string $corporate_name The new Contact corporate_name
		
		*/
		function set_corporate_name ( $corporate_name )
		{
			$this->main_fields['corporate_name']['value'] = $corporate_name;
			$this->manage_fields($this->main_fields['corporate_name'], 'changed');
		}
		
		/*!
		
			@function set_job_title
			@abstract Sets the Contact's job_title
			@author David Buaque
			@param string $alias The new Contact job_title
		
		*/
		function set_job_title ( $job_title )
		{
			$this->main_fields['job_title']['value'] = $job_title;
			$this->manage_fields($this->main_fields['job_title'], 'changed');
		}
		
		/*!
		
			@function set_department
			@abstract Sets the Contact's department
			@author David Buaque
			@param string $alias The new Contact department 
		
		*/
		function set_department ( $department )
		{
			$this->main_fields['department']['value'] = $department;
			$this->manage_fields($this->main_fields['department'], 'changed');
		}
		
		/*!
		
			@function set_web_page
			@abstract Sets the Contact's web_page
			@author David Buaque
			@param string $alias The new Contact web_page 
		
		*/
		function set_web_page ( $web_page )
		{
			$this->main_fields['web_page']['value'] = $web_page;
			$this->manage_fields($this->main_fields['web_page'], 'changed');
		}
	
		/*!
		
			@function set_id_prefix
			@abstract Sets the Contact's Prefix
			@author Raphael Derosso Pereira
			@param string $id_prefix The new Contact prefix ID 
		
		*/
		function set_id_prefix ( $id_prefix )
		{
			$this->main_fields['prefix']['value'] = $id_prefix;
			$this->manage_fields($this->main_fields['prefix'], 'changed');
		}
	
		/*!
		
			@function set_given_names
			@abstract Sets the Contact's Given Names
			@author Raphael Derosso Pereira
			@param string $names The new Contact's Given Names 
		
		*/
		function set_given_names ( $names )
		{
			$this->main_fields['given_names']['value'] = $names;
			$this->manage_fields($this->main_fields['given_names'], 'changed');
		}
	
		/*!
		
			@function set_family_names
			@abstract Sets the Contact's Family Names
			@author Raphael Derosso Pereira
			@param string $names The new Contact's Family Names 
		
		*/
		function set_family_names ( $names )
		{
			$this->main_fields['family_names']['value'] = $names;
			$this->manage_fields($this->main_fields['family_names'], 'changed');
		}
	
		/*!
		
			@function set_names_ordered
			@abstract Sets the Contact's Names Ordered
			@author Raphael Derosso Pereira
			@param string $names The new Contact's Names Ordered 
		
		*/
		function set_names_ordered ( $names )
		{
			$this->main_fields['names_ordered']['value'] = $names;
			$this->manage_fields($this->main_fields['names_ordered'], 'changed');
		}
	
		/*!
		
			@function set_id_suffix
			@abstract Sets the Contact's Sulfix
			@author Raphael Derosso Pereira
			@param string $id_suffix The new Contact's Sulfix 
		
		*/
		function set_id_suffix ( $id_suffix )
		{
			$this->main_fields['suffix']['value'] = $names;
			$this->manage_fields($this->main_fields['suffix'], 'changed');
		}
	
		/*!
		
			@function set_birthdate
			@abstract Sets the Contact's Birthdate
			@author Raphael Derosso Pereira
			@param string $date The new Contact's Birthdate 
		
		*/
		function set_birthdate ( $date )
		{
			$this->main_fields['birthdate']['value'] = $names;
			$this->manage_fields($this->main_fields['birthdate'], 'changed');
		}
	
		/*!
		
			@function set_sex
			@abstract Sets the Contact's Sex
			@author Raphael Derosso Pereira
			@param string $sex The new Contact's Sex 
		
		*/
		function set_sex ( $sex )
		{
			$this->main_fields['birthdate']['value'] = $names;
			$this->manage_fields($this->main_fields['birthdate'], 'changed');
		}
	
		/*!
		
			@function set_pgp_key
			@abstract Sets the Contact's PGP Key
			@author Raphael Derosso Pereira
			@param string $pgp_key The new Contact's PGP Key 
		
		*/
		function set_pgp_key ( $pgp_key )
		{
			$this->main_fields['birthdate']['value'] = $names;
			$this->manage_fields($this->main_fields['birthdate'], 'changed');
		}
	
		/*!
		
			@function set_notes
			@abstract Sets the Contact's Notes
			@author Raphael Derosso Pereira
			@param string $notes The new Contact's Notes 
		
		*/
		function set_notes ( $notes )
		{
			$this->main_fields['birthdate']['value'] = $names;
			$this->manage_fields($this->main_fields['birthdate'], 'changed');
		}
	
		/*!
		
			@function set_status
			@abstract Change the Contact's set Status
			@author Raphael Derosso Pereira
			@param integer $id_status The new Contact's set Status 
		
		*/
		function set_id_status ( $id_status )
		{
			$this->main_fields['id_status']['value'] = $names;
			$this->manage_fields($this->main_fields['id_status'], 'changed');
		}
	
		/*!
		
			@function set_relation
			@abstract Sets the Contact's Relation
			@author Raphael Derosso Pereira
			@param integer $id_related The new Contact's Relation type ID
			@param integer $id_type The new Contact's Relation ID 
		
		*/
		function set_relation ( $id_related, $id_type )
		{
			if (($pos = array_search($id_related, $this->relations['id_related']['values'])) !== false)
			{
				$this->relations['id_typeof_contact_relation']['values'][$pos] = $id_type;
				$this->manage_fields($this->relations['id_typeof_contact_relation'], 'changed', $pos);
				
				return;
			}
			
			array_push($this->relations['id_contact']['values'], & $this->id);
			array_push($this->relations['id_related']['values'], $id_related);
			array_push($this->relations['id_typeof_contact_relation']['values'], $id_type);
			
			$this->manage_fields($this->relations['id_typeof_contact_relation'], 'new', 'new');
			$this->manage_fields($this->relations['id_related'], 'new', 'new');
			$this->manage_fields($this->relations['id_contact'], 'new', 'new');
		}
	
		/*!
			@function set_related
			@abstract Sets the Contact's related
			@author Raphael Derosso Pereira
			@param integer $id_related The new Contact's Relation type ID
			@param integer $id_type The new Contact's Relation ID 

		*/
		function set_relateds ( $relations , $id_type )
		{
			$id = $this->id;
			$db = $GLOBALS['phpgw']->db;

			//Remove todos os contatos relacionados
			$query = "delete from phpgw_cc_contact_rels where id_related=$id and id_typeof_contact_relation=1";
			
			if (!$db->query($query, __LINE__, __FILE__)) {
				return false;
			}
			foreach($relations as $id_relation) {
				$query = "insert into phpgw_cc_contact_rels (id_contact,id_related,id_typeof_contact_relation) values ($id_relation,$id,$id_type)";
				
				if (!$db->query($query, __LINE__, __FILE__)) {
					return false;
				}
			}
			return true;
		}
	
		/*!
		
			@function set_address
			@abstract Sets the Contact's Address
			@author Raphael Derosso Pereira
			@param integer $id_address The new Contact's Address ID 
			@param integer $id_type The new Contact's Address type ID
		
		*/
		function set_address ( $id_address, $id_type )
		{
			if (($pos = array_search($id_address, $this->addresses['id_address']['values'])) !== false)
			{
				$this->addresses['id_typeof_contact_address']['values'][$pos] = $id_type;
				$this->manage_fields($this->addresses['id_typeof_contact_address'], 'changed', $pos);
				
				return;
			}
			
			array_push($this->addresses['id_contact']['values'], & $this->id);
			array_push($this->addresses['id_address']['values'], $id_address);
			array_push($this->addresses['id_typeof_contact_address']['values'], $id_type);
			
			$this->manage_fields($this->addresses['id_typeof_contact_address'], 'new', 'new');
			$this->manage_fields($this->addresses['id_address'], 'new', 'new');
			$this->manage_fields($this->addresses['id_contact'], 'new', 'new');
			
		}
	
		/*!
		
			@function set_connection
			@abstract Sets the Contact's Connection
			@author Raphael Derosso Pereira
			@param integer $id_connection The new Contact's Connection ID 
			@param integer $id_type The new Contact's Connection type ID
		
		*/
		function set_connection ( $id_connection, $id_type )
		{
			if (($pos = array_search($id_connection, $this->connections['id_connection']['values'])) !== false)
			{
				$this->connections['id_typeof_contact_connection']['values'][$pos] = $id_type;
				$this->manage_fields($this->connections['id_typeof_contact_connection'], 'changed', $pos);
				
				return;
			}
			
			array_push($this->connections['id_contact']['values'], & $this->id);
			array_push($this->connections['id_connection']['values'], $id_connection);
			array_push($this->connections['id_typeof_contact_connection']['values'], $id_type);
			
			$this->manage_fields($this->connections['id_typeof_contact_connection'], 'new', 'new');
			$this->manage_fields($this->connections['id_connection'], 'new', 'new');
			$this->manage_fields($this->connections['id_contact'], 'new', 'new');
		}
	
		/*!
		
			@function set_company
			@abstract Sets the Contact's Company and bussiness information
			@author Raphael Derosso Pereira
			@param array $company_info The new Contact's Company Information
		
		*/
		function set_company ( $company_info )
		{
			if (is_array($company_info))
			{
				if (($pos = array_search($company_info['id_company'], $this->companies['id_company']['values'])) !== false)
				{
					$this->companies['title']['values'][$pos] = $company_info['title'];
					$this->companies['department']['values'][$pos] = $company_info['department'];
					$this->companies['default_company']['values'][$pos] = $company_info['default_company'];
					$this->companies['default_contact']['values'][$pos] = $company_info['default_contact'];
					
					$this->manage_fields($this->companies['title'], 'changed', $pos);
					$this->manage_fields($this->companies['department'], 'changed', $pos);
					$this->manage_fields($this->companies['default_company'], 'changed', $pos);
					$this->manage_fields($this->companies['default_contact'], 'changed', $pos);
					
					return true;
				}
				
				array_push($this->companies['id_contact']['values'], & $this->id);
				array_push($this->companies['id_company']['values'], $company_info['id_company']);
				array_push($this->companies['title']['values'], $company_info['title']);
				array_push($this->companies['department']['values'], $company_info['department']);
				array_push($this->companies['default_company']['values'], $company_info['default_company']);
				array_push($this->companies['default_contact']['values'], $company_info['default_contact']);
				
				$this->manage_fields($this->companies['id_company'], 'new', 'new');
				$this->manage_fields($this->companies['id_contact'], 'new', 'new');
				$this->manage_fields($this->companies['title'], 'new', 'new');
				$this->manage_fields($this->companies['department'], 'new', 'new');
				$this->manage_fields($this->companies['default_company'], 'new', 'new');
				$this->manage_fields($this->companies['default_contact'], 'new', 'new');
				
				return true;
			}
			
			return false;
		}


		/*********************************************************************\
		 *                   Methods to Remove Data                          *
		\*********************************************************************/


		/*!
		
			@function remove_relation
			@abstract Remove one Contact's Relation
			@author Raphael Derosso Pereira
			@param integer $id_related The ID of the relation to be removed 
		
		*/
		function remove_relation ( $id_related )
		{
			if (($pos = array_search($id_related, $this->relations['id_related']['values'])) !== false)
			{
				$this->manage_fields($this->relations['id_contact'], 'deleted', $pos);
				$this->manage_fields($this->relations['id_related'], 'deleted', $pos);
				$this->manage_fields($this->relations['id_typeof_contact_relation'], 'deleted', $pos);
				
				return true;
			}
			
			return false;
		}
	
		/*!
		
			@function remove_address
			@abstract Remove one Contact's Address
			@author Raphael Derosso Pereira
			@param integer $id_address The ID of the address to be removed 
		
		*/
		function remove_address ( $id_address )
		{
			if (($pos = array_search($id_address, $this->addresses['id_address']['values'])) !== false)
			{
				$this->manage_fields($this->addresses['id_contact'], 'deleted', $pos);
				$this->manage_fields($this->addresses['id_address'], 'deleted', $pos);
				$this->manage_fields($this->addresses['id_typeof_contact_address'], 'deleted', $pos);
				
				return true;
			}
			
			return false;
		}
	
		/*!
		
			@function remove_connection
			@abstract Remove one Contact's Connection
			@author Raphael Derosso Pereira
			@param integer $id_connection The ID of the connection to be removed 
		
		*/
		function remove_connection ( $id_connection )
		{
			if (($pos = array_search($id_connection, $this->connections['id_connection']['values'])) !== false)
			{
				$this->manage_fields($this->connections['id_contact'], 'deleted', $pos);
				$this->manage_fields($this->connections['id_connection'], 'deleted', $pos);
				$this->manage_fields($this->connections['id_typeof_contact_connection'], 'deleted', $pos);
				
				return true;
			}
			
			return false;
		}
	
		/*!
		
			@function remove_company
			@abstract Remove one Contact's Company
			@author Raphael Derosso Pereira
			@param integer $id_company The ID of the compay to be removed 
		
		*/
		function remove_company ( $id_company )
		{
			if (($pos = array_search($id_company, $this->companies['id_company']['values'])) !== false)
			{
				$this->manage_fields($this->companies['id_contact'], 'deleted', $pos);
				$this->manage_fields($this->companies['id_company'], 'deleted', $pos);
				$this->manage_fields($this->companies['title'], 'deleted', $pos);
				$this->manage_fields($this->companies['department'], 'deleted', $pos);
				
				return true;
			}
			
			return false;
		}
		
	}
?>
