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


	include_once( "class.so_type.inc.php" );
	
	class so_relation_type extends so_type 
	{
		var $is_subordinated;
	
		/*!
			
			@function is_subordinated
			@abstract Returns true if this relation type is
				subordinated
			@author Raphael Derosso Pereira
			
		*/
		function is_subordinated (  )
		{
			return $this->is_subordinated['value'];
		}
	
		/*!
			
			@function set_subordinated
			@abstract Sets the trueness of the subordinated property
			@author Raphael Derosso Pereira
			
			@param boolean $subordinated
			
		*/
		function set_subordinated ( $subordinated )
		{
			$this->is_subordinated['value'] = $subordinated;
			$this->manage_fields($this->subordinated, 'changed');
		}
	
	}
?>
