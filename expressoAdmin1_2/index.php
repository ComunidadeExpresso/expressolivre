<?php
	/*************************************************************************************\
	* Expresso Administração                										     *
	* by Joao Alfredo Knopik Junior (joao.alfredo@gmail.com, jakjr@celepar.pr.gov.br)  	 *
	* -----------------------------------------------------------------------------------*
	*  This program is free software; you can redistribute it and/or modify it			 *
	*  under the terms of the GNU General Public License as published by the			 *
	*  Free Software Foundation; either version 2 of the License, or (at your			 *
	*  option) any later version.														 *
	\*************************************************************************************/
	$GLOBALS['phpgw_info'] = array();
	$GLOBALS['phpgw_info']['flags']['currentapp'] = 'expressoAdmin1_2';
	include('../header.inc.php');
    
        echo '
            <link rel="Stylesheet" type="text/css" href="../prototype/plugins/jquery/jquery-ui.css" />
    
            <script type="text/javascript" src="../prototype/plugins/jquery/jquery.min.js"></script>
        
            <script type="text/javascript" src="../prototype/plugins/jquery/jquery-ui.min.js"></script>
            <script type="text/javascript" src="../prototype/plugins/jquery/jquery-ui.custom.min.js"></script>
            <script type="text/javascript" src="../prototype/plugins/json2/json2.js"></script>
            <script type="text/javascript" src="../prototype/plugins/ejs/ejs.js"></script>
            <script type="text/javascript" src="../prototype/plugins/store/jquery.store.js"></script>
            
            <script type="text/javascript" src="../prototype/api/datalayer.js"></script>
           
            <link rel="stylesheet" type="text/css" href="../prototype/plugins/zebradialog/css/zebra_dialog.css" />

            <script type="text/javascript" src="../prototype/plugins/zebradialog/javascript/zebra_dialog.js" ></script>
            <script type="text/javascript" language="javascript">DataLayer.dispatchPath = "../prototype/";</script>
            <script type="text/javascript" src="../prototype/plugins/datejs/date-pt-BR.js" ></script>
            <script type="text/javascript" src="../prototype/plugins/datejs/sugarpak.js" ></script>
            <script type="text/javascript" src="../prototype/plugins/datejs/parser.js" ></script>

            <script type="text/javascript" src="../prototype/modules/calendar/js/timezone.js"></script>
            
            <script type="text/javascript" src="../prototype/modules/calendar/js/calendar.codecs.js"></script>
            <script type="text/javascript" src="../prototype/modules/calendar/js/load.js"></script>
            
            <script type="text/javascript" src="js/jscode/assing_calendar.js"></script>
            <script type="text/javascript" src="../prototype/plugins/scrollto/jquery.scrollTo.js"></script>

            <link rel="stylesheet" type="text/css" href="../prototype/modules/calendar/css/layout.css" />
            <link rel="stylesheet" type="text/css" href="../prototype/modules/calendar/css/style.css" />
            <link rel="stylesheet" type="text/css" href="templates/default/assing_calendar.css" />
        ';

    $debug = false;	
	$time_start = microtime(true);
	$c = CreateObject('phpgwapi.config','expressoAdmin1_2');
	$c->read_repository();
	
	$current_config = $c->config_data;
                  
	$ldap_manager = CreateObject('contactcenter.bo_ldap_manager');
	$boemailadmin	= CreateObject('emailadmin.bo');
	$emailadmin_profile = $boemailadmin->getProfileList();
	$_SESSION['phpgw_info']['expresso']['email_server'] = $boemailadmin->getProfile($emailadmin_profile[0]['profileID']);
	$_SESSION['phpgw_info']['expresso']['user'] = $GLOBALS['phpgw_info']['user'];
	$_SESSION['phpgw_info']['expresso']['server'] = $GLOBALS['phpgw_info']['server'];
	$_SESSION['phpgw_info']['expresso']['cc_ldap_server'] = $ldap_manager ? $ldap_manager->srcs[1] : null;
	$_SESSION['phpgw_info']['expresso']['expressoAdmin'] = $current_config;
	$_SESSION['phpgw_info']['expresso']['global_denied_users'] = $GLOBALS['phpgw_info']['server']['global_denied_users'];
	$_SESSION['phpgw_info']['expresso']['global_denied_groups'] = $GLOBALS['phpgw_info']['server']['global_denied_groups'];
	
	$template = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
	$template->set_file(Array('expressoAdmin' => 'index.tpl'));
	$template->set_block('expressoAdmin','body');
	
	$var = Array(
        'lang_configurations'	=> lang('Configurations'),
		'lang_user_accounts'	=> lang('User Accounts'),
		'lang_shared_accounts'  => lang('Shared Accounts'),
		'lang_messages_size'    => lang('Messages Size'),
		'lang_institutional_accounts'=> lang('Institutional Accounts'),
                'lang_assing_calendar' => lang('Assign default calendar for user'),
		'lang_user_groups'		=> lang('User Groups'),
		'lang_email_lists'		=> lang('Email Lists'),
		'lang_computers'		=> lang('Computers'),
		'lang_organizations'	=> lang('Organizations'),
		'lang_sambadomains'		=> lang('Samba Domains'),
		'lang_sectors'			=> lang('Sectors'),
		'lang_show_sessions'	=> lang('Show Sessions'),
		'display_samba_suport'	=> $current_config['expressoAdmin_samba_support'] == 'true' ? '' : 'display:none',
		'lang_logs'				=> lang('Logs')
	);
	$template->set_var($var);
	$template->pfp('out','body');
	
	/* save lang and session */
	if (empty($_SESSION['phpgw_info']['expressoAdmin']['lang']))
	{
		$_SESSION['phpgw_info']['expressoAdmin']['user']['preferences']['common']['lang'] = $GLOBALS['phpgw_info']['user']['preferences']['common']['lang'];
		$fn = './setup/phpgw_'.$_SESSION['phpgw_info']['expressoAdmin']['user']['preferences']['common']['lang'].'.lang';
		if (file_exists($fn))
		{
			$fp = fopen($fn,'r');
			while ($data = fgets($fp,16000))
			{
				list($message_id,$app_name,$null,$content) = explode("\t",substr($data,0,-1));
				$_SESSION['phpgw_info']['expressoAdmin']['lang'][$message_id] = $content;
			}
			fclose($fp);
		}
	}

	$GLOBALS['phpgw']->common->phpgw_footer();
	
	if($debug)
		echo lang('Page was generated in %1 seconds',microtime(true) - $time_start);	
?>
