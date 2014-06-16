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
	//	Since Expresso 1.2 using ExpressoMail 1.233		
	$test[] = '1.233';
	function expressoMail_upgrade1_233() {
		$setup_info['expressoMail']['currentver'] = '1.234';
		return $setup_info['expressoMail']['currentver'];
	}
	$test[] = '1.234';
	function expressoMail_upgrade1_234() {
    	$oProc = $GLOBALS['phpgw_setup']->oProc;            
            $oProc->CreateTable('phpgw_certificados',array(
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
			)
		);
		$GLOBALS['setup_info']['expressoMail']['currentver'] = '1.235';
        return $GLOBALS['setup_info']['expressoMail']['currentver'];
	}
	$test[] = '1.235';
	function expressoMail_upgrade1_235() {
		$setup_info['expressoMail']['currentver'] = '2.0.000';
		return $setup_info['expressoMail']['currentver'];
	}		
	$test[] = '2.0.000';
	function expressoMail_upgrade2_0_000() {
		$setup_info['expressoMail']['currentver'] = '2.0.001';
		return $setup_info['expressoMail']['currentver'];
	}
	$test[] = '2.0.001';
	function expressoMail_upgrade2_0_001() {
		$setup_info['expressoMail']['currentver'] = '2.0.002';
		return $setup_info['expressoMail']['currentver'];
	}	
	$test[] = '2.0.002';
	function expressoMail_upgrade2_0_002() {
		$setup_info['expressoMail']['currentver'] = '2.0.003';
		return $setup_info['expressoMail']['currentver'];
	}
	$test[] = '2.0.003';
	function expressoMail_upgrade2_0_003() {
		$setup_info['expressoMail']['currentver'] = '2.0.004';
		return $setup_info['expressoMail']['currentver'];
	}
	$test[] = '2.0.004';
	function expressoMail_upgrade2_0_004() {
		$setup_info['expressoMail']['currentver'] = '2.0.005';
		return $setup_info['expressoMail']['currentver'];
	}	
	$test[] = '2.0.005';
	function expressoMail_upgrade2_0_005() {
		$setup_info['expressoMail']['currentver'] = '2.0.006';
		return $setup_info['expressoMail']['currentver'];
	}	
	$test[] = '2.0.006';
	function expressoMail_upgrade2_0_006() {
		$setup_info['expressoMail']['currentver'] = '2.0.007';
		return $setup_info['expressoMail']['currentver'];
	}		
	$test[] = '2.0.007';
	function expressoMail_upgrade2_0_007() {
		$setup_info['expressoMail']['currentver'] = '2.0.008';
		return $setup_info['expressoMail']['currentver'];
	}	
	$test[] = '2.0.008';
	function expressoMail_upgrade2_0_008() {
		$setup_info['expressoMail']['currentver'] = '2.0.009';
		return $setup_info['expressoMail']['currentver'];
	}
	$test[] = '2.0.009';
	function expressoMail_upgrade2_0_009() {
		$setup_info['expressoMail']['currentver'] = '2.0.010';
		return $setup_info['expressoMail']['currentver'];
	}
	$test[] = '2.0.010';
	function expressoMail_upgrade2_0_010() {
		$setup_info['expressoMail']['currentver'] = '2.1.000';
		return $setup_info['expressoMail']['currentver'];
	}
	$test[] = '2.1.000';
	function expressoMail_upgrade2_1_000() {
		$setup_info['expressoMail']['currentver'] = '2.2.000';
		return $setup_info['expressoMail']['currentver'];
	}	
	$test[] = '2.2.000';
	function expressoMail_upgrade2_2_000() {
		$setup_info['expressoMail']['currentver'] = '2.2.1';
		return $setup_info['expressoMail']['currentver'];
	}
	$test[] = '2.2.1'; 
 	function expressoMail_upgrade2_2_1() { 
 	    $setup_info['expressoMail']['currentver'] = '2.2.2'; 
 	    return $setup_info['expressoMail']['currentver']; 
 	} 
	$test[] = '2.2.2'; 
 	function expressoMail_upgrade2_2_2() { 
 	    $setup_info['expressoMail']['currentver'] = '2.2.4'; 
 	    return $setup_info['expressoMail']['currentver']; 
 	}
 	$test[] = '2.2.4';
 	function expressoMail_upgrade2_2_4() {
 		$setup_info['expressoMail']['currentver'] = '2.2.6';
 		return $setup_info['expressoMail']['currentver'];
 	}
 	$test[] = '2.2.6';
 	function expressoMail_upgrade2_2_6() {
 		$setup_info['expressoMail']['currentver'] = '2.2.8';
 		return $setup_info['expressoMail']['currentver'];
 	}
 	$test[] = '2.2.8';
 	function expressoMail_upgrade2_2_8() {
 		$setup_info['expressoMail']['currentver'] = '2.2.10';
 		return $setup_info['expressoMail']['currentver'];
 	}
 	$test[] = '2.2.10';
 	function expressoMail_upgrade2_2_10() {
 		$setup_info['expressoMail']['currentver'] = '2.3.0';
 		return $setup_info['expressoMail']['currentver'];
 	} 	 	
	$test[] = '2.3.0'; 
 	function expressoMail_upgrade2_3_0() { 
 	    $setup_info['expressoMail']['currentver'] = '2.4.0'; 
 	    return $setup_info['expressoMail']['currentver']; 
 	}
	$test[] = '2.4.0';
	function expressoMail_upgrade2_4_0() {
    	$oProc = $GLOBALS['phpgw_setup']->oProc;            
            $oProc->CreateTable('expressomail_label',array(
				'fd' => array(
					'id' => array('type' => 'auto','nullable' => False),
					'user_id' => array('type' => 'int', 'precision' => '8','nullable' => true),
					'name' => array('type' => 'varchar','precision' => '255','nullable' => true),
					'border_color' => array('type' => 'varchar','precision' => '7','nullable' => true),
					'background_color' => array('type' => 'varchar','precision' => '7','nullable' => true),
					'font_color' => array('type' => 'varchar','precision' => '7','nullable' => true)
				),
				'pk' => array('id'),
				'fk' => array(),
				'ix' => array(),
				'uc' => array()
				)
			);
			$oProc->CreateTable('expressomail_followupflag',array(
				'fd' => array(
					'id' => array('type' => 'auto','nullable' => False),
					'user_id' => array('type' => 'int', 'precision' => '8','nullable' => true),
					'name' => array('type' => 'varchar','precision' => '255','nullable' => False)
				),
				'pk' => array('id'),
				'fk' => array(),
				'ix' => array(),
				'uc' => array()
				)
			);
			$oProc->CreateTable('expressomail_message_followupflag',array(
				'fd' => array(
					'id' => array('type' => 'auto','nullable' => False),
					'followupflag_id' => array('type' => 'int', 'precision' => '8','nullable' => False),
					'border_color' => array('type' => 'varchar','precision' => '7','nullable' => true),
					'background_color' => array('type' => 'varchar','precision' => '7','nullable' => true),
					'font_color' => array('type' => 'varchar','precision' => '7','nullable' => true),
					'alarm_deadline' => array('type' => 'timestamp', 'nullable' => true),
					'done_deadline' => array('type' => 'timestamp', 'nullable' => true),
					'is_done' => array('type' => 'int', 'precision' => '8','nullable' => true)
				),
				'pk' => array('id'),
				'fk' => array(),
				'ix' => array(),
				'uc' => array()
				)
			);			
			$oProc->query("ALTER TABLE expressomail_message_followupflag ADD CONSTRAINT expressomail_message_followupflag_followupflag_id_fkey FOREIGN KEY (followupflag_id) REFERENCES expressomail_followupflag (id);");
			
			$oProc->query("INSERT INTO expressomail_followupflag(name) VALUES ('Follow up');");
			$oProc->query("INSERT INTO expressomail_followupflag(name) VALUES ('Read');");
			$oProc->query("INSERT INTO expressomail_followupflag(name) VALUES ('Forward');"); 
			$oProc->query("INSERT INTO expressomail_followupflag(name) VALUES ('Answer');"); 
			$oProc->query("INSERT INTO expressomail_followupflag(name) VALUES ('Don''t forward');");
			$oProc->query("INSERT INTO expressomail_followupflag(name) VALUES ('Don''t answer');");

						
		$GLOBALS['setup_info']['expressoMail']['currentver'] = '2.4.1';
        return $GLOBALS['setup_info']['expressoMail']['currentver'];
	}
        $test[] = '2.4.1';
	function expressoMail_upgrade2_4_1() {
            $oProc = $GLOBALS['phpgw_setup']->oProc;            
            $oProc->CreateTable('expressomail_attachment',array(
			'fd' => array(
				'id' => array('type' => 'auto','nullable' => False),
				'source' => array('type' => 'blob','nullable' => False),
				'type' => array('type' => 'varchar','precision' => '255','nullable' => False),
				'name' => array('type' => 'varchar','precision' => '255','nullable' => False),
                                'disposition' => array('type' => 'varchar','precision' => '20','nullable' => true),
				'size' => array('type' => 'int','precision' => '16','nullable' => False),
                                'dtstamp' => array('type' => 'int','precision' => '16','nullable' => False),
				'owner' => array('type' => 'int', 'precision' => '8','nullable' => True)
			),
			'pk' => array('id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		)
			);						
		$GLOBALS['setup_info']['expressoMail']['currentver'] = '2.4.2';
        return $GLOBALS['setup_info']['expressoMail']['currentver'];
	}
	
	$test[] = '2.4.2';
	function expressoMail_upgrade2_4_2() {
            $oProc = $GLOBALS['phpgw_setup']->oProc;
	    $oProc->query('ALTER TABLE mail_attachment RENAME TO expressomail_attachment');
	    $GLOBALS['setup_info']['expressoMail']['currentver'] = '2.4.3';
	    return $GLOBALS['setup_info']['expressoMail']['currentver'];
	}
	
	$test[] = '2.4.3';
	function expressoMail_upgrade2_4_3() {
            $oProc = $GLOBALS['phpgw_setup']->oProc;
	    $oProc->query("ALTER TABLE expressomail_message_followupflag ADD COLUMN message_id character varying(100) not null default 'unknown' ");
 
	    $GLOBALS['setup_info']['expressoMail']['currentver'] = '2.4.4';
	    return $GLOBALS['setup_info']['expressoMail']['currentver'];
	}
	
	$test[] = '2.4.4';
	function expressoMail_upgrade2_4_4() {
            $oProc = $GLOBALS['phpgw_setup']->oProc;
		$oProc->query("DELETE FROM expressomail_label");
	    $oProc->query("ALTER TABLE expressomail_label ADD COLUMN slot bigint not null");
 
	    $GLOBALS['setup_info']['expressoMail']['currentver'] = '2.4.5';
	    return $GLOBALS['setup_info']['expressoMail']['currentver'];
	}
	
	$test[] = '2.4.5';
	function expressoMail_upgrade2_4_5() {
		$oProc = $GLOBALS['phpgw_setup']->oProc;
		/* Seta o valor padrão para a configuração de número mínimo de marcadores */
		$oProc->query("INSERT INTO phpgw_config(config_app, config_name, config_value) VALUES ('expressoMail', 'expressoMail_limit_labels', 20);");
	    $GLOBALS['setup_info']['expressoMail']['currentver'] = '2.4.6';
	    return $GLOBALS['setup_info']['expressoMail']['currentver'];
	}

	/* Registra o hook de validação do administrador*/
	$test[] = '2.4.6';
	function expressoMail_upgrade2_4_6() {
	    $GLOBALS['setup_info']['expressoMail']['currentver'] = '2.4.7';
	    return $GLOBALS['setup_info']['expressoMail']['currentver'];
	}

	$test[] = '2.4.7';
	function expressoMail_upgrade2_4_7() {
		$oProc = $GLOBALS['phpgw_setup']->oProc;

		//Criando nova tabela de contatos dinamicos
		$oProc->CreateTable('expressomail_dynamic_contact',array(
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
			)
		);

		/* Cria um indice unico para um owner e mail para nao ocorrer duplicidade em e-mails para um mesmo owner  */
		$oProc->query("ALTER TABLE expressomail_dynamic_contact ADD CONSTRAINT owner_mail UNIQUE (owner, mail)");

		//Migra dados antigos para nova tabela
		$oProc->query('SELECT * FROM phpgw_expressomail_contacts');
		$return = array();
	        while($oProc->next_record())
	        	$return[$oProc->f('id_owner')] = $oProc->f('data');

	        foreach ($return as $owner => &$value) {
        		$contacts = unserialize($value);
        		foreach ($contacts as &$contact) {
	        		$info = explode('#', $contact['email']);
        			$oProc->query("INSERT INTO expressomail_dynamic_contact (owner, name ,mail , number_of_messages ,timestamp) values ('".$owner."', '".$info[0]."', '".$info[1]."', 1, '".$contact['timestamp']."');");
	        	}
        	}

	        //Deleta tabela antiga
		$oProc->DropTable('phpgw_expressomail_contacts');

		$GLOBALS['setup_info']['expressoMail']['currentver'] = '2.4.8';
	    return $GLOBALS['setup_info']['expressoMail']['currentver'];
	}
	
	$test[] = '2.4.8';
	function expressoMail_upgrade2_4_8() {
		$oProc = $GLOBALS['phpgw_setup']->oProc;

		$oProc->query("ALTER TABLE expressomail_message_followupflag ADD COLUMN sent smallint not null default 0");

		$GLOBALS['setup_info']['expressoMail']['currentver'] = '2.4.8.1';
	    return $GLOBALS['setup_info']['expressoMail']['currentver'];
	}

    $test[] = '2.4.8.1';
    function expressoMail_upgrade2_4_8_1() {
        $GLOBALS['setup_info']['expressoMail']['currentver'] = '2.4.8.2';
        return $GLOBALS['setup_info']['expressoMail']['currentver'];
    }

	$test[] = '2.4.8.2';
    function expressoMail_upgrade2_4_8_2() {
        $GLOBALS['setup_info']['expressoMail']['currentver'] = '2.5.0';
        return $GLOBALS['setup_info']['expressoMail']['currentver'];
    }

    $test[] = '2.5.0';
    function expressoMail_upgrade2_5_0() {
        $GLOBALS['setup_info']['expressoMail']['currentver'] = '2.5.1';
        return $GLOBALS['setup_info']['expressoMail']['currentver'];
    }

	$test[] = '2.5.1';
	function expressoMail_upgrade2_5_1()
	{
		$GLOBALS['setup_info']['expressoMail']['currentver'] = '2.5.1.1';
		$GLOBALS['phpgw_setup']->oProc->query(
'DO $$
	DECLARE
		r record;
		c record;
		v varchar[];
		vrepl varchar[] := array[
			[\'expressoMail1_2\',\'expressoMail\'],
			[\'expressomail1_2\',\'expressomail\']
		];
	BEGIN
		FOR r IN
			SELECT tb.tablename AS tname, att.attname AS cname
			FROM
				pg_catalog.pg_tables AS tb,
				pg_catalog.pg_type AS typ,
				pg_catalog.pg_attribute AS att,
				pg_catalog.pg_type AS typatt
			WHERE tb.tablename = typ.typname
			 AND att.attrelid = typ.typrelid
			 AND tb.schemaname = \'public\'
			 AND att.atttypid = typatt.oid
			 AND att.attname NOT IN (\'cmin\', \'cmax\', \'ctid\', \'oid\', \'tableoid\', \'xmin\', \'xmax\')
			 AND typatt.typcategory = \'S\'
		LOOP
			FOREACH v SLICE 1 IN ARRAY vrepl
			LOOP
				BEGIN
					EXECUTE \'UPDATE \'||r.tname||\' SET \'||r.cname||\' = regexp_replace(\'||r.cname||\',\'||quote_literal(v[1])||\',\'||quote_literal(v[2])||\',\'||quote_literal(\'g\')||\') WHERE \'||r.cname||\' like \'||quote_literal(\'%\'||v[1]||\'%\');
				EXCEPTION WHEN unique_violation THEN
					FOR c IN EXECUTE \'SELECT oid FROM \'||r.tname||\' WHERE \'||r.cname||\' like \'||quote_literal(\'%\'||v[1]||\'%\')||\' LIMIT 1\' 
					LOOP
						BEGIN
							EXECUTE \'UPDATE \'||r.tname||\' SET \'||r.cname||\' = regexp_replace(\'||r.cname||\',\'||quote_literal(v[1])||\',\'||quote_literal(v[2])||\',\'||quote_literal(\'g\')||\') WHERE  oid = \'||quote_literal(c.oid);
						EXCEPTION WHEN unique_violation THEN
							EXECUTE \'DELETE FROM \'||r.tname||\' WHERE oid = \'||quote_literal(c.oid);
						END;
					END LOOP;
				END;
			END LOOP;
		END LOOP;
	END;
$$;'
		);
		return $GLOBALS['setup_info']['expressoMail']['currentver'];
	}
	$test[] = '2.5.1.1';
	function expressoMail_upgrade2_5_1_1() {
		$GLOBALS['setup_info']['expressoMail']['currentver'] = '2.5.2';
		return $GLOBALS['setup_info']['expressoMail']['currentver'];
	}
