<?php
	/**************************************************************************\
	* eGroupWare - administration                                              *
	* http://www.egroupware.org                                                *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/


	if (! $GLOBALS['phpgw']->acl->check('site_config_access',1,'admin'))
	{
		$file['Site Configuration']         = $GLOBALS['phpgw']->link('/index.php','menuaction=admin.uiconfig.index&amp;appname=admin');
	}

/* disabled it, til it does something useful
	if (! $GLOBALS['phpgw']->acl->check('peer_server_access',1,'admin'))
	{
		$file['Peer Servers']               = $GLOBALS['phpgw']->link('/index.php','menuaction=admin.uiserver.list_servers');
	}
*/	
	if (! $GLOBALS['phpgw']->acl->check('applications_access',1,'admin'))
	{
		$file['Applications']               = $GLOBALS['phpgw']->link('/index.php','menuaction=admin.uiapplications.get_list');
	}

	if (! $GLOBALS['phpgw']->acl->check('global_categories_access',1,'admin'))
	{
		$file['Global Categories']          = $GLOBALS['phpgw']->link('/index.php','menuaction=admin.uicategories.index');
	}

	if (!$GLOBALS['phpgw']->acl->check('mainscreen_message_access',1,'admin') || !$GLOBALS['phpgw']->acl->check('mainscreen_message_access',2,'admin'))
	{
		$file['Change Main Screen Message'] = $GLOBALS['phpgw']->link('/index.php','menuaction=admin.uimainscreen.index');
	}

	if (! $GLOBALS['phpgw']->acl->check('current_sessions_access',1,'admin'))
	{
		$file['View Sessions'] = $GLOBALS['phpgw']->link('/index.php','menuaction=admin.uicurrentsessions.list_sessions');
	}
	
	if (! $GLOBALS['phpgw']->acl->check('access_log_access',1,'admin'))
	{
		$file['View Access Log'] = $GLOBALS['phpgw']->link('/index.php','menuaction=admin.uiaccess_history.list_history');
	}

	if (! $GLOBALS['phpgw']->acl->check('error_log_access',1,'admin'))
	{
		$file['View Error Log']  = $GLOBALS['phpgw']->link('/index.php','menuaction=admin.uilog.list_log');
	}

	if (! $GLOBALS['phpgw']->acl->check('applications_access',16,'admin'))
	{
		$file['Find and Register all Application Hooks'] = $GLOBALS['phpgw']->link('/index.php','menuaction=admin.uiapplications.register_all_hooks');
	}

	if (! $GLOBALS['phpgw']->acl->check('info_access',1,'admin'))
	{
		$file['phpInfo']         = $GLOBALS['phpgw']->link('/admin/phpinfo.php');
	}

	if (! $GLOBALS['phpgw']->acl->check('asyncservice_access',1,'admin'))
	{
		if($GLOBALS['phpgw_info']['server']['use_assinar_criptografar']  ||  $GLOBALS['phpgw_info']['server']['certificado'])
		{
			$file['CASCRLS']    = $GLOBALS['phpgw']->link('../security/security_admin.php');
		}
	} 

        if (! $GLOBALS['phpgw']->acl->check('asyncservice_access',1,'admin'))
	{
            if(file_exists(dirname( __FILE__ ) . '/../../infodist/revisoes-svn.php'))
		$file['subversion']    = $GLOBALS['phpgw']->link('subversion.php');
	}
        
	if (! $GLOBALS['phpgw']->acl->check('site_config_access',1,'admin'))
	{
		$file['VoIP settings']         = $GLOBALS['phpgw']->link('/index.php','menuaction=admin.uivoip.edit_conf');
	}


	/* Do not modify below this line */
	display_section('admin',$file);
?>
