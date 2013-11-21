<?php
  /***************************************************************************\
  * eGroupWare - Contacts Center                                              *
  * http://www.egroupware.org                                                 *
  * Storage Object Classes                                                    *
  * Written by:                                                               *
  *  - Nilton Emilio Buhrer Neto <niltonneto@celepar.pr.gov.br>			      *
  *  sponsored by Celepar - http://celepar.pr.gov.br                          *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/
	class so_group {
		
		var $db;
		var $owner;

		function so_group ()
		{
			$this->db    = $GLOBALS['phpgw']->db;
			$this->owner = $GLOBALS['phpgw_info']['user']['account_id']; 
		}
		
		function select($id = '')
		{
			$query = 'SELECT id_group,title,short_name FROM phpgw_cc_groups ';
			if($id != '')
				$query .= 'WHERE id_group ='.$id;
			
			$query .= ' ORDER BY title';
			
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

		function remove_accents($string) {
 			return strtr($string, 
 			"?Ó??ó?Ý?úÁÀÃÂÄÇÉÈÊËÍÌ?ÎÏÑÕÔÓÒÖÚÙ?ÛÜ?áàãâäçéèêëíì?îïñóòõôöúù?ûüýÿ", 
 			"SOZsozYYuAAAAACEEEEIIIIINOOOOOUUUUUsaaaaaceeeeiiiiinooooouuuuuyy");
		}
		
		function insert($data)
		{
			$this->db->query("select * from phpgw_cc_groups where 
					upper(title) like '".strtoupper($data['title'])."' and owner=".$this->owner);
			if($this->db->next_record()) 
				return false;//Não posso criar grupos com nomes iguais
			$shortName = $this -> remove_accents(strtolower(str_replace(" ","", $data['title'])));			
			
			$query = "INSERT INTO phpgw_cc_groups(title, owner,short_name) ".
			 		"VALUES('".$data['title']."',".$this->owner.",'".$shortName."')";			
										
			if (!$this->db->query($query))
				exit ('Query failed! File: '.__FILE__.' on line'.__LINE__);				
						
			$objSequence = $this->db->query("SELECT last_value FROM seq_phpgw_cc_groups");			
			$id = $objSequence -> fields['last_value'];
			$this -> updateContactsByGroup($id, $data['contact_in_list']);

			return $id;		
		}
		
		function update($data)
		{
			$this->db->query("select * from phpgw_cc_groups where 
					id_group!='".$data['id_group']."' and
					upper(title) like '".strtoupper($data['title'])."' and owner=".$this->owner);
			if($this->db->next_record()) 
				return false;//Não posso criar grupos com nomes iguais
			$shortName = $this -> remove_accents(strtolower(str_replace(" ","", $data['title'])));
						
			$query = "UPDATE phpgw_cc_groups SET title = '".$data['title']."',short_name = '".$shortName."' WHERE id_group = ".$data['id_group'];
			
			if (!$this->db->query($query))			
				exit ('Query failed! File: '.__FILE__.' on line'.__LINE__);				
			
			$this -> updateContactsByGroup($data['id_group'], $data['contact_in_list']);		
						 			
			return True;		
		}
		
                function touchContacts( $pContacts )
                {
                   
                    $qSelect =  'SELECT A.id_contact from phpgw_cc_contact A,'.
                                'phpgw_cc_contact_conns B WHERE '.
                                'A.id_contact = B.id_contact and B.id_connection in('.implode( ',' , $pContacts ).')';
                    
                    
                    $qUpdate = 'UPDATE phpgw_cc_contact '.
                               'SET last_status = \'U\', last_update = \''.time().'000\' '.
                               'WHERE id_contact IN( '.$qSelect.' )';   
                    
                      if (!$this->db->query($qUpdate))
                        exit ('Query failed! File: '.__FILE__.' on line'.__LINE__);
                }
                
                function touchContactsByGroup ( $pIdGroup )
                {
                    $qSelect = 'SELECT A.id_contact from phpgw_cc_contact A,'.
			'phpgw_cc_contact_conns B, phpgw_cc_connections C,phpgw_cc_contact_grps D where '.
			'A.id_contact = B.id_contact and B.id_connection = C.id_connection '.
			' and '.
			' D.id_connection = C.id_connection and D.id_group = '.$pIdGroup;

                    
                    $qUpdate = 'UPDATE phpgw_cc_contact '.
                             'SET last_status = \'U\', last_update = \''.time().'000\' '.
                             'WHERE id_contact IN( '.$qSelect.' )';   
   

                    if (!$this->db->query($qUpdate))
			exit ('Query failed! File: '.__FILE__.' on line'.__LINE__);
			
                    
                }
                
		function delete($data)
		{
			$query = "DELETE FROM phpgw_cc_groups WHERE id_group = ".$data['id_group'];			
										
			if (!$this->db->query($query))			
				exit ('Query failed! File: '.__FILE__.' on line'.__LINE__);				
									
			$this -> deleteContactsByGroup($data['id_group'], $data['contact_in_list']);
			 			
			return True;		
		}		
		
		/*!
		 * @function select_owner_groups
		 * @abstract Busca todos os grupos do usuário atual.
		 * @author Luiz Carlos Viana Melo - Prognus
		 * @param (integer) $id_owner O ID do usuário.
		 * @return Retorna uma estrutura contendo:
		 * array(
		 * 		array(
		 * 			id_group => O ID do grupo;
		 * 			title => O nome do grupo;
		 * 			short_name => O nome abreviado do grupo.
		 * 		)
		 * )
		 */
		function select_owner_groups($id_owner = false)
		{
			$query = 'SELECT id_group, title, short_name FROM phpgw_cc_groups ';
			if ($id_owner)
				$query .= 'WHERE owner = ' . $id_owner. ' ORDER BY title;';
			else
				$query .= 'WHERE owner = ' . $this->owner . ' ORDER BY title;';
				
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
		 * @function select_contact_groups
		 * @abstract Seleciona os grupos do contato.
		 * @author Luiz Carlos Viana Melo - Prognus
		 * @param (integer) $id_contact O ID do contato.
		 * @return Retorna uma estrutura contendo:
		 * array(
		 * 		array(
		 * 			id_contact => O ID do contato;
		 * 			id_group => O ID do grupo;
		 * 			title => O nome do grupo;
		 * 			short_name => O nome abreviado do grupo.
		 * 		)
		 * )
		 */
		function select_contact_groups($id_contact)
		{
			$query = 'SELECT Contato.id_contact, ContatoGprs.id_connection, Grupos.id_group, Grupos.title, ' .
				'Grupos.short_name FROM phpgw_cc_contact_conns Contato, ' . 
				'phpgw_cc_connections Conexao, phpgw_cc_contact_grps ContatoGprs, ' . 
				'phpgw_cc_groups Grupos WHERE Contato.id_contact = ' . $id_contact . 
				' AND Contato.id_connection = Conexao.id_connection AND ' . 
				'Conexao.id_connection = ContatoGprs.id_connection AND ' . 
				'ContatoGprs.id_group = Grupos.id_group ORDER BY title';
			
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
		
		function deleteContactFromGroups($id)
		{
			$query = "DELETE FROM phpgw_cc_contact_grps WHERE id_connection = ".$id;			
										
			if (!$this->db->query($query))			
				exit ('Query failed! File: '.__FILE__.' on line'.__LINE__);				
									
			return True;		
		}
		
		function selectGroupsOwnerCanImportContacts($owner) {
			$query = "select id_group,title,short_name from phpgw_cc_groups where owner=$owner or owner in (select B.acl_location::bigint from phpgw_acl A, phpgw_acl B where
                       A.acl_location=B.acl_account::text and A.acl_account::text=B.acl_location
                       and A.acl_appname = 'contactcenter' and B.acl_appname = 'contactcenter'
                       and A.acl_rights & 4 <> 0 and B.acl_rights & 1 <> 0
                       and A.acl_location = '".$owner."')"; //He can import contacts only to his groups, or shared groups that he gave read permission.
			
			$this->db->query($query);
			
			$return = array();
			
			while($this->db->next_record())
			{
				$return[] = $this->db->row(); 
			}
			
			return $return;
			
		}
		
		//Owner = null means the owner setted on constructor.
		function selectAllContacts( $field = false ,$shared_from=null)
		{
			if ( $shared_from == NULL )
			{
				$query = 'select'
						. ' C.id_connection,'
						. ' A.id_contact,'
						. ' A.names_ordered,'
						. ' A.alias,'
						. ' A.birthdate,'
						. ' A.sex,'
						. ' A.pgp_key,'
						. ' A.notes,'
						. ' A.web_page,'
						. ' A.corporate_name,'
						. ' A.job_title,'
						. ' A.department,'
						. ' C.connection_name,'
						. ' C.connection_value,'
						. ' B.id_typeof_contact_connection,'
						. ' phpgw_cc_contact_addrs.id_typeof_contact_address,'
						. ' phpgw_cc_addresses.address1,'
						. ' phpgw_cc_addresses.address2,'
						. ' phpgw_cc_addresses.complement,'
						. ' phpgw_cc_addresses.postal_code,'
						. ' phpgw_cc_city.city_name,'
						. ' phpgw_cc_state.state_name,'
						. ' phpgw_cc_addresses.id_country'
						;

				$query .= ' from'
                        . ' phpgw_cc_contact A'
                        . ' inner join phpgw_cc_contact_conns B on ( A.id_contact = B.id_contact )'
                        . ' inner join phpgw_cc_connections C on ( B.id_connection = C.id_connection )'
                        . ' left join phpgw_cc_contact_addrs on ( A.id_contact = phpgw_cc_contact_addrs.id_contact )'
						. ' left join phpgw_cc_addresses on ( phpgw_cc_contact_addrs.id_address = phpgw_cc_addresses.id_address )'
						. ' left join phpgw_cc_city on ( phpgw_cc_addresses.id_city = phpgw_cc_city.id_city )'
						. ' left join phpgw_cc_state on ( phpgw_cc_addresses.id_state = phpgw_cc_state.id_state)'
						;

				$query .= ' where'
						. " A.id_owner = {$this->owner}"
						//. ' and phpgw_cc_connections.connection_is_default = true'
						;

			}
			else {
				$sub_query = 'select A.id_related from phpgw_cc_contact_rels A,phpgw_acl B
							  where B.acl_location!=\'run\' and A.id_contact = B.acl_location::bigint and A.id_related = B.acl_account and 
							  B.acl_appname = \'contactcenter\' and B.acl_rights & 1 <> 0 
							  and A.id_typeof_contact_relation=1 and A.id_contact = '.$shared_from.' and A.id_related='.$this->owner;

				$query = 'select C.id_connection, A.id_contact, A.names_ordered, C.connection_value , B.id_typeof_contact_connection from phpgw_cc_contact A,'.
				'phpgw_cc_contact_conns B, phpgw_cc_connections C where '.
				'A.id_contact = B.id_contact and B.id_connection = C.id_connection and '.
				'A.id_owner in ('.$shared_from.',('.$sub_query.'))'.
				' and C.connection_is_default = true ';
			}

			if ( $field == 'only_email' )
				$query .= 'and B.id_typeof_contact_connection = 1 ';

			$query .= ' order by A.names_ordered, C.connection_value';

			if (!$this->db->query($query))
			{
				exit ('Query failed! File: '.__FILE__.' on line'.__LINE__);
			}

			$return = array();

			while($this->db->next_record())
			{
				$return[] = $this->db->row(); 
			}

			if( ! count( $return ) )
				return $return;

                        $all_contacts = array(); 
			foreach( $return as $i => $object )
			{
				if ( ! array_key_exists( $object[ 'id_contact' ], $all_contacts ) )
					$all_contacts[ $object[ 'id_contact' ] ] = array(
						'connection_value' => '',
						'phone' => '',
						'mobile' => '',
						'names_ordered' => '',
						'id_contact' => '',
						'id_connection' => '',
						'alias' => '',
						'birthdate' => '',
						'sex' => '',
						'pgp_key' => '',
						'notes' => '',
						'web_page' => '',
						'corporate_name' => '',
						'job_title' => '',
						'department' => '',				
						'main-mail' => '',
						'aternative-mail' => '',
						'business-phone' => '',
						'business-address' => '',
						'business-complement' => '',
						'business-postal_code' => '',
						'business-city_name' => '',
						'business-state_name' => '',
						'business-id_country' => '',
						'business-fax' => '',
						'business-pager' => '',
						'business-mobile' => '',
						'business-address-2' => '',
						'home-phone' => '',
						'home-address' => '',
						'home-complement' => '',
						'home-postal_code' => '',
						'home-city_name' => '',
						'home-state_name' => '',
						'home-fax' => '',
						'home-pager' => '',
						'home-address-2' => ''
						
						
					);

				switch( $object[ 'id_typeof_contact_connection' ] )
				{
					case 1 :
						$all_contacts[ $object[ 'id_contact' ] ][ 'connection_value' ] = $object[ 'connection_value' ];
						switch ( strtolower( $object[ 'connection_name' ] ) )
						{
							case 'alternativo' :
								$all_contacts[ $object[ 'id_contact' ] ][ 'alternative-mail' ] = $object[ 'connection_value' ];
								break;
							case 'principal' :
								$all_contacts[ $object[ 'id_contact' ] ][ 'main-mail' ] = $object[ 'connection_value' ];
								break;
						}
						break;
					case 2 :
						$all_contacts[ $object[ 'id_contact' ] ][ 'phone' ] = $object[ 'connection_value' ];
						switch ( strtolower( $object[ 'connection_name' ] ) )
						{
							case 'casa' :
								$all_contacts[ $object[ 'id_contact' ] ][ 'home-phone' ] = $object[ 'connection_value' ];
								break;
							case 'celular' :
								$all_contacts[ $object[ 'id_contact' ] ][ 'mobile' ] = $object[ 'connection_value' ];
								break;
							case 'trabalho' :
								$all_contacts[ $object[ 'id_contact' ] ][ 'business-phone' ] = $object[ 'connection_value' ];
								break;								
							case 'fax' :
								$all_contacts[ $object[ 'id_contact' ] ][ 'home-fax' ] = $object[ 'connection_value' ];
								break;
							case 'pager' :
								$all_contacts[ $object[ 'id_contact' ] ][ 'home-pager' ] = $object[ 'connection_value' ];
								break;
							case 'celular corporativo' :
								$all_contacts[ $object[ 'id_contact' ] ][ 'business-mobile' ] = $object[ 'connection_value' ];
								break;								
							case 'pager corporativo' :
								$all_contacts[ $object[ 'id_contact' ] ][ 'business-pager' ] = $object[ 'connection_value' ];
								break;
							case 'fax corporativo' :
								$all_contacts[ $object[ 'id_contact' ] ][ 'business-fax' ] = $object[ 'connection_value' ];
								break;
						}
						break;
				}

				$all_contacts[ $object[ 'id_contact' ] ][ 'names_ordered' ] = $object[ 'names_ordered' ];
				$all_contacts[ $object[ 'id_contact' ] ][ 'id_contact' ]    = $object[ 'id_contact' ];
				$all_contacts[ $object[ 'id_contact' ] ][ 'id_connection' ] = $object[ 'id_connection' ];
				$all_contacts[ $object[ 'id_contact' ] ][ 'alias' ]         = $object[ 'alias' ];				
				$all_contacts[ $object[ 'id_contact' ] ][ 'birthdate' ] 	= $object[ 'birthdate' ];
				$all_contacts[ $object[ 'id_contact' ] ][ 'sex' ]    		= $object[ 'sex' ];
				$all_contacts[ $object[ 'id_contact' ] ][ 'pgp_key' ] 		= $object[ 'pgp_key' ];
				$all_contacts[ $object[ 'id_contact' ] ][ 'notes' ]         = $object[ 'notes' ];
				$all_contacts[ $object[ 'id_contact' ] ][ 'web_page' ] 		= $object[ 'web_page' ];
				$all_contacts[ $object[ 'id_contact' ] ][ 'corporate_name' ]= $object[ 'corporate_name' ];
				$all_contacts[ $object[ 'id_contact' ] ][ 'job_title' ] 	= $object[ 'job_title' ];
				$all_contacts[ $object[ 'id_contact' ] ][ 'department' ]    = $object[ 'department' ];

				switch( $object[ 'id_typeof_contact_address' ] )
				{
					case 1 :
						$all_contacts[ $object[ 'id_contact' ] ][ 'business-address' ]     = $object[ 'address1' ];
						$all_contacts[ $object[ 'id_contact' ] ][ 'business-address-2' ]   = $object[ 'address2' ];
						$all_contacts[ $object[ 'id_contact' ] ][ 'business-complement' ]  = $object[ 'complement' ];
						$all_contacts[ $object[ 'id_contact' ] ][ 'business-postal_code' ] = $object[ 'postal_code' ];
						$all_contacts[ $object[ 'id_contact' ] ][ 'business-city_name' ]   = $object[ 'city_name' ];
						$all_contacts[ $object[ 'id_contact' ] ][ 'business-state_name' ]  = $object[ 'state_name' ];
						$all_contacts[ $object[ 'id_contact' ] ][ 'business-id_country' ]  = $object[ 'id_country' ];
						break;
					case 2 :
						$all_contacts[ $object[ 'id_contact' ] ][ 'home-address' ]     = $object[ 'address1' ];
						$all_contacts[ $object[ 'id_contact' ] ][ 'home-address-2' ]   = $object[ 'address2' ];
						$all_contacts[ $object[ 'id_contact' ] ][ 'home-complement' ]  = $object[ 'complement' ];
						$all_contacts[ $object[ 'id_contact' ] ][ 'home-postal_code' ] = $object[ 'postal_code' ];
						$all_contacts[ $object[ 'id_contact' ] ][ 'home-city_name' ]   = $object[ 'city_name' ];
						$all_contacts[ $object[ 'id_contact' ] ][ 'home-state_name' ]  = $object[ 'state_name' ];
						$all_contacts[ $object[ 'id_contact' ] ][ 'home-id_country' ]  = $object[ 'id_country' ];
						break;
				}
			}

			return array_values($all_contacts);
		}

		function verifySharedContact($owner,$email)
		{
			$query = 'select A.names_ordered, C.id_connection from phpgw_cc_contact A,'.
			'phpgw_cc_contact_conns B, phpgw_cc_connections C where '.
			'A.id_contact = B.id_contact and B.id_connection = C.id_connection '.
			'and B.id_typeof_contact_connection = 1 and '.
			'A.id_owner ='.$owner.' and C.connection_value = \''.$email.'\'';

			if (!$this->db->query($query))
			{
				exit ('Query failed! File: '.__FILE__.' on line'.__LINE__);
			}

			$return = false;

			while($this->db->next_record())
			{
				$row = $this->db->row();
				$return[] =  $row['names_ordered'];
                                $return[] =  $row['id_connection'];
			}

			return $return;
		}

		function selectContactsByGroup($idGroup)
		{
			$query = 'select C.id_connection, A.names_ordered, C.connection_value, A.id_contact from phpgw_cc_contact A,'.
			'phpgw_cc_contact_conns B, phpgw_cc_connections C,phpgw_cc_contact_grps D where '.
			'A.id_contact = B.id_contact and B.id_connection = C.id_connection '.
			'and '.
			//'A.id_owner ='.$this->owner.' and'.
			' D.id_connection = C.id_connection and D.id_group = '.$idGroup.
			' order by A.names_ordered';
						
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
		
		function selectContactsByGroupAlias($alias)
		{
			$query = "select C.id_connection, A.names_ordered, C.connection_value from phpgw_cc_contact A, ".
			"phpgw_cc_contact_conns B, phpgw_cc_connections C,phpgw_cc_contact_grps D,phpgw_cc_groups E where ".
			"A.id_contact = B.id_contact and B.id_connection = C.id_connection ".
			" and ".
			"A.id_owner =".$this->owner." and ".			
			"D.id_group = E.id_group and ".
			"D.id_connection = C.id_connection and E.short_name = '".$alias.
			"' order by A.names_ordered";
						
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
		
		function insertContactsByGroup($idGroup, $contacts)
		{									
			
			foreach($contacts as $index => $idConnection) 
			{			
				$query = "INSERT INTO phpgw_cc_contact_grps(id_group,id_connection) ".
			 			"VALUES(".$idGroup.",".$idConnection.")";			

				if (!$this->db->query($query))
					exit ('Query failed! File: '.__FILE__.' on line'.__LINE__);			
			}
									 			
			return True;		
		}
		
		function updateContactsByGroup($id_group, $contacts)
		{
                        //Atualiza timestamp de status dos contatos afetados
                        $this -> touchContacts($contacts);
                        $this -> touchContactsByGroup($id_group);
                        ///---------------------------------------------------//
			$query = 'select C.id_connection from phpgw_cc_contact A,'.
			'phpgw_cc_contact_conns B, phpgw_cc_connections C,phpgw_cc_contact_grps D where '.
			'A.id_contact = B.id_contact and B.id_connection = C.id_connection '.
			' and '.
			//'A.id_owner ='.$this->owner.' and D.id_connection = C.id_connection and D.id_group = '.$id_group.
			' D.id_connection = C.id_connection and D.id_group = '.$id_group. //If I have the group ID, why ask about owner?
			' order by A.names_ordered';
						
			if (!$this->db->query($query))
			{
				exit ('Query failed! File: '.__FILE__.' on line'.__LINE__);
			}
			 
			$return = false;
			
			
			while($this->db->next_record())
			{
				$return[] = $this->db->row();
				 
			}
						
			$array_connections = array();
			
			if($return) {
				foreach($return as $index => $connection){				
					array_push($array_connections,$connection['id_connection']);
				}
			}
			
			if(!is_array($contacts))
				$contacts = array();
			
			if(!is_array($connections))
				$connections = array();				
									
			$connections_to_add 	= array_diff($contacts, $array_connections);
			$connections_to_remove 	= array_diff($array_connections,$contacts);
			
			if($connections_to_add){
				$this -> insertContactsByGroup($id_group, $connections_to_add);	 
			}
			
			if($connections_to_remove){
				$this -> deleteContactsByGroup($id_group, $connections_to_remove);
			}
			
			return True;
		}		
		
		function updateContactGroups($id_defaultconnection, $connections, $groups)
		{
			if (is_array($groups) && count($groups) > 0)
			{
				$query = "UPDATE phpgw_cc_contact_grps SET id_connection = " . $id_defaultconnection . " WHERE (";
				$more = false;
				foreach ($connections as $connection)
				{
					if ($more)
					$query .= " OR ";
					$query .= "id_connection = " . $connection['id_connection'];
					$more = true;
				}
				$query .= ") AND (";
				$more = false;
				foreach ($groups as $group)
				{
					if ($more)
					$query .= " OR ";
					$query .= "id_group = " . $group['id_group'];
					$more = true;
				}
				$query .= ");";
				if (!$this->db->query($query))			
					exit ('Query failed! File: '.__FILE__.' on line'.__LINE__);				
						 			
				return true;
			}
		}
		
		function deleteContactsByGroup($id_group, $contacts = null)
		{
			
			$query = "DELETE FROM phpgw_cc_contact_grps ";
			
			if($contacts) {						
				$index = 0;
				
				foreach($contacts as $a => $id_connection) {
					 
					if($index++)
						$query .= " OR";
					else
						$query .= " WHERE (";
					 
					$query .= " id_connection = ".$id_connection;										
				}
				$query .= ") AND ";
			} 	
			else
				$query.= " WHERE ";
			
			$query.= "id_group = ".$id_group;
			
			
			if (!$this->db->query($query))			
				exit ('Query failed! File: '.__FILE__.' on line'.__LINE__);				
						 			
			return True;		
		}
		
		function add_user_by_name($id_group){
			$query = 'select C.id_connection, A.id_contact, A.names_ordered, C.connection_value, B.id_typeof_contact_connection'.
			         ' from phpgw_cc_contact A, phpgw_cc_contact_conns B, phpgw_cc_connections C'. 
					 ' where A.id_contact = B.id_contact and B.id_connection = C.id_connection'.
					 	' and A.last_update = (select max(up.last_update) from phpgw_cc_contact up where up.id_owner ='.$this->owner.")".
					 	' and A.id_owner ='.$this->owner.' and C.connection_is_default = true'.
					 ' order by A.names_ordered,C.connection_value';
			
						
			if (!$this->db->query($query)){
				exit ('Query failed! File: '.__FILE__.' on line'.__LINE__);
			}
			 
			$return = array(); 
			
			$array_connections = array();
			while($this->db->next_record()){
				$return = $this->db->row();
				array_push($array_connections, $return['id_connection']);				
			}			
			$this -> insertContactsByGroup($id_group, $array_connections);
									
		}
		
		
		function verifyContact($email, $name, $phone) 
	 	                { 
								if($email && $phone && $name){
								
									$query = 'select A.names_ordered, C.id_connection from phpgw_cc_contact A,'. 
	 	                                'phpgw_cc_contact_conns B, phpgw_cc_connections C where '. 
	 	                                'A.id_contact = B.id_contact and B.id_connection = C.id_connection '. 
	 	                                'and B.id_typeof_contact_connection = 1 and '. 
	 	                                "A.id_owner ='" .$this->owner."' and (C.connection_value = '".$email. "' or C.connection_value = '".$phone. "') and RTRIM(A.names_ordered) = '". $name. "'"; 
								
									if (!$this->db->query($query)) 
	 	                                {        
	 	                                        exit ('Query failed! File: '.__FILE__.' on line'.__LINE__); 
	 	                                } 
	 	                          
	 	                                $return = false; 
	 	                         
	 	                                while($this->db->next_record()) 
	 	                                { 
	 	                                        $row = $this->db->row(); 
	 	                                        $return[] =  $row['names_ordered'];  
	 	                                } 
								}
	 	                        if (!$return && $email) { 
	 	                         
	 	                                $query = 'select A.names_ordered, C.id_connection from phpgw_cc_contact A,'. 
	 	                                'phpgw_cc_contact_conns B, phpgw_cc_connections C where '. 
	 	                                'A.id_contact = B.id_contact and B.id_connection = C.id_connection '. 
	 	                                'and B.id_typeof_contact_connection = 1 and '. 
	 	                                "A.id_owner ='" .$this->owner."' and C.connection_value = '".$email. "' and RTRIM(A.names_ordered) = '". $name. "'"; 
	 	 
	 	                                if (!$this->db->query($query)) 
	 	                                {        
	 	                                        exit ('Query failed! File: '.__FILE__.' on line'.__LINE__); 
	 	                                } 
	 	                          
	 	                                $return = false; 
	 	                         
	 	                                while($this->db->next_record()) 
	 	                                { 
	 	                                        $row = $this->db->row(); 
	 	                                        $return[] =  $row['names_ordered'];  
	 	                                } 
	 	                        }  	               
	 	                        if (!$return && $phone) {  
	 	 
										$query = 'select A.names_ordered, C.id_connection from phpgw_cc_contact A,'. 
	 	                                'phpgw_cc_contact_conns B, phpgw_cc_connections C where '. 
	 	                                'A.id_contact = B.id_contact and B.id_connection = C.id_connection '. 
	 	                                'and B.id_typeof_contact_connection = 2 and '. 
	 	                                "A.id_owner ='" .$this->owner."' and C.connection_value = '".$phone. "' and RTRIM(A.names_ordered) = '". $name. "'"; 
	 	 
	 	                                if (!$this->db->query($query)) 
	 	                                {        
	 	                                        exit ('Query failed! File: '.__FILE__.' on line'.__LINE__); 
	 	                                } 
	 	                          
	 	                                $return = false; 
	 	                         
	 	                                while($this->db->next_record()) 
	 	                                { 
	 	                                        $row = $this->db->row(); 
	 	                                        $return[] =  $row['names_ordered'];  
	 	                                } 
	 	 
	 	                        } 

	 	                        if (!$return && $name) {  
	 	 
	 	                                $query = "
										select A.names_ordered, C.id_connection 
										
										from phpgw_cc_contact A, 
											phpgw_cc_contact_conns B, 
											phpgw_cc_connections C
										where A.id_contact = B.id_contact and B.id_connection = C.id_connection and
										B.id_typeof_contact_connection = 2 and
										A.id_owner = '" . $this->owner . "' and RTRIM(A.names_ordered) = '". $name. "'"; 
	 	 
	 	                                if (!$this->db->query($query)) 
	 	                                {        
	 	                                        exit ('Query failed! File: '.__FILE__.' on line'.__LINE__); 
	 	                                } 
	 	                          
	 	                                $return = false; 
	 	                         
	 	                                while($this->db->next_record()) 
	 	                                { 
	 	                                        $row = $this->db->row(); 
	 	                                        $return[] =  $row['names_ordered'];  
	 	                                } 
	 	 
	 	                        } 
	 	                    $return[] =  $row['id_connection']; 

	 	                        return $return; 
	 	                } 
		
		
	}
?>
