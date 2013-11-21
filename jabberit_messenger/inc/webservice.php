<?php
  /***************************************************************************\
  *  Expresso - Expresso Messenger                                            *
  *  	- Alexandre Correia / Rodrigo Souza							          *
  *  	- JETI - http://jeti-im.org/										  *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/

class webService
{
	private $attribute;
	private $conn;
	private $contextLdap;
	private $userLDAP;	
	private $jabberName = null;
	private $passwd;
	private $photo_user = array();
	private $refer;
	private $serverLdap;
	private $fileLdapInternal = false;
	private $fileLdapExternal = false;
	private $version3;
	
	function __construct()
	{
		if ( file_exists('confLDAPInternal.php') )
		{
			require_once('confLDAPInternal.php');
			$handle   = unserialize(base64_decode($LDAP_INTERNAL));
	
			$this->jabberName	= $handle['jabberName'];
			$this->serverLdap	= $handle['serverLdap'];
			$this->contextLdap	= $handle['contextLdap'];
			$this->userLDAP		= $handle['user'];
			$this->passwd		= $handle['password'];
			
			$this->refer  	= true;
			$this->version3 = true;
			
			$this->fileLdapInternal = true;
		}
		
		$this->attribute = "uid";
		
		if ( file_exists('attributeLdap.php') )
		{
			require_once('attributeLdap.php');
			$this->attribute = trim($attributeTypeName);
		}
	}
	
	private final function ldapConnect()
	{
		if(!function_exists('ldap_connect'))
			return False;
		
		if(!$this->conn = ldap_connect($this->serverLdap))
			return False;

		if( $this->version3 )
			if( !ldap_set_option($this->conn,LDAP_OPT_PROTOCOL_VERSION,3) )
				$this->version3 = false;

		ldap_set_option($this->conn, LDAP_OPT_REFERRALS, $this->refer);

		// Bind as Admin
		if( $this->userLDAP && $this->passwd && !ldap_bind($this->conn, $this->userLDAP . "," . $this->contextLdap, $this->passwd) )
			return False;
		
		// Bind as Anonymous
		if( !$this->userLDAP && !$this->passwd && !@ldap_bind($this->conn) )
			return False;
	}

	private final function ldapConnectExternal($pHostJabber)
	{
		if( file_exists('confLDAPExternal.php'))
		{ 
			require_once('confLDAPExternal.php');
			$handle   = unserialize(base64_decode($LDAP_EXTERNAL));
			foreach($handle as $itens)
			{
				if(trim($pHostJabber) == $itens['jabberName'])
				{
					$this->jabberName	= $itens['jabberName'];
					$this->serverLdap	= $itens['serverLdap'];
					$this->contextLdap	= $itens['contextLdap'];
					$this->userLDAP		= $itens['user'];
					$this->passwd		= $itens['password'];
					
					$this->fileLdapExternal = true;
				}
			}		
	
			$this->refer  	= true;
			$this->version3 = true;
			
			$this->ldapConnect();
		}
	}
	
	public final function CallVoipConnect($pVoipFrom, $pVoipTo)
	{
		$this->ldapConnect();

		if( $this->conn )
		{
			$filter  = "(|(&(phpgwaccounttype=u)(uid=".$pVoipFrom."))(&(phpgwaccounttype=u)(uid=".$pVoipTo.")))";
			$justthese = array("telephoneNumber", "uid");
			$search = ldap_search($this->conn,$this->contextLdap,$filter,$justthese);
			$entry = ldap_get_entries($this->conn,$search);

			$fromNumber = $entry[0]['telephonenumber'][0];
			$toNumber = $entry[1]['telephonenumber'][0];

			if ( trim($entry[0]['uid'][0]) !== trim($pVoipFrom) )
			{
				$fromNumber = $entry[1]['telephonenumber'][0];
				$toNumber = $entry[0]['telephonenumber'][0];
			}
		}
		
		if( $fromNumber && $toNumber )
		{
			$voipServer	= "www.pabx.celepar.parana";
			$voipUrl	= "/telefoniaip/servicos/voip.php";
			$voipPort	= "80";
	
			if( !$voipServer || !$voipUrl || !$voipPort )
				return false;
			
			$url		= "http://".$voipServer.":".$voipPort.$voipUrl."?magic=1333&acao=liga&ramal=".$fromNumber."&numero=".$toNumber;
			$sMethod	= 'GET ';
			$crlf		= "\r\n";
			$sRequest	= " HTTP/1.1" . $crlf;
			$sRequest	.= "Host: localhost" . $crlf;
			$sRequest	.= "Accept: */* " . $crlf;
			$sRequest	.= "Connection: Close" . $crlf . $crlf;            
			$sRequest	= $sMethod . $url . $sRequest;    
			$sockHttp	= socket_create(AF_INET, SOCK_STREAM, SOL_TCP);            
			
			if ( !$sockHttp )
			    return false;
			
			$resSocketConnect = socket_connect($sockHttp, $voipServer, $voipPort);
			
			if ( !$resSocketConnect )
			    return false;
	
			$resSocketWrite = socket_write($sockHttp, $sRequest, strlen($sRequest));
	
			if ( !$resSocketWrite )
			    return false;
	    
			$sResponse = '';    
	
			while ($sRead = socket_read($sockHttp, 512))
			{
			    $sResponse .= $sRead;
			}            
			
			socket_close($sockHttp);            
			
			$pos = strpos($sResponse, $crlf . $crlf);
			
			return substr($sResponse, $pos + 2 * strlen($crlf));
		}
		
		return "ERRO";									
	}
	
	
	public final function getNameOrganization($pJid, $pCharset)
	{
		$uid = substr($pJid, 0, strpos($pJid,"@"));
		$return = utf8_encode("Nome : Nуo Identificado ;Organizaчуo : Nуo Identificado");
		
		if( $this->jabberName == (substr($pJid, strpos($pJid, "@") + 1 )))
		{
			$this->ldapConnect();

			if( $this->fileLdapInternal )
			{
				if( $this->conn )
				{
					$filter = "(&(phpgwaccounttype=u)(".$this->attribute."=".$uid.")(!(phpgwaccountvisible=-1)))";
					$justthese = array($this->attribute,"cn","dn");
					$search = ldap_search( $this->conn, $this->contextLdap, $filter,$justthese);
					$get_entries = ldap_get_entries( $this->conn, $search);
		
					if( $get_entries['count'] > 0 )
					{					
						$cn = $get_entries[0]['cn'][0];
						$ou = explode("dc=", $get_entries[0]['dn']);
						$ou = explode("ou=",$ou[0]);
						$ou = array_pop($ou);
						$dn = strtoupper(substr($ou,0,strlen($ou)-1));
						$return = utf8_encode("Nome : " . $cn . ";Organizaчуo : " . $dn);
					}
				}
			}
		}
		else
		{
			$this->ldapConnectExternal(substr($pJid, strpos($pJid, "@") + 1 ));
			
			if( $this->fileLdapExternal )
			{
				if( $this->conn )
				{
					$filter = "(&(phpgwaccounttype=u)(".$this->attribute."=".$uid.")(!(phpgwaccountvisible=-1)))";
					$justthese = array($this->attribute,"cn","dn");
					$search = ldap_search( $this->conn, $this->contextLdap, $filter, $justthese);
					$get_entries = ldap_get_entries( $this->conn, $search);
					
					if( $get_entries['count'] > 0 )
					{
						$cn = $get_entries[0]['cn'][0];
						$ou = explode("dc=", $get_entries[0]['dn']);
						$ou = explode("ou=",$ou[0]);
						$ou = array_pop($ou);
						$dn = strtoupper(substr($ou,0,strlen($ou)-1));
						$return = utf8_encode("Nome : " . $cn . ";Organizaчуo : " . $dn);
					}
				}
			}
		}

		if( $pCharset === "1" || $pCharset === 1 )
			return $return;
		else
			return mb_convert_encoding($return, "ISO-8859-1", "UTF-8");

	}
	
	public final function getPhotoLdap( $pJid , $pLdapInternal )
	{
		$uid = substr($pJid, 0, strpos($pJid, "@"));

		if( $pLdapInternal )
		{
			if( !$this->fileLdapInternal )
				return false;

			if( $this->jabberName == (substr($pJid, strpos($pJid, "@") + 1 )))
			{
				
				$this->ldapConnect();
				
				if( $this->conn )
				{
					$filter			= "(&(phpgwaccounttype=u)(".$this->attribute."=".$uid.")(!(phpgwaccountvisible=-1)))";
					$justthese		= array($this->attribute,"jpegPhoto");
					$search			= ldap_search($this->conn,$this->contextLdap,$filter,$justthese);
					$get_entries	= ldap_get_entries($this->conn,$search);
					
					if( $get_entries['count'] > 0 )
					{
						$first_entry = ldap_first_entry( $this->conn, $search );
						$photo = @ldap_get_values_len($this->conn, $first_entry, 'jpegphoto');
						
						if ( $photo )
							return $photo[0];
						
						return false;								
					}
				}
			}
		}
		else
		{				
			$jabberName = substr($pJid, strpos($pJid, "@") + 1 );

			if( strpos($jabberName, "/") )
				$jabberName = substr($jabberName, 0, strpos($jabberName, "/"));

			$this->ldapConnectExternal($jabberName);

			if( !$this->fileLdapExternal )
				return false;
			
			if( $this->conn )
			{
				$filter			= "(&(phpgwaccounttype=u)(".$this->attribute."=".$uid.")(!(phpgwaccountvisible=-1)))";
				$justthese		= array($this->attribute,"jpegPhoto");
				$search			= ldap_search($this->conn,$this->contextLdap,$filter,$justthese);
				$get_entries	= ldap_get_entries($this->conn,$search);
				
				if( $get_entries['count'] > 0 )
				{
					$first_entry = ldap_first_entry( $this->conn, $search );
					$photo = @ldap_get_values_len($this->conn, $first_entry, 'jpegphoto');
					
					if ( $photo )
						return $photo[0];
					
					return false;								
				}
			}
		}
		
		return false;
	}
	
	public final function getPhotoSession($pUid, $pOu)
	{
		$uid = $pUid;
		if( strpos($pUid, "@") )
			$uid = substr($pUid, 0, strpos($pUid, "@"));
		
		require_once("../../header.session.inc.php");
		
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

// Applet - utilizando o serviчo Voip;
if(trim($_REQUEST['javaVoipFrom']) != "" && trim($_REQUEST['javaVoipTo']) != "" )
{
	$obj = new webService();
	$voipFrom = $_REQUEST['javaVoipFrom'];
	$voipTo = $_REQUEST['javaVoipTo'];
	printf("%s",$obj->CallVoipConnect($voipFrom, $voipTo));
}

// Applet - fotos pelo applet; 
if(trim($_REQUEST['javaPhoto']) != "" )
{
	$obj = new webService();
	$jid = $_REQUEST['javaPhoto'];
	$jid = ( strpos($jid, "/") !== false ) ? substr($jid, 0, strpos($jid, "/")) : $jid;
	$photo = $obj->getPhotoLdap( $jid, true );
	$photoWidth = 70;
	$photoHeight = 90;
	$newImage = imagecreatetruecolor($photoWidth,$photoHeight);		

	if( $photo )
	{
		$photo = imagecreatefromstring($photo);
		imagecopyresized($newImage,$photo,0,0,0,0,$photoWidth,$photoHeight,imagesx($photo),imagesy($photo));
	}
	else
	{
		$photo = $obj->getPhotoLdap($jid, false);
		if( $photo )
		{
			$photo = imagecreatefromstring($photo);
			imagecopyresized($newImage,$photo,0,0,0,0,$photoWidth,$photoHeight,imagesx($photo),imagesy($photo));
		}
		else
		{
			$photo = @imagecreatefrompng("../templates/default/images/photo.png");
			imagecopyresized($newImage,$photo,0,0,0,0,$photoWidth,$photoHeight,imagesx($photo),imagesy($photo));
		}
	}
	
	ob_start();
	imagepng($newImage);
	$imagePhoto = ob_get_contents();
	imagedestroy($newImage);
	ob_end_clean();
	printf("%s",base64_encode($imagePhoto));
}

// Applet - jid;
if(trim($_REQUEST['jid']) != "")
{
	
	$jid = trim($_REQUEST['jid']);
	$charset = trim($_REQUEST['charset']);
	$obj = new webService();
	
	printf("%s",$obj->getNameOrganization($jid, $charset));
}

// Php - fotos pelo php;
if(trim($_REQUEST['phpPhoto']) != "")
{
	$obj = new webservice();
	$ou = $_REQUEST['phpOu'];
	$jid = $_REQUEST['phpPhoto'];
	
	$obj->getPhotoSession($jid, $ou);
}

?>