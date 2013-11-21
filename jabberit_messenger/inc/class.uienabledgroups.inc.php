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
  
require_once "class.boenabledgroups.inc.php";

class uienabledgroups
{
	private $bo;
	
	public $public_functions = array(
		'backPage'  => True,
		'editGroups' => True,
		'getGroups' => True,
	);
	
	function __construct()
	{
		$this->bo = new boenabledgroups();
	}
	
	public final function editGroups()
	{
		if($_GET['menuaction'])
		{
			if( !$GLOBALS['phpgw']->acl->check('run',1,'admin') )
			{
				$GLOBALS['phpgw']->redirect_link('/admin/index.php');
			}		
			
			$GLOBALS['phpgw_info']['flags']['app_header'] = lang('Admin') .' - ' . 'Liberar Organizações para grupos restritos';
	
			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();
	
			$webserver_url = $GLOBALS['phpgw_info']['server']['webserver_url'];
			$webserver_url = ( !empty($webserver_url) ) ? $webserver_url : '/';
	
			if(strrpos($webserver_url,'/') === false || strrpos($webserver_url,'/') != (strlen($webserver_url)-1))
				$webserver_url .= '/';
	
			// Load Language;
			require_once "load_lang.php";
			
			// Load Ldap;
			require_once "class.ldap_im.inc.php";
			
			$ldap = new ldap_im();
			
			$ous = "<option value='-1'>-- ".lang('Select Organization')." --</option>";	
			if( ($LdapOus = $ldap->getOrganizationsLdap('localhost')) )
			{
				foreach($LdapOus as $key => $val )
					$ous .= "<option value='".$key."'>".$val."</option>";
			}

			$group = explode(":",$_GET['groups']);

			$valueGroupsOrganizations = "";
			
			if( $group[2] )
			{
				$ou_groups = explode(",",$group[2]);
			
				natcasesort($ou_groups);
			
				foreach($ou_groups as $tmp)
				{
					$valueGroupsOrganizations .= "<tr id='".$tmp."'>";
					$valueGroupsOrganizations .= "<td align='left' class='row_on'>".$tmp."</td>";
					$valueGroupsOrganizations .= "<td align='left' class='row_on' style='width:30% !important'><a href='javascript:constructScript.removeOrgGroupsLocked(\"".$tmp."\");'>Excluir</a></td>";					
					$valueGroupsOrganizations .= "</tr>";
				}
			}
			
			$GLOBALS['phpgw']->template->set_file(array('jabberit_messenger'=>'confGroupsLocked.tpl'));
			$GLOBALS['phpgw']->template->set_block('jabberit_messenger','confGroups');	
			$var = array(
							'action_url' => "./index.php?menuaction=jabberit_messenger.uienabledgroups.getGroups",
							'lang_Back' => "Voltar",	
							'lang_Cadastrar_Organizacao' => "Cadastrar Organização",	
							'lang_Delete' => "Excluir",							
							'lang_Informe_as_Organizacoes' => "Informe as Organizações",
							'lang_Nome_Grupo' => "Nome do Grupo",
							'lang_Organizacoes_cadastradas_para_grupo' => "Organizações cadastradas para o grupo",	
							'lang_Organization' => "Organização",
							'lang_save' => "Salvar",
							'value_Groups_Organizations' => $valueGroupsOrganizations,	
							'value_organizations_ldap' => $ous,							
							'value_Name_Group' => $group[0],
							'value_gidNumber' => $group[1],
						);	
			$GLOBALS['phpgw']->template->set_var($var);
			$GLOBALS['phpgw']->template->pparse('out','confGroups');
		}
	}

	public final function getGroups()
	{
		if( !$GLOBALS['phpgw']->acl->check('run',1,'admin') ) 
		{
			$GLOBALS['phpgw']->redirect_link('/admin/index.php');
		}		
		
		$GLOBALS['phpgw_info']['flags']['app_header'] = lang('Admin') .' - ' . 'Liberar Organizações para grupos restritos';

		$GLOBALS['phpgw']->common->phpgw_header();
		echo parse_navbar();

		$webserver_url = $GLOBALS['phpgw_info']['server']['webserver_url'];
		$webserver_url = ( !empty($webserver_url) ) ? $webserver_url : '/';

		if(strrpos($webserver_url,'/') === false || strrpos($webserver_url,'/') != (strlen($webserver_url)-1))
			$webserver_url .= '/';

		echo '<script type="text/javascript">var path_jabberit="'.$webserver_url .'"</script>';

		// Load Language;
		require_once "load_lang.php";

		$groups_locked_jabberit = $this->bo->getGroupsBlocked();

		if(trim($groups_locked_jabberit))
		{
			$glocked = explode(';',$GLOBALS['phpgw_info']['server']['groups_locked_jabberit']);
			$list_groups = "";

			natcasesort($glocked);
						
			foreach( $glocked as $tmp )
			{
				$groups = explode(":",$tmp);
				$list_groups .= "<tr class='row_off'>";
				$list_groups .= "<td width='30%'>".$groups[0]."</td>";
				$list_groups .= "<td width='55%'>".$groups[2]."</td>";
				$list_groups .= "<td width='5%' align='center'><a href='./index.php?menuaction=jabberit_messenger.uienabledgroups.editGroups&groups=".$tmp."'>Editar</a></td>";
				$list_groups .= "</tr>";
			}
		}

		$GLOBALS['phpgw']->template->set_file(array('jabberit_messenger'=>'enabled_ou_groups.tpl'));
		$GLOBALS['phpgw']->template->set_block('jabberit_messenger','enabled_ous');	
		$var = array(
						'action_url' => './index.php?menuaction=jabberit_messenger.uiconfig.configPermission',						
						'lang_back' => lang("Back"),
						'list_groups' => (trim($list_groups) != "") ? $list_groups : "",
					);	
		$GLOBALS['phpgw']->template->set_var($var);
		$GLOBALS['phpgw']->template->pparse('out','enabled_ous');
	}
}


?>
