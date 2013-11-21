<?php
	/************************************************************************************\
	* Expresso Administração                 										     *
	* by Joao Alfredo Knopik Junior (joao.alfredo@gmail.com, jakjr@celepar.pr.gov.br)  	 *
	* -----------------------------------------------------------------------------------*
	*  This program is free software; you can redistribute it and/or modify it			 *
	*  under the terms of the GNU General Public License as published by the			 *
	*  Free Software Foundation; either version 2 of the License, or (at your			 *
	*  option) any later version.														 *
	\************************************************************************************/

	class uidomains
	{
		var $public_functions = array
		(
			'list_domains'	=> True,
			'add'			=> True,
			'delete'		=> True,
			'validate_data'	=> True
		);

		var $db;
		var $ldap_functions;
		var $functions;
		var $nextmatchs;
			
		function uidomains()
		{
			$this->db = CreateObject('expressoAdmin1_2.db_functions');
			$this->ldap_functions = CreateObject('expressoAdmin1_2.ldap_functions');
			$this->functions = CreateObject('expressoAdmin1_2.functions');
			$this->nextmatchs = CreateObject('phpgwapi.nextmatchs');
		}
		
		function list_domains()
		{
			$account_lid = $GLOBALS['phpgw']->accounts->data['account_lid'];
			$tmp = $this->functions->read_acl($account_lid);
			$manager_context = $tmp[0]['context'];
			$context_display = $tmp[0]['context_display'];
			
			// Verifica se o administrador tem acesso.
			if (!$this->functions->check_acl($account_lid,'edit_sambadomains'))
			{
				$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/expressoAdmin1_2/inc/access_denied.php'));
			}
			
			unset($GLOBALS['phpgw_info']['flags']['noheader']);
			unset($GLOBALS['phpgw_info']['flags']['nonavbar']);

			$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['expressoAdmin1_2']['title'].' - '.lang('Samba Domains');
			$GLOBALS['phpgw']->common->phpgw_header();

			$p = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
			$p->set_file(array('domains' => 'domains.tpl'));
			$p->set_block('domains','list','list');
			$p->set_block('domains','row','row');
			$p->set_block('domains','row_empty','row_empty');
			
			$sambadomains_info = $this->db->get_sambadomains_list();
			//_debug_array($sambadomains_info);
			
			$var = Array(
				'th_bg'					=> $GLOBALS['phpgw_info']['theme']['th_bg'],
				'back_url'				=> $GLOBALS['phpgw']->link('/expressoAdmin1_2/index.php')
			);
			$p->set_var($var);
			$p->set_var($this->functions->make_dinamic_lang($p, 'list'));

			if (!count($sambadomains_info))
			{
				$p->set_var('message',lang('No matches found'));
			}
			else
			{
				foreach($sambadomains_info as $domains)
				{
					$tr_color = $this->nextmatchs->alternate_row_color($tr_color);
					
					$var = Array(
						'tr_color'		=> $tr_color,
						'sambadomainname'	=> $domains['samba_domain_name'],
						'sambaSID'			=> $domains['samba_domain_sid'],
						'delete_link'		=> $this->row_action('delete',$domains['samba_domain_name'])
					);
					$p->set_var($var);
					$p->fp('rows','row',True);
				}
			}
			
			$var = Array(
				'action'	=> $GLOBALS['phpgw']->link('/index.php','menuaction=expressoAdmin1_2.uidomains.add'),
				'input_add' => '<input type="submit" value="' . lang('Add Samba Domains') . '">'
			);
			$p->set_var($var);
			$p->parse('rows','row_empty',True);
			$p->pfp('out','list');
		}

		
		function add()
		{
			$account_lid = $GLOBALS['phpgw']->accounts->data['account_lid'];
			$acl = $this->functions->read_acl($account_lid);
			$manager_context = $acl[0]['context'];
			
			$context = $_GET['context'];
			
			// Verifica se tem acesso a este modulo
			if (!$this->functions->check_acl($account_lid,'edit_sambadomains'))
			{
				$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/expressoAdmin1_2/inc/access_denied.php'));
			}

			// Pega combo das organizações e seleciona, caso seja um post, o setor que o usuario selecionou.
			$organizations = $this->functions->get_sectors($_POST['context'], false, false);
			
			unset($GLOBALS['phpgw_info']['flags']['noheader']);
			unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
			$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['expressoAdmin1_2']['title'].' - '.lang('Add Samba Domain');
			$GLOBALS['phpgw']->common->phpgw_header();
			
			// Set o template
			$p = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
			$p->set_file(Array('create_sambadomains' => 'domains_form.tpl'));
			$p->set_block('create_sambadomains','list','list');
						
			// Seta variaveis utilizadas pelo tpl.
			$var = Array(
				'action'		=> $GLOBALS['phpgw']->link('/index.php','menuaction=expressoAdmin1_2.uidomains.validate_data'),
				'back_url'		=> $GLOBALS['phpgw']->link('/index.php','menuaction=expressoAdmin1_2.uidomains.list_domains'),
				'row_on'		=> "#DDDDDD",
				'row_off'		=> "#EEEEEE",
				'color_bg1'		=> "#E8F0F0",
				'organizations'		=> $organizations,
				'sambadomainname'	=> $_POST['sambadomainname'],
				'sambasid'			=> $_POST['sambasid'],
				
				'error_messages' => $_POST['error_messages'] == '' ? '' : "<script type='text/javascript'>alert('".$_POST['error_messages']."')</script>",
			);
			$p->set_var($var);
			$p->set_var($this->functions->make_dinamic_lang($p, 'list'));
			
			$p->pfp('out','create_sambadomains');
		}
		
		function validate_data()
		{
			if ($_POST['sambadomainname'] == '')
			{
				$_POST['error_messages'] = lang('Samba domains name is empty') . '.';
				ExecMethod('expressoAdmin1_2.uidomains.add');
				return;
			}

			if ($_POST['sambasid'] == '')
			{
				$_POST['error_messages'] = lang('Samba SID is empty') . '.';
				ExecMethod('expressoAdmin1_2.uidomains.add');
				return;
			}

			// Verifica se o name do dominio está sendo usado.
			if ($this->db->exist_domain_name_sid($_POST['sambadomainname'], $_POST['sambasid']))
			{
				$_POST['error_messages'] = lang('Samba domain name or SID already exist') . '.';
				ExecMethod('expressoAdmin1_2.uidomains.add');
				return;
			}

			if (!$this->ldap_functions->exist_domain_name_sid($_POST['sambadomainname'], $_POST['sambasid']))
			{
				$return = $this->ldap_functions->add_sambadomain(strtoupper($_POST['sambadomainname']), $_POST['sambasid'], $_POST['context']);
				if (!$return['status'])
				{
					$_POST['error_messages'] = $return['msg'];
					ExecMethod('expressoAdmin1_2.uidomains.add');
					return;
				}
			}
			
			$this->db->add_sambadomain(strtoupper($_POST['sambadomainname']), $_POST['sambasid']);
			
			ExecMethod('expressoAdmin1_2.uidomains.list_domains');
			return;
		}
		
		function delete()
		{
			$account_lid = $GLOBALS['phpgw']->accounts->data['account_lid'];
			$acl = $this->functions->read_acl($account_lid);
			$manager_context = $acl[0]['context'];
			
			// Verifica se tem acesso a este modulo
			if (!$this->functions->check_acl($account_lid,'edit_sambadomains'))
			{
				$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/expressoAdmin1_2/inc/access_denied.php'));
			}
			
			$return = $this->ldap_functions->delete_sambadomain($_GET['sambadomainname']);
			if (!$return['status'])
			{
				echo $return['msg'];
				//ExecMethod('expressoAdmin1_2.uidomains.add');
				return;
			}
			$this->db->delete_sambadomain($_GET['sambadomainname']);
			
			ExecMethod('expressoAdmin1_2.uidomains.list_domains');
			return;
		}
		
		function row_action($action,$sambadomainname)
		{
			return '<a href="'.$GLOBALS['phpgw']->link('/index.php',Array(
				'menuaction'		=> 'expressoAdmin1_2.uidomains.'.$action,
				'sambadomainname'	=> $sambadomainname
			)).'"> '.lang($action).' </a>';
		}
	}
?>