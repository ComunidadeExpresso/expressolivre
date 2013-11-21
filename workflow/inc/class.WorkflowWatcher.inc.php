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

require_once 'common.inc.php';

/**
 * Classe utilizada para protejer objetos do código dos processos. Essa classe atua como um intermediário entre o objeto protegido e o trecho de código que tenta acessar um de seus métodos/atributos.
 * @author Sidnei Augusto Drovetto Jr. - drovetto@gmail.com
 * @version 1.0
 * @package Workflow
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 */
class WorkflowWatcher
{
	/**
	 * @var object $protectedObject O objeto que está sendo protegido
	 * @access private
	 */
	private $protectedObject;

	/**
	 * @var string $currentDir O diretório a partir do qual, os arquivos de código podem utilizar o objeto protegido
	 * @access private
	 */
	private $currentDir;

	/**
	 * @var int $currentDirLength O número de caracteres de $currentDir
	 * @access private
	 */
	private $currentDirLength;

	/**
	 * @var bool $enabledSecurity Indica se a segurança está ativa ou não
	 * @access private
	 */
	private static $enabledSecurity = false;

	/**
	 * Verifica se o arquivo que está acessando um método/atributo do objeto possui permissão para tal
	 * @param int $index O índice da pilha de chamadas de métodos e funções que deve ser verificado para se encontrar a origem da chamada ao objeto protegido
	 * @return bool true caso o objeto possa ser acessado ou false caso contrário
	 * @access private
	 */
	private final function workflowWatcherCheckSecurity($index = 2)
	{
		if (self::$enabledSecurity == false)
			return true;

		/* busca a pilha das chamadas de métodos/funções */
		$debugBacktrace = debug_backtrace();

		/* checa se o acesso ao objeto será permitido ou não */
		if ((!isset($debugBacktrace[$index]['file'])) || (substr($debugBacktrace[$index]['file'], 0, $this->currentDirLength) === $this->currentDir))
			return true;
		else
			return false;
	}

	/**
	 * Aplica as diretivas de segurança do módulo
	 * @return void
	 * @access public
	 */
	public final function WorkflowWatcher($object)
	{
		$this->protectedObject = $object;
		$this->currentDir = dirname(__FILE__);
		$this->currentDir = substr($this->currentDir, 0, -13);
		$this->currentDirLength = strlen($this->currentDir);
	}

	/**
	 * Ativa a verificação de acesso ao objeto protegido. Por padrão a segurança fica desabilitada pois ela só é interessante antes da execução do código dos processos e, sendo assim, só é habilitada nestes casos.
	 * @return void
	 * @access public
	 */
	public final static function workflowWatcherEnableSecurity()
	{
		self::$enabledSecurity = true;
	}

	/**
	 * Implementação do método mágico "__call" que intercepta as chamadas ao objeto protegido
	 * @param string $methodName O nome do método que está sendo requisitado
	 * @param array $arguments Os parâmetros passados para o método
	 * @return mixed O resultado da chamada do método ou false se o código que o método não tiver permissão para tal
	 * @access public
	 */
	public final function __call($methodName, $arguments)
	{
		if (!$this->workflowWatcherCheckSecurity())
			return false;

		$method = new ReflectionMethod($this->protectedObject, $methodName);
		return $method->invokeArgs($this->protectedObject, $arguments);
	}

	/**
	 * Implementação do método mágico "__set" que intercepta definições de atributos do objeto protegido
	 * @param string $name O nome do atributo ao qual está sendo atribuído um valor
	 * @param mixed $value O valor que será atribuído ao atributo do objeto
	 * @return void
	 * @access public
	 */
	public final function __set($name, $value)
	{
		if (!$this->workflowWatcherCheckSecurity(1))
			return false;

		$this->protectedObject->{$name} = $value;
	}

	/**
	 * Implementação do método mágico "__get" que intercepta leitura de atributos do objeto potegido
	 * @param string $name O nome do atributo que se está tentando acessar
	 * @return mixed O valor do atributo que se está acessando. Ou false, caso o acesso ao atributo seja negado
	 * @access public
	 */
	public final function __get($name)
	{
		if (!$this->workflowWatcherCheckSecurity(1))
			return false;

		return $this->protectedObject->{$name};
	}
}
?>
