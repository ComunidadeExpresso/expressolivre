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
 * Classe utilizada para protejer objetos do c�digo dos processos. Essa classe atua como um intermedi�rio entre o objeto protegido e o trecho de c�digo que tenta acessar um de seus m�todos/atributos.
 * @author Sidnei Augusto Drovetto Jr. - drovetto@gmail.com
 * @version 1.0
 * @package Workflow
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 */
class WorkflowWatcher
{
	/**
	 * @var object $protectedObject O objeto que est� sendo protegido
	 * @access private
	 */
	private $protectedObject;

	/**
	 * @var string $currentDir O diret�rio a partir do qual, os arquivos de c�digo podem utilizar o objeto protegido
	 * @access private
	 */
	private $currentDir;

	/**
	 * @var int $currentDirLength O n�mero de caracteres de $currentDir
	 * @access private
	 */
	private $currentDirLength;

	/**
	 * @var bool $enabledSecurity Indica se a seguran�a est� ativa ou n�o
	 * @access private
	 */
	private static $enabledSecurity = false;

	/**
	 * Verifica se o arquivo que est� acessando um m�todo/atributo do objeto possui permiss�o para tal
	 * @param int $index O �ndice da pilha de chamadas de m�todos e fun��es que deve ser verificado para se encontrar a origem da chamada ao objeto protegido
	 * @return bool true caso o objeto possa ser acessado ou false caso contr�rio
	 * @access private
	 */
	private final function workflowWatcherCheckSecurity($index = 2)
	{
		if (self::$enabledSecurity == false)
			return true;

		/* busca a pilha das chamadas de m�todos/fun��es */
		$debugBacktrace = debug_backtrace();

		/* checa se o acesso ao objeto ser� permitido ou n�o */
		if ((!isset($debugBacktrace[$index]['file'])) || (substr($debugBacktrace[$index]['file'], 0, $this->currentDirLength) === $this->currentDir))
			return true;
		else
			return false;
	}

	/**
	 * Aplica as diretivas de seguran�a do m�dulo
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
	 * Ativa a verifica��o de acesso ao objeto protegido. Por padr�o a seguran�a fica desabilitada pois ela s� � interessante antes da execu��o do c�digo dos processos e, sendo assim, s� � habilitada nestes casos.
	 * @return void
	 * @access public
	 */
	public final static function workflowWatcherEnableSecurity()
	{
		self::$enabledSecurity = true;
	}

	/**
	 * Implementa��o do m�todo m�gico "__call" que intercepta as chamadas ao objeto protegido
	 * @param string $methodName O nome do m�todo que est� sendo requisitado
	 * @param array $arguments Os par�metros passados para o m�todo
	 * @return mixed O resultado da chamada do m�todo ou false se o c�digo que o m�todo n�o tiver permiss�o para tal
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
	 * Implementa��o do m�todo m�gico "__set" que intercepta defini��es de atributos do objeto protegido
	 * @param string $name O nome do atributo ao qual est� sendo atribu�do um valor
	 * @param mixed $value O valor que ser� atribu�do ao atributo do objeto
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
	 * Implementa��o do m�todo m�gico "__get" que intercepta leitura de atributos do objeto potegido
	 * @param string $name O nome do atributo que se est� tentando acessar
	 * @return mixed O valor do atributo que se est� acessando. Ou false, caso o acesso ao atributo seja negado
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
