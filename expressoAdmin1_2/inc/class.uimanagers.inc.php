<?php
	/************************************************************************************\
	* Expresso Administração                 										    *
	* by Joao Alfredo Knopik Junior (joao.alfredo@gmail.com, jakjr@celepar.pr.gov.br)   *
	* ----------------------------------------------------------------------------------*
	*  This program is free software; you can redistribute it and/or modify it			*
	*  under the terms of the GNU General Public License as published by the			*
	*  Free Software Foundation; either version 2 of the License, or (at your			*
	*  option) any later version.														*
	\************************************************************************************/

	class uimanagers
	{
		var $public_functions = array
		(
			'list_managers'		=> True,
			'add_managers'		=> True,
			'delete_managers'	=> True,
			'edit_managers' 	=> True,
			'validate'			=> True
		);

		var $functions;
		var $config;

		function uimanagers()
		{
			$this->functions = CreateObject('expressoAdmin1_2.functions');
			$c = CreateObject('phpgwapi.config','expressoAdmin1_2');
			$c->read_repository();
			$this->config = $c->config_data;
			
			if(!@is_object($GLOBALS['phpgw']->js))
			{
				$GLOBALS['phpgw']->js = CreateObject('phpgwapi.javascript');
			}
			$GLOBALS['phpgw']->js->validate_file('jscode','connector','expressoAdmin1_2');#diretorio, arquivo.js, aplicacao
			$GLOBALS['phpgw']->js->validate_file('jscode','finder','expressoAdmin1_2');
			$GLOBALS['phpgw']->js->validate_file('jscode','expressoadmin','expressoAdmin1_2');
			$GLOBALS['phpgw']->js->validate_file('jscode','managers','expressoAdmin1_2');
		}

		function row_action($lang,$link,$manager_lid,$context)
		{	
			return '<a href="'.$GLOBALS['phpgw']->link('/index.php',Array(
				'menuaction' => 'expressoAdmin1_2.uimanagers.'.$link,
				'action'		=>	$lang,
				'manager_lid' => $manager_lid,
				'context' => $context
			)).'" onmouseover="window.status=\''.lang($lang).' Manager\'; return true;" onmouseout="window.status=\'\';" >'.lang($lang).' </a>';
		}
				
		function list_managers()
		{
			// Caso nao seja admin, sai.
			if (!$GLOBALS['phpgw']->acl->check('run',1,'admin'))
			{
				$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/admin/index.php'));
			}
			// Imprime o NavBar
			unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
			$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['expressoAdmin1_2']['title'].' - '.lang('List Managers');
			$GLOBALS['phpgw']->common->phpgw_header();

			// Seta o template
			$p = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
			$p->set_file(array('managers' => 'managers.tpl'));
			$p->set_block('managers','body','body');
			$p->set_block('managers','row','row');
			$p->set_block('managers','row_empty','row_empty');
			$tpl_vars = $p->get_undefined('body');

			$var = Array(
				'action' 			=> $GLOBALS['phpgw']->link('/index.php','menuaction=expressoAdmin1_2.uimanagers.add_managers'),
				'tr_color'			=> '#DDDDDD',
				'th_bg'         	=> $GLOBALS['phpgw_info']['theme']['th_bg']
			);

			// Cria dinamicamente os langs
			foreach ($tpl_vars as $atribute)
			{
				$lang = strstr($atribute, 'lang_');
				if($lang !== false)
				{
					$p->set_var($atribute, $this->make_lang($atribute));
				}
			}

			// Le BD para pegar os administradors.
			$query = 'SELECT DISTINCT ON (manager_lid) manager_lid , context FROM phpgw_expressoadmin_acls ORDER by manager_lid ';
			$GLOBALS['phpgw']->db->query($query);
			while($GLOBALS['phpgw']->db->next_record())
			{
				$managers[] = $GLOBALS['phpgw']->db->row();
			}
			$ldap_conn = $GLOBALS['phpgw']->common->ldapConnect();
			$justthese = array("cn");
			// Loop para listar os administradores
			if (count($managers))
			{
				foreach($managers as $array_managers)
				{
					$managers_context = "";
					$a_managers_context = preg_split('/%/', $array_managers['context']);

					foreach ($a_managers_context as $context)
					{
						$managers_context .= "$context<br />";
					}
					
					$filter="(&(phpgwAccountType=u)(uid=".$array_managers['manager_lid']."))";
					$ldap_search = ldap_search($ldap_conn, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
					$ldap_result = ldap_get_entries($ldap_conn, $ldap_search);
					$p->set_var('manager_lid', $array_managers['manager_lid']);
					$p->set_var('manager_cn', $ldap_result[0]['cn'][0] == '' ? '<font color=red>NAO ENCONTRADO NO LDAP</font>' : $ldap_result[0]['cn'][0]);
					$p->set_var('context', $managers_context);
					$p->set_var('link_edit',$this->row_action('edit','edit_managers',$array_managers['manager_lid'],$array_managers['context']));
					$p->set_var('link_delete',$this->row_action('delete','delete_managers',$array_managers['manager_lid'],$array_managers['context']));
					$p->set_var('link_copy',"<a href='#' onClick='javascript:copy_manager(\"".$array_managers['manager_lid']."\");'>Copiar</a>");
					$p->fp('rows','row',True);
				}
			}
			$p->set_var($var);
			$p->pfp('out','body');
			ldap_close($ldap_conn);
		}


		function add_managers()
		{
			// Caso nao seja admin, sai.
			if (!$GLOBALS['phpgw']->acl->check('run',1,'admin'))
			{
				$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/admin/index.php'));
			}
			
			// Seta o template
			$p = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
			$p->set_file(array('managers' => 'managers_form.tpl'));
			$p->set_block('managers','form','form');
			$tpl_vars = $p->get_undefined('form');

			// Imprime o NavBar
			unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
			$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['expressoAdmin1_2']['title'].' - '.lang('Add Managers');
			$GLOBALS['phpgw']->common->phpgw_header();
			
			// Seta variaveis javascript necessárias
			$webserver_url = $GLOBALS['phpgw_info']['server']['webserver_url'];
			$scripts_java = '<script type="text/javascript" src="'.$webserver_url.'/expressoAdmin1_2/js/jscode/expressoadmin.js"></script>';
			
			// App, create list of available apps
			$applications_list = $this->make_app_list('');
			
			/*
			if ($_POST['context'])
			{
				$contexts = preg_split('/%/', $_POST['context']);
				foreach ($contexts as $manager_context)
					$input_context_fields .= "<input type='text' size=60 value=$manager_context></input><br />";
			}
			else
				$input_context_fields = '<input type="text" size=60></input><br />';
			*/
			
			$options_context = $this->functions->get_organizations($GLOBALS['phpgw_info']['server']['ldap_context'], '', false, true, false);
			
			// Seta variaveis que estao no TPL
			$var = Array(
				'scripts_java'			=>	$scripts_java,	
				//'action'				=> $GLOBALS['phpgw']->link('/index.php','menuaction=expressoAdmin1_2.uimanagers.validate'),
				//'action'				=> $GLOBALS['phpgw']->link('/index.php','menuaction=expressoAdmin1_2.bomanagers.add_managers'),
				'display_samba_suport'	=> $this->config['expressoAdmin_samba_support'] == 'true' ? '' : 'display:none',
				'type'					=> "add",
				'color_bg1'				=> "#E8F0F0",
				'color_bg2'				=> "#D3DCE3",
				'color_font1'			=> "#DDDDDD",
				'color_font2'			=> "#EEEEEE",
				'input_context_fields'	=> $input_context_fields,
				'error_messages'		=> $_POST['error_messages'] == '' ? '' : '<script type="text/javascript">alert("'.$_POST['error_messages'].'");</script>',
				'manager_lid'			=> $_POST['manager_lid'],
				'context'				=> $_POST['context'],
				'app_list'				=> $applications_list,
				'options_contexts'		=> $options_context
			);
			$p->set_var($var);
			
			// Cria dinamicamente os langs e seta acls
			foreach ($tpl_vars as $atribute)
			{
				$acl  = strstr($atribute, 'acl_');
				$lang = strstr($atribute, 'lang_');
				// Recuperar os valores das ACLS
				if ($acl !== false)
				{
					$p->set_var($atribute, $_POST[$atribute] != '' ? 'checked' : ''); 
				}
				// Setar os langs do tpl.
				elseif($lang !== false)
				{
					$p->set_var($atribute, $this->make_lang($atribute));
				}
			}
			
			echo $p->fp('out','form');
		}
	
		function delete_managers()
		{
			if (!$GLOBALS['phpgw']->acl->check('run',1,'admin'))
			{
				$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/admin/index.php'));
			}
			// Criar uma verificação e jogar a query para o BO.
			$context = $_GET['context'];
			$manager_lid = $_GET['manager_lid'];
			
			$query = "DELETE FROM phpgw_expressoadmin_acls WHERE manager_lid = '".$manager_lid."' AND context = '" . $context ."'"; 
			$GLOBALS['phpgw']->db->query($query);
			
			// Remove Gerente da tabela dos apps
			$query = "DELETE FROM phpgw_expressoadmin_apps WHERE "
			. "manager_lid = '".$manager_lid."' AND "
			. "context = '".$context."'";
			$GLOBALS['phpgw']->db->query($query);		
			
			// Remove Gerente na ACL do expressoadmin
			$accounts = CreateObject('phpgwapi.accounts');
			$manager_id = $accounts->name2id($_GET['manager_lid']);
			$sql = "DELETE FROM phpgw_acl WHERE acl_appname = 'expressoadmin' AND acl_account = '" . $manager_id . "'"; 
			$GLOBALS['phpgw']->db->query($sql);			
			
			ExecMethod('expressoAdmin1_2.uimanagers.list_managers');
		}
	
		function edit_managers()
		{
			echo "<input type='hidden' name='old_url_context_aux' value='".$_GET['context']."'>";
			
			// Caso nao seja admin, sai.
			if (!$GLOBALS['phpgw']->acl->check('run',1,'admin'))
			{
				$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/admin/index.php'));
			}
			
			// Verifica se eh a primeira entrada, ai eu tenho o get, senao pego o post.
			if ($_GET['manager_lid'] != '')
			{
				$first_time = true;
				$_POST['manager_lid']	= $_GET['manager_lid'];
				$_POST['context'] 		= $_GET['context'];
				$hidden_manager_lid		= $_GET['manager_lid'];
			}
			elseif ($_POST['manager_lid'] != '')
			{
				$first_time 		= false;
				$hidden_manager_lid	= $_POST['old_manager_lid'];				
			}
			
			if ($first_time)
			{
				
				$manager_acl = $this->functions->read_acl($_GET['manager_lid']);
				//Pesquisa no Banco e pega os valores dos apps.
				$query = "SELECT * FROM phpgw_expressoadmin_apps WHERE manager_lid = '" . addslashes($_GET['manager_lid']) . "' AND context = '" . addslashes($_GET['context']) . "'";
				$GLOBALS['phpgw']->db->query($query);
				$i=0;
				$manager[0]['apps'] = array();
				while($GLOBALS['phpgw']->db->next_record())
				{
					$tmp[$i] = $GLOBALS['phpgw']->db->row();
					$_POST['applications_list'][$tmp[$i]['app']] = 1;
					$manager[0]['apps'][$tmp[$i]['app']] = 1;
					++$i;
				}
			}

			// Seta o template
			$p = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
			$p->set_file(array('managers' => 'managers_form.tpl'));
			$p->set_block('managers','form','form');
			$tpl_vars = $p->get_undefined('form');
			
			// Imprime o NavBar
			unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
			$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['expressoAdmin1_2']['title'].' - '.lang('Edit Managers');
			$GLOBALS['phpgw']->common->phpgw_header();

			// Seta variaveis javas necessárias
			$webserver_url = $GLOBALS['phpgw_info']['server']['webserver_url'];
			$scripts_java = '<script type="text/javascript" src="'.$webserver_url.'/expressoAdmin1_2/js/jscode/expressoadmin.js"></script>';

			// App, create list of available apps
			$applications_list = $this->make_app_list($manager[0]['apps']);

			$a_context = preg_split('/%/', $_POST['context']);
			foreach ($a_context as $context)
				$input_context_fields .= '<div><input disabled type="text" value="'.$context.'" size=60></input><span onclick="this.parentNode.parentNode.removeChild(this.parentNode);" style="cursor:pointer"> -</span></div>';
			$options_context = $this->functions->get_organizations($GLOBALS['phpgw_info']['server']['ldap_context'], '', false, true, false);

			$var = Array(
				'scripts_java'				=> $scripts_java,
				'action'					=> $GLOBALS['phpgw']->link('/index.php','menuaction=expressoAdmin1_2.uimanagers.validate'),
				'display_samba_suport'		=> $this->config['expressoAdmin_samba_support'] == 'true' ? '' : 'display:none',
				'color_bg1'					=> "#E8F0F0",
				'color_bg2'					=> "#D3DCE3",
				'color_font1'				=> "#DDDDDD",
				'color_font2'				=> "#EEEEEE",
				'type'						=> "edit",
				'display_manager_select' 	=> 'none',
				'input_manager_lid_disabled'=> 'disabled',
				'error_messages'			=> $_POST['error_messages'] == '' ? '' : '<script language="JavaScript1.3">alert("'.$_POST['error_messages'].'");</script>',
				'manager_lid'				=> $_POST['manager_lid'],
				'hidden_manager_lid'		=> $_POST['manager_lid'],
				'context'					=> $_POST['context'],
				
				'input_context_fields'		=> $input_context_fields,
				'options_contexts'			=> $options_context,
				
				'hidden_manager_lid'		=> $hidden_manager_lid,
				'app_list'					=> $applications_list
			);
			$p->set_var($var);
			
			// Cria dinamicamente os langs e seta acls
			foreach ($tpl_vars as $atribute)
			{
				$acl  = strstr($atribute, 'acl_');
				$lang = strstr($atribute, 'lang_');
				// Recuperar os valores das ACLS
				if ($acl !== false)
				{
					if ($first_time)
						$p->set_var($atribute, $manager_acl[$atribute] === '1' ? 'checked' : '');
					else
						$p->set_var($atribute, $_POST[$atribute] === '1' ? 'checked' : ''); 
				}
				// Setar os langs do tpl.
				elseif($lang !== false)
				{
					$p->set_var($atribute, $this->make_lang($atribute));
				}
			}
			
			echo $p->fp('out','form');
		}
				
		function make_lang($ram_lang)
		{
			$a_lang = preg_split('/_/', $ram_lang);
			$a_lang_reverse  = array_reverse ( $a_lang, true );
			//Retira o lang do array.
			array_pop ( $a_lang_reverse );
			$a_lang  = array_reverse ( $a_lang_reverse, true );
			$a_new_lang = implode ( " ", $a_lang );
			return lang($a_new_lang);
		}
		
		function make_app_list($manager_app_list)
		{
			$this->nextmatchs = createobject('phpgwapi.nextmatchs');
			$apps = CreateObject('phpgwapi.applications',$_account_id);
			$db_perms = $apps->read_account_specific();
			$availableApps = $GLOBALS['phpgw_info']['apps'];
			
			uasort($availableApps,create_function('$a,$b','return strcasecmp($a["title"],$b["title"]);'));
			
			// Loop para criar dinamicamente uma tabela com 3 colunas, cada coluna com um aplicativo e um check box.
			$applications_list = '';
			$app_col1 = '';
			$app_col2 = '';
			$app_col3 = '';
			$total_apps = count($availableApps);
			$i = 0;

			foreach($availableApps as $app => $data)
			{
				// 1 coluna 
				if (($i +1) % 3 == 1)
				{
					if ($manager_app_list[$app] == 1)
						$checked = 'checked';
					else
						$checked = '';
					$app_col1 = sprintf("<td>%s</td><td width='10'><input type='checkbox' name='applications_list[%s]' value='1' %s %s></td>\n",
					$data['title'],$app,$checked, $disabled);
					
					if ($i == ($total_apps-1))
						$applications_list .= sprintf('<tr bgcolor="%s">%s</tr>',$this->nextmatchs->alternate_row_color(), $app_col1);
				}
				// 2 coluna
				if (($i +1) % 3 == 2)
				{
					if ($manager_app_list[$app] == 1)
						$checked = 'checked';
					else
						$checked = '';
					$app_col2 = sprintf("<td>%s</td><td width='10'><input type='checkbox' name='applications_list[%s]' value='1' %s %s></td>\n",
					$data['title'],$app,$checked, $disabled);
					
					if ($i == ($total_apps-1))
						$applications_list .= sprintf('<tr bgcolor="%s">%s%s</tr>',$this->nextmatchs->alternate_row_color(), $app_col1,$app_col2);
				}
				// 3 coluna 
				if (($i +1) % 3 == 0)
				{
					if ($manager_app_list[$app] == 1)
						$checked = 'checked';
					else
						$checked = '';
					$app_col3 = sprintf("<td>%s</td><td width='10'><input type='checkbox' name='applications_list[%s]' value='1' %s %s></td>\n",
					$data['title'],$app,$checked, $disabled);
					
					// Cria nova linha
					$applications_list .= sprintf('<tr bgcolor="%s">%s%s%s</tr>',$this->nextmatchs->alternate_row_color(), $app_col1, $app_col2, $app_col3);					
				}
				++$i;
			}
			return $applications_list;
		}
	}
?>
