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
 * Realiza operações de acl e organograma no banco de dados e ldap
 *
 * @package Workflow
 * @class so_adminaccess
 * @private
 * @author Sidnei Augusto Drovetto Junior - drovetto@gmail.com
 * @author Rodrigo Daniel C de Lira - rodrigo.lira@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 */
class so_adminaccess
{
	/**
	* Objeto do banco de dados (conectado)
	*
	* @var object $db
	* @access public
	*/
	var $db;

	private $numberOfPermissions = 32;

	/**
	* Recebe objeto do banco de dados e armazena como atributo da classe.
	*
	* @return void
	* @access public
	*/
	function so_adminaccess($db)
	{
		$this->db = $db;
	}

	/**
	* Obtém a lista de administradores de um processo
	*
	* @param integer $proc_id
	* @return array
	* @access public
	*/
	function get_process_admins_id($proc_id)
	{
		return $this->getResourceAdmins('PRO', $proc_id);
	}

	/**
	* Obtém a lista de administradores do organograma de uma organização
	*
	* @param integer $org
	* @return array
	* @access public
	*/
	function get_organogram_admins_id($org)
	{
		return $this->getResourceAdmins('ORG', $org);
	}

	/**
	* Obtém os atributos cn e uidNumber, no ldap, para uma lista de usuários
	*
	* @param array $userIDs Os IDs dos usuários/grupos
	* @return array Uma array contendo os IDs e nomes que foram encontrados
	* @access public
	*/
	function getUserNames($userIDs)
	{
		$output = array();
		$names = Factory::getInstance('WorkflowLDAP')->getNames($userIDs);
		foreach ($names as $name)
			$output[] = array(
				'cn' => $name['name'],
				'uidnumber' => $name['id']);
		return $output;
    }

	/**
	* Remove o administrador de um organograma
	*
	* @param integer $org_id
	* @param integer $admin_id
	* @return void
	* @access public
	*/
	function del_organogram_admin($org_id,$admin_id)
	{
		$this->removeAdmin('ORG', $admin_id, $org_id);
	}

	/**
	* Remove o administrador de um processo
	*
	* @param integer $proc_id
	* @param integer $admin_id
	* @return void
	* @access public
	*/
	function del_process_admin($proc_id,$admin_id)
	{
		$this->removeAdmin('PRO', $admin_id, $proc_id);
	}

	/**
	* Remove todos os administradores de um processo
	*
	* @param integer $proc_id
	* @return void
	* @access public
	*/
	function del_process($proc_id)
	{
		$this->removeAdminsFromResource('PRO', $proc_id);
	}


	/**
	* Insere administradores de organograma
	*
	* @param integer $org_id
	* @param array $ids
	* @return void
	* @access public
	*/
	function add_organogram_admins($org_id,$ids)
	{
		$this->addAdmin('ORG', $ids, $org_id);
	}

	/**
	* Insere administradores de um processo
	*
	* @param integer $proc_id
	* @param integer $ids
	* @return void
	* @access public
	*/
	function add_process_admins($proc_id,$ids)
	{
		$this->addAdmin('PRO', $ids, $proc_id);
	}

	/**
	* Verifica se um usuário pode administrar um processo
	*
	* @param integer $user_id
	* @param integer $proc_id
	* @return array
	* @access public
	*/
	function check_process_access($user_id,$proc_id)
	{
		return $this->checkUserGroupAccessToResource('PRO', $user_id, $proc_id);
	}

		/**
	* Obtém os processos permitidos para um usuário
	*
	* @param integer $user_id
	* @return array
	* @access public
	*/
	function get_granted_processes($user_id)
	{
		return $this->getUserGroupPermissions('PRO', $user_id);
	}

	/**
	* Obtém os organogramas permitidos para um usuário
	*
	* @param integer $user_id
	* @return array
	* @access public
	*/
	function get_granted_organograms($user_id)
	{
		return $this->getUserPermissions('ORG', $user_id, 0);
	}

	/**
	* Indica se um usuário é administrador do Workflow ou não.
	*
	* @param int $userID O ID do usuário
	* @return bool true se o usuário é um administrador do Workflow (ou do Expresso) ou false caso contrário
	* @access public
	*/
	function checkWorkflowAdmin($userID)
	{
		if ($this->checkUserGroupAccessToType('ADM', $userID) === true)
			return true;

		if (!is_object($GLOBALS['phpgw']->acl))
		{
			$GLOBALS['phpgw']->db =& Factory::getInstance('WorkflowObjects')->getDBExpresso();
			$GLOBALS['phpgw']->acl =& Factory::getInstance('acl', $userID);
		}

		if (is_object($GLOBALS['phpgw']->acl))
			if ($GLOBALS['phpgw']->acl->check('run', 1, 'admin') === true)
				return true;

		return false;
	}

	/**
	* Obtém a lista de permissoes equivalente ao numero decimal passado como parametro
	*
	* @param integer $number
	* @return array
	* @access public
	*/
	function _numberToPermissionList($number)
	{
		/* prepare the permission list*/
		$levelList = array_fill(0, $this->numberOfPermissions, false);

		$tmpBin = decbin($number);
		$tmpSize = strlen($tmpBin);
		for ($i = 0; $i < $tmpSize; ++$i)
			$levelList[$tmpSize - $i - 1] = ($tmpBin[$i] == '0') ? false : true;

		return $levelList;
	}
	/**
	* Converte uma lista de permissoes para um numero inteiro que a representa
	*
	* @param string $list
	* @return integer
	* @access public
	*/
	function _permissionListToNumber($list)
	{
		if (is_numeric($list))
			return (int) $list;

		$text = "";
		for ($i = 0; $i < $this->numberOfPermissions; ++$i)
			if (isset($list[$i]))
				$text = (($list[$i] == true) ? '1' : '0') . $text;
			else
				$text = "0" . $text;

		return bindec($text);
	}

	/**
	* Verifica se o numero passado representa todas as permissoes requeridas
	*
	* @param integer $number Número que representa uma lista de permissões
	* @param array $required Permissões requeridas
	* @return boolean
	* @access public
	*/
	function _checkLevelNumber($number, $required)
	{
		if (is_null($required))
			return true;
		if (!is_array($required))
			$required = array($required);

		$levelList = $this->_numberToPermissionList($number);

		/* check for the required permissions */
		foreach ($required as $req)
			if (!$levelList[$req])
				return false;

		return true;
	}

	/**
	* Retorna os recursos disponiveis ao Administrador
	*
	* @param string $type type of resource
	* @param integer $numvalue resource id
	* @return array
	* @access public
	*/
	function getResourceAdmins($type, $numvalue)
	{
		$query = "
			SELECT
				admin_access_id
			FROM
				egw_wf_admin_access
			 WHERE
				(tipo = ?) AND
				(numvalue = ?)";

		$result = $this->db->query($query, array($type, $numvalue));

		$output = array();
		while ($row = $result->fetchRow())
			$output[] = $row['admin_access_id'];

		return $output;
	}

	/**
	* Verifica se o usuario tem acesso a um tipo de recurso
	*
	* @param string $type type of resource
	* @param integer $uid user id
	* @param requiredLevel Level Required to Access.
	* @return bool
	* @access public
	*/
	function getUserPermissions($type, $uid, $requiredLevel = null)
	{
		$query = "
			SELECT
				numvalue,
				nivel
			FROM
				egw_wf_admin_access
			 WHERE
				(tipo = ?) AND
				(admin_access_id = ?)";

		$result = $this->db->query($query, array($type, $uid));

		$output = array();
		if ($result)
			while ($row = $result->fetchRow()) {
				$authorized = $this->_checkLevelNumber($row['nivel'], $requiredLevel);
				if (isset($requiredLevel)) {
					$authorized = $this->checkUserAccessToResource($type,$uid,$row['numvalue'],$requiredLevel);
					if ($authorized) {
						$output[] = $row['numvalue'];
					}
				} else {
					$output[] = $row['numvalue'];
				}
			}

		return $output;
	}
	/**
	 * Retorna as permissoes do grupo do usuário
	 *
	 * @param string $type
	 * @param integer $uid id do usuário
	 * @access public
	 * @return array
 	 */
	function getUserGroupPermissions($type, $uid, $requiredLevel = null)
	{
		$groups = galaxia_retrieve_user_groups($uid);
		if ($groups === false)
			$groups = array();

		$query = "
			SELECT
				DISTINCT(numvalue) AS numvalue
			FROM
				egw_wf_admin_access
			 WHERE
				(tipo = ?) AND
				(admin_access_id IN (" . implode(', ', array_merge(array($uid), $groups)) . "))";

		$result = $this->db->query($query, array($type));

		$output = array();
		if ($result)
			while ($row = $result->fetchRow()) {
				if (isset($requiredLevel)) {
					//$authorized = $this->_checkLevelNumber($row['nivel'], $requiredLevel);
					$authorized = $this->checkUserGroupAccessToResource($type,$uid,$row['numvalue'],$requiredLevel);
					if ($authorized) {
						$output[] = $row['numvalue'];
					}
				} else {
					$output[] = $row['numvalue'];
				}
			}

		return $output;
	}
	/**
	* Verifica se o usuario tem acesso a um tipo de recurso
	*
	* @param string $type type of resource
	* @param integer $uid user id
	* @return bool
	* @access public
	*/
	function checkUserAccessToType($type, $uid)
	{
		return (count($this->getUserPermissions($type, $uid,0)) > 0);
	}
	/**
	* Verifica se o grupo do usuario tem acesso a um tipo de recurso
	*
	* @param string $type type of resource
	* @param integer $uid user id
	* @return bool
	* @access public
	*/
	function checkUserGroupAccessToType($type, $uid)
	{
		return (count($this->getUserGroupPermissions($type, $uid)) > 0);
	}

	/**
	* Verifica se o usuário tem direito de acesso a um recurso
	*
	* @param string $type tipo de recurso
	* @param integer $uid id do usuário
	* @param integer $numvalue id do recurso
	* @param array  $requiredLevel permissoes requeridas
	* @return bool
	* @access public
	*/
	function checkUserAccessToResource($type, $uid, $numvalue, $requiredLevel = null)
	{
		$query = "
			SELECT
				nivel
			FROM
				egw_wf_admin_access
			 WHERE
				(tipo = ?) AND
				(admin_access_id = ?) AND
				(numvalue = ?)";

		$result = $this->db->query($query, array($type, $uid, $numvalue));
		
		$row = $result->fetchRow();

		if (isset($row['nivel'])) {
			$res = $this->_checkLevelNumber($row['nivel'], $requiredLevel);		
			return $res;
		}
		else
			return false;
	}
	/**
	* Verifica se o grupo do usuário tem direito de acesso a um recurso
	*
	* @param string $type tipo de recurso
	* @param integer $uid id do usuário
	* @param integer $numvalue id do recurso
	* @param array  $requiredLevel permissoes requeridas
	* @return bool
	* @access public
	*/
	function checkUserGroupAccessToResource($type, $uid, $numvalue, $requiredLevel = null)
	{
		$groups = galaxia_retrieve_user_groups($uid);
		if ($groups === false)
			$groups = array();

		$query = "
			SELECT
				nivel
			FROM
				egw_wf_admin_access
			 WHERE
				(tipo = ?) AND
				(admin_access_id IN (" . implode(', ', array_merge(array($uid), $groups)) . ")) AND
				(numvalue = ?)";

		$levels = array();
		$permissions = 0;
		$result = $this->db->query($query, array($type, $numvalue));
		while ($row = $result->fetchRow())
		{
			$levels[] = $row['nivel'];
			$permissions |= $row['nivel'];
		}

		if (empty($levels))
			return false;
		else
			return $this->_checkLevelNumber($permissions, $requiredLevel);
	}

	/**
	* Adiciona um administrador
	*
	* @param string  $type tipo de recurso
	* @param array   $uids
	* @param integer $numvalue id do recurso
	* @param array   $level nivel de permissao do usuario
	* @return void
	* @access public
	*/
	function addAdmin($type, $uids, $numvalue, $level = 0)
	{
		if (!is_array($uids))
			$uids = array($uids);
		if (is_array($level))
			$level = $this->_permissionListToNumber($level);
		foreach($uids as $uid)
		{
			$query = "
				INSERT INTO egw_wf_admin_access
					(admin_access_id,
					tipo,
					numvalue,
					nivel)
				VALUES (?, ?, ?, ?)";
			$this->db->query($query, array($uid, $type, $numvalue, $level));
		}
	}
	/**
	* Seta o nivel de administracao
	*
	* @param string  $type tipo de recurso
	* @param integer $uid id do usuário
	* @param integer $numvalue id do recurso
	* @param array   $level permissoes requeridas
	* @return void
	* @access public
	*/
	function setAdminLevel($type, $uid, $numvalue, $level)
	{
		if (is_array($level))
			$level = $this->_permissionListToNumber($level);

		$query = "UPDATE egw_wf_admin_access
			SET
				nivel = ?
			WHERE
				(admin_access_id = ?) AND
				(tipo = ?) AND
				(numvalue = ?)";
		$result = $this->db->query($query, array($level, $uid, $type, $numvalue));
		
		return $result;
	}

	/**
	* Informa o nível de administração do usuário
	* @param string $type O tipo do recurso
	* @param int $uid O ID do usuário
	* @param int $numvalue O ID do recurso
	* @return array As permissões do usuário (em forma de número e de bits)
	* @access public
	*/
	function getUserAdminLevel($type, $uid, $numvalue)
	{
		$query = "
			SELECT
				nivel
			FROM
				egw_wf_admin_access
			 WHERE
				(tipo = ?) AND
				(admin_access_id = ?) AND
				(numvalue = ?)";

		$result = $this->db->query($query, array($type, $uid, $numvalue));

		$row = $result->fetchRow();
		if (isset($row['nivel']))
		{
			$output['number'] = $row['nivel'];
			$output['bits'] = $this->_numberToPermissionList($row['nivel']);
			return $output;
		}
		else
			return false;
	}

	/**
	* Informa o nível de administração do usuário (incluindo herança por grupo)
	* @param string $type O tipo do recurso
	* @param int $uid O ID do usuário
	* @param int $numvalue O ID do recurso
	* @return array As permissões do usuário, inclusive herança por grupo. As permissões são retornadas em forma de número e de bits
	* @access public
	*/
	function getUserGroupAdminLevel($type, $uid, $numvalue)
	{
		/* prepare the group information */
		$groups = galaxia_retrieve_user_groups($uid);
		if ($groups === false)
			$groups = array();
		$groups[] = $uid;
        $groups = '{' . implode(', ', $groups) . '}';

		$query = "
			SELECT
				nivel
			FROM
				egw_wf_admin_access
			 WHERE
				(tipo = ?) AND
				(admin_access_id = ANY (?)) AND
				(numvalue = ?)";

		$result = $this->db->query($query, array($type, $groups, $numvalue));

		$levels = array();
		$permissions = 0;
		while ($row = $result->fetchRow())
		{
			$permissions |= $row['nivel'];
			$levels[] = $row['nivel'];
		}

		if (count($levels) > 0)
		{
			return array(
				'number' => $permissions,
				'bits' => $this->_numberToPermissionList($permissions));
		}
		else
			return false;
	}

	/**
	* Remove administrador(es)
	*
	* @param string  $type tipo de recurso
	* @param integer $uids id(s) do(s) usuário(s)
	* @param integer $numvalue id do recurso
	* @return void
	* @access public
	*/
	function removeAdmin($type, $uids, $numvalue)
	{
		if (!is_array($uids))
			$uids = array($uids);
		foreach($uids as $uid)
		{
			$query = "
				DELETE FROM
					egw_wf_admin_access
				WHERE
					(tipo = ?) AND
					(numvalue = ?) AND
					(admin_access_id = ?)";

			$result = $this->db->query($query, array($type, $numvalue, $uid));
		}
	}
	/**
	* Remove todos os administradores de um recurso
	*
	* @param string  $type tipo de recurso
	* @param integer $numvalue id do recurso
	* @return void
	* @access public
	*/
	function removeAdminsFromResource($type, $numvalue)
	{
		$query = "
			DELETE FROM
				egw_wf_admin_access
			WHERE
				(tipo = ?) AND
				(numvalue = ?)";

		$result = $this->db->query($query, array($type, $numvalue));
	}
}

?>
