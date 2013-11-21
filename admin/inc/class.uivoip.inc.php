<?php

 /**************************************************************************\
  * Expresso Livre - Voip - administration                                   *
  *															                 *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

class uivoip
{
	var $public_functions = array(
		'add'      => True,
		'edit_conf' => True,
	);

	var $bo;

	final function __construct()
	{
		if(!isset($_SESSION['admin']['ldap_host']))
		{
			$_SESSION['admin']['server']['ldap_host'] = $GLOBALS['phpgw_info']['server']['ldap_host'];
			$_SESSION['admin']['server']['ldap_root_dn'] = $GLOBALS['phpgw_info']['server']['ldap_root_dn'];
			$_SESSION['admin']['server']['ldap_host_pw'] = $GLOBALS['phpgw_info']['server']['ldap_root_pw'];
			$_SESSION['admin']['server']['ldap_context'] = $GLOBALS['phpgw_info']['server']['ldap_context'];
		}
		$this->bo = CreateObject('admin.bovoip');
	}

	final function edit_conf()
	{
		if($GLOBALS['phpgw']->acl->check('applications_access',1,'admin'))
		{
			$GLOBALS['phpgw']->redirect_link('/index.php');
		}		

		$GLOBALS['phpgw_info']['flags']['app_header'] = lang('Admin') .' - ' . lang('Configuration Service VoIP');

		if(!@is_object($GLOBALS['phpgw']->js))
		{
			$GLOBALS['phpgw']->js = CreateObject('phpgwapi.javascript');
		}

		$webserver_url = $GLOBALS['phpgw_info']['server']['webserver_url'];
		$webserver_url = ( !empty($webserver_url) ) ? $webserver_url : '/';

		if(strrpos($webserver_url,'/') === false || strrpos($webserver_url,'/') != (strlen($webserver_url)-1))
			$webserver_url .= '/';

		$js = array('connector','xtools','functions');
		
		foreach( $js as $tmp )
			$GLOBALS['phpgw']->js->validate_file('voip',$tmp,'admin');
		
		$GLOBALS['phpgw']->common->phpgw_header();
		echo parse_navbar();
		echo '<script type="text/javascript">var path_adm="'.$webserver_url .'"</script>';

		$ous = "<option value='-1'>-- ".lang('Select Organization')." --</option>";	
		if( ($LdapOus = $this->bo->getOuLdap()) )
		{
			foreach($LdapOus as $tmp )
				$ous .= "<option value='".$tmp."'>".$tmp."</option>";
		}
		
		$groups_voip = $GLOBALS['phpgw_info']['server']['voip_groups']; 

		if( $groups_voip )
		{
			$gvoip = explode(',', $groups_voip);
			natcasesort($gvoip);
			
			foreach( $gvoip as $tmp ){
				$option = explode(";",$tmp);
				$gvoip .= "<option value='".$tmp."'>".$option[0]."</option>";
			}
		}

		$GLOBALS['phpgw']->template->set_file(array('voip' => 'voip.tpl'));
		$GLOBALS['phpgw']->template->set_block('voip','voip_page','voip_page');	
		$GLOBALS['phpgw']->template->set_var(array(
										'action_url' => $GLOBALS['phpgw']->link('/index.php','menuaction=admin.uivoip.add'),
										'lang_Email_Voip' => "Caixa Voip (Email) para habilitar o alerta telefônico",//lang('Email Voip'),
										'lang_VoIP_settings' => lang('Configuration Service VoIP'),
										'lang_Enter_your_VoIP_server_address' => lang('Enter your VoIP server address'),	
										'lang_Enter_your_VoIP_server_url' => lang('Enter your VoIP server url'),	
										'lang_Enter_your_VoIP_server_port' => lang('Enter your VoIP server port'),
										'lang_save' => lang('Save'),
										'lang_cancel' => lang('Cancel'),
										'value_voip_email_redirect' => ($GLOBALS['phpgw_info']['server']['voip_email_redirect']) ? $GLOBALS['phpgw_info']['server']['voip_email_redirect'] : '',
										'value_voip_server' => ($GLOBALS['phpgw_info']['server']['voip_server']) ? $GLOBALS['phpgw_info']['server']['voip_server'] : '',
										'value_voip_url' => ($GLOBALS['phpgw_info']['server']['voip_url']) ? $GLOBALS['phpgw_info']['server']['voip_url'] : '',
										'value_voip_port' => ($GLOBALS['phpgw_info']['server']['voip_port']) ? $GLOBALS['phpgw_info']['server']['voip_port'] : '',
										'lang_load' => lang('Wait Loading...!'),
										'lang_grupos_ldap' => 'Grupos Ldap',
										'lang_grupos_liberados' => 'Grupos Liberados',
										'lang_groups_ldap' => lang('groups ldap'),
										'lang_organizations' => lang('Organizations'),
										'groups_voip' => $gvoip,
										'ous_ldap' => $ous
										));
	
		$GLOBALS['phpgw']->template->pparse('out','voip_page');
	}
	
	function display_row($label, $value)
	{
		$GLOBALS['phpgw']->template->set_var('tr_color',$this->nextmatchs->alternate_row_color());
		$GLOBALS['phpgw']->template->set_var('label',$label);
		$GLOBALS['phpgw']->template->set_var('value',$value);
		$GLOBALS['phpgw']->template->parse('rows','row',True);
	}

	function add()
	{
		
		if($GLOBALS['phpgw']->acl->check('applications_access',1,'admin'))
		{
			$GLOBALS['phpgw']->redirect_link('/index.php');
		}		
		
		if ($_POST['cancel'])
		{
			$GLOBALS['phpgw']->redirect_link('/admin/index.php');
		}

		if ( $_POST['save'] )
		{
			$conf['voip_server']= $_POST['voip_server'];
			$conf['voip_url']	= $_POST['voip_url'];
			$conf['voip_port']	= $_POST['voip_port'];
			$conf['voip_email_redirect'] = $_POST['voip_email_redirect'];
			
			if( is_array($_POST['voip_groups']) )
				foreach($_POST['voip_groups'] as $tmp)
					$conf['voip_groups'] = (count($conf['voip_groups']) > 0 ) ? $conf['voip_groups'] . "," . $tmp : $tmp;
			else{
				$conf['voip_groups'] = '';
			}
			$this->bo->setConfDB($conf);
		}

		$GLOBALS['phpgw']->redirect_link('/admin/index.php');
	}
}
?>
