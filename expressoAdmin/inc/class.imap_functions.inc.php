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
		
include_once('class.functions.inc.php');

class imap_functions
{
	var $functions;
	var $imap;
	var $imapDelimiter;
	var $imap_admin;
	var $imap_passwd;
	var $imap_server;
	var $imap_port;
    var $imap_trashfolder;
    var $imap_sentfolder;
    var $imap_draftsfolder;
    var $imap_spamfolder;
	
	function imap_functions()
	{
		$this->functions			= new functions;
		$this->imap_admin			= $_SESSION['phpgw_info']['expresso']['email_server']['imapAdminUsername'];
		$this->imap_passwd			= $_SESSION['phpgw_info']['expresso']['email_server']['imapAdminPW'];
		$this->imap_server			= $_SESSION['phpgw_info']['expresso']['email_server']['imapServer'];
		$this->imap_port			= $_SESSION['phpgw_info']['expresso']['email_server']['imapPort'];
		$this->imap_trashfolder  	= $_SESSION['phpgw_info']['expresso']['email_server']['imapDefaultTrashFolder']  ? $_SESSION['phpgw_info']['expresso']['email_server']['imapDefaultTrashFolder']  : str_replace("*","", $this->functions->lang("trash"));
		$this->imap_sentfolder   	= $_SESSION['phpgw_info']['expresso']['email_server']['imapDefaultSentFolder']   ? $_SESSION['phpgw_info']['expresso']['email_server']['imapDefaultSentFolder']   : str_replace("*","", $this->functions->lang("sent"));
		$this->imap_draftsfolder 	= $_SESSION['phpgw_info']['expresso']['email_server']['imapDefaultDraftsFolder'] ? $_SESSION['phpgw_info']['expresso']['email_server']['imapDefaultDraftsFolder'] : str_replace("*","", $this->functions->lang("drafts"));
		$this->imap_spamfolder   	= $_SESSION['phpgw_info']['expresso']['email_server']['imapDefaultSpamFolder']   ? $_SESSION['phpgw_info']['expresso']['email_server']['imapDefaultSpamFolder']   : str_replace("*","", $this->functions->lang("spam"));
		$this->imapDelimiter		= $_SESSION['phpgw_info']['expresso']['email_server']['imapDelimiter'];
		$this->imap 				= imap_open('{'.$this->imap_server.':'.$this->imap_port.'/novalidate-cert}', $this->imap_admin, $this->imap_passwd, OP_HALFOPEN);
	}
	
	function create($uid, $mailquota)
	{
		if (!imap_createmailbox($this->imap, '{'.$this->imap_server.'}' . "user" . $this->imapDelimiter . $uid))
		{
			$error = imap_errors();
			if ($error[0] == 'Mailbox already exists')
			{
				$result['status'] = true;
			}
			else
			{
				$result['status'] = false;
				$result['msg'] = $this->functions->lang('Error on function') . " imap_functions->create(INBOX) ($uid):" . $error[0];
			}
			return $result;
		}
		if ( (!empty($this->imap_sentfolder)) && (!imap_createmailbox($this->imap, '{'.$this->imap_server.'}' . "user" . $this->imapDelimiter . $uid . $this->imapDelimiter . $this->imap_sentfolder)) )
		{
			$error = imap_errors();
			$result['status'] = false;
			$result['msg'] = $this->functions->lang('Error on function') . " imap_functions->create(".$this->imap_sentfolder."):" . $error[0];
			return $result;
		}
		if ( (!empty($this->imap_draftsfolder)) && (!imap_createmailbox($this->imap, '{'.$this->imap_server.'}' . "user" . $this->imapDelimiter . $uid . $this->imapDelimiter . $this->imap_draftsfolder)) )
		{
			$error = imap_errors();
			$result['status'] = false;
			$result['msg'] = $this->functions->lang('Error on function') . " imap_functions->create(".$this->imap_draftsfolder."):" . $error[0];
			return $result;
		}
		if ( (!empty($this->imap_trashfolder)) && (!imap_createmailbox($this->imap, '{'.$this->imap_server.'}' . "user" . $this->imapDelimiter . $uid . $this->imapDelimiter . $this->imap_trashfolder)) )
		{
			$error = imap_errors();
			$result['status'] = false;
			$result['msg'] = $this->functions->lang('Error on function') . " imap_functions->create(".$this->imap_trashfolder."):" . $error[0];
			return $result;
		}
		/* Esperando correção do william (prognus) sobre a utilização do DSPAM
		if (!empty($this->imap_sentfolder))
		{
		    if (!imap_createmailbox($this->imap, '{'.$this->imap_server.'}' . "user" . $this->imapDelimiter . $uid . $this->imapDelimiter . $this->imap_spamfolder))
			{
			    $error = imap_errors();
			    $result['status'] = false;
			    $result['msg'] = $this->functions->lang('Error on function') . " imap_functions->create(".$this->imap_spamfolder."):" . $error[0];
			    return $result;
         }
		}		
		*/

		if (!imap_set_quota($this->imap,"user" . $this->imapDelimiter . $uid, ($mailquota*1024))) 
		{
			$error = imap_errors();
			$result['status'] = false;
			$result['msg'] = $this->functions->lang('Error on function') . " imap_functions->create(imap_set_quota):" . $error[0];
			return $result;
		}
		
		$result['status'] = true;
		return $result;
	}
	
	function get_user_info($uid)
	{
		$get_quota = @imap_get_quotaroot($this->imap,"user" . $this->imapDelimiter . $uid);
		
		if (count($get_quota) == 0)
		{
			$quota['mailquota'] = '-1';
			$quota['mailquota_used'] = '-1';
		}	
		else
		{
			$quota['mailquota'] = round (($get_quota['limit'] / 1024), 2);
			$quota['mailquota_used'] = round (($get_quota['usage'] / 1024), 2);
		}
		return $quota;
	}
	
	function change_user_quota($uid, $quota)
	{
		$result['status'] = true;
		
		if (!imap_set_quota($this->imap,"user" . $this->imapDelimiter . $uid, ($quota*1024)) )
		{
			$result['status'] = false;
			$result['msg'] = $this->functions->lang('it was not possible to change users mailbox quota') . ".\n";
			$result['msg'] .= $this->functions->lang('Server returns') . ': ' . imap_last_error();
		}
		
		return $result;
	}
	
	function delete_mailbox($uid)
	{
		$result['status'] = true;
		
		//Seta acl imap para poder deletar o user.
		// Esta sem tratamento de erro, pois o retorno da funcao deve ter um bug.
		imap_setacl($this->imap, "user" . $this->imapDelimiter . $uid, $this->imap_admin, 'c');
		
		if (!imap_deletemailbox($this->imap, '{'.$this->imap_server.'}' . "user" . $this->imapDelimiter . $uid))
		{
			$result['status'] = false;
			$result['msg'] = $this->functions->lang('it was not possible to delete mailbox') . ".\n";
			$result['msg'] .= $this->functions->lang('Server returns') . ': ' . imap_last_error();
		}
		
		return $result;
	}
	
	function rename_mailbox($old_mailbox, $new_mailbox)
	{
		$result['status'] = true;

		if (!$quota = @imap_get_quotaroot($this->imap, 'user' . $this->imapDelimiter . $old_mailbox))
		{
				$result['status'] = false;
				$result['msg'] = $this->functions->lang("Error getting user quota. Process aborted.\n") . $this->functions->lang('Server returns') . ': ' . imap_last_error();
				return $result;
		}
		$limit = $quota['STORAGE']['limit'];
		$usage = $quota['STORAGE']['usage'];
		
		if ($usage >= $limit)
		{
			if (! @imap_set_quota($this->imap, 'user' . $this->imapDelimiter . $old_mailbox, (int)($usage+10240)) )
			{
				$result['status'] = false;
				$result['msg'] = $this->functions->lang("Error increasing user quota. Process aborted.\n") . $this->functions->lang('Server returns') . ': ' . imap_last_error();
				return $result;
			}
		}

		
		if (! @imap_renamemailbox($this->imap,
						'{'.$this->imap_server.':'.$this->imap_port.'}user' . $this->imapDelimiter . $old_mailbox,
						'{'.$this->imap_server.':'.$this->imap_port.'}user' . $this->imapDelimiter . $new_mailbox) )
		{
			$result['status'] = false;
			$result['msg'] = $this->functions->lang('Server returns') . ': ' . imap_last_error();

		}


		if ($usage >= $limit)
		{
			if (! @imap_set_quota($this->imap, 'user' . $this->imapDelimiter . $new_mailbox, (int)($limit)) )
			{
				$result['status'] = false;
				$result['msg'] .= $this->functions->lang("Error returning user quota.\n") . $this->functions->lang('Server returns') . ': ' . imap_last_error();
				
				@imap_renamemailbox($this->imap,
					'{'.$this->imap_server.':'.$this->imap_port.'}user' . $this->imapDelimiter . $new_mailbox,
					'{'.$this->imap_server.':'.$this->imap_port.'}user' . $this->imapDelimiter . $old_mailbox);
			}
		}

		return $result;
	}
	function empty_user_inbox($params){
	       if (!$this->functions->check_acl($_SESSION['phpgw_session']['session_lid'], 'empty_user_inbox'))
		{
			$result['status'] = false;
			$result['msg'] = $this->functions->lang('You do not have access to clean an user inbox');
			return $result;
		} else return $this->empty_inbox($params);
		
        }
        function empty_shared_account_inbox($params){
            if (!$this->functions->check_acl($_SESSION['phpgw_session']['session_lid'], 'empty_shared_accounts_inbox'))
		{
			$result['status'] = false;
			$result['msg'] = $this->functions->lang('You do not have right to empty an shared account inbox');
			return $result;
		} else return $this->empty_inbox($params);
        }
	function empty_inbox($params)
	{
		// Verifica o acesso do gerente
		if (!($this->functions->check_acl($_SESSION['phpgw_session']['session_lid'], 'empty_user_inbox') ||
                      $this->functions->check_acl($_SESSION['phpgw_session']['session_lid'], 'empty_shared_accounts_inbox')
                  ))
		{
			$result['status'] = false;
			$result['msg'] = $this->functions->lang('You do not have access to clean an inbox');
			return $result;
		}

                if ($_SESSION['phpgw_info']['expressomail']['email_server']['imapTLSEncryption'] == 'yes')
		{
			$imap_options = '/tls/novalidate-cert';
		}
		else
		{
			$imap_options = '/notls/novalidate-cert';
		}

		
		$result['status'] = true;
		$uid = $params['uid'];
		
		$return_setacl = imap_setacl($this->imap, "user" . $this->imapDelimiter . $uid, $this->imap_admin, 'lrswipcda');
		
		if ($return_setacl)
		{
			$mbox_stream = imap_open('{'.$this->imap_server.':'.$this->imap_port.$imap_options .'}user'. $this->imapDelimiter . $uid, $this->imap_admin, $this->imap_passwd);
			
			$check = imap_mailboxmsginfo($mbox_stream);
			$inbox_size = (string)(round ((($check->Size)/(1024*1024)), 2));
			
			$return_imap_delete = imap_delete($mbox_stream,'1:*');
			imap_close($mbox_stream, CL_EXPUNGE);
			
			imap_setacl ($this->imap, "user" . $this->imapDelimiter . $uid, $this->imap_admin, '');
			
			if ($return_imap_delete)
			{
				$result['inbox_size'] = $inbox_size;
				
				$get_user_quota = @imap_get_quotaroot($this->imap,"user" . $this->imapDelimiter . $uid);
				$result['mailquota_used'] = (string)(round(($get_user_quota['usage']/1024), 2));
			}
			else
			{
				$result['status'] = false;
				$result['msg'] = $this->functions->lang('It was not possible clean the inbox') . ".\n" . $this->functions->lang('Server returns') . ': ' . imap_last_error();
			}
		}
		else
		{
			$result['status'] = false;
			$result['msg'] = $this->functions->lang('It was not possible to modify the users acl') . ".\n" . $this->functions->lang('Server returns') . ': ' . imap_last_error();
		}
		return $result;
	}
	function getaclfrombox($params)
	{
        $boxacl = imap_utf7_encode($params['uid']);

		$return = array();
		
		$mbox_acl = imap_getacl($this->imap, "user" . $this->imapDelimiter . $boxacl);
		
		foreach ($mbox_acl as $user => $acl)
		{
			if ($user != $boxacl )
			{
				$return[$user] = $acl;
			}
		}
		return $return;
	}
	function setaclfrombox($user, $acl, $mailbox)
	{
		$serverString = '{'.$this->imap_server.':'.$this->imap_port.'/novalidate-cert}';
		$mailboxes_list = imap_getmailboxes($this->imap, $serverString, "user".$this->imapDelimiter.$mailbox.$this->imapDelimiter."*");                
		$result = Array();
                $result['status'] = true;
                if (is_array($mailboxes_list))
		{
                        $folder = str_replace($serverString, "", imap_utf7_encode("user".$this->imapDelimiter.$mailbox));
                        $folder = str_replace("&-", "&", $folder);

                        if (imap_setacl ($this->imap, $folder, $user, $acl) ) {
                            foreach ($mailboxes_list as $key => $val)
                            {
                                    $folder = str_replace($serverString, "", imap_utf7_encode($val->name));
                                    $folder = str_replace("&-", "&", $folder);
                                    if (!imap_setacl ($this->imap, $folder, $user, $acl))
                                    {
                                            $result['status'] = false;
                                            $result['msg']  = $this->functions->lang('Error on function') . ' imap_functions->setaclfrombox: imap_setacl';
                                            $result['msg'] .= "\n" . $this->functions->lang('Server return') . ': ' . imap_last_error();
                                            break;
                                    }
                            }
                        } else {
                            $result['status'] = false;
                            $result['msg']  = $this->functions->lang('Error on function') . ' imap_functions->setaclfrombox: imap_setacl';
                            $result['msg'] .= "\n" . $this->functions->lang('Server return') . ': ' . imap_last_error();
//                            break;
                        }
		}

		if( $result['status'] )
		    $this->sendACLAdvice( $user, $acl, $mailbox );

		return $result;
	}
        function save_shared_account($params){           
            //Rename mailbox
            $result = Array();
            $result['status'] = true;
            if($params['uid'] != $params['old_uid'] ){
                    $result = $this->rename_mailbox($params['old_uid'], $params['uid']);
                    if(!$result['status']) return $result;
            }

            //Begin edit Quota
            $quota = $this->get_user_info($params['uid'] );

            if ( $quota['mailquota'] != $params['mailquota'] ){
                    if(!$this->functions->check_acl($_SESSION['phpgw_session']['session_lid'], 'edit_shared_accounts_quote') )
                    {
                                $result['status'] = false;
                                $result['msg'] = $this->functions->lang('You do not have access to edit an shared account inbox quota');
                                return $result;
                    } else $this->change_user_quota($params['old_uid'], $params['mailquota']);
            }
            //End Edit Quota
 
            /* Begin Set ACL */
            //Old users set in the box
            $owners_acl_old = $this->getaclfrombox($params);
            //new settings
            //necessario devido a um bug do serialize do javascript do connector zera uma string
            //serializada "N;", diferente da serializada do php "a:0:{}"
            
            if( $params['owners_acl'] != "N;" ){
            	$owners_acl_new = unserialize($params['owners_acl']);
            } else {
            	$owners_acl_new = Array();            
            }

            $owners_to_remove = array_diff_assoc($owners_acl_old,$owners_acl_new);
            $owners_to_add = array_diff_assoc($owners_acl_new,$owners_acl_old);
			
            //Find modified users
            $tmp_update = array_diff_assoc($owners_acl_old,$owners_to_remove);
            $owners_to_update = Array();
            foreach($tmp_update  as $user => $acl){
                if($owners_acl_old[$user] != $acl){
                    $owners_to_update[$user] = $acl;
                }
            }
            
            //Check Modify manage acl
            
            if( (count($owners_to_remove) > 0 || count($owners_to_add) > 0 || count($owners_to_update) > 0) && !$this->functions->check_acl($_SESSION['phpgw_session']['session_lid'], 'edit_shared_accounts_acl') ){
                $result['status'] = false;
				$result['msg'] = $this->functions->lang('You do not have access to edit an shared account inbox acl');
				return $result;
            }

            if(count($owners_to_remove) > 0)
            {
                foreach($owners_to_remove as $user => $acl)
                {
                    $this->functions->write_log("User removed from the shared account",'USER: '.$user.' - SHARED ACCOUNT: '.$params['uid']);
                }
            }
            if(count($owners_to_add) > 0)
            {
                foreach($owners_to_add as $user => $acl)
                {
                    $this->functions->write_log("User added from the shared account",'USER: '.$user.' - SHARED ACCOUNT: '.$params['uid']);
                }
            }

            //file_put_contents("/tmp/saida", "old ".print_r($owners_acl_old, true)."remove ".print_r($owners_to_remove, true)."add ".print_r($owners_to_add, true)."update ".print_r($owners_to_update, true));
            if( is_array($owners_acl_new)){
                foreach($owners_to_remove as $user => $acl){
                    $params['user'] = $user;
                    $params['acl'] = "";
                    $user = $params['user'];                  
                    $result = $this->setaclfrombox($user,"",$params['uid']);
                }
                //add new users
                foreach($owners_to_add as $user => $acl){
                    $params['user'] = $user;
                    $params['acl'] = $acl;
                    $result = $this->setaclfrombox($user,$acl,$params['uid']);
                }
                //update users
                 foreach($owners_to_update as $user => $acl){
                    $params['user'] = $user;
                    $params['acl'] = $acl;
                    $result = $this->setaclfrombox($user,$acl,$params['uid']);
                }               
            }
            /* End Set Acl */
            return $result;
        }
	
	function sendACLAdvice( $user, $acls, $shared_account )
	{
	    //acl treat
	    $acl_labels = array( 'lrs' => 'read messages from this shared account',
				 'd' => 'delete/move messages from this shared account',
				 'wi' => 'create/add messages in this shared account',
				 'a' => 'send message by this shared account',
				 'p' => 'save sent messages in this shared account',
				 'c' => 'create or delete folders on this shared account' );

	    $acl_found = array();

	    foreach( $acl_labels as $acl => $label )
	    {
		if( strpos( $acls, $acl ) !== false )
		{
		    $acl_found[] = $this->functions->lang( $label );
		}
	    }

	    $acl = implode( "<br/>", $acl_found );

	    if( empty( $acl ) )
	    return;

	    //body mail template generation
	    $body = $this->getTemplate( "body_email.tpl", array( "user" => $user,
								 "acl" => $acl,
								 "shared_account" => $shared_account ) );

	    //ldap fetch mail to
	    require_once('class.ldap_functions.inc.php');

//	    if( !$ldap )
//	    {
		$ldap = new ldap_functions();
//	    }

	    $to = $ldap->uid2mailforwardingaddress( $user );
	    $to = $to['mail'];

	    //mail send service
	    $mail = ServiceLocator::getService( 'mail' );
	    $mail->sendMail( $to, false, $this->functions->lang("Your user was add/updated in the shared account"), $body );
	}

	function getTemplate( $tpls, $macros, $target = false )
	{
	    require_once( ROOT.'/header.inc.php' );

	    $template = CreateObject( 'phpgwapi.Template', PHPGW_APP_TPL );

	    if( !is_array( $tpls ) )
	    {
		$tpls = array( $tpls );
	    }

	    $keys = array_keys( $tpls );

	    if( !array_diff_key( $tpls, array_keys( $keys ) ) )
	    {
		$newTpls = array();

		foreach( $tpls as $tpl )
		{
		    $key = basename( $tpl );

		    $dot = strrpos( $key, '.' );

		    if( $dot !== false )
		    {
			$key = substr( $key, 0, $dot );
		    }

		    $newTpls[ $key ] = $tpl;
		}

		$tpls = $newTpls;

		$keys = array_keys( $tpls );
	    }

	    $template->set_file( $tpls );

	    $target = $target ? $tpls[ $target ] : $keys[0];

	    $template->set_var( $this->functions->make_dinamic_lang( $template, $target ) );
	    $template->set_var( $macros );

	    return $template->fp( 'out', $target );
	}
}
