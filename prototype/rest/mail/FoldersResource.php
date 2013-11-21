<?php

class FoldersResource extends MailAdapter {	
	public function post($request){
		// to Receive POST Params (use $this->params)		
 		parent::post($request);	 		
 		
		if($this-> isLoggedIn()) 
		{	
			$imap_folders =  $this->getImap()->get_folders_list();
		}
		if(!$imap_folders){
			return $this->getResponse();
		}
			
		$all_folders    = array();
		$user_folders   = array();
		$shared_folders = array();
		
		foreach ($imap_folders as $i => $imap_folder) {
			if(is_int($i)) {
				$folder = array();
				$folder['folderName'] 	  = mb_convert_encoding($imap_folder['folder_name'], "UTF8", "ISO_8859-1");
				 if(strtoupper($folder['folderName']) == 'INBOX') {				
					$folder['folderName'] = $this->getImap()->functions->getLang("Inbox");
				}
				$folder['folderParentID'] = mb_convert_encoding($imap_folder['folder_parent'],'UTF8','ISO-8859-1');
				$folder['folderHasChildren'] = $imap_folder['folder_hasChildren'];
				$folder['qtdUnreadMessages'] = $imap_folder['folder_unseen'];
				$folder['qtdMessages'] = $this->getImap()->get_num_msgs(array("folder" => $imap_folder['folder_id']));
				$folder['folderID'] = mb_convert_encoding($imap_folder['folder_id'],'UTF8','ISO-8859-1');
		
				if(substr($folder['folderID'], 0, 4) == 'user'){
					$folder['folderType'] = "6";
					$shared_folders[] = $folder;
				}else if(array_key_exists($folder['folderID'], $this->defaultFolders) !== false) {
					$folder_type = $this->defaultFolders[$folder['folderID']];
					$folder['folderType'] = $folder_type;
					$default_folders[] = $folder;
				}
				else{
					$folder['folderType'] = "5";
					$user_folders[] = $folder;
				}
			}
		}
		
		$all_folders = array_merge($default_folders, $user_folders, $shared_folders);
		$quota_folders = $this->getImap()->get_quota_folders();
		$search = $this->getParam('search') ? mb_convert_encoding($this->getParam('search'),"ISO_8859-1", "UTF8") : null;
		foreach($all_folders as $i => $folder){
			$folder_name = mb_convert_encoding($folder['folderName'],"ISO_8859-1", "UTF8");
			$folder_id   = mb_convert_encoding($folder['folderID'],"ISO_8859-1", "UTF8");
			if($folder_id == 'INBOX') {
				$j = $this->getImap()->functions->getLang("Inbox");
			}else {
				$j = str_replace("INBOX".$this->getImap()->imap_delimiter,"",$folder_id);
			}
			$all_folders[$i]['diskSizeUsed'] 	= $quota_folders[$j]['quota_used'];
			$all_folders[$i]['diskSizePercent'] = $quota_folders[$j]['quota_percent']/100;
			if($search != null && stristr($folder_name, $search) == null){
				unset($all_folders[$i]);
			}
		}
		
		$result = array (
				'folders' => array_values($all_folders),
				'diskSizeUsed'     => $quota_folders['quota_root']['quota_used']*1024,
				'diskSizeLimit'    => $quota_folders['quota_root']['quota_limit']*1024,
				'diskSizePercent'  => $quota_folders['quota_root']['quota_percent']/100
		);			
		
		$this->setResult($result);
		
		//to Send Response (JSON RPC format)
		return $this->getResponse();		
	}	

}