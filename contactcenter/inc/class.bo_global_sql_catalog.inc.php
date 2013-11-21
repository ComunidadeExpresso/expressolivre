<?php
  /***************************************************************************\
  * eGroupWare - Contacts Center                                              *
  * http://www.egroupware.org                                                 *
  * Written by:                                                               *
  *  - Raphael Derosso Pereira <raphaelpereira@users.sourceforge.net>         *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/

	/*
		This class is responsible for manipulating the global SQL Contact Manager
	*/

	class bo_global_sql_manager 
	{
		// Attributes
		// Associations
	   /**
		*    XXX
		*    @accociation bo_ldap_manager to ldap_manager
		*    @access private
		*/
		#var $ldap_manager;
	
		// Operations
	   /**
		*    XXX
		*    
		*    @access public 
		*/
		function global_contact_manager (  ){
		}
	
	   /**
		*    XXX
		*    
		*    @access public 
		*    @returns array
		*    @param string $root XXX
		*/
		function get_global_tree ( $root ){
		}
	
	   /**
		*    XXX
		*    
		*    @access public 
		*    @returns string
		*/
		function get_actual_brach (  ){
		}
	
	   /**
		*    XXX
		*    
		*    @access public 
		*    @returns bool
		*    @param string $branch XXX
		*/
		function set_actual_branch ( $branch ){
		}
	
	}
?>