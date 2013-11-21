<?php
/**
 * Creates an instance from a class requested by user
 * @param string $class_name Class' name to be instantiated
 * @return mixed
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @deprecated 2.2.00.000
 * @package Workflow 
 * @subpackage local 
 * @access public 
 */ 
function wf_create_object($class_name)
{
	wf_warn_deprecated_method('Factory', 'getInstance');
	$obj = null;
	$file_name = PHPGW_SERVER_ROOT.SEP.'workflow'.SEP.'inc'.SEP.'local'.SEP.'classes'.SEP.'class.'.$class_name.'.php';
	if(@file_exists($file_name))
	{
		include_once($file_name);
		$obj = new $class_name; 	
	}
	return $obj;
} 
?>
