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
		This class is responsible for the LDAP control/generic functions and for
		configuration gathering
	*/
	include_once('class.Thread.inc.php');

	class bo_ldap_manager 
	{
		
		var $srcs;

		
		function bo_ldap_manager ()
		{
			if (!($this->srcs = $GLOBALS['phpgw']->session->appsession('bo_ldap_manager.srcs','contactcenter')))
			{
				$c = CreateObject('phpgwapi.config','contactcenter');
				$data = $c->read_repository();
				
				if (!$data or $data['cc_global_source0'] !== 'ldap')
				{
					$this->srcs = null;
					return;
				}
				
				$ou = '';
                                $subLevels = 'false';

				if( (!isset($data['cc_ldap_subLevels'])) || ($data['cc_ldap_subLevels'] == "true") )
				{
					$ou = strtolower('ou');
                                        $subLevels = 'true';
				}

				$this->srcs = array(
					1 => array(
						'name'   => $data['cc_catalog_name'],
						'host'   => $data['cc_ldap_host0'],
						'dn'     => $data['cc_ldap_context0'],
						'acc'    => (isset($data['cc_ldap_browse_dn0'])?$data['cc_ldap_browse_dn0']:""),
						'pw'     => (isset($data['cc_ldap_pw0'])?$data['cc_ldap_pw0']:""),
						'obj'    => 'phpgwAccount',
						'branch' => $ou, //strtolower('ou'),
						'montaDN'=> $subLevels, //$data['cc_ldap_subLevels'],
						'visible' => $data['cc_ldap_query_automatic'], 
 	                    'max_results' => $data['cc_ldap_max_results'],
						'recursive' =>	$data['cc_ldap_recursive']
					)
				);
			}
		}

		function new_ldap_source ( $source_name, $charset, $host, $port, $dn_root, $dn_admin, $admin_pass, $contact_objectclass )
		{
		}
	
		/*
		
			@function get_all_ldap_sources
			@abstract Returns an array containing all LDAP sources informations
			@author Raphael Derosso Pereira
		
			@return array All LDAP information
				$return = array(
					<id_source> => array(
						'host' => (string),
						'dn'   => (string),
						'acc'  => (string),
						'pw'   => (string)   
					),
					...
				)
				
			TODO: Return multiple sources...
		*/
		function get_all_ldap_sources (  )
		{
			return $this->srcs;
		}
		
		/*
		* @function get_external_ldap_sources
		* @author Mário César Kolling <mario.kolling@serpro.gov.br>
		* @abstract returns an array with the external sources
		* @return (array) the external sources
		*/

		function get_external_ldap_sources()
		{
			include(PHPGW_INCLUDE_ROOT . '/contactcenter/setup/external_catalogs.inc.php' );
			//include('external_catalogs.inc.php' );
			return $external_srcs;
		}

		/*
		 * @function get_ldap_fields_association
		 * @abstract get the fields associantion for ldap source
		 * @return an array with attribute mappings
		 */
		function get_ldap_fields_association ( $id_source )
		{
			
			$op_iop = array(
				'contact.uidnumber'		   => array('uidNumber'),
				'contact.id_contact'               => array('dn'),
				'contact.photo'                    => array('jpegPhoto'),
				'contact.prefixes.prefix'          => false,
				'contact.alias'                    => array('alias'),
				'contact.given_names'              => array('givenName'),
				'contact.family_names'             => array('sn'),
				'contact.names_ordered'            => array('cn'),//,'displayName'),
				'contact.suffixes.suffix'          => false,
				'contact.birthdate'                => false,
				'contact.sex'                      => false,
				'contact.pgp_key'                  => false,
				'contact.notes'                    => false,
				'contact.mail_forwarding_address' => array('mailForwardingAddress'),
				'contact.account_type' => array('phpgwAccountType'),
                'contact.account_status'           => array('phpgwAccountStatus'),
                'contact.account_visible'          => array('phpgwAccountVisible'),
				'contact.object_class'             => array('objectClass'),
				'contact.business_info.title'      => array('title'),
				'contact.business_info.department' => array('ou'), // Setor do empregado...
				'contact.business_info.empNumber'  => array('employeeNumber'), // Matricula do empregado

				'contact.business_info.celPhone'   => array('mobile'), // Celular empresarial do empregado

                          	'contact.company.company_name'     => array('o'),
				'contact.company.company_notes'    => array('businessCategory'),
				
				'contact.contact_related.names_ordered' => 'contact.contact_related.typeof_relation.contact_relation_name',
				'contact.contact_related.typeof_relation.contact_relation_name' =>  array(
					'manager'   => array('manager'),
					'secretary' => array('secretary')
				),
				
				'contact.address.address1'         => 'contact.address.typeof_address.contact_address_type_name',
				'contact.address.typeof_address.contact_address_type_name' => array(
					'home' => array('street', 'st', 'postalAddress', 'homePostalAddress'),
				),
				
				'contact.address.postal_code'      => 'contact.address.typeof_address.contact_address_type_name',
				'contact.address.typeof_address.contact_address_type_name' => array(
					'home' => array('PostalCode'),
				),
				
				'contact.address.city.city_name'   => 'contact.address.typeof_address.contact_address_type_name',
				'contact.address.typeof_address.contact_address_type_name' => array(
					'home' => array('l'),
				),
				
				'contact.address.city.state.state_name'       => 'contact.address.typeof_address.contact_address_type_name',
				'contact.address.typeof_address.contact_address_type_name' => array(
					'home' => false,
				),
				
				'contact.address.city.country.id_country'     => 'contact.address.typeof_address.contact_address_type_name',
				'contact.address.typeof_address.contact_address_type_name' => array(
					'home' => array('c')
				),
				
				'contact.connection.connection_value'         => 'contact.connection.typeof_connection.contact_connection_type_name',
				'contact.connection.typeof_connection.contact_connection_type_name' => array (
					'email'  => array('mail'),
					'phone'  => array('telephoneNumber'),
				//	'mobile' => array('mobile'),
				//	'pager'  => array('pager'), // idem ao comentario abaixo, do atributo fax;
				//	'fax'    => array('facsimileTelephoneNumber'), //linha comentada para nao trazer
				// o atributo fax do Ldap; correcao temporaria para nao exibir o fax no ContactCenter
				//(estava sobrepondo o telefone do usuario)

					'telex'  => array('telexNumber')
				),
				'contact.connection.mail'				   => array('mail'),
				'contact.connection.phone'				   => array('telephoneNumber')
			);
			
			return $op_iop;
		}

		/*
		 * @function get_external_ldap_fields_association
		 * @author Mário César Kolling <mario.kolling@serpro.gov.br>
		 * @abstract get the fields association for an external ldap_source
		 * @return an array with attribute mappings
		 */
		function get_external_ldap_fields_association ( $id_source )
		{
			include(PHPGW_INCLUDE_ROOT . '/contactcenter/setup/external_catalogs.inc.php' );
			//include('external_catalogs.inc.php' );
			return $external_mappings[$id_source];
		}

		/*
		 * @function test_connection
		 * @author Mário César Kolling <mario.kolling@serpro.gov.br>
		 * @abstract Test if we can bind to a ldap server in a reasonable time
		 * @param (string) $host ldap server's hostname
		 * @param (string) $account ldap bind dn
		 * @param (string) $password a bind dn's password
		 * @return (array) an array with the answer from the subprocess, null otherwise
		 */
		function test_connection($host, $account, $password, $timeout = 5)
		{
			//opens a subprocess for nonblocking bind
			$tsearch = Thread::Create('class.ldap_assync.inc.php', array('host'	=> $host,
																'account'	=> $account,
																'password'	=> $password
																)
			);

			// It's problably more efficient to let method readResponse control the timeout through
			// stream_select native timeout.
			$response = NULL;
			for ($i = 0; $i < $timeout; ++$i)
			{
				if ($tsearch->isActive())
				{
					sleep(1);
					if (($response = $tsearch->readResponse()) !== NULL)
					{
						$tsearch->close();
						return $response;
					}

				}
				else
				{
					$response = $tsearch->readResponse();
					break;
				}
			}

			$tsearch->close();
			return null;
		}

		/*!
		
			@function get_ldap_tree
			@abstract Returns the LDAP tree corresponding to the specified level
			@author Raphael Derosso Pereira
			
			@param (integer) $id_source The ID of the LDAP source
			
			@param (string)  $context The context to be used as root branch
				
			@param (boolean) $recursive Make it a recursive construction.
				CAUTION! This is EXTREMELY SLOW on large LDAP databases,
				specially when they're not indexed
		*/		
		function get_ldap_tree($id_source, $context = false, $recursive = false) 
		{
			if (!$this->srcs[$id_source])
			{
				return null;
			}
			
			$ldap = $GLOBALS['phpgw']->common->ldapConnect($this->srcs[$id_source]['host'], $this->srcs[$id_source]['acc'],$this->srcs[$id_source]['pw'], true);
			if (!$ldap)
			{
				return false;
			}
			
			if ($recursive)
			{
				$tree = $this->get_ldap_tree_recursive($ldap, $context, $this->srcs[$id_source]['obj'],$this->srcs[$id_source]['branch']);
				$tree['recursive'] = true;

				return $tree;
			}
			
			return $this->get_ldap_tree_level($id_source, $ldap, $context, $this->srcs[$id_source]['obj'],$this->srcs[$id_source]['branch']);
		}
		// SERPRO

		/*!

			@function get_external_ldap_tree
			@abstract Returns the LDAP external tree corresponding to the specified level
			@author Mário César Kolling <mario.kolling@serpro.gov.br>
			@param (integer) $id_source The ID of the external LDAP source
			@param (string)  $context The context to be used as root branch
			@param (boolean) $recursive Make it a recursive construction.
				CAUTION! This is EXTREMELY SLOW on large LDAP databases,
				specially when they're not indexed
		*/
		function get_external_ldap_tree($id_source, $context = false, $recursive = false)
		{


			include(PHPGW_INCLUDE_ROOT . '/contactcenter/setup/external_catalogs.inc.php' );
			//include('external_catalogs.inc.php' );

			if (!$external_srcs[$id_source])
			{
				return null;
			}

			// calls test_connection first. If succeeded continue, return error message otherwise.
			if (!($response = $this->test_connection($external_srcs[$id_source]['host'], $external_srcs[$id_source]['acc'], $external_srcs[$id_source]['pw'], 10)))
			{
				return array(
					'msg'		=>	lang("Catalog %1 temporarily unavailable. Please try again later!", $external_srcs[$id_source]['name']),
					'timeout'	=>	'true'
				);
			}

			$ldap = $GLOBALS['phpgw']->common->ldapConnect($external_srcs[$id_source]['host'], $external_srcs[$id_source]['acc'],$external_srcs[$id_source]['pw'], false);
			if (!$ldap)
			{
				return false;
			}

			// Option recursive commented out
			/*
			if ($recursive)
			{
				$tree = $this->get_ldap_tree_recursive($ldap, $context, $this->srcs[$id_source]['obj'],$this->srcs[$id_source]['branch']);
				$tree['recursive'] = true;

				return $tree;
			}
			*/

			return $this->get_ldap_tree_level($id_source, $ldap, $context, $external_srcs[$id_source]['obj'],$external_srcs[$id_source]['branch'], 1);
		}

		/*!

			THIS FUNCTION IS NOT TESTED AND IS PROBABLY BROKEN!
			I WILL CORRECT IT IN THE NEAR FUTURE

		*/
		function get_ldap_tree_recursive($resource, $context, $objectClass)
		{
			$filter = '(!(objectClass='.$objectClass.'))';
			$result_res = ldap_list($resource, $context, $filter);

			if ($result_res === false)
			{
				return null;
			}
			
			$count = ldap_count_entries($resource,$result_res);
			if ( $count == 0 )
			{
				$filter = 'objectClass='.$objectClass;
				$result_res2 = ldap_list($resource, $context, $filter);
				$entries_count = ldap_count_entries($resource, $result_res2);

				if ($result_res2 !== false && $entries_count > 0)
				{
					return $entries_count;
				}
				else
				{
					return null;
				}
			}
			
			$entries = ldap_get_entries($resource, $result_res);
			
			for ($i = 0; $i < $entries['count']; ++$i)
			{
				$subtree = $this->get_ldap_tree_recursive($resource, $entries[$i]['dn'], $objectClass);
				
				$dn_parts=ldap_explode_dn($entries[$i]['dn'],1);
				
				if ($subtree !== null and is_array($subtree)) 
				{
					$tree[$i]['name'] = $dn_parts[0];
					$tree[$i]['type'] = 'catalog_group';
					$tree[$i]['recursive'] = true;
					$tree[$i]['sub_branch'] = $subtree;
				}
				else if (is_int($subtree) and $subtree !== null)
				{
					$tree[$i] = array(
						'name'       => $dn_parts[0],
						'type'       => 'catalog',
						'class'      => 'global_contact_manager',
						'icon'       => 'share-mini.png',
						'value'      => $entries[$i]['dn'],
						'sub_branch' => false
					);
				} 
			}

			if (is_array($tree))
			{
				return $tree;
			}
			else
			{
				return null;
			}
		}
		
		function get_ldap_referrals($ds, $dn, $filter) {
			
			ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);
			ldap_set_option($ds,LDAP_OPT_PROTOCOL_VERSION,3);
			
			if ($ds) {
			    ldap_bind($ds);
			   	$sr=ldap_list($ds,$dn, $filter);		    
			   	$ref = ldap_first_reference($ds, $sr);
			   	$array_referral = array();
			   	$idx = 0;
			    	
				 while ($ref) {
					$array_referral[$idx++] = ldap_get_dn($ds, $ref);
					$ref = ldap_next_reference($ds, $ref);
				}
				return $array_referral;
			}
			else 
				return false;
		}

		function get_ldap_sub_branches_referrals($ds, $dn, $filter) {
			
			$referral = $this -> get_ldap_referrals($ds, $dn, $filter);
			$sub_branches = array();

            $referral_count = count($referral);
			for($i = 0; $i <$referral_count; ++$i) {
				$dn = str_replace("??base","",preg_replace('!^(ldap://[^/]+)/(.*$)!', '\\2', $referral[$i]));
				$dn = explode(",",$dn);				
				$dn = strtoupper(str_replace("ou=", "",$dn[0]));
				$dn = str_replace("DC=", "",$dn);
																						
				$sub_branch = array(
											'name' => $dn,
					                    	'type' => 'unknown',
                    						'value' => $referral[$i],
                    						'sub_branch' => false		
										);															
				$sub_branches[$i] = $sub_branch;			
			}
			return $sub_branches;
		}
		

		function translate_accentuation($text)
		{
			/*
			 * Esta operação resolve o problema causado pela conversão de caracteres acentuados realizada
			 * pela função ldap_explode_dn().
			 */

			return utf8_decode(preg_replace("/\\\([0-9A-Fa-f]{2})/e", "''.chr(hexdec('\\1')).''", $text));
		}

		function get_ldap_tree_level($id_source, $resource, $context, $objectClass, $branch_dn, $external = 0)
		{			

			/*
			 * TODO: Search timeouts
			 */

			$dn_parts = ldap_explode_dn(($refer_context ? $refer_context : $context),1);
			//$filter = '(!(objectClass='.$objectClass.'))';
			// Don't show OU's whith phpgwAccountVisible equal to '-1'
			if ($external)
			{
				// external source: get all organizationalUnits
				$filter = '(objectClass=organizationalUnit)';
			}
			else
			{
				// get any objectClass except the objectClass used for the source
				// and whose attribute phpgwAccountVisible value is different from -1
				$filter = '(&(!(objectClass='.$objectClass.')) (!(phpgwAccountVisible=-1)))';
			}
			$result_res = @ldap_list($resource,  $context, $filter, array(), 0, 0);
			@ldap_sort($resource, $result_res, 'ou');

			// Timeouts commented out
			/*
			if ($result_res === false)
			{
				return null;
			}
			*/

			$count = ldap_count_entries($resource,$result_res);

			if ( $count == 0 )
			{
				$filter = '(objectClass='.$objectClass.')';
				// Get only one attribute of the source's objectClass
				$result_res2 = @ldap_list($resource, $context, $filter, Array('cn'), 0, 1);
				$entries_count = ldap_count_entries($resource, $result_res2);

				if ($result_res2 !== false && $entries_count > 0)
				{
					return array(
						'name'       => $this->translate_accentuation($dn_parts[0]),
						'type'       => 'catalog',
						'class'      => 'bo_global_ldap_catalog',
						// Pass the variable $external as a parameter to the constructor
						'class_args' => array($id_source, $context, $external),
						'icon'       => 'globalcatalog-mini.png',
						'value'      => $context,
						'sub_branch' => false
					);
				}
				else
				{
					return array(
						'name' => $this->translate_accentuation($dn_parts[0]),
						'type' => 'empty'
					);
				}
			}

			$sub_branch_found = false;
			$i = 0;
			for ($entry = ldap_first_entry($resource, $result_res);
			     $entry != false;
			     $entry = ldap_next_entry($resource, $entry))
			{
				$dn = ldap_get_dn($resource, $entry);
				$dn_parts_1 = ldap_explode_dn($dn,1);
				$dn_parts_full = ldap_explode_dn($dn,0);
				list($group) = explode('=',$dn_parts_full[0]);

				//Faz a comparação do branch como case insensitive
				if (strtolower($group) == strtolower($branch_dn) or $branch_dn === 'all')
				{
					$tree['sub_branch'][$i] = array(
						'name'  => $this->translate_accentuation($dn_parts_1[0]),
						'type'  => 'unknown',
						'value' =>  $dn,
						'sub_branch' => false
					);
					$sub_branch_found = true;
				}
				$i++;
			}
			

			$filter = 'objectClass='.$objectClass;
			$result_res2 = @ldap_list($resource, $context, $filter, Array('cn'), 0, 1);
			$entries_count = ldap_count_entries($resource, $result_res2);

			if ($result_res2 !== false && $entries_count > 0 && $sub_branch_found)
			{
				$tree['name']       = $this->translate_accentuation($dn_parts[0]);
				$tree['type']       = 'mixed_catalog_group';
				$tree['class']      = 'bo_global_ldap_catalog';
				// Pass the variable $external as a parameter to the constructor
				$tree['class_args'] = array($id_source,$context,$external);
				$tree['icon']       = 'globalcatalog-mini.png';
				$tree['value']      = $context;
			}
			elseif ($result_res2 !== false && $entries_count > 0 && !$sub_branch_found)
			{
				return array(
					'name'       => $this->translate_accentuation($dn_parts[0]),
					'type'       => 'catalog',
					'class'      => 'bo_global_ldap_catalog',
					// Pass the variable $external as a parameter to the constructor
					'class_args' => array($id_source, $context,$external),
					'icon'       => 'globalcatalog-mini.png',
					'value'      => $context,
					'sub_branch' => false
				);
			}
			else
			{
				$tree['name']       = $this->translate_accentuation($dn_parts[0]);
				$tree['type']       = 'catalog_group';
				$tree['class']      = 'bo_catalog_group_catalog';
				// Pass the variable $external as a parameter to the constructor
				$tree['class_args'] = array('$this', '$this->get_branch_by_level($this->catalog_level[0])', $external);
				$tree['value']      = $context;
				$tree['ldap']       = array('id_source' => $id_source, 'context' => $context);
			}
			
			usort($tree['sub_branch'], array($this, "compareTreeNodes"));
			return $tree;
		}

		function compareTreeNodes($a, $b)	{
						
			return strnatcasecmp($a['name'], $b['name']);
		}	

	}
?>
