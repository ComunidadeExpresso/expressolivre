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
 * Classe que enumera os tipos de data
 * @author Sidnei Augusto Drovetto Junior - drovetto@gmail.com
 * @version 1.0
 * @final
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package Workflow
 * @subpackage Job
 */
final class DateType
{
	/**
	 * Representa o tipo de data Absoluta
	 * @name ABSOLUTE_DATE
	 */
	const ABSOLUTE_DATE = 0;

	/**
	 * Representa o tipo de data da Semana
	 * @name ABSOLUTE_DATE
	 */
	const WEEK_DATE = 1;

	/**
	 * Representa o tipo de data Relativa ao final do mês
	 * @name RELATIVE_DATE
	 */
	const RELATIVE_DATE = 2;
}

/**
 * Classe que enumera os tipos de unidades de intervalo
 * @author Sidnei Augusto Drovetto Junior - drovetto@gmail.com
 * @version 1.0
 * @final
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package Workflow
 * @subpackage Job
 */
final class DateUnity
{
	/**
	 * Unidade de intervalo que representa Ano
	 * @name YEAR
	 */
	const YEAR = 0;

	/**
	 * Unidade de intervalo que representa Mês
	 * @name MONTH
	 */
	const MONTH = 1;

	/**
	 * Unidade de intervalo que representa Semana
	 * @name WEEK
	 */
	const WEEK = 2;

	/**
	 * Unidade de intervalo que representa Dia
	 * @name DAY
	 */
	const DAY = 3;

	/**
	 * Unidade de intervalo que representa Hora
	 * @name HOUR
	 */
	const HOUR = 4;

	/**
	 * Unidade de intervalo que representa Minuto
	 * @name HOUR
	 */
	const MINUTE = 5;

	/**
	 * Representa a não repetição de jobs
	 * @name NONE
	 */
	const NONE = 6;
}

/**
 * Classe que enumera os dias da semana
 * @author Sidnei Augusto Drovetto Junior - drovetto@gmail.com
 * @version 1.0
 * @final
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package Workflow
 * @subpackage Job
 */
final class WeekDays
{
	/**
	 * Representa o domingo
	 * @name SUNDAY
	 */
	const SUNDAY = 1;

	/**
	 * Representa a segunda-feira
	 * @name MONDAY
	 */
	const MONDAY = 2;

	/**
	 * Representa a terça-feira
	 * @name TUESDAY
	 */
	const TUESDAY = 4;

	/**
	 * Representa a quarta-feira
	 * @name WEDNESDAY
	 */
	const WEDNESDAY = 8;

	/**
	 * Representa a quinta-feira
	 * @name THURSDAY
	 */
	const THURSDAY = 16;

	/**
	 * Representa a sexta-feira
	 * @name FRIDAY
	 */
	const FRIDAY = 32;

	/**
	 * Representa o sábado
	 * @name SATURDAY
	 */
	const SATURDAY = 64;
}
?>
