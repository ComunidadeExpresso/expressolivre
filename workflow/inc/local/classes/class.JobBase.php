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
	 * @var string $currentDate Uma string cujo conte�do � um data e hor�rio devidamente formatados
	 * @access private
	 */
	private $currentDate;

	/**
	 * @var object $jobManager Objeto da classe JobManager
	 * @access private
	 */
	private $jobManager;

	/**
	 * @var bool $userLog Indica se o usu�rio gravou algum log (true) ou n�o (false)
	 * @access private
	 */
	private $userLog;

	/**
	 * @var float $maximumExecutionTime O tempo m�ximo de execu��o do Job
	 * @access private
	 */
	private $maximumExecutionTime;

	/**
	 * @var float $timeStart In�cio do Job (em microsegundos)
	 * @access private
	 */
	private $timeStart;

	/**
	 * @var bool $testMode Indica se o job est� sendo executado em modo de teste (true) ou n�o (false)
	 * @access protected
	 */
	protected $testMode;

	/**
	 * @var array $environment Cont�m algumas vari�veis/objetos relevantes para a execu��o de alguns Jobs
	 * @access protected
	 */
	protected $environment;

	/**
	 * Construtor da classe JobBase
	 * @param int $jobID O ID do Job que ser� atualizado
	 * @param int $processID O ID do processo a qual o Job pertence
	 * @param string $currentDate Uma string cujo conte�do � um data e hor�rio devidamente formatados
	 * @param bool $testMode Indica se o job est� sendo executado em modo de teste (true) ou n�o (false)
	 * @param float $maximumExecutionTime O tempo m�ximo de execu��o do Job (em segundos)
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
	 * Indica que a execu��o do Job falhou
	 * @param string $message A mensagem que ser� armazenada
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
	 * Indica que a execu��o do Job foi bem sucedida
	 * @param string $message A mensagem que ser� armazenada
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
	 * Grava uma mensagem de log padr�o (caso o desenvolvedor n�o chame os m�todos "fail" ou "success")
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
	 * Prepara o ambiente para a execu��o do Job
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
	 * Procedimentos que s�o executados na finaliza��o do Job
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
	 * Pega o tempo de execu��o do Job (at� o momento)
	 * @return float O tempo de execu��o do Job
	 * @final
	 * @access public
	 */
	final public function getExecutionTime()
	{
		return microtime(true) - $this->timeStart;
	}

	/**
	 * Pega o tempo m�ximo de execu��o do Job
	 * @return float O tempo m�ximo de execu��o do Job
	 * @final
	 * @access public
	 */
	final public function getMaximumExectuionTime()
	{
		return $this->maximumExecutionTime;
	}

	/**
	 * Cria uma nova inst�ncia do processo e encaminha para a pr�xima atividade
	 * @param int $startActivityId ID da atividade Start que ser� usada para criar a inst�ncia
	 * @param string $instanceName Identificador da inst�ncia
	 * @param array $properties Array associativo contendo as popriedades da inst�ncia
	 * @param int $user uidNumer do usu�rio do dono da inst�ncia (usu�rio que gerou a inst�ncia)
	 * @return bool true se a inst�ncia foi criada, caso contr�rio, false.
	 * @final
	 * @access public
	 */
	final public function createNewInstance($startActivityId, $instanceName=false, $properties=false, $user=false)
	{
		$activityManager = Factory::getInstance('workflow_activitymanager');
		$activity = $activityManager->get_activity($startActivityId);

		// Verifica se a atividade existe e � uma atividade de start
		if ($activity['wf_type'] != 'start')
			return false;

		// Verifica se existe uma transi��o da atividade de start para outra atividade
		$transition = $activityManager->get_process_transitions($this->processID, $startActivityId);
		$nextActivityId = $transition[0]['wf_act_to_id'];
		if (empty($nextActivityId))
			return false;

		// Captura objeto runtime da globals
		$runtime = &$GLOBALS['workflow']['wf_runtime'];

		// TODO: Pegar usu�rio padr�o da tabela phpgw_config
		$defaultUser = 73231;

		// Seta usu�rio para execu��o. Se $user for false, seta usu�rio padr�o do job
		$uidNumber = ($user)? $user : $defaultUser;
		if (empty($uidNumber)) return false;

		$GLOBALS['phpgw_info']['user']['account_id'] = $uidNumber;
		$_SESSION['phpgw_info']['workflow']['account_id'] = $uidNumber;
		$GLOBALS['user'] = $uidNumber;

		if (!empty($instanceName) && is_string($instanceName))
			$runtime->setName($instanceName);

		if (!empty($properties) && is_array($properties))
			$runtime->setProperties($properties);

		// Cria a inst�ncia
		if ($runtime->instance->complete($startActivityId)){
			// Envia inst�ncia para pr�xima transi��o
			$runtime->instance->sendTo($startActivityId, $nextActivityId);
			return true;
		}
		return false;
	}

	/**
	 * � o m�todo que cont�m o c�digo do Job que ser� executado
	 * @return void
	 * @abstract
	 * @access public
	 */
	abstract public function run();
}
?>
