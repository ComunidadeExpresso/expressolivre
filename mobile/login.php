<?php
	//TODO: Trocar name="login" para name="user" no campo username

	$phpgw_info = array();
	$submit = False;

	$GLOBALS['phpgw_info']['flags'] = array(
		'disable_Template_class' => True,
		'login'                  => True,
		'currentapp'             => 'login',
		'currentdir'             => '/mobile',
		'noheader'               => True
	);
	
	include_once('../header.inc.php');
	include_once('./mobile_header.inc.php');
	$GLOBALS['sessionid'] = @$_GET['sessionid'] ? $_GET['sessionid'] : @$_COOKIE['sessionid'];

	function check_logoutcode($code)
	{
		$_return = '';
		
		switch($code)
		{
			case 'logout_mobile': 
			case 1:
				logout();
				$_return = lang('You have been successfully logged out');
				break;
			case 2:
				$_return = lang('Sorry, your login has expired');
				break;
			case 4:
				$_return = lang('Cookies are required to login to this site.');
				break;
			case 5:
				$_return = lang('Bad login or password');
				break;
			case 6:
				$_return = lang('Your password has expired, and you do not have access to change it');
				break;
			case 97:
				$_return = lang('Access not permitted');
				break;
			case 98:
				$_return = lang('Account is expired');
				break;
			case 99:
				$_return = lang('Blocked, too many attempts');
				break;
			case 10:
				$GLOBALS['phpgw']->session->phpgw_setcookie('sessionid');
				$GLOBALS['phpgw']->session->phpgw_setcookie('kp3');
				$GLOBALS['phpgw']->session->phpgw_setcookie('domain');
				if($GLOBALS['phpgw_info']['server']['sessions_type'] == 'php4')
				{
					$GLOBALS['phpgw']->session->phpgw_setcookie(PHPGW_PHPSESSID);
				}
				$_return = lang('Your session could not be verified.');
				break;
		}
		
		return $_return;
	}
	
	if ( $GLOBALS['phpgw_info']['server']['use_https'] > 0 )
	{
		if ($_SERVER['HTTPS'] != 'on')
		{
			$proxies = explode(',',$_SERVER['HTTP_X_FORWARDED_HOST']);
            $fwConstruct = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $proxies[0] : $_SERVER['HTTP_HOST'];
   			Header('Location: https://' . $fwConstruct . '/' . $_SERVER['REQUEST_URI']);
			exit;
		}
	}

	$GLOBALS['phpgw']->session = CreateObject('phpgwapi.sessions');
	$GLOBALS['phpgw_info']['server']['template_dir'] = PHPGW_SERVER_ROOT.$GLOBALS['phpgw_info']['flags']['currentdir'].'/templates/'.$GLOBALS['phpgw_info']['user']['preferences']['common']['template_set'];
	$tmpl = CreateObject('phpgwapi.Template', PHPGW_SERVER_ROOT . '/mobile/templates/'.$GLOBALS['phpgw_info']['server']['template_set']);
	$GLOBALS['phpgw_info']['user']['preferences']['common']['template_set'] = $GLOBALS['phpgw_info']['login_template_set'];
	
	//URL Expresso
	$url_expresso = $GLOBALS['phpgw_info']['server']['webserver_url'];
	$url_expresso = ( !empty($url_expresso) ) ? $url_expresso : '/';
	
	if(strrpos($url_expresso,'/') === false || strrpos($url_expresso,'/') != (strlen($url_expresso)-1))
	{
		$url_expresso .= '/';
	}
	
	$tmpl->set_file(array('login_form' => 'login.tpl'));
	$tmpl->set_block('login_form','page');
	$tmpl->set_block('login_form','success_message');
	$tmpl->set_block('login_form','error_message');
	$tmpl->set_var('url_expresso', $url_expresso);
	$tmpl->set_var('lang_username', lang('username'));
	$tmpl->set_var('lang_password', lang('password'));
	$tmpl->set_var('lang_login', lang('login'));
	
	//verificando a mensagem erro ou sucesso
	$cd = check_logoutcode($_GET['cd']);
	$tmpl->set_var('message', $cd);
	
	if( trim($cd) != "" )
	{
		$tmpl->parse('message_box', (($_GET['cd'] == 1) ? 'success_message' : 'error_message') ,true);
	}
	
	//detect if the user has a mobile browser
	$browser	= CreateObject('phpgwapi.browser');
	$platform	= false;
	
	switch ($browser->get_platform())
	{
		case browser::PLATFORM_IPHONE:
		case browser::PLATFORM_IPOD:
		case browser::PLATFORM_IPAD:
		case browser::PLATFORM_BLACKBERRY:
		case browser::PLATFORM_NOKIA:
		case browser::PLATFORM_ANDROID:
			$platform = $browser->get_platform();
			break;
	}

	$tmpl->set_var('os_browser',$platform );

	// Automatic login from browser cookies
	if( get_var('lem',array('GET','COOKIE')) && get_var('pem',array('GET','COOKIE')) )
	{
		$submit = True;
		$login  = base64_decode(get_var('lem',array('GET','COOKIE')));
		$passwd = base64_decode(get_var('pem',array('GET','COOKIE')));
		$passwd_type = 'text';

		if( $_GET['cd'] == 66 )
		{
			unset( $_GET['cd'] );
		}
	}
	else
	{
		if($GLOBALS['phpgw_info']['server']['auth_type'] == 'http' && isset($_SERVER['PHP_AUTH_USER']))
		{
			$submit = True;
			$login  = $_SERVER['PHP_AUTH_USER'];
			$passwd = $_SERVER['PHP_AUTH_PW'];
			$passwd_type = 'text';
		}
		else 
		{
			$passwd = $_POST['passwd'];
			$passwd_type = $_POST['passwd_type'];
		}
	}

	if( isset($passwd_type) || $_POST['submitit_x'] || $_POST['submitit_y'] || $submit )
	{
		if( !get_var('pem',array('GET','COOKIE')) && getenv('REQUEST_METHOD') != 'POST' 
			&& $_SERVER['REQUEST_METHOD'] != 'POST' && !isset($_SERVER['PHP_AUTH_USER']) && !isset($_SERVER['SSL_CLIENT_S_DN']))
		{
			$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link($GLOBALS['phpgw_info']['flags']['currentdir'].'/login.php','cd=5'));
		}

		if(!$submit)
		{
			$login = $_POST['login'];
		}

		$GLOBALS['sessionid'] = $GLOBALS['phpgw']->session->create(strtolower($login),$passwd,$passwd_type,'u');

		if(!isset($GLOBALS['sessionid']) || ! $GLOBALS['sessionid']){
			$GLOBALS['phpgw']->redirect($GLOBALS['phpgw_info']['server']['webserver_url'] .$GLOBALS['phpgw_info']['flags']['currentdir'].'/login.php?cd=' . $GLOBALS['phpgw']->session->cd_reason);
		}
		else
		{
			if(isset($_POST['max_resolution']) && $_POST['max_resolution'] > 600)
			{
				$GLOBALS['phpgw_info']['user']['preferences']['common']['default_mobile_app'] = 'mobilemail';
				$GLOBALS['phpgw']->session->appsession('mobile.layout','mobile','mini_desktop');
			}
			else
			{
				$GLOBALS['phpgw']->session->appsession('mobile.layout','mobile','mini_mobile');
			}
			
			$preferences = $GLOBALS['phpgw']->preferences->read();
			$_SESSION['phpgw_info']['user']['preferences']['expressoMail'] = $preferences['expressoMail'];
			
			if($_POST['save_login'] === 'on')
			{
				// Time to keep values into cookies
				$ttl = time()+15552000; // Six Months
				$GLOBALS['phpgw']->session->phpgw_setcookie('lem', base64_encode(strtolower($login)),$ttl); // lem = login
				$GLOBALS['phpgw']->session->phpgw_setcookie('pem', base64_encode($passwd), $ttl);			// pem = password
			}
			
			if( isset($GLOBALS['sessionid']) )
			{
				if( $_GET['cd'] != 10 && $_GET['cd'] != 1 && $_GET['cd'] !== 'logout_mobile' && $_GET['cd'] != 66 )
				{
					start_prefered_app();
				}
			}
		}
	}
	elseif(!isset($_COOKIE['last_loginid']) || !$prefs->account_id)
	{
		list($lang) = explode(',',$_SERVER['HTTP_ACCEPT_LANGUAGE']);
		$GLOBALS['phpgw_info']['user']['preferences']['common']['lang'] = $lang;
	}

	$tmpl->set_var('charset',$GLOBALS['phpgw']->translation->charset());
	$tmpl->set_var('cookie',$last_loginid);
	$tmpl->set_var('lang_notices', lang('notices'));
	$tmpl->set_var('website_title', $GLOBALS['phpgw_info']['server']['site_title']);
	$tmpl->set_var('template_set',$GLOBALS['phpgw_info']['login_template_set']);
	$tmpl->set_var('language_select','');
	$tmpl->set_var($var);
	$tmpl->set_block('login_form','language_select');
	$tmpl->pfp('loginout','page');

	function logout()
	{
		$verified = $GLOBALS['phpgw']->session->verify();
		if ($verified)
		{
			if (file_exists($GLOBALS['phpgw_info']['server']['temp_dir'] . SEP . $GLOBALS['sessionid']))
			{
				$dh = opendir($GLOBALS['phpgw_info']['server']['temp_dir'] . SEP . $GLOBALS['sessionid']);
				while ($file = readdir($dh))
				{
					if ($file != '.' && $file != '..')
					{
						unlink($GLOBALS['phpgw_info']['server']['temp_dir'] . SEP . $GLOBALS['sessionid'] . SEP . $file);
					}
				}
				rmdir($GLOBALS['phpgw_info']['server']['temp_dir'] . SEP . $GLOBALS['sessionid']);
			}
			$GLOBALS['phpgw']->hooks->process('logout');
			$GLOBALS['phpgw']->session->destroy($GLOBALS['sessionid'],$GLOBALS['kp3']);
		}
	}
?>