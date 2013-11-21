<?php
/**
 * Writes new contents to files.
 * @param string $filename File's name to be written.
 * @param string $content New contents to be inserted.
 * @return boolean
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package Workflow
 * @subpackage local
 * @access public
 */
function wf_write_file($filename, $content)
{
	/* reject empty file name */
	if (trim(basename($filename)) == '')
		return false;

	/* reject "php" extensions */
	if (strpos(strtolower(strrchr($filename, '.')), 'php') !== false)
		return false;

	/* check if the file will be stored inside the process resource path */
	if (strpos($filename, '..') !== false)
		return false;

	$processPath = GALAXIA_PROCESSES . SEP . $GLOBALS['workflow']['wf_normalized_name'] . SEP . 'resources';
	$filename = $processPath . SEP . $filename;

	$subDirectories = explode(SEP, substr($filename, strlen($processPath) + 1));

	/* reject directories with empty name */
	foreach ($subDirectories as $subDir)
		if (trim($subDir) == "")
			return false;

	array_pop($subDirectories);
	$baseDir = $processPath;
	foreach ($subDirectories as $subDir)
	{
		$baseDir .= SEP . $subDir;
		if (!is_dir($baseDir))
			@mkdir($baseDir, 0770);
	}

	$fileHandle = fopen($filename, 'w+');
	fwrite($fileHandle, $content);
	fclose($fileHandle);

	return true;
}
?>
