<?php
	/**************************************************************************\
	* phpGroupWare                                                             *
	* http://www.phpgroupware.org                                              *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/
	
	$current_url	= substr($_SERVER["SCRIPT_NAME"], 0, strpos($_SERVER["SCRIPT_NAME"],'index.php'));
	$phpgw_info 	= array();
	$api_requested	= false;
	$invalid_data	= false;

	if(!file_exists('header.inc.php'))
	{
		Header('Location: '.$current_url.'setup/index.php');
		exit;
	}

	$GLOBALS['sessionid'] = isset($_GET['sessionid']) ? $_GET['sessionid'] : @$_COOKIE['sessionid'];
	if(!$GLOBALS['sessionid'])
	{
		Header('Location: '.$current_url.'login.php'.
		(isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING']) ?
		'?phpgw_forward='.urlencode('/index.php?'.$_SERVER['QUERY_STRING']):''));
		exit;
	}

	/*
		This is the menuaction driver for the multi-layered design
	*/
	if(isset($_GET['menuaction']))
	{
		list($app,$class,$method) = explode('.',@$_GET['menuaction']);
		if(! $app || ! $class || ! $method)
		{
			$invalid_data = True;
		}
	}
	else
	{
		$app = 'home';
		$invalid_data = True;
	}

	if($app == 'phpgwapi')
	{
		$app = 'home';
		$api_requested = True;
	}

	$GLOBALS['phpgw_info']['flags'] = array(
		'noheader'   => True,
		'nonavbar'   => True,
		'currentapp' => $app
	);
	include('./header.inc.php');

	if (($GLOBALS['phpgw_info']['server']['use_https'] == 2) && ($_SERVER['HTTPS'] != 'on'))
	{
		Header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
		exit;
	}

	if($app == 'home' && !$api_requested)
	{
		Header('Location: ' . $GLOBALS['phpgw']->link('/home.php'));
	}

	if($api_requested)
	{
		$app = 'phpgwapi';
	}

	$GLOBALS[$class] = CreateObject(sprintf('%s.%s',$app,$class));
	if( ( isset($GLOBALS[$class]->public_functions) && is_array($GLOBALS[$class]->public_functions) && $GLOBALS[$class]->public_functions[$method]) && ! $invalid_data)
	{
		execmethod($_GET['menuaction']);
		if(isset($app)){unset($app);}
		if(isset($class)){unset($class);}
		if(isset($method)){unset($method);}
		if(isset($invalid_data)){unset($invalid_data);}
		if(isset($api_requested)){unset($api_requested);}
	}
	else
	{
		if( !$app || !$class || !$method )
		{
			if(@is_object($GLOBALS['phpgw']->log))
			{
				if($menuaction)
                {			
					$GLOBALS['phpgw']->log->message(array(
						'text' => "W-BadmenuactionVariable, menuaction missing or corrupt: $menuaction",
						'p1'   => $menuaction,
						'line' => __LINE__,
						'file' => __FILE__
					));
                }
			}
		}

		if( isset($GLOBALS[$class]->public_functions) )
		{
			if(!is_array($GLOBALS[$class]->public_functions) || ! $$GLOBALS[$class]->public_functions[$method] && isset($method) )
			{
				if(@is_object($GLOBALS['phpgw']->log))
				{				
				 	if( isset($menuaction) )
	                {			
						$GLOBALS['phpgw']->log->message(array(
							'text' => "W-BadmenuactionVariable, attempted to access private method: $method",
							'p1'   => $method,
							'line' => __LINE__,
							'file' => __FILE__
						));
	                }
				}
			}
		}

		if(@is_object($GLOBALS['phpgw']->log))
		{
			$GLOBALS['phpgw']->log->commit();
		}

		Header('Location: ' . $GLOBALS['phpgw']->link('/home.php'));
	}

	if( isset($_GET['menuaction']) )
	{
		$modulo = explode('.', $_GET['menuaction']);
		
		if($modulo[0] == 'expressoAdmin' || $modulo[0] == 'calendar')
		{
			echo '<script type="text/javascript" src="prototype/plugins/jquery/jquery.min.js"></script>
				  <script type="text/javascript" src="prototype/plugins/jquery/jquery-ui.min.js"></script>
				  <script src="prototype/plugins/json2/json2.js" language="javascript"></script>
				  <script src="prototype/plugins/ejs/ejs.js" language="javascript"></script>
				  <script src="prototype/plugins/store/jquery.store.js" language="javascript"></script>
				  <script type="text/javascript" src="prototype/api/rest.js"></script>
				  <script src="prototype/api/datalayer.js" language="javascript"></script>
				  <script type="text/javascript">DataLayer.dispatchPath = "/"; REST.dispatchPath = "prototype/";REST.load("")</script>
				  <link rel="stylesheet" type="text/css" href="prototype/plugins/zebradialog/css/zebra_dialog.css"></link>
				  <script type="text/javascript" src="prototype/plugins/zebradialog/javascript/zebra_dialog.js"></script>	
 				  <script src="expressoMail/js/ccQuickAdd.js" type="text/javascript"></script>
				';
			if($modulo[0] == 'calendar')
			{
                echo '<link rel="stylesheet" href="prototype/plugins/jquery.jrating/jRating.jquery.css" type="text/css" />
                <script type="text/javascript" src="prototype/plugins/jquery.jrating/jRating.jquery.js"></script>
                <script src="expressoMail/js/common_functions.js" type="text/javascript"></script>
                <script type="text/javascript">userContacts = false; currentTypeContact = ""; REST.get("/usercontacts", false, updateDynamicContact);</script>';
            }
		}
	}
?>
