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
 * Classe respons�vel pela execu��o dos Jobs
 * @package Workflow
 * @author Sidnei Augusto Drovetto Jr. - drovetto@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @subpackage Job
 */
class JobRunner
{
	/**
	 * @var array $parameters Par�metros passados pela linha de comando
	 * @access private
	 */
	private $parameters;

	/**
	 * @var object $jobManager Objeto da classe JobManager
	 * @access private
	 */
	private $jobManager;

	/**
	 * Construtor da classe JobRunner
	 * @param array $parameters Par�metros passados pela linha de comando
	 * @return object Objeto da classe JobRunner
	 * @access public
	 */
	public function JobRunner($parameters)
	{
		ini_set('display_errors', false);
		ini_set('log_errors', true);
		$this->parameters = unserialize(base64_decode($parameters));
		$this->parameters['currentDate'] = new DateTime($this->parameters['currentDate']);
		$this->jobManager =& Factory::getInstance('WorkflowJobManager');
	}

	/**
	 * Indica que a execu��o do Job falhou (este Status de falha implica que o Job n�o foi executado). Ap�s a execu��o deste m�todo, a execu��o do PHP � encerrada
	 * @param string $message A mensagem que ser� armazenada
	 * @return void
	 * @access private
	 */
	private function fail($message)
	{
		$this->jobManager->writeLog($this->parameters['jobID'], $this->parameters['currentDate'], $message, JobManager::STATUS_FAIL);
		exit;
	}

	/**
	 * Executa o Job
	 * @return void
	 * @access public
	 */
	public function run()
	{
		/* activate the security policy */
		Factory::getInstance('WorkflowSecurity')->enableSecurityPolicy();

		/**
		 * Since all jobs must run in process mode, e. g. it's user code,
		 * we must enable the security.
		 */
		Security::enable();

		if (!file_exists($this->parameters['file']))
			$this->fail('Arquivo contendo o c�digo do Job n�o foi encontrado');

		require_once dirname(__FILE__) . '/local/classes/class.JobBase.php';
		require_once $this->parameters['file'];

		if (!class_exists($this->parameters['className']))
			$this->fail('A classe "' . $this->parameters['className'] . '" n�o foi encontrada');

		$job = new $this->parameters['className']($this->parameters['jobID'], $this->parameters['processID'], $this->parameters['currentDate'], $this->parameters['testMode'], $this->parameters['maximumExecutionTime']);

		if (!is_subclass_of($job, 'JobBase'))
			$this->fail('A classe "' . $this->parameters['className'] . '" n�o est� estendendo a classe JobBase');

		$GLOBALS['workflow']['job']['processID'] = $this->parameters['processID'];
		$job->run(!empty($this->parameters['jobParams']) ? $this->parameters['jobParams'] : null);
		$job->finalize();
	}
}

/* esta classe s� pode ser executada a partir da linha de comando.
 * Al�m disso, ela exige um par�metro (que � uma string serializada e codificada em base64) */
if ((php_sapi_name() !== 'cli') || (!isset($argv[1])))
	exit;

require_once 'common.inc.php';
Factory::getInstance('WorkflowMacro')->prepareEnvironment();

$jobRunner = &Factory::newInstance('JobRunner', $argv[1]);
$jobRunner->run();
?>
