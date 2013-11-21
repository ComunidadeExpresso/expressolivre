<?php
/**
 * Supporting class for file system handling.
 * @author Carlos Eduardo Nogueira Gonçalves
 * @version 1.0
 * @package Workflow
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 */
class FsUtils {
	
	/**
	 * @var string $configDir Path to configs files directory
	 * @access public
	 */
	var $configDir = null;
	/**
	 * @var string $processDir Path to workflow processes directory
	 * @access public
	 */
	var $processDir = null;
	/**
	 * @var array $errors Errors summary
	 * @access public
	 */
	var $errors = null;		
	/**
	 * Sets constants and initializes attributes
	 * @access public
	 * @return FileSystem
	 */
	function FsUtils()
	{
		$this->configDir = PHPGW_SERVER_ROOT . SEP . 'workflow' . SEP .'inc' . SEP . 'config' . SEP;
		$this->processDir = GALAXIA_PROCESSES . SEP;				
		$this->errors = array();		
	}
	/**
	 * Simple wrapper for appending errors to the summary
	 * @param string $errorMsg Error details
	 * @return void
	 * @access public
	 */
	function _appendError ($errorMsg) {
		array_push($this->errors, $errorMsg);
	}
	/**
	 * Clears error summary
	 * @return void
	 * @access public
	 */	
	function clearErrors () {
		$this->errors = array();
	}
	/** 
	 * Reads directories contents 
	 * @param string $path Directory path
	 * @return mixed  Directory's content where the array's keys are the file names and the values its contents or false in case of errors
	 * @access public  
	 */
	function readFolder ($path)	{

		$contents = array(); /* dir content */
		$dir = null; /* dir reading handle */	
		$file = null; /* each dir's file name */
		$absolute = $this->processDir . $path; /* absolute path to the processes dir - prevents access to other dirs */

		if (! is_dir($absolute) ) {			
			$this->_appendError("O caminho $absolute não é um diretório.");
			return false; 
		}
		
		if (! $dir = @opendir($absolute) ) {			
			$this->_appendError("O diretório $absolute não pode ser lido.");
			return false; 					
		} else {			
			while ( false !== ( $fileName = readdir($dir) ) ) {				
				$file = $absolute . $fileName; /* gets full path reference */
				if ( is_file($file) ) { /* ignores subdirs */										
					if (! is_readable($file) ) {
						$this->_appendError("O arquivo " . $path . $file . " não pode ser lido.");
					} else {
						@$contents[$fileName] = file_get_contents($file); /* reads each file contents */						
					}								
				}				
			}
		}
		
		if ( count($this->errors) ) { 
			return false;
		} else {
			return $contents;
		}
	}
	
	/**
	 * Loads configuration settings from *.ini files
	 * @param string $configFile File name
	 * @return array Configuration settings or an error message
	 * @access public
	 */
	function getConfig ($configFile) {		
		
		$absolute = $this->configDir . $configFile; /* absolute path to the config file - prevents access to other dirs */
		
		if (! file_exists($absolute) ) {			
			return "O arquivo $configFile não existe.";
		} elseif (! is_file($absolute) ) {
			return $configFile . " não é um arquivo comum.";
		} elseif (! is_readable($absolute) ) {
			return "O arquivo $configFile não pode ser lido.";
		}

		return parse_ini_file($absolute);
	}		
}
?>
