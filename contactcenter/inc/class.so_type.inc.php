<?php
  /***************************************************************************\
  * eGroupWare - Contacts Center                                              *
  * http://www.egroupware.org                                                 *
  *                                                                           *
  * Storage Object Classes                                                    *
  * Written by:                                                               *
  *  - Raphael Derosso Pereira <raphaelpereira@users.sourceforge.net>         *
  *  sponsored by Thyamad - http://www.thyamad.com                            *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/


	include_once("class.so_main.inc.php");
	
	class so_type extends so_main 
	{
		var $type_name;
		
		/*!
		
			@function get_type_name
			@abstract Returns the Type Name
			@author Raphael Derosso Pereira
		
		*/
		function get_type_name (  )
		{
			return $this->type_name['value'];
		}
	
		/*!
		*/
		function set_type_name ( $type )
		{
			$this->type_name['value'] = $name;
			$this->manage_fields($this->type_name, 'changed');
		}
	
	}
?>