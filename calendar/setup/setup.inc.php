<?php
	/**************************************************************************\
	* eGroupWare - Calendar                                                    *
	* http://www.egroupware.org                                                *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/


	$setup_info['calendar']['name']    = 'calendar';
	$setup_info['calendar']['version'] = '2.5.1';
	$setup_info['calendar']['app_order'] = 3;
	$setup_info['calendar']['enable']  = 1;

	$setup_info['calendar']['author'] = 'Mark Peters';
	$setup_info['calendar']['license']  = 'GPL';
	$setup_info['calendar']['description'] =
		'Powerful calendar with meeting request system and ACL security.';
	$setup_info['calendar']['note'] =
		'Bassed on Webcalendar by <a href="http://www.radix.net/~cknudsen" target="_blank">Craig Knudsen</a>.<p>
		';
	$setup_info['calendar']['maintainer'] = array(
		'name'  => 'Ralf Becker',
		'email' => 'RalfBecker@outdoor-training.de'
	);

	$setup_info['calendar']['tables'][] = 'phpgw_cal';
	$setup_info['calendar']['tables'][] = 'phpgw_cal_holidays';
	$setup_info['calendar']['tables'][] = 'phpgw_cal_repeats';
	$setup_info['calendar']['tables'][] = 'phpgw_cal_user';
	$setup_info['calendar']['tables'][] = 'phpgw_cal_extra';

	/* The hooks this app includes, needed for hooks registration */
	$setup_info['calendar']['hooks'][] = 'add_def_prefs';
	$setup_info['calendar']['hooks'][] = 'admin';
	$setup_info['calendar']['hooks'][] = 'deleteaccount';
	$setup_info['calendar']['hooks'][] = 'email';
	$setup_info['calendar']['hooks'][] = 'home';
	$setup_info['calendar']['hooks'][] = 'home_day';
	$setup_info['calendar']['hooks'][] = 'home_month';
	$setup_info['calendar']['hooks'][] = 'home_week';
	$setup_info['calendar']['hooks'][] = 'home_year';
	$setup_info['calendar']['hooks'][] = 'manual';
	$setup_info['calendar']['hooks'][] = 'preferences';
	$setup_info['calendar']['hooks'][] = 'settings';
	$setup_info['calendar']['hooks'][] = 'sidebox_menu';

	/* Dependencies for this app to work */
	$setup_info['calendar']['depends'][] = array(
		 'appname' => 'phpgwapi',
		 'versions' => Array('2.5.1.0')
	);
