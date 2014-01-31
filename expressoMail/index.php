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
		'nonavbar' => false,
		'currentapp' => 'expressoMail',
		'enable_nextmatchs_class' => True
	);

	require_once('../header.inc.php');
	include_once dirname(__FILE__) . '/../header.inc.php';
	require_once dirname(__FILE__) . '/../services/class.servicelocator.php';
	$template = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
	$alternativeMailService = ServiceLocator::getService('ldap');
	$AlternateEmailExpresso = Array();
	$AlternateEmailExpresso = $alternativeMailService->getMailAlternateByUidNumber($_SESSION['phpgw_info']['expressomail']['user']['account_id']);
	$template->set_var("user_email_alternative", implode(",", $AlternateEmailExpresso));	
  	
  	if (execmethod('emailadmin.ui.countProfiles') == 0){
        execmethod('emailadmin.ui.addDefaultProfile');
    }

	$update_version = $GLOBALS['phpgw_info']['apps']['expressoMail']['version'];
	$_SESSION['phpgw_info']['expressomail']['user'] = $GLOBALS['phpgw_info']['user'];
	echo "<script type='text/javascript'>var template = '".$_SESSION['phpgw_info']['expressoMail']['user']['preferences']['common']['template_set']."';</script>";


	//jquery and Editor 
	echo '<link rel="stylesheet" type="text/css" href="assetic_css.php"></link>';
	echo '
		<link rel="stylesheet" type="text/css" href="../prototype/plugins/jquery/jquery-ui.css"/>
		<link rel="stylesheet" type="text/css" href="../prototype/modules/filters/filters.css"/>
		<link rel="stylesheet" type="text/css" href="../prototype/plugins/jqgrid/css/ui.jqgrid.css"/>
		<link rel="stylesheet" type="text/css" href="../prototype/plugins/contextmenu/jquery.contextMenu.css"/>
		<link rel="stylesheet" type="text/css" href="../prototype/plugins/zebradialog/css/zebra_dialog.css"/>
		<link rel="stylesheet" type="text/css" href="../prototype/plugins/fileupload/jquery.fileupload-ui.css"/>
		<link rel="stylesheet" type="text/css" href="../prototype/plugins/freeow/style/freeow/freeow.css"/>
		<link rel="stylesheet" type="text/css" href="../prototype/modules/mail/css/followupflag.css"/>
		<link rel="stylesheet" type="text/css" href="../prototype/plugins/farbtastic/farbtastic.css"/>
		<link rel="stylesheet" type="text/css" href="templates/default/main.css"/>
		<link rel="stylesheet" type="text/css" href="../prototype/plugins/treeview/jquery.treeview.css"/>
		<link rel="stylesheet" type="text/css" href="../prototype/modules/attach_message/attach_message.css"/>
		<link rel="stylesheet" type="text/css" href="../prototype/plugins/jquery.jrating/jRating.jquery.css"/>
		
		<script src="../prototype/plugins/jquery/jquery.min.js" language="javascript" charset="utf-8"></script>
		<script src="../prototype/plugins/jquery/jquery.migrate.js" language="javascript" charset="utf-8"></script>
		<script src="../prototype/library/ckeditor/ckeditor.js" language="javascript" charset="utf-8"></script>
		<script src="../prototype/library/ckeditor/adapters/jquery.js" language="javascript" charset="utf-8"></script>
		<script src="../prototype/plugins/jquery/jquery-ui.min.js" language="javascript" charset="utf-8"></script>
		<script type="text/javascript" src="../prototype/plugins/farbtastic/farbtastic.js"></script>
		<script src="../prototype/plugins/countdown/jquery.countdown.min.js" language="javascript" charset="utf-8"></script>
		<script src="../prototype/plugins/countdown/jquery.countdown-pt-BR.js" language="javascript" charset="utf-8"></script>
		<script src="../prototype/plugins/fileupload/jquery.fileupload.js" language="javascript" charset="utf-8"></script>
		<script type="text/javascript" src="../prototype/plugins/contextmenu/jquery.contextMenu.js"></script>
		<script type="text/javascript" src="../prototype/plugins/mask/jquery.maskedinput.js"></script>
		<script type="text/javascript" src="../prototype/plugins/lazy/jquery.lazy.js"></script>
		<script type="text/javascript" src="../prototype/plugins/jquery.autoscroll/jquery.aautoscroll.min.2.41.js"></script>
		';	

	echo "<script src='js/globals.js?".$update_version."' type='text/javascript'></script>";
	echo '<script type="text/javascript" src="../phpgwapi/js/wz_dragdrop/wz_dragdrop.js?'.$update_version.'"></script>
		  <script type="text/javascript" src="../phpgwapi/js/dJSWin/dJSWin.js?'.$update_version.'"></script>
		  <script type="text/javascript" src="js/connector.js"></script>
		  <script type="text/javascript" src="../phpgwapi/js/x_tools/xtools.js?'.$update_version.'"></script>
		  <script type="text/javascript" src="js/DropDownContacts.js"></script>
		  ';

	/*
	 * TODO: implementar o controle como preferência do usuário 
	 *
	 */
	$jcarousel = false;
	if ($jcarousel) {
		//jcarousel
		echo "\n".'<link rel="stylesheet" type="text/css" href="../prototype/plugins/jcarousel/skins/default/skin.css" />';
		echo "\n".'<script src="../prototype/plugins/jcarousel/lib/jquery.jcarousel.min.js" type="text/javascript"></script>';
		//fancybox
		echo "\n".'<link rel="stylesheet" type="text/css" href="../prototype/library/fancybox/jquery.fancybox-1.3.4.css" />';		
		echo "\n".'<script src="../prototype/library/fancybox/jquery.fancybox-1.3.4.pack.js" type="text/javascript"></script>';
	}

	echo "<div id='overlay' style='background-color: #AAAAAAA; opacity: .50; filter:Alpha(Opacity=50); height: 100%; width: 100%; position: absolute; top: 0; left: 0; visibility: hidden; z-index: 30000000000000000000000'></div>";

	

	//Enable/Disable VoIP Service -> Voip Server Config
	$voip_enabled = false;
	$voip_groups = array();	
	if($GLOBALS['phpgw_info']['server']['voip_groups']) {
		$emailVoip = false;
		foreach(explode(",",$GLOBALS['phpgw_info']['server']['voip_groups']) as $i => $voip_group){
			$a_voip = explode(";",$voip_group);			
			$voip_groups[] = $a_voip[1];
		}
		foreach($GLOBALS['phpgw']->accounts->membership() as $idx => $group){			
			if(array_search($group['account_name'],$voip_groups) !== FALSE){		 
				$voip_enabled = true;
				$emailVoip = $GLOBALS['phpgw_info']['server']['voip_email_redirect'];
				break;
			}
		}
	}

	//Local messages
	$_SESSION['phpgw_info']['server']['expressomail']['enable_local_messages'] = $current_config['enable_local_messages'];

	// Get Data from ldap_manager and emailadmin.
	$ldap_manager = CreateObject('contactcenter.bo_ldap_manager');
	$boemailadmin	= CreateObject('emailadmin.bo');
	$emailadmin_profile = $boemailadmin->getProfileList();
    // Loading Admin Config Module
    $c = CreateObject('phpgwapi.config','expressoMail');
    $c->read_repository();
    $current_config = $c->config_data;
    
    // Loading Config Module
    $conf = CreateObject('phpgwapi.config','phpgwapi');
    $conf->read_repository();
    $config = $conf->config_data;   

    //Carrega Configuração global do expressoMail 
 	$_SESSION['phpgw_info']['expresso']['expressoMail'] =  $current_config; 
    
	$_SESSION['phpgw_info']['expressomail']['email_server'] = $boemailadmin->getProfile($emailadmin_profile[0]['profileID']);
	//$_SESSION['phpgw_info']['expressomail']['user'] = $GLOBALS['phpgw_info']['user'];
	$_SESSION['phpgw_info']['expressomail']['server'] = $GLOBALS['phpgw_info']['server'];
	$_SESSION['phpgw_info']['expressomail']['ldap_server'] = $ldap_manager ? $ldap_manager->srcs[1] : null;
	$_SESSION['phpgw_info']['expressomail']['user']['email'] = $GLOBALS['phpgw']->preferences->values['email'];
	$_SESSION['phpgw_info']['server']['temp_dir'] = $GLOBALS['phpgw_info']['server']['temp_dir'];
	
	$preferences = $GLOBALS['phpgw']->preferences->read();
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail'] = $preferences['enable_local_messages']; 
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail'] = $preferences['expressoMail'];
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['voip_enabled'] = $voip_enabled;
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['voip_email_redirect'] = $emailVoip;
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['outoffice'] = $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['outoffice'];
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['telephone_number'] = $GLOBALS['phpgw_info']['user']['telephonenumber'];
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['use_cache'] = $current_config['expressoMail_enable_cache'];
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['expressoMail_ldap_identifier_recipient'] = $current_config['expressoMail_ldap_identifier_recipient'];
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['use_x_origin'] = $current_config['expressoMail_use_x_origin'];
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['number_of_contacts'] = $current_config['expressoMail_Number_of_dynamic_contacts'] ? $current_config['expressoMail_Number_of_dynamic_contacts'] : "0";
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['notification_domains'] = $current_config['expressoMail_notification_domains'];
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['use_assinar_criptografar'] = $GLOBALS['phpgw_info']['server']['use_assinar_criptografar'] ?  $GLOBALS['phpgw_info']['server']['use_assinar_criptografar'] : "0";
    $_SESSION['phpgw_info']['user']['preferences']['expressoMail']['use_signature_digital_cripto'] = $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['use_signature_digital_cripto'] ? $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['use_signature_digital_cripto'] : "0";
    $_SESSION['phpgw_info']['user']['preferences']['expressoMail']['use_signature_digital'] = $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['use_signature_digital'] ? $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['use_signature_digital'] : "0";
    $_SESSION['phpgw_info']['user']['preferences']['expressoMail']['search_result_number'] = $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['search_result_number'] ? $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['search_result_number'] : "50";
    $_SESSION['phpgw_info']['user']['preferences']['expressoMail']['search_characters_number'] = $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['search_characters_number'] ? $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['search_characters_number'] : "4";
    $_SESSION['phpgw_info']['user']['preferences']['expressoMail']['num_max_certs_to_cipher'] = $GLOBALS['phpgw_info']['server']['num_max_certs_to_cipher'] ?  $GLOBALS['phpgw_info']['server']['num_max_certs_to_cipher'] : "10";
    $_SESSION['phpgw_info']['user']['preferences']['expressoMail']['use_signature_cripto'] = $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['use_signature_cripto'] ? $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['use_signature_cripto'] : "0";
	
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['max_attachment_size'] = $current_config['expressoMail_Max_attachment_size'] ? $current_config['expressoMail_Max_attachment_size']."M" : '';
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['max_msg_size'] = $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['max_msg_size'] ? $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['max_msg_size'] : "0";
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['imap_max_folders'] = $current_config['expressoMail_imap_max_folders'];
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['max_email_per_page'] = $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['max_email_per_page'] ? $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['max_email_per_page'] : "50";
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['extended_info'] = $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['extended_info']?$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['extended_info'] = $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['extended_info']:'0';
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['from_to_sent'] = $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['from_to_sent'] ? $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['from_to_sent'] : "0";
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['auto_create_local'] = $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['auto_create_local'] ? $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['auto_create_local'] : "0";
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['return_recipient_deafault'] = $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['return_recipient_deafault'] ? $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['return_recipient_deafault'] : "0";
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['quick_search_default'] = $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['quick_search_default'] ? $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['quick_search_default'] : 1;
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['confirm_read_message'] = $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['confirm_read_message'] ? $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['confirm_read_message'] : 0;
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['alert_message_attachment'] = $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['alert_message_attachment'] ? $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['alert_message_attachment'] : 0;
	// 	ACL for block edit Personal Data.
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['enable_quickadd_telephonenumber'] = $current_config['expressoMail_enable_quickadd_telephonenumber'] == 'true' ? $current_config['expressoMail_enable_quickadd_telephonenumber'] : "";
	if($_SESSION['phpgw_info']['user']['preferences']['expressoMail']['enable_quickadd_telephonenumber']){
		$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['blockpersonaldata'] = $GLOBALS['phpgw']->acl->check('blockpersonaldata',1,'preferences');		
	}
	
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['auto_close_first_tab'] = $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['auto_close_first_tab'] ? $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['auto_close_first_tab'] : "0";
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

	$acc = CreateObject('phpgwapi.accounts');
	$template->set_var("user_organization", $acc->get_organization($GLOBALS['phpgw_info']['user']['account_dn']));
	$template->set_var("cyrus_delimiter",$_SESSION['phpgw_info']['expressomail']['email_server']['imapDelimiter']);	
	$template->set_var("lang_contact_details", lang("Contact Details")); 
 	$template->set_var("lang_catalog", lang("catalog")); 
 	$template->set_var("lang_search", lang("search")); 
 	$template->set_var("lang_page", lang("page")); 
 	$template->set_var("lang_quick_search_users_dialog_title", lang("Quick Search Contacts")); 
 	$template->set_var("lang_global_catalog", lang("Global Catalog")); 
 	$template->set_var("lang_personal_catalog", lang("Personal Catalog")); 
 	$template->set_var("lang_all_catalogs", lang("All Catalogs")); 
	// Fix problem with cyrus delimiter changes in preferences.
	// Dots in names: enabled/disabled.
	$save_in_folder = @preg_replace('/INBOX\//i', "INBOX".$_SESSION['phpgw_info']['expressomail']['email_server']['imapDelimiter'], $_SESSION['phpgw_info']['user']['preferences']['expressoMail']['save_in_folder']);
	$save_in_folder = @preg_replace('/INBOX./i', "INBOX".$_SESSION['phpgw_info']['expressomail']['email_server']['imapDelimiter'], $save_in_folder);
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['save_in_folder'] = $save_in_folder;
	// End Fix.

	$template->set_file(Array('expressoMail' => 'index.tpl'));
	$template->set_block('expressoMail','list');
	$template->pfp('out','list');
	$GLOBALS['phpgw']->common->phpgw_footer();
    
    $_SESSION['phpgw_info']['server']['expressomail']['expressoMail_enable_log_messages'] = $current_config['expressoMail_enable_log_messages'];
	
    // Begin Set Anti-Spam options.
    $_SESSION['phpgw_info']['server']['expressomail']['expressoMail_command_for_ham'] = $current_config['expressoMail_command_for_ham'];
    $_SESSION['phpgw_info']['server']['expressomail']['expressoMail_command_for_spam'] = $current_config['expressoMail_command_for_spam'];
    $_SESSION['phpgw_info']['server']['expressomail']['expressoMail_use_spam_filter'] = $current_config['expressoMail_use_spam_filter'];   
    echo '<script> var use_spam_filter = \''.$current_config['expressoMail_use_spam_filter'].'\'
           var sieve_forward_domains = \''.$current_config['expressoMail_sieve_forward_domains'].'\' 
		  </script>';
	// End Set Anti-Spam options.

	// Begin Set Hidden Copy options. 
	$_SESSION['phpgw_info']['server']['expressomail']['allow_hidden_copy'] = $current_config['allow_hidden_copy']; 
	echo '<script> var allow_hidden_copy = \''.$current_config['allow_hidden_copy'].'\' </script>'; 
	// End Set Hidden Copy options. 
	
    // Set Imap Folder names options
    $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultTrashFolder'] 	= $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultTrashFolder']	? $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultTrashFolder']		: lang("Trash");
    $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultDraftsFolder'] 	= $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultDraftsFolder'] ? $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultDraftsFolder'] 	: lang("Drafts");
    $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultSpamFolder'] 	= $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultSpamFolder']	? $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultSpamFolder']		: lang("Spam");
    $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultSentFolder']	= $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultSentFolder'] 	? $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultSentFolder'] 		: lang("Sent");

    // gera paramero com tokens suportados ....
    $var_tokens = '';
    for($ii = 1; $ii < 11; ++$ii)
    {
        if($GLOBALS['phpgw_info']['server']['test_token' . $ii . '1'])
            $var_tokens .= $GLOBALS['phpgw_info']['server']['test_token' . $ii . '1'] . ',';
    }

    if(!$var_tokens)
    {
        $var_tokens = 'ePass2000Lx;/usr/lib/libepsng_p11.so,ePass2000Win;c:/windows/system32/ngp11v211.dll';
    }

    echo '<script type="text/javascript"> var preferences  = '.json_encode($_SESSION['phpgw_info']['user']['preferences']['expressoMail']).'</script>';

    echo '
	<script> var special_folders = new Array(4);
		special_folders[\'Trash\'] = "'.$_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultTrashFolder'].'";
		special_folders[\'Drafts\'] = "'.$_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultDraftsFolder'].'";
		special_folders[\'Spam\'] = "'.$_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultSpamFolder'].'";
		special_folders[\'Sent\'] = "'.$_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultSentFolder'].'";
        special_folders[\'Outbox\'] = "Outbox";
		var trashfolder = "'.$_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultTrashFolder'].'";
		var draftsfolder = "'.$_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultDraftsFolder'].'";
		var sentfolder = "'.$_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultSentFolder'].'";
		var spamfolder = "'.$_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultSpamFolder'].'";
        var outboxfolder = "Outbox";

		var token_param = "'.$var_tokens.'";
		var locale = "'.$GLOBALS['phpgw']->common->getPreferredLanguage().'";
		var language = "'.$_SESSION['phpgw_info']['expressomail']['user']['preferences']['common']['lang'].'";
		var defaultCalendar = "'.  (isset($config['defaultCalendar']) ?  $config['defaultCalendar']  :  "calendar" )    .'";
		$("#sideboxdragarea").hide();
		$("#menu2Container").hide();
    </script>

		<script type="text/javascript" src="../prototype/plugins/store/jquery.store.js"></script>
		<script type="text/javascript" src="../prototype/api/datalayer.js"</script>
		
		<script type="text/javascript" src="../prototype/modules/mail/js/label.js"></script>
		<script type="text/javascript" src="../prototype/api/rest.js"></script>
		
	
	';
    // End Set Imap Folder names options
	//User info
    echo "<script language='javascript'> var account_id = ".$GLOBALS['phpgw_info']['user']['account_id'].";var expresso_offline = false; var mail_archive_host = '127.0.0.1';</script>\n";
        
    //MAILARCHIVER-02
    //todo: remover a linha abaixo e implementar a configuração
    //$GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['use_local_messages'] = true;
	
	if ( $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['use_local_messages'] == true )
	{
        $mail_archiver_host = '127.0.0.1';
        //Check protocol in use (https or http)
        if($_SERVER['HTTPS'] != 'on'){
            $mail_archiver_protocol = "http";
            $mail_archiver_port = "4333";
        }
        else{
            $mail_archiver_protocol = "https";
            $mail_archiver_port = "4334";
        }
    
        //JS Variables (to add on jscripts variables, needed by dependence scripts following)
        echo '<script type="text/javascript">var mail_archive_protocol="'.$mail_archiver_protocol.'"; var mail_archive_port="'.$mail_archiver_port.'";</script>';
    
        //CXF custom js files, from MailArchiver ArcServUtil JS files repository, intended to be running already: CORS support and custom TRANSPORT object
        echo '<script type="text/javascript" src="'.$mail_archiver_protocol.'://'.$mail_archiver_host.':'.$mail_archiver_port.'/arcservutil/cxf-addon-xdr-adapter.js"></script>';
        echo '<script type="text/javascript" src="'.$mail_archiver_protocol.'://'.$mail_archiver_host.':'.$mail_archiver_port.'/arcservutil/cxf-addon-cors-request-object.js"></script>';
        echo '<script type="text/javascript" src="'.$mail_archiver_protocol.'://'.$mail_archiver_host.':'.$mail_archiver_port.'/arcservutil/cxf-addon-cors-utils.js"></script>';

        //CXF UTILS MAIN FILE
        echo '<script type="text/javascript" src="'.$mail_archiver_protocol.'://'.$mail_archiver_host.':'.$mail_archiver_port.'/arcserv/ArchiveServices?js&nojsutils"></script>';
    
        //QueryConfig add on
        echo '<script src="js/MAQueryConfig.js?'.$update_version.'"></script>';        
        //Expresso serialized format add on
        echo '<script src="js/MAExpressoPattern.js?'.$update_version.'"></script>';        
    
        //echo $obj -> getFilesJs("js/mail_archiver.js," . $GLOBALS['phpgw_info']['flags']['update_version']);
        echo '<script src="js/mail_archiver.js?'.$update_version.'"></script>';
    
    }
	//echo "<script language='javascript'> var account_id = ".$GLOBALS['phpgw_info']['user']['account_id'].";var expresso_offline = false;</script>";

	$obj = createobject("expressoMail.functions");

	// setting timezone preference
	$zones = $obj->getTimezones();
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['timezone'] = $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['timezone'] ? $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['timezone'] : sprintf("%s", array_search("America/Sao_Paulo", $zones));

	// este arquivo deve ser carregado antes que
	// os demais pois nele contem a função get_lang
	// que é utilizada em diversas partes
	//echo $obj -> getFilesJs("js/common_functions.js",$update_version);
	include("inc/load_lang.php");

 	echo '<script src="../phpgwapi/js/dftree/dftree.js?'.$update_version.'"></script>'; 
    
	$scripts = "";
	
	if ($GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['use_shortcuts'])
	{
		//echo $obj -> getFilesJs("js/shortcut.js", $update_version); 
		$scripts .= "js/shortcut.js,";
	}
	echo '<script> use_local_messages = '.$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['use_local_messages'].'</script>';		
	echo '
		<!--<script type="text/javascript" src="../prototype/modules/mail/js/followupflag.js"></script>-->
		<script language="javascript">
			DataLayer.dispatchPath = "../prototype/";
			REST.dispatchPath = "../prototype/";
			REST.load("");
		</script>
	';

//////////////////////////////////////////// Carregar Timezones para o javascript /////////////////////////////////////////

    $zones = timezone_identifiers_list();
    $Time = new DateTime('now', new DateTimeZone('UTC'));
    $timezone = array();

    foreach ($zones as $zone)
    {
        $timezone['timezones'][$zone] = $Time->setTimezone(new DateTimeZone($zone))->format('O');
    }

    $localtime = localtime(time(), true);
    $timezone['isDaylightSaving'] =  !!$localtime['tm_isdst'] ? 1 : 0;

    echo '<script type="text/javascript"> var Timezone  = '.json_encode($timezone).'</script>';

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	echo
	    '<script src="../prototype/plugins/datejs/date-pt-BR.js" language="javascript" ></script>
		<script src="../prototype/plugins/dateFormat/dateFormat.js" language="javascript" ></script>
		<script src="../prototype/modules/calendar/js/calendar.date.js" language="javascript" ></script>
		<script src="../prototype/modules/calendar/js/calendar.codecs.js" language="javascript" ></script>
		<script src="../prototype/modules/calendar/js/calendar.alarms.js" language="javascript" ></script>
		<script src="../prototype/modules/calendar/js/helpers.js" language="javascript" ></script>';

	echo $obj -> getFilesJs($scripts, $update_version);
	echo '<script type="text/javascript">connector.updateVersion = "'.$update_version.'";</script>';
	echo '<script type="text/javascript" src="assetic.php"></script>';

/////////   Verifica se o usuario esta fora do escritorio imprime a variavel javascript "outOfficeFlag" /////////////////////////////

    include_once(__DIR__ .'/../prototype/library/Net/Sieve.php');
    $sieveConf  =  parse_ini_file( __DIR__."/../prototype/config/Sieve.srv", true );
    $sieveConf = $sieveConf['config'];
    $sieve = new Net_Sieve();
    $inVacation = false;
    @$sieve->connect( $sieveConf['host'] , $sieveConf['port'] , $sieveConf['options'] , $sieveConf['useTLS'] );
    @$sieve->login( $_SESSION['wallet']['Sieve']['user'], $_SESSION['wallet']['Sieve']['password'] , $sieveConf['loginType']);
    $script = $sieve->getScript($sieve->getActive());
    $pos = strripos($script, "#PseudoScript#");
    $pseudo_script = substr( $script, $pos+17 );
    $sieveRules = json_decode( $pseudo_script, true );

	if( count($sieveRules) > 0 )
    {
	    foreach( $sieveRules as $i => $v)
	    {
	        if($v['id'] == 'vacation' && $v['enabled'] == 'true')
	            $inVacation = true;
	    }
	}

    if($inVacation)
    {	
		echo '<script language="javascript"> write_msg(get_lang("Attention, you are in out of office mode."), true);   </script>';
    }

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	// Get Preferences or redirect to preferences page.
	$GLOBALS['phpgw']->preferences->read_repository();
	//print_r($_SESSION['phpgw_info']['user']['preferences']['expressoMail']);
	unset($_SESSION['phpgw_info']['expressomail']['user']['preferences']);
	unset($_SESSION['phpgw_info']['expressomail']['user']['acl']);
	unset($_SESSION['phpgw_info']['expressomail']['user']['apps']);
	unset($_SESSION['phpgw_info']['expressomail']['server']['global_denied_users']);
	unset($_SESSION['phpgw_info']['expressomail']['server']['global_denied_groups']);
?>
<!-----Expresso Mail - Version Updated:<?=$update_version?>-------->
