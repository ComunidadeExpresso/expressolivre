<?php
	/**************************************************************************\
	* eGroupWare - Online User manual                                          *
	* http://www.eGroupWare.org                                                *
	* Written and (c) by RalfBecker@outdoor-training.de                        *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id: index.php,v 1.13 2004/04/13 08:19:10 ralfbecker Exp $ */

$GLOBALS['phpgw_info']['flags'] = array(
					'currentapp' => 'help',
					'nonavbar'   => true,
					'noheader'   => true,
					);

include('../header.inc.php');
ExecMethod('help.uihelp.viewHelp');
//	$GLOBALS['phpgw']->common->phpgw_footer();
?>
