<?php
	/**************************************************************************\
	* eGroupWare                                                               *
	* http://www.egroupware.org                                                *
	* The file written by Mário César Kolling <mario.kolling@serpro.gov.br>    *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	//TODO: Criar a Classe Widget.
	include_once(PHPGW_INCLUDE_ROOT.'/expressoMail1_2/inc/class.imap_functions.inc.php');

	// Classe principal do Mini Mail
	class ui_mobilemail{

		// Define as funções públicas
		var $public_functions = array(
			'mail_list'	=> True,
			'change_folder' => True,
			'change_page'	=> True,
			'show_msg'	=> True,
			'send_mail'	=> True,
			//'reply_msg'	=> True,
			'new_msg'	=> True,
			'delete_msg'	=> True,
			'init_schedule' => true,
			'add_recipients' => true,
			'add_recipient' => true,
			'list_folders' => true,
			'save_draft' => true,
			'mark_message_with_flag' => true,
			'change_search_box_type' => true,
			'index' => true
		);
	
		var $bo_mobilemail;
		var $template;
		var $common;
		var $folders; // Pastas imap
		var $current_search_box_type;
		var $current_folder; // Pasta corrente
		var $current_page; // Página corrente da lista de e-mails da pasta corrente
		var $imap_functions; // Variável que recebe um objeto do tipo class.imap_functions.inc.php
		var $allowed_tags = '<p><a><br /><em><strong><ol><li><ul><div><font>'; // Tags html que não serão removidas
			// ao mostrar corpo do e-mail


		/*
		 * @function mobilemail
		 * @abstract Método construtor da classe principal do Mini Mail
		 * @author Mário César Kolling <mario.kolling@serpro.gov.br>
		 */
		function ui_mobilemail()
		{
			$this-> load_session();
			$this->template = CreateObject('phpgwapi.Template', PHPGW_SERVER_ROOT . '/mobile/templates/'.$GLOBALS['phpgw_info']['server']['template_set']);
			$this->common	= CreateObject('mobile.common_functions');
			$this->bo_mobilemail = CreateObject('mobile.bo_mobilemail');
			
			// Recupera atributos da classe gravados na sessão
			$folders = $GLOBALS['phpgw']->session->appsession('mobilemail.folders','mobile');
			$current_folder = $GLOBALS['phpgw']->session->appsession('mobilemail.current_folder','mobile');
			$current_page = $GLOBALS['phpgw']->session->appsession('mobilemail.current_page','mobile');
			$current_search_box_type = $GLOBALS['phpgw']->session->appsession('mobilemail.current_search_box_type','mobile');
			
			// Inicializa a classe class.imap_functions.inc.php
			$this->imap_functions = new imap_functions();

			// Testa a existência dos atributos da classe recuperadas da sessão, e as carrega ou inicializa.
			if ($folders)
			{
				$this->folders = $folders;
			}
			else
			{
				$this->folders = $this->imap_functions->get_folders_list(array('noSharedFolders' => true));
			}

			if ($current_folder)
			{
				$this->current_folder = $current_folder;
				$current_page = 1;
			}
			else
			{
				$this->current_folder = 0; // Define o folder INBOX como o folder corrente
			}

			if ($current_page)
			{
				$this->current_page = $current_page;
			}
			else
			{
				$this->current_page = 1; // Define a primeira página como página padrão
			}
			
			if($current_search_box_type) 
			{
				$this->current_search_box_type = $current_search_box_type;
			}
			else {
				$this->current_search_box_type = "all";
			}

		}

		/*
		 * @function save_session
		 * @abstract Salva os atributos da classe na sessão
		 * @author Mário César Kolling <mario.kolling@serpro.gov.br>
		 */
		function save_session()
		{
			$GLOBALS['phpgw']->session->appsession('mobilemail.folders','mobile',$this->folders);
			$GLOBALS['phpgw']->session->appsession('mobilemail.current_folder','mobile',$this->current_folder);
			$GLOBALS['phpgw']->session->appsession('mobilemail.current_page','mobile',$this->current_page);
			$GLOBALS['phpgw']->session->appsession('mobilemail.current_search_box_type','mobile',$this->current_search_box_type);
		}

		/*
		 * @function change_page
		 * @abstract Troca a página de exibição da lista de e-mails, e mostra a nova página
		 * @author Mário César Kolling <mario.kolling@serpro.gov.br>
		 */
		function change_page($params)
		{
			if (isset($params['page']))
			{
				$this->current_page = $params['page'];
			}
			$this->mail_list();
			$this->save_session();
		}

		function change_search_box_type($params) {
			if (isset($params['search_box_type']))
			{
				$this->current_search_box_type = $params['search_box_type'];
				$this->current_page = 1;
			}
			$this->mail_list();
			$this->save_session();
		}

		/*
		 * @function change_folder
		 * @abstract Troca a pasta do imap, e mostra a primeira página da nova pasta
		 * @author Mário César Kolling <mario.kolling@serpro.gov.br>
		 */
		function change_folder($params)
		{
			$folder = $params['folder'];
			if (isset($folder))
			{
				$this->current_folder = $folder;
				$this->current_page = 1;
				$this->current_search_box_type = "ALL";
			}
			
				
			$GLOBALS['phpgw_info']['mobiletemplate']->set_error_msg($params["error_message"]);
			$GLOBALS['phpgw_info']['mobiletemplate']->set_success_msg($params["success_message"]);
			$this->mail_list();
			$this->save_session();
		}
		
		function mark_message_with_flag($params=array())
		{
			
			if(isset($params['msgs']))
				$params["msgs_to_set"] = implode(",",$params["msgs"]);
			
			if (isset($params["msgs_to_set"])){
			
				$return = $this->imap_functions->set_messages_flag($params);
			
				if($return)
					header('Location: index.php?menuaction=menuaction=mobile.ui_mobilemail.index&success_message='.lang("The messages were marked as seen"));
				else
					header('Location: index.php?menuaction=menuaction=mobile.ui_mobilemail.show_msg&msg_number='.$params["msgs_to_set"].'&msg_folder='.$return["msg_folder"].'&error_message='.$return["msg"]);
					
			} else {
				header('Location: index.php?menuaction=menuaction=mobile.ui_mobilemail.index&error_message='.lang("please select one e-mail"));
			}
			
			exit;
		}
		
		/*
		 * @function show_msg
		 * @abstract Mostra a mensagem de e-mail requisitada
		 * @author Mário César Kolling <mario.kolling@serpro.gov.br>
		 */
		 // TODO: retirar msg_folder dos parâmentros necessários no GET e usar $this->current_folder
		function show_msg($params = array())
		{
			$msg = $this->imap_functions->get_info_msg($params);

			$msg_number = $params['msg_number'];
			$msg_folder = $params['msg_folder'];

			// Carrega o template
			$this->template->set_file(array('view_msg' => 'view_msg.tpl'));
			$this->template->set_block('view_msg', 'page');
			$this->template->set_block('view_msg', 'operation_block');
			$this->template->set_block('view_msg', 'attachment_alert_block');
			$this->template->set_var('lang_back', lang("back"));
			$this->template->set_var('href_back',$GLOBALS['phpgw_info']['mobiletemplate']->get_back_link());
			$this->template->set_var('lang_reading_message', lang("Reading Message"));		
			$this->template->set_var('lang_confirm_delete_message', lang("Do you like to delete this message?"));
			$this->template->set_var('theme', $GLOBALS['phpgw_info']['server']['template_set']);
			$this->template->set_var('msg_folder', $msg_folder);
			$this->template->set_var('msg_number', $msg_number);

			// Define o cabeçalho do e-mail
			$this->template->set_var('lang_from', lang("From"));
			$this->template->set_var('from', $msg['from']['full']);
			$this->template->set_var('lang_to', lang("To"));
			$this->template->set_var('to', $msg['toaddress2']);
			$this->template->set_var('lang_cc', lang("cc"));
			$this->template->set_var('cc', $msg['cc']);
			$this->template->set_var('size', $this->common->borkb($msg['Size']));

			$this->template->set_var('lang_subject', lang("Subject"));
			$this->template->set_var('subject', $msg['subject']);
			$this->template->set_var('date', $msg['msg_day']." ".$msg['msg_hour']);

			// Mostra o corpo do e-mail
			$this->template->set_var('body', strip_tags($msg['body'], $this->allowed_tags)); // Usa a função strip_tags() para filtrar

			$operations = array();

			if($msg["Draft"] === "X") {
				$operations["edit_draft"]["link"] = "location.href='index.php?menuaction=mobile.ui_mobilemail.new_msg&msg_number=$msg_number&msg_folder=$msg_folder&type=use_draft'";
				$operations["edit_draft"]["lang"] = lang("edit draft");
			}	else {
				$operations["mark_as_unread"]["link"] = "location.href='index.php?menuaction=mobile.ui_mobilemail.mark_message_with_flag&flag=unseen&msgs_to_set=$msg_number&msg_folder=$msg_folder'";
				$operations["mark_as_unread"]["lang"] = lang("mark as unread");
				$operations["forward"]["link"] = "location.href='index.php?menuaction=mobile.ui_mobilemail.new_msg&msg_number=$msg_number&msg_folder=$msg_folder&type=forward'";
				$operations["forward"]["lang"] = lang("Forward");
				$operations["reply"]["link"] = "location.href='index.php?menuaction=mobile.ui_mobilemail.new_msg&msg_number=$msg_number&msg_folder=$msg_folder&type=reply'";
				$operations["reply"]["lang"] = lang("Reply");
				$operations["reply_all"]["link"] = "location.href='index.php?menuaction=mobile.ui_mobilemail.new_msg&msg_number=$msg_number&msg_folder=$msg_folder&type=reply_all'";
				$operations["reply_all"]["lang"] = lang("Reply to all");
			}

			$operations["delete"]["link"] = "delete_msg()";
			$operations["delete"]["lang"] = lang("Delete");

			foreach($operations as $index=>$operation) {
				$this->template->set_var('operation_link', $operation["link"]);
				$this->template->set_var('operation_id', $index);
				$this->template->set_var('lang_operation', $operation["lang"]);
				$this->template->parse('operation_box','operation_block', true);
			}

			if (!empty($msg['attachments']))
			{
				$attachs = "<br />".lang("This message has the follow attachments:")."<br />";
				foreach($msg['attachments'] as $key => $attach) {
					if(is_array($attach)) {
						//$attachs.=$attach['name']."&nbsp;&nbsp;&nbsp;&nbsp;";
						$attachs.="<a href='../expressoMail1_2/inc/gotodownload.php?msg_folder=".$msg_folder.
								  "&msg_number=".$msg_number."&idx_file=".$key."&msg_part=".$attach['pid'].
								  "&newfilename=".$attach['name']."&encoding=".$attach['encoding']."'>".
									  lang('Download').":&nbsp;".$attach['name']."</a><br />";
					}
				}

				$this->template->parse('attachment_alert_box','attachment_alert_block', true);
				$this->template->set_var('attachment_message', $attachs);
			}
			else
			{
				$this->template->set_var('attachment_message', lang('This message don\'t have attachment(s)'));
			}

			$GLOBALS['phpgw_info']['mobiletemplate']->set_error_msg($params["error_message"]);
			$GLOBALS['phpgw_info']['mobiletemplate']->set_content($this->template->fp('out', 'page'));
		}

		/*
		 * @function index
		 * @abstract Página inicial da aplicação mobilemail, mantém o estado atual. Ou seja, mostra lista de e-mails
		 * do folder e página definidos pelos parâmetros current_folder e current_page.
		 * @author Mário César Kolling <mario.kolling@serpro.gov.br>
		 */
		 // TODO: Talvez seja melhor voltar sempre para o Inbox e primeira página
		function index($params)
		{
			$GLOBALS['phpgw_info']['mobiletemplate']->set_error_msg($params["error_message"]);
			$GLOBALS['phpgw_info']['mobiletemplate']->set_success_msg($params["success_message"]);
			$this->mail_list();
			$this->save_session();

		}
		
		function load_session(){
			/************************************\
			 * Inicialização do expressoMail1_2 *
			\************************************/
			// Get Data from ldap_manager and emailadmin.
			$ldap_manager = CreateObject('contactcenter.bo_ldap_manager');
			$boemailadmin	= CreateObject('emailadmin.bo');
			$emailadmin_profile = $boemailadmin->getProfileList();
			$_SESSION['phpgw_info']['expressomail']['email_server'] = $boemailadmin->getProfile($emailadmin_profile[0]['profileID']);
			$_SESSION['phpgw_info']['expressomail']['user'] = $GLOBALS['phpgw_info']['user'];
			$_SESSION['phpgw_info']['expressomail']['server'] = $GLOBALS['phpgw_info']['server'];
			$_SESSION['phpgw_info']['expressomail']['ldap_server'] = $ldap_manager ? $ldap_manager->srcs[1] : null;
			$_SESSION['phpgw_info']['expressomail']['user']['email'] = $GLOBALS['phpgw']->preferences->values['email'];
		
			// Fix problem with cyrus delimiter changes in preferences.
			// Dots in names: enabled/disabled.
			$save_in_folder = @preg_replace('/INBOX//i', "INBOX".$_SESSION['phpgw_info']['expressomail']['email_server']['imapDelimiter'], $_SESSION['phpgw_info']['user']['preferences']['expressoMail']['save_in_folder']);
			$save_in_folder = @preg_replace('/INBOX./i', "INBOX".$_SESSION['phpgw_info']['expressomail']['email_server']['imapDelimiter'], $save_in_folder);
			$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['save_in_folder'] = $save_in_folder;
			// End Fix.
		
		    // Loading Admin Config Module
		    $c = CreateObject('phpgwapi.config','expressoMail1_2');
		    $c->read_repository();
		    $current_config = $c->config_data;
		    $_SESSION['phpgw_info']['server']['expressomail']['expressoMail_enable_log_messages'] = $current_config['expressoMail_enable_log_messages'];
		    // Begin Set Anti-Spam options.
		    $_SESSION['phpgw_info']['server']['expressomail']['expressoMail_command_for_ham'] = $current_config['expressoMail_command_for_ham'];
		    $_SESSION['phpgw_info']['server']['expressomail']['expressoMail_command_for_spam'] = $current_config['expressoMail_command_for_spam'];
		    $_SESSION['phpgw_info']['server']['expressomail']['expressoMail_use_spam_filter'] = $current_config['expressoMail_use_spam_filter'];
			$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['max_attachment_size'] = $current_config['expressoMail_Max_attachment_size'] ? $current_config['expressoMail_Max_attachment_size']."M" : ini_get('upload_max_filesize');

			// echo '<script> var array_lang = new Array();var use_spam_filter = \''.$current_config['expressoMail_use_spam_filter'].'\' </script>';
			// End Set Anti-Spam options.
		
		    // Set Imap Folder names options
		    $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultTrashFolder'] 	= $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultTrashFolder']	? $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultTrashFolder']		: lang("Trash");
		    $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultDraftsFolder'] 	= $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultDraftsFolder'] ? $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultDraftsFolder'] 	: lang("Drafts");
		    $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultSpamFolder'] 	= $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultSpamFolder']	? $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultSpamFolder']		: lang("Spam");
		    $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultSentFolder']	= $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultSentFolder'] 	? $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultSentFolder'] 		: lang("Sent");
		}

		/*
		 * @function print_folder_selection
		 * @abstract Imprime o folder corrente (INBOX)
		 * @author Mário César Kolling <mario.kolling@serpro.gov.br>
		 */
		function print_folder_selection()
		{
			$this->template->set_file(array('mobilemail_t' => 'mobilemail.tpl'));
			$this->template->set_block('mobilemail_t', 'inbox_folder_list');
			$this->template->set_var('lang_folder', lang('Folder'));
			$folder = str_replace("*","",lang($this->folders[$this->current_folder]['folder_name']));
			if(!$this->current_folder == 0){
				$this->template->set_var('lang_inbox', $folder.' :: <a title="'.lang('Inbox').'" href="index.php?menuaction=mobile.ui_mobilemail.mail_list&folder=0">'.lang('Inbox').'</a>');
			}else{
				$this->template->set_var('lang_inbox', lang('Inbox'));
			}
			
			//$this->template->set_var('folder_items', $folder_items);
			$this->template->parse('mobilemail_t', 'inbox_folder_list');			
			//$this->template->fpf('out', 'mobilemail_t');
			$GLOBALS['phpgw_info']['mobiletemplate']->set_content($this->template->fp('out', 'mobilemail_t'));
		}

		/*
		 * @function old_print_folder_selection
		 * @abstract Imprime na tela a caixa de seleção de folders
		 * @author Mário César Kolling <mario.kolling@serpro.gov.br>
		 */
		function old_print_folder_selection()
		{
			// Processa as options
			$folder_items = '';

			foreach ($this->folders as $i => $j)
			{

				$option_selected = '';
				$this->template->set_file(array('mobilemail_t' => 'mobilemail.tpl'));
				$this->template->set_block('mobilemail_t', 'folder_item');

				if (is_numeric($i))
				{
					if ($i == $this->current_folder)
					{
						 $option_selected = 'selected="selected"';
					}

					$this->template->set_var('option_selected', $option_selected);
					$this->template->set_var('folder_id', $j['folder_id']);
					$this->template->set_var('folder_name', $j['folder_id']); // Mudar... provavelmente usar preg_replace
					// para substituir cpf pelo nome do usuário.
					if ($j['folder_unseen'] > 0)
						$this->template->set_var('folder_unseen', ' - ('.$j['folder_unseen'].')');

					$folder_items .= $this->template->fp('mobile_t', 'folder_item');
				}
			}

			// Processa o select
			$this->template->set_file(array('mobilemail_t' => 'mobilemail.tpl'));
			$this->template->set_block('mobilemail_t', 'folder_list');
			$this->template->set_var('folder_items', $folder_items);
			$this->template->parse('mobilemail_t', 'folder_list');			
			//$this->template->pfp('out', 'mobilemail_t');
			$GLOBALS['phpgw_info']['mobiletemplate']->set_content($this->template->fp('out', 'mobilemail_t'));

		}

		/*
		 * @function mail_list
		 * @abstract Imprime a lista de e-mails
		 * @author Mário César Kolling <mario.kolling@serpro.gov.br>
		 */
		function mail_list()
		{	

			$p = $this->template;
			$p->set_file( array( 'mail_t' => 'mobilemail.tpl', 'home_search_bar' => 'search_bar.tpl' ) );

			$p->set_block('home_search_bar','search_bar');

			$p->set_var("page",$this->current_page+1);
			$p->set_var("lang_new_message",lang("new message"));
			$p->set_var("lang_new",strtoupper(lang("new")));
			$p->set_var("folder_id",$this->folders[$this->current_folder]['folder_id']);
			//translate name of the default folders
			$translated_folder_name = $this->bo_mobilemail->get_translate_default_folder_name_from_id($this->folders[$this->current_folder]["folder_id"]);
			$p->set_var("folder", (($translated_folder_name == "") ? $this->folders[$this->current_folder]["folder_name"] : $translated_folder_name) );
			
			$p->set_var("selected_".$this->current_search_box_type,"selected");
			$p->set_var("lang_back",lang("back"));
			$p->set_var('href_back',$GLOBALS['phpgw_info']['mobiletemplate']->get_back_link());
			$p->set_var("selecteds",ucfirst(lang("Selecteds")));
			$p->set_var("filter_by",lang("filter by"));
			$p->set_var("refresh",lang("refresh"));
			$p->set_var("lang_new_message",lang("new message"));
			$p->set_var('lang_search',lang('search'));
			$p->set_var("lang_more",lang("more"));
			$p->set_var("lang_messages",lang("messages"));
			
			if($GLOBALS['phpgw']->session->appsession('mobile.layout','mobile')!="mini_desktop")
				$p->set_var('search',$p->fp('out','search_bar'));
			
			$max_per_page = 
					isset($GLOBALS['phpgw_info']['user']['preferences']['mobile']['max_message_per_page'])?
					$GLOBALS['phpgw_info']['user']['preferences']['mobile']['max_message_per_page']:10; 
						
			$params = array(
				'folder' 			=> $this->folders[$this->current_folder]['folder_id'],
				'msg_range_begin' 	=> 1,
				'msg_range_end'		=> $this->current_page * $max_per_page,
				'search_box_type'	=> $this->current_search_box_type,
				'sort_box_type'		=> 'SORTARRIVAL',
				'sort_box_reverse'	=> 1
			);
			
			$messages = $this->imap_functions->get_range_msgs2($params);
			if($params['msg_range_end']<$messages['num_msgs'])
				$p->set_var("show_more","block");
			else
				$p->set_var("show_more","none");
			$this->number_of_messages = $messages['num_msgs'];
			
			unset($messages["offsetToGMT"]);
			unset($messages["tot_unseen"]);
			
			$p->set_var('mails',$this->print_mails_list($messages,true));
			
			$GLOBALS['phpgw_info']['mobiletemplate']->set_content($p->fp('out','mail_t'));
			

		}

		/*
		 * @function print_mails_list
		 * @abstract Imprime a lista de mensagens
		 * @author Mário César Kolling <mario.kolling@serpro.gov.br>
		 * @param array Um array com a lista de mensagens recuperado por $this->imap_functions->get_range_msgs2($params)
		 */
		function print_mails_list($messages,$print_checkbox=false)
		{
            $functions = $this->common;
			$p = $this->template;
			$p->set_file( array( 'mobilemail_t' => 'mails_list.tpl' ) );
			$p->set_block('mobilemail_t', 'rows_mails');
			$p->set_block('mobilemail_t', 'row_mails');
			$p->set_block('mobilemail_t', 'no_messages');
			
			if( array_key_exists("folder", $messages) )
			{
				unset($messages['folder']);
			}
			
			if( count($messages) > 1 && $messages['num_msgs'] > 0 )
			{ 
				//O array de emails tem pelo menos uma posição com o total de mensagens.
				$bg = "bg-azul";
				
				foreach($messages as $id => $message)
				{
					if(($id==='num_msgs') ||($id==='total_msgs') || ($id==='has_more_msg'))
						continue;
					if($message['from']['name'])
						$from_name = $message['from']['name'];
					else
						$from_name = $message['from']['email'];
					$bg = $bg=="bg-azul"?"bg-branco":"bg-azul";
					$p->set_var('bg',"email-geral $bg");
					
					$flag="";
										
					if($message["Unseen"]==="U") 
						$flag="email-nao-lido ";
					else
						$flag="email-lido ";
					
					if( $message["Flagged"]==="F" )
						$flag.="email-importante";
					
					$p->set_var("flag",$flag);
					if($print_checkbox)
						$p->set_var('show_check','inline');
					else
						$p->set_var('show_check','none');
					
					if($print_checkbox)
						$p->set_var("details","email-corpo");
					else
						$p->set_var("details","limpar_div margin-geral");

					$p->set_var('pre_type',$pre);
					$p->set_var('pos_type',$pos);
					$p->set_var('from',$from_name);
					$p->set_var('url_images','templates/'.$GLOBALS['phpgw_info']['server']['template_set'].'/images');
					$p->set_var('msg_number',$message["msg_number"]);
					$p->set_var('mail_time',$message['smalldate']);
					$p->set_var('mail_from',$message['from']['email']);
					$p->set_var('subject',$message['subject']?$message['subject']:"(".lang("no subject").")");
					$p->set_var('size',$functions->borkb($message['Size']));
					$p->set_var('lang_attachment',lang('attachment'));
					$p->set_var('msg_number', $message['msg_number']);
					$p->set_var('msg_folder', isset($message['msg_folder']) ? $message['msg_folder'] : $this->folders[$this->current_folder]['folder_id']);
					$p->set_var('show_attach', ($message['attachment']['number_attachments']>0) ? '' : 'none');
					$p->fp('rows','row_mails',True);
				}
			}
			else {
				$p->set_var("lang_no_results",lang("no results found"));
				$p->parse("rows","no_messages");
			}
			
			return $p->fp('out','rows_mails');

		}

		/*
		 * @funtion print_page_navigation
		 * @abstract Imprime a barra de navegação da lista de e-mails da pasta corrente. Quem chama essa função é quem faz o controle do modelo.
		 * @author Mário César Kolling <mario.kolling@serpro.gov.br>
		 * @param integer Número de páginas que serão geradas
		 * @param integer Página corrente
		 */
		// TODO: mover este método para a classe page_navigation subclasse de widget
		function print_page_navigation($number_of_pages, $page = 1)
		{

			$pages = '';

			if ($number_of_pages != 0)
			{
				// Geração das páginas
				for ($i = 1; $i <= $number_of_pages ; ++$i)
				{

          $p = CreateObject('phpgwapi.Template', PHPGW_SERVER_ROOT . '/mobile/templates/'.$GLOBALS['phpgw_info']['server']['template_set']);
					$p->set_file(array('mobilemail_t' => 'mobilemail.tpl'));
					$p->set_block('mobilemail_t', 'space');
					$p->set_block('mobilemail_t', 'begin_anchor');
					$p->set_block('mobilemail_t', 'end_anchor');
					$p->set_block('mobilemail_t', 'page_item');
					$p->set_block('mobilemail_t', 'begin_strong');
					$p->set_block('mobilemail_t', 'end_strong');

					if ($i == $page)
					{
						// Se for a página sendo gerada for a página corrente,
						// não gera a âncora e destaca o número (negrito)
						$p->set_var('end_anchor', '');
						$p->set_var('begin_anchor', '');
						$p->set_var('begin_strong', trim($p->fp('mobilemail_t', 'begin_strong')));
						$p->set_var('end_strong', trim($p->fp('mobilemail_t', 'end_strong')));
					}
					else
					{
						// Senão, gera a âncora
						$p->set_var('begin_strong', '');
						$p->set_var('end_strong', '');
						$p->set_var('end_anchor', trim($p->fp('mobilemail_t', 'end_anchor')));
						$p->set_var('begin_anchor_href', "index.php?menuaction=mobile.ui_mobilemail.change_page&folder=$this->current_folder&page=$i");
						$p->set_var('begin_anchor', trim($p->fp('mobilemail_t', 'begin_anchor')));
					}

					$p->set_var('page', $i);
					//$pages .= trim($p->fp('mobilemail_t', 'page_item'));

				}
				$pages .= " ".$page." ".lang("of")." ".$number_of_pages." ";

				// Geração dos links "anterior" e "próximo"
				$p = CreateObject('phpgwapi.Template', PHPGW_SERVER_ROOT . '/mobile/templates/'.$GLOBALS['phpgw_info']['server']['template_set']);
				$p->set_file(array('mobilemail_t' => 'mobilemail.tpl'));

				//$p->set_block('mobilemail_t', 'space');
				$p->set_block('mobilemail_t', 'mail_footer');
				$p->set_block('mobilemail_t', 'previous');
				$p->set_block('mobilemail_t', 'next');

				$next_page = $page + 1;
				$previous_page = $page - 1;

				if ($page == 1)
				{
					// Se for a primeira página, não imprime o link "anterior""
					$p->set_var('previous', '');
					if ($page == $number_of_pages)
					{
						// Se só existir uma página, não imprime o link "próximo"
						$p->set_var('next', '');
					}
					else
					{
						$p->set_var('next_href', "index.php?menuaction=mobile.ui_mobilemail.change_page&folder=$this->current_folder&page=$next_page");
						$p->set_var('next', trim($p->fp('mobilemail_t', 'next')));
					}

				}
				else if ($page == $number_of_pages)
				{
					// Se for a última página, não imprime o link "próximo"
					$p->set_var('next', '');
					$p->set_var('previous_href', "index.php?menuaction=mobile.ui_mobilemail.change_page&folder=$this->current_folder&page=$previous_page");
					$p->set_var('previous', trim($p->fp('mobilemail_t', 'previous')));
				}
				else
				{
					// Senão, imprime os links "anterior" e "próximo"
					$p->set_var('previous_href', "index.php?menuaction=mobile.ui_mobilemail.change_page&folder=$this->current_folder&page=$previous_page");
					$p->set_var('previous', trim($p->fp('mobilemail_t', 'previous')));

					$p->set_var('next_href', "index.php?menuaction=mobile.ui_mobilemail.change_page&folder=$this->current_folder&page=$next_page");
					$p->set_var('next', trim($p->fp('mobilemail_t', 'next')));
				}

				$p->set_var('pages', $pages);
				//$p->pfp('out', 'mail_footer');
				$GLOBALS['phpgw_info']['mobiletemplate']->set_content($p->fp('out', 'mail_footer'));
			}

		}

		function define_action_message($type) {
			switch($type) {
				case "clk":
				case "from_mobilecc":
				case "use_draft":
					$this->template->set_var('action_msg', lang("New message"));
					break;
				case "reply_all":
					$this->template->set_var('action_msg', lang("Reply to All"));
					break;
				case "forward":
					$this->template->set_var('action_msg', lang("Forward"));
					break;						
			}
		}
		
		/*
		 * @function new_msg()
		 * @abstract Gera o formulário para criar/enviar novo e-mail ou resposta de e-mail.
		 * @author Rommel de Brito Cysne <rommel.cysne@serpro.gov.br>
		 */
		function new_msg($params)
		{
			$flagImportant = intval($GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['use_important_flag']);
			$this->template->set_file(array('new_msg_t' => 'new_msg.tpl'));
			$this->template->set_block('new_msg_t', 'page');
			$this->template->set_var('lang_back', lang("back"));
			$this->template->set_var('href_back',$GLOBALS['phpgw_info']['mobiletemplate']->get_back_link());
			$this->template->set_var('lang_calendar', strtoupper(lang("Calendar")));
			$this->template->set_var('lang_send', strtoupper(lang("Send")));
			$this->template->set_var('lang_attachment', lang("attachment"));
			$this->template->set_var('lang_more_attachment', lang("more attachment"));
			$this->template->set_var('lang_cancel', strtoupper(lang("cancel")));
			$this->template->set_var('lang_save_draft', strtoupper(lang("save draft")));
			$this->template->set_var('lang_to', lang("To"));
			$this->template->set_var('lang_cc', lang("cc"));
			$this->template->set_var('lang_subject', lang("Subject"));
			$this->template->set_var('visible_important', ( ($flagImportant == 1 ) ? "block" : "none" ) );
			$this->template->set_var('lang_mark_as_important', lang("mark as important"));
			$this->template->set_var('lang_read_confirmation', lang("read confirmation"));
			$this->template->set_var('lang_add_history', lang("add history"));
			$this->template->set_var("show_forward_attachment","none");
			$this->template->set_var("show_check_add_history","none");
			
			if(isset($params["error_message"]))
			{
				$this->template->set_var('input_to', $_POST['input_to']);
				$this->template->set_var('input_cc', $_POST['input_cc']);
				$this->template->set_var('subject', $_POST['input_subject']);
				$this->template->set_var('msg_number', $_POST['msg_number']);
				$this->template->set_var('msg_folder', $_POST['msg_folder']);
				$this->template->set_var('body_value', $_POST['body']);
				$this->template->set_var('msg_folder', $_POST['folder']);
				$this->template->set_var('msg_number', $_POST['reply_msg_number']);
				$this->template->set_var('from', $_POST['reply_from']);
				$this->template->set_var('check_important', ( ( $_POST['check_important'] ) ? "checked" : "" ) );
				$this->template->set_var('check_read_confirmation', ( ( $_POST['check_read_confirmation'] )  ? "checked" : "" ) );
				$this->template->set_var('check_add_history', ( ( $_POST['check_add_history'] )  ? "checked" : "" ) );				
				
				$GLOBALS['phpgw_info']['mobiletemplate']->set_error_msg($params["error_message"]);
			}
			else
			{
				if (isset($params['msg_number']) )
				{
					$msg = $this->imap_functions->get_info_msg(array('msg_number' => $params['msg_number'], 'msg_folder' => $params['msg_folder'] ) );
				}
				
				if($params['type']=="clk")
				{
					$this->template->set_var('input_to', "");
					$this->template->set_var('input_cc', "");
					$this->template->set_var('subject', "");
				}
				else if($params['type']=="from_mobilecc")
				{
					$this->template->set_var('input_to', $_GET['input_to']);
					$this->template->set_var('input_cc', $_GET['input_cc']);
				}
				else if($params['type']=="reply_all"){
					$reply_to_all = $msg['from']['email'];
					if($msg['toaddress2']) $reply_to_all .= ','.$msg['toaddress2'];
					if($msg['cc']) $reply_to_all .= ','.$msg['cc'];
					if($msg['bcc']) $reply_to_all .= ','.$msg['bcc'];
					
					$array_emails = explode(',',$reply_to_all);
					$reply_to_all = '';
					
					foreach ($array_emails as $index => $email) {
						$flag = preg_match('/&lt;(.*?)&gt;/',$email,$reply);
						$email_to_add = $flag == 0 ? $email.',' : $reply[1].',';
						
						if( strpos($reply_to_all, $email_to_add) === false)
							$reply_to_all .= $email_to_add;
					}
					
					$reply_to_all = substr_replace($reply_to_all, "", strrpos($reply_to_all, ","), strlen($reply_to_all));
					
					$this->template->set_var('input_to', $reply_to_all);
					$this->template->set_var('subject', "Re:" . $msg['subject']);
	
					$this->template->set_var('msg_number', $_GET['msg_number']);
					$this->template->set_var('msg_folder', $_GET['msg_folder']);
				}
				else if($params['type']=="user_add"){
					$this->template->set_var('input_to', $params['mobile_add_contact']['mobile_mail']);
					$this->template->set_var('input_cc', $params['mobile_add_contact']['mobile_mail_cc']);
					$this->template->set_var('subject', $params['mobile_add_contact']['subject_mail']);
					$this->template->set_var('body_value', $params['mobile_add_contact']['body_mail']);
	
					$this->template->set_var('check_important', ( ( $params['mobile_add_contact']['check_important'] ) ? "checked" : "" ) );
					$this->template->set_var('check_read_confirmation', ( ( $params['mobile_add_contact']['check_read_confirmation'] )  ? "checked" : "" ) );
					$this->template->set_var('check_add_history', ( ( $params['mobile_add_contact']['check_add_history'] )  ? "checked" : "" ) );
					$this->template->set_var('msg_number', $params['msg_number']);
					$this->template->set_var('msg_folder', $params['msg_folder']);
					
					$params["type"] = $params['mobile_add_contact']['type'];
				}
				else if($params['type']=="forward")
				{
					$this->template->set_var('from', $msg['toaddress2']);
					$this->template->set_var('subject', "Enc:" . $msg['subject']);
					
					$_name	= ( isset($msg['from']['name']) ) ? $msg['from']['name'] : "";
					$_email	= ( isset($msg['from']['email']) ) ? " ".$msg['from']['email']." " : "";
					$forward_msg = "\n" . lang('At %1, %2 hours, %3 wrote:', $msg['msg_day'], $msg['msg_hour'],"\"".$_name."\"".$_email );
						
					// Usa a função strip_tags() para filtrar
					// as tags que estão presentes no corpo do e-mail.
					$this->template->set_var('body_value', "\n\n\n" . $forward_msg . "\n" . strip_tags($msg['body']) ); 
					
					$this->template->set_var('msg_number', $_GET['msg_number']);
					$this->template->set_var('msg_folder', $_GET['msg_folder']);	
					if(count($msg['attachments'])>0) {
						$this->template->set_var("lang_forward_attachment",lang("forward attachments"));
						$this->template->set_var("show_forward_attachment","block");
						$this->template->set_block("new_msg_t","forward_attach_block");
						foreach($msg['attachments'] as $forward_attach)
						{
							$value = rawurlencode(serialize(array(0=>$msg['msg_folder'],
										   1=>$msg['msg_number'],
										   3=>$forward_attach['pid'],
										   2=>$forward_attach['name'],
										   4=>$forward_attach['encoding'])));
							$this->template->set_var("value_forward_attach",$value);
							$this->template->set_var("label_forward_attach",$forward_attach['name']);
							$this->template->fp("forwarding_attachments","forward_attach_block",true);
						}
					}
				}
				else if($params['type']=="use_draft")
				{
					$this->template->set_var('input_to', $msg['toaddress2']);
					$this->template->set_var('input_cc', $msg['cc']);
					$this->template->set_var('subject', $msg['subject']);
					$this->template->set_var('body_value', strip_tags($msg['body'])); // Usa a função strip_tags() para filtrar
					$this->template->set_var('msg_number', $_GET['msg_number']);
					$this->template->set_var('msg_folder', $_GET['msg_folder']);
				}
				else if($params['type']=="reply")
				{
					$this->template->set_var('from', $msg['toaddress2']);
					$this->template->set_var('input_to', $msg['from']['email']);
					$this->template->set_var('subject', "Re:" . $msg['subject']);
					$this->template->set_var('msg_number', $_GET['msg_number']);
					$this->template->set_var('msg_folder', $_GET['msg_folder']);
				}
				else 
				{
					$this->template->set_var('input_to', "");
					$this->template->set_var('input_cc', "");
					$this->template->set_var('subject', "");
				}
			}
			
			if($params['type']=="reply" || $params['type']=="forward"  || $params['type']=="reply_all" )
				$this->template->set_var("show_check_add_history","block");
			
			//tem que ser realizado no final, pois o tipo user_add é modificado para o tipo que o originou
			$this->template->set_var('type', $params['type']);
			$this->define_action_message($params['type']);
			
			unset($_SESSION['mobile_add_contact']);
			$GLOBALS['phpgw_info']['mobiletemplate']->set_content($this->template->fp('out', 'page'));
		}

				/*
		 * @function save_draft()
		 * @abstract Função que salva o email como rascunho
		 * @author Thiago Antonius
		 */
		function save_draft($params)
		{
			$params["folder"] = "INBOX/".$_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultDraftsFolder'];
			$params["FILES"] = $_FILES["FILES"];
			$this->common->fixFilesArray($params["FILES"]);
			$params['forwarding_attachments'] = $params["forward_attachments"];
			$return = $this->imap_functions->save_msg($params);
			if($return["has_error"]) {
				$params["error_message"] = lang("draft not save")."<br />".lang("error") . $return["append"];
				$this->new_msg( $params );
			}else {
				header('Location: index.php?menuaction=menuaction=mobile.ui_home.index&success_message='.lang("draft saved").'&ignore_trace_url=true');
			}
		}
		
		/*
		 * @function send_mail()
		 * @abstract Função que realiza o envio de e-mails.
		 * @author Rommel de Brito Cysne <rommel.cysne@serpro.gov.br>
		 */
		function send_mail()
		{
			//Chamada da classe phpmailer
			include_once(PHPGW_SERVER_ROOT."/expressoMail1_2/inc/class.phpmailer.php");
			include_once(PHPGW_SERVER_ROOT."/expressoMail1_2/inc/class.imap_functions.inc.php");
			
			//Recebe os dados do form (passados pelo POST)
			$toaddress = $_POST['input_to'];
			$ccaddress = $_POST['input_cc'];
			$subject = $_POST['input_subject']; //"Mail Subject";
			$body = nl2br($_POST['body']); //"Mail body. Any text.";
			$isImportant = $_POST['check_important'];
			$addHistory = $_POST['check_add_history'];
			$readConfirmation = $_POST['check_read_confirmation'];
			$msgNumber = $_POST['reply_msg_number'];
			$attachments = $_FILES['FILES'];
			$this->common->fixFilesArray($attachments);
			$forwarding_attachments = $_POST["forward_attachments"];
			

			//Cria objeto
			$mail = new PHPMailer();
			
			$db_functions = CreateObject('expressoMail1_2.db_functions');
			
			//chama o getAddrs para carregar os emails caso seja um grupo
			$toaddress = implode(',',$db_functions->getAddrs(explode(',',$toaddress)));
			$ccaddress = implode(',',$db_functions->getAddrs(explode(',',$ccaddress)));
			
			if(!$this->imap_functions->add_recipients("to", $toaddress, &$mail, true))
			{
				$error_msg = lang("Some addresses in the To field were not recognized. Please make sure that all addresses are properly formed");
			}
			
			if(!$this->imap_functions->add_recipients("cc", $ccaddress, &$mail, true))
			{
				$error_msg = lang("Some addresses in the CC field were not recognized. Please make sure that all addresses are properly formed");
			}

			$mail->IsSMTP();
			$mail->Host = $_SESSION['phpgw_info']['expressomail']['email_server']['smtpServer'];
			$mail->Port = $_SESSION['phpgw_info']['expressomail']['email_server']['smtpPort'];

			$mail->SaveMessageInFolder = $_SESSION['phpgw_info']['user']['preferences']['expressoMail']['save_in_folder'];
			//Envia os emails em formato HTML; se false -> desativa.
			$mail->IsHTML(true);
			//Email do remetente da mensagem
			$mail->Sender = $mail->From = $_SESSION['phpgw_info']['expressomail']['user']['email'];
			//Nome do remetente do email
			$mail->SenderName = $mail->FromName = $_SESSION['phpgw_info']['expressomail']['user']['fullname'];
			//Assunto da mensagem
			$mail->Subject = $subject;
			//Corpo da mensagem
			$mail->Body .= "<br />$body<br />";
			//Important message
			if($isImportant) $mail->isImportant();
			//add history
			if($addHistory && $msgNumber)
			{
				$msg = $this->imap_functions->get_info_msg(array('msg_number' => $msgNumber ) );
				
				$_name	= ( isset($msg['from']['name']) ) ? $msg['from']['name'] : "";
				$_email	= ( isset($msg['from']['email']) ) ? " ".$msg['from']['email']." " : "";
				
				$history_msg  = "<br/><br/><br/>";
				$history_msg .= lang('At %1, %2 hours, %3 wrote:', $msg['msg_day'], $msg['msg_hour'],"\"".$_name."\"".$_email );
				$history_msg .= "<br/>";
				
				$mail->Body .= "<br/>". $history_msg . $msg['body']."<br/>";
			}
			//read confirmation
			if ($readConfirmation) $mail->ConfirmReadingTo = $_SESSION['phpgw_info']['expressomail']['user']['email'];

			$imap_functions = new imap_functions();
			if (count($attachments)>0) //Attachment
			{
				
				$total_uploaded_size = 0;
				$upload_max_filesize = str_replace("M","",$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['max_attachment_size']) * 1024 * 1024;
				
				foreach ($attachments as $attach)
				{
					$mail->AddAttachment($attach['tmp_name'], $attach['name'], "base64", $imap_functions->get_file_type($attach['name']));  // optional name					
					$total_uploaded_size = $total_uploaded_size + $attach['size'];
				}
				if( $total_uploaded_size > $upload_max_filesize){

					return $imap_functions->parse_error("message file too big");
				}
			}
			if (count($forwarding_attachments) > 0) { //forward attachment
				foreach($forwarding_attachments as $forwarding_attachment)
				{
					$file_description = unserialize(rawurldecode($forwarding_attachment));
					$fileContent = $imap_functions->getForwardingAttachment(
						$file_description[0],
						$file_description[1],
						$file_description[3],
                                                TRUE,
                                                FALSE
						);
					$fileName = $file_description[2];
					$mail->AddStringAttachment($fileContent,html_entity_decode(rawurldecode($fileName)), $file_description[4], $imap_functions->get_file_type($file_description[2]));
				}
			}

			if(!$mail->Send()) {
				$params["error_message"] = lang("Message not sent")."<br />".lang("error") . $mail->ErrorInfo;
				$this->new_msg( $params );
			}else {
				if($GLOBALS['phpgw']->session->appsession('mobile.layout','mobile')=="mini_desktop") {
					header('Location: index.php?menuaction=mobile.ui_mobilemail.index&success_message='.lang("Message sent successfully").'&ignore_trace_url=true');
				} else {
					header('Location: index.php?menuaction=mobile.ui_home.index&success_message='.lang("Message sent successfully").'&ignore_trace_url=true');
				}
			}
		}

		function delete_msg($params)
		{
			$boemailadmin		= CreateObject('emailadmin.bo');
			$emailadmin_profile = $boemailadmin->getProfileList();
			$email_server		= $boemailadmin->getProfile($emailadmin_profile[0]['profileID']);
			
			if ( !isset($params['msgs']) && !isset($params['msg_number']) )
			{
				header("Location: index.php?menuaction=mobile.ui_mobilemail.index&error_message=".lang("please select one e-mail"));
			}
			else
			{
				$imapDefaultTrashFolder = ( isset($email_server['imapDefaultTrashFolder']) && trim($email_server['imapDefaultTrashFolder']) !== "" ) ? $email_server['imapDefaultTrashFolder'] : lang("Trash");
				
				$folderTrash = "INBOX".$this->imap_functions->imap_delimiter.$imapDefaultTrashFolder;

				if( strtoupper($params["msg_folder"]) === strtoupper($folderTrash) )
				{ 	
					$params_messages = array(
												'msgs_number' => isset($params['msgs'])?implode(",",$params['msgs']):$params['msg_number'],
                                                'folder' => $folderTrash
	                                        );

					$this->imap_functions->delete_msgs($params_messages);

					$msg = lang("The messages were deleted");
				}
				else
				{
					$params_messages = array
					(
						'msgs_number'		=> isset( $params['msgs'] ) ? implode( ",",$params['msgs']) : $params['msg_number'],
						'folder'			=> $this->folders[$this->current_folder]['folder_name'],
						'new_folder_name' 	=> $imapDefaultTrashFolder,
						'new_folder' 		=> $folderTrash
					);
					
					$this->imap_functions->move_messages($params_messages);
					
					$msg = lang("The messages were moved to trash");
				}
				
				header("Location: index.php?menuaction=mobile.ui_mobilemail.index&success_message=".$msg.'&ignore_trace_url=true');
			}
		}
		
		function get_folder_number($folder_name){
			foreach($this->folders as $folderNumber => $folder){
				if($folder['folder_id'] == $folder_name){
					return $folderNumber;
				}
			}
			return 0;
		}
		
		function init_schedule() {
			$_SESSION['mobile_add_contact'] = array();
			$_SESSION['mobile_add_contact']['mobile_mail']  = $_POST['input_to'];
			$_SESSION['mobile_add_contact']['mobile_mail_cc'] = $_POST['input_cc'];
			$_SESSION['mobile_add_contact']['add_to'] = $_POST['add_to'];
			$_SESSION['mobile_add_contact']['type'] = $_POST['type'];
			$_SESSION['mobile_add_contact']['msg_number'] = $_POST['reply_msg_number'];
			$_SESSION['mobile_add_contact']['msg_folder'] = $_POST['folder'];
			$_SESSION['mobile_add_contact']['subject_mail'] = $_POST['input_subject'];
			$_SESSION['mobile_add_contact']['body_mail'] = $_POST['body'];
			$_SESSION['mobile_add_contact']['check_important'] = $_POST['check_important'];
			$_SESSION['mobile_add_contact']['check_read_confirmation'] = $_POST['check_read_confirmation'];
			$_SESSION['mobile_add_contact']['check_add_history'] = $_POST['check_add_history'];

			$ui_cc = CreateObject('mobile.ui_mobilecc');
			$ui_cc->choose_contact(array("request_from" => "ui_mobilemail.new_msg"));
		}
		
		function add_recipient() {
			if($_SESSION['mobile_add_contact']['add_to'] == "to")
				$arr_key_name = "mobile_mail";
			else
				$arr_key_name = "mobile_mail_cc";
			
			$arr_mobile_add_contact = $_SESSION['mobile_add_contact'];
			
			if(strpos($arr_mobile_add_contact[$arr_key_name], $_GET['mail']) === false)
				$arr_mobile_add_contact[$arr_key_name] .= ( (trim($arr_mobile_add_contact[$arr_key_name]) == "") ? $_GET['mail'] : ",".$_GET['mail']);
			
			unset($_SESSION['mobile_add_contact']);
			
			$this->new_msg( array( 
				'mobile_add_contact' => $arr_mobile_add_contact, 
				'type' => 'user_add', 
				'msg_number' => $arr_mobile_add_contact['msg_number'], 
				'msg_folder' => $arr_mobile_add_contact['msg_folder']));
		}
		
		function list_folders(){			
			//Define o template para mensagens de retorno da funcao
			$this->template->set_file(array('folders_t' => 'folders.tpl'));
			$this->template->set_block('folders_t','retorno');
			
			$folders_list = '';
			$array_folders = Array();
			$this->folders = $this->imap_functions->get_folders_list(array('noSharedFolders' => true));		
			
			foreach($this->folders as $id => $folder)
			{
				if((strpos($folder['folder_id'],'user')===true && !is_array($folder)) || !is_numeric($id)) 
					continue;
					$array_folders[$folder['folder_id']]['id'] = $id;
					$array_folders[$folder['folder_id']]['folder_name'] = $folder['folder_name'];
			}
			
			foreach($array_folders as $folder_id => $folder)
			{
				if(($folder_id != $_SESSION['phpgw_info']['user']['preferences']['expressoMail']['save_in_folder']) && ($folder['id'] != 0)){
					$folder_name = str_replace('*','',lang($folder['folder_name']));
					$folder_link = "index.php?menuaction=mobile.ui_mobilemail.mail_list&folder=".$folder['id'];
					$folders_list .= "<br />:: <a href=".$folder_link.">".$folder_name."</a>";
				}
			}
			$this->template->set_var('folders_list', $folders_list);
			$this->template->pfp('out','retorno');				   

		}

	}
?>
