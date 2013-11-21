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
 * Classe para datas absolutas e com repetições comuns
 * @author Sidnei Augusto Drovetto Junior - drovetto@gmail.com
 * @version 1.0
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package Workflow
 * @subpackage Job
 */
class AbsoluteDate extends JobDate
{
	/**
	 * Verifica se um Job será executado na data fornecida
	 * @param object $checkDate A data que será verificada
	 * @return bool True caso o Job deva ser executado e false caso contrário
	 * @access public
	 */
	public function checkMatchesInterval($checkDate)
	{
		$preCheck = array();

		switch($this->interval['unity'])
		{
			case DateUnity::NONE:
				$preCheck[] = 'Y';
			case DateUnity::YEAR:
				$preCheck[] = 'n';
			case DateUnity::MONTH:
				$preCheck[] = 'j';
			case DateUnity::DAY:
				$preCheck[] = 'G';
			case DateUnity::HOUR:
				$preCheck[] = 'i';
		}

		$preCheck = implode(':', $preCheck);
		if ($checkDate->format($preCheck) !== $this->startDate->format($preCheck))
			return false;

		if ($this->interval['unity'] == DateUnity::NONE)
			return true;

		if ($this->interval['unity'] == DateUnity::MINUTE)
		{
			$start = $this->startDate->format('U') / 60;
			$check = $checkDate->format('U') / 60;
			return (((($check - $start) % $this->interval['value']) === 0) && ($check >= $start));
		}

		if ($this->interval['unity'] == DateUnity::HOUR)
		{
			$start = $this->startDate->format('U') / 3600;
			$check = $checkDate->format('U') / 3600;
			return (((($check - $start) % $this->interval['value']) === 0) && ($check >= $start));
		}

		if ($this->interval['unity'] == DateUnity::DAY)
		{
			$start = $this->startDate->format('U') / 86400;
			$check = $checkDate->format('U') / 86400;
			return (((($check - $start) % $this->interval['value']) === 0) && ($check >= $start));
		}

		if ($this->interval['unity'] == DateUnity::MONTH)
		{
			$start = ($this->startDate->format('Y') * 12) + $this->startDate->format('n');
			$check = ($checkDate->format('Y') * 12) + $checkDate->format('n');
			return (((($check - $start) % $this->interval['value']) === 0) && ($check >= $start));
		}

		if ($this->interval['unity'] == DateUnity::YEAR)
		{
			$start = $this->startDate->format('Y');
			$check = $checkDate->format('Y');
			return (((($check - $start) % $this->interval['value']) === 0) && ($check >= $start));
		}

		return false;
	}
}
?>
