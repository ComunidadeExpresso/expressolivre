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


	/* This is a special class that handles requests to a catalog that is actually
	 * a catalog group. So this class actually performs all the requests in its
	 * catalog childs.
	 */

	include_once('class.abo_catalog.inc.php');
	
	class bo_catalog_group_catalog extends abo_catalog
	{
		var $external;
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
		function bo_catalog_group_catalog(& $bo_contactcenter, & $catalog, $external = 0 )
		{
			$this->bo_contactcenter = & $bo_contactcenter;
			$this->catalog = & $catalog;
			$this->external = $external;
		}
		 	

		/*!

		 @function find
		 @abstract Returns all the IDs of the entries found in all child
		 	catalogues that corresponds to the specified rules.
		 @author Raphael Derosso Pereira

		 @param SEE class bo_contactcenter for usage
		
		*/
		function find($what, $rules, $other, $area=false, $recursive=false)
		{
			if ($ldap_info = $this->catalog['ldap'])
			{
				$meta_catalog = CreateObject('contactcenter.bo_global_ldap_catalog',$ldap_info['id_source'], $ldap_info['context'], $this->external);
				return $meta_catalog->find($what, $rules, $other, $area, $recursive);
			}
			
			$results = array();
			
			reset($this->catalog);
			while(list(, $new_catalog) = each($this->catalog['sub_branch']))
			{
				//print_r($new_catalog);
				//echo 'Setting catalog: <b>'.$new_catalog['name'].'</b><br>';
				$new_catalog = $this->bo_contactcenter->set_catalog($new_catalog);
				
				//if($new_catalog) echo 'Setado corretamente!<br><br>'; else echo 'Erro ao setar<br><br>';
				
				if ($new_catalog['type'] === 'empty')
				{
					continue;
				}
				
				$temp_res = $this->bo_contactcenter->catalog->find($what, $rules, $other, false, $recursive);
				if (is_array($temp_res) and count($temp_res))
				{
					foreach ($temp_res as $id => $value)
					{
						$result[$id] = $value;
					}
				}
			}

			//$this->bo_contactcenter->set_catalog($this->catalog);
			return $result;
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
			
			$contact_array = $this->get_multiple_entries(array($id_contact), $fields);
			
			if (!count($contact_array))
			{
				return false;
			}

			if($contact_array[0])
				$contact_data = $contact_array[0];
			else
				$contact_data = $contact_array[$id_contact];
			
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
	
			/* First check if this is a LDAP Catalog Group. In this case, just leave the
			 * subtree search for the LDAP server
			 */
			if ($ldap_info = $this->catalog['ldap'])
			{
				$meta_catalog = CreateObject('contactcenter.bo_global_ldap_catalog', $ldap_info['id_source'], $ldap_info['context'], $this->external);
				return $meta_catalog->get_multiple_entries($id_contacts, $fields, $other_data);
			}
			
			/* Search for the catalog of the first entry and try to get all ids from that
			 * catalog. Repeat to the ones not found until there's none missing or no more
			 * catalogs.
			 */

			$contacts = array();
			reset($this->catalog);
			while (list($level,$branch) = each($this->catalog['sub_branch']))
			{
				$this->bo_contactcenter->set_catalog($branch);
				$contacts += $this->bo_contactcenter->catalog->get_multiple_entries($id_contacts, $fields, $other_data);
				
				reset($contacts);
				while (list($id) = each($contacts))
				{
					if ($id_contacts[$id])
					{
						unset($id_contacts[$id]);
					}
				}

				if (!count($id_contacts))
				{
					break;
				}
			}

			$this->bo_contactcenter->set_catalog($this->catalog);
			
			return $contacts;
		}
	
		function get_all_entries_ids ()
		{
			// TODO!
			return null;
		}
		
		/*********************************************************************\
		 *                Methods to get general fields                      *
		\*********************************************************************/
		
		/*********************************************************************\
		 *                   Methods to Include Data                         *
		\*********************************************************************/

		/*********************************************************************\
		 *                   Methods to Alter Data                           *
		\*********************************************************************/

		/*********************************************************************\
		 *                   Methods to Remove Data                          *
		\*********************************************************************/

	}
?>
