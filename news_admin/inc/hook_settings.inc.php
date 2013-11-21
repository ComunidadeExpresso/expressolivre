<?php
	/**************************************************************************\
	* eGroupWare - Webpage News Admin                                          *
	* http://www.egroupware.org                                                *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	* --------------------------------------------                             *
	* This program was sponsered by Golden Glair productions                   *
	* http://www.goldenglair.com                                               *
	\**************************************************************************/


	$show_entries = array(
		0 => lang('No'),
		1 => lang('Yes'),
		2 => lang('Yes').' - '.lang('small view'),
	);	
	create_select_box('Show news articles on main page?','homeShowLatest',$show_entries,
		'Should News_Admin display the latest article headlines on the main screen.');
	unset($show_entries);

	create_input_box('Number of articles to display on the main screen','homeShowLatestCount',
			'The number of articles to display on the main screen.','10',3);

