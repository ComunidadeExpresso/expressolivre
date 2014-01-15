<?php
		/*************************************************************************** 
		* Expresso Livre                                                           * 
		* http://www.expressolivre.org                                             * 
		* --------------------------------------------                             * 
		*  This program is free software; you can redistribute it and/or modify it * 
		*  under the terms of the GNU General Public License as published by the   * 
		*  Free Software Foundation; either version 2 of the License, or (at your  * 
		*  option) any later version.                                              * 
		\**************************************************************************/ 
		
if(!isset($GLOBALS['phpgw_info'])){
        $GLOBALS['phpgw_info']['flags'] = array(
                'currentapp' => 'expressoAdmin1_2',
                'nonavbar'   => true,
                'noheader'   => true
        );
}
require_once '../header.session.inc.php';
require_once '../services/class.servicelocator.php';

	//	Explode action from cExecuteForm function
	$cExecuteFormReturn = false;
 	if( isset($_POST['_action']) && $_POST['_action']) {
 		if($_FILES) {
 			$count_files = $_POST['countFiles'];
			$array_files = array(); 		
 			for($idx = 1; $idx <= $count_files; ++$idx) {
 				if($_FILES['file_'.$idx] && !$_FILES['file_'.$idx]['error'])
 					$array_files[] = $_FILES['file_'.$idx]; 					 
 			}
 			$_POST['FILES'] = $array_files;
 		} 		  		
 		list($app,$class,$method) = explode('.',@$_POST['_action']);
 		$cExecuteFormReturn = true;
 	}
 	//	Explode action from cExecute function
 	else if(isset($_GET['action']) && $_GET['action'])
    {
        $aAction =  explode('.',$_GET['action']);
        $app = isset($aAction[0]) ? $aAction[0] : null;
        $class = isset($aAction[1]) ? $aAction[1] : null;
        $method = isset($aAction[2]) ? $aAction[2] : null;
    }
	else // NO ACTION
		return $_SESSION['response'] = 'false';
	
	// Load dinamically class file.
	if($app == '$this')
		$filename = 'inc/class.'.$class.'.inc.php';
	else if( strpos($app, '$this/') !== false)
	{
		$filename = str_replace('$this/','',$app) . '.php';
		include_once($filename);
		exit;
	}
	else
		$filename = '../'.$app.'/inc/class.'.$class.'.inc.php';
		
	include_once($filename);
	
	// Create new Object  (class loaded).	
	$obj = new $class;
	
	// Prepare parameters for execution.	
	$params = array();
	
	// If array $_POST is not null , the submit method is POST. 
	if($_POST) {
		$params = $_POST;
	}
	// If array $_POST is null , and the array $_GET > 1, the submit method is GET.
	else if(count($_GET) > 1)	{		
		array_shift($_GET);
		$params = $_GET;
	}

	$result = array();
	
	// if params is not empty, then class method with parameters.	
	if($params)
		$result = $obj -> $method($params);
	else 		
		$result = $obj -> $method();

    $_SESSION['response'] = $result;

    // Return result serialized.
	if(!$cExecuteFormReturn)
		echo serialize($result);
?>