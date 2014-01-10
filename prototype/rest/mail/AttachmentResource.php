<?php

class AttachmentResource extends MailAdapter {	
	public function post($request){		
		// to Receive POST Params (use $this->params)		
 		parent::post($request); 		
 		$folderID 		= $this->getParam('folderID');
 		$msgID 			= $this->getParam('msgID');
 		$attachmentID 	= $this->getParam('attachmentID');
 		
		if($this-> isLoggedIn()) {
								
			if( $folderID && $msgID && $attachmentID) {				
				$dir = PHPGW_INCLUDE_ROOT."/expressoMail/inc";
				
				if($this->getExpressoVersion() != "2.2"){
					$_GET['msgFolder'] = $folderID;
					$_GET['msgNumber'] = $msgID;
					$_GET['indexPart'] = $attachmentID;
					include("$dir/get_archive.php");
					
				}else{
					$_GET['msg_folder'] = $folderID;
					$_GET['msg_number'] = $msgID;
					$_GET['msg_part'] = $attachmentID;
					$_GET['idx_file']	= $this->getParam('attachmentIndex');
					$_GET['newfilename']= $this->getParam('attachmentName');
					$_GET['encoding']	= $this->getParam('attachmentEncoding');
					include("$dir/gotodownload.php");
				}
				// Dont modify header of Response Method to 'application/json'
				$this->setCannotModifyHeader(true);
				return $this->getResponse();
			}
			else{
				Errors::runException("MAIL_ATTACHMENT_NOT_FOUND");
			}
		}
	}
}