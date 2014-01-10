<?php

class SendSupportFeedbackResource extends MailAdapter {
	public function post($request){
		// to Receive POST Params (use $this->params)
 		parent::post($request);

		if($this-> isLoggedIn())
		{
			$msgBody = $this->getParam("message");

			$params['input_to'] = $GLOBALS['phpgw_info']['server']['sugestoes_email_to'];
			$params['input_cc'] = $GLOBALS['phpgw_info']['server']['sugestoes_email_cc'];
			$params['input_cc'] = $GLOBALS['phpgw_info']['server']['sugestoes_email_bcc'];
			$params['input_subject'] = lang("Suggestions");
			$params['body'] = $msgBody;
			$params['type'] = 'textplain';

			$GLOBALS['phpgw']->preferences->read_repository();
			$_SESSION['phpgw_info']['expressomail']['user'] = $GLOBALS['phpgw_info']['user'];
			$boemailadmin   = CreateObject('emailadmin.bo');
			$emailadmin_profile = $boemailadmin->getProfileList();
			$_SESSION['phpgw_info']['expressomail']['email_server'] = $boemailadmin->getProfile($emailadmin_profile[0]['profileID']);
			$_SESSION['phpgw_info']['expressomail']['server'] = $GLOBALS['phpgw_info']['server'];
			$_SESSION['phpgw_info']['expressomail']['user']['email'] = $GLOBALS['phpgw']->preferences->values['email'];

			$expressoMail = CreateObject('expressoMail.imap_functions');
			$returncode   = $expressoMail->send_mail($params);

			if (!$returncode || !(is_array($returncode) && $returncode['success'] == true))
				Errors::runException("MAIL_NOT_SENT");
		}

		$this->setResult(true);

		//to Send Response (JSON RPC format)
		return $this->getResponse();
	}

}
