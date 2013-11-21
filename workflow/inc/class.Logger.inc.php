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

require_once 'log/Log.php';

/**
 * Classe de logs de dentro dos processos e módulo
 * @package Workflow
 * @author Guilherme Striquer Bisotto - gbisotto@celepar.pr.gov.br
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @subpackage Job
 */
class Logger
{
	/**
	 * @var object $db Objeto de banco de dados já conectado
	 * @access private
	 */
	private $db = null;

	/**
	 * @var object $db Objeto de banco de dados do modulo workflow
	 * @access private
	 */
	private $dbGalaxia = null;

	/**
	 * @var string $logPath Caminho onde será salvo o arquivo de log, caso o tipo de log Log_file seja adicionado à composição
	 * @access private
	 */
	private $logPath = null;

	/**
	 * @var string $logFileName Nome do arquivo de log, caso o tipo de log Log_file seja adicionado à composição
	 * @access private
	 */
	private $logFileName = 'workflow.log';

	/**
	 * @var string $curProcessName Nome do processo normalizado, caso o objeto de log seja criado por um processo
	 * @access private
	 */
	private $curProcessName = null;

	/**
	 * @var array $logsAllowed Logs permitidos no frontend
	 * @access private
	 */
	private $logsAllowed = array();

	/**
	 * @var array $logTypes Array com ponteiros para os objetos de log adicionados à composição
	 * @access private
	 */
	private $logTypes = array();

	/**
	 * @var object $log Objeto de log do tipo Log_composite
	 * @access private
	 */
	private $log = null;

	/**
	 * @var int $logLevel nível de log atualmente configurado
	 * @access protected
	 */
	protected $logLevel = null;

	/**
	 * Construtor da classe Logger
	 * @param array $logTypes array com os tipos de logs que se deseja gerar
	 * @param string $curProcessName nome normalizado do processo que utilizará o log
	 * @return object Objeto da classe Logger
	 * @access public
	 */
	public function Logger($logTypes = 'file', $curProcessId = null, $curProcessName = null)
	{

		$this->db =& Factory::getInstance('WorkflowObjects')->getDBExpresso()->Link_ID;
		$this->dbGalaxia =& Factory::getInstance('WorkflowObjects')->getDBGalaxia()->Link_ID;

		if(!empty($curProcessName)){

			$query = '
				SELECT
					wf_normalized_name
				FROM
					egw_wf_processes';

			if(!($result = $this->dbGalaxia->query($query)))
				throw new Exception(lang('Cannot execute query'));

			while($row = $result->fetchRow())
				$processNames[] = $row['wf_normalized_name'];

			if(!in_array($curProcessName, $processNames))
				throw new Exception(lang('Cannot find the process provided'));

			$this->curProcessName = $curProcessName;
		}

		$this->configure($curProcessId);

		if(!is_array($logTypes) && !empty($logTypes))
			$logTypes = array($logTypes);

		$this->log =& Log::factory('composite');
		foreach($logTypes as $logType){
			$this->addLogType($logType);
		}
	}

	/**
	 * Configura o objeto de acordo com o contexto
	 * @params int $curProcessId O Id do processo corrente
	 * @return null
	 * @access private
	 */
	private function configure($curProcessId = null)
	{
		if(!empty($curProcessId)){
			$query = "
					SELECT
						wf_config_name, wf_config_value_int
					FROM
						egw_wf_process_config
					WHERE
						wf_p_id = $curProcessId
					AND
						wf_config_name like 'log_level'
						";
			$config_value = array_pop($this->dbGalaxia->query($query)->GetArray(-1));
			$this->logLevel = $config_value['wf_config_value_int'];
		} else {
			$query = "
					SELECT
						config_name, config_value
					FROM
						phpgw_config
					WHERE
						config_app like 'workflow'
					AND
						config_name like 'log_level'
						";

			$result = array_pop($this->db->query($query)->GetArray());
			$this->logLevel = $result['config_value'];
		}

		$query = "
			SELECT
				config_name, config_value
			FROM
				phpgw_config
			WHERE
				config_app like 'workflow'
			AND
				config_name like 'log_type_%'
				";
		$result = $this->db->query($query);
		while($row = $result->fetchRow()){
			if($row['config_value'] == 'True'){
				$this->logsAllowed[] = str_replace('log_type_', '', $row['config_name']);
			}
		}
	}
	/**
	 * Configura e salva um objeto de log
	 * @params string logType tipo de log a ser criado
	 * @return object Objeto da classe Log_($logType) ou null caso o tipo de log não exista ou não seja permitido no frontend
	 * @access private
	 */
	private function setLogType($logType)
	{
		if(in_array($logType, $this->logsAllowed)){
			$logMethod = 'setLog_'.$logType;
			if(method_exists($this, $logMethod))
				$this->logTypes[$logType] =& $this->$logMethod();
		}
		return $this->logTypes[$logType];
	}

	/**
	 * Adiciona um tipo de log à composição de logs caso ela já não tenha sido adicionada
	 * @params string $logType tipo de log a ser adicionado
	 * @return boolean true no caso do tipo de log ter sido adicionado com sucesso, false caso contrário
	 * @access public
	 */
	public function addLogType($logType)
	{
		if(!in_array($logType, array_keys($this->logTypes))){
			$log =& $this->setLogType($logType);
			if(is_a($log, "Log_$logType")){
				return $this->log->addChild($log);
			}
		}
		return false;
	}

	/**
	 * Remove um tipo de log da composição de logs
	 * @params $logType tipo de log a ser removido
	 * @return object Objeto Log_($logType) removido, ou null caso não tenha removido qualquer um
	 * @access public
	 */
	public function removeLogType($logType)
	{
		if(in_array($logType ,array_keys($this->logTypes)) && count($this->logTypes) > 1){
			$this->log->removeChild($this->logTypes[$logType]);
			$logRemoved =& $this->logTypes[$logTypes];
			unset($this->logTypes[$logType]);

			if(count($this->logTypes) == 1){
				unset($this->log);
				$this->log =& $this->logTypes[0];
			}
		} else {
			return null;
		}
		return $logRemoved;
	}

	/**
	 * Configura o log do tipo file
	 * @return object Objeto do tipo Log_file
	 * @access private
	 */
	private function setLog_file()
	{
		if(!empty($this->curProcessName)){
			$this->logPath = GALAXIA_PROCESSES.SEP.$this->curProcessName.SEP.'logs';
		} else {
			$this->logPath = GALAXIA_PROCESSES.SEP.'logs';
		}

		$conf = array('mode' => 0600, 'timeFormat' => '%X %x');
		$log =& Log::factory('file', $this->logPath.SEP.$this->logFileName, $this->curProcessName, $conf, $this->logLevel);
		return $log;
	}

	/**
	 * Configura o log do tipo firebug
	 * @return object Objeto do tipo Log_firebug
	 * @access private
	 */
	private function setLog_firebug()
	{
		$log =& Log::factory('firebug', '', $this->curProcessName, array('buffering' => true), $this->logLevel);
		return $log;
	}

	/**
	 * Grava um log do nível emergência (sistema inoperante)
	 * @params string $message Mensagem a ser gravada do log
	 * @return boolean true em caso de sucesso e false caso contrário
	 * @access public
	 */
	public function emerg($message)
	{
		return $this->log->log($message, PEAR_LOG_EMERG);
	}

	/**
	 * Grava um log do nível alerta (ação imediata requerida)
	 * @params string $message Mensagem a ser gravada do log
	 * @return boolean true em caso de sucesso e false caso contrário
	 * @access public
	 */
	public function alert($message)
	{
		return $this->log->log($message, PEAR_LOG_ALERT);
	}

	/**
	 * Grava um log do nível crítico (situações críticas)
	 * @params string $message Mensagem a ser gravada do log
	 * @return boolean true em caso de sucesso e false caso contrário
	 * @access public
	 */
	public function crit($message)
	{
		return $this->log->log($message, PEAR_LOG_CRIT);
	}

	/**
	 * Grava um log do nível erro (situações de erro)
	 * @params string $message Mensagem a ser gravada do log
	 * @return boolean true em caso de sucesso e false caso contrário
	 * @access public
	 */
	public function err($message)
	{
		return $this->log->log($message, PEAR_LOG_ERR);
	}

	/**
	 * Grava um log do nível aviso (condições de aviso - warning)
	 * @params string $message Mensagem a ser gravada do log
	 * @return boolean true em caso de sucesso e false caso contrário
	 * @access public
	 */
	public function warning($message)
	{
		return $this->log->log($message, PEAR_LOG_WARNING);
	}

	/**
	 * Grava um log do nível notificação (situações normais, mas significantes)
	 * @params string $message Mensagem a ser gravada do log
	 * @return boolean true em caso de sucesso e false caso contrário
	 * @access public
	 */
	public function notice($message)
	{
		return $this->log->log($message, PEAR_LOG_NOTICE);
	}

	/**
	 * Grava um log do nível informação (informacional)
	 * @params string $message Mensagem a ser gravada do log
	 * @return boolean true em caso de sucesso e false caso contrário
	 * @access public
	 */
	public function info($message)
	{
		return $this->log->log($message, PEAR_LOG_INFO);
	}

	/**
	 * Grava um log do nível debug
	 * @params string $message Mensagem a ser gravada do log
	 * @return boolean true em caso de sucesso e false caso contrário
	 * @access public
	 */
	public function debug($message)
	{
		return $this->log->log($message, PEAR_LOG_DEBUG);
	}
}

?>
