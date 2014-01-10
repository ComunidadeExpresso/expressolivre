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
$fileType = $_GET['file_type'];
$msgFolder = $_GET['msg_folder'];
$msgNumber = $_GET['msg_num'];
$part = $_GET['msg_part'];
//-------------------------------------//


/*
 * Main
 */
$attachmentObj = new attachment();
$attachmentObj->setStructureFromMail($msgFolder, $msgNumber);
$fileContent = $attachmentObj->getAttachment($part);
$pic = @imagecreatefromstring($fileContent);
if($pic !== FALSE)
{
    header("Content-Type: ".$fileType);
    header("Content-Disposition: inline");
    $width = imagesx($pic);
    $height = imagesy($pic);
    $twidth = 160; # width of the thumb 160 pixel
    $theight = $twidth * $height / $width; # calculate height
    $theight =  $theight < 1 ? 1 : $theight;
    $thumb = imagecreatetruecolor ($twidth, $theight);
    imagecopyresized($thumb, $pic, 0, 0, 0, 0,$twidth, $theight, $width, $height); # resize image into thumb
    imagejpeg($thumb,"",75); # Thumbnail as JPEG
}
//------------------------------------------//
?>
