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

$size_of_acl = sizeof($GLOBALS['phpgw_info']['user']['acl']);

for( $i = 0; $i < $size_of_acl && $GLOBALS['phpgw_info']['user']['acl'] != "jabberit_messenger"; ++$i )
{
	$apps = unserialize($GLOBALS['phpgw_info']['server']['apps_jabberit']);
	$flag = false;

	if( is_array($apps) )
	{
		foreach($apps as $tmp)
		{
			$app_enabled = substr($tmp,0,strpos($tmp,";"));
			if( $GLOBALS['phpgw_info']['flags']['currentapp'] == $app_enabled )
				$flag = true;
		}	
	}
	
	if ( $GLOBALS['phpgw_info']['user']['acl'][$i]['appname'] == 'jabberit_messenger' && ( $flag || $GLOBALS['phpgw_info']['flags']['currentapp'] == 'jabberit_messenger' ))
	{

		$ldapManager = CreateObject('contactcenter.bo_ldap_manager');
		$_SESSION['phpgw_info']['jabberit_messenger']['ldapManager'] = $ldapManager->srcs[1];
		
		$webserver_url = $GLOBALS['phpgw_info']['server']['webserver_url'];
		$webserver_url = ( !empty($webserver_url) ) ? $webserver_url : '/';

		if(strrpos($webserver_url,'/') === false || strrpos($webserver_url,'/') != (strlen($webserver_url)-1))
			$webserver_url .= '/';

		$help_expresso      = $webserver_url .'help';
                $webserver_expresso = $webserver_url . "phpgwapi/";
		$webserver_url      = $webserver_url . 'jabberit_messenger/jmessenger/'; 
		
		require_once PHPGW_SERVER_ROOT . '/jabberit_messenger/jmessenger/inc/jabberit_sessions.inc.php';
		
		$_SESSION['phpgw_info']['jabberit_messenger']['webserver_url'] = $webserver_url;
		
		// Temas Expresso
		$theme = "window_" . $GLOBALS['phpgw_info']['user']['preferences']['common']['theme'] . ".css";
		
		if( !file_exists('../jabberit_messenger/jmessenger/templates/default/css/'.$theme) )
			$theme = "window_default.css";

		
		//Bloqueio das Salas de Bate-Papo por Organização
		$account_dn = $GLOBALS['phpgw_info']['user']['account_dn'];
		$ou			= explode("dc=", $account_dn);
		$ou			= explode("ou=",$ou[0]);
		$ou			= array_pop($ou);
		$ou			= strtoupper(substr($ou,0,strlen($ou)-1));
		
		$OUS_BLOQ 		= array();
		$Im_ChatRoom	= "false";
		
		foreach( $OUS_BLOQ as $lock )
		{
			if ( strtoupper($lock) === strtoupper($ou) )
			{
				$Im_ChatRoom = "true";
			}		
		}
			
		// User
		$fullName	= $_SESSION['phpgw_info']['jabberit_messenger']['fullname'];

		$js  = "var path_jabberit	= '".$webserver_url."';";
		$js .= "var theme_jabberit	= '".$theme."';";
		$js .= "var help_expresso	= '".$help_expresso."';";
		$js .= "var im_chatroom		= '".$Im_ChatRoom."';";

		// Preferences User
		require_once PHPGW_SERVER_ROOT . '/jabberit_messenger/jmessenger/inc/class.DataBaseIM.inc.php';
		
		$_DbIM			= new DataBaseIM();
		$preferences	= $_DbIM->getPreferences();
                
		echo "<script type='text/javascript'>".$js."</script>";
		echo "<script type='text/javascript' src='".$webserver_url .        "js/strophe.mini.js'></script>";
		echo "<script type='text/javascript' src='".$webserver_expresso .   "js/browser/browserDetect.js'></script>";
		echo "<script type='text/javascript' src='".$webserver_url .        "js/jscode/loadIM.mini.js'></script>";
		echo "<script type='text/javascript' src='".$webserver_url .        "js/connector.mini.js'></script>";
		echo "<script type='text/javascript' src='".$webserver_expresso .   "js/x_tools/xtools.js'></script>";
		echo "<script type='text/javascript' src='".$webserver_url .        "js/lang/i18n_pt_Br.mini.js'></script>";
		echo "<script type='text/javascript' src='".$webserver_url .        "js/dragdrop.mini.js'></script>";
	 	echo "<script type='text/javascript' src='".$webserver_url .        "js/makeW.mini.js'></script>";
	 	echo "<script type='text/javascript' src='".$webserver_url .        "js/show_hidden.mini.js'></script>";
	 	echo "<script type='text/javascript' src='".$webserver_url .        "js/trophyim_constants.js'></script>";
        echo "<script type='text/javascript' src='".$webserver_url .        "js/trophyim.mini.js'></script>";
        echo "<script type='text/javascript' src='".$webserver_url .        "js/AddUser.mini.js'></script>";			            	 	
	 	echo "<script type='text/javascript' src='".$webserver_url .        "js/json2.js'></script>";
	 	echo "<script type='text/javascript' src='".$webserver_url .        "js/SelectEditable.mini.js'></script>";
		echo "<script type='text/javascript'> var loadscript = new LoadIM('".$fullName."','".$preferences."','".$webserver_expresso."'); </script>";
		
		break;
	}
	
}

?>