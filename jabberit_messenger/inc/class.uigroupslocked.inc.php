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

require_once "class.bogroupslocked.inc.php";

class uigroupslocked
{
	
	private $bo;
	
	public $public_functions = array(
		'add'  => True,
		'editGroups' => True,
	);
	
	function __construct()
	{
		$this->bo = new bogroupslocked();
		
		if(!isset($_SESSION['phpgw_info']['jabberit_messenger']['ldapManager']['host']))
		{
			$_SESSION['phpgw_info']['jabberit_messenger']['ldapManager']['host'] = $GLOBALS['phpgw_info']['server']['ldap_host'];
			$_SESSION['phpgw_info']['jabberit_messenger']['ldapManager']['acc'] = $GLOBALS['phpgw_info']['server']['ldap_root_dn'];
			$_SESSION['phpgw_info']['jabberit_messenger']['ldapManager']['pw'] = $GLOBALS['phpgw_info']['server']['ldap_root_pw'];
			$_SESSION['phpgw_info']['jabberit_messenger']['ldapManager']['dn'] = $GLOBALS['phpgw_info']['server']['ldap_context'];
		}
	}

	public final function editGroups()
	{
		if( !$GLOBALS['phpgw']->acl->check('run',1,'admin') )
		{
			$GLOBALS['phpgw']->redirect_link('/admin/index.php');
		}		
		
		$GLOBALS['phpgw_info']['flags']['app_header'] = lang('Admin') .' - ' . 'Restringir o Uso do Módulo por Grupo';	

		$_SESSION['phpgwinfo']['db_host'] = $GLOBALS['phpgw_info']['server']['db_host'];
    	$_SESSION['phpgwinfo']['db_port'] = $GLOBALS['phpgw_info']['server']['db_port'];
    	$_SESSION['phpgwinfo']['db_name'] = $GLOBALS['phpgw_info']['server']['db_name'];
    	$_SESSION['phpgwinfo']['db_user'] = $GLOBALS['phpgw_info']['server']['db_user'];
    	$_SESSION['phpgwinfo']['db_pass'] = $GLOBALS['phpgw_info']['server']['db_pass']; 
    	$_SESSION['phpgwinfo']['db_type'] = $GLOBALS['phpgw_info']['server']['db_type'];			

		$GLOBALS['phpgw']->common->phpgw_header();
		echo parse_navbar();

		$webserver_url = $GLOBALS['phpgw_info']['server']['webserver_url'];
		$webserver_url = ( !empty($webserver_url) ) ? $webserver_url : '/';

		if(strrpos($webserver_url,'/') === false || strrpos($webserver_url,'/') != (strlen($webserver_url)-1))
			$webserver_url .= '/';

		echo '<script type="text/javascript">var path_jabberit="'.$webserver_url .'"</script>';

		// Load Language;
		require_once "load_lang.php";
		require_once "class.ldap_im.inc.php";
		
		$ldap = new ldap_im();
		
		$ous = "<option value='-1'>-- ".lang('Select Organization')." --</option>";	
		
		if( ($LdapOus = $ldap->getOrganizationsLdap("localhost")) )
		{
			foreach( $LdapOus as $key => $val )
				$ous .= "<option value='".$key."'>".$val."</option>";
		}

		$groupsRestricts = "";

		if(isset($GLOBALS['phpgw_info']['server']['groups_locked_jabberit']))
		{
			$glocked = explode(';',$GLOBALS['phpgw_info']['server']['groups_locked_jabberit']);
			natcasesort($glocked);
	
			foreach( $glocked as $tmp ){
				$option = explode(":",$tmp);
				$groupsRestricts .= "<option value='".$tmp."'>".$option[0]."</option>";
			}
		}
		
		$GLOBALS['phpgw']->template->set_file(array('jabberit_messenger'=>'groupslocked.tpl'));
		$GLOBALS['phpgw']->template->set_block('jabberit_messenger','groups_locked');	
		$GLOBALS['phpgw']->template->set_var(array(
										'action_url' => $GLOBALS['phpgw']->link('/index.php','menuaction=jabberit_messenger.uigroupslocked.add'),
										'lang_add' => "Adicionar",
										'lang_cancel' => lang('Cancel'),										
										'lang_description' => "Os grupos cadastrados como restritos, só poderão adicionar contatos da sua própria organização",
										'lang_grupos_ldap' => "Grupos Ldap",
										'lang_grupos_restritos' => "Grupos Restritos",
										'lang_save' => lang('Save'),
										'lang_Jabberit_settings' => "Configurações",
										'lang_remove' => "Remover",
										'lang_organizations' => lang('Organizations'),										
										'groups_restricts' => trim($groupsRestricts),
										'ous_ldap' => $ous,
										'value_serverLdap' 	=> "localhost"										
										));
	
		$GLOBALS['phpgw']->template->pparse('out','groups_locked');
	}	
	
	public final function add()
	{
		if( !$GLOBALS['phpgw']->acl->check('run',1,'admin') )
		{
			$GLOBALS['phpgw']->redirect_link('/admin/index.php');
		}		
		
		if ( $_POST['save'] )
		{
			$this->bo->setGroupsLocked($_POST['groups_locked_jabberit']);
		}

		$GLOBALS['phpgw']->redirect_link('/index.php?menuaction=jabberit_messenger.uiconfig.configPermission');
	}
}

?>
