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
 * The Factory frontend class.
 * This class controls which concrete Factory
 * will be used, depending on the current
 * 'security mode'. It lazy instantiates both
 * factories (process and module) when they are
 * required, and stores these objects. All the
 * accesses to factories are done through this class,
 * implementing a kind of Proxy design pattern.
 * This class depends on Security frontend class
 * to decide which factory to call.
 *
 * @package Factory
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @author Pedro Eugênio Rocha - pedro.eugenio.rocha@gmail.com
 */
class Factory {


	/**
	 * @var object $_unsecuredFactory Stores WorkflowFactory object.
	 * @access private
	 * @static
	 */
	private static $_unsecuredFactory = null;


	/**
	 * @var object $_securedFactory Stores ProcessFactory object.
	 * @access private
	 * @static
	 */
	private static $_securedFactory = null;


	/**
	 * Constructor. Just disable direct instantiation.
	 *
	 * @access public
	 * @return void
	 * @static
	 */
	public function __construct() {
		throw new Exception("Oops! Static only class.");
	}


	/**
	 * Just forward this call.
	 *
	 * @access public
	 * @return object
	 * @static
	 */
	public static function &getInstance() {

		$args = func_get_args();
		return self::_callMethod(__FUNCTION__, $args);
	}


	/**
	 * Just forward this call.
	 *
	 * @access public
	 * @return object
	 * @static
	 */
	public static function &newInstance() {

		$args = func_get_args();
		return self::_callMethod(__FUNCTION__, $args);
	}


	/**
	 * Selecting the proper factory to call. This function
	 * should never be called with a random $methodName. Allowed
	 * values are 'getInstance' and 'newInstance'.
	 *
	 * @param string $methodName Name of the BaseFactory method to call.
	 * @param array $args Parameters to class's constructor.
	 * @access private
	 * @return object
	 * @static
	 */
	private static function &_callMethod($methodName, $args) {

		/* security off (module space) */
		if (!Security::isEnabled()) {

			/* it must be instatiated */
			if (is_null(self::$_unsecuredFactory))
				self::$_unsecuredFactory = new WorkflowFactory();

			$className = array_shift($args);
			return self::$_unsecuredFactory->$methodName($className, $args);
		}
		/* oops. we are in the process space (restricted). */
		else {

			/* it must be instatiated */
			if (is_null(self::$_securedFactory))
				self::$_securedFactory = new ProcessFactory();

			$className = array_shift($args);

			/**
			 * If the class is not allowed, we must check who is trying
			 * to instantiate it. If it's a module guy, let's allow him.
			 * Throw up the exception otherwise.
			 */
			try {
				$obj = &self::$_securedFactory->$methodName($className, $args);
			}

			/**
			 * We are erroneously catching any exceptions. We should catch only the 'class not allowed'
			 * types of exceptions. To do so, a custom exception class must be defined.
		 	 */
			catch(Exception $e) {

				/**
				 * Here we are using depth 2 in isSafeDir method, because we are on a private
				 * method. Thus, we need to know if the "caller's caller's" function is on a
				 * safe dir, instead of the direct caller's method.
				 */
				if (Security::isSafeDir(2))
					$obj = &self::$_unsecuredFactory->$methodName($className, $args);

				/* naaasty one. take this! */
				else
					throw($e);
			}

			// finally
			return $obj;
		}
	}
}

?>
