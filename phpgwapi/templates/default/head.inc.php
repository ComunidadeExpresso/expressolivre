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


	if($GLOBALS['phpgw_info']['user']['preferences']['common']['show_generation_time'])
	{
		$mtime = microtime(); 
		$mtime = explode(' ',$mtime); 
		$mtime = $mtime[1] + $mtime[0]; 
		$GLOBALS['page_start_time'] = $mtime; 
	}

	// get used language code
	$lang_code = $GLOBALS['phpgw_info']['user']['preferences']['common']['lang'];

	$bodyheader = ' bgcolor="' . $GLOBALS['phpgw_info']['theme']['bg_color'] . '" alink="'
		. $GLOBALS['phpgw_info']['theme']['alink'] . '" link="' . $GLOBALS['phpgw_info']['theme']['link'] . '" vlink="'
		. $GLOBALS['phpgw_info']['theme']['vlink'] . '"';

	if( isset($GLOBALS['phpgw_info']['server']['htmlcompliant']) && !$GLOBALS['phpgw_info']['server']['htmlcompliant'] )
	{
		$bodyheader .= '';
	}
	
	$currentapp = $GLOBALS['phpgw_info']['flags']['currentapp'] ;

	#_debug_array($GLOBALS['phpgw_info']['user']['preferences']['common']);

	//pngfix defaults to yes
	if(!$GLOBALS['phpgw_info']['user']['preferences']['common']['disable_pngfix'])
	{
		$pngfix_src = $GLOBALS['phpgw_info']['server']['webserver_url'] . '/phpgwapi/templates/'.$GLOBALS['phpgw_info']['server']['template_set'].'/js/pngfix.js';
		$pngfix ='<!-- This solves the Internet Explorer PNG-transparency bug, but only for IE 5.5 and higher --> 
		<!--[if lt IE 7]>
		<script src="'.$pngfix_src.'" type="text/javascript"></script>
		<![endif]-->';
	}

	if(!$GLOBALS['phpgw_info']['user']['preferences']['common']['disable_slider_effects'])
	{
		$slider_effects_src = $GLOBALS['phpgw_info']['server']['webserver_url'] . '/phpgwapi/templates/'.$GLOBALS['phpgw_info']['server']['template_set'].'/js/slidereffects.js';
		$slider_effects = '<script src="'.$slider_effects_src.'" type="text/javascript"></script>';
	}
	else
	{
		$simple_show_hide_src = $GLOBALS['phpgw_info']['server']['webserver_url'] . '/phpgwapi/templates/'.$GLOBALS['phpgw_info']['server']['template_set'].'/js/simple_show_hide.js';
		$simple_show_hide = '<script src="'.$simple_show_hide_src.'" type="text/javascript"></script>';
	}

	$cookie_manager = '<script src="'.$GLOBALS['phpgw_info']['server']['webserver_url'] . '/phpgwapi/templates/'.$GLOBALS['phpgw_info']['server']['template_set'].'/js/cookieManager.js" type="text/javascript"></script>';	
	$tpl = CreateObject('phpgwapi.Template',PHPGW_TEMPLATE_DIR);
	$tpl->set_unknowns('remove');
	$tpl->set_file(array('_head' => 'head.tpl'));
	$tpl->set_block('_head','head');

	$app = $GLOBALS['phpgw_info']['flags']['currentapp'];
	$app = $app ? ' ['.(isset($GLOBALS['phpgw_info']['apps'][$app]) ? $GLOBALS['phpgw_info']['apps'][$app]['title'] : lang($app)).']':'';

	$webserver_url = $GLOBALS['phpgw_info']['server']['webserver_url'];
	$webserver_url = ( !empty($webserver_url) ) ? $webserver_url : '/';
	
	if(strrpos($webserver_url,'/') === false || strrpos($webserver_url,'/') != (strlen($webserver_url)-1))
		$webserver_url .= '/';
	
	if( is_null($_SESSION['phpgw_info'][$GLOBALS['phpgw_info']['flags']['currentapp']]['user']))
		$tmpDefault = "default";
	else
		$tmpDefault = $_SESSION['phpgw_info'][$GLOBALS['phpgw_info']['flags']['currentapp']]['user']['preferences']['common']['template_set'];

	$var = Array(
		'img_icon'      => $webserver_url . $currentapp . '/templates/'.$tmpDefault.'/images/navbar.png',
		'img_shortcut'  => $webserver_url . $currentapp . '/templates/'.$tmpDefault.'/images/navbar.png',
		'pngfix'        => $pngfix,
		'slider_effects'	=> ((isset($slider_effects))? $slider_effects:""),
		'simple_show_hide'	=> $simple_show_hide,
		'lang_code'		=> $lang_code,
		'charset'       => $GLOBALS['phpgw']->translation->charset(),
		'font_family'   => $GLOBALS['phpgw_info']['theme']['font'],
		'website_title' => $GLOBALS['phpgw_info']['server']['site_title'].$app,
		'body_tags'     => $bodyheader .' '. $GLOBALS['phpgw']->common->get_body_attribs(),
		'css'           => $GLOBALS['phpgw']->common->get_css(),
		'java_script'   => $GLOBALS['phpgw']->common->get_java_script(),
		'cookie_manager'=>	$cookie_manager
	);
	$tpl->set_var($var);
	$tpl->pfp('out','head');
	unset($tpl);
?>
