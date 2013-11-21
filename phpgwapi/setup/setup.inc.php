<?php
	/**************************************************************************\
	* eGroupWare - phpgwapi setup                                              *
	* http://www.egroupware.org                                                *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/


	/* Basic information about this app */
	$setup_info['phpgwapi']['name']      = 'phpgwapi';
	$setup_info['phpgwapi']['title']     = 'API';
	$setup_info['phpgwapi']['version']   = '2.5.1.0';
	$setup_info['phpgwapi']['versions']['current_header'] = '2.5.1';
	$setup_info['phpgwapi']['enable']    = 3;
	$setup_info['phpgwapi']['app_order'] = 1;

	/* The tables this app creates */
	$setup_info['phpgwapi']['tables'][]  = 'phpgw_config';
	$setup_info['phpgwapi']['tables'][]  = 'phpgw_applications';
	$setup_info['phpgwapi']['tables'][]  = 'phpgw_acl';
	$setup_info['phpgwapi']['tables'][]  = 'phpgw_accounts';
	$setup_info['phpgwapi']['tables'][]  = 'phpgw_preferences';
	$setup_info['phpgwapi']['tables'][]  = 'phpgw_sessions';
	$setup_info['phpgwapi']['tables'][]  = 'phpgw_app_sessions';
	$setup_info['phpgwapi']['tables'][]  = 'phpgw_access_log';
	$setup_info['phpgwapi']['tables'][]  = 'phpgw_hooks';
	$setup_info['phpgwapi']['tables'][]  = 'phpgw_languages';
	$setup_info['phpgwapi']['tables'][]  = 'phpgw_lang';
	$setup_info['phpgwapi']['tables'][]  = 'phpgw_nextid';
	$setup_info['phpgwapi']['tables'][]  = 'phpgw_categories';
	$setup_info['phpgwapi']['tables'][]  = 'phpgw_addressbook';
	$setup_info['phpgwapi']['tables'][]  = 'phpgw_addressbook_extra';
	$setup_info['phpgwapi']['tables'][]  = 'phpgw_log';
	$setup_info['phpgwapi']['tables'][]  = 'phpgw_log_msg';
	$setup_info['phpgwapi']['tables'][]  = 'phpgw_interserv';
	$setup_info['phpgwapi']['tables'][]  = 'phpgw_vfs';
	$setup_info['phpgwapi']['tables'][]  = 'phpgw_history_log';
	$setup_info['phpgwapi']['tables'][]  = 'phpgw_async';