<?php
	/***********************************************************************************\
	* Expresso Administraï¿½ï¿½o															*
	* by Valmir André de Sena (valmirse@gmail.com, valmir.sena@ati.pe.gov.br
	* ----------------------------------------------------------------------------------*
	*  This program is free software; you can redistribute it and/or modify it			*
	*  under the terms of the GNU General Public License as published by the			*
	*  Free Software Foundation; either version 2 of the License, or (at your			*
	*  option) any later version.														*
	\***********************************************************************************/

include_once('class.functions.inc.php');
include_once('class.ldap_functions.inc.php');
include_once('class.imap_functions.inc.php');
include_once('class.db_functions.inc.php');

	class shared_accounts
	{
		var $functions;
		var $ldap_functions;
		var $imap_functions;
                var $db_functions;

		function shared_accounts()
		{			
			
			$this->ldap_functions = new ldap_functions;			
			$this->imap_functions = new imap_functions;
			$this->functions = new functions;
                        $this->db_functions = new db_functions();
			
		}
		function create($params)
		{
			$params['uid'] =  $this->get_shared_mail2uid($params);
                        $return = $this->ldap_functions->create_shared_accounts($params);
						
			if( $return['status'] ){
				//Create mailbox				
				$mailquota = 10;
				$return = $this->imap_functions->create($params['uid'], $params['mailquota']);
                                $owners_acl_new = unserialize($params['owners_acl']); 
                                //add new users e set permissions
                                foreach($owners_acl_new as $user => $acl){
                                   $result &= $this->imap_functions->setaclfrombox($user,$acl,$params['uid']);
                                }
                                $owners_calendar_acl_new = unserialize($params['owners_calendar_acl']);
                                $owner = $this->ldap_functions->uid2uidnumber($params['uid']);
                                foreach($owners_calendar_acl_new as $user => $acl){
                                   $result &= $this->db_functions->save_calendar_acls($this->ldap_functions->uid2uidnumber($user),$acl,$owner);
                                }
                $this->db_functions->write_log('Create Shared account',$params['uid']);
			}


			return $return;
		}
	
		function save($params)
		{
			
                        $params['uid'] = $this->get_shared_mail2uid($params);
                        $params['old_uid'] = $this->get_shared_dn2uid($params['anchor']);
                        $result = $this->ldap_functions->save_shared_accounts($params);

            if( $result['status']){
                            $result = $this->imap_functions->save_shared_account($params);
                            $owners_calendar_acl_new = unserialize($params['owners_calendar_acl']);

                            $owner = $params['uidnumber'];
                            foreach($owners_calendar_acl_new as $user => $acl){
                              $this->db_functions->save_calendar_acls($this->ldap_functions->uid2uidnumber($user),$acl,$owner);
                            }

                            $this->db_functions->write_log('Update Shared account','Old UID:'.$params['old_uid'].' New UID '.$params['uid']);
                        }

                      return  $result;
		}
		
		function get($params)
		{
                        if (!$this->functions->check_acl($_SESSION['phpgw_info']['expresso']['user']['account_lid'], 'list_institutional_accounts'))
                        {
                                $return['status'] = false;
                                $return['msg'] = $this->functions->lang('You do not have right to list institutional accounts') . ".";
                                return $return;
                        }

                        $input = $params['input'];
                        $justthese = array("cn", "mail", "uid");
                        $trs = array();

                        foreach ($this->manager_contexts as $idx=>$context)
                        {
                                $shared_accounts = ldap_search($this->ldap, $context, ("(&(phpgwAccountType=s)(|(mail=$input*)(cn=*$input*)))"), $justthese);
                                $entries = ldap_get_entries($this->ldap, $shared_accounts);

                                for ($i=0; $i<$entries['count']; ++$i)
                                {
                                        $tr = "<tr class='normal' onMouseOver=this.className='selected' onMouseOut=this.className='normal'><td onClick=edit_shared_account('".$entries[$i]['uid'][0]."')>" . $entries[$i]['cn'][0] . "</td><td onClick=edit_shared_account('".$entries[$i]['uid'][0]."')>" . $entries[$i]['mail'][0] . "</td><td align='center' onClick=delete_shared_accounts('".$entries[$i]['uid'][0]."')><img HEIGHT='16' WIDTH='16' src=./expressoAdmin/templates/default/images/delete.png></td></tr>";
                                        $trs[$tr] = $entries[$i]['cn'][0];
                                }
                        }
    	
                        $trs_string = '';
                        if (count($trs))
                        {
                                natcasesort($trs);
                                foreach ($trs as $tr=>$cn)
                                {
                                        $trs_string .= $tr;
                                }
                        }

                        $return['status'] = 'true';
                        $return['trs'] = $trs_string;
                        return $return;
                }
	
                function get_data($params)
                {
                        $return = $this->ldap_functions->get_shared_account_data($params);
                        $owners_acl = $this->imap_functions->getaclfrombox($params);
			            $uid = $params['uid'];
                        $quota = $this->imap_functions->get_user_info($params['uid']);
                        $owner = $this->ldap_functions->uid2uidnumber($params['uid']);
                        $calendarAcls = $this->db_functions->get_calendar_acls($owner);

                        $return['uidnumber'] = $owner;
                        $return['mailquota'] = $quota['mailquota'];
                        $return['display_empty_inbox'] = $this->functions->check_acl($_SESSION['phpgw_session']['session_lid'],'empty_shared_accounts_inbox') ? 'block' : 'none';
                        $return['allow_edit_shared_account_acl'] = $this->functions->check_acl($_SESSION['phpgw_session']['session_lid'],'edit_shared_accounts_acl');
                        $return['mailquota_used'] = $quota['mailquota_used'];                        
                        $i = 0;
                        if( is_array($owners_acl) ){
	                        foreach($owners_acl as $key => $value)
	                        {
	                                $cn = $this->ldap_functions->uid2cn($key);
                                        $uidnumber = $this->ldap_functions->uid2uidnumber($key);
					
					if( $uid )
					    $cn .= '(' . $key . ')';
                            if (!isset($return['owners_options'])) $return['owners_options'] = '';
                            if (!isset($return['owners'][$i])) $return['owners'][$i] = '';
                            if (!isset($return['owners_acl'][$i])) $return['owners_acl'][$i] = '';
                            if (!isset($return['owners_calendar_acl'][$i])) $return['owners_calendar_acl'][$i] = '';
                            $return['owners_options'] .= '<option value='. $key .'>' . $cn . '</option>';
	                                $return['owners'][$i] .= $key;
	                                $return['owners_acl'][$i] .= $value;
                                        $return['owners_calendar_acl'][$i] .= isset($calendarAcls[$key]) ? $calendarAcls[$key] : '';
	                                ++$i;
	                        }
                        } else {
                                $return['owners_options'] = false;
                                $return['owners'] = false;
                                $return['owners_acl'] = false;
                                $return['owners_calendar_acl'] == false;
                        }                        


                        return $return;
                }

                
                function delete($params){
                    $result = $this->ldap_functions->delete_shared_account_data($params);
                    if( $result['status'] )
                    {
                        $result = $this->imap_functions->delete_mailbox($params['uid']);
                        $this->db_functions->write_log('Removed Shared account',$params['uid']);
                    }
                    return $result;
                }
                //Get the shared uid from mail
                function get_shared_mail2uid($params){
                        list($uid) = explode("@",$params['mail']);
                        return $uid;
                }
                function get_shared_dn2uid($dn){
                        $uid = "";
                        list($uid) = explode(",", str_replace("uid=","", $dn));
                        return $uid;                	
                }
                function empty_inbox($params){
                    $params['uid'] = $this->get_shared_dn2uid($params['uid']);
                    return $this->imap_functions->empty_shared_account_inbox($params);
                }
	
}
?>
