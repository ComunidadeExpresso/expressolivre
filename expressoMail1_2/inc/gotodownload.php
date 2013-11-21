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
		
if(!isset($GLOBALS['phpgw_info'])){
	$GLOBALS['phpgw_info']['flags'] = array(
		'currentapp' => 'expressoMail1_2',
		'nonavbar'   => true,
		'noheader'   => true
	);
}
require_once '../../header.inc.php';


	$msg_number = $_GET['msg_number'];
	$idx_file = $_GET['idx_file'];
        $newfilename = html_entity_decode(rawurldecode($_GET['newfilename']));
	$msg_part = $_GET['msg_part'];
	$msg_folder = $_GET['msg_folder'];
	$msg_folder = mb_convert_encoding($msg_folder,"UTF7-IMAP", mb_detect_encoding($msg_folder, "UTF-8, ISO-8859-1", true));

	$encoding = strtolower($_GET['encoding']);
	$fileContent = "";

	if($msg_number && $msg_part && $msg_folder && (intval($idx_file == '0' ? '1' : $idx_file))) {
		$username = $_SESSION['phpgw_info']['expressomail']['user']['userid'];
		$password = $_SESSION['phpgw_info']['expressomail']['user']['passwd'];
		$imap_server = $_SESSION['phpgw_info']['expressomail']['email_server']['imapServer'];
		$imap_port 	= $_SESSION['phpgw_info']['expressomail']['email_server']['imapPort'];
		if ($_SESSION['phpgw_info']['expressomail']['email_server']['imapTLSEncryption'] == 'yes')
		{
			$imap_options = '/tls/novalidate-cert';
		}
		else
		{
			$imap_options = '/notls/novalidate-cert';
		}
		$mbox_stream = imap_open("{".$imap_server.":".$imap_port.$imap_options."}".$msg_folder, $username, $password);
		$fileContent = imap_fetchbody($mbox_stream, $msg_number, $msg_part, FT_UID);
		/*
		 *Removed by Bug #546
		 *include("class.imap_attachment.inc.php");
		 *$imap_attachment = new imap_attachment();
		 *$a = $imap_attachment->download_attachment($mbox_stream, $msg_number);
		 *$filename = $a[$idx_file]['name'];
		 */
		$filename = $newfilename;
	}
	else
		$filename = $idx_file;

	$filename 	 = $filename 	? $filename 	: "attachment.bin";
	$newfilename = $newfilename ? $newfilename 	: $filename;
	$strFileType = strrev(substr(strrev(strtolower($filename)),0,4));
	if(strpos($strFileType ,"." )===false)
		$strFileType = strrev(substr(strrev(strtolower($newfilename)),0,4));

	downloadFile($strFileType, $filename, $newfilename, $fileContent, $encoding);

	function downloadFile($strFileType, $strFileName, $newFileName, $fileContent, $encoding) {
		//avoid stuck request
		session_write_close();
		$ContentType = "application/octet-stream";

		if ($strFileType == ".asf")
			$ContentType = "video/x-ms-asf";
		if ($strFileType == ".avi")
			$ContentType = "video/avi";
		if ($strFileType == ".doc")
			$ContentType = "application/msword";
		if ($strFileType == ".zip")
			$ContentType = "application/zip";
		if ($strFileType == ".xls")
			$ContentType = "application/vnd.ms-excel";
		if ($strFileType == ".gif")
			$ContentType = "image/gif";
		if ($strFileType == ".jpg" || $strFileType == "jpeg")
			$ContentType = "image/jpeg";
		if ($strFileType == ".wav")
			$ContentType = "audio/wav";
		if ($strFileType == ".mp3")
			$ContentType = "audio/mpeg3";
		if ($strFileType == ".mpg" || $strFileType == "mpeg")
			$ContentType = "video/mpeg";
		if ($strFileType == ".rtf")
			$ContentType = "application/rtf";
		if ($strFileType == ".htm" || $strFileType == "html")
			$ContentType = "text/html";
		if ($strFileType == ".xml")
			$ContentType = "text/xml";
		if ($strFileType == ".xsl")
			$ContentType = "text/xsl";
		if ($strFileType == ".css")
			$ContentType = "text/css";
		if ($strFileType == ".php")
			$ContentType = "text/php";
		if ($strFileType == ".asp")
			$ContentType = "text/asp";
		if ($strFileType == ".pdf")
			$ContentType = "application/pdf";
		if ($strFileType == ".txt")
			$ContentType = "text/plain";
		if ($strFileType == ".log")
			$ContentType = "text/plain";
		if ($strFileType == ".wmv")
			$ContentType = "video/x-ms-wmv";
		if ($strFileType == ".sxc")
			$ContentType = "application/vnd.sun.xml.calc";
		if ($strFileType == ".odt")
			$ContentType = "application/vnd.oasis.opendocument.text";
		if ($strFileType == ".stc")
			$ContentType = "application/vnd.sun.xml.calc.template";
		if ($strFileType == ".sxd")
			$ContentType = "application/vnd.sun.xml.draw";
		if ($strFileType == ".std")
			$ContentType = "application/vnd.sun.xml.draw.template";
		if ($strFileType == ".sxi")
			$ContentType = "application/vnd.sun.xml.impress";
		if ($strFileType == ".sti")
			$ContentType = "application/vnd.sun.xml.impress.template";
		if ($strFileType == ".sxm")
			$ContentType = "application/vnd.sun.xml.math";
		if ($strFileType == ".sxw")
			$ContentType = "application/vnd.sun.xml.writer";
		if ($strFileType == ".sxq")
			$ContentType = "application/vnd.sun.xml.writer.global";
		if ($strFileType == ".stw")
			$ContentType = "application/vnd.sun.xml.writer.template";
		if ($strFileType == ".ps")
			$ContentType = "application/postscript";
		if ($strFileType == ".pps")
			$ContentType = "application/vnd.ms-powerpoint";
		if ($strFileType == ".odt")
			$ContentType = "application/vnd.oasis.opendocument.text";
		if ($strFileType == ".ott")
			$ContentType = "application/vnd.oasis.opendocument.text-template";
		if ($strFileType == ".oth")
			$ContentType = "application/vnd.oasis.opendocument.text-web";
		if ($strFileType == ".odm")
			$ContentType = "application/vnd.oasis.opendocument.text-master";
		if ($strFileType == ".odg")
			$ContentType = "application/vnd.oasis.opendocument.graphics";
		if ($strFileType == ".otg")
			$ContentType = "application/vnd.oasis.opendocument.graphics-template";
		if ($strFileType == ".odp")
			$ContentType = "application/vnd.oasis.opendocument.presentation";
		if ($strFileType == ".otp")
			$ContentType = "application/vnd.oasis.opendocument.presentation-template";
		if ($strFileType == ".ods")
			$ContentType = "application/vnd.oasis.opendocument.spreadsheet";
		if ($strFileType == ".ots")
			$ContentType = "application/vnd.oasis.opendocument.spreadsheet-template";
		if ($strFileType == ".odc")
			$ContentType = "application/vnd.oasis.opendocument.chart";
		if ($strFileType == ".odf")
			$ContentType = "application/vnd.oasis.opendocument.formula";
		if ($strFileType == ".odi")
			$ContentType = "application/vnd.oasis.opendocument.image";
		if ($strFileType == ".ndl")
			$ContentType = "application/vnd.lotus-notes";
	   	if ($strFileType == ".eml")
	   		$ContentType = "text/plain";

		header ("Content-Type: $ContentType");
		header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");
		header("Pragma: public");
		header("Expires: 0"); // set expiration time
		header ("Content-Disposition: attachment; filename=\"". addslashes($newFileName)."\"");
		// No IE para que os nomes de arquivos com caracteres especiais no download fiquei corretos o nome deve ser codificado com urlencode.
        	 if (preg_match('/msie/i', $_SERVER['HTTP_USER_AGENT']))
			$newFileName=urlencode($newFileName);
                if($fileContent) {
			if($encoding == 'base64')
				echo imap_base64($fileContent);
			else if($encoding == 'quoted-printable')
				echo quoted_printable_decode($fileContent);
			else
				echo $fileContent;
		}
		else

			if (strstr($strFileName,$GLOBALS['phpgw']->session->sessionid)&&file_exists($strFileName))
			{
			    header("Content-Type: $ContentType");
			    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			    header("Pragma: public");
			    header("Expires: 0"); // set expiration time
			    header ("Content-Disposition: attachment; filename=\"". addslashes($newFileName)."\"");
			    readfile($strFileName);
			}else{
			    header("HTTP/1.1 404 Not Found");
			}


			if (preg_match("#^".ini_get('session.save_path')."/(".$GLOBALS['phpgw']->session->sessionid."/)*[A-z]+_".$GLOBALS['phpgw']->session->sessionid."[A-z0-9]*(\.[A-z]{3,4})?$#",$strFileName))
			{
				if ( ! preg_match("#^".dirname( __FILE__ ) . '/../tmpLclAtt'."/source_#",$strFileName)) {
					//reset time limit for big files
					set_time_limit(0);
					ob_end_flush();

					if ($fp = fopen ($strFileName, 'rb'))
					{
						$bufferSize=1024;
						for ($i=$bufferSize; $i<=(filesize($strFileName)+$bufferSize); $i+=$bufferSize)
						{
							echo fread($fp, $i);
							flush();
						}
						fclose ($fp);
					}
					//readfile($strFileName);

					exec("rm -f ".escapeshellcmd(escapeshellarg($strFileName)));
				}
				else
					readfile($strFileName);
			}
	}
?>
