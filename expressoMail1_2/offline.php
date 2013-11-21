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
		
	$GLOBALS['phpgw_info']['flags'] = array(
		'noheader' => False,
		'nonavbar' => True,
		'currentapp' => 'expressoMail1_2',
		'update_version'	=> '1.222',
		'enable_nextmatchs_class' => True
	);
	
	include('../header.inc.php');
	$GLOBALS['phpgw_info']['flags']['currentapp'] = "expresso_offline";
	echo "


<div  id=\"toolbar\" style=\"visibility:hidden;position:absolute\">


</div>

	<div id=\"divSubContainer\">
		<table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
		<tr>
			
		
		<!-- End Sidebox Column -->
		<!-- Applicationbox Column -->
		<td id=\"tdAppbox\" valign=\"top\" style=\"padding-left:0px;\" class='content-menu'>
		<div id=\"divAppboxHeader\" style=\"display:none\">--online compatibility</div>
		<div id=\"divStatusBar\">".lang('ExpressoMail Offline')."</div>
		<div id=\"divAppbox\">

		<table id=\"tableDivAppbox\" width=\"98%\" cellpadding=\"0\" cellspacing=\"0\">
		<tr><td>
	";
	/*echo parse_navbar();
	exit;*/
	$update_version = $GLOBALS['phpgw_info']['flags']['update_version'];
	//Info do usuário
	echo "<script language='javascript'> 
				var account_id = null;
				var expresso_offline = true;
				var template = 'default';
		  </script>";
	echo "<script src='js/globals.js?".$update_version."' type='text/javascript'></script>";
	echo "<script src='js/sniff_browser.js?".$update_version."' type='text/javascript'></script>";
	echo '<script type="text/javascript" src="../phpgwapi/js/wz_dragdrop/wz_dragdrop.js?'.$update_version.'"></script>
		<script type="text/javascript" src="../phpgwapi/js/dJSWin/dJSWin.js?'.$update_version.'"></script>';
	
	//Configurações do módulo
    $c = CreateObject('phpgwapi.config','expressoMail1_2');
    $c->read_repository();
    $current_config = $c->config_data;
	
	//Local messages
	$_SESSION['phpgw_info']['server']['expressomail']['enable_local_messages'] = $current_config['enable_local_messages'];


/*	// Get Data from ldap_manager and emailadmin.
	$ldap_manager = CreateObject('contactcenter.bo_ldap_manager');
	$boemailadmin	= CreateObject('emailadmin.bo');
	$emailadmin_profile = $boemailadmin->getProfileList();
	$_SESSION['phpgw_info']['expressomail']['email_server'] = $boemailadmin->getProfile($emailadmin_profile[0]['profileID']);
	$_SESSION['phpgw_info']['expressomail']['user'] = $GLOBALS['phpgw_info']['user'];
	$_SESSION['phpgw_info']['expressomail']['server'] = $GLOBALS['phpgw_info']['server'];
	$_SESSION['phpgw_info']['expressomail']['ldap_server'] = $ldap_manager ? $ldap_manager->srcs[1] : null;
	$_SESSION['phpgw_info']['expressomail']['user']['email'] = $GLOBALS['phpgw']->preferences->values['email'];
	if($current_config['enable_local_messages']!='True')  {
		$GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['use_local_messages'] = 0;
	}
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail'] = array(
			'voip_enabled'						=> "0",
//			'outoffice'							=> $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['outoffice'],
//			'telephone_number'					=> $GLOBALS['phpgw_info']['user']['telephonenumber'],
			'max_email_per_page' 				=> "25",
    		'save_deleted_msg' 					=> "0",
//            'delete_trash_messages_after_n_days'=> $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['delete_trash_messages_after_n_days'],
    		'delete_and_show_previous_message' 	=> "1",
    		'alert_new_msg' 					=> "0",
//    		'mainscreen_showmail' 				=> $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['mainscreen_showmail'] ? $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['mainscreen_showmail'] : "0",
    		'signature' 						=> "0",
    		'use_signature' 					=> "0",
			'hide_folders' 						=> "1",    		
    		'save_in_folder' 					=> $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['save_in_folder'] ? $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['save_in_folder'] : "-1",
    		'line_height' 						=> $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['line_height'] ? $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['line_height'] : "30",
    		'font_size' 						=> "11",
    		'use_shortcuts' 					=> "0",
    		'auto_save_draft'					=> "0",
    		'use_local_messages'	 			=> "1",			
    		'keep_archived_messages'	 		=> "0"
    		
	);*/
	
	$template = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
	$template->set_var("txt_loading",lang("Loading"));
	$template->set_var("txt_clear_trash",lang("message(s) deleted from your trash folder."));
    $template->set_var("new_message", lang("New Message"));
	$template->set_var("lang_inbox", lang("Inbox"));
    $template->set_var("refresh", lang("Refresh"));
    $template->set_var("tools", lang("Tools"));	
	$template->set_var("lang_Open_Search_Window", lang("Open search window") . '...');
	$template->set_var("lang_search_user", lang("Search user") . '...'); 
	$template->set_var("upload_max_filesize",ini_get('upload_max_filesize'));
	$template->set_var("msg_folder",$_GET['msgball']['folder']);
	$template->set_var("msg_number",$_GET['msgball']['msgnum'] ? $_GET['msgball']['msgnum'] : $_GET['to']);
	$template->set_var("user_email",$_SESSION['phpgw_info']['expressomail']['user']['email']);
	$template->set_var("logoff",lang("Logoff"));
	$template->set_var("template",'default');
	
	$acc = CreateObject('phpgwapi.accounts');
	if(isset($_GET['inside'])) {
		$template->set_var("start_coment_logoff","<tr><td class='content-menu-td'>&nbsp;</td></tr><!--");
		$template->set_var("end_coment_logoff","-->");
	}else  {
		$template->set_var("start_coment_logoff"," ");
		$template->set_var("end_coment_logoff","");
	}
	$template->set_var("user_organization", $acc->get_organization($GLOBALS['phpgw_info']['user']['account_dn']));
	$template->set_var("cyrus_delimiter",$_SESSION['phpgw_info']['expressomail']['email_server']['imapDelimiter']);	
	// Fix problem with cyrus delimiter changes in preferences.
	// Dots in names: enabled/disabled.
	$save_in_folder = @preg_replace('/INBOX//i', "INBOX".$_SESSION['phpgw_info']['expressomail']['email_server']['imapDelimiter'], $_SESSION['phpgw_info']['user']['preferences']['expressoMail']['save_in_folder']);
	$save_in_folder = @preg_replace('/INBOX./i', "INBOX".$_SESSION['phpgw_info']['expressomail']['email_server']['imapDelimiter'], $save_in_folder);
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['save_in_folder'] = $save_in_folder;
	// End Fix.
	
	$template->set_file(Array('expressoMail' => 'index_offline.tpl'));
	$template->set_block('expressoMail','list');
	$template->pfp('out','list');
	$GLOBALS['phpgw']->common->phpgw_footer();
    
    // Loading Admin Config Module

    $_SESSION['phpgw_info']['server']['expressomail']['expressoMail_enable_log_messages'] = $current_config['expressoMail_enable_log_messages'];
    // Begin Set Anti-Spam options.
    $_SESSION['phpgw_info']['server']['expressomail']['expressoMail_command_for_ham'] = $current_config['expressoMail_command_for_ham'];
    $_SESSION['phpgw_info']['server']['expressomail']['expressoMail_command_for_spam'] = $current_config['expressoMail_command_for_spam'];
    $_SESSION['phpgw_info']['server']['expressomail']['expressoMail_use_spam_filter'] = $current_config['expressoMail_use_spam_filter'];   
    echo '<script> var use_spam_filter = \''.$current_config['expressoMail_use_spam_filter'].'\' </script>';
	// End Set Anti-Spam options.


       // Set Imap Folder names options

    $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultTrashFolder'] 	= $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultTrashFolder']	? $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultTrashFolder']		: lang("Trash");
    $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultDraftsFolder'] 	= $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultDraftsFolder'] ? $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultDraftsFolder'] 	: lang("Drafts");
    $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultSpamFolder'] 	= $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultSpamFolder']	? $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultSpamFolder']		: lang("Spam");
    $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultSentFolder']	= $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultSentFolder'] 	? $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultSentFolder'] 		: lang("Sent");

    echo '<script> var special_folders = new Array(4);
   	special_folders["'.$_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultTrashFolder'].'"] = \'Trash\';
    special_folders["'.$_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultDraftsFolder'].'"] = \'Drafts\';
    special_folders["'.$_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultSpamFolder'].'"] = \'Spam\';
    special_folders["'.$_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultSentFolder'].'"] = \'Sent\';
    var trashfolder = "'.$_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultTrashFolder'].'";
    var draftsfolder = "'.$_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultDraftsFolder'].'";
    var sentfolder = "'.$_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultSentFolder'].'";
    var spamfolder = "'.$_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultSpamFolder'].'";                      
    </script>';

    // End Set Imap Folder names options
	

	$obj = createobject("expressoMail1_2.functions");
	$offline_language = 'pt-br';
	// INCLUDE these JS Files: 
	echo $obj -> getFilesJs("js/abas.js," .
							"js/common_functions.js," .
							"js/doiMenuData.js," .
							"js/drag_area.js," .
							"js/draw_api.js," .
							"js/DropDownContacts.js," .
							"js/InfoContact.js," .
							"js/main.js," .
							"js/gears_init.js," .
							"js/local_messages.js," .
							"js/messages_controller.js," .
					//		"js/rich_text_editor.js," .
							"js/wfolders.js,".
							"js/offline_access.js,",
							$GLOBALS['phpgw_info']['flags']['update_version']);

	echo $obj -> getFilesJs("js/rich_text_editor.js,",$GLOBALS['phpgw_info']['flags']['update_version']);

	if ($GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['use_shortcuts'])
	{
		echo $obj -> getFilesJs("js/shortcut.js", $GLOBALS['phpgw_info']['flags']['update_version']);
	}

	// Get Preferences or redirect to preferences page.
	$GLOBALS['phpgw']->preferences->read_repository();
?>

<html>
<head>
<title>ExpressoMail</title>
<link rel="stylesheet" type="text/css" href="templates/<?php echo $_SESSION['phpgw_info']['expressoMail1_2']['user']['preferences']['common']['template_set'];?>/main.css">
<link rel="stylesheet" type="text/css" href="../phpgwapi/js/dftree/dftree.css">
</head>
<body scroll="no" style="overflow:hidden">
</body>
</html>
<script src="js/connector.js?<?=$update_version?>" type="text/javascript"></script>
<script src="../phpgwapi/js/dftree/dftree.js?<?=$update_version?>" type="text/javascript"></script>
<?php
	$offline_language = "pt-br";
	include("inc/load_lang.php");
?>
<script language="Javascript">
	expresso_offline_access.has_permition();
	preferences = {
			'voip_enabled'						: "0",
			'max_email_per_page' 				: "<?php echo $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['max_email_per_page']?>",
    		'save_deleted_msg' 					: "0",
    		'delete_and_show_previous_message' 	: "<?php echo $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['delete_and_show_previous_message']?>",
    		'alert_new_msg' 					: "0",
    		'signature' 						: "<?php echo str_replace('"','\"',str_replace(array("\r\n", "\n", "\r"),' ',$GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['signature']))?>",
    		'use_signature' 					: "<?php echo $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['use_signature']?>",
			'hide_folders' 						: "0",    		
    		'save_in_folder'		    : "<?php echo $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['save_in_folder'] ? $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['save_in_folder'] : '-1' ?>",
    		'line_height' 						: "20",
    		'font_size' 						: "11",
    		'use_shortcuts' 					: "0",
    		'auto_save_draft'					: "0",
    		'use_local_messages'	 			: "1",			
    		'keep_archived_messages'	 		: "0",
			'remove_attachments_function'		: "False",
			'use_assinar_criptografar'			:'0',
			'use_signature_digital_cripto'		:'0',
			'use_important_flag'				: "<?php echo $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['use_important_flag'] ? $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['use_important_flag'] : '0'?>",
			'search_result_number'				: "<?php echo $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['search_result_number'] ? $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['search_result_number'] : '100' ?>"


	};
	connector.updateVersion = "<?=$update_version?>";init_offline();</script>
<!-----Expresso Mail - Version Updated:<?=$update_version?>-------->
