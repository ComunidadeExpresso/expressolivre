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

	require 'ThreadUtility.inc.php';

	/*
	 * This is the class that abstracts the details of process management
	 */

	class Thread {
		var $pref ;
		var $pipes;
		var $pid;
		var $stdout;
		//var $timeout;

		function Thread() {
			$this->pref = 0;
			$this->stdout = "";
			$this->pipes = (array)NULL;
			//$this->timeout = 30000;
		}

		function Create ($url, $env) {
			$t = new Thread;
			$descriptor = array (0 => array ("pipe", "r"), 1 => array ("pipe", "w"), 2 => array ("pipe", "w"));
			$t->pref = proc_open ("php -q ". PHPGW_SERVER_ROOT . "/contactcenter/inc/" . $url, $descriptor, $t->pipes, NULL, $env);
			stream_set_blocking ($t->pipes[1], 0);
			stream_set_blocking ($t->pipes[2], 0);
			//usleep ($this->timeout);
			return $t;
		}

		function isActive () {
			$status = proc_get_status($this->pref);
			return $status['running'];
		}

		function close () {
			$r = proc_terminate($this->pref);
			$this->pref = NULL;
			return $r;
		}

		function tell ($thought, $params = NULL) {
		    fwrite ($this->pipes[0], $thought . "\n");
			if (is_array ($params)) {
				foreach ($params as $param) {
					fwrite ($this->pipes[0], $param . "\n");
				}
			}
		}

		function readResponse()
		{
			$response = NULL;
			$read = array($this->pipes[1]);
			$write = NULL;
			$exception = NULL;

			if (stream_select($read, $write, $exception, 0) > 0){
				$response = $this->listen();
				return processresponse($response);
			}
			else
			{
				return $response;
			}
		}

		function listen () {
			$buffer = $this->stdout;
			$this->stdout = "";
			while ($r = fgets ($this->pipes[1], 1024)) {
				$buffer .= $r;
			}
			return $buffer;
		}

		function getError () {
			$buffer = "";
			while ($r = fgets ($this->pipes[2], 1024)) {
				$buffer .= $r;
			}
			return $buffer;
		}
	}

?>
