<?php
	/***********************************************************************************\
	* Expresso Administração                 										   *
	* by Joao Alfredo Knopik Junior (joao.alfredo@gmail.com, jakjr@celepar.pr.gov.br)  *
	* ---------------------------------------------------------------------------------*
	*  This program is free software; you can redistribute it and/or modify it		   *
	*  under the terms of the GNU General Public License as published by the		   *
	*  Free Software Foundation; either version 2 of the License, or (at your		   *
	*  option) any later version.													   *
	\***********************************************************************************/

	class uicomputers
	{
		var $public_functions = array
		(
			'list_computers'				=> True,
			'add_computer'					=> True,
			'validade_computers_data_add'	=> True,
			'edit_computer'					=> True,
			'validade_computers_data_edit'	=> True,
			'delete_computer'				=> True,
			'css'							=> True
		);

		var $bo;
		var $nextmatchs;
		var $functions;
		var $current_config;
		var $db_functions;
			
		function uicomputers()
		{
			$this->nextmatchs = CreateObject('phpgwapi.nextmatchs');
			$this->bo = CreateObject('expressoAdmin1_2.bocomputers');
			$this->so = $this->bo->so;
			$this->functions = $this->bo->functions;
			$this->db_functions = $this->bo->db_functions;
			
			$c = CreateObject('phpgwapi.config','expressoAdmin1_2');
			$c->read_repository();
			$this->current_config = $c->config_data;

			if(!@is_object($GLOBALS['phpgw']->js))
			{
				$GLOBALS['phpgw']->js = CreateObject('phpgwapi.javascript');
			}
			$GLOBALS['phpgw']->js->validate_file('jscode','computers','expressoAdmin1_2');#diretorio, arquivo.js, aplicacao
		}
		
		function list_computers()
		{
			$manager_lid = $GLOBALS['phpgw']->accounts->data['account_lid'];
			$manager_acl = $this->functions->read_acl($manager_lid);
			$manager_contexts = $manager_acl['contexts'];
			foreach ($manager_acl['contexts_display'] as $index=>$tmp_context)
			{
				$context_display .= '<br>'.$tmp_context;
			}
	
			// Verifica se tem acesso a este modulo
			if (!$this->functions->check_acl($manager_lid,'list_computers'))
			{
				$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/expressoAdmin1_2/inc/access_denied.php'));
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
			
			$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['expressoAdmin1_2']['title'].' - '.lang('Computers');
			$GLOBALS['phpgw']->common->phpgw_header();

			$p = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
			$p->set_file(array('computers'   => 'computers.tpl'));
			$p->set_block('computers','body','body');
			$p->set_block('computers','row','row');
			$p->set_block('computers','row_empty','row_empty');

			// Seta as variaveis padroes.
			$var = Array(
				'th_bg'						=> $GLOBALS['phpgw_info']['theme']['th_bg'],
				'add_action'				=> $GLOBALS['phpgw']->link('/index.php','menuaction=expressoAdmin1_2.uicomputers.add_computer'),
				'add_computers_disabled'	=> $this->functions->check_acl($manager_lid,'create_computers') ? '' : 'display:none',
				'back_url'					=> $GLOBALS['phpgw']->link('/expressoAdmin1_2/index.php'),
				'context_display'			=> $context_display,
			);
			$p->set_var($var);
			$p->set_var($this->functions->make_dinamic_lang($p, 'body'));
			
			// Save query
			$p->set_var('query', $GLOBALS['query']);
			
			//Admin make a search
			if ($GLOBALS['query'] != '')
			{
				$computers_info = $this->functions->get_list('computers', $GLOBALS['query'], $manager_contexts);
			}
			
			if (!count($computers_info) && $GLOBALS['query'] != '')
			{
				$p->set_var('message',lang('No matches found'));
				$p->parse('rows','row_empty',True);
			}
			else if (count($computers_info))
			{
				if ($this->functions->check_acl($manager_lid,'edit_computers'))
				{
					$can_edit = True;
				}
				if ($this->functions->check_acl($manager_lid,'delete_computers'))
				{
					$can_delete = True;
				}

				foreach($computers_info as $computer)
				{
					$tr_color = $this->nextmatchs->alternate_row_color($tr_color);
					$var = array(
						'tr_color'			=> $tr_color,
						'row_cn'			=> $computer['cn'],
						'row_description'	=> $computer['description']
					);
					$p->set_var($var);

					if ($can_edit)
					{
						$p->set_var('edit_link',$this->row_action('edit','computer',$computer['uidNumber']));
					}
					else
					{
						$p->set_var('edit_link','&nbsp;');
					}

					if ($can_delete)
					{
						$p->set_var('delete_link',$this->row_action('delete','computer',$computer['uidNumber']));
					}
					else
					{
						$p->set_var('delete_link','&nbsp;');
					}

					$p->fp('rows','row',True);
				}
			}
			$p->set_var($var);
			$p->pfp('out','body');
		}
		
		function add_computer()
		{
			$manager_lid = $GLOBALS['phpgw']->accounts->data['account_lid'];
			$manager_acl = $this->functions->read_acl($manager_lid);
			$manager_contexts = $manager_acl['contexts'];
			
			// Verifica se tem acesso a este modulo
			if (!$this->functions->check_acl($manager_lid,'create_computers'))
			{
				$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/expressoAdmin1_2/inc/access_denied.php'));
			}

			unset($GLOBALS['phpgw_info']['flags']['noheader']);
			unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
			$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['expressoAdmin1_2']['title'].' - '.lang('Create Computers');
			$GLOBALS['phpgw']->common->phpgw_header();
			
			// Set o template
			$p = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
			$p->set_file(Array('create_email_list' => 'computers_form.tpl'));
			$p->set_block('create_email_list','body','body');

			// Inclue na combo, os usuarios previamente selecionados no caso de um erro na validacao dos dados.
			$users_in_list = '';
			if (count($_POST['email_list_users']) != 0)
			{			
				foreach ($_POST['email_list_users'] as $user_data)
				{
					$tmp			= explode("|", $user_data);
					$user_cn		= $tmp[0];
					$user_email	= $tmp[1];
					$users_in_list .= '<option value="'.$user_data.'">'.$user_cn. ' (' . $user_email . ')' . '</option>';
				}
			}
			
			foreach ($manager_contexts as $index=>$context)
				$sectors .= $this->functions->get_organizations($context);

			// Cria combo de dominio samba
			if ($this->current_config['expressoAdmin_samba_support'] == 'true')
			{
				$a_sambadomains = $this->db_functions->get_sambadomains_list();
				$sambadomainname_options = '';
				if (count($a_sambadomains))
				{
					foreach ($a_sambadomains as $a_sambadomain)
					{
						$sambadomainname_options .= "<option value='" . $a_sambadomain['samba_domain_sid'] . "'>" . $a_sambadomain['samba_domain_name'] . "</option>";
					}
				}
			}

			// Seta variaveis utilizadas pelo tpl.
			$var = Array(
				// LINKS
				'back_url'						=> $GLOBALS['phpgw']->link('/index.php','menuaction=expressoAdmin1_2.uicomputers.list_computers'),
				'form_action'					=> $GLOBALS['phpgw']->link('/index.php','menuaction=expressoAdmin1_2.uicomputers.validade_computers_data_add'),

				'combo_sectors'					=> $sectors,			
				'row_on'						=> "#DDDDDD",
				'row_off'						=> "#EEEEEE",
				'color_bg1'						=> "#E8F0F0",

				'display_tr_computer_password'	=> $_POST['sambaAcctFlags'] == '[I          ]' ? '' : 'display:none',

				'sambadomainname_options'		=> $sambadomainname_options,

				// Retorna os valores, quando da um erro na validação.
				'computer_cn'					=> $_POST['computer_cn'],
				'computer_description'			=> $_POST['computer_description'],
								
				// Quando for edit, passa o id do grupo, quando for para criar um grupo novo, passa vazio.
				'hidden_vars'					=> '<input type="hidden" name="uidnumber" value="">',
				'error_messages'				=> $_POST['error_messages'] == '' ? '' : "<script type='text/javascript'>alert('".$_POST['error_messages']."')</script>"
			);
			$p->set_var($var);
			$p->set_var($this->functions->make_dinamic_lang($p, 'body'));
			
			if ($_POST['sambaAcctFlags'] != '')
			{
				switch($_POST['sambaAcctFlags'])
				{
					case '[W          ]':
						$p->set_var('active_workstation_selected', 'selected');
						break;
					case '[DW         ]':
						$p->set_var('desactive_workstation_selected', 'selected');
						break;
					case '[I          ]':
						$p->set_var('trust_account_selected', 'selected');
						break;
					case '[S          ]':
						$p->set_var('server_selected', 'selected');
						break;
				}	
			}
			
			$p->pfp('out','create_email_list');
		}
		
		function validade_computers_data_add()
		{
			if (($_POST['sambaAcctFlags'] == '[I          ]') && ($_POST['computer_password'] == ''))
			{
				$_POST['error_messages'] = lang('Computer password is empty.');
				ExecMethod('expressoAdmin1_2.uicomputers.add_computer');
				return;
			}

			$computer_cn = $_POST['computer_cn'];
			
			// Verifica se o uid do computador nao esta vazio.
			if ($computer_cn == '')
			{
				$_POST['error_messages'] = lang('Computer UID is empty.');
				ExecMethod('expressoAdmin1_2.uicomputers.add_computer');
				return;
			}
			
			// Verifica se o nome do computaor existe no contexto atual.
			if ($this->so->exist_computer_uid($computer_cn))
			{
				$_POST['error_messages'] = lang('Computer UID already exist.');
				ExecMethod('expressoAdmin1_2.uicomputers.add_computer');
				return;
			}
			
			ExecMethod('expressoAdmin1_2.bocomputers.create_computer');
		}
		
		
		function edit_computer()
		{
			$manager_lid = $GLOBALS['phpgw']->accounts->data['account_lid'];
			$manager_acl = $this->functions->read_acl($manager_lid);
			$manager_contexts = $manager_acl['contexts'];
			
			// Verifica se tem acesso a este modulo
			if (!$this->functions->check_acl($manager_lid,'edit_computers'))
			{
				$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/expressoAdmin1_2/inc/access_denied.php'));
			}
			
			// Set o header
			unset($GLOBALS['phpgw_info']['flags']['noheader']);
			unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
			$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['expressoAdmin1_2']['title'].' - '.lang('Edit Computer');
			$GLOBALS['phpgw']->common->phpgw_header();

			// Set o template
			$p = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
			$p->set_file(Array('edit_computers' => 'computers_form.tpl'));
			$p->set_block('edit_computers','body','body');
			
			//O POST esta vazio, oq indica que precisamos recuperar os dados do computador no ldap.
			if ($_POST['try_saved'] != 'true')
			{
				$uidnumber = $_GET['uidnumber'];
				$computer_data = $this->so->get_computer_data($uidnumber);

				// Gera combo sectors
				foreach ($manager_contexts as $index=>$context)
					$sectors .= $this->functions->get_organizations($context, trim(strtolower($computer_data['context'])));

				// Cria combo de dominios do samba
				if ($this->current_config['expressoAdmin_samba_support'] == 'true')
				{
					$a_sambadomains = $this->db_functions->get_sambadomains_list();
					$sambadomainname_options = '';
					if (count($a_sambadomains))
					{
						foreach ($a_sambadomains as $a_sambadomain)
						{
							if ($a_sambadomain['samba_domain_sid'] == $computer_data['sambasid'])
								$sambadomainname_options .= "<option value='" . $a_sambadomain['samba_domain_sid'] . "' SELECTED>" . $a_sambadomain['samba_domain_name'] . "</option>";
							else
								$sambadomainname_options .= "<option value='" . $a_sambadomain['samba_domain_sid'] . "'>" . $a_sambadomain['samba_domain_name'] . "</option>";
						}
					}
				}

				// Salva valores antigos
				$var = Array(
					'uidnumber'						=> $uidnumber,
					'old_computer_cn'				=> $computer_data['computer_cn'],
					'old_computer_dn'				=> $computer_data['dn'],
					'old_computer_sambaAcctFlags'	=> $computer_data['sambaAcctFlags'],
					'old_computer_description'		=> $computer_data['computer_description'],
					'old_computer_context'			=> $computer_data['context'],
					'old_sambasid'					=> $computer_data['sambasid'],

					'row_on'						=> "#DDDDDD",
					'row_off'						=> "#EEEEEE",
					'color_bg1'						=> "#E8F0F0",
				
					'display_tr_computer_password'	=> $computer_data['sambaAcctFlags'] == '[I          ]' ? '' : 'display:none',

					'computer_cn'				=> $computer_data['computer_cn'],
					'computer_dn'				=> $computer_data['dn'],
					'computer_description'		=> $computer_data['computer_description'],
					'combo_sectors'				=> $sectors,
					'sambadomainname_options'	=> $sambadomainname_options,
					
					// LINKS
					'back_url'					=> $GLOBALS['phpgw']->link('/index.php','menuaction=expressoAdmin1_2.uicomputers.list_computers'),
					'form_action'				=> $GLOBALS['phpgw']->link('/index.php','menuaction=expressoAdmin1_2.uicomputers.validade_computers_data_edit')
				);
				$p->set_var($var);
				$p->set_var($this->functions->make_dinamic_lang($p, 'body'));
				
				if ($computer_data['sambaAcctFlags'] != '')
				{
					switch($computer_data['sambaAcctFlags'])
					{
						case '[W          ]':
							$p->set_var('active_workstation_selected', 'selected');
							break;
						case '[DW         ]':
							$p->set_var('desactive_workstation_selected', 'selected');
							break;
						case '[I          ]':
							$p->set_var('trust_account_selected', 'selected');
							break;
						case '[S          ]':
							$p->set_var('server_selected', 'selected');
							break;
					}	
				}
			}
			else // DEMAIS VEZES
			{
				// Pega combo das organizações e seleciona um dos setores em caso de um erro na validaçao dos dados.
				foreach ($manager_contexts as $index=>$context)
					$sectors .= $this->functions->get_organizations($context, trim(strtolower($_POST['sector_context'])));
				//$sectors = $this->functions->get_organizations($manager_contexts);
				
				// Cria combo de dominios do samba
				if ($this->current_config['expressoAdmin_samba_support'] == 'true')
				{
					$a_sambadomains = $this->db_functions->get_sambadomains_list();
					$sambadomainname_options = '';
					if (count($a_sambadomains))
					{
						foreach ($a_sambadomains as $a_sambadomain)
						{
							if ($a_sambadomain['samba_domain_sid'] == $_POST['sambasid'])
								$sambadomainname_options .= "<option value='" . $a_sambadomain['samba_domain_sid'] . "' SELECTED>" . $a_sambadomain['samba_domain_name'] . "</option>";
							else
								$sambadomainname_options .= "<option value='" . $a_sambadomain['samba_domain_sid'] . "'>" . $a_sambadomain['samba_domain_name'] . "</option>";
						}
					}
				}
				
				$var = Array(
					// LINKS
					'back_url'						=> $GLOBALS['phpgw']->link('/index.php','menuaction=expressoAdmin1_2.uicomputers.list_computers'),
					'form_action'					=> $GLOBALS['phpgw']->link('/index.php','menuaction=expressoAdmin1_2.uicomputers.validade_computers_data_edit'),

					'row_on'						=> "#DDDDDD",
					'row_off'						=> "#EEEEEE",
					'color_bg1'						=> "#E8F0F0",

					// Retorna os valores, quando da um erro na validação.
					'uidnumber'						=> $_POST['uidnumber'],
					'error_messages'				=> $_POST['error_messages'] == '' ? '' : "<script type='text/javascript'>alert('".$_POST['error_messages']."')</script>",

					'display_tr_computer_password'	=> $_POST['sambaAcctFlags'] == '[I          ]' ? '' : 'display:none',

					// Retorna os valores, quando da um erro na validação.
					'computer_cn'					=> $_POST['computer_cn'],
					'computer_description'			=> $_POST['computer_description'],
					'combo_sectors'					=> $sectors,
					'sambadomainname_options'		=> $sambadomainname_options,
				
					// Valores que devem ser mantidos, aqui sao referenciados como old.
					'old_computer_cn'				=> $_POST['old_computer_cn'],
					'old_computer_dn'				=> $_POST['old_computer_dn'],
					'old_computer_sambaAcctFlags'	=> $_POST['old_computer_sambaAcctFlags'],
					'old_computer_description'		=> $_POST['old_computer_description'],
					'old_computer_context'			=> $_POST['old_computer_context'],
					'old_sambasid'					=> $_POST['sambasid']
				);
				$p->set_var($var);
				$p->set_var($this->functions->make_dinamic_lang($p, 'body'));	

				if ($_POST['sambaAcctFlags'] != '')
				{
					switch($_POST['sambaAcctFlags'])
					{
						case '[W          ]':
							$p->set_var('active_workstation_selected', 'selected');
							break;
						case '[DW         ]':
							$p->set_var('desactive_workstation_selected', 'selected');
							break;
						case '[I          ]':
							$p->set_var('trust_account_selected', 'selected');
							break;
						case '[S          ]':
							$p->set_var('server_selected', 'selected');
							break;
					}	
				}
			}
			$p->pfp('out','edit_computers');
		}
		
		function validade_computers_data_edit()
		{
			// Verifica se o uid do computador nao esta vazio.
			if ($_POST['computer_cn'] == '')
			{
				$_POST['error_messages'] = lang('Computer UID is empty.');
				ExecMethod('expressoAdmin1_2.uicomputers.edit_computer');
				return;
			}

			ExecMethod('expressoAdmin1_2.bocomputers.save_computer');
		}
		
		function delete_computer()
		{
			$account_lid = $GLOBALS['phpgw']->accounts->data['account_lid'];
			$acl = $this->functions->read_acl($account_lid);
			$manager_context = $acl[0]['context'];
			
			// Verifica se tem acesso a este modulo
			if (!$this->functions->check_acl($account_lid,'delete_computers'))
			{
				$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/expressoAdmin1_2/inc/access_denied.php'));
			}
			
			unset($GLOBALS['phpgw_info']['flags']['noheader']);
			unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
			$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['expressoAdmin1_2']['title'].' - '.lang('Delete Computer');
			$GLOBALS['phpgw']->common->phpgw_header();

			// Set o template
			$p = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
			$p->set_file(Array('delete_computer' => 'computers_delete.tpl'));
			$p->set_block('delete_computer','body','body');
			
			// Get group data 
			$uidnumber		= $_GET['uidnumber'];
			$computer_data	= $this->so->get_computer_data($uidnumber, $manager_context);
			
			// Seta variaveis utilizadas pelo tpl.
			$var = Array(
				'color_bg1'					=> "#E8F0F0",
				'dn'						=> $email_list_data['dn'],
				'computer_cn'				=> $computer_data['computer_cn'],
				'computer_dn'				=> $computer_data['dn'],
				'computer_description'		=> $computer_data['computer_description'],
				'back_url'					=> $GLOBALS['phpgw']->link('/index.php','menuaction=expressoAdmin1_2.uicomputers.list_computers'),
				'delete_action'				=> $GLOBALS['phpgw']->link('/index.php','menuaction=expressoAdmin1_2.bocomputers.delete_computer'),
			);
			$p->set_var($var);
			$p->set_var($this->functions->make_dinamic_lang($p, 'body'));
			$p->pfp('out','delete_computer');
		}
		
		function row_action($action,$type,$uidNumber)
		{
			return '<a href="'.$GLOBALS['phpgw']->link('/index.php',Array(
				'menuaction'		=> 'expressoAdmin1_2.uicomputers.'.$action.'_'.$type,
				'uidnumber'			=> $uidNumber
			)).'"> '.lang($action).' </a>';
		}
		
		function css()
		{
			$appCSS = '';
			return $appCSS;
		}
	}
?>
