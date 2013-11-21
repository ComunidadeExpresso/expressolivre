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

require_once 'class.bo_ajaxinterface.inc.php';

/**
 * Camada Business para administrar Jobs
 * @package Workflow
 * @author Sidnei Augusto Drovetto Jr. - drovetto@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 */
class bo_adminjobs extends bo_ajaxinterface
{
	/**
	 * @var object $so Acesso à camada model.
	 * @access private
	 */
	private $so;

	/**
	 * Construtor da classe bo_adminjobs
	 * @return object
	 * @access public
	 */
	function bo_adminjobs()
	{
		parent::bo_ajaxinterface();
		$this->so = &Factory::getInstance('so_adminjobs');
	}

	/**
	 * Carrega a lista de todos os jobs de um determinado processo.
	 * @param array $params Uma array contendo os parâmetros advindos da chamada Ajax
	 * @return array Lista dos jobs (ordenada por nome)
	 * @access public
	 */
	function loadJobs($params)
	{
		$output = $this->so->loadJobs($params['processID']);
		if (isset($output['error']))
			return $output;

		usort($output, create_function('$a,$b', 'return strcasecmp($a[\'name\'],$b[\'name\']);'));
		return $output;
	}

	/**
	 * Atualiza um job ou cria um novo job (dependendo dos parâmetros passados)
	 * @param array $params Uma array contendo os parâmetros advindos da chamada Ajax
	 * @return array Lista dos jobs (ordenada por nome)
	 * @access public
	 */
	function saveJob($params)
	{
		$output = array();
		if (!empty($params['jobID']))
			$output = $this->so->updateJob($params['jobID'], $params['processID'], $params['name'], $params['description'], $params['timeStart'], $params['intervalValue'], $params['intervalUnity'], $params['dateType'], $params['weekDays'], $params['monthOffset'], $params['active']);
		else
			$output = $this->so->createJob($params['processID'], $params['name'], $params['description'], $params['timeStart'], $params['intervalValue'], $params['intervalUnity'], $params['dateType'], $params['weekDays'], $params['monthOffset'], $params['active']);

		if (count($output['error']) === 0)
			return $this->loadJobs(array('processID' => $params['processID']));
		else
			return $output;
	}

	/**
	 * Remove um job (e seus logs)
	 * @param array $params Uma array contendo os parâmetros advindos da chamada Ajax
	 * @return void
	 * @access public
	 */
	function removeJob($params)
	{
		return $this->so->removeJob($params['jobID']);
	}

	/**
	 * Carrega a lista dos logs de um Job
	 * @param array $params Uma array contendo os parâmetros advindos da chamada Ajax
	 * @return array Lista dos logs
	 * @access public
	 */
	function loadLogs($params)
	{
		$logs = $this->so->loadLogs($params['jobID']);
		if (isset($logs['error']))
			return $logs;

		/* paginate the result */
		$logEntriesPerPage = 30;
		$paging =& Factory::newInstance('Paging', $logEntriesPerPage, $params);
		$logs = $paging->restrictItems($logs);

		/* use a more human readable time notation (today, yesterday) */
		$date = new DateTime();
		$writtenDates = array();
		$writtenDates[$date->format('d/m/Y')] = 'Hoje';
		$date->modify('-1 day');
		$writtenDates[$date->format('d/m/Y')] = 'Ontem';
		foreach ($logs as $key => $value)
		{
			$logs[$key]['date_time'] = preg_replace('/([[:digit:]]{4})-([[:digit:]]{2})-([[:digit:]]{2}) ([[:digit:]]{2}):([[:digit:]]{2}):00/', '\3/\2/\1 \4h\5', $value['date_time']);
			$currentDate = $value['date_time'];
			if (isset($writtenDates[substr($logs[$key]['date_time'], 0, 10)]))
				$logs[$key]['human_date_time'] = $writtenDates[substr($logs[$key]['date_time'], 0, 10)] . substr($logs[$key]['date_time'], 10);
		}

		/* prepare the output */
		$output = array();
		$output['logs'] = $logs;
		$output['pagingLinks'] = $paging->commonLinks();
		return $output;
	}

	/**
	 * Alterna o status do Job entre ativado e desativado
	 * @param array $params Uma array contendo os parâmetros advindos da chamada Ajax
	 * @return array Lista dos jobs (ordenada por nome)
	 * @access public
	 */
	function toggleActive($params)
	{
		$jobID = (int) $params['jobID'];
		$output = $this->so->toggleActive($jobID);
		if (count($output['error']) === 0)
			return $this->loadJobs(array('processID' => (int) $params['processID']));
		else
			return $output;
	}

	/**
	 * Executa um determinado Job
	 * @param array $params Uma array contendo os parâmetros advindos da chamada Ajax
	 * @return array Uma array contendo a saída da execução do Job (saída padrão e saída de erro) e outras mensagens
	 * @access public
	 */
	function runJob($params)
	{
		$jobID = (int) $params['jobID'];
		return $this->so->runJob($jobID);
	}
}
?>
