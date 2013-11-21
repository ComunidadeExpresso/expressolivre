<?php
	/**************************************************************************\
	* eGroupWare                                                               *
	* http://www.egroupware.org                                                *
	* The file written by M�rio C�sar Kolling <mario.kolling@serpro.gov.br>    *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* Basic information about this app */
	$setup_info['mobile']['name']      = 'mobile';
	$setup_info['mobile']['title']     = 'Expresso Mini';
	$setup_info['mobile']['version']   = '2.5.1';
	$setup_info['mobile']['app_order'] = 4;
	$setup_info['mobile']['enable']    = 2;

	$setup_info['mobile']['author'] = 'M�rio C�sar Kolling';
	$setup_info['mobile']['note']   = 'Mobile � uma vers�o mais simples do Expresso, desenvolvida para ser utilizada atrav�s de dispositivos m�veis';
	$setup_info['mobile']['license']  = 'GPL';
	$setup_info['mobile']['description'] = 'Mobile � uma vers�o mais simples do Expresso, desenvolvida para ser utilizada' .
			' atrav�s de dispositivos m�veis. Atualmente compreende um m�dulo de visualiza��o de agenda e outro de leitura' .
			' de e-mails.';

	$setup_info['mobile']['maintainer'] = 'M�rio C�sar Kolling';
	$setup_info['mobile']['maintainer_email'] = 'mario.kolling@serpro.gov.br';

	/* The hooks this app includes, needed for hooks registration */
	//$setup_info['mobile']['hooks'][] = 'admin';
	//$setup_info['mobile']['hooks'][] = 'preferences';
	//$setup_info['mobile']['hooks'][] = 'config_validate';

	/* mobile Tables */

	/* Dependencies for this app to work */
	//TODO: Adicionar expressoMail1_2 e calendar como depend�ncias
	$setup_info['mobile']['depends'][] = array(
		'appname' => 'phpgwapi',
		'versions' => Array('2.5.1.1')
	);
?>
