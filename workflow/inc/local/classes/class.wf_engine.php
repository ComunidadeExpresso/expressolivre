<?php
/**
* Prov� m�todos que acessam informa��es relacionadas � engine.
* @author Sidnei Augusto Drovetto Junior - drovetto@gmail.com
* @version 1.0
* @license http://www.gnu.org/copyleft/gpl.html GPL
* @package Workflow
* @subpackage local
*/
class wf_engine
{
	/**
	* @var object $processManager objeto da classe ProcessManager
	* @access private
	*/
	var $processManager;
	/**
	* @var object $acl objeto da classe workflow_acl
	* @access private
	*/
	var $acl;
	/**
	* @var object $run_activity objeto da classe run_activity
	* @access private
	*/
	var $run_activity;

	/**
	* Construtor do wf_engine.
	* @return object
	* @access public
	*/
	function wf_engine()
	{
		$this->processManager = null;
		$this->acl = null;
		$this->run_activity = null;
	}

	private function getCurrentProcessID()
	{
		if (!is_null($GLOBALS['workflow']['wf_runtime']->activity))
			return (int) $GLOBALS['workflow']['wf_runtime']->activity->getProcessId();

		if (isset($GLOBALS['workflow']['job']))
			return (int) $GLOBALS['workflow']['job']['processID'];

		return false;
	}

	private function checkProcessAccess($processID)
	{
		$processID = (int) $processID;
		return ($processID === $this->getCurrentProcessID());
	}

	/**
	* Busca informa��es de um (ou mais) processo(s).
	* @param mixed $pids Uma array de IDs de processo ou um inteiro representando o ID de um processo.
	* @return array Informa��es sobre o(s) processo(s).
	* @access public
	*/
	function getProcess($pids)
	{
		if (!is_array($pids))
			$pids = array($pids);

		$flagObject[0] = is_null($this->processManager);
		if ($flagObject[0])
			$this->processManager = Factory::getInstance('workflow_processmanager');

		$output = array();
		foreach ($pids as $pid)
			$output[] = $this->processManager->get_process($pid, false);

		if ($flagObject[0])
			$this->processManager = null;

		return $output;
	}

	/**
	* Busca informa��es de um (ou mais) processo(s) pelo seu nome (ou apenas parte do nome).
	* @param string $name String contendo o nome de um processo.
	* @return array Informa��es sobre o(s) processo(s).
	* @access public
	*/
	function getProcessesByName($name)
	{
		$output = array();
		if (is_string($name)){
			$flagObject[0] = is_null($this->processManager);
			if ($flagObject[0])
				$this->processManager = Factory::getInstance('workflow_processmanager');

			// assinatura do m�todo: list_processes($offset,$maxRecords,$sort_mode,$find='',$where='')
			$output = $this->processManager->list_processes(-1, -1, '', $name, null);

			if ($flagObject[0])
				$this->processManager = null;
		}
		return $output;
	}

	/**
	* D� seq��ncia no fluxo de uma inst�ncia (simula a��o do usu�rio).
	* @param int $activityID O ID da atividade da inst�ncia.
	* @param int $instanceID O ID da inst�ncia.
	* @return bool true caso a inst�ncia tenha sido continuada e false caso contr�rio.
	* @access public
	* @deprecated 2.2.00.000
	*/
	function continueInstance($activityID, $instanceID)
	{
		wf_warn_deprecated_method('wf_instance', 'continueInstance');
		$WFInstance = &Factory::getInstance('wf_instance');
		return $WFInstance->continueInstance($activityID, $instanceID);
	}

	/**
	* Aborta uma inst�ncia
	* @param int $instanceID O ID da inst�ncia.
	* @return boolean true se foi poss�vel abortar a inst�ncia e false caso contr�rio.
	* @access public
	* @deprecated 2.2.00.000
	*/
	function abortInstance($instanceID)
	{
		wf_warn_deprecated_method('wf_instance', 'abort');
		$WFInstance = &Factory::getInstance('wf_instance');
		return $WFInstance->abort($instanceID);
	}

	/**
	* Define o nome (identificador) de uma inst�ncia
	* @param int $instanceID O ID da inst�ncia.
	* @param string $name O novo nome da inst�ncia.
	* @return boolean true se foi poss�vel mudar o nome da inst�ncia e false caso contr�rio.
	* @access public
	* @deprecated 2.2.00.000
	*/
	function setInstanceName($instanceID, $name)
	{
		wf_warn_deprecated_method('wf_instance', 'setName');
		$WFInstance = &Factory::getInstance('wf_instance');
		return $WFInstance->setName($instanceID, $name);
	}

	/**
	* Define a prioridade de uma inst�ncia
	* @param int $instanceID O ID da inst�ncia.
	* @param int $priority A nova prioridade da inst�ncia
	* @return boolean true se foi poss�vel mudar a prioridade da inst�ncia e false caso contr�rio.
	* @access public
	* @deprecated 2.2.00.000
	*/
	function setInstancePriority($instanceID, $priority)
	{
		wf_warn_deprecated_method('wf_instance', 'setPriority');
		$WFInstance = &Factory::getInstance('wf_instance');
		return $WFInstance->setPriority($instanceID, $priority);
	}

	/**
	* Busca inst�ncias abandonadas.
	* @param int $numberOfDays O tempo (em dias) em que a inst�ncia est� abandonada.
	* @param array $activities Uma lista de atividades das quais se quer as inst�ncias abandonadas (tamb�m pode ser um valor inteiro).
	* @return array As inst�ncias que satisfazem o crit�rio de sele��o.
	* @access public
	* @deprecated 2.2.00.000
	*/
	function getIdleInstances($numberOfDays, $activities = null)
	{
		wf_warn_deprecated_method('wf_instance', 'getIdle');
		$WFInstance = &Factory::getInstance('wf_instance');
		return $WFInstance->getIdle($numberOfDays, $activities);
	}

	/**
	* Busca todas as inst�ncias.
	* @param array $activities Uma lista de atividades das quais se quer as inst�ncias (tamb�m pode ser um valor inteiro).
	* @return array As inst�ncias que satisfazem o crit�rio de sele��o.
	* @access public
	* @deprecated 2.2.00.000
	*/
	function getInstances($activities = null)
	{
		wf_warn_deprecated_method('wf_instance', 'getIdle');
		return $this->getIdleInstances(0, $activities);
	}

	/**
	* Busca todas as inst�ncias que possuem esse nome (identificador).
	* @param string $name O nome da inst�ncia que se quer encontrar.
	* @return array As inst�ncias que satisfazem o crit�rio de sele��o.
	* @access public
	* @deprecated 2.2.00.000
	*/
	function getInstancesByName($name)
	{
		wf_warn_deprecated_method('wf_instance', 'getByName');
		$WFInstance = &Factory::getInstance('wf_instance');
		$preOutput = $WFInstance->getByName($name);
		$output = array();
		foreach ($preOutput as $childInstance)
			if (!is_null($childInstance['wf_activity_id']))
				$output[] = $childInstance;

		return $output;
	}

	/**
	* Busca as inst�ncias filhas de uma inst�ncia
	* Se os par�metros n�o forem informados, retorna inst�ncias filhas das inst�ncia atual.
	* @param int $instanceID O ID da inst�ncia pai (n�o obrigat�rio).
	* @param int $activityID O ID da atividade corrente da inst�ncia pai
	* @return array As inst�ncias filhas do par inst�ncia/atividade atual
	* @access public
	* @deprecated 2.2.00.000
	*/
	function getChildInstances($instanceID = null, $activityID = null)
	{
		wf_warn_deprecated_method('wf_instance', 'getChildren');
		$WFInstance = &Factory::getInstance('wf_instance');
		$preOutput = $WFInstance->getChildren($instanceID);
		$output = array();
		foreach ($preOutput as $childInstance)
			if (!is_null($childInstance['wf_activity_id']))
				$output[] = $childInstance;

		return $output;
	}

	/**
	* Busca as propriedades de uma inst�ncia (do mesmo processo).
	* @param int $instanceID O ID da inst�ncia.
	* @return mixed Uma array contento as propriedades da inst�ncia (no formato "nome_da_propriedade" => "valor"). Ou false em caso de erro.
	* @access public
	* @deprecated 2.2.00.000
	*/
	function getInstanceProperties($instanceID)
	{
		wf_warn_deprecated_method('wf_instance', 'getProperties');
		$WFInstance = &Factory::getInstance('wf_instance');
		return $WFInstance->getProperties($instanceID);
	}

	/**
	* Busca as propriedades de uma inst�ncia filha.
	* @param int $instanceID O ID da inst�ncia filha.
	* @return array Propriedades da inst�ncia filha. Array no formato "nome_da_propriedade" => "valor".
	* @access public
	* @deprecated 2.2.00.000
	*/
	function getChildInstanceProperties($instanceID)
	{
		wf_warn_deprecated_method('wf_instance', 'getProperties');
		$WFInstance = &Factory::getInstance('wf_instance');
		return $WFInstance->getProperties($instanceID);
	}

	/**
	* Busca os recursos que um usu�rio pode administrar.
	* @param string $type Tipo do recurso ("PRO", "MON, etc.).
	* @param int $uid O usu�rio do qual se quer obter informa��es de administra��o de recursos.
	* @return array IDs dos recursos ao qual o usu�rio tem acesso.
	* @access public
	*/
	function getUserPermissions($type, $uid)
	{
		$flagObject[0] = is_null($this->acl);
		if ($flagObject[0])
			$this->acl = Factory::getInstance('workflow_acl');

		$output = $this->acl->getUserPermissions($type, $uid);

		if ($flagObject[0])
			$this->acl = null;

		return $output;
	}

	/**
	* Busca os recursos que um usu�rio pode administrar (inclusive faz verifica��o de acordo com permiss�es advindas de grupos).
	* @param string $type Tipo do recurso ("PRO", "MON, etc.).
	* @param int $uid O usu�rio do qual se quer obter informa��es de administra��o de recursos.
	* @return array IDs dos recursos ao qual o usu�rio tem acesso.
	* @access public
	*/
	function getUserGroupPermissions($type, $uid)
	{
		$flagObject[0] = is_null($this->acl);
		if ($flagObject[0])
			$this->acl = Factory::getInstance('workflow_acl');

		$output = $this->acl->getUserGroupPermissions($type, $uid);

		if ($flagObject[0])
			$this->acl = null;

		return $output;
	}

	/**
	* Traz informa��es sobre uma atividade a partir de seu ID
	* @param int $activityID O ID da atividade
	* @return mixed Uma array associativa contendo as informa��es sobre a atividade ou false caso a atividade n�o seja encontrada
	* @access public
	*/
	function getActivityInformationByID($activityID)
	{
		$activityID = (int) $activityID;
		$processID = $this->getCurrentProcessID();

		/* build the SQL query */
		$query = "SELECT wf_activity_id, wf_name, wf_normalized_name, wf_type, wf_description FROM egw_wf_activities WHERE (wf_activity_id = ?) AND (wf_p_id = ?)";
		$db = &Factory::getInstance('WorkflowObjects')->getDBGalaxia()->Link_ID;
		$resultSet = $db->query($query, array($activityID, $processID));

		/* return the data */
		if (($row = $resultSet->fetchRow()))
			return array(
				'activity_id' => $row['wf_activity_id'],
				'name' => $row['wf_name'],
				'normalized_name' => $row['wf_normalized_name'],
				'type' => $row['wf_type'],
				'description' => $row['wf_description']
			);
		else
			return false;
	}

	/**
	* Traz informa��es sobre uma atividade a partir de seu nome
	* @param int $activityName O nome da atividade
	* @return mixed Uma array associativa contendo as informa��es sobre a atividade ou false caso a atividade n�o seja encontrada
	* @access public
	*/
	function getActivityInformationByName($activityName)
	{
		$processID = $this->getCurrentProcessID();

		/* build the SQL query */
		$query = "SELECT wf_activity_id, wf_name, wf_normalized_name, wf_type, wf_description FROM egw_wf_activities WHERE (wf_name = ?) AND (wf_p_id = ?)";
		$db = &Factory::getInstance('WorkflowObjects')->getDBGalaxia()->Link_ID;
		$resultSet = $db->query($query, array($activityName, $processID));

		/* return the data */
		if (($row = $resultSet->fetchRow()))
			return array(
				'activity_id' => $row['wf_activity_id'],
				'name' => $row['wf_name'],
				'normalized_name' => $row['wf_normalized_name'],
				'type' => $row['wf_type'],
				'description' => $row['wf_description']
			);
		else
			return false;
	}

	/**
	* Busca as inst�ncia de usu�rios de acordo com alguns crit�rios
	* @param mixed $users Um array com IDs de usu�rios ou perfis (no caso de perfis, deve-se prefixar seu ID com o caractere 'p'). Tamb�m pode possuir um �nico ID (seja de usu�rio ou de perfil)
	* @param mixed $activities Um array com IDs de atividades das se quer as inst�ncias. Tamb�m pode ser um inteiro, representando um �nico ID. Caso possua valor null, o resultado n�o � filtrado de acordo com as atividades (par�metro opcional)
	* @param mixed $status Um array com os status requeridos (para filtrar as inst�ncias). Tamb�m pode ser uma string, representando um �nico status. Caso possua valor null, o resultado n�o � filtrado de acordo com o status. Os status podem ser: completed, active, aborted e exception (par�metro opcional)
	* @return array As inst�ncias que satisfazem o crit�rio de sele��o.
	* @access public
	* @deprecated 2.2.00.000
	*/
	function getUserInstances($users, $activities = null, $status = null)
	{
		wf_warn_deprecated_method('wf_instance', 'getByUser');
		$WFInstance = &Factory::getInstance('wf_instance');
		return $WFInstance->getByUser($users, $activities, $status);
	}

	/**
	* Seta uma propriedade de uma inst�ncia.
	* @param int $instanceID O ID da inst�ncia.
	* @return bool true caso a propriedade tenha sido alterada com sucesso
	* @access public
	* @deprecated 2.2.00.000
	*/
	function setInstanceProperty($instanceID, $nameProperty, $value)
	{
		wf_warn_deprecated_method('wf_instance', 'setProperty');
		$WFInstance = &Factory::getInstance('wf_instance');
		return $WFInstance->setProperty($instanceID, $nameProperty, $value);
	}

	/**
	* Verifica se um dado usu�rio tem acesso a uma inst�ncia
	* @param int $userID O ID do usu�rio que se quer verificar
	* @param int $instanceID O ID da inst�ncia
	* @param int $activityID O ID da atividade onde a inst�ncia est�
	* @param bool $writeAccess Se true, indica que � necess�rio que o usu�rio tenha acesso para modificar a inst�ncia (dar seq��ncia ao fluxo). Se false, n�o ser� verificado se o usu�rio tem permiss�o de escrita na inst�ncia
	* @return bool true se o usu�rio tiver acesso � inst�ncia (levando em considera��o $writeAccess) ou false caso contr�rio
	* @access public
	* @deprecated 2.2.00.000
	*/
	function checkUserAccessToInstance($userID, $instanceID, $activityID, $writeAccess = true)
	{
		wf_warn_deprecated_method('wf_instance', 'checkUserAccess');
		$WFInstance = &Factory::getInstance('wf_instance');
		return $WFInstance->checkUserAccess($userID, $instanceID, $activityID, $writeAccess);
	}
}
?>
