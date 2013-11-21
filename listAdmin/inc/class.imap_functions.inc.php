<?php

class imap_functions
{
	var $imap;
	var $imapDelimiter;
	var $imap_admin;
	var $imap_passwd;
	var $imap_server;
	var $imap_port;
	
	function imap_functions(){
		$this->imap_admin	= $_SESSION['phpgw_info']['expresso']['email_server']['imapAdminUsername'];
		$this->imap_passwd	= $_SESSION['phpgw_info']['expresso']['email_server']['imapAdminPW'];
		$this->imap_server	= $_SESSION['phpgw_info']['expresso']['email_server']['imapServer'];
		$this->imap_port	= $_SESSION['phpgw_info']['expresso']['email_server']['imapPort'];
		$this->imapDelimiter= $_SESSION['phpgw_info']['expresso']['email_server']['imapDelimiter'];
		$this->imap 		= imap_open('{'.$this->imap_server.':'.$this->imap_port.'/novalidate-cert}', $this->imap_admin, $this->imap_passwd, OP_HALFOPEN);
	}
	
	function create($uid, $mailquota)
	{
		if (!imap_createmailbox($this->imap, '{'.$this->imap_server.'}' . "user" . $this->imapDelimiter . $uid))
		{
			$error = imap_errors();
			$result['status'] = false;
			$result['msg'] = 'Erro na funcao imap_function->create(INBOX): ' . $error[0];
			$result['error'] = $error[0];
			return $result;
		}
		if (!imap_createmailbox($this->imap, '{'.$this->imap_server.'}' . "user" . $this->imapDelimiter . $uid . $this->imapDelimiter . "Sent"))
		{
			$error = imap_errors();
			$result['status'] = false;
			$result['msg'] = 'Erro na funcao imap_function->create(Enviados): ' . $error[0];
			return $result;
		}
		if (!imap_createmailbox($this->imap, '{'.$this->imap_server.'}' . "user" . $this->imapDelimiter . $uid . $this->imapDelimiter . "Drafts"))
		{
			$error = imap_errors();
			$result['status'] = false;
			$result['msg'] = 'Erro na funcao imap_function->create(Rascunho): ' . $error[0];
			return $result;
		}
		if (!imap_createmailbox($this->imap, '{'.$this->imap_server.'}' . "user" . $this->imapDelimiter . $uid . $this->imapDelimiter . "Trash"))
		{
			$error = imap_errors();
			$result['status'] = false;
			$result['msg'] = 'Erro na funcao imap_function->create(Lixeira): ' . $error[0];
			return $result;
		}
		if (!imap_set_quota($this->imap,"user" . $this->imapDelimiter . $uid, ($mailquota*1024))) 
		{
			$error = imap_errors();
			$result['status'] = false;
			$result['msg'] = 'Erro na funcao imap_function->create(set_quota): ' . $error[0];
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
			$quota['mailquota'] = ($get_quota['limit'] / 1024);
			$quota['mailquota_used'] = ($get_quota['usage'] / 1024);
		}
			
		return $quota;
	}
	
	function change_user_quota($uid, $quota)
	{
		$set_quota = imap_set_quota($this->imap,"user" . $this->imapDelimiter . $uid, ($quota*1024));
		return true;
	}
	
	function delete_user($uid)
	{
		$result['status'] = true;
		
		//Seta acl imap para poder deletar o user.
		// Esta sem tratamento de erro, pois o retorno da funcao deve ter um bug.
		imap_setacl($this->imap, "user" . $this->imapDelimiter . $uid, $this->imap_admin, 'c');
		
		if (!imap_deletemailbox($this->imap, '{'.$this->imap_server.'}' . "user" . $this->imapDelimiter . $uid))
		{
			$result['status'] = false;
			$result['msg'] = "Erro na funcao imap_function->delete_user.\nRetorno do servidor: " . imap_last_error();
		}
		
		return $result;
	}
	
	function rename_mailbox($old_mailbox, $new_mailbox)
	{
		$result['status'] = true;		
		$result_rename = imap_renamemailbox($this->imap,
						'{'.$this->imap_server.':'.$this->imap_port.'}user' . $this->imapDelimiter . $old_mailbox,
						'{'.$this->imap_server.':'.$this->imap_port.'}user' . $this->imapDelimiter . $new_mailbox);
		
		if (!$result_rename)
		{
			$result['status'] = false;
			$result['msg'] = "Erro na funcao imap_function->rename_mailbox.\nRetorno do servidor: " . imap_last_error();
		}
		return $result;
	}
}
