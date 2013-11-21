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
 * Camada Model para Mover Instâncias.
 * @package Workflow
 * @author Sidnei Augusto Drovetto Jr. - drovetto@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 */
class so_move_instances
{
	/**
	 * @var bool True se o usuário for administrador do expresso.
	 * @access private
	 */
	private $isAdmin;

	/**
	 * @var int ID do usuário logado no Expresso
	 * @access private
	 */
	private $userID;

	/**
	 * @var object Link para a ACL do Workflow.
	 * @access private
	 */
	private $acl;

	/**
	 * @var object Link para o Banco de Dados do Expresso.
	 * @access private
	 */
	private $db;

	/**
	 * Checa se o usuário possui direitos administrativos em um processo.
	 * @param int $processID O ID do processo que se quer checar se o usuário tem direito administrativo.
	 * @return bool True em caso de sucesso. Em caso de falha, a execução é abortada.
	 * @access private
	 */
	private function _checkAccess($processID = null)
	{
		/* the user is an administrator */
		if ($this->isAdmin)
			return true;

		if (!is_null($processID))
		{
			if ($this->acl->checkUserGroupAccessToResource('PRO', $this->userID, $processID))
				return true;
			else
				die(serialize("Você não tem permissão para executar este procedimento!"));
		}
		if ($this->acl->checkUserGroupAccessToType('PRO', $this->userID))
			return true;

		die(serialize("Você não tem permissão para executar este procedimento!"));
	}

	/**
	 * Verifica se houve erro em alguma query do Banco de Dados.
	 * @param object $result O resultado de alguma query
	 * @return void
	 * @access private
	 */
	private function _checkError($result)
	{
		if ($result === false)
			die(serialize("Ocorreu um erro ao se tentar executar a operação solicitada."));
	}

	/**
	 * Construtor da classe so_move_instances
	 * @return object
	 */
	function so_move_instances()
	{
		$this->userID = $_SESSION['phpgw_info']['workflow']['account_id'];
		$this->isAdmin = $_SESSION['phpgw_info']['workflow']['user_is_admin'];
		$this->acl =& $GLOBALS['ajax']->acl;
		$this->db =& Factory::getInstance('WorkflowObjects')->getDBGalaxia()->Link_ID;
	}

	/**
	 * Carrega a lista de processos que o usuário tem acesso.
	 * @return array Lista de processos.
	 * @access public
	 */
	function loadProcesses()
	{
		$this->_checkAccess();

		$where = array();
		if (!$this->isAdmin)
		{
			$processIDs = $this->acl->get_granted_processes($this->userID);
			if (count($processIDs) > 0)
				$where[] = 'wf_p_id IN (' . implode(',', $processIDs) . ')';
			else
				$where[] = 'wf_p_id IS NULL';
		}
		$processManager = &Factory::getInstance('ProcessManager');

		/* workaround to sort the result using two columns */
		$items = $processManager->list_processes(0, -1, 'wf_name__ASC, wf_version ASC', '', implode(' AND ', $where));
		$output = array();
		foreach ($items['data'] as $item)
			$output[] = array(
				'wf_p_id' => $item['wf_p_id'],
				'wf_name' => $item['wf_name'],
				'wf_version' => $item['wf_version']);

		return $output;
	}

	/**
	 * Carrega a lista de atividades de um processo (com exceção de atividades do tipo Standalone e View).
	 * @param int $processID O ID do processo do qual se quer a lista de atividades.
	 * @return array Lista de atividades de um processo.
	 * @access public
	 */
	function loadProcessActivities($processID)
	{
		$this->_checkAccess($processID);

		$activityManager = &Factory::newInstance('ActivityManager');
		$activities = $activityManager->list_activities($processID, 0, -1, 'wf_name__ASC', '', 'wf_type <> \'standalone\' AND wf_type <> \'view\'');
		$output = array();
		foreach ($activities['data'] as $activity)
			$output[] = array(
				'wf_activity_id' => $activity['wf_activity_id'],
				'wf_name' => $activity['wf_name'],
				'wf_type' => $activity['wf_type']);
		return $output;
	}

	/**
	 * Faz um pré-relacionamento das atividades dos dois processos.
	 * @param array $fromActivities Lista de atividades do processo de origem.
	 * @param array $toActivities Lista de atividades do processo de destino.
	 * @param float $threshold Limiar que define a menor porcentagem de semelhança entre o nome de duas atividades para que estas sejam relacionadas.
	 * @return array Lista de atividades relacionadas.
	 * @access public
	 */
	function matchActivities($fromActivities, $toActivities, $threshold)
	{
		$preOutput = array();
		foreach ($fromActivities as $fromActivity)
		{
			$fromActivityName = $fromActivity['wf_name'];
			$fromActivityID = $fromActivity['wf_activity_id'];
			foreach ($toActivities as $toActivity)
			{
				$toActivityName = $toActivity['wf_name'];
				$toActivityID = $toActivity['wf_activity_id'];
				$currentValue = isset($preOutput[$fromActivityID]) ? $preOutput[$fromActivityID]['value'] : 0;

				similar_text($fromActivityName, $toActivityName, $matchValue);
				if (($matchValue > $threshold) && ($matchValue > $currentValue))
					$preOutput[$fromActivityID] = array(
						'activityID' => $toActivityID,
						'value' => $matchValue);
			}
		}
		$output = array();
		foreach ($preOutput as $fromActivityID => $toActivityInfo)
			$output[] = array(
				'from' => $fromActivityID,
				'to' => $toActivityInfo['activityID']);

		return $output;
	}

	/**
	 * Move as instâncias de um processo para outro.
	 * @param int $from O ID do processo de origem.
	 * @param int $to O ID do processo de destino.
	 * @param bool $active Indica se devem ser movidas as instâncias ativas.
	 * @param bool $completed Indica se devem ser movidas as instâncias finalizadas.
	 * @param array $activityMappings O relacionamento entre as atividades dos dois processos.
	 * @return bool TRUE em caso de sucesso e FALSE caso contrário.
	 * @access public
	 */
	function moveInstances($from, $to, $activityMappings, $active, $completed)
	{
		$this->_checkAccess($from);
		$this->_checkAccess($to);

		if (($active || $completed) == false)
			return array('error' => 'Nenhuma instância foi movida. Selecione pelo menos uma das checkboxes do status das instâncias.');

		$instanceStatus = array();
		$instanceActivityStatus = array();
		if ($active)
		{
			$instanceStatus[] = "'active'";
			$instanceStatus[] = "'exception'";
			$instanceActivityStatus[] = "'running'";
		}

		if ($completed)
		{
			$instanceStatus[] = "'completed'";
			$instanceStatus[] = "'aborted'";
			$instanceActivityStatus[] = "'completed'";

		}

		/* create an array for quick conversion between the old and the new activities ID */
		$activitiesConvert = array();
		foreach ($activityMappings as $toActivityID => $fromActivities)
			foreach ($fromActivities as $fromActivityID)
				$activitiesConvert[$fromActivityID] = $toActivityID;

		$this->db->StartTrans();
		$transactionResult = true;

		/* update the instances table */
		$resultSet = $this->db->query("SELECT wf_instance_id, wf_next_activity, wf_next_user FROM egw_wf_instances WHERE (wf_status IN (" . implode(', ', $instanceStatus) . ")) AND (wf_p_id = ?)", array($from));
		$rows = $resultSet->GetArray(-1);
		$instanceList = array();
		if (is_array($rows))
		{
			foreach ($rows as $row)
			{
				/* save the ID of the instances for future use */
				$instanceList[] = $row['wf_instance_id'];

				/* get the data and unserialize them */
				$oldInstance = array(
					'wf_instance_id' => $row['wf_instance_id'],
					'wf_next_activity' => unserialize(base64_decode($row['wf_next_activity'])),
					'wf_next_user' => unserialize(base64_decode($row['wf_next_user'])));

				/* change the next activity */
				$newInstance['wf_next_activity'] = array();
				if (is_array($oldInstance['wf_next_activity']))
				{
					foreach ($oldInstance['wf_next_activity'] as $k => $v)
						$newInstance['wf_next_activity'][$activitiesConvert[$k]] = $activitiesConvert[$v];
				}

				/* change the next user */
				$newInstance['wf_next_user'] = array();
				if (is_array($oldInstance['wf_next_user']))
				{
					foreach ($oldInstance['wf_next_user'] as $k => $v)
						if ($k[0] == '*')
							$newInstance['wf_next_user']['*' . $activitiesConvert[substr($k, 1)]] = $v;
						else
							$newInstance['wf_next_user'][$activitiesConvert[$k]] = $v;
				}

				/* serialize and encode the data */
				$newInstance['wf_next_activity'] = base64_encode(serialize($newInstance['wf_next_activity']));
				$newInstance['wf_next_user'] = base64_encode(serialize($newInstance['wf_next_user']));

				/* update the egw_wf_instances table */
				if (!$this->db->query("UPDATE egw_wf_instances SET wf_next_activity = ?, wf_next_user = ?, wf_p_id = ? WHERE (wf_instance_id = ?)", array($newInstance['wf_next_activity'], $newInstance['wf_next_user'], $to, $oldInstance['wf_instance_id'])))
				{
					$this->db->FailTrans();
					return array('error' => 'Erro atualizando a tabela de instâncias. Nenhuma modificação foi salva');
				}
			}
		}
		/* assure at least one element */
		$instanceList[] = -1;

		/* update the instance_activities table */
		$instanceActivityList = array();
		$resultSet = $this->db->query("SELECT wf_instance_id, wf_activity_id FROM egw_wf_instance_activities WHERE (wf_status IN (" . implode(', ', $instanceActivityStatus) . ")) AND (wf_activity_id IN (" . implode(', ', array_keys($activitiesConvert)) . "))");
		$rows = $resultSet->GetArray(-1);
		if (is_array($rows))
		{
			foreach ($rows as $row)
			{
				/* save the instance ID and activity ID pair for future use */
				$instanceActivityList[] = array(
					'iid' => $row['wf_instance_id'],
					'aid' => $row['wf_activity_id']);

				/* update the activity ID */
				if (!$this->db->query("UPDATE egw_wf_instance_activities SET wf_activity_id = ? WHERE (wf_instance_id = ?) AND (wf_activity_id = ?)", array($activitiesConvert[$row['wf_activity_id']], $row['wf_instance_id'], $row['wf_activity_id'])))
				{
					$this->db->FailTrans();
					return array('error' => 'Erro atualizando a tabela que relaciona instâncias e atividades. Nenhuma modificação foi salva');
				}
			}
		}
		/* assure at least one element */
		$instanceActivityList[] = array('iid' => -1, 'aid' => -1);

		/* update the workitems of the modified instances */
		$resultSet = $this->db->query("SELECT wf_item_id, wf_activity_id FROM egw_wf_workitems WHERE (wf_instance_id IN (" . implode(', ', $instanceList) . "))");
		$rows = $resultSet->GetArray(-1);
		if (is_array($rows))
		{
			foreach ($rows as $row)
			{
				/* update the activity ID */
				if (!$this->db->query("UPDATE egw_wf_workitems SET wf_activity_id = ? WHERE (wf_item_id = ?)", array($activitiesConvert[$row['wf_activity_id']], $row['wf_item_id'])))
				{
					$this->db->FailTrans();
					return array('error' => 'Erro atualizando a tabela de workitems. Nenhuma modificação foi salva');
				}
			}
		}

		/* in case of success, commit the modifications */
		$this->db->CompleteTrans();
		return true;
	}
}
?>
