<?php
	/**************************************************************************\
	* ExpressoLivre - preferences                                              *
	* http://www.celepar.pr.gov.br                                             *
	* Written by Joseph Engo <jengo@phpgroupware.org>                          *
	* Modify by João Alfredo Knopik Junior <jakjr@celepar.pr.gov.br>           *
	* 																		   * 
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/


	$GLOBALS['phpgw_info']['flags'] = array(
		'noheader'   => True,
		'nonavbar'   => True,
		'currentapp' => 'preferences'
	);

	include('../header.inc.php');

	if($_POST['cancel'])
	{
		$GLOBALS['phpgw']->redirect_link('/preferences/index.php');
		$GLOBALS['phpgw']->common->phpgw_exit();
	}

	if(!@is_object($GLOBALS['phpgw']->js))
	{
		$GLOBALS['phpgw']->js = CreateObject('phpgwapi.javascript');
	}
	$GLOBALS['phpgw']->js->validate_file('jscode','scripts','preferences');#diretorio, arquivo.js, aplicacao

	$GLOBALS['phpgw']->template->set_file(array(
		'form' => 'changepersonaldata.tpl'
	));

	$GLOBALS['phpgw']->template->set_var('lang_commercial_telephonenumber',lang('%1 telephone number',lang('Commercial')));
	$GLOBALS['phpgw']->template->set_var('lang_birthday',lang('Birthday'));
	$GLOBALS['phpgw']->template->set_var('lang_ps_commercial_telephonenumber',
	lang('Observation') . ': ' . lang('This telephone number will apear in searches for your name, and it will be visible for all ExpressoLivre Users') . '.');
	$GLOBALS['phpgw']->template->set_var('lang_mobile_telephonenumber',lang('%1 telephone number',lang('Mobile')));
	$GLOBALS['phpgw']->template->set_var('lang_homephone_telephonenumber',lang('%1 telephone number',lang('Home')));
	$GLOBALS['phpgw']->template->set_var('lang_change',lang('Change'));
	$GLOBALS['phpgw']->template->set_var('lang_cancel',lang('Cancel'));
	$GLOBALS['phpgw']->template->set_var('form_action',$GLOBALS['phpgw']->link('/preferences/changepersonaldata.php'));
	
	/* Get telephone number from ldap or from post */
	$ldap_conn = $GLOBALS['phpgw']->common->ldapConnect();
	$result = ldap_search($ldap_conn, $GLOBALS['phpgw_info']['server']['ldap_context'], 'uid='.$GLOBALS['phpgw_info']['user']['account_lid'], array('telephonenumber','mobile','homephone','datanascimento'));
	$entrie = ldap_get_entries($ldap_conn, $result);

	/* BEGIN ACL Check for Personal Data Fields.*/
	$disabledTelephoneNumber = false;
	$disabledMobile = false;
	$disabledHomePhone = false;
	$disableBirthday = false;
	if ($GLOBALS['phpgw']->acl->check('blockpersonaldata',1)) {
		$disabledTelephoneNumber = '"disabled=true"';
	}
	if ($GLOBALS['phpgw']->acl->check('blockpersonaldata',2)) {
		$disabledMobile = '"disabled=true"';
	}
	if ($GLOBALS['phpgw']->acl->check('blockpersonaldata',4)) {
		$disabledHomePhone = '"disabled=true"';
	}
	if ($GLOBALS['phpgw']->acl->check('blockpersonaldata',8)) {
		$disableBirthday = '"disabled=true"';
	}
	/* END ACL Check for Personal Data Fields.*/
	
	$GLOBALS['phpgw']->template->set_var('telephonenumber',($_POST['telephonenumber'] ? $_POST['telephonenumber'] : $entrie[0]['telephonenumber'][0]).$disabledTelephoneNumber);
	$GLOBALS['phpgw']->template->set_var('mobile',($_POST['mobile'] ? $_POST['mobile'] : $entrie[0]['mobile'][0]).$disabledMobile);
	$GLOBALS['phpgw']->template->set_var('homephone',($_POST['homephone'] ? $_POST['homephone'] : $entrie[0]['homephone'][0]).$disabledHomePhone);
	$GLOBALS['phpgw']->template->set_var('datanascimento',$_POST['datanascimento'] ? $_POST['datanascimento'] : $entrie[0]['datanascimento'][0] != '' ? $entrie[0]['datanascimento'][0] : '');


	ldap_close($ldap_conn);

	if ($GLOBALS['phpgw_info']['server']['auth_type'] != 'ldap')
	{
		$GLOBALS['phpgw']->template->set_var('sql_message',lang('note: This feature is *exclusive* for ldap repository.'));
	}

	if ($_POST['change'])
	{
		if ($_POST['telephonenumber'] != $GLOBALS['phpgw_info']['user']['telephonenumber'] || $_POST['mobile'] != $GLOBALS['phpgw_info']['user']['mobile']
		 || $_POST['homephone'] != $GLOBALS['phpgw_info']['user']['homephone'] || $_POST['datanascimento'] != $GLOBALS['phpgw_info']['user']['datanascimento'])
		{
			$pattern = '/\([0-9]{2,3}\)[0-9]{4}-[0-9]{4}$/';
			if ((strlen($_POST['telephonenumber']) != 0) && (!preg_match($pattern, $_POST['telephonenumber'])))
			{
				$errors[] = lang('Format of %1 telephone number is invalid.', lang("Commercial"));
			}
			if ((strlen($_POST['mobile']) != 0) && (!preg_match($pattern, $_POST['mobile'])))
			{
				$errors[] = lang('Format of %1 telephone number is invalid.', lang("Mobile"));
			}
			if ((strlen($_POST['homephone']) != 0) && (!preg_match($pattern, $_POST['homephone'])))
			{
				$errors[] = lang('Format of %1 telephone number is invalid.', lang("Home"));
			}

			if(!(checkdate(substr($_POST['datanascimento'],3,2),substr($_POST['datanascimento'],0,2),substr($_POST['datanascimento'],6,4)) == 1 
				|| ($_POST['datanascimento'] == '')))
			{
				$errors[] = lang('invalid date');
			}				
			if(!is_array($errors))
			{
				// Use LDAP Replication mode, if available
				if ( (!empty($GLOBALS['phpgw_info']['server']['ldap_master_host'])) &&
					 (!empty($GLOBALS['phpgw_info']['server']['ldap_master_root_dn'])) &&
				 	 (!empty($GLOBALS['phpgw_info']['server']['ldap_master_root_pw'])) )
				{
					$ldap_conn = $GLOBALS['phpgw']->common->ldapConnect(
												   $GLOBALS['phpgw_info']['server']['ldap_master_host'],
												   $GLOBALS['phpgw_info']['server']['ldap_master_root_dn'],
												   $GLOBALS['phpgw_info']['server']['ldap_master_root_pw']
												   );
				}
				else
				{
					$ldap_conn = $GLOBALS['phpgw']->common->ldapConnect();
				}
				
				if(!$disabledTelephoneNumber && ($_POST['telephonenumber'] != $GLOBALS['phpgw_info']['user']['telephonenumber'])) {
					if (strlen($_POST['telephonenumber']) == 0) {
						$info['telephonenumber'] = $GLOBALS['phpgw_info']['user']['telephonenumber'];
						$result = @ldap_mod_del($ldap_conn, $GLOBALS['phpgw_info']['user']['account_dn'], $info);
					}
					elseif(strlen($GLOBALS['phpgw_info']['user']['telephonenumber']) == 0) {
						$info['telephonenumber'] = $_POST['telephonenumber'];
						$result = @ldap_mod_add($ldap_conn, $GLOBALS['phpgw_info']['user']['account_dn'], $info);
					}					
					else {
						$info['telephonenumber'] = $_POST['telephonenumber'];
						$result = @ldap_mod_replace($ldap_conn, $GLOBALS['phpgw_info']['user']['account_dn'], $info);
					}
					unset($info['telephonenumber']);
				}
				if (!$disabledMobile && ($_POST['mobile'] != $GLOBALS['phpgw_info']['user']['mobile'])) {
					if (strlen($_POST['mobile']) == 0) {
						$info['mobile'] = $GLOBALS['phpgw_info']['user']['mobile'];
						$result = @ldap_mod_del($ldap_conn, $GLOBALS['phpgw_info']['user']['account_dn'], $info);
					}
					elseif(strlen($GLOBALS['phpgw_info']['user']['mobile']) == 0) {
						$info['mobile'] = $_POST['mobile'];
						$result = @ldap_mod_add($ldap_conn, $GLOBALS['phpgw_info']['user']['account_dn'], $info);
					}
					else {
						$info['mobile'] = $_POST['mobile'];
						$result = @ldap_mod_replace($ldap_conn, $GLOBALS['phpgw_info']['user']['account_dn'], $info);
					}
					unset($info['mobile']);
				}
				if (!$disabledHomePhone && ($_POST['homephone'] != $GLOBALS['phpgw_info']['user']['homephone'])) {
					if (strlen($_POST['homephone']) == 0) {
						$info['homephone'] = $GLOBALS['phpgw_info']['user']['homephone'];
						$result = @ldap_mod_del($ldap_conn, $GLOBALS['phpgw_info']['user']['account_dn'], $info);
					}
					elseif(strlen($GLOBALS['phpgw_info']['user']['homephone']) == 0) {
						$info['homephone'] = $_POST['homephone'];
						$result = @ldap_mod_add($ldap_conn, $GLOBALS['phpgw_info']['user']['account_dn'], $info);
					}
					else {
						$info['homephone'] = $_POST['homephone'];
						$result = @ldap_mod_replace($ldap_conn, $GLOBALS['phpgw_info']['user']['account_dn'], $info);
					}
					unset($info['homephone']);
				}
				if (!$disableBirthday && ($_POST['datanascimento'] != $GLOBALS['phpgw_info']['user']['datanascimento'])) {
					if (strlen($_POST['datanascimento']) == 0) {
						$info['datanascimento'] = $GLOBALS['phpgw_info']['user']['datanascimento'];
						$result = @ldap_mod_del($ldap_conn, $GLOBALS['phpgw_info']['user']['account_dn'], $info);
					}
					elseif(strlen($GLOBALS['phpgw_info']['user']['datanascimento']) == 0) {
						$info['datanascimento'] = $_POST['datanascimento'];
						$result = @ldap_mod_add($ldap_conn, $GLOBALS['phpgw_info']['user']['account_dn'], $info);
					}
					else {
						$info['datanascimento'] = $_POST['datanascimento'];
						$result = @ldap_mod_replace($ldap_conn, $GLOBALS['phpgw_info']['user']['account_dn'], $info);
					}
					unset($info['datanascimento']);
				}
				ldap_close($ldap_conn);	
			}
			
			if(is_array($errors))
			{
				$GLOBALS['phpgw']->common->phpgw_header();
				echo parse_navbar();
				$GLOBALS['phpgw']->template->set_var('messages',$GLOBALS['phpgw']->common->error_list($errors));
				$GLOBALS['phpgw']->template->pfp('out','form');
				$GLOBALS['phpgw']->common->phpgw_exit(True);
			}
		}
		$GLOBALS['phpgw']->redirect_link('/preferences/index.php','cd=18');
	}
	else
	{
		$GLOBALS['phpgw_info']['flags']['app_header'] = lang('Change your Personal Data');
		$GLOBALS['phpgw']->common->phpgw_header();
		echo parse_navbar();

		$GLOBALS['phpgw']->template->pfp('out','form');
		$GLOBALS['phpgw']->common->phpgw_footer();
	}
?>
