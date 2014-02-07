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
		
if(!isset($_SESSION['phpgw_info']['expressomail']['server']['db_name'])) { 
	include_once('../header.inc.php'); 
	$_SESSION['phpgw_info']['expressomail']['server']['db_name'] = $GLOBALS['phpgw_info']['server']['db_name'];  
	$_SESSION['phpgw_info']['expressomail']['server']['db_host'] = $GLOBALS['phpgw_info']['server']['db_host']; 
	$_SESSION['phpgw_info']['expressomail']['server']['db_port'] = $GLOBALS['phpgw_info']['server']['db_port']; 
	$_SESSION['phpgw_info']['expressomail']['server']['db_user'] = $GLOBALS['phpgw_info']['server']['db_user']; 
	$_SESSION['phpgw_info']['expressomail']['server']['db_pass'] = $GLOBALS['phpgw_info']['server']['db_pass']; 
	$_SESSION['phpgw_info']['expressomail']['server']['db_type'] = $GLOBALS['phpgw_info']['server']['db_type']; 
} 
else{ 
	define('PHPGW_INCLUDE_ROOT','../');      
	define('PHPGW_API_INC','../phpgwapi/inc');       
	include_once(PHPGW_API_INC.'/class.db.inc.php'); 
} 
	
class db_functions
{	
	
	var $db;
	var $user_id;
	var $related_ids; 
	
	function db_functions(){
		$this->db = new db();		
		$this->db->Halt_On_Error = 'no';
		$this->db->connect(
				$_SESSION['phpgw_info']['expressomail']['server']['db_name'], 
				$_SESSION['phpgw_info']['expressomail']['server']['db_host'],
				$_SESSION['phpgw_info']['expressomail']['server']['db_port'],
				$_SESSION['phpgw_info']['expressomail']['server']['db_user'],
				$_SESSION['phpgw_info']['expressomail']['server']['db_pass'],
				$_SESSION['phpgw_info']['expressomail']['server']['db_type']
		);		
		$this -> user_id = $_SESSION['phpgw_info']['expressomail']['user']['account_id'];	
	}

	// BEGIN of functions.
	function get_cc_contacts()
	{				
		$result = array();
		$stringDropDownContacts = '';		
		
		$query_related = $this->get_query_related('A.id_owner'); // field name for owner
			
		// Traz os contatos pessoais e compartilhados
		$query = 'select A.names_ordered, C.connection_value from phpgw_cc_contact A, '.
			'phpgw_cc_contact_conns B, phpgw_cc_connections C where '.
			'A.id_contact = B.id_contact and B.id_connection = C.id_connection '.
			'and B.id_typeof_contact_connection = 1 and ('.$query_related.') group by '. 
			'A.names_ordered,C.connection_value	order by lower(A.names_ordered)';
		
        if (!$this->db->query($query))
        	return null;
		while($this->db->next_record())
			$result[] = $this->db->row();

		if (count($result) != 0) 
		{
			// Monta string				
			foreach($result as $contact)
				$stringDropDownContacts = $stringDropDownContacts . urldecode(urldecode($contact['names_ordered'])). ';' . $contact['connection_value'] . ',';
			//Retira ultima virgula.
			$stringDropDownContacts = substr($stringDropDownContacts,0,(strlen($stringDropDownContacts) - 1));
		}
		else 
			return null;

		return $stringDropDownContacts;
	}
	// Get Related Ids for sharing contacts or groups.
	function get_query_related($field_name){		
		$query_related = $field_name .'='.$this -> user_id;
		// Only at first time, it gets all related ids...
		if(!$this->related_ids) {
			$query = 'select id_related from phpgw_cc_contact_rels where id_contact='.$this -> user_id.' and id_typeof_contact_relation=1';		
			if (!$this->db->query($query)){
    	    	return $query_related;
			}
			
			$result = array( );
			while($this->db->next_record()){
				$row = $this->db->row();
				$result[] = $row['id_related'];
			}
			if($result)
				$this->related_ids = implode(",",$result);
		}
		if($this->related_ids)
			$query_related .= ' or '.$field_name.' in ('.$this->related_ids.')';
		
		return $query_related;
	}
	function get_cc_groups() 
	{
		// Pesquisa no CC os Grupos Pessoais.
		$stringDropDownContacts = '';			
		$result = array();
		$query_related = $this->get_query_related('owner'); // field name for 'owner'		
		$query = 'select title, short_name, owner from phpgw_cc_groups where '.$query_related.' order by lower(title)';

		// Executa a query 
		if (!$this->db->query($query))
        	return null;
		// Retorna cada resultado            	
		while($this->db->next_record())
			$result[] = $this->db->row();

		// Se houver grupos ....				
		if (count($result) != 0) 
		{
			// Create Ldap Object, if exists related Ids for sharing groups.
			if($this->related_ids){
				$_SESSION['phpgw_info']['expressomail']['user']['cc_related_ids']= array();
				include_once("class.ldap_functions.inc.php");
				$ldap = new ldap_functions();
			}
			$owneruid = '';
			foreach($result as $group){
				// Searching uid (LDAP), if exists related Ids for sharing groups.
				// Save into user session. It will used before send mail (verify permission).
				if(!isset($_SESSION['phpgw_info']['expressomail']['user']['cc_related_ids'][$group['owner']]) && isset($ldap)){					
					$_SESSION['phpgw_info']['expressomail']['user']['cc_related_ids'][$group['owner']] = $ldap -> uidnumber2uid($group['owner']);
				}
				if($this->user_id != $group['owner'])
					$owneruid = "::".$_SESSION['phpgw_info']['expressomail']['user']['cc_related_ids'][$group['owner']];
				else
					$owneruid = '';

				$stringDropDownContacts .=  $group['title']. ';' . ($group['short_name'].$owneruid) . ',';
			}
			//Retira ultima virgula.
			$stringDropDownContacts = substr($stringDropDownContacts,0,(strlen($stringDropDownContacts) - 1));
		}
		else
			return null;		
		return $stringDropDownContacts;
	}
function getContactsByGroupAlias($alias)
	{
		/*
		list($alias,$uid) = explode("::",$alias);		
		
		$cc_related_ids = $_SESSION['phpgw_info']['expressomail']['user']['cc_related_ids'];		
		// Explode personal group, If exists related ids (the user has permission to send email).
		
		if(is_array($cc_related_ids) && $uid){
			$owner =  array_search($uid,$cc_related_ids);					
		}*/
		$groups = $this->get_cc_groups();
		if ($groups){
			$groups = explode(",", $groups);
			for($ii=0; $ii < count($groups); ++$ii) {
				$tmp = preg_split("/;|\::/",$groups[$ii]);
				$relatedGroups[$ii] = array("name" => $tmp[0],"alias" => $tmp[1],"owner" => $tmp[2]);
			}
			foreach ($relatedGroups as $key => $value) {
				if ($value["alias"] == $alias)
				 	$owner = $value["owner"];
			}
			if ($owner){
					include_once("class.ldap_functions.inc.php");
					$ldap = new ldap_functions();			
					$owner = $ldap->uid2uidnumber($owner);
			}
		}

		$query = "select C.id_connection, A.names_ordered, C.connection_value from phpgw_cc_contact A, ".
		"phpgw_cc_contact_conns B, phpgw_cc_connections C,phpgw_cc_contact_grps D,phpgw_cc_groups E where ".
		"A.id_contact = B.id_contact and B.id_connection = C.id_connection ".
		"and B.id_typeof_contact_connection = 1 and ".
		"A.id_owner =".($owner ? $owner : $this->user_id)." and ".			
		"D.id_group = E.id_group and ".
		"D.id_connection = C.id_connection and E.short_name = '".$alias."'";

		if (!$this->db->query($query))
		{
			exit ('Query failed! File: '.__FILE__.' on line'.__LINE__);
		}

		$return = false;

		while($this->db->next_record())
		{
			$return[] = $this->db->row(); 
		}

		return $return;
	}

	function getAddrs($array_addrs) {
		$array_addrs_final = array();

        $array_addrs_count = count($array_addrs);
		for($i = 0; $i < $array_addrs_count; ++$i){
			$j = count($array_addrs_final);

			if(preg_replace('/\s+/', '', $array_addrs[$i]) != ""){

				if(strchr($array_addrs[$i],'@') == "") {		
					if(strpos($array_addrs[$i],'<') && strpos($array_addrs[$i],'>')){
						$alias = substr($array_addrs[$i], strpos($array_addrs[$i],'<'), strpos($array_addrs[$i],'>'));
						$alias = str_replace('<','', str_replace('>','',$alias));

					}
					else{
						$alias = $array_addrs[$i];			
						$alias = preg_replace('/\s/', '', $alias);
					} 	

					$arrayContacts = $this -> getContactsByGroupAlias($alias);


					if($arrayContacts) {
						foreach($arrayContacts as $index => $contact){
							if($contact['names_ordered']) {
								$array_addrs_final[$j] = '"'.$contact['names_ordered'].'" <'.$contact['connection_value'].'>';
							}
							else 
								$array_addrs_final[$j] = $contact['connection_value'];

							++$j;
						}
					}else{
						return array("False" => "$alias");
					}
				}
//-- validação email --
				else{
					$array_addrs[$i] = trim($array_addrs[$i]);
					preg_match('/<([^>]+)>/', $array_addrs[$i], $match);
					if(count($match) == 2){
					$ex_arr = explode('@', $match[1]);
					}else{
					 $ex_arr = explode('@', $array_addrs[$i]);
					 }
					if(count($ex_arr) == 2){
						if($ex_arr[0] !== '' && $ex_arr[1] !== ''){
							if(preg_match("/[^0-9a-zA-Z._-]+/", $ex_arr[0]) == 0 && preg_match("/[^0-9a-zA-Z._-]+/", $ex_arr[1]) == 0){
								$array_addrs_final[$j] = $array_addrs[$i];
							}else{
								return array("False" => "$alias");
							 }
						}else{
							return array("False" => "$alias");
						 }
					}else{
						return array("False" => "$alias");
					 }
				}
//-- fim --
			}else{
				$array_addrs_final[$j] = $array_addrs[$i]; 
			}
		}
		return $array_addrs_final;
	}

	function getUserByEmail($params){	
		// Follow the referral
		$email = $params['email'];
		$query = 'select A.names_ordered, C.connection_name, C.connection_value, A.photo'. 
				' from phpgw_cc_contact A, phpgw_cc_contact_conns B, '.
				'phpgw_cc_connections C where A.id_contact = B.id_contact'. 
 				' and B.id_connection = C.id_connection and A.id_contact ='. 
				'(select A.id_contact from phpgw_cc_contact A, phpgw_cc_contact_conns B,'. 
				'phpgw_cc_connections C where A.id_contact = B.id_contact'. 
				' and B.id_connection = C.id_connection and A.id_owner = '.$this -> user_id.
				' and C.connection_value = \''.$email.'\') and '.
				'C.connection_is_default = true and B.id_typeof_contact_connection = 2';

        if (!$this->db->query($query))
        	return null;


		if($this->db->next_record()) {
			$result = $this->db->row();

			$obj =  array("cn" => $result['names_ordered'],
					  "email" => $email,
					  "type" => "personal",
					  "telefone" =>  $result['connection_value']);

			if($result['photo'])
				$_SESSION['phpgw_info']['expressomail']['contact_photo'] =  array($result['photo']);				

			return $obj;
		}
		return $result;
	}
	
	function update_preferences($params){
		$string_serial = urldecode($params['prefe_string']);				
		$string_serial = get_magic_quotes_gpc() ? $string_serial : addslashes($string_serial);
		$query = "update phpgw_preferences set preference_value = '".$string_serial."' where preference_app = 'expressoMail'".
			" and preference_owner = '".$this->user_id."'";

		if (!$this->db->query($query))
			return $this->db->error;
		else
			return array("success" => true);
	}
	
	function import_vcard($params){
            include_once('class.imap_functions.inc.php');
            $objImap = new imap_functions();
            $msg_number = $params['msg_number'];
            $idx_file = $params['idx_file'];
            $msg_part = $params['msg_part'];
            $msg_folder = $params['msg_folder'];
            $from_ajax = $params['from_ajax'];
            $encoding = strtolower($params['encoding']);
            $fileContent = "";
            $cirus_delimiter = $params['cirus_delimiter'];
            $expFolder = explode($cirus_delimiter, $msg_folder);

            if($msg_number != null && $msg_part != null && $msg_folder != null && (intval($idx_file == '0' ? '1' : $idx_file)))
            {
                require_once PHPGW_INCLUDE_ROOT.'/expressoMail/inc/class.attachment.inc.php';
                $attachmentObj = new attachment();
                $attachmentObj->setStructureFromMail($msg_folder,$msg_number);
                $fileContent = $attachmentObj->getAttachment($msg_part);
                $info = $attachmentObj->getAttachmentInfo($msg_part);
                $filename = $info['name'];
            }
            else
                    $filename = $idx_file;
                    
            // It's necessary to access calendar method.
            $GLOBALS['phpgw_info']['flags']['noappheader'] = True;
            $GLOBALS['phpgw_info']['flags']['noappfooter'] = True;
            $GLOBALS['phpgw_info']['flags']['currentapp'] = 'calendar';

			if(isset($params['selected']) || isset($params['readable'])){
				
				$_REQUEST['data'] = $fileContent;
				$_REQUEST['type'] = 'iCal';
				$_REQUEST['params']['calendar'] = isset($params['selected']) ? $params['selected'] : false;
				$_REQUEST['readable'] = (isset($params['readable']) && $params['readable']) ? true : false;
				$_REQUEST['analize'] = isset($params['analize']) ? true : false;
				$_REQUEST['params']['status'] = isset($params['status']) ? $params['status'] : false;
				$_REQUEST['params']['owner'] = $params['uidAccount'];
				if(isset($params['acceptedSuggestion'])){
					$_REQUEST['params']['acceptedSuggestion'] = $params['acceptedSuggestion'];
					$_REQUEST['params']['from'] = $params['from'];
				}
				
				ob_start();
				include_once(PHPGW_INCLUDE_ROOT.'/prototype/converter.php');
				$output = ob_get_clean();			
				$valid = json_decode($output, true);

				if($_REQUEST['readable']){	
					if(!is_array($valid))
					{
						$output = unserialize($output);	
						foreach($output as $key => $value)
							return $value;
					}
					return false;
				}				
				if(empty($output))
					return "error";	
				return "ok";
			}
			
	    include_once(PHPGW_INCLUDE_ROOT.'/header.inc.php');
            $uiicalendar = CreateObject("calendar.uiicalendar");	
            if(strtoupper($expFolder[0]) == 'USER' && $expFolder[1]) // IF se a conta o ical estiver em uma conta compartilhada
            {
                include_once('class.ldap_functions.inc.php');
                $ldap = new ldap_functions();
                $account['uid'] = $expFolder[1];
                $account['uidnumber']  = $ldap->uid2uidnumber($expFolder[1]);
                $account['mail']  = $ldap->getMailByUid($expFolder[1]);

                return $uiicalendar->import_from_mail($fileContent, $from_ajax,$account);
            }
            else
                return $uiicalendar->import_from_mail($fileContent, $from_ajax);

	}

    function insert_certificate($email,$certificate,$serialnumber,$authoritykeyidentifier=null)
	{
		if(!$email || !$certificate || !$serialnumber || !$authoritykeyidentifier)
			return false;
		// Insere uma chave publica na tabela phpgw_certificados.
		$data = array	('email' => $email,
						 'chave_publica' => $certificate,
						 'serialnumber' => $serialnumber,
						 'authoritykeyidentifier' => $authoritykeyidentifier);

		if(!$this->db->insert('phpgw_certificados',$data,array(),__LINE__,__FILE__)){
          	return $this->db->Error;
        }
    	return true;
	}

	function get_certificate($email=null)
	{
		if(!$email) return false;
		$result = array();

		$where = array ('email' => $email,
						'revogado' => 0,
						'expirado' => 0);

 		if(!$this->db->select('phpgw_certificados','chave_publica', $where, __LINE__,__FILE__))
        {
            $result['dberr1'] = $this->db->Error;
            return $result;
        }
		$regs = array();
		while($this->db->next_record())
        {
            $regs[] = $this->db->row();
        }
		if (count($regs) == 0)
        {
            $result['dberr2'] = ' Certificado nao localizado.';
            return $result;
        }
		$result['certs'] = $regs;
		return $result;
	}

	function update_certificate($serialnumber=null,$email=null,$authoritykeyidentifier,$expirado,$revogado)
	{
		if(!$email || !$serialnumber) return false;
		if(!$expirado)
			$expirado = 0;
		if(!$revogado)
			$revogado = 0;

		$data = array	('expirado' => $expirado,
						 'revogado' => $revogado);

		$where = array	('email' => $email,
						 'serialnumber' => $serialnumber,
						 'authoritykeyidentifier' => $authoritykeyidentifier);

		if(!$this->db->update('phpgw_certificados',$data,$where,__LINE__,__FILE__))
		{
			return $this->db->Error;
		}
		return true;
	}

	
	/**
     * @abstract Recupera o valor da regra padrão.
     * @return retorna o valor da regra padrão.
     */
	function get_default_max_size_rule()
	{
		$query = "SELECT config_value FROM phpgw_config WHERE config_name = 'expressoAdmin_default_max_size'";
		if(!$this->db->query($query))
            return false;

        $return = array();
			
		while($this->db->next_record())
            array_push($return, $this->db->row());
			
		return $return;
	}
	
	/**
     * @abstract Recupera a regra de um usuário.
     * @return retorna a regra que o usuário pertence. Caso o usuário não participe de nenhuma regra, retorna false.
     */
	function get_rule_by_user($id_user) 
	{
		$return = array();	
		$query = "SELECT email_max_recipient FROM phpgw_expressoadmin_configuration WHERE email_user='$id_user' AND configuration_type='MessageMaxSize'";
		
		if(!$this->db->query($query))
            return false;
		
		while($this->db->next_record())
            array_push($return, $this->db->row());
			
		return $return;
	}
	
	
	function get_rule_by_user_in_groups($id_group)
	{
		$return = array();
		$query = "SELECT email_max_recipient FROM phpgw_expressoadmin_configuration WHERE configuration_type='MessageMaxSize' AND email_user_type='G' AND email_user='".$id_group."'";

		if(!$this->db->query($query))	
			return false;	
			
		while($this->db->next_record())
			array_push($return, $this->db->row());

		return $return; 
	}
        function getMaximumRecipientsUser($pUserUID)
        {

           $query = 'SELECT email_max_recipient FROM phpgw_expressoadmin_configuration WHERE email_user = \''.$pUserUID.'\' AND configuration_type = \'LimitRecipient\' AND email_user_type = \'U\' ';
           $this->db->query($query);

           $return = array();

            while($this->db->next_record())
              $return =  $this->db->row();

            return $return['email_max_recipient'];
        }

        function getMaximumRecipientsGroup($pGroupsGuidnumbers)
        {
           $groupsGuidNumbers = '';

           foreach ($pGroupsGuidnumbers as $guidNumber => $cn)
             $groupsGuidNumbers .= $guidNumber.', ';

           $groupsGuidNumbers = substr($groupsGuidNumbers,0,-2);

           $query = 'SELECT email_max_recipient FROM phpgw_expressoadmin_configuration WHERE email_user IN ('.$groupsGuidNumbers.') AND configuration_type = \'LimitRecipient\' AND email_user_type = \'G\' ';
           $this->db->query($query);

           $return = array();

            while($this->db->next_record())
              $return[] =  $this->db->row();

            $maxSenderReturn = 0;

            foreach ($return as $maxSender)
            {
                if($maxSender['email_max_recipient'] > $maxSenderReturn)
                    $maxSenderReturn = $maxSender['email_max_recipient'];
            }

            return $maxSenderReturn;
        }

	function validadeSharedAccounts($user,$grups,$accountsMails)
        {

             $arrayMailsBlocked = array();

             $query = 'SELECT * FROM phpgw_expressoadmin_configuration WHERE email_user = \''.$user.'\' AND email_recipient = \'*\'  AND configuration_type = \'InstitutionalAccountException\' AND email_user_type = \'U\' ';
             $this->db->query($query);
             $this->db->next_record();
                 if($this->db->row())
                   return $arrayMailsBlocked;

            foreach ($grups as $guidNumber => $cn)
            {
                 $query = 'SELECT * FROM phpgw_expressoadmin_configuration WHERE email_user = \''.$guidNumber.'\' AND email_recipient = \'*\'  AND configuration_type = \'InstitutionalAccountException\' AND email_user_type = \'G\' ';
                 $this->db->query($query);
                 $this->db->next_record();
                 if($this->db->row())
                     return $arrayMailsBlocked;

            }

            foreach ($accountsMails as $mail)
            {
               
               $blocked = true;

               $query = 'SELECT * FROM phpgw_expressoadmin_configuration WHERE email_recipient = \''.$mail.'\' AND configuration_type = \'InstitutionalAccountException\' ';
               $this->db->query($query);
           
                while($this->db->next_record())
                {
                    $row =  $this->db->row();
                    
                    if(($row['email_user'] == '*' ||  $row['email_user'] == $user) && ($row['email_user_type'] == 'T' || $row['email_user_type'] == 'U'))
                        $blocked = false;
                    else if(array_key_exists($row['email_user'], $grups) && $row['email_user_type'] == 'G')
                         $blocked = false; 

                }

                if($blocked == true)
                    array_push ($arrayMailsBlocked, $mail);
            }

            return $arrayMailsBlocked;
        }

		function write_log($action, $about)
		{
			$sql = "INSERT INTO phpgw_expressoadmin_log (date, manager, action, userinfo) "
				. "VALUES('now','" . $_SESSION['phpgw_info']['expressomail']['user']['account_lid'] . "','" . strtolower($action) . "','" . strtolower($about) . "')";
			if (!$this->db->query($sql)) {
        		return false;
        	}
        return true;
       }
}
?>
