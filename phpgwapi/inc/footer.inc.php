<?php
  /**************************************************************************\
  * eGroupWare API - phpgwapi footer                                         *
  * This file written by Dan Kuykendall <seek3r@phpgroupware.org>            *
  * and Joseph Engo <jengo@phpgroupware.org>                                 *
  * Closes out interface and db connections                                  *
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


	$d1 = strtolower(substr(PHPGW_APP_INC,0,3));
	if($d1 == 'htt' || $d1 == 'ftp')
	{
		echo "Failed attempt to break in via an old Security Hole!<br>\n";
		exit;
	} unset($d1);

	/**************************************************************************\
	* Include the apps footer files if it exists                               *
	\**************************************************************************/
	if (PHPGW_APP_INC != PHPGW_API_INC &&	// this prevents an endless inclusion on the homepage 
		                                	// (some apps set currentapp in hook_home => it's not releyable)
		(file_exists (PHPGW_APP_INC . '/footer.inc.php') || isset($_GET['menuaction'])) &&
		$GLOBALS['phpgw_info']['flags']['currentapp'] != 'home' &&
		$GLOBALS['phpgw_info']['flags']['currentapp'] != 'login' &&
		$GLOBALS['phpgw_info']['flags']['currentapp'] != 'logout' &&
		!@$GLOBALS['phpgw_info']['flags']['noappfooter'])
	{
		if ($_GET['menuaction'])
		{
			list($app,$class,$method) = explode('.',$_GET['menuaction']);
			if (is_array($GLOBALS[$class]->public_functions) && isset($GLOBALS[$class]->public_functions['footer']))
			{
//				eval("\$GLOBALS[$class]->footer();");
				$GLOBALS[$class]->footer();
			}
			elseif(file_exists(PHPGW_APP_INC.'/footer.inc.php'))
			{
				include(PHPGW_APP_INC . '/footer.inc.php');
			}
		}
		elseif(file_exists(PHPGW_APP_INC.'/footer.inc.php'))
		{
			include(PHPGW_APP_INC . '/footer.inc.php');
		}
	}
	if ($GLOBALS['phpgw_info']['flags']['need_footer'])
	{
		echo $GLOBALS['phpgw_info']['flags']['need_footer'];
	}
	if(function_exists('parse_navbar_end'))
	{
		parse_navbar_end();
	}
	if (DEBUG_TIMER)
	{
		$GLOBALS['debug_timer_stop'] = perfgetmicrotime();
		echo 'Page loaded in ' . ($GLOBALS['debug_timer_stop'] - $GLOBALS['debug_timer_start']) . ' seconds.';
	}

   /*
    * Verifica��o de permiss�o para o mensageiro instant�neo e a sua inicializa��o caso haja permiss�o
    */
    if ( $GLOBALS['phpgw_info']['apps']['jabberit_messenger'] )
    	require_once PHPGW_SERVER_ROOT . '/jabberit_messenger/inc/jabberit_acl.inc.php';