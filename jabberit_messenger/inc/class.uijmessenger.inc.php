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

require_once "class.bojmessenger.inc.php";

class uijmessenger
{
	private $bo;
	
	public $public_functions = array(
		'backPage'		=> true,
		'getGroups'		=> true,
		'setGroups'		=> true,
	);
	
	function __construct()
	{
		$this->bo = new bojmessenger();
	}

	public final function getGroups()
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
		
		// Ldap Groups;
		$optionsOUS = "<option value='-1'>-- ".lang('Select Organization')." --</option>";	
		if( ($LdapOus = $this->bo->getOrganizationsLdap('localhost')) )
		{
			foreach($LdapOus as $key => $val )
				$optionsOUS .= "<option value='".$key."'>".$val."</option>";
		}
		
		// JMessenger Groups;		
		$groups = unserialize($this->bo->getGroupsJmessenger());

		if( $groups )
		{
			natcasesort($groups);
				
			foreach($groups as $tmp)
			{
				$grp = explode(":", $tmp);
				$optionsGroups .= "<option value='".$tmp."'>".$grp[0]."</option>";
			}
		}						

		$GLOBALS['phpgw']->template->set_file(array('jabberit_messenger'=>'jmessenger.tpl'));
		$GLOBALS['phpgw']->template->set_block('jabberit_messenger','edit_groups_jmessenger');	
		$GLOBALS['phpgw']->template->set_var(array(
												'action_url' => $GLOBALS['phpgw']->link('/index.php','menuaction=jabberit_messenger.uijmessenger.setGroups'),										
												'label_Back'			=> "Voltar",
												'lang_add'				=> lang("add"),
												'lang_cancel'			=> lang("Cancel"),
												'lang_description'		=> "Adicione somente os grupos para utilizar o JMESSENGER SEM JAVA.",
												'lang_groups_add'		=> lang("Groups Added"),
												'lang_groups_ldap'		=> lang("Groups Ldap"),
												'lang_organizations'	=> lang("Organizations"),
												'lang_remove'			=> lang("Remove"),
												'lang_save'				=> lang("Save"),
												'lang_Search_quick_for'	=> "Busca rápida por",
												'lang_settings'			=> lang("Settings"),
												'value_ous_ldap'		=> $optionsOUS,
												'value_groups_added'	=> $optionsGroups,												
												'value_serverLdap' 		=> 'localhost'
											));

		$GLOBALS['phpgw']->template->pparse('out','edit_groups_jmessenger');
	}

	public final function setGroups()
	{ 		
		if( !$GLOBALS['phpgw']->acl->check('run',1,'admin') )
		{
			$GLOBALS['phpgw']->redirect_link('/admin/index.php');
		}
		
		if( $_POST['cancel'] || $_POST['save'] )
		{
			if( $_POST['save'] )
			{
				$groups_added_jabberit = ( $_POST['groups_added_jabberit'] ) ? $_POST['groups_added_jabberit'] : "";				
				$this->bo->setAddGroupsJmessenger($groups_added_jabberit);
			}	
				
			$GLOBALS['phpgw']->redirect_link('/index.php?menuaction=jabberit_messenger.uiconfig.configPermission');
		}
	}
			
}

?>