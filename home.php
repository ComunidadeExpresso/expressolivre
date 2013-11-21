<?php
	/**************************************************************************\
	* eGroupWare                                                               *
	* http://www.egroupware.org                                                *
	* The file written by Joseph Engo <jengo@phpgroupware.org>                 *
	* This file modified by Greg Haygood <shrykedude@bellsouth.net>            *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/
	$phpgw_info = array();
	$current_url = substr($_SERVER["SCRIPT_NAME"], 0, strpos($_SERVER["SCRIPT_NAME"],'home.php'));

	if (!is_file('header.inc.php'))
	{
		Header('Location: '.$current_url.'setup/index.php');
		exit;
	}

	$GLOBALS['sessionid'] = @$_GET['sessionid'] ? $_GET['sessionid'] : @$_COOKIE['sessionid'];
	if (!isset($GLOBALS['sessionid']) || !$GLOBALS['sessionid'])
	{
		Header('Location: '.$current_url.'login.php?cd=10');
		exit;
	}

	$GLOBALS['phpgw_info']['flags'] = array(
		'noheader'                => True,
		'nonavbar'                => True,
		'currentapp'              => 'home',
		'enable_network_class'    => True,
		'enable_contacts_class'   => True,
		'enable_nextmatchs_class' => True
	);
	include('header.inc.php');
	
	//detect browser
	require_once('phpgwapi/inc/class.browser.inc.php');
	
	$ifMobile	= false;
	$browser	= new browser();
	
	
	switch( $browser->get_platform() )
	{
		case browser::PLATFORM_IPHONE:
		case browser::PLATFORM_IPOD:
		case browser::PLATFORM_IPAD:
		case browser::PLATFORM_BLACKBERRY:
		case browser::PLATFORM_ANDROID:						
			$ifMobile = false;
			break;			
	}
	
	if( $ifMobile )
	{
		if( $_GET['dont_redirect_if_moble'] != 1 )
		{
			$GLOBALS['phpgw']->redirect('/mobile/index.php');
			exit;			
		}
	} 
	
	
	$GLOBALS['phpgw_info']['flags']['app_header']=lang('home');

	// Commented by alpeb: The following prevented anonymous users to get a home page. Perhaps it was done with anonymous users such as the ones
	// used by  wiki and sitemgr in mind. However, if you mark a normal user as anonymous just to avoid being shown in sessions and access log (like you would for an admin that doesn't want to be noticed), the user won't be able to login anymore. That's why I commented the code.
	/*if ($GLOBALS['phpgw']->session->session_flags == 'A')
	{
		if ($_SERVER['HTTP_REFERER'] && strstr($_SERVER['HTTP_REFERER'],'home.php') === False)
		{
			$GLOBALS['phpgw']->redirect($_SERVER['HTTP_REFERER']);
		}
		else
		{
			// redirect to the login-page, better then giving an empty page
			$GLOBALS['phpgw']->redirect('login.php');
		}
		exit;
	}*/

	if ($GLOBALS['phpgw_info']['server']['force_default_app'] && $GLOBALS['phpgw_info']['server']['force_default_app'] != 'user_choice')
	{
		$GLOBALS['phpgw_info']['user']['preferences']['common']['default_app'] = $GLOBALS['phpgw_info']['server']['force_default_app'];
	}

	if ($_GET['cd']=='yes' && $GLOBALS['phpgw_info']['user']['preferences']['common']['default_app'] &&
		$GLOBALS['phpgw_info']['user']['apps'][$GLOBALS['phpgw_info']['user']['preferences']['common']['default_app']])
	{
		$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/' . $GLOBALS['phpgw_info']['user']['preferences']['common']['default_app'] . '/' . 'index.php'));
	}
	else
	{
		$GLOBALS['phpgw']->common->phpgw_header();
		echo parse_navbar();
	}

        // Default Applications (Home Page) 
        $default_apps = Array(                  
                        'workflow',                     
                        'expressoMail1_2',
                        'calendar',
                        'news_admin'
                );
        $sorted_apps = array();
        $user_apps = $GLOBALS['phpgw_info']['user']['apps']; 
        @reset($user_apps);
        $default_apps_count = count($default_apps);
        for($i = 0; $i < $default_apps_count;++$i) {
                if(array_key_exists($default_apps[$i], $user_apps)){
                        $sorted_apps[] = $default_apps[$i];
                }               
        }
        
        foreach($GLOBALS['phpgw_info']['user']['apps'] as $i => $p) {
                $sorted_apps[] = $p['name'];
        }

	$portal_oldvarnames = array('mainscreen_showevents', 'homeShowEvents','homeShowLatest','mainscreen_showmail','mainscreen_showbirthdays','mainscreen_show_new_updated');
        $done = array();
        // Display elements, within appropriate table cells     
        @reset($sorted_apps);
        $idx = 1;
        echo "<table width='100%' cellpadding=5>";
        foreach($sorted_apps as $appname)
        {
                if((int)$done[$appname] == 1 || empty($appname)){
                        continue;
                }
                $varnames = $portal_oldvarnames;
                $varnames[] = 'homepage_display';
                $thisd = 0;
                $tmp = '';

                foreach($varnames as $varcheck)
                {

                        /*if($appname == 'expressoMail1_2') {
                                $tmp = $appname;
                                $appname = 'expressoMail';
                        }*/

                        if(array_search($appname, $default_apps) !== False){
                                $thisd = 1;
                                break;
                        }
                        if($GLOBALS['phpgw_info']['user']['preferences'][$appname][$varcheck]=='True') {
                                $thisd = 1;
                                break;
                        }
                        else  {
                                $_thisd = (int)$GLOBALS['phpgw_info']['user']['preferences'][$appname][$varcheck];
                                if($_thisd > 0) {
                                        $thisd = $_thisd;
                                        break;
                                }
                        }
                }

               if($thisd > 0)
                {
                        if($tmp) {
                $appname = $tmp;
                                $tmp = '';
                        }
                        if($idx == 0) {
                                print '<tr>';
                        }
                        print '<td style="vertical-align:top;" width="45%">';
                        $GLOBALS['phpgw']->hooks->single('home',$appname);
                        print '</td>';

                        if($idx == 2){
                                $idx = 0;
                                print '</tr>';
                        }
                        ++$idx;
                        $neworder[] = $appname;
                }
                $done[$appname] = 1;
        }
        print '</table>';

	$GLOBALS['phpgw']->common->phpgw_footer();
?>
