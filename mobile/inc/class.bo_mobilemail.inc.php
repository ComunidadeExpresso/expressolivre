<?php
	class bo_mobilemail {
		
		function get_translate_default_folder_name_from_id($folder_id) {
			$imap_delimiter = $_SESSION['phpgw_info']['expressomail']['email_server']['imapDelimiter'];
			
			switch ($folder_id) {
				case 'INBOX':
					return lang("Inbox");
				case 'INBOX'.$imap_delimiter.$_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultTrashFolder']: 
					return lang("Trash");
				case 'INBOX'.$imap_delimiter.$_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultDraftsFolder']: 
					return lang("Drafts");
				case 'INBOX'.$imap_delimiter.$_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultSpamFolder']: 
					return lang("Spam");
				case 'INBOX'.$imap_delimiter.$_SESSION['phpgw_info']['expressomail']['email_server']['imapDefaultSentFolder']: 
					return lang("Sent");
				default:
					return "";
			}
		}
	}
?>