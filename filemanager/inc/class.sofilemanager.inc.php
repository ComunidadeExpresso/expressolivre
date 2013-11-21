<?php
		/*************************************************************************** 
		* Expresso Livre                                                           * 
		* http://www.expressolivre.org                                             * 
		* --------------------------------------------                             * 
		*  This program is free software; you can redistribute it and/or modify it * 
		*  under the terms of the GNU General Public License as published by the   * 
		*  Free Software Foundation; either version 2 of the License, or (at your  * 
		*  option) any later version.                                              * 
		\**************************************************************************/ 
		
	class sofilemanager
	{
		var $db;

		function sofilemanager()
		{
			$this->db = $GLOBALS['phpgw']->db;
		}

		/* Any initializations that need to be done */
		function db_init ()
		{
			$this->db->Auto_Free = 0;
		}

		/* General SQL query */
		function db_query ($query)
		{

			return $this->db->query ($query);
		}

		/* Fetch next array for $query_id */
		function db_fetch_array ($query_id)
		{

			//	$phpgw->db->Query_ID = $query_id;
			$this->db->next_record ();
			return $this->db->Record;
		}

		/*
		General wrapper for all other db calls
		Calls in here are simply returned, so not all will work
		*/
		function db_call ($function, $query_id)
		{

			//	$phpgw->db->Query_ID = $query_id;
			return $this->db->$function ();
		}


	}
?>
