<?php
	 /**************************************************************************\
	 * eGroupWare API - phpgwapi loader                                         *
	 * This file written by Dan Kuykendall <seek3r@phpgroupware.org>            *
	 * and Joseph Engo <jengo@phpgroupware.org>                                 *
	 * Has a few functions, but primary role is to load the phpgwapi            *
	 * Copyright (C) 2000, 2001 Dan Kuykendall                                  *
	 * -------------------------------------------------------------------------*
	 * This library is part of the eGroupWare API                               *
	 * http://www.egroupware.org/api                                            *
	 * ------------------------------------------------------------------------ *
	 * This library is free software; you can redistribute it and/or modify it  *
	 * under the terms of the GNU Lesser General Public License as published by *
	 * the Free Software Foundation; either version 2.1 of the License,         *
	 * or any later version.                                                    *
	 * This library is distributed in the hope that it will be useful, but      *
	 * WITHOUT ANY WARRANTY; without even the implied warranty of               *
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     *
	 * See the GNU Lesser General Public License for more details.              *
	 * You should have received a copy of the GNU Lesser General Public License *
	 * along with this library; if not, write to the Free Software Foundation,  *
	 * Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA            *
	 \**************************************************************************/
	
	
	/***************************************************************************\
	* If running in PHP3, then force admin to upgrade                           *
	\***************************************************************************/

	error_reporting(error_reporting() & ~E_NOTICE);
	
	include(PHPGW_API_INC.'/common_functions.inc.php');
	
	/*!
	 @function lang
	 @abstract function to handle multilanguage support
	*/
	function lang($key,$m1='',$m2='',$m3='',$m4='',$m5='',$m6='',$m7='',$m8='',$m9='',$m10='')
	{
		if(is_array($m1))
		{
			$vars = $m1;
		}
		else
		{
			$vars = array($m1,$m2,$m3,$m4,$m5,$m6,$m7,$m8,$m9,$m10);
		}
		// Get the translation from Lang File, if the database is down.
		if(!$GLOBALS['phpgw']->translation){
			$fn = PHPGW_SERVER_ROOT.'/phpgwapi/setup/phpgw_'.$GLOBALS['_SERVER']['HTTP_ACCEPT_LANGUAGE'].'.lang';
			if (file_exists($fn)){
				$fp = fopen($fn,'r');
				while ($data = fgets($fp,16000)){
					list($message_id,$app_name,$null,$content) = explode("\t",substr($data,0,-1));
					$GLOBALS['phpgw_info']['phpgwapi']['lang'][$message_id] =  $content;
				}
				fclose($fp);
			}
			$return = str_replace('%1',$vars[0],$GLOBALS['phpgw_info']['phpgwapi']['lang'][$key]);			
			return $return;  
		}
		$value = $GLOBALS['phpgw']->translation->translate("$key",$vars);
		return $value;
	}

        function get_theme()
        {

            $test_cookie = get_var('THEME', 'COOKIE');

            // se o cookie foi definido coloca tema na sessão
            if (!empty($test_cookie))
            {
                $_SESSION['THEME'] = $test_cookie;
            }

            // se tema não estiver definido na sessão retorna $GLOBALS['phpgw_info']['user']['preferences']['common']['theme']
            if (!$_SESSION['THEME'])
            {
                return $GLOBALS['phpgw_info']['user']['preferences']['common']['theme'];
            }
            
            // senão retorna o tema definido na sessão
            return $_SESSION['THEME'];
        }

	/* Make sure the header.inc.php is current. */
	if ($GLOBALS['phpgw_info']['server']['versions']['header'] < $GLOBALS['phpgw_info']['server']['versions']['current_header'])
	{
		header("location:setup/manageheader.php");
	}

	/* Make sure the developer is following the rules. */
	if (!isset($GLOBALS['phpgw_info']['flags']['currentapp']))
	{
		/* This object does not exist yet. */
	/*	$GLOBALS['phpgw']->log->write(array('text'=>'W-MissingFlags, currentapp flag not set'));*/

		echo '<b>!!! YOU DO NOT HAVE YOUR $GLOBALS[\'phpgw_info\'][\'flags\'][\'currentapp\'] SET !!!';
		echo '<br>!!! PLEASE CORRECT THIS SITUATION !!!</b>';
	}

	print_debug('sane environment','messageonly','api');

	/****************************************************************************\
	* Multi-Domain support                                                       *
	\****************************************************************************/
	
	/* make them fix their header */
	if (!isset($GLOBALS['phpgw_domain']))
	{
		echo '<center><b>The administrator must upgrade the header.inc.php file before you can continue.</b></center>';
		exit;
	}
	if (!isset($GLOBALS['phpgw_info']['server']['default_domain']) ||	// allow to overwrite the default domain
		!isset($GLOBALS['phpgw_domain'][$GLOBALS['phpgw_info']['server']['default_domain']]))
	{
		reset($GLOBALS['phpgw_domain']);
		list($GLOBALS['phpgw_info']['server']['default_domain']) = each($GLOBALS['phpgw_domain']);
	}
	if (isset($_POST['login']))	// on login
	{
		$GLOBALS['login'] = $_POST['login'];
		if (strstr($GLOBALS['login'],'@') === False || count($GLOBALS['phpgw_domain']) == 1)
		{
			$GLOBALS['login'] .= '@' . get_var('logindomain',array('POST'),$GLOBALS['phpgw_info']['server']['default_domain']);
		}
		$parts = explode('@',$GLOBALS['login']);
		$GLOBALS['phpgw_info']['user']['domain'] = array_pop($parts);
	}
	else	// on "normal" pageview
	{
		$GLOBALS['phpgw_info']['user']['domain'] = get_var('domain', array('GET', 'COOKIE'), FALSE);
	}

	if (@isset($GLOBALS['phpgw_domain'][$GLOBALS['phpgw_info']['user']['domain']]))
	{
		$GLOBALS['phpgw_info']['server']['db_host'] = $GLOBALS['phpgw_domain'][$GLOBALS['phpgw_info']['user']['domain']]['db_host'];
		$GLOBALS['phpgw_info']['server']['db_port'] = $GLOBALS['phpgw_domain'][$GLOBALS['phpgw_info']['user']['domain']]['db_port'];
		$GLOBALS['phpgw_info']['server']['db_name'] = $GLOBALS['phpgw_domain'][$GLOBALS['phpgw_info']['user']['domain']]['db_name'];
		$GLOBALS['phpgw_info']['server']['db_user'] = $GLOBALS['phpgw_domain'][$GLOBALS['phpgw_info']['user']['domain']]['db_user'];
		$GLOBALS['phpgw_info']['server']['db_pass'] = $GLOBALS['phpgw_domain'][$GLOBALS['phpgw_info']['user']['domain']]['db_pass'];
		$GLOBALS['phpgw_info']['server']['db_type'] = $GLOBALS['phpgw_domain'][$GLOBALS['phpgw_info']['user']['domain']]['db_type'];
	}
	else
	{
		$GLOBALS['phpgw_info']['server']['db_host'] = $GLOBALS['phpgw_domain'][$GLOBALS['phpgw_info']['server']['default_domain']]['db_host'];
		$GLOBALS['phpgw_info']['server']['db_port'] = $GLOBALS['phpgw_domain'][$GLOBALS['phpgw_info']['server']['default_domain']]['db_port'];
		$GLOBALS['phpgw_info']['server']['db_name'] = $GLOBALS['phpgw_domain'][$GLOBALS['phpgw_info']['server']['default_domain']]['db_name'];
		$GLOBALS['phpgw_info']['server']['db_user'] = $GLOBALS['phpgw_domain'][$GLOBALS['phpgw_info']['server']['default_domain']]['db_user'];
		$GLOBALS['phpgw_info']['server']['db_pass'] = $GLOBALS['phpgw_domain'][$GLOBALS['phpgw_info']['server']['default_domain']]['db_pass'];
		$GLOBALS['phpgw_info']['server']['db_type'] = $GLOBALS['phpgw_domain'][$GLOBALS['phpgw_info']['server']['default_domain']]['db_type'];
	}

	if ($GLOBALS['phpgw_info']['flags']['currentapp'] != 'login' && ! $GLOBALS['phpgw_info']['server']['show_domain_selectbox'])
	{
		unset ($GLOBALS['phpgw_domain']); // we kill this for security reasons
	}

	print_debug('domain',@$GLOBALS['phpgw_info']['user']['domain'],'api');

	 /****************************************************************************\
	 * These lines load up the API, fill up the $phpgw_info array, etc            *
	 \****************************************************************************/
	 /* Load main class */
	$GLOBALS['phpgw'] = CreateObject('phpgwapi.phpgw');
	 /************************************************************************\
	 * Load up the main instance of the db class.                             *
	 \************************************************************************/
	$GLOBALS['phpgw']->db           = CreateObject('phpgwapi.db');
	if ($GLOBALS['phpgw']->debug)
	{
		$GLOBALS['phpgw']->db->Debug = 1;
	}
	$GLOBALS['phpgw']->db->Halt_On_Error = 'no';
	/* jakjr: ExpressoLivre: We do not count the config table. */
	if (!
	$GLOBALS['phpgw']->db->connect(
		$GLOBALS['phpgw_info']['server']['db_name'],
		$GLOBALS['phpgw_info']['server']['db_host'],
		$GLOBALS['phpgw_info']['server']['db_port'],
		$GLOBALS['phpgw_info']['server']['db_user'],
		$GLOBALS['phpgw_info']['server']['db_pass'],
		$GLOBALS['phpgw_info']['server']['db_type']
	) )
	//@$GLOBALS['phpgw']->db->query("SELECT COUNT(config_name) FROM phpgw_config");
	//if(!@$GLOBALS['phpgw']->db->next_record())
	{
		
		/* BEGIN - CELEPAR - jakjr - 05/06/2006 */
		/* $setup_dir = str_replace($_SERVER['PHP_SELF'],'index.php','setup/'); */
		/*echo '<center><b>Fatal Error:</b> It appears that you have not created the database tables for '
			.'eGroupWare.  Click <a href="' . $setup_dir . '">here</a> to run setup.</center>';*/
		echo '<center><b>'.lang("ExpressoLivre is unavailable at this moment. Code %1<br>Please, try later.","001").'</b></center>';
		/* END - CELEPAR - jakjr - 05/06/2006 */
		exit;
	}
	$GLOBALS['phpgw']->db->Halt_On_Error = 'yes';

	/* Fill phpgw_info["server"] array */
	// An Attempt to speed things up using cache premise
	/* jakjr: ExpressoLivre does not use cache. */
	/*
	$GLOBALS['phpgw']->db->query("select config_value from phpgw_config WHERE config_app='phpgwapi' and config_name='cache_phpgw_info'",__LINE__,__FILE__);
	if ($GLOBALS['phpgw']->db->num_rows())
	{
		$GLOBALS['phpgw']->db->next_record();
		$GLOBALS['phpgw_info']['server']['cache_phpgw_info'] = stripslashes($GLOBALS['phpgw']->db->f('config_value'));
	}*/

	/* jakjr: ExpressoLivre does not use cache. */
	/*	
	$cache_query = "select content from phpgw_app_sessions where"
		." sessionid = '0' and loginid = '0' and app = 'phpgwapi' and location = 'config'";

	$GLOBALS['phpgw']->db->query($cache_query,__LINE__,__FILE__);
	$server_info_cache = $GLOBALS['phpgw']->db->num_rows();
	*/
	/*
	if(@$GLOBALS['phpgw_info']['server']['cache_phpgw_info'] && $server_info_cache)
	{
		$GLOBALS['phpgw']->db->next_record();
		$GLOBALS['phpgw_info']['server'] = unserialize(stripslashes($GLOBALS['phpgw']->db->f('content')));
	}
	else
	{*/
		$GLOBALS['phpgw']->db->query("SELECT * from phpgw_config WHERE config_app='phpgwapi'",__LINE__,__FILE__);
		while ($GLOBALS['phpgw']->db->next_record())
		{
			$GLOBALS['phpgw_info']['server'][$GLOBALS['phpgw']->db->f('config_name')] = stripslashes($GLOBALS['phpgw']->db->f('config_value'));
		}

		/*
		if(@isset($GLOBALS['phpgw_info']['server']['cache_phpgw_info']))
		{
			if($server_info_cache)
			{
				$cache_query = "DELETE FROM phpgw_app_sessions WHERE sessionid='0' and loginid='0' and app='phpgwapi' and location='config'";
				$GLOBALS['phpgw']->db->query($cache_query,__LINE__,__FILE__);
			}
			$cache_query = 'INSERT INTO phpgw_app_sessions(sessionid,loginid,app,location,content) VALUES('
				. "'0','0','phpgwapi','config','".addslashes(serialize($GLOBALS['phpgw_info']['server']))."')";
			$GLOBALS['phpgw']->db->query($cache_query,__LINE__,__FILE__);
		}*/
	//}
	unset($cache_query);
	unset($server_info_cache);
	if(@isset($GLOBALS['phpgw_info']['server']['enforce_ssl']) && !$_SERVER['HTTPS'])
	{
		Header('Location: https://' . $GLOBALS['phpgw_info']['server']['hostname'] . $GLOBALS['phpgw_info']['server']['webserver_url'] . $_SERVER['REQUEST_URI']);
		exit;
	}

	/****************************************************************************\
	* This is a global constant that should be used                              *
	* instead of / or \ in file paths                                            *
	\****************************************************************************/
	define('SEP',filesystem_separator());

	/************************************************************************\
	* Required classes                                                       *
	\************************************************************************/
	$GLOBALS['phpgw']->log          = CreateObject('phpgwapi.errorlog');
	$GLOBALS['phpgw']->translation  = CreateObject('phpgwapi.translation');
	$GLOBALS['phpgw']->common       = CreateObject('phpgwapi.common');
	$GLOBALS['phpgw']->hooks        = CreateObject('phpgwapi.hooks');
	$GLOBALS['phpgw']->auth         = CreateObject('phpgwapi.auth');
	$GLOBALS['phpgw']->accounts     = CreateObject('phpgwapi.accounts');
	$GLOBALS['phpgw']->acl          = CreateObject('phpgwapi.acl');
	$GLOBALS['phpgw']->session      = CreateObject('phpgwapi.sessions');
	$GLOBALS['phpgw']->preferences  = CreateObject('phpgwapi.preferences');
	$GLOBALS['phpgw']->applications = CreateObject('phpgwapi.applications');
	$GLOBALS['phpgw']->css          = CreateObject('phpgwapi.css');
	print_debug('main class loaded', 'messageonly','api');
	if (! isset($GLOBALS['phpgw_info']['flags']['included_classes']['error']) ||
		! $GLOBALS['phpgw_info']['flags']['included_classes']['error'])
	{
		include_once(PHPGW_INCLUDE_ROOT.'/phpgwapi/inc/class.error.inc.php');
		$GLOBALS['phpgw_info']['flags']['included_classes']['error'] = True;
	}

	/*****************************************************************************\
	* ACL defines - moved here to work for xml-rpc/soap, also                     *
	\*****************************************************************************/
	define('PHPGW_ACL_READ',1);
	define('PHPGW_ACL_ADD',2);
	define('PHPGW_ACL_EDIT',4);
	define('PHPGW_ACL_DELETE',8);
	define('PHPGW_ACL_PRIVATE',16);
	define('PHPGW_ACL_GROUP_MANAGERS',32);
	define('PHPGW_ACL_CUSTOM_1',64);
	define('PHPGW_ACL_CUSTOM_2',128);
	define('PHPGW_ACL_CUSTOM_3',256);

	/****************************************************************************\
	* Forcing the footer to run when the rest of the script is done.             *
	\****************************************************************************/
	register_shutdown_function(array($GLOBALS['phpgw']->common, 'phpgw_final'));

	/****************************************************************************\
	* Stuff to use if logging in or logging out                                  *
	\****************************************************************************/
	if ($GLOBALS['phpgw_info']['flags']['currentapp'] == 'login' || $GLOBALS['phpgw_info']['flags']['currentapp'] == 'logout')
	{
		if ($GLOBALS['phpgw_info']['flags']['currentapp'] == 'login')
		{
			if (@$_POST['login'] != '')
			{
				if (count($GLOBALS['phpgw_domain']) > 1)
				{
					list($login) = explode('@',$_POST['login']);
				}
				else
				{
					$login = $_POST['login'];
				}
				print_debug('LID',$login,'app');
				$login_id = $GLOBALS['phpgw']->accounts->name2id($login);
				print_debug('User ID',$login_id,'app');
				$GLOBALS['phpgw']->accounts->accounts($login_id);
				$GLOBALS['phpgw']->preferences->preferences($login_id);
				$GLOBALS['phpgw']->datetime = CreateObject('phpgwapi.date_time');
			}
		}
	/**************************************************************************\
	* Everything from this point on will ONLY happen if                        *
	* the currentapp is not login or logout                                    *
	\**************************************************************************/
	}
	else
	{
		if (! $GLOBALS['phpgw']->session->verify())
		{
			// we forward to the same place after the re-login
			if ($GLOBALS['phpgw_info']['server']['webserver_url'] && $GLOBALS['phpgw_info']['server']['webserver_url'] != '/')
			{
				list(,$relpath) = explode($GLOBALS['phpgw_info']['server']['webserver_url'],$_SERVER['PHP_SELF'],2);
			}
			else	// the webserver-url is empty or just a slash '/' (eGW is installed in the docroot and no domain given)
			{
				if (preg_match('/^https?:\/\/[^\/]*\/(.*)$/',$relpath=$_SERVER['PHP_SELF'],$matches))
				{
					$relpath = $matches[1];
				}
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
			
			if( $ifMobile )
			{
				Header('Location: '.$GLOBALS['phpgw_info']['server']['webserver_url'].'/login.php?cd=66');
				exit;
			}
			else
			{
				// this removes the sessiondata if its saved in the URL
				$query = preg_replace('/[&]?sessionid(=|%3D)[^&]+&kp3(=|%3D)[^&]+&domain=.*$/','',$_SERVER['QUERY_STRING']);
				Header('Location: '.$GLOBALS['phpgw_info']['server']['webserver_url'].'/login.php?cd=10&phpgw_forward='.urlencode($relpath.(!empty($query) ? '?'.$query : '')));
				exit;
			}
		}

		$GLOBALS['phpgw']->datetime = CreateObject('phpgwapi.date_time');

		/* A few hacker resistant constants that will be used throught the program */
		define('PHPGW_TEMPLATE_DIR', ExecMethod('phpgwapi.phpgw.common.get_tpl_dir', 'phpgwapi'));
		define('PHPGW_IMAGES_DIR', ExecMethod('phpgwapi.phpgw.common.get_image_path', 'phpgwapi'));
		define('PHPGW_IMAGES_FILEDIR', ExecMethod('phpgwapi.phpgw.common.get_image_dir', 'phpgwapi'));
		define('PHPGW_APP_ROOT', ExecMethod('phpgwapi.phpgw.common.get_app_dir'));
		define('PHPGW_APP_INC', ExecMethod('phpgwapi.phpgw.common.get_inc_dir'));
		define('PHPGW_APP_TPL', ExecMethod('phpgwapi.phpgw.common.get_tpl_dir'));
		define('PHPGW_IMAGES', ExecMethod('phpgwapi.phpgw.common.get_image_path'));
		define('PHPGW_APP_IMAGES_DIR', ExecMethod('phpgwapi.phpgw.common.get_image_dir'));

		/*	define('PHPGW_APP_IMAGES_DIR', $GLOBALS['phpgw']->common->get_image_dir()); */

		/* Moved outside of this logic
		define('PHPGW_ACL_READ',1);
		define('PHPGW_ACL_ADD',2);
		define('PHPGW_ACL_EDIT',4);
		define('PHPGW_ACL_DELETE',8);
		define('PHPGW_ACL_PRIVATE',16);
		*/

		/********* This sets the user variables *********/
		$GLOBALS['phpgw_info']['user']['private_dir'] = $GLOBALS['phpgw_info']['server']['files_dir']
			. '/users/'.$GLOBALS['phpgw_info']['user']['userid'];

		/* This will make sure that a user has the basic default prefs. If not it will add them */
		$GLOBALS['phpgw']->preferences->verify_basic_settings();

		/********* Optional classes, which can be disabled for performance increases *********/
		while ($phpgw_class_name = each($GLOBALS['phpgw_info']['flags']))
		{
			if (preg_match('/enable_/',$phpgw_class_name[0]))
			{
				$enable_class = str_replace('enable_','',$phpgw_class_name[0]);
				$enable_class = str_replace('_class','',$enable_class);
				eval('$GLOBALS["phpgw"]->' . $enable_class . ' = createobject(\'phpgwapi.' . $enable_class . '\');');
			}
		}
		unset($enable_class);
		reset($GLOBALS['phpgw_info']['flags']);


		if (!include(PHPGW_SERVER_ROOT . '/phpgwapi/themes/' . get_theme() . '.theme'))
		{
			if(!include(PHPGW_SERVER_ROOT . '/phpgwapi/themes/default.theme'))
			{
				/* Hope we don't get to this point.  Better then the user seeing a */
				/* complety back screen and not know whats going on                */
				echo '<body bgcolor="FFFFFF">';
				$GLOBALS['phpgw']->log->write(array('text'=>'F-Abort, No themes found'));

				exit;
			}
		}

		/*************************************************************************\
		* These lines load up the templates class                                 *
		\*************************************************************************/
		if(!@$GLOBALS['phpgw_info']['flags']['disable_Template_class'])
		{
			$GLOBALS['phpgw']->template = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
			preg_match('/(.*)\/(.*)/', PHPGW_APP_TPL, $matches);

			if ( $GLOBALS['phpgw_info']['flags']['currentapp'] != "jabberit_messenger" )
				$_SESSION['phpgw_info'][$GLOBALS['phpgw_info']['flags']['currentapp']]['user']['preferences']['common']['template_set'] = $matches[2];
		}


		/*************************************************************************\
		* If they are using frames, we need to set some variables                 *
		\*************************************************************************/
		if (((isset($GLOBALS['phpgw_info']['user']['preferences']['common']['useframes']) &&
			$GLOBALS['phpgw_info']['user']['preferences']['common']['useframes']) &&
			$GLOBALS['phpgw_info']['server']['useframes'] == 'allowed') ||
			($GLOBALS['phpgw_info']['server']['useframes'] == 'always'))
		{
			$GLOBALS['phpgw_info']['flags']['navbar_target'] = 'phpgw_body';
		}

		/*************************************************************************\
		* Verify that the users session is still active otherwise kick them out   *
		\*************************************************************************/
		if ($GLOBALS['phpgw_info']['flags']['currentapp'] != 'home' &&
			$GLOBALS['phpgw_info']['flags']['currentapp'] != 'about' &&
			$GLOBALS['phpgw_info']['flags']['currentapp'] != 'mobile')
		{
			// This will need to use ACL in the future
			if (! $GLOBALS['phpgw_info']['user']['apps'][$GLOBALS['phpgw_info']['flags']['currentapp']] ||
				(@$GLOBALS['phpgw_info']['flags']['admin_only'] &&
				! $GLOBALS['phpgw_info']['user']['apps']['admin']))
			{
				$GLOBALS['phpgw']->common->phpgw_header();
				if ($GLOBALS['phpgw_info']['flags']['noheader'])
				{
					echo parse_navbar();
				}

				$GLOBALS['phpgw']->log->write(array('text'=>'W-Permissions, Attempted to access %1','p1'=>$GLOBALS['phpgw_info']['flags']['currentapp']));

				echo '<p><center><b>'.lang('Access not permitted').'</b></center>';
				$GLOBALS['phpgw']->common->phpgw_exit(True);
			}
		}

		if(!is_object($GLOBALS['phpgw']->datetime))
		{
			$GLOBALS['phpgw']->datetime = CreateObject('phpgwapi.date_time');
		}
		$GLOBALS['phpgw']->applications->read_installed_apps();	// to get translated app-titles
		
		/*************************************************************************\
		* Load the header unless the developer turns it off                       *
		\*************************************************************************/
		if (!@$GLOBALS['phpgw_info']['flags']['noheader'])
		{
			$GLOBALS['phpgw']->common->phpgw_header();
		}

	}
