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

	/*******************************************\
	 * Define a aplica��o mobile preferencial  *
	\*******************************************/
	//TODO: Ler do banco do expresso as prefer�ncias do usu�rio e definir a aplica��o m�vel padr�o
	// por enquanto isto ser� hardcoded para mobilemail.

	$GLOBALS['phpgw_info']['flags']['currentapp'] = 'mobile';
	$GLOBALS['phpgw_info']['user']['preferences']['common']['default_mobile_app'] = 'home';

	if ( is_null($GLOBALS['phpgw_info']['server']['template_set']) )
		$GLOBALS['phpgw_info']['server']['template_set'] = $GLOBALS['phpgw_info']['login_template_set'];
	if ( !file_exists( PHPGW_SERVER_ROOT . '/mobile/templates/'.$GLOBALS['phpgw_info']['server']['template_set'] ) )
		$GLOBALS['phpgw_info']['server']['template_set'] = 'default';

	/*
	 * @

	 * @abstract Fun��o que chama a aplica��o m�vel preferencial.
	 * @author M�rio Cesar Kolling <mario.kolling@serpro.gov.br>
	 */
	function start_prefered_app(){
		//TODO: Determinar qual a aplica��o m�vel preferida e inici�-la.			
	
		switch($GLOBALS['phpgw_info']['user']['preferences']['common']['default_mobile_app'])
		{
			case home:
				$link = "ui_home.index";		
				break;			
			case mobilemail:
				$link = "ui_mobilemail.change_folder&folder=0";		
				break;
			case mobilecalendar:
			//	$link = "ui_mobilecalendar.index";
				$link = "ui_home.index";
				break;
			case mobilecc:
				$link = "ui_mobilecc.contacts_list";
				break;		
			default:
				break;		
		}		
		$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link("index.php?menuaction=mobile.".$link));
	}
?>
