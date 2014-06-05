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
        global $phpgw_setup;

        $phpgw_setup->oProc->query("CREATE INDEX idx_access_log_session_id ON phpgw_access_log USING btree (sessionid COLLATE pg_catalog.\"default\" )");
        $phpgw_setup->oProc->query("CREATE INDEX idx_phpgw_access_log_account_id ON phpgw_access_log USING btree (account_id )");
        $phpgw_setup->oProc->query("CREATE INDEX idx_phpgw_access_log_id_log_li ON phpgw_access_log USING btree  (account_id , li )");
        $phpgw_setup->oProc->query("CREATE INDEX idx_phpgw_access_log_lo_sessionid ON phpgw_access_log USING btree (lo , sessionid COLLATE pg_catalog.\"default\" )");

        $GLOBALS['setup_info']['phpgwapi']['currentver'] = '2.5.2';
        return $GLOBALS['setup_info']['phpgwapi']['currentver'];
    }
