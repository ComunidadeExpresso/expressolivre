<?php
	/**************************************************************************\
	* eGroupWare - Filemanager                                                 *
	* http://www.egroupware.org                                                *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id: setup.inc.php 15591 2004-07-02 22:32:53Z ralfbecker $ */

	$setup_info['filemanager']['name']    = 'filemanager';
	$setup_info['filemanager']['title']   = 'Filemanager';
	$setup_info['filemanager']['version'] = '2.5.2';
	$setup_info['filemanager']['app_order'] = 6;
	$setup_info['filemanager']['enable']  = 1;

	$setup_info['filemanager']['author'] 		= 'eGroupware 1.0';
	$setup_info['filemanager']['license']  		= 'GPL';
	$setup_info['filemanager']['description'] 	= 'Great filemanager with good resources.';
	$setup_info['filemanager']['note'] 			= 'Bassed on egw filemanager 1.0 changes by Alexandre Felipe Muller de Souza';
	$setup_info['filemanager']['maintainer']  = 'Alexandre Felipe Muller de Souza <br/>';
	$setup_info['filemanager']['maintainer'] .= 'Alexandre Luiz Correia <br/> ';
	$setup_info['filemanager']['maintainer'] .= 'Fernando Porto Correa <br/>';
	$setup_info['filemanager']['maintainer'] .= 'Rodrigo Souza<br/></br>';
    $setup_info['filemanager']['maintainer'] .= 'Coordenador do Projeto : Nilton Emilio Buhrer Neto<br/><br/>';	

    $setup_info['filemanager']['tables'][] = 'phpgw_vfs_quota';
	$setup_info['filemanager']['tables'][] = 'phpgw_filemanager_notification';

	/* The hooks this app includes, needed for hooks registration */
	$setup_info['filemanager']['hooks'] = array
	(
		'add_def_pref',
		'admin',
		'deleteaccount',
		'settings',
		'sidebox_menu',
		'personalizer',
		'preferences',
	);

	/* Dependencies for this app to work */
	$setup_info['filemanager']['depends'][] = array
	(
		 'appname' => 'phpgwapi',
		 'versions' => array('2.5.1.1')
	);
?>
