<?php
  /**************************************************************************\
  * eGroupWare                                                               *
  * http://www.egroupware.org                                                *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/


	$phpgw_baseline = array(
		
		'calendar_attach' => array(
			'fd' => array(
				'id' => array('type' => 'auto','nullable' => False),
				'object_id' => array('type' => 'int', 'precision' => '8','nullable' => True),
				'attach_id' => array('type' => 'int', 'precision' => '8','nullable' => True)
			),
			'pk' => array('id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		
		'attachment' => array(
			'fd' => array(
				'id' => array('type' => 'auto','nullable' => False),
				'source' => array('type' => 'blob','nullable' => False),
				'type' => array('type' => 'varchar','precision' => '255','nullable' => False),
				'name' => array('type' => 'varchar','precision' => '255','nullable' => False),
				'size' => array('type' => 'varchar','precision' => '255','nullable' => False),
				'owner' => array('type' => 'int', 'precision' => '8','nullable' => True)
			),
			'pk' => array('id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		'calendar' => array(
			'fd' => array(
				'id' => array( 'type' => 'auto', 'nullable' => False),
				'name' => array( 'type' => 'varchar','precision' => '150', 'nullable' => False),
				'location' => array( 'type' => 'varchar','precision' => '150', 'nullable' => False),
				'description' => array('type' => 'text', 'nullable' => True),
				'duration' => array('type' => 'int', 'precision' => '8' ,'nullable' => True),
				'tzid' => array('type' => 'varchar', 'precision' => '50' ,'nullable' => True),
				'type' => array('type' => 'int', 'precision' => '2' , 'default' => 0),
				'dtstamp' => array('type' => 'int', 'precision' => '8' ,'nullable' => True)
			),
			'pk' => array('id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		'calendar_class' => array(
            'fd' => array(
                'id' => array( 'type' => 'auto', 'nullable' => False),
                'name' => array(  'type' => 'varchar','precision' => '50', 'nullable' => False)
            ),
            'pk' => array('id'),
            'fk' => array(),
            'ix' => array(),
            'uc' => array()
		),
		
		'calendar_object_type' => array(
            'fd' => array(
                'id' => array( 'type' => 'auto', 'nullable' => False),
                'name' => array(  'type' => 'varchar','precision' => '50', 'nullable' => False)
            ),
            'pk' => array('id'),
            'fk' => array(),
            'ix' => array(),
            'uc' => array()
		),
		
		'calendar_participant_status' => array(
            'fd' => array(
                'id' => array( 'type' => 'auto', 'nullable' => False),
                'name' => array(  'type' => 'varchar','precision' => '50', 'nullable' => False)
            ),
            'pk' => array('id'),
            'fk' => array(),
            'ix' => array(),
            'uc' => array()
		),
            		
	   'calendar_to_calendar_object' => array(
            'fd' => array(
                'id' => array( 'type' => 'auto', 'nullable' => False),
                'calendar_id' => array(  'type' => 'int', 'precision' => '8', 'nullable' => False),
                'calendar_object_id' => array(  'type' => 'int', 'precision' => '8', 'nullable' => False),
            ),
            'pk' => array('id'),
            'fk' => array(),
            'ix' => array(),
            'uc' => array()
                ),

       'calendar_task_to_activity_object' => array(
            'fd' => array(
                'id' => array( 'type' => 'auto', 'nullable' => False),
                'calendar_object_activity_id' => array(  'type' => 'int', 'precision' => '8', 'nullable' => False),
                'calendar_object_task_id' => array(  'type' => 'int', 'precision' => '8', 'nullable' => False),
                'owner' => array(  'type' => 'int', 'precision' => '8', 'nullable' => False),
            ),
            'pk' => array('id'),
            'fk' => array(),
            'ix' => array(),
            'uc' => array()
        ),
		'calendar_object' => array(
			'fd' => array(
				'id'          => array( 'type' => 'auto', 'nullable' => False),
				'type_id'     => array( 'type' => 'int', 'precision' => '8', 'nullable' => False),
				'cal_uid'     => array( 'type' => 'varchar','precision' => '255', 'nullable' => True),
				'dtstamp'     => array( 'type' => 'bigint', 'precision' => '16', 'nullable' => True ),
				'dtstart'     => array( 'type' => 'bigint', 'precision' => '16', 'nullable' => False),
				'description' => array( 'type' => 'text', 'nullable' => True),
				'dtend'       => array( 'type' => 'bigint', 'precision' => '16', 'nullable' => True),
				'location'    => array( 'type' => 'varchar', 'precision' => '255', 'nullable' => True),
				'class_id'    => array( 'type' => 'int', 'precision' => '8', 'nullable' => True),
				'last_update' => array( 'type' => 'int', 'precision' => '8', 'nullable' => False),
				'range_end'   => array( 'type' => 'bigint', 'precision' => '16', 'nullable' => True),
				'summary'     => array( 'type' => 'varchar', 'precision' => '255', 'nullable' => True),
				'range_start' => array( 'type' => 'bigint', 'precision' => '16', 'nullable' => True),
				'allday'      => array( 'type' => 'int', 'precision' => '2', 'default' => 0, 'nullable' => True),
				'repeat'      => array( 'type' => 'int', 'precision' => '2', 'default' => 0, 'nullable' => True),
				'tzid'        => array( 'type' => 'varchar', 'precision' => '50' ,'nullable' => True),
				'transp'      => array( 'type' => 'int', 'precision' => '2', 'nullable' => FALSE , 'default' => 0 ),
				'sequence'    => array( 'type' => 'int', 'precision' => '8', 'nullable' => FALSE , 'default' => 0 ),
				'due'         => array( 'type' => 'int', 'precision' => '8', 'default' => 0 ),
				'percentage'  => array( 'type' => 'int', 'precision' => '2', 'default' => 0 ),
				'status'      => array( 'type' => 'int', 'precision' => '2', 'default' => 0 ),
				'priority'    => array( 'type' => 'int', 'precision' => '2', 'default' => 0 ),
			),
			'pk' => array('id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array('cal_uid')
		),
        'calendar_participant' => array(
            'fd' => array(
                'id' => array( 'type' => 'auto', 'nullable' => False),
                'user_info_id' => array(  'type' => 'int', 'precision' => '8', 'nullable' => False),
                'object_id' => array(  'type' => 'int', 'precision' => '8', 'nullable' => True),
                'delegated_from' => array( 'type' => 'int', 'precision' => '8','default' => 0, 'nullable' => False),
                'is_organizer' => array( 'type' => 'int', 'precision' => '2', 'default' => 0, 'nullable' => False),
                'is_external' => array( 'type' => 'int', 'precision' => '2', 'default' => 0, 'nullable' => False),
                'participant_status_id' => array( 'type' => 'int', 'precision' => '8', 'nullable' => false , 'default' => 4),
				'acl' => array('type' => 'varchar', 'precision' => '10' ,'default' => 'r', 'nullable' => False),
				'receive_notification' => array( 'type' => 'int', 'precision' => '2', 'default' => 1, 'nullable' => False)
            ),
            'pk' => array('id'),
            'fk' => array(),
            'ix' => array(),
            'uc' => array()
		),
		'calendar_alarm' => array(
			'fd' => array(
				'id' => array( 'type' => 'auto', 'nullable' => False),
				'action_id' => array( 'type' => 'int', 'precision' => '8', 'nullable' => False),
				'unit' => array('type' => 'varchar','precision' => '20','nullable' => True),
				'alarm_offset' => array( 'type' => 'bigint', 'precision' => '16' ),
				'time' => array('type' => 'varchar','precision' => '50','nullable' => True),
				'participant_id' => array('type' => 'int', 'precision' => '8','nullable' => True),
				'object_id' => array('type' => 'int', 'precision' => '8','nullable' => True),
				'sent' => array('type' => 'int', 'precision' => '2', 'default' => 0, 'nullable' => False)
			),
			'pk' => array('id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
        'calendar_historic' => array(
            'fd' => array(
                'id' => array( 'type' => 'auto', 'nullable' => False),
                'object_id' => array('type' => 'int', 'precision' => '8','nullable' => True),
                'user_uidnumber' => array( 'type' => 'int', 'precision' => '8', 'nullable' => False),
                'dtstamp' => array( 'type' => 'bigint', 'precision' => '16', 'nullable' => False),
                'attribute' => array('type' => 'varchar','precision' => '50','nullable' => True),
                'before_value' => array( 'type' => 'varchar', 'precision' => '255', 'nullable' => True),              
                'after_value' => array( 'type' => 'varchar', 'precision' => '255', 'nullable' => True)            
            ),
            'pk' => array('id'),
            'fk' => array(),
            'ix' => array(),
            'uc' => array()
        ),	
		
        'calendar_signature_alarm' => array(
            'fd' => array(
                'id' => array( 'type' => 'auto', 'nullable' => False),
                'action_id' => array( 'type' => 'int', 'precision' => '8', 'nullable' => False),
                'unit' => array('type' => 'varchar','precision' => '20','nullable' => True),
                'time' => array('type' => 'varchar','precision' => '50','nullable' => True),
                'calendar_signature_id' => array('type' => 'int', 'precision' => '8','nullable' => FALSE),				
            ),
            'pk' => array('id'),
            'fk' => array(),
            'ix' => array(),
            'uc' => array()
		),
            
		'calendar_ex_participant' => array(
            'fd' => array(
                'id' => array( 'type' => 'auto', 'nullable' => False),
                'name' => array(  'type' => 'varchar','precision' => '100', 'nullable' => True),
                'mail' => array(  'type' => 'varchar','precision' => '100', 'nullable' => False),
                'owner' => array(  'type' => 'int','precision' => '8', 'nullable' => False)
            ),
            'pk' => array('id'),
            'fk' => array(),
            'ix' => array(),
            'uc' => array()
		),
		'calendar_permission' => array(
			'fd' => array(
				'id' => array( 'type' => 'auto', 'nullable' => False ),
				'uidnumber' => array( 'type' => 'int', 'precision' => '8', 'nullable' => False ),
				'object_id' => array( 'type' => 'int', 'precision' => '8', 'nullable' => False ),
				'owner' => array( 'type' => 'int', 'precision' => '8' ),
				'object_type' => array( 'type' => 'int', 'precision' => '8', 'nullable' => False ),
				'permission' => array( 'type' => 'varchar','precision' => '50', 'nullable' => False ),
			),
			'pk' => array('id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		'calendar_repeat' => array(
            'fd' => array(
                'id' => array( 'type' => 'auto', 'nullable' => False),
                'frequency' => array(  'type' => 'varchar','precision' => '20', 'nullable' => False),
                'until' => array(  'type' => 'int', 'precision' => '8', 'nullable' => True),
		        'dtstart' => array(  'type' => 'bigint','precision' => '16', 'nullable' => True),
                'count' => array(  'type' => 'int', 'precision' => '8', 'nullable' => True),
                'object_id' => array(  'type' => 'int', 'precision' => '8', 'nullable' => False),
                'bysecond' => array(  'type' => 'varchar','precision' => '50', 'nullable' => True),
                'byminute' => array(  'type' => 'varchar','precision' => '50', 'nullable' => True),
                'byhour' => array(  'type' => 'varchar','precision' => '50', 'nullable' => True),
                'byday' => array(  'type' => 'varchar','precision' => '50', 'nullable' => True),
                'bymonthday' => array(  'type' => 'varchar','precision' => '50', 'nullable' => True),
                'byyearday' => array(  'type' => 'varchar','precision' => '50', 'nullable' => True),
                'byweekno' => array(  'type' => 'varchar','precision' => '50', 'nullable' => True),
                'bymonth' => array(  'type' => 'varchar','precision' => '50', 'nullable' => True),
                'bysetpos' => array(  'type' => 'varchar','precision' => '50', 'nullable' => True),
                'wkst' => array(  'type' => 'varchar','precision' => '50', 'nullable' => True),
		        'exceptions' => array(  'type' => 'varchar','precision' => '50', 'nullable' => True),
                'interval' => array(  'type' => 'int', 'precision' => '8', 'nullable' => True)
            ),
            'pk' => array('id'),
            'fk' => array(),
            'ix' => array(),
            'uc' => array()
		),

	    'calendar_repeat_ranges' => array(
            'fd' => array(
                'id' => array( 'type' => 'auto', 'nullable' => False),
                'range_start' => array(  'type' => 'bigint','precision' => '16', 'nullable' => False),
                'range_end' => array(  'type' => 'bigint', 'precision' => '16', 'nullable' => False),
                'user_info_id' => array(  'type' => 'bigint', 'precision' => '16', 'nullable' => False)
            ),
            'pk' => array('id'),
            'fk' => array(),
            'ix' => array(),
            'uc' => array()
		),

	    'calendar_repeat_occurrence' => array(
            'fd' => array(
                'id' => array( 'type' => 'auto', 'nullable' => False),
                'occurrence' => array(  'type' => 'bigint','precision' => '16', 'nullable' => False),
                'exception' => array(  'type' => 'smallint','precision' => '1', 'nullable' => False, 'default' => 0),
		'repeat_id' => array(  'type' => 'int', 'precision' => '8', 'nullable' => False)
            ),

            'pk' => array('id'),
            'fk' => array('repeat_id'),
            'ix' => array(),
            'uc' => array()
		),

		'calendar_signature' => array(
			'fd' => array(
				'id' => array( 'type' => 'auto', 'nullable' => False ),
				'user_uidnumber' => array( 'type' => 'int', 'precision' => '8', 'nullable' => False ),
				'calendar_id' => array( 'type' => 'int', 'precision' => '8', 'nullable' => false ),
				'is_owner' => array( 'type' => 'int', 'precision' => '2', 'default' => 0, 'nullable' => False ),
				'dtstamp' => array( 'type' => 'bigint', 'precision' => '16', 'precision' => '16', 'nullable' => False ),
				'msg_add' => array( 'type' => 'text', 'nullable' => True ),
				'msg_cancel' => array( 'type' => 'text', 'nullable' => True ),
				'msg_update' => array( 'type' => 'text', 'nullable' => True ),
				'msg_reply' => array( 'type' => 'text', 'nullable' => True ),
				'msg_alarms' => array( 'type' => 'text', 'nullable' => True ),
				'font_color' => array( 'type' => 'varchar','precision' => '6', 'nullable' => True ),
				'background_color' => array( 'type' => 'varchar','precision' => '6', 'nullable' => True ),
				'border_color' => array( 'type' => 'varchar','precision' => '6', 'nullable' => True ),
				'type' => array( 'type' => 'int', 'precision' => '2' , 'default' => 0 ),
				'hidden' => array( 'type' => 'int', 'precision' => '4' )
			),
			'pk' => array('id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),

				
	'module_preference' => array(
            'fd' => array(
                'id' => array( 'type' => 'auto', 'nullable' => False),
                'user_uidnumber' => array(  'type' => 'int', 'precision' => '8',  'nullable' => False),
                'value' => array(   'type' => 'varchar','precision' => '100','nullable' => False),
                'name' => array(  'type' => 'varchar', 'precision' => '50', 'nullable' => False),
                'module' => array(  'type' => 'varchar', 'precision' => '30', 'nullable' => False)
            ),
            'pk' => array('id'),
            'fk' => array(),
            'ix' => array(),
            'uc' => array()
		),


        'calendar_timezones' => array(
            'fd' => array(
                'id' => array( 'type' => 'auto', 'nullable' => False),
                'timezone' => array(  'type' => 'varchar','precision' => '150', 'nullable' => False),

                'standard_frequency' => array(  'type' => 'varchar','precision' => '20', 'nullable' => False),
                'standard_dtstart' => array(  'type' => 'varchar','precision' => '20', 'nullable' => True),
                'standard_byday' => array(  'type' => 'varchar','precision' => '50', 'nullable' => True),
                'standard_bymonth' => array(  'type' => 'varchar','precision' => '50', 'nullable' => True),
                'standard_from' => array(  'type' => 'varchar','precision' => '10', 'nullable' => True),
                'standard_to' => array(  'type' => 'varchar','precision' => '10', 'nullable' => True),

                'daylight_frequency' => array(  'type' => 'varchar','precision' => '20', 'nullable' => False),
                'daylight_dtstart' => array(  'type' => 'varchar','precision' => '20', 'nullable' => True),
                'daylight_byday' => array(  'type' => 'varchar','precision' => '50', 'nullable' => True),
                'daylight_bymonth' => array(  'type' => 'varchar','precision' => '50', 'nullable' => True),
                'daylight_from' => array(  'type' => 'varchar','precision' => '10', 'nullable' => True),
                'daylight_to' => array(  'type' => 'varchar','precision' => '10', 'nullable' => True),

                'dtstamp' => array( 'type' => 'bigint', 'precision' => '16', 'precision' => '16', 'nullable' => False)
            ),
            'pk' => array('id'),
            'fk' => array(),
            'ix' => array(),
            'uc' => array()
        ),
		
	);
?>
