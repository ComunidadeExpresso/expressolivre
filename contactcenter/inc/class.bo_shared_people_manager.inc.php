<?php
		/*************************************************************************** 
		* Expresso Livre                                                           * 
		* http://www.expressolivre.org                                             * 
		* --------------------------------------------                             * 
		*  This program is free software; you can redistribute it and/or modify it * 
		*  under the terms of the GNU General Public License as published by the   * 
		*  Free Software Foundation; either version 2 of the License, or (at your  * 
		*  option) any later version.                                              * 
		\**************************************************************************/ 
		
	include_once('class.abo_catalog.inc.php');
	class bo_shared_people_manager extends abo_catalog {
		var $fields = array(
			'id_contact'    => true,
			'status'        => true,
			'photo'         => true,
			'alias'         => true,
			'prefix'        => true,
			'given_names'   => true,
			'family_names'  => true,
			'names_ordered' => true,
			'suffix'        => true,
			'birthdate'     => true,
			'sex'           => true,
			'pgp_key'       => true,
			'notes'         => true,
			'corporate_name'=> true,
			'job_title' 	=> true,
			'department' 	=> true,
			'web_page' 		=> true,
			
			/* Array fields */
			'companies'     => true,
			'relations'     => true,
			'addresses'     => true,
			'connections'   => true
		);
	
		/*!
		
		 @function bo_people_catalog
		 @abstract Constructor
	 	 @author Raphael Derosso Pereira
	 	 
	 	*/
		function bo_shared_people_manager()
		{
			$this->init();
		}

		/*!
			@function find
			@abstract Find function for this catalog
			@author Raphael Derosso Pereira

		*/
		function find($what, $rules=false, $other=false)
		{
			$so_contact = CreateObject('contactcenter.so_contact',  $GLOBALS['phpgw_info']['user']['account_id']);
			$relacionados = $so_contact->get_relations();
			$array_retorno = array();
			$array_map = array();
			$rules_inicio = $rules;
	
			foreach($relacionados as $uid_relacionado => $tipo_relacionamento) {
				$aclTemp = CreateObject("phpgwapi.acl",$uid_relacionado); 
				$aclTemp->read();
				$perms_relacao = $aclTemp->get_specific_rights($GLOBALS['phpgw_info']['user']['account_id'],'contactcenter'); //Preciso verificar as permissões que o contato relacionado deu para o atual
				
				if(!($perms_relacao&1)) //Se não tiver permissão de leitura, nem se preocupe em listá-los
					continue;
				if($tipo_relacionamento == 1) {
					if (is_array($what) and count($what))
					{
						$found = false;
				
						foreach ($what as $value)
						{
							if (strpos($value, 'contact') === 0)
							{
								$found = true;
							}
						}
						
						if (!$found)
						{
							return $this->sql_find($what, $rules, $other);
						}
						
						if ($rules and is_array($rules))
						{
							array_push($rules, array(
								'field' => 'contact.id_owner',
								'type'  => '=',
								'value' => $uid_relacionado
							));
						}
						else
						{
							$rules = array(
								0 => array(
									'field' => 'contact.id_owner',
									'type'  => '=',
									'value' => $uid_relacionado
								)
							);
						}
					}
					$array_temp = $this->sql_find($what, $rules, $other);
					if(count($array_retorno)==0) {
						$array_map = $this->cria_aux($array_temp);
						if($array_map)foreach($array_temp as $valor_retorno) {
							$valor_retorno['perms'] = $perms_relacao;
							$valor_retorno['owner'] = $uid_relacionado;
							array_push($array_retorno,$valor_retorno);
						}
					}
					else {
						if($array_temp) foreach($array_temp as $value) {
							if(!array_key_exists($value["names_ordered"],$array_map)) {
								$value['perms'] = $perms_relacao;
								$value['owner'] = $uid_relacionado;
								$array_map[$value["names_ordered"]] = 1;
								array_push($array_retorno,$value);
							}
						}
					}
					
					
					$rules = $rules_inicio;
				}
			}
			
			usort($array_retorno, array($this, "compareTreeNodes"));
			return $array_retorno;
		}

		function compareTreeNodes($a, $b)	{					
			return strnatcasecmp($a['names_ordered'], $b['names_ordered']);
		}	
					
		function cria_aux($array) {
			$retorno = array();
			if($array)foreach($array as $valor) {
				$retorno[$valor["names_ordered"]] = 1;
			}
			return $retorno;
		}
			
		/*!
		
		 @function get_single_entry
		 @abstract Returns all information requested about one contact
		 @author Raphael Derosso Pereira
		     
		 @param integer $id_contact The contact ID
		 @param array $fields The array returned by get_fields whith true
		 	on the fields to be taken.
		 	
		*/
		function get_single_entry ( $id_contact, $fields )
		{	
			if (!is_array($fields)) 
			{
				if (is_object($GLOBALS['phpgw']->log)) 
				{
					$GLOBALS['phpgw']->log->message(array(
						'text' => 'F-BadcontactcenterParam, wrong get_single_entry parameters type.',
						'line' => __LINE__,
						'file' => __FILE__));
					
					$GLOBALS['phpgw']->log->commit();
				}
				else 
				{
					exit('Argument Error on: <br>File:'.__FILE__.'<br>Line:'.__LINE__.'<br>');
				}
			}
			
			// Verify permissions
			/*$permissions = $this->security->get_permissions('entry', $id_contact);

			if (!$permissions['read'])
			{
				return false;
			}*/
			
			//$contact_data = $this->fields;
	
			$contact = CreateObject('contactcenter.so_contact', $id_contact);

			foreach ($fields as $field => $trueness)
			{
				if (!$trueness)
				{
					//unset($contact_data[$field]);
					continue;
				}
					
				switch ($field)
				{
					case 'companies':
						$companies = $this->get_companies($id_contact,$trueness);

						if (is_array($companies) and count($companies))
						{
							$contact_data['companies'] = $companies;
						}
						break;
					
					case 'relations':
						$relations = $this->get_relations($id_contact,$trueness);

						if (is_array($relations) and count($relations))
						{
							$contact_data['relations'] = $relations;
						}
						break;
					
					case 'addresses':
						$addresses = $this->get_addresses($id_contact,$trueness);

						if (is_array($addresses) and count($addresses))
						{
							$contact_data['addresses'] = $addresses;
						}
						break;
					
					case 'connections':
						$connections = $this->get_connections($id_contact,$trueness);

						if (is_array($connections) and count($connections))
						{
							$contact_data['connections'] = $connections;
						}
						break;
					
					case 'prefix':
						$id = $contact->get_prefix();
						if ($id)
						{
							$prefix = CreateObject('contactcenter.so_prefix', $id);
							$contact_data['id_prefix'] = $id;
							$contact_data['prefix'] = $prefix->get_prefix();  
						}
						break;
						
					case 'suffix':
						$id = $contact->get_suffix();
						if ($id)
						{
							$suffix = CreateObject('contactcenter.so_suffix', $id);
							$contact_data['id_suffix'] = $id;
							$contact_data['suffix'] = $suffix->get_suffix();  
						}
						break;
						
					case 'status':
						$id = $contact->get_status();
						if ($id)
						{
							$status = CreateObject('contactcenter.so_status', $id);
							$contact_data['id_status'] = $id;
							$contact_data['status'] = $status->get_status();  
						}
						break;
						
					default:
						//$func_name = 'contact->get_'.$field;
						//$contact_data[$field] = $this->$func_name();
						$contact_data[$field] = $contact->get_field($field);
						break;
				}
			}

			if (!is_array($contact_data))
			{
				return false;
			}
			
			return $contact_data;
		}
	
		/*!
		 
		 @function get_multiple_entries
		 @abstract Returns multiple Contacts data into one array
		 @author Raphael Derosso Pereira

		 @param array $id_contacts The Contacts IDs
		 @param array $fields The Contacts fields to be retrieved
		 @param array $other_data Other informations. The format is:
		 	$other_data = array(
		 		'offset'    => <offset>,
		 		'limit'     => <max_num_returns>,
		 		'sort'      => <sort>,
		 		'order_by'  => <order by>
		 	); 
		
		*/
		function get_multiple_entries ( $id_contacts, $fields, $other_data = false )
		{
			if (!is_array($id_contacts) or !is_array($fields) or ($other_data != false and !is_array($other_data)))
			{
				if (is_object($GLOBALS['phpgw']->log)) 
				{
					$GLOBALS['phpgw']->log->message(array(
						'text' => 'F-BadcontactcenterParam, wrong get_multiple_entry parameter type.',
						'line' => __LINE__,
						'file' => __FILE__));
					
					$GLOBALS['phpgw']->log->commit();
				}
				else {
					exit('Argument Error on: <br>File:'.__FILE__.'<br>Line:'.__LINE__.'<br>');
				}
			}
			
			$contacts = array();
	
			if ($other_data)
			{
				//TODO
			}
	
			foreach ($id_contacts as $id)
			{
				$contact = $this->get_single_entry($id,$fields);
				if ($contact)
				{
					$contacts[$id] = $contact;
				}
			}
			
			return $contacts;
		}

		/*!
		
			@function get_all_entries_ids
			@abstract Returns the IDs of all the entries in this catalog
			@author Raphael Derosso Pereira

		*/
		function get_all_entries_ids ()
		{
			$search_fields = array('contact.id_contact', 'contact.names_ordered');
			$search_other  = array('order' => 'contact.names_ordered');

			$result_i = $this->find($search_fields, null, $search_other);

			if (is_array($result_i) and count($result_i))
			{
				$result = array();
				foreach($result_i as $result_part)
				{
					$result[] = $result_part['id_contact'];
				}

				return $result;
			}

			return null;
		}
	
		/*!
		
			@function get_relations
			@abstract Returns the IDs of all Contacts relations
			@author Raphael Derosso Pereira
		 
			@param integer $id_contact The Contact ID
			
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
		function get_relations ($id_contact,$extra=false)
		{
			$contact = CreateObject('contactcenter.so_contact', $id_contact);
			$relations = $contact->get_relations();
		
			$count = 1;	
			foreach($relations as $id => $type)
			{
				$relation = CreateObject('contactcenter.so_contact_relation_type', $type);
				
				if ($extra === 'subordinated' and $relation->get_is_subordinated())
				{
					$return['relation'.$count]['id_relation'] = $id;
					$return['relation'.$count]['id_type'] = $type;
					$return['relation'.$count]['type'] = $relation->get_type_name(); 
					$return['relation'.$count]['is_subordinated'] = $relation->get_is_subordinated();
					++$count;
				}
				else if ($extra !== 'subordinated')
				{
					$return['relation'.$count]['id_relation'] = $id;
					$return['relation'.$count]['id_type'] = $type;
					$return['relation'.$count]['type'] = $relation->get_type_name(); 
					$return['relation'.$count]['is_subordinated'] = $relation->get_is_subordinated();
					++$count;
				}
			}
			
			return $return;
		}
	
		/*!
		
			@function get_addresses
			@abstract Returns all Contacts Address Information
			@author Raphael Derosso Pereira
		 
			@param integer $id_contact The Contact ID
			
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
						'city_name'          => (string),
						'city_timezone'      => (int),
						'city_geo_location'  => (string),
						'id_state'           => (int),
						'state_name'         => (string),
						'state_symbol'       => (string),
						'id_country'         => (int),
						'country_name'       => (string),
				 		'address_is_default' => <trueness>
				 	),
				 	'address2' => array(...),
				 	...
				 );
		 
		*/
		function get_addresses ( $id_contact,$extra=false )
		{
			$contact = CreateObject('contactcenter.so_contact', $id_contact);
			$addresses = $contact->get_addresses(extra);
		
			foreach($addresses as $id => $type)
			{
				$address = CreateObject('contactcenter.so_address',$id);
				$address_type = CreateObject('contactcenter.so_contact_address_type',$type);
				
				if ($extra === 'default' and $address->is_default())
				{
					$return['address'.$type]['id_address'] = $id;
					$return['address'.$type]['id_typeof_address'] = $type;
					$return['address'.$type]['type'] = $address_type->get_type_name();
					$return['address'.$type]['address1'] = $address->get_address1();
					$return['address'.$type]['address2'] = $address->get_address2();
					$return['address'.$type]['complement'] = $address->get_complement();
					$return['address'.$type]['address_other'] = $address->get_address_other();
					$return['address'.$type]['postal_code'] = $address->get_postal_code();
					$return['address'.$type]['po_box'] = $address->get_po_box();
					$return['address'.$type]['address_is_default'] = true;
					$return['address'.$type]['id_city'] = $id_city = $address->get_id_city();
					$return['address'.$type]['id_state'] = $id_state = $address->get_id_state();
					$return['address'.$type]['id_country'] = $id_country = $address->get_id_country();
					
					if ($id_city)
					{
						$city = CreateObject('contactcenter.so_city',$id_city);
						$return['address'.$type]['city_name'] = $city->get_city_name();
						$return['address'.$type]['city_timezone'] = $city->get_city_timezone();
						$return['address'.$type]['city_geo_location'] = $city->get_city_geo_location();
					}
					
					if ($id_state)
					{
						$state = CreateObject('contactcenter.so_state',$id_state);
						$return['address'.$type]['state_name'] = $state->get_state_name();
						$return['address'.$type]['state_symbol'] = $state->get_state_symbol();
						$return['address'.$type]['id_country'] = $id_country = $state->get_id_country();
					}

					$country = CreateObject('contactcenter.so_country',$id_country);
					$return['address'.$type]['country_name'] = $country->get_country_name();
				}
				else if ($extra !== 'default')
				{	
					$return['address'.$type]['id_address'] = $id;
					$return['address'.$type]['id_typeof_address'] = $type;
					$return['address'.$type]['type'] = $address_type->get_type_name();
					$return['address'.$type]['address1'] = $address->get_address1();
					$return['address'.$type]['address2'] = $address->get_address2();
					$return['address'.$type]['complement'] = $address->get_complement();
					$return['address'.$type]['address_other'] = $address->get_address_other();
					$return['address'.$type]['postal_code'] = $address->get_postal_code();
					$return['address'.$type]['po_box'] = $address->get_po_box();
					$return['address'.$type]['address_is_default'] = $address->is_default();
					$return['address'.$type]['id_city'] = $id_city = $address->get_id_city();
					$return['address'.$type]['id_state'] = $id_state = $address->get_id_state();
					$return['address'.$type]['id_country'] = $id_country = $address->get_id_country();
					
					if ($id_city)
					{
						$city = CreateObject('contactcenter.so_city',$id_city);
						$return['address'.$type]['city_name'] = $city->get_city_name();
						$return['address'.$type]['city_timezone'] = $city->get_city_timezone();
						$return['address'.$type]['city_geo_location'] = $city->get_city_geo_location();
					}
					
					if ($id_state)
					{
						$state = CreateObject('contactcenter.so_state',$id_state);
						$return['address'.$type]['state_name'] = $state->get_state_name();
						$return['address'.$type]['state_symbol'] = $state->get_state_symbol();
						$return['address'.$type]['id_country'] = $id_country = $state->get_id_country();
					}

					$country = CreateObject('contactcenter.so_country',$id_country);
					$return['address'.$type]['country_name'] = $country->get_country_name();
				}
			}
			
			return $return;
		}
	
	
		/*!
		
			@function get_connections
			@abstract Returns all Contacts connections information
			@author Raphael Derosso Pereira
		 
			@param integer $id_contact The Contact ID
			
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
		function get_connections ( $id_contact,$extra=false )
		{
			$contact = CreateObject('contactcenter.so_contact', $id_contact);
			$connections = $contact->get_connections($extra);
		
			$count = 1;	
			foreach($connections as $id => $type)
			{
				$connection = CreateObject('contactcenter.so_connection', $id);

				if ($extra === 'default' and $connection->is_default())
				{
					$connection_type = CreateObject('contactcenter.so_contact_connection_type', $type);
					$return['connection'.$count]['id_connection'] = $id;
					$return['connection'.$count]['id_type'] = $type;
					$return['connection'.$count]['type'] = $connection_type->get_type_name(); 
					$return['connection'.$count]['connection_name'] = $connection->get_name();
					$return['connection'.$count]['connection_value'] = $connection->get_value();
					$return['connection'.$count]['connection_is_default'] = $connection->is_default();
					++$count;
				}
				else if ($extra !== 'default')
				{
					$connection_type = CreateObject('contactcenter.so_contact_connection_type', $type);
					$return['connection'.$count]['id_connection'] = $id;
					$return['connection'.$count]['id_type'] = $type;
					$return['connection'.$count]['type'] = $connection_type->get_type_name(); 
					$return['connection'.$count]['connection_name'] = $connection->get_name();
					$return['connection'.$count]['connection_value'] = $connection->get_value();
					$return['connection'.$count]['connection_is_default'] = $connection->is_default();
					++$count;
				}
			}
			
			return $return;
		}
	
		/*!
		
			@function get_companies
			@abstract Returns all Contacts companies information
			@author Raphael Derosso Pereira
		 
			@param integer $id_contact The Contact ID
			
			@return The following array:
				 $return = array(
				 	'company1' => array(
				 		'id_company'   => <id_company>,
				 		'company_name' => '<company_name>'
				 		'title'        => '<company_name>',
				 		'department'   => '<company_value>',
				 	),
				 	'company2' => array(...),
				 	...
				 );
		 
		*/
		function get_companies ( $id_contact,$extra=false )
		{
			$contact = CreateObject('contactcenter.so_contact', $id_contact);
			$companies = $contact->get_companies($extra);
		
			$count = 1;	
			foreach($companies as $id => $value)
			{
				$company = CreateObject('contactcenter.so_company', $id);

				if ($extra === 'default' and $value['default_company'])
				{
					$return['company'.$count]['id_company'] = $id;
					$return['company'.$count]['company_name'] = $company->get_company_name();
					$return['company'.$count]['title'] = $value['title'];
					$return['company'.$count]['department'] = $value['department'];
					$return['company'.$count]['default_company'] = $value['default_company'];
					$return['company'.$count]['default_contact'] = $value['default_contact'];
					++$count;
				}
				else if ($extra !== 'default')
				{
					$return['company'.$count]['id_company'] = $id;
					$return['company'.$count]['company_name'] = $company->get_company_name();
					$return['company'.$count]['title'] = $value['title'];
					$return['company'.$count]['department'] = $value['department'];
					$return['company'.$count]['default_company'] = $value['default_company'];
					$return['company'.$count]['default_contact'] = $value['default_contact'];
					++$count;
				}
			}
			
			return $return;
		}
		
		
		/*********************************************************************\
		 *                Methods to get general fields                      *
		\*********************************************************************/
		
		/*!
		
		 @function get_all_prefixes
		 @abstract Returns all the registered prefixes
		 @author Raphael Derosso Pereira
		 
		*/
		function get_all_prefixes (  )
		{
			$fields = array('prefix.id_prefix','prefix.prefix');
			
			$prefixes = $this->find($fields);
			
			if (!is_array($prefixes))
			{
				return false;
			}
			
			while (list(,$prefix) = each($prefixes))
			{
				$result[$prefix['id_prefix']] = $prefix['prefix'];
			}
			
			return $result;
		}
		
	
		/*!
		
		 @function get_all_suffixes
		 @abstract Returns all the registered suffixes
		 @author Raphael Derosso Pereira
		 @return An array as follows:
		 	$return = array(
		 		<id_suffix1> => '<suffix_name1>',
		 		<id_suffix2> => '<suffix_name2>',
		 		...		 		
		 	);
		 
		*/
		function get_all_suffixes (  )
		{
			$fields = array('suffix.id_suffix','suffix.suffix');
			
			$suffixes = $this->find($fields);
			
			if (!is_array($suffixes))
			{
				return false;
			}
			
			while (list(,$suffix) = each($suffixes))
			{
				$result[$suffix['id_suffix']] = $suffix['suffix']; 
			}
			
			return $result;
		}
	
		/*!
		
		 @function get_all_status
		 @abstract Returns all the registered status
		 @author Raphael Derosso Pereira
		 @return An array as follows:
		 	$return = array(
		 		<id_status1> => '<status_name1>',
		 		<id_status2> => '<status_name2>',
		 		...		 		
		 	);
		 
		*/
		function get_all_status (  )
		{
			$fields = array('status.id_status','status.status_name');
			
			$status = $this->find($fields);
			
			if (!is_array($status))
			{
				return false;
			}
			
			while (list(,$status_) = each($status))
			{
				$result[$status_['id_status']] = $status_['status_name'];
			}
			
			return $result;
		}

		/*!
		
		 @function get_all_relations_types
		 @abstract Returns all contacts relations types
		 @author Raphael Derosso Pereira
		 
		 @return array The format of the return is:
		 	$return = array(
		 		<id_type1> => '<type1_name>',
		 		<id_type2> => '<type2_name>',
		 		...);
		*/
		function get_all_relations_types (  )
		{
			$fields = array('typeof_contact_relation.id_typeof_contact_relation', 'typeof_contact_relation.contact_relation_type_name');
			
			$relation_types = $this->find($fields);
			
			if (!is_array($relation_types))
			{
				return false;
			}
			
			while (list(,$relation_type) = each($relation_types))
			{
				$result[$relation_type['id_typeof_contact_relation']] = $relation_type['contact_relation_type_name'];				
			}
			
			return $result;
		}
	
		/*!
		
		 @function get_all_addresses_types
		 @abstract Returns all contacts addresses types
		 @author Raphael Derosso Pereira
		 
		 @return array The format of the return is:
		 	$return = array(
		 		<id_type1> => '<type1_name>',
		 		<id_type2> => '<type2_name>',
		 		...);
		*/
		function get_all_addresses_types (  )
		{
			$fields = array('typeof_contact_address.id_typeof_contact_address', 'typeof_contact_address.contact_address_type_name');
			
			$address_types = $this->find($fields);
			
			if (!is_array($address_types))
			{
				return false;
			}
			
			while (list(,$address_type) = each($address_types))
			{
				$result[$address_type['id_typeof_contact_address']] = $address_type['contact_address_type_name'];				
			}
			
			return $result;
		}

		/*!
		
		 @function get_all_connections_types
		 @abstract Returns all contacts connections types
		 @author Raphael Derosso Pereira
		 
		 @return array The format of the return is:
		 	$return = array(
		 		<id_type1> => '<type1_name>',
		 		<id_type2> => '<type2_name>',
		 		...);
		*/
		function get_all_connections_types (  )
		{
			$fields = array('typeof_contact_connection.id_typeof_contact_connection', 'typeof_contact_connection.contact_connection_type_name');
			
			$connection_types = $this->find($fields);
			
			if (!is_array($connection_types))
			{
				return false;
			}
			
			while (list(,$connection_type) = each($connection_types))
			{
				$result[$connection_type['id_typeof_contact_connection']] = $connection_type['contact_connection_type_name'];				
			}
			
			return $result;
		}

		/*!
		
			@function get_vcard
			@abstract Returns an URL that points to the file
				that contains a vCard of the specified Contact
			@author Raphael Derosso Pereira
		
			@param integer $id_status The Contact ID
			
		*/ 
		function get_vcard ( $id_contact )
		{
		}


		/*********************************************************************\
		 *                   Methods to Include Data                         *
		\*********************************************************************/

		/*!
		
			@function add_single_entry
			@abstract Insert a new Contact record in the DB
			@author Raphael Derosso Pereira
		
			@param array $data The Contact Information. 
			
			Format:
		
				$data = array(
					'id_status'          => <id_status>,
					'photo'              => '<photo_bin_stream>',
					'alias'              => '<alias>',
					'id_prefix'          => <id_prefix>,
					'given_names'        => '<given_names>',
					'family_names'       => '<family_names>',
					'names_ordered'      => '<names_ordered>',
					'id_suffix'          => <id_suffix>,
					'birthdate'          => '<birthdate>',
					'sex'                => '<sex>',
					'pgp_key'            => '<pgp_key>',
					'notes'              => '<notes>',
					
					'companies'          => array(
						company1 => array(
							'id_company'      => <id_company>,
							'company_name'    => <company_name>,
							'title'           => <title>,
							'department'      => <department>,
							'default_company' => (bool),
							'default_contact' => (bool)
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
			 		
				);
			
			If any of the above fields doesn't have a value, it should hold false.
			In the case of the multiple-values fields, instead of the array, there 
			should be a false.

			@return integer $id The Contact ID
		*/	
		function add_single_entry ( $data , $owner)
		{
			$permissions = $this->security->get_permissions();
			
			if (!$permissions['create'])
			{
				//return false;
			}
			
			$contact = CreateObject('contactcenter.so_contact');
			$contact->reset_values();
			
			$altered = false;
			foreach($data as $field => $value)
			{
				if ($value === false)
				{
					continue;
				}
				
				$altered = true;
				switch($field)
				{
					case 'corporate_name':
					case 'job_title':
					case 'department':
					case 'web_page':
					case 'photo':
					case 'alias':
					case 'id_prefix':
					case 'id_status':
					case 'id_suffix':
					case 'given_names':
					case 'family_names':
					case 'names_ordered':
					case 'birthdate':
					case 'sex':
					case 'pgp_key':
					case 'notes':
						$contact->set_field($field,$value);
						break;

					case 'companies':					
						foreach($value as $company_fields)
						{
							if ($company_fields['company_name'])
							{
								$fields = array('company.id_company');
								$restric = array(
									0 => array(
										'field' => 'company.company_name',
										'type'  => 'iLIKE',
										'value' => $company_fields['company_name']
									)
								);
								
								if($result = $this->find($fields,$restric))
								{
									$id = $result[0]['id_company'];
									$company_fields['id_company'] = $id;
								}
								else
								{
									$company = CreateObject('contactcenter.so_company');
									$company->reset_values();
									$company->set_company_name($company_fields['company_name']);
									$company->set_field('id_company_owner',$GLOBALS['phpgw_info']['user']['account_id']);
									$company_fields['id_company'] = $company->commit();
								}
							}
							
							$contact->set_company($company_fields);
						}
						break;
					
					case 'relations':
						foreach($value as $relation_fields)
						{
							$contact->set_relation($relation_fields['id_relation'], $relation_fields['id_typeof_relation']);
						}
						break;
					
					case 'addresses':
						foreach($value as $address_fields)
						{
							$address = CreateObject('contactcenter.so_address');
							$address->reset_values();
							foreach($address_fields as $a_field => $a_value)
							{
								if ($a_field !== 'id_typeof_address')
								{
									$address->set_field($a_field,$a_value);
								}
							}
							$address->commit();
							$id_address = $address->get_id();
							$contact->set_address($id_address, $address_fields['id_typeof_address']);
						}
						break;
					
					case 'connections':
						foreach($value as $connection_name => $connection_fields)
						{
							$connection = CreateObject('contactcenter.so_connection');
							$connection->reset_values();
							
							foreach($connection_fields as $a_field => $a_value)
							{
								if ($a_field !== 'id_typeof_connection')
								{
									$connection->set_field($a_field,$a_value);
								}
							}
							
							$connection->commit();
							$id_connection = $connection->get_id();
							$contact->set_connection($id_connection, $connection_fields['id_typeof_connection']);
						}
			 			break;
			 			
			 		default:
			 			return false;
				}
			}
			
			if ($altered)
			{
				if ($owner)
				{	
					$contact->set_field('id_owner',$owner);
				}else
					$contact->set_field('id_owner',$GLOBALS['phpgw_info']['user']['account_id']);
				return $contact->commit();
			}
			
			return false;
		}
		
		/*!
		
			@function quick_add
			@abstract Insert a new Contact record in the DB with just the
				main fields
			@author Raphael Derosso Pereira
		
			@param array $data The Contact Information. 
			
			Format:
		
				$data = array(
					'alias'              => '<alias>',
					'given_names'        => '<given_names>',
					'family_names'       => '<family_names>',

			 		'connections'        => array(
			 			'default_email' => array(
				 			'connection_name'       => <connection_name>,
				 			'connection_value'      => <connection_value>
			 			),
			 			'default_phone' => array(
				 			'connection_name'       => <connection_name>,
				 			'connection_value'      => <connection_value>
			 			)
			 		)
				);
		*/
		function quick_add ( $data )
		{
			$permissions = $this->security->get_permissions();
			
			if (!$permissions['create'])
			{
				return false;
			}

			// TODO: GET THE ORDER TO PUT names_ordered FROM PREFERENCES!

			$preferences = ExecMethod('contactcenter.ui_preferences.get_preferences');
			
			if (!is_array($preferences))
			{
				$preferences['personCardEmail'] = 1;
				$preferences['personCardPhone'] = 2;
			}
			
			$new_data = array(
				'alias'              => $data['alias'],
				'id_status'          => 1,
				'given_names'        => $data['given_names'],
				'family_names'       => $data['family_names'],
				'names_ordered'      => $data['given_names'].' '.$data['family_names'],
			);

			$i = 1;
			if ($data['connections']['default_email']['connection_value'])
			{
		 		$new_data['connections']['connection'.$i] = array(
			 		'id_typeof_connection'  => $preferences['personCardEmail'],
			 		'connection_name'       => $data['connections']['default_email']['connection_name'],
			 		'connection_value'      => $data['connections']['default_email']['connection_value'],
			 		'connection_is_default' => 1,
		 		);
				++$i;
			}

			if ($data['connections']['default_phone']['connection_value'])
			{
		 		$new_data['connections']['connection'.$i] = array(
			 		'id_typeof_connection'  => $preferences['personCardPhone'],
			 		'connection_name'       => $data['connections']['default_phone']['connection_name'],
			 		'connection_value'      => $data['connections']['default_phone']['connection_value'],
			 		'connection_is_default' => 1,
		 		);
			}
			
			return $this->add_single_entry($new_data);
		}
	
		/*!
		
			@function add_prefix
			@abstract Insert a new Prefix in the DB
			@author Raphael Derosso Pereira
		
			@param string $prefix The Prefix
			@return integer The new ID
			
		*/ 
		function add_prefix ( $prefix )
		{
			$permissions = $this->security->get_type_permissions();
			
			if (!$permissions['create'])
			{
				return false;
			}
		}
	
		/*!
		
			@function add_suffix
			@abstract Insert a new suffix in the DB
			@author Raphael Derosso Pereira
		
			@param string $suffix The suffix
			@return integer The new ID
			
		*/ 
		function add_suffix ( $suffix )
		{
			$permissions = $this->security->get_type_permissions();
			
			if (!$permissions['create'])
			{
				return false;
			}
		}
	
		/*!
		
			@function add_status
			@abstract Insert a new Status in the DB
			@author Raphael Derosso Pereira
		
			@param string $status The Status
			@return integer The new ID
			
		*/ 
		function add_status ( $status )
		{
			$permissions = $this->security->get_type_permissions();
			
			if (!$permissions['create'])
			{
				return false;
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
	


		/*********************************************************************\
		 *                   Methods to Alter Data                           *
		\*********************************************************************/

		/*!
		
			@function update_single_info
			@abstract Update information of an existing Contact
			@author Raphael Derosso Pereira
		
			@param integer $id_status The Contact ID
			@param string $status The new Status value
			
		*/ 
		function update_single_info ( $id_contact, $data )
		{
			$permissions = $this->security->get_permissions($id_contact);
			
			if (!$permissions['write'])
			{
				return false;
			}
			
			$contact = CreateObject('contactcenter.so_contact', $id_contact);
			
			$altered = false;
//			print_r($data);
			foreach($data as $field => $value)
			{
				if ($value === false)
				{
					continue;
				}
				
				$altered = true;
				switch($field)
				{
					case 'corporate_name':
					case 'job_title':
					case 'department':
					case 'web_page':
					case 'photo':
					case 'alias':
					case 'id_prefix':
					case 'id_status':
					case 'id_suffix':
					case 'given_names':
					case 'family_names':
					case 'names_ordered':
					case 'birthdate':
					case 'sex':
					case 'pgp_key':
					case 'notes':
						$contact->set_field($field,$value);
						break;

					case 'companies':					
						foreach($value as $company_fields)
						{
							if ($company_fields['company_name'])
							{
								$fields = array('company.id_company');
								$restric = array(
									0 => array(
										'field' => 'company.company_name',
										'type'  => 'iLIKE',
										'value' => $company_fields['company_name']
									)
								);
								
								if($result = $this->find($fields,$restric))
								{
									$id = $result[0]['id_company'];
									$company_fields['id_company'] = $id;
								}
								else
								{
									$company = CreateObject('contactcenter.so_company');
									$company->reset_values();
									$company->set_company_name($company_fields['company_name']);
									$company->set_field('id_company_owner',$GLOBALS['phpgw_info']['user']['account_id']);
									$company_fields['id_company'] = $company->commit();
								}
							}
							
							$contact->set_company($company_fields);
						}
						break;
					
					case 'relations':
						foreach($value as $relation_fields)
						{
							$contact->set_relation($relation_fields['id_relation'], $relation_fields['id_typeof_relation']);
						}
						break;
					
					case 'addresses':
						foreach($value as $address_name => $address_fields)
						{
							if ($address_fields['id_address'] && $address_fields['id_address'] !== '')
							{
								$address = CreateObject('contactcenter.so_address', $address_fields['id_address']);
							}
							else
							{
								$address = CreateObject('contactcenter.so_address');
								$address->reset_values();
							}

							if (!isset($address_fields['id_country']) or $address_fields['id_country'] === false)
							{
								#var_dump($address_fields);
								echo(serialize(array(
									'file' => __FILE__,
									'line' => __LINE__,
									'msg'  => lang('An Address must have at least a Country'),
									'status' => 'aborted'
								)));
								return;
							}
							
							foreach ($address_fields as $f_name => $f_value)
							{
								if ($f_value === false)
								{
									$address->set_field($f_name, null);
								}
								elseif (isset($f_value))
								{
									$address->set_field($f_name, $f_value);
								}
							}
								
							$address->commit();
							$id_address = $address->get_id();
							$contact->set_address($id_address, $address_fields['id_typeof_address']);
						}
						break;
					
					case 'connections':
						foreach($value as $connection_name => $connection_fields)
						{
							if ($connection_name === 'removed_conns')
							{
								foreach($connection_fields as $id)
								{
									$connection = CreateObject('contactcenter.so_connection', $id);
									if (!($connection->remove()))
									{
										return false;
									}

									$contact->remove_connection($id);
								}

								continue;
							}
							
							$id_connection = $connection_fields['id_connection'];
							if ($id_connection === '_NEW_' or
							    $id_connection === '')
							{
								$connection = CreateObject('contactcenter.so_connection');
								$connection->reset_values();
							}
							else
							{
								$connection = CreateObject('contactcenter.so_connection', $id_connection);
							}
								
							foreach($connection_fields as $a_field => $a_value)
							{
								if ($a_field !== 'id_typeof_connection')
								{
									$connection->set_field($a_field,$a_value);
								}
							}
							
							if (!$connection->commit())
							{
								return false;
							}
							$id_connection = $connection->get_id();
							$contact->set_connection($id_connection, $connection_fields['id_typeof_connection']);
						}
			 			break;
			 			
			 		default:
						echo 'Invalid Field: '.$field.'<br>Value: '.$value;
			 			return false;
				}
			}
			
			if ($altered)
			{
				return $contact->commit();
			}
			
			return false;
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
			@abstract Update an existing suffix
			@author Raphael Derosso Pereira
		
			@param integer $id_suffix The suffix ID
			@param string $suffix The new suffix value
			
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
		
			@param integer $id_uype The Type ID
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


		/*********************************************************************\
		 *                   Methods to Remove Data                          *
		\*********************************************************************/

		/*!
		
			@function remove_single_entry
			@abstract Remove one contact from the DB
			@author Raphael Derosso Pereira
		
			@param integer $id_contact The Contact ID
			
		*/ 
		function remove_single_entry ( $id_contact )
		{
			$permissions = $this->security->get_permissions();
			
			if (!$permissions['remove'])
			{
				return false;
			}
			
			$contact = CreateObject('contactcenter.so_contact', $id_contact);
			
			if ($contact->get_state() === 'new')
			{
				return false;
			}
			
			$addresses = $contact->get_addresses();
			$connections = $contact->get_connections();
			
			if (!$contact->remove())
			{
				return false;
			}
			
			foreach ($addresses as $id => $type)
			{
				if (!($address = CreateObject('contactcenter.so_address', $id)))
				{
					return false;
				}
				
				if (!($address->remove()))
				{
					return false;
				}
				
			}

			foreach ($connections as $id => $type)
			{
				if (!($connection = CreateObject('contactcenter.so_connection',$id)))
				{					
					return false;
				}
				
				if (!($connection->remove()))
				{
					return false;
				}
				
			}
			
			return true;
		}
	}
?>
