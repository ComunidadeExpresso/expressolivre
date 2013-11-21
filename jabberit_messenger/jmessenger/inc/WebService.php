<?php
  /***************************************************************************\
  *  Expresso - Expresso Messenger                                            *
  *  	- Alexandre Correia / Rodrigo Souza							          *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/

class WebService
{
	function __construct()
	{
		require_once("../../../header.session.inc.php");		
	}

	public final function getPhoto($pUid)
	{
		require_once("class.LdapIM.inc.php");
		
		$ldap = new LdapIM();

		$uid = $pUid;
		if( strpos($pUid, "/") )
			$uid = substr($pUid, 0, strpos($pUid, "/"));
		
		$photo = $ldap->getPhotoUser($uid );
	
		if( $photo )
			$photo = imagecreatefromstring($photo);
		else
			$photo = imagecreatefrompng("../../templates/default/images/photo.png");

		header("Content-Type: image/jpeg");
		$width = imagesx($photo);
		$height = imagesy($photo);
		$twidth = 60;
		$theight = 80;
		$small_photo = imagecreatetruecolor ($twidth, $theight);
		imagecopyresampled($small_photo, $photo, 0, 0, 0, 0,$twidth, $theight, $width, $height);
		imagejpeg($small_photo,'',100);

		return;
	}
	
	public final function getPhotoSession($pUid, $pOu)
	{
		$uid = $pUid;
		if( strpos($pUid, "@") )
			$uid = substr($pUid, 0, strpos($pUid, "@"));
		
		if( isset($_SESSION['phpgw_info']['jabberit_messenger']['photo'][$pOu][$uid]) )
		{
			$photo = imagecreatefromstring($_SESSION['phpgw_info']['jabberit_messenger']['photo'][$pOu][$uid]);

			header("Content-Type: image/jpeg");
			$width = imagesx($photo);
			$height = imagesy($photo);
			$twidth = 60;
			$theight = 80;
			$small_photo = imagecreatetruecolor ($twidth, $theight);
			imagecopyresampled($small_photo, $photo, 0, 0, 0, 0,$twidth, $theight, $width, $height);
			imagejpeg($small_photo,'',100);

			unset($_SESSION['phpgw_info']['jabberit_messenger']['photo'][$pOu][$uid]);

			return;	
		}
	}
}

// Photo in Session
if(trim($_REQUEST['photo_session']) != "")
{
	$obj	= new WebService();
	$ou		= $_REQUEST['ou'];
	$jid	= $_REQUEST['photo_session'];
	
	$obj->getPhotoSession($jid, $ou);
}

// Photo Ldap 
if(trim($_REQUEST['photo_ldap']))
{
	$obj	= new WebService();
	$jid	= $_REQUEST['photo_ldap'];
	$obj->getPhoto($jid);
}

?>