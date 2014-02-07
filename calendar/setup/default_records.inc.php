<?php
	/**************************************************************************\
	* eGroupWare - Setup / Calendar                                            *
	* http://www.egroupware.org                                                *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	$oProc->query('ALTER TABLE phpgw_cal ALTER COLUMN last_status SET DEFAULT \'N\'::bpchar;');
	$oProc->query('ALTER TABLE phpgw_cal ALTER COLUMN last_update SET DEFAULT (date_part(\'epoch\'::text, (\'now\'::text)::timestamp(3) with time zone) * (1000)::double precision);');
	
	// enable auto-loading of holidays from localhost by default
	$oProc->query("INSERT INTO phpgw_config (config_app, config_name, config_value) VALUES ('phpgwapi','auto_load_holidays','True')");
	$oProc->query("INSERT INTO phpgw_config (config_app, config_name, config_value) VALUES ('phpgwapi','holidays_url_path','localhost')");
