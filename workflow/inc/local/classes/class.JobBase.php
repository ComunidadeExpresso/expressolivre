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
 * Classe base dos Jobs
 * @author Sidnei Augusto Drovetto Junior - drovetto@gmail.com
 * @version 1.0
 * @abstract
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package Workflow
 * @subpackage Job
 */
abstract class JobBase
{
	/**
	 * @var int $jobID O ID do Job
	 * @access private
	 */
	private $jobID;

	/**
	 * @var int $processID O ID do Processo
	 * @access private
	 */
	private $processID;

	/**
	 * @var string $currentDate Uma string cujo conteúdo é um data e horário devidamente formatados
	 * @access private
	 */
	private $currentDate;

	/**
	 * @var object $jobManager Objeto da classe JobManager
	 * @access private
	 */
	private $jobManager;

	/**
	 * @var bool $userLog Indica se o usuário gravou algum log (true) ou não (false)
	 * @access private
	 */
	private $userLog;

	/**
	 * @var float $maximumExecutionTime O tempo máximo de execução do Job
	 * @access private
	 */
	private $maximumExecutionTime;

	/**
	 * @var float $timeStart Início do Job (em microsegundos)
	 * @access private
	 */
	private $timeStart;

	/**
	 * @var bool $testMode Indica se o job está sendo executado em modo de teste (true) ou não (false)
	 * @access protected
	 */
	protected $testMode;

	/**
	 * @var array $environment Contém algumas variáveis/objetos relevantes para a execução de alguns Jobs
	 * @access protected
	 */
	protected $environment;

	/**
	 * Construtor da classe JobBase
	 * @param int $jobID O ID do Job que será atualizado
	 * @param int $processID O ID do processo a qual o Job pertence
	 * @param string $currentDate Uma string cujo conteúdo é um data e horário devidamente formatados
	 * @param bool $testMode Indica se o job está sendo executado em modo de teste (true) ou não (false)
	 * @param float $maximumExecutionTime O tempo máximo de execução do Job (em segundos)
	 * @return object Objeto da classe JobBase
	 * @final
	 * @access public
	 */
	final public function JobBase($jobID, $processID, $currentDate, $testMode, $maximumExecutionTime)
	{
		require_once 'common.inc.php';
		Factory::getInstance('WorkflowMacro')->prepareEnvironment();
		$this->jobID = $jobID;
		$this->processID = $processID;
		$this->currentDate = $currentDate;
		$this->jobManager = Factory::getInstance('WorkflowJobManager');
		$this->testMode = $testMode;
		$this->maximumExecutionTime = $maximumExecutionTime;
		$this->timeStart = microtime(true);
		$this->userLog = false;

		$this->prepareEnvironment();
	}

	/**
	 * Indica que a execução do Job falhou
	 * @param string $message A mensagem que será armazenada
	 * @return void
	 * @final
	 * @access public
	 */
	final public function fail($message)
	{
		$this->jobManager->writeLog($this->jobID, $this->currentDate, $message, JobManager::STATUS_JOB_FAIL);
		$this->userLog = true;
	}

	/**
	 * Indica que a execução do Job foi bem sucedida
	 * @param string $message A mensagem que será armazenada
	 * @return void
	 * @final
	 * @access public
	 */
	final public function success($message)
	{
		$this->jobManager->writeLog($this->jobID, $this->currentDate, $message, JobManager::STATUS_JOB_SUCCESS);
		$this->userLog = true;
	}

	/**
	 * Grava uma mensagem de log padrão (caso o desenvolvedor não chame os métodos "fail" ou "success")
	 * @return void
	 * @final
	 * @access private
	 */
	final private function defaultLog()
	{
		$this->jobManager->writeLog($this->jobID, $this->currentDate, 'Job executado', JobManager::STATUS_UNKNOWN);
		$this->userLog = true;
	}

	/**
	 * Prepara o ambiente para a execução do Job
	 * @return void
	 * @final
	 * @access private
	 */
	final private function prepareEnvironment()
	{
		Factory::getInstance('WorkflowMacro')->prepareProcessEnvironment($this->processID);

		$this->environment = array();
		$this->environment['factory']  = &Factory::newInstance('ProcessWrapperFactory');
	}

	/**
	 * Procedimentos que são executados na finalização do Job
	 * @return void
	 * @final
	 * @access private
	 */
	final public function finalize()
	{
		if (!$this->userLog)
			$this->defaultLog();
	}

	/**
	 * Pega o tempo de execução do Job (até o momento)
	 * @return float O tempo de execução do Job
	 * @final
	 * @access public
	 */
	final public function getExecutionTime()
	{
		return microtime(true) - $this->timeStart;
	}

	/**
	 * Pega o tempo máximo de execução do Job
	 * @return float O tempo máximo de execução do Job
	 * @final
	 * @access public
	 */
	final public function getMaximumExectuionTime()
	{
		return $this->maximumExecutionTime;
	}

	/**
	 * Cria uma nova instância do processo e encaminha para a próxima atividade
	 * @param int $startActivityId ID da atividade Start que será usada para criar a instância
	 * @param string $instanceName Identificador da instância
	 * @param array $properties Array associativo contendo as popriedades da instância
	 * @param int $user uidNumer do usuário do dono da instância (usuário que gerou a instância)
	 * @return bool true se a instância foi criada, caso contrário, false.
	 * @final
	 * @access public
	 */
	final public function createNewInstance($startActivityId, $instanceName=false, $properties=false, $user=false)
	{
		$activityManager = Factory::getInstance('workflow_activitymanager');
		$activity = $activityManager->get_activity($startActivityId);

		// Verifica se a atividade existe e é uma atividade de start
		if ($activity['wf_type'] != 'start')
			return false;

		// Verifica se existe uma transição da atividade de start para outra atividade
		$transition = $activityManager->get_process_transitions($this->processID, $startActivityId);
		$nextActivityId = $transition[0]['wf_act_to_id'];
		if (empty($nextActivityId))
			return false;

		// Captura objeto runtime da globals
		$runtime = &$GLOBALS['workflow']['wf_runtime'];

		// TODO: Pegar usuário padrão da tabela phpgw_config
		$defaultUser = 73231;

		// Seta usuário para execução. Se $user for false, seta usuário padrão do job
		$uidNumber = ($user)? $user : $defaultUser;
		if (empty($uidNumber)) return false;

		$GLOBALS['phpgw_info']['user']['account_id'] = $uidNumber;
		$_SESSION['phpgw_info']['workflow']['account_id'] = $uidNumber;
		$GLOBALS['user'] = $uidNumber;

		if (!empty($instanceName) && is_string($instanceName))
			$runtime->setName($instanceName);

		if (!empty($properties) && is_array($properties))
			$runtime->setProperties($properties);

		// Cria a instância
		if ($runtime->instance->complete($startActivityId)){
			// Envia instância para próxima transição
			$runtime->instance->sendTo($startActivityId, $nextActivityId);
			return true;
		}
		return false;
	}

	/**
	 * É o método que contém o código do Job que será executado
	 * @return void
	 * @abstract
	 * @access public
	 */
	abstract public function run();
}
?>
