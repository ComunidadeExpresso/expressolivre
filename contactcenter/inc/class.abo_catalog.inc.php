<?php
  /***************************************************************************\
  * eGroupWare - Contacts Center                                              *
  * http://www.egroupware.org                                                 *
  * Written by:                                                               *
  *  - Raphael Derosso Pereira <raphaelpereira@users.sourceforge.net>         *
  *  sponsored by Thyamad - http://www.thyamad.com
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/

	class abo_catalog
	{
		var $db;
		
		var $security;
		
		/*!
			@attr array Tables
			@abstract The main descriptor between high-level field indication
				and low-level table relations
			@author Raphael Derosso Pereira (conception and code)
			@author Vinicius Cubas Brand (conception)
		*/

		var $tables = array(
			'status' => array(
				'table' => 'phpgw_cc_status'
			),
			
			'prefix' => array(
				'table' => 'phpgw_cc_prefixes'
			),
			
			'suffix' => array(
				'table' => 'phpgw_cc_suffixes'
			),
			
			'contact' => array(
				'table'  => 'phpgw_cc_contact',
				
				'status' => 'id_status,id_status',
				'prefix' => 'id_prefix,id_prefix',
				'suffix' => 'id_suffix,id_suffix',
				
				'contact_related' => 'id_contact,id_contact',
				'related'         => array('shortcut' => 'contact_related'),
				
				'contact_connection' => 'id_contact,id_contact',
				'connection'         => array('path' => 'contact_connection'),

				'contact_address' => 'id_contact,id_contact',
				'address'         => array('path' => 'contact_address'),
				
				'business_info'   => 'id_contact,id_contact',
				'company'         => array('path' => 'business_info') 
			),
			
			'business_info' => array(
				'table'   => 'phpgw_cc_contact_company',
				'company' => 'id_company,id_company'
			),
			
			'group' => array('phpgw_cc_groups',
				'table'              => 'phpgw_cc_groups',
			),
			
			'company' => array(
				'table'              => 'phpgw_cc_company',
				
				'company_related'    => 'id_company.id_company',
				'related'            => array('shortcut' => 'company_related'),
				
				'company_address'    => 'id_company,id_company',
				'address'            => array('path' => 'company_address'),
				
				'company_connection' => 'id_company,id_company',
				'connection'         => array('path' => 'company_connection'),
				
				'business_info'      => 'id_company,id_company',
				'contact'            => array('path' => 'business_info'),
				
				'legal'              => 'id_company,id_company',
			),
			
			'company_related' => array(
				'table'                   => 'phpgw_cc_company_rels',
				'company'                 => 'id_related,id_company',

				'company_related'         => 'id_related,id_company',
				'related'                 => array('shortcut' => 'company_related'),

				'typeof_company_relation' => 'id_typeof_company_relation,id_typeof_company_relation',
				'typeof_relation'         => array('shortcut' => 'typeof_company_relation'),
				'type'                    => array('shortcut' => 'typeof_company_relation')
			),
			'contact_related' => array(
				'table'                   => 'phpgw_cc_contact_rels',
				'contact'                 => 'id_related,id_contact',
				
				'contact_related'         => 'id_contact,id_related',
				'related'                 => array('shortcut' => 'contact_related'),
				
				'typeof_contact_relation' => 'id_typeof_contact_relation,id_typeof_contact_relation',
				'typeof_relation'         => array('shortcut' => 'typeof_contact_relation'),
				'type'                    => array('shortcut' => 'typeof_contact_relation')
			),
			
			'company_address' => array(
				'table'          => 'phpgw_cc_company_addrs',
				'address'        => 'id_address,id_address',
				'typeof_address' => array('shortcut' => 'typeof_company_address'),
				'type'           => array('shortcut' => 'typeof_company_address')
			),
			'contact_address' => array(
				'table'          => 'phpgw_cc_contact_addrs',
				'address'        => 'id_address,id_address',
				'typeof_address' => array('shortcut' => 'typeof_contact_address'),
				'type'           => array('shortcut' => 'typeof_contact_address')
			),
			'address' => array(
				'table' => 'phpgw_cc_addresses',
				'city'  => 'id_city,id_city'
			),
			'city' => array(
				'table'   => 'phpgw_cc_city',
				'state'   => 'id_state,id_state',
				'country' => 'id_country,id_country'
			),
			'state' => array(
				'table'   => 'phpgw_cc_state',
				'country' => 'id_country,id_country',
			),
			'country' => array(
				'table' => 'phpgw_cc_country'
			),
			
			'company_connection' => array(
				'table'             => 'phpgw_cc_company_conns',
				'connection'        => 'id_connection,id_connection',
				'typeof_connection' => array('shortcut' => 'typeof_company_connection'),
				'type'              => array('shortcut' => 'typeof_company_connection')
			),
			'contact_connection' => array(
				'table'             => 'phpgw_cc_contact_conns',
				'connection'        => 'id_connection,id_connection',
				'typeof_connection' => array('shortcut' => 'typeof_contact_connection'),
				'type'              => array('shortcut' => 'typeof_contact_connection')
			),
			'connection' => array(
				'table'             => 'phpgw_cc_connections'
			),

			'legal' => array(
				'table'                => 'phpgw_cc_company_legals',
				'typeof_company_legal' => 'id_typeof_company_legal,id_typeof_company_legal',
				'typeof_legal'         => array('shortcut' => 'typeof_company_legal'),
				'type'                 => array('shortcut' => 'typeof_company_legal') 
			),
			
						
			'typeof_contact_relation' => array(
				'table' => 'phpgw_cc_typeof_ct_rels'
			),
			'typeof_company_relation' => array(
				'table' => 'phpgw_cc_typeof_co_rels'
			),
			'typeof_contact_address' => array(
				'table' => 'phpgw_cc_typeof_ct_addrs'
			),
			'typeof_company_address' => array(
				'table' => 'phpgw_cc_typeof_co_addrs'
			),
			'typeof_contact_connection' => array(
				'table' => 'phpgw_cc_typeof_ct_conns'
			),
			'typeof_company_connection' => array(
				'table' => 'phpgw_cc_typeof_co_conns'
			),
			'typeof_company_legal' => array(
				'table' => 'phpgw_cc_typeof_co_legals'
			)
		);
		
		function init()
		{
			$this->db = $GLOBALS['phpgw']->db;
			$this->security = CreateObject('contactcenter.security_manager');
		}

		/*!
		
			@function sql_find
			@abstract Performs a search in the DB based on the parameters
			@author Raphael Derosso Pereira (algorithm and code)
			@author Vinicius Cubas Brand (algorithm)
			
			@param array $what The list of fields to be returned. The format is:
				$what = array(
					'contact.company.company_name',
					'contact.names_ordered'
				);
			
			@param array $rules The restrictions.
			
			The restrictions format is quite complicated, but is very complete.
			As defined here, there is the possibility to do almost any type of
			search (tell me if you can't do any). For example, imagine the
			following search:
					
						and(a,or(d,e,and(f,g)))
			
			That is represented by the folloowing tree:
				
                                   and
                                    |
                  .--------------------.
                  |                    |
                a = 5                 or
                                       |
                          .---------.------------.
                          |         |            |
                       d != 10  e LIKE %a       and
                                                 |
                                             .-------.
                                             |       |
                                           f = 5   g < 10


			The rules that should be passed to the find function for this tree
			is:
				
	 			$rules = array(
 					0 => array(
 						'field' => 'A',
 						'type'  => '=',
 						'value' => 5
 					),
 					1 => array (
	 					'type'	     => 'branch',
	 					'value'	     => 'OR',
	 					'sub_branch' => array(
 							0 => array(
 								'field' => 'D'
 								'type'  => '!=',
 								'value' => 10
 							),
 							1 => array(
 								'field' => 'E',
 								'type'  => 'LIKE',
 								'value' => '%a'
 							)
 							2 => array(
 								'type'       => 'branch',
 								'value'      => 'AND',
 								'sub_branch' => array(
 									0 => array(
 										'field' => 'F',
 										'type'  => '=',
 										'value' => 5
 									),
 									1 => array(
 										'field' => 'G'
 										'type'  => '<',
 										'value' => 10
 									)
 								)
 							)
 						)
 					)
	 			);

  
			The restriction type can be: =, !=, <=, <, >, >=, NULL, IN, LIKE, 
			NOT NULL, NOT IN, NOT LIKE
			Value of branch can be AND, OR, NOT
			
			@param array $other Other parameter to the search 
				$other = array(
					'offset'          => (int),
					'limit'           => (int),
					'order'           => (string with field names separated by commas)
					'sort'            => {ASC|DESC},
					'fields_modifier' => (COUNT|MAX)
				);

			@return array $array[<field_name>][<row_number>]
				
		*/
		function sql_find($what, $rules=false, $other=false)
		{
			if (!is_array($what))
			{
				exit('Error');
			}
			
			$tables_def = $GLOBALS['phpgw']->db->get_table_definitions('contactcenter');
			
			$query_select = 'SELECT ';
			$query_from = array();
			$query_fields_joins = array();
			$query_restric_joins = array();
			$query_wheres = array();
			$tables_as = array();
			
			$n_fields = count($what);
			for($i = 0; $i < $n_fields; ++$i)
			{
				$path = $this->get_tables_by_field($what[$i], $tables_def);
				$n_tables = count($path);
				
				$fields[] = $path[$n_tables-1];
				$fields_translate[$what[$i]] = $path[$n_tables-1]; 
				
				list($from_t,) = explode('.',$path[0]);
				$query_from[] = $from_t;
				
				$last_non_unique = false; 
				for($j = 0; $j < $n_tables-1; $j += 2)
				{
					list($left_t,$left_k) = explode('.',$path[$j+1]);
					list($right_t,$right_k) = explode('.',$path[$j]);
					
					$query_join_t = 'LEFT JOIN '.$left_t.' AS t0'.$i.$j.' ON t0'.
					                 $i.$j.'.'.$left_k.'=';

					$query_join_reg = 'LEFT JOIN '.$left_t.' AS t[0-9]{2,} ON t[0-9]{2,}'.
					                 '\.'.$left_k.'=';
					
					$t = $j-2;
					if ($j == 0)
					{
						$query_join_t .= $path[$j];
						$query_join_reg .= $path[$j];
					}
					else
					{
						$query_join_t .= 't0'.$i.$t.'.'.$right_k;
						$query_join_reg .= 't[0-9]{2,}\.'.$right_k;
					}
					
					$query_fields_joins[] = $query_join_t;
					$tables_as[] = 't0'.$i.$j;
					continue;
					
					/* TODO: The code below detects the double JOIN lines, but
					 * the problem is that it doesn't do that correctly, because
					 * it should consider the hole path, not just one entry.
					 */
					$unique = true;
					if ($n_joins = count($query_fields_joins))
					{
						for($k = 0; $k < $n_joins; ++$k)
						{
							if (preg_match("/$query_join_reg/",$query_fields_joins[$k]))
							{	
								$unique = false;
								$last_non_unique = $k;	
							}
						}
					}

					if ($unique and $j != 0)
					{
						if ($j != 0)
						{
							if ($last_non_unique === false)
							{
								$query_join_t .= 't0'.$i.$t.'.'.$right_k;
							}
							else
							{
								$query_join_t .= $tables_as[$last_non_unique].'.'.$right_k;
							}
						}
						$query_fields_joins[] = $query_join_t;
						$tables_as[] = 't0'.$i.$j;
	
						$last_non_unique = false;
					}
				}
			}
			
			$restric_fields = $this->get_fields_from_restrictions($rules);
			$n_restrictions = count($restric_fields);
			
			for ($i = 0; $i < $n_restrictions; ++$i)
			{
				$path = $this->get_tables_by_field($restric_fields[$i], $tables_def);
				$n_tables = count($path);
				
				$last_non_unique = false; 
				for($j = 0; $j < $n_tables-1; $j += 2)
				{
					list($left_t,$left_k) = explode('.',$path[$j+1]);
					list($right_t,$right_k) = explode('.',$path[$j]);
					
					$query_join_t = 'LEFT JOIN '.$left_t.' AS t1'.$i.$j.' ON t1'.
					                 $i.$j.'.'.$left_k.'=';

					$query_join_reg = 'LEFT JOIN '.$left_t.' AS t[0-9]{2,} ON t[0-9]{2,}'.
					                 '\.'.$left_k.'=';

					$t = $j-2;
					if ($j == 0)
					{
						$query_join_t .= $path[$j];
						$query_join_reg .= $path[$j];
					}
					else
					{
						$query_join_t .= 't1'.$i.$t.'.'.$right_k;
						$query_join_reg .= 't[0-9]{2,}\.'.$right_k;
					}
					
					$query_restric_joins[] = $query_join_t;
					$tables_as[] = 't1'.$i.$j;
					continue;
					
					/* TODO: The code below detects the double JOIN lines, but
					 * the problem is that it doesn't do that correctly, because
					 * it should consider the hole path, not just one entry.
					 */
					$unique = true;
					if ($n_joins = count($query_restric_joins))
					{
						for($k = 0; $k < $n_joins; ++$k)
						{
							if (preg_match("/$query_join_reg/",$query_restric_joins[$k]))
							{	
								$unique = false;
								$last_non_unique = $k;	
							}
						}
					}

					if ($unique)
					{
						if ($j != 0)
						{
							if ($last_non_unique === false)
							{
								$query_join_t .= 't1'.$i.$t.'.'.$right_k;
							}
							else
							{
								$query_join_t .= $tables_as[$last_non_unique].'.'.$right_k;
							}
						}
						$query_restric_joins[] = $query_join_t;
						$tables_as[] = 't1'.$i.$j;
	
						$last_non_unique = false;
					}
				}
				
				list($table,$field_r) = explode('.',$path[$n_tables-1]);
				
				if ($n_tables > 1)
				{
					$t = $j-2;
					$tables_restric[$restric_fields[$i]] = array(
						'table' => 't1'.$i.$t,
						'field' => $field_r
					);
				}
				else
				{
					$tables_restric[$restric_fields[$i]] = array(
						'table' => $table,
						'field' => $field_r
					);
				}
				
			}

			if ($other)
			{			
				foreach($other as $name => $value)
				{
					switch($name)
					{
						case 'offset':
							if(is_int($value))
							{
								$query_other[2] = 'OFFSET '.$value;
							}
							break;
							 
						case 'limit':
							if(is_int($value))
							{
								$query_other[3] = 'LIMIT '.$value;
							}
							break;
							
						case 'order':
							$order_fs = explode(',',$value);
							foreach($order_fs as $order_f)
							{
								$query_other[0] = 'ORDER BY '.$fields_translate[$order_f];
							}
							break;
							
						case 'sort':
							switch($value)
							{
								case 'ASC':
								case 'DESC':
									$query_other[1] = $value;
							}
							break;
							
						case 'fields_modifier':
							switch($value)
							{
								case 'COUNT':
								case 'MAX':
									$query_fields_mod = $value;
							}
							break;
						case 'customFilter':
						case 'exact':
						case 'CN':
							break;
						
						default:
							exit('Invalid \'other\' field passed to find in file '.__FILE__.' on line '.__LINE__);
					}
				}
			}

			$query_from = array_unique($query_from);
			if(isset($query_fields_mod))
			{
				$query_select .= ' '.$query_fields_mod.'('.implode(',',$fields).') AS mod';
			}
			else
			{
				$query_select .= implode(',',$fields);
			}
			
			@ksort($query_other);
			
			$query_from_f = ' FROM '.implode(',',$query_from);
			
			$query = $query_select . $query_from_f;
			
			if (count($query_fields_joins))
			{
				$query = ' '.implode("\n",$query_fields_joins);
			}

			if (count($query_restric_joins))
			{
				$query .= ' '.implode("\n",$query_restric_joins);
			}

			if ($rules)
			{
				$query .= ' WHERE '.$this->process_restrictions($rules,$tables_restric);
			}

			if(count($query_other))
			{
				if (!((isset($query_other[1]) && $query_other[1]) and !isset($query_other[0])))
				{
					$query .= ' '.@implode(' ',$query_other);
				}
			}

			//echo 'Query in Find: "'.$query.'"<br />';
			if (!$this->db->query($query))
			{
				exit ('Query failed! File: '.__FILE__.' on line'.__LINE__);
			}
			 
			$return = false;
			while($this->db->next_record())
			{
				$return[] = $this->db->row(); 
			}
			 
			return $return;
		}
		
		/*!
			
			@function get_tables_by_field
			@abstract Returns the table wich holds the specified field
			@author Raphael Derosso Pereira
			
			@param string $field  The field to be found
			@param array  $tables The array returned by get_db_tables
		*/
		function get_tables_by_field ($field, &$apptables)
		{
			
			$field_parts = explode('.',$field);
			$n_fields = count($field_parts);
			
			$return = array();
			$previous = false;
			for($i = 0; $i < $n_fields-1; ++$i)
			{
				$actual = $field_parts[$i];
				$next = $field_parts[$i+1];
				
				if (!isset($apptables[$this->tables[$actual]['table']]))
				{
					exit('Some unrecognized parameter in '.__FILE__.' on line '.__LINE__.'<br />'.
					     'Couldn\'t find '.$this->tables[$actual]['table']);
				}
				
				if (array_key_exists($next,$apptables[$this->tables[$actual]['table']]['fd']))
				{
					array_push($return, $this->tables[$actual]['table'].'.'.$next);
					continue;
				}

				if (array_key_exists($next,$this->tables[$actual]))
				{
					if (is_array($this->tables[$actual][$next]))
					{
					    if (isset($this->tables[$actual][$next]['shortcut']))
						{
							$next = $this->tables[$actual][$next]['shortcut'];
							$field_parts[$i+1] = $next;
						}
						else if (isset($this->tables[$actual][$next]['path']))
						{
							$path = $this->tables[$actual][$next]['path'];
							$field_parts[$i+1] = $path;
							$field_parts = array_merge(array_slice($field_parts,0,$i+2),$next,array_slice($field_parts,$i+2,count($field_parts)));
							$next = $path;
							$n_fields = count($field_parts);
						}
					}

					list($key1, $key2) = explode(',',$this->tables[$actual][$next]);
					array_push($return, $this->tables[$actual]['table'].'.'.$key1, $this->tables[$next]['table'].'.'.$key2);
					continue;
				}

				exit('Invalid field in '.__FILE__.' on line '.__LINE__.'<br />Actual: '.$actual.'<br />Next: '.$next);
			}
			
			return $return;
		}

		/*!
		
			@function get_fields_from_restrictions
			@abstract Returns an array containing the fields inside the restrictions
				ignoring the branches
			@author Raphael Derosso Pereira
			
			@param array $restrictions The restrictions
		
		*/
		function get_fields_from_restrictions(&$restrictions)
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
						$fields = array_merge($fields, $this->get_fields_from_restrictions($restrict_data['sub_branch']));
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
					case 'LIKE and ~=':
					case 'NOT NULL':
					case 'NOT IN':
					case 'NOT LIKE':
					case 'NOT iLIKE':
						array_push($fields, $restrict_data['field']);
						break;
						
					default:
						exit('Error in '.__FILE__.' on '.__LINE__.'<br />The restriction type passed was: '.$restrict_data['type']);
				}
			}
			
			return $fields;
		}
		
		/*!
		
			@function process_restrictions
			@abstract Takes the restrictions array and returns an string
				that corresponds to the array
			@author Raphael Derosso Pereira
			
			@param array $restriction The restriction array
			@param array $associative_tree The 
			@param string $logic_type The type of the logic that should be
				used to join the fields
		
		*/
		function process_restrictions(&$restrictions, &$associative_tree, $join_type='AND' )
		{
			foreach($restrictions as $restrict_index => $restrict_data)
			{
				switch($restrict_data['type'])
				{
					case 'branch':
						$return_t[] = ' ('.$this->process_restrictions($restrict_data['sub_branch'],$associative_tree,$restrict_data['value']).') ';
						break;
						
					case 'iLIKE':
						$return_t[] = 'UPPER( translate('.$associative_tree[$restrict_data['field']]['table'].'.'.
						              $associative_tree[$restrict_data['field']]['field'].',\'áàâãäéèêëíìïóòôõöúùûüÁÀÂÃÄÉÈÊËÍÌÏÓÒÔÕÖÚÙÛÜçÇñÑ\',\'aaaaaeeeeiiiooooouuuuAAAAAEEEEIIIOOOOOUUUUcCnN\')) LIKE UPPER(translate(\''.$restrict_data['value'].'\', \'áàâãäéèêëíìïóòôõöúùûüÁÀÂÃÄÉÈÊËÍÌÏÓÒÔÕÖÚÙÛÜçÇñÑ\',\'aaaaaeeeeiiiooooouuuuAAAAAEEEEIIIOOOOOUUUUcCnN\'))';
						break;
					
					case 'LIKE and ~=':
					$return_t[] = 'UPPER(translate('.$associative_tree[$restrict_data['field']]['table'].'.'.
                                      $associative_tree[$restrict_data['field']]['field'].',\'áàâãäéèêëíìïóòôõöúùûüÁÀÂÃÄÉÈÊËÍÌÏÓÒÔÕÖÚÙÛÜçÇñÑ\',\'aaaaaeeeeiiiooooouuuuAAAAAEEEEIIIOOOOOUUUUcCnN\')) LIKE UPPER(translate(\''."%".($restrict_data['value'])."%".'\', \'áàâãäéèêëíìïóòôõöúùûüÁÀÂÃÄÉÈÊËÍÌÏÓÒÔÕÖÚÙÛÜçÇñÑ\',\'aaaaaeeeeiiiooooouuuuAAAAAEEEEIIIOOOOOUUUUcCnN\'))';
						break;
					
					case 'NOT iLIKE':
						$return_t[] = 'UPPER( '.$associative_tree[$restrict_data['field']]['table'].'.'.
						              $associative_tree[$restrict_data['field']]['field'].') NOT LIKE UPPER(\''.$restrict_data['value'].'\')';
						break;
						
					case '=':
					case '!=':	
					case '<=':
					case '<':
					case '>':
					case '>=':
					case 'NULL':
					case 'LIKE':
					case 'NOT NULL':
					case 'NOT LIKE':
						$return_t[] = $associative_tree[$restrict_data['field']]['table'].'.'.
						              $associative_tree[$restrict_data['field']]['field'].' '.$restrict_data['type'].' \''.$restrict_data['value'].'\'';
						break;
					
					case 'IN':
					case 'NOT IN':
						$return_t[] = $associative_tree[$restrict_data['field']]['table'].'.'.
						              $associative_tree[$restrict_data['field']]['field'].' '.$restrict_data['type'].' '.$restrict_data['value'];
						break;
				}
				
			}
			
			if (count($return_t) > 1)
			{
				return(implode(' '.$join_type.' ',$return_t));
			}
			
			return $return_t[0];
		}


		/*********************************************************************\
		 *                     Data Management                               *
		\*********************************************************************/

		/*!
		 @function get_fields
		 @abstract Returns all the fields that a catalog can have
		 	on an array
		 @author Raphael Derosso Pereira
		     
		 @param bool $all Return filled with True or False?
		 
		*/
		function get_fields($all=false)
		{
			if (!is_bool($all)) 
			{
				if (is_object($GLOBALS['phpgw']->log)) 
				{
					$GLOBALS['phpgw']->log->message(array(
						'text' => 'F-BadcontactcenterParam, get_contact_fields parameter must be boolean.',
						'line' => __LINE__,
						'file' => __FILE__));
					
					$GLOBALS['phpgw']->log->commit();
				}
				else {
					exit('Argument Error on: <br />File:'.__FILE__.'<br />Line:'.__LINE__.'<br />');
				}
			}
			
			if ($all)
			{
				return $this->fields;
			} 
			else 
			{
				$fields_temp = $this->fields;
				
				foreach(array_keys($fields_temp) as $field)
				{
					$fields_temp[$field] = false;
				}
				
				return $fields_temp;
			}
		}


        /*********************************************************************\
		 *                Methods to Access Shared Catalog Data              *
		\*********************************************************************/


		/*!

			@function get_all_countries
			@abstract Returns all the countries the API provides
			@author Raphael Derosso Pereira
		
		*/
		function get_all_countries()
		{
			$obj = CreateObject('phpgwapi.country');
			$countries = $obj->country_array;
			unset($countries['  ']);

			foreach($countries as $code => $name)
			{
				$name = lang($name);
				$countries[$code] = $name{0} . strtolower(substr($name, 1));
			}

			return $countries;
		}

		/*!

			@function get_all_states
			@abstract Returns all the states for the given country
			@author Raphael Derosso Pereira

			@param $id_country The ID of the Country that contains the requested States

		*/
		function get_all_states($id_country)
		{
			$id_states = $this->find(array('state.id_state', 'state.state_name'), 
			                         array(
									 	0 => array(
										 	'field' => 'state.id_country',
											'type'  => '=',
											'value' => $id_country
										)
			                         ),
									 array(
									 	'order' => 'state.state_name',
										'sort'  => 'ASC'
									 ));

			if (!is_array($id_states) || count($id_states) == 0)
			{
				return false;
			}

			$result = false;
			foreach($id_states as $id_state)
			{
				$state = CreateObject('contactcenter.so_state', $id_state['id_state']);
				
				$result[$id_state['id_state']]['id_state']   = $state->get_id();
				$result[$id_state['id_state']]['id_country'] = $state->get_id_country();
				$result[$id_state['id_state']]['name']       = $state->get_state_name();
				$result[$id_state['id_state']]['symbol']     = $state->get_state_symbol();
			}

			return $result;
		}

		/*!

			@function get_all_cities
			@abstract Returns all the cities for the given state
			@author Raphael Derosso Pereira

			#param $id_country The ID of the Country that contains the requested Cities
			@param $id_state   The ID of the State that contains the requested Cities

		*/
		function get_all_cities($id_country, $id_state=false)
		{
			if ($id_state)
			{
				$id_cities = $this->find(array('city.id_city', 'city.city_name'), 
										 array(
											0 => array(
												'field' => 'city.id_state',
												'type'  => '=',
												'value' => $id_state
											)
										 ),
										 array(
											'order' => 'city.city_name',
											'sort'  => 'ASC'
										 ));
			}
			else
			{
				$id_cities = $this->find(array('city.id_city', 'city.city_name'), 
										 array(
											0 => array(
												'field' => 'city.id_country',
												'type'  => '=',
												'value' => $id_country
											)
										 ),
										 array(
											'order' => 'city.city_name',
											'sort'  => 'ASC'
										 ));
			}

			if (!is_array($id_cities) || count($id_cities) == 0)
			{
				return false;
			}

			$result = false;
			foreach($id_cities as $id_city)
			{
				$city = CreateObject('contactcenter.so_city', $id_city['id_city']);
				
				$result[$id_city['id_city']]['id_city']      = $city->get_id();
				$result[$id_city['id_city']]['id_country']   = $city->get_id_country();
				$result[$id_city['id_city']]['id_state']     = $city->get_id_state();
				$result[$id_city['id_city']]['name']         = $city->get_city_name();
				$result[$id_city['id_city']]['timezone']     = $city->get_city_timezone();
				$result[$id_city['id_city']]['geo_location'] = $city->get_city_geo_location();
			}

			return $result;
		}


		/*********************************************************************\
		 *           Methods to Insert/Update Shared Catalog Data            *
		\*********************************************************************/

		/*!
			@function add_city
			@abstract Inserts a new City in the DB
			@author Raphael Derosso Pereira

			@params array $city_info The city information:
				$city_info = array(
					'id_state'  => (int),
					'id_country' => (int),     MANDATORY
					'city_name' => (str),      MANDATORY
					'city_time_zone' => (int),
					'city_geo_location' => (str),
				);

			@return int City ID

		*/
		function add_city($city_info)
		{
			if(!is_array($city_info) || !count($city_info) || !$city_info['city_name'] || !$city_info['id_country'])
			{
				exit(print_r(array(
					'file' => __FILE__,
					'line' => __LINE__,
					'msg' => lang('Wrong parameters'),
					'status' => 'fatal',
				)));
			}
			
			$permissions = $this->security->get_permissions();

			if (!is_array($permissions['cities']) || array_search('c', $permissions['cities']) === false)
			{
				exit(print_r(array(
					'file' => __FILE__,
					'line' => __LINE__,
					'msg' => lang('You does not have sufficient privileges. Aborted!'),
					'status' => 'aborted'
				)));
			}

			/* Search for cities with the same data */
			$what = array('city.id_city');
			$rules = array(
				0 => array(
					'field' => 'city.city_name',
					'type'  => 'iLIKE',
					'value' => $city_info['city_name']
				),
				1 => array(
					'field' => 'city.id_country',
					'type'  => 'iLIKE',
					'value' => $city_info['id_country']
				)
			);

			if (isset($city_info['id_state']))
			{
				array_push($rules, array(
					'field' => 'city.id_state',
					'type'  => '=',
					'value' => $city_info['id_state']
				));
			}

			$result = $this->find($what, $rules);

			if (is_array($result) and count($result))
			{
				return $result[0]['id_city'];
			}

			$city = CreateObject('contactcenter.so_city');

			$city->set_id_country($city_info['id_country']);
			$city->set_city_name($city_info['city_name']);
			isset($city_info['id_state']) ? $city->set_id_state($city_info['id_state']) : null;
			isset($city_info['city_timezone']) ? $city->set_city_timezone($city_info['city_timezone']) : null;
			isset($city_info['city_geo_location']) ? $city->set_city_geo_location($city_info['city_geo_location']) : null;

			$city->commit();
			$id = $city->get_id();

			return $id;
		}


		/*!
			@function add_state
			@abstract Inserts a new State in the DB
			@author Raphael Derosso Pereira

			@params array $state_info The state information:
				$state_info = array(
					'id_country' => (int),     MANDATORY
					'state_name' => (str),      MANDATORY
					'state_symbol' => (str),
				);

			@return int State ID

		*/
		function add_state($state_info)
		{
			if(!is_array($state_info) || !count($state_info) || !$state_info['state_name'] || !$state_info['id_country'])
			{
				exit(print_r(array(
					'msg' => lang('Wrong parameters'),
					'status' => 'fatal'
				)));
			}
			
			$permissions = $this->security->get_permissions();

			if (!is_array($permissions['states']) || array_search('c', $permissions['states']) === false)
			{
				exit(print_r(array(
					'msg' => lang('You does not have sufficient privileges. Aborted!'),
					'status' => 'aborted'
				)));
			}

			/* Search for states with the same data */
			$what = array('state.id_state');
			$rules = array(
				0 => array(
					'field' => 'state.state_name',
					'type'  => 'iLIKE',
					'value' => $state_info['state_name']
				),
				1 => array(
					'field' => 'state.id_country',
					'type'  => 'iLIKE',
					'value' => $state_info['id_country']
				)
			);
			
			$result = $this->find($what, $rules);

			if (is_array($result) and count($result))
			{
				return $result[0]['id_state'];
			}

			$state = CreateObject('contactcenter.so_state');

			$state->set_id_country($state_info['id_country']);
			$state->set_state_name($state_info['state_name']);
			isset($state_info['state_symbol']) ? $state->set_state_symbol($state_info['state_symbol']) : null;

			$state->commit();
			$id = $state->get_id();

			return $id;
		}

	}
?>
