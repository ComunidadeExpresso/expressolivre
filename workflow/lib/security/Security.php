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
 * Security class for Workflow module.
 * You should never forget to call 'enable'
 * public method to enable security before
 * executing process code.
 *
 * @package Security
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @author Pedro Eugênio Rocha - pedro.eugenio.rocha@gmail.com
 */
class Security {

	/**
	 * @var boolean $_protection Stores the current security mode.
	 * @access private
	 * @static
	 */
	private static $_protection = false;


	/**
	 * Disallow the instantiation of this class.
	 * @access public
	 * @return void
	 */
	public function __construct() {
		throw new Exception("Oops! Static only class.");
	}


	/**
	 * Returns the current security mode.
	 * @access public
	 * @return boolean
	 * @static
	 */
	public static function isEnabled() {
		return self::$_protection;
	}


	/**
	 * Change to secured mode.
	 * @access public
	 * @return boolean
	 * @static
	 */
	public static function enable() {

		if (self::isSafeDir())
			self::$_protection = true;
		else
			throw new Exception('You are not allowed to change the security mode.');
		return true;
	}

	/**
	 * Change to unsecured mode.
	 * @access public
	 * @return boolean
	 * @static
	 */
	public static function disable() {

		if (self::isSafeDir())
			self::$_protection = false;
		else
			throw new Exception('You are not allowed to change the security mode.');
		return true;
	}


	/**
	 * Implements the security validation.
	 * This function tell us if a fileName is on a safe directory.
	 * For safe dir we mean that no process code exists under it.
	 * The 'depth' parameter specifies the deepness of the file that
	 * we are validating. Default value is to validate the imediate
	 * previous function.
	 *
	 * @param integer $depth The deepness of the fileName in backtrace.
	 * @access public
	 * @return boolean
	 * @static
	 */
	public static function isSafeDir($depth = 1) {

		/* our backtrace based policy */
		$backtrace = debug_backtrace();
		$originFile = $backtrace[$depth]['file'];

		if (empty($originFile))
			return false;

		/* if $fileName is a file under our server root, then it's safe. */
		if (substr_compare($originFile, EGW_SERVER_ROOT, 0, strlen(EGW_SERVER_ROOT)) == 0)
			return true;
		return false;
	}
}
?>
