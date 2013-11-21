<?php
/**
 * Read file contents.
 * @param string $filename File's name to be read.
 * @return mixed String containing the file contents or false in case of error
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package Workflow
 * @subpackage local
 * @access public
 */
function wf_read_file($filename)
{
	/* reject empty file name */
	if (trim(basename($filename)) == "")
		return false;

	/* check if the file is stored within the process resource path */
	if (strpos($filename, '..') !== false)
		return false;

	/* complete path of the file */
	$filename = GALAXIA_PROCESSES . SEP . $GLOBALS['workflow']['wf_normalized_name'] . SEP . 'resources' . SEP . $filename;

	/* check if the file exists */
	if (!file_exists($filename))
		return false;

	/* get the file contents */
	$output = file_get_contents($filename);

	return $output;
}
?>
