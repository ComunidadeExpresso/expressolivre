<?php

  /***************************************************************************\
  *  Expresso - Expresso Messenger                                            *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/

if(!defined('PHPGW_INCLUDE_ROOT')){define('PHPGW_INCLUDE_ROOT', '../');}
if(!defined('PHPGW_API_INC')){define('PHPGW_API_INC','../phpgwapi/inc');}
require_once( PHPGW_API_INC . '/class.common.inc.php' );

class configMessenger
{
	private $ldap_context;
	private $ldap_host;
	private $ldap_root_dn;
	private $ldap_root_pw;
	private $repository;

	// Plubic functions for API Expresso
	public $public_functions = array( 'edit' => true , 'save' => true );

	function __construct()
	{
		$this->repository		= CreateObject('phpgwapi.config','phpgwapi');
		$this->ldap_host		= $GLOBALS['phpgw_info']['server']['ldap_host'];
		$this->ldap_root_dn		= $GLOBALS['phpgw_info']['server']['ldap_root_dn'];
		$this->ldap_root_pw		= $GLOBALS['phpgw_info']['server']['ldap_root_pw'];
		$this->ldap_context		= $GLOBALS['phpgw_info']['server']['ldap_context'];

		$GLOBALS['phpgw']->common->phpgw_header();
		$this->repository->read_repository();
	}	

	private function getOrganizations()
	{
		// Common functions
		$common = new common();

		// Ldap Connections
		$ldap 	= $common->ldapConnect( $this->ldap_host, $this->ldap_root_dn, $this->ldap_root_pw );

		if ( $ldap )	
		{
			$filter		= "objectClass=organizationalUnit";
			$justthese	= array("ou");
			$search		= ldap_list( $ldap, $this->ldap_context, $filter, $justthese );
			$entry		= ldap_get_entries( $ldap, $search );
		}

		if( $entry['count'] > 0 )
		{
			foreach($entry as $tmp)
			{
				if( $tmp['ou'][0] != "" )
				{
					$result_ou[] = $tmp['ou'][0];
				}
			}
		}
		else
		{
		    $result_ou[] = $this->ldap_context;
		}
		
		natcasesort( $result_ou );

		ldap_close( $ldap );

		return ( ( $result_ou ) ? $result_ou : '');
	}

	public final function edit()
	{
		if( !$GLOBALS['phpgw']->acl->check('run',1,'admin') )
		{
			$GLOBALS['phpgw']->redirect_link('/admin/index.php');
		}

		$webserver_url = $GLOBALS['phpgw_info']['server']['webserver_url'];
		$webserver_url = ( !empty($webserver_url) ) ? $webserver_url : '/';
		
		$GLOBALS['phpgw_info']['flags']['app_header'] = lang('Admin') .' - ' . lang('Expresso Messenger');
		echo parse_navbar();
		
		$path_expressoMail = $webserver_url . "/". $GLOBALS['phpgw_info']['flags']['currentapp']; 

		// Get Organizations Ldap
		$organizationsLdap = "<option value='-1'>-- ".lang('Select Organization')." --</option>";	
		
		if( ( $orgs = $this->getOrganizations() ) )
		{
			foreach( $orgs as $value )
				$organizationsLdap .= "<option value='".$value."'>".$value."</option>";
		}

		$current_config = array();

		// Get values
		if( $this->repository->config_data )
		{
			$current_config = $this->repository->config_data;
		}

		if( isset($current_config['groups_expresso_messenger']) )
		{
			//Groups Expresso Messenger
			natsort( $current_config['groups_expresso_messenger'] );

			foreach( $current_config['groups_expresso_messenger'] as $value )
			{
				$groups = explode(";", $value);
				$groups_expresso_messenger .= "<option value='".$value."'>".$groups[0]."</option>";
			}
		}

		$GLOBALS['phpgw']->template->set_file(array('expressoMessenger' => 'expressoMessenger.tpl'));
		$GLOBALS['phpgw']->template->set_block('expressoMessenger','bodyMessenger');	
		$GLOBALS['phpgw']->template->set_var(array(
													'action_url'			=> $GLOBALS['phpgw']->link('/index.php','menuaction=expressoMail.configMessenger.save'),
													'lang_add'				=> lang('Add'),
													'lang_cancel'			=> lang('Cancel'),
													'lang_Domain_Jabber'	=> lang('Domain Jabber'),
													'lang_Expresso_Messenger_settings'	=> lang('Expresso Messenger Settings'),
													'lang_enabled_groups'	=> lang('Enabled groups'),
													'lang_groups_ldap'		=> lang('Groups ldap'),
													'lang_organizations'	=> lang('Organizations'),
													'lang_remove'			=> lang('Remove'),
													'lang_save'				=> lang("Save"),
													'lang_URL_for_connecting_via_WebServer' => lang('URL for connecting via WebServer'),
													'lang_URL_for_direct_connection'		=> lang('URL for direct connection'),
													'path_expressoMail'						=> $path_expressoMail,
													'value_jabber_url_1'					=> (isset($current_config['jabber_url_1'])?$current_config['jabber_url_1']:""),
													'value_jabber_url_2'					=> (isset($current_config['jabber_url_2'])?$current_config['jabber_url_2']:""),
													'value_jabber_domain'					=> (isset($current_config['jabber_domain'])?$current_config['jabber_domain']:""),
													'value_organizationsLdap'				=> (isset($organizationsLdap)?$organizationsLdap:""),
													'value_groups_expresso_messenger'		=> (isset($groups_expresso_messenger)?$groups_expresso_messenger:"")
											));
		
		$GLOBALS['phpgw']->template->pparse('out','bodyMessenger');
	}

	public function save()
	{
		if( !$GLOBALS['phpgw']->acl->check('run',1,'admin') )
		{
			$GLOBALS['phpgw']->redirect_link('/admin/index.php');
		}		
		else
		{
			if ( isset($_POST['save'] ) )
			{
				$this->repository->config_data['jabber_domain']		= $_POST['jabber_domain'];
				$this->repository->config_data['jabber_url_1']		= $_POST['jabber_url_1'];
				$this->repository->config_data['jabber_url_2']		= $_POST['jabber_url_2'];
				$this->repository->config_data['groups_expresso_messenger'] = ( count($_POST['groups_expresso_messenger']) > 0 ) ? 
																					serialize($_POST['groups_expresso_messenger']) : "";			

				$this->repository->save_repository();
			}

			$GLOBALS['phpgw']->redirect_link('/admin/index.php');
		}		
	}
}

?>