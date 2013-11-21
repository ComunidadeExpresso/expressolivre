<?php

/**************************************************************************\
* eGroupWare                                                               *
* http://www.egroupware.org                                                *
* --------------------------------------------                             *
*  This program is free software; you can redistribute it and/or modify it *
*  under the terms of the GNU General Public License as published by the   *
*  Free Software Foundation; either version 2 of the License, or (at your  *
*  option) any later version.                                              *
\**************************************************************************/

/**
* Provê métodos para administrar os perfis do processo.
* @author Sidnei Augusto Drovetto Junior - drovetto@gmail.com
* @version 1.0
* @license http://www.gnu.org/copyleft/gpl.html GPL
* @package Workflow
* @subpackage local
*/
class wf_role
{
	/**
	* @var int $processID o ID do processo onde a classe está sendo utilizada
	* @access private
	*/
	private $processID;

	/**
	* @var object $db link com o banco de dados do Expresso
	* @access private
	*/
	private $db;

	/**
	* @var object $roleManager objeto da classe RoleManager
	* @access private
	*/
	private $roleManager;

	/**
	* @var object $activityManager objeto da classe ActivityManager
	* @access private
	*/
	private $activityManager;

	/**
	* Construtor da wf_role
	* @return object
	* @access public
	*/
	function wf_role()
	{
		if (!is_null($GLOBALS['workflow']['wf_runtime']->activity))
			$this->processID = (int) $GLOBALS['workflow']['wf_runtime']->activity->getProcessId();
		if (isset($GLOBALS['workflow']['job']))
			$this->processID = (int) $GLOBALS['workflow']['job']['processID'];

		$this->db = &Factory::getInstance('WorkflowObjects')->getDBGalaxia()->Link_ID;
		$this->roleManager = Factory::getInstance('workflow_rolemanager');
		$this->activityManager = Factory::getInstance('workflow_activitymanager');
	}

	/**
	* Busca os perfis pertencentes ao processo que chama o método.
	* @return array Lista dos perfis pertencentes ao processo.
	* @access public
	*/
	function getRoles()
	{
		$output = array();
		$roles = $this->roleManager->list_roles($this->processID, 0, -1, 'wf_name__ASC', '', '');
		foreach ($roles['data'] as $role)
			$output[] = array('id' => $role['wf_role_id'], 'name' => $role['wf_name'], 'description' => $role['wf_description']);
		return $output;
	}

	/**
	* Busca os perfis associados a uma atividade do processo.
	* @param string $activityName O nome da atividade da qual se quer os perfis.
	* @return array Lista dos perfis pertencentes à atividade.
	* @access public
	*/
	function getActivityRoles($activityName)
	{
		/* check if the activity exists */
		$output = array();
		if (!$this->activityManager->activity_name_exists($this->processID, $activityName))
			return $output;

		/* get the roles */
		$activityID = $this->activityManager->_get_activity_id_by_name($this->processID, $activityName);
		$roles = $this->activityManager->get_activity_roles($activityID);
		foreach ($roles as $role)
			$output[] = array('id' => $role['wf_role_id'], 'name' => $role['wf_name'], 'read_only' => ($role['wf_readonly'] == 0) ? false : true);

		usort($output, create_function('$a,$b', 'return strcasecmp($a[\'name\'],$b[\'name\']);'));
		return $output;
	}

	/**
	* Busca o id de uma atividade pelo seu nome
	* @param string $activityName O nome da atividade da qual se quer o id
	* @return int O id da atividade ou null se não encontrado
	* @access public
	*/
	function getActivityIdByName($activityName)
	{
		/* check if the activity exists */
		if (!$this->activityManager->activity_name_exists($this->processID, $activityName))
			return null;

		/* get the id */
		$activityID = $this->activityManager->_get_activity_id_by_name($this->processID, $activityName);
		return $activityID;
	}

	/**
	* Cria um perfil que poderá ser utilizado no processo atual.
	* @param string $roleName O nome do perfil que será criado.
	* @param string $description A descrição do perfil que será criado.
	* @return mixed O ID do perfil criado. Em caso de erro, será retornado false.
	* @access public
	*/
	function createRole($roleName, $description)
	{
		$values = array(
			'wf_name' => $roleName,
			'wf_description' => $description);
		return $this->roleManager->replace_role($this->processID, 0, $values);
	}

	/**
	* Remove um perfil.
	* @param string $roleName O nome do perfil que será removido.
	* @return bool Será retornado true em caso de sucesso e false caso contrário.
	* @access public
	*/
	function removeRole($roleName)
	{
		/* check if the role exists */
		if ($this->roleManager->role_name_exists($this->processID, $roleName) == 0)
			return false;

		/* remove the role */
		$roleID = $this->roleManager->get_role_id($this->processID, $roleName);
		return $this->roleManager->remove_role($this->processID, $roleID);
	}

	/**
	* Atualiza um perfil.
	* @param string $previousRoleName O nome do perfil que será atualizado.
	* @param string $roleName O novo nome do perfil que será atualizado.
	* @param string $description A nova descrição do perfil que será atualizado.
	* @return mixed O ID do perfil atualizado. Em caso de erro, será retornado false.
	* @access public
	*/
	function updateRole($previousRoleName, $newRoleName, $newDescription)
	{
		/* check if the role exists */
		if ($this->roleManager->role_name_exists($this->processID, $previousRoleName) == 0)
			return false;

		/* update the role */
		$roleID = $this->roleManager->get_role_id($this->processID, $previousRoleName);
		$values = array(
			'wf_name' => $newRoleName,
			'wf_description' => $newDescription);
		return $this->roleManager->replace_role($this->processID, $roleID, $values);
	}

	/**
	* Adiciona usuários/grupos a um perfil.
	* @param mixed $users Um inteiro ou um array de inteiros representando os IDs dos usuários/grupos que serão adicionados ao perfil.
	* @param string $roleName O nome do perfil que receberá os usuários.
	* @access public
	*/
	function addUsersToRole($users, $roleName)
	{
		/* check if the role exists */
		if ($this->roleManager->role_name_exists($this->processID, $roleName) == 0)
			return false;

		/* add the user/group to the role */
		if (!is_array($users))
			$users = array((int) $users);
		$roleID = $this->roleManager->get_role_id($this->processID, $roleName);
		$ldap = Factory::getInstance('WorkflowLDAP');
		foreach ($users as $user)
		{
			$user = str_replace('u', '', str_replace('g', '', $user));
			$accountType = $ldap->getEntityType($user);
			if (($accountType == 'u') || ($accountType == 'g'))
				$this->roleManager->map_user_to_role($this->processID, $user, $roleID, $accountType);
		}
	}

	/**
	* Busca os usuários/grupos de um perfil.
	* @param string  $roleName O nome do perfil do qual se quer os usuários/grupos.
	* @param boolean $expandGroups Valor booleano que indica se os grupos devem ser expandidos.
	* @return array Lista dos usuários/grupos (id, nome e tipo) pertencentes ao perfil especificado.
	* @access public
	*/
	function getUsersFromRole($roleName, $expandGroups = false)
	{
		/* check if the role exists */
		if ($this->roleManager->role_name_exists($this->processID, $roleName) == 0)
			return false;

		/* get the user/group from the role */
		$users = $this->roleManager->list_mapped_users($this->processID, false, array('wf_role_name' => $roleName));

		$ldap = Factory::getInstance('WorkflowLDAP');
		$output = array();
		foreach ($users as $id => $login)
		{
			$accountType = $ldap->getEntityType($id);
			// if it must expand the group, get its users and put them into the tmp_output array using
			// their uidnumber as key (it avoids duplicated values)
			if($accountType == 'g' && $expandGroups)
			{
				$groupUsers = $ldap->getGroupUsers($id);
				foreach ($groupUsers as $groupUser)
					if ((!isset($output[$groupUser['account_id']])) && ($groupUser['account_name'] != ''))
						$output[$groupUser['account_id']] = array('id' => $groupUser['account_id'],	'name' => $groupUser['account_name'],	'type' => 'u');
			}
			else
			{
				if (isset($output[$id]))
					continue;

				$name = $ldap->getName($id);
				if ($name != '')
					$output[$id] = array('id' => $id, 'name' => $name, 'type' => $accountType);
			}
		}

		// format the output array
		$output = array_values($output);

		usort($output, create_function('$a,$b', 'return strcasecmp($a[\'name\'],$b[\'name\']);'));
		return $output;
	}

	/**
	* Remove os usuários/grupos um perfil.
	* @param mixed $users Um inteiro ou um array de inteiros representando os IDs dos usuários/grupos que serão removidos do perfil.
	* @param string $roleName O nome do perfil que terá os usuários/grupos removidos.
	* @access public
	*/
	function removeUsersFromRole($users, $roleName)
	{
		/* check if the role exists */
		if ($this->roleManager->role_name_exists($this->processID, $roleName) == 0)
			return false;

		/* remove the users from the role */
		if (!is_array($users))
			$users = array((int) $users);
		$roleID = $this->roleManager->get_role_id($this->processID, $roleName);

		foreach ($users as $user)
		{
			$user = str_replace('u', '', str_replace('g', '', $user));
			$this->roleManager->remove_mapping($user, $roleID);
		}
	}

	/**
	* Associa um perfil a uma atividade.
	* @param string $roleName O nome do perfil que será associado à atividade.
	* @param string $activityName O nome da atividade que será associada ao perfil.
	* @return bool Será retornado true em caso de sucesso e false caso contrário.
	* @access public
	*/
	function mapRoleToActivity($roleName, $activityName, $readOnly = false)
	{
		/* check if the role and the activity exist */
		if (($this->roleManager->role_name_exists($this->processID, $roleName) == 0) || (!$this->activityManager->activity_name_exists($this->processID, $activityName)))
			return false;

		/* create the new mapping */
		$activityID = $this->activityManager->_get_activity_id_by_name($this->processID, $activityName);
		$roleID = $this->roleManager->get_role_id($this->processID, $roleName);
		return $this->activityManager->add_activity_role($activityID, $roleID, $readOnly);
	}

	/**
	* Desassocia um perfil de uma atividade.
	* @param string $roleName O nome do perfil que será desassociado à atividade.
	* @param string $activityName O nome da atividade que será desassociada ao perfil.
	* @return bool Será retornado true em caso de sucesso e false caso contrário.
	* @access public
	*/
	function unmapRoleFromActivity($roleName, $activityName)
	{
		/* check if the role and the activity exist */
		if (($this->roleManager->role_name_exists($this->processID, $roleName) == 0) || (!$this->activityManager->activity_name_exists($this->processID, $activityName)))
			return false;

		/* remove the mapping */
		$activityID = $this->activityManager->_get_activity_id_by_name($this->processID, $activityName);
		$roleID = $this->roleManager->get_role_id($this->processID, $roleName);
		return $this->activityManager->remove_activity_role($activityID, $roleID);
	}

	/**
	* Recupera o ID de um perfil através do seu nome
	* @param string $roleName O nome do perfil que será criado.
	* @return int O ID do perfil solicitado
	* @access public
	*/
	function getRoleIdByName($roleName)
	{
		/* check if the role exists */
		if ($this->roleManager->role_name_exists($this->processID, $roleName) == 0)
			return false;

		/* get roleId by roleName */
		return $this->roleManager->get_role_id($this->processID, $roleName);
	}

	/**
	* Recupera informações sobre um perfil a partir de seu ID
	* @param int $roleID O ID do perfil solicitado.
	* @return mixed Uma array associativa contendo informações sobre o perfil. Em caso de erro, será retornado false.
	* @access public
	*/
	function getRoleByID($roleID)
	{
		/* get role information */
		$data = $this->roleManager->get_role($this->processID, $roleID);
		if (!is_array($data))
			return false;

		/* user friendly keys */
		$output = array(
			'id' => $data['wf_role_id'],
			'name' => $data['wf_name'],
			'description' => $data['wf_description']
		);

		return $output;
	}

	/**
	* Busca os perfis de um usuário
	* @param int $userID O ID do usuário.
	* @param string $activityName O nome de uma atividade do processo (parâmetro opcional que se utilizado, verifica somente os perfis do usuário na atividade indicada).
	* @return array Uma array contendo os perfis do usuário.
	* @access public
	*/
	function getUserRoles($userID, $activityName = null)
	{
		/* get valid roles and initilize some variables */
		$roles = (is_null($activityName)) ? $this->getRoles() : $this->getActivityRoles($activityName);
		$userID = (int) $userID;
		$output = array();

		/* if no role found, return an empty array */
		if (count($roles) < 1)
			return $output;

		/* get only the IDs of the roles */
		$roleIDs = array_map(create_function('$a', 'return $a[\'id\'];'), $roles);

		/* get the roles */
		$userGroups = Factory::getInstance('WorkflowLDAP')->getUserGroups($userID);
		$query = 'SELECT DISTINCT role.wf_role_id, role.wf_name, role.wf_description FROM egw_wf_roles role, egw_wf_user_roles user_role WHERE (user_role.wf_role_id = role.wf_role_id) AND (role.wf_p_id = ?) AND (role.wf_role_id = ANY (?))';
		$query .= ' AND (((user_role.wf_user = ?) AND (user_role.wf_account_type = ?))';
		$values = array($this->processID, '{' . implode(', ', $roleIDs) . '}', $userID, 'u');
		if (!empty($userGroups))
		{
			$query .= ' OR ((user_role.wf_user = ANY (?)) AND (user_role.wf_account_type = ?))';
			$values[] = '{' . implode(', ', $userGroups) . '}';
			$values[] = 'g';
		}
		$query .= ')';
		$resultSet = $this->db->query($query, $values)->getArray(-1);

		/* format the output */
		foreach ($resultSet as $row)
			$output[] = array(
				'id' => $row['wf_role_id'],
				'name' => $row['wf_name'],
				'description' => $row['wf_description']
			);

		return $output;
	}

	/**
	* Verifica se um usuário pertence a um determinado perfil
	* @param int $userID O ID do usuário.
	* @param string $roleName O nome do perfil.
	* @return bool True se o usuário pertence ao perfil ou false caso contrário.
	* @access public
	*/
	function checkUserInRole($userID, $roleName)
	{
		$roles = $this->getUserRoles($userID);
		foreach ($roles as $role)
			if ($role['name'] == $roleName)
				return true;

		return false;
	}
}
?>
