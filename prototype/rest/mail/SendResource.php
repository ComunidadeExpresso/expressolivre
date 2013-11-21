<?php

class SendResource extends MailAdapter {
	public function post($request){
		// to Receive POST Params (use $this->params)
 		parent::post($request);

		if($this-> isLoggedIn())
		{
			// parametros recuperados conforme draft
			$msgForwardTo		= $this->getParam("msgForwardTo");
			$originalMsgID		= $this->getParam("originalMsgID");
			$originalUserAction	= $this->getParam("originalUserAction");

			$params['input_subject']	= $this->getParam("msgSubject");
			$params['input_to']			= $this->getParam("msgTo");
			$params['input_cc']			= $this->getParam("msgCcTo");
			$params['input_cco']		= $this->getParam("msgBccTo");
			$params['input_replyto']	= $this->getParam("msgReplyTo");
			$params['body']				= $this->getParam("msgBody");
			$params['type']				= $this->getParam("msgType") ? $this->getParam("msgType") : "plain";
			$params['folder'] =	
				$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['save_in_folder'] == "-1" ? "null" :
				$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['save_in_folder'];

			if(count($_FILES))
			{
				$files = array();
				$totalSize = 0;
				foreach( $_FILES as $name => $file )
				{
					$files[$name] = array('name' => $file['name'],
							'type' => $file['type'],
							'source' => base64_encode(file_get_contents( $file['tmp_name'], $file['size'])),
							'size' => $file['size'],
							'error' => $file['error']
					);
					$totalSize += $file['size'];
				}
				
				$uploadMaxFileSize = str_replace("M","",$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['max_attachment_size']) * 1024 * 1024;
				if($totalSize > $uploadMaxFileSize){
					Errors::runException("MAIL_NOT_SENT_LIMIT_EXCEEDED", $_SESSION['phpgw_info']['user']['preferences']['expressoMail']['max_attachment_size']);
				}

				if($this->getExpressoVersion() != "2.2")
				{
					require_once (__DIR__.'/../../../prototype/api/controller.php');
					Controller::addFallbackHandler( 0, function($e){
						throw $e;
					} );
				
					$result = array();
					$attachments_ids = array();
						
					foreach($files as $key => $value){
						$value['disposition']  = isset($value['disposition']) ?
							$value['disposition'] : 'attachment';
						try{
							$attachment = Controller::put( array( 'concept' =>  "mailAttachment" ), $value );
							$attachments_ids[] = $attachment[0]['id'];
						}catch(Exception $e){
							Errors::runException($e->getMessage());
						}
					}
					$params['attDisposition1'] 	= 'attachment';
					$params['attachments'] 		= json_encode($attachments_ids);
				}
			}
			$returncode = $this->getImap()->send_mail($params);
			if (!$returncode || !(is_array($returncode) && $returncode['success'] == true))
				Errors::runException("MAIL_NOT_SENT");
		}

		$this->setResult(true);

		//to Send Response (JSON RPC format)
		return $this->getResponse();
	}

}
