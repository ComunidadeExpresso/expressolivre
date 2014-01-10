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

	class uisectors
	{
		var $public_functions = array
		(
			'list_sectors'					=> True,
			'add_sector'					=> True,
			'validate_data_sectors_add'		=> True,
			'edit_sector'					=> True,
			'validate_data_sectors_edit'	=> True,
			'delete_sector'					=> True,
			'css'                                                   => True, 
 	                'view_cota'                                             => True 
		);

		var $bo;
		var $nextmatchs;
		var $functions;
			
		function uisectors()
		{
			$this->bo = CreateObject('expressoAdmin.bosectors');
			$this->so = $this->bo->so;
			$this->functions = $this->bo->functions;
			$this->nextmatchs = createobject('phpgwapi.nextmatchs');
		}
		
		function list_sectors()
		{
			$manager_lid = $GLOBALS['phpgw']->accounts->data['account_lid'];
			$acl = $this->functions->read_acl($manager_lid);
			$contexts = $acl['contexts'];
			foreach ($acl['contexts_display'] as $index=>$tmp_context)
			{
				$context_display .= '<br>'.$tmp_context;
			}

			// Verifica se o administrador tem acesso.
			if (!$this->functions->check_acl($manager_lid,'list_sectors'))
			{
				$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/expressoAdmin/inc/access_denied.php'));
			}
			
			unset($GLOBALS['phpgw_info']['flags']['noheader']);
			unset($GLOBALS['phpgw_info']['flags']['nonavbar']);

			$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['expressoAdmin']['title'].' - '.lang('Sectors');
			$GLOBALS['phpgw']->common->phpgw_header();

			$p = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
			$p->set_file(array('sectors' => 'sectors.tpl'));
			$p->set_block('sectors','list','list');
			$p->set_block('sectors','row','row');
			$p->set_block('sectors','row_empty','row_empty');
			
			//$sectors_info = $this->functions->get_sectors_list($contexts); 
 	                $sectors_info = $this->functions->get_organizations2($contexts); 
			
			$var = Array(
				'th_bg'					=> $GLOBALS['phpgw_info']['theme']['th_bg'],
				'back_url'				=> $GLOBALS['phpgw']->link('/expressoAdmin/index.php'),
				'context_display'		=> $context_display,
				'lang_inactives'                        => lang('list inactives'), 
 	                        'lang_ver_cota'         => lang('view cota')
			);

			$p->set_var($var);
			$p->set_var($this->functions->make_dinamic_lang($p, 'list'));

			if (!count($sectors_info))
			{
				$p->set_var('message',lang('No matches found'));
				$p->parse('rows','row_empty',True);
			}
			else
			{
				if ($this->functions->check_acl($manager_lid,'edit_sectors'))
				{
					$can_edit = True;
				}

				if ($this->functions->check_acl($manager_lid,'delete_sectors'))
				{
					$can_delete = True;
				}

				foreach($sectors_info as $context=>$sector)
				{
					$tr_color = $this->nextmatchs->alternate_row_color($tr_color);
					$var = Array(
						'tr_color'    => $tr_color,
						'sector_name'  => $sector['display'], 
 	                                        'cota_link' => $this->row_action('view','cota',$sector['dn']), 
 	                                        'add_link' => $this->row_action('add','sector',$sector['dn']) 
					);	
					
					$var['sector_name'] =  utf8_decode($var['sector_name']);

									
					if(isset($GLOBALS['phpgw_info']['server']['time_to_account_expires']))
						$var['inactives_link'] = $this->row_action('list_inactive','users',$sector['dn'],'uiaccounts'); 
					else
						$var['inactives_link'] = lang('disabled');

					$p->set_var($var);

					if ($can_edit)
					{
						$p->set_var('edit_link',$this->row_action('edit','sector',$sector['dn'])); 
					}
					else
					{
						$p->set_var('edit_link','&nbsp;');
					}

					if ($can_delete)
					{
						$p->set_var('delete_link',$this->row_action('delete','sector',$sector['dn'])); 
					}
					else
					{
						$p->set_var('delete_link','&nbsp;');
					}
					
					$p->fp('rows','row',True);
				}
			}
			$var = Array(
				'action' => $GLOBALS['phpgw']->link('/index.php','menuaction=expressoAdmin.uisectors.add_sector')
			);
			$p->set_var($var);
			
				$p->set_var('input_add','<input type="submit" value="' . lang('Add Sectors') . '">');
			
			$p->parse('rows','row_empty',True);
			$p->pfp('out','list');
		}

		
		function add_sector($context='')
		{
			$manager_lid = $GLOBALS['phpgw']->accounts->data['account_lid'];
			$acl = $this->functions->read_acl($manager_lid);

			$manager_contexts = $acl['contexts'];
			$combo_manager_org = '';

			if ( array_key_exists( 'context', $_GET ) )
			{
				$context = $_GET['context'];
				$combo_manager_org = $this->functions->get_organizations( $context, '', true, true, true );
				$combo_manager_org = preg_replace("/\\\([0-9A-Fa-f]{2})/e", "''.chr(hexdec('\\1')).''",$combo_manager_org);
				$combo_manager_org = utf8_decode($combo_manager_org);
			}
			else
			{
				foreach ($manager_contexts as $index=>$context)
					$combo_manager_org .= $this->functions->get_organizations( $context );
			}

			// Verifica se tem acesso a este modulo
			if (!$this->functions->check_acl($manager_lid,'create_sectors'))
			{
				$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/expressoAdmin/inc/access_denied.php'));
			}

			unset($GLOBALS['phpgw_info']['flags']['noheader']);
			unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
			$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['expressoAdmin']['title'].' - '.lang('Create Sector');
			$GLOBALS['phpgw']->common->phpgw_header();
			
			// Set o template
			$p = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
			$p->set_file(Array('create_sector' => 'sectors_form.tpl'));
			$p->set_block('create_sector','list','list');

			// Seta variaveis utilizadas pelo tpl.
			$var = Array(
				'action'			=> $GLOBALS['phpgw']->link('/index.php','menuaction=expressoAdmin.uisectors.validate_data_sectors_add'),
				'back_url'			=> $GLOBALS['phpgw']->link('/index.php','menuaction=expressoAdmin.uisectors.list_sectors'),
				'th_bg'				=> $GLOBALS['phpgw_info']['theme']['th_bg'],
				'context'			=> $context == '' ? $GLOBALS['phpgw_info']['server']['ldap_context'] : $context,
				'sector'			=> $_POST['sector'],
				'associated_domain' => $_POST['associated_domain'],
				'disk_quota'        => $_POST['disk_quota'], 
 	            'users_quota'       => $_POST['users_quota'], 
				'manager_org'		=> $combo_manager_org,
				'sector_visible_checked'=> $_POST['sector_visible'] ? 'checked' : '',
				'error_messages'	=> $_POST['error_messages'] == '' ? '' : "<script type='text/javascript'>alert('".$_POST['error_messages']."')</script>",
			);

		        if($this->functions->db_functions->use_cota_control()) { 
		                $var["open_comment_cotas"] = ""; 
		                $var["close_comment_cotas"] =""; 
		        } 
		        else { 
		                $var["open_comment_cotas"] = "<!--"; 
		                $var["close_comment_cotas"] ="-->"; 
		        }        
 		                         			
			$var['sector'] = preg_replace("/\\\([0-9A-Fa-f]{2})/e", "''.chr(hexdec('\\1')).''",$var['sector']);
			$var['sector'] = utf8_decode($var['sector']);
			
			$p->set_var($var);
			$p->set_var($this->functions->make_dinamic_lang($p, 'list'));

			$p->pfp('out','create_sector');
		}
		
		function edit_sector()
		{
			$account_lid = $GLOBALS['phpgw']->accounts->data['account_lid'];
			$acl = $this->functions->read_acl($account_lid);
			$manager_context = $acl[0]['context'];
			
			$context = $_GET['context'];

			$context = utf8_encode($context);
			$context = preg_replace("/\\\([0-9A-Fa-f]{2})/e", "''.chr(hexdec('\\1')).''",$context);

			$combo_manager_org = $this->functions->get_organizations($context, '', true, true, true);

			$combo_manager_org = utf8_decode(preg_replace("/\\\([0-9A-Fa-f]{2})/e", "''.chr(hexdec('\\1')).''",$combo_manager_org));

			$combo_manager_org = substr( $combo_manager_org, 0, ( strpos($combo_manager_org, '</option>') + 9 ) );
			$combo_manager_org =utf8_decode($combo_manager_org);
			$a_tmp = explode(",", $context); 
 			$sector_name = utf8_decode( str_replace('ou=' , '' ,$a_tmp[0]));
			
			// Verifica se tem acesso a este modulo
			if (!$this->functions->check_acl($account_lid,'edit_sectors'))
			{
				$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/expressoAdmin/inc/access_denied.php'));
			}

			unset($GLOBALS['phpgw_info']['flags']['noheader']);
			unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
			$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['expressoAdmin']['title'].' - '.lang('Edit Sector');
			$GLOBALS['phpgw']->common->phpgw_header();
			
			// Set o template
			$p = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
			$p->set_file(Array('edit_sector' => 'sectors_form.tpl'));
			$p->set_block('edit_sector','list','list');
			
			if (!$_POST)
			{
				$sector_info = $this->so->get_info($_GET['context']);
                $sector_disk_quota = $sector_info[0]['diskquota'][0]; 
 	            $sector_users_quota = $sector_info[0]['usersquota'][0]; 
 	            $sector_associated_domain = $sector_info[0]['associateddomain'][0]; 
				$_POST['sector_visible'] = $sector_info[0]['phpgwaccountvisible'][0];
			} 
			
			// Seta variaveis utilizadas pelo tpl.
			$var = Array(
				'action'			=> $GLOBALS['phpgw']->link('/index.php','menuaction=expressoAdmin.bosectors.save_sector'),
				'back_url'			=> $GLOBALS['phpgw']->link('/index.php','menuaction=expressoAdmin.uisectors.list_sectors'),
				'th_bg'				=> $GLOBALS['phpgw_info']['theme']['th_bg'],
				'context'			=> $context == '' ? $manager_context : $context,
				'sector'			=> $_POST['sector'] == '' ? $sector_name : $_POST['sector'],
				'manager_org'		=> $combo_manager_org,
				'sector_visible_checked'=> $_POST['sector_visible'] ? 'checked' : '',
                'disk_quota'        => $_POST['disk_quota'] == '' ? $sector_disk_quota : $_POST['disk_quota'], 
 	            'users_quota'       => $_POST['users_quota'] == '' ? $sector_users_quota : $_POST['users_quota'], 
 	            'associated_domain'        => $_POST['associated_domain'] == '' ? $sector_associated_domain : $_POST['associated_domain'], 
				'lang_add'			=> lang('Add'),
				'disable'			=> 'disabled',
				'error_messages'	=> $_POST['error_messages'] == '' ? '' : "<script type='text/javascript'>alert('".$_POST['error_messages']."')</script>",
			        'lang_disk_quota'   => lang('disk quota'), 
 		                'lang_users_quota'  => lang('users quota') 
 	                ); 
 	                if($this->functions->db_functions->use_cota_control()) { 
 	                        $var["open_comment_cotas"] = ""; 
 	                        $var["close_comment_cotas"] =""; 
 	                } 
 	                else { 
 	                        $var["open_comment_cotas"] = "<!--"; 
 	                        $var["close_comment_cotas"] ="-->"; 
 	                } 

			$var['sector'] = preg_replace("/\\\([0-9A-Fa-f]{2})/e", "''.chr(hexdec('\\1')).''",$var['sector']);
			$var['sector'] = utf8_decode($var['sector']);
			
			$p->set_var($var);
			$p->set_var($this->functions->make_dinamic_lang($p, 'list'));

			$p->pfp('out','edit_sector');
		}
			
                function view_cota() 
                { 
                        $context = utf8_decode($_GET['context']); 
                        $a_tmp = explode(",", $context); 
 
                        $sector_name = str_replace('ou=' , '' ,$a_tmp[0]); 
                        if($this->functions->db_functions->use_cota_control()) { 
                                $sector_info = $this->so->get_info($_GET['context']); 
                                $sector_disk_cota = $sector_info[0]['diskquota'][0]; 
                                $sector_users_cota = $sector_info[0]['usersquota'][0]; 
                        } 
                        else { 
                                $sector_disk_cota = lang('cotas control disabled'); 
                                $sector_users_cota = lang('cotas control disabled');                             
                        } 
 
 
                        unset($GLOBALS['phpgw_info']['flags']['noheader']); 
                        unset($GLOBALS['phpgw_info']['flags']['nonavbar']); 
                        $GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['expressoAdmin']['title'].' - '.lang('Edit Sector'); 
                        $GLOBALS['phpgw']->common->phpgw_header(); 
                         
                        // Set o template 
                        $p = CreateObject('phpgwapi.Template',PHPGW_APP_TPL); 
                        $p->set_file(Array('ver_cota' => 'sectors_cota.tpl')); 
                         
                 
                        // Seta variaveis utilizadas pelo tpl. 
                        $var = Array( 
                                'back_url'                      => $GLOBALS['phpgw']->link('/index.php','menuaction=expressoAdmin.uisectors.list_sectors'), 
                                'th_bg'                         => $GLOBALS['phpgw_info']['theme']['th_bg'], 
                                'context'                       => $context, 
                                'sector'                        => $sector_name, 
                                'disk_cota'        => $sector_disk_cota, 
                                'users_cota'       => $sector_users_cota, 
                                'actual_users'          => $this->functions->get_num_users($context), 
                                'actual_disk'           => round($this->functions->get_actual_disk_usage($context),2),                           
                                 
                                'lang_back'                     => lang('Back'), 
                                'lang_context'          => lang('Context'), 
                                'lang_sector_name'      => lang('Sector name'), 
                                'lang_disk_cota'   => lang('disk usage cota'), 
                                'lang_users_cota'  => lang('user number cota'), 
                                'lang_user_number'  => lang('user number'), 
                                'lang_disk_used'    => lang('disk usage'),                               
                                'error_messages'        => $_POST['error_messages'] == '' ? '' : "<script type='text/javascript'>alert('".$_POST['error_messages']."')</script>", 
                        ); 
                        $p->set_var($var); 
 
                        $p->pfp('out','ver_cota'); 
                }         
		
		function validate_data_sectors_add()
		{
			$sector_name	= $_POST['sector'];
			$context		= $_POST['context'];
			
			// Verifica se o nome do sector nao esta vazio.
			if ($sector_name == '')
			{
				$_POST['error_messages'] = lang('Sector name is empty.');
				ExecMethod('expressoAdmin.uisectors.add_sector');
				return;
			}
			
			// Verifica se o nome do setor existe no contexto atual.
			if ($this->so->exist_sector_name($sector_name, $context))
			{
				$_POST['error_messages'] = lang('Sector name already exist.');
				ExecMethod('expressoAdmin.uisectors.add_sector');
				return;
			}
			
			ExecMethod('expressoAdmin.bosectors.create_sector');
		}

		function delete_sector()
		{
			$account_lid = $GLOBALS['phpgw']->accounts->data['account_lid'];
			$acl = $this->functions->read_acl($account_lid);
			$manager_context = $acl[0]['context'];
			
			$manager_context = utf8_encode($manager_context);
			$manager_context = preg_replace("/\\\([0-9A-Fa-f]{2})/e", "''.chr(hexdec('\\1')).''",$manager_context);
			
			// Verifica se tem acesso a este modulo
			if (!$this->functions->check_acl($account_lid,'delete_sectors'))
			{
				$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/expressoAdmin/inc/access_denied.php'));
			}
			
			unset($GLOBALS['phpgw_info']['flags']['noheader']);
			unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
			$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['expressoAdmin']['title'].' - '.lang('Delete Sectors');
			$GLOBALS['phpgw']->common->phpgw_header();

			// Set o template
			$p = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
			$p->set_file(Array('delete_sector' => 'sectors_delete.tpl'));
			$p->set_block('delete_sector','list','list');
			
			$tmp_sector_name = $_GET['context'];
			$tmp_sector_name = explode(",",$tmp_sector_name);
			$tmp_sector_name = $tmp_sector_name[0];
			$tmp_sector_name = explode("=", $tmp_sector_name);
			$sector_name = $tmp_sector_name[1];
			

			// Get users of sector
			$sector_users		= $this->so->get_sector_users(utf8_encode($_GET['context']));
			$sector_groups		= $this->so->get_sector_groups(utf8_encode($_GET['context']));
			$sector_subsectors	= $this->so->get_sector_subsectors(utf8_encode($_GET['context']));
			
			$users_list = '';
			foreach ($sector_users as $user)
			{
				$users_list .= $user['cn'][0] . '<br>';	
			}
			
			$groups_list = '';
			foreach ($sector_groups as $group)
			{
				$groups_list .= $group['cn'][0] . '<br>';	
			}

			$subsectors_list = '';
			foreach ($sector_subsectors as $subsector)
			{
				if ($subsector['dn'] != $_GET['context'])
					$subsectors_list .= utf8_decode($subsector['ou'][0] . '<br>');
			}

			// Seta variaveis utilizadas pelo tpl.
			$var = Array(
				'color_bg1'					=> "#E8F0F0",
				'manager_context'			=> $manager_context,
				'dn'						=> $_GET['context'],
				'back_url'					=> $GLOBALS['phpgw']->link('/index.php','menuaction=expressoAdmin.uisectors.list_sectors'),
				'action'					=> $GLOBALS['phpgw']->link('/index.php','menuaction=expressoAdmin.bosectors.delete_sector'),
				
				'sector_name'				=> $sector_name,
				'users_list'				=> $users_list,
				'groups_list'				=> $groups_list,
				'sectors_list'				=> $subsectors_list 
			);
			$var['sector_name'] =utf8_decode( preg_replace("/\\\([0-9A-Fa-f]{2})/e", "''.chr(hexdec('\\1')).''",$var['sector_name']));

			$p->set_var($var);
			$p->set_var($this->functions->make_dinamic_lang($p, 'list'));
			$p->pfp('out','delete_sector');			
		}
		
		function row_action($action,$type,$context,$class='uisectors')
		{
			return '<a href="'.$GLOBALS['phpgw']->link('/index.php',Array(
				'menuaction'		=> 'expressoAdmin.'.$class.'.'.$action.'_'.$type,
				'context'		=> $context
			)).'"> '.lang($action).' </a>';
		}
		
		function css()
		{
			$appCSS = '';
			return $appCSS;
		}
		
	}
?>
