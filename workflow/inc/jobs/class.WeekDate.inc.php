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

require_once 'class.JobDate.inc.php';

/**
 * Classe para dias da semana
 * @author Sidnei Augusto Drovetto Junior - drovetto@gmail.com
 * @version 1.0
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package Workflow
 * @subpackage Job
 */
class WeekDate extends JobDate
{
	/**
	 * @var int $weekDays Inteiro que representa os dias da semana quando o Job será executado
	 * @access private
	 */
	private $weekDays;

	/**
	 * Construtor da classe WeekDate
	 * @param object $startDate A data a partir da qual o Job é válido
	 * @param array $interval O intervalo de execução do Job
	 * @param int $weekDays Inteiro que representa os dias da semana
	 * @return object Objeto da classe WeekDate
	 * @access public
	 */
	public function WeekDate($startDate, $interval, $weekDays = null)
	{
		parent::JobDate($startDate, $interval);
		if (!is_null($weekDays))
			$this->setWeekDays($weekDays);
	}

	public function checkMatchesInterval($checkDate)
	{
		if (($checkDate->format('G:i') == $this->startDate->format('G:i')) && ($this->weekDays & $this->getWeekDay($checkDate)))
		{
			$startSunday = new DateTime(date($this->startDate->format('Y-n-j') . ' 00:00:00'));
			if ($this->getWeekDay($startSunday) != WeekDays::SUNDAY)
				$startSunday->modify('-' . $startSunday->format('N') . ' day');

			$checkSunday = new DateTime(date($checkDate->format('Y-n-j') . ' 00:00:00'));
			if ($this->getWeekDay($checkSunday) != WeekDays::SUNDAY)
				$checkSunday->modify('-' . $checkSunday->format('N') . ' day');

			$weeksBetween = round(($checkSunday->format('U') - $startSunday->format('U')) / 604800);

			if ($this->interval['unity'] == DateUnity::NONE)
				return ($weeksBetween == 0);

			return ((($weeksBetween % $this->interval['value']) === 0) && ($checkDate->format('U') >= $this->startDate->format('U')));
		}

		return false;
	}

	/**
	 * Pega o inteiro que representa os dias da semana quando o Job será executado
	 * @return int $weekDays Inteiro que representa os dias da semana
	 * @access public
	 */
	public function getWeekDays($weekDays)
	{
		return $this->weekDays;
	}

	/**
	 * Define o inteiro que representa os dias da semana quando o Job será executado
	 * @param int $weekDays Inteiro que representa os dias da semana
	 * @return void
	 * @access public
	 */
	public function setWeekDays($weekDays)
	{
		$this->weekDays = $weekDays;
	}

	/**
	 * Pega o dia da semana de uma data
	 * @param object $date A data
	 * @return int O inteiro que representa o dia da semana da data
	 * @access public
	 */
	static public function getWeekDay($date)
	{
		switch ($date->format('N'))
		{
			case 1:
				return WeekDays::MONDAY;

			case 2:
				return WeekDays::TUESDAY;

			case 3:
				return WeekDays::WEDNESDAY;

			case 4:
				return WeekDays::THURSDAY;

			case 5:
				return WeekDays::FRIDAY;

			case 6:
				return WeekDays::SATURDAY;

			case 7:
				return WeekDays::SUNDAY;
		}
	}
}
?>
