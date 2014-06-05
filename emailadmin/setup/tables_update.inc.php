<?php
	/**************************************************************************\
	* EGroupWare - EMailadmin                                                  *
	* http://www.egroupware.org                                                *
	* http://www.phpgw.de                                                      *
	* Author: lkneschke@phpgw.de                                               *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	$test[] = '0.0.3';
	function emailadmin_upgrade0_0_3()
	{
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_emailadmin','smtpType', array('type' => 'int', 'precision' => 4));		

		$setup_info['emailadmin']['currentver'] = '0.0.4';
		return $setup_info['emailadmin']['currentver'];
	}

	$test[] = '0.0.4';
	function emailadmin_upgrade0_0_4()
	{
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_emailadmin','defaultDomain', array('type' => 'varchar', 'precision' => 100));		

		$setup_info['emailadmin']['currentver'] = '0.0.5';
		return $setup_info['emailadmin']['currentver'];
	}

	$test[] = '0.0.5';
	function emailadmin_upgrade0_0_5()
	{
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_emailadmin','organisationName', array('type' => 'varchar', 'precision' => 100));		
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_emailadmin','userDefinedAccounts', array('type' => 'varchar', 'precision' => 3));		

		$setup_info['emailadmin']['currentver'] = '0.0.6';
		return $setup_info['emailadmin']['currentver'];
	}
	


	$test[] = '0.0.6';
	function emailadmin_upgrade0_0_6()
	{
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_emailadmin','oldimapcclient',array(
			'type' => 'varchar',
			'precision' => '3'
		));


		$GLOBALS['setup_info']['emailadmin']['currentver'] = '0.0.007';
		return $GLOBALS['setup_info']['emailadmin']['currentver'];
	}


	$test[] = '0.0.007';
	function emailadmin_upgrade0_0_007()
	{
		$GLOBALS['phpgw_setup']->oProc->RenameColumn('phpgw_emailadmin','oldimapcclient','imapoldcclient');


		$GLOBALS['setup_info']['emailadmin']['currentver'] = '0.0.008';
		return $GLOBALS['setup_info']['emailadmin']['currentver'];
	}
	

	$test[] = '0.0.008';
	function emailadmin_upgrade0_0_008()
	{
		$GLOBALS['setup_info']['emailadmin']['currentver'] = '1.0.0';
		return $GLOBALS['setup_info']['emailadmin']['currentver'];
	}

   $test[] = '1.0.0';
   function emailadmin_upgrade1_0_0()
	{
   	$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_emailadmin','imapdefaulttrashfolder', array('type' => 'varchar', 'precision' => 20));
	   $GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_emailadmin','imapdefaultsentfolder', array('type' => 'varchar', 'precision' => 20));
	   $GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_emailadmin','imapdefaultdraftsfolder', array('type' => 'varchar', 'precision' => 20));
	   $GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_emailadmin','imapdefaultspamfolder', array('type' => 'varchar', 'precision' => 20));
      $GLOBALS['setup_info']['emailadmin']['currentver'] = '1.0.1';
      return $GLOBALS['setup_info']['emailadmin']['currentver'];
   }
   $test[] = '1.0.1';
   function emailadmin_upgrade1_0_1()
   	{
      $GLOBALS['setup_info']['emailadmin']['currentver'] = '2.0.000';
      return $GLOBALS['setup_info']['emailadmin']['currentver'];
   }
   $test[] = '2.0.000';
   function emailadmin_upgrade2_0_000()
	{      
      $GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_emailadmin','imapcreatespamfolder', array('type' => 'varchar', 'precision' => 3));
      $GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_emailadmin','imapcyrususerpostspam', array('type' => 'varchar', 'precision' => 30));
	  $GLOBALS['setup_info']['emailadmin']['currentver'] = '2.0.001';
      return $GLOBALS['setup_info']['emailadmin']['currentver'];
   }
   $test[] = '2.0.001';
   function emailadmin_upgrade2_0_001()
	{
	  $GLOBALS['setup_info']['emailadmin']['currentver'] = '2.1.000';      
      return $GLOBALS['setup_info']['emailadmin']['currentver'];
   }
   $test[] = '2.1.000';
   function emailadmin_upgrade2_1_000()
	{
      $GLOBALS['setup_info']['emailadmin']['currentver'] = '2.2.000';
      return $GLOBALS['setup_info']['emailadmin']['currentver'];
   }
   $test[] = '2.2.000';
   function emailadmin_upgrade2_2_000()
	{
      $GLOBALS['setup_info']['emailadmin']['currentver'] = '2.2.1';
      return $GLOBALS['setup_info']['emailadmin']['currentver'];
   }
   $test[] = '2.2.1';
   function emailadmin_upgrade2_2_1()
	{
      $GLOBALS['setup_info']['emailadmin']['currentver'] = '2.3.0';
      return $GLOBALS['setup_info']['emailadmin']['currentver'];
   }
   $test[] = '2.3.0';
   function emailadmin_upgrade2_3_0()
	{
      $GLOBALS['setup_info']['emailadmin']['currentver'] = '2.4.0';
      return $GLOBALS['setup_info']['emailadmin']['currentver'];
   }

   $test[] = '2.4.0';
   function emailadmin_upgrade2_4_0()
	{
      $GLOBALS['setup_info']['emailadmin']['currentver'] = '2.4.1';
      return $GLOBALS['setup_info']['emailadmin']['currentver'];
   }

    $test[] = '2.4.1';
   function emailadmin_upgrade2_4_1()
	{
      $GLOBALS['setup_info']['emailadmin']['currentver'] = '2.4.2';
      return $GLOBALS['setup_info']['emailadmin']['currentver'];
   }
    $test[] = '2.4.2';
    function emailadmin_upgrade2_4_2()
    {
            $GLOBALS['setup_info']['emailadmin']['currentver'] = '2.5.0';
            return $GLOBALS['setup_info']['emailadmin']['currentver'];
    }

    $test[] = '2.5.0';
    function emailadmin_upgrade2_5_0()
    {
        $GLOBALS['setup_info']['emailadmin']['currentver'] = '2.5.1';
        return $GLOBALS['setup_info']['emailadmin']['currentver'];
    }

    $test[] = '2.5.1';
    function emailadmin_upgrade2_5_1()
    {
        $GLOBALS['setup_info']['emailadmin']['currentver'] = '2.5.2';
        return $GLOBALS['setup_info']['emailadmin']['currentver'];
    }


