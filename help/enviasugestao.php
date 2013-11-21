<?php
	/**************************************************************************\
	* eGroupWare - Online User manual                                          *
	* http://www.eGroupWare.org                                                *
	* Written and (c) by RalfBecker@outdoor-training.de                        *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id: index.php,v 1.13 2004/04/13 08:19:10 ralfbecker Exp $ */

$GLOBALS['phpgw_info']['flags'] = array(
					'currentapp' => 'help',
					'nonavbar'   => true,
					'noheader'   => true,
					);

include('../header.inc.php');
if($_POST){
	
	$params['input_to'] = $GLOBALS['phpgw_info']['server']['sugestoes_email_to'];
	$params['input_cc'] = $GLOBALS['phpgw_info']['server']['sugestoes_email_cc'];
	$params['input_bcc'] = $GLOBALS['phpgw_info']['server']['sugestoes_email_bcc'];
	$params['input_subject'] = 	lang("Suggestions");
	$params['body']	= base64_encode($_POST['body']);
	$params['type'] = 'textplain'; 
	$GLOBALS['phpgw']->preferences->read_repository();
	$_SESSION['phpgw_info']['expressomail']['user'] = $GLOBALS['phpgw_info']['user'];
	$boemailadmin	= CreateObject('emailadmin.bo');
	$emailadmin_profile = $boemailadmin->getProfileList();
	$_SESSION['phpgw_info']['expressomail']['email_server'] = $boemailadmin->getProfile($emailadmin_profile[0]['profileID']);
	$_SESSION['phpgw_info']['expressomail']['server'] = $GLOBALS['phpgw_info']['server'];		
	$_SESSION['phpgw_info']['expressomail']['user']['email'] = $GLOBALS['phpgw']->preferences->values['email'];									
	$expressoMail = CreateObject('expressoMail1_2.imap_functions');			
	$returncode = $expressoMail->send_mail($params);
	if (!$returncode) {
		echo "$to<Br>$subject<br>$tmpbody<br>$sender<br>\n";
		echo '<i>'.$send->err['desc']."</i><br>\n";
		exit;		
	}
	else{
		ExecMethod('help.uihelp.viewSuccess');
	}
}
else {
	ExecMethod('help.uihelp.viewSuggestions');
}
?>
