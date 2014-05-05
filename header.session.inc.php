<?php
require_once(__DIR__.'/prototype/api/esecurity.php');
$s = new ESecurity();
$s->valid();


		/*************************************************************************** 
		* Expresso Livre                                                           * 
		* http://www.expressolivre.org                                             * 
		* --------------------------------------------                             * 
		*  This program is free software; you can redistribute it and/or modify it * 
		*  under the terms of the GNU General Public License as published by the   * 
		*  Free Software Foundation; either version 2 of the License, or (at your  * 
		*  option) any later version.                                              * 
		\**************************************************************************/ 
		
if ( isset( $_COOKIE[ 'sessionid' ] ) ) 
	session_id( $_COOKIE[ 'sessionid' ] ); 

if( !isset($_SESSION) )
    session_start( );

$sess = $_SESSION[ 'phpgw_session' ];
$invalidSession = false; 
$user_agent = array(); 
if (isset($GLOBALS['phpgw']) && !isset($_SESSION['connection_db_info']))
{ 
	$_SESSION['phpgw_info']['admin']['server']['sessions_checkip'] = (isset($GLOBALS['phpgw_info']['server']['sessions_checkip'])?$GLOBALS['phpgw_info']['server']['sessions_checkip']:"");
	
	if($GLOBALS['phpgw_info']['server']['use_https'] == 1)
	{ 
		$new_ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR']."," : ""). $_SERVER['REMOTE_ADDR'];		
		if(strlen($new_ip)>30)
		{
			$ip_exploded = explode(",",$new_ip);
			$new_ip = "";
			for($i=0;$i<2;++$i)
				$new_ip .= isset($ip_exploded[$i])?(($i==1?",":"").trim($ip_exploded[$i])):("");
			if(strlen($new_ip)>30)
				$new_ip = $ip_exploded[0];
		}		
		$GLOBALS['phpgw']->db->query("UPDATE phpgw_access_log SET ip='$new_ip' WHERE account_id <> 0 and lo = 0 and sessionid='{$GLOBALS['sessionid']}'",__LINE__,__FILE__);
	} 
	 $GLOBALS['phpgw']->db->query("select trim(sessionid),".($_SESSION['phpgw_info']['admin']['server']['sessions_checkip'] ? "ip," : "")."browser from phpgw_access_log where account_id <> 0 and lo = 0 and sessionid='{$GLOBALS['sessionid']}' limit 1",__LINE__,__FILE__); 
	$GLOBALS['phpgw']->db->next_record(); 
	if($GLOBALS['phpgw']->db->row( )) 
		$_SESSION['connection_db_info']['user_auth'] = implode("",$GLOBALS['phpgw']->db->row( )); 
} 
if($_SESSION['connection_db_info']['user_auth']){ 
	$invalidSession = true; 
	$http_user_agent = substr($_SERVER[ 'HTTP_USER_AGENT' ],0,199); 
	$user_ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? array($_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_X_FORWARDED_FOR']) : array($_SERVER['REMOTE_ADDR']); 
	$user_agent[] = ($_SESSION['phpgw_info']['admin']['server']['sessions_checkip'] ? "{$sess['session_id']}{$user_ip[0]}" : "{$sess['session_id']}").$http_user_agent;
	if(count($user_ip) == 2) { 
		$user_agent[] = "{$sess['session_id']}{$user_ip[1]}".$http_user_agent; 
		$user_agent[] = $sess['session_id'].implode(",",array_reverse($user_ip)).$http_user_agent; 
	} 
	$pconnection_id = $_SESSION['connection_db_info']['user_auth']; 
	if(array_search($pconnection_id, $user_agent)  !== FALSE) { 
		$invalidSession = false; 
	} 
} 
if (empty($_SESSION['phpgw_session']['session_id']) || $invalidSession) 
{
	if($_SESSION['connection_db_info']['user_auth'] && !strstr($_SERVER['SCRIPT_NAME'],"/controller.php")) {
		error_log( '[ INVALID SESSION ] >>>>' .$_SESSION['connection_db_info']['user_auth'].'<<<< - >>>>' . implode("",$user_agent), 0 ); 
		$GLOBALS['phpgw']->session->phpgw_setcookie('sessionid'); 
		$GLOBALS['phpgw']->redirect($GLOBALS['phpgw_info']['server']['webserver_url'].'/login.php?cd=10'); 
	} 

	setcookie(session_name(),"",0); // Removing session cookie. 
	unset($_SESSION);                               // Removing session values. 
	// From ExpressoAjax response "nosession" 
	if(strstr($_SERVER['SCRIPT_NAME'],"/controller.php")){ 
		echo serialize(array("nosession" => true)); 
		exit; 
	} 
} 
else{ 
	// From ExpressoAjax update session_dla (datetime last access).  
	if(strstr($_SERVER['SCRIPT_NAME'],"/controller.php")) 
		$_SESSION['phpgw_session']['session_dla'] = time(); 

}
?>
