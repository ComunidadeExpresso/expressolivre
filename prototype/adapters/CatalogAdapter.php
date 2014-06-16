<?php


class CatalogAdapter extends ExpressoAdapter {	
	private $minArgumentSearch;
	private $userId;
	private $ldapCatalog;

	public function __construct($id){
		parent::__construct($id);
		$prefs = $GLOBALS['phpgw']->preferences->read();
		$this-> setMinArgumentSearch($prefs['expressoMail']['search_characters_number'] ? $prefs['expressoMail']['search_characters_number'] : "4");
	}
	
	protected function setMinArgumentSearch($minArgumentSearch){
		$this->minArgumentSearch = $minArgumentSearch;
	}
	
	protected function getMinArgumentSearch(){
		return $this-> minArgumentSearch;
	}
	
	protected function getUserId(){
		return $GLOBALS['phpgw_info']['user']['account_id'];
	}		

	
	protected function getLdapCatalog(){
		if(!$this->ldapCatalog) {
			$catalog_config = CreateObject("contactcenter.bo_ldap_manager");
			$_SESSION['phpgw_info']['expressomail']['ldap_server'] = $catalog_config ? $catalog_config->srcs[1] : null;
			$this->ldapCatalog = CreateObject("expressoMail.ldap_functions");
		}
	
		return $this->ldapCatalog;
	}
	
	protected function getDb(){
		return $GLOBALS['phpgw']->db;
	}	
	
	protected function getUserLdapAttrs($mail)
	{
		$filter="(&(phpgwAccountType=u)(mail=".$mail."))";
		$ldap_context = $_SESSION['phpgw_info']['expressomail']['ldap_server']['dn'];
		$justthese = array("dn", 'jpegPhoto','givenName', 'sn'); 
		$ds = $this->getLdapCatalog()->ds;
		if ($ds){
			$sr = @ldap_search($ds, $ldap_context, $filter, $justthese);	
			if ($sr) {
				$entry = ldap_first_entry($ds, $sr);
				if($entry) {									
					$givenName = @ldap_get_values_len($ds, $entry, "givenname");
					$sn = @ldap_get_values_len($ds, $entry, "sn");
					$contactHasImagePicture = (@ldap_get_values_len($ds, $entry, "jpegphoto") ? 1 : 0);
					$dn = ldap_get_dn($ds, $entry);
					return array(
						"contactID" => urlencode($dn),
						"contactFirstName" => $givenName[0],
						"contactLastName" 	=> $sn[0],
						"contactHasImagePicture" => $contactHasImagePicture 
					);
				}
			}
		}
		return false;
	}
	
	protected function getUserLdapPhoto($contactID) {		
		$ldap_context = $_SESSION['phpgw_info']['expressomail']['ldap_server']['dn'];
		$justthese = array("dn", 'jpegPhoto','givenName', 'sn');
		$this->getLdapCatalog()->ldapConnect(true);
		$ds = $this->getLdapCatalog()->ds;		
		
		if ($ds){
			$resource = @ldap_read($ds, $contactID, "phpgwaccounttype=u");
			$n_entries = @ldap_count_entries($ds, $resource);

			if ( $n_entries == 1) {			
				$first_entry = ldap_first_entry($ds, $resource);
				$obj = ldap_get_attributes($ds, $first_entry);
				
				if($obj['jpegPhoto']){
					return ldap_get_values_len( $ds, $first_entry, "jpegPhoto");
				}
			}								
		}
		return false;
	}	
	
	protected function getGlobalContacts($search, $uidNumber){
		$contacts = array();
		$params = array ("search_for" => $search);
 		$result = $this->getLdapCatalog()->quicksearch($params);
 		// Reconnect for searching other attributes.
 		$this->getLdapCatalog()->ldapConnect(true);
		foreach($result as $i => $row) {
			if(is_int($i)) {
				$contacts[$i] = array(
					'contactMails'	=> array($result[$i]['mail']),
					'contactPhones'	=> array($result[$i]['phone']),
					'contactAlias' => "",					
					'contactFullName' 	=> ($result[$i]['cn'] != null ? mb_convert_encoding($row['cn'],"UTF8", "ISO_8859-1") : ""),
					'contactBirthDate'	=> "",
					'contactNotes' 		=> ""
				);
				// Buscar atributos faltantes. 
				$otherAttrs = $this->getUserLdapAttrs($result[$i]['mail']);
				if(is_array($otherAttrs))
					$contacts[$i] = array_merge($otherAttrs, $contacts[$i]);				
			}
		}
		// Force ldap close
		ldap_close($this->getLdapCatalog()->ds);		
		$result = array ('contacts' => $contacts);
		$this->setResult($result);
		return $this->getResponse();
	}
}