<?php
	class uihelp
	{
		function viewHelp()
		{
		    $template = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
		    $template->set_file(Array('manual' => 'index.tpl'));
		    $template->set_var('title_help', lang('Help'));
		    $template->set_var('lang_help', lang('Help'));
		    $template->set_var('template_set', $GLOBALS['phpgw_info']['user']['preferences']['common']['template_set']);
		    $template->set_var('theme', $GLOBALS['phpgw_info']['user']['preferences']['common']['theme']);
		    $template->set_var('css', $GLOBALS['phpgw']->common->get_css());
		    $template->set_var('lang', $GLOBALS['phpgw_info']['user']['preferences']['common']['lang']);
		    $template->set_block('manual','help');		    
		    $template->pfp('out', 'manual');
		}
		function viewSuggestions()
		{
		    $template = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
		    $template->set_file(Array('manual' => 'suggestions.tpl'));
		    $template->set_var('title_suggestions', lang('Suggestions'));
		    $template->set_var('lang_suggestions', lang('Suggestions'));
		    $template->set_var('template_set', $GLOBALS['phpgw_info']['user']['preferences']['common']['template_set']);
		    $template->set_var('theme', $GLOBALS['phpgw_info']['user']['preferences']['common']['theme']);
		    $template->set_var('css', $GLOBALS['phpgw']->common->get_css());
			$template->set_var('txt_desc',lang("Use this space to send your doubts, critics and suggestions"));
			$template->set_var('txt_send', lang("Send"));
			$template->set_var('txt_cancel', lang("Cancel"));
		    $template->set_block('manual','help');		    
		    $template->pfp('out', 'manual');
		}
		function viewSuccess()
		{
		    $template = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
		    $template->set_file(Array('manual' => 'success.tpl'));
		    $template->set_var('template_set', $GLOBALS['phpgw_info']['user']['preferences']['common']['template_set']);
		    $template->set_var('theme', $GLOBALS['phpgw_info']['user']['preferences']['common']['theme']);
		    $template->set_var('css', $GLOBALS['phpgw']->common->get_css());
		    $template->set_var('lang_suggestions', lang('Suggestions'));
		    $template->set_var('txt_close', lang("Close"));
		    $template->set_var('title_suggestions', lang('Suggestions'));		    
		    $template->set_var('txt_success', lang('Your suggestion was sent successfully.'));
		    $template->set_var('html_description', lang('html_description'));		    		    
		    $template->set_block('manual','help');		    
		    $template->pfp('out', 'manual');		    
		}		
	}
?>
