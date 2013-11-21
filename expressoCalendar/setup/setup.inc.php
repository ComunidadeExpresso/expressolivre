<?php
	/***********************************************************************************\
	* Expresso Calendar                										   
	* 
	* ---------------------------------------------------------------------------------*
	*  This program is free software; you can redistribute it and/or modify it		   *
	*  under the terms of the GNU General Public License as published by the		   *
	*  Free Software Foundation; either version 2 of the License, or (at your		   *
	*  option) any later version.													   *
	\***********************************************************************************/

	$setup_info['expressoCalendar']['name']      	= 'expressoCalendar';
	$setup_info['expressoCalendar']['title']     	= 'Expresso Calendar';
	/* Ao incrementar versão, não esquecer de declarar função do tables_update.inc.php*/
	$setup_info['expressoCalendar']['version']   	= '1.012';
	$setup_info['expressoCalendar']['app_order']	= 10;

	$setup_info['expressoCalendar']['tables'][]             =  'calendar_timezones';
	$setup_info['expressoCalendar']['tables'][]		=  'calendar_signature_alarm';
	$setup_info['expressoCalendar']['tables'][]		=  'calendar_signature';
	$setup_info['expressoCalendar']['tables'][]		=  'calendar_repeat_occurrence';	 
	$setup_info['expressoCalendar']['tables'][]		=  'calendar_repeat';
	$setup_info['expressoCalendar']['tables'][]		=  'calendar_alarm';
 	$setup_info['expressoCalendar']['tables'][]		=  'calendar_participant';
	$setup_info['expressoCalendar']['tables'][]		=  'calendar_participant_status';
	$setup_info['expressoCalendar']['tables'][]		=  'calendar_attach';
	$setup_info['expressoCalendar']['tables'][]		=  'attachment';		 	
	$setup_info['expressoCalendar']['tables'][]		=  'calendar_to_calendar_object';
	$setup_info['expressoCalendar']['tables'][]		=  'calendar';
	$setup_info['expressoCalendar']['tables'][]		=  'calendar_object';
	$setup_info['expressoCalendar']['tables'][]		=  'calendar_object_type';
	$setup_info['expressoCalendar']['tables'][]		=  'calendar_class';
	$setup_info['expressoCalendar']['tables'][]		=  'calendar_ex_participant';
	$setup_info['expressoCalendar']['tables'][]		=  'calendar_permission';
	$setup_info['expressoCalendar']['tables'][]		=  'module_preference';
    $setup_info['expressoCalendar']['tables'][]     =  'calendar_repeat_ranges';
    $setup_info['expressoCalendar']['tables'][]     =  'calendar_task_to_activity_object';
	$setup_info['expressoCalendar']['tables'][]		=  'calendar_historic';

	$setup_info['expressoCalendar']['enable']		= 1;

	$setup_info['expressoCalendar']['author'] = 'autor';

	$setup_info['expressoCalendar']['maintainer'] = 'mantedor';

	$setup_info['expressoCalendar']['license']  = 'GPL';
	$setup_info['expressoCalendar']['description'] = 'Modulo de Calendario ExpressoLivre';

	$setup_info['expressoCalendar']['hooks'][] = 'admin';

	/* The hooks this app includes, needed for hooks registration */
//	$setup_info['expressoCalendar']['hooks'][] = 'admin';
	
//	/* Dependencies for this app to work */
//	$setup_info['expressoCalendar']['depends'][] = array(
//		'appname' => 'phpgwapi',
//		'versions' => Array('2.4.0')
//	);
    
    
?>
