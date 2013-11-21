<?php
  /**************************************************************************\
  * eGroupWare - Setup                                                       *
  * http://www.egroupware.org                                                *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/
	function addSpecialColumn($table,$column, $attrs){
		$result = $GLOBALS['phpgw_setup']->db->metadata($table);
		if($result){
			foreach($result as $idx => $col){
				if($col['name'] == $column)
					return;
			}
		}
		$GLOBALS['phpgw_setup']->db->query("ALTER TABLE ".$table." ADD COLUMN ".$column." ".$attrs);
	}
/// Since Expresso 1.2 using Calendar 0.9.3
	$test[] = '0.9.3';
	function calendar_upgrade0_9_3()
	{
		$GLOBALS['setup_info']['calendar']['currentver'] = '2.0.000';
		return $GLOBALS['setup_info']['calendar']['currentver'];
	}
	$test[] = '2.0.000';
	function calendar_upgrade2_0_000()
	{
		$GLOBALS['setup_info']['calendar']['currentver'] = '2.0.001';
		return $GLOBALS['setup_info']['calendar']['currentver'];
	}
	$test[] = '2.0.001';
	function calendar_upgrade2_0_001()
	{
		$GLOBALS['setup_info']['calendar']['currentver'] = '2.0.002';
		return $GLOBALS['setup_info']['calendar']['currentver'];
	}
	$test[] = '2.0.002';
	function calendar_upgrade2_0_002()
	{
		$GLOBALS['setup_info']['calendar']['currentver'] = '2.0.003';
		return $GLOBALS['setup_info']['calendar']['currentver'];
	}
	$test[] = '2.0.003';
	function calendar_upgrade2_0_003()
	{
		$GLOBALS['setup_info']['calendar']['currentver'] = '2.0.004';
		return $GLOBALS['setup_info']['calendar']['currentver'];
	}	
	$test[] = '2.0.004';
	function calendar_upgrade2_0_004()
	{
		$GLOBALS['setup_info']['calendar']['currentver'] = '2.0.005';
		return $GLOBALS['setup_info']['calendar']['currentver'];
	}	
	$test[] = '2.0.005';
	function calendar_upgrade2_0_005()
	{
		$GLOBALS['setup_info']['calendar']['currentver'] = '2.0.006';
		return $GLOBALS['setup_info']['calendar']['currentver'];
	}	
	$test[] = '2.0.006';
	function calendar_upgrade2_0_006() {
		$GLOBALS['setup_info']['calendar']['currentver'] = '2.0.007';
		return $GLOBALS['setup_info']['calendar']['currentver'];
	}		
	$test[] = '2.0.007';
	function calendar_upgrade2_0_007()
	{
		$GLOBALS['setup_info']['calendar']['currentver'] = '2.0.008';
		return $GLOBALS['setup_info']['calendar']['currentver'];
	}
	$test[] = '2.0.008';
	function calendar_upgrade2_0_008()
	{
		$GLOBALS['setup_info']['calendar']['currentver'] = '2.0.009';
		return $GLOBALS['setup_info']['calendar']['currentver'];
	}
 	$test[] = '2.0.009';
 	function calendar_upgrade2_0_009()
 	{
    	$GLOBALS['setup_info']['calendar']['currentver'] = '2.1.000';
		return $GLOBALS['setup_info']['calendar']['currentver'];
	}	
	$test[] = '2.1.000';
	function calendar_upgrade2_1_000()
	{
		$GLOBALS['setup_info']['calendar']['currentver'] = '2.2.000';
		return $GLOBALS['setup_info']['calendar']['currentver'];
	}
	$test[] = '2.2.000';
	function calendar_upgrade2_2_000()
	{
		$GLOBALS['setup_info']['calendar']['currentver'] = '2.2.1';
		return $GLOBALS['setup_info']['calendar']['currentver'];
	}
	$test[] = '2.2.1';
	function calendar_upgrade2_2_1()
	{
		$GLOBALS['setup_info']['calendar']['currentver'] = '2.2.6';
		return $GLOBALS['setup_info']['calendar']['currentver'];
	}
	$test[] = '2.2.6';
	function calendar_upgrade2_2_6()
	{
		$GLOBALS['setup_info']['calendar']['currentver'] = '2.2.8';
		return $GLOBALS['setup_info']['calendar']['currentver'];
	}
	$test[] = '2.2.8';
	function calendar_upgrade2_2_8()
	{
		$GLOBALS['setup_info']['calendar']['currentver'] = '2.2.10';
		return $GLOBALS['setup_info']['calendar']['currentver'];
	}
	$test[] = '2.2.10';
	function calendar_upgrade2_2_10()
	{
	    $GLOBALS['phpgw_setup']->db->query("ALTER TABLE phpgw_cal ADD COLUMN notifications_owner INT NOT NULL default '0'");
		$GLOBALS['phpgw_setup']->db->query("ALTER TABLE phpgw_cal ADD COLUMN observations text");
	    $GLOBALS['phpgw_setup']->db->query("ALTER TABLE phpgw_cal ADD COLUMN alter_by varchar(160)");
	    $GLOBALS['phpgw_setup']->db->query("ALTER TABLE phpgw_cal ADD COLUMN attachment text");
	    $GLOBALS['phpgw_setup']->db->query("ALTER TABLE phpgw_cal ADD COLUMN organizer character varying(255);");
		$GLOBALS['phpgw_setup']->db->query("ALTER TABLE phpgw_cal ALTER title TYPE varchar(1024)");
		$GLOBALS['phpgw_setup']->db->query("ALTER TABLE phpgw_cal_repeats ALTER recur_exception TYPE varchar");
	    $GLOBALS['setup_info']['calendar']['currentver'] = '2.3.0';
	    return $GLOBALS['setup_info']['calendar']['currentver'];
	}
	$test[] = '2.3.0';
	function calendar_upgrade2_3_0()
	{
		$GLOBALS['setup_info']['calendar']['currentver'] = '2.4.0';
		return $GLOBALS['setup_info']['calendar']['currentver'];
	}

	$test[] = '2.4.0';
	function calendar_upgrade2_4_0()
	{
		$GLOBALS['setup_info']['calendar']['currentver'] = '2.4.1';
		return $GLOBALS['setup_info']['calendar']['currentver'];
	}

    $test[] = '2.4.1';
    function calendar_upgrade2_4_1()
    {
        $GLOBALS['setup_info']['calendar']['currentver'] = '2.4.2';
        return $GLOBALS['setup_info']['calendar']['currentver'];
    }
	$test[] = '2.4.2';
    	function calendar_upgrade2_4_2()
    	{
        	$GLOBALS['setup_info']['calendar']['currentver'] = '2.5.0';
        	return $GLOBALS['setup_info']['calendar']['currentver'];
    	}
  	$test[] = '2.5.0';
        function calendar_upgrade2_5_0()
        {
                $GLOBALS['setup_info']['calendar']['currentver'] = '2.5.1';
                return $GLOBALS['setup_info']['calendar']['currentver'];
        }


?>
