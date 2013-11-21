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
		
	if(!isset($GLOBALS['phpgw_info'])){
        	$GLOBALS['phpgw_info']['flags'] = array(
                	'currentapp' => 'expressoMail1_2',
                	'nonavbar'   => true,
                	'noheader'   => true
        	);
	}

	
	$current_app = 'expressoMail1_2';
	$current_name	 = 'Expresso Mail';
	if(!$_SESSION['phpgw_info']['user']['preferences']['expressoMail']) { 
		$preferences = $GLOBALS['phpgw']->preferences->read(); 
		$_SESSION['phpgw_info']['user']['preferences']['expressoMail'] = $preferences['expressoMail'];
	}
  	$homedisplay = $_SESSION['phpgw_info']['user']['preferences']['expressoMail']['mainscreen_showmail'];
	if($homedisplay=='True')
	{
		$homedisplay = 1;
	}	
	$homedisplay = intval($homedisplay);
	
	$prev_currentapp = $GLOBALS['phpgw_info']['flags']['currentapp'];
	$GLOBALS['phpgw_info']['flags']['currentapp'] = $current_app;
	 
	if(intval($homedisplay))
	{
		$boemailadmin	= CreateObject('emailadmin.bo');
		$emailadmin_profile = $boemailadmin->getProfileList();
		$_SESSION['phpgw_info']['expressomail']['email_server'] = $boemailadmin->getProfile($emailadmin_profile[0]['profileID']);
		$_SESSION['phpgw_info']['expressomail']['user'] = $GLOBALS['phpgw_info']['user'];
		$_SESSION['phpgw_info']['expressomail']['server'] = $GLOBALS['phpgw_info']['server'];
		$expressoMail	= CreateObject($current_app.'.imap_functions');
		$mbox_stream = $expressoMail-> open_mbox(False,false); 		
		if(!$mbox_stream) {
			$portalbox = CreateObject('phpgwapi.listbox',
				Array(
					'title'     => "<font color=red>".lang('Connection failed with %1 Server. Try later.',lang('Mail'))."</font>",
					'primary'   => $GLOBALS['phpgw_info']['theme']['navbar_bg'],
					'secondary' => $GLOBALS['phpgw_info']['theme']['navbar_bg'],
					'tertiary'  => $GLOBALS['phpgw_info']['theme']['navbar_bg'],
					'width'     => '100%',
					'outerborderwidth' => '0',
					'header_background_image' => $GLOBALS['phpgw']->common->image('phpgwapi/templates/phpgw_website','bg_filler')
				)
			);
		}
		else {
			$messages	 = imap_sort($mbox_stream, SORTARRIVAL, true, SE_UID, UNSEEN);
			$num_new_messages = count($messages);
			$subjects = array();
			
			foreach($messages as $idx => $message){
				if($idx == 10){
					break;
				}
				$header = @imap_headerinfo($mbox_stream, imap_msgno($mbox_stream,$message), 80, 255);
				if (!is_object($header))
					return false;			
	
				$date_msg = date("d/m/Y",$header->udate);
				if (date("d/m/Y") == $date_msg)
					$date = date("H:i",$header->udate);
				else
					$date = $date_msg;

				$subject = $expressoMail->decode_string($header->fetchsubject);

                                $text=$date." .: ".$subject;
                                if(strlen($text) > 55){
                                    $text = html_entity_decode($text);
                                    $text = substr($text,0,55).' ...';
                                    $text = htmlentities($text);                                      
                                }
                                $text = "<div style='overflow:hidden;white-space:nowrap'>".$text."</div>";
                                
				$link_msg = $GLOBALS['phpgw']->link(
						'/'.$current_app.'/index.php',
						'msgball[msgnum]='.$message.'&msgball[folder]=INBOX');
				$data[] = array('text' => $text, 'link' => $link_msg);					
			}
					
			imap_close($mbox_stream);
			
			$title = $current_name." - ".($num_new_messages > 1 ? lang("You have %1 new messages!","<font color=red>".$num_new_messages."</font>") : ($num_new_messages == 1 ? str_replace("1","<font color=red>1</font>",lang("you have 1 new message!")) : lang("you have no new messages")));			
			$GLOBALS['phpgw']->translation->add_app($current_app);
	
			if ((isset($prev_currentapp)) && ($prev_currentapp)	&& ($GLOBALS['phpgw_info']['flags']['currentapp'] != $prev_currentapp))		
				$GLOBALS['phpgw_info']['flags']['currentapp'] = $prev_currentapp;
			
			$portalbox = CreateObject('phpgwapi.listbox',
				Array(
					'title'     => $title,
					'primary'   => $GLOBALS['phpgw_info']['theme']['navbar_bg'],
					'secondary' => $GLOBALS['phpgw_info']['theme']['navbar_bg'],
					'tertiary'  => $GLOBALS['phpgw_info']['theme']['navbar_bg'],
					'width'     => '100%',
					'outerborderwidth' => '0',
					'header_background_image' => $GLOBALS['phpgw']->common->image('phpgwapi/templates/phpgw_website','bg_filler')
				)
			);
	
			$app_id = $GLOBALS['phpgw']->applications->name2id('expressoMail');
			$GLOBALS['portal_order'][] = $app_id;
	
			$var = Array(
				'up'       => Array('url' => '/set_box.php', 'app' => $app_id),
				'down'     => Array('url' => '/set_box.php', 'app' => $app_id),
				'close'    => Array('url' => '/set_box.php', 'app' => $app_id),
				'question' => Array('url' => '/set_box.php', 'app' => $app_id),
				'edit'     => Array('url' => '/set_box.php', 'app' => $app_id)
			);
	
			while(list($key,$value) = each($var))		
				$portalbox->set_controls($key,$value);
	
			$portalbox->data = $data;
		}			
		echo "\n".'<!-- BEGIN Mailbox info -->'."\n".$portalbox->draw($extra_data).'<!-- END Mailbox info -->'."\n";
	}
