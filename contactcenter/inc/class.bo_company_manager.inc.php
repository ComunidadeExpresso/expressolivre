<?php
  /***************************************************************************\
  * eGroupWare - Contacts Center                                              *
  * http://www.egroupware.org                                                 *
  * Written by:                                                               *
  *  - Raphael Derosso Pereira <raphaelpereira@users.sourceforge.net>         *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/

	include_once('class.abo_catalog.inc.php');

	class bo_company_manager extends abo_catalog
	{
		
		var $fields = array(
			'id_company'    => true,
			'company_name'  => true,
			'company_notes' => true,
			
			/* Array fields */
			'contacts'      => true,
			'relations'     => true,
			'addresses'     => true,
			'connections'   => true,
			'legals'        => true
		);
	
		/*!
		
		 @function bo_contact_manager
		 @abstract Constructor
	 	 @author Raphael Derosso Pereira
	 	 
	 	*/
		function bo_company_manager()
		{
		}		 	
		
		 
		/*********************************************************************\
		 *                Methods to Get Companies Info                      *
		\*********************************************************************/

		/*!
		
		 @function get_single_info
		 @abstract Returns all information requested about one company
		 @author Raphael Derosso Pereira    
		 @param integer $id_company The company ID
		 @param array $fields The array returned by get_fields whith true
		 	on the fields to be taken.
		*/
		function get_single_info ( $id_company, $fields )
		{	
			if (!is_integer($id_company) or !is_array($fields)) 
			{
				if (is_object($GLOBALS['phpgw']->log)) 
				{
					$GLOBALS['phpgw']->log->message(array(
						'text' => 'F-BadcontactcenterParam, wrong get_single_info parameters type.',
						'line' => __LINE__,
						'file' => __FILE__));
					
					$GLOBALS['phpgw']->log->commit();
				}
				else 
				{
					exit('Argument Error on: <br>File:'.__FILE__.'<br>Line:'.__LINE__.'<br>');
				}
			}
			
			// Verify permitions
			$permitions = $this->security->get_permissions($id_company);
			
			if (!$permitions['read'])
			{
				return false;
			}
			
			$company_data = $this->fields;
	
			$company = CreateObject('contactcenter.so_company', $id_company);
			
			foreach ($fields as $field => $trueness)
			{
				if (!$trueness)
				{
					unset($company_data[$field]);
					continue;
				}
					
				switch ($field)
				{
					case 'contacts':
						$company_data['contacts'] = $this->get_contacts($id_company);
						break;
					
					case 'relations':
						$company_data['relations'] = $this->get_relations($id_company);
						break;
					
					case 'addresses':
						$company_data['addresses'] = $this->get_addresses($id_company);
						break;
					
					case 'connections':
						$company_data['connections'] = $this->get_connections($id_company);
						break;
					
					case 'legals':
						$company_data['legals'] = $this->get_legals($id_company);
						break;
					
					default:
						$func_name = 'company->get_'.$field;
						$company_data[$field] = $this->$func_name();
						break;
				}
			}
			
			if (!is_array($company_data))
			{
				return false;
			}
			
			return $company_data;
		}
	
		/*!
		 
		 @function get_multiple_info
		 @abstract Returns multiple companies data into one array
		 @author Raphael Derosso Pereira

		 @param array $id_companies The companies IDs
		 @param array $fields The companies fields to be retrieved
		 @param array $other_data Other informations. The format is:
		 	$other_data = array(
		 		'offset' => <offset>,
		 		'limit'  => <max_num_returns>
		 	); 
		
		*/
		function get_multiple_info ( $id_companies, $fields, $other_data = false )
		{
			if (!is_array($id_companies) or !is_array($fields) or ($other_data != false and !is_array($other_data)))
			{
				if (is_object($GLOBALS['phpgw']->log)) 
				{
					$GLOBALS['phpgw']->log->message(array(
						'text' => 'F-BadcontactcenterParam, wrong get_multiple_companies_info parameter type.',
						'line' => __LINE__,
						'file' => __FILE__));
					
					$GLOBALS['phpgw']->log->commit();
				}
				else {
					exit('Argument Error on: <br>File:'.__FILE__.'<br>Line:'.__LINE__.'<br>');
				}
			}
			
			$companies = array();
	
			if ($other_data)
			{
				//TODO
			}
	
			foreach ($id_companies as $id)
			{
				$companies[$id] = $this->get_single_info($id,$fields[$id]);
			}
			
			return $companies;
		}
	
		/*!
		
			@function get_relations
			@abstract Returns the IDs of all companies relations
			@author Raphael Derosso Pereira
		 
			@param integer $id_company The company ID
			
			@return The following array:
				 $return = array(
				 	'relation1' => array(
				 		'id_relation'     => <id_relation>,
				 		'id_type'         => <id_type>,
				 		'type'            => '<type_name>',
				 		'is_subordinated' => <trueness>
				 	),
				 	'relation2' => array(...),
				 	...
				 );
		 
		*/
		function get_relations ($id_company)
		{
			$company =& CreateObject('contactcenter.so_company', $id_company);
			$relations = $company->get_relations();
		
			$count = 1;	
			foreach($relations as $id => $type)
			{
				$relation =& CreateObject('contactcenter.so_company_relation_type',$type);
				$return['relation'.$count]['id_relation'] = $id;
				$return['relation'.$count]['id_type'] = $type;
				$return['relation'.$count]['type'] = $relation->get_type_name(); 
				$return['relation'.$count]['is_subordinated'] = $relation->get_is_subordinated();
				++$count;
			}
			
			return $return;
		}
	
		/*!
		
			@function get_addresses
			@abstract Returns all companies Address Information
			@author Raphael Derosso Pereira
		 
			@param integer $id_company The company ID
			
			@return The following array:
				 $return = array(
				 	'address1' => array(
				 		'id_address'         => <id_address>,
				 		'id_type'            => <id_type>,
				 		'type'               => '<type_name>',
				 		'address1'           => '<address1>',
				 		'address2'           => '<address2>',
				 		'complement'         => '<complement>',
				 		'address_other'      => '<address_other>',
				 		'postal_code'        => '<postal_code>',
				 		'po_box'		     => '<po_box>',
				 		'id_city'            => '<city>',
				 		'city_name'          => '<city_name>',
				 		'city_timezone'      => '<city_timezone>',
				 		'city_geo_location'  => '<city_geo_location>',
				 		'id_state'           => '<id_state>',
				 		'state_name'         => '<state_name>',
				 		'state_symbol'       => '<state_symbol>',
				 		'id_country'         => '<id_country>',
				 		'country_name'       => '<country_name>',
				 		'address_is_default' => <trueness>
				 	),
				 	'address2' => array(...),
				 	...
				 );
		 
		*/
		function get_addresses ( $id_company )
		{
			$company =& CreateObject('contactcenter.so_company', $id_company);
			$addresses = $company->get_addresses();
		
			$count = 1;	
			foreach($addresses as $id => $type)
			{
				$address =& CreateObject('contactcenter.so_address',$id);
				$address_type =& CreateObject('contactcenter.so_company_address_type',$type);
				
				$return['address'.$count]['id_address'] = $id;
				$return['address'.$count]['id_type'] = $type;
				$return['address'.$count]['type'] = $address_type->get_type_name();
				$return['address'.$count]['address1'] = $address->get_address1();
				$return['address'.$count]['address2'] = $address->get_address2();
				$return['address'.$count]['complement'] = $address->get_complement();
				$return['address'.$count]['address_other'] = $address->get_address_other();
				$return['address'.$count]['postal_code'] = $address->get_postal_code();
				$return['address'.$count]['po_box'] = $address->get_po_box();
				$return['address'.$count]['address_is_default'] = $address->is_default();
				$return['address'.$count]['id_city'] = $address->get_id_city();
				$return['address'.$count]['city_name'] = $address->get_city_name();
				$return['address'.$count]['city_timezone'] = $address->get_city_timezone();
				$return['address'.$count]['city_geo_location'] = $address->get_geo_location();
				$return['address'.$count]['id_state'] = $address->get_id_state();
				$return['address'.$count]['state_name'] = $address->get_state_name();
				$return['address'.$count]['state_symbol'] = $address->get_state_symbol();
				$return['address'.$count]['id_country'] = $address->get_id_country();
				$return['address'.$count]['country_name'] = $address->get_country_name();
				++$count;
			}
			
			return $return;
		}
	
	
		/*!
		
			@function get_connections
			@abstract Returns all companies connections information
			@author Raphael Derosso Pereira
		 
			@param integer $id_company The company ID
			
			@return The following array:
				 $return = array(
				 	'connection1' => array(
				 		'id_connection'         => <id_connection>,
				 		'id_type'               => <id_type>,
				 		'type'                  => '<type_name>',
				 		'connection_name'       => '<connection_name>',
				 		'connection_value'      => '<connection_value>',
				 		'connection_is_default' => '<connection_is_default>'
				 	),
				 	'connection2' => array(...),
				 	...
				 );
		 
		*/
		function get_connections ( $id_company, $types )
		{
			$company =& CreateObject('contactcenter.so_company', $id_company);
			$connections = $company->get_connections();
		
			$count = 1;	
			foreach($connections as $id => $type)
			{
				$connection =& CreateObject('contactcenter.so_connection',$id);
				$connection_type =& CreateObject('contactcenter.so_company_connection_type',$type);
				$return['connection'.$count]['id_connection'] = $id;
				$return['connection'.$count]['id_type'] = $type;
				$return['connection'.$count]['type'] = $connection_type->get_type_name(); 
				$return['connection'.$count]['connection_name'] = $connection->get_name();
				$return['connection'.$count]['connection_value'] = $connection->get_value();
				$return['connection'.$count]['connection_is_default'] = $connection->is_default();
				++$count;
			}
			
			return $return;
		}
	
		/*!
		
			@function get_companies
			@abstract Returns all Companies Employees information
			@author Raphael Derosso Pereira
		 
			@param integer $id_company The company ID
			
			@return The following array:
				 $return = array(
				 	'contact1' => array(
				 		'id_contact' => <id_company>,
				 		'title'      => '<company_name>',
				 		'department' => '<company_value>',
				 	),
				 	'contact2' => array(...),
				 	...
				 );
		 
		*/
		function get_contacts ( $id_company )
		{
			$company =& CreateObject('contactcenter.so_company', $id_company);
			$companies = $company->get_contacts();
		
			$count = 1;	
			foreach($companies as $id => $value)
			{
				$return['contact'.$count]['id_contact'] = $id;
				$return['contact'.$count]['title'] = $value['title'];
				$return['contact'.$count]['department'] = $value['department'];
				++$count;
			}
			
			return $return;
		}
		
		/*!
		
			@function get_legals
			@abstract Returns all companies legals information
			@author Raphael Derosso Pereira
		 
			@param integer $id_company The company ID
			
			@return The following array:
				 $return = array(
				 	'legal1' => array(
				 		'id_legal'         => <id_legal>,
				 		'id_type'          => <id_type>,
				 		'type'             => '<type_name>',
				 		'legal_info_name'  => '<legal_name>',
				 		'legal_info_value' => '<legal_value>'
				 	),
				 	'legal2' => array(...),
				 	...
				 );
		 
		*/
		function get_legals ( $id_company, $types )
		{
			$company =& CreateObject('contactcenter.so_company', $id_company);
			$legals = $company->get_legals();
		
			$count = 1;	
			foreach($legals as $id => $type)
			{
				$legal =& CreateObject('contactcenter.so_legal',$id);
				$legal_type =& CreateObject('contactcenter.so_company_legal_type',$type);
				$return['legal'.$count]['id_legal'] = $id;
				$return['legal'.$count]['id_type'] = $type;
				$return['legal'.$count]['type'] = $legal_type->get_type_name(); 
				$return['legal'.$count]['legal_info_name'] = $legal->get_name();
				$return['legal'.$count]['legal_info_value'] = $legal->get_value();
				$return['legal'.$count]['legal_is_default'] = $legal->is_default();
				++$count;
			}
			
			return $return;
		}
	
		
		/*********************************************************************\
		 *                Methods to get general fields                      *
		\*********************************************************************/
		

		/*!
		
		 @function get_all_relations_types
		 @abstract Returns all companies relations types
		 @author Raphael Derosso Pereira
		 
		 @return array The format of the return is:
		 	$return = array(
		 		<id_type1> => '<type1_name>',
		 		<id_type2> => '<type2_name>',
		 		...);
		*/
		function get_all_relations_types (  )
		{
			$fields = array('id_typeof_company_relation', 'company_relation_name');
			
			$relation_types = $this->find($fields);
			
			if (!is_array($relation_types))
			{
				return false;
			}
			
			while ($relation_type = each($relation_types))
			{
				$result[$relation_type['id_typeof_company_relation']] = $relation_type['company_relation_name'];				
			}
			
			return $result;
		}
	
		/*!
		
		 @function get_all_addresses_types
		 @abstract Returns all companies addresses types
		 @author Raphael Derosso Pereira
		 
		 @return array The format of the return is:
		 	$return = array(
		 		<id_type1> => '<type1_name>',
		 		<id_type2> => '<type2_name>',
		 		...);
		*/
		function get_all_addresses_types (  )
		{
			$fields = array('id_typeof_company_address', 'company_address_type_name');
			
			$address_types = $this->find($fields);
			
			if (!is_array($address_types))
			{
				return false;
			}
			
			while ($address_type = each($address_types))
			{
				$result[$address_type['id_typeof_company_address']] = $address_type['company_address_name'];				
			}
			
			return $result;
		}

		/*!
		
		 @function get_all_connections_types
		 @abstract Returns all companies connections types
		 @author Raphael Derosso Pereira
		 
		 @return array The format of the return is:
		 	$return = array(
		 		<id_type1> => '<type1_name>',
		 		<id_type2> => '<type2_name>',
		 		...);
		*/
		function get_all_connections_types (  )
		{
			$fields = array('id_typeof_company_relation', 'company_connections_type_name');
			
			$relation_types = $this->find($fields);
			
			if (!is_array($relation_types))
			{
				return false;
			}
			
			while ($relation_type = each($relation_types))
			{
				$result[$relation_type['id_typeof_company_relation']] = $relation_type['company_connections_type_name'];				
			}
			
			return $result;
		}

		/*!
		
		 @function get_all_legals_types
		 @abstract Returns all companies legals types
		 @author Raphael Derosso Pereira
		 
		 @return array The format of the return is:
		 	$return = array(
		 		<id_type1> => '<type1_name>',
		 		<id_type2> => '<type2_name>',
		 		...);
		*/
		function get_all_legals_types (  )
		{
			$fields = array('id_typeof_company_legal', 'legal_type_name');
			
			$relation_types = $this->find($fields);
			
			if (!is_array($relation_types))
			{
				return false;
			}
			
			while ($relation_type = each($relation_types))
			{
				$result[$relation_type['id_typeof_company_legal']] = $relation_type['legal_type_name'];				
			}
			
			return $result;
		}


		/*********************************************************************\
		 *                   Methods to Include Data                         *
		\*********************************************************************/

		/*!
		
			@function add
			@abstract Insert a new company record in the DB
			@author Raphael Derosso Pereira
		
			@param array $data The company Information. 
			
			Format:
		
				$data = array(
					'company_name'       => '<company_name>',
					'company_notes'      => '<company_notes>',
					
					'contacts'          => array(
						contact1 => array(
							'id_contact' => <id_company>,
							'title'      => <title>,
							'department' => <department>
						),
						company2 => array(...),
						...
					),
					
					'relations'          => array(
						'relation1' => array(
							'id_relation'        => <id_relation>,
							'id_typeof_relation' => <id_typeof_relation>,
						),
						'relation2' => array(...),
						...
					),
			 		
			 		'addresses'          => array(
			 			'address1' => array(
					 		'id_typeof_address'  => <id_typeof_address>,
					 		'address1'           => '<address1>',
					 		'address2'           => '<address2>',
					 		'complement'         => '<complement>',
					 		'address_other'      => '<address_other>',
					 		'postal_code'        => '<postal_code>',
					 		'po_box'		     => '<po_box>',
					 		'id_city'            => '<city>',
					 		'id_state'           => '<id_state>',
					 		'id_country'         => '<id_country>',
					 		'address_is_default' => <trueness>
			 			),
			 			'address2' => array(...),
			 			...
			 		),
			 		
			 		'connections'        => array(
			 			'connection1' => array(
				 			'id_typeof_connection'  => <id_typeof_connection>,
				 			'connection_name'       => <connection_name>,
				 			'connection_value'      => <connection_value>,
				 			'connection_is_default' => <trueness>
			 			),
			 			'connection2' => array(...),
			 			...
			 		),
			 		
			 		'legals'             => array(
			 			'legal1' => array(
				 			'id_typeof_legal'       => <id_typeof_connection>,
				 			'legal_info_name'       => <connection_name>,
				 			'legal_info_value'      => <connection_value>
			 			),
			 			'legal2' => array(...),
			 			...
			 		)
			 		
				);
			
			If any of the above fields doesn't have a value, it should hold false.
			In the case of the multiple-values fields, instead of the array, there 
			should be a false.
		*/	
		function add ( $data )
		{
			$permissions = $this->security->get_permissions();
			
			if (!$permissions['create'])
			{
				return false;
			}
			
			$company =& CreateObject('contactcenter.so_company');
			$company->reset_fields();
			
			foreach($data as $field => $value)
			{
				if ($value === false)
				{
					continue;
				}
				
				switch($field)
				{
					case 'company_name':
					case 'company_notes':
						$func = 'set_'.$field;
						$company->$func();
						break;

					case 'contacts':					
						foreach($value as $contact_fields)
						{
							$company->set_contact($contact_fields['id_contact'], 
								$company_fields['title'], $company_fields['department']);
						}
						break;
					
					case 'relations':
						foreach($value as $relation_fields)
						{
							$company->set_relation($relation_fields['id_relation'], $relation_fields['id_typeof_relation']);
						}
						break;
					
					case 'addresses':
						foreach($value as $address_fields)
						{
							$address =& CreateObject('contactcenter.so_address');
							$address->reset_values();
							foreach($address_fields as $a_field => $a_value)
							{
								if ($a_field !== 'id_typeof_address')
								{
									$func = 'set_'.$a_field;
									$address->$func($a_value);
								}
							}
							$address->commit();
							$id_address = $address->get_id();
							$company->set_address($id_address, $address_fields['id_typeof_address']);
						}
						break;
					
					case 'connections':
						foreach($value as $connection_fields)
						{
							$connection =& CreateObject('contactcenter.so_connection');
							$connection->reset_values();
							foreach($connection_fields as $a_field => $a_value)
							{
								if ($a_field !== 'id_typeof_connection')
								{
									$func = 'set_'.$a_field;
									$connection->$func($a_value);
								}
							}
							$connection->commit();
							$id_connection = $connection->get_id();
							$company->set_connection($id_connection, $connection_fields['id_typeof_connection']);
						}
			 			break;
			 			
					case 'legals':
						foreach($value as $connection_fields)
						{
							$legal =& CreateObject('contactcenter.so_legal');
							$legal->reset_values();
							foreach($legal_fields as $a_field => $a_value)
							{
								if ($a_field !== 'id_typeof_legal')
								{
									$func = 'set_'.$a_field;
									$legal->$func($a_value);
								}
							}
							$legal->commit();
							$id_legal = $legal->get_id();
							$company->set_legal($id_legal, $legal_fields['id_typeof_legal']);
						}
			 			break;
			 			
			 		default:
			 			return false;
			 			break;
				}
			}
		}
		
	
		/*!
		
			@function add_relation_type
			@abstract Insert a new Relation Type in the DB
			@author Raphael Derosso Pereira
		
			@param string $type_name The Relation Type
			@return integer The new ID
			
		*/ 
		function add_relation_type ( $type_name )
		{
			$permissions = $this->security->get_type_permissions();
			
			if (!$permissions['create'])
			{
				return false;
			}
		}
	
		/*!
		
			@function add_address_type
			@abstract Insert a new Address Type in the DB
			@author Raphael Derosso Pereira
		
			@param string $type_name The Address Type
			@return integer The new ID
			
		*/ 
		function add_address_type ( $type_name )
		{
			$permissions = $this->security->get_type_permissions();
			
			if (!$permissions['create'])
			{
				return false;
			}
		}
	
		/*!
		
			@function add_connection_type
			@abstract Insert a new Connection Type in the DB
			@author Raphael Derosso Pereira
		
			@param string $type_name The Connection Type
			@return integer The new ID
			
		*/ 
		function add_connection_type ( $type_name )
		{
			$permissions = $this->security->get_type_permissions();
			
			if (!$permissions['create'])
			{
				return false;
			}
		}
	
		/*!
		
			@function add_legal_type
			@abstract Insert a new legal Type in the DB
			@author Raphael Derosso Pereira
		
			@param string $type_name The legal Type
			@return integer The new ID
			
		*/ 
		function add_legal_type ( $type_name )
		{
			$permissions = $this->security->get_type_permissions();
			
			if (!$permissions['create'])
			{
				return false;
			}
		}


		/*********************************************************************\
		 *                   Methods to Alter Data                           *
		\*********************************************************************/

		/*!
		
			@function update_company_info
			@abstract Update information of an existing company
			@author Raphael Derosso Pereira
		
			@param integer $id_status The company ID
			@param string $status The new Status value
			
		*/ 
		function update_single_info ( $id_company, $data )
		{
			$permissions = $this->security->get_permissions($id_company);
			
			if (!$permissions['alter'])
			{
				return false;
			}
		}
	
		/*!
		
			@function update_prefix
			@abstract Update an existing Prefix
			@author Raphael Derosso Pereira
		
			@param integer $id_prefix The Prefix ID
			@param string $prefix The new Prefix value
			
		*/ 
		function update_prefix ( $id_prefix, $prefix )
		{
			$permissions = $this->security->get_type_permissions();
			
			if (!$permissions['alter'])
			{
				return false;
			}
		}
	
		/*!
		
			@function update_suffix
			@abstract Update an existing Sulfix
			@author Raphael Derosso Pereira
		
			@param integer $id_suffix The Sulfix ID
			@param string $suffix The new Sulfix value
			
		*/ 
		function update_suffix ( $id_suffix, $suffix )
		{
			$permissions = $this->security->get_type_permissions();
			
			if (!$permissions['alter'])
			{
				return false;
			}
		}
	
		/*!
		
			@function update_status
			@abstract Update an existing Status
			@author Raphael Derosso Pereira
		
			@param integer $id_status The Status ID
			@param string $status The new Status value
			
		*/ 
		function update_status ( $id_status, $status )
		{
			$permissions = $this->security->get_type_permissions();
			
			if (!$permissions['alter'])
			{
				return false;
			}
		}
	
		/*!
		
			@function update_relation_type
			@abstract Update an existing Relation Type
			@author Raphael Derosso Pereira
		
			@param integer $id_type The Type ID
			@param string $type The new type value
			
		*/ 
		function update_relation_type ( $old_name, $relation_name )
		{
		}
	
		/*!
		
			@function update_address_type
			@abstract Update an existing Address Type
			@author Raphael Derosso Pereira
		
			@param integer $id_type The Type ID
			@param string $type The new type value
			
		*/ 
		function update_address_type ( $id_type, $type )
		{
		}
	
		/*!
		
			@function update_connection_type
			@abstract Update an existing Connection Type
			@author Raphael Derosso Pereira
		
			@param integer $id_type The Type ID
			@param string $type The new type value
			
		*/ 
		function update_connection_type ( $id_type, $type )
		{
		}

		/*!
		
			@function update_legal_type
			@abstract Update an existing legal Type
			@author Raphael Derosso Pereira
		
			@param integer $id_type The Type ID
			@param string $type The new type value
			
		*/ 
		function update_legal_type ( $id_type, $type )
		{
		}
	}
?>
