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

	class so_country 
	{

		function so_country ( $id = false )
		{
			if($id)
			{
				$obj = CreateObject('phpgwapi.country');
				$countries = $obj->country_array;

				if ($countries[$id])
				{
					$name = lang($countries[$id]);
					$this->country = $name{0} . strtolower(substr($name, 1));
				}
			}
		}
		

		/*********************************************************************\
		 *                   Methods to Get Information                      *
		\*********************************************************************/
		
		/*!
		
			@function get_country_name
			@abstract Returns the Name of the Country
			@author Raphael Derosso Pereira
		
		*/
		function get_country_name (  )
		{
			return $this->country;
		}
	}

?>
