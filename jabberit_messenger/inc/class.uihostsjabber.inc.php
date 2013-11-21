<?php

  /***************************************************************************\
  *  Expresso - Expresso Messenger                                            *
  *  	- Alexandre Correia / Rodrigo Souza							          *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/

require_once "class.bohostsjabber.inc.php";

class uihostsjabber
{
	private $bo;
	private $ldap;

	public $public_functions = array(
		'backPage'  => True,
		'edit_conf' => True,
	);

	function __construct()
	{
		$this->bo = new bohostsjabber();
	}
	
	public final function edit_conf()
	{
		if( !$GLOBALS['phpgw']->acl->check('run',1,'admin') )
		{
			$GLOBALS['phpgw']->redirect_link('/admin/index.php');
		}		
		
		$GLOBALS['phpgw_info']['flags']['app_header'] = lang('Admin') .' - ' . 'Configurar Organizações e Servidores Jabber';	
		
		$_SESSION['phpgwinfo']['db_host'] = $GLOBALS['phpgw_info']['server']['db_host'];
    	$_SESSION['phpgwinfo']['db_port'] = $GLOBALS['phpgw_info']['server']['db_port'];
    	$_SESSION['phpgwinfo']['db_name'] = $GLOBALS['phpgw_info']['server']['db_name'];
    	$_SESSION['phpgwinfo']['db_user'] = $GLOBALS['phpgw_info']['server']['db_user'];
    	$_SESSION['phpgwinfo']['db_pass'] = $GLOBALS['phpgw_info']['server']['db_pass']; 
    	$_SESSION['phpgwinfo']['db_type'] = $GLOBALS['phpgw_info']['server']['db_type'];
			
		$webserver_url = $GLOBALS['phpgw_info']['server']['webserver_url'];
		$webserver_url = ( !empty($webserver_url) ) ? $webserver_url : '/';
		
		$GLOBALS['phpgw']->common->phpgw_header();
		echo parse_navbar();

		// Load Language;
		require_once "load_lang.php";

		$ConfHosts = $this->bo->getHostsJabber();
		
		if( $ConfHosts )
		{
			$ConfHosts = unserialize($this->bo->getHostsJabber());
		}
		
		$value_Organizations_Servers = "";
		
		if( is_array($ConfHosts) )	
		{
			foreach($ConfHosts as $itens)
			{
				$value_Organizations_Servers .= "<tr id='".$itens['org'].":".$itens['jabberName']."' style='width:40%' class='row_off'>";
				$value_Organizations_Servers .=	"<td>".$itens['org']."</td>";
				$value_Organizations_Servers .=	"<td>".$itens['jabberName']."</td>";
				$value_Organizations_Servers .=	"<td><a href='javascript:constructScript.editHostsJ(\"".$itens['org'].":".$itens['jabberName']."\");'>".lang('Edit')."</a></td>";
				$value_Organizations_Servers .=	"<td><a href='javascript:constructScript.removeHostsJ(\"".$itens['org'].":".$itens['jabberName']."\");'>".lang('Delete')."</a></td>";				
				$value_Organizations_Servers .= "</tr>";
			}
		}
		
		$GLOBALS['phpgw']->template->set_file(array('jabberit_messenger' => 'confServersJabber.tpl'));
		$GLOBALS['phpgw']->template->set_block('jabberit_messenger','confServersJabber');	
		$GLOBALS['phpgw']->template->set_var(array(
												    'action_url' => $GLOBALS['phpgw']->link('/index.php','menuaction=jabberit_messenger.uihostsjabber.backPage'),
													'lang_Add_Organizations_and_Servers_Jabber' => lang('Add organizations and servers jabber'),
													'lang_Back' => lang('Back'),
												    'lang_Delete' => lang('Delete'),
												    'lang_Edit' => lang('Edit'),
												    'lang_Example' => lang('Example'),
												    'lang_new' => lang('New'),
												    'lang_Organization' => lang('Organization'),
													'lang_save' => lang('Save'),
												    'lang_ServerJabber' => lang('Servers Jabber'),
													'lang_Registration_Organizations_and_Server_Jabber' => lang("Registration of Organizations and Servers Jabber"),													
													'value_Organizations_Servers' => $value_Organizations_Servers,
										));
	
		$GLOBALS['phpgw']->template->pparse('out','confServersJabber');
	}
	
	public final function backPage()
	{
		if( !$GLOBALS['phpgw']->acl->check('run',1,'admin') )
		{
			$GLOBALS['phpgw']->redirect_link('/admin/index.php');
		}		
		
		if ($_POST['cancel'])
		{
			$GLOBALS['phpgw']->redirect_link('/index.php?menuaction=jabberit_messenger.uiconfig.configServer');
		}

		$GLOBALS['phpgw']->redirect_link('/index.php?menuaction=jabberit_messenger.uiconfig.configServer');
	}
	
}

?>
