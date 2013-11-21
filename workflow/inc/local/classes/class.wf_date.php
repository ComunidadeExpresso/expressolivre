<?php
/**
 * Corresponde ao formato de data armazenado na configuração (d/m/Y ou Y/m/d)
 * @name DATE_FORMAT_LOCAL
 */
define('DATE_FORMAT_LOCAL', 'd/m/Y');
/**
 * maior número inteiro
 * @name DATE_FORMAT_LOCAL
 */
define('LONG_MAX', is_int(2147483648) ? 9223372036854775807 : 2147483647);
/**
 * Formato de data customizado
 * @name DATE_FORMAT_LOCAL
 */
define('DATE_FORMAT_CUSTOM', 'CUSTOM');

/**
 * Classe para manipulação e realização de cálculos com datas
 * @author Marcos Pont
 * @author Carlos Eduardo Nogueira Gonçalves
 * @version 1.0
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package Workflow 
 * @subpackage local 
 */
class wf_date
{

	/**
	 * Construtora da classe wf_date
	 * @return object Objeto da classe wf_date
	 * @access public
	 */
	function wf_date()
	{
	}

	/**
	 * Verifica se uma determinada data é válida
	 * @access public
	 * @param string $date Data a ser validada
	 * @return boolean
	 */
	function isValid($date) {
		$regs = array();
		if ($this->isEuroDate($date, $regs)) {
			list(, $day, $month, $year) = $regs;
		} else if ($this->isUsDate($date, $regs) || $this->isSqlDate($date, $regs)) {
			list(, $year, $month, $day) = $regs;
		} else {
			return FALSE;
		}
		if ($year < 0 || $year > 9999) {
			return FALSE;
		} else {
			return (checkdate($month, $day, $year));
		}
	}
	/**
	 *	Verifica se um valor de time zone é válido
	 *	@access public
	 *	@param	string $tz	Valor de time zone
	 *	@return boolean	
	 */	
	function isValidTZ($tz) {
		return preg_match("/^(((\+|\-)[0-9]{2}\:[0-9]{2})|(UT|GMT|EST|EDT|CST|CDT|MST|MDT|PST|PDT)|([A-IK-Y]{1}))$/", $tz);
	}
	
	/**
	 * Verifica se uma data está no formato europeu dd[/-.]mm[/-.]YYYY[ HH:mm:ss]
	 * @param array &$regs Vetor para onde retornam os valores destacados de dia, mês e ano
	 * @access public
	 * @return boolean
	 */
	function isEuroDate($date, &$regs) {
		$date = trim($date);
		if (preg_match('/^([0-9]{1,2})(\/|\-|\.)([0-9]{1,2})(\/|\-|\.)([0-9]{4})([[:space:]]([0-9]{1,2}):([0-9]{1,2}):?([0-9]{1,2})?)?$/', $date, $matches)) {
			$regs = array(
				$matches[0], 
				$matches[1], $matches[3], $matches[5], 
				$matches[7], $matches[8], $matches[9]
			);
			return TRUE;
		}
		return FALSE;
	}
	
	/**
	 * Verifica se uma data está no formato americano YYYY[/-.]mm[/-.]dd[ HH:mm:ss]
	 * @param string $date Data a ser verificada
	 * @param array	&$regs Vetor para onde retornam os valores destacados de dia, mês e ano
	 * @access public
	 * @return boolean
	 */	
	function isUsDate($date, &$regs) {
		$date = trim($date);
		if (preg_match('/^([0-9]{4})(\/|\-|\.)([0-9]{1,2})(\/|\-|\.)([0-9]{1,2})([[:space:]]([0-9]{1,2}):([0-9]{1,2}):?([0-9]{1,2})?)?$/', $date, $matches)) {
			$regs = array(
				$matches[0], 
				$matches[1], $matches[3], $matches[5], 
				$matches[7], $matches[8], $matches[9]
			);
			return TRUE;
		}
		return FALSE;
	}
	
	/**
	 * Verifica se uma data está no formato SQL YYYY-mm-dd
	 * @param string $date Data a ser verificada
	 * @param array &$regs Vetor para onde retornam os valores destacados de dia, mês e ano
	 * @access public
	 * @return boolean
	 */
	function isSqlDate($date, &$regs) {
		$date = trim($date);
		if (preg_match('/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})([[:space:]]([0-9]{1,2}):([0-9]{1,2}):?([0-9]{1,2})?)?$/', $date, $matches)) {
			$regs = array(
				$matches[0], 
				$matches[1], $matches[2], $matches[3], 
				$matches[5], $matches[6], $matches[7]
			);
			return TRUE;			
		}
		return FALSE;
	}
	
	/**
	 * Verifica se uma data é posterior à data atual
	 * @access public
	 * @param string $date Data a ser verificada
	 * @return boolean
	 */
	function isFuture($date) {
		$daysFrom = $this->dateToDays($date);
		$daysTo = $this->dateToDays();
		return ($daysFrom > $daysTo);
	}
	
	/**
	 * Verifica se uma data é anterior à data atual
	 * @access public
	 * @param string date Data a ser verificada
	 * @return boolean
	 */
	function isPast($date) {
		$daysFrom = $this->dateToDays($date);
		$daysTo = $this->dateToDays();
		return ($daysTo > $daysFrom);
	}
	
	/**
	 * Calcula o dia seguinte em relação à data atual
	 * @access public
	 * @return string Data calculada
	 */
	function tomorrow() {
		return $this->nextDay();
	}
	
	/**
	 * Verifica se um determinado valor é NULL
	 * @param mixed $value Valor a testar
	 * @param boolean $strict Considerar o tipo do dado
	 * @access public
	 * @return boolean
	 */
	function isNull($value, $strict = FALSE) {
		return ($strict) ? (NULL === $value) : (NULL == $value);
	}	
	
	/**
	 * Calcula a data imediatamente posterior a uma determinada data
	 * @access public
	 * @param string $date Data base
	 * @return string Dia seguinte calculado
	 */
	function nextDay($date=NULL) {
		if ($this->isNull($date)) {
			$date = $this->localDate();
		}
		return $this->futureDate($date, 1);
	}
	
	/**
	 * Calcula uma data no futuro, a partir de um número de dias, meses e anos
	 * @access public
	 * @param string $date Data original
	 * @param iny $days Número de dias no futuro
	 * @param int $months Número de meses no futuro
	 * @param int $years Número de anos no futuro
	 * @return string Data calculada no formato original
	 */
	function futureDate($date, $days = 0, $months = 0, $years = 0) {
		// Captura o formato e os elementos da data base
		$regs = array();		
		if ($this->isEuroDate($date, $regs)) {
			list(, $day, $month, $year) = $regs;
			$dateFormat = "EURO";
		} else if ($this->isSqlDate($date, $regs)) {
			list(, $year, $month, $day) = $regs;
			$dateFormat = "SQL";
		} else if ($this->isUsDate($date, $regs)) {
			list(, $year, $month, $day) = $regs;
			$dateFormat = "US";
		} else {			
			return NULL;
		}
		// Calcula o número de dias da data original
		$daysFrom = $this->dateToDays($date);
		$daysInc = 0;
		// Adiciona os anos
		$years = intval($years);
		for ($i = 1; $i <= $years; ++$i) {
			++$year;
			$daysInc += ($this->isLeapYear($year)) ? 366 : 365;
		}
		// Adiciona os meses de acordo com o número de dias em cada um
		$months = intval($months);
		for ($i = 1; $i <= $months; ++$i) {
			$mTemp = $i % 12 - 1;
			$yTemp = intval($i / 12);
			if (($month + $mTemp) > 12) {
				++$yTemp;
				$mTemp = ($month + $mTemp) - 12;
			} else {
				$mTemp = $month + $mTemp;
			}
			$daysInc += $this->daysInMonth($mTemp, $year + $yTemp);
		}
		// Adiciona os dias
		$daysInc += intval($days);
		// Retorna a data calculada no formato original
		return $this->daysToDate($daysFrom + $daysInc, $dateFormat);				
	}
	
	/**
	 * Calcula o dia anterior em relação à data atual
	 * @access public
	 * @return string Data calculada
	 */
	function yesterday() {
		return $this->prevDay();
	}
	
	/**
	 * Calcula a data imediatamente anterior a uma determinada data
	 * @access public 
	 * @param string $date Data base
	 * @return string Dia anterior calculado
	 */
	function prevDay($date=NULL) {
		if ($this->isNull($date)) {
			$date = $this->localDate();
		}
		return $this->pastDate($date, 1);
	}
	
	/**
	 * Calcula uma data no passado, a partir de um número de dias, meses e anos
	 * @param string $date Data original
	 * @param int $days Número de dias no passado
	 * @param int $months Número de meses no passado
	 * @param int $years Número de anos no passado
	 * @return string Data calculada no formato original
	 * @access public
	 */
	function pastDate($date, $days=0, $months=0, $years=0) {
		// Captura o formato e os elementos da data base
		$regs = array();
		if ($this->isEuroDate($date, $regs)) {
			list(, $day, $month, $year) = $regs;
			$dateFormat = 'EURO';
		} else if ($this->isSqlDate($date, $regs)) {
			list(, $year, $month, $day) = $regs;
			$dateFormat = 'SQL';
		} else if ($this->isUsDate($date, $regs)) {
			list(, $year, $month, $day) = $regs;
			$dateFormat = 'US';
		} else {
			return NULL;
		}
		// Calcula o número de dias da data original
		$daysFrom = $this->dateToDays($date);
		$daysDec = 0;
		// Adiciona os anos
		for ($i = 1; $i <= $years; ++$i) {
			$s = ($this->isLeapYear($year)) ? 366 : 365;
			$daysDec += ($this->isLeapYear($year)) ? 366 : 365;
			$year--;			
		}		
		// Adiciona os meses de acordo com os dias de cada mês
		for ($i = 1; $i <= $months; ++$i) {
			$mTemp = $i % 12;
			$yTemp = intval($i / 12);
			if (($month - $mTemp) <= 0) {
				++$yTemp;
				$mTemp = 12 + ($month - $mTemp);
			} else {
				$mTemp = $month - $mTemp;
			}
			$daysDec += $this->daysInMonth($mTemp, $year - $yTemp);
		}
		// Adiciona os dias
		$daysDec += $days;
		// Retorna a data calculada no formato original
		return $this->daysToDate($daysFrom - $daysDec, $dateFormat);
	}
	
	/**
	 * Calcula a diferença em dias entre duas datas
	 * @param string $dateM Data 1
	 * @param string $dateS Data 2
	 * @param boolean $unsigned Usar sinal no retorno
	 * @access public
	 */
	function getDiff($dateM, $dateS, $unsigned=TRUE) {
		// Calcula o número de dias da diferença
		$daysS = $this->dateToDays($dateS);
		$daysM = $this->dateToDays($dateM);
		return ($unsigned? abs($daysS - $daysM) : ($daysS - $daysM));
	}
	
	/**
	 * Retorna o dia da semana para uma data
	 * @param string $date Data a ser processada
	 * @param boolean $text Retornar o nome do dia da semana
	 * @param boolean $abbr Retornar o nome do dia da semana abreviadamente
	 * @return mixed Nome ou número do dia da semana (baseado em zero)
	 * @access public 
	 */
	function dayOfWeek($date, $text=TRUE, $abbr=FALSE) {
		// Captura os elementos da data base de acordo com o formato
		$regs = array();
		if ($this->isEuroDate($date, $regs)) {
			list(, $day, $month, $year) = $regs;
		} else if ($this->isUsDate($date, $regs) || $this->isSqlDate($date, $regs)) {
			list(, $year, $month, $day) = $regs;
		} else {
			return NULL;
		}
		// Cálculo do dia da semana
		if ($month > 2) {
			$month -= 2;
		} else {
			$month += 10;
			$year--;
		}
		$dow = (floor((13 * $month - 1) / 5) + $day + ($year % 100) + floor(($year % 100) / 4) + floor(($year / 100) / 4) - 2 * floor($year / 100) + 77);
		$dow = (($dow - 7 * floor($dow / 7)));
		// Exibição do resultado, de acordo com os parâmetros fornecidos
		if ($text) {
			$daysOfWeek = array('segunda', 'terça', 'quarta', 'quinta', 'sexta');
			if ($abbr) {
				return $daysOfWeek[$dow];	
			} else {
				return $daysOfWeek[$dow] . '-feira';
			}					
		} else {
			return $dow;
		}
	}
	
	/**
	 * Retorna o número de dias de um mês de acordo com o mês e o ano
	 * @param int $month Mês
	 * @param int $year Ano
	 * @return int Número de dias do mês solicitado
	 * @access public 
	 */
	function daysInMonth($month=NULL, $year=NULL) {
		if ($this->isNull($year)) 
			$year = date("Y");
		if ($this->isNull($month)) 
			$month = date("m");
		if ($month == 2) {
			return ($this->isLeapYear($year) ? 29 : 28);
		} elseif (in_array($month, array(4, 6, 9, 11))) {
			return 30;
		} else {
			return 31;
		}
	}
	
	/**
	 * Verifica se um determinado ano é bissexto
	 * @param int $year Ano para ser verificado
	 * @access public
	 * @return boolean
	 */
	function isLeapYear($year=NULL) {
		if ($this->isNull($year))
			$year = date("Y");
		if (strlen($year) != 4 || preg_match("/\D/", $year))
			return NULL;
		return ((($year % 4) == 0 && ($year % 100) != 0) || ($year % 400) == 0);
	}	
	
	/**
	 * Converte uma data no padrão europeu (dd/mm/YYYY) para o padrão SQL (YYYY-mm-dd)
	 * @param string $date Data a ser convertida
	 * @param boolean @preserveTime Preservar porção de hora, se ela existir na data fornecida
	 * @return string Data convertida ou a original se o padrão de entrada estiver incorreto
	 * @access public
	 */
	function fromEuroToSqlDate($date, $preserveTime=FALSE) {
		$regs = array();
		if ($this->isEuroDate($date, $regs)) {
			$res = "$regs[3]-$regs[2]-$regs[1]";
			if ($preserveTime && $regs[4] !== FALSE && $regs[5] !== FALSE) {
				$res .= " $regs[4]:$regs[5]";
				if ($regs[6] !== FALSE)
					$res .= ":$regs[6]";
			}
			return $res;
		} else {
			return $date;
		}
	}
	
	/**
	 * Converte uma data no padrão europeu (dd/mm/YYYY)
	 * @param string $date Data a ser convertida
	 * @param boolean $preserveTime Preservar porção de hora, se ela existir na data fornecida
	 * @return string Data convertida ou a original se o padrão de entrada estiver incorreto
	 * @access public
	 */
	function fromEuroToUsDate($date, $preserveTime=FALSE) {
		$regs = array();
		if ($this->isEuroDate($date, $regs)) {			
			$res = "$regs[3]/$regs[2]/$regs[1]";
			if ($preserveTime && $regs[4] !== FALSE && $regs[5] !== FALSE) {
				$res .= " $regs[4]:$regs[5]";
				if ($regs[6] !== FALSE)
					$res .= ":$regs[6]";
			}
			return $res;			
		} else {
			return $date;
		}
	}
	
	/**
	 * Converte uma data no padrã americano (YYYY/mm/dd) para o padrão SQL (YYYY-mm-dd)
	 * @param string $date Data a ser convertida
	 * @return string Data convertida ou a original se o padrão de entrada estiver incorreto
	 * @access public
	 */
	function fromUsToSqlDate($date) {
		$regs = array();
		if ($this->isUsDate($date, $regs)) {
			return str_replace("/", "-", $date);
		} else {
			return $date;
		}
	}
	
	/**
	 * Converte uma data no padrão americano (YYYY/mm/dd) para o padrão europeu (dd/mm/YYYY)
	 * @param string $date Data a ser convertida
	 * @param boolean $preserveTime Preservar porção de hora, se ela existir na data fornecida
	 * @return string Data convertida ou a original se o padrão de entrada estiver incorreto
	 * @access public
	 */
	function fromUsToEuroDate($date, $preserveTime=FALSE) {
		$regs = array();
		if ($this->isUsDate($date, $regs)) {
			$res = "$regs[3]/$regs[2]/$regs[1]";
			if ($preserveTime && $regs[4] !== FALSE && $regs[5] !== FALSE) {
				$res .= " $regs[4]:$regs[5]";
				if ($regs[6] !== FALSE)
					$res .= ":$regs[6]";
			}
			return $res;			
		} else {
			return $date;
		}
	}
	
	/**
	 * Converte uma data no padrão SQL (YYYY-mm-dd) para o padrão europeu (dd/mm/YYYY)
	 * @param string $date	Data a ser convertida
	 * @param boolean preserveTime Preservar porção de hora, se ela existir na data fornecida
	 * @return string Data convertida ou a original se o padrão de entrada estiver incorreto
	 * @access public
	 */
	function fromSqlToEuroDate($date, $preserveTime=FALSE) {
		$regs = array();
		if ($this->isSqlDate($date, $regs)) {
			$res = "$regs[3]/$regs[2]/$regs[1]";
			if ($preserveTime && $regs[4] !== FALSE && $regs[5] !== FALSE) {
				$res .= " $regs[4]:$regs[5]";
				if ($regs[6] !== FALSE)
					$res .= ":$regs[6]";
			}
			return $res;			
		} else {
			return $date;
		}
	}
	
	/**
	 * Converte uma data no padrão SQL (YYYY-mm-dd) para o padrão americano (YYYY/mm/dd)
	 * @param string $date Data a ser convertida
	 * @return string Data convertida ou a original se o padrão de entrada estiver incorreto
	 * @access public
	 */
	function fromSqlToUsDate($date) {
		$regs = array();
		if ($this->isSqlDate($date, $regs)) {
			return str_replace("-", "/", $date);
		} else {
			return $date;
		}
	}
	
	/**
	 * Converte um timestamp Unix em uma data/hora no formato DOS com 4 bytes
	 * @param int $unixTimestamp Timestamp UNIX para a conversão
	 * @return string Data e hora no formato DOS
	 * @access public
	 */
	function fromUnixToDosDate($unixTimestamp=0) {
		$timeData = ($unixTimestamp) ? getdate($unixTimestamp) : getdate();
		if ($timeData['year'] < 1980) {
			$timeData['year'] = 1980;
			$timeData['mon'] = 1;
			$timeData['mday'] = 1;
			$timeData['hours'] = 0;
			$timeData['minutes'] = 0;
			$timeData['seconds'] = 0;
		}
		return ((($timeData['year'] - 1980) << 25) |
			($timeData['mon'] << 21) |
			($timeData['mday'] << 16) |
			($timeData['hours'] << 11) |
			($timeData['minutes'] << 5) |
			($timeData['seconds'] << 1));
	}
	
	/**
	 * Converte uma data nos formatos EURO, US ou SQL em um timestamp UNIX
	 * @param string $date Data
	 * @return int
	 * @access public
	 */
	function dateToTime($date) {
		$date = $this->fromEuroToUsDate($date, TRUE);
		return strtotime($date);
	}
	
	/**
	 * Converte uma data para o correspondente em número de dias
	 * @param string $date Data base para o cálculo
	 * @return int Data convertida em número de dias
	 * @access public
	 */
	function dateToDays($date=NULL) {		
		if ($this->isNull($date))
			$date = $this->localDate();
		$regs = array();
		if ($this->isEuroDate($date, $regs)) {
			list(, $day, $month, $year) = $regs;
		} else if ($this->isUsDate($date, $regs) || $this->isSqlDate($date, $regs)) {
			list(, $year, $month, $day) = $regs;
		} else {
			return -1;
		}		
        $century = (int) substr($year,0,2);
        $year = (int) substr($year,2,2);
        if ($month > 2) {
            $month -= 3;
        } else {
            $month += 9;
            if ($year) {
                $year--;
            } else {
                $year = 99;
                $century --;
            }
        }
        return (floor((146097 * $century) / 4 ) + floor(( 1461 * $year) / 4 ) + floor(( 153 * $month + 2) / 5 ) + $day + 1721119);
	}
	
	/**
	 * Converte um número de dias em uma data
	 * @param int $days Número de dias
	 * @param string $dateType Tipo da data a ser retornada (EURO, US ou SQL)
	 * @return string Data correspondente
	 * @access public
	 */
	function daysToDate($days, $dateType) {
		if (!is_float($days) || !in_array(strtolower($dateType), array('euro', 'us', 'sql'))) {
			return NULL;
		}
        $days -= 1721119;
        $century = floor(( 4 * $days - 1) / 146097);
        $days = floor(4 * $days - 1 - 146097 * $century);
        $day = floor($days / 4);
        $year = floor(( 4 * $day +  3) / 1461);
        $day = floor(4 * $day +  3 - 1461 * $year);
        $day = floor(($day +  4) / 4);
        $month = floor(( 5 * $day - 3) / 153);
        $day = floor(5 * $day - 3 - 153 * $month);
        $day = floor(($day +  5) /  5);
        if ($month < 10) {
            $month +=3;
        } else {
            $month -=9;
            if ($year++ == 99) {
                $year = 0;
                ++$century;
            }
        }
        $century = sprintf('%02d', $century);
        $year = sprintf('%02d', $year);
        $month = sprintf('%02d', $month);
        $day = sprintf('%02d', $day);
        if (strtolower($dateType) == 'euro') {
        	return ("$day/$month/$century$year");
        } else if (strtolower($dateType) == 'us') {
        	return ("$century$year/$month/$day");
        } else {
        	return ("$century$year-$month-$day");
        }		
	}
	
	/**
	 * Retorna a data local de acordo com o formato
	 * @access public
	 * @param int $ts Timestamp opcional para geração da data local
	 * @return string Data local, a partir do timestamp atual ou um determinado
	 */
	function localDate($ts=0) {
		$dateFormat = DATE_FORMAT_LOCAL;
		if ($ts > 0) {
			if ($dateFormat) {
				return date($dateFormat . ' H:i:s', $ts);
			} else {
				return date("d/m/Y H:i:s", $ts);
			}
		} else {
			if ($dateFormat) {
				return date($dateFormat);
			} else {
				return date("d/m/Y");
			}
		}
	}
	
	/**
	 * Formata um valor de data a partir dos valores de dia, mês e ano
	 * @param int $day Valor do dia na data
	 * @param int $month Valor do mês na data
	 * @param int $year Valor do ano na data, com 4 dígitos
	 * @param int $fmtType Tipo de formato de data (vide constantes da classe)
	 * @return string Data formatada
	 * @access public
	 */
	function formatDate($day, $month, $year, $fmtType=DATE_FORMAT_LOCAL, $fmtStr='') {
		$day = strval(str_repeat('0', (2 - strlen($day))) . $day);
		$month = strval(str_repeat('0', (2 - strlen($month))) . $month);
		$year = strval(str_repeat('0', (4 - strlen($year))) . $year);
		$tsDate = mktime(0, 0, 0, $month, $day, $year);
		return $this->formatTime($tsDate, $fmtType, $fmtStr);
	}
	
	/**
	 * Formata um valor de unix timestamp
	 * @access public
	 * @param int $time Unix timestamp
	 * @param int $fmtType Tipo de formato de data (vide constantes da classse)
	 * @param string $fmtString Descrição do formato (quando $fmtType==DATE_FORMAT_CUSTOM)
	 * @return string Timestamp formatado
	 */
	function formatTime($time=NULL, $fmtType=DATE_FORMAT_LOCAL, $fmtStr='') {
		if (empty($time)) {
			$time = time();
		}			
		if (!is_int($time) || $time < 0 || $time > LONG_MAX) {
			return $time;
		}			
		if ($fmtType == DATE_FORMAT_LOCAL) {			
			return $this->localDate($time);			
		} elseif ($fmtType == DATE_FORMAT_CUSTOM && !empty($fmtStr)) {
			return date($fmtStr, $time);
		} else {
			return $time;
		}
	}
}
?>
