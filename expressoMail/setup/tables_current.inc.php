<?php
	/***********************************************************************************\
	* Expresso Administração                                                            *
	* by Joao Alfredo Knopik Junior (joao.alfredo@gmail.com, jakjr@celepar.pr.gov.br)   *
	* ----------------------------------------------------------------------------------*
	*  This program is free software; you can redistribute it and/or modify it          *
	*  under the terms of the GNU General Public License as published by the            *
	*  Free Software Foundation; either version 2 of the License, or (at your           *
	*  option) any later version.                                                       *
	\***********************************************************************************/
	$phpgw_baseline = array(
		'expressomail_dynamic_contact' => array(
			'fd' => array(
				'id' => array('type' => 'auto','nullable' => False),
				'owner' => array('type' => 'int','precision' => '16','nullable' => False),
				'name' => array('type' => 'varchar','precision' => '100','nullable' => true),
				'mail' => array('type' => 'varchar','precision' => '100','nullable' => False),
				'number_of_messages' => array('type' => 'int','precision' => '16','nullable' => False),
				'timestamp' => array('type' => 'int','precision' => '16','nullable' => False),
			),
			'pk' => array('id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		
        'phpgw_certificados' => array(
            'fd' => array(
                'email' => array( 'type' => 'varchar', 'precision' => 60, 'nullable' => false),
                'chave_publica' => array( 'type' => 'text'),
                'expirado' => array('type' => 'bool', 'default' => 'false'),
                'revogado' => array('type' => 'bool', 'default' => 'false'),
                'serialnumber' => array('type' => 'int', 'precision' => 8, 'nullable' => false),
                'authoritykeyidentifier' => array( 'type' => 'text', 'nullable' => false),
            ),
            'pk' => array('email','serialnumber','authoritykeyidentifier'),
            'fk' => array(),
            'ix' => array(),
            'uc' => array()
		),
		
		'expressomail_label' => array(
			'fd' => array(
				'id' => array('type' => 'auto','nullable' => False),
				'user_id' => array('type' => 'int', 'precision' => '8','nullable' => true),
				'name' => array('type' => 'varchar','precision' => '255','nullable' => true),
				'border_color' => array('type' => 'varchar','precision' => '7','nullable' => true),
				'background_color' => array('type' => 'varchar','precision' => '7','nullable' => true),
				'font_color' => array('type' => 'varchar','precision' => '7','nullable' => true),
				'slot' => array('type' => 'int', 'precision' => '8', 'nullable' => False)
			),
			'pk' => array('id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),

		'expressomail_followupflag' => array(
			'fd' => array(
				'id' => array('type' => 'auto','nullable' => False),
				'user_id' => array('type' => 'int', 'precision' => '8','nullable' => true),
				'name' => array('type' => 'varchar','precision' => '255','nullable' => False)
			),
			'pk' => array('id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
	
		'expressomail_message_followupflag' => array(
			'fd' => array(
				'id' => array('type' => 'auto','nullable' => False),
				'followupflag_id' => array('type' => 'int', 'precision' => '8','nullable' => False),
				'message_id' => array('type' => 'varchar', 'precision' => '100','nullable' => False, 'default' => 'unknown' ),
				'border_color' => array('type' => 'varchar','precision' => '7','nullable' => true),
				'background_color' => array('type' => 'varchar','precision' => '7','nullable' => true),
				'font_color' => array('type' => 'varchar','precision' => '7','nullable' => true),
				'alarm_deadline' => array('type' => 'timestamp', 'nullable' => true),
				'done_deadline' => array('type' => 'timestamp', 'nullable' => true),
				'is_done' => array('type' => 'int', 'precision' => '8','nullable' => true),
				'sent' => array('type' => 'int', 'precision' => '2','nullable' => false, 'default' => 0),
			),
			'pk' => array('id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		
		),
	
		'expressomail_attachment' => array(
			'fd' => array(
				'id' => array('type' => 'auto','nullable' => False),
				'source' => array('type' => 'blob','nullable' => False),
				'type' => array('type' => 'varchar','precision' => '255','nullable' => False),
				'name' => array('type' => 'varchar','precision' => '255','nullable' => False),
                'disposition' => array('type' => 'varchar','precision' => '20','nullable' => true),
				'size' => array('type' => 'int','precision' => '16','nullable' => False),
                'dtstamp' => array('type' => 'int','precision' => '16','nullable' => False),
				'owner' => array('type' => 'int', 'precision' => '8','nullable' => True),
				
			),
			'pk' => array('id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		)
	);
?>
