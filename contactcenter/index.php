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

	$_SESSION['phpgw_info']['user']['preferences']['contactcenter']['telephone_number'] = $GLOBALS['phpgw_info']['user']['telephonenumber'];

	$prefs = CreateObject('contactcenter.ui_preferences');
	$actual = $prefs->get_preferences();

	$_SESSION['phpgw_info']['user']['preferences']['contactcenter']['empNumShow'] 		= (isset($actual['empNum']) ? true : false);
	$_SESSION['phpgw_info']['user']['preferences']['contactcenter']['cellShow'] 		= (isset($actual['cell']) ? true : false);
	$_SESSION['phpgw_info']['user']['preferences']['contactcenter']['departmentShow'] 	= (isset($actual['department']) ? true : false);	
	$_SESSION['phpgw_info']['user']['preferences']['contactcenter']['personCardEmail'] 	= (isset($actual['personCardEmail']) && $actual['personCardEmail'] != '_NONE_'? $actual['personCardEmail'] : 1);
	$_SESSION['phpgw_info']['user']['preferences']['contactcenter']['personCardPhone']	= (isset($actual['personCardPhone']) && $actual['personCardPhone'] != '_NONE_'?  $actual['personCardPhone'] : 2);
	$_SESSION['phpgw_info']['user']['preferences']['contactcenter']['displayConnectorDefault']= (isset($actual['displayConnectorDefault']) ? true : false);
	$_SESSION['phpgw_info']['user']['preferences']['contactcenter']['displayConnector']	= (isset($actual['displayConnector']) ? true : false);
        
	//Enable/Disable VoIP Service -> Voip Server Config
	$_SESSION['phpgw_info']['user']['preferences']['contactcenter']['voip_enabled'] = false;	
	$voip_groups = array();
	if( isset($GLOBALS['phpgw_info']['server']['voip_groups']) )
	{
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
	echo '<script> var v_label = \'' . (isset($opts['cc_ldap_legend'])?$opts['cc_ldap_legend']:"") . '\'; var v_atrib = \'' . (isset($opts['cc_ldap_atrib'])?$opts['cc_ldap_atrib']:"") . '\';  v_min = \'' . (isset($opts['cc_ldap_min'])?$opts['cc_ldap_min']:"") . '\' </script>';
	$opts = null;
	$obj = CreateObject('contactcenter.ui_data');
	$obj->index();
       
   	echo '<script type="text/javascript" src="../prototype/plugins/jquery/jquery.min.js"></script>';
	$GLOBALS['phpgw']->common->phpgw_footer();
?>
