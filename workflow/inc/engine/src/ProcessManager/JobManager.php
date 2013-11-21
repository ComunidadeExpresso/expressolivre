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

require_once GALAXIA_LIBRARY . '/src/ProcessManager/BaseManager.php';
require_once GALAXIA_LIBRARY . '/src/ProcessManager/ProcessManager.php';
require_once GALAXIA_LIBRARY . '/../jobs/class.JobEnum.inc.php';

/**
 * Classe para gerenciamento de Jobs
 * @package Workflow
 * @author Sidnei Augusto Drovetto Jr. - drovetto@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @subpackage Job
 */
class JobManager extends BaseManager
{
	/**
	 * @var string $jobTable O nome da tabela de Jobs (normalmente: egw_wf_jobs)
	 * @access private
	 */
	private $jobTable;

	/**
	 * @var string $logTable O nome da tabela de Log dos Jobs (normalmente: egw_wf_job_logs)
	 * @access private
	 */
	private $logTable;

	/**
	 * @var object $processManager Objeto da classe ProcessManager
	 * @access private
	 */
	private $processManager;

	/**
	 * Status de Job executado com sucesso (o usu�rio chama o m�todo informando o sucesso)
	 * @name STATUS_JOB_SUCCESS
	 */
	const STATUS_JOB_SUCCESS = 0;

	/**
	 * Status de Job executado com falha (quando o usu�rio chama o m�todo de falha)
	 * @name STATUS_JOB_FAIL
	 */
	const STATUS_JOB_FAIL = 1;

	/**
	 * Status de Job que apresentou falha. A classe JobRunner faz algumas verifica��es e, pode detectar alguns problemas e impedir a execu��o do Job (atribuindo este Status ao log do Job)
	 * @name STATUS_FAIL
	 */
	const STATUS_FAIL = 2;

	/**
	 * Status de Job quando ocorre algum erro Fatal do PHP na execu��o do Job. A descri��o do Job � a o erro gerado pelo PHP
	 * @name STATUS_ERROR
	 */
	const STATUS_ERROR = 3;

	/**
	 * Status de Job que � aplicado quando n�o h� erros e o c�digo do usu�rio n�o informa se a execu��o foi bem sucedida ou n�o
	 * @name STATUS_UNKNOWN
	 */
	const STATUS_UNKNOWN = 4;

	/**
	 * Construtor da classe JobManager
	 * @return object
	 * @access public
	 */
	public function JobManager()
	{
		parent::BaseManager();
		$this->child_name = 'JobManager';

		$this->jobTable = GALAXIA_TABLE_PREFIX . 'jobs';
		$this->logTable = GALAXIA_TABLE_PREFIX . 'job_logs';
		$this->processManager = &Factory::newInstance('ProcessManager');
	}

	/**
	 * Gera um nome normalizado a partir de um nome de Job
	 * @param string $name O nome do Job
	 * @return string O valor de $name normalizado
	 * @access private
	 */
	private function normalize($name)
	{
		$search = array('/[�-�]/', '/�/', '/�/', '/[�-�]/', '/[�-�]/', '/�/', '/�/', '/[�-��]/', '/�/', '/[�-�]/', '/�/', '/�/', '/[�-�]/', '/�/', '/�/', '/[�-�]/', '/[�-�]/', '/�/', '/�/', '/[�-��]/', '/�/', '/[�-�]/', '/[�-�]/');
		$replace = array('A', 'AE', 'C', 'E', 'I', 'D', 'N', 'O', 'X', 'U', 'Y', 'ss', 'a', 'ae', 'c', 'e', 'i', 'd', 'n', 'o', 'x', 'u', 'y');
		$output = str_replace(' ', '', ucwords(preg_replace($search, $replace, $name)));
		$output = preg_replace("/[^0-9A-Za-z\_\-]/", '', $output);

		return $output;
	}

	/**
	 * Valida os par�metros para atualiza��o e cria��o de jobs
	 * @param string $name O nome do Job
	 * @param string $timeStart Uma string cujo conte�do � um data e hor�rio devidamente formatados
	 * @param int $intervalValue O intervalo de repeti��o
	 * @param int $intervalUnity A unidade de repeti��o (dia, m�s, etc.)
	 * @param int $dateType O tipo que define quando o Job � executado
	 * @param int $weekDays Inteiro que representa os dias da semana em que o Job ser� executado
	 * @param int $monthOffset Intervalo de execu��o relativa a m�s
	 * @param bool $active Indica se o job est� ativo (true) ou n�o (false)
	 * @param int $processID O ID do processo a qual o Job pertence
	 * @param int $jobID O ID do Job (no caso de atualiza��o)
	 * @return bool Indica se os par�metros foram aceitos (true) ou n�o (false)
	 * @access private
	 */
	private function validateJobParameters($name, $timeStart, $intervalValue, $intervalUnity, $dateType, $weekDays, $monthOffset, $active, $processID, $jobID = null)
	{
		$normalizedName = $this->normalize($name);
		if (strlen($normalizedName) < 1)
			$this->error[] = 'Este processo produz um nome normalizado que � vazio. Troque o nome do job.';

		list($date, $time) = explode(' ', $timeStart, 2);
		if (strlen($date) != 10)
			$this->error[] = 'A data est� num formato inv�lido. Utilize: dd/mm/aaaa.';

		list($year, $month, $day) = explode('-', $date, 3);
		if (!checkdate($month, $day, $year))
			$this->error[] = 'A data fornecida n�o � v�lida.';

		list($hour, $minute, $second) = explode(':', $time, 3);
		if (($hour < 0) || ($hour > 23) || ($minute < 0) || ($minute > 59))
			$this->error[] = 'A hor�rio informado n�o � v�lido.';

		if ((!is_numeric($intervalValue)) || (strpos($intervalValue, '.') !== false) || (strpos($intervalValue, ',') !== false) || (($intervalValue < 1) && ($intervalUnity != DateUnity::NONE)))
			$this->error[] = 'O intervalo de tempo para repeti��o precisa ser um n�mero inteiro positivo.';

		switch ($dateType)
		{
			case DateType::ABSOLUTE_DATE:
				if (!in_array($intervalUnity, array(DateUnity::YEAR, DateUnity::MONTH, DateUnity::WEEK, DateUnity::DAY, DateUnity::HOUR, DateUnity::MINUTE, DateUnity::NONE)))
					$this->error[] = 'A unidade de intervalo utilizada � inv�lida.';
				break;

			case DateType::WEEK_DATE:
				if (($weekDays < 0) || ($weekDays > 127))
					$this->error[] = 'Os dias da semana selecionados geraram n�o formam uma possibilidade v�lida.';
				break;

			case DateType::RELATIVE_DATE:
				if ($monthOffset < 1)
					$this->error[] = 'O deslocamento relativo ao m�s seguinte n�o possui um valor v�lido.';
				break;
		}

		/* a new job will be created */
		$allJobs = $this->getJobsByProcessID($processID);

		foreach ($allJobs as $currentJob)
		{
			if ($currentJob['job_id'] == $jobID)
				continue;
			if (strcasecmp($this->normalize($currentJob['name']), $normalizedName) == 0)
			{
				$this->error[] = 'O nome escolhido entra em conflito com outro job do mesmo processo.';
				break;
			}
		}

		return (count($this->error) === 0);
	}

	/**
	 * Busca informa��es de todos os Jobs de um processo
	 * @param int $processID O ID do processo
	 * @return array A lista de Jobs de um processo
	 * @access public
	 */
	public function getJobsByProcessID($processID)
	{
		return $this->query("SELECT * FROM {$this->jobTable} WHERE wf_process_id = ?", array($processID))->getArray();
	}

	/**
	 * Obt�m o arquivo de c�digo do Job
	 * @param int $jobID O ID do Job
	 * @return string O arquivo do Job
	 * @access public
	 */
	public function getJobFile($jobID)
	{
		$jobInfo = $this->getJob($jobID);
		$processInfo = $this->processManager->get_process($jobInfo['wf_process_id']);
		return GALAXIA_PROCESSES . '/' . $processInfo['wf_normalized_name'] . '/code/jobs/' . 'class.job.' . $this->normalize($jobInfo['name']) . '.php';
	}

	/**
	 * Obt�m o nome da classe de um Job
	 * @param int $jobID O ID do Job
	 * @return string O nome da classe do Job
	 * @access public
	 */
	public function getClassName($jobID)
	{
		$jobInfo = $this->getJob($jobID);
		return $this->normalize($jobInfo['name']);
	}

	/**
	 * Obt�m informa��es sobre um Job
	 * @param int $jobID O ID do Job
	 * @return array Informa��es sobre o Job
	 * @access public
	 */
	public function getJob($jobID)
	{
		return $this->query("SELECT * FROM {$this->jobTable} WHERE job_id = ?", array($jobID))->fetchRow();
	}

	/**
	 * Define se um Job est� ativo ou n�o
	 * @param int $jobID O ID do Job
	 * @param bool $active Informa se o Job ser� ativado (true) ou n�o (false)
	 * @return bool True em caso de sucesso ou false caso contr�rio
	 * @access public
	 */
	public function setActive($jobID, $active)
	{
		$jobID = (int) $jobID;
		$active = $active ? 'TRUE' : 'FALSE';
		return $this->query("UPDATE {$this->jobTable} SET active = ? WHERE job_id = ?", array($active, $jobID));
	}

	/**
	 * Busca os logs de um Job
	 * @param int $jobID O ID do Job
	 * @return array Os logs do Job
	 * @access public
	 */
	public function getLogsByJobID($jobID)
	{
		return $this->query("SELECT * FROM {$this->logTable} WHERE job_id = ? ORDER BY date_time DESC", array($jobID))->getArray();
	}

	/**
	 * Grava uma entrada de log para um Job
	 * @param int $jobID O ID do Job
	 * @param object $currentDate Um objeto da classe DateTime contendo a data em que o Job foi executado
	 * @param string $message A mensagem que ser� salva no Log
	 * @param int $status O status do log
	 * @return void
	 * @access public
	 */
	public function writeLog($jobID, $currentDate, $message, $status)
	{
		$message = str_replace(array(chr(92), chr(0), chr(39)), array('\134', '\000', '\047'), $message);
		$this->db->StartTrans();

		/* remove any previous log for the same time */
		$query = "DELETE FROM {$this->logTable} WHERE (job_id = ?) AND (date_time = TO_TIMESTAMP(?, 'YYYY-MM-DD HH24:MI:SS'))";
		$values = array($jobID, $currentDate->format('Y-m-d H:i:00'));
		$this->query($query, $values);

		/* insert the new log */
		$query = "INSERT INTO {$this->logTable}(job_id, date_time, result, status) VALUES(?, TO_TIMESTAMP(?, 'YYYY-MM-DD HH24:MI:SS'), ?, ?)";
		$values = array($jobID, $currentDate->format('Y-m-d H:i:00'), $message, $status);
		$this->query($query, $values);

		$this->db->CompleteTrans();
	}

	/**
	 * Cria e grava um arquivo modelo do Job
	 * @param int $jobID O ID do Job
	 * @return void
	 * @access public
	 */
	private function createModelFile($jobID)
	{
		$className = $this->getClassName($jobID);
		$contents = "<?php\nclass {$className} extends JobBase\n{\n\tpublic function run()\n\t{\n\t\t/* c�digo que ser� executado */\n\t}\n}\n?>";
		$this->setJobFileContent($jobID, $contents);
	}

	/**
	 * Define o conte�do de um arquivo de Job
	 * @param int $jobID O ID do Job
	 * @param string $contents O conte�do do arquivo
	 * @return bool True se o arquivo foi criado corretamente ou false caso contr�rio
	 * @access public
	 */
	public function setJobFileContent($jobID, $contents)
	{
		$jobFile = $this->getJobFile($jobID);
		if (!is_dir(($jobDirectory = dirname($jobFile))))
			mkdir($jobDirectory, 0770);

		$success = @file_put_contents($jobFile, $contents);
		if ($success === false)
			$this->error[] = "N�o foi poss�vel definir o conte�do do arquivo de Job. Crie manualmente o arquivo:\n{$jobFile}";

		return $success;
	}

	/**
	 * Cria um novo Job
	 * @param int $processID O ID do processo a qual o Job pertence
	 * @param string $name O nome do Job
	 * @param string $description A descri��o do Job
	 * @param string $timeStart Uma string cujo conte�do � um data e hor�rio devidamente formatados
	 * @param int $intervalValue O intervalo de repeti��o
	 * @param int $intervalUnity A unidade de repeti��o (dia, m�s, etc.)
	 * @param int $dateType O tipo que define quando o Job � executado
	 * @param int $weekDays Inteiro que representa os dias da semana em que o Job ser� executado
	 * @param int $monthOffset Intervalo de execu��o relativa a m�s
	 * @param bool $active Indica se o job est� ativo (true) ou n�o (false)
	 * @return bool True em caso de sucesso ou false caso os par�metros n�o sejam v�lidos
	 * @access public
	 */
	public function createJob($processID, $name, $description, $timeStart, $intervalValue, $intervalUnity, $dateType, $weekDays, $monthOffset, $active)
	{
		if ($this->validateJobParameters($name, $timeStart, $intervalValue, $intervalUnity, $dateType, $weekDays, $monthOffset, $active, $processID) === false)
			return false;

		switch ($dateType)
		{
			case DateType::ABSOLUTE_DATE:
				$weekDays = $monthOffset = null;
				break;

			case DateType::WEEK_DATE:
				$monthOffset = null;
				break;

			case DateType::RELATIVE_DATE:
				$weekDays = null;
				break;
		}

		$query = "INSERT INTO {$this->jobTable}(wf_process_id, name, description, time_start, interval_value, interval_unity, date_type, week_days, month_offset, active) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
		$values = array($processID, $name, $description, $timeStart, $intervalValue, $intervalUnity, $dateType, $weekDays, $monthOffset, $active);
		$this->query($query, $values);
		$row = $this->query("SELECT job_id FROM {$this->jobTable} WHERE wf_process_id = ? AND name = ?", array($processID, $name))->fetchRow();
		$this->createModelFile($row['job_id']);

		return true;
	}

	/**
	 * Atualiza um Job
	 * @param int $jobID O ID do Job que ser� atualizado
	 * @param int $processID O ID do processo a qual o Job pertence
	 * @param string $name O nome do Job
	 * @param string $description A descri��o do Job
	 * @param string $timeStart Uma string cujo conte�do � um data e hor�rio devidamente formatados
	 * @param int $intervalValue O intervalo de repeti��o
	 * @param int $intervalUnity A unidade de repeti��o (dia, m�s, etc.)
	 * @param int $dateType O tipo que define quando o Job � executado
	 * @param int $weekDays Inteiro que representa os dias da semana em que o Job ser� executado
	 * @param int $monthOffset Intervalo de execu��o relativa a m�s
	 * @param bool $active Indica se o job est� ativo (true) ou n�o (false)
	 * @return bool True em caso de sucesso ou false caso os par�metros n�o sejam v�lidos
	 * @access public
	 */
	public function updateJob($jobID, $processID, $name, $description, $timeStart, $intervalValue, $intervalUnity, $dateType, $weekDays, $monthOffset, $active)
	{
		if ($this->validateJobParameters($name, $timeStart, $intervalValue, $intervalUnity, $dateType, $weekDays, $monthOffset, $active, $processID, $jobID) === false)
			return false;

		$oldJob = $this->getJob($jobID);
		$oldJobFile = $this->getJobFile($jobID);
		$oldJobClassName = $this->getClassName($jobID);

		switch ($dateType)
		{
			case DateType::ABSOLUTE_DATE:
				$weekDays = $monthOffset = null;
				break;

			case DateType::WEEK_DATE:
				$monthOffset = null;
				break;

			case DateType::RELATIVE_DATE:
				$weekDays = null;
				break;
		}
		$query = "UPDATE {$this->jobTable} SET name = ?, description = ?, time_start = ?, interval_value = ?, interval_unity = ?, date_type = ?, week_days = ?, month_offset = ?, active = ? WHERE job_id = ?";
		$values = array($name, $description, $timeStart, $intervalValue, $intervalUnity, $dateType, $weekDays, $monthOffset, $active, $jobID);

		$this->query($query, $values);

		if (!file_exists($oldJobFile))
		{
			$this->createModelFile($jobID);
		}
		else
		{
			if ($oldJob['name'] != $name)
			{
				$oldFileContents = file_get_contents($oldJobFile);
				$newFileContents = str_replace("class {$oldJobClassName}", 'class ' . $this->getClassName($jobID), $oldFileContents, $count);
				if ($count == 0)
					$this->error[] = '� necess�rio trocar manualmente o nome da classe. O nome que deve ser utilizado �: ' . $this->getClassName($jobID) . '.';
				file_put_contents($this->getJobFile($jobID), $newFileContents);
				unlink($oldJobFile);
			}
		}

		return true;
	}

	/**
	 * Cria ou atualiza um Job. Este m�todo � utilizado na cria��o de Jobs ocorrida na importa��o de um processo
	 * @param int $processID O ID do processo
	 * @param int $jobID O ID do Job. Se for 0 (zero) indica que deve ser criado um novo processo
	 * @param array $params Os par�metros para cria��o/atualiza��o de um processo
	 * @return int O ID do Job criado/atualizado
	 * @access public
	 */
	public function replaceJob($processID, $jobID, $params)
	{
		$name = $params['name'];
		$description = $params['description'];
		$timeStart = $params['timeStart'];
		$intervalValue = $params['intervalValue'];
		$intervalUnity = $params['intervalUnity'];
		$dateType = $params['dateType'];
		$weekDays = $params['weekDays'];
		$monthOffset = $params['monthOffset'];
		$active = 'f';
		$fileContents = $params['fileContents'];

		if ($jobID === 0)
		{
			$this->createJob($processID, $name, $description, $timeStart, $intervalValue, $intervalUnity, $dateType, $weekDays, $monthOffset, $active);
			$jobID = $this->getOne("SELECT MAX(job_id) FROM {$this->jobTable} WHERE (wf_process_id = ?)", array($processID));
		}
		else
		{
			$this->updateJob($jobID, $processID, $name, $description, $timeStart, $intervalValue, $intervalUnity, $dateType, $weekDays, $monthOffset, $active);
		}
		$this->setJobFileContent($jobID, $fileContents);

		return $jobID;
	}

	/**
	 * Remove um Job e seus logs
	 * @param int $jobID O ID do Job
	 * @return void
	 * @access public
	 */
	public function removeJob($jobID)
	{
		$jobID = (int) $jobID;
		@unlink($this->getJobFile($jobID));
		$this->query("DELETE FROM {$this->jobTable} WHERE job_id = ?", array($jobID));
		$this->query("DELETE FROM {$this->logTable} WHERE job_id = ?", array($jobID));
	}

	/**
	 * Remove os Jobs e logs de um processo
	 * @param int $processID O ID do processo
	 * @return void
	 * @access public
	 */
	public function removeJobsByProcessID($processID)
	{
		$jobs = $this->getJobsByProcessID($processID);
		foreach ($jobs as $job)
			$this->removeJob($job['job_id']);
	}
}
?>
