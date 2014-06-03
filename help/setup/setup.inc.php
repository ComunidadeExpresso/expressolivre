<?php
	/**************************************************************************\
	* phpGroupWare - Online User Manual                                        *
	* http://www.eGroupWare.org                                              *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/
	
	/* Basic information about this app */
	$setup_info['help']['name']      = 'help';
	$setup_info['help']['title']     = 'User Manual and Help Page';
	$setup_info['help']['version']   = '2.5.2';
	$setup_info['help']['app_order'] = 5;
	$setup_info['help']['enable']    = 2;	// Invisible on top (navigation bar)

	$setup_info['help']['author']    = 'William Merlotto';
	$setup_info['help']['maintainer'] = 'William Merlotto - Nilton Neto';
	$setup_info['help']['maintainer_email'] = '';
	$setup_info['help']['license']   = 'GPL';
	$setup_info['help']['description'] ='The new Expresso Online User Manual uses the Wiki app.';

	/* Dependencies for this app to work */
	$setup_info['help']['depends'][] = array(
		 'appname' => 'phpgwapi',
		 'versions' => Array('2.5.1.1')
	);
?>
