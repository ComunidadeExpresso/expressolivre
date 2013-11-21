<?php
		/*************************************************************************** 
		* Expresso Livre                                                           * 
		* http://www.expressolivre.org                                             * 
		* --------------------------------------------                             * 
		*  This program is free software; you can redistribute it and/or modify it * 
		*  under the terms of the GNU General Public License as published by the   * 
		*  Free Software Foundation; either version 2 of the License, or (at your  * 
		*  option) any later version.                                              * 
		\**************************************************************************/ 
		
	/**************************************************************************/
	ini_set("display_errors","1");
	$GLOBALS['phpgw_info']['flags'] = array(
		'currentapp' => 'expressoMail1_2',
		'noheader'   => True, 
		'nonavbar'   => True,
		'enable_nextmatchs_class' => True
	);


	require_once('../header.inc.php');

	
	if($_POST["save"]=="save") {
		if ($GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['keep_after_auto_archiving'])
			$GLOBALS['phpgw']->preferences->change('expressoMail','keep_after_auto_archiving',$_POST['keep_after_auto_archiving']);
		else
			$GLOBALS['phpgw']->preferences->add('expressoMail','keep_after_auto_archiving',$_POST['keep_after_auto_archiving']);

		$GLOBALS['phpgw']->preferences->save_repository();
		$url = ($GLOBALS['phpgw']->link('/'.'expressoMail1_2'));
		$GLOBALS['phpgw']->redirect($url);
	}
	else {
		$GLOBALS['phpgw']->preferences->read_repository();
		if ($GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['keep_after_auto_archiving'])
			$GLOBALS['phpgw']->template->set_var('keep_after_auto_archiving_Yes_selected','selected');
		else {
			$GLOBALS['phpgw']->template->set_var('keep_after_auto_archiving_No_selected','');
			$GLOBALS['phpgw']->template->set_var('keep_after_auto_archiving_Yes_selected','');
		}
		
		$boemailadmin	= CreateObject('emailadmin.bo');
		$emailadmin_profile = $boemailadmin->getProfileList();
		$_SESSION['phpgw_info']['expressomail']['email_server'] = $boemailadmin->getProfile($emailadmin_profile[0]['profileID']);
		$_SESSION['phpgw_info']['expressomail']['user'] = $GLOBALS['phpgw_info']['user'];
		$_SESSION['phpgw_info']['expressomail']['server'] = $GLOBALS['phpgw_info']['server'];
		$_SESSION['phpgw_info']['expressomail']['ldap_server'] = $ldap_manager ? $ldap_manager->srcs[1] : null;
		$_SESSION['phpgw_info']['expressomail']['user']['email'] = $GLOBALS['phpgw']->preferences->values['email'];
		
		$GLOBALS['phpgw']->common->phpgw_header();
		print parse_navbar();
	
		$GLOBALS['phpgw']->template->set_file(array(
			'expressoMail_prefs' => 'programed_archiving.tpl'
		));
		
		//Checa gears instalado
		$check_gears = "if (!window.google || !google.gears) {
					temp = confirm('".lang('To use local messages you have to install google gears. Would you like to be redirected to gears installation page?')."');
					if (temp) {
						location.href = \"http://gears.google.com/?action=install&message=\"+
						\"Para utilizar o recurso de mensagens locais, instale o google gears&return=\" + document.location.href;
					}
					else {
						alert('".lang('Impossible install offline without Google Gears')."');
						location.href='../preferences/';
					}
			}";
		
		//Bibliotecas JS.
		$obj = createobject("expressoMail1_2.functions");
		echo "<script src='js/gears_init.js'></script>";
		$libs =  $obj -> getFilesJs("js/main.js," .
								"js/local_messages.js," .
								"js/offline_access.js," .
								"js/mail_sync.js," .
								"js/md5.js,",
								$GLOBALS['phpgw_info']['flags']['update_version']);
		
		$GLOBALS['phpgw']->template->set_var('libs',$libs);
		$GLOBALS['phpgw']->template->set_var('lib_modal',"<script src='js/modal/modal.js'></script>");
	
	
		//combo folders
		$imap_functions = CreateObject('expressoMail1_2.imap_functions');
		$all_folders = $imap_functions->get_folders_list();
		$options = " ";
		foreach($all_folders as $folder) {
			if(strpos($folder['folder_id'],'user')===false && is_array($folder)) {
				$folder_name = (strtoupper($folder['folder_name'])=="INBOX" ||
								strtoupper($folder['folder_name'])=="SENT" ||
								strtoupper($folder['folder_name'])=="TRASH" ||
								strtoupper($folder['folder_name'])=="DRAFTS")?lang($folder['folder_name']):$folder['folder_name'];
				
				$folder['folder_id'] = str_replace(" ","#",$folder['folder_id']);
				
				$options.="<option value='".$folder['folder_id']."'>".$folder_name."</option>";
			}
				
		}
		$GLOBALS['phpgw']->template->set_var('all_folders',$options);
		echo '<script language="javascript">var array_lang = new Array();</script>';
		include("inc/load_lang.php");	
	
		$GLOBALS['phpgw']->template->set_var('lang_Would_you_like_to_keep_messages_on_server_?',lang("Would you like to keep archived messages?"));
		$GLOBALS['phpgw']->template->set_var('lang_check_redirect',$check_gears);
		$GLOBALS['phpgw']->template->set_var('lang_folders_to_sync',lang('Folders to sync'));
		$GLOBALS['phpgw']->template->set_var('lang_add',lang('Add'));
		$GLOBALS['phpgw']->template->set_var('lang_save',lang('Save'));
		$GLOBALS['phpgw']->template->set_var('lang_Yes',lang('Yes'));
		$GLOBALS['phpgw']->template->set_var('lang_No',lang('No'));
		$GLOBALS['phpgw']->template->set_var('account_id',$GLOBALS['phpgw_info']['user']['account_id']);
		$GLOBALS['phpgw']->template->set_var('lang_rem',lang('Remove'));
		$GLOBALS['phpgw']->template->set_var('go_back','../preferences/');
	
		$GLOBALS['phpgw']->template->set_var('value_save_in_folder',$o_folders);
		$GLOBALS['phpgw']->template->set_var('lang_save',lang('Save'));
		$GLOBALS['phpgw']->template->set_var('lang_cancel',lang('Cancel'));
		
		$GLOBALS['phpgw']->template->set_var('save_action',$GLOBALS['phpgw']->link('/'.'expressoMail1_2'.'/programed_archiving.php'));
		$GLOBALS['phpgw']->template->set_var('th_bg',$GLOBALS['phpgw_info']["theme"][th_bg]);
	
		$tr_color = $GLOBALS['phpgw']->nextmatchs->alternate_row_color($tr_color);
		$GLOBALS['phpgw']->template->set_var('tr_color1',$GLOBALS['phpgw_info']['theme']['row_on']);
		$GLOBALS['phpgw']->template->set_var('tr_color2',$GLOBALS['phpgw_info']['theme']['row_off']);
	
		$GLOBALS['phpgw']->template->parse('out','expressoMail_prefs',True);
		$GLOBALS['phpgw']->template->p('out');
	}
	
	
?>
