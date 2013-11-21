<?php
  /***************************************************************************\
  * Expresso Livre - Contact Center                                           *
  * http://www.expressolivre.org                                              *
  * Written by:                                                               *
  *  - Nilton Emilio Buhrer Neto <niltonneto@celepar.pr.gov.br>			      *
  *  sponsored by CELEPAR - http://www.celepar.pr.gov.br					  *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/
 /*
	This hookfile is for generating an app-specific side menu used in the idots
	template set.

	$menu_title speaks for itself
	$file is the array with link to app functions

	display_sidebox can be called as much as you like
 */
	if ($GLOBALS['phpgw_info']['user']['apps']['preferences'])	{
		$menu_title = lang('Preferences');
		$file = Array(
			'Grant Access'=>$GLOBALS['phpgw']->link('/index.php','menuaction=preferences.uiaclprefs.index&acl_app=contactcenter')
		);
		display_sidebox($appname,$menu_title,$file);
	}
?>
