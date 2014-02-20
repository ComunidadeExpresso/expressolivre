<?php
	/**************************************************************************\
	* eGroupWare login                                                         *
	* http://www.egroupware.org                                                *
	* Originaly written by Dan Kuykendall <seek3r@phpgroupware.org>            *
	*                      Joseph Engo    <jengo@phpgroupware.org>             *
	* Updated by Nilton Emilio Buhrer Neto <niltonneto@celepar.pr.gov.br>      *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/
	
	$phpgw_info = array();
	$submit = False;			// set to some initial value

	$GLOBALS['phpgw_info']['flags'] = array(
		'disable_Template_class' => True,
		'login'                  => True,
		'currentapp'             => 'login',
		'noheader'               => True
	);

	if(file_exists('./header.inc.php'))
	{
		include('./header.inc.php');
		// Force location to home, while logged in.
		$GLOBALS['sessionid'] = @$_GET['sessionid'] ? $_GET['sessionid'] : @$_COOKIE['sessionid'];
		
		if(isset($GLOBALS['sessionid']) && $_GET['cd'] != 10)
		{
			if( $_GET['cd'] != '66' )
			{
				$GLOBALS['phpgw']->redirect_link('/home.php');
			}

		}
		
		if ($GLOBALS['phpgw_info']['server']['use_https'] > 0)
		{
			if ($_SERVER['HTTPS'] != 'on')
			{
        		Header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
				exit;
			}
		}
			
		$GLOBALS['phpgw']->session = CreateObject('phpgwapi.sessions');	
		
	}
	else
	{
		Header('Location: setup/index.php');
		exit;
	}
		
	if($_POST)
	{
	    $accountInfo = $GLOBALS['phpgw']->accounts->read_repository();
	    isset($_COOKIE[ 'sessionid' ]) ? session_id($_COOKIE[ 'sessionid' ]) : session_id(); 
	    session_start();
	    //Carregando na sessão configurações do usuario usado na nova API.	
	    $_SESSION['wallet']['user']['uid']	          =  $accountInfo['account_lid'];
	    $_SESSION['wallet']['user']['uidNumber']      =  $accountInfo['account_id'];
	    $_SESSION['wallet']['user']['password']       =  $_POST['passwd'];
	    $_SESSION['wallet']['user']['cn']             =  $accountInfo['firstname'].' '.$accountInfo['lastname'];
	    $_SESSION['wallet']['user']['mail']           =  $accountInfo['email'];

	    $_SESSION['wallet']['Sieve']['user']          =  $accountInfo['account_lid'];
	    $_SESSION['wallet']['Sieve']['password']      =  $_POST['passwd'];

	    $_SESSION['wallet']['Cyrus']['user']          =  $accountInfo['account_lid'];
	    $_SESSION['wallet']['Cyrus']['password']      =  $_POST['passwd'];

	}
	
	//detect if the user has a compatible browser, if don't have send him to expresso mini
	$ifMobile = false;
	$browser = CreateObject('phpgwapi.browser');
	switch ( $browser->get_platform() )
	{
		case browser::PLATFORM_IPHONE:
		case browser::PLATFORM_IPOD:
		case browser::PLATFORM_IPAD:
		case browser::PLATFORM_BLACKBERRY:
		case browser::PLATFORM_NOKIA:
		case browser::PLATFORM_ANDROID:
		case browser::PLATFORM_WINMOBILE:
			$ifMobile = true;						
			break;
	}

	if( $ifMobile && $_GET['dont_redirect_if_moble'] != 1 ) 
	{
		$GLOBALS['phpgw_info']['user']['preferences']['common']['lang'] = preg_replace("/\,.*/","",$GLOBALS['_SERVER']['HTTP_ACCEPT_LANGUAGE']);
		$GLOBALS['phpgw']->redirect_link('/mobile/login.php');
	}
	else
	{
		$GLOBALS['phpgw_info']['server']['template_dir'] = PHPGW_SERVER_ROOT . '/phpgwapi/templates/' . $GLOBALS['phpgw_info']['login_template_set'];
		$tmpl = CreateObject('phpgwapi.Template', $GLOBALS['phpgw_info']['server']['template_dir']);
	
		// read the images from the login-template-set, not the (maybe not even set) users template-set
		$GLOBALS['phpgw_info']['user']['preferences']['common']['template_set'] = $GLOBALS['phpgw_info']['login_template_set'];
	
		// This is used for system downtime, to prevent new logins.
		if($GLOBALS['phpgw_info']['server']['deny_all_logins'])
		{
			$deny_msg=lang('Oops! You caught us in the middle of system maintainance.<br/>
			Please, check back with us shortly.');
	
			$tmpl->set_file(array('login_form' => 'login_denylogin.tpl'));
	
			$tmpl->set_var('template_set','default');
			$tmpl->set_var('deny_msg',$deny_msg);
			$tmpl->pfp('loginout','login_form');
			exit;
		}
		$tmpl->set_file(array('login_form' => 'login.tpl'));
	
		$tmpl->set_var('template',$GLOBALS['phpgw_info']['login_template_set']);
		$tmpl->set_var('lang',$_GET['lang']?$_GET['lang']:preg_replace("/\,.*/","",$GLOBALS['_SERVER']['HTTP_ACCEPT_LANGUAGE']));
	
		if (count($GLOBALS['phpgw_info']['server']['login_logo_file']) > 0)
			$tmpl->set_var('logo_config',$GLOBALS['phpgw_info']['server']['login_logo_file'] . '<br />');
		else
			$tmpl->set_var('logo_config','
			    <div style="float:left">
                    <a title="Governo do Paran&aacute" href="http://www.pr.gov.br" target="_blank">
                        <img src="phpgwapi/templates/'.$GLOBALS['phpgw_info']['login_template_set'].'/images/logo_governo.gif" border="0" alt="Governo do Paraná" />
                    </a>
                </div>
                <div style="float: right">
                    <a title="Celepar Inform&aacute;tica do Paran&aacute;" target="_blank" href="http://www.celepar.pr.gov.br/">
                        <img src="phpgwapi/templates/'.$GLOBALS['phpgw_info']['login_template_set'].'/images/logo_celepar.gif" border="0" alt="Celepar - Tecnologia da Informação e Comunicação do Paraná" />
                    </a><br />
            ');
		// !! NOTE !!
		// Do NOT and I repeat, do NOT touch ANYTHING to do with lang in this file.
		// If there is a problem, tell me and I will fix it. (jengo)
	
		// whoooo scaring
	
		// ServerID => Identify the Apache Frontend.
		if($GLOBALS['phpgw_info']['server']['usecookies'] == True && $GLOBALS['phpgw_info']['server']['use_frontend_id'])
		{
			$GLOBALS['phpgw']->session->phpgw_setcookie('serverID', $GLOBALS['phpgw_info']['server']['use_frontend_id']);
		}
		if($GLOBALS['phpgw_info']['server']['captcha']==1)
	  	{
			session_start();
	  	}
			  			
		include(personalize_include_path('phpgwapi','login'));
	}   
            
?>
