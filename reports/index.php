<?php
	/*************************************************************************************\
	* Expresso Relatório                										         *
	* by Elvio Rufino da Silva (elviosilva@yahoo.com.br, elviosilva@cepromat.mt.gov.br)  *
	* -----------------------------------------------------------------------------------*
	*  This program is free software; you can redistribute it and/or modify it			 *
	*  under the terms of the GNU General Public License as published by the			 *
	*  Free Software Foundation; either version 2 of the License, or (at your			 *
	*  option) any later version.														 *
	\*************************************************************************************/
	$GLOBALS['phpgw_info'] = array();
	$GLOBALS['phpgw_info']['flags']['currentapp'] = 'reports';
	include('../header.inc.php');

	$c = CreateObject('phpgwapi.config','reports');
	$c->read_repository();
	$current_config = $c->config_data;
	$boemailadmin	= CreateObject('emailadmin.bo');
	$emailadmin_profile = $boemailadmin->getProfileList();
	$_SESSION['phpgw_info']['expresso']['email_server'] = $boemailadmin->getProfile($emailadmin_profile[0]['profileID']);
	
	$template = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
	$template->set_file(Array('reports' => 'index.tpl'));
	$template->set_block('reports','body');	
	
	$var = Array(
		'lang_rel_user_all'			=> lang('report user'),
		'lang_rel_title'			=> lang('reports'),
		'lang_rel_user_org'			=> lang('report organization'),
		'lang_rel_share_account_org'		=> lang('report share account organization'),
		'lang_rel_institutional_account_org'	=> lang('report institutional account organization'),
		'lang_rel_cota_org' 		=> lang('report cota organization'),
		'lang_rel_logon_org' 		=> lang('report of time without logging by Organization'),
		'lang_rel_maillists_org' 	=> lang('report maillists organization'),
		'lang_rel_usersgroups_org' 	=> lang('report usersgroups organization'),
		'page'						=> $_GET['page']
	);
	$template->set_var($var);
	$template->pfp('out','body');
	$GLOBALS['phpgw']->common->phpgw_footer();
?>