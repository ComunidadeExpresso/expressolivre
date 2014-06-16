<?php
if(!isset($GLOBALS['phpgw_info'])){
        $GLOBALS['phpgw_info']['flags'] = array(
                'currentapp' => 'expressoMail',
                'nonavbar'   => true,
                'noheader'   => true
        );
}
require_once '../header.inc.php';
require_once '../services/class.servicelocator.php';

	//	Explode action from cExecuteForm function
	$cExecuteFormReturn = false;
 	if( isset( $_POST['_action'] ) ) { 		
 		if($_FILES) {
 			$count_files = $_POST['countFiles'];
			$array_files = array(); 		
 			for($idx = 1; $idx <= $count_files; ++$idx) {
 				if(array_key_exists('file_'.$idx , $_FILES) && $_FILES['file_'.$idx] && !$_FILES['file_'.$idx]['error'])
 					$array_files[] = $_FILES['file_'.$idx]; 					 
 			}
 			$_POST['FILES'] = $array_files;
 		} 		  		
		$get_p = explode('.',@$_POST['_action']);

 		$cExecuteFormReturn = true;
 	}
 	//	Explode action from cExecute function
 	else if(array_key_exists('action', $_GET))
			$get_p = explode('.',@$_GET['action']);
			

	// NO ACTION
	else
		return $_SESSION['response'] = 'Post-Content-Length';
	
	// Load dinamically class file.
	if($get_p[0] == '$this')
		$filename = 'inc/class.'.$get_p[1].'.inc.php';
	else
		$filename = '../'.$get_p[0].'/inc/class.'.$get_p[1].'.inc.php';
		
	include_once($filename);	
	
	// Create new Object  (class loaded).	
	$obj = new $get_p[1];
	
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
		$result = $obj -> $get_p[2]($params);
	else 		
		$result = $obj -> $get_p[2]();
		
	// Return result serialized.	
	

	if(!$cExecuteFormReturn /*&& (!$_REQUEST['isPost'])*/ )
		if(array_key_exists(3,$get_p))
			echo $result;
		else
		echo serialize($result);
	else
		$_SESSION['response'] = $result;
?>
