<?php
/**************************************************************************\
* eGroupWare                                                 			   *
* http://www.egroupware.org                                                *
* --------------------------------------------                             *
*  This program is free software; you can redistribute it and/or modify it *
*  under the terms of the GNU General Public License as published by the   *
*  Free Software Foundation; either version 2 of the License, or (at your  *
*  option) any later version.                                              *
\**************************************************************************/

require_once('class.bo_ajaxinterface.inc.php');


/**
 * DO NOT remove this line. Ever. Somehow, monitors interface will stop working..
 */
require_once(PHPGW_API_INC . SEP . 'common_functions.inc.php');


/**
 * @package Workflow
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @author Sidnei Augusto Drovetto Jr. - drovetto@gmail.com
 */
class bo_monitors extends bo_ajaxinterface
{
	/* permissions index */

	/**
	 * @var integer $IP_CHANGE_PRIORITY
	 * @access public
	 */
	var $IP_CHANGE_PRIORITY = 0;
	/**
	 * @var integer $IP_CHANGE_USER
	 * @access public
	 */
	var $IP_CHANGE_USER = 1;
	/**
	 * @var integer $IP_CHANGE_STATUS
	 * @access public
	 */
	var $IP_CHANGE_STATUS = 2;
	/**
	 * @var integer $IP_CHANGE_NAME
	 * @access public
	 */
	var $IP_CHANGE_NAME = 3;
	/**
	 * @var integer $IP_CHANGE_ACTIVITY
	 * @access public
	 */
	var $IP_CHANGE_ACTIVITY = 4;
	/**
	 * @var integer $IP_VIEW_PROPERTIES
	 * @access public
	 */
	var $IP_VIEW_PROPERTIES = 5;
	/**
	 * @var integer $IP_CHANGE_PROPERTIES
	 * @access public
	 */
	var $IP_CHANGE_PROPERTIES = 6;
	/**
	 * @var integer $IP_VIEW_STATISTICS
	 * @access public
	 */
	var $IP_VIEW_STATISTICS = 7;
	/**
	 * @var integer $IP_REMOVE_COMPLETED_INSTANCES
	 * @access public
	 */
	var $IP_REMOVE_COMPLETED_INSTANCES = 8;
	/**
	 * @var integer $IP_REPLACE_USER
	 * @access public
	 */
	var $IP_REPLACE_USER = 9;
	/**
	 * @var integer $IP_SEND_EMAILS
	 * @access public
	 */
	var $IP_SEND_EMAILS = 10;
	/**
	 * @var object $processManager
	 * @access public
	 */
	var $processManager;
	/**
	 * @var object $activityManager
	 * @access public
	 */
	var $activityManager;
	/**
	 * @var object $instanceManager
	 * @access public
	 */
	var $instanceManager;
	/**
	 * @var object $roleManager
	 * @access public
	 */
	var $roleManager;
	/**
	 * @var integer $userID
	 * @access public
	 */
	var $userID;

	/**
	 * @var bool $isWorkflowAdmin
	 * @access public
	 */
	var $isWorkflowAdmin;

	/**
	 * @var object $processMonitor
	 * @access public
	 */
	var $processMonitor;

	/**
	 * Construtor da classe bo_monitors
	 *
	 * @access public
	 * @return object
	 */
	function bo_monitors()
	{
		parent::bo_ajaxinterface();
		$GLOBALS['ajax']->gui   = &Factory::newInstance('GUI');
		$this->userID 			= $_SESSION['phpgw_info']['workflow']['account_id'];
		$this->isWorkflowAdmin  = $GLOBALS['ajax']->acl->checkWorkflowAdmin($this->userID);
		$this->processManager   = &Factory::newInstance('ProcessManager');
		$this->activityManager  = &Factory::newInstance('ActivityManager');
		$this->instanceManager  = &Factory::newInstance('InstanceManager');
		$this->roleManager 		= &Factory::newInstance('RoleManager');
		$this->processMonitor   = &Factory::newInstance('ProcessMonitor');
	}

	/**
	 * Verifica se o usuário logado possui acesso ao Monitoramento
	 *
	 * @param int $processID O ID do processo que se está tentando monitorar
	 * @param array $requiredLevel Permissões necessárias
	 * @return bool true se o usuário tiver permissão ou false caso contrário
	 * @access private
	 */
	private function checkAccess($processID, $requiredLevel = null)
	{
		if ($this->isWorkflowAdmin)
			return true;

		return $GLOBALS['ajax']->acl->checkUserGroupAccessToResource('MON', $this->userID, (int) $processID, $requiredLevel);
	}

	/**
	 * Converte os filtros utilizados na interface de monitoramento para código SQL
	 *
	 * @param string $filters Uma string contendo dados, serializados, sobre os filtros selecionados
	 * @return array Uma array onde cada elemento corresponde a um condicional de um filtro
	 * @access private
	 */
	private function convertFiltersToSQL($filters)
	{

		/* desserializa os dados */
		$JSON = &Factory::newInstance('Services_JSON');
		/* desserializa a array principal, depois desserializa cada elemento desta array e, por fim, converte os elementos (que estão em forma de objeto) para array associativa */
		$filters = array_map('get_object_vars', array_map(array($JSON, 'decode'), $JSON->decode($filters)));
		$sqlFilters = array();

		/* gera o SQL de acordo com o filtro selecionado */
		foreach ($filters as $filter)
		{
			/* verifica se existe o ID do filtro */
			if (!isset($filter['id']))
				continue;

			switch ($filter['id'])
			{
				case 'activityDate':
					$sqlFilters[] = '(DATE_TRUNC(\'DAY\', \'EPOCH\'::TIMESTAMP + (gia.wf_started || \' SECONDS\')::INTERVAL) ' . (($filter['operator'] == 'EQ') ? '=' : (($filter['operator'] == 'LT') ? '<' : '>')) . ' TO_DATE(\'' . addslashes($filter['date']) . '\', \'DD/MM/YYYY\'))';
					break;

				case 'instanceName':
					$sqlFilters[] = '(gi.wf_name ILIKE \'%' . addslashes($filter['name']) . '%\')';
					break;

				case 'instanceID':
					$sqlFilters[] = '(gi.wf_instance_id ' . (($filter['operator'] == 'EQ') ? '=' : (($filter['operator'] == 'LT') ? '<' : '>')) . ' ' . addslashes($filter['number']) . ')';
					break;

				case 'instancePriority':
					$sqlFilters[] = '(gi.wf_priority ' . (($filter['operator'] == 'EQ') ? '=' : (($filter['operator'] == 'LT') ? '<' : '>')) . ' ' . addslashes($filter['priority']) . ')';
					break;

				case 'instanceDate':
					$sqlFilters[] = '(DATE_TRUNC(\'DAY\', \'EPOCH\'::TIMESTAMP + (gi.wf_started || \' SECONDS\')::INTERVAL) ' . (($filter['operator'] == 'EQ') ? '=' : (($filter['operator'] == 'LT') ? '<' : '>')) . ' TO_DATE(\'' . addslashes($filter['date']) . '\', \'DD/MM/YYYY\'))';
					break;

				case 'instanceEndDate':
					$sqlFilters[] = '(DATE_TRUNC(\'DAY\', \'EPOCH\'::TIMESTAMP + (gi.wf_ended || \' SECONDS\')::INTERVAL) ' . (($filter['operator'] == 'EQ') ? '=' : (($filter['operator'] == 'LT') ? '<' : '>')) . ' TO_DATE(\'' . addslashes($filter['date']) . '\', \'DD/MM/YYYY\'))';
					break;

				case 'instanceStatus':
					$sqlFilters[] = '(gi.wf_status = \'' . addslashes($filter['status']) . '\')';
					break;

				case 'instanceActivity':
					$sqlFilters[] = '(gia.wf_activity_id = \'' . addslashes($filter['activity']) . '\')';
					break;

				case 'instanceUser':
					$sqlFilters[] = '(gia.wf_user = \'' . addslashes($filter['user']) . '\')';
					break;

				case 'instanceOwner':
					$sqlFilters[] = '(gi.wf_owner = \'' . addslashes($filter['owner']) . '\')';
					break;
			}
		}

		return $sqlFilters;
	}

	/**
	 * Lista os processos do usuario
	 *
	 * @access public
	 * @return array lista de processos
	 */
	function listProcesses()
	{
		if ($this->isWorkflowAdmin)
		{
			$permissions = $this->processManager->list_processes(0, -1, 'wf_p_id__ASC');
			$permissions = array_map(create_function('$a', 'return $a[\'wf_p_id\'];'), $permissions['data']);
		}
		else
		{
			$permissions = $GLOBALS['ajax']->acl->getUserGroupPermissions('MON', $this->userID);
		}
		$processes = array();
		foreach ($permissions as $pid)
		{
			$process = $this->processManager->get_process($pid);
			if ($process)
			{
				$processes['data'][] = $process;
				if ($this->isWorkflowAdmin)
					$processes['permissions'][$pid] = array(
						'number' => 4294967295,
						'bits' => array_fill(0, 32, true)
					);
				else
					$processes['permissions'][$pid] = $GLOBALS['ajax']->acl->getUserGroupAdminLevel('MON', $this->userID, $pid);
			}
		}

		usort($processes['data'], create_function('$a,$b', 'return strnatcasecmp($a[\'wf_name\'],$b[\'wf_name\']);'));
		return $processes;
	}
	/**
	 * Lista as instancias ativas de um processo
	 *
	 * @param array $params Parâmetros advindos da chamada Ajax
	 * @param bool $completeData Informa se devem ser retornados todos os dados ou somente aqueles necessários para a construção da listagem de instâncias ativas da interface de monitoramento. false por padrão
	 * @access public
	 * @return array lista de instancias
	 */
	function listInstances($params, $completeData = false)
	{
		$output = array();

		/* check if the user has the proper right to list the instances of this process */
		if (!$this->checkAccess($params['pid']))
			return "Você não tem permissão para executar este procedimento!";

		/* check for filters */
		if ($params['filters'])
			$filters = $this->convertFiltersToSQL($params['filters']);
		else
			$filters = array();

		if(empty($params['srt']))
			$params['srt'] = 2;
		if(empty($params['ord']))
		{
			$params['ord'] = "__ASC";
		}
		else
		{

			// being sure that the prefix "__DESC" or "__ASC" exists, preventing SQL injections
			$isOrdOK = false;
			if($params['ord'] == "__DESC") $isOrdOK = true;
			if($params['ord'] == "__ASC") $isOrdOK = true;

			if(!$isOrdOK) $params['ord'] = "__ASC";
		}

		switch($params['srt'])
		{
			case 1:
				$order = "wf_instance_id".$params['ord'];
				break;
			case 2:
				$order = "wf_activity_name".$params['ord'];
				break;
			case 3:
				$order = "wf_instance_name".$params['ord'];
				break;
			case 4:
				$order = "wf_priority".$params['ord'];
				break;
			case 5:
				$order = "wf_user".$params['ord'];
				break;
			case 6:
				$order = "wf_status".$params['ord'];
				break;
			default:
				$order = "wf_activity_name".$params['ord'];
				break;
		}

		$filters[] = '(gp.wf_p_id = ' . $params['pid'] . ')';
	   	$filters[] = '(gia.wf_user IS NOT NULL)';

		$ldap = &Factory::getInstance('WorkflowLDAP');
		if (!$completeData)
		{
			$paging = Factory::newInstance('Paging', 500, $_POST);
			$tmp = $this->processMonitor->monitor_list_instances($paging->nextItem, $paging->itemsPerPage, $order, '', implode(' AND ', $filters));
			$output['data'] = $paging->restrictItems(array_values($tmp['data']), $tmp['cant']);
			$output['instanceCount'] = $tmp['cant'];
			$output['pagingData'] = $paging->commonLinks();
		}
		else
		{
			$tmp = $this->processMonitor->monitor_list_instances(0, -1, $order, '', implode(' AND ', $filters));
			$output['data'] = array_values($tmp['data']);
		}

		$userMapping = array('*' => '*');
		$activityMapping = array();
		$instanceCount = count($output['data']);
		$cachedLDAP = &Factory::getInstance('CachedLDAP');
		for ($i = 0; $i < $instanceCount; ++$i)
		{
			/* get the user name */
			$currentInstanceUser = $output['data'][$i]['wf_user'];
			if (!isset($userMapping[$currentInstanceUser]))
			{
				if (substr($currentInstanceUser, 0, 1) == 'p')
				{
					$role = $this->roleManager->get_role($output['data'][$i]['wf_p_id'], substr($currentInstanceUser, 1));
					$userMapping[$currentInstanceUser] = 'Perfil: ' . $role['wf_name'];
				}
				else
				{
					$name = $ldap->getName($currentInstanceUser);
					if (empty($name))
					{
						$userInfo = $cachedLDAP->getEntryByID($currentInstanceUser);
						if ($userInfo)
							$name = "ID: {$currentInstanceUser} - {$userInfo['cn']} (excluído)";
						else
							$name = "ID: {$currentInstanceUser}";
					}

					$userMapping[$currentInstanceUser] = $name;
				}
			}

			if ($completeData)
			{
				$currentInstanceOwner = $output['data'][$i]['wf_owner'];
				if (!isset($userMapping[$currentInstanceOwner]))
					$userMapping[$currentInstanceOwner] = $ldap->getName($currentInstanceOwner);
			}

			/* get the activity names */
			if (!isset($activityMapping[$output['data'][$i]['wf_activity_id']]))
				$activityMapping[$output['data'][$i]['wf_activity_id']] = $output['data'][$i]['wf_activity_name'];

			/* remove unused elements */
			if (!$completeData)
				unset(
					$output['data'][$i]['wf_is_interactive'],
					$output['data'][$i]['wf_proc_normalized_name'],
					$output['data'][$i]['wf_owner'],
					$output['data'][$i]['wf_procname'],
					$output['data'][$i]['wf_version'],
					$output['data'][$i]['wf_activity_name'],
					$output['data'][$i]['wf_type'],
					$output['data'][$i]['wf_started'],
					$output['data'][$i]['wf_ended'],
					$output['data'][$i]['wf_act_status'],
					$output['data'][$i]['wf_act_started']
				);
		}
		$output['userMapping'] = $userMapping;
		$output['activityMapping'] = $activityMapping;

		$output['params'] = $params;
		return $output;
	}

	/**
	 * Lista as instancias finalizadas de um processo
	 *
	 * @param array $params Parâmetros advindos da chamada Ajax
	 * @param bool $completeData Informa se devem ser retornados todos os dados ou somente aqueles necessários para a construção da listagem de instâncias finalizadas da interface de monitoramento. false por padrão
	 * @access public
	 * @return array lista de instancias
	 */
	function listCompletedInstances($params, $completeData = false)
	{
		$params['pid'] = (int) $params['pid'];
		$output = array();

		/* check if the user has the proper right to list the instances of this process */
		if (!$this->checkAccess($params['pid']))
			return "Você não tem permissão para executar este procedimento!";

		/* check for filters */
		if ($params['filters'])
			$filters = $this->convertFiltersToSQL($params['filters']);
		else
			$filters = array();

		if(empty($params['sort']))
			$params['sort'] = 1;

		if(empty($params['ord']) || ($params['ord'] != '__DESC'))
			$params['ord'] = '__ASC';

		/* define the sorting criteria */
		switch($params['sort'])
		{
			case 1:
				$order = 'wf_instance_id';
				break;
			case 2:
				$order = 'wf_instance_name';
				break;
			case 3:
				$order = 'wf_owner';
				break;
			case 4:
				$order = 'wf_priority';
				break;
			case 5:
				$order = 'wf_started';
				break;
			case 6:
				$order = 'wf_ended';
				break;
			case 7:
				$order = 'wf_status';
				break;
			default:
				$order = 'wf_instance_id';
				break;
		}
		$order .= $params['ord'];

		/* load the data */
		$filters[] = '(gp.wf_p_id = ' . $params['pid'] . ')';
		$ldap = &Factory::getInstance('WorkflowLDAP');
		if (!$completeData)
		{
			$paging = Factory::newInstance('Paging', 500, $_POST);
			$tmp = $this->processMonitor->monitor_list_completed_instances($paging->nextItem, $paging->itemsPerPage, $order, '', implode(' AND ', $filters));
			$output['data'] = $paging->restrictItems(array_values($tmp['data']), $tmp['cant']);
			$output['instanceCount'] = $tmp['cant'];
			$output['pagingData'] = $paging->commonLinks();
		}
		else
		{
			$tmp = $this->processMonitor->monitor_list_completed_instances(0, -1, $order, '', implode(' AND ', $filters));
			$output['data'] = array_values($tmp['data']);
		}

		$cachedLDAP = &Factory::getInstance('CachedLDAP');
		$userMapping = array();
		$instanceCount = count($output['data']);
		for ($i = 0; $i < $instanceCount; ++$i)
		{
			/* get the user name */
			$currentInstanceOwner = $output['data'][$i]['wf_owner'];
			{
				$name = $ldap->getName($currentInstanceOwner);
				if (empty($name))
				{
					$userInfo = $cachedLDAP->getEntryByID($currentInstanceOwner);
					if ($userInfo)
						$name = "ID: {$currentInstanceOwner} - {$userInfo['cn']} (excluído)";
					else
						$name = "ID: {$currentInstanceOwner}";
				}

				$userMapping[$currentInstanceOwner] = $name;
			}

			/* format the data */
			$output['data'][$i]['wf_started'] = date('d/m/Y H:i', $output['data'][$i]['wf_started']);
			$output['data'][$i]['wf_ended'] = date('d/m/Y H:i', $output['data'][$i]['wf_ended']);

			/* remove unused elements */
			if (!$completeData)
				unset(
					$output['data'][$i]['wf_proc_normalized_name'],
					$output['data'][$i]['wf_procname'],
					$output['data'][$i]['wf_version']
				);
		}
		$output['userMapping'] = $userMapping;

		$output['params'] = $params;
		return $output;
	}

	/**
	 * Lista as atividades da instancia
	 * @param array $params Parâmetros advindos da chamada Ajax
	 * @access public
	 * @return array
	 */
	function listActivities($params)
	{
		$pid = (int) $params['pid'];
		/* check if the user has the proper right to list the activities of this process */
		if (!$this->checkAccess($pid))
			return "Você não tem permissão para executar este procedimento!";

		/* retrieve all the activities */
		$activities = $this->activityManager->list_activities($pid, 0, -1, 'wf_name__asc', '', '');

		/* format the output */
		$output['params'] = $params;
		$output['data'] = array();
		foreach ($activities['data'] as $activity)
			if (($activity['wf_type'] != 'view') && ($activity['wf_type'] != 'standalone') && ($activity['wf_type'] != 'start') && ($activity['wf_is_interactive'] == 'y'))
				$output['data'][] = array('id' => $activity['wf_activity_id'], 'name' => $activity['wf_name']);

		return $output;
	}
	/**
	 * Lista os usuarios da instancia
	 * @param array $params Parâmetros advindos da chamada Ajax
	 * @access public
	 * @return array lista de usuarios
	 */
	function listUsers($params)
	{
		/* check if the user has the right to view all users from this instance */
		$instance = $this->instanceManager->get_instance($params['iid']);
		if ((!$this->checkAccess($params['pid'])) || ($instance['wf_p_id'] != $params['pid']))
			return 'Você não tem permissão para executar este procedimento!';

		/* retrieve all the users from the activity */
		$users = $this->roleManager->list_mapped_users($params['pid'], true, array('wf_activity_id' => $params['aid']));
		asort($users);

		$roles = $this->activityManager->get_activity_roles($params['aid']);
		usort($roles, create_function('$a,$b', 'return strcasecmp($a[\'wf_name\'],$b[\'wf_name\']);'));
		$usersOutput['data'] = array();
		$usersOutput['data'][] = array('id' => '-1', 'name' => 'TODOS');
		foreach ($roles as $role)
			$usersOutput['data'][] = array('id' => 'p' . $role['wf_role_id'], 'name' => 'Perfil: ' . $role['wf_name']);

		/* format the output */
		$usersOutput['params'] = $params;
		foreach ($users as $id => $name)
			$usersOutput['data'][] = array('id' => $id, 'name' => $name);

		return $usersOutput;
	}
	/**
	 * Realiza o Update da prioridade de uma instancia
	 * @param array $params Parâmetros advindos da chamada Ajax
	 * @access public
	 * @return array parametros?
	 */
	function updatePriority($params)
	{
		/* check if the user has the right to update the priority of the instance */
		$instance = $this->instanceManager->get_instance($params['iid']);
		if ((!$this->checkAccess($params['pid'], array($this->IP_CHANGE_PRIORITY))) || ($instance['wf_p_id'] != $params['pid']))
			return "Você não tem permissão para executar este procedimento!";

		$this->instanceManager->set_instance_priority($params['iid'], $params['np']);
		return $params;
	}

	/**
	 * Realiza o update do usuario atual da instancia
	 * @param array $params Parâmetros advindos da chamada Ajax
	 * @return array
	 * @access public
	 */
	function updateUser($params)
	{
		/* check if the user has the right to update the current user of this instance */
		$instance = $this->instanceManager->get_instance($params['iid']);
		if ((!$this->checkAccess($params['pid'], array($this->IP_CHANGE_USER))) || ($instance['wf_p_id'] != $params['pid']))
			return "Você não tem permissão para executar este procedimento!";

		$output = $params;
		$user = ($params['user'] == -1) ? '*' : $params['user'];
		if ($user == '*')
			$output['fullname'] = '*';
		else
			if (substr($user, 0, 1) == 'p')
			{
				$role = $this->roleManager->get_role($instance['wf_p_id'], substr($user, 1));
				$output['fullname'] = 'Perfil: ' . $role['wf_name'];
			}
			else
				$output['fullname'] = Factory::getInstance('WorkflowLDAP')->getName($user);

		$this->instanceManager->set_instance_user($params['iid'], $params['aid'], $user);

		return $output;
	}
	/**
	 * Realiza o update do status da instancia
	 * @param array $params Parâmetros advindos da chamada Ajax
	 * @return array
	 * @access public
	 */
	function updateStatus($params)
	{
		/* check if the user has the right to update the status of the instance */
		$instance = $this->instanceManager->get_instance($params['iid']);
		if ((!$this->checkAccess($params['pid'], array($this->IP_CHANGE_STATUS))) || ($instance['wf_p_id'] != $params['pid']))
			return "Você não tem permissão para executar este procedimento!";

		if ($params['ns'] == 'aborted')
		{
			$realInstance = &Factory::newInstance('Instance');
			$realInstance->getInstance($params['iid']);
			if (!empty($realInstance->instanceId))
			{
				if (!$realInstance->abort())
				{
					unset($realInstance);
					return 'Ocorreu um erro ao abortar a instância selecionada';
				}
			}
			else
			{
				unset($realInstance);
				return 'Não foi possível abortar a instância selecionada';
			}
		}
		else
			$this->instanceManager->set_instance_status($params['iid'], $params['ns']);

		return $params;
	}

	/**
	 * Realiza o update de uma atividade da instancia
	 * @param array $params Parâmetros advindos da chamada Ajax
	 * @return array
	 * @access public
	 */
	function updateActivity($params)
	{
		/* check if the user has the right to update change the activity of the instance */
		$instance = $this->instanceManager->get_instance($params['iid']);
		if ((!$this->checkAccess($params['pid'], array($this->IP_CHANGE_ACTIVITY))) || ($instance['wf_p_id'] != $params['pid']))
			return "Você não tem permissão para executar este procedimento!";

		/* use next user or * for the new instance */
		$realInstance = &Factory::newInstance('Instance');
		$realInstance->getInstance($params['iid'], false, false);
		$user = $realInstance->getNextUser($params['aid']);
		$user = ($user == '') ? '*' : $user;
		$this->instanceManager->set_instance_destination($params['iid'], $params['aid'], $user);
		$result = $this->activityManager->get_activity($params['aid']);
		$output = $params;
		$output['name'] = $result['wf_name'];
		return $output;
	}

	/**
	 * Realiza o update do nome da instancia
	 * @param array $params Parâmetros advindos da chamada Ajax
	 * @return array
	 * @access public
	 */
	function updateName($params)
	{
		/* check if the user has the right to update the status of the instance */
		$instance = $this->instanceManager->get_instance($params['iid']);
		if ((!$this->checkAccess($params['pid'], array($this->IP_CHANGE_NAME))) || ($instance['wf_p_id'] != $params['pid']))
			return "Você não tem permissão para executar este procedimento!";

		$this->instanceManager->set_instance_name($params['iid'], $params['nn']);
		return $params;
	}

	/**
	 * Remove instâncias finalizadas
	 * @param array $params Parâmetros advindos da chamada Ajax
	 * @return int O número de instâncias removidas
	 * @access public
	 */
	function removeCompletedInstances($params)
	{
		/* check if the user has the proper right to remove the completed instances of this process */
		if (!$this->checkAccess($params['pid'], array($this->IP_REMOVE_COMPLETED_INSTANCES)))
			return array('error' => 'Você não tem permissão para executar este procedimento!');

		/* load the instances */
		$rawData = $this->listCompletedInstances($params, true);

		/* remove the instances */
		$instanceCount = 0;
		if (!empty($rawData['data']))
			foreach ($rawData['data'] as $instance)
				if ($this->processMonitor->remove_instance($instance['wf_instance_id']))
					++$instanceCount;
		return $instanceCount;
	}


	/**
	 * Lista as propriedades de uma instancia
	 * @param array $params Parâmetros advindos da chamada Ajax
	 * @return array
	 * @access public
	 */
	function listInstanceProperties($params)
	{
		/* check if the user has the right to view the  instance properties */
		$instanceInfo = $this->instanceManager->get_instance($params['iid']);
		if (((!$this->checkAccess($params['pid'], array($this->IP_VIEW_PROPERTIES))) && (!$this->checkAccess($params['pid'], array($this->IP_CHANGE_PROPERTIES)))) || ($instanceInfo['wf_p_id'] != $params['pid']))
			return "Você não tem permissão para executar este procedimento!";

		$maximumDisplaySize = 100;
		$instance = &Factory::newInstance('Instance');
		$instance->getInstance($params['iid']);

		$output = array();
		$output['params'] = $params;
		foreach ($instance->properties as $name => $value)
		{
			$complete = 1;
			if (strlen($value) > $maximumDisplaySize)
			{
				$value = substr($value, 0, $maximumDisplaySize-3) . "...";
				$complete = 0;
			}
			$output[] = array ("name" => $name, "value" => htmlentities($value), "complete" => $complete);
		}
		return $output;
	}

	/**
	 * Retorna o valor de uma propriedade
	 *
	 * @param array $params Parâmetros advindos da chamada Ajax
	 * @return array
	 * @access public
	 */
	function getCompletePropertyValue($params)
	{
		/* check if the user has the right to edit the instance properties */
		$instanceInfo = $this->instanceManager->get_instance($params['iid']);
		if ((!$this->checkAccess($params['pid'], array($this->IP_CHANGE_PROPERTIES))) || ($instanceInfo['wf_p_id'] != $params['pid']))
			return "Você não tem permissão para executar este procedimento!";

		$instance = &Factory::newInstance('Instance');
		$instance->getInstance($params['iid']);
		$output = $params;
		$output['value'] = $instance->properties[$params['name']];
		return $output;
	}

	/**
	 * Realiza o update de uma propriedade de uma instancia
	 * @param array $params Parâmetros advindos da chamada Ajax
	 * @access public
	 */
	function updateProperty($params)
	{
		/* check if the user has the right to edit the instance properties */
		$instanceInfo = $this->instanceManager->get_instance($params['iid']);
		if ((!$this->checkAccess($params['pid'], array($this->IP_CHANGE_PROPERTIES))) || ($instanceInfo['wf_p_id'] != $params['pid']))
			return "Você não tem permissão para executar este procedimento!";

		$maximumDisplaySize = 100;

		$instance = &Factory::newInstance('Instance');
		$instance->getInstance($params['iid']);
		$instance->set($params['name'], $params['value']);
		$instance->sync();

		$output = array();
		if (strlen($params['value']) > $maximumDisplaySize)
		{
			$params['value'] = substr($params['value'], 0, $maximumDisplaySize-3) . "...";
			$output['complete'] = 0;
		}
		else
		$output['complete'] = 1;
		$output['value'] = htmlentities($params['value']);

		return $output;
	}

	/**
	 * Remove uma propriedade da instancia
	 * @param array $params Parâmetros advindos da chamada Ajax
	 * @return array
	 * @access public
	 */
	function removeProperty($params)
	{
		/* check if the user has the right to remove a property of the instance */
		$instanceInfo = $this->instanceManager->get_instance($params['iid']);
		if ((!$this->checkAccess($params['pid'], array($this->IP_CHANGE_PROPERTIES))) || ($instanceInfo['wf_p_id'] != $params['pid']))
			return "Você não tem permissão para executar este procedimento!";

		$instance = &Factory::newInstance('Instance');
		$instance->getInstance($params['iid']);
		$instance->clear($params['name']);
		$instance->sync();

		$output = $params;
		return $output;
	}

	/**
	 * Mostra as estatisticas
	 * @param array $params Parâmetros advindos da chamada Ajax
	 * @return array
	 * @access public
	 */
	function showStatistics($params)
	{
		if (!$this->checkAccess($params['pid'], array($this->IP_VIEW_STATISTICS)))
			return "Você não tem permissão para executar este procedimento!";

		/* common configuration */
		$output = array();
		$urlPrefix = 'workflow/inc/class.powergraphic.inc.php?';
		$powergraphic = &Factory::getInstance('powergraphic');
		$powergraphic->graphic_1 = $params['pid'];
		$powergraphic->skin = 1;
		$powergraphic->credits = 0;

		/* number of instances per month */
		$powergraphic->type = 4;
		$powergraphic->title = 'Quantidade de Instancias x Mes';
		$powergraphic->axis_x = 'Mes';
		$powergraphic->axis_y = 'No. de Instancias';
		$aux = $this->processMonitor->stats_instances_per_month($params['pid']);
		$index = 0;
		foreach ($aux as $date => $count)
		{
			list($year, $month) = explode('-', $date);
			$powergraphic->x[$index] = $month . '/' . substr($year, -2);
			$powergraphic->y[$index++] = $count;
		}
		if (count($aux) > 0)
			$output[] = $powergraphic->create_query_string();

		/* number of instances per activity */
		$powergraphic->x = $powergraphic->y = array();
		$powergraphic->type = 5;
		$powergraphic->title = 'Instancias x Atividade';
		$powergraphic->axis_x = 'Atividade';
		$powergraphic->axis_y = 'No. de Instancias';
		$aux = $this->processMonitor->stats_instances_activities($params['pid']);
		$index = 0;
		foreach ($aux as $info)
		{
			$powergraphic->x[$index] = $info['wf_name'] . " ({$info['count']})";
			$powergraphic->y[$index++] = $info['count'];
		}
		if (count($aux) > 0)
			$output[] = $powergraphic->create_query_string();

		/* number of instances per user */
		$powergraphic->x = $powergraphic->y = array();
		$powergraphic->type = 5;
		$powergraphic->title = 'Instancias x Usuario';
		$powergraphic->axis_x = 'Usuario';
		$powergraphic->axis_y = 'No. de Instancias';
		$aux = $this->processMonitor->stats_instances_per_user($params['pid']);
		/* prepare the data */
		$aux2 = array();
		$ldap = &Factory::getInstance('WorkflowLDAP');
		foreach ($aux as $user => $count)
			$aux2[] = array(
				'user' => $ldap->getName($user) . " ({$count})",
				'count' => $count
			);
		usort($aux2, create_function('$a,$b', 'return strcasecmp($a[\'user\'],$b[\'user\']);'));
		$index = 0;
		foreach ($aux2 as $info)
		{
			$powergraphic->x[$index] = $info['user'];
			$powergraphic->y[$index++] = $info['count'];
		}
		if (count($aux) > 0)
			$output[] = $powergraphic->create_query_string();

		/* number of instances per status */
		$translateStatus = array(
			'completed' => 'Completada',
			'active' => 'Ativa',
			'aborted' => 'Abortada',
			'exception' => 'Em exceção');
		$powergraphic->x = $powergraphic->y = array();
		$powergraphic->type = 5;
		$powergraphic->title = 'Instancias x Status';
		$powergraphic->axis_x = 'Status';
		$powergraphic->axis_y = 'No. de Instancias';
		$aux = $this->processMonitor->stats_instances_per_status($params['pid']);
		$index = 0;
		foreach ($aux as $status => $count)
		{
			$powergraphic->x[$index] = $translateStatus[$status] . " ({$count})";
			$powergraphic->y[$index++] = $count;
		}
		if (count($aux) > 0)
			$output[] = $powergraphic->create_query_string();

		foreach ($output as $key => $value)
			$output[$key] = $urlPrefix . $value;
		return $output;
	}

	/**
	 * Pega os usuários de instâncias ativas de um determinado processo
	 * @param array $params Parâmetros advindos da chamada Ajax
	 * @return array Contendo o nome/id dos usuários
	 * @access public
	 */
	function getUsersInInstances($params)
	{
		$pid = (int) $params['pid'];
		/* check if the user has the proper right to list the users of this process */
		if (!$this->checkAccess($pid))
			return "Você não tem permissão para executar este procedimento!";

		/* initialize some variables */
		$users = array();
		$roles = array();
		$specialUsers = array();

		/* load the users */
		$activities = $this->activityManager->get_process_activities($pid);
		if (!empty($activities))
			$users = $this->processMonitor->monitor_list_users('wf_activity_id IN (' . implode(',', array_map(create_function('$a', 'return $a[\'wf_activity_id\'];'), $activities)) . ')');

		/* remove the '*' user */
		if (($asteriskPosition = array_search('*', $users)) !== false)
			unset($users[$asteriskPosition]);

		/* separate roles from users */
		foreach ($users as $key => $user)
		{
			if (substr($user, 0, 1) == 'p')
			{
				$roles[] = $user;
				unset($users[$key]);
			}
		}

		/* load LDAP info and sort the result */
		$foundUsers = Factory::getInstance('WorkflowLDAP')->getNames($users);
		usort($foundUsers, create_function('$a,$b', 'return strcasecmp($a[\'name\'],$b[\'name\']);'));


		/* special treatment for  users not found in LDAP */
		if (count($users) > count($foundUsers))
		{
			$cachedLDAP = &Factory::getInstance('CachedLDAP');
			$foundUsersID = array_map(create_function('$a', 'return $a[\'id\'];'), $foundUsers);
			$missingUsers = array_diff($users, $foundUsersID);
			foreach ($missingUsers as $missingUser)
			{
				$userInfo = $cachedLDAP->getEntryByID($missingUser);
				if ($userInfo)
					$name = "ID: {$missingUser} - {$userInfo['cn']} (excluído)";
				else
					$name = "ID: {$missingUser}";
				$specialUsers[] = array('id' => $missingUser, 'name' => $name);
			}
		}

		/* load roles info */
		foreach ($roles as $role)
		{
			$roleInfo = $this->roleManager->get_role($pid, substr($role, 1));
			$specialUsers[] = array('id' => $role, 'name' => 'Perfil: ' . $roleInfo['wf_name']);
		}

		/* sort the special users */
		usort($specialUsers, create_function('$a,$b', 'return strnatcasecmp($a[\'name\'],$b[\'name\']);'));

		/* define the output (merging the ordinary users with the "special" ones) */
		$output = $params;
		$output['users'] = array_merge($specialUsers, $foundUsers);

		return $output;
	}

	/**
	 * Pega os proprietários de instâncias ativas ou inativas de um determinado processo
	 * @param array $params Parâmetros advindos da chamada Ajax
	 * @return array Contendo o nome/id dos proprietários
	 * @access public
	 */
	function getInstancesOwners($params)
	{
		$pid = (int) $params['pid'];

		/* check if the user has the proper right to list the owners of this process */
		if (!$this->checkAccess($pid))
			return 'Você não tem permissão para executar este procedimento!';

		$output = $params;
		$output['owners'] = array();

		/* load the instances (active ou completed ones) */
		if ($params['currentList'] == 'active')
			$instances = $this->listInstances($params, true);
		else
			$instances = $this->listCompletedInstances($params, true);

		/* get the owners */
		if (count($instances['userMapping']) > 0)
		{
			if ($params['currentList'] == 'completed')
			{
				foreach ($instances['userMapping'] as $id => $name)
					$output['owners'][] = array('id' => $id, 'name' => $name);
			}
			else
			{
				foreach ($instances['data'] as $instance)
					if (!isset($output['owners'][$instance['wf_owner']]))
						$output['owners'][$instance['wf_owner']] = array('id' => $instance['wf_owner'], 'name' => $instances['userMapping'][$instance['wf_owner']]);
				$output['owners'] = array_values($output['owners']);
			}
		}

		/* sort the owners */
		usort($output['owners'], create_function('$a,$b', 'return strcasecmp($a[\'name\'],$b[\'name\']);'));

		return $output;
	}

	/**
	 * Pega as atividades das instâncias de um determinado usuário
	 * @param array $params Parâmetros advindos da chamada Ajax
	 * @return array Contendo o nome/id das atividades
	 * @access public
	 */
	function getUserActivities($params)
	{
		$pid = (int) $params['pid'];
		$user = (int) $params['user'];
		/* check if the user has the proper right to list the instances of this process */
		if (!$this->checkAccess($pid, array($this->IP_REPLACE_USER)))
			return "Você não tem permissão para executar este procedimento!";

		$instances = $this->processMonitor->monitor_list_instances(0, -1, 'wf_activity_name__ASC', null, "gia.wf_user = '{$user}' AND gp.wf_p_id = {$pid}");
		$activities = array_map('unserialize', array_unique(array_map(create_function('$a', 'return serialize(array(\'id\' => $a[\'wf_activity_id\'], \'name\' => $a[\'wf_activity_name\']));'), $instances['data'])));
		usort($activities, create_function('$a,$b', 'return strcasecmp($a[\'name\'],$b[\'name\']);'));

		$output = $params;
		$output['activities'] = $activities;

		return $output;
	}

	/**
	 * Verifica se o novo dono das instâncias já está em todos os perfis necessários. Caso não esteja, envia uma lista com sugestões de perfis
	 * @param array $params Parâmetros advindos da chamada Ajax
	 * @return array Contendo informações e sugestões de perfis para adicionar o novo dono das instâncias
	 * @access public
	 */
	function checkUserRoles($params)
	{
		$pid = (int) $params['pid'];
		$oldUser = (int) $params['oldUser'];
		$newUser = (int) $params['newUser'];
		$activity = (int) $params['activity'];

		/* check if the user has the proper right to list the instances of this process */
		if (!$this->checkAccess($pid, array($this->IP_REPLACE_USER)))
			return "Você não tem permissão para executar este procedimento!";

		$activities = array();
		if ($activity == 0)
		{
			$instances = $this->processMonitor->monitor_list_instances(0, -1, 'wf_activity_name__ASC', null, "gia.wf_user = '{$oldUser}' AND gp.wf_p_id = {$pid}");
			$activities = array_values(array_map('unserialize', array_unique(array_map(create_function('$a', 'return serialize(array(\'id\' => $a[\'wf_activity_id\'], \'name\' => $a[\'wf_activity_name\']));'), $instances['data']))));
		}
		else
		{
			$activityInfo = $this->activityManager->get_activity($activity);
			$activities[] = array('id' => $activity, 'name' => $activityInfo['wf_name']);
		}

		$userRoles = $this->roleManager->getUserRoles($newUser, $pid);
		foreach ($activities as $key => $value)
		{
			$roles = $this->activityManager->get_activity_roles($value['id']);
			$userExistsInRoles = false;
			foreach ($roles as $role)
			{
				if (in_array($role['wf_role_id'], $userRoles))
				{
					$userExistsInRoles = true;
					break;
				}
				else
					$activities[$key]['possibleRoles'][] = array('id' => $role['wf_role_id'], 'name' => $role['wf_name']);
			}
			if ($userExistsInRoles)
			{
				unset($activities[$key]);
				continue;
			}
		}
		$output = $params;
		$output['roles'] = array_values($activities);

		return $output;
	}

	/**
	 * Adiciona um usuário ao perfil indicado
	 * @param array $params Parâmetros advindos da chamada Ajax
	 * @return array Contém a saída do método 'checkUserRoles'
	 * @access public
	 */
	function addUserToRole($params)
	{
		$pid = (int) $params['pid'];
		$newUser = (int) $params['newUser'];
		$role = (int) $params['role'];

		/* check if the user has the proper right to list the instances of this process */
		if (!$this->checkAccess($pid, array($this->IP_REPLACE_USER)))
			return "Você não tem permissão para executar este procedimento!";

		/* get the process roles */
		$processRoles = $this->roleManager->list_roles($pid, 0, -1, 'wf_p_id__ASC', null);
		$processRoles = array_map(create_function('$a', 'return $a[\'wf_role_id\'];'), $processRoles['data']);

		/* check if the role belongs to the process */
		if (!in_array($role, $processRoles))
			return 'Os dados fornecidos estão incorretos.';
		$this->roleManager->map_user_to_role($pid, $newUser, $role, 'u');

		return $this->checkUserRoles($params);
	}

	/**
	 * Troca os usuários das instâncias (de acordo com filtro de atividade e usuário original)
	 * @param array $params Parâmetros advindos da chamada Ajax
	 * @return array Contém estatísticas sobre as instâncias alteradas
	 * @access public
	 */
	function replaceUser($params)
	{
		$pid = (int) $params['pid'];
		$oldUser = (int) $params['oldUser'];
		$newUser = (int) $params['newUser'];
		$activity = (int) $params['activity'];

		/* check if the user has the proper right to list the instances of this process */
		if (!$this->checkAccess($pid, array($this->IP_REPLACE_USER)))
			return "Você não tem permissão para executar este procedimento!";

		$instances = $this->processMonitor->monitor_list_instances(0, -1, 'wf_activity_name__ASC', null, "gia.wf_user = '{$oldUser}' AND gp.wf_p_id = {$pid}");
		$activities = array();
		if ($activity == 0)
			$activities = array_unique(array_map(create_function('$a', 'return $a[\'wf_activity_id\'];'), $instances['data']));
		else
			$activities[] = $activity;

		$OKCount = 0;
		$errorCount = 0;
		foreach ($instances['data'] as $instance)
		{
			if (in_array($instance['wf_activity_id'], $activities))
			{
				if ($this->instanceManager->set_instance_user($instance['wf_instance_id'], $instance['wf_activity_id'], $newUser))
					++$OKCount;
				else
					++$errorCount;
			}
		}
		$output = $params;
		$output['OKCount'] = $OKCount;
		$output['errorCount'] = $errorCount;

		return $output;
	}

	/**
	 * Gera os e-mails enviados para alertar sobre determinadas instâncias
	 * @param array $params Parâmetros repassados por outros métodos e que são advindos de uma chamada Ajax
	 * @return array Uma array seqüencial onde cada elemento corresponde a uma array contendo as informações de: nome do destinatário, e-mail do destinatário, ID do destinatário, corpo do e-mail e assunto do e-mail
	 * @access public
	 */
	function generateEmails($params)
	{
		/* define the type */
		$type = ($params['emailType'] == 'user') ? 'user' : 'instance';

		/* load the instances and add the username */
		$rawData = $this->listInstances($params, true);
		$list = array();
		foreach ($rawData['data'] as $element)
			if (($element['wf_user'] != '*') && (substr($element['wf_user'], 0, 1) != 'p'))
				$list[] = array_merge($element, array('wf_user_name' => $rawData['userMapping'][$element['wf_user']]));

		/* check if there is at least one instance */
		$output = array();
		if (count($list) === 0)
			return $output;

		$BOUserInterface = &Factory::getInstance('bo_userinterface');

		/* initialize some variables */
		$translationArray = array();
		$emailBody = $params['emailBody'];
		$emailSubject = $params['emailSubject'];

		/* if the developer decided to send only one e-mail per user */
		if ($type === 'user')
		{
			/* split the e-mail according to definition */
			preg_match('/(.*)%inicio_loop%(.*)%fim_loop%(.*)/', $emailBody, $records);
			if (($records == null) || (count($records) != 4))
				return array('error' => 'Formato de e-mail inválido');

			/* prepare the list */
			$userList = array();
			foreach ($list as $element)
				$userList[$element['wf_user']][] = $element;

			/* construct the e-mails */
			foreach ($userList as $user => $instances)
			{
				/* add the e-mail header */
				$email = $records[1];
				$totalInstances = count($instances);
				$currentInstance = 1;
				foreach ($instances as $instance)
				{
					/* prepare the translation array */
					$url = sprintf('%s/index.php?menuaction=workflow.run_activity.go&activity_id=%d&iid=%d', $_SESSION['phpgw_info']['workflow']['server']['webserver_url'], $instance['wf_activity_id'], $instance['wf_instance_id']);
					$translationArray = array(
						'%atividade%' => $instance['wf_activity_name'],
						'%usuario%' => $instance['wf_user_name'],
						'%processo%' => $instance['wf_procname'],
						'%identificador%' => $instance['wf_instance_name'],
						'%tempo_atividade%' => $BOUserInterface->time_diff(mktime() - $instance['wf_act_started']),
						'%tempo_instancia%' => $BOUserInterface->time_diff(mktime() - $instance['wf_started']),
						'%prioridade%' => $instance['wf_priority'],
						'%quantidade_instancia%' => $totalInstances,
						'%atual_instancia%' => $currentInstance++,
						'%inicio_atividade%' => date('d/m/Y H\hi', $instance['wf_act_started']),
						'%inicio_instancia%' => date('d/m/Y H\hi', $instance['wf_started']),
						'%url%' => $url,
						'%link%' => sprintf('<a href="%s" target="_blank">%s</a>', $url, $url)
					);

					$email .= str_replace(array_keys($translationArray), array_values($translationArray), $records[2]);
				}

				/* add the e-mail footer */
				$email .= $records[3];

				/* replace, once again, any %variable% (also for the subject) */
				$email = str_replace(array_keys($translationArray), array_values($translationArray), $email);
				$subject = str_replace(array_keys($translationArray), array_values($translationArray), $emailSubject);

				/* construct the final e-mail array */
				$output[] = array(
					'emailBody' => $email,
					'emailSubject' => $subject,
					'user' => $user
				);
			}
		}

		if ($type === 'instance')
		{
			/* construct the e-mails */
			foreach ($list as $instance)
			{
				$url = sprintf('%s/index.php?menuaction=workflow.run_activity.go&activity_id=%d&iid=%d', $_SESSION['phpgw_info']['workflow']['server']['webserver_url'], $instance['wf_activity_id'], $instance['wf_instance_id']);
				$translationArray = array(
					'%atividade%' => $instance['wf_activity_name'],
					'%usuario%' => $instance['wf_user_name'],
					'%processo%' => $instance['wf_procname'],
					'%identificador%' => $instance['wf_instance_name'],
					'%tempo_atividade%' => $BOUserInterface->time_diff(mktime() - $instance['wf_act_started']),
					'%tempo_instancia%' => $BOUserInterface->time_diff(mktime() - $instance['wf_started']),
					'%prioridade%' => $instance['wf_priority'],
					'%quantidade_instancia%' => 1,
					'%atual_instancia%' => 1,
					'%inicio_atividade%' => date('d/m/Y H\hi', $instance['wf_act_started']),
					'%inicio_instancia%' => date('d/m/Y H\hi', $instance['wf_started']),
					'%url%' => $url,
					'%link%' => sprintf('<a href="%s" target="_blank">%s</a>', $url, $url)
				);

				/* replace any %variable% for its correspondence (also for the subject) */
				$email = str_replace(array_keys($translationArray), array_values($translationArray), $emailBody);
				$subject = str_replace(array_keys($translationArray), array_values($translationArray), $emailSubject);

				/* construct the final e-mail array */
				$output[] = array(
					'emailBody' => $email,
					'emailSubject' => $subject,
					'user' => $instance['wf_user']
				);
			}
		}

		/* load the recipient e-mail */
		$ldap = &Factory::getInstance('WorkflowLDAP');
		foreach ($output as $key => $value)
		{
			$userData = $ldap->getUserInfo($value['user']);
			$output[$key]['to'] = $userData['mail'];
			$output[$key]['username'] = $userData['cn'];
		}

		return $output;
	}

	/**
	 * Gera um preview do e-mail que será enviado
	 * @param array $params Parâmetros advindos da chamada Ajax
	 * @return string O corpo do primeiro e-mail que seria enviado caso a ação "Enviar" fosse disparada
	 * @access public
	 */
	function previewEmail($params)
	{
		/* check if the user has the proper right to send e-mails related to this process */
		$pid = (int) $params['pid'];
		if (!$this->checkAccess($pid, array($this->IP_SEND_EMAILS)))
			return array('error' => 'Você não tem permissão para executar este procedimento!');

		$output = $this->generateEmails($params);
		if (!isset($output['error']))
			return array('emailBody' => $output[0]['emailBody'], 'emailCount' => count($output));
		else
			return $output;
	}

	/**
	 * Envia e-mails para os usuários das instâncias selecionadas
	 * @param array $params Parâmetros advindos da chamada Ajax
	 * @return int O número de e-mails enviados
	 * @access public
	 */
	function sendMail($params)
	{
		/* check if the user has the proper right to send e-mails related to this process */
		$pid = (int) $params['pid'];
		if (!$this->checkAccess($pid, array($this->IP_SEND_EMAILS)))
			return array('error' => 'Você não tem permissão para executar este procedimento!');

		/* generate the e-mails */
		$emails = $this->generateEmails($params);

		/* check for errors */
		if (isset($emails['error']) || (count($emails) == 0))
			return $emails;

		/* prepare the environment to load some configuration values from other module */
		$GLOBALS['phpgw']->db =& Factory::getInstance('WorkflowObjects')->getDBExpresso();
		$GLOBALS['phpgw']->common = Factory::getInstance('common');
		$GLOBALS['phpgw']->session = Factory::getInstance('sessions');
		function lang($a){return $a;};

		/* get the required configuration */
		$BOEmailAdmin = Factory::getInstance('bo');
		$profileList = $BOEmailAdmin->getProfileList();
		$profile = $BOEmailAdmin->getProfile($profileList[0]['profileID']);

		/**
		 * XXX - XXX
		 * It's weird.. There are two almost identical PHPMailer classes.
		 * The class registered in our factory is under 'EGW_INC_ROOT'.
		 * The class used here has the same name and it's under another
		 * directory. For now, let's just include it in the old-fashion
		 * way, but if someone, someday try to use both classes in the
		 * same access, it could cause us troubles.
		 */

		/* configure the PHPMailer class to send the e-mails */
		require_once '../expressoMail1_2/inc/class.phpmailer.php';
		$phpMailer = new PHPMailer();
		$phpMailer->SMTPDebug = false;
		$phpMailer->IsSMTP();
		$phpMailer->Host = $profile['smtpServer'];
		$phpMailer->Port = $profile['smtpPort'];
		$phpMailer->From = 'no-reply@' . $profile['defaultDomain'];
		$phpMailer->FromName = 'Workflow Expresso';

		/* send the e-mails */
		$count = 0;
		foreach ($emails as $email)
		{
			$phpMailer->ClearAllRecipients();
			$phpMailer->IsHTML(true);
			$phpMailer->Subject = $email['emailSubject'];
			$phpMailer->Body = $email['emailBody'];
			$phpMailer->AddAddress($email['to'], $email['username']);
			if ($phpMailer->Send())
				++$count;
		}
		return $count;
	}

	/**
	 * Lista instâncias inconsistentes.
	 * @param array $params Parâmetros advindos da chamada Ajax
	 * @return array Uma array contendo as instâncias de acordo com suas inconsistências
	 * @access public
	 */
	function loadInconsistentInstances($params)
	{
		$processID = (int) $params['pid'];
		/* check if the user has the proper right to send e-mails related to this process */
		if (!$this->checkAccess($processID))
			return "Você não tem permissão para executar este procedimento!";

		$output = array(
			'instances' => array(),
			'names' => array(),
			'params' => $params
		);

		$output['instances'][] = array(
			'name' => 'Usuários Removidos',
			'description' => 'Quando uma instância está atribuída a um usuário que não está mais no LDAP.',
			'items' => $this->inconsistentInstancesRemovedUser($processID)
		);

		$output['instances'][] = array(
			'name' => 'Usuários sem Autorização',
			'description' => 'Quando o usuário que está com a instância não pertence a nenhum dos perfis da atividade desta instância.',
			'items' => $this->inconsistentInstancesUnauthorizedUsers($processID)
		);

		$output['instances'][] = array(
			'name' => 'Instâncias Falhas',
			'description' => 'Quando uma instância encontra-se em uma atividade não-interativa.',
			'items' => $this->inconsistentInstancesFailedInstances($processID)
		);

		$output['instances'][] = array(
			'name' => 'Instâncias Finalizadas Vinculadas a uma Atividade',
			'description' => 'Quando uma instância finalizada ou abortada ainda está vinculada a uma atividade.',
			'items' => $this->inconsistentInstancesUnfinishedActivity($processID)
		);

		$userIDs = array();
		foreach ($output['instances'] as $inconsistencyTypes)
			foreach ($inconsistencyTypes['items'] as $instance)
				$userIDs[$instance['wf_user']] = '';

		$output['names'] = $this->getNames(array_keys($userIDs), $processID);

		return $output;
	}

	/**
	 * Carrega os nomes de usuários/perfis
	 * @param array $userIDs Uma array contendo os IDs dos usuários/perfis.
	 * @param int $processID O ID do processo de onde os nomes dos perfis serão extraídos
	 * @return array Uma array associativa no formato ID => Nome
	 * @access public
	 */
	private function getNames($userIDs, $processID)
	{
		$output = array();
		$cachedLDAP = &Factory::getInstance('CachedLDAP');
		$ldap = &Factory::getInstance('WorkflowLDAP');
		foreach ($userIDs as $userID)
		{
			if (!isset($output[$userID]))
			{
				if ($userID == '*')
				{
					$output['*'] = '*';
					continue;
				}

				if (substr($userID, 0, 1) == 'p')
				{
					$role = $this->roleManager->get_role($processID, substr($userID, 1));
					$output[$userID] = 'Perfil: ' . $role['wf_name'];
				}
				else
				{
					$name = $ldap->getName($userID);
					if (empty($name))
					{
						$userInfo = $cachedLDAP->getEntryByID($userID);
						if ($userInfo)
							$name = "ID: {$userID} - {$userInfo['cn']} (excluído)";
						else
							$name = "ID: {$userID}";
					}

					$output[$userID] = $name;
				}
			}
		}
		return $output;
	}

	/**
	 * Lista as instâncias inconsistentes: instâncias que estão com usuários que foram removidos do LDAP
	 * @param int $processID O ID do processo
	 * @return array Lista das instâncias que satisfazem o critério de inconsistência
	 * @access public
	 */
	private function inconsistentInstancesRemovedUser($processID)
	{
		$filters = array();
		$filters[] = "(gp.wf_p_id = {$processID})";
	   	$filters[] = '(gia.wf_user IS NOT NULL)';

		$cachedLDAP = &Factory::newInstance('CachedLDAP');
		$cachedLDAP->setOperationMode($cachedLDAP->OPERATION_MODE_LDAP);
		$output = array();
		$instanceList = $this->processMonitor->monitor_list_instances(0, -1, 'wf_instance_id__ASC', '', implode(' AND ', $filters));

		$userIDs = array();
		foreach ($instanceList['data'] as $instance)
		{
			if (substr($instance['wf_user'], 0, 1) == 'p')
				continue;

			if ($instance['wf_user'] == '*')
				continue;

			$userIDs[$instance['wf_user']] = true;
		}

		$validUsers = Factory::getInstance('WorkflowLDAP')->getNames(array_keys($userIDs));
		array_walk($validUsers, create_function('&$a', '$a = $a[\'id\'];'));

		foreach ($instanceList['data'] as $instance)
		{
			if (substr($instance['wf_user'], 0, 1) == 'p')
				continue;

			if ($instance['wf_user'] == '*')
				continue;

			if (!in_array($instance['wf_user'], $validUsers))
				$output[] = $instance;
		}

		return $output;
	}

	/**
	 * Lista as instâncias inconsistentes: instâncias que estão com usuários que não estão em nenhum dos perfis da atividade em que a instância se encontra
	 * @param int $processID O ID do processo
	 * @return array Lista das instâncias que satisfazem o critério de inconsistência
	 * @access public
	 */
	private function inconsistentInstancesUnauthorizedUsers($processID)
	{
		$output = array();
		$roleIDs = array();
		$roles = $this->roleManager->list_roles($processID, 0, -1, 'wf_role_id__ASC', '');
		foreach ($roles['data'] as $role)
			$roleIDs[] = $role['wf_role_id'];
		$activities = $this->activityManager->list_activities($processID, 0, -1, 'wf_name__asc', '', '');
		$activityUsers = array();
		foreach ($activities['data'] as $activity)
		{
			if ($activity['wf_is_interactive'] == 'y')
			{
				$userList = $this->roleManager->list_mapped_users($processID, true, array('wf_activity_id' => $activity['wf_activity_id']));
				if (!isset($activityUsers[$activity['wf_activity_id']]))
					$activityUsers[$activity['wf_activity_id']] = array();
				$activityUsers[$activity['wf_activity_id']] += array_keys($userList);
			}
		}

		$filters = array();
		$filters[] = "(gp.wf_p_id = {$processID})";
		$filters[] = "(ga.wf_is_interactive = 'y')";
	   	$filters[] = '(gia.wf_user IS NOT NULL)';
		$instanceList = $this->processMonitor->monitor_list_instances(0, -1, 'wf_instance_id__ASC', '', implode(' AND ', $filters));
		foreach ($instanceList['data'] as $instance)
		{
			if ($instance['wf_user'] == '*')
				continue;

			if (substr($instance['wf_user'], 0, 1) == 'p')
				if (in_array(substr($instance['wf_user'], 1), $roleIDs))
					continue;

			if (in_array($instance['wf_user'], $activityUsers[$instance['wf_activity_id']]))
				continue;

			$output[] = $instance;
		}

		return $output;
	}

	private function inconsistentInstancesFailedInstances($processID)
	{
		$filters = array();
		$filters[] = "(gp.wf_p_id = {$processID})";
		$filters[] = "(ga.wf_is_interactive = 'n')";
		$filters[] = '(gia.wf_started < ' . (date('U') - 60). ')';
		$instanceList = $this->processMonitor->monitor_list_instances(0, -1, 'wf_instance_id__ASC', '', implode(' AND ', $filters));

		return $instanceList['data'];
	}

	/**
	 * Lista as instâncias inconsistentes: instâncias que estão paradas em atividades não-interativas há mais de um minuto
	 * @param int $processID O ID do processo
	 * @return array Lista das instâncias que satisfazem o critério de inconsistência
	 * @access public
	 */
	private function inconsistentInstancesUnfinishedActivity($processID)
	{
		$filters = array();
		$filters[] = "(gp.wf_p_id = {$processID})";
		$filters[] = "(gi.wf_status IN ('aborted', 'completed'))";
		$filters[] = '(gia.wf_activity_id IS NOT NULL)';
		$instanceList = $this->processMonitor->monitor_list_instances(0, -1, 'wf_instance_id__ASC', '', implode(' AND ', $filters));

		return $instanceList['data'];
	}
}
?>
