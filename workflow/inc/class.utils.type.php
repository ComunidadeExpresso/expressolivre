<?php
require_once('class.utils.php');
/**
 * Contains useful methods for PHP type checking and casting
 * @author Carlos Eduardo Nogueira Gonçalves
 * @author Marcos Pont
 * @version 1.0
 * @link http://workflow.celepar.parana/doc-workflow/classes/typeutils Complete reference
 * @package Workflow
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 */
class TypeUtils extends Utils
{	
	/**
	 * Return the type of variable
	 * @param $value 
	 * @return type of element
	 * @access public
	 */
	function getType($value) {
		return gettype($value);
	}
	
	/**
	 * Check if value is a float number
	 * @param int  $value 
	 * @param bool $stritct
	 * @access public
	 * @return bool 
	 */
	function isFloat(&$value, $strict=FALSE) {
		$locale = localeconv();
		$dp = $locale['decimal_point'];
		$exp = "/^\-?[0-9]+(\\" . $dp . "[0-9]+)?$/";
		if (preg_match($exp, $value)) {
			if (!$strict && !is_float($value)) {
				$value = $this->parseFloat($value);
			}
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	/**
	 * Parse Float number
	 * @param string $value
	 * @access public
	 * @return float
	 */
	function parseFloat($value) {
		if ($this->isString($value)) {
			$locale = localeconv();
			if ($locale['decimal_point'] != '.') {
				$value = str_replace($locale['decimal_point'], '.', $value);
			}
		}
		return floatval($value);
	}
	
	
	/**
	 * Parse Float Positive
	 * @param string $value
	 * @access public
	 * @return int
	 */
	function parseFloatPositive($value) {
		return abs(floatval($value));
	}
	
	/**
	 * Checks if $value is a string
	 * @param string $value
	 * @access public
	 * @return bool
	 */
	function isString($value) {
		return is_string($value);
	}

	/**
	 * Checks if $values is a array
	 * @param string $value
	 * @access public
	 * @return bool
	 */
	function isArray($value) {
		return is_array($value);
	}
	
	/**
	 * Checks if $value is a Hash array
	 * @param string $value
	 * @access public
	 * @return bool
	 */
	function isHashArray($value) {
		if (is_array($value) && sizeof($value)) {
			$i = 0;
			$keys = array_keys($value);
			foreach ($keys as $k=>$v) {
				if ($v !== $i) {
					return TRUE;
				}
				++$i;
			}
		}
		return FALSE;
	}
	
	/**
	 * the value to array
	 * @param mixed $value 
	 * @access public
	 * @return array
	 */
	function toArray($value) {
		return is_array($value) ? $value : array($value);
	}
	
	/**
	 * Check if the parameter is a object
	 * @param mixed $value type to test
	 * @access public
	 * @return bool
	 */
	function isObject($value) {
		return is_object($value);
	}
	
	/**
	 * Check if object is instance of class
	 * @param object $object
	 * @param string $className
	 * @param bool   $recurse
	 * @return bool
	 * @access public
	 */
	function isInstanceOf($object, $className, $recurse=TRUE) {
		if (!is_object($object))
			return FALSE;
		$objClass = get_class($object);
		$otherClass = ($this->isPHP(5) ? $className : strtolower($className));
		if ($recurse)
			return ($objClass == $otherClass || is_subclass_of($object, $otherClass));
		return ($objClass == $otherClass);
	}
	
	/**
	 * Check if the parameter is a Resource 
	 * @param mixed $value
	 * @return bool
	 * @access public
	 */
	function isResource($value) {
		if (is_resource($value))
			return get_resource_type($value);
		return FALSE;
	}

	/**
	 * Checks if the value is a boolean
	 * @param mixed $value
	 * @return bool
	 * @access public
	 */
	function isBoolean($value) {
		return ($value === TRUE || $value === FALSE);
	}
	
	/**
	 * Check if the value is a boolean and true
	 * @param bool $value
	 * @return bool  
	 * @access public
	 */
	function isTrue($value) {
		return ($value === TRUE);
	}

	/**
	 * Return a default value if first parameter is false
	 * @param  mixed $value   test value
	 * @param  mixed $default default value to return if first parameter is false
	 * @access public
	 */
	function ifFalse($value, $default = FALSE) {
		if ($value === FALSE)
			return $default;
		return $value;
	}
	
	/**
	 * Converts value to boolean 0 false otherwise true
	 * @param mixed $value
	 * @return bool true or false
	 * @access public
	 */
	function toBoolean($value) {
		return (bool)$value;
	}
	
	/**
	 * Checks if value parameter is empty
	 * @param mixed $value
	 * @return bool true empty otherwise false 
	 * @access public
	 */
	function isEmpty($value) {
		$result = empty($value);
		return $result;
	}
}
?>