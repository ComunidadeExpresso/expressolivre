<?php
/**************************************************************************\
* eGroupWare                                                               *
* http://www.egroupware.org                                                *
* The file written by Mário César Kolling <mario.kolling@serpro.gov.br>    *
* --------------------------------------------                             *
*  This program is free software; you can redistribute it and/or modify it *
*  under the terms of the GNU General Public License as published by the   *
*  Free Software Foundation; either version 2 of the License, or (at your  *
*  option) any later version.                                              *
\**************************************************************************/

class common_functions
{
	function borkb($size, $enclosed = NULL) 
	{
		if (!$size)
			$size = 0;

		if ($enclosed)
		{
			$left = '(';
			$right = ')';
		}

		if ($size < 1024)
			$rstring = $left . $size . ' B' . $right;
		else if ($size < 1048576)
			$rstring = $left . round($size/1024) . ' KB' . $right;
		else if ($size < 1073741824)
			$rstring = $left . round($size/1024/1024) . ' MB' . $right;
		else
			$rstring = $left . round($size/1024/1024/1024) . ' GB' . $right;

		return $rstring;
	}

	function complete_string($str = "", $length = 10, $align = "R", $char = " ") {
		if( $str == null )
			$str = "";
		else 
		if( strlen($str) > $length ) {
			return substr($str, 0, $length);
		} else if( strlen($str) == $length ) {
			return $str;
		}			

		$char = substr($char, 0, 1);
		$complete_str = "";

		while( strlen($str) + strlen($complete_str) < $length  )
			$complete_str .= $char;

		if( $align == "L" )
			return $str . $complete_str;
		else
			return $complete_str . $str;
	}		

	function strach_string($string,$size) {
		return strlen($string)>$size ? substr($string,0,$size)."...":
		$string;
	}

	/**
	* Fixes the odd indexing of multiple file uploads from the format:
	*
	* $_FILES['field']['key']['index']
	*
	* To the more standard and appropriate:
	*
	* $_FILES['field']['index']['key']
	*
	* @param array $files
	* @author Corey Ballou
	* @link http://www.jqueryin.com
	*/
	function fixFilesArray(&$files)
	{
		$names = array( 'name' => 1, 'type' => 1, 'tmp_name' => 1, 'error' => 1, 'size' => 1);

		foreach ($files as $key => $part) {
			// only deal with valid keys and multiple files
			$key = (string) $key;
			if (isset($names[$key]) && is_array($part)) {
				foreach ($part as $position => $value) {
					$files[$position][$key] = $value;
				}
				// remove old key reference
				unset($files[$key]);
			}
		}
	}

} //end common class


?>
