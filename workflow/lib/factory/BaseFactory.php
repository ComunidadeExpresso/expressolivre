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
 * Base Factory abstract class.
 * This class implements all the common behaviour of
 * it's specialized classes (ProcessFactory and WorkflowFactory).
 * The only class allowed to use it's methods should be the
 * Factory frontend.
 *
 * @package Factory
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @author Pedro Eugênio Rocha - pedro.eugenio.rocha@gmail.com
 */
abstract class BaseFactory {

	/**
	 * @var array $_fileInfo Store what classes we can instantiate.
	 * @access private
	 */
	private $_fileInfo;


	/**
	 * Registers classes into '$_fileInfo' private
	 * atribute.
	 *
	 * @param string $className Class name.
	 * @param string $fileName Name of the file that contains the class definition.
	 * @param string $relativePath The path to the file, relative to $basePath.
	 * @param string $basePath Base path to append $relativePath. Optional.
	 * @access protected
	 * @return void
	 */
	protected final function registerFileInfo($className, $fileName, $relativePath, $basePath = WF_SERVER_ROOT) {

		/* default is to override */
		$this->_fileInfo[$className] = array(	'filename' => $fileName,
												'path' => $relativePath,
												'basePath' => $basePath,
												'instance' => null);
	}


	/**
	 * Here we should do all the factory stuff.
	 * The classes instatiated here will be stored
	 * into our private object cache. If there is no
	 * object of the given type into the cache, 'newInstance'
	 * will be called. This method must deal with
	 * pointers (&) to avoid object duplications.
	 *
	 * @param string $className Name of the class.
	 * @param array $classArgs Parameters to the class's constructor.
	 * @access public
	 * @return object
	 */
	public function &getInstance($className, $classArgs){

		/* have we a class name? */
		if (empty($className))
			return null;

		/* recovering class data */
		if ($entry = &$this->_getEntry($className)) {

			if (is_null($entry['instance'])) {

				/* we must instantiate it */
                if (($obj = &$this->newInstance($className, $classArgs)) == null)
					throw new Exception("Unable to instantiate '".$className."'");

				/* saving the object reference */
				$this->_setEntryInstance($className, $obj);
				return $obj;
			}
			return $entry['instance'];

		}
		/* class not allowed */
		else
			throw new Exception("You are not allowed to instantiate class '".$className."'.");
	}

	/**
	 * Instantiating classes.
	 *
	 * @todo Maybe we don't have to use the reflection
	 *		 class here. Future work...
	 * @param string $className Name of the class.
	 * @param array $classArgs Parameters to the class's constructor.
	 * @access public
	 * @return object
	 */
	public function &newInstance($className, $classArgs){

		/* have we a class name? */
		if (empty($className))
			return null;

		/* just to be sure. If this method fails, it will throw an exception anyway... */
		if(!$this->_import($className))
			return null;

		/**
		 * Here we use this big white elephant (by big white elephant I mean php
		 * reflection interface) just because we have to pass an unknown number
		 * of parameters to the constructor of the class.
		 * If you know a better way to do this, please update this code =D
		 */
		$reflectionObj = new ReflectionClass($className);

		if (count($classArgs) == 0)
			return $reflectionObj->newInstance();
		return $reflectionObj->newInstanceArgs($classArgs);
	}


	/**
	 * Private stuff. Handles the cache and information
	 * '$_fileInfo' array.
	 *
	 * @param string $className Key to search for into the internal cache.
	 * @access private
	 * @return array
	 */
	private function &_getEntry($className){
		return $this->_fileInfo[$className];
	}


	/**
	 * Stores the given object into the internal cache
	 * for upcoming requests.
	 *
	 * @param string $className Name of the class.
	 * @param object Object whose reference will be stored.
	 * @access private
	 * @return boolean
	 */
	private function _setEntryInstance($className, &$obj){

		if (is_array($this->_fileInfo[$className])) {
			$this->_fileInfo[$className]['instance'] = &$obj;
			return true;
		}
		return false;
	}


	/**
	 * Including (requiring_once ;P ) the file(s) itself. Ideally,
	 * it could be the only place to include files.
	 *
	 * @param string $className Key to search for into internal cache.
	 * @access private
	 * @return boolean
	 */
	private function _import($className){

		/* not found */
		if (!($entry = $this->_getEntry($className)))
			throw new Exception('You are not allowed to instantiate \''.$className.'\' class.');

		$fullPath = $entry['basePath'] . '/' . $entry['path'] . '/' . $entry['filename'];

		/* file not found */
		if (!file_exists($fullPath))
			throw new Exception("File '".$fullPath."' not found.");

		/* including file */
		require_once $fullPath;
		return true;
	}
}
?>
