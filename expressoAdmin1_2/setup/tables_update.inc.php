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
	$test[] = '1.2';
	function expressoAdmin1_2_upgrade1_2()
	{
		$GLOBALS['setup_info']['expressoAdmin1_2']['currentver'] = '1.21';
		return $GLOBALS['setup_info']['expressoAdmin1_2']['currentver'];
	}
	
	$test[] = '1.21';
	function expressoAdmin1_2_upgrade1_21()
	{
		$oProc = $GLOBALS['phpgw_setup']->oProc;

		$oProc->CreateTable(
			'phpgw_expressoadmin_samba', array(
				'fd' => array(
					'samba_domain_name' => array( 'type' => 'varchar', 'precision' => 50),
					'samba_domain_sid' => array( 'type' => 'varchar', 'precision' => 100)
				),
				'pk' => array('samba_domain_name'),
				'fk' => array(),
				'ix' => array(),
				'uc' => array()
			)
		);
		
		$GLOBALS['setup_info']['expressoAdmin1_2']['currentver'] = '1.240';
		return $GLOBALS['setup_info']['expressoAdmin1_2']['currentver'];
	}

	$test[] = '1.240';
	function expressoAdmin1_2_upgrade1_240()
	{
		$GLOBALS['setup_info']['expressoAdmin1_2']['currentver'] = '1.250';
		return $GLOBALS['setup_info']['expressoAdmin1_2']['currentver'];
	}
	
	$test[] = '1.250';
	function expressoAdmin1_2_upgrade1_250()
	{
		$GLOBALS['phpgw_setup']->oProc->DropColumn('phpgw_expressoadmin_log','','appinfo');
		$GLOBALS['phpgw_setup']->oProc->DropColumn('phpgw_expressoadmin_log','','groupinfo');
		$GLOBALS['phpgw_setup']->oProc->DropColumn('phpgw_expressoadmin_log','','msg');
		$GLOBALS['setup_info']['expressoAdmin1_2']['currentver'] = '1.261';
		return $GLOBALS['setup_info']['expressoAdmin1_2']['currentver'];
	}
	
	$test[] = '1.261';
	function expressoAdmin1_2_upgrade1_261()
	{
		$GLOBALS['setup_info']['expressoAdmin1_2']['currentver'] = '2.0.000';
		return $GLOBALS['setup_info']['expressoAdmin1_2']['currentver'];
	}
	$test[] = '2.0.000';
	function expressoAdmin1_2_upgrade2_0_000()
	{
		$GLOBALS['setup_info']['expressoAdmin1_2']['currentver'] = '2.0.001';
		return $GLOBALS['setup_info']['expressoAdmin1_2']['currentver'];
	}
	$test[] = '2.0.001';
	function expressoAdmin1_2_upgrade2_0_001()
	{
		$GLOBALS['setup_info']['expressoAdmin1_2']['currentver'] = '2.0.002';
		return $GLOBALS['setup_info']['expressoAdmin1_2']['currentver'];
	}	
	$test[] = '2.0.002';
	function expressoAdmin1_2_upgrade2_0_002()
	{
		$GLOBALS['setup_info']['expressoAdmin1_2']['currentver'] = '2.0.003';
		return $GLOBALS['setup_info']['expressoAdmin1_2']['currentver'];
	}	
	$test[] = '2.0.003';
	function expressoAdmin1_2_upgrade2_0_003()
	{
		$GLOBALS['setup_info']['expressoAdmin1_2']['currentver'] = '2.0.004';
		return $GLOBALS['setup_info']['expressoAdmin1_2']['currentver'];
	}		
	$test[] = '2.0.004';
	function expressoAdmin1_2_upgrade2_0_004()
	{
		$GLOBALS['setup_info']['expressoAdmin1_2']['currentver'] = '2.0.005';
		return $GLOBALS['setup_info']['expressoAdmin1_2']['currentver'];
	}
	$test[] = '2.0.005';
	function expressoAdmin1_2_upgrade2_0_005()
	{
		$GLOBALS['setup_info']['expressoAdmin1_2']['currentver'] = '2.0.006';
		return $GLOBALS['setup_info']['expressoAdmin1_2']['currentver'];
	}
	
	$test[] = '2.0.006';
	function expressoAdmin1_2_upgrade2_0_006()
	{
		$GLOBALS['phpgw_setup']->db->query("alter table phpgw_expressoadmin_log drop groupinfo");
		$GLOBALS['phpgw_setup']->db->query("alter table phpgw_expressoadmin_log drop appinfo");
		$GLOBALS['phpgw_setup']->db->query("alter table phpgw_expressoadmin_log drop msg");
		$GLOBALS['setup_info']['expressoAdmin1_2']['currentver'] = '2.1.000';
		return $GLOBALS['setup_info']['expressoAdmin1_2']['currentver'];
	}
	$test[] = '2.1.000';
	function expressoAdmin1_2_upgrade2_1_000()
	{
		$GLOBALS['setup_info']['expressoAdmin1_2']['currentver'] = '2.2.000';
		return $GLOBALS['setup_info']['expressoAdmin1_2']['currentver'];
	}
	$test[] = '2.2.000';
	function expressoAdmin1_2_upgrade2_2_000()
	{
		$GLOBALS['setup_info']['expressoAdmin1_2']['currentver'] = '2.2.1';
		return $GLOBALS['setup_info']['expressoAdmin1_2']['currentver'];
	}
	$test[] = '2.2.1';
	function expressoAdmin1_2_upgrade2_2_1()
	{
		$GLOBALS['setup_info']['expressoAdmin1_2']['currentver'] = '2.2.2';
		return $GLOBALS['setup_info']['expressoAdmin1_2']['currentver'];
	}
	$test[] = '2.2.2';
	function expressoAdmin1_2_upgrade2_2_2()
	{
		$GLOBALS['setup_info']['expressoAdmin1_2']['currentver'] = '2.2.3';
		return $GLOBALS['setup_info']['expressoAdmin1_2']['currentver'];
	}
	$test[] = '2.2.3';
	function expressoAdmin1_2_upgrade2_2_3()
	{
		$GLOBALS['setup_info']['expressoAdmin1_2']['currentver'] = '2.2.6';
		return $GLOBALS['setup_info']['expressoAdmin1_2']['currentver'];
	}
	$test[] = '2.2.6';
	function expressoAdmin1_2_upgrade2_2_6()
	{
		$GLOBALS['setup_info']['expressoAdmin1_2']['currentver'] = '2.2.8';
		return $GLOBALS['setup_info']['expressoAdmin1_2']['currentver'];
	}	
	$test[] = '2.2.8';
	function expressoAdmin1_2_upgrade2_2_8()
	{
		$GLOBALS['setup_info']['expressoAdmin1_2']['currentver'] = '2.3.0';
		return $GLOBALS['setup_info']['expressoAdmin1_2']['currentver'];
	}	
	$test[] = '2.3.0';
	function expressoAdmin1_2_upgrade2_3_0() {
                $GLOBALS['phpgw_setup']->db->query("

                    DROP SEQUENCE IF EXISTS seq_phpgw_expressoadmin_configuration;
                    CREATE SEQUENCE seq_phpgw_expressoadmin_configuration
                      INCREMENT 1
                      MINVALUE 1
                      MAXVALUE 9223372036854775807
                      START 93
                      CACHE 1;
                    ALTER TABLE seq_phpgw_expressoadmin_configuration OWNER TO ".$GLOBALS['phpgw_domain']['default']['db_user'].";


                    DROP TABLE IF EXISTS phpgw_expressoadmin_configuration;
                    CREATE TABLE phpgw_expressoadmin_configuration
                    (
                      id integer NOT NULL DEFAULT nextval(('seq_phpgw_expressoadmin_configuration'::text)::regclass),
                      email_user character varying(100),
                      configuration_type character varying(30) NOT NULL,
                      email_max_recipient integer DEFAULT 0,
                      email_user_type character varying(1),
                      email_quota integer,
                      email_recipient character varying(50),
                      CONSTRAINT phpgw_expressoadmin_configuration_pkey PRIMARY KEY (id)
                    )
                    WITH (
                      OIDS=TRUE
                    );

                    DROP INDEX IF EXISTS configuration_type_indice;
                    CREATE INDEX configuration_type_indice
                      ON phpgw_expressoadmin_configuration
                      USING btree
                      (configuration_type);
                    ALTER TABLE phpgw_expressoadmin_configuration CLUSTER ON configuration_type_indice;

                    DROP INDEX IF EXISTS email_user_indice;
                    CREATE INDEX email_user_indice
                      ON phpgw_expressoadmin_configuration
                      USING btree
                      (email_user);
                    ALTER TABLE phpgw_expressoadmin_configuration CLUSTER ON email_user_indice;

                    DROP TABLE IF EXISTS phpgw_expressoadmin_acls;
                    CREATE TABLE phpgw_expressoadmin_acls
                    (
                      manager_lid character varying(50) NOT NULL,
                      context character varying(255) NOT NULL,
                      acl_name character varying(255) NOT NULL
                    )
                    WITH (
                      OIDS=TRUE
                    );
                    ALTER TABLE phpgw_expressoadmin_acls OWNER TO ".$GLOBALS['phpgw_domain']['default']['db_user'].";


                    DROP INDEX IF EXISTS manager_lid_indice;
                    CREATE INDEX manager_lid_indice
                      ON phpgw_expressoadmin_acls
                      USING btree
                      (manager_lid);
                    ALTER TABLE phpgw_expressoadmin_acls CLUSTER ON manager_lid_indice;


                ");

                 $GLOBALS['phpgw_setup']->db->query("SELECT * FROM phpgw_expressoadmin");
                 $results = array();

                function safeBitCheck($number,$comparison)
	{
                    $binNumber = base_convert($number,10,2);
                    $binComparison = strrev(base_convert($comparison,10,2));
                            $str = strlen($binNumber);

                    if ( ($str <= strlen($binComparison)) && ($binComparison{$str-1}==="1") )
                            return '1';
                    else
                            return '0';
	}

                function make_array_acl($acl)
	{

			$array_acl_tmp = array();
			$tmp = array(		"acl_add_users",
							 	"acl_edit_users",
							 	"acl_delete_users",
							 	"acl_EMPTY1",
							 	"acl_add_groups",
							 	"acl_edit_groups",
							 	"acl_delete_groups",
							 	"acl_change_users_password",
							 	"acl_add_maillists",
							 	"acl_edit_maillists",
							 	"acl_delete_maillists",
							 	"acl_EMPTY2",
							 	"acl_create_sectors",
							 	"acl_edit_sectors",
							 	"acl_delete_sectors",
							 	"acl_edit_sambausers_attributes",
							 	"acl_view_global_sessions",
							 	"acl_view_logs",
							 	"acl_change_users_quote",
							 	"acl_set_user_default_password",
							 	"acl_create_computers",
							 	"acl_edit_computers",
							 	"acl_delete_computers",
							 	"acl_rename_users",
							 	"acl_edit_sambadomains",
							 	"acl_view_users",
							 	"acl_edit_email_groups",
							 	"acl_empty_user_inbox",
							 	"acl_manipulate_corporative_information",
							 	"acl_edit_users_picture",
							 	"acl_edit_scl_email_lists",
							 	"acl_edit_users_phonenumber",
							 	"acl_add_institutional_accounts",
							 	"acl_edit_institutional_accounts",
							 	"acl_remove_institutional_accounts",
                                                                "acl_add_shared_accounts",
                                                                "acl_edit_shared_accounts",
                                                                "acl_delete_shared_accounts",
                                                                "acl_edit_shared_accounts_acl",
                                                                "acl_edit_shared_accounts_quote",
                                                                "acl_empty_shared_accounts_inbox"
							 	);

			foreach ($tmp as $index => $right)
	{
				$bin = '';
				for ($i=0; $i<$index; ++$i)
				{
					$bin .= '0';
				}
				$bin = '1' . $bin;

				$array_acl[$right] = safeBitCheck(bindec($bin), $acl);
	}
			return $array_acl;
		}

                 while($GLOBALS['phpgw_setup']->db->next_record())
                     array_push($results, $GLOBALS['phpgw_setup']->db->row());

                 foreach ($results as $result)
                 {
                    $manager_info = make_array_acl($result['acl']);


                   foreach ($manager_info as $info => $value)
                    {
                        $acl  = strstr($info, 'acl_');

                        if ($acl !== false)
 	{ 

                                $fields = array(
                                                'manager_lid' => $result['manager_lid'],
                                                'context' =>    $result['context'],
                                                'acl_name' => $info,
                                               );


                                $GLOBALS['phpgw_setup']->db->insert('phpgw_expressoadmin_acls', $fields);
                           
                        }

                    }

                 }
		$GLOBALS['setup_info']['expressoAdmin1_2']['currentver'] = '2.4.0';
 	    return $GLOBALS['setup_info']['expressoAdmin1_2']['currentver']; 
 	}

 	$test[] = '2.4.0';
	function expressoAdmin1_2_upgrade2_4_0()
	{
		$GLOBALS['setup_info']['expressoAdmin1_2']['currentver'] = '2.4.1';
		return $GLOBALS['setup_info']['expressoAdmin1_2']['currentver'];
	}

    	$test[] = '2.4.1';
	function expressoAdmin1_2_upgrade2_4_1()
	{
		$GLOBALS['setup_info']['expressoAdmin1_2']['currentver'] = '2.4.2';
		return $GLOBALS['setup_info']['expressoAdmin1_2']['currentver'];
	}
	$test[] = '2.4.2';
    function expressoAdmin1_2_upgrade2_4_2()
    {
            $GLOBALS['setup_info']['expressoAdmin1_2']['currentver'] = '2.5.0';
            return $GLOBALS['setup_info']['expressoAdmin1_2']['currentver'];
    }

    $test[] = '2.5.0';
    function expressoAdmin1_2_upgrade2_5_0()
    {
        $GLOBALS['setup_info']['expressoAdmin1_2']['currentver'] = '2.5.1';
        return $GLOBALS['setup_info']['expressoAdmin1_2']['currentver'];
    }

    $test[] = '2.5.1';
    function expressoAdmin1_2_upgrade2_5_1()
    {
        $GLOBALS['setup_info']['expressoAdmin1_2']['currentver'] = '2.5.2';
        return $GLOBALS['setup_info']['expressoAdmin1_2']['currentver'];
    }
