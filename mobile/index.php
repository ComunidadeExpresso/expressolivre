<?php
	//TODO: Aplicar o conceito de subapp (mobileapp)

	$phpgw_info = array();
	$GLOBALS['sessionid'] = isset($_GET['sessionid']) ? $_GET['sessionid'] : @$_COOKIE['sessionid'];

	$proxies = explode(',',$_SERVER['HTTP_X_FORWARDED_HOST']);
	//$fwConstruct = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $proxies[0] : $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$fwConstruct = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $proxies[0] : $_SERVER['HTTP_HOST'];	
	$REQUEST_URI = substr( $_SERVER['REQUEST_URI'], 0, strpos( $_SERVER['REQUEST_URI'], "/mobile/"+1));
	if( strpos( $_SERVER['REQUEST_URI'], "/mobile/" ) !== false )
	{
		$REQUEST_URI = substr($_SERVER['REQUEST_URI'], 0, strpos( $_SERVER['REQUEST_URI'], "/mobile/"));
	}
	
	$fwConstruct .= urldecode($REQUEST_URI);
	
	if(!$GLOBALS['sessionid'])
	{
	    if ($_SERVER['HTTPS'] != 'on')
	    {
	    	$aux = 'http://';
	    }
	    else
	    {
	    	$aux = 'https://';
	    }
	  
	    Header('Location: ' . $aux . $fwConstruct . "/login.php");
	  	exit;
	}

	if ($GLOBALS['phpgw_info']['server']['use_https'] > 0)
	{
		if ($_SERVER['HTTPS'] != 'on') {
   			Header('Location: https://' . $fwConstruct . $_SERVER['REQUEST_URI']);
			exit;
		}
	}

	/*
		This is the menuaction driver for the multi-layered design
	*/

	if(isset($_REQUEST['menuaction']))
	{
		list($mobileapp,$class,$method) = explode('.',@$_REQUEST['menuaction']);
		if(! $mobileapp || ! $class || ! $method)
		{
			$invalid_data = True;
		}
	}
	else
	{
		$mobileapp = 'home';
		$invalid_data = True;
	}

	
	$GLOBALS['phpgw_info']['flags'] = array(
		'noheader'   => True,
		'nonavbar'   => True,
		'currentapp' => 'mobile',
		'mobileapp'  => $mobileapp,
	);

	include_once('../header.inc.php');
	include_once('./mobile_header.inc.php');

	if(	array_key_exists('expressoMail1_2',$GLOBALS['phpgw_info']['user']['apps']) === FALSE ||
		array_key_exists('contactcenter',$GLOBALS['phpgw_info']['user']['apps']) === FALSE /*||
		array_key_exists('calendar',$GLOBALS['phpgw_info']['user']['apps']) === FALSE*/) {
			$GLOBALS['phpgw']->session->phpgw_setcookie('lem', null);
			$GLOBALS['phpgw']->session->phpgw_setcookie('pem', null);			
			Header('Location: ' . $GLOBALS['phpgw']->link('/mobile/login.php?cd=97'));
	}
	
	if($mobileapp == 'home')
	{
		start_prefered_app();
	}
	
	$GLOBALS[$class] = CreateObject(sprintf('%s.%s','mobile',$class));
	$public_functions = $GLOBALS[$class]->public_functions;
	if((is_array($GLOBALS[$class]->public_functions) && $GLOBALS[$class]->public_functions[$method]) && ! $invalid_data)
	{						
		$GLOBALS['phpgw_info']['mobiletemplate'] = CreateObject("mobile.mobiletemplate");					
		$GLOBALS['phpgw_info']['mobiletemplate'] -> print_page($class,$method);		
		unset($mobileapp);
		unset($class);
		unset($method);
		unset($invalid_data);

	}
	else
	{
		if(!$mobileapp || !$class || !$method)
		{
			if(@is_object($GLOBALS['phpgw']->log))
			{
				$GLOBALS['phpgw']->log->message(array(
					'text' => 'W-BadmenuactionVariable, menuaction missing or corrupt: %1',
					'p1'   => $menuaction,
					'line' => __LINE__,
					'file' => __FILE__
				));
			}
		}

		if(!is_array($GLOBALS[$class]->public_functions) || ! $GLOBALS[$class]->public_functions[$method] && $method)
		{
			if(@is_object($GLOBALS['phpgw']->log))
			{
				$GLOBALS['phpgw']->log->message(array(
					'text' => 'W-BadmenuactionVariable, attempted to access private method: %1',
					'p1'   => $method,
					'line' => __LINE__,
					'file' => __FILE__
				));
			}
		}
		if(@is_object($GLOBALS['phpgw']->log))
		{
			$GLOBALS['phpgw']->log->commit();
		}		
		start_prefered_app();
	}

?>