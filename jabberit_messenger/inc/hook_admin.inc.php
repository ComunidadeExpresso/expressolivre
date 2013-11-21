<?php
  /***************************************************************************\
  *  Expresso - Expresso Messenger                                            *
  *  	- Alexandre Correia / Rodrigo Souza							          *
  *  	- JETI - http://jeti-im.org/										  *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/

// Only Modify the $file and $title variables.....
	$title = 'jabberit_messenger';

	$file = array(
		'Site Configuration' => $GLOBALS['phpgw']->link('/index.php','menuaction=jabberit_messenger.uiconfig.configServer'),
		'Access Permissions' => $GLOBALS['phpgw']->link('/index.php','menuaction=jabberit_messenger.uiconfig.configPermission'),
		'Auditing'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=jabberit_messenger.uilogmessage.getLog'),	
	);
	

//Do not modify below this line
	display_section($appname,$title,$file);
	
?>
