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

require_once 'class.JobEnum.inc.php';

/**
 * Classe base dos tipos de datas (e intervalos) utilizadas pelos Jobs
 * @author Sidnei Augusto Drovetto Junior - drovetto@gmail.com
 * @version 1.0
 * @abstract
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package Workflow
 * @subpackage Job
 */
abstract class JobDate
{
	/**
	 * @var object A data a partir da qual o Job � v�lido
	 * @access protected
	 */
	protected $startDate;

	/**
	 * @var array O intervalo de execu��o do Job. � uma array associativa com os elementos 'unity' e 'value'
	 * @access protected
	 */
	protected $interval;

	/**
	 * Construtor da classe JobDate
	 * @param object $startDate A data a partir da qual o Job � v�lido
	 * @param array $interval O intervalo de execu��o do Job
	 * @return object Objeto da classe JobDate
	 * @access public
	 */
	public function JobDate($startDate, $interval)
	{
		$this->startDate = $startDate;
		$this->setInterval($interval);
	}

	/**
	 * Define o intervalo de execu��o do Job
	 * @param array $interval O intervalo de execu��o do Job
	 * @return void
	 * @access public
	 */
	public function setInterval($interval)
	{
		$this->interval = $interval;
	}

	/**
	 * Verifica se um Job ser� executado na data fornecida
	 * @param object $checkDate A data que ser� verificada
	 * @return bool True caso o Job deva ser executado e false caso contr�rio
	 * @access public
	 * @abstract
	 */
	abstract public function checkMatchesInterval($checkDate);
}
?>
