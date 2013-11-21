<?php

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
	
	function imap_functions(){
		$this->functions	= new functions;
		$this->imap_admin	= $_SESSION['phpgw_info']['expresso']['email_server']['imapAdminUsername'];
		$this->imap_passwd	= $_SESSION['phpgw_info']['expresso']['email_server']['imapAdminPW'];
		$this->imap_server	= $_SESSION['phpgw_info']['expresso']['email_server']['imapServer'];
		$this->imap_port	= $_SESSION['phpgw_info']['expresso']['email_server']['imapPort'];
		$this->imapDelimiter= $_SESSION['phpgw_info']['expresso']['email_server']['imapDelimiter'];
		$this->imap 		= imap_open('{'.$this->imap_server.':'.$this->imap_port.'/novalidate-cert}', $this->imap_admin, $this->imap_passwd, OP_HALFOPEN);
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

	function getMembersShareAccount($uid)
	{
		$owner_user_share = imap_getacl($this->imap, "user" . $this->imapDelimiter . $uid);

                //Organiza participantes da conta compartilha em um array, retira apenas os members, 
		$i =0;
		foreach($owner_user_share as $key => $value)
		{
			if ($i != 0)
			{
				$return[$i] = $key;
			}
			++$i;
		}

		//Ordena os participantes da conta compartilhada
		sort($return);

	        return $return;
	}

}
