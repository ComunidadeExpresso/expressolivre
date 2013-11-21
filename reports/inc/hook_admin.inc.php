<?php
	/************************************************************************************\
	* Expresso relatório                 										        *
	* by Elvio Rufino da Silva (elviosilva@yahoo.com.br, elviosilva@cepromat.mt.gov.br) *
	* ----------------------------------------------------------------------------------*
	*  This program is free software; you can redistribute it and/or modify it			*
	*  under the terms of the GNU General Public License as published by the			*
	*  Free Software Foundation; either version 2 of the License, or (at your			*
	*  option) any later version.														*
	\************************************************************************************/

	if (! $GLOBALS['phpgw']->acl->check('site_config_access',1,'admin'))
	{
		$file = Array(
			'Global Configuration' => $GLOBALS['phpgw']->link('/index.php','menuaction=reports.uireports.report_config_global')
		);
	}
	/* Do not modify below this line */
	display_section($appname,$file);

?>
