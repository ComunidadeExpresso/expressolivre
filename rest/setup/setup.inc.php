<?php
	/***********************************************************************************\
	* Expresso REST API                										   
	* 
	* ---------------------------------------------------------------------------------*
	*  This program is free software; you can redistribute it and/or modify it		   *
	*  under the terms of the GNU General Public License as published by the		   *
	*  Free Software Foundation; either version 2 of the License, or (at your		   *
	*  option) any later version.													   *
	\***********************************************************************************/

	$setup_info['rest']['name']      	= 'rest';
	$setup_info['rest']['title']     	= 'REST API' ;
	/* Ao incrementar versão, não esquecer de declarar função do tables_update.inc.php*/
	$setup_info['rest']['version']   	= '1.000';
	$setup_info['rest']['app_order']	= 9;

	$setup_info['rest']['tables'][]		=  'rest_access_token';
	$setup_info['rest']['tables'][]		=  'rest_auth_code';
	$setup_info['rest']['tables'][]		=  'rest_client';
	$setup_info['rest']['tables'][]		=  'rest_refresh_token';


	$setup_info['rest']['enable']		= 1;

	$setup_info['rest']['author'] = 'autor';
	$setup_info['rest']['maintainer'] = 'mantedor';

	$setup_info['rest']['license']  = 'GPL';
	$setup_info['rest']['description'] = 'Modulo de Suport a REST ExpressoLivre';

    
    
?>
