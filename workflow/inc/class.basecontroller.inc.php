<?php
/**
 * Manage workflow module processes providing support for MVC architecture by controlling the process flow.
 * @author Carlos Eduardo Nogueira Gonчalves
 * @version 1.3
 * @package Workflow
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 */
class BaseController 
{	
	/* begin attributes declaration */

	/**
     * @var object $view Holds view layer instance 
     * @access public
     */		
	var $view = null;

	/**
     * @var string $templateFile Template file reference 
     * @access public
     */
	var $templateFile = null;       

	/**
     * @var object $model Holds model layer instance
     * @access public
     */
	var $model = null;

	/* ends attributes declaration */
	/**
     * Base controller layer constructor
     * 
     * @access public
     * @param object $model Model layer instance for activities business logic
     * @return void
     * @param array $env MVC environment settings
     */
	function BaseController(&$model , &$env) {
		/* Gets other layers instances for controlling them */
		$this->view          =&  $env['view'];
		$this->templateFile  =&  $env['template_file'];
		$this->model         =&  $model;
	}
	/* begin methods declaration */
	
    /**
     * Shows requested interface (template)
     * @param string $file Template file to be presented
     * @return void
     * @access public
     */
	function showForm($file) {
		$this->templateFile = $file;
	}
   /**
    * Assigns all model's attributes into view layer
    * @return void.
    * @access public
    */
	function syncVars()	{           
		/* retrieves only process attributes */		
		$processVars = $this->model->getAttributes();
		
		/* makes synchronization */
		foreach ( $processVars as $var => $value ) {
			$this->assign($var, $value);
		}
	}
	
   /** 
    * Fills view vars with registered vars from model layer
    * @return void      
    * @access public
    */
    function loadViewVars() {   	
		foreach ( $this->model->viewData as $var => $value ) {
			$this->assign($var, $value);
		}
    }
   
   /**
    * Assigns value to a view var
    * @param string $var Template var name to be assigned
    * @param mixed $value Value assigned
    * @return void
    * @access public 
    */
	function assign($var, $value) {   	
		if (!empty($this->view))
	        $this->view->assign($var, $value);
    }   
    
   /**
    * Halts activities execution
    * @return void
    * @access public
    */
	function cancelar() {		
		$this->model->setWfProperty($this->model->CANCELAR , true);
	}    

   /**
    * Normalizes methods names replacing upper case letters by lower case ones,  
    * blank spaces by underline chars and non-english chars by english ones.  
    * @return string normalized method name
    * @access public
    */
	function getNormalizedAction($str) {
		$ts = array("/[Р-Х]/", "/Ц/", "/Ч/", "/[Ш-Ы]/", "/[Ь-Я]/", "/а/", "/б/", "/[в-жи]/", "/з/", "/[й-м]/", "/н/", "/п/", "/[р-х]/", "/ц/", "/ч/", "/[ш-ы]/", "/[ь-я]/", "/№/", "/ё/", "/[ђ-іј]/", "/ї/", "/[љ-ќ]/", "/[§-џ]/");
		$tn = array("A", "AE", "C", "E", "I", "D", "N", "O", "X", "U", "Y", "ss", "a", "ae", "c", "e", "i", "d", "n", "o", "x", "u", "y");
		$output = strtolower(preg_replace($ts, $tn, $str));
		while ($i = strpos($output, ' '))
			$output = substr_replace($output, strtoupper($output[$i+1]), $i, 2);
		return $output;
	}
	
   /**
    * Searches and runs requested action's implementation in model layer
    * @param string $action Requested action
    * @return void
    * @access public
    */
	function dispatch($action)
	{
		$action = $this->getNormalizedAction($action);
    	if (method_exists($this, $action))
    		$this->{$action}();
    	else
    		$this->__default();
	}

   /**
    * Default activities action
    * @return void
    * @access public
    */
	function __default() {}
	
   /** 
    * Runs activity. It must be implemented in activities controller classes 
    * @abstract
    * @param string $action Requested action
    * @return void
    * @access public
    */    
    function run($action) {}

   /**
    * Provides constructor access to its subclasses for processes startup settings
    * @param object $model Model layer instance for activities business logic
    * @param array $env MVC environment settings 
    * @return void
    * @access public
    */
    function super(&$model , &$env) {
        BaseController::BaseController(&$model , &$env);
    } 
	/* ends method declarations */
}

?>