<?php
	/**************************************************************************\
	* ExpressoLivre - Setup                                                     *
	* http://www.expressolivre.org                                              *
	* --------------------------------------------                             *
	* This program is free software; you can redistribute it and/or modify it  *
	* under the terms of the GNU General Public License as published by the    *
	* Free Software Foundation; either version 2 of the License, or (at your   *
	* option) any later version.                                               *
	\**************************************************************************/

	$test[] = '1.000';
	function expressoCalendar_upgrade1_000() {

		$oProc = $GLOBALS['phpgw_setup']->oProc;

		$oProc->query("ALTER TABLE calendar_participant ADD COLUMN acl character varying(10) not null DEFAULT 'r' ");
		
		$oProc->query("ALTER TABLE calendar_participant ADD COLUMN receive_notification smallint not null DEFAULT 1 ");
		$oProc->query('ALTER TABLE calendar_participant RENAME COLUMN delegated_to TO delegated_from ');
		
		$oProc->query("UPDATE calendar_participant SET acl = 'rowi' where is_organizer = 1 ");

		$oProc->query('ALTER TABLE calendar_object  ALTER COLUMN range_start TYPE bigint USING (date_part(\'epoch\',(cast(range_start as timestamp)))::bigint) * 1000');
		$oProc->query('ALTER TABLE calendar_object  ALTER COLUMN range_end TYPE bigint USING (date_part(\'epoch\',(cast(range_end as timestamp)))::bigint) * 1000');

		$oProc->query('ALTER TABLE calendar_alarm ALTER COLUMN range_end TYPE bigint USING (date_part(\'epoch\',(cast(range_end as timestamp)))::bigint) * 1000');
		$oProc->query('ALTER TABLE calendar_alarm ALTER COLUMN range_start TYPE bigint USING (date_part(\'epoch\',(cast(range_start as timestamp)))::bigint) * 1000');

		$oProc->query("ALTER TABLE attachment ADD COLUMN owner integer");

		$GLOBALS['setup_info']['expressoCalendar']['currentver'] = '1.001';
        return $GLOBALS['setup_info']['expressoCalendar']['currentver'];
	}

	$test[] = '1.001';
	function expressoCalendar_upgrade1_001() {

		$oProc = $GLOBALS['phpgw_setup']->oProc;

		$oProc->query("ALTER TABLE calendar_repeat ADD COLUMN dtstart bigint");
		$oProc->query('ALTER TABLE calendar_repeat ALTER COLUMN until DROP NOT NULL');

		$oProc->CreateTable('calendar_repeat_occurrence', array(
				'fd' => array(
				    'id' => array( 'type' => 'auto', 'nullable' => False),
				    'occurrence' => array(  'type' => 'bigint','precision' => '16', 'nullable' => False),
				    'repeat_id' => array(  'type' => 'int', 'precision' => '8', 'nullable' => False)
				),

				'pk' => array('id'),
				'fk' => array('repeat_id'),
				'ix' => array(),
				'uc' => array()
				)
		);

		$oProc->CreateTable('calendar_repeat_ranges', array(
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
			    )
		);

		$oProc->query("ALTER TABLE calendar_participant ADD COLUMN receive_notification smallint not null DEFAULT 1 ");	

		$GLOBALS['setup_info']['expressoCalendar']['currentver'] = '1.002';
        return $GLOBALS['setup_info']['expressoCalendar']['currentver'];
	};

	$test[] = '1.002';
	function expressoCalendar_upgrade1_002() {

        $oProc = $GLOBALS['phpgw_setup']->oProc;

        $oProc->query("ALTER TABLE calendar_repeat_occurrence ADD COLUMN exception smallint DEFAULT 0");

        $GLOBALS['setup_info']['expressoCalendar']['currentver'] = '1.003';
        return $GLOBALS['setup_info']['expressoCalendar']['currentver'];
	};
        
        $test[] = '1.003';
	function expressoCalendar_upgrade1_003() {
        $GLOBALS['setup_info']['expressoCalendar']['currentver'] = '1.004';
        return $GLOBALS['setup_info']['expressoCalendar']['currentver'];
	};

	$test[] = '1.004';
	function expressoCalendar_upgrade1_004() {
        $oProc = $GLOBALS['phpgw_setup']->oProc;
        $oProc->query("ALTER TABLE calendar_repeat_occurrence  ALTER COLUMN exception SET default 0");

        $GLOBALS['setup_info']['expressoCalendar']['currentver'] = '1.005';
        return $GLOBALS['setup_info']['expressoCalendar']['currentver'];
	};
        
        $test[] = '1.005';
	function expressoCalendar_upgrade1_005() {
        $oProc = $GLOBALS['phpgw_setup']->oProc;
        $oProc->query("ALTER TABLE calendar_alarm ADD COLUMN alarm_offset bigint;");
	    $oProc->query("UPDATE calendar_alarm SET alarm_offset = obj.range_start - calendar_alarm.range_start FROM calendar_object as obj WHERE obj.id = object_id;");
	    $oProc->query("ALTER TABLE calendar_alarm DROP COLUMN range_start;");
	    $oProc->query("ALTER TABLE calendar_alarm DROP COLUMN range_end;");
        $GLOBALS['setup_info']['expressoCalendar']['currentver'] = '1.006';
        return $GLOBALS['setup_info']['expressoCalendar']['currentver'];
	};
	
	
	$test[] = '1.006';
	function expressoCalendar_upgrade1_006() {
            $GLOBALS['setup_info']['expressoCalendar']['currentver'] = '1.007';
            return $GLOBALS['setup_info']['expressoCalendar']['currentver'];
	};
        
	$test[] = '1.007';
	function expressoCalendar_upgrade1_007() {
	    $oProc = $GLOBALS['phpgw_setup']->oProc;

	    $oProc->query("ALTER TABLE calendar_object ADD COLUMN priority smallint DEFAULT 0;");
	    $oProc->query("ALTER TABLE calendar_object ADD COLUMN percentage smallint DEFAULT 0;");
	    $oProc->query("ALTER TABLE calendar_object ADD COLUMN status smallint DEFAULT 0;");

	    $oProc->query("ALTER TABLE calendar_object ADD COLUMN due bigint DEFAULT 0;");

	    $oProc->query("ALTER TABLE calendar ADD COLUMN type smallint DEFAULT 0;");
	    $oProc->query("ALTER TABLE calendar_signature ADD COLUMN type smallint DEFAULT 0;");

	    $oProc->query("INSERT INTO calendar_object_type(id, name) VALUES ('2', 'TODO');");

	    $oProc->query("CREATE TABLE calendar_task_to_activity_object(id serial not null, calendar_object_activity_id integer not null, calendar_object_task_id integer not null, owner integer not null);");

	    $oProc->CreateTable('calendar_historic', array(
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
		)
	    );

	    $oProc->CreateTable('calendar_task_to_activity_object', array(
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
		)
	    );

	    $GLOBALS['setup_info']['expressoCalendar']['currentver'] = '1.008';
	    return $GLOBALS['setup_info']['expressoCalendar']['currentver'];
	};

    $test[] = '1.008';
    function expressoCalendar_upgrade1_008() {
        $oProc = $GLOBALS['phpgw_setup']->oProc;

        $oProc->query("ALTER TABLE calendar_permission ADD COLUMN owner bigint;");
        $oProc->query("UPDATE calendar_permission SET owner = sig.user_uidnumber FROM calendar_signature as sig WHERE (sig.calendar_id = object_id AND sig.is_owner = '1');");

        $GLOBALS['setup_info']['expressoCalendar']['currentver'] = '1.009';
        return $GLOBALS['setup_info']['expressoCalendar']['currentver'];
    };

    $test[] = '1.009';
    function expressoCalendar_upgrade1_009() {
        $oProc = $GLOBALS['phpgw_setup']->oProc;

        $oProc->query("ALTER TABLE attachment ALTER COLUMN type TYPE character varying(255);");
        $GLOBALS['setup_info']['expressoCalendar']['currentver'] = '1.010';
        return $GLOBALS['setup_info']['expressoCalendar']['currentver'];
    };


    $test[] = '1.010';
    function expressoCalendar_upgrade1_010() {
        $oProc = $GLOBALS['phpgw_setup']->oProc;


            $oProc->CreateTable('calendar_timezones', array(
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
            )
        );

        $oProc->query("INSERT INTO calendar_timezones(timezone, standard_frequency, standard_dtstart, standard_byday,
        standard_bymonth, standard_from, standard_to, daylight_frequency, daylight_dtstart, daylight_byday,
        daylight_bymonth, daylight_from, daylight_to, dtstamp) VALUES ('America/Sao_Paulo', 'YEARLY', '23:59',
        '4SA', '2', '-0200','-0300', 'YEARLY', '23:59', '3SA', '10', '-0300','-0200', '". time() ."');");

        $GLOBALS['setup_info']['expressoCalendar']['currentver'] = '1.011';
        return $GLOBALS['setup_info']['expressoCalendar']['currentver'];
    };

    $test[] = '1.011';
    function expressoCalendar_upgrade1_011() {
        $oProc = $GLOBALS['phpgw_setup']->oProc;

        $oProc->query("ALTER TABLE calendar_signature ADD COLUMN hidden integer");

        $GLOBALS['setup_info']['expressoCalendar']['currentver'] = '1.012';
        return $GLOBALS['setup_info']['expressoCalendar']['currentver'];
    };
        
?>