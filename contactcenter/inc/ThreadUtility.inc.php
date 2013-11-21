<?php
	/***********************************************************************************\
	* eGroupWare - Contacts Center                                              		*
	* http://www.egroupware.org                                                 		*
	* Written by:                                                               		*
	*  - Brian W. Bosh from	Multi-threading strategies in PHP at						*
	* http://www.alternateinterior.com/2007/05/multi-threading-strategies-in-php.html	*
	* Adapted by:																		*
	*  - Mário César Kolling <mario.kolling@serpro.gov.br>								*
	* ----------------------------------------------------------------------------------*
	*  This program is free software; you can redistribute it and/or modify it  		*
	*  under the terms of the GNU General Public License as published by the    		*
	*  Free Software Foundation; either version 2 of the License, or (at your   		*
	*  option) any later version.                                               		*
	\***********************************************************************************/

	/*
	 * Utility class used in the "Thread" solution
	 */

	function response ($status, $response) {
		echo $status . "\n";
		echo base64_encode(serialize($response)), "\n";
	}
	function processresponse ($string) {
		$parts = explode ("\n", $string);
		$status = $parts[0];
		$data = unserialize (base64_decode ($parts[1]));
		return array ("status" => $status, "data" => $data);
	}

?>