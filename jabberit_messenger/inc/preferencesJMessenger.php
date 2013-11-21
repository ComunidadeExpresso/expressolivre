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

class preferencesJMessenger
{
	public function setPreferences($pArgs)
	{
		require_once "preferences.php";
		
		$preferences = $pArgs['preferences1'];
		
		if(isset($pArgs['preferences2']))
			$preferences .= ";". $pArgs['preferences2'];
		
		if(isset($pArgs['preferences3']))
			$preferences .= ";". $pArgs['preferences3'];

		return setPreferences($preferences);
	}
}

?>