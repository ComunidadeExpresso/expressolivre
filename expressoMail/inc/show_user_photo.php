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


	function ldapRebind($ldap_connection, $ldap_url)
	{
		@ldap_bind($ldap_connection, $_SESSION['phpgw_info']['expressomail']['ldap_server']['acc'],$_SESSION['phpgw_info']['expressomail']['ldap_server']['pw']);
	}

	if ($_SESSION['phpgw_info']['expressomail']['user']['account_lid'] == '')
		exit;
	
	$mail = $_GET['mail'];
	
	if (!preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)+$/i', $mail))
		exit;
	
	$ldap_host 	= $_SESSION['phpgw_info']['expressomail']['ldap_server']['host'];
	$ldap_context = $_SESSION['phpgw_info']['expressomail']['ldap_server']['dn'];
	
	$ldap_conn=ldap_connect($ldap_host);
	ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
	ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 1);
	ldap_set_rebind_proc($ldap_conn, ldapRebind);
	
	$r=ldap_bind($ldap_conn, $_SESSION['phpgw_info']['expressomail']['ldap_server']['acc'],$_SESSION['phpgw_info']['expressomail']['ldap_server']['pw']);
	
	if (!$r)
	{
		echo 'Sem BIND: ' . ldap_error ( $ldap_conn );
		exit;
	}

	$justthese = array("jpegPhoto");
	$filter="(mail=$mail)";
	$search=ldap_search($ldap_conn, $ldap_context, $filter, $justthese);

	$entry = ldap_first_entry($ldap_conn, $search);
	$contact = ldap_get_attributes($ldap_conn, $entry);
	
	if($contact['jpegPhoto'])
	{
		$contact['jpegPhoto'] = ldap_get_values_len ($ldap_conn, $entry, "jpegPhoto");
		$image = imagecreatefromstring ($contact['jpegPhoto'][0]); 
	}
	else
	{
		$loadFile = "../templates/default/images/photo.jpg";
		$image = imagecreatefromjpeg($loadFile);
	}
	
	header("Content-Type: image/jpeg");
	
 	$pic = $image;
	if ($pic)
	{
		$width = imagesx($pic);
		$height = imagesy($pic);
		$twidth = 60; # width of the thumb 160 pixel
		$theight = $twidth * $height / $width; # calculate height
		$thumb = imagecreatetruecolor ($twidth, $theight);
		imagecopyresampled($thumb, $pic, 0, 0, 0, 0,$twidth, $theight, $width, $height); # resize image into thumb
		imagejpeg($thumb,"",80); # Thumbnail as JPEG
	}
														    
	ldap_close($ldap_conn);
?>
