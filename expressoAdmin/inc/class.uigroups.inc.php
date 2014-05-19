<?php
	/***********************************************************************************\
	* Expresso Administração                 										    *
	* by Joao Alfredo Knopik Junior (joao.alfredo@gmail.com, jakjr@celepar.pr.gov.br)   *
	* ----------------------------------------------------------------------------------*
	*  This program is free software; you can redistribute it and/or modify it		    *
	*  under the terms of the GNU General Public License as published by the			*
	*  Free Software Foundation; either version 2 of the License, or (at your			*
	*  option) any later version.											            *
	\***********************************************************************************/

	class uigroups
	{
		var $public_functions = array
		(
			'list_groups'	=> True,
			'add_groups'	=> True,
			'edit_groups'	=> True,
			'css'			=> True
		);

		var $nextmatchs;
		var $group;
		var $functions;
		var $ldap_functions;
		var $db_functions;
			
		function uigroups()
		{
			$this->group		= CreateObject('expressoAdmin.group');
			$this->nextmatchs	= createobject('phpgwapi.nextmatchs');
			$this->functions	= CreateObject('expressoAdmin.functions');
			$this->ldap_functions = CreateObject('expressoAdmin.ldap_functions');
			$this->db_functions = CreateObject('expressoAdmin.db_functions');
			
			$c = CreateObject('phpgwapi.config','expressoAdmin');
			$c->read_repository();
			$this->current_config = $c->config_data;
			
			if(!@is_object($GLOBALS['phpgw']->js))
			{
				$GLOBALS['phpgw']->js = CreateObject('phpgwapi.javascript');
			}
			$GLOBALS['phpgw']->js->validate_file('jscode','connector','expressoAdmin');#diretorio, arquivo.js, aplicacao
			$GLOBALS['phpgw']->js->validate_file('jscode','finder','expressoAdmin');
			$GLOBALS['phpgw']->js->validate_file('jscode','expressoadmin','expressoAdmin');
			$GLOBALS['phpgw']->js->validate_file('jscode','groups','expressoAdmin');
		}
		
		function list_groups()
		{
			$account_lid 		= $GLOBALS['phpgw']->accounts->data['account_lid'];
			$manager_acl		= $this->functions->read_acl($account_lid);
			$contexts 			= $manager_acl['contexts'];
			$context_display	= "";

			foreach ($manager_acl['contexts_display'] as $index=>$tmp_context)
			{
				$context_display .= '<br>'.$tmp_context;
			}
			
			// Verifica se tem acesso a este modulo
			if (!$this->functions->check_acl($account_lid,'list_groups'))
			{
				$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/expressoAdmin/inc/access_denied.php'));
			}

			if(isset($_POST['query']))
			{
				// limit query to limit characters
				if(preg_match('/^[a-z_0-9_-].+$/i',$_POST['query'])) 
					$GLOBALS['query'] = $_POST['query'];
			}
						
			unset($GLOBALS['phpgw_info']['flags']['noheader']);
			unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
			
			$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['expressoAdmin']['title'].' - '.lang('User groups');
			$GLOBALS['phpgw']->common->phpgw_header();

			$p = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
			$p->set_file(array('groups'   => 'groups.tpl'));
			$p->set_block('groups','list','list');
			$p->set_block('groups','row','row');
			$p->set_block('groups','row_empty','row_empty');

			// Seta as variaveis padroes.
			$var = Array(
				'th_bg'					=> $GLOBALS['phpgw_info']['theme']['th_bg'],
				'back_url'				=> $GLOBALS['phpgw']->link('/expressoAdmin/index.php'),
				'add_action'			=> $GLOBALS['phpgw']->link('/index.php','menuaction=expressoAdmin.uigroups.add_groups'),
				'add_group_disabled'	=> $this->functions->check_acl($account_lid,'add_groups') ? '' : 'disabled',
				'context_display'		=> $context_display
			);
			$p->set_var($var);
			$p->set_var($this->functions->make_dinamic_lang($p, 'list'));
			
			// Save query
			$p->set_var('query', (isset($GLOBALS['query'])? $GLOBALS['query'] : ""));
			
			//Admin make a search
			$groups_info = array();
			
			if( isset($GLOBALS['query']) && $GLOBALS['query'] != '' )
			{
				$groups_info = $this->functions->get_list('groups', $GLOBALS['query'], $contexts);
			}
			
			$total = count($groups_info);

			if (!count($total) && $GLOBALS['query'] != '')
			{
				$p->set_var('message',lang('No matches found'));
			}
			else if ($total)
			{
				if ($this->functions->check_acl($account_lid,'edit_groups'))
				{
					$can_edit = True;
				}
				if ($this->functions->check_acl($account_lid,'delete_groups'))
				{
					$can_delete = True;
				}

				if( count($groups_info) )
				{
					$tr_color = "";

					foreach($groups_info as $group)
					{
						$tr_color = $this->nextmatchs->alternate_row_color($tr_color);
						$var = Array(
							'tr_color'    		=> $tr_color,
							'row_cn'  			=> $group['cn'],
							'row_description'	=> $group['description']
						);
						$p->set_var($var);

						if ($can_edit)
						{
							$p->set_var('edit_link',$this->row_action('edit','groups',$group['gidnumber'],$group['cn']));
						}
						else
						{
							$p->set_var('edit_link','&nbsp;');
						}

						if ($can_delete)
						{
							$p->set_var('delete_link',"<a href='#' onClick='javascript:delete_group(\"".$group['cn']."\",\"".$group['gidnumber']."\");'>".lang('to delete')."</a>");
						}
						else
						{
							$p->set_var('delete_link','&nbsp;');
						}

						$p->fp('rows','row',True);
					}
				}
			}
			$p->parse('rows','row_empty',True);
			$p->set_var($var);

			if (! $GLOBALS['phpgw']->acl->check('run',4,'admin'))
			{
				$p->set_var('input_add','<input type="submit" value="' . lang('Add') . '">');
			}
			if (! $GLOBALS['phpgw']->acl->check('run',2,'admin'))
			{
				$query = (isset($GLOBALS['query'])? $GLOBALS['query'] : "");
				$p->set_var('input_search',lang('Search') . '&nbsp;<input name="query" value="'.htmlspecialchars(stripslashes($query)).'">');
			}
			$p->pfp('out','list');
		}
		
		function add_groups()
		{
			if ($this->current_config['expressoAdmin_samba_support'] == 'true')
			{
				$GLOBALS['phpgw']->js->set_onload('get_available_sambadomains(document.forms[0].context.value, \'create_group\');');
			}

			$manager_lid 		= $GLOBALS['phpgw']->accounts->data['account_lid'];
			$manager_acl 		= $this->functions->read_acl($manager_lid);
			$manager_contexts 	= $manager_acl['contexts'];
			$group_info 		=  array();

			// Verifica se tem acesso a este modulo
			if (!$this->functions->check_acl($manager_lid,'add_groups'))
			{
				$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/expressoAdmin/inc/access_denied.php'));
			}

			unset($GLOBALS['phpgw_info']['flags']['noheader']);
			unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
			$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['expressoAdmin']['title'].' - '.lang('Create Group');
			$GLOBALS['phpgw']->common->phpgw_header();
			
			// Set o template
			$p = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
			$p->set_file(Array('create_group' => 'groups_form.tpl'));
			$p->set_block('create_group','list','list');

			// Pega combo das organizações e seleciona um dos setores em caso de um erro na validaçao dos dados.
			//$combo_manager_org = $this->functions->get_organizations($manager_context, trim(strtolower($group_info['context'])));
			foreach( $manager_contexts as $index => $context )
			{
				if( isset($group_info['context']) )
					$combo_manager_org .= $this->functions->get_organizations($context, trim(strtolower($group_info['context'])));
			}
			
			if( isset($group_info['context']) )
				$combo_all_orgs = $this->functions->get_organizations($GLOBALS['phpgw_info']['server']['ldap_context'], trim(strtolower($group_info['context'])));
			
			// Chama funcao para criar lista de aplicativos disponiveis.
			$apps = $this->functions->make_list_app($manager_lid);
			
			// Cria combo de dominio samba
			if ($this->current_config['expressoAdmin_samba_support'] == 'true')
			{
				$a_sambadomains = $this->db_functions->get_sambadomains_list();
				$sambadomainname_options = '';
				if (count($a_sambadomains))
				{
					foreach ($a_sambadomains as $a_sambadomain)
					{
						// So mostra os sambaDomainName do contexto do manager
						if ($this->ldap_functions->exist_sambadomains($manager_contexts, $a_sambadomain['samba_domain_name']))
							$sambadomainname_options .= "<option value='" . $a_sambadomain['samba_domain_sid'] . "'>" . $a_sambadomain['samba_domain_name'] . "</option>";
					}
				}
			}
			
			// Seta variaveis utilizadas pelo tpl.
			$var = Array(
				'color_bg1'					=> "#E8F0F0",
				'color_bg2'					=> "#D3DCE3",
				'type'						=> 'create_group',
				'cn'						=> '',
				'restrictionsOnGroup'		=> (isset($this->current_config['expressoAdmin_restrictionsOnGroup'])? $this->current_config['expressoAdmin_restrictionsOnGroup'] : "" ),
				'type'						=> 'create_group',
				'ldap_context'				=> $GLOBALS['phpgw_info']['server']['ldap_context'],
				'ufn_ldap_context'			=> ldap_dn2ufn($GLOBALS['phpgw_info']['server']['ldap_context']),
				'concatenateDomain'			=> (isset($this->current_config['expressoAdmin_concatenateDomain'])?$this->current_config['expressoAdmin_concatenateDomain'] : "" ),
				'defaultDomain'				=> (isset($this->current_config['expressoAdmin_defaultDomain'])?$this->current_config['expressoAdmin_defaultDomain'] : "" ),
				'apps'						=> $apps,
				'use_attrs_samba_checked'	=> '',
				'disabled_samba'			=> 'disabled',
				'display_samba_options'		=> $this->current_config['expressoAdmin_samba_support'] == 'true' ? '' : '"display:none"',
				'disable_email_groups'		=> $this->functions->check_acl($manager_lid,'edit_email_groups') ? '' : 'disabled',
				'sambadomainname_options'	=> ( isset($sambadomainname_options) ? $sambadomainname_options : "" ),
				'back_url'					=> $GLOBALS['phpgw']->link('/index.php','menuaction=expressoAdmin.uigroups.list_groups'),
				'combo_manager_org'			=> (isset($combo_manager_org)? $combo_manager_org : "" ),
				'combo_all_orgs'			=> (isset($combo_all_orgs) ? $combo_all_orgs : "" )
			);
			$p->set_var($var);
			$p->set_var($this->functions->make_dinamic_lang($p, 'list'));
			
			$p->pfp('out','create_group');
		}
		
		function edit_groups()
		{
			$manager_lid = $GLOBALS['phpgw']->accounts->data['account_lid'];
			$manager_acl = $this->functions->read_acl($manager_lid);
			$manager_contexts = $manager_acl['contexts'];

			// Verifica se tem acesso a este modulo
			if (!$this->functions->check_acl($manager_lid,'edit_groups'))
			{
				$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/expressoAdmin/inc/access_denied.php'));
			}

			// GET all infomations about the group.
			$group_info = $this->group->get_info($_GET['gidnumber']);

			unset($GLOBALS['phpgw_info']['flags']['noheader']);
			unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
			$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['expressoAdmin']['title'].' - '.lang('Edit Group');
			$GLOBALS['phpgw']->common->phpgw_header();

			// Set o template
			$p = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
			$p->set_file(Array('create_group' => 'groups_form.tpl'));
			$p->set_block('create_group','list','list');

			// Obtem combo das organizações e seleciona a org do grupo.
			$combo_manager_org = "";
			foreach ($manager_contexts as $index=>$context)
			{
				$combo_manager_org .= $this->functions->get_organizations($context, trim(strtolower($group_info['context'])));
			}

			$combo_all_orgs = $this->functions->get_organizations($GLOBALS['phpgw_info']['server']['ldap_context'], trim(strtolower($group_info['context'])));

			// Usuarios do grupo.
			$user_count = 0;
			if (count($group_info['memberuid_info']) > 0)
			{
				foreach ($group_info['memberuid_info'] as $uid=>$user_data)
				{
					if ($user_data['uidnumber'])
					{
						$array_users[$user_data['uidnumber']] = $user_data['cn'];
						$array_users_uid[$user_data['uidnumber']] = $uid;
						$array_users_type[$user_data['uidnumber']] = $user_data['type'];
					}
					else
					{
						$array_users[$uid] = $user_data['cn'];
					}
				}
				natcasesort($array_users);
				
				$users 	= "";
				$unknow	= "";

				foreach ($array_users as $uidnumber=>$cn)
				{
					++$user_count;
					
					if ($array_users_type[$uidnumber] == 'u')
					{
						$users .= "<option value=" . $uidnumber . ">" . utf8_decode($cn) . " (" . $array_users_uid[$uidnumber] . ")</option>";
					}
					else
					{
						$unknow .= "<option value=-1>" . utf8_decode($cn) . " (Corrigir manualmente)</option>";
					}					
				}
				
				$opt_tmp_users  = '<option  value="-1" disabled>-----------------------------&nbsp;&nbsp;&nbsp;&nbsp;'.lang('users').'&nbsp;&nbsp;&nbsp;&nbsp;---------------------------- </option>'."\n";
				$opt_tmp_unknow = '<option  value="-1" disabled>------------&nbsp;&nbsp;&nbsp;&nbsp;'.lang('users did not find on DB, only on ldap').'&nbsp;&nbsp;&nbsp;&nbsp;------------</option>'."\n";
				$ea_select_usersInGroup = $unknow != '' ? $opt_tmp_unknow . $unknow . $opt_tmp_users . $users : $opt_tmp_users . $users;
			}
			
			// Chama funcao para criar lista de aplicativos disponiveis.
			$apps = $this->functions->make_list_app($manager_lid, $group_info['apps']);
			
			// Cria combo de dominios do samba
			if ($this->current_config['expressoAdmin_samba_support'] == 'true')
			{
				$a_sambadomains = $this->db_functions->get_sambadomains_list();
				$sambadomainname_options = '';
				if (count($a_sambadomains))
				{
					foreach ($a_sambadomains as $a_sambadomain)
					{
						if ($a_sambadomain['samba_domain_sid'] == $group_info['sambasid'])
							$sambadomainname_options .= "<option value='" . $a_sambadomain['samba_domain_sid'] . "' SELECTED>" . $a_sambadomain['samba_domain_name'] . "</option>";
						else
							$sambadomainname_options .= "<option value='" . $a_sambadomain['samba_domain_sid'] . "'>" . $a_sambadomain['samba_domain_name'] . "</option>";
					}
				}
			}
			
			// Seta variaveis utilizadas pelo tpl.
			$var = Array(
				'color_bg1'					=> "#E8F0F0",
				'color_bg2'					=> "#D3DCE3",
				'type'						=> 'edit_group',
				'ldap_context'				=> $GLOBALS['phpgw_info']['server']['ldap_context'],
				'gidnumber'					=> $group_info['gidnumber'],
				'cn'						=> $group_info['cn'],
				'user_count'				=> $user_count,
				'email'						=> $group_info['email'],
				'description'				=> $group_info['description'],
				'apps'						=> $apps,
				'use_attrs_samba_checked'	=> (isset($group_info['sambaGroup']) ? 'CHECKED' : ''),
				'disabled_samba'			=> (isset($group_info['sambaGroup']) ? '' : 'disabled'),
				'disable_email_groups'		=> $this->functions->check_acl($manager_lid,'edit_email_groups') ? '' : 'disabled',
				'sambadomainname_options'	=> (isset($sambadomainname_options)? $sambadomainname_options : "" ),
				'phpgwaccountvisible_checked'	=> $group_info['phpgwaccountvisible'] == '-1' ? 'CHECKED' : '',
				'back_url'					=> $GLOBALS['phpgw']->link('/index.php','menuaction=expressoAdmin.uigroups.list_groups'),
				'combo_manager_org'			=> $combo_manager_org,
				'combo_all_orgs'			=> $combo_all_orgs,
				'ea_select_usersInGroup'	=> $ea_select_usersInGroup
			);
			$p->set_var($var);
			$p->set_var($this->functions->make_dinamic_lang($p, 'list'));
			$p->pfp('out','create_group');
		}
				
		function row_action($action,$type,$gidnumber,$group_name)
		{
			return '<a href="'.$GLOBALS['phpgw']->link('/index.php',Array(
				'menuaction'		=> 'expressoAdmin.uigroups.'.$action.'_'.$type,
				'gidnumber'		=> $gidnumber,
				'group_name'	=> $group_name
			)).'"> '.lang($action).' </a>';
		}
		
		function css()
		{
			$appCSS = '';
			return $appCSS;
		}
		
	}
?>
