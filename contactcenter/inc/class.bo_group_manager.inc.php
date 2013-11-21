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

	include_once('class.abo_catalog.inc.php');
	class bo_group_manager extends abo_catalog
	{
			
		var $fields = array(
			'id_group'    	=> true,
			'title'       			=> true,			
			'short_name'	=> true,
			'owner'	=> true
		);
	
		/*!
		
		 @function bo_group_manager
		 @abstract Constructor
	 	 @author Raphael Derosso Pereira
	 	 
	 	*/
		function bo_group_manager()
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
			
			if (is_array($what) and count($what))
			{
				$found = false;
				
				foreach ($what as $value)
				{
					if (strpos($value, 'group') === 0)
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
						'field' => 'group.owner',
						'type'  => '=',
						'value' => $GLOBALS['phpgw_info']['user']['account_id']
					));
				}
				else
				{
					$rules = array(
						0 => array(
							'field' => 'group.owner',
							'type'  => '=',
							'value' => $GLOBALS['phpgw_info']['user']['account_id']
						)
					);
				}
								
			}
			
			return $this->sql_find($what, $rules, $other);
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
		function get_multiple_entries ( $id_groups, $fields, $other_data = false )
		{
				
			$groups = array();
			
			foreach ($id_groups as $id)
			{
				$group = $this->get_single_entry($id,$fields);				
								
				if ($group)
				{
					$groups[] = $group;
				}
			}			
			
			return $groups;
	
		}

		/*!
		
			@function get_all_entries_ids
			@abstract Returns the IDs of all the entries in this catalog
			@author Raphael Derosso Pereira

		*/
		function get_all_entries_ids ()
		{
			
			$search_fields = array('group.id_group', 'group.title');
			$search_other  = array('order' => 'group.title');
			
			$search_rules = array();
			array_push($search_rules, array(
					'field' => 'group.owner',
					'type'  => '=',
					'value' => $GLOBALS['phpgw_info']['user']['account_id']
				));			

			$result_i = $this->find($search_fields, $search_rules, $search_other);
			

			if (is_array($result_i) and count($result_i))
			{
				$result = array();
				foreach($result_i as $result_part)
				{
					$result[] = $result_part['id_group'];
				}
												
				return $result;
			}
			
			return null;
		}
		
		function get_single_entry ( $id, $fields )
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

			$soGroup = CreateObject('contactcenter.so_group');			
			$group = $soGroup -> select($id);

			 return $group[0];						
		}
		
		function add_group ( $data )
		{
			$permissions = $this->security->get_permissions();
			
			if (!$permissions['create'])
			{
				return false;
			}
			
			$soGroup = CreateObject('contactcenter.so_group');
	
			if(! $data['id_group'])
				$id = $soGroup -> insert($data);
			else				
				$id = $soGroup -> update($data);
				
			return $id;
		}
		
		function get_contacts_by_group ( $id )
		{
			$soGroup = CreateObject('contactcenter.so_group');			
			return $soGroup -> selectContactsByGroup($id);
		}
	
}
?>
