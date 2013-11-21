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

require_once "class.bogroupsldap.inc.php";

class uigroupsldap
{
	private $bo;
	
	public $public_functions = array (
			'edit' => True,
			'editServersLdap' => True,
			'setAddGroups' => True
	);		
	
	function __construct()
	{
		$this->bo = new bogroupsldap();
		$GLOBALS['phpgw_info']['flags']['app_header'] = lang('Admin') .' - ' . 'Servidores Jabber';	
	}

	function edit()
	{
		if( !$GLOBALS['phpgw']->acl->check('run',1,'admin') )
		{
			$GLOBALS['phpgw']->redirect_link('/admin/index.php');
		}		
		
		$GLOBALS['phpgw']->common->phpgw_header();
		echo parse_navbar();

		$ldapInternal = $this->bo->getServerLdapInternal();

		$valueListsLdaps = "";
		$valueListsLdaps .= "<tr>";
		$valueListsLdaps .= "<td>&nbsp;".$ldapInternal."</td>";
		$valueListsLdaps .= "<td><a href='index.php?menuaction=jabberit_messenger.uigroupsldap.editServersLdap&host=".$ldapInternal."'>&nbsp;".lang('View')."</a></td>";
		$valueListsLdaps .= "</tr>";

		// Ldap Externos		
		$serversLdap = $this->bo->getServersLdapExternal();

		if(is_array($serversLdap))
		{
			foreach($serversLdap as $tmp)
			{
				$valueListsLdaps .= "<tr>";	
				$valueListsLdaps .= "<td>&nbsp;".$tmp['serverLdap']."</td>";	
				$valueListsLdaps .= "<td><a href='index.php?menuaction=jabberit_messenger.uigroupsldap.editServersLdap&host=".$tmp['serverLdap']."'>&nbsp;".lang('View')."</a></td>";
				$valueListsLdaps .= "</tr>";
			}	
		}
		
		$GLOBALS['phpgw']->template->set_file(array('jabberit_messenger'=>'groupsLdap.tpl'));
		$GLOBALS['phpgw']->template->set_block('jabberit_messenger','groups_ldap');	
		$GLOBALS['phpgw']->template->set_var(array(
												'action_url_back' => './index.php?menuaction=jabberit_messenger.uiconfig.configPermission',										
												'label_Back' => "Voltar",
												'lang_Edit' => lang('View'),
												'lang_Servers_ldap' => "Servidores Ldap",
												'value_lists_ldaps' => $valueListsLdaps,
											));

		$GLOBALS['phpgw']->template->pparse('out','groups_ldap');

	}	
	
	function editServersLdap()
	{
		if( !$GLOBALS['phpgw']->acl->check('run',1,'admin') )
		{
			$GLOBALS['phpgw']->redirect_link('/admin/index.php');
		}

		$GLOBALS['phpgw']->common->phpgw_header();
		echo parse_navbar();
		
		$webserver_url = $GLOBALS['phpgw_info']['server']['webserver_url'];
		$webserver_url = ( !empty($webserver_url) ) ? $webserver_url : '/';

		if(strrpos($webserver_url,'/') === false || strrpos($webserver_url,'/') != (strlen($webserver_url)-1))
			$webserver_url .= '/';

		echo '<script type="text/javascript">var path_jabberit="'.$webserver_url .'"</script>';
		
		$optionsOUS = "<option value='-1'>-- ".lang('Select Organization')." --</option>";	
		
		if( ($LdapOus = $this->bo->getOrganizationsLdap($_REQUEST['host'])) )
		{
			foreach( $LdapOus as $key => $val )
				$optionsOUS .= "<option value='".$key."'>".$val."</option>";
		}

		$optionsGroups = "";
		
		if( ($groupsLdaps = unserialize($this->bo->getGroupsSearch())))
		{
			if(count($groupsLdaps) > 0 )
			{
				foreach( $groupsLdaps as $key => $val )
				{
					if( trim($key) == trim($_REQUEST['host']) )
					{
						$groups = unserialize($val);

						if( $groups )
						{
							natcasesort($groups);
							
							foreach($groups as $tmp)
							{
								$grp = explode(":", $tmp);
								$optionsGroups .= "<option value='".$tmp."'>".$grp[0]."</option>";
							}
						}						
					}
				}
			}	
		}


		$GLOBALS['phpgw']->template->set_file(array('jabberit_messenger'=>'groupsLdap.tpl'));
		$GLOBALS['phpgw']->template->set_block('jabberit_messenger','edit_servers');	
		$GLOBALS['phpgw']->template->set_var(array(
												'action_url' => $GLOBALS['phpgw']->link('/index.php','menuaction=jabberit_messenger.uigroupsldap.setAddGroups'),										
												'label_Back'		=> "Voltar",
												'label_serverLdap'	=> lang("Server Ldap"),
												'lang_add'			=> lang("add"),
												'lang_cancel'		=> lang("Cancel"),
												'lang_description'	=> "Adicione somente os grupos que possuem o módulo JMessenger liberado.",
												'lang_groups_add'	=> lang("Groups Added"),
												'lang_groups_ldap'	=> lang("Groups Ldap"),
												'lang_organizations'	=> lang("Organizations"),
												'lang_remove'		=> lang("Remove"),
												'lang_save'			=> lang("Save"),
												'lang_Search_quick_for'	=> "Busca rápida por",
												'lang_settings'		=> lang("Settings"),
												'value_ous_ldap'	=> $optionsOUS,
												'value_groups_added'	=> $optionsGroups,												
												'value_serverLdap' 	=> $_REQUEST['host']
											));

		$GLOBALS['phpgw']->template->pparse('out','edit_servers');
		
	}
	
	function setAddGroups()
	{
		if( !$GLOBALS['phpgw']->acl->check('run',1,'admin') )
		{
			$GLOBALS['phpgw']->redirect_link('/admin/index.php');
		}
	
		if( $_POST['cancel'] )
		{
			$this->edit();
		}
		
		if( $_POST['save'])
		{
			$serverLdap = array( $_POST['name_serverLdap'] => serialize($_POST['groups_added_jabberit']));
			$this->bo->setAddGroups($serverLdap);
			$this->edit();
		}
	}
}

?>
