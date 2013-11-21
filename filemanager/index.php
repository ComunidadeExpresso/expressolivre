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
	
		// FIXME add copyright header
		/*
		eGroupWare - http://www.egroupware.org
		written by Pim Snel <pim@lingewoud.nl>
		*/


	$phpgw_flags = Array(
		'currentapp'    =>      'filemanager',
		'noheader'      =>      True,
		'nonavbar'      =>      True,
		'noappheader'   =>      True,
		'noappfooter'   =>      True,
		'nofooter'      =>      True
	);

	$GLOBALS['phpgw_info']['flags'] = $phpgw_flags;

	include('../header.inc.php');
	$preferences = $GLOBALS['phpgw']->preferences->read();
	$_SESSION['phpgw_info']['user']['preferences']['filemanager'] = $preferences['filemanager'];

	Header('Location: '.$GLOBALS['phpgw']->link('/index.php','menuaction=filemanager.uifilemanager.index'));
	$GLOBALS['phpgw']->common->phpgw_exit();
?>
