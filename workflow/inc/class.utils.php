<?php
/**
 * Base utility class
 * @author Carlos Eduardo Nogueira Goncalves
 * @author Marcos Pont
 * @version 1.0
 * @link http://workflow.celepar.parana/doc-workflow/classes/utils Complete reference
 * @package Workflow
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 */
class Utils 
{
	/**
	 * Include File
	 * @param string $filePath
	 * @param bool $return
	 * @access public
	 * @return bool
	 */
	function includeFile($filePath, $return=FALSE) 
	{
		if ($return === TRUE) {
			return (include($filePath));
		} 
		else 
		{
			if (!@include($filePath)) 
			{
				return FALSE;
			} 
			else 
			{
				return TRUE;
			}
		}
	}
	
	/**
	 * Println 
	 * @param $str
	 * @param $nl
	 * @access public
	 * @return void
	 */
	function println($str, $nl='<br>') {
		echo $str . $nl;
	}	
	
	/**
	 * Dump the $var Variable on screen
	 * @param mixed $var
	 */
	function dumpVariable($var) {
		print '<pre>';
		var_dump($var);
		print '</pre>';
	}
	/**
	 * DumpArray
	 * @param $arr
	 * @param $stringLimit
	 * @param deep
	 */
	function dumpArray($arr, $return=TRUE, $stringLimit=200, $deep=FALSE) {
		$r = array();
		foreach ($arr as $k => $v) {
			if (is_string($v)) {
				$r[] = $k . "=>'" . (strlen($v) > $stringLimit ? substr($v, 0, $stringLimit) . "...(" . strlen($v) . ")" : $v) . "'";
			} elseif ($deep && (is_array($v) || is_object($v))) {
				(is_object($v)) && ($v = get_object_vars($v));
				$r[] = $k . '=>' . dumpArray($v, TRUE, $stringLimit, TRUE);
			} else {
				$r[] = $k . '=>' . $v;
			}
		}
		if ($return)
			return "[" . implode(", ", $r) . "]";
		print "[" . implode(", ", $r) . "]";
		return TRUE;
	}
	
	/**
	 * Export Variable
	 * @param $var 
	 * @param bool $formatted 
	 * @access public
	 * @return string
	 */
	function exportVariable($var, $formatted=FALSE) {
		if (is_object($var) && !$this->isPHP(5) && method_exists($var, '__tostring'))
			$export = $var->__toString();
		else
			$export = var_export($var, TRUE);
		if ($formatted)
			return '<pre>' . $export . '</pre>';
		else
			return $export;
	}	
	
	
	/**
	 *
	 * @param $var 
	 * @param bool $formatted 
	 * @access public
	 * @return string
	 */
	function consumeArray(&$array, $key) {
		if (is_array($array)) {
			if (array_key_exists($key, $array)) {
				$return = $array[$key];
				unset($array[$key]);
				return $return;
			}
		}
		return NULL;
	}	
	
	/**
	 * Find Array path
	 * @param  array  $arr 
	 * @param  string $formatted
	 * @param  string $separator
	 * @param  string $fallback  
	 * @access public
	 * @return string
	 */
	function findArrayPath($arr, $path, $separator='.', $fallback=NULL) {
		if (!is_array($arr))
			return $fallback;
		$parts = explode($separator, $path);
		if (sizeof($parts) == 1) {
			return (isset($arr[$path]) ? $arr[$path] : $fallback);
		} else {
			$i = 0;
			$base = $arr;
			$size = sizeof($parts);
			while ($i < $size) {
				if (!isset($base[$parts[$i]]))
					return $fallback;
				else
					$base = $base[$parts[$i]];
				if ($i < ($size-1) && !is_array($base))
					return $fallback;
				++$i;
			}
			return $base;
		}
	}	
	
	/**
	 * isPHP
	 * @param  int $num   
	 * @access public
	 * @return float
	 */
	function isPHP($num=5) {
		return (floatval(PHP_VERSION) >= $num);
	}	
	
	/**
	 * uri
	 * @param uri
	 * @bool
	 * 
	 * @access public
	 * @return string
	 */
	function uri($full=TRUE) {
		if ($full) {
			$protocol = $this->protocol();
			$port = $this->get('SERVER_PORT');
			if (($protocol == 'http' && $port != '80') || ($protocol == 'https' && $port != '443'))
				$base = "{$protocol}://" . $this->serverName() . ":{$port}";
			else
				$base = "{$protocol}://" . $this->serverName();
		} else {
			$base = '';
		}
		if (!$requestUri = $this->get('REQUEST_URI')) {
			if ($queryString = $this->get('QUERY_STRING'))
				return $base . $this->scriptName() . '?' . $queryString;
			else
				return NULL;
		}
		$requestUri = preg_replace('#[?|&]' . session_name() . '=[^&|?]*#', '', $requestUri);
		return $base . $requestUri;
	}
	
	
	/**
	 * Return the protocol
	 * @return string if isSecure returns true https else return http
	 * @access public
	 */
	function protocol() {
		if ($this->isSecure())
			return 'https';
		else
			return 'http';
	}	
	
	/**
	 * Return if secure connection is required
	 * @return bool https required or not 
	 * @access public
	 */
	function isSecure() {
		return (strtolower($this->get('HTTPS')) == 'on' || $this->has('SSL_PROTOCOL_VERSION'));
	}
	
	/**
	 * Return the script name
	 * @return string script name
	 * @access public
	 */
	function scriptName() {
		return $this->get('SCRIPT_NAME');
	}	
	
	/**
	 * Return the server name
	 * @return string server Name
	 * @access public
	 */	
	function serverName() {
		return $this->get('SERVER_NAME');
	}
	
	/**
	 * Return Environment variable
	 * @return enviroment variable
	 * @access public
	 */	
	function get($key) {
		if (isset($_SERVER[$key])) {
			return $_SERVER[$key];
		}
		if (@getenv($key))
			return getenv($key);
		else
			return NULL;
	}	
	
	/**
	 * Check if the value of variable is NULL
	 * @param mixed $value value 
	 * @param bool  $strict  strict true compares the type and value 
	 * @access public
	 */
	function isNull($value, $strict = FALSE) {
		return ($strict) ? (NULL === $value) : (NULL == $value);
	}
	
	/**
	 * Get the string value of variable
	 * @param mixed $value
	 * @access public
	 * @return string 
	 */
	function parseString($value) {
		return strval($value);
	}	
	
	/**
	 * Checks if $value is false
	 * @param mixed $value
	 * @return bool true if is a boolean and false 
	 */
	function isFalse($value) {
		return ($value === FALSE);
	}
	
	/**
	 * Checks if $value is false
	 * @param string $str
	 * @param $chars 
	 * @return string
	 * @access public
	 *  
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
	 * Checks if $value is a integer
	 * @param string $value
	 * @param $chars 
	 * @return string
	 * @access public 
	 */
	function isInteger(&$value, $strict=FALSE) {
		$exp = "/^\-?[0-9]+$/";
		if (preg_match($exp, $value)) {
			if (!$strict && !is_int($value)) {
				$value = $this->parseInteger($value);
			}
			return TRUE;
		} else {
			return FALSE;
		}
	}	
	
	/**
	 * Get the integer value of variable
	 * @param $value integer value
	 * @access public
	 */
	function parseInteger($value) {
		return intval($value);
	}	
	
	/**
	 * Return the positive integer value of variable 
	 * @param mixed $value
	 * @return int 
	 * @access public
	 */
	function parseIntegerPositive($value) {
		return abs(intval($value));
	}
	
	/**
	 * Returns a random number 
	 * @param int $rangeMin
	 * @param int $rangeMax
	 * @return string
	 * @access public
	 */
	function randomize($rangeMin, $rangeMax) {
		if ($rangeMax > $rangeMin && is_numeric($rangeMin) && is_numeric($rangeMax)) {
			return rand($rangeMin, $rangeMax);
		} else {
			return NULL;
		}
	}	
	
	/** 
	 * Return a $default value if the first parameter is NULL else returns the first parameter 
	 * 
	 * @param mixed $value   first parameter
	 * @param mixed $default default value to return if $value == NULL
	 * @access public
	 * @return mixed  
	 */
	
	function ifNull($value, $default = NULL) {
		if ($value === NULL)
			return $default;
		return $value;
	}	
	
	/**
	 * Return the javascript code for a anchor
	 * 
	 * @param string $url     base url
	 * @param string $text    
	 * @param string $statusBarText
	 * @param string $cssClass
	 * @param array $jsEvents
	 * @param string $target
	 * @param string $name
	 * @param string $id
	 * @param string $rel
	 * @param string $accessKey
	 * 
	 * @param array $jsEvents
	 * 
	 * @return string javascript code
	 * @access public
	 */
	 function anchor($url, $text, $statusBarText='', $cssClass='', $jsEvents=array(), $target='', $name='', $id='', $rel='', $accessKey='') 
	 {
		if (empty($url))
			$url = "javascript:void(0);";
		$scriptStr = '';
		if (!empty($jsEvents) && $statusBarText != "") {
			$jsEvents['onMouseOver'] = (isset($jsEvents['onMouseOver']) ? $jsEvents['onMouseOver'] . "window.status='$statusBarText';return true;" : "window.status='$statusBarText';return true;");
			$jsEvents['onMouseOut'] = (isset($jsEvents['onMouseOut']) ? $jsEvents['onMouseOut'] . "window.status='';return true;" : "window.status='';return true;");
		} else if ($statusBarText) {
			$scriptStr .= "onMouseOver=\"window.status='$statusBarText';return true;\" onMouseOut=\"window.status='';return true;\"";
		}
		foreach ($jsEvents as $event => $action)
			$scriptStr .= " $event=\"" . preg_replace("/\"/", "'", $action) . "\"";
		return sprintf("<a href=\"%s\"%s%s%s%s%s%s%s%s>%s</a>", htmlentities($url),
			(!empty($name) ? " name=\"{$name}\"" : ""),
			(!empty($id) ? " id=\"{$id}\"" : ""),
			(!empty($rel) ? " rel=\"{$rel}\"" : ""),
			(!empty($accessKey) ? " accesskey=\"{$accessKey}\"" : ""),
			(!empty($target) ? " target=\"{$target}\"" : ""),
			(!empty($cssClass) ? " class=\"{$cssClass}\"" : ""),
			(!empty($statusBarText) ? " title=\"{$statusBarText}\"" : ""),
			(!empty($scriptStr) ? " {$scriptStr}" : ""),
			$text);
	}	
	
	/**
	 * Return a error message 
	 * @param string $msg
	 * @param string $file
	 * @param string $line
	 * 
	 * @return void 
	 * @access public 
	 */
	function raiseError($msg = 'Erro', $file = __FILE__, $line = __LINE__)
	{
		ini_set('display_errors', true);
		error_reporting(E_ALL);
		trigger_error("$file ($line): $msg", E_USER_ERROR);
	}	
}
?>