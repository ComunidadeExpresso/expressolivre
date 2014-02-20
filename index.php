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
	
	$current_url = substr($_SERVER["SCRIPT_NAME"], 0, strpos($_SERVER["SCRIPT_NAME"],'index.php'));

	$phpgw_info = array();
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
	//$phpgw->log->message('W-BadmenuactionVariable, menuaction missing or corrupt: %1',$menuaction);
	//$phpgw->log->commit();

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
		if( $_GET['dont_redirect_if_moble'] == 1 )
			Header('Location: ' . $GLOBALS['phpgw']->link('/home.php?dont_redirect_if_moble=1'));
		else
			Header('Location: ' . $GLOBALS['phpgw']->link('/home.php'));
	}

	if($api_requested)
	{
		$app = 'phpgwapi';
	}

	$GLOBALS[$class] = CreateObject(sprintf('%s.%s',$app,$class));
	if((is_array($GLOBALS[$class]->public_functions) && $GLOBALS[$class]->public_functions[$method]) && ! $invalid_data)
	{
		execmethod($_GET['menuaction']);
		unset($app);
		unset($class);
		unset($method);
		unset($invalid_data);
		unset($api_requested);
	}
	else
	{
		if(!$app || !$class || !$method)
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

		if(!is_array($GLOBALS[$class]->public_functions) || ! $$GLOBALS[$class]->public_functions[$method] && $method)
		{
			if(@is_object($GLOBALS['phpgw']->log))
			{				
			 	if($menuaction)
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
		if(@is_object($GLOBALS['phpgw']->log))
		{
			$GLOBALS['phpgw']->log->commit();
		}

		if( $_GET['dont_redirect_if_moble'] == 1 )
			Header('Location: ' . $GLOBALS['phpgw']->link('/home.php?dont_redirect_if_moble=1'));
		else
			Header('Location: ' . $GLOBALS['phpgw']->link('/home.php'));
				
		//$GLOBALS['phpgw']->redirect_link('/home.php');
	}

	/*if(!isset($GLOBALS['phpgw_info']['nofooter']))
	{
		$GLOBALS['phpgw']->common->phpgw_footer();
	}*/

		if($_GET['menuaction']){
			$modulo = explode('.', $_GET['menuaction']);
			
			if($modulo[0] == 'expressoAdmin1_2' || $modulo[0] == 'calendar'){
				echo '<script type="text/javascript" src="prototype/plugins/jquery/jquery.min.js" ></script>
					  <script type="text/javascript" src="prototype/plugins/jquery/jquery-ui.min.js" ></script>
					  <link rel="stylesheet" href="prototype/plugins/jqgrid/themes/prognusone/jquery-ui-1.8.2.custom.css" type="text/css" />
					  <script type="text/javascript" src="prototype/plugins/json2/json2.js" ></script>
					  <script type="text/javascript" src="prototype/plugins/ejs/ejs.js" ></script>
					  <script type="text/javascript" src="prototype/plugins/store/jquery.store.js" ></script>
					  <script type="text/javascript" src="prototype/api/rest.js" ></script>
					  <script type="text/javascript" src="prototype/api/datalayer.js"></script>
					  <script type="text/javascript">DataLayer.dispatchPath = "/"; REST.dispatchPath = "prototype/";REST.load("")</script>
					  <link rel="stylesheet" type="text/css" href="prototype/plugins/zebradialog/css/zebra_dialog.css" />
					  <script type="text/javascript" src="prototype/plugins/zebradialog/javascript/zebra_dialog.js"></script>
					  <script type="text/javascript" src="calendar/templates/default/js/quickSearch.js"></script>
					  <script type="text/javascript" src="expressoMail1_2/js/ccQuickAdd.js"></script>
					';
				if($modulo[0] == 'calendar'){
                    echo '<link rel="stylesheet" href="prototype/plugins/jquery.jrating/jRating.jquery.css" type="text/css" />
                    <script type="text/javascript" src="prototype/plugins/jquery.jrating/jRating.jquery.js"></script>
                    <script src="expressoMail1_2/js/common_functions.js" type="text/javascript"></script>
                    <script type="text/javascript">userContacts = false; currentTypeContact = ""; REST.get("/usercontacts", false, updateDynamicContact);</script>';
                }

		}
	}
?>
