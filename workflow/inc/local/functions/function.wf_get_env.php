<?php
/**
 * Returns the environment variable
 * @author Sidnei Augusto Drovetto Jr. - drovetto@gmail.com
 * @return array
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package Workflow 
 * @subpackage local 
 * @access public
 */
function wf_get_env()
{
	if (isset($GLOBALS['workflow_env']))
		return $GLOBALS['workflow_env'];
	else
		return false;
}
?>