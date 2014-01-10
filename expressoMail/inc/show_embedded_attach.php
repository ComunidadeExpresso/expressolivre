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
		
/*
 * Requieres
 */
 require_once '../../header.session.inc.php';
 require_once dirname(__FILE__).'/class.attachment.inc.php';
 //------------------------//

 /*
  * Get variables
  */
$msgFolder = $_GET['msg_folder'];
$msgNumber = $_GET['msg_num'];
$embeddedPart = $_GET['msg_part'];
//-------------------------------------//


/*
 * Main
 */
$attachmentObj = new attachment();
$attachmentObj->setStructureFromMail($msgFolder, $msgNumber);
$fileContent = $attachmentObj->getAttachment($embeddedPart);

header("Content-Type: image/jpeg");
header("Content-Disposition: inline");

echo $fileContent;
//------------------------------------------//

?>
