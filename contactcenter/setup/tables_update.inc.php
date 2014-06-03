<?php
	/**************************************************************************\
	* phpGroupWare - Setup                                                     *
	* http://www.phpgroupware.org                                              *
	* --------------------------------------------                             *
	* This program is free software; you can redistribute it and/or modify it  *
	* under the terms of the GNU General Public License as published by the    *
	* Free Software Foundation; either version 2 of the License, or (at your   *
	* option) any later version.                                               *
	\**************************************************************************/	
	// Since Expresso 1.2 using ContactCenter 1.21
	$test[] = '1.21';
	function contactcenter_upgrade1_21() {
		$GLOBALS['setup_info']['contactcenter']['currentver'] = '2.0.000';
		// Bug fixing for type cast problem PGSQL version > 8.1. Replacing trigger function:
			$GLOBALS['phpgw_setup']->db->query("CREATE OR REPLACE function share_catalog_delete() returns trigger as '".
					"begin if old.acl_appname = ''contactcenter'' and old.acl_location!=''run'' then delete from ".
					"phpgw_cc_contact_rels where id_contact=old.acl_location::bigint and id_related=old.acl_account ".
					"and id_typeof_contact_relation=1; end if; return new; end;' language 'plpgsql'");
		return $GLOBALS['setup_info']['contactcenter']['currentver'];
	}
	$test[] = '2.0.000';
	function contactcenter_upgrade2_0_000() {
		$GLOBALS['setup_info']['contactcenter']['currentver'] = '2.0.001';
		return $GLOBALS['setup_info']['contactcenter']['currentver'];
	}
	$test[] = '2.0.001';
	function contactcenter_upgrade2_0_001() {
		$GLOBALS['setup_info']['contactcenter']['currentver'] = '2.0.002';
		return $GLOBALS['setup_info']['contactcenter']['currentver'];
	}		
	$test[] = '2.0.002';
	function contactcenter_upgrade2_0_002() {
		$GLOBALS['setup_info']['contactcenter']['currentver'] = '2.0.003';
		return $GLOBALS['setup_info']['contactcenter']['currentver'];
	}	
	$test[] = '2.0.003';
	function contactcenter_upgrade2_0_003() {
		$GLOBALS['setup_info']['contactcenter']['currentver'] = '2.0.004';
		return $GLOBALS['setup_info']['contactcenter']['currentver'];
	}
	$test[] = '2.0.004';
	function contactcenter_upgrade2_0_004() {
		$GLOBALS['setup_info']['contactcenter']['currentver'] = '2.1.000';
		return $GLOBALS['setup_info']['contactcenter']['currentver'];
	}	
	$test[] = '2.1.000';
	function contactcenter_upgrade2_1_000() {		
		$GLOBALS['phpgw_setup']->db->query("ALTER TABLE phpgw_cc_contact ADD COLUMN web_page character varying(100)");
 		$GLOBALS['phpgw_setup']->db->query("ALTER TABLE phpgw_cc_contact ADD COLUMN corporate_name character varying(100)");
		$GLOBALS['phpgw_setup']->db->query("ALTER TABLE phpgw_cc_contact ADD COLUMN job_title character varying(40)");
		$GLOBALS['phpgw_setup']->db->query("ALTER TABLE phpgw_cc_contact ADD COLUMN department character varying(30)");
		$GLOBALS['setup_info']['contactcenter']['currentver'] = '2.2.000';
		return $GLOBALS['setup_info']['contactcenter']['currentver'];
	}
        $test[] = '2.2.000';
	function contactcenter_upgrade2_2_000() {
		$GLOBALS['setup_info']['contactcenter']['currentver'] = '2.2.1';
		return $GLOBALS['setup_info']['contactcenter']['currentver'];
	}
	
	$test[] = '2.2.1'; 
 	function contactcenter_upgrade2_2_1() { 
 	    $GLOBALS['setup_info']['contactcenter']['currentver'] = '2.2.2'; 
 	    return $GLOBALS['setup_info']['contactcenter']['currentver']; 
 	}
	
 	$test[] = '2.2.2';
 	function contactcenter_upgrade2_2_2() {
 		$GLOBALS['setup_info']['contactcenter']['currentver'] = '2.2.3';
 		return $GLOBALS['setup_info']['contactcenter']['currentver'];
 	}
 	$test[] = '2.2.3';
 	function contactcenter_upgrade2_2_3() {
 		$GLOBALS['setup_info']['contactcenter']['currentver'] = '2.2.10';
 		return $GLOBALS['setup_info']['contactcenter']['currentver'];
 	} 	
 	$test[] = '2.2.10';
 	function contactcenter_upgrade2_2_10() {
 		$GLOBALS['setup_info']['contactcenter']['currentver'] = '2.3.0';
 		return $GLOBALS['setup_info']['contactcenter']['currentver'];
 	}
 	$test[] = '2.3.0';
 	function contactcenter_upgrade2_3_0() {
 		$GLOBALS['phpgw_setup']->db->query("ALTER TABLE phpgw_cc_contact ALTER COLUMN alias TYPE character varying(100);");
 		$GLOBALS['setup_info']['contactcenter']['currentver'] = '2.4.0';
 		return $GLOBALS['setup_info']['contactcenter']['currentver'];
 	}
	/*Atualizacao dos hooks do modulo*/
	$test[] = '2.4.0';
 	function contactcenter_upgrade2_4_0() {
 		$GLOBALS['setup_info']['contactcenter']['currentver'] = '2.4.1';
 		return $GLOBALS['setup_info']['contactcenter']['currentver'];
 	}

    	$test[] = '2.4.1';
    	function contactcenter_upgrade2_4_1() {
        	$GLOBALS['setup_info']['contactcenter']['currentver'] = '2.4.2';
        	return $GLOBALS['setup_info']['contactcenter']['currentver'];
    	}
	$test[] = '2.4.2';
    function contactcenter_upgrade2_4_2() {
        $GLOBALS['setup_info']['contactcenter']['currentver'] = '2.5.0';
        return $GLOBALS['setup_info']['contactcenter']['currentver'];
    }

    $test[] = '2.5.0';
    function contactcenter_upgrade2_5_0() {
        $GLOBALS['setup_info']['contactcenter']['currentver'] = '2.5.1';
        return $GLOBALS['setup_info']['contactcenter']['currentver'];
    }

    $test[] = '2.5.1';
    function contactcenter_upgrade2_5_1() {
        $GLOBALS['setup_info']['contactcenter']['currentver'] = '2.5.2';
        return $GLOBALS['setup_info']['contactcenter']['currentver'];
    }
