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
		
	require_once '../../header.session.inc.php';
//	This script upload an CSV file to import contacts ....	
	if(array_key_exists('import_file', $_FILES) && $ftmp = $_FILES['import_file']['tmp_name']){				 		
		// Foi necessário modificar o caminho onde se recuperava o diretório temporário.
		// Esse caminho foi hardcodificado na string abaixo ( /tmp ) temporariamente, mas deve ser recuperada atraves
		// da configuracao do Expresso. Ver ticket #385.
		$fname = "/tmp/contacts_".md5(microtime()).".swp";					
		if(move_uploaded_file($ftmp, $fname))	
			$_SESSION['contactcenter']['importCSV'] = $fname;		
	}		
//	... or download an CSVfile to export contacts.	
	else if($_GET['file_name']) {	
		$file_name = $_GET['file_name'];	
		$file_path = $_GET['file_path'];
		header("Pragma: public");
		header ("Content-Type: application/octet-stream");
		header ("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header('Content-Length: ' . filesize($file_path)); 
		header("Content-disposition: attachment; filename=".$file_name);
		readfile($file_path);
		unlink($file_path);
	}
?>