<?php
		/*************************************************************************** 
		* Expresso Livre                                                           * 
		* http://www.expressolivre.org                                             * 
		* --------------------------------------------                             * 
		*  This program is free software; you can redistribute it and/or modify it * 
		*  under the terms of the GNU General Public License as published by the   * 
		*  Free Software Foundation; either version 2 of the License, or (at your  * 
		*  option) any later version.                                              * 
		\**************************************************************************/ 
	/**************************************************************************/
	ini_set("display_errors","1");
	$GLOBALS['phpgw_info']['flags'] = array(
		'currentapp' => 'expressoMail1_2',
		'noheader'   => True, 
		'nonavbar'   => True,
		'enable_nextmatchs_class' => True
	);

	
	require_once('../header.inc.php');
	
	
	$GLOBALS['phpgw']->common->phpgw_header();
	print parse_navbar();

	$GLOBALS['phpgw']->template->set_file(array(
		'expressoMail_prefs' => 'offline_preferences.tpl'
	));

	$GLOBALS['phpgw']->template->set_var('url_offline','offline.php');
	$GLOBALS['phpgw']->template->set_var('url_icon','templates/default/images/offline.png');
	$GLOBALS['phpgw']->template->set_var('user_uid',$GLOBALS['phpgw_info']['user']['account_id']);
	$GLOBALS['phpgw']->template->set_var('user_login',$GLOBALS['phpgw_info']['user']['account_lid']);
	$GLOBALS['phpgw']->template->set_var('lang_install_offline',lang('Install Offline'));
	$GLOBALS['phpgw']->template->set_var('lang_pass_offline',lang('Offline Pass'));
	$GLOBALS['phpgw']->template->set_var('lang_expresso_offline',lang('Expresso Offline'));
	$GLOBALS['phpgw']->template->set_var('lang_uninstall_offline',lang('Uninstall Offline'));
	$GLOBALS['phpgw']->template->set_var('lang_gears_redirect',lang('To use local messages you have to install google gears. Would you like to be redirected to gears installation page?'));
	$GLOBALS['phpgw']->template->set_var('lang_offline_installed',lang('Offline success installed'));
	$GLOBALS['phpgw']->template->set_var('lang_offline_uninstalled',lang('Offline success uninstalled'));
	$GLOBALS['phpgw']->template->set_var('lang_only_spaces_not_allowed',lang('The password cant have only spaces'));
	$GLOBALS['phpgw']->template->set_var('go_back','../preferences/');

	$GLOBALS['phpgw']->template->set_var('value_save_in_folder',$o_folders);
	$GLOBALS['phpgw']->template->set_var('lang_save',lang('Save'));
	$GLOBALS['phpgw']->template->set_var('lang_cancel',lang('Cancel'));

	$proxies=explode(',',$_SERVER['HTTP_X_FORWARDED_HOST']);
        if ($GLOBALS['phpgw_info']['server']['use_https'] != 2)
            {
                $fwConstruct = 'http://';
            }
        else
            {
                $fwConstruct = 'https://';
            }
        $fwConstruct .= isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $proxies[0] : $_SERVER['HTTP_HOST'];
        $GLOBALS['phpgw']->template->set_var('root',$fwConstruct);
	$GLOBALS['phpgw']->template->set_var('offline_install_msg',lang("If you want to install a desktop shortcut for accessing the offline ExpressoMail please confirm it after pressing the Install offline button. </br> The application also can be accessed using the URL:" ));
	$GLOBALS['phpgw']->template->set_var('th_bg',$GLOBALS['phpgw_info']["theme"][th_bg]);

	$tr_color = $GLOBALS['phpgw']->nextmatchs->alternate_row_color($tr_color);
	$GLOBALS['phpgw']->template->set_var('tr_color1',$GLOBALS['phpgw_info']['theme']['row_on']);
	$GLOBALS['phpgw']->template->set_var('tr_color2',$GLOBALS['phpgw_info']['theme']['row_off']);

	$GLOBALS['phpgw']->template->parse('out','expressoMail_prefs',True);
	$GLOBALS['phpgw']->template->p('out');
	// Com o Módulo do IM habilitado, ocorre um erro no IE
	//$GLOBALS['phpgw']->common->phpgw_footer();
?>
