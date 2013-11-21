<?php
	/**************************************************************************\
	* eGroupWare                                                               *
	* http://www.egroupware.org                                                *
	* This file was originaly written by Dan Kuykendall                        *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/
	/* $Id: header.inc.php.template,v 1.55.2.1 2004/08/03 14:05:35 reinerj Exp $ */

	/**************************************************************************\
	* !!!!!!! EDIT THESE LINES !!!!!!!!                                        *
	* This setting allows you to easily move the include directory and the     *
	* base of the eGroupWare install. Simple edit the following 2 lines with   *
	* the absolute path to fit your site, and you should be up and running.    *
	\**************************************************************************/
	ob_start();
	define('PHPGW_SERVER_ROOT','EXPRESSO_DIR');
	define('PHPGW_INCLUDE_ROOT','EXPRESSO_DIR');
	$GLOBALS['phpgw_info']['server']['header_admin_user'] = 'expresso-admin';
	$GLOBALS['phpgw_info']['server']['header_admin_password'] = 'HEADER_PWD';
	$GLOBALS['phpgw_info']['server']['setup_acl'] = '';
 
	// Opcoes exclusivas a partir da versão 2.0 :: Configurar via setup/header
	$GLOBALS['phpgw_info']['server']['captcha'] = 1;
	$GLOBALS['phpgw_info']['server']['num_badlogin'] = 2;
	$GLOBALS['phpgw_info']['server']['atributoexpiracao'] = '';                                 
        $GLOBALS['phpgw_info']['server']['atributousuarios'] = '';                                  
	$GLOBALS['phpgw_info']['server']['certificado'] = 0;
	$GLOBALS['phpgw_info']['server']['certificado_atributo_cpf'] = '';
	$GLOBALS['phpgw_info']['server']['use_assinar_criptografar'] = 0;
	$GLOBALS['phpgw_info']['server']['num_max_certs_to_cipher'] = 0;
	
	// Opcoes exlusivas para o Expresso Livre
	$GLOBALS['phpgw_info']['server']['use_https'] = 0;
	$GLOBALS['phpgw_info']['server']['sugestoes_email_to'] = '';
	$GLOBALS['phpgw_info']['server']['domain_name'] = '';
	$GLOBALS['phpgw_info']['server']['use_prefix_organization'] = False;
	// If you want to identify your App Server (recommended for multiple servers):          
	//$GLOBALS['phpgw_info']['server']['use_frontend_id']   = 1024;
	//$GLOBALS['phpgw_info']['server']['use_frontend_name'] = '01';

	/* eGroupWare domain-specific db settings */
	$GLOBALS['phpgw_domain']['default'] = array(
		'db_host' => '/tmp',
		'db_port' => '5432',
		'db_name' => 'expresso',
		'db_user' => 'postgres',
		'db_pass' => '',
		// Look at the README file
		'db_type' => 'pgsql',
		// This will limit who is allowed to make configuration modifications
		'config_user'   => 'expresso-admin',
		'config_passwd' => 'HEADER_PWD'
	);

	/*
	** If you want to have your domains in a select box, change to True
	** If not, users will have to login as user@domain
	** Note: This is only for virtual domain support, default domain users can login only using
	** there loginid.
	*/
	$GLOBALS['phpgw_info']['server']['show_domain_selectbox'] = False;

	$GLOBALS['phpgw_info']['server']['db_persistent'] = False;

	/*
	** eGroupWare can handle session management using the database or 
	** the session support built into PHP4 which usually gives better
	** performance. 
	** Your choices are 'db' or 'php4'
	*/
	$GLOBALS['phpgw_info']['server']['sessions_type'] = 'php4';

	/* Select which login template set you want, most people will use default */
	$GLOBALS['phpgw_info']['login_template_set'] = 'default';

	/* This is used to control mcrypt's use */
	$GLOBALS['phpgw_info']['server']['mcrypt_enabled'] = False;
	/* Set this to 'old' for versions < 2.4, otherwise the exact mcrypt version you use. */
	$GLOBALS['phpgw_info']['server']['versions']['mcrypt'] = '';

	/*
	** This is a random string used as the initialization vector for mcrypt
	** feel free to change it when setting up eGrouWare on a clean database,
	** but you must not change it after that point!
	** It should be around 30 bytes in length.
	*/
	$GLOBALS['phpgw_info']['server']['mcrypt_iv'] = 'Ngi4u5HOw64uCuhEdAOlZlmdiZCRNE';
	if(!function_exists('perfgetmicrotime'))                                                
	{ 
		if(!isset($GLOBALS['phpgw_info']['flags']['nocachecontrol']) || !$GLOBALS['phpgw_info']['flags']['nocachecontrol'])
		{
			header('Cache-Control: no-cache, must-revalidate');  // HTTP/1.1
			header('Pragma: no-cache');                          // HTTP/1.0
		}
		else
		{
			// allow caching by browser
			session_cache_limiter(PHP_VERSION >= 4.2 ? 'private_no_expire' : 'private');
		}
	}
	/* debugging settings */
	define('DEBUG_APP',  False);
	define('DEBUG_API',  False);
	define('DEBUG_DATATYPES',  False);
	define('DEBUG_LEVEL',  3);
	define('DEBUG_OUTPUT', 2); /* 1 = screen,  2 = DB. For both use 3. */
	define('DEBUG_TIMER', False);

	function perfgetmicrotime()
	{
		list($usec, $sec) = explode(' ',microtime());
		return ((float)$usec + (float)$sec);
	}

	if (DEBUG_TIMER)
	{
		$GLOBALS['debug_timer_start'] = perfgetmicrotime();
	}

	/**************************************************************************\
	* Do not edit these lines                                                  *
	\**************************************************************************/
	define('PHPGW_API_INC',PHPGW_INCLUDE_ROOT.'/phpgwapi/inc');
	include(PHPGW_SERVER_ROOT.'/phpgwapi/setup/setup.inc.php');
	$GLOBALS['phpgw_info']['server']['versions']['phpgwapi'] = $setup_info['phpgwapi']['version'];
	$GLOBALS['phpgw_info']['server']['versions']['current_header'] = $setup_info['phpgwapi']['versions']['current_header'];
	unset($setup_info);
	$GLOBALS['phpgw_info']['server']['versions']['header'] = '2.5.1';
	/* This is a fix for NT */
	if(!isset($GLOBALS['phpgw_info']['flags']['noapi']) || !$GLOBALS['phpgw_info']['flags']['noapi'] == True)
	{
		include(PHPGW_API_INC . '/functions.inc.php');
	}
	$connection_id = $GLOBALS['phpgw']->session->sessionid;
	if (!strlen($connection_id) != 32){
		include("header.session.inc.php");
	}

	/*
	  Leave off the final php closing tag, some editors will add
	  a \n or space after which will mess up cookies later on
	*/
