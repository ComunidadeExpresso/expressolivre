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
 * Camada Model para Administração de Jobs.
 * @package Workflow
 * @author Sidnei Augusto Drovetto Jr. - drovetto@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 */
class so_adminjobs
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
	 * @var object Link para o objeto de Administração de Jobs
	 * @access private
	 */
	private $jobManager;

	/**
	 * Checa se o usuário possui direitos administrativos em um processo.
	 * @param int $processID O ID do processo que se quer checar se o usuário tem direito administrativo.
	 * @param int $jobID O ID do job que se quer checar acesso.
	 * @return bool True em caso de sucesso. Em caso de falha, a execução é abortada.
	 * @access private
	 */
	private function checkAccess($processID, $jobID = null)
	{
		$error = array('error' => array('Você não tem permissão para executar este procedimento!'));

		/* the user is an administrator */
		if ($this->isAdmin)
			return true;

		if (is_null($processID) && is_null($jobID))
			return $error;

		if (!is_null($jobID))
		{
			$jobInfo = $this->jobManager->getJob((int) $jobID);

			if (is_null($processID))
				$processID = $jobInfo['wf_process_id'];
			else
				if ($jobInfo['wf_process_id'] != $processID)
					return $error;
		}

		if ($this->acl->checkUserGroupAccessToResource('PRO', $this->userID, $processID))
			return true;

		return $error;
	}

	/**
	 * Construtor da classe so_adminjobs
	 * @return object
	 */
	function so_adminjobs()
	{
		$this->userID = $_SESSION['phpgw_info']['workflow']['account_id'];
		$this->isAdmin = $_SESSION['phpgw_info']['workflow']['user_is_admin'];
		$this->acl = &$GLOBALS['ajax']->acl;
		$this->db = &Factory::getInstance('WorkflowObjects')->getDBGalaxia()->Link_ID;
		$this->jobManager = &Factory::newInstance('JobManager');
	}

	/**
	 * Carrega a lista de todos os jobs de um determinado processo.
	 * @param int $processID O ID do processo
	 * @return array Lista dos jobs
	 * @access public
	 */
	function loadJobs($processID)
	{
		$processID = (int) $processID;
		if (($errors = $this->checkAccess($processID)) !== true)
			return $errors;

		return $this->jobManager->getJobsByProcessID($processID);
	}

	/**
	 * Atualiza um job
	 * @param int $jobID O ID do Job que será atualizado
	 * @param int $processID O ID do processo a qual o Job pertence
	 * @param string $name O nome do Job
	 * @param string $description A descrição do Job
	 * @param string $timeStart Uma string cujo conteúdo é um data e horário devidamente formatados
	 * @param int $intervalValue O intervalo de repetição
	 * @param int $intervalUnity A unidade de repetição (dia, mês, etc.)
	 * @param int $dateType O tipo que define quando o Job é executado
	 * @param int $weekDays Inteiro que representa os dias da semana em que o Job será executado
	 * @param int $monthOffset Intervalo de execução relativa a mês
	 * @param bool $active Indica se o job está ativo (true) ou não (false)
	 * @return array Lista de possíveis erros
	 * @access public
	 */
	function updateJob($jobID, $processID, $name, $description, $timeStart, $intervalValue, $intervalUnity, $dateType, $weekDays, $monthOffset, $active)
	{
		$processID = (int) $processID;
		$jobID = (int) $jobID;
		if (($errors = $this->checkAccess($processID, $jobID)) !== true)
			return $errors;

		$this->jobManager->updateJob($jobID, $processID, $name, $description, $timeStart, $intervalValue, $intervalUnity, $dateType, $weekDays, $monthOffset, $active);
		return array('error' => $this->jobManager->get_error(true));
	}

	/**
	 * Cria um novo Job
	 * @param int $processID O ID do processo a qual o Job pertence
	 * @param string $name O nome do Job
	 * @param string $description A descrição do Job
	 * @param string $timeStart Uma string cujo conteúdo é um data e horário devidamente formatados
	 * @param int $intervalValue O intervalo de repetição
	 * @param int $intervalUnity A unidade de repetição (dia, mês, etc.)
	 * @param int $dateType O tipo que define quando o Job é executado
	 * @param int $weekDays Inteiro que representa os dias da semana em que o Job será executado
	 * @param int $monthOffset Intervalo de execução relativa a mês
	 * @param bool $active Indica se o job está ativo (true) ou não (false)
	 * @return array Lista de possíveis erros
	 * @access public
	 */
	function createJob($processID, $name, $description, $timeStart, $intervalValue, $intervalUnity, $dateType, $weekDays, $monthOffset, $active)
	{
		$processID = (int) $processID;
		if (($errors = $this->checkAccess($processID)) !== true)
			return $errors;

		$this->jobManager->createJob($processID, $name, $description, $timeStart, $intervalValue, $intervalUnity, $dateType, $weekDays, $monthOffset, $active);
		return array('error' => $this->jobManager->get_error(true));
	}

	/**
	 * Remove um Job
	 * @param int $jobID O ID do job
	 * @return void
	 * @access public
	 */
	function removeJob($jobID)
	{
		$jobID = (int) $jobID;
		if (($errors = $this->checkAccess(null, $jobID)) !== true)
			return $errors;

		return $this->jobManager->removeJob($jobID);
	}

	/**
	 * Carrega a lista dos logs de um Job
	 * @param int $jobID O ID do job
	 * @return array Lista dos logs
	 * @access public
	 */
	function loadLogs($jobID)
	{
		$jobID = (int) $jobID;
		if (($errors = $this->checkAccess(null, $jobID)) !== true)
			return $errors;

		return $this->jobManager->getLogsByJobID($jobID);
	}

	/**
	 * Alterna o status do Job entre ativado e desativado
	 * @param int $jobID O ID do job
	 * @return array Lista de possíveis erros
	 * @access public
	 */
	function toggleActive($jobID)
	{
		$jobID = (int) $jobID;
		if (($errors = $this->checkAccess(null, $jobID)) !== true)
			return $errors;

		$jobInfo = $this->jobManager->getJob($jobID);
		$newStatus = ($jobInfo['active'] != 't');
		$this->jobManager->setActive($jobID, $newStatus);
		return array('error' => $this->jobManager->get_error(true));
	}

	/**
	 * Executa um determinado Job
	 * @param int $jobID O ID do job
	 * @return array Uma array contendo a saída da execução do Job (saída padrão e saída de erro) e outras mensagens
	 * @access public
	 */
	function runJob($jobID)
	{
		$jobID = (int) $jobID;
		if (($errors = $this->checkAccess(null, $jobID)) !== true)
			return $errors;

		$output = array();
		$job = $this->jobManager->getJob($jobID);
		$jobScheduler =& Factory::getInstance('JobScheduler');
		$totalTime = microtime(true);
		$thread = $jobScheduler->execute($job, true);
		while ($thread->isActive())
			usleep(50000);
		$totalTime = microtime(true) - $totalTime;

		$output['output']['messages'] = array();
		$output['output']['messages'][] = 'Tempo de execução: ' . number_format($totalTime, 4) . 's (o tempo máximo permitido é de ' . JobScheduler::MAXIMUM_EXECUTION_TIME/1000000.0 . 's)';
		$output['output']['default'] = htmlentities($thread->listen());
		$output['output']['error'] = htmlentities($thread->getError());

		if ($thread->isActive())
			$thread->kill();
		else
			$thread->close();

		if (strpos($output['output']['error'], 'PHP Fatal error') !== false)
			$this->jobManager->writeLog($jobID, $jobScheduler->getCurrentDate(), $output['output']['error'], JobManager::STATUS_ERROR);

		return $output;
	}
}
?>
