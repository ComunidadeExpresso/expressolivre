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
		'phpgw_cal' => array(
			'fd' => array(
				'cal_id'              => array( 'type' => 'auto',                           'nullable' => false ),
				'uid'                 => array( 'type' => 'varchar', 'precision' => '255',  'nullable' => false ),
				'owner'               => array( 'type' => 'int',     'precision' => '8',    'nullable' => false ),
				'category'            => array( 'type' => 'varchar', 'precision' => '30' ),
				'groups'              => array( 'type' => 'varchar', 'precision' => '255' ),
				'datetime'            => array( 'type' => 'int',     'precision' => '8' ),
				'mdatetime'           => array( 'type' => 'int',     'precision' => '8' ),
				'edatetime'           => array( 'type' => 'int',     'precision' => '8' ),
				'priority'            => array( 'type' => 'int',     'precision' => '8',    'nullable' => false, 'default' => '2' ),
				'cal_type'            => array( 'type' => 'varchar', 'precision' => '10' ),
				'is_public'           => array( 'type' => 'int',     'precision' => '8',    'nullable' => false, 'default' => '1' ),
				'title'               => array( 'type' => 'varchar', 'precision' => '1024', 'nullable' => false, 'default' => '1' ),
				'description'         => array( 'type' => 'text' ),
				'location'            => array( 'type' => 'varchar', 'precision' => '255' ),
				'reference'           => array( 'type' => 'int',     'precision' => '8',    'nullable' => false, 'default' => '0' ),
				'ex_participants'     => array( 'type' => 'text' ),
				'observations'        => array( 'type' => 'text' ),
				'attachment'          => array( 'type' => 'text' ),
				'alter_by'            => array( 'type' => 'varchar', 'precision' => '160' ),
				'organizer'           => array( 'type' => 'varchar', 'precision' => '255' ),
				'last_status'         => array( 'type' => 'char',    'precision' => '1' ),
				'last_update'         => array( 'type' => 'int',     'precision' => '8' ),
				'notifications_owner' => array( 'type' => 'int',                            'nullable' => false, 'default' => '0' ),
			),
			'pk' => array('cal_id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		'phpgw_cal_holidays' => array(
			'fd' => array(
				'hol_id' => array('type' => 'auto','nullable' => False),
				'locale' => array('type' => 'char','precision' => '2','nullable' => False),
				'name' => array('type' => 'varchar','precision' => '50','nullable' => False),
				'mday' => array('type' => 'int','precision' => '8','nullable' => False,'default' => '0'),
				'month_num' => array('type' => 'int','precision' => '8','nullable' => False,'default' => '0'),
				'occurence' => array('type' => 'int','precision' => '8','nullable' => False,'default' => '0'),
				'dow' => array('type' => 'int','precision' => '8','nullable' => False,'default' => '0'),
				'observance_rule' => array('type' => 'int','precision' => '8','nullable' => False,'default' => '0')
			),
			'pk' => array('hol_id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		'phpgw_cal_repeats' => array(
			'fd' => array(
				'cal_id' => array('type' => 'int','precision' => '8','nullable' => False),
				'recur_type' => array('type' => 'int','precision' => '8','nullable' => False),
				'recur_use_end' => array('type' => 'int','precision' => '8','nullable' => True,'default' => '0'),
				'recur_enddate' => array('type' => 'int','precision' => '8','nullable' => True),
				'recur_interval' => array('type' => 'int','precision' => '8','nullable' => True,'default' => '1'),
				'recur_data' => array('type' => 'int','precision' => '8','nullable' => True,'default' => '1'),
				'recur_exception' => array('type' => 'varchar','nullable' => True,'default' => '')
			),
			'pk' => array(),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		'phpgw_cal_user' => array(
			'fd' => array(
				'cal_id' => array('type' => 'int','precision' => '8','nullable' => False,'default' => '0'),
				'cal_login' => array('type' => 'int','precision' => '8','nullable' => False,'default' => '0'),
				'cal_status' => array('type' => 'char','precision' => '1','nullable' => True,'default' => 'A'),
				'cal_type' => array('type' => 'varchar','precision' => '1','nullable' => False,'default' => 'u')
			),
			'pk' => array('cal_id','cal_login'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		'phpgw_cal_extra' => array(
			'fd' => array(
				'cal_id' => array('type' => 'int','precision' => '4','nullable' => False),
				'cal_extra_name' => array('type' => 'varchar','precision' => '40','nullable' => False),
				'cal_extra_value' => array('type' => 'varchar','precision' => '255','nullable' => False,'default' => '')
			),
			'pk' => array('cal_id','cal_extra_name'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		)
	);
?>
