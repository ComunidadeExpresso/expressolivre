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
		
	class bo_group
	{
			
		var $so;
		
		function bo_group()
		{	
			$this->so = CreateObject('contactcenter.so_group');
		}
		
		function get_groups()
		{				
			return $this->so -> select();			
		}
		
		function get_group($id)
		{	$result = $this-> so -> select($id);			
			return $result[0]; 
		}
		
		function commit($status, $data)
		{
			if($status == 'insert')
				$result = $this-> so -> insert($data);
				
			else if($status == 'update')
				$result = $this-> so -> update($data);
				
			else if($status == 'delete')
				$result = $this-> so -> delete($data);				
				
			
			return $result;
		}
		
		function get_all_contacts($field = false,$owner=null){
		
			$result = $this-> so -> selectAllContacts($field,$owner);
			return $result;
		}
		
		function verify_shared_contact($owner,$email){

			$result = $this-> so -> verifySharedContact($owner,$email);
			return $result;
		}
		
		function get_contacts_by_group($id){
		
			$result = $this-> so -> selectContactsByGroup($id);
			return $result;
		}
		
		function verify_contact($email, $name, $phone){  	                 
            $result = $this->so->verifyContact($email, $name, $phone); 
            return $result; 
		}
		
		function getContactsByGroupAlias($alias){
			$result = $this-> so -> selectContactsByGroupAlias($alias);
			return $result;
		}		
		
		/*!
		 * @function get_groups_by_user
		 * @abstract Busca todos os grupos cadastrados de um usuário.
		 * @author Luiz Carlos Viana Melo - Prognus
		 * @param (integer) $id_user O ID do usuário.
		 * @return Retorna uma estrutura contendo:
		 * array(
		 * 		array(
		 * 			id_group => O ID do grupo;
		 * 			title => O nome do grupo;
		 * 			short_name => O nome abreviado do grupo.
		 * 		)
		 * )
		 */
		function get_groups_by_user($id_user = false)
		{
			return $this->so->select_owner_groups($id_user);
		}
		
		/*!
		 * @function get_contact_groups
		 * @abstract Busca todos os grupos a qual o contato pertence.
		 * @author Luiz Carlos Viana Melo - Prognus
		 * @param (integer) $id_contact O ID do contato.
		 * @return Retorna uma estrutura contendo:
		 * array(
		 * 		array(
		 * 			id_contact => O ID do contato;
		 * 			id_connection => O ID da conexão de associação;
		 * 			id_group => O ID do grupo;
		 * 			title => O nome do grupo;
		 * 			short_name => O nome abreviado do grupo.
		 * 		)
		 * )
		 */
		function get_contact_groups ($id_contact)
		{
			return $this->so->select_contact_groups($id_contact);
		}
		
		/*!
		 * @function add_contacts_in_group
		 * @abstract Adiciona novos contatos em um determinado grupo.
		 * @author Luiz Carlos Viana Melo - Prognus
		 * @param (integer) $id_group O ID do grupo onde serão adicionados os contatos.
		 * @param (array) $contacts_conns Os IDs das conexões dos contatos que serão
		 * 					associados ao grupo.
		 * @return Retorna true caso for adicionado com sucesso.
		 */
		function add_contacts_in_group($id_group, $contacts_conns)
		{
			//essa função recebe uma lista de ID de conexões que serão adicionadas no grupo.
			return $this->so->insertContactsByGroup($id_group, $contacts_conns);
		}
		
		/*!
		 * @function remove_contacts_from_group
		 * @abstract Remove contatos de um grupo.
		 * @author Luiz Carlos Viana Melo - Prognus
		 * @param (integer) $id_group O ID do grupo de onde serão removidos os contatos.
		 * @param (array) $contact_conns Os ID das conexões dos contatos que serão
		 * 					removidos do grupo.
		 * @return Retorna True caso for removidos com sucesso.
		 */
		function remove_contacts_from_group($id_group, $contacts_conns)
		{
			return $this->so->deleteContactsByGroup($id_group, $contacts_conns);
		}

		function update_contact_groups($id_defaultconnection, $connections, $groups)
		{
			return $this->so->updateContactGroups($id_defaultconnection, $connections, $groups);
		}
		
	}
?>
