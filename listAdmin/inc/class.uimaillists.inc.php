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
			'adm_maillists'		=> True,
			'css'			=> True
		);

		var $nextmatchs;
		var $functions;
			
		function uimaillists()
		{
			$this->maillist		= CreateObject('listAdmin.maillist');
			$this->functions	= CreateObject('listAdmin.functions');
			$this->ldap_functions   = CreateObject('listAdmin.ldap_functions');
			$this->nextmatchs	= CreateObject('phpgwapi.nextmatchs');

			$c = CreateObject('phpgwapi.config','listAdmin');
			$c->read_repository();
			$this->current_config = $c->config_data;

			if(!@is_object($GLOBALS['phpgw']->js))
			{
				$GLOBALS['phpgw']->js = CreateObject('phpgwapi.javascript');
			}
			$GLOBALS['phpgw']->js->validate_file('jscode','connector','listAdmin');#diretorio, arquivo.js, aplicacao
			$GLOBALS['phpgw']->js->validate_file('jscode','expressoadmin','listAdmin');
			$GLOBALS['phpgw']->js->validate_file('jscode','maillists','listAdmin');
		}
		
		function list_maillists()
		{
			$account_lid = $GLOBALS['phpgw']->accounts->data['account_lid'];
			//$acl = $this->functions->read_acl($account_lid);
			$context = $GLOBALS['phpgw_info']['server']['ldap_context']; //$acl[0]['context'];
			$context_display = ldap_dn2ufn($GLOBALS['phpgw_info']['server']['ldap_context']); //$acl[0]['context_display'];
			
			if(isset($_POST['query']))
			{
				// limit query to limit characters
				if(preg_match('/^[a-z_0-9_%-].+$/i',$_POST['query'])) 
					$GLOBALS['query'] = $_POST['query'];
			}

			unset($GLOBALS['phpgw_info']['flags']['noheader']);
			unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
			
			$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['listAdmin']['title'].' - '.lang('Email Lists');
			$GLOBALS['phpgw']->common->phpgw_header();

			$p = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
			$p->set_file(array('maillists'   => 'maillists.tpl'));
			$p->set_block('maillists','list','list');
			$p->set_block('maillists','row','row');
			$p->set_block('maillists','row_empty','row_empty');


			//Pega o id do usuario atual da sessao
			$usuarioAtual=$_SESSION['phpgw_info']['expresso']['user']['account_lid'];
			//Pega o uidnumber do usuario atual
			$admUidnumber = $this->ldap_functions->uid2uidnumber($usuarioAtual);

			//Pega o email do usuario atual
			$admlista = $this->ldap_functions->uidnumber2mail($admUidnumber);

			$desabilitado = 'disabled';

			if($this->ldap_functions->is_user_listAdmin($usuarioAtual) == 1)
			{
				$desabilitado = '';
				$can_edit = True;
				$can_delete = True;
			}
			else
			{
				$can_edit = True;
				$can_delete = False;
			}


			// Seta as variaveis padroes.
			$var = Array(
				'th_bg'					=> $GLOBALS['phpgw_info']['theme']['th_bg'],
				'back_url'				=> $GLOBALS['phpgw']->link('/listAdmin/index.php'),
				'add_action'				=> $GLOBALS['phpgw']->link('/index.php','menuaction=listAdmin.uimaillists.add_maillists'),
				'add_email_lists_disabled'		=> $this->functions->check_acl($account_lid,'add_maillists') ? '' : 'disabled',
				'context'				=> $context,
				'context_display'			=> $context_display,
				'desabilitado'				=> $desabilitado,
				'lang_email_lists_uid'			=> lang('Email Lists Logins'),
				'lang_email_lists_names'		=> lang('Email Lists Names'),
				'lang_add_email_lists'			=> lang('Add Email Lists'),
				'lang_edit'  				=> lang('Edit'),
				'lang_scl'  				=> 'SCL',
				'lang_adm'  				=> 'ADM',
				'lang_delete'				=> 'Excluir',
				'lang_view'				=> lang('View'),
				'lang_back'				=> lang('back'),
				'lang_context'				=> lang('context'),
				'lang_email'				=> lang('E-mail'),
				'lang_search'				=> lang('search')
			);
			$p->set_var($var);

			// Save query
			$p->set_var('query', $GLOBALS['query']);

			//Variavel recebe o contexto onde estao gravadas as listas no sistema;
			$contextoListas = $this->current_config['dn_listas'];

			$auto_list = $this->current_config['mm_ldap_query_automatic'];
			if( ($auto_list == "true") && ($GLOBALS['query'] == '') )
			{
				//Retorna todas as listas que o usuario conectado administra
				$maillists_info = $this->functions->auto_list('maillists', $contextoListas, $admlista);
			}else {
			//Admin make a search
				if( ($GLOBALS['query'] != '') && (strlen($GLOBALS['query']) < 4) ) {
					$p->set_var('message',lang('Search argument too short'));
				}else if ($GLOBALS['query'] != ''){
					//Retorna as listas que o usuario conectado administra, de acordo com o argumento de busca
					$maillists_info = $this->functions->get_list('maillists', $GLOBALS['query'], $contextoListas, $admlista);
				}
			}

			$total = count($maillists_info);


			if (!count($total) && $GLOBALS['query'] != '')
			{
				$p->set_var('message',lang('No matches found'));
			}
			else if ($total)
			{

				foreach($maillists_info as $maillist)
				{
					$tr_color = $this->nextmatchs->alternate_row_color($tr_color);
					$var = array(
						'tr_color'		=> $tr_color,
						'row_uid'		=> $maillist['uid'],
						'row_name'		=> $maillist['name'],
						'row_email'		=> $maillist['email']
					);
					$p->set_var($var);

					if ($can_edit)
					{
						$p->set_var('edit_link',$this->row_action('edit','maillists',$maillist['uidnumber'],$maillist['uid']));
						$p->set_var('scl_link',$this->row_action('scl','maillists',$maillist['uidnumber'],$maillist['uid']));
						$p->set_var('adm_link',$this->row_action('adm','maillists',$maillist['uidnumber'],$maillist['uid']));
					}
					else
					{
						$p->set_var('edit_link','&nbsp;');
						$p->set_var('scl_link','&nbsp;');
						$p->set_var('adm_link','&nbsp;');
					}

					if ($can_delete)
					{
						$p->set_var('delete_link',"<a href='#' onClick='javascript:delete_maillist(\"".$maillist['uid']."\",\"".$maillist['uidnumber']."\",\"".$context."\");'>Excluir</a>");
					}
					else
					{
						$p->set_var('delete_link','&nbsp;');
					}
					
					$p->fp('rows','row',True);
				}
			}
			$p->parse('rows','row_empty',True);
			$p->set_var($var);
			$p->pfp('out','list');

		}
		
		function add_maillists()
		{
			//$GLOBALS['phpgw']->js->set_onload('get_available_users(document.forms[0].org_context.value, document.forms[0].ea_check_allUsers.checked);');
			
			$account_lid = $GLOBALS['phpgw']->accounts->data['account_lid'];
			//$acl = $this->functions->read_acl($account_lid);
			//$context = $acl[0]['context'];
			$context = $GLOBALS['phpgw_info']['server']['ldap_context']; //$acl[0]['context'];
			//$context_display = ldap_dn2ufn($GLOBALS['phpgw_info']['server']['ldap_context']); //$acl[0]['context_display'];
			
			unset($GLOBALS['phpgw_info']['flags']['noheader']);
			unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
			$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['listAdmin']['title'].' - '.lang('Create Email List');
			$GLOBALS['phpgw']->common->phpgw_header();
			
			// Set o template
			$p = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
			$p->set_file(Array('create_maillist' => 'maillists_form.tpl'));

			// Pega combo das organizações.
			$org = $this->functions->get_organizations($context, '');

			// Seta variaveis utilizadas pelo tpl.
			$var = Array(
				'color_bg1'					=> "#E8F0F0",
				'color_bg2'					=> "#D3DCE3",
				'type'						=> 'create_maillist',
				'ldap_context'					=> $GLOBALS['phpgw_info']['server']['ldap_context'],
				'uid'						=> 'lista-',
				'exibir_div'					=> ' style="display: none;" ',
				'accountStatus_checked'				=> 'CHECKED',
//				'defaultMemberModeration_checked'		=> 'CHECKED',	
				'accountAdm_checked'				=> 'CHECKED',
				'restrictionsOnEmailLists'			=> $this->current_config['expressoAdmin_restrictionsOnEmailLists'],
				'lang_back'					=> lang('Back'),
				'lang_save'					=> lang('save'),
				'lang_org'					=> lang('Organizations'),
				'lang_maillist_uid'				=> lang('Maillist login'),
				'lang_maillist_mail'				=> lang('Maillist Mail'),
				'lang_maillist_name'				=> lang('Maillist name'),
				'lang_maillist_description'			=> lang('Maillist description'),
				'lang_maillist_users'				=> lang('Maillist users'),
				'lang_add_user'					=> lang('Add User'),
				'lang_rem_user'					=> lang('Remove User'),
				'lang_all_users'				=> lang('Show users from all sub-organizations'),
				'back_url'					=> $GLOBALS['phpgw']->link('/index.php','menuaction=listAdmin.uimaillists.list_maillists'),
				'combo_org'					=> $org,
				'ea_select_usersInMaillist'			=> $ea_select_usersInMaillist
			);
			$p->set_var($var);
			
			$p->pfp('out','create_maillist');
		}
		
		
		function edit_maillists()
		{
	
			$GLOBALS['phpgw']->js->set_onload('get_available_users(document.forms[0].org_context.value, document.forms[0].ea_check_allUsers.checked);');
			
			$account_lid = $GLOBALS['phpgw']->accounts->data['account_lid'];
			//$acl = $this->functions->read_acl($account_lid);
			//$manager_context = $acl[0]['context'];
			$manager_context = $GLOBALS['phpgw_info']['server']['ldap_context']; //$acl[0]['context'];
			//$context_display = ldap_dn2ufn($GLOBALS['phpgw_info']['server']['ldap_context']); //$acl[0]['context_display'];
			

			//Teste o tipo de lista que esta sendo editada (corporativa ou funcional);
			$uid_lista = $_GET['maillist_uid']; //recebe o uid da lista selecionada;
			$str = explode("-", $uid_lista); //separa pelo "-";
			$str = $str[0]; //pega o primeiro elemento, que neste caso e o que indica o tipo de lista

			$somente_leitura = "";
			$desabilitado = "";
			if(($str == "listacorp") || ($str == "listafunc")) {
				$somente_leitura = "readonly"; //se lista for corp ou func, a variavel recebe readonly - sera usada em todos os input do form de edicao de listas;
				$desabilitado = "disabled";
			}

			//Pega o id do administrador do Expresso (expresso-admin)
			$expressoAdmin 						= $GLOBALS['phpgw_info']['server']['header_admin_user'];

			//Pega o id do usuario atual da sessao
			$usuarioAtual 						= $_SESSION['phpgw_info']['expresso']['user']['account_lid'];

			if($expressoAdmin != $usuarioAtual) {

				$soAdminLe = "readonly"; //se usuarioAtual for diferente de expressoAdmin, a variavel recebe readonly - sera usada nos input com uid, mail e cn da lista;

			}




			// GET all infomations about the group.
			$maillist_info = $this->maillist->get_info($_GET['uidnumber'], $manager_context);
			// debug_array($maillist_info);
					
	
			unset($GLOBALS['phpgw_info']['flags']['noheader']);
			unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
			$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['listAdmin']['title'].' - '.lang('Edit Email Lists');
			$GLOBALS['phpgw']->common->phpgw_header();

			// Set o template
			$p = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
			$p->set_file(Array('edit_maillist' => 'maillists_form.tpl'));

			// Pega combo das organizações e seleciona a org da lista.
			$org = $this->functions->get_organizations($manager_context, trim(strtolower($maillist_info['context'])));

			// Usuarios da lista.
			if (count($maillist_info['members_info']) > 0)
			{
				foreach ($maillist_info['members_info'] as $uidnumber=>$userinfo)
				{
					$array_users[$uidnumber] = $userinfo['cn'];
					$array_users_uid[$uidnumber] = $userinfo['uid'];
					$array_users_type[$uidnumber] = $userinfo['type'];
				}
				natcasesort($array_users);
				foreach ($array_users as $uidnumber=>$cn)
				{
					if ($array_users_type[$uidnumber] == 'u')
					{
						//$users .= "<option value=" . $uidnumber . ">" . $cn .  " [" . $array_users_uid[$uidnumber] . "]</option>";
						$users .= "<option value=" . $uidnumber . ">" . $cn .  " [" . $maillist_info['members_info'][$uidnumber]['mail'] . "]</option>";
					}
					elseif ($array_users_type[$uidnumber] == 'l')
					{
						$lists .= "<option value=" . $uidnumber . ">" . $cn .  " [" . $array_users_uid[$uidnumber] . "]</option>";
					}
					else
					{
						$unknow .= "<option value=" . $uidnumber . ">" . $cn .  " [" . $array_users_uid[$uidnumber] . "]</option>";
					}
				}
				
				if ($unknow != '')
				{
					//$opt_tmp_unknow = '<option  value="-1" disabled>--------------------&nbsp;&nbsp;&nbsp;&nbsp;E-mails não encontrados&nbsp;&nbsp;&nbsp;&nbsp;------------------ </option>'."\n";
					$opt_tmp_unknow = '<option  value="-1" disabled>-----------&nbsp;&nbsp;&nbsp;Usu&aacute;rios n&atilde;o pertencentes ou n&atilde;o criados no Expresso&nbsp;&nbsp;&nbsp;---------- </option>'."\n";
					$ea_select_usersInMaillist .= $opt_tmp_unknow . $unknow;
				}
				if ($lists != '')
				{
					$opt_tmp_lists  = '<option  value="-1" disabled>------------------------------&nbsp;&nbsp;&nbsp;&nbsp;Listas&nbsp;&nbsp;&nbsp;&nbsp;------------------------------ </option>'."\n";
					$ea_select_usersInMaillist .= $opt_tmp_lists . $lists;
				}
				$opt_tmp_users  = '<option  value="-1" disabled>-----------------------------&nbsp;&nbsp;&nbsp;&nbsp;Usuários&nbsp;&nbsp;&nbsp;&nbsp;---------------------------- </option>'."\n";
				$ea_select_usersInMaillist .= $opt_tmp_users . $users;
			}

			// Seta variaveis utilizadas pelo tpl.
			$var = Array(
				'color_bg1'					=> "#E8F0F0",
				'color_bg2'					=> "#D3DCE3",
				'type'						=> 'edit_maillist',
				'ldap_context'					=> $GLOBALS['phpgw_info']['server']['ldap_context'],
				'lang_back'					=> lang('Back'),
				'lang_save'					=> lang('save'),
				'lang_org'					=> lang('Organizations'),
				'lang_maillist_uid'				=> lang('Maillist login'),
				'lang_maillist_mail'				=> lang('Maillist Mail'),
				'lang_maillist_name'				=> lang('Maillist name'),
				'lang_maillist_users'				=> lang('Maillist users'),
				'lang_maillist_description'			=> lang('Maillist description'),
				'lang_add_user'					=> lang('Add User'),
				'lang_rem_user'					=> lang('Remove User'),
				'lang_all_users'				=> lang('Select users from all sub-organizations'),
				'back_url'					=> $GLOBALS['phpgw']->link('/index.php','menuaction=listAdmin.uimaillists.list_maillists'),
//				'back_url'					=> $GLOBALS['phpgw']->link('/index.php','menuaction=listAdmin.uimaillists.list_maillists'),
				'combo_org'					=> $org,
				'manager_context'				=> $manager_context,
				'somente_leitura'				=> $somente_leitura, //recebe a variavel testada acima;
				'desabilitado'					=> $desabilitado, //recebe a variavel testada acima;
				'soAdminLe'					=> $soAdminLe, //recebe a variavel testada acima;
				'uidnumber'					=> $_GET['uidnumber'],
				'uid'						=> $maillist_info['uid'],
				'defaultMemberModeration'			=> $maillist_info['defaultMemberModeration'],
				'admlista'					=> $maillist_info['admlista'],
				'listPass'					=> $maillist_info['listPass'],
				'exibir_div'					=> '',
				'mail'						=> $maillist_info['mail'],
				'description'					=> $maillist_info['description'],
				'cn'						=> $maillist_info['cn'],
				'accountStatus_checked'				=> $maillist_info['accountStatus'] == 'active' ? 'CHECKED' : '',
				'accountAdm_checked'            		=> $maillist_info['accountAdm'] == 'active' ? 'CHECKED' : '',

				'phpgwAccountVisible_checked'			=> $maillist_info['phpgwAccountVisible'] == '-1' ? 'CHECKED' : '',
				'defaultMemberModeration_checked'               => $maillist_info['defaultMemberModeration'] == '1' ? 'CHECKED' : '',
				'ea_select_usersInMaillist'			=> $ea_select_usersInMaillist
			);
			$p->set_var($var);
			
			$p->pfp('out','edit_maillist');
		}
		
		function adm_maillists() //Funcao que trata do modulo/template de administradores de listas
		{
	
//			$GLOBALS['phpgw']->js->set_onload('get_available_users_only(document.forms[0].org_context.value, document.forms[0].ea_check_allUsers.checked);');
			
			$account_lid = $GLOBALS['phpgw']->accounts->data['account_lid'];
			//$acl = $this->functions->read_acl($account_lid);
			//$manager_context = $acl[0]['context'];
			$manager_context = $GLOBALS['phpgw_info']['server']['ldap_context']; //$acl[0]['context'];
			//$context_display = ldap_dn2ufn($GLOBALS['phpgw_info']['server']['ldap_context']); //$acl[0]['context_display'];
			
			//Teste o tipo de lista que esta sendo editada (corporativa ou funcional);
			$uid_lista = $_GET['maillist_uid']; //recebe o uid da lista selecionada;
			$str = explode("-", $uid_lista); //separa pelo "-";
			$str = $str[0]; //pega o primeiro elemento, que neste caso e o que indica o tipo de lista

			$somente_leitura = "";
			$desabilitado = "";
			if(($str == "listacorp") || ($str == "listafunc")) {
				$somente_leitura = "readonly"; //se lista for corp ou func, a variavel recebe readonly - sera usada nos input com uid, mail e cn da lista;
				$desabilitado = "disabled";
			}


			// GET all infomations about the group.
			$maillist_info = $this->maillist->get_adm_info($_GET['uidnumber'], $manager_context);
			// debug_array($maillist_info);
					
	
			unset($GLOBALS['phpgw_info']['flags']['noheader']);
			unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
			$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['listAdmin']['title'].' - '.lang('Admin Lists');
			$GLOBALS['phpgw']->common->phpgw_header();

			// Set o template
			$p = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
			$p->set_file(Array('adm_maillist' => 'maillists_adm.tpl'));

			// Pega combo das organizações e seleciona a org da lista.
			$org = $this->functions->get_organizations($manager_context, trim(strtolower($maillist_info['context'])));

			// Usuarios da lista.
			if (count($maillist_info['members_info']) > 0)
			{
				foreach ($maillist_info['members_info'] as $uidnumber=>$userinfo)
				{
					$array_users[$uidnumber] = $userinfo['cn'];
					$array_users_uid[$uidnumber] = $userinfo['uid'];
					$array_users_type[$uidnumber] = $userinfo['type'];
				}
				natcasesort($array_users);
				foreach ($array_users as $uidnumber=>$cn)
				{
					if ($array_users_type[$uidnumber] == 'u')
					{
//						$users .= "<option value=" . $uidnumber . ">" . $cn .  " [" . $array_users_uid[$uidnumber] . "]</option>";
						$users .= "<option value=" . $uidnumber . ">" . $cn .  " [" . $maillist_info['members_info'][$uidnumber]['mail'] . "]</option>";
					}
					else
					{
						$unknow .= "<option value=" . $uidnumber . ">" . $cn .  " [" . $array_users_uid[$uidnumber] . "]</option>";
					}
				}
				
				if ($unknow != '')
				{
					//$opt_tmp_unknow = '<option  value="-1" disabled>--------------------&nbsp;&nbsp;&nbsp;&nbsp;E-mails não encontrados&nbsp;&nbsp;&nbsp;&nbsp;------------------ </option>'."\n";
					$opt_tmp_unknow = '<option  value="-1" disabled>-----------&nbsp;&nbsp;&nbsp;Usu&aacute;rios n&atilde;o pertencentes ou n&atilde;o criados no Expresso&nbsp;&nbsp;&nbsp;---------- </option>'."\n";
					$ea_select_ADM_Maillist .= $opt_tmp_unknow . $unknow;
				}
				$opt_tmp_users  = '<option  value="-1" disabled>-----------------------------&nbsp;&nbsp;&nbsp;&nbsp;Usuários&nbsp;&nbsp;&nbsp;&nbsp;---------------------------- </option>'."\n";
				$ea_select_ADM_Maillist .= $opt_tmp_users . $users;
			}

			// Seta variaveis utilizadas pelo tpl.
			$var = Array(
				'color_bg1'					=> "#E8F0F0",
				'color_bg2'					=> "#D3DCE3",
				'type'						=> 'adm_maillist',
				'ldap_context'					=> $GLOBALS['phpgw_info']['server']['ldap_context'],
				'dn_listas'					=> $GLOBALS['phpgw_info']['server']['dn_listas'],
				'lang_back'					=> lang('Back'),
				'lang_save'					=> lang('save'),
				'lang_org'					=> lang('Organizations'),
				'lang_maillist_uid'				=> lang('Maillist login'),
				'lang_maillist_mail'				=> lang('Maillist Mail'),
				'lang_maillist_name'				=> lang('Maillist name'),
				'lang_maillist_description'			=> lang('Maillist description'),
				'lang_maillist_users'				=> lang('Maillist users'),
				'lang_maillist_adm'				=> lang('Maillist adm'),
				'lang_add_user'					=> lang('Add User'),
				'lang_rem_user'					=> lang('Remove User'),
				'lang_all_users'				=> lang('Select users from all sub-organizations'),
				'back_url'					=> $GLOBALS['phpgw']->link('/index.php','menuaction=listAdmin.uimaillists.list_maillists'),
				'combo_org'					=> $org,
				'teste'						=> $maillist_info['context'],
				'manager_context'				=> $manager_context,
				'uidnumber'					=> $_GET['uidnumber'],
				'uid'						=> $maillist_info['uid'],
				'admlista'					=> $maillist_info['admlista'],
				'somente_leitura'				=> $somente_leitura, //recebe a variavel testada acima;
				'desabilitado'					=> $desabilitado, //recebe a variavel testada acima;
				'mail'						=> $maillist_info['mail'],
				'cn'						=> $maillist_info['cn'],
				'description'					=> $maillist_info['description'],
				'accountStatus_checked'				=> $maillist_info['accountStatus'] == 'active' ? 'CHECKED' : '',
				'accountAdm_checked'            		=> $maillist_info['accountAdm'] == 'active' ? 'CHECKED' : '',

				'phpgwAccountVisible_checked'			=> $maillist_info['phpgwAccountVisible'] == '-1' ? 'CHECKED' : '',
				'ea_select_ADM_Maillist'			=> $ea_select_ADM_Maillist
			);

			$p->set_var($var);
			
			$p->pfp('out','adm_maillist');
		}

		function scl_maillists()
		{
			$GLOBALS['phpgw']->js->set_onload('get_available_users(document.forms[0].org_context.value, document.forms[0].ea_check_allUsers.checked);');
			
			$account_lid = $GLOBALS['phpgw']->accounts->data['account_lid'];
			//$acl = $this->functions->read_acl($account_lid);
			//$manager_context = $acl[0]['context'];
			$manager_context = $GLOBALS['phpgw_info']['server']['ldap_context']; //$acl[0]['context'];
			//$context_display = ldap_dn2ufn($GLOBALS['phpgw_info']['server']['ldap_context']); //$acl[0]['context_display'];
			
			//Teste o tipo de lista que esta sendo editada (corporativa ou funcional);
			$uid_lista = $_GET['maillist_uid']; //recebe o uid da lista selecionada;
			$str = explode("-", $uid_lista); //separa pelo "-";
			$str = $str[0]; //pega o primeiro elemento, que neste caso e o que indica o tipo de lista

			$somente_leitura = "";
			$desabilitado = "";
			if(($str == "listacorp") || ($str == "listafunc")) {
				$somente_leitura = "readonly"; //se lista for corp ou func, a variavel recebe readonly - sera usada nos input com uid, mail e cn da lista;
				$desabilitado = "disabled";
			}

			// GET all infomations about the group.
			$maillist_info = $this->maillist->get_scl_info($_GET['uidnumber'], $manager_context);
			//_debug_array($maillist_info);
			
			unset($GLOBALS['phpgw_info']['flags']['noheader']);
			unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
			$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['listAdmin']['title'].' - '.lang('Edit Sending Control List');
			$GLOBALS['phpgw']->common->phpgw_header();

			// Set o template
			$p = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
			$p->set_file(Array('sql_maillist' => 'maillists_scl.tpl'));

			// Pega combo das organizações e seleciona a org da lista.
			$org = $this->functions->get_organizations($manager_context, trim(strtolower($maillist_info['context'])));

			// Usuarios de senders.
			if (count($maillist_info['senders_info']) > 0)
			{
				foreach ($maillist_info['senders_info'] as $uidnumber=>$senderinfo)
				{
					$array_senders[$uidnumber] = $senderinfo['cn'];
				}
				natcasesort($array_senders);
				foreach ($array_senders as $uidnumber=>$cn)
				{
					$ea_select_users_SCL_Maillist .= "<option value=" . $uidnumber . ">" . $cn . " [" . $maillist_info['senders_info'][$uidnumber]['mail'] . "]</option>";
				}
			}

			// Seta variaveis utilizadas pelo tpl.
			$var = Array(
				'color_bg1'					=> "#E8F0F0",
				'color_bg2'					=> "#D3DCE3",
				'type'						=> 'edit_maillist',
				'ldap_context'					=> $GLOBALS['phpgw_info']['server']['ldap_context'],
				'dn'						=> $maillist_info['dn'],
				'lang_back'					=> lang('Back'),
				'lang_save'					=> lang('save'),
				'lang_org'					=> lang('Organizations'),
				'lang_maillist_uid'				=> lang('Maillist login'),
				'lang_maillist_mail'				=> lang('Maillist Mail'),
				'lang_maillist_name'				=> lang('Maillist name'),
				'lang_maillist_description'			=> lang('Maillist description'),
				'lang_maillist_users'				=> lang('Maillist users'),
				'lang_add_user'					=> lang('Add User'),
				'lang_rem_user'					=> lang('Remove User'),
				'lang_all_users'				=> lang('Show users from all sub-organizations'),
				'back_url'					=> $GLOBALS['phpgw']->link('/index.php','menuaction=listAdmin.uimaillists.list_maillists'),
				'combo_org'					=> $org,
				'manager_context'				=> $manager_context,
				'uidnumber'					=> $_GET['uidnumber'],
				'uid'						=> $maillist_info['uid'],
				'mail'						=> $maillist_info['mail'],
				'cn'						=> $maillist_info['cn'],
				'description'					=> $maillist_info['description'],
				'admlista'					=> $maillist_info['admlista'],
				'somente_leitura'				=> $somente_leitura, //recebe a variavel testada acima;
				'desabilitado'					=> $desabilitado, //recebe a variavel testada acima;
				'listPass'					=> $maillist_info['listPass'],
				'accountRestrictive_checked'			=> $maillist_info['accountRestrictive'] == 'mailListRestriction' ? 'CHECKED' : '',
				'participantCanSendMail_checked'		=> $maillist_info['participantCanSendMail'] == 'TRUE' ? 'CHECKED' : '',
				'ea_select_users_SCL_Maillist'			=> $ea_select_users_SCL_Maillist
			);
			$p->set_var($var);
			
			$p->pfp('out','sql_maillist');
		}
	
		function row_action($action,$type,$uidnumber,$maillist_uid)
		{
			return '<a href="'.$GLOBALS['phpgw']->link('/index.php',Array(
				'menuaction'					=> 'listAdmin.uimaillists.'.$action.'_'.$type,
				'uidnumber'					=> $uidnumber,
				'maillist_uid'					=> $maillist_uid
				)).'"> '.lang($action).' </a>';
		}
		
		function css()
		{
			$appCSS = '';
			return $appCSS;
		}
	}
?>
