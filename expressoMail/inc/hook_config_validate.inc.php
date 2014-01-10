<?php

$GLOBALS['phpgw_info']['server']['found_validation_hook'] = True;

function expressoMail_limit_labels($value){
	$db = '';
	$db = $db ? $db : $GLOBALS['phpgw']->db;	// this is to allow setup to set the db
	$db->query("SELECT max(slot) as slot from expressomail_label",__LINE__,__FILE__);
	while( $db->next_record() )
	{
		$slot = $db->f('slot');
	}
	if(isset($slot)){
		if($value < $slot){
			$GLOBALS['config_error'] = 'There are users with an quantity greater than of markers. Respect the minimum number indicated.';
		}
	}

}

?>