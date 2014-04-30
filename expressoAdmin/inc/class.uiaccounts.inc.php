<?php
	/***********************************************************************************\
	* Expresso Administração															*
	* by Joao Alfredo Knopik Junior (joao.alfredo@gmail.com, jakjr@celepar.pr.gov.br)  	*
	* ----------------------------------------------------------------------------------*
	*  This program is free software; you can redistribute it and/or modify it			*
	*  under the terms of the GNU General Public License as published by the			*
	*  Free Software Foundation; either version 2 of the License, or (at your			*
	*  option) any later version.														*
	\***********************************************************************************/

	class uiaccounts
	{
		var $public_functions = array
		(
			'list_users'				=> True,
			'add_users'					=> True,
			'edit_user'					=> True,
			'view_user'					=> True,
			'show_photo'				=> True,
			'show_access_log'			=> True,
			'css'						=> True,
			'list_inactive_users' => True
		);

		var $nextmatchs;
		var $user;
		var $functions;
		var $current_config;
		var $ldap_functions;
		var $db_functions;

		function uiaccounts()
		{
			$this->user			= CreateObject('expressoAdmin.user');
			$this->nextmatchs	= CreateObject('phpgwapi.nextmatchs');
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
			$GLOBALS['phpgw']->js->validate_file('jscode','tabs','expressoAdmin');
			$GLOBALS['phpgw']->js->validate_file('jscode','users','expressoAdmin');
		}

		function list_inactive_users() {

			$context = $_GET["context"];

			unset($GLOBALS['phpgw_info']['flags']['noheader']);
			unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
			$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['expressoAdmin']['title'].' - '. lang('list inactives');
			$GLOBALS['phpgw']->common->phpgw_header();

			$p = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
			$p->set_file(array('inactive_users' => 'list_inactives.tpl'));
			$p->set_block('inactive_users','list','list');
			$p->set_block('inactive_users','row','row');
			$p->set_block('inactive_users','row_empty','row_empty');
			$contexts = array();
			array_push($contexts,$context);
						
			$usuarios = $this->functions->get_inactive_users($contexts);

	
			
			$var = Array(
				'th_bg'					=> $GLOBALS['phpgw_info']['theme']['th_bg'],
				'back_url'				=> $GLOBALS['phpgw']->link('/index.php?menuaction=expressoAdmin.uisectors.list_sectors'),
				'context_display'		=> $context_display,
				'lang_idusuario'			=> lang('uidNumber'),
				'lang_login'			=> lang('user login'),
				'lang_ultimo_login'	=> lang('last login'),
				'lang_back'			=> lang('back')
			);
			$p->set_var($var);

			if (!count($usuarios))
			{
				$p->set_var('message',lang('No matches found'));
			}
			else
			{
				foreach($usuarios as $usuario)
				{
					$tr_color = $this->nextmatchs->alternate_row_color($tr_color);
					
					$var = Array(
						'tr_color'    => $tr_color,
						'id'  => $usuario["uidNumber"],
						'login' => $usuario["login"],
						'data_ultimo_login' => date("d/m/Y",$usuario["li"])
					);
					$p->set_var($var);

					$p->fp('rows','row',True);
				}
			}		
			$p->parse('rows','row_empty',True);
			$p->pfp('out','list');

			
		}

		function list_users()
		{
			$account_lid = $GLOBALS['phpgw']->accounts->data['account_lid'];
			$acl = $this->functions->read_acl($account_lid);
			$raw_context = $acl['raw_context'];
			$contexts = $acl['contexts'];
			foreach ($acl['contexts_display'] as $index=>$tmp_context)
			{
				$context_display .= '<br>'.$tmp_context;
			}
			// Verifica se o administrador tem acesso.
			if (!$this->functions->check_acl($account_lid,'list_users'))
			{
				$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/expressoAdmin/inc/access_denied.php'));
			}

			if(isset($_POST['query']))
			{
				// limit query to limit characters
				if(preg_match('/^[a-z_0-9_-].+$/i',$_POST['query'])) 
				{
					$GLOBALS['query'] = $_POST['query'];
				}
			}
			
			unset($GLOBALS['phpgw_info']['flags']['noheader']);
			unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
			$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['expressoAdmin']['title'].' - '.lang('User accounts');
			$GLOBALS['phpgw']->common->phpgw_header();

			$p = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
			$p->set_file(Array('accounts' => 'accounts.tpl'));
			$p->set_block('accounts','body');
			$p->set_block('accounts','row');
			$p->set_block('accounts','row_empty');

			$var = Array(
				'bg_color'					=> $GLOBALS['phpgw_info']['theme']['bg_color'],
				'th_bg'						=> $GLOBALS['phpgw_info']['theme']['th_bg'],
				'accounts_url'				=> $GLOBALS['phpgw']->link('/index.php','menuaction=expressoAdmin.uiaccounts.list_users'),
				'back_url'					=> $GLOBALS['phpgw']->link('/expressoAdmin/index.php'),
				'add_action'				=> $GLOBALS['phpgw']->link('/index.php','menuaction=expressoAdmin.uiaccounts.add_users'),
				'create_user_disabled'		=> $this->functions->check_acl($account_lid,'add_users') ? '' : 'disabled',
				'context'					=> $raw_context,
				'context_display'			=> $context_display,
				'imapDelimiter'				=> $_SESSION['phpgw_info']['expresso']['email_server']['imapDelimiter']
			);
			$p->set_var($var);
			$p->set_var($this->functions->make_dinamic_lang($p, 'body'));

			$p->set_var('query', $GLOBALS['query']);
			
			//Admin make a search
			if ($GLOBALS['query'] != '')
			{
				$account_info = $this->functions->get_list('accounts', $GLOBALS['query'], $contexts);
			}
			
			if (!count($account_info) && $GLOBALS['query'] != '')
			{
				$p->set_var('message',lang('No matches found'));
				$p->parse('rows','row_empty',True);
			}
			else if (count($account_info))
			{  // Can edit, delete or rename users ??
				if (($this->functions->check_acl($account_lid,'edit_users')) ||
					($this->functions->check_acl($account_lid,'change_users_password')) ||
					($this->functions->check_acl($account_lid,'edit_sambausers_attributes')) ||  
					($this->functions->check_acl($account_lid,'change_users_quote')) ||
					($this->functions->check_acl($account_lid,'manipulate_corporative_information')) ||
					($this->functions->check_acl($account_lid,'edit_users_phonenumber'))
					) 
					$can_edit = True;
				elseif ($this->functions->check_acl($account_lid,'view_users'))
					$can_view = True;
				if ($this->functions->check_acl($account_lid,'delete_users'))
					$can_delete = True;
				if ($this->functions->check_acl($account_lid,'rename_users'))
					$can_rename = True;

				while (list($null,$account) = each($account_info))
				{
					$this->nextmatchs->template_alternate_row_color($p);

					$var = array(
						'row_loginid'	=> $account['account_lid'],
						'row_cn'		=> $account['account_cn'],
						'row_mail'		=> (!$account['account_mail']?'<font color=red>Sem E-mail</font>':$account['account_mail'])
					);
					$p->set_var($var);

					if ($can_edit)
						$p->set_var('row_edit',$this->row_action('edit','user',$account['account_id']));
					elseif ($can_view)
						$p->set_var('row_edit',$this->row_action('view','user',$account['account_id']));
					else
						$p->set_var('row_edit','&nbsp;');

					if ($can_rename)
						$p->set_var('row_rename',"<a href='#' onClick='javascript:rename_user(\"".$account['account_lid']."\",\"".$account['account_id']."\");'>".lang('to rename')."</a>");
					else
						$p->set_var('row_rename','&nbsp;');

					if ($can_delete)
					{
						$p->set_var('row_delete',"<a href='#' onClick='javascript:delete_user(\"".$account['account_lid']."\",\"".$account['account_id']."\");'>".lang('to delete')."</a>");
					}
					else
						$p->set_var('row_delete','&nbsp;');

					$p->parse('rows','row',True);
				}
			}
			$p->pfp('out','body');
		}

		function add_users()
		{
			$GLOBALS['phpgw']->js->validate_file('jscode','users','expressoAdmin');
			
			$GLOBALS['phpgw']->js->set_onload('get_available_groups(document.forms[0].context.value);');
			$GLOBALS['phpgw']->js->set_onload('get_available_maillists(document.forms[0].context.value);');
			$GLOBALS['phpgw']->js->set_onload('get_associated_domain(document.forms[0].context.value);');
			if ($this->current_config['expressoAdmin_samba_support'] == 'true')
				$GLOBALS['phpgw']->js->set_onload('get_available_sambadomains(document.forms[0].context.value, \'create_user\');');
			
			$manager_lid = $GLOBALS['phpgw']->accounts->data['account_lid'];
			$acl = $this->functions->read_acl($manager_lid);
			
			$manager_contexts = $acl['contexts'];
			
			// Verifica se tem acesso a este modulo
			if (!$this->functions->check_acl($manager_lid,'add_users'))
			{
				$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/expressoAdmin/inc/access_denied.php'));
			}
				
			// Imprime nav_bar
			unset($GLOBALS['phpgw_info']['flags']['noheader']);
			unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
			$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['expressoAdmin']['title'].' - '.lang('Create User');
			$GLOBALS['phpgw']->common->phpgw_header();
			
			// Seta template
			$GLOBALS['phpgw']->js = CreateObject('phpgwapi.javascript');
			$t = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
			$t->set_file(array("body" => "accounts_form.tpl"));
			$t->set_block('body','main');

			// Pega combo das organizações e seleciona, caso seja um post, o setor que o usuario selecionou.
			foreach ($manager_contexts as $index=>$context)
				$combo_manager_org .= $this->functions->get_organizations($context);
			$combo_all_orgs = $this->functions->get_organizations($GLOBALS['phpgw_info']['server']['ldap_context'], '', true, true, true);

			// Chama funcao para criar lista de aplicativos disponiveis.
			$applications_list = $this->functions->make_list_app($manager_lid);

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
			
			// Valores default.
			$var = Array( 
				'row_on'				=> "#DDDDDD",
				'row_off'				=> "#EEEEEE",
				'color_bg1'				=> "#E8F0F0",
				//'manager_context'		=> $manager_context,
				'type'					=> 'create_user',
				'back_url'				=> $GLOBALS['phpgw']->link('/index.php','menuaction=expressoAdmin.uiaccounts.list_users'),
				'display_samba_suport'	=> $this->current_config['expressoAdmin_samba_support'] == 'true' ? '' : 'none',
				'disabled_access_button'=> 'disabled',
				'display_access_log_button'	=> 'none',
				'display_access_log_button'	=> 'none',
				'display_empty_user_inbox'	=>'none',
				'display_quota_used'		=> 'none',
				
				// First ABA
				'display_spam_uid'				=> 'display:none',
				'lang_generate_login'              => lang('Generate login'),
				'start_coment_expired'						=> "<!--",
				'end_coment_expired'						=> "-->",
				'sectors'						=> $combo_manager_org,
				'combo_organizations'			=> $combo_manager_org,
				'combo_all_orgs'				=> $combo_all_orgs,
				'passwd_expired_checked'		=> 'CHECKED',
				'changepassword_checked'		=> 'CHECKED',
				'phpgwaccountstatus_checked'	=> 'CHECKED',
				'photo_bin'						=> $GLOBALS['phpgw_info']['server']['webserver_url'].'/expressoAdmin/templates/default/images/photo_celepar.png',
				'display_picture'				=> $this->functions->check_acl($manager_lid,'edit_users_picture') ? '' : 'none', 
				'display_tr_default_password'	=> 'none',
				'minimumSizeLogin'				=> $this->current_config['expressoAdmin_minimumSizeLogin'],
				'defaultDomain'					=> ( isset($this->current_config['expressoAdmin_defaultDomain']) ? $this->current_config['expressoAdmin_defaultDomain'] : "" ),
				'concatenateDomain'				=> $this->current_config['expressoAdmin_concatenateDomain'],
				'ldap_context'					=> ldap_dn2ufn($GLOBALS['phpgw_info']['server']['ldap_context']),
				
				// Corporative Information
				'display_corporative_information' => $this->functions->check_acl($manager_lid,'manipulate_corporative_information') ? '' : 'none',
				
				//MAIL
				'accountstatus_checked'			=> 'CHECKED',
				'mailquota'						=> $this->current_config['expressoAdmin_defaultUserQuota'],
				'changequote_disabled'			=> $this->functions->check_acl($manager_lid,'change_users_quote') ? '' : 'readonly',
				'imapDelimiter'					=> $_SESSION['phpgw_info']['expresso']['email_server']['imapDelimiter'],
				'input_mailalternateaddress_fields' => '<input type="text" name="mailalternateaddress[]" id="mailalternateaddress" autocomplete="off" value="{mailalternateaddress}" {disabled} size=30>',
				'input_mailforwardingaddress_fields'=> '<input type="text" name="mailforwardingaddress[]" id="mailforwardingaddress" autocomplete="off" value="{mailforwardingaddress}" {disabled} size=30>',
				
				'apps'								=> $applications_list,
				
				//SAMBA ABA
				'use_attrs_samba_checked'			=> 'CHECKED',
				'sambadomainname_options'			=> $sambadomainname_options,
				'sambalogonscript'					=> ( isset($this->current_config['expressoAdmin_defaultLogonScript']) && $this->current_config['expressoAdmin_defaultLogonScript'] != '' ) ? $this->current_config['expressoAdmin_defaultLogonScript'] : '',
				'use_suggestion_in_logon_script'	=> ( isset($this->current_config['expressoAdmin_defaultLogonScript']) && $this->current_config['expressoAdmin_defaultLogonScript'] == '' ) ? 'true' : 'false',
			);
			
			if( (isset($this->current_config['expressoAdmin_loginGenScript'])) && 
				($this->current_config['expressoAdmin_loginGenScript'])) {
				$var['input_uid_disabled'] = 'disabled';
				$var['comment_button'] = ' ';
				$var['end_comment_button'] = ' ';
			}
			else {
				$var['input_uid_disabled'] = ' ';
				$var['comment_button'] = '<!--';
				$var['end_comment_button'] = '-->';
			}		
			
			$t->set_var($var);
			$t->set_var($this->functions->make_dinamic_lang($t, 'main'));
			$t->pfp('out','main');
		}
		
		function view_user()
		{
			ExecMethod('expressoAdmin.uiaccounts.edit_user');
			return;
		}
		
		function edit_user()
		{
			$manager_account_lid = $GLOBALS['phpgw']->accounts->data['account_lid'];
			$acl = $this->functions->read_acl($manager_account_lid);
			$raw_context = $acl['raw_context'];
			$contexts = $acl['contexts'];		
			$alert_warning = '';
			
			// Verifica se tem acesso a este modulo
			$disabled = 'disabled';
			$disabled_password = 'disabled';
			$disabled_samba = 'disabled';
			$disabled_edit_photo = 'disabled';
			$disabled_phonenumber = 'disabled';
			$disabled_group = 'disabled';
			
			$display_picture = 'none';
			if ((!$this->functions->check_acl($manager_account_lid,'edit_users')) &&
				(!$this->functions->check_acl($manager_account_lid,'change_users_password')) &&
				(!$this->functions->check_acl($manager_account_lid,'edit_sambausers_attributes')) &&
				(!$this->functions->check_acl($manager_account_lid,'view_users')) &&
				(!$this->functions->check_acl($manager_account_lid,'manipulate_corporative_information')) &&
				(!$this->functions->check_acl($manager_account_lid,'edit_users_phonenumber'))
				)
			{
				$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/expressoAdmin/inc/access_denied.php'));
			}
			// SOMENTE ALTERAÇÃO DE SENHA
			if ((!$this->functions->check_acl($manager_account_lid,'edit_users')) && ($this->functions->check_acl($manager_account_lid,'change_users_password')))
			{
				$disabled = 'disabled';
				$disabled_password = '';
			}
			// SOMENTE ALTERAÇÃO DOS ATRIBUTOS SAMBA
			if ((!$this->functions->check_acl($manager_account_lid,'edit_users')) && ($this->functions->check_acl($manager_account_lid,'edit_sambausers_attributes')))
			{
				$disabled = 'disabled';
				$disabled_samba = '';
			}
			// SOMENTE ALTERAÇÃO DE TELEFONE
			if ((!$this->functions->check_acl($manager_account_lid,'edit_users')) && ($this->functions->check_acl($manager_account_lid,'edit_users_phonenumber')))
			{
				$disabled = 'disabled';
				$disabled_phonenumber = '';
			}
			// SOMENTE GRUPOS
			if ((!$this->functions->check_acl($manager_account_lid,'edit_users')) && ($this->functions->check_acl($manager_account_lid,'edit_groups')))
			{
				$disabled = 'disabled';
				$disabled_group = '';
			}
			// TOTAIS MENOS O SAMBA
			if (($this->functions->check_acl($manager_account_lid,'edit_users')) && (!$this->functions->check_acl($manager_account_lid,'edit_sambausers_attributes')))
			{
				$disabled = '';
				$disabled_password = '';
				$disabled_samba = 'disabled';
				$disabled_group = '';
			}
			// TOTAIS
			elseif ($this->functions->check_acl($manager_account_lid,'edit_users'))
			{
				$disabled = '';
				$disabled_password = '';
				$disabled_samba = '';
				$disabled_phonenumber = '';
				$disabled_group = '';
			}
			
			if (!$this->functions->check_acl($manager_account_lid,'change_users_quote'))
				$disabled_quote = 'readonly';
			
			if ($this->functions->check_acl($manager_account_lid,'edit_users_picture'))
			{
				$disabled_edit_photo = '';
				$display_picture = '';
			}
			// GET all infomations about the user.
			$user_info = $this->user->get_user_info($_GET['account_id']);

			// Formata o CPF
			if ($user_info['corporative_information_cpf'] != '')
			{
				if (strlen($user_info['corporative_information_cpf']) < 11)
				{
					while (strlen($user_info['corporative_information_cpf']) < 11)
					{
						$user_info['corporative_information_cpf'] = '0' . $user_info['corporative_information_cpf'];
					}
				} 
				if (strlen($user_info['corporative_information_cpf']) == 11)
				{
					// Compatível com o php4.
					//$cpf_tmp = str_split($user_info['corporative_information_cpf'], 3);
					$cpf_tmp[0] = $user_info['corporative_information_cpf'][0] . $user_info['corporative_information_cpf'][1] . $user_info['corporative_information_cpf'][2]; 
					$cpf_tmp[1] = $user_info['corporative_information_cpf'][3] . $user_info['corporative_information_cpf'][4] . $user_info['corporative_information_cpf'][5];
					$cpf_tmp[2] = $user_info['corporative_information_cpf'][6] . $user_info['corporative_information_cpf'][7] . $user_info['corporative_information_cpf'][8];
					$cpf_tmp[3] = $user_info['corporative_information_cpf'][9] . $user_info['corporative_information_cpf'][10];
					$user_info['corporative_information_cpf'] = $cpf_tmp[0] . '.' . $cpf_tmp[1] . '.' . $cpf_tmp[2] . '-' . $cpf_tmp[3];
				}
			}
			// JavaScript
			$GLOBALS['phpgw']->js->validate_file("jscode","users","expressoAdmin");
			$GLOBALS['phpgw']->js->set_onload("get_available_groups(document.forms[0].context.value);");
			$GLOBALS['phpgw']->js->set_onload("get_available_maillists(document.forms[0].context.value);");
			$GLOBALS['phpgw']->js->set_onload("use_samba_attrs('".$user_info['sambaUser']."');");
			
			// Seta header.
			unset($GLOBALS['phpgw_info']['flags']['noheader']);
			unset($GLOBALS['phpgw_info']['flags']['nonavbar']);

			$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['expressoAdmin']['title'].' - '.lang('Edit User');
			$GLOBALS['phpgw']->common->phpgw_header();

			// Seta templates.
			$t = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
			$t->set_file(array("body" => "accounts_form.tpl"));
			$t->set_block('body','main');
							
			foreach ($contexts as $index=>$context)
				$combo_manager_org .= $this->functions->get_organizations($context, $user_info['context']);
			$combo_all_orgs = $this->functions->get_organizations($GLOBALS['phpgw_info']['server']['ldap_context'], $user_info['context'], true, true, true);			

			// GROUPS.
			if (count($user_info['groups_info']) > 0)
			{
				foreach ($user_info['groups_info'] as $group)
				{
					$array_groups[$group['gidnumber']] = $group['cn'].'('.$group['uid'].')';
				}
				natcasesort($array_groups);
				foreach ($array_groups as $gidnumber=>$cn)
				{
                                        //Não foi possível encontrar o grupo do usuário, portanto excluimos o usuário(não tem inserir no grupo do Ldap se não possui grupo)
					if (is_null($user_info['groups_ldap'][$gidnumber]))
					{
                                                $this->db_functions->remove_user2group($gidnumber, $_GET['account_id']);
						if ($alert_warning == '')
							$alert_warning = lang("the expressoadmin corrected the following inconsistencies") . ":\\n";
						$alert_warning .= lang("user excluded because the group do not was found") . ":\\n$cn - gidnumber: $gidnumber.";
					}
					else                                               
						$ea_select_user_groups_options .= "<option value=" . $gidnumber . ">" . $cn . "</option>";
					
					if ($gidnumber == $user_info['gidnumber'])
					{
						$ea_combo_primary_user_group_options .= "<option value=" . $gidnumber . " selected>" . $cn . "</option>";
					}
					else
					{
						$ea_combo_primary_user_group_options .= "<option value=" . $gidnumber . ">" . $cn . "</option>";
					}
				}
                                
				// O memberUid do usuário está somente no Ldap.
				$groups_db = array_flip($user_info['groups']);
				foreach ($user_info['groups_ldap'] as $gidnumber=>$cn)
				{
					if (is_null($groups_db[$gidnumber]))
					{
						/*
						$this->ldap_functions->remove_user2group($gidnumber, $user_info['uid']);
						if ($alert_warning == '')
							$alert_warning = "O expressoAdmin corrigiu as seguintes inconsistências:\\n";
						$alert_warning .= "Removido atributo memberUid do usuário do grupo $cn.\\n";
						*/
						$ea_select_user_groups_options .= "<option value=" . $gidnumber . ">" . $cn . " [".lang('only on ldap')."]</option>";
					}
				}
			}
			
			// MAILLISTS
			if (count($user_info['maillists_info']) > 0)
			{
				foreach ($user_info['maillists_info'] as $maillist)
				{
					$array_maillist[$maillist['uid']] = $maillist['uid'] . "  (" . $maillist['mail'] . ") ";
				}
				natcasesort($array_maillist);
				foreach ($array_maillist as $uid=>$option)
				{
					$ea_select_user_maillists_options .= "<option value=" . $uid . ">" . $option.'('.$uid . ")</option>";
				}
			}
			
			// APPS.
			if ($disabled == 'disabled')
				$apps = $this->functions->make_list_app($manager_account_lid, $user_info['apps'], 'disabled');
			else
				$apps = $this->functions->make_list_app($manager_account_lid, $user_info['apps']);
			
			//PHOTO
			if ($user_info['photo_exist'])
			{
				$photo_bin = "./index.php?menuaction=expressoAdmin.uiaccounts.show_photo&uidNumber=".$_GET['account_id'];
			}
			else
			{
				$photo_bin = $GLOBALS['phpgw_info']['server']['webserver_url'] . '/expressoAdmin/templates/default/images/photo_celepar.png';
				$disabled_delete_photo = 'disabled';
			}

			// Cria combo de dominios do samba
			if ($this->current_config['expressoAdmin_samba_support'] == 'true')
			{
				$a_sambadomains = $this->db_functions->get_sambadomains_list();
				$sambadomainname_options = '';
				if (count($a_sambadomains))
				{
					foreach ($a_sambadomains as $a_sambadomain)
					{
						if ($a_sambadomain['samba_domain_sid'] == $user_info['sambasid'])
							$sambadomainname_options .= "<option value='" . $a_sambadomain['samba_domain_sid'] . "' SELECTED>" . $a_sambadomain['samba_domain_name'] . "</option>";
						else
							$sambadomainname_options .= "<option value='" . $a_sambadomain['samba_domain_sid'] . "'>" . $a_sambadomain['samba_domain_name'] . "</option>";
					}
				}
			}
			
			// Mail Alternate & Forwarding
			if (is_array($user_info['mailalternateaddress']))
			{
				for ($i = 0; $i < $user_info['mailalternateaddress']['count']; ++$i)
				{
					if ($i > 0)
						$input_mailalternateaddress_fields .= '<br>';
					$input_mailalternateaddress_fields .= '<input type="text" name="mailalternateaddress[]" id="mailalternateaddress" autocomplete="off" value="'.$user_info['mailalternateaddress'][$i].'" {disabled} size=30>';
				}
			}
			else
			{
				$input_mailalternateaddress_fields = '<input type="text" name="mailalternateaddress[]" id="mailalternateaddress" autocomplete="off" value="" {disabled} size=30>';
			}

			if (is_array($user_info['mailforwardingaddress']))
			{
				for ($i = 0; $i < $user_info['mailforwardingaddress']['count']; ++$i)
				{
					if ($i > 0)
						$input_mailforwardingaddress_fields .= '<br>';
					$input_mailforwardingaddress_fields .= '<input type="text" name="mailforwardingaddress[]" id="mailforwardingaddress" autocomplete="off" value="'.$user_info['mailforwardingaddress'][$i].'" {disabled} size=30>';
				}
			}
			else
			{
				$input_mailforwardingaddress_fields = '<input type="text" name="mailforwardingaddress[]" id="mailforwardingaddress" autocomplete="off" value="" {disabled} size=30>';
			}

			$start_coment = "<!--";
			$end_coment = "-->";		
			$time_to_expire = $GLOBALS['phpgw_info']['server']['time_to_account_expires'];
			if(isset($time_to_expire)) {
				if ($GLOBALS['phpgw']->session->get_last_access_on_history($user_info["uidnumber"])+($time_to_expire*86400) < time())
				{
					$start_coment = "";
					$end_coment = "";
				}
			}

			if ($alert_warning != '')
				$alert_warning = "alert('". $alert_warning ."')";
			
			$var = Array(
				'uidnumber'					=> $_GET['account_id'],
				'type'						=> 'edit_user',
				'photo_exist'				=> $user_info['photo_exist'],
				'departmentnumber'			=> $user_info['departmentnumber'],
				'user_context'				=> $user_info['context'],
				
				'row_on'					=> "#DDDDDD",
				'row_off'					=> "#EEEEEE",
				'color_bg1'					=> "#E8F0F0",
				'action'					=> $GLOBALS['phpgw']->link('/index.php','menuaction=expressoAdmin.uiaccounts.validate_user_data_edit'),
				'back_url'					=> './index.php?menuaction=expressoAdmin.uiaccounts.list_users',
				'disabled'					=> $disabled,
				'disabled_password'			=> $disabled_password,
				'disabled_samba'			=> $disabled_samba,
				'changequote_disabled'		=> $disabled_quote,
				'disable_phonenumber'		=> $disabled_phonenumber,
				'disable_group'				=> $disabled_group,
				'lang_account_expired' 	=> lang('lang_account_expired'),
				'lang_yes'						=> lang('yes'),
				'lang_no'						=> lang('no'),
				'start_coment_expired'						=> $start_coment,
				'end_coment_expired'						=> $end_coment,	
				
				// Display ABAS
				'display_corporative_information'=> $this->functions->check_acl($manager_account_lid,'manipulate_corporative_information') ? '' : 'none',
				'display_applications'		=> $this->functions->check_acl($manager_account_lid,'display_applications') ? '' : 'none',
				'display_emaillists'		=> $this->functions->check_acl($manager_account_lid,'display_emaillists') ? '' : 'none',
				'display_groups'			=> $this->functions->check_acl($manager_account_lid,'display_groups') ? '' : 'none',
				'display_emailconfig'		=> $this->functions->check_acl($manager_account_lid,'display_emailconfig') ? '' : 'none',
				
				// First ABA
				'alert_warning'					=> "$alert_warning",
				'display_input_account_lid'		=> 'display:none',
				'sectors'						=> $combo_manager_org,
				'combo_organizations'			=> $combo_manager_org,
				'combo_all_orgs'				=> $combo_all_orgs,
				'uid'							=> $user_info['uid'],
				'givenname'						=> $user_info['givenname'],
				'mail1'							=> $user_info['mail'],
				'sn'							=> $user_info['sn'],
				'telephonenumber'				=> $user_info['telephonenumber'],
				'photo_bin'						=> $photo_bin,
				'disabled_edit_photo'			=> $disabled_edit_photo,
				'display_picture'				=> $display_picture,
				'display_tr_default_password'	=> $this->functions->check_acl($manager_account_lid,'set_user_default_password') ? '' : 'none',
				'passwd_expired_checked'		=> $user_info['passwd_expired'] == '0' ? 'CHECKED' : '',
				'changepassword_checked'		=> $user_info['changepassword'] == '1' ? 'CHECKED' : '',
				'phpgwaccountstatus_checked'	=> $user_info['phpgwaccountstatus'] == 'A' ? 'CHECKED' : '',
				'phpgwaccountvisible_checked'	=> $user_info['phpgwaccountvisible'] == '-1' ? 'CHECKED' : '',

				// Corporative Information
				'corporative_information_employeenumber' => $user_info['corporative_information_employeenumber'],
				'corporative_information_cpf'			=> $user_info['corporative_information_cpf'],
				'corporative_information_rg'			=> $user_info['corporative_information_rg'],
				'corporative_information_rguf'			=> $user_info['corporative_information_rguf'],
				'corporative_information_description'	=> $user_info['corporative_information_description'],
				
				//MAIL
				'disabled_quota_used'		=> 'disabled',
				'accountstatus_checked'		=> $user_info['accountstatus'] == 'active' ? 'CHECKED' : '',
				'mail'						=> $user_info['mail'],
				'input_mailalternateaddress_fields'	=> $input_mailalternateaddress_fields,
				'input_mailforwardingaddress_fields'=> $input_mailforwardingaddress_fields,
				'deliverymode_checked'		=> $user_info['deliverymode'] == 'forwardOnly' ? 'CHECKED' : '',
				'mailquota'					=> $user_info['mailquota'] == '-1' ? '' : $user_info['mailquota'],
				'mailquota_used'			=> $user_info['mailquota_used'] == '-1' ? lang('without quota') : $user_info['mailquota_used'],

				//Third ABA
				'ea_select_user_groups_options'	=> $ea_select_user_groups_options,
				'ea_combo_primary_user_group_options'	=> $ea_combo_primary_user_group_options,
				
				//Fourd ABA
				'ea_select_user_maillists_options'  => $ea_select_user_maillists_options,
								
				//Five ABA
				'apps'	=> $apps,

				//SAMBA ABA
				'userSamba'					=> $user_info['sambaUser'],
				'sambadomainname_options'	=> $sambadomainname_options,
				'use_attrs_samba_checked'	=> $user_info['sambaUser'] ? 'CHECKED' : '',
				'active_user_selected'		=> $user_info['sambaaccflags'] == '[U          ]' ? 'selected' : '',
				'desactive_user_selected'	=> $user_info['sambaaccflags'] == '[DU         ]' ? 'selected' : '',
				'sambalogonscript'			=> $user_info['sambalogonscript'],
				'sambahomedirectory'		=> $user_info['homedirectory'],
				'defaultLogonScript'		=> $this->current_config['expressoAdmin_defaultLogonScript'],
				'use_suggestion_in_logon_script' => $this->current_config['expressoAdmin_defaultLogonScript'] == '' ? 'true' : 'false'
			);
			$t->set_var($var);
			$t->set_var($this->functions->make_dinamic_lang($t, 'main'));
			
			// Devo mostrar aba SAMBA ??
			if ( ($this->current_config['expressoAdmin_samba_support'] == 'true') && ($this->functions->check_acl($manager_account_lid,'edit_sambausers_attributes')) )
				$t->set_var('display_samba_suport', '');
			else
				$t->set_var('display_samba_suport', 'none');
			
			$t->pfp('out','body');			
		}
		
		function row_action($action,$type,$account_id)
		{
			return '<a href="'.$GLOBALS['phpgw']->link('/index.php',Array(
				'menuaction' => 'expressoAdmin.uiaccounts.'.$action.'_'.$type,
				'account_id' => $account_id
			)).'"> '.lang($action).' </a>';
		}

		function css()
		{
			$appCSS = 
			'th.activetab
			{
				color:#000000;
				background-color:#D3DCE3;
				border-top-width : 1px;
				border-top-style : solid;
				border-top-color : Black;
				border-left-width : 1px;
				border-left-style : solid;
				border-left-color : Black;
				border-right-width : 1px;
				border-right-style : solid;
				border-right-color : Black;
				font-size: 12px;
				font-family: Tahoma, Arial, Helvetica, sans-serif;
			}
			
			th.inactivetab
			{
				color:#000000;
				background-color:#E8F0F0;
				border-bottom-width : 1px;
				border-bottom-style : solid;
				border-bottom-color : Black;
				font-size: 12px;
				font-family: Tahoma, Arial, Helvetica, sans-serif;				
			}
			
			.td_left {border-left:1px solid Gray; border-top:1px solid Gray; border-bottom:1px solid Gray;}
			.td_right {border-right:1px solid Gray; border-top:1px solid Gray; border-bottom:1px solid Gray;}
			
			div.activetab{ display:inline; }
			div.inactivetab{ display:none; }';
			
			return $appCSS;
		}

		function show_photo()
		{
			$uidNumber = $_GET['uidNumber'];
			$photo = $this->get_photo($uidNumber); 
			
        	if ($photo)
			{
        		header("Content-Type: image/jpeg");
				$width = imagesx($photo);
				$height = imagesy($photo);
        	    $twidth = 80;
            	$theight = 106;
				$small_photo = imagecreatetruecolor ($twidth, $theight);
				imagecopyresampled($small_photo, $photo, 0, 0, 0, 0,$twidth, $theight, $width, $height);
				imagejpeg($small_photo,"",100);
				return;
			}
		}
		
		function get_photo($uidNumber)
		{
			$ldap_conn = $GLOBALS['phpgw']->common->ldapConnect();
			$filter="(&(phpgwAccountType=u)(uidNumber=".$uidNumber."))";
			$justthese = array("jpegphoto");

			$search = ldap_search($ldap_conn, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
			$entry = ldap_first_entry($ldap_conn, $search);
			$jpeg_data = ldap_get_values_len($ldap_conn, $entry, "jpegphoto");
			$jpegphoto = imagecreatefromstring($jpeg_data[0]);
			return $jpegphoto;
		}
		
		function show_access_log()
		{	
			$account_id = $_GET['account_id']; 
			
			$manager_account_lid = $GLOBALS['phpgw']->accounts->data['account_lid'];
			$tmp = $this->functions->read_acl($manager_account_lid);
			$manager_context = $tmp[0]['context'];
			
			// Verifica se tem acesso a este modulo
			if ((!$this->functions->check_acl($manager_account_lid,'edit_users')) && (!$this->functions->check_acl($manager_account_lid,'change_users_password')))
			{
				$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/expressoAdmin/inc/access_denied.php'));
			}

			// Seta header.
			unset($GLOBALS['phpgw_info']['flags']['noheader']);
			unset($GLOBALS['phpgw_info']['flags']['nonavbar']);

			$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['expressoAdmin']['title'].' - '.lang('Access Log');
			$GLOBALS['phpgw']->common->phpgw_header();

			// Seta templates.
			$t = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
			$t->set_file(array("body" => "accesslog.tpl"));
			$t->set_block('body','main');
			$t->set_block('body','row','row');

			// GET access log from the user.
			$GLOBALS['phpgw']->db->limit_query("select loginid,ip,li,lo,account_id,sessionid from phpgw_access_log WHERE account_id=".$account_id." order by li desc",$start,__LINE__,__FILE__);
			while ($GLOBALS['phpgw']->db->next_record())
			{
				$records[] = array(
					'loginid'    => $GLOBALS['phpgw']->db->f('loginid'),
					'ip'         => $GLOBALS['phpgw']->db->f('ip'),
					'li'         => $GLOBALS['phpgw']->db->f('li'),
					'lo'         => $GLOBALS['phpgw']->db->f('lo'),
					'account_id' => $GLOBALS['phpgw']->db->f('account_id'),
					'sessionid'  => $GLOBALS['phpgw']->db->f('sessionid')
				);
			}

			// Seta as vcariaveis
			while (is_array($records) && list(,$record) = each($records))
			{
				$var = array(
					'row_loginid' => $record['loginid'],
					'row_ip'      => $record['ip'],
					'row_li'      => date("d/m/Y - H:i:s", $record['li']),
					'row_lo'      => $record['lo'] == 0 ? 0 : date("d/m/Y - H:i:s", $record['lo'])
				);
				$t->set_var($var);
				$t->fp('rows','row',True);
			}

			$var = Array(
				'th_bg'			=> $GLOBALS['phpgw_info']['theme']['th_bg'],
				'back_url'		=> "./index.php?menuaction=expressoAdmin.uiaccounts.edit_user&account_id=$account_id",
			);
			$t->set_var($var);
			$t->set_var($this->functions->make_dinamic_lang($t, 'body'));
			$t->pfp('out','body');
		}
	}
?>
