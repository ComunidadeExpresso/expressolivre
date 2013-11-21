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

	set_time_limit (0);
	require 'ThreadUtility.inc.php';

	/*
	 * A class that is extended by the new php process and deals with communication at the new php process side
	 */

	class ThreadInstance {
		var $stdin;
		var $stdout;

		function setup() {
			$this->stdin = fopen ("php://stdin", "r");
			$this->stderr = fopen ("php://stderr", "w");
			stream_set_blocking ($this->stdin, false);
		}
		function getCommand() {
				return $this->getLine(true);
		}
		function response ($status, $data) {
			response ($status, $data);
		}
		function getLine ($wait = false) {
			if ($wait) {
				$buffer = "";
				while (!strlen($buffer)) {
					$buffer .= fgets ($this->stdin, 1024);
				}
			} else {
				$buffer = fgets ($this->stdin, 1024);
			}
			return trim($buffer);
		}
		function debug ($text) {
			fwrite ($this->stderr, $text);
		}
	}

?>