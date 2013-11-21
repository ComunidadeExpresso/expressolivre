<?php
/**************************************************************************\
* eGroupWare - Contactcenter Preferences                                   *
* http://www.expressolivre.org                                             *	
* Modified by Alexandre Correia <alexandrecorreia@celepar.pr.gov.br> 	   *
* --------------------------------------------                             *
*  This program is free software; you can redistribute it and/or modify it *
*  under the terms of the GNU General Public License as published by the   *
*  Free Software Foundation; either version 2 of the License, or (at your  *
*  option)                                                                 *
\**************************************************************************/

if(!isset($GLOBALS['phpgw_info'])){
	$GLOBALS['phpgw_info']['flags'] = array(
		'currentapp' => 'contactcenter',
		'nonavbar'   => true,
		'noheader'   => true
	);
}

require_once '../header.inc.php';

$pCatalog			= CreateObject('contactcenter.bo_people_catalog');
$typesConnections	= $pCatalog->get_all_connections_types();

$preferences	= CreateObject('contactcenter.ui_preferences');
$actual 		= $preferences->get_preferences();

// Section Cards Visualization Preferences;
create_section("Cards Visualization Preferences");

$defaultConnections = array();
if(is_array($typesConnections))
{
	foreach( $typesConnections as $key => $value)
	{
		$defaultConnections[$key] = $value;
	}
}

// Default Person Email Type
create_select_box('Default Person Email Type', 'personCardEmail', $defaultConnections);
//Default Person Telephone Type
create_select_box('Default Person Telephone Type', 'personCardPhone', $defaultConnections);

// Section Connector Setup;
create_section("Connector Setup");
create_check_box('Display Connector Client-Server Status Information?','displayConnector',$actual['displayConnector']);

// Section Display Preferences;
create_section("Display Preferences");
create_check_box('Registration','empNum', $actual['empNum']);
create_check_box('Cellphone','cell',$actual['cell']);
create_check_box('Sector','department',$actual['department']);
