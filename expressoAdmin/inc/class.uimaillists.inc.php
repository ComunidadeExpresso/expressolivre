<?php
	/*************************************************************************************\
	* Expresso Administração                 										     *
	* by Joao Alfredo Knopik Junior (joao.alfredo@gmail.com, jakjr@celepar.pr.gov.br)  	 *
	* -----------------------------------------------------------------------------------*
	*  This program is free software; you can redistribute it and/or modify it			 *
	*  under the terms of the GNU General Public License as published by the			 *
	*  Free Software Foundation; either version 2 of the License, or (at your			 *
	*  option) any later version.														 *
	\*************************************************************************************/

	class uimaillists
	{
		var $public_functions = array
		(
			'list_maillists'	=> True,
			'add_maillists'		=> True,
			'edit_maillists'	=> True,
			'scl_maillists'		=> True,
			'css'				=> True
		);

		var $nextmatchs;
		var $functions;
			
		function uimaillists()
		{
			$this->maillist		= CreateObject('expressoAdmin.maillist');
			$this->functions	= CreateObject('expressoAdmin.functions');
			$this->nextmatchs	= CreateObject('phpgwapi.nextmatchs');

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
			$GLOBALS['phpgw']->js->validate_file('jscode','maillists','expressoAdmin');
		}
		
		function list_maillists()
		{
			$manager_lid 		= $GLOBALS['phpgw']->accounts->data['account_lid'];
			$manager_acl 		= $this->functions->read_acl($manager_lid);
			$manager_contexts	= $manager_acl['contexts'];
			$context_display	= "";

			foreach ($manager_acl['contexts_display'] as $index=>$tmp_context)
			{
				$context_display .= '<br />'.$tmp_context;
			}
			
			// Verifica se tem acesso a este modulo
			if (!$this->functions->check_acl($manager_lid,'list_maillists'))
			{
				$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/expressoAdmin/inc/access_denied.php'));
			}

			if(isset($_POST['query']))
			{
				// limit query to limit characters
				if(preg_match('/^[a-z_0-9_%-].+$/i',$_POST['query'])) 
					$GLOBALS['query'] = $_POST['query'];
			}

			unset($GLOBALS['phpgw_info']['flags']['noheader']);
			unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
			
			$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['expressoAdmin']['title'].' - '.lang('Email Lists');
			$GLOBALS['phpgw']->common->phpgw_header();

			$p = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
			$p->set_file(array('maillists'   => 'maillists.tpl'));
			$p->set_block('maillists','list','list');
			$p->set_block('maillists','row','row');
			$p->set_block('maillists','row_empty','row_empty');
			
			// Seta as variaveis padroes.
			$var = Array(
				'th_bg'						=> $GLOBALS['phpgw_info']['theme']['th_bg'],
				'back_url'					=> $GLOBALS['phpgw']->link('/expressoAdmin/index.php'),
				'add_action'				=> $GLOBALS['phpgw']->link('/index.php','menuaction=expressoAdmin.uimaillists.add_maillists'),
				'add_email_lists_disabled'	=> $this->functions->check_acl($manager_lid,'add_maillists') ? '' : 'disabled',
				'context_display'			=> $context_display
			);
			$p->set_var($var);
			$p->set_var($this->functions->make_dinamic_lang($p, 'list'));
			
			// Save query
			$p->set_var('query', (isset($GLOBALS['query']) ? $GLOBALS['query'] : ""));
			
			$maillists_info = array();

			//Admin make a search
			if ( isset($GLOBALS['query']) && $GLOBALS['query'] != '')
			{
				$maillists_info = $this->functions->get_list('maillists', $GLOBALS['query'], $manager_contexts);
			}
			$total = count($maillists_info);

			if (!count($total) && $GLOBALS['query'] != '')
			{
				$p->set_var('message',lang('No matches found'));
			}
			else if ($total)
			{
				if ($this->functions->check_acl($manager_lid,'edit_maillists'))
				{
					$can_edit = True;
				}
				if ($this->functions->check_acl($manager_lid,'delete_maillists'))
				{
					$can_delete = True;
				}
				if ($this->functions->check_acl($manager_lid,'edit_scl_email_lists'))
				{
					$can_edit_scl = True;
				}


				foreach($maillists_info as $maillist)
				{
					$tr_color = $this->nextmatchs->alternate_row_color($tr_color);
					$var = array(
						'tr_color'		=> $tr_color,
						'row_uid'		=> $maillist['uid'],
						'row_name'	=> $maillist['name'],
						'row_email'	=> $maillist['email']
					);
					$p->set_var($var);

					if ($can_edit)
					{
						$p->set_var('edit_link',$this->row_action('edit','maillists',$maillist['uidnumber'],$maillist['uid']));
					}
					else
					{
						$p->set_var('edit_link','&nbsp;');
					}

					if ( ($can_edit_scl) && ($this->current_config['expressoAdmin_scl']) )
					{
						$p->set_var('scl_link',$this->row_action('scl','maillists',$maillist['uidnumber'],$maillist['uid']));
					}
					else
					{
						$p->set_var('scl_link','&nbsp;');
					}

					if ($can_delete)
					{
						$p->set_var('delete_link',"<a href='#' onClick='javascript:delete_maillist(\"".$maillist['uid']."\",\"".$maillist['uidnumber']."\");'>".lang('to delete')."</a>");
					}
					else
						$p->set_var('delete_link','&nbsp;');
					
					$p->fp('rows','row',True);
				}
			}
			$p->parse('rows','row_empty',True);
			$p->set_var($var);
			$p->pfp('out','list');			
		}
		
		function add_maillists()
		{
			$manager_lid = $GLOBALS['phpgw']->accounts->data['account_lid'];
			$manager_acl = $this->functions->read_acl($manager_lid);
			$manager_contexts = $manager_acl['contexts'];

			// Verifica se tem acesso a este modulo
			if (!$this->functions->check_acl($manager_lid,'add_maillists'))
			{
				$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/expressoAdmin/inc/access_denied.php'));
			}

			unset($GLOBALS['phpgw_info']['flags']['noheader']);
			unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
			$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['expressoAdmin']['title'].' - '.lang('Create Email List');
			$GLOBALS['phpgw']->common->phpgw_header();
			
			// Set o template
			$p = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
			$p->set_file(Array('create_maillist' => 'maillists_form.tpl'));
			$p->set_block('create_maillist','body','body');

			// Obtem combos das organizações.
			$combo_manager_org = "";
			foreach( $manager_contexts as $index => $context )
			{
				$combo_manager_org .= $this->functions->get_organizations($context);
			}
			$combo_all_orgs = $this->functions->get_organizations($GLOBALS['phpgw_info']['server']['ldap_context'], '', true, true, true);			
			
			// Seta variaveis utilizadas pelo tpl.
			$var = Array(
				'color_bg1'					=> "#E8F0F0",
				'color_bg2'					=> "#D3DCE3",
				'type'						=> "create_maillist",
				'ldap_context'				=> $GLOBALS['phpgw_info']['server']['ldap_context'],
				'uid'						=> "lista-",
				'accountStatus_checked'		=> "CHECKED",
				'restrictionsOnEmailLists'	=> (isset($this->current_config['expressoAdmin_restrictionsOnEmailLists'])?$this->current_config['expressoAdmin_restrictionsOnEmailLists']:""),
				'back_url'					=> $GLOBALS['phpgw']->link('/index.php','menuaction=expressoAdmin.uimaillists.list_maillists'),
				'combo_manager_org'			=> $combo_manager_org,
				'combo_all_orgs'			=> $combo_all_orgs,
				'defaultDomain'				=> ( isset($this->current_config['expressoAdmin_defaultDomain']) ? $this->current_config['expressoAdmin_defaultDomain'] : "" )
			);
			$p->set_var($var);
			$p->set_var($this->functions->make_dinamic_lang($p, 'body'));
			$p->pfp('out','create_maillist');
		}
		
		function edit_maillists()
		{
			$manager_lid = $GLOBALS['phpgw']->accounts->data['account_lid'];
			$manager_acl = $this->functions->read_acl($manager_lid);
			$manager_contexts = $manager_acl['contexts'];

			// Verifica se tem acesso a este modulo
			if (!$this->functions->check_acl($manager_lid,'edit_maillists'))
			{
				$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/expressoAdmin/inc/access_denied.php'));
			}
			
			// GET all infomations about the group.
			$maillist_info = $this->maillist->get_info($_GET['uidnumber']);
			
			unset($GLOBALS['phpgw_info']['flags']['noheader']);
			unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
			$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['expressoAdmin']['title'].' - '.lang('Edit Email Lists');
			$GLOBALS['phpgw']->common->phpgw_header();

			// Set o template
			$p = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
			$p->set_file(Array('edit_maillist' => 'maillists_form.tpl'));
			$p->set_block('edit_maillist','body','body');

			// Obtem combos das organizações.
			foreach ($manager_contexts as $index=>$context)
				$combo_manager_org .= $this->functions->get_organizations($context, trim(strtolower($maillist_info['context'])));
			$combo_all_orgs = $this->functions->get_organizations($GLOBALS['phpgw_info']['server']['ldap_context'], trim(strtolower($maillist_info['context'])), true, true, true);			

			// Usuarios da lista.
			$user_count = 0;
			if (count($maillist_info['mailForwardingAddress_info']) > 0)
			{
				foreach ($maillist_info['mailForwardingAddress_info'] as $mail=>$userinfo)
				{
					$array_users[$mail] = $userinfo['cn'];
					$array_users_uid[$mail] = $userinfo['uid'];
					$array_users_type[$mail] = $userinfo['type'];
				}
				natcasesort($array_users);
				foreach ($array_users as $mail=>$cn)
				{
					++$user_count;
					if ($array_users_type[$mail] == 'u')
					{
						$users .= "<option value=" . $mail . ">" . utf8_decode($cn) .  " (" . $array_users_uid[$mail] . ")</option>";
					}
					elseif ($array_users_type[$mail] == 'l')
					{
						$lists .= "<option value=" . $mail . ">" . utf8_decode($cn) .  " (" . $array_users_uid[$mail] . ")</option>";
					}
					else
					{
						$mail_not_found .= "<option value=" . $mail . ">"  . $array_users_uid[$mail] . "</option>";
					}
				}
				
				if ($mail_not_found != '')
				{
					$opt_tmp_mail_not_found = '<option  value="-1" disabled>--------------------&nbsp;&nbsp;&nbsp;&nbsp;'.lang('external users').'&nbsp;&nbsp;&nbsp;&nbsp;------------------ </option>'."\n";
					$ea_select_usersInMaillist .= $opt_tmp_mail_not_found . $mail_not_found;
				}
				if ($lists != '')
				{
					$opt_tmp_lists  = '<option  value="-1" disabled>------------------------------&nbsp;&nbsp;&nbsp;&nbsp;'.lang('email lists').'&nbsp;&nbsp;&nbsp;&nbsp;------------------------------ </option>'."\n";
					$ea_select_usersInMaillist .= $opt_tmp_lists . $lists;
				}
				$opt_tmp_users  = '<option  value="-1" disabled>-----------------------------&nbsp;&nbsp;&nbsp;&nbsp;'.lang('users').'&nbsp;&nbsp;&nbsp;&nbsp;---------------------------- </option>'."\n";
				$ea_select_usersInMaillist .= $opt_tmp_users . $users;
			}

			// Seta variaveis utilizadas pelo tpl.
			$var = Array(
				'color_bg1'						=> "#E8F0F0",
				'color_bg2'						=> "#D3DCE3",
				'type'							=> 'edit_maillist',
				'ldap_context'					=> $GLOBALS['phpgw_info']['server']['ldap_context'],
				'back_url'						=> $GLOBALS['phpgw']->link('/index.php','menuaction=expressoAdmin.uimaillists.list_maillists'),
				'combo_manager_org'				=> $combo_manager_org,
				'combo_all_orgs'				=> $combo_all_orgs,
				'uidnumber'						=> $_GET['uidnumber'],
				'uid'							=> $maillist_info['uid'],
				'mail'							=> $maillist_info['mail'],
				'description'					=> $maillist_info['description'],
				'cn'							=> $maillist_info['cn'],
				'user_count'					=> $user_count,
				'accountStatus_checked'			=> $maillist_info['accountStatus'] == 'active' ? 'CHECKED' : '',
				'phpgwAccountVisible_checked'	=> $maillist_info['phpgwAccountVisible'] == '-1' ? 'CHECKED' : '',
				'ea_select_usersInMaillist'		=> $ea_select_usersInMaillist,
				'defaultDomain'					=> $this->current_config['expressoAdmin_defaultDomain']
			);
			$p->set_var($var);
			$p->set_var($this->functions->make_dinamic_lang($p, 'body'));
			
			$p->pfp('out','edit_maillist');
		}
		
		function scl_maillists()
		{
			$manager_lid = $GLOBALS['phpgw']->accounts->data['account_lid'];
			$manager_acl = $this->functions->read_acl($manager_lid);
			$manager_contexts = $manager_acl['contexts'];
						
			// Verifica se tem acesso a este modulo
			if (!$this->functions->check_acl($manager_lid,'edit_maillists'))
			{
				$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/expressoAdmin/inc/access_denied.php'));
			}
			
			// GET all infomations about the group.
			$maillist_info = $this->maillist->get_scl_info($_GET['uidnumber']);
			
			unset($GLOBALS['phpgw_info']['flags']['noheader']);
			unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
			$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['expressoAdmin']['title'].' - '.lang('Edit Sending Control List');
			$GLOBALS['phpgw']->common->phpgw_header();

			// Set o template
			$p = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
			$p->set_file(Array('scl_maillist' => 'maillists_scl.tpl'));
			$p->set_block('scl_maillist','body','body');

			// Pega combo das organizações e seleciona a org da lista.			
			// $org = $this->functions->get_organizations($manager_context, trim(strtolower($maillist_info['context'])));
			foreach ($manager_contexts as $index=>$context)
				$sectors .= $this->functions->get_organizations($context, trim(strtolower($maillist_info['context'])) );

			// Usuarios de senders.
			if (count($maillist_info['senders_info']) > 0)
			{
				foreach ($maillist_info['senders_info'] as $mail=>$senderinfo)
				{
					$array_senders[$mail] = $senderinfo['cn'];
				}
				natcasesort($array_senders);
				foreach ($array_senders as $mail=>$cn)
				{
					$ea_select_users_SCL_Maillist .= "<option value=" . $mail . ">" . $cn . " [" . $mail . "]</option>";
				}
			}

			// Seta variaveis utilizadas pelo tpl.
			$var = Array(
				'color_bg1'						=> "#E8F0F0",
				'color_bg2'						=> "#D3DCE3",
				'type'							=> 'edit_maillist',
				'ldap_context'					=> $GLOBALS['phpgw_info']['server']['ldap_context'],
				'dn'							=> $maillist_info['dn'],
				'back_url'						=> $GLOBALS['phpgw']->link('/index.php','menuaction=expressoAdmin.uimaillists.list_maillists'),
				'combo_org'						=> $sectors,
				'uidnumber'						=> $_GET['uidnumber'],
				'uid'							=> $maillist_info['uid'],
				'mail'							=> $maillist_info['mail'],
				'cn'							=> $maillist_info['cn'],
				'accountRestrictive_checked'	=> $maillist_info['accountRestrictive'] == 'mailListRestriction' ? 'CHECKED' : '',
				'participantCanSendMail_checked'=> $maillist_info['participantCanSendMail'] == 'TRUE' ? 'CHECKED' : '',
				'ea_select_users_SCL_Maillist'	=> $ea_select_users_SCL_Maillist
			);
			$p->set_var($var);
			$p->set_var($this->functions->make_dinamic_lang($p, 'body'));
			
			$p->pfp('out','scl_maillist');
		}
		
		function row_action($action,$type,$uidnumber,$maillist_uid)
		{
			return '<a href="'.$GLOBALS['phpgw']->link('/index.php',Array(
				'menuaction'		=> 'expressoAdmin.uimaillists.'.$action.'_'.$type,
				'uidnumber'			=> $uidnumber,
				'maillist_uid'		=> $maillist_uid
			)).'"> '.lang($action).' </a>';
		}
		
		function css()
		{
			$appCSS = '';
			return $appCSS;
		}
	}
?>
