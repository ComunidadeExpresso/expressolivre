<?php

class AddFolderResource extends MailAdapter {
	public function post($request){
		// to Receive POST Params (use $this->params)
 		parent::post($request);

		if($this-> isLoggedIn())
		{
			$parent_id = $this->getParam('parentFolderID');
			$parent_id = empty($parent_id) ? 'INBOX' : $parent_id;
			$new_name  = $this->getParam('folderName');

			$all_folders = $this->getImap()->get_folders_list();
			if(!$all_folders){
				return $this->getResponse();
			}

			$max_folders = $this->getImap()->prefs['imap_max_folders'];
			if(count($all_folders) == $max_folders)
				Errors::runException("MAIL_FOLDER_LIMIT_REACHED");

			if(empty($new_name) || preg_match('/[\/\\\!\@\#\$\%\&\*\(\)]/', $new_name))
				Errors::runException("MAIL_INVALID_NEW_FOLDER_NAME");

			$new_id = $parent_id . $this->getImap()->imap_delimiter . $new_name;

			$params['newp'] = $new_id;

			$result = $this->getImap()->create_mailbox($params);
			if($result != 'Ok')
				Errors::runException("MAIL_FOLDER_NOT_ADDED");
		}

		$this->setResult(array('folderID' => $new_id));

		//to Send Response (JSON RPC format)
		return $this->getResponse();
	}

}
