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

require_once "class.bomodule.inc.php"; 

class uimodule
{
	public $public_functions = array(
		'add'      => True,
		'edit_conf' => True,
	);

	private $bo;
	
	final function __construct()
	{
		$this->bo = new bomodule();	
	}	
	
	public final function edit_conf()
	{
		if( !$GLOBALS['phpgw']->acl->check('run',1,'admin') )
		{
			$GLOBALS['phpgw']->redirect_link('/admin/index.php');
		}		
		
		$GLOBALS['phpgw_info']['flags']['app_header'] = lang('Admin') .' - ' . 'Configurar o Uso do Módulo';	
		
		if(!@is_object($GLOBALS['phpgw']->js))
		{
			$GLOBALS['phpgw']->js = CreateObject('phpgwapi.javascript');
		}
			
		$webserver_url = $GLOBALS['phpgw_info']['server']['webserver_url'];
		$webserver_url = ( !empty($webserver_url) ) ? $webserver_url : '/';
		
		$js = array('functions');
		
		foreach( $js as $tmp )
			$GLOBALS['phpgw']->js->validate_file('',$tmp,'jabberit_messenger');
		
		$GLOBALS['phpgw']->common->phpgw_header();
		echo parse_navbar();
		
		if( $apps_list = $this->bo->getApplicationsEnabled())
		{
			$apps_list = unserialize($apps_list);
			if(is_array($apps_list))
			{
				foreach($apps_list as $tmp)
					$apps_en .= "<option value='".$tmp."'>".substr($tmp,strpos($tmp, ";")+1)."</option>";
			}
		}

		if( $apps_enabled = $this->bo->getApplicationsList() )
		{
			if(is_array($apps_enabled))
			{
				foreach($apps_enabled as $tmp )
					$apps .= "<option value='".$tmp['name'].";".$tmp['title']."'>".$tmp['title']."</option>";
			}
		}

		$GLOBALS['phpgw']->template->set_file(array('jabberit_messenger'=>'module.tpl'));
		$GLOBALS['phpgw']->template->set_block('jabberit_messenger','module');	
		$GLOBALS['phpgw']->template->set_var(array(
										'action_url' => $GLOBALS['phpgw']->link('/index.php','menuaction=jabberit_messenger.uimodule.add'),
										'apps_enabled' => $apps_en,
										'apps_list' => $apps,
										'lang_Select_the_modules_where_the_Expresso_Messenger_will_be_loaded' => lang('Select the modules where the Expresso Messenger will be loaded.'),										
										'lang_Enable_the_Expresso_Messenger_module' => lang('Enable the Expresso Messenger module'),
										'lang_Modules_List' => lang('Modules List'),
										'lang_Modules_Enabled' => lang('Modules Enabled'),
										'lang_save' => lang('Save'),
										'lang_cancel' => lang('Cancel')
										));
	
		$GLOBALS['phpgw']->template->pparse('out','module');
	}
	
	public final function add()
	{
		if( !$GLOBALS['phpgw']->acl->check('run',1,'admin') )
		{
			$GLOBALS['phpgw']->redirect_link('/admin/index.php');
		}		
		
		if ($_POST['cancel'])
		{
			$GLOBALS['phpgw']->redirect_link('/index.php?menuaction=jabberit_messenger.uiconfig.configPermission');
		}

		if ( $_POST['save'] )
		{
			if(is_array($_POST['apps_enabled']))
				$this->bo->setApplications($_POST['apps_enabled']);
			else
				$this->bo->setApplications("");
		}
		
		$GLOBALS['phpgw']->redirect_link('/index.php?menuaction=jabberit_messenger.uiconfig.configPermission');
	}
}

?>
