<?php
	/**************************************************************************\
	* phpGroupWare - Setup                                                     *
	* http://www.phpgroupware.org                                              *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	// Since Expresso 1.2 using API EgroupWare 1.0.0.007 
	$test[] = '1.0.0.007';
	function phpgwapi_upgrade1_0_0_007()
	{

		global $setup_info,$phpgw_setup;

		$phpgw_setup->oProc->AddColumn('phpgw_access_log','browser', array ('type' => 'varchar', 'precision' => 200));
		$GLOBALS['setup_info']['phpgwapi']['currentver'] = '1.0.0.008';
		return $GLOBALS['setup_info']['phpgwapi']['currentver'];
	}
	
	$test[] = '1.0.0.008';
	function phpgwapi_upgrade1_0_0_008()
	{
		$GLOBALS['setup_info']['phpgwapi']['currentver'] = '2.0.0.pre-alpha';
		return $GLOBALS['setup_info']['phpgwapi']['currentver'];
	}

	$test[] = '2.0.0.pre-alpha';
	function phpgwapi_upgrade2_0_0_prealpha()
	{
		$GLOBALS['setup_info']['phpgwapi']['currentver'] = '2.0.000';
		return $GLOBALS['setup_info']['phpgwapi']['currentver'];
	}
	$test[] = '2.0.000';
	function phpgwapi_upgrade2_0_000()
	{
		$GLOBALS['setup_info']['phpgwapi']['currentver'] = '2.0.001';
		return $GLOBALS['setup_info']['phpgwapi']['currentver'];
	}
	$test[] = '2.0.001';
	function phpgwapi_upgrade2_0_001()
	{
		$GLOBALS['setup_info']['phpgwapi']['currentver'] = '2.0.002';
		return $GLOBALS['setup_info']['phpgwapi']['currentver'];
	}		
	$test[] = '2.0.002';
	function phpgwapi_upgrade2_0_002()
	{
		$GLOBALS['setup_info']['phpgwapi']['currentver'] = '2.0.003';
		return $GLOBALS['setup_info']['phpgwapi']['currentver'];
	}
	$test[] = '2.0.003';
	function phpgwapi_upgrade2_0_003()
	{
		$GLOBALS['setup_info']['phpgwapi']['currentver'] = '2.0.004';
		return $GLOBALS['setup_info']['phpgwapi']['currentver'];
	}
	$test[] = '2.0.004';
	function phpgwapi_upgrade2_0_004()
	{
		$GLOBALS['setup_info']['phpgwapi']['currentver'] = '2.0.005';
		return $GLOBALS['setup_info']['phpgwapi']['currentver'];
	}
	$test[] = '2.0.005';
	function phpgwapi_upgrade2_0_005()
	{
		$GLOBALS['setup_info']['phpgwapi']['currentver'] = '2.0.006';
		return $GLOBALS['setup_info']['phpgwapi']['currentver'];
	}			
	$test[] = '2.0.006';
	function phpgwapi_upgrade2_0_006()
	{
		$GLOBALS['setup_info']['phpgwapi']['currentver'] = '2.0.007';
		return $GLOBALS['setup_info']['phpgwapi']['currentver'];
	}	
	$test[] = '2.0.007';
	function phpgwapi_upgrade2_0_007()
	{
		$GLOBALS['setup_info']['phpgwapi']['currentver'] = '2.0.008';
		return $GLOBALS['setup_info']['phpgwapi']['currentver'];
	}	
	$test[] = '2.0.008';
	function phpgwapi_upgrade2_0_008()
	{
		$GLOBALS['setup_info']['phpgwapi']['currentver'] = '2.0.009';
		return $GLOBALS['setup_info']['phpgwapi']['currentver'];
	}
	$test[] = '2.0.009';
	function phpgwapi_upgrade2_0_009()
	{
		$GLOBALS['setup_info']['phpgwapi']['currentver'] = '2.0.010';
		return $GLOBALS['setup_info']['phpgwapi']['currentver'];
	}	
	$test[] = '2.0.010';
	function phpgwapi_upgrade2_0_010()
	{
		$GLOBALS['setup_info']['phpgwapi']['currentver'] = '2.1.000';
		return $GLOBALS['setup_info']['phpgwapi']['currentver'];
	}	
	$test[] = '2.1.000';
	function phpgwapi_upgrade2_1_000()
	{
		$GLOBALS['setup_info']['phpgwapi']['currentver'] = '2.2.000';
		return $GLOBALS['setup_info']['phpgwapi']['currentver'];
	}
	$test[] = '2.2.000';
	function phpgwapi_upgrade2_2_000()
	{
		$GLOBALS['setup_info']['phpgwapi']['currentver'] = '2.2.1';
		return $GLOBALS['setup_info']['phpgwapi']['currentver'];
	}
	$test[] = '2.2.1'; 
	function phpgwapi_upgrade2_2_1()
	{
		$GLOBALS['setup_info']['phpgwapi']['currentver'] = '2.2.2';
		return $GLOBALS['setup_info']['phpgwapi']['currentver'];
	}
	$test[] = '2.2.2';
	function phpgwapi_upgrade2_2_2()
	{
		$GLOBALS['setup_info']['phpgwapi']['currentver'] = '2.2.3';
		return $GLOBALS['setup_info']['phpgwapi']['currentver'];
	}
	$test[] = '2.2.3';
	function phpgwapi_upgrade2_2_3()
	{
		$GLOBALS['setup_info']['phpgwapi']['currentver'] = '2.2.4';
		return $GLOBALS['setup_info']['phpgwapi']['currentver'];
	}
	$test[] = '2.2.4';
	function phpgwapi_upgrade2_2_4()
	{
		$GLOBALS['setup_info']['phpgwapi']['currentver'] = '2.2.6';
		return $GLOBALS['setup_info']['phpgwapi']['currentver'];
	}
	$test[] = '2.2.6';
	function phpgwapi_upgrade2_2_6()
	{
		$GLOBALS['setup_info']['phpgwapi']['currentver'] = '2.2.8';
		return $GLOBALS['setup_info']['phpgwapi']['currentver'];
	}
	$test[] = '2.2.8';
	function phpgwapi_upgrade2_2_8()
	{
		$GLOBALS['setup_info']['phpgwapi']['currentver'] = '2.2.10';
		return $GLOBALS['setup_info']['phpgwapi']['currentver'];
	}	
	$test[] = '2.2.10';
	function phpgwapi_upgrade2_2_10()
	{
		$GLOBALS['setup_info']['phpgwapi']['currentver'] = '2.3.0';
		return $GLOBALS['setup_info']['phpgwapi']['currentver'];
	}	
	$test[] = '2.3.0';
	function phpgwapi_upgrade2_3_0()
	{
		$GLOBALS['setup_info']['phpgwapi']['currentver'] = '2.4.0';
		return $GLOBALS['setup_info']['phpgwapi']['currentver'];
	}
	$test[] = '2.4.0';
	function phpgwapi_upgrade2_4_0()
	{
		$GLOBALS['setup_info']['phpgwapi']['currentver'] = '2.4.1';
		return $GLOBALS['setup_info']['phpgwapi']['currentver'];
	}

    	$test[] = '2.4.1';
	function phpgwapi_upgrade2_4_1()
	{
		$GLOBALS['setup_info']['phpgwapi']['currentver'] = '2.4.2';
		return $GLOBALS['setup_info']['phpgwapi']['currentver'];
	}
    $test[] = '2.4.2';
    function phpgwapi_upgrade2_4_2()
    {
        $GLOBALS['setup_info']['phpgwapi']['currentver'] = '2.5.0';
        return $GLOBALS['setup_info']['phpgwapi']['currentver'];
    }

    $test[] = '2.5.0';
	function phpgwapi_upgrade2_5_0()
	{
        $GLOBALS['setup_info']['phpgwapi']['currentver'] = '2.5.1';
        global $setup_info,$phpgw_setup;
        $phpgw_setup->oProc->query("ALTER TABLE phpgw_access_log ALTER COLUMN ip TYPE character varying(255) ");

        return $GLOBALS['setup_info']['phpgwapi']['currentver'];
	}

    $test[] = '2.5.1';
    function phpgwapi_upgrade2_5_1()
    {
        global $phpgw_setup;

        $phpgw_setup->oProc->query( 'select * from information_schema.tables where table_name= \'phpgw_vfs\'');
        if( !$phpgw_setup->oProc->next_record() )
        {
            $phpgw_setup->oProc->CreateTable('phpgw_vfs', array(
                'fd' => array(
                    'file_id' => array('type' => 'auto','nullable' => False),
                    'owner_id' => array('type' => 'int','precision' => '4','nullable' => False),
                    'createdby_id' => array('type' => 'int','precision' => '4'),
                    'modifiedby_id' => array('type' => 'int','precision' => '4'),
                    'created' => array('type' => 'timestamp','nullable' => False,'default' => '1970-01-01'),
                    'modified' => array('type' => 'timestamp'),
                    'size' => array('type' => 'int','precision' => '4'),
                    'mime_type' => array('type' => 'varchar','precision' => '64'),
                    'deleteable' => array('type' => 'char','precision' => '1','default' => 'Y'),
                    'comment' => array('type' => 'varchar','precision' => '255'),
                    'app' => array('type' => 'varchar','precision' => '25'),
                    'directory' => array('type' => 'varchar','precision' => '255'),
                    'name' => array('type' => 'varchar','precision' => '128','nullable' => False),
                    'link_directory' => array('type' => 'varchar','precision' => '255'),
                    'link_name' => array('type' => 'varchar','precision' => '128'),
                    'version' => array('type' => 'varchar','precision' => '30','nullable' => False,'default' => '0.0.0.0'),
                    'content' => array('type' => 'longtext')
                )
            ));
        }

        $GLOBALS['setup_info']['phpgwapi']['currentver'] = '2.5.1.0';
        return $GLOBALS['setup_info']['phpgwapi']['currentver'];
    }

	$test[] = '2.5.1.0';
	function phpgwapi_upgrade2_5_1_0()
	{
		$GLOBALS['setup_info']['phpgwapi']['currentver'] = '2.5.1.1';
		
		// Fixing missing table phpgw_vfs_quota
		$GLOBALS['phpgw_setup']->oProc->query( 'SELECT tablename FROM pg_catalog.pg_tables WHERE tablename= \'phpgw_vfs_quota\'');
		if ( !$GLOBALS['phpgw_setup']->oProc->next_record() )
		{
			$GLOBALS['phpgw_setup']->oProc->CreateTable('phpgw_vfs_quota', array(
				'fd' => array(
					'directory'  => array( 'type' => 'varchar', 'precision' => '100', 'nullable' => false ),
					'quota_size' => array( 'type' => 'int', 'precision' => '4', 'nullable' => false ),
				),
				'pk' => array( 'directory' ),
				'fk' => array(),
				'ix' => array(),
				'uc' => array(),
			));
		}
		
		// Set in all tables OIDS = true 
		$GLOBALS['phpgw_setup']->oProc->query(
'DO $$
	DECLARE
		r record;
	BEGIN
		FOR r IN
			SELECT
				tb.tablename AS tname
			FROM
				pg_catalog.pg_tables AS tb,
				pg_catalog.pg_class AS cl
			WHERE tb.tablename = cl.relname
				AND tb.schemaname = \'public\'
				AND cl.relhasoids = false
		LOOP
			EXECUTE \'ALTER TABLE \'||r.tname||\' SET WITH OIDS\';
		END LOOP;
	END;
$$;'
		);
		
		// Rename sequences to Postgres serial format
		$GLOBALS['phpgw_setup']->oProc->query(
'DO $$
	DECLARE
		r record;
	BEGIN
		FOR r IN
			SELECT
				tp.typname AS tname,
				att.attname AS cname,
				cl.relname AS sname
			FROM
				pg_class AS cl,
				pg_attrdef AS def,
				pg_attribute AS att,
				pg_type AS tp
			WHERE cl.relkind=\'S\'
				AND cl.relname LIKE \'seq_%\'
				AND def.adsrc LIKE \'%\'||quote_literal(cl.relname)||\'%\'
				AND def.adrelid = att.attrelid
				AND def.adnum = att.attnum
				AND def.adrelid = tp.typrelid
		LOOP
			EXECUTE \'ALTER SEQUENCE \'||r.sname||\' RENAME TO \'||r.tname||\'_\'||r.cname||\'_seq\';
			EXECUTE \'ALTER TABLE \'||r.tname||\' ALTER COLUMN \'||r.cname||\' SET DEFAULT nextval(\'||quote_literal(r.tname||\'_\'||r.cname||\'_seq\')||\'::regclass)\';
			EXECUTE \'ALTER SEQUENCE \'||r.tname||\'_\'||r.cname||\'_seq\'||\' OWNED BY \'||r.tname||\'.\'||r.cname;
		END LOOP;
	END;
$$;'
		);
		
		// Fixing sequences ownership
		$GLOBALS['phpgw_setup']->oProc->query(
'DO $$
	DECLARE
		r record;
	BEGIN
		FOR r IN
			SELECT
				tp.typname AS tname,
				att.attname AS cname,
				cl.relname AS sname
			FROM
				pg_class AS cl,
				pg_namespace AS ns,
				pg_attrdef AS def,
				pg_attribute AS att,
				pg_type AS tp
			WHERE cl.relkind=\'S\'
				AND cl.relnamespace = ns.oid
				AND NOT EXISTS (
					SELECT * FROM pg_depend WHERE objid = cl.oid AND deptype = \'a\'
				)
				AND def.adsrc LIKE \'%\'||quote_literal(cl.relname)||\'%\'
				AND def.adrelid = att.attrelid
				AND def.adnum = att.attnum
				AND def.adrelid = tp.typrelid
		LOOP
			EXECUTE \'ALTER SEQUENCE \'||r.tname||\'_\'||r.cname||\'_seq\'||\' OWNED BY \'||r.tname||\'.\'||r.cname;
		END LOOP;
	END;
$$;'
		);
		
		// Rename modules for update
		$GLOBALS['phpgw_setup']->oProc->query('UPDATE phpgw_applications SET app_name = \'expressoAdmin\' WHERE app_name = \'expressoAdmin1_2\';');
		$GLOBALS['phpgw_setup']->oProc->query('UPDATE phpgw_applications SET app_name = \'expressoMail\' WHERE app_name = \'expressoMail1_2\';');
		
		return $GLOBALS['setup_info']['phpgwapi']['currentver'];
	}

?>