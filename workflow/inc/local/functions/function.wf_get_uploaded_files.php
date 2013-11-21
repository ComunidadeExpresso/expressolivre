<?php
/**
 * Gets uploaded files
 * @param string $name File's name
 * @return array
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package Workflow 
 * @subpackage local 
 * @access public
 */
function wf_get_uploaded_files( $name )
{
	if (!isset($_FILES[$name]))
		return array();
	
	$filesArray = $_FILES[$name];
	$fileAtributes = array_keys($filesArray);
	$numberOfFiles = count($filesArray['name']);
	$return = array();
	
	if ((!is_array($filesArray['name'])))
	{
		$return[0] = $filesArray;
		$return[0]['content'] = file_get_contents($filesArray['tmp_name']);
	}
	else
		for ($i = 0; $i < $numberOfFiles; ++$i)
		{
			foreach ($fileAtributes as $atribute)
				$return[$i][$atribute] = $filesArray[$atribute][$i];
			$return[$i]['content'] = file_get_contents($filesArray['tmp_name'][$i]);
		}
	
	return $return;
}
?>
