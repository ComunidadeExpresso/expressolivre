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

require_once 'jobs/class.JobEnum.inc.php';

/**
 * Classe que verifica se um Job deve ser executado neste momento. E, em caso positivo, dispara sua execução
 * @author Sidnei Augusto Drovetto Junior - drovetto@gmail.com
 * @version 1.0
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package Workflow
 * @subpackage Job
 */
class JobScheduler
{
	/**
	 * @var object $currentDate A data corrente (truncada nos minutos)
	 * @access private
	 */
	private $currentDate;

	/**
	 * @var object $db Objeto de conexão com o Banco de Dados
	 * @access private
	 */
	private $db;

	/**
	 * @var object $jobManager Objeto da classe JobManager
	 * @access private
	 */
	private $jobManager;

	/**
	 * Tempo máximo de execução de um Job (em microsegundos)
	 * @name MAXIMUM_EXECUTION_TIME
	 */
	const MAXIMUM_EXECUTION_TIME = 900000000;

	/**
	 * Construtor da classe JobScheduler
	 * @return object Objeto da classe JobScheduler
	 * @access public
	 */
	function JobScheduler()
	{
		$this->currentDate = new DateTime(date('Y-n-j G:i:00'));
		$this->db = &Factory::getInstance('WorkflowObjects')->getDBGalaxia()->Link_ID;
		$this->jobManager = &Factory::getInstance('WorkflowJobManager');
	}

	/**
	 * Gera um objeto de data (dos Jobs) a partir de um registro de banco de dados
	 * @param array $record Um registro da tabela de jobs
	 * @return object Um objeto de data
	 * @access private
	 */
	private function convertRecordToDateObject($record)
	{
		$interval = array('value' => $record['interval_value'], 'unity' => $record['interval_unity']);
		$startDate = new DateTime($record['time_start']);

		$object = null;
		switch ($record['date_type'])
		{
			case DateType::ABSOLUTE_DATE:
				$object = &Factory::newInstance('AbsoluteDate', $startDate, $interval);
				break;

			case DateType::WEEK_DATE:
				$object = &Factory::newInstance('WeekDate', $startDate, $interval);
				$object->setWeekDays($record['week_days']);
				break;

			case DateType::RELATIVE_DATE:
				$object = &Factory::newInstance('RelativeDate', $startDate, $interval);
				$object->setOffset($record['month_offset']);
				break;
		}

		return $object;
	}

	/**
	 * Verifica os jobs que devem ser executados e solicita sua execução
	 * @return void
	 * @access public
	 */
	public function run()
	{
		$records = $this->db->query('SELECT job.job_id, job.wf_process_id, job.name, job.time_start, job.interval_value, job.interval_unity, job.date_type, job.week_days, job.month_offset FROM egw_wf_jobs job, egw_wf_processes process WHERE job.active AND (job.wf_process_id = process.wf_p_id) AND (wf_is_active = \'y\')')->getArray();
		$jobs = array();
		foreach ($records as $record)
			if ($this->convertRecordToDateObject($record)->checkMatchesInterval($this->currentDate))
				$jobs[] = $record;

		$runningJobs = array_map(array($this, 'execute'), $jobs);

		$numerOfJobs = count($runningJobs);
		$timeLeft = JobScheduler::MAXIMUM_EXECUTION_TIME;
		$timeStep = 50000;
		do
		{
			$active = false;
			for ($i = 0; ($i < $numerOfJobs) && !$active; ++$i)
				$active = $active || $runningJobs[$i]->isActive();
			usleep($timeStep);
			$timeLeft -= $timeStep;
			if ($timeLeft <= 0)
				$active = false;
		}
		while ($active);

		for ($i = 0; $i < $numerOfJobs; ++$i)
		{
			if (!$runningJobs[$i]->isActive())
			{
				if (strpos(($errors = $runningJobs[$i]->getError()), 'PHP Fatal error') !== false)
					$this->jobManager->writeLog($jobs[$i]['job_id'], $this->currentDate, $errors, JobManager::STATUS_ERROR);
				$runningJobs[$i]->close();
			}
			else
			{
				$runningJobs[$i]->kill();
				$this->jobManager->writeLog($jobs[$i]['job_id'], $this->currentDate, 'O Job foi abortado por ultrapassar o limite do tempo de execução (atualmente em: ' . (JobScheduler::MAXIMUM_EXECUTION_TIME / 1000000) . 's)', JobManager::STATUS_FAIL);
			}
		}
	}

	/**
	 * Dispara a execução de um Job
	 * @param array $job Um registro da tabela de jobs
	 * @param bool $testMode Indica se o Job será executado em modo de teste (true) ou não (false)
	 * @return object Um objeto da classe Thread que gerencia a execução do Job
	 * @access public
	 */
	public function execute($job, $testMode = false)
	{
		if (($job['interval_unity'] == DateUnity::NONE) && (!$testMode))
		{
			$disable = true;
			if ($job['date_type'] == DateType::WEEK_DATE)
				if (WeekDate::getWeekDay($this->currentDate) <= ($job['week_days'] - WeekDate::getWeekDay($this->currentDate)))
					$disable = false;

			if ($disable)
				$records = $this->db->query('UPDATE egw_wf_jobs SET active = FALSE WHERE job_id = ?', array($job['job_id']));
		}

		$parameters = array();
		$parameters['file'] = $this->jobManager->getJobFile($job['job_id']);
		$parameters['jobID'] = $job['job_id'];
		$parameters['processID'] = $job['wf_process_id'];
		$parameters['currentDate'] = $this->currentDate->format('Y-m-d H:i:00');
		$parameters['className'] = $this->jobManager->getClassName($job['job_id']);
		$parameters['maximumExecutionTime'] = JobScheduler::MAXIMUM_EXECUTION_TIME / 1000000;
		$parameters['testMode'] = $testMode;
		if(!empty($job['parameters']))
			$parameters['jobParams'] = $job['parameters'];

		$parameters = base64_encode(serialize($parameters));

		$previousDir = getcwd();
		chdir(GALAXIA_LIBRARY . '/../');
		$output = Factory::newInstance('Thread', 'class.JobRunner.inc.php "' . $parameters . '"');
		chdir($previousDir);
		return $output;
	}

	/**
	 * Pega a data atual
	 * @return object A data atual
	 * @access public
	 */
	public function getCurrentDate()
	{
		return $this->currentDate;
	}
}

/* se este arquivo é executado a partir da linha de comando, executa os
 * jobs que estão programados para execução no momento da chamada */
if (php_sapi_name() == 'cli')
{
	require_once 'common.inc.php';
	Factory::getInstance('WorkflowMacro')->prepareEnvironment();

	$job = Factory::newInstance('JobScheduler');
	$job->run();
}
?>
