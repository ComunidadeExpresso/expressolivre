<?php

class ContactPictureResource extends CatalogAdapter {	
	public function post($request){
		// to Receive POST Params (use $this->params)
 		parent::post($request);	
		
		if($this-> isLoggedIn()) 
		{								
			$contact = array();
			$contactID = $this->getParam('contactID');
			// User Contact
			if($this->getParam('contactType') == 1 && $contactID != null){
				$query = 'select A.id_contact, A.photo from phpgw_cc_contact A where A.id_contact='.$contactID.' and A.id_owner='.$this -> getUserId();
				if (!$this->getDb()->query($query))
					return false;
				if($this->getDb()->next_record()) {
					$row = $this->getDb()->row();
					if($row['photo'] != null) {
						$contact[] = array(
								'contactID'		=> $row['id_contact'],
								'contactImagePicture'	=> ($row['photo'] != null ? base64_encode($row['photo']) : "")
						);
					}
				}
			}
			// Global Catalog
			elseif($this->getParam('contactType') == 2){
				if(!$contactID){
					$contactID = $GLOBALS['phpgw_info']['user']['account_dn'];
				}
				$photo = $this->getUserLdapPhoto(urldecode($contactID));
				$contact[] = array(
						'contactID'		=> $contactID,
						'contactImagePicture'	=> ($photo != null ? base64_encode($photo[0]) : "")
				);
	
			}
			$result = array ('contacts' => $contact);
			$this->setResult($result);			
		}
		//to Send Response (JSON RPC format)
		return $this->getResponse();		
	}	

}