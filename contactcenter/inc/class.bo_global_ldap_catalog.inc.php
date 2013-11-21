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

	/*
		This class is responsible for manipulating the Global LDAP Contact Manager
	*/
	include_once('class.abo_catalog.inc.php');
	
	class bo_global_ldap_catalog extends abo_catalog
	{
		var $ldap;
	
		var $src_info;
		
		var $trans_table;
		// used to determine if a catalog is external
		var $external;
		var $fields = array(
			'id_contact'    => true,
			'status'        => true,
			'photo'         => true,
			'alias'         => true,
			'prefix'        => true,
			'given_names'   => true,
			'family_names'  => true,
			'account_type'  => true,
			'names_ordered' => true,
			'suffix'        => true,
			'birthdate'     => true,
			'sex'           => true,
			'pgp_key'       => true,
			'notes'         => true,
			'companies'     => true,
			'relations'     => true,
			'addresses'     => true,
			'connections'   => true
		);
		
		/*
		
			@function global_ldap_catalog
			@abstract Constructor
			@author Raphael Derosso Pereira
			@author Mário César Kolling (external catalogs)

			@param integer $id_source The ID of the LDAP source
			@param string $context Ldap bind DN
			@param integer $external 0 = internal catalog, 1 = external catalog
		*/
		function bo_global_ldap_catalog ( $id_source, $context, $external = 0 )
		{
			$this->external = $external;
			if (!function_exists('ldap_search'))
			{
				exit('PHP LDAP support Unavailable!');
			}
			
			$this->ldap = CreateObject('contactcenter.bo_ldap_manager');
			
			if ($this->external)
			{
				$all_src = $this->ldap->get_external_ldap_sources();
			}
			else
			{
				$all_src = $this->ldap->get_all_ldap_sources();
			}

			if (!$all_src[$id_source] or !$context)
			{
				exit('Unavailable LDAP source.');
			}

			$this->src_info = $all_src[$id_source];
			$this->src_info['context'] = $context;

			if ($this->external)
			{
				$this->trans_table = $this->ldap->get_external_ldap_fields_association($id_source);
			}
			else
			{	
				$this->trans_table = $this->ldap->get_ldap_fields_association($id_source);
			}
		}
		
		/*
		
			@function find
			@abstract Searches the LDAP directory for the specified fields with
				the specified rules and retuns an array containing all the DNs
				that matches the rules.
			@author Raphael Derosso Pereira
			
			@param array $what The fields to be taken
			@param array $rules The rules to be match. See class.abo_catalog.inc.php
				for reference
			@param array $other Other parameters:
				$return = array(
					'limit'  => (integer),
					'offset' => (integer) [NOT IMPLEMENTED]
				)
		
		*/
		function find($what, $rules=false, $other=false, $area=false, $recursive=false)
		{
		    $find = '';
		    if($rules)
		    {
			foreach ($rules as $rule)
			{
			    if($rule['field'] == 'contact.names_ordered' && $find === '')
				$find .= $rule['value'];
			    else if($rule['field'] == 'contact.names_ordered')
				$find .= ' '.$rule['value'];
			}
		    }

		    if(!((strlen($find) == 2 && substr($find, -1) == '%') || $find == '%' ) && !$this->external)
                          return $this->findAddress($find,$other);

		    $restric_fields = $this->get_restrictions_without_branch($rules);

		    $trans_f = $this->translate_fields($what, $restric_fields);

		    foreach($trans_f as $orig => $field_a)
		    {
			foreach($field_a as $field)
			{
			    $fields[] = $field;
			}
		    }

		    $fields = array_unique($fields);

		    //Testa se a busca foi realizada com aspas
		    $rules_search = $rules[3]['value'];
		    $rules_len = (strlen($rules_search)) -1;

		    if((($rules_search{1}) == "\"") && (($rules_search{$rules_len -1}) == "\"")){
					$rules_search = substr($rules_search,2,$rules_len-3);
					$filter = "(&(|(objectClass=phpgwAccount))(&(!(phpgwAccountVisible=-1)))(|(cn=* $rules_search *)(cn=$rules_search *)(cn=* $rules_search)))";
		    }
		    else{
					$filter = $this->process_restrictions($rules, $trans_f);
		    }

		    // Find objects where 'mail' attribute is not null.
		    $filter = "(&".$filter."(mail=*))";
			$ldap = $GLOBALS['phpgw']->common->ldapConnect($this->src_info['host'], $this->src_info['acc'], $this->src_info['pw'], true);
			$result_r = $recursive ? ldap_search($ldap , $this->src_info['context'], $filter, $fields, 0, $this->src_info['max_results']) :
			//Traz apenas 10 resultados,
			//ldap_list($ldap , $this->src_info['context'], $filter, $fields, 0, $this->src_info['max_results']);
			//Traz todos os usuários
			ldap_list($ldap , $this->src_info['context'], $filter, $fields, 0, 0);

			 if (!$result_r)
			{
				return false;
			}

		    if ($other['order'])
		    {
			    $sort_f = array($other['order']);
			    $ldap_sort_by = $this->translate_fields($sort_f, $restric_fields);
			}

		    if ($ldap_sort_by)
		    {
				if (!ldap_sort($ldap, $result_r, $ldap_sort_by[$other['order']][0]))
				{
				    return false;
			    }
		    }

		    $iTotalEntries = ldap_count_entries( $ldap, $result_r );

		    if($iTotalEntries < 1 )
			    return true;

		    $iEnd = $iTotalEntries;

		    $return = array();

		    $rEntry = ldap_first_entry( $ldap, $result_r );
		    for ( $iCurrent = 0; $iCurrent < $iEnd ;++$iCurrent)
		    {

			$result_p = ldap_get_attributes($ldap, $rEntry );

			if(!$this->external and $result_p['phpgwaccountvisible'][0] == '-1'){
			    continue;
			}

			$returnTemp = array();
			$returnTemp['id_contact'] = ldap_get_dn($ldap, $rEntry);
			$returnTemp['names_ordered'] = $result_p['cn']['0'];

			array_push( $return, $returnTemp );
			$rEntry = ldap_next_entry( $ldap, $rEntry );

		    }


		    usort($return, array($this, "compareObjects"));
		    return $return;
		}

 		function findAddress($find, $other=false)
		{

                    require_once dirname(__FILE__).'/../../services/class.servicelocator.php';
                    $ldapService = ServiceLocator::getService('ldap');

                    if($other['customFilter'])
                        $filter =  $ldapService->getSearchFilter( $other['CN'], false, $find, $other['exact'] );
                    else
                        $filter =  $ldapService->getSearchFilter( $find );

                    $fields = array('cn','dn');
                    $ldap = $GLOBALS['phpgw']->common->ldapConnect($this->src_info['host'], $this->src_info['acc'], $this->src_info['pw'], true);
                    $result_r = ldap_search($ldap , $this->src_info['context'], $filter, $fields);


                    if (!$result_r)
                    {
                            return false;
                    }

                    if ($other['order'])
                    {
                            $sort_f = array($other['order']);
                            $ldap_sort_by = $this->translate_fields($sort_f, $restric_fields);
                    }

                    if ($ldap_sort_by)
                    {
                            if (!ldap_sort($ldap, $result_r, $ldap_sort_by[$other['order']][0]))
                            {
                                    return false;
                            }
                    }

                    $iTotalEntries = ldap_count_entries( $ldap, $result_r );

                    if($iTotalEntries < 1 )
                            return true;

                    $iEnd = $iTotalEntries;

                    $return = array();

                    $rEntry = ldap_first_entry( $ldap, $result_r );
                    for ( $iCurrent = 0; $iCurrent < $iEnd ;++$iCurrent)
                    {

                        $result_p = ldap_get_attributes($ldap, $rEntry );

                        $returnTemp = array();
                        $returnTemp['id_contact'] = ldap_get_dn($ldap, $rEntry);
                        $returnTemp['names_ordered'] = $result_p['cn']['0'];

                        array_push( $return, $returnTemp );
                        $rEntry = ldap_next_entry( $ldap, $rEntry );

                    }


                    usort($return, array($this, "compareObjects"));
                    return $return;
		}

		// Compare function for usort.
		function compareObjects($a, $b)	{
			return strnatcasecmp($a['names_ordered'], $b['names_ordered']);
		}
		
		/*
		
			@function translate_fields
			@abstract Return the LDAP objectClass fields that corresponds to the
				specified parameter fields
			@author Raphael Derosso Pereira
			
			@param array $fields The fields in the standard ContactCenter format
			@param array $rules The rules
		
		*/
		function translate_fields ( $fields, &$restric_fields )
		{
			$return = array();
			
			$i = 0;
			foreach ($fields as $field)
			{
				if (!array_key_exists($field,$this->trans_table) or !$this->trans_table[$field])
				{
					continue;
				}
				
				if (!is_array($this->trans_table[$field]))
				{
					$reference = $this->trans_table[$field];
					
					reset($restric_fields);
					while(list(,$field_r) = each($restric_fields))
					{
						if ($field_r['field'] === $reference and array_key_exists($field_r['value'], $this->trans_table[$reference]))
						{
							array_push($return[$field], $this->trans_table[$reference][$field_r['value']]);
						}
					}
				}
				else
				{
					if (!is_array($return[$field]))
					{
						$return[$field] = $this->trans_table[$field];
					}
					else
					{
						array_push($return[$field], $this->trans_table[$field]);
					}
				}
			}
			
			if (count($return))
			{
				return $return;
			}
			
			return false;
		}
		
		/*
		
			@function process_restrictions
			@abstract Returns a LDAP filter string that corresponds to the
				specified restriction rules
			@author Raphael Derosso Pereira
			
			@param string $rules The restriction rules
		
		*/
		function process_restrictions( &$rules, &$trans_table, $join_type='&' )
		{
			if (!is_array($rules) or !count($rules))
			{
				return null;
			}
			
			foreach($rules as $rule_i => $rule)
			{
				$t = array();
				switch($rule['type'])
				{
					case 'branch':
						switch(strtoupper($rule['value']))
						{
							case 'OR':
								$join = '|';
								break;
								
							case 'AND':
								$join = '&';
								break;
								
							case 'NOT':
								$join = '!';
								break;
								
							default:
								$join = $join_type;
						}
						$return_t[] = $this->process_restrictions($rule['sub_branch'], $trans_table, $join);
						break;
						
					case '=':
						if (array_key_exists($rule['field'], $trans_table))
						{
							foreach($trans_table[$rule['field']] as $field)
							{
								$t[] = '('.$field.'='.$rule['value'].')';
							}
							$return_t[] = '(|'.implode(' ',$t).')';
						}
						break;
					
					case '!=':	
						if (array_key_exists($rule['field'], $trans_table))
						{
							foreach($trans_table[$rule['field']] as $field)
							{
								$t[] = '(!('.$field.'='.$rule['value'].'))';
							}
							$return_t[] = '(&'.implode(' ',$t).')';
						}
						break;
					
					case '<=':
					case '<':
						if (array_key_exists($rule['field'], $trans_table))
						{
							foreach($trans_table[$rule['field']] as $field)
							{
								$t[] = '('.$field.'<='.$rule['value'].')';
							}
							$return_t[] = '(|'.implode(' ',$t).')';
						}
						break;
					
					case '>':
					case '>=':
						if (array_key_exists($rule['field'], $trans_table))
						{
							foreach($trans_table[$rule['field']] as $field)
							{
								$t[] = '('.$field.'>='.$rule['value'].')';
							}
							$return_t[] = '(|'.implode(' ',$t).')';
						}
						break;
						
					case 'NULL':
						if (array_key_exists($rule['field'], $trans_table))
						{
							foreach($trans_table[$rule['field']] as $field)
							{
								$t[] = '(!('.$field.'=*'.'))';
							}
							$return_t[] = '(&'.implode(' ',$t).')';
						}
						break;
					
					case 'IN':
						if (array_key_exists($rule['field'], $trans_table))
						{
							foreach($trans_table[$rule['field']] as $field)
							{
								foreach($rule['value'] as $value)
								{
									$t[] = '('.$field.'='.$value.')';
								}
							}
							$return_t[] = '(|'.implode(' ',$t).')';
						}
						break;

					case 'iLIKE':
/*						if (array_key_exists($rule['field'], $trans_table))
						{
							$value_1 = strtoupper(str_replace('%', '*', $rule['value']));
							$value_2 = strtolower($value_1);
							
							foreach($trans_table[$rule['field']] as $field)
							{
								$t[] = '('.$field.'='.$value_1.')';
								$t[] = '('.$field.'='.$value_2.')';
							}
							$return_t[] = '(|'.implode(' ',$t).')';
						}
						break;
						
*/					case 'LIKE':
						if (array_key_exists($rule['field'], $trans_table))
						{
							$value = str_replace('%', '*', $rule['value']);
							
							foreach($trans_table[$rule['field']] as $field)
							{
								$t[] = '('.$field.'='.$value.')';
							}
							$return_t[] = '(|'.implode(' ',$t).')';
						}
						break;
						
					case 'NOT NULL':
						if (array_key_exists($rule['field'], $trans_table))
						{
							foreach($trans_table[$rule['field']] as $field)
							{
								$t[] = '('.$field.'=*'.')';
							}
							$return_t[] = '(|'.implode(' ',$t).')';
						}
						break;
					
					case 'NOT IN':
						if (array_key_exists($rule['field'], $trans_table))
						{
							foreach($trans_table[$rule['field']] as $field)
							{
								foreach($rule['value'] as $value)
								{
									$t[] = '('.$field.'='.$value.')';
								}
							}
							$return_t[] = '(!(|'.implode('',$t).'))';
						}
						break;

					case 'NOT iLIKE':
						if (array_key_exists($rule['field'], $trans_table))
						{
							$value_1 = strtoupper(str_replace('%', '*', $rule['value']));
							$value_2 = strtolower($value_1);
							
							foreach($trans_table[$rule['field']] as $field)
							{
								$t[] = '('.$field.'='.$value_1.')';
								$t[] = '('.$field.'='.$value_2.')';
							}
							$return_t[] = '(!(|'.implode(' ',$t).'))';
						}
						break;

					case 'NOT LIKE':
						if (array_key_exists($rule['field'], $trans_table))
						{
							$value = str_replace('%', '*', $rule['value']);
							
							foreach($trans_table[$rule['field']] as $field)
							{
								$t[] = '('.$field.'='.$value.')';
							}
							$return_t[] = '(!(|'.implode(' ',$t).'))';
						}
						break;
						
						case 'LIKE and ~=': 
							if (array_key_exists($rule['field'], $trans_table)) 
							{ 
								$value = str_replace('%', '*', $rule['value']); 
 	
								foreach($trans_table[$rule['field']] as $field) 
								{ 
									$t[] = '('.$field.'=*'.$value.'*)'.'('.$field.'~='.$value.')'; 
								} 
								$return_t[] = '(|'.implode(' ',$t).')'; 
							} 
						break; 
				}
			}
			
			if (count($return_t))
			{
				$return = '('.$join_type;
				foreach ($return_t as $return_p)
				{
					$return .= $return_p;
				}
				$return .= ')';
			}
			else
			{
				$return = null;
			}
			return $return;
		}

		/*!
		
			@function get_restrictions_without_branch
			@abstract Returns an array containing the restrictions ignoring the
				branches
			@author Raphael Derosso Pereira
			
			@param array $restrictions The restrictions
		
		*/
		function get_restrictions_without_branch(&$restrictions)
		{
			if (!is_array($restrictions))
			{
				return null;
			}
			
			$fields = array();
			
			foreach ($restrictions as $restrict_data)
			{
				switch($restrict_data['type'])
				{
					case 'branch':
						$fields = array_merge($fields, $this->get_restrictions_without_branch($restrict_data['sub_branch']));
						break;
						
					case '=':
					case '!=':	
					case '<=':
					case '<':
					case '>':
					case '>=':
					case 'NULL':
					case 'IN':
					case 'LIKE':
					case 'iLIKE':
					case 'NOT NULL':
					case 'NOT IN':
					case 'NOT LIKE':
					case 'NOT iLIKE':
					case 'LIKE and ~=':
						array_push($fields, $restrict_data);
						break;
						
					default:
						exit('Error in '.__FILE__.' on '.__LINE__.'<br>The restriction type passed was: '.$restrict_data['type']);					
				}
			}
			
			return $fields;
		}
		
		
		/*********************************************************************\
		 *                        Methods to Get Data                        *
		\*********************************************************************/
		
	
		/*!
		
		 @function get_single_entry
		 @abstract Returns all information requested about one contact
		 @author Raphael Derosso Pereira
		     
		 @param integer $id_contact The contact ID
		 @param array $fields The array returned by get_fields with true
		 	on the fields to be taken.
		 	
		*/
		function get_single_entry ( $id_contact, $fields, $external=false )
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
			
			$ldap = $GLOBALS['phpgw']->common->ldapConnect($this->src_info['host'],$this->src_info['acc'],$this->src_info['pw'],true);
			
			if (!$ldap)
			{
				return false;
			}

			//Alteração feita especificamente para tratar problema da montagem do $id_contact
			if(!$external)
			{
				if($this->src_info['montaDN'] == "true")
				{
				$id_contact = preg_replace("/dc=(.*)/i",$this->src_info['dn'],$id_contact);
				}
			}
			
			$resource = @ldap_read($ldap, $id_contact, 'objectClass='.$this->src_info['obj']);
			$n_entries = @ldap_count_entries($ldap, $resource);
			if ( $n_entries > 1 or $n_entries < 1)
			{
				return false;
			}
			
			$first_entry = ldap_first_entry($ldap, $resource);
			$contact = ldap_get_attributes($ldap,$first_entry);
			if($contact['jpegPhoto']){	
				$contact['jpegPhoto'] = ldap_get_values_len ($ldap, $first_entry, "jpegPhoto"); 
			}

//			print_r($contact);
			
		//	$contact_data = $this->fields;
			
			foreach($fields as $field => $trueness)
			{
				if (!$trueness)
				{
					//unset($contact_data[$field]);
					continue;
				}
				
				switch ($field)
				{
					case 'companies':
						unset($l_fields);
						$l_fields['company_name']  = $this->trans_table['contact.company.company_name'];
						$l_fields['title']         = $this->trans_table['contact.business_info.title'];
						//$l_fields['department']    = $this->trans_table['contact.business_info.department'];
						$l_fields['company_notes'] = $this->trans_table['contact.company.company_notes'];

						//Relaciona o array com o atributo 'ou' do RHDS; tambem verifica se a
						//preferencia esta configurada para exibir o campo
						if(isset($_SESSION['phpgw_info']['user']['preferences']['contactcenter']['departmentShow']) && $_SESSION['phpgw_info']['user']['preferences']['contactcenter']['departmentShow'])
						{
							$l_fields['department']    = $this->trans_table['contact.business_info.department'];
						}
						//Relaciona o array com o atributo 'employeeNumber' do RHDS; tambem verifica se a
						//preferencia esta configurada para exibir o campo
						if(isset($_SESSION['phpgw_info']['user']['preferences']['contactcenter']['empNumShow']) && $_SESSION['phpgw_info']['user']['preferences']['contactcenter']['empNumShow'])
						{
							$l_fields['empNumber']     = $this->trans_table['contact.business_info.empNumber'];
						}
						//Relaciona o array com o atributo 'mobile' do RHDS; tambem verifica se a
						//preferencia esta configurada para exibir o campo
						if(isset($_SESSION['phpgw_info']['user']['preferences']['contactcenter']['cellShow']) && $_SESSION['phpgw_info']['user']['preferences']['contactcenter']['cellShow'])
						{
							$l_fields['celPhone']     = $this->trans_table['contact.business_info.celPhone'];
						}

						$contact_data['companies'] = array();
						foreach($l_fields as $l_field => $l_value)
						{
							if (!( $contact[$l_value[0]][0]))
							{
								continue;
							}
							
							$contact_data['companies']['company1'][$l_field] = utf8_decode($contact[$l_value[0]][0]);
						}
						
						if (!(count($contact_data['companies'])))
						{
							unset($contact_data['companies']);
						}
						break;
					
					case 'relations':
						unset($l_fields);
						if (!$this->trans_table['contact.contact_related.names_ordered'])
						{
							unset($contact_data['relations']);
						}
						
						$contact_data['relations'] = array();
						if (!is_array($this->trans_table['contact.contact_related.names_ordered']))
						{
							if (!($trans = $this->trans_table[$this->trans_table['contact.contact_related.names_ordered']]))
							{
								continue;
							}
							
							$i = 1;
							foreach($trans as $l_type => $l_type_fields)
							{
								if (!($contact[$l_type_fields[0]][0]))
								{
									continue;
								}
								
								$contact_data['relations']['relation'.$i]['type'] = $l_type;
								$contact_data['relations']['relation'.$i]['names_ordered'] = utf8_decode($contact[$l_type_fields[0]][0]);
								++$i;
							}
						}
						
						if (!(count($contact_data['relations'])))
						{
							unset($contact_data['relations']);
						}
						break;
					
					case 'addresses':
						unset($l_fields);
						$l_fields['address1'] = $this->trans_table['contact.address.address1'];
				 		$l_fields['address2'] = $this->trans_table['contact.address.address2'];
				 		$l_fields['complement'] = $this->trans_table['contact.address.complement'];
				 		$l_fields['address_other'] = $this->trans_table['contact.address.address_other'];
						$l_fields['postal_code'] = $this->trans_table['contact.address.postal_code'];
				 		$l_fields['po_box'] = $this->trans_table['contact.address.po_box'];
				 		$l_fields['id_city'] = $this->trans_table['contact.address.city.id_city'];
						$l_fields['city_name'] = $this->trans_table['contact.address.city.city_name'];
						$l_fields['city_timezone'] = $this->trans_table['contact.address.city.city_timezone'];
						$l_fields['city_geo_location'] = $this->trans_table['contact.address.city.city_geo_location'];
						$l_fields['id_state'] = $this->trans_table['contact.address.city.state.id_state'];
						$l_fields['state_name'] = $this->trans_table['contact.address.city.state.state_name'];
						$l_fields['state_symbol'] = $this->trans_table['contact.address.city.state.state_symbol'];
						$l_fields['id_country'] = $this->trans_table['contact.address.city.country.id_country'];
						$l_fields['country_name'] = $this->trans_table['contact.address.city.country.country_name'];
				 		$l_fields['address_is_default'] = $this->trans_table['contact.address.address_is_default'];

						$contact_data['addresses'] = array();
						foreach($l_fields as $l_field => $l_value)
						{
							if (!is_array($l_value))
							{
								if (!($trans = $this->trans_table[$l_value]))
								{
									continue;
								}
								
								$i = 1;
								foreach($trans as $l_type => $l_type_fields)
								{
									if (!($contact[$l_type_fields[0]][0]))
									{
										continue;
									}
									
									$contact_data['addresses']['address'.$i]['type'] = $l_type;
									$contact_data['addresses']['address'.$i][$l_field] = utf8_decode($contact[$l_type_fields[0]][0]);
									++$i;
								}
							}
							else
							{
								$contact_data['addresses']['address1'][$l_field] = utf8_decode($contact[$l_value[0]][0]);
							}
						}
						
						if (!(count($contact_data['addresses'])))
						{
							unset($contact_data['addresses']);
						}
						break;
					
					case 'connections':
	                    $preferences = ExecMethod('contactcenter.ui_preferences.get_preferences');

                                                if(!array_key_exists('personCardEmail', $preferences)) $preferences['personCardEmail'] = 1;
                                                if(!array_key_exists('personCardPhone', $preferences)) $preferences['personCardPhone'] = 2;        		
                            
						unset($l_fields);
				 		$l_fields['connection_name'] = $this->trans_table['contact.connection.connection_name'];
				 		$l_fields['connection_value'] = $this->trans_table['contact.connection.connection_value'];

						$contact_data['connections'] = array();
						foreach($l_fields as $l_field => $l_value)
						{
							if (!is_array($l_value))
							{
								if (!($trans = $this->trans_table[$l_value]))
								{
									continue;
								}
								
								$i = 1;
								foreach($trans as $l_type => $l_type_fields)
								{
									if (!($contact[$l_type_fields[0]][0]))
									{
										continue;
									}
									
									switch ($l_type)
									{
										case 'email':
										$contact_data['connections']['connection'.$i]['id_type'] = $preferences['personCardEmail'];
										break;

										default:
										$contact_data['connections']['connection'.$i]['id_type'] = $preferences['personCardPhone'];
									}
									$contact_data['connections']['connection'.$i]['type'] = $l_type;
									$contact_data['connections']['connection'.$i][$l_field] = utf8_decode($contact[$l_type_fields[0]][0]);
									++$i;
								}
							}
							else
							{
								$contact_data['connections']['connection1'][$l_field] = utf8_decode($contact[$l_value[0]][0]);
							}
						}
						
						if (!(count($contact_data['connections'])))
						{
							unset($contact_data['connections']);
						}
						break;
					
					case 'prefix':
						unset($l_fields);
						$l_fields = $this->trans_table['contact.prefixes.prefix'];
						if (!$l_fields or !$contact[$l_fields[0]][0])
						{
							unset($contact_data['prefix']);
							continue;
						}
						
						$contact_data['prefix'] = utf8_decode($contact[$l_fields[0]][0]);
						break;
						
					case 'suffix':
						unset($l_fields);
						$l_fields = $this->trans_table['contact.suffixes.suffix'];
						if (!$l_fields or !$contact[$l_fields[0]][0])
						{
							unset($contact_data['suffix']);
							continue;
						}
						
						$contact_data['suffix'] = utf8_decode($contact[$l_fields[0]][0]);
						break;
						
					case 'status':
						unset($l_fields);
						$l_fields = $this->trans_table['contact.status.status_name'];
						if (!$l_fields or !$contact[$l_fields[0]][0])
						{
							unset($contact_data['status']);
							continue;
						}
						
						$contact_data['status'] = utf8_decode($contact[$l_fields[0]][0]);
						break;
						
						case 'photo':
						unset($l_fields);
						$l_fields = $this->trans_table['contact.photo'];
						if (!$l_fields or !$contact[$l_fields[0]][0])
						{
							unset($contact_data['photo']);
							continue;
						}
						
						$contact_data['photo'] = $contact[$l_fields[0]][0];
						break;											

					default:
						unset($l_fields);
						$l_fields = $this->trans_table['contact.'.$field];
						if (!$l_fields or !$contact[$l_fields[0]][0])
						{
							unset($contact_data[$field]);
							continue;
						}
						
						if(count($contact[$l_fields[0]]) > 1)
						{
							$tmp = array();
							foreach ($contact[$l_fields[0]] as $i => $j)
							{
								$tmp["$i"] = utf8_decode($j);
							}
							//$contact_data[$field] = $contact[$l_fields[0]];
							$contact_data[$field] = $tmp;
						}
						else
							$contact_data[$field] = utf8_decode($contact[$l_fields[0]][0]);

						break;
				}
			}
			
			if (!is_array($contact_data))
			{
				return false;
			}
			
			return $contact_data;
		}
		//SERPRO
		/*!

		 @function get_all_entries
		 @abstract Returns all information requested about a bunch of contacts, usually a page
		 @author Raphael Derosso Pereira
		 @author Mário César Kolling

		 @param string $filter Filter (returned by generate_filter).
		 @param array $fields The array returned by get_fields with true
		 	on the fields to be taken.

		*/
		function get_all_entries($filter, $fields)
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

			$ldap = $GLOBALS['phpgw']->common->ldapConnect($this->src_info['host'],$this->src_info['acc'],$this->src_info['pw'],true);

			if (!$ldap)
			{
				return false;
			}

			$resource = @ldap_search($ldap, $this->src_info['dn'], $filter);
			$n_entries = @ldap_count_entries($ldap, $resource);

			ldap_sort($ldap, $resource, 'cn');

			if ( $n_entries < 1)
			{
				return false;
			}

			$contacts = array();

			for ($entry = ldap_first_entry($ldap, $resource);
				 $entry != false;
				 $entry = ldap_next_entry($ldap, $entry))
			{
				$contact = ldap_get_attributes($ldap,$entry);
				if($contact['jpegPhoto']){
					$contact['jpegPhoto'] = ldap_get_values_len ($ldap, $entry, "jpegPhoto");
				}

				foreach($fields as $field => $trueness)
				{
					if (!$trueness)
					{
						//unset($contact_data[$field]);
						continue;
					}

					switch ($field)
					{
						case 'companies':
							unset($l_fields);
							$l_fields['company_name']  = $this->trans_table['contact.company.company_name'];
							$l_fields['title']         = $this->trans_table['contact.business_info.title'];
							$l_fields['department']    = $this->trans_table['contact.business_info.department'];
							$l_fields['company_notes'] = $this->trans_table['contact.company.company_notes'];

							//Relaciona o array com o atributo 'employeeNumber' do RHDS
							if(isset($_SESSION['phpgw_info']['user']['preferences']['contactcenter']['empNumberShow']) && $_SESSION['phpgw_info']['user']['preferences']['contactcenter']['empNumberShow'])
							{
								$l_fields['empNumber']     = $this->trans_table['contact.business_info.empNumber'];
							}

							//Relaciona o array com o atributo 'mobile' do RHDS
							$l_fields['celPhone']     = $this->trans_table['contact.business_info.celPhone'];

							$contact_data['companies'] = array();
							foreach($l_fields as $l_field => $l_value)
							{
								if (!( $contact[$l_value[0]][0]))
								{
									continue;
								}

								$contact_data['companies']['company1'][$l_field] = utf8_decode($contact[$l_value[0]][0]);
							}

							if (!(count($contact_data['companies'])))
							{
								unset($contact_data['companies']);
							}
							break;

						case 'relations':
							unset($l_fields);
							if (!$this->trans_table['contact.contact_related.names_ordered'])
							{
								unset($contact_data['relations']);
							}

							$contact_data['relations'] = array();
							if (!is_array($this->trans_table['contact.contact_related.names_ordered']))
							{
								if (!($trans = $this->trans_table[$this->trans_table['contact.contact_related.names_ordered']]))
								{
									continue;
								}

								$i = 1;
								foreach($trans as $l_type => $l_type_fields)
								{
									if (!($contact[$l_type_fields[0]][0]))
									{
										continue;
									}

									$contact_data['relations']['relation'.$i]['type'] = $l_type;
									$contact_data['relations']['relation'.$i]['names_ordered'] = utf8_decode($contact[$l_type_fields[0]][0]);
									++$i;
								}
							}

							if (!(count($contact_data['relations'])))
							{
								unset($contact_data['relations']);
							}
							break;

						case 'addresses':
							unset($l_fields);
							$l_fields['address1'] = $this->trans_table['contact.address.address1'];
					 		$l_fields['address2'] = $this->trans_table['contact.address.address2'];
					 		$l_fields['complement'] = $this->trans_table['contact.address.complement'];
					 		$l_fields['address_other'] = $this->trans_table['contact.address.address_other'];
							$l_fields['postal_code'] = $this->trans_table['contact.address.postal_code'];
					 		$l_fields['po_box'] = $this->trans_table['contact.address.po_box'];
					 		$l_fields['id_city'] = $this->trans_table['contact.address.city.id_city'];
							$l_fields['city_name'] = $this->trans_table['contact.address.city.city_name'];
							$l_fields['city_timezone'] = $this->trans_table['contact.address.city.city_timezone'];
							$l_fields['city_geo_location'] = $this->trans_table['contact.address.city.city_geo_location'];
							$l_fields['id_state'] = $this->trans_table['contact.address.city.state.id_state'];
							$l_fields['state_name'] = $this->trans_table['contact.address.city.state.state_name'];
							$l_fields['state_symbol'] = $this->trans_table['contact.address.city.state.state_symbol'];
							$l_fields['id_country'] = $this->trans_table['contact.address.city.country.id_country'];
							$l_fields['country_name'] = $this->trans_table['contact.address.city.country.country_name'];
					 		$l_fields['address_is_default'] = $this->trans_table['contact.address.address_is_default'];

							$contact_data['addresses'] = array();
							foreach($l_fields as $l_field => $l_value)
							{
								if (!is_array($l_value))
								{
									if (!($trans = $this->trans_table[$l_value]))
									{
										continue;
									}

									$i = 1;
									foreach($trans as $l_type => $l_type_fields)
									{
										if (!($contact[$l_type_fields[0]][0]))
										{
											continue;
										}

										$contact_data['addresses']['address'.$i]['type'] = $l_type;
										$contact_data['addresses']['address'.$i][$l_field] = utf8_decode($contact[$l_type_fields[0]][0]);
										++$i;
									}
								}
								else
								{
									$contact_data['addresses']['address1'][$l_field] = utf8_decode($contact[$l_value[0]][0]);
								}
							}

							if (!(count($contact_data['addresses'])))
							{
								unset($contact_data['addresses']);
							}
							break;

						case 'connections':
		                    $preferences = ExecMethod('contactcenter.ui_preferences.get_preferences');
		                    if (!is_array($preferences))
		                    {
								$preferences['personCardEmail'] = 1;
								$preferences['personCardPhone'] = 2;
							}
							unset($l_fields);
					 		$l_fields['connection_name'] = $this->trans_table['contact.connection.connection_name'];
					 		$l_fields['connection_value'] = $this->trans_table['contact.connection.connection_value'];

							$contact_data['connections'] = array();
							foreach($l_fields as $l_field => $l_value)
							{
								if (!is_array($l_value))
								{
									if (!($trans = $this->trans_table[$l_value]))
									{
										continue;
									}

									$i = 1;
									foreach($trans as $l_type => $l_type_fields)
									{
										if (!($contact[$l_type_fields[0]][0]))
										{
											continue;
										}

										switch ($l_type)
										{
											case 'email':
											$contact_data['connections']['connection'.$i]['id_type'] = $preferences['personCardEmail'];
											break;

											default:
											$contact_data['connections']['connection'.$i]['id_type'] = $preferences['personCardPhone'];
										}
										$contact_data['connections']['connection'.$i]['type'] = $l_type;
										$contact_data['connections']['connection'.$i][$l_field] = utf8_decode($contact[$l_type_fields[0]][0]);
										++$i;
									}
								}
								else
								{
									$contact_data['connections']['connection1'][$l_field] = utf8_decode($contact[$l_value[0]][0]);
								}
							}

							if (!(count($contact_data['connections'])))
							{
								unset($contact_data['connections']);
							}
							break;

						case 'prefix':
							unset($l_fields);
							$l_fields = $this->trans_table['contact.prefixes.prefix'];
							if (!$l_fields or !$contact[$l_fields[0]][0])
							{
								unset($contact_data['prefix']);
								continue;
							}

							$contact_data['prefix'] = utf8_decode($contact[$l_fields[0]][0]);
							break;

						case 'suffix':
							unset($l_fields);
							$l_fields = $this->trans_table['contact.suffixes.suffix'];
							if (!$l_fields or !$contact[$l_fields[0]][0])
							{
								unset($contact_data['suffix']);
								continue;
							}

							$contact_data['suffix'] = utf8_decode($contact[$l_fields[0]][0]);
							break;

						case 'status':
							unset($l_fields);
							$l_fields = $this->trans_table['contact.status.status_name'];
							if (!$l_fields or !$contact[$l_fields[0]][0])
							{
								unset($contact_data['status']);
								continue;
							}

							$contact_data['status'] = utf8_decode($contact[$l_fields[0]][0]);
							break;

							case 'photo':
							unset($l_fields);
							$l_fields = $this->trans_table['contact.photo'];
							if (!$l_fields or !$contact[$l_fields[0]][0])
							{
								unset($contact_data['photo']);
								continue;
							}

							$contact_data['photo'] = $contact[$l_fields[0]][0];
							break;

						default:
							unset($l_fields);
							$l_fields = $this->trans_table['contact.'.$field];
							if (!$l_fields or !$contact[$l_fields[0]][0])
							{
								unset($contact_data[$field]);
								continue;
							}

							if(count($contact[$l_fields[0]]) > 1)
							{
								$tmp = array();
								foreach ($contact[$l_fields[0]] as $i => $j)
								{
									$tmp["$i"] = utf8_decode($j);
								}
								//$contact_data[$field] = $contact[$l_fields[0]];
								$contact_data[$field] = $tmp;
							}
							else
								$contact_data[$field] = utf8_decode($contact[$l_fields[0]][0]);

							break;

					}
				}

				if (is_array($contact_data))
				{
					$contacts[ldap_get_dn($ldap, $entry)] = $contact_data;
				}

			}

			return $contacts;
		}
		
		function get_multiple_entries ( $id_contacts, $fields, $other_data = false, $external=false )
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
			 
			foreach ($id_contacts as $id)
			{
				$contacts[$id] = $this->get_single_entry($id,$fields,$external);
			}
			
			return $contacts;

			// SERPRO
						
			/*$contacts = array();
	
			if ($other_data)
			{
				//TODO
			}

			$filter = $this->generate_filter($id_contacts);

			//$teste = $this->get_all_entries($filter, $fields);

			return $this->get_all_entries($filter, $fields);*/
			
		}

        // CELEPAR
		function generate_filter($id_contacts)
		{
			if (($size = count($id_contacts)))
			{
				$contacts[$id] = $this->get_single_entry($id,$fields);
			}
			
			return $contacts;
		}
		
		// SERPRO
		/*
		function generate_filter($id_contacts)
		{
			if (($size = count($id_contacts)))
			{
				$filter = '(&(objectClass='.$this->src_info['obj'] .  ')(|';
				for ($i = 0; $i < $size; ++$i)
				{

					// 
					//  Não utiliza mais a função ldap_explode, usa a expressão regular a seguir para pegar o primeiro
					//  componente da dn
					// 
					preg_match('/^(\w*=[^,]*),.*$/', $id_contacts[$i], $cn);

    				//
    				// Adicionados os str_replace para adicionar caracteres de escape em frente aos caracteres '(' e ')',
    				// posteriormente poderá ser necessário substituir por uma expressão regular mais genérica.
    				//

					if ($cn[1])
					{
						//
						// Esta operação resolve o problema causado pela conversão de caracteres acentuados realizada
						// pela função ldap_explode_dn(). Talvez seja necessário utilizar esta tradução em outros lugares,
						// neste caso é mais apropriado colocar dentro de uma função.
						//
						//foreach($cn as $key=>$value){
	          			//	$cn[$key]=preg_replace("/\\\([0-9A-Fa-f]{2})/e", "''.chr(hexdec('\\1')).''", $value);
	    				//}

						$filter .= '(' . str_replace(')', '\)', str_replace('(','\(',$cn[1])) . ')';
					}
				}
				$filter .= '))';
			}

			return $filter;

		}
		*/

		function get_all_entries_ids ()
		{
			$search_fields = array('contact.id_contact', 'contact.names_ordered');
			$search_rules  = array(
				0 => array(
					'field' => 'contact.names_ordered',
					'type'  => 'LIKE',
					'value' => '%'
				)
			);
			$search_other  = array('order' => 'contact.names_ordered');

			$result_i = $this->find($search_fields, $search_rules, $search_other);

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
		
		function get_relations ($id_contact,$extra=false)
		{
		}
		
		function get_addresses ( $id_contact,$extra=false )
		{
		}
		
		function get_connections ( $id_contact,$extra=false )
		{
		}
		
		function get_companies ( $id_contact, $extra=false )
		{
		}
		
		function get_all_prefixes (  )
		{
		}
		
		function get_all_suffixes (  )
		{
		}
		
		function get_all_status (  )
		{
		}
		
		function get_all_relations_types (  )
		{
		}
		
		function get_all_addresses_types (  )
		{
		}
		
		function get_all_connections_types (  )
		{
		}
		
		function get_vcard ( $id_contact )
		{
		}
		
		
		
		
		function get_global_tree ( $root )
		{
		}
	
		function get_actual_brach (  )
		{
		}
	
		function set_actual_branch ( $branch )
		{
		}
	}
?>
