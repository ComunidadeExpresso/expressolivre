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
		'noheader' => false,
		'nonavbar' => false,
		'currentapp' => 'expressoMail',
		'enable_nextmatchs_class' => true
	);

	require_once __DIR__ . '/../header.inc.php';
	require_once __DIR__ . '/../services/class.servicelocator.php';

	$template = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
	$alternativeMailService = ServiceLocator::getService('ldap');
	$AlternateEmailExpresso = Array();
	$AlternateEmailExpresso = ((isset($_SESSION['phpgw_info']['expressomail']))?$alternativeMailService->getMailAlternateByUidNumber($_SESSION['phpgw_info']['expressomail']['user']['account_id']):"");
	if( is_array($AlternateEmailExpresso) ){
		$template->set_var("user_email_alternative", implode(",", $AlternateEmailExpresso));	
	}
  	
  	if (execmethod('emailadmin.ui.countProfiles') == 0){
        execmethod('emailadmin.ui.addDefaultProfile');
    }

	$update_version = $GLOBALS['phpgw_info']['apps']['expressoMail']['version'];
	$_SESSION['phpgw_info']['expressomail']['user'] = $GLOBALS['phpgw_info']['user'];

//////////////////////////////////////////// Enable/Disable VoIP Service -> Voip Server Config /////////////////////////
	$voip_enabled = false;
	$voip_groups = array();	
	$emailVoip = false;
	if( isset($GLOBALS['phpgw_info']['server']['voip_groups']) )
	{
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
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	echo '<script type="text/javascript"> template = "'.$_SESSION['phpgw_info']['expressoMail']['user']['preferences']['common']['template_set'].'";</script>'; //Javascript global template
	
	echo '<link rel="stylesheet" type="text/css" href="styles.php"/>';
	echo '<link rel="stylesheet" type="text/css" href="../prototype/plugins/jquery/jquery-ui.css"/>';
	echo '<link rel="stylesheet" type="text/css" href="../prototype/modules/filters/filters.css"/>';
	echo '<link rel="stylesheet" type="text/css" href="../prototype/plugins/jqgrid/css/ui.jqgrid.css"/>';
	echo '<link rel="stylesheet" type="text/css" href="../prototype/plugins/contextmenu/jquery.contextMenu.css"/>';
	echo '<link rel="stylesheet" type="text/css" href="../prototype/plugins/zebradialog/css/zebra_dialog.css"/>';
	echo '<link rel="stylesheet" type="text/css" href="../prototype/plugins/fileupload/jquery.fileupload-ui.css"/>';
	echo '<link rel="stylesheet" type="text/css" href="../prototype/plugins/freeow/style/freeow/freeow.css"/>';
	echo '<link rel="stylesheet" type="text/css" href="../prototype/modules/mail/css/followupflag.css"/>';
	echo '<link rel="stylesheet" type="text/css" href="../prototype/plugins/farbtastic/farbtastic.css"/>';
	echo '<link rel="stylesheet" type="text/css" href="templates/default/main.css"/>';
	echo '<link rel="stylesheet" type="text/css" href="../prototype/plugins/treeview/jquery.treeview.css"/>';
	echo '<link rel="stylesheet" type="text/css" href="../prototype/modules/attach_message/attach_message.css"/>';
	echo '<link rel="stylesheet" type="text/css" href="../prototype/plugins/jquery.jrating/jRating.jquery.css"/>';

	echo '<script type="text/javascript" src="js/globals.js" ></script>';
	echo '<script type="text/javascript" charset="utf-8" src="../prototype/plugins/jquery/jquery.min.js"></script>';
	echo '<script type="text/javascript" charset="utf-8" src="../prototype/plugins/jquery/jquery.migrate.js"></script>';
	echo '<script type="text/javascript" src="../prototype/plugins/store/jquery.store.js"></script>';
	echo '<script type="text/javascript" src="../prototype/api/datalayer.js"></script>';
	echo '<script type="text/javascript" src="../prototype/api/rest.js" ></script>';
	echo '<script type="text/javascript" charset="utf-8" src="../prototype/library/ckeditor/ckeditor.js"></script>';
	echo '<script type="text/javascript" charset="utf-8" src="../prototype/library/ckeditor/adapters/jquery.js"></script>';
	echo '<script type="text/javascript" charset="utf-8" src="../prototype/plugins/jquery/jquery-ui.min.js"></script>';
	echo '<script type="text/javascript" charset="utf-8" src="../prototype/plugins/countdown/jquery.countdown.min.js"></script>';
	echo '<script type="text/javascript" charset="utf-8" src="../prototype/plugins/countdown/jquery.countdown-pt-BR.js"></script>';
	echo '<script type="text/javascript" charset="utf-8" src="../prototype/plugins/fileupload/jquery.fileupload.js"></script>';
	echo '<script type="text/javascript" src="../prototype/plugins/contextmenu/jquery.contextMenu.js"></script>';
	echo '<script type="text/javascript" src="../prototype/plugins/mask/jquery.maskedinput.js"></script>';
	echo '<script type="text/javascript" src="../prototype/plugins/lazy/jquery.lazy.js"></script>';
	echo '<script type="text/javascript" src="../prototype/plugins/jquery.autoscroll/jquery.aautoscroll.min.2.41.js"></script>';

	// Jquery - Expresso Messenger
	echo '<link rel="stylesheet" type="text/css" href="../prototype/plugins/wijmo/jquery.wijmo.css"/>';
	echo '<link rel="stylesheet" type="text/css" href="../prototype/plugins/messenger/im.css"/>';

	//Configuração Datalayer
	echo '<script type="text/javascript">
		DataLayer.dispatchPath = "../prototype/";
		REST.dispatchPath = "../prototype/";
		REST.load("");
	</script>';
	
	//Enable/Disable Expresso Messenger -> ExpressoMail Config
	$messenger = array();
	$messenger_groups = array();
	if( isset($GLOBALS['phpgw_info']['server']['groups_expresso_messenger']) && $GLOBALS['phpgw_info']['server']['groups_expresso_messenger'] != "" )
	{
		$messenger_groups = unserialize($GLOBALS['phpgw_info']['server']['groups_expresso_messenger']);
		foreach( $messenger_groups as $group )
		{
			$values = explode( ";", $group );
			$messenger[] = $values[1];
		}
		foreach( $GLOBALS['phpgw']->accounts->membership() as $group )
		{			
			$search = array_search( $group['account_name'], $messenger_groups );
			if( array_search( $group['account_name'], $messenger ) !== FALSE )
			{	
				echo '<input type="hidden" name="expresso_messenger_enabled" value="true">';
				echo '<input type="hidden" name="messenger_fullName" value="'.$GLOBALS['phpgw_info']['user']['fullname'].'">';
				break;
			}
		}
	}

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
	$_SESSION['phpgw_info']['expressomail']['server'] = $GLOBALS['phpgw_info']['server'];
	$_SESSION['phpgw_info']['expressomail']['ldap_server'] = $ldap_manager ? $ldap_manager->srcs[1] : null;
	$_SESSION['phpgw_info']['expressomail']['user']['email'] = $GLOBALS['phpgw']->preferences->values['email'];
	$_SESSION['phpgw_info']['server']['temp_dir'] = $GLOBALS['phpgw_info']['server']['temp_dir'];
	
	$preferences = $GLOBALS['phpgw']->preferences->read();
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail'] = (isset($preferences['enable_local_messages'])?$preferences['enable_local_messages']:""); 
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail'] = (isset($preferences['expressoMail'])?$preferences['expressoMail']:"");
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['voip_enabled'] = $voip_enabled;
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['voip_email_redirect'] = $emailVoip;
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['outoffice'] = (isset($GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['outoffice'])?$GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['outoffice']:"");
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['telephone_number'] = $GLOBALS['phpgw_info']['user']['telephonenumber'];
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['use_cache'] = $current_config['expressoMail_enable_cache'];
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['expressoMail_ldap_identifier_recipient'] = (isset($current_config['expressoMail_ldap_identifier_recipient'])?$current_config['expressoMail_ldap_identifier_recipient']:"");
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['use_x_origin'] = (isset($current_config['expressoMail_use_x_origin'])?$current_config['expressoMail_use_x_origin']:"");
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['number_of_contacts'] = (isset($current_config['expressoMail_Number_of_dynamic_contacts']) ? $current_config['expressoMail_Number_of_dynamic_contacts'] : "0");
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['notification_domains'] = (isset($current_config['expressoMail_notification_domains'])?$current_config['expressoMail_notification_domains']:"");
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['use_assinar_criptografar'] = (isset($GLOBALS['phpgw_info']['server']['use_assinar_criptografar']) ?  $GLOBALS['phpgw_info']['server']['use_assinar_criptografar'] : "0");
    $_SESSION['phpgw_info']['user']['preferences']['expressoMail']['use_signature_digital_cripto'] = (isset($GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['use_signature_digital_cripto']) ? $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['use_signature_digital_cripto'] : "0");
    $_SESSION['phpgw_info']['user']['preferences']['expressoMail']['use_signature_digital'] = (isset($GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['use_signature_digital']) ? $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['use_signature_digital'] : "0");
    $_SESSION['phpgw_info']['user']['preferences']['expressoMail']['search_result_number'] = (isset($GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['search_result_number']) ? $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['search_result_number'] : "50");
    $_SESSION['phpgw_info']['user']['preferences']['expressoMail']['search_characters_number'] = (isset($GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['search_characters_number']) ? $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['search_characters_number'] : "4");
    $_SESSION['phpgw_info']['user']['preferences']['expressoMail']['num_max_certs_to_cipher'] = (isset($GLOBALS['phpgw_info']['server']['num_max_certs_to_cipher']) ?  $GLOBALS['phpgw_info']['server']['num_max_certs_to_cipher'] : "10");
    $_SESSION['phpgw_info']['user']['preferences']['expressoMail']['use_signature_cripto'] = (isset($GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['use_signature_cripto']) ? $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['use_signature_cripto'] : "0");
	
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['max_attachment_size'] = (isset($current_config['expressoMail_Max_attachment_size']) ? $current_config['expressoMail_Max_attachment_size']."M" : '');
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['max_msg_size'] = (isset($GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['max_msg_size']) ? $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['max_msg_size'] : "0");
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['imap_max_folders'] = (isset($current_config['expressoMail_imap_max_folders'])?$current_config['expressoMail_imap_max_folders']:"");
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['max_email_per_page'] = (isset($GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['max_email_per_page']) ? $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['max_email_per_page'] : "50");
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['extended_info'] = (isset($GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['extended_info'])?$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['extended_info'] = $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['extended_info']:'0');
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['from_to_sent'] = (isset($GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['from_to_sent'])? $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['from_to_sent'] : "0");
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['auto_create_local'] = (isset($GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['auto_create_local']) ? $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['auto_create_local'] : "0");
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['return_recipient_deafault'] = (isset($GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['return_recipient_deafault']) ? $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['return_recipient_deafault'] : "0");
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['quick_search_default'] = (isset($GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['quick_search_default']) ? $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['quick_search_default'] : 1);
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['confirm_read_message'] = (isset($GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['confirm_read_message']) ? $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['confirm_read_message'] : 0);
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['alert_message_attachment'] = (isset($GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['alert_message_attachment']) ? $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['alert_message_attachment'] : 0);
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['enable_quickadd_telephonenumber'] = '';
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['auto_close_first_tab'] = (isset($GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['auto_close_first_tab']) ? $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['auto_close_first_tab'] : "0");
	
	// 	ACL for block edit Personal Data.
	if( $current_config['expressoMail_enable_quickadd_telephonenumber'] == 'true' ){
		$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['enable_quickadd_telephonenumber'] =  $current_config['expressoMail_enable_quickadd_telephonenumber'];
		$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['blockpersonaldata'] = $GLOBALS['phpgw']->acl->check('blockpersonaldata',1,'preferences');
	}
	
	// Fix problem with cyrus delimiter changes in preferences.
	// Dots in names: enabled/disabled.
	$save_in_folder = @preg_replace('/INBOX\//i', "INBOX".$_SESSION['phpgw_info']['expressomail']['email_server']['imapDelimiter'], $_SESSION['phpgw_info']['user']['preferences']['expressoMail']['save_in_folder']);
	$save_in_folder = @preg_replace('/INBOX./i', "INBOX".$_SESSION['phpgw_info']['expressomail']['email_server']['imapDelimiter'], $save_in_folder);
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['save_in_folder'] = $save_in_folder;
	// End Fix.
	
	$acc = CreateObject('phpgwapi.accounts');
	$template->set_var("txt_loading",lang("Loading"));
	$template->set_var("txt_clear_trash",lang("message(s) deleted from your trash folder."));
    $template->set_var("new_message", lang("New Message"));
	$template->set_var("lang_inbox", lang("Inbox"));
    $template->set_var("refresh", lang("Refresh"));
    $template->set_var("tools", lang("Tools"));	
	$template->set_var("lang_Open_Search_Window", lang("Open search window") . '...');
	$template->set_var("lang_search_user", lang("Search user") . '...'); 
	$template->set_var("upload_max_filesize",ini_get('upload_max_filesize'));
	$template->set_var("msg_folder",(isset($_GET['msgball']['folder'])?$_GET['msgball']['folder']:""));
	$template->set_var("msg_number",(isset($_GET['msgball']['msgnum']) ? $_GET['msgball']['msgnum'] : (isset($_GET['to'])?$_GET['to']:"")));
	$template->set_var("user_email",$_SESSION['phpgw_info']['expressomail']['user']['email']);

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
	$template->set_file(Array('expressoMail' => 'index.tpl'));
	$template->set_block('expressoMail','list');
	$template->pfp('out','list');
	$GLOBALS['phpgw']->common->phpgw_footer();
    
	
////////////////////////////////////////////  Anti-Spam options ////////////////////////////////////////////////////////
    $_SESSION['phpgw_info']['server']['expressomail']['expressoMail_command_for_ham'] = (isset($current_config['expressoMail_command_for_ham'])?$current_config['expressoMail_command_for_ham']:"");
    $_SESSION['phpgw_info']['server']['expressomail']['expressoMail_command_for_spam'] = (isset($current_config['expressoMail_command_for_spam'])?$current_config['expressoMail_command_for_spam']:"");
    $_SESSION['phpgw_info']['server']['expressomail']['expressoMail_use_spam_filter'] = (isset($current_config['expressoMail_use_spam_filter'])?$current_config['expressoMail_use_spam_filter']:"");
    $_SESSION['phpgw_info']['server']['expressomail']['expressoMail_enable_log_messages'] = (isset($current_config['expressoMail_enable_log_messages'])?$current_config['expressoMail_enable_log_messages']:"");   
    echo '<script> var use_spam_filter = \''.(isset($current_config['expressoMail_use_spam_filter'])?$current_config['expressoMail_use_spam_filter']:"").'\'; var sieve_forward_domains = \''.(isset($current_config['expressoMail_sieve_forward_domains'])?$current_config['expressoMail_sieve_forward_domains']:"").'\'; </script>';
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////// Hidden Copy options ///////////////////////////////////////////////////////
	$_SESSION['phpgw_info']['server']['expressomail']['allow_hidden_copy'] = $current_config['allow_hidden_copy']; 
	echo '<script> var allow_hidden_copy = \''.$current_config['allow_hidden_copy'].'\' </script>'; 
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////// Imap Folder names options /////////////////////////////////////////////////
    $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultTrashFolder'] 	= (isset($_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultTrashFolder'])	? $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultTrashFolder']		: lang("Trash"));
    $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultDraftsFolder'] 	= (isset($_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultDraftsFolder']) ? $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultDraftsFolder'] 	: lang("Drafts"));
    $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultSpamFolder'] 	= (isset($_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultSpamFolder'])	? $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultSpamFolder']		: lang("Spam"));
    $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultSentFolder']	= (isset($_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultSentFolder']) 	? $_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultSentFolder'] 		: lang("Sent"));
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////// Gera paramero com tokens suportados ///////////////////////////////////////
    $var_tokens = '';
    
    for($ii = 1; $ii < 11; ++$ii)
    {
        if( isset($GLOBALS['phpgw_info']['server']['test_token' . $ii . '1']) )
        {
            $var_tokens .= $GLOBALS['phpgw_info']['server']['test_token' . $ii . '1'] . ',';
        }
    }

    if( !$var_tokens )
    {
        $var_tokens = 'ePass2000Lx;/usr/lib/libepsng_p11.so,ePass2000Win;c:/windows/system32/ngp11v211.dll';
    }
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//Exporta as preferencias para o javascript

    echo '<script type="text/javascript"> var preferences  = '.json_encode($_SESSION['phpgw_info']['user']['preferences']['expressoMail']).'</script>';
////////////////////////////////////////// Imap Folder names options  //////////////////////////////////////////////////
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
    </script>';
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    echo "<script type='text/javascript'> var account_id = ".$GLOBALS['phpgw_info']['user']['account_id'].";var expresso_offline = false; var mail_archive_host = '127.0.0.1';</script>\n";

	$obj = createobject("expressoMail.functions");

	// setting timezone preference
	$zones = $obj->getTimezones();
	$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['timezone'] = ( isset($GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['timezone']) ? $GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['timezone'] : sprintf("%s", array_search("America/Sao_Paulo", $zones)));

	// este arquivo deve ser carregado antes que
	// os demais pois nele contem a função get_lang
	// que é utilizada em diversas partes
	include("inc/load_lang.php");
//////////////////////////////////////////// Carrega Timezones para o javascript ///////////////////////////////////////

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

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////   Verifica se o usuario esta fora do escritorio imprime a variavel javascript "outOfficeFlag" ////////////////
    include_once(__DIR__ .'/../prototype/library/Net/Sieve.php');
    $sieveConf  =  parse_ini_file( __DIR__."/../prototype/config/Sieve.srv", true );
    $sieveConf = $sieveConf['config'];
    $sieve = new Net_Sieve();
    $inVacation = false;
    @$sieve->connect( $sieveConf['host'] , $sieveConf['port'] , $sieveConf['options'] , $sieveConf['useTLS'] );
    @$sieve->login( $_SESSION['wallet']['Sieve']['user'], $_SESSION['wallet']['Sieve']['password'] , $sieveConf['loginType']);
    $script = $sieve->getScript($sieve->getActive());
    $old_rule = strripos($script, "##PSEUDO script start");
	if($old_rule) {
		if (preg_match("/^ *#vacation/im", $script))
			$inVacation = true;
	}
	else {
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
	}

    if( $inVacation )
    {	
		echo '<script type="text/javascript"> write_msg(get_lang("Attention, you are in out of office mode."), true);   </script>';
    }
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	echo '<script type="text/javascript" src="scripts.php?lang='.$GLOBALS['phpgw_info']['user']['preferences']['common']['lang'].'" charset="UTF-8" ></script>';
	
	if ( isset($GLOBALS['phpgw_info']['user']['preferences']['expressoMail']['use_shortcuts']) ) //usar teclas de atalho ?
	{
		echo '<script type="text/javascript" src="js/shortcut.js" ></script>';
	}
	
	// Get Preferences or redirect to preferences page.
	$GLOBALS['phpgw']->preferences->read_repository();
	unset($_SESSION['phpgw_info']['expressomail']['user']['preferences']);
	unset($_SESSION['phpgw_info']['expressomail']['user']['acl']);
	unset($_SESSION['phpgw_info']['expressomail']['user']['apps']);
	unset($_SESSION['phpgw_info']['expressomail']['server']['global_denied_users']);
	unset($_SESSION['phpgw_info']['expressomail']['server']['global_denied_groups']);
