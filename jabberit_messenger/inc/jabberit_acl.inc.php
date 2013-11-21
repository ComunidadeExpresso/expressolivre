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

// Verifica qual será o módulo a ser carregado.
$flag = false;

$groupsJmessenger = unserialize( $GLOBALS['phpgw_info']['server']['groups_jmessenger_jabberit'] );

if( is_array($groupsJmessenger) )
{

	foreach( $groupsJmessenger as $tmp )
	{
		$_explode = explode( ":", $tmp );
		$groups[] = $_explode[1];
	}

	foreach( $GLOBALS['phpgw']->accounts->membership() as $idx => $group )
	{
		if( array_search($group['account_name'], $groups) !== FALSE )
			$flag = true;
	}
}

if( $flag )
{
	require_once PHPGW_SERVER_ROOT . '/jabberit_messenger/jmessenger/inc/jabberit_acl.inc.php';
}
else
{	
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
	
                        
                        $webserver_expresso = $webserver_url . "phpgwapi/";
			$webserver_url      = $webserver_url . 'jabberit_messenger/'; 
			
			require_once PHPGW_SERVER_ROOT . '/jabberit_messenger/inc/jabberit_sessions.inc.php';
			
			$_SESSION['phpgw_info']['jabberit_messenger']['webserver_url'] = $webserver_url;
			
                        
			require_once dirname(__FILE__) . '/load_lang.php';
			
			$js  = "var path_jabberit='".$webserver_url."';";
			$js .= "var _ZINDEX='99000'; ";	
	
            // Xtools Phpgwapi
            echo "<script type='text/javascript' src='".$webserver_expresso."js/x_tools/xtools.js'></script>";
                        
            // Javascript JMessenger
            echo "<script type=\"text/javascript\">".$js."</script>";
			$js = array(
						'j.connector',
						'j.dragdrop',
						'j.makeW',
						'j.ldap',
						'j.images',
						'j.show_hidden',
						'j.load',
						'j.editSelect'
						);

			require_once dirname(__FILE__) . '/Controller.class.php';
	
			$controller = new Controller;
			$script = '';
			
			foreach( $js as $key => $val ) 
			{
				$script .= $controller->exec(array('act' => $val), PHPGW_SERVER_ROOT . "/jabberit_messenger");
			}	
			
			$theme = "window_" . $GLOBALS['phpgw_info']['user']['preferences']['common']['theme'] . ".css";
	
			if( !file_exists('../jabberit_messenger/templates/default/css/'.$theme) )
				$theme = "window_default.css";
			
			print '<link rel="stylesheet" type="text/css" href="' . $webserver_url . 'templates/default/css/'.$theme.'" >';
			print '<link rel="stylesheet" type="text/css" href="' . $webserver_url . 'templates/default/css/common.css" >';
			//print '<link rel="stylesheet" type="text/css" href="' . $webserver_url . 'templates/default/css/button.css" >';
			//print '<link rel="stylesheet" type="text/css" href="' . $webserver_url . 'templates/default/css/selectEditStyle.css" >';		
	
			printf("<script type=\"text/javascript\">%s</script>", $script);
	
			break;
		}
	}
}

?>
