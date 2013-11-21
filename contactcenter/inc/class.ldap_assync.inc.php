<?php
	/***********************************************************************************\
	* eGroupWare - Contacts Center                                              		*
	* http://www.egroupware.org                                                 		*
	* Written by:                                                               		*
	*  - Mário César Kolling <mario.kolling@serpro.gov.br>								*
	* Based on:																			*
	* - Multi-threading strategies in PHP from 											*
	* http://www.alternateinterior.com/2007/05/multi-threading-strategies-in-php.html	*
	* ----------------------------------------------------------------------------------*
	*  This program is free software; you can redistribute it and/or modify it  		*
	*  under the terms of the GNU General Public License as published by the    		*
	*  Free Software Foundation; either version 2 of the License, or (at your   		*
	*  option) any later version.                                               		*
	\***********************************************************************************/

	require_once('class.ThreadInstance.inc.php');

	/*
	 * This is the Main Class that will be called in a new php process to test a bind in an ldap source
	 */

	class ldap_assync extends ThreadInstance
	{

		var $ldap;
		var $account;
		var $password;
		var $host;

		function ldap_assync($host, $account = false, $password = false)
		{
			$this->host = $host;
			$this->account = $account;
			$this->password = $password;
			$this->ldap = ldap_connect($this->host);

			$this->setup();
			$this->go();
		}

		function go ()
		{
			if (ldap_bind($this->ldap, $this->account, $this->password))
			{
				$this->response("ok", ldap_error($this->ldap));
			}
			else
			{
				$this->response("no", ldap_error($this->ldap));
			}

		}

	}

	/*
	 * Here is where we instantiate the new class. All the parameters are passed to the php process
	 * as environment variables
	 */
	$search_ldap = new ldap_assync($_ENV['host'], $_ENV['account'], $_ENV['password']);

?>
