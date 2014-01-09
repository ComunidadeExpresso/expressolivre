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

	require_once "logout_code.php";

	$ifMobile = false;
	// $browser = CreateObject('phpgwapi.browser');
	// switch ( $browser->get_platform() )
	// {
	// 	case browser::PLATFORM_IPHONE:
	// 	case browser::PLATFORM_IPOD:
	// 	case browser::PLATFORM_IPAD:
	// 	case browser::PLATFORM_BLACKBERRY:
	// 	case browser::PLATFORM_NOKIA:
	// 	case browser::PLATFORM_ANDROID:
	// 		$ifMobile = true;						
	// 		break;
	// }
	

	/* Program starts here */
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

	# Apache + mod_ssl style SSL certificate authentication
	# Certificate (chain) verification occurs inside mod_ssl
	if( $GLOBALS['phpgw_info']['server']['auth_type'] == 'sqlssl' && isset($_SERVER['SSL_CLIENT_S_DN']) && !isset($_GET['cd']) )
	{
		# an X.509 subject looks like:
		# /CN=john.doe/OU=Department/O=Company/C=xx/Email=john@comapy.tld/L=City/
		# the username is deliberately lowercase, to ease LDAP integration
		$sslattribs = explode('/',$_SERVER['SSL_CLIENT_S_DN']);
		# skip the part in front of the first '/' (nothing)
		while($sslattrib = next($sslattribs))
		{
			list($key,$val) = explode('=',$sslattrib);
			$sslattributes[$key] = $val;
		}

		if(isset($sslattributes['Email']))
		{
			$submit = True;

			# login will be set here if the user logged out and uses a different username with
			# the same SSL-certificate.
			if( !isset($_POST['login']) && isset($sslattributes['Email']) )
			{
				$login = $sslattributes['Email'];
				# not checked against the database, but delivered to authentication module
				$passwd = $_SERVER['SSL_CLIENT_S_DN'];
			}
		}
		unset($key);
		unset($val);
		unset($sslattributes);
	}

    if( isset( $_GET[ 'cd' ] ) && ( $_GET['cd']=='1' || $_GET['cd'] == 10 ) )
	{
		$_SESSION['contador_captcha'] = 0;
	}

	if( isset($passwd_type) || $_POST['submitit_x'] || $_POST['submitit_y'] || $submit )
	{
	    // Primeiro testa o captcha....se houver......
        if( $GLOBALS['phpgw_info']['server']['captcha'] == 1 )
		{
			if( $_SESSION['contador_captcha'] > $GLOBALS['phpgw_info']['server']['num_badlogin'] )
			{
				if ($_SESSION['CAPTCHAString'] != trim(strtoupper($_POST['codigo'])))
				{
					if(!$_GET['cd'])
					{
						$_GET['cd'] = '200';
					}
				}
				
				unset($_SESSION['CAPTCHAString']);
			}
		}

		if( $_POST['user'] && (trim($_POST['user']) != "") )
		{
	 		$_POST['login'] = $_POST['user'];
		}
		
		if(getenv('REQUEST_METHOD') != 'POST' && $_SERVER['REQUEST_METHOD'] != 'POST' &&
			!isset($_SERVER['PHP_AUTH_USER']) && !isset($_SERVER['SSL_CLIENT_S_DN']))
		{
            if(!$_GET['cd'])
            {
                $_GET['cd'] = '5';
            }
		}
		
		// don't get login data again when $submit is true
		if( $submit == false )
		{
			$login = $_POST['login'];
		}

		if( !$_GET['cd'] )
		{
			$GLOBALS['sessionid'] = $GLOBALS['phpgw']->session->create(strtolower($login),$passwd,$passwd_type,'u');
		}

		if( !isset($GLOBALS['sessionid']) || ! $GLOBALS['sessionid'] )
		{
			If(!$_GET['cd']) $_GET['cd'] = $GLOBALS['phpgw']->session->cd_reason;
		}
		else
		{
			if( $_POST['lang'] && preg_match('/^[a-z]{2}(-[a-z]{2}){0,1}$/',$_POST['lang']) &&
			    $_POST['lang'] != $GLOBALS['phpgw_info']['user']['preferences']['common']['lang'] )
			{
				$GLOBALS['phpgw']->preferences->add('common','lang',$_POST['lang'],'session');
			}

			if(!$GLOBALS['phpgw_info']['server']['disable_autoload_langfiles'])
			{
				$GLOBALS['phpgw']->translation->autoload_changed_langfiles();
			}
			
			$forward = isset($_GET['phpgw_forward']) ? urldecode($_GET['phpgw_forward']) : @$_POST['phpgw_forward'];
			
			if ( !$forward )
			{
				$extra_vars['cd'] = 'yes';
				$forward = '/home.php';
			}
			else
			{
				list($forward,$extra_vars) = explode('?',$forward,2);
			}
			
			if( $GLOBALS['phpgw_info']['server']['use_https'] != 2 )
			{
				//Modificacao feita para que o Expresso redirecione para o primeiro proxy caso haja um encadeamento de mais de um proxy.
				//$forward = 'http://'.$_SERVER['HTTP_HOST'].($GLOBALS['phpgw']->link($forward.'?cd=yes'));
				$forward = 'http://' . nearest_to_me() . $GLOBALS['phpgw']->link($forward.'?cd=yes');
				echo "<script language='Javascript1.3'>location.href='".$forward."'</script>";
			}
			else
			{
				$GLOBALS['phpgw']->redirect_link($forward,$extra_vars);
			}
		}
	}

	// Incrementar Contador para o Uso do Captcha
    $_SESSION['contador_captcha']++;

	// !!! DONT CHANGE THESE LINES !!!
	// If there is something wrong with this code TELL ME!
	// Commenting out the code will not fix it. (jengo)
	if( isset( $_COOKIE['last_loginid'] ) )
	{
		$accounts = CreateObject('phpgwapi.accounts');
		$prefs = CreateObject('phpgwapi.preferences', $accounts->name2id($_COOKIE['last_loginid']));

		if($prefs->account_id)
		{
			$GLOBALS['phpgw_info']['user']['preferences'] = $prefs->read_repository();
		}
	}
	
	$_GET['lang'] = addslashes($_GET['lang']);
	if ($_GET['lang'])
	{
		$GLOBALS['phpgw_info']['user']['preferences']['common']['lang'] = $_GET['lang'];
	}
	elseif(!isset($_COOKIE['last_loginid']) || !$prefs->account_id)
	{
		// If the lastloginid cookies isn't set, we will default to the first language,
		// the users browser accepts.
		list($lang) = explode(',',$_SERVER['HTTP_ACCEPT_LANGUAGE']);
		$GLOBALS['phpgw_info']['user']['preferences']['common']['lang'] = $lang;
	}

	$GLOBALS['phpgw']->translation->init();	// this will set the language according to the (new) set prefs
	$GLOBALS['phpgw']->translation->add_app('login');
	$GLOBALS['phpgw']->translation->add_app('loginscreen');

	// Get cookie last_loginid
	$last_loginid = $_COOKIE['last_loginid'];
	if($last_loginid !== '')
	{
		reset($GLOBALS['phpgw_domain']);
		list($default_domain) = each($GLOBALS['phpgw_domain']);

		if($_COOKIE['last_domain'] != $default_domain && !empty($_COOKIE['last_domain']))
		{
			$last_loginid .= '@' . $_COOKIE['last_domain'];
		}
	}

 	foreach($_GET as $name => $value)
	{
		if(ereg('phpgw_',$name))
		{
			$extra_vars .= '&' . $name . '=' . urlencode($value);
		}
	}

	if( is_string( $extra_vars ) )
	{
		$extra_vars = '?' . substr($extra_vars,1);
	}

	/********************************************************\
	* Check is the registration app is installed, activated  *
	* And if the register link must be placed                *
	\********************************************************/
	
	$cnf_reg = createobject('phpgwapi.config','registration');
	$cnf_reg->read_repository();
	$config_reg = $cnf_reg->config_data;

	if($config_reg[enable_registration]=='True' && $config_reg[register_link]=='True')
	{
		$reg_link='&nbsp;<a href="registration/">'.lang('Not a user yet? Register now').'</a><br/>';
	}

	$template = $GLOBALS['phpgw_info']['login_template_set'];

	$GLOBALS['phpgw_info']['server']['template_set'] = $template;

	$tmpl->set_var('register_link',$reg_link);
	$tmpl->set_var('charset',$GLOBALS['phpgw']->translation->charset());
	$tmpl->set_var('login_url', $GLOBALS['phpgw_info']['server']['webserver_url'] . '/login.php' . $extra_vars);
	$tmpl->set_var('registration_url',$GLOBALS['phpgw_info']['server']['webserver_url'] . '/registration/');
	$tmpl->set_var('version',$GLOBALS['phpgw_info']['server']['versions']['phpgwapi']);
	$tmpl->set_var('cd',check_logoutcode($_GET['cd']));
	$tmpl->set_var('cookie',$last_loginid);

	$tmpl->set_var('lang_username',lang('username'));
	$tmpl->set_var('lang_password',lang('password'));
	$tmpl->set_var('lang_login',lang('login'));

	$tmpl->set_var('website_title', $GLOBALS['phpgw_info']['server']['site_title']);
	$tmpl->set_var('template_set', $template);

	// Keyboard Virtual
	$tmpl->set_var('show_kbd',$GLOBALS['phpgw_info']['server']['login_virtual_keyboard']); 

	$tmpl->set_var('autocomplete', ($GLOBALS['phpgw_info']['server']['autocomplete_login'] ? 'autocomplete="off"' : ''));

	// soh mostra o captcha se for login sem certificado....
	if($GLOBALS['phpgw_info']['server']['captcha'] && $_GET['cd']!='300' )
	{
		$aux_captcha = '<input type="hidden" name="'.session_name().'"  value="'.session_id().'">';

		if( $_SESSION['contador_captcha'] > $GLOBALS['phpgw_info']['server']['num_badlogin'] )
		{
			$aux_captcha = '<div>'
			   .'<img id="id_captcha" src="./security/captcha.php?' . session_name() . '=' . session_id() . '" title="'.lang('Security code').'" alt="'.lang('Security code').'" style="position:static;">'
			   .'<input class="input" type="text" maxlength="50" size="15" name="codigo" id="codigo" value="" >'
			   .'<input type="hidden" name="' . session_name() . '"  value="' . session_id() . '" >'
			   .'</div>';
		}

		$tmpl->set_var('captcha',$aux_captcha);
	}

	// Testa se deve incluir applet para login com certificado......
	if ( $_GET['cd']=='300' && $GLOBALS['phpgw_info']['server']['certificado'] == 1 )
	{
		//Zera o Cookie contador, responsavel pelo captcha
		$_SESSION['contador_captcha'] = 0;
		$link_alterna_login = '<img src="phpgwapi/templates/default/images/warning.gif"/><a href="login.php">' . lang('Access without Digital Certificate') . '</a>';
		$tmpl->set_var('show','none');
		$tmpl->set_var('action','<div id="action"><img style="border:0px;margin:31px 0px 58px 0px;" src="phpgwapi/templates/default/images/acao.gif" /></div>');
		// gera parametro com tokens suportados ....
		$var_tokens = '';
		
		for($ii = 1; $ii < 11; $ii++)
		{
			if($GLOBALS['phpgw_info']['server']['test_token' . $ii . '1'])
				$var_tokens .= $GLOBALS['phpgw_info']['server']['test_token' . $ii . '1'] . ',';
		}

		if(!$var_tokens)
		{
			$var_tokens = 'ePass2000Lx;/usr/lib/libepsng_p11.so,ePass2000Win;c:/windows/system32/ngp11v211.dll';
		}
		
		$param1 = "'<param name=\"token\" value=\"" . substr($var_tokens,0,strlen($var_tokens)) . "\">'+";
		$param2 = "'token=\"" . substr($var_tokens,0,strlen($var_tokens)) . "\" ' +";

		$cod_applet =
            // sem debug ativado
            '<script type="text/javascript">
					if (navigator.userAgent.match(\'MSIE\')){
						document.write(\'<object style="display:yes;width:0;height:0;vertical-align:bottom;" id="login_applet" \' +
						\'classid="clsid:8AD9C840-044E-11D1-B3E9-00805F499D93"> \' +
						\'<param name="type" value="application/x-java-applet;version=1.5"> \' + 
                                                \'<param name="codebase" value="/security/">\' +
						\'<param name="code" value="LoginApplet.class"> \' +
						\'<param name="locale" value="' . $lang . '"> \' +
						\'<param name="mayscript" value="true"> \' + ' 
						. $param1 
						. ' \'<param name="archive" value="ExpressoCertLogin.jar,ExpressoCert.jar,commons-httpclient-3.1.jar,commons-logging-1.1.1.jar,commons-codec-1.3.jar,bcmail-jdk15-142.jar,mail.jar,activation.jar,bcprov-jdk15-142.jar"> \' +
						\'</object>\');
					}
					else {
						document.write(\'<embed style="display:yes;width:0;height:0;vertical-align:bottom;" id="login_applet" codebase="/security/" code="LoginApplet.class" locale="' . $lang . '"\' +
						\'archive="ExpressoCertLogin.jar,ExpressoCert.jar,commons-httpclient-3.1.jar,commons-logging-1.1.1.jar,commons-codec-1.3.jar,bcmail-jdk15-142.jar,mail.jar,activation.jar,bcprov-jdk15-142.jar" \' + '
						. $param2  
						. ' \'type="application/x-java-applet;version=1.5" mayscript > \' +
						\'<noembed> \' +
						\'No Java Support. \' +
						\'</noembed> \' +
						\'</embed> \');
					}
				</script>';

	}
	else
	{
		if($GLOBALS['phpgw_info']['server']['certificado']==1)
		{
			$tmpl->set_var('show','yes');
			$link_alterna_login = '<img src="phpgwapi/templates/default/images/lock1_icon.gif"/><a title="' . lang('Link to use digital certificate') . '" href="login.php?cd=300">' . lang('Logon with my digital certificate') . '</a>';
		}
		$tmpl->set_var('lang_username',lang('username'));
		$tmpl->set_var('action','');
		$cod_applet = '';
	}

	$tmpl->set_var('applet',$cod_applet);
	$tmpl->set_var('link_alterna_login',$link_alterna_login);

	$tmpl->set_var('dir_root', 'http://' . nearest_to_me() . '/');
	if(is_file(dirname( __FILE__ ) . '/../../../infodist/ultima-revisao-svn.php'))
	include_once(dirname( __FILE__ ) . '/../../../infodist/ultima-revisao-svn.php');
	if(isset($ultima_revisao)) $tmpl->set_var('ultima_rev','<br>' . $ultima_revisao);

	$tmpl->pfp('loginout','login_form');

?>