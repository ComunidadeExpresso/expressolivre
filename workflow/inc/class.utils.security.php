<?php
require_once('class.utils.php');
 /**
  * Filters input of stray or malicious PHP, Javascript or HTML tags.
  * It can be used to prevent cross-site scripting (XSS) attacks.
  * It should be used to filter input supplied by the user, such as an HTML code
  * entered in form fields. You would create the filter object, configure it with your
  * own settings, then call its process method to clean the form input values.
  *
  * @author: Daniel Morris
  * @version: 1.2.2_php4/php5
  * @package Workflow
  * @license http://www.gnu.org/copyleft/gpl.html GPL
  */
class SecurityUtils extends Utils {

	/**
	 * @var array $tagsArray default = empty array
	 * @access public
	 */
	var $tagsArray;

	/**
	 * @var array $attrArray  default = empty array
	 * @access public
	 */
	var $attrArray;

	/**
	 * @var $tagsMethod  default = 0
	 * @access public
	 */
	var $tagsMethod;

	/**
	 * @var $attrMethod  default = 0
	 * @access public
	 */
	var $attrMethod;

	/**
	 * @var $xssAuto  default = 1
	 * @access public
	 */
	var $xssAuto;

	/**
	 * @var array $tagBlacklist
	 * @access public
	 */
	var $tagBlacklist = array('applet', 'body', 'bgsound', 'base', 'basefont', 'embed', 'frame', 'frameset', 'head', 'html', 'id', 'iframe', 'ilayer', 'layer', 'link', 'meta', 'name', 'object', 'script', 'style', 'title', 'xml');

	/**
	 * @var array $attrBlackList
	 * @access public
	 */
	var $attrBlacklist = array('action', 'background', 'codebase', 'dynsrc', 'lowsrc');  // also will strip ALL event handlers

	/**
	  * Constructor for inputFilter class. Only first parameter is required.
	  * @access constructor
	  * @param Array $tagsArray - list of user-defined tags
	  * @param Array $attrArray - list of user-defined attributes
	  * @param int $tagsMethod - 0= allow just user-defined, 1= allow all but user-defined
	  * @param int $attrMethod - 0= allow just user-defined, 1= allow all but user-defined
	  * @param int $xssAuto - 0= only auto clean essentials, 1= allow clean blacklisted tags/attr
	  */
	function SecurityUtils($tagsArray = array(), $attrArray = array(), $tagsMethod = 0, $attrMethod = 0, $xssAuto = 1) {
		// make sure user defined arrays are in lowercase
        $tagsArray_count = count($tagsArray);
		for ($i = 0; $i < $tagsArray_count; ++$i)
            $tagsArray[$i] = strtolower($tagsArray[$i]);
        $attrArray_count = count($attrArray);
		for ($i = 0; $i < $attrArray_count; ++$i)
            $attrArray[$i] = strtolower($attrArray[$i]);
		// assign to member vars
		$this->tagsArray = (array) $tagsArray;
		$this->attrArray = (array) $attrArray;
		$this->tagsMethod = $tagsMethod;
		$this->attrMethod = $attrMethod;
		$this->xssAuto = $xssAuto;
	}

	/**
	  * Method to be called by another php script. Processes for XSS and specified bad code.
	  * @access public
	  * @param Mixed $source - input string/array-of-string to be 'cleaned'
	  * @return String $source - 'cleaned' version of input parameter
	  */
	function process($source) {
		// clean all elements in this array
		if (is_array($source)) {
			foreach($source as $key => $value)
				// filter element for XSS and other 'bad' code etc.
				if (is_string($value)) $source[$key] = $this->remove($this->decode($value));
			return $source;
		// clean this string
		} else if (is_string($source)) {
			// filter source for XSS and other 'bad' code etc.
			return $this->remove($this->decode($source));
		// return parameter as given
		} else return $source;
	}

	/**
	  * Internal method to iteratively remove all unwanted tags and attributes
	  * @access protected
	  * @param String $source - input string to be 'cleaned'
	  * @return String $source - 'cleaned' version of input parameter
	  */
	function remove($source) {
		$loopCounter=0;
		// provides nested-tag protection
		while($source != $this->filterTags($source)) {
			$source = $this->filterTags($source);
			++$loopCounter;
		}
		return $source;
	}

	/**
	  * Internal method to strip a string of certain tags
	  * @access protected
	  * @param String $source - input string to be 'cleaned'
	  * @return String $source - 'cleaned' version of input parameter
	  */
	function filterTags($source)
	{
		return strip_tags($source);
	}

	/**
	  * Internal method to strip a tag of certain attributes
	  * @access protected
	  * @param Array $attrSet
	  * @return Array $newSet
	  */
	function filterAttr($attrSet) {
		$newSet = array();
		// process attributes
        $attrSet_count = count($attrSet);
		for ($i = 0; $i < $attrSet_count; ++$i) {
			// skip blank spaces in tag
			if (!$attrSet[$i]) continue;
			// split into attr name and value
			$attrSubSet = explode('=', trim($attrSet[$i]));
			list($attrSubSet[0]) = explode(' ', $attrSubSet[0]);
			// removes all "non-regular" attr names AND also attr blacklisted
			if ((!preg_match('/^[a-z]*$/i',$attrSubSet[0])) || (($this->xssAuto) && ((in_array(strtolower($attrSubSet[0]), $this->attrBlacklist)) || (substr($attrSubSet[0], 0, 2) == 'on'))))
				continue;
			// xss attr value filtering
			if ($attrSubSet[1]) {
				// strips unicode, hex, etc
				$attrSubSet[1] = str_replace('&#', '', $attrSubSet[1]);
				// strip normal newline within attr value
				$attrSubSet[1] = preg_replace('/\s+/', '', $attrSubSet[1]);
				// strip double quotes
				$attrSubSet[1] = str_replace('"', '', $attrSubSet[1]);
				// [requested feature] convert single quotes from either side to doubles (Single quotes shouldn't be used to pad attr value)
				if ((substr($attrSubSet[1], 0, 1) == "'") && (substr($attrSubSet[1], (strlen($attrSubSet[1]) - 1), 1) == "'"))
					$attrSubSet[1] = substr($attrSubSet[1], 1, (strlen($attrSubSet[1]) - 2));
				// strip slashes
				$attrSubSet[1] = stripslashes($attrSubSet[1]);
			}
			// auto strip attr's with "javascript:
			if (	((strpos(strtolower($attrSubSet[1]), 'expression') !== false) &&	(strtolower($attrSubSet[0]) == 'style')) ||
					(strpos(strtolower($attrSubSet[1]), 'javascript:') !== false) ||
					(strpos(strtolower($attrSubSet[1]), 'behaviour:') !== false) ||
					(strpos(strtolower($attrSubSet[1]), 'vbscript:') !== false) ||
					(strpos(strtolower($attrSubSet[1]), 'mocha:') !== false) ||
					(strpos(strtolower($attrSubSet[1]), 'livescript:') !== false)
			) continue;

			// if matches user defined array
			$attrFound = in_array(strtolower($attrSubSet[0]), $this->attrArray);
			// keep this attr on condition
			if ((!$attrFound && $this->attrMethod) || ($attrFound && !$this->attrMethod)) {
				// attr has value
				if ($attrSubSet[1]) $newSet[] = $attrSubSet[0] . '="' . $attrSubSet[1] . '"';
				// attr has decimal zero as value
				else if ($attrSubSet[1] == "0") $newSet[] = $attrSubSet[0] . '="0"';
				// reformat single attributes to XHTML
				else $newSet[] = $attrSubSet[0] . '="' . $attrSubSet[0] . '"';
			}
		}
		return $newSet;
	}

	/**
	  * Try to convert to plaintext
	  * @access protected
	  * @param String $source
	  * @return String $source
	  */
	function decode($source) {
		// url decode
		$source = html_entity_decode($source, ENT_QUOTES, "ISO-8859-1");
		// convert decimal
		$source = preg_replace('/&#(\d+);/me',"chr(\\1)", $source);				// decimal notation
		// convert hex
		$source = preg_replace('/&#x([a-f0-9]+);/mei',"chr(0x\\1)", $source);	// hex notation
		return $source;
	}

	/**
	  * Method to be called by another php script. Processes for SQL injection
	  * @access public
	  * @param Mixed $source - input string/array-of-string to be 'cleaned'
	  * @return String $source - 'cleaned' version of input parameter
	  */
	function safeSQL($source) {
		// clean all elements in this array
		if (is_array($source)) {
			foreach($source as $key => $value)
				// filter element for SQL injection
				if (is_string($value)) $source[$key] = $this->quoteSmart($this->decode($value));
			return $source;
		// clean this string
		} else if (is_string($source)) {
			// filter source for SQL injection
			if (is_string($source)) return $this->quoteSmart($this->decode($source));
		// return parameter as given
		} else return $source;
	}

	/**
	  * @author Chris Tobin
	  * @author Daniel Morris
	  * @access protected
	  * @param String $source
	  * @return String $source
	  */
	function quoteSmart($source) {
		// strip slashes
		if (get_magic_quotes_gpc()) $source = stripslashes($source);
		// quote both numeric and text
		$source = $this->escapeString($source);
		return $source;
	}

	/**
	  * @author Chris Tobin
	  * @author Daniel Morris
	  * @access protected
	  * @param String $source
	  * @return String $source
	  */
	function escapeString($string) {
		// depreciated function
		if (version_compare(phpversion(),"4.3.0", "<")) mysql_escape_string($string);
		// current function
		else mysql_real_escape_string($string);
		return $string;
	}

	/**
	 * @author Sidnei Augusto Drovetto Jr. - drovetto@gmail.com
	 * @param mixed $source A string or array of strings to be escaped
	 * @return mixed $source An object of the same type of the input, only escaped
	 * @access public
	 */
	function escapeHTML($source)
	{
		$output = $source;
		if (is_string($output))
			$output = htmlentities($output, ENT_QUOTES, 'ISO-8859-1');
		else
			if (is_array($output))
				foreach ($output as &$element)
					$element = $this->escapeHTML($element);

		return $output;
	}
}

?>
