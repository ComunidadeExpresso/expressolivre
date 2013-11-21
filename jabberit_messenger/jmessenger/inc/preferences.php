<?php

  /***************************************************************************\
  *  Expresso - Expresso Messenger                                            *
  *  	- Alexandre Correia / Rodrigo Souza							          *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/

	$GLOBALS['phpgw_info']['flags'] = array(
											'currentapp' => 'jabberit_messenger',
											'noheader'   => True, 
											'nonavbar'   => True
											);
	if( is_dir('../phpgwapi/inc') )
		require_once("../header.inc.php");
	else
		require_once("../../header.inc.php");			

	function setPreferences($pArgs)
	{
		if( $GLOBALS['phpgw_info']['user']['preferences']['jabberit_messenger']['preferences'] )
			$GLOBALS['phpgw']->preferences->change('jabberit_messenger','preferences',$pArgs);
		else
			$GLOBALS['phpgw']->preferences->add('jabberit_messenger','preferences',$pArgs);

		if ( $GLOBALS['phpgw']->preferences->save_repository() )
			return "true";
		else
			return "false";
	}	

?>