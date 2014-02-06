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

function check_logoutcode($code)
	{
		switch($code)
		{
			case 1:
				return lang('You have been successfully logged out');
				
			case 2:
				return lang('Sorry, your login has expired');
				
			case 4:
				return lang('Cookies are required to login to this site.');
				
			case 5:
				return '<font color="FF0000">' . lang('Bad login or password') . '</font>';

			case 6:
				return '<font color="FF0000">' . lang('Your password has expired, and you do not have access to change it') . '</font>';
				
			case 98:
				return '<font color="FF0000">' . lang('Account is expired') . '</font>';
				
			case 99:
				return '<font color="FF0000">' . lang('Blocked, too many attempts(%1)! Retry in %2 minute(s)',$GLOBALS['phpgw_info']['server']['num_unsuccessful_id'],$GLOBALS['phpgw_info']['server']['block_time']) . '</font>';
			case 200:
                            //return '<font color="FF0000">' . lang('Invalid code') . '</font>';
                return '<font color="FF0000">' . lang('Bad login or password') . '</font>';
			    break;
			case 10:
				$GLOBALS['phpgw']->session->phpgw_setcookie('sessionid');
				$GLOBALS['phpgw']->session->phpgw_setcookie('kp3');
				$GLOBALS['phpgw']->session->phpgw_setcookie('domain');

				//fix for bug php4 expired sessions bug
				if($GLOBALS['phpgw_info']['server']['sessions_type'] == 'php4')
				{
					$GLOBALS['phpgw']->session->phpgw_setcookie(PHPGW_PHPSESSID);
				}

				return '<font color="#FF0000">' . lang('Your session could not be verified.') . '</font>';
				
			default:
				return '';
		}
	}
        function troca_espaco_por_mais($pem_data)
	{
            $begin = "CERTIFICATE-----";
            $end   = "-----END";
            $aux = substr($pem_data, strpos($pem_data, $begin)+strlen($begin));
            $aux = substr($aux, 0, strpos($aux, $end));
            $aux = strtr($aux,' ','+');
            $aux = '-----BEGIN CERTIFICATE-----'.$aux.'-----END CERTIFICATE-----';
            return $aux;
	}
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
			$ifMobile = true;
			break;
	}
	
	if( $ifMobile && $_GET['dont_redirect_if_moble'] != 1 ) 
	{
		$GLOBALS['phpgw']->redirect_link('/mobile/login.php');
	}
	else
	{
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
	if($GLOBALS['phpgw_info']['server']['auth_type'] == 'sqlssl' && isset($_SERVER['SSL_CLIENT_S_DN']) && !isset($_GET['cd']))
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
			if(!isset($_POST['login'])&&isset($sslattributes['Email']))
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
        if(isset($_POST['certificado']) && $_POST['certificado'])
        {

            $_SESSION['login_certificado'] = troca_espaco_por_mais(str_replace(chr(0x0D).chr(0x0A),chr(0x0A),str_replace(chr(0x0A).chr(0x0A),chr(0x0A),$_POST['certificado'])));
        }
    if( isset( $_GET[ 'cd' ] ) && ( $_GET['cd']=='1' || $_GET['cd'] == 10 ) )
	{
            $_SESSION['contador'] = 0;
	}

	if(isset($passwd_type) || $_POST['submitit_x'] || $_POST['submitit_y'] || $submit)
	{
        
	    // Primeiro testa o captcha....se houver......
            if($GLOBALS['phpgw_info']['server']['captcha']==1)
              {
                if($_SESSION['contador'] > $GLOBALS['phpgw_info']['server']['num_badlogin'])
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
		if($_POST['user']) {
			if($GLOBALS['phpgw_info']['server']['use_prefix_organization']) {
				$common = CreateObject('phpgwapi.common');
				$ldap_conn = $common->ldapConnect();
				$justthese = array("uid");
				$filter="(&(phpgwAccountType=u)(uid=".$_POST['user']."))";
				$ldap_search = ldap_search($ldap_conn, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
				$ldap_info 	 = ldap_get_entries($ldap_conn, $ldap_search);
				ldap_close($ldap_conn);
				if ($ldap_info['count'] != 0) {
					$_POST['login'] = $_POST['user'];
				}
			}
			else
				$_POST['login'] = $_POST['user'];

			/**
			 * LOGIN OAUTH POR CURL
			 */
			$ch = curl_init();

			$restConf = parse_ini_file( __DIR__ . '/../../../prototype/config/REST.ini', true );

			$param  = 'grant_type=password';
			$param .= '&client_id=' . $restConf['oauth']['client_id'];
			$param .= '&client_secret=' . $restConf['oauth']['client_secret'];
			$param .= '&username=' . $_POST['user'];
			$param .= '&password=' . $_POST['passwd'];

			// set URL and other appropriate options
			curl_setopt($ch, CURLOPT_URL, $restConf['oauth']['url_token']);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: */*'));
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);  //configura para nao imprimir a saida na tela
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);//Passe um n�mero long como par�metro que cont�m o limite de tempo, em segundos, que voc� permite as fun��es CURL levar. 

			// grab URL and pass it to the browser
			$res = curl_exec($ch);
			
			// close cURL resource, and free up system resources
			curl_close($ch);
			$a = json_decode($res);
	
			if ( isset($a->access_token) ) {
				$_SESSION['oauth']['access_token'] = $a->access_token;
				$_SESSION['oauth']['expires_in'] = $a->expires_in;
				$_SESSION['oauth']['token_type'] = $a->token_type;
				$_SESSION['oauth']['scope'] = $a->scope;
				$_SESSION['oauth']['refresh_token'] = $a->refresh_token;
				
			}
			else {
			}
			/**
			 * ####################
			 */

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
		if($submit == false)
		{
			$login = $_POST['login'];
		}
		
		if(strstr($login,'@') === False && isset($_POST['logindomain']))
		{
			$login .= '@' . $_POST['logindomain'];
		}
		elseif(!isset($GLOBALS['phpgw_domain'][$GLOBALS['phpgw_info']['user']['domain']]))
		{
			$login .= '@'.$GLOBALS['phpgw_info']['server']['default_domain'];
		}
		
		If(!$_GET['cd'])
			$GLOBALS['sessionid'] = $GLOBALS['phpgw']->session->create(strtolower($login),$passwd,$passwd_type,'u');

		if(!isset($GLOBALS['sessionid']) || ! $GLOBALS['sessionid'])
		{
			If(!$_GET['cd']) $_GET['cd'] = $GLOBALS['phpgw']->session->cd_reason;
		}
		else
		{
			if ($_POST['lang'] && preg_match('/^[a-z]{2}(-[a-z]{2}){0,1}$/',$_POST['lang']) &&
			    $_POST['lang'] != $GLOBALS['phpgw_info']['user']['preferences']['common']['lang'])
			{
				$GLOBALS['phpgw']->preferences->add('common','lang',$_POST['lang'],'session');
			}

			if(!$GLOBALS['phpgw_info']['server']['disable_autoload_langfiles'])
			{
				$GLOBALS['phpgw']->translation->autoload_changed_langfiles();
			}
			$forward = isset($_GET['phpgw_forward']) ? urldecode($_GET['phpgw_forward']) : @$_POST['phpgw_forward'];
			if (!$forward)
			{
				$extra_vars['cd'] = 'yes';
				$forward = '/home.php';
			}
			else
			{
				list($forward,$extra_vars) = explode('?',$forward,2);
			}
		if ($GLOBALS['phpgw_info']['server']['use_https'] != 2)
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
	//else   // =================================================================================
	//{
            $valor_contador = $_SESSION['contador'];
            $valor_contador = $valor_contador + 1;
            $_SESSION['contador'] = $valor_contador;
		// !!! DONT CHANGE THESE LINES !!!
		// If there is something wrong with this code TELL ME!
		// Commenting out the code will not fix it. (jengo)
		if(isset($_COOKIE['last_loginid']))
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
			/*
			if(strlen($lang) > 2)
			{
				$lang = substr($lang,0,2);
			}
			*/
			$GLOBALS['phpgw_info']['user']['preferences']['common']['lang'] = $lang;

			if (!isset($GLOBALS['phpgw_info']['user']['preferences']['common']['theme']))
			{
				$prefs2 = CreateObject('phpgwapi.preferences');
				$temp_pref = $prefs2->read_repository();
				$GLOBALS['phpgw_info']['user']['preferences']['common']['theme'] = $temp_pref['common']['theme'];
			}
		}
		#print 'LANG:' . $GLOBALS['phpgw_info']['user']['preferences']['common']['lang'] . '<br>';

		$GLOBALS['phpgw']->translation->init();	// this will set the language according to the (new) set prefs
		$GLOBALS['phpgw']->translation->add_app('login');
		$GLOBALS['phpgw']->translation->add_app('loginscreen');
		if(lang('loginscreen_message') == 'loginscreen_message*')
		{
			$GLOBALS['phpgw']->translation->add_app('loginscreen','en');	// trying the en one
		}
		if(lang('loginscreen_message') != 'loginscreen_message*')
		{
			$tmpl->set_var('lang_message',stripslashes(lang('loginscreen_message')));
		}
	//}

	if($GLOBALS['phpgw_info']['server']['use_prefix_organization'])
	{
		$obj_organization = CreateObject('phpgwapi.sector_search_ldap');
		$organizations = $obj_organization->organization_search($GLOBALS['phpgw_info']['server']['ldap_context']);

        $organizations_count = count($organizations);
		for ($i=0; $i<$organizations_count; ++$i)
		{
			$tmp_array[strtolower($organizations[$i])] = $organizations[$i];	
		}
		
		$arrayOrganization = $tmp_array;		
		ksort($arrayOrganization);
		
		foreach($arrayOrganization
			 as $organization_name => $organization_vars)
		{
			$organization_select .= '<option value="' . $organization_name . '"';

			if($organization_name == $_COOKIE['last_organization'])
			{
				$organization_select .= ' selected';
			}
			$organization_select .= '>' . $organization_vars . "</option>\n";
		}
		$organization_select =  '<div class="login_label"><label>'.lang("organization")
							.'</label><br><select name="organization">'
							.$organization_select.'</select></div>';
		$tmpl->set_var('select_organization',$organization_select);
	}
		
	$domain_select = '&nbsp;';
	$last_loginid = $_COOKIE['last_loginid'];
	if($GLOBALS['phpgw_info']['server']['show_domain_selectbox'])
	{
		$domain_select = "<select name=\"logindomain\">\n";
		foreach($GLOBALS['phpgw_domain'] as $domain_name => $domain_vars)
		{
			$domain_select .= '<option value="' . $domain_name . '"';

			if($domain_name == $_COOKIE['last_domain'])
			{
				$domain_select .= ' selected';
			}
			$domain_select .= '>' . $domain_name . "</option>\n";
		}
		$domain_select .= "</select>\n";
	}
	elseif($last_loginid !== '')
	{
		reset($GLOBALS['phpgw_domain']);
		list($default_domain) = each($GLOBALS['phpgw_domain']);

		if($_COOKIE['last_domain'] != $default_domain && !empty($_COOKIE['last_domain']))
		{
			$last_loginid .= '@' . $_COOKIE['last_domain'];
		}
	}
	$tmpl->set_var('select_domain',$domain_select);

	foreach($_GET as $name => $value)
	{
		if(preg_match('/phpgw_/',$name))
		{
			$extra_vars .= '&' . $name . '=' . urlencode($value);
		}
	}

	if ( is_string( $extra_vars ) )
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

	if($config_reg['enable_registration']=='True' && $config_reg['register_link']=='True')
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

	// loads the template's login.css
	// and then the theme's login.css (if any)
	$template_dir = $GLOBALS['phpgw_info']['server']['webserver_url'] . '/phpgwapi/templates/' . $template;
	$login_dir = $template_dir . '/login.css';
	$login_css = "<link href='" . $login_dir . "' rel='stylesheet' type='text/css' />";
	$login_dir = $template_dir . '/themes/' . $GLOBALS['phpgw_info']['user']['preferences']['common']['theme'] . '/login.css';
	if(file_exists('./'.$login_dir))
	{
		$login_css .= "<link href='" . $login_dir . "' rel='stylesheet' type='text/css'/>";
	}
	$tmpl->set_var('login_css',$login_css);

	$GLOBALS['phpgw']->translation->add_app('loginhelp',$_GET['lang']);

	if(lang('loginhelp_message') != 'loginhelp_message*' && trim(lang('loginhelp_message')) != ""){					
		$tmpl->set_var('lang_help',lang("Help"));
		$tmpl->set_var('lang','pt-br');	
	}
	else 
		$tmpl->set_var('display_help','none');

	$tmpl->set_var('bg_color',($GLOBALS['phpgw_info']['server']['login_bg_color']?$GLOBALS['phpgw_info']['server']['login_bg_color']:'FFFFFF'));
	$tmpl->set_var('bg_color_title',($GLOBALS['phpgw_info']['server']['login_bg_color_title']?$GLOBALS['phpgw_info']['server']['login_bg_color_title']:'486591'));

	if($GLOBALS['phpgw_info']['server']['use_frontend_name'])
		$tmpl->set_var('frontend_name', " - ".$GLOBALS['phpgw_info']['server']['use_frontend_name']);

	if (substr($GLOBALS['phpgw_info']['server']['login_logo_file'],0,4) == 'http')
	{
		$var['logo_file'] = $GLOBALS['phpgw_info']['server']['login_logo_file'];
	}
	else
	{
		$var['logo_file'] = $GLOBALS['phpgw']->common->image('phpgwapi',$GLOBALS['phpgw_info']['server']['login_logo_file']?$GLOBALS['phpgw_info']['server']['login_logo_file']:'logo');
	}
	$var['logo_url'] = $GLOBALS['phpgw_info']['server']['login_logo_url']?$GLOBALS['phpgw_info']['server']['login_logo_url']:'http://www.eGroupWare.org';
	if (substr($var['logo_url'],0,4) != 'http')
	{
		$var['logo_url'] = 'http://'.$var['logo_url'];
	}
	$var['logo_title'] = $GLOBALS['phpgw_info']['server']['login_logo_title']?$GLOBALS['phpgw_info']['server']['login_logo_title']:'www.eGroupWare.org';
	$tmpl->set_var($var);

	if (!@$GLOBALS['phpgw_info']['server']['login_virtual_keyboard']) 
		$tmpl->set_var('show_kbd','none'); 

	if (@$GLOBALS['phpgw_info']['server']['login_show_language_selection'])
	{
		$select_lang = '<select name="lang" onchange="'."location.href=location.href+(location.search?'&':'?')+'lang='+this.value".'">';
		$langs = $GLOBALS['phpgw']->translation->get_installed_langs();
		uasort($langs,'strcasecmp');
		foreach ($langs as $key => $name)	// if we have a translation use it
		{
			$select_lang .= "\n\t".'<option value="'.$key.'"'.($key == $GLOBALS['phpgw_info']['user']['preferences']['common']['lang'] ? ' selected="1"' : '').'>'.$name.'</option>';
		}
		$select_lang .= "\n</select>\n";
		$tmpl->set_var(array(
			'lang_language' => lang('Language'),
			'select_language' => $select_lang,
		));
	}
	else
	{
		$tmpl->set_block('login_form','language_select');
		$tmpl->set_var('language_select','');
	}

	$tmpl->set_var('autocomplete', ($GLOBALS['phpgw_info']['server']['autocomplete_login'] ? 'autocomplete="off"' : ''));

	// soh mostra o captcha se for login sem certificado....
	if($GLOBALS['phpgw_info']['server']['captcha'] && $_GET['cd']!='300' )
	{
		$aux_captcha = '<input type="hidden" name="' . session_name() . '"  value="' . session_id() . '" >';
	//        setcookie(session_name(),base64_encode(session_convert($key_convert . session_id(),$key_convert)),0);
		if($valor_contador > $GLOBALS['phpgw_info']['server']['num_badlogin'])
		{
			$aux_captcha = '<div class="login_label" >
			   <img id="id_captcha" src="./security/captcha.php?' . session_name() . '=' . session_id() . '" title="'.lang('Security code').'" alt="'.lang('Security code').'" style="position:static;">
			   <input class="input" type="text" maxlength="50" size="20" name="codigo" id="codigo" value="" >
			   <input type="hidden" name="' . session_name() . '"  value="' . session_id() . '" >
			   </div>';
		}
	}
	$tmpl->set_var('captcha',$aux_captcha);

	// Testa se deve incluir applet para login com certificado......
	if ($_GET['cd']=='300' && $GLOBALS['phpgw_info']['server']['certificado']==1)
	{
		//Zera o Cookie contador, responsavel pelo captcha
		$_SESSION['contador'] = 0;
		$valor_contador = 0;
		$link_alterna_login = '<img src="phpgwapi/templates/default/images/warning.gif"/><a href="login.php">' . lang('Access without Digital Certificate') . '</a>';
		$tmpl->set_var('show','none');
		$tmpl->set_var('action','<div id="action"><img style="border:0px;margin:31px 0px 58px 0px;" src="phpgwapi/templates/default/images/acao.gif" /></div>');
		// gera parametro com tokens suportados ....
		$var_tokens = '';
		for($ii = 1; $ii < 11; ++$ii)
		{
			if($GLOBALS['phpgw_info']['server']['test_token' . $ii . '1'])
				$var_tokens .= $GLOBALS['phpgw_info']['server']['test_token' . $ii . '1'] . ',';
		}

		if(!$var_tokens)
		{
			$var_tokens = 'ePass2000Lx;/usr/lib/libepsng_p11.so,ePass2000Win;c:/windows/system32/ngp11v211.dll';
		}
		$param1 = "
											'<param name=\"token\" value=\"" . substr($var_tokens,0,strlen($var_tokens)) . "\"> ' +
										   ";
		$param2 = "
											'token=\"" . substr($var_tokens,0,strlen($var_tokens)) . "\" ' +
										   ";

		$cod_applet =

/*    // com debug ativado
            '<script type="text/javascript">
					if (navigator.userAgent.match(\'MSIE\')){
						document.write(\'<object style="display:yes;width:0;height:0;vertical-align:bottom;" id="login_applet" \' +
						\'classid="clsid:8AD9C840-044E-11D1-B3E9-00805F499D93"> \' +
						\'<param name="type" value="application/x-java-applet;version=1.5"> \' +
						\'<param name="code" value="LoginApplet.class"> \' +
						\'<param name="locale" value="' . $lang . '"> \' +
						\'<param name="mayscript" value="true"> \' + '
						. $param1
						. ' \'<param name="archive" value="ExpressoCertLogin.jar,ExpressoCert.jar,commons-httpclient-3.1.jar,commons-logging-1.1.1.jar,commons-codec-1.3.jar,bcmail-jdk15-142.jar,mail.jar,activation.jar,bcprov-jdk15-142.jar"> \' +
                        \'<param name="debug" value="true"> \' +
						\'</object>\');
					}
					else {
						document.write(\'<embed style="display:yes;width:0;height:0;vertical-align:bottom;" id="login_applet" code="LoginApplet.class" locale="' . $lang . '"\' +
						\'archive="ExpressoCertLogin.jar,ExpressoCert.jar,commons-httpclient-3.1.jar,commons-logging-1.1.1.jar,commons-codec-1.3.jar,bcmail-jdk15-142.jar,mail.jar,activation.jar,bcprov-jdk15-142.jar" \' + '
						. $param2
						. ' \'type="application/x-java-applet;version=1.5" debug= "true" mayscript > \' +
						\'<noembed> \' +
						\'No Java Support. \' +
						\'</noembed> \' +
						\'</embed> \');
					}
				</script>';
*/
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

	// Adiciona c�digo personalizado de outro template
	// que esteja utilizando o login_default.php
	if(is_file('.'.$template_dir.'/login.inc.php')) {
		include_once('.'.$template_dir.'/login.inc.php');
	}

	$tmpl->pfp('loginout','login_form');
	}

?>

