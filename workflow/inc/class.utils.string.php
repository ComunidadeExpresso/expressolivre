<?php
require_once('class.utils.php');
/**
 * Contains useful methods for string handling that extend standard PHP set and include new tools
 * @author Carlos Eduardo Nogueira Gonçalves
 * @author Marcos Pont
 * @version 1.0
 * @link http://workflow.celepar.parana/doc-workflow/classes/stringutils Complete reference
 * @package Workflow
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 */
class StringUtils extends Utils
{
	/**
	 * Strips all blank  characters from the beginning and end of a string
	 * @param string $str
	 * @return result string
	 * @access public 
	 */
	function allTrim($str) {
		return $this->stripBlank(trim($str));
	}

	/**
	 * Replace all blank characters 
	 * @param string $str         
	 * @param string $replace replace string
	 * @return string result string   
	 * @access public
	 */
	function stripBlank($str, $replace=' ') {
		return preg_replace('/[[:blank:]]{1,}/', $replace, $str);
	}
	
	/**
	 * Return the first n chars of the string 
	 * @param string $str base string
	 * @param int $chars number of chars to return  
	 * @return string result substring
	 * @access public
	 */
	function left($str, $chars = 0) {
		if (!$this->isInteger($chars)) {
			return $str;
		} else if ($chars == 0) {
			return '';
		} else {
			return substr($str, 0, $chars);
		}
	}
	
	/**
	 * Return the last n chars of the string 
	 * @param string $str base string  
	 * @param int $chars  number of chars to return 
	 * @return string result substring
	 * @access public
	 */
	function right($str, $chars=0) {
		if (!$this->isInteger($chars)) {
			return $str;
		} else if ($chars == 0) {
			return '';
		} else {
			return substr($str, strlen($str) - $chars, strlen($str)-1);
		}
	}
	
	/**
	 * Return parts of the current string 
	 * @param string $str   base string  
	 * @param int 	$startAt 
	 * @param int 	$chars
	 * @return string result substring
	 * @access public
	 */
	function mid($str, $startAt=1, $chars=0) {
		if (!$this->isInteger($chars)) {
			return $str;
		} else if ($str == '' || $chars == 0) {
			return '';
		} else if (($startAt + $chars) > strlen($str)) {
			return $str;
		} else {
			if ($startAt == 0) $startAt = 1;
			return substr($str, $startAt-1, $chars);
		}
	}
	
	/**
	 * Returns the character at position  
	 * @param string $str reference string
	 * @param $index
	 * @return string string value of a variable
	 * @access public
	 */
	function charAt($str, $index) {
		if (!$this->isInteger($index)) {
			return '';
		} else if ($str == '' || $index < 0 || $index >= strlen($str)) {
			return '';
		} else {
			$strTranslated = strval($str);
			return $strTranslated{$index};
		}
	}
	/**
	 * Search a string in another string 
	 * @param string $str reference string  
	 * @param $sValue search string
	 * @param $caseSensitive caseSensitive true or false 
	 * @return string position of  first occurrence of  sValue in str 
	 * @access public
	 */
	function match($str, $sValue, $caseSensitive=TRUE) {
		if (!$caseSensitive) $sValue = strtolower($sValue);
		if (strlen($sValue) == 0) {
			return FALSE;
		} else {
			$pos = strpos($str, $sValue);
			return ($pos !== FALSE);
		}
	}
	/**
	 * Return all string starts with the substring
	 * @param string $str referenfe string 
	 * @param $slice
	 * @param bool $caseSensitive caseSensitive true or false 
	 * @param bool $ignSpaces ignore spaces
	 * @return string
	 * @access public  
	 */
	function startsWith($str, $slice, $caseSensitive=TRUE, $ignSpaces=TRUE) {
		if (!$caseSensitive) {
			$strUsed = ($ignSpaces) ? ltrim(strtolower($str)) : strtolower($str);
			$sliceUsed = strtolower($slice);
		} else {
			$strUsed = ($ignSpaces) ? ltrim($str) : $str;
			$sliceUsed = $slice;
		}
		return ($this->left($strUsed, strlen($sliceUsed)) == $sliceUsed);
	}
	
	/**
	 * Return all string ends with the substring
	 * @param string $str
	 * @param $slice
	 * @param bool $caseSensitive caseSensitive true or false
	 * @param bool $ignSpaces ignore spaces
	 * @return string
	 * @access public  
	 */
	function endsWith($str, $slice, $caseSensitive=TRUE, $ignSpaces=TRUE) {
		if (!$caseSensitive) {
			$strUsed = ($ignSpaces) ? rtrim(strtolower($str)) : strtolower($str);
			$sliceUsed = strtolower($slice);
		} else {
			$strUsed = ($ignSpaces) ? rtrim($str) : $str;
			$sliceUsed = $slice;
		}
		return ($this->right($strUsed, strlen($sliceUsed)) == $sliceUsed);
	}
	
	/**
	 * Checks with the all characters of string is upper case
	 * @param string $str
	 * @return bool
	 * @access public   
	 */
	function isAllUpper($str) {
		return (preg_match("/[a-z]/", $str) !== FALSE);
	}
	
	/**
	 * Checks with the all characters of string is lower case
	 * @param string $str
	 * @return bool
	 * @access public  
	 * 
	 */
	function isAllLower($str) {
		return (preg_match("/[A-Z]/", $str) !== FALSE);
	}
	
	/**
	 * Checks with the values is empty
	 * @param string $value 
	 * @return bool
	 * @access public  
	 */
	function isEmpty($value) {
		$value = strval($value);
		return (empty($value) && strlen($value) == 0);
	}
	
	/**
	 * If the $values replace with the $replacement parameter
	 * @param mixed $value         
	 * @param mixed $replacement 
	 * @return bool
	 * @access public  
	 */
	function ifEmpty($value, $replacement) {
		return (empty($value) ? $replacement : $value);
	}

	/**
	 * Concat two strings
	 * @param string $str first string
	 * @param string $concat second string
	 * @return result string 
	 */
	function concat($str, $concat) {
		return $str . $concat;
	}

	/**
	 * Adds a prefix and suffix to a string 
	 * @param string $str     reference string
	 * @param string $prefix  prefix string
	 * @param string $suffix  suffix string
	 * @return string result string
	 * @access public  
	 */
	function surround($str, $prefix, $suffix) {
		return $prefix . $str . $suffix;
	}
	
	/**
	 * Insert a value in a determined  position in a string
	 * @param $str reference string
	 * @param $insValue value to insert
	 * @param $insPos insert position 
	 * @return string 
	 * @access public
	 */
	function insert($str, $insValue = '', $insPos = 0) {
		if (($insValue == '') || ($insPos < 0) || ($insPos > strlen($str))) {
			return $str;
		} else if ($insPos == 0) {
			return $insValue . $str;
		} else if ($insPos == strlen($str)) {
			return $str . $insValue;
		} else {
			return $this->left($str, $insPos) . $insValue . $this->right($str, $insPos, strlen($str) - $insPos);
		}
	}

	/**
	 * Replace all occurrences of search in haystack with replace
	 * 
	 * @param string $str reference string
	 * @param string $from string to search
	 * @param string $to  string to replace
	 * @return mixed 
	 * @access public
	 */
	function replace($str, $from, $to) {
		return str_replace($from, $to, $str);
	}
	
	/**
	 * Replace the pattern with a parameter string 
	 * 
	 * @param string $str reference string
	 * @param string $pattern pattern to search
	 * @param string $replacement string to replace
	 * @return string  
	 * @access public
	 */
	function regexReplace($str, $pattern, $replacement) {
		if (empty($pattern))
			return $str;
		$matches = array();
    	if (preg_match('!\W(\w+)$!s', $pattern, $matches) && (strpos($matches[1], 'e') !== FALSE))
			$pattern = substr($pattern, 0, -strlen($matches[1])) . str_replace('e', '', $matches[1]);
		return preg_replace($pattern, $replacement, $str);
	}
	
	/**
	 * Explode a string  in array of components 
	 * 
	 * @param string $str reference string
	 * @param string $sep separator
	 * @return array array of components
	 * @access public
	 */
	function explode($str, $sep) {
		$arr = explode($sep, $str);
		return $arr;
	}
	
	/**
  	 * Join elements array in one string 
	 * @param string $str reference string
	 * @param string $glue glue string 
	 * @return string result string 
	 * @access public
	 */
	function implode($values, $glue) {
		return implode($glue, (array)$values);
	}

	/**
	 * Encode the string with encodetype 
	 * 
	 * @param string $str reference string 
	 * @param string $encodeType encode type: 'base64', 'utf8' ,'7bit' , '8bit' or 'quoted-printable'
	 * @param array  $params 
	 * @return string encoded string 
	 * @access public
	 */
	function encode($str, $encodeType, $params=NULL) {
		switch(strtolower($encodeType)) {
			case 'base64' :
				$encoded = chunk_split(base64_encode($str));
				break;
			case 'utf8' :
				$encoded = utf8_encode($str);
				break;
			case '7bit' :
			case '8bit' :
				$nl = $this->ifNull($params['nl'], "\n");
				$str = str_replace(array("\r\n", "\r"), array("\n", "\n"), $str);
				$encoded = str_replace("\n", $nl, $str);
				if (!$this->endsWith($encoded, $nl))
					$encoded .= $nl;
				break;
			case 'quoted-printable' :
				static $qpChars;
				if (!isset($qpChars))
					$qpChars = array_merge(array(64, 61, 46), range(0, 31), range(127, 255));
				$charset = $this->ifNull($params['charset'], 'iso-8859-1');
				$replace = array(' ' => '_');
				foreach ($qpChars as $char)
					$replace[chr($char)] = '=' . strtoupper(dechex($char));
				return sprintf("=?%s?Q?%s=", $charset, strtr($str, $replace));
			default:
				$encoded = $str;
				break;
		}
		return $encoded;
	}
	/**
	 * Decode the string parameter with encodetype  
	 * 
	 * @param string $str reference string
	 * @param string $encodetype 'base64', 'base64', 'quoted-printable'
	 * @return string decode string 
	 * @access public
	 */
	function decode($str, $encodeType) {
		switch(strtolower($encodeType)) {
			case 'base64' :
				$decoded = base64_decode($str);
				break;
			case 'base64' :
				$decoded = utf8_decode($str);
				break;
			case 'quoted-printable' :
				$decoded = quoted_printable_decode($str);
				break;
			default :
				$decoded = $str;
				break;
		}
		return $decoded;
	}
	
	/**
 	 * Map 
	 * 
	 * @access public
	 */
	function map() {
		$argc = func_num_args();
		$argv = func_get_args();
		if ($argc == 0)
			return NULL;
		$base = $argv[0];
		for ($i=1,$s=sizeof($argv); $i<$s; $i+=2) {
			if (array_key_exists($i+1, $argv)) {
				if ($base == $argv[$i])
					return $argv[$i+1];
			} else {
				return $argv[$i];
			}
		}
		return $base;
	}
	
	/**
 	 * Filter the selected strings  
	 * 
	 * @param string $str string reference 
	 * @param string $filtertype regex expression
	 * @param string $replaceStr string to replace
	 * 
	 * @return string 
	 * @access public
	 * 
	 */
	function filter($str, $filterType='alphanum', $replaceStr='') {
		$replaceStr = strval($replaceStr);
		switch ($filterType) {
			case 'alpha' :
				return (preg_replace('/[^a-zA-Z]/', $replaceStr, $str));
			case 'alphalower' :
				return (preg_replace('/[^a-z]/', $replaceStr, $str));
			case 'alphaupper' :
				return (preg_replace('/[^A-Z]/', $replaceStr, $str));
			case 'num' :
				return (preg_replace('/[^0-9]/', $replaceStr, $str));
			case 'alphanum' :
				return (preg_replace('/[^0-9a-zA-Z]/', $replaceStr, $str));
			case 'htmlentities' :
				return (preg_replace('/&[[:alnum:]]{0,};/', $replaceStr, $str));
			case 'blank' :
				return (preg_replace('/[[:blank:]]{1,}/', $replaceStr, $str));
			default :
				return $str;
		}
	}

	/**
	 * Escape special characters
	 * 
	 * @param string $str reference string  
	 * @param string $conversionType 'html', 'htmlall' ,'url' ,'quotes', 'javascript' 'mail'
	 * @access public
	 * @return string
	 */
	function escape($str, $conversionType='html') {
		switch ($conversionType) {
			case 'html':
				return htmlspecialchars($str, ENT_QUOTES);
			case 'htmlall' :
				return htmlentities($str, ENT_QUOTES);
			case 'url' :
				return rawurlencode($str);
			case 'quotes' :
				return preg_replace("%(?<!\\\\)'%", "\\'", $str);
			case 'javascript' :
				$expressions = array(
					"/(<scr)(ipt)/i" => "$1\"+\"$2", // quebrar tags "<script"
					'/\\\\/' => '\\\\', // backslashes
					'/\'/' => "\'", // single quotes
					'/"/' => '\\"', // double quotes
					"/\r/"=>'\\r', // caractere CR
					"/\n/"=>'\\n', // caractere LF
					"/\t/" => "\\t" // tabulaï¿½ï¿½es
				);
				$str = str_replace("\\", "\\\\", $str);
				$str = preg_replace(array_keys($expressions), array_values($expressions), $str);
				return $str;
			case 'mail' :
				return str_replace(array('@', '.'), array(' at ', ' dot '), $str);
			default :
				return $str;
		}
	}

    /**
     * Camelize a string 
     * 
     * @param  string $str 
     * @return string camelized string
     * @access public
     */
	function camelize($str) {
		return preg_replace("/[_|\s]([a-z0-9])/e", "strtoupper('\\1')", strtolower($str));
	}

	/**
	 * Capitalize the parameter string 
	 * @param string $str parameterstring 
	 * @return string result string 
	 * @access public
	 */
	function capitalize($str) {
		if (!empty($str)) {
			$w = preg_split("/\s+/", $str);
			for ($i=0, $s=sizeof($w); $i<$s; ++$i) {
				if (empty($w[$i]))
					continue;
				$f = strtoupper($w[$i][0]);
				$r = strtolower(substr($w[$i], 1));
				$w[$i] = $f . $r;
			}
			return implode(' ', $w);
		}
		return $str;
	}
	
	/**
	 * Normalize the string 
	 * @param string $str reference string 
	 * @return string normalized string 
	 * @access public
	 */
	function normalize($str) {
		$ts = array("/[ï¿½-ï¿½]/", "/ï¿½/", "/ï¿½/", "/[ï¿½-ï¿½]/", "/[ï¿½-ï¿½]/", "/ï¿½/", "/ï¿½/", "/[ï¿½-ï¿½ï¿½]/", "/ï¿½/", "/[ï¿½-ï¿½]/", "/ï¿½/", "/ï¿½/", "/[ï¿½-ï¿½]/", "/ï¿½/", "/ï¿½/", "/[ï¿½-ï¿½]/", "/[ï¿½-ï¿½]/", "/ï¿½/", "/ï¿½/", "/[ï¿½-ï¿½ï¿½]/", "/ï¿½/", "/[ï¿½-ï¿½]/", "/[ï¿½-ï¿½]/");
		$tn = array("A", "AE", "C", "E", "I", "D", "N", "O", "X", "U", "Y", "ss", "a", "ae", "c", "e", "i", "d", "n", "o", "x", "u", "y");
		return preg_replace($ts, $tn, $str);
	}
	
	/**
	 * Cut before 
	 * @param string $string 
	 * @param string $token string to match
	 * @param bool 
	 * @return string 
	 * @access public
	 */
	function cutBefore($string, $token, $caseSensitive=TRUE) {
		if ($this->match($caseSensitive ? $string : strtolower($string), $token, $caseSensitive)) {
			return stristr($string, $token);
		}
		return $string;
	}
	
	/**
	 * Cut last occurrence  
	 * @param string $string 
	 * @param string $cutOff
	 * @param bool $caseSensitive 
	 * @param bool 
	 * @return string 
	 * @access public
	 */
	function cutLastOcurrence($string, $cutOff, $caseSensitive=TRUE) {
		if (!$this->match($caseSensitive ? $string : strtolower($string), $cutOff, $caseSensitive))
			return $string;
		else
			return strrev(substr(stristr(strrev($string), strrev($cutOff)),strlen($cutOff)));
	}

	/**
	 * Ident a string 
	 * @param string $str string reference
	 * @access public
	 * @return string 
	 */	
	function indent($str, $nChars, $iChar=' ') {
		if (!$this->isInteger($nChars) || $nChars < 1) {
			$nChars = 1;
		}
		return preg_replace('!^!m', str_repeat($iChar, $nChars), $str);
	}

	/**
	 * Truncate .....
	 * @param string $str reference string 
	 * @param int $lenght 
	 * @param string $truncSufix truncate sufix 
	 * @param bool $forcebreak
	 * @return string  
	 * @access public
	 */
	function truncate($str, $length, $truncSufix='...', $forceBreak=TRUE) {
		if (!$this->isInteger($length) || $length < 1) {
			return '';
		} else {
			if (strlen($str) > $length) {
				$length -= strlen($truncSufix);
        		if (!$forceBreak)
            		$str = preg_replace('/\s+?(\S+)?$/', '', substr($str, 0, $length+1));
				return substr($str, 0, $length) . $truncSufix;
			} else {
				return $str;
			}
		}
	}
	
	/**
	 * Insert Char in string 
	 * @param $str reference string 
	 * @param bool $stringEmpty 
	 * @param string $char 
	 * @return string 
	 * @access public
	 */
	function insertChar($str, $char = ' ', $stripEmpty = TRUE) {
		if ($stripEmpty) {
			$strChars = preg_split('//', $str, -1, PREG_SPLIT_NO_EMPTY);
		} else {
			$strChars = preg_split('//', $str, -1);
		}
		return implode($char, $strChars);
	}
	
	/**
	 * Wrap line 
	 * @param string $str reference string   
	 * @param int $num num of line to wrap
	 * @param string $breakstring  
	 * @return string 
	 * @access public
	 */
	function wrapLine($str, $num, $breakString="\n") {
		$line = '';
		$processed = '';
		$token = strtok($str, ' ');
		while($token) {
			if (strlen($line) + strlen($token) < ($num + 2)) {
				$line .= " $token";
			} else {
				$processed .= "$line$breakString";
				$line = $token;
			}
			$token = strtok(' ');
		}
		$processed .= $line;
		$processed = trim($processed);
		return $processed;
	}

	/**
	 * Wrap  ....
	 * 
	 * @param $str reference string 
	 * @param string $breakString separator
	 * @return string  $processed  
	 * @access public
	 */
	function wrap($str, $num, $breakString="\n") {
		$str = preg_replace('/([^\r\n])\r\n([^\r\n])/', "\\1 \\2", $str);
		$str = preg_replace('/[\r\n]*\r\n[\r\n]*/', "\r\n\r\n", $str);
		$str = preg_replace('/[ ]* [ ]*/', ' ', $str);
		$str = stripslashes($str);
		$processed = '';
		$paragraphs = explode("\n", $str);
		for ($i=0; $i<sizeof($paragraphs); ++$i) {
			$processed .= $this->wrapLine($paragraphs[$i], $num, $breakString) . $breakString;
		}
		$processed = trim($processed);
		return $processed;
	}
	
	/**
	 * Add line numbers 
	 * 
	 * @param string $str reference string  
	 * @param string $start character to start  
	 * @param string $indent indent size  
	 * @param string $afternumberChar char to place after number  
	 * @return string $processed  result string 
	 * @access public
	 */
	function addLineNumbers(&$str, $start = 1, $indent = 3, $afterNumberChar = ':', $glue='\n') {
		// divide a string em linhas de um array
		$line = explode("\n", $str);
		$size = sizeof($line);
		// calcula a largura maxima da numeraçao de acordo com o numero de linhas
		$width = strlen((string)($start + $size -1));
		$indent = max($width, $indent);
		// gera a numeraï¿½ï¿½o de linhas da string
		for ($i = 0; $i < $size; ++$i) {
			$line[$i] = str_pad((string)($i + $start), $indent, ' ', STR_PAD_LEFT) . $afterNumberChar . ' ' . trim($line[$i]);
		}
		return implode($glue, $line);
	}

	/**
	 * Count Chars 
	 * @param string $str  reference string 
	 * @param bool $includeSpaces spaces too
	 * @return int number of chars
	 * @access public 
	 */
	function countChars($str, $includeSpaces = FALSE) {
		if ($includeSpaces) {
			return strlen($str);
		} else {
			$match = array();
			return preg_match_all('/[^\s]/', $str, $match);
		}
	}

	/**
	 * Count Words in a string 
	 * @param string $str reference string 
	 * @return mixed array or string 
	 * @access public 
	 */
	function countWords($str) {
		return str_word_count($str);
	}

	/**
	 * Count Sentences in a string 
	 * @param string $str reference string 
	 * @return mixed number of sentences
	 * @access public 
	 */
	function countSentences($str) {
		$matches = array();
		return preg_match_all('/[^\s]\.(?!\w)/', $str, $matches);
	}

	/**
	 * Count Paragraphs in a string 
	 * @param string $str reference string 
	 * @return int number of paragraphs
	 * @access public 
	 */
	function countParagraphs($str) {
		return count(preg_split('/[\r\n]+/', $str));
	}
	
	/**
	 * Create a random string 
	 * 
	 * @param mixed $size size of the string  
	 * @param bool $upper upper case permitted
	 * @param bool $digit digits included 
	 * @return string result string 
	 * @access public 
	 */
	function randomString($size, $upper=TRUE, $digit=TRUE) {
		$pSize = max(1, $size);
		$start = $digit ? 48 : 65;
		$end = 122;
		$result = '';
		while (strlen($result) < $size) {
			$random = $this->randomize($start, $end);
			if (($digit && $random >= 48 && $random <= 57) ||
				($upper && $random >= 65 && $random <= 90) ||
				($random >= 97 && $random <= 122)) {
				$result .= chr($random);
			}
		}
		return $result;
	}
}
?>