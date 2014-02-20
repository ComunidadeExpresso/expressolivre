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

	/*!
		This is the main SO class. Every other SO class is derived from this. 
		The purpose of this class is to centralize the search, checkout and 
		commit methods, that are designed to be common to all derived classes.
	*/

	class so_main 
	{

		// The ID of the class, usually its primary key on the DB.
		var $id;
		
		// The state of this object
		var $state;
		
		// The DB object
		var $db;
		
		/**
		 * The DB tables' used by the class
		 *
		 * This attribute is VERY important. It is responsible
		 * to maintain all the DB Tables' Information.
		 *
		 * It's format is:
		 *
		 * $db_tables = array(
		 *		'<table1_name>'	=> array(
		 *			'type'      => '<table_type>',
		 *			'keys'      => array(
		 *				'primary'	=> array(
		 *					<pkey1>	=> array(
		 *						'name'	=> '<key_field_name>',
		 *						'value'	=>	<key_value>),
		 *					<pkey2> => array(...)),
		 *				'foreign'	=> array(
		 *					<fkey1> => array(
		 *						'name'			=> '<key_field_name>',
		 *						'value'			=> <key_value>,
		 *						'association'	=> array(
		 *							'table'		=> '<original_table>',
		 *							'field'		=> '<original_field>')),
		 *					<fkey2>	=> array(...)),
		 *			'fields'	=> array(
		 *				<field1>	=> array(
		 *					'name'	=> '<field_name>',
		 *					'type'	=> '<field_type>',
		 *					'state'	=> '<field_state>',
		 *					'value'	=> <field_value>),
		 *				<field2>	=> array(...)))),
		 *		'<table2_name>'	=> array(...));
		 *
		 * The variables:
		 *	<table_type> can be: (if you find any other type, please, report it).
		 *		- 'main'	=> The main table used by the class. In essence, 
		 *			this table 
		 *		- 'single'	=> The single tables are the ones that each field 
		 *			keeps just one value;
		 *		- 'multi'	=> The multi tables are those in which fields keeps 
		 *			multiple values. So, for this type of tables, the 'state' 
		 *			and 'value' properties becomes arrays and their names are 
		 *			'states' and 'values';
		 *
		 *	<field_type> can be: (report if find any other)
		 *		- false		=> The field is an ordinary field;
		 *		- 'primary'	=> The field is a primary key;
		 *		- 'foreign' => The field is a foreign key;
		 *		- array()	=> The field is a multi-type field. The elements of
		 *			the array specifies the types. 
		 */
		 	
		var $db_tables = array();

		var $remove_all = false;
	
		function init()
		{
			$this->db = $GLOBALS['phpgw']->db;
		}

		/*!
		
			@function get_field
			@abstract Returns the value of the Field specified
			@author Raphael Derosso Pereira
			
			@param string $field The name of the Field
		
		*/
		function get_field($field)
		{
			if(array_key_exists($field, $this->main_fields))
			{
				return $this->main_fields[$field]['value'];
			}
			
			// TODO: Log?
		}
	
		function get_state()
		{
			return $this->state;
		}	

		/*!
		
			@function set_field
			@abstract Sets the value of the Field specified
			@author Raphael Derosso Pereira
			
			@param string $field The name of the Field
			@param mixed $value The value to be setup
		
		*/
		function set_field($field, $value)
		{
			if(array_key_exists($field, $this->main_fields))
			{
				$this->main_fields[$field]['value'] = $value;
				$this->manage_fields($this->main_fields[$field], 'changed');
			}
			
			// TODO: Log?
			return;
		}

		/*!
		
			@function checkout
			@abstract Load object from DB
			@author Raphael Derosso Pereira
			
			@param integer $id The object ID (primary_key)
		
			@notes IMPORTANT!!! This version just loads from DB in the case
				where there is just ONE primary key in the main table. If you
				want to extend it, go ahead and share the code!!! :)
		*/
		
		function checkout ( $id )
		{
			$query_main_select = 'SELECT ';
			$query_main_from = ' FROM ';
			$query_main_where = ' WHERE ';
			
			$query_multi_select = array();
			$query_multi_from = array();
			$query_multi_where = array();
			
			$table_main = '';
			reset($this->db_tables);
			while(list($table, $table_info) = each($this->db_tables))
			{
				if ($table_info['type'] === 'main')
				{
					$table_main = $table;
					$main_pkey = $table_info['keys']['primary'][0]['name'];
					
					foreach($table_info['fields'] as $field_info)
					{
						$query_main_select .= $table.'.'.$field_info['name'].',';
					}

					$query_main_select{strlen($query_main_select)-1} = ' ';		
					$query_main_from .= $table;
					$query_main_where .= $table_info['keys']['primary'][0]['name'].'=\''.$id.'\'';
					
					break;
				}
			}
			
			reset($this->db_tables);
			while(list($table, $table_info) = each($this->db_tables))
			{
				if ($table_info['type'] === 'multi')
				{
					$query_multi_select[$table] = 'SELECT ';
					$query_multi_from[$table] = ' FROM ';
					$query_multi_where[$table] = ' WHERE ';
				
					foreach($table_info['fields'] as $field_info)
					{
						$query_multi_select[$table] .= $table.'.'.$field_info['name'].',';
					}

					$query_multi_select[$table]{strlen($query_multi_select[$table])-1} = ' ';		
					$query_multi_from[$table] .= $table;
					$query_multi_where[$table] .= $table_info['keys']['primary'][0]['name'].'=\''.$id.'\'';
				}
			}
			
			if($table_main!='') { //Quando faço checkout e não incluo a tabela principal, a função precisa funcionar...
				$query = $query_main_select . $query_main_from . $query_main_where;
	
//			echo 'Query in SO_Main CheckOut: "'.$query.'"<br />';
				
				if ($this->db->query($query,__LINE__, __FILE__)) {
					if ($this->db->next_record()) {
						reset($this->db_tables[$table_main]['fields']);
			
						while (list(,$field_info) = each($this->db_tables[$table_main]['fields']))
						{
							$this->db_tables[$table_main]['fields'][$field_info['name']]['value'] = $this->db->f($field_info['name']);
							$this->manage_fields($this->db_tables[$table_main]['fields'][$field_info['name']], 'sync');
						}
					}
				}
			}
			//echo "\n\n\n".$query_multi_select;
			foreach($query_multi_select as $table => $query)
			{
				
				$query = $query_multi_select[$table] . $query_multi_from[$table] . $query_multi_where[$table];

				//echo 'Query in SO_Main CheckOut: "'.$query.'"<br />';
				if ($this->db->query($query, __LINE__, __FILE__)) {
					$pos = 0;
					while($this->db->next_record())
					{
						reset($this->db_tables[$table]['fields']);					
						while (list(,$field_info) = each($this->db_tables[$table]['fields']))
						{
							$this->db_tables[$table]['fields'][$field_info['name']]['values'][$pos] = $this->db->f($field_info['name']);
							$this->manage_fields($this->db_tables[$table]['fields'][$field_info['name']], 'sync', 'new');
						}
						++$pos;
					}
					++$pos;
				}
			}
			
			return true;
		}
	
		/*!
		
			@function commit
			@abstract Saves object to DB
			@author Raphael Derosso Pereira
			
			@return integer The object ID (primary_key)
		
		*/
		function commit ( )
		{
			if ($this->state === 'new')
			{
				return $this->commit_new();
			}
			else if ($this->state === 'delete')
			{
				return $this->delete_record();
			}
			
			$query_main_head = 'UPDATE ';
			$query_main_tail = ' WHERE ';
			$query_main_fields = false;
			
			$query_multi_head = array();
			$query_multi_fields = array();

			$table_main = '';
			$main_table_changed = false;
			reset($this->db_tables);
			while(list($table, $table_info) = each($this->db_tables))
			{
				if ($table_info['type'] === 'main')
				{
					$table_main = $table;
					$main_pkey = $table_info['keys']['primary'][0]['name'];
					
					$query_main_head .= $table . ' SET ';
					
					foreach($table_info['fields'] as $field_info)
					{
						if ($field_info['state'] === 'changed' or $field_info['state'] === 'new')
						{
							$main_table_changed = true;

							if ($field_info['value'] == null)
							{
								$sep = '';
							}
							else
							{
								$sep = "'";
							}

							if ($field_info['name'] === 'photo')
							{
								$query_main_fields .= $field_info['name'].'='.$this->db->quote($field_info['value'], 'blob').',';
							}
							else
							{
								$f_value = $field_info['value'] === null ? 'NULL' : "'".$field_info['value']."'";
								$query_main_fields .= $field_info['name'].'='.$f_value.',';
							}
							$this->manage_fields($this->main_fields[$field_info['name']], 'sync');
						}
						else if ($field_info['state'] === 'deleted')
						{
							$main_table_changed = true;
							$query_main_fields .= $field_info['name'].'=NULL,';
							$this->manage_fields($this->main_fields[$field_info['name']], 'sync');
						}
					}

					if (!$main_table_changed)
					{
						continue;
					}
					
					$query_main_fields{strlen($query_main_fields)-1} = ' ';
					
					$query_main_tail .= $main_pkey.'=\''.$this->id.'\'';

					$query = $query_main_head . $query_main_fields . $query_main_tail;
					
					//echo '<p>Main Update Query: "'.$query.'"</p>';
		
					if (!$this->db->query($query, __LINE__, __FILE__))
					{
						return false;
					}
				}
				else if ($table_info['type'] === 'multi')
				{
					if (!$this->commit_multi($table, $table_info))
					{
						exit('Couldn\'t commit on table "'.$table.'"');
					}
				}
			}
			
			$this->state = 'sync';

			return $this->id;
		}
		
		/*!
		
			@function delete_record
			@abstract Removes the current entry from the DB
			@author Raphael Derosso Pereira 
		
		*/
		function delete_record ()
		{
			reset($this->db_tables);
			while(list($table, $table_info) = each($this->db_tables))
			{
				if ($table_info['type'] === 'main')
				{
					$where = false;
					$where[$table_info['keys']['primary'][0]['name']] = $table_info['keys']['primary'][0]['value'];
					
					$sql = "DELETE FROM $table WHERE ".$table_info['keys']['primary'][0]['name']."='".$table_info['keys']['primary'][0]['value']."'";
					
					if (!$this->db->query($sql, __LINE__, __FILE__))
					{
						return false;
					}

					//if (!$this->db->delete($table,$where,__LINE__,__FILE__))
					//{
					//	return false;
					//}
					
					continue;
				}
				else if ($table_info['type'] === 'multi')
				{
					$where = false;
					$n_values = count($table_info['keys']['primary'][0]['values']);
					for ($i = 0; $i < $n_values; ++$i)
					{
						unset($where);
						foreach ($table_info['keys']['primary'] as $key)
						{
							//$where[$key['name']] = $key['values'][$i];
							$where[] = $key['name']."='".$key['values'][$i]."'";
						}
						
						//print_r($where);
						$sql = "DELETE FROM $table WHERE ".implode(' AND ', $where);
						
						//echo $sql;
						
						if (!$this->db->query($sql,__LINE__,__FILE__))
						{
							return false;
						}
						
					}
					continue;
				}
			}
			
			return true;
		}
		
		/*!
		
			@function commit_new
			@abstract Sets the new ID and commits the data to the DB
			@author Raphael Derosso Pereira
			
			@return integer The new ID
		
		*/
		function commit_new()
		{
			$query_main_head = 'INSERT INTO ';
			$query_main_fields = '( ';
			$query_main_values = ' VALUES ( ';
			
			$query_multi_head = array();
			$query_multi_fields = array();
			$query_multi_values = array();

			reset($this->db_tables);
			while(list($table, $table_info) = each($this->db_tables))
			{
				if ($table_info['type'] === 'main')
				{
					$main_table_changed = false;

					$table_main = $table;
					$main_pkey = $table_info['keys']['primary'][0]['name'];
					
					$query_main_head .= $table . ' ';
					
					$query_id = 'SELECT MAX('.$main_pkey.') AS new_id FROM '.$table_main;
					
					//echo 'Query MAX: '.$query_id.'<br />';
					if (!$this->db->query($query_id, __LINE__, __FILE__) or !$this->db->next_record())
					{
						exit('Error in '.__FILE__.' line: '.__LINE__);
					}
					
					$this->id = $this->db->f('new_id')+1;
					$this->manage_fields($this->main_fields[$main_pkey], 'sync');
					//echo 'ID: '.$this->id.' Type: '.gettype($this->id);
				
					foreach($table_info['fields'] as $field_info)
					{
						if ($field_info['state'] === 'changed')
						{
							$main_table_changed = true;
							$query_main_fields .= $field_info['name'].',';
							
							if ($field_info['name'] === 'photo')
							{
								$query_main_values .= $this->db->quote($field_info['value'], 'blob').',';
							}
							else
							{
								$query_main_values .= ($field_info['value'] === null || $field_info['value'] == '') ? 'NULL,' : "'".$field_info['value']."',";
							}
							$this->manage_fields($this->main_fields[$field_info['name']], 'sync');
						}
					}
					
					if (!$main_table_changed)
					{
						continue;
					}
					
					$query_main_fields .= $main_pkey.')';
					$query_main_values .= '\''.$this->id.'\')';

					$query = $query_main_head . $query_main_fields . $query_main_values;
					
					//echo '<p>Main Insert Query: "'.$query.'"</p>';

					if (!$this->db->query($query, __LINE__, __FILE__))
					{
						return false;
					}
				}
				else if ($table_info['type'] === 'multi')
				{
					$this->commit_multi($table, $table_info);
				}
			}
			
			$this->state = 'sync';
			return $this->id;
		}

		/*!
		
			@function commit_multi
			@abstract Handles the commit of Multi Values tables
			@author Raphael Derosso Pereira
			
			@param string $table The table name
			@param array $table_info The table infrastructure
		
		*/
		function commit_multi($table, $table_info)
		{
			reset($table_info['fields']);
			list(,$t_field) = each($table_info['fields']);
			$n_values = count($t_field['values']);
			
			//echo 'Table: '.$table.'<br />n_values => '.$n_values.'<br />';
			
			reset($table_info['fields']);
			for ($i = 0; $i < $n_values; ++$i)
			{
				$multi_table_changed = false;
				$query_multi_update_head = 'UPDATE '.$table.' SET ';
				$query_multi_insert_head = 'INSERT INTO '.$table;
				$query_multi_update_fields = false;
				$query_multi_insert_fields = '(';
				$query_multi_insert_values = ' VALUES (';
				$query_multi_update_tail = ' WHERE ';
				$main_pkeys = $table_info['keys']['primary'];
				
				foreach($table_info['fields'] as $field_info)
				{
					$f_value = $field_info['values'][$i] == null ? 'NULL' : $field_info['values'][$i];
					
					switch ($field_info['states'][$i])
					{
						case 'changed':
							$multi_table_changed = 'changed';
							$query_multi_update_fields .= $field_info['name'].'='.$f_value.',';
							$this->manage_fields($this->db_tables[$table]['fields'][$field_info['name']], 'sync', $i);
							break;
					
						case 'new':
							$multi_table_changed = 'new';
							$query_multi_insert_fields .= $field_info['name'].',';
							$query_multi_insert_values .= $f_value.',';
							$this->manage_fields($this->db_tables[$table]['fields'][$field_info['name']], 'sync', $i);
							break;

						case 'deleted':
							$multi_table_changed = 'deleted';
							//unset($this->db_tables[$table]['fields'][$field_info['name']]['values'][$i]);
							//unset($this->db_tables[$table]['fields'][$field_info['name']]['states'][$i]);
							break;
					}
				}

				if (!$multi_table_changed)
				{
					continue;
				}
				
				$query_multi_update_fields{strlen($query_multi_update_fields)-1} = ' ';

				foreach($main_pkeys as $pkey)
				{
					$query_multi_update_tail_t[] = $pkey['name'].'=\''.$pkey['values'][$i].'\'';
				}
				$query_multi_update_tail .= @implode(' AND ', $query_multi_update_tail_t);
				unset($query_multi_update_tail_t);
				
				$query_multi_insert_fields{strlen($query_multi_insert_fields)-1} = ')';
				$query_multi_insert_values{strlen($query_multi_insert_values)-1} = ')';

				switch ($multi_table_changed)
				{
					case 'changed':
						$query = $query_multi_update_head . $query_multi_update_fields . $query_multi_update_tail;
						break;
						
					case 'new':
						$query = $query_multi_insert_head . $query_multi_insert_fields . $query_multi_insert_values;
						break;

					case 'deleted':
						$query = 'DELETE FROM ' . $table . $query_multi_update_tail;
				}

//				echo '<p>Multi Query, type '.$multi_table_changed.': "'.$query.'"</p>';

				if (!$this->db->query($query, __LINE__, __FILE__))
				{
					return false;
				}
			}
/*
			foreach($this->db_tables[$table]['fields'] as $field_info)
			{
				$this->db_tables[$table]['fields'][$field_info['name']]['values'] = array_values($field_info['values']);
				$this->db_tables[$table]['fields'][$field_info['name']]['states'] = array_values($field_info['states']);
			}
*/
			return true;
		}
	
		/*!
		
			@function get_id
			@abstract Return the object ID
			@author Raphael Derosso Pereira
			
		*/
		function get_id (  )
		{
			return $this->id;
		}
	
		/*!
		
			@function get_db_tables
			@abstract Return the object DB Tables
			@author Raphael Derosso Pereira
			
		*/
		function get_db_tables (  )
		{
			return $this->db_tables;
		}

		/**
		 * Misc Methods
		 */
		 
		/*!
		
			@function get_class_by_field
			@abstract Returns the name of the class that has the specified
				field
			@author Raphael Derosso Pereira
			@param string $field The field name
					
		*/
		function get_class_by_field ($field)
		{
			$class_name = '_UNDEF_';
			
			switch ($field)
			{
				case 'id_status':
				case 'status_name':
					$class_name = 'so_status';
					break;						

				case 'id_prefix':
				case 'prefix':
					$class_name = 'so_prefix';
					break;						

				case 'id_suffix':
				case 'suffix':
					$class_name = 'so_suffix';
					break;						

				case 'id_contact':
				case 'id_owner':
				case 'photo':
				case 'alias':
				case 'given_names':
				case 'family_names':
				case 'names_ordered':
				case 'birthdate':
				case 'sex':
				case 'pgp_key':
				case 'notes':
				case 'id_related':
				case 'is_global':
				case 'title':
				case 'department':
					$class_name = 'so_contact';
					break;						
				
				case 'id_company':
				case 'id_company_owner':
				case 'company_name':
				case 'company_notes':
					$class_name = 'so_company';
					break;				
					
				case 'legal_info_name':
				case 'legal_info_value':
					$class_name = 'so_company_legal';
					break;						

				case 'id_typeof_company_legal':
				case 'company_legal_type_name':
					$class_name = 'so_company_legal_type';
					break;						
					
				case 'id_typeof_contact_relation':
				case 'contact_relation_name':
				case 'contact_relation_is_subordinated':
					$class_name = 'so_contact_relation_type';
					break;						
					
				case 'id_typeof_company_relation':
				case 'company_relation_name':
				case 'company_relation_is_subordinated':
					$class_name = 'so_company_relation_type';
					break;						
					
				case 'id_address':
				case 'address1':
				case 'address2':
				case 'complement':
				case 'address_other':
				case 'postal_code':
				case 'po_box':
				case 'address_is_default':
					$class_name = 'so_address';
					break;

				case 'id_city':
				case 'city_name':
				case 'city_timezone':
				case 'city_geographic_location':
					$class_name = 'so_city';
					break;

				case 'id_state':
				case 'state_name':
				case 'state_symbol':
					$class_name = 'so_state';
					break;
				
				case 'country_name':
					$class_name = 'so_country';
					break;
					
				case 'id_typeof_contact_address':
				case 'contact_address_type_name':
					$class_name = 'so_contact_address_type';
					break;
					
				case 'id_typeof_company_address':
				case 'company_address_type_name':
					$class_name = 'so_company_address_type';						
					
				case 'id_connection':
				case 'connection_name':
				case 'connection_value':
				case 'connection_is_default':
					$class_name = 'so_connection';
					break;						
					
				case 'id_typeof_contact_connection':
				case 'contact_connection_type_name':
					$class_name = 'so_contact_connection_type';
					break;
					
				case 'id_typeof_company_connection':
				case 'company_connection_type_name':
					$class_name = 'so_company_connection_type';
					break;

				default:
					break;						

			}
			
			if ($class_name == '_UNDEF_')
			{
				return false;
			}
			
			return 'contactcenter.'.$class_name;
		}
		
		/*!
			
			@function get_table_by_field
			@abstract Returns the table wich holds the specified field
			@author Raphael Derosso Pereira
			
			@param string $field The field to be found
			@param array $tables The array returned by get_db_tables
		*/
		function get_table_by_field ($field, & $tables)
		{
			$return = false;
			
			reset($tables);
			while(list($table, $properties) = each($tables))
			{
				if(array_key_exists($properties['fields']))
				{
					if (strtolower($properties['type']) === 'main')
					{
						return $table;
					}
					array_push($return,$table);
				}
			}
			
			return $return;
		}

		/*!
		
			@function manage_fields
			@abstract Change the state of the fields
			@author Raphael Derosso Pereira
			
			@param mixed $field The reference to the field
			@param string $new_state The new field state
			@param mixed $position [optional] The position where the field 
				is state is to be stored. This is used when the table type
				is 'multi'.
		
		*/
		function manage_fields(& $field, $new_state, $position = false)
		{
			if ($this->state !== 'delete')
			{
				if ($this->state != 'new')
				{
					$this->state = 'changed';
				}
				
				if ($position === false)
				{
					$field['state'] = $new_state;
					
					return;
				}
				
				if ($position !== 'new')
				{
					$field['states'][$position] = $new_state;
				}
				else
				{
					array_push($field['states'], $new_state);
				}
			}
		}
		
		/*!
			
			@function reset_values
			@abstract Reset all the values to false and all
				states to empty
			@author Raphael Derosso Pereira
		*/
		function reset_values()
		{
			foreach($this->db_tables as $table_name => $table_info)
			{
				foreach($table_info['fields'] as $field => $field_info)
				{
					if ($field_info['value'])
					{
						$this->db_tables[$table_name]['fields'][$field]['value'] = false;
						$this->manage_fields($this->db_tables[$table_name]['fields'][$field], 'empty');
					}
					else if ($field_info['values'])
					{
						unset($this->db_tables[$table_name]['fields'][$field]['states']);
						unset($this->db_tables[$table_name]['fields'][$field]['values']);
					}
				}
			}
			$this->state = 'new';
		}


		/*!
		
			@function remove
			@abstract Removes the entry from the DB
			@author Raphael Derosso Pereira
		*/
		function remove()
		{
			$this->state = 'delete';
			
			$result = $this->commit();
			$this->reset_values();
			return $result;
		}
	
	}
?>
