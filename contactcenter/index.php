<?php
  /***************************************************************************\
  * eGroupWare - Contacts Center                                              *
  * http://www.egroupware.org                                                 *
  * Written by:                                                               *
  *  - Raphael Derosso Pereira <raphaelpereira@users.sourceforge.net>         *
  *  - Jonas Goes <jqhcb@users.sourceforge.net>                               *
  *  sponsored by Thyamad - http://www.thyamad.com                            *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/

	$GLOBALS['phpgw_info'] = array();

	$GLOBALS['phpgw_info']['flags'] = array(
		'currentapp' => 'contactcenter',
		'noheader'   => true,
		//'nonavbar'   => true
	);
	include('../header.inc.php');

    echo "<script src='../" . $GLOBALS['phpgw_info']['flags']['currentapp'] . "/inc/load_lang.php'></script>";

	$_SESSION['phpgw_info']['user']['preferences']['contactcenter']['telephone_number'] = $GLOBALS['phpgw_info']['user']['telephonenumber'];

	$prefs = CreateObject('contactcenter.ui_preferences');
	$actual = $prefs->get_preferences();

	$_SESSION['phpgw_info']['user']['preferences']['contactcenter']['empNumShow'] = $actual['empNum'] ? true : false;
	$_SESSION['phpgw_info']['user']['preferences']['contactcenter']['cellShow'] = $actual['cell'] ? true : false;
	$_SESSION['phpgw_info']['user']['preferences']['contactcenter']['departmentShow'] = $actual['department'] ? true : false;	
	$_SESSION['phpgw_info']['user']['preferences']['contactcenter']['personCardEmail'] 	= $actual['personCardEmail'] && $actual['personCardEmail'] != '_NONE_'? $actual['personCardEmail'] : 1;
	$_SESSION['phpgw_info']['user']['preferences']['contactcenter']['personCardPhone']	= $actual['personCardPhone'] && $actual['personCardPhone'] != '_NONE_'?  $actual['personCardPhone'] : 2;
	$_SESSION['phpgw_info']['user']['preferences']['contactcenter']['displayConnectorDefault']= $actual['displayConnectorDefault'] ? true : false;
	$_SESSION['phpgw_info']['user']['preferences']['contactcenter']['displayConnector']= $actual['displayConnector'] ? true : false;
        
	//Enable/Disable VoIP Service -> Voip Server Config
	$_SESSION['phpgw_info']['user']['preferences']['contactcenter']['voip_enabled'] = false;	
	$voip_groups = array();
	if($GLOBALS['phpgw_info']['server']['voip_groups']) {
		foreach(explode(",",$GLOBALS['phpgw_info']['server']['voip_groups']) as $i => $voip_group){
			$a_voip = explode(";",$voip_group);			
			$voip_groups[] = $a_voip[1];
		}		
		foreach($GLOBALS['phpgw']->accounts->membership() as $idx => $group){			
			if(array_search($group['account_name'],$voip_groups) !== FALSE){		 
				$_SESSION['phpgw_info']['user']['preferences']['contactcenter']['voip_enabled'] = true;
				break;
			}
		}
	}

	// Verificar se há contatos compartilhados para o usuario logado
	$acl = CreateObject("phpgwapi.acl",$GLOBALS['phpgw_info']['user']['account_id']); 
	$grants = $acl->get_grants("contactcenter");
	foreach($grants as $id => $rights){
		if ($id != $GLOBALS['phpgw_info']['user']['account_id']) {
			$_SESSION['phpgw_info']['user']['preferences']['contactcenter']['shared_contacts'] = true;		
		}
	}
        $obj = CreateObject('phpgwapi.config','contactcenter');
        $opts = $obj->read_repository();
        echo '<script> var v_label = \'' . $opts['cc_ldap_legend'] . '\'; var v_atrib = \'' . $opts['cc_ldap_atrib'] . '\';  v_min = \'' . $opts['cc_ldap_min'] . '\' </script>';
        $opts = null;
	$obj = CreateObject('contactcenter.ui_data');
		$obj->index();
       
   	echo '<script type="text/javascript" src="../prototype/plugins/jquery/jquery.min.js"></script>';

	$GLOBALS['phpgw']->common->phpgw_footer();
?>
