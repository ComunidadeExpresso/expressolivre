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
 * Redefines the apache_request_headers function if it was not done before by a phpmodule
 * This usually happens if php runs as cgi
 * @return array HTTP info presents in $_SERVER array
 * @package Workflow
 * @access public
 */
if( !function_exists('apache_request_headers') ) {
	///
	function apache_request_headers() {
		$arh = array();
		$rx_http = '/\AHTTP_/';
		foreach($_SERVER as $key => $val) {
			if( preg_match($rx_http, $key) ) {
				$arh_key = preg_replace($rx_http, '', $key);
				$rx_matches = array();
				// do some nasty string manipulations to restore the original letter case
				// this should work in most cases
				$rx_matches = explode('_', $arh_key);
				if( count($rx_matches) > 0 and strlen($arh_key) > 2 ) {
					foreach($rx_matches as $ak_key => $ak_val) $rx_matches[$ak_key] = ucfirst($ak_val);
					$arh_key = implode('-', $rx_matches);
				}
				$arh[$arh_key] = $val;
			}
		}
		return( $arh );
	}
	///
}

/**
 * Generates warning message of deprecated methods executing PHP function trigger_error
 * @author Anderson Tadayuki Saikawa
 * @param string $new_class The name of the class that has the method that replaces the deprecated one
 * @param string $new_method The name of the method that replaces the deprecated one
 * @return boolean
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package Workflow
 * @access public
 */
function wf_warn_deprecated_method($new_class = null, $new_method = null)
{
	$caller = next(debug_backtrace());
	$old_class = !empty($caller['class']) ? " of <strong>" . $caller['class'] . "</strong> object" : "";
	$deprecated_msg = sprintf("Deprecated method <strong>%s</strong>%s was called in <strong>%s</strong> on line <strong>%s</strong>. It MUST be replaced by its equivalent",
								$caller['function'], $old_class, $caller['file'], $caller['line']);
	if(!empty($new_class) && !empty($new_method)){
		$new_class = !empty($new_class) ? " of <strong>" . $new_class . "</strong> object" : "";
		$deprecated_msg .= sprintf(' <strong>%s</strong>%s', $new_method, $new_class);
	}
	$error_msg = $deprecated_msg . ".\n<br>Error handler";
	return trigger_error("[WORKFLOW WARNING]: " . $error_msg, E_USER_WARNING);
}

?>
