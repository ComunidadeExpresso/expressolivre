<?php
/**
 * Includes files from the process folder.
 * @param $file_name File's name to be included.
 * @return void
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package Workflow 
 * @subpackage local 
 * @access public
 */
function wf_include( $file_name )
{
	if ( strpos($file_name,'..' ) === false )
	{
		require_once( GALAXIA_PROCESSES.SEP.$GLOBALS['workflow']['wf_normalized_name'].SEP.'code'.SEP.$file_name );
	}
} 
?>
