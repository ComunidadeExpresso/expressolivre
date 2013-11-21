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
 * Classe que implementa, de forma simples, uma thread
 * @package Workflow
 * @author Brian W. Bosh
 * @author Sidnei Augusto Drovetto Jr. - drovetto@gmail.com (minor modifications)
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 */
class Thread
{
	/**
	 * @var resource $processReference Referência ao processo em execução
	 * @access private
	 */
	private $processReference;

	/**
	 * @var array $pipes Pipes do processo
	 * @access private
	 */
	private $pipes;

	/**
	 * @var string $buffer Buffer da saída padrão
	 * @access private
	 */
	private $buffer;

	/**
	 * @var float $timeStart Início da execução do processo (em microsegundos)
	 * @access private
	 */
	private $timeStart;

	/**
	 * @var float $timeEnd Fim da execução do processo (em microsegundos)
	 * @access private
	 */
	private $timeEnd;

	/**
	 * Construtor da classe Thread
	 * @param string $file O arquivo PHP que será executado
	 * @return object Objeto da classe Thread
	 * @access public
	 */
	public function Thread($file)
	{
		$this->timeEnd = null;
		$this->timeStart = microtime(true);
		$this->buffer = '';
		$this->pipes = array();

		$descriptor = array(
			0 => array('pipe', 'r'),
			1 => array('pipe', 'w'),
			2 => array('pipe', 'w')
		);

		$this->processReference = proc_open("php -q {$file} ", $descriptor, $this->pipes);
		stream_set_blocking($this->pipes[1], 0);
	}

	/**
	 * Indica se o processo está ativo ou não
	 * @return bool Indica se o processo está ativo (true) ou não (false)
	 * @access public
	 */
	public function isActive()
	{
		$this->buffer .= $this->listen();
		$f = stream_get_meta_data($this->pipes[1]);
		return !$f['eof'];
	}

	/**
	 * Libera o recurso do processo (só deve ser chamado quando o processo não estiver mais ativo)
	 * @return int O código de saída do processo
	 * @access public
	 */
	public function close()
	{
		$this->timeEnd = microtime(true);
		fclose($this->pipes[0]);
		fclose($this->pipes[1]);
		fclose($this->pipes[2]);
		$output = proc_close($this->processReference);
		$this->processReference = NULL;
		return $output;
	}

	/**
	 * Mata o processo
	 * @return int O código de finalização do processo
	 * @access public
	 */
	public function kill()
	{
		$this->timeEnd = microtime(true);
		fclose($this->pipes[0]);
		fclose($this->pipes[1]);
		fclose($this->pipes[2]);
		$output = proc_terminate($this->processReference);
		$this->processReference = null;
		return $output;
	}

	/**
	 * Envia mensagens ao processo
	 * @param string $thought A mensagem a ser enviada
	 * @return void
	 * @access public
	 */
	public function tell($thought)
	{
		fwrite($this->pipes[0], $thought);
	}

	/**
	 * Lê o buffer da saída padrão do processo
	 * @return string O conteúdo do buffer da saída padrão
	 * @access public
	 */
	public function listen()
	{
		$buffer = $this->buffer;
		$this->buffer = '';
		while ($r = fgets($this->pipes[1], 1024))
			$buffer .= $r;

		return $buffer;
	}

	/**
	 * Lê o buffer da saída de erro do processo
	 * @return string O conteúdo do buffer da saída de erro
	 * @access public
	 */
	public function getError()
	{
		$buffer = '';
		while ($r = fgets($this->pipes[2], 1024))
			$buffer .= $r;

		return $buffer;
	}

	/**
	 * Informa o tempo de execução do processo (até o momento)
	 * @return float O tempo de execução do processo
	 * @access public
	 */
	public function getExecutionTime()
	{
		if (!is_null($this->timeEnd))
			return $this->timeEnd - $this->timeStart;
		else
			return microtime(true) - $this->timeStart;
	}
}
?>
