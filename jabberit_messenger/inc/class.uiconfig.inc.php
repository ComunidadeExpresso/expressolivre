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
  
class uiconfig
{
	private $uimodule;
	
	public $public_functions = array(
		'configAudit'  => True,
		'configPermission'  => True,		
		'configServer'  => True,
		'css' => True
	);
	
	function __construct()
	{
		$GLOBALS['phpgw']->common->phpgw_header();
	}	

	public final function configPermission()
	{
		if( !$GLOBALS['phpgw']->acl->check('run',1,'admin') )
		{
			$GLOBALS['phpgw']->redirect_link('/admin/index.php');
		}

		$GLOBALS['phpgw_info']['flags']['app_header'] = lang('Admin') .' - ' . 'Permissões de Acesso';
		echo parse_navbar();

		$GLOBALS['phpgw']->template->set_file(array('jabberit_messenger'=>'configItens.tpl'));
		$GLOBALS['phpgw']->template->set_block('jabberit_messenger','configPermission');	
		$GLOBALS['phpgw']->template->set_var(array(
													'action_url_back' => "./admin",
													'label_config1' => lang("Enable the Expresso Messenger inside of the modules"),
													'label_config2' => "Habilitar adição de novos contatos somente por grupos",
													'label_config3' => lang("Restrict group"),													
													'label_config4' => lang("Free organization for group"),	
													'label_config5' => "Habilitar módulo SEM Java para os grupos",
													'value_config1' => $GLOBALS['phpgw']->link('/index.php','menuaction=jabberit_messenger.uimodule.edit_conf'),												
													'value_config2' => $GLOBALS['phpgw']->link('/index.php','menuaction=jabberit_messenger.uigroupsldap.edit'),
													'value_config3' => $GLOBALS['phpgw']->link('/index.php','menuaction=jabberit_messenger.uigroupslocked.editGroups'),
													'value_config4' => $GLOBALS['phpgw']->link('/index.php','menuaction=jabberit_messenger.uienabledgroups.getGroups'),
													'value_config5' => $GLOBALS['phpgw']->link('/index.php','menuaction=jabberit_messenger.uijmessenger.getGroups'),
													'value_display_jmessenger' => ( (is_dir('./jabberit_messenger/jmessenger') ) ? "block" : "none"),
													'value_image1' 	=> "./jabberit_messenger/templates/default/images/app.gif",
													'value_image2' 	=> "./jabberit_messenger/templates/default/images/group.gif",
													'value_image3' 	=> "./jabberit_messenger/templates/default/images/group_deny.gif",													
													'value_image4' 	=> "./jabberit_messenger/templates/default/images/group_add.gif",
													'value_image5'	=> "./jabberit_messenger/templates/default/images/sem_java.png"												
													));
	
		$GLOBALS['phpgw']->template->pparse('out','configPermission');
		
	}

	public final function configServer()
	{
		if( !$GLOBALS['phpgw']->acl->check('run',1,'admin') )
		{
			$GLOBALS['phpgw']->redirect_link('/admin/index.php');
		}

		$GLOBALS['phpgw_info']['flags']['app_header'] = lang('Admin') .' - ' . 'Configurações do Servidor';
		echo parse_navbar();
		
		$GLOBALS['phpgw']->template->set_file(array('jabberit_messenger'=>'configItens.tpl'));
		$GLOBALS['phpgw']->template->set_block('jabberit_messenger','configServer');	
		$GLOBALS['phpgw']->template->set_var(array(
													'action_url_back' => "./admin",
													'label_config1' => lang("Site Configuration Jabber"),
													'label_config2' => lang("Map organization for realm jabber"),
													'value_config1' => $GLOBALS['phpgw']->link('/index.php','menuaction=admin.uiconfig.index&appname=jabberit_messenger'),												
													'value_config2' => $GLOBALS['phpgw']->link('/index.php','menuaction=jabberit_messenger.uihostsjabber.edit_conf'),
													'value_image1' 	=> "jabberit_messenger/templates/default/images/navbar.png",
													'value_image2' 	=> "jabberit_messenger/templates/default/images/gear.png",
													));
	
		$GLOBALS['phpgw']->template->pparse('out','configServer');
	}

}

?>
