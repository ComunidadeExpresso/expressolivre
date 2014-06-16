<?php

  /***************************************************************************\
  *  Expresso - Expresso Messenger                                            *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/

if( !isset($GLOBALS['phpgw_info']) )
{
	$GLOBALS['phpgw_info']['flags'] = array(
		'currentapp' => 'expressoMail',
		'nonavbar'   => true,
		'noheader'   => true
	);
}

require_once '../../../header.inc.php';

if( session_id() )
{
	$startMessenger = false;
	$messenger = array();
	$messenger_groups = array();
	if( $GLOBALS['phpgw_info']['server']['groups_expresso_messenger'] && $GLOBALS['phpgw_info']['server']['groups_expresso_messenger'] != "" )
	{
		$messenger_groups = unserialize($GLOBALS['phpgw_info']['server']['groups_expresso_messenger']);
		foreach( $messenger_groups as $group )
		{
			$values = explode( ";", $group );
			$messenger[] = $values[1];
		}
		foreach( $GLOBALS['phpgw']->accounts->membership() as $group )
		{			
			$search = array_search( $group['account_name'], $messenger_groups );
			
			if( array_search( $group['account_name'], $messenger ) !== FALSE )
			{	
				$startMessenger = true;
				break;
			}
		}
	}

	// Start Messenger ?
	if( $startMessenger )
	{	
		// Loading Admin Config Module ExpressoMail
	    $c = CreateObject('phpgwapi.config','phpgwapi');
	    $c->read_repository();
	    $config = $c->config_data;   

	    // Loading user@jabber_domain
		$jid		= $_SESSION['phpgw_info']['expressomail']['user']['userid']."@".$config['jabber_domain'];
		$user		= $_SESSION['phpgw_info']['expressomail']['user']['userid'];
		$passwd		= $_SESSION['phpgw_info']['expressomail']['user']['passwd'];
		$resource 	= "JABBER_".strtoupper($_SESSION['phpgw_info']['expressomail']['email_server']['organisationName']);

		// Auth Jabber Base64
		$auth_jabber = base64_encode($jid."\0".$user."\0".$passwd);
		$auth_jabber = str_replace("==","",$auth_jabber);

		// URL connection
		$connectionURL = $config['jabber_url_1'];

		$jabber_server = array(
			"dt__a"	=> $resource,
			"dt__b"	=> $connectionURL,
			"dt__c"	=> $config['jabber_domain'],
			"dt__d"	=> $user,
			"dt__e" => $auth_jabber
		);

		echo json_encode($jabber_server);
	}
	else
	{	
		echo json_encode( array( "error" => "Not Permission" ) );
	}
}

?>