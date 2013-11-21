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
header("Content-Type: image/jpeg");

if($_GET['msg_num'] != null && $_GET['msg_part'] != null && $_GET['msg_folder'] != null)
{
    $attachmentObj = new attachment();
    $attachmentObj->setStructureFromMail($msgFolder, $msgNumber);
    $fileContent = $attachmentObj->getAttachment($part);
    header("Content-Disposition: inline");

    echo $fileContent;
}
else if($_SESSION['phpgw_info']['expressomail']['contact_photo'])
{
    $data  = $_SESSION['phpgw_info']['expressomail']['contact_photo'];

    if($data)
    {
        $photo = imagecreatefromstring($data[0]);
        if($photo)
        {
                $width = imagesx($photo);
                $height = imagesy($photo);
                $twidth = 60;
                $theight = 80;
                $small_photo = imagecreatetruecolor ($twidth, $theight);
                imagecopyresampled($small_photo, $photo, 0, 0, 0, 0,$twidth, $theight, $width, $height);
                imagejpeg($small_photo,'',100);
                unset($_SESSION['phpgw_info']['expressomail']['contact_photo']);
        }
    }
}
else
       readfile("./../../contactcenter/templates/default/images/photo_celepar.png");
//------------------------------------------//

?>
