<?php
define('PHPGW_INCLUDE_ROOT','../');
define('PHPGW_API_INC','../phpgwapi/inc');	
include_once(PHPGW_API_INC.'/class.common.inc.php');
include_once('class.functions.inc.php');

function ldapRebind($ldap_connection, $ldap_url)
{
	// Enquanto estivermos utilizando referral na arvore ldap, teremos que continuar a utilizar o usuário sistemas:expresso.
	// Depois, quando não existir mais referral, não existirá a necessidade de ldapRebind.
	//ldap_bind($ldap_connection, $GLOBALS['phpgw_info']['server']['ldap_master_root_dn'], $GLOBALS['phpgw_info']['server']['ldap_master_root_pw']);
	if ( ($_SESSION['phpgw_info']['expresso']['cc_ldap_server']['acc'] != '') && ($_SESSION['phpgw_info']['expresso']['cc_ldap_server']['pw'] != '') )
	{
		@ldap_bind($ldap_connection, $_SESSION['phpgw_info']['expresso']['cc_ldap_server']['acc'], $_SESSION['phpgw_info']['expresso']['cc_ldap_server']['pw']);
	}
}

class ldap_functions
{
	var $ldap;
	var $current_config;
	var $functions;
	var $manager_contexts;
	
	function ldap_functions(){		
		$GLOBALS['phpgw_info']['server'] = $_SESSION['phpgw_info']['expresso']['server'];
		$this->current_config = $_SESSION['phpgw_info']['expresso']['expressoAdmin'];
		$common = new common();
		
		if ( (!empty($GLOBALS['phpgw_info']['server']['ldap_master_host'])) &&
			 (!empty($GLOBALS['phpgw_info']['server']['ldap_master_root_dn'])) &&
			 (!empty($GLOBALS['phpgw_info']['server']['ldap_master_root_pw'])) )
		{
			$this->ldap = $common->ldapConnect($GLOBALS['phpgw_info']['server']['ldap_master_host'],
											   $GLOBALS['phpgw_info']['server']['ldap_master_root_dn'],
											   $GLOBALS['phpgw_info']['server']['ldap_master_root_pw']);
		}
		else
		{
			$this->ldap = $common->ldapConnect();
		}
		
		$this->functions = new functions;
		$manager_acl = $this->functions->read_acl($_SESSION['phpgw_info']['expresso']['user']['account_lid']);
		$this->manager_contexts = $manager_acl['contexts'];
	}

	//Busca usuários de um contexto e já retorna as options do select;
	function get_available_users($params)
	{
		$context = $params['context'];
		$recursive = $params['recursive'];
		$justthese = array("cn", "uidNumber");
		$filter="(phpgwAccountType=u)";
		
		if ($recursive == 'true')
			$groups_list=ldap_search($this->ldap, $context, $filter, $justthese);
		else
    		$groups_list=ldap_list($this->ldap, $context, $filter, $justthese);
    	
    	$entries = ldap_get_entries($this->ldap, $groups_list);
    	
		for ($i=0; $i<$entries["count"]; ++$i){
			$u_tmp[$entries[$i]["uidnumber"][0]] = $entries[$i]["cn"][0];
		}
			
		if (count($u_tmp))
			natcasesort($u_tmp);

		$i = 0;
		$users = array();
			
		if (count($u_tmp))
		{
			foreach ($u_tmp as $uidnumber => $cn)
			{
				$options .= "<option value=$uidnumber>$cn</option>";
			}
			unset($u_tmp);
		}

    	return $options;
	}

	function get_available_groups($params)
	{
		$context = $params['context'];
		$justthese = array("cn", "gidNumber");
    	$groups_list=ldap_list($this->ldap, $context, ("(phpgwAccountType=g)"), $justthese);
    	ldap_sort($this->ldap, $groups_list, "cn");
    	
    	$entries = ldap_get_entries($this->ldap, $groups_list);
    	    	
		$options = '';
		for ($i=0; $i<$entries['count']; ++$i)
		{
			$options .= "<option value=" . $entries[$i]['gidnumber'][0] . ">" . $entries[$i]['cn'][0] . "</option>";
		}
    	
    	return $options;		
	}
	
	function get_available_maillists($params)
	{
		if ( !$ldapMasterConnect = $this->ldapMasterConnect() )
			return false;
		
		$context = $params['context'];
		$justthese = array("uid","mail","uidNumber");
    	$maillists=ldap_list($ldapMasterConnect, $context, ("(phpgwAccountType=l)"), $justthese);
    	ldap_sort($ldapMasterConnect, $maillists, "uid");
    	
    	$entries = ldap_get_entries($ldapMasterConnect, $maillists);
    	
		$options = '';			
		for ($i=0; $i<$entries['count']; ++$i)
		{
			$options .= "<option value=" . $entries[$i]['uid'][0] . ">" . $entries[$i]['uid'][0] . " (" . $entries[$i]['mail'][0] . ")" . "</option>";
		}
    	
    	ldap_close($ldapMasterConnect);
    	return $options;
	}
	
	function get_user_info($uidnumber)
	{
		foreach ($this->manager_contexts as $index=>$context)
		{
			$filter="(&(phpgwAccountType=u)(uidNumber=".$uidnumber."))";
			$search = ldap_search($this->ldap, $context, $filter);
			$entry = ldap_get_entries($this->ldap, $search);
			
			if ($entry['count'])
			{
				//Pega o dn do setor do usuario.
				$entry[0]['dn'] = strtolower($entry[0]['dn']);
				$sector_dn_array = explode(",", $entry[0]['dn']);
                $sector_dn_array_count = count($sector_dn_array);
				for($i=1; $i<$sector_dn_array_count; ++$i)
					$sector_dn .= $sector_dn_array[$i] . ',';
				//Retira ultimo pipe.
				$sector_dn = substr($sector_dn,0,(strlen($sector_dn) - 1));
		
				$result['context']				= $sector_dn;
				$result['uid']					= $entry[0]['uid'][0];
				$result['uidnumber']			= $entry[0]['uidnumber'][0];
				$result['gidnumber']			= $entry[0]['gidnumber'][0];
				$result['departmentnumber']		= $entry[0]['departmentnumber'][0];
				$result['givenname']			= $entry[0]['givenname'][0];
				$result['sn']					= $entry[0]['sn'][0];
				$result['telephonenumber']		= $entry[0]['telephonenumber'][0];
				$result['passwd_expired']		= $entry[0]['phpgwlastpasswdchange'][0];
				$result['phpgwaccountstatus']	= $entry[0]['phpgwaccountstatus'][0];
				$result['phpgwaccountvisible']	= $entry[0]['phpgwaccountvisible'][0];
				$result['accountstatus']		= $entry[0]['accountstatus'][0];
				$result['mail']					= $entry[0]['mail'][0];
				$result['mailalternateaddress']	= $entry[0]['mailalternateaddress'];
				$result['mailforwardingaddress']= $entry[0]['mailforwardingaddress'];
				$result['deliverymode']			= $entry[0]['deliverymode'][0];
				$result['userPasswordRFC2617']	= $entry[0]['userpasswordrfc2617'][0];

				//Photo
				if ($entry[0]['jpegphoto']['count'] == 1)
					$result['photo_exist'] = 'true';
		
				// Samba
				for ($i=0; $i<$entry[0]['objectclass']['count']; ++$i)
				{
					if ($entry[0]['objectclass'][$i] == 'sambaSamAccount')
						$result['sambaUser'] = true;
				}
				if (($this->current_config['expressoAdmin_samba_support'] == 'true') && ($result['sambaUser']))
				{
					$result['sambaaccflags'] = $entry[0]['sambaacctflags'][0];
					$result['sambalogonscript'] = $entry[0]['sambalogonscript'][0];
					$result['homedirectory'] = $entry[0]['homedirectory'][0];
					$a_tmp = explode("-", $entry[0]['sambasid'][0]);
					array_pop($a_tmp);
					$result['sambasid'] = implode("-", $a_tmp);
				}

				// Verifica o acesso do gerente aos atributos corporativos
				if ($this->functions->check_acl($_SESSION['phpgw_session']['session_lid'], 'manipulate_corporative_information'))
				{
					$result['corporative_information_employeenumber']= $entry[0]['employeenumber'][0];
					$result['corporative_information_cpf']			= $entry[0]['cpf'][0];
					$result['corporative_information_rg']			= $entry[0]['rg'][0];
					$result['corporative_information_rguf']			= $entry[0]['rguf'][0];
					$result['corporative_information_description']	= utf8_decode($entry[0]['description'][0]);
				}
				
				// MailLists
				$result['maillists_info'] = $this->get_user_maillists($result['mail']);
				if($result['maillists_info'])
				{
					foreach ($result['maillists_info'] as $maillist)
					{
						$result['maillists'][] = $maillist['uid'];
					}
				}
				
				// Groups
				$justthese = array("gidnumber","cn");
				$filter="(&(phpgwAccountType=g)(memberuid=".$result['uid']."))";
				$search = ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
    			ldap_sort($this->ldap, $search, "cn");
    			$entries = ldap_get_entries($this->ldap, $search);
    			for ($i=0; $i<$entries['count']; ++$i)
	    		{
    				$result['groups_ldap'][ $entries[$i]['gidnumber'][0] ] = $entries[$i]['cn'][0];
    			}
			}
		}
		if (is_array($result))
			return $result;
		else
			return false;
	}
		
	function get_user_maillists($mail)
	{
		if ( !$ldapMasterConnect = $this->ldapMasterConnect() )
			return false;
		
		$result = array();
		
		//Mostra somente os mailists dos contextos do gerente
		$justthese = array("uid","mail","uidnumber");
		$filter="(&(phpgwAccountType=l)(mailforwardingaddress=$mail))";
		
		foreach ($this->manager_contexts as $index=>$context)
		{
			$search = ldap_search($ldapMasterConnect, $context, $filter, $justthese);
    		$entries = ldap_get_entries($ldapMasterConnect, $search);
    		
	    	for ($i=0; $i<$entries['count']; ++$i)
    		{
				$result[ $entries[$i]['uid'][0] ]['uid']		= $entries[$i]['uid'][0];
				$result[ $entries[$i]['uid'][0] ]['mail']		= $entries[$i]['mail'][0];
				
				$a_tmp[] = $entries[$i]['uid'][0];
    		}
		}
    	
    	if($a_tmp) {
    		natcasesort($a_tmp);
    	
    		foreach ($a_tmp as $uid)
    		{
				$return[$uid]['uid']		= $result[$uid]['uid'];
				$return[$uid]['mail']		= $result[$uid]['mail'];
    		}
    	}
    	ldap_close($ldapMasterConnect);
		return $return;
	}
	
	function get_group_info($gidnumber)
	{
		foreach ($this->manager_contexts as $index=>$context)
		{
			$filter="(&(phpgwAccountType=g)(gidNumber=".$gidnumber."))";
			$search = ldap_search($this->ldap, $context, $filter);
			$entry = ldap_get_entries($this->ldap, $search);
			
			if ($entry['count'])
			{
				//Pega o dn do setor do grupo.
				$entry[0]['dn'] = strtolower($entry[0]['dn']);
				$sector_dn_array = explode(",", $entry[0]['dn']);
                $sector_dn_array_count = count($sector_dn_array);
				for($i=1; $i<$sector_dn_array_count; ++$i)
					$sector_dn .= $sector_dn_array[$i] . ',';
				//Retira ultimo pipe.
				$sector_dn = substr($sector_dn,0,(strlen($sector_dn) - 1));
		
				$result['context']				= $sector_dn;
				$result['cn']					= $entry[0]['cn'][0];
				$result['description']			= $entry[0]['description'][0];
				$result['gidnumber']			= $entry[0]['gidnumber'][0];
				$result['phpgwaccountvisible']	= $entry[0]['phpgwaccountvisible'][0];
				$result['email']				= $entry[0]['mail'][0];
		
				//MemberUid
				for ($i=0; $i<$entry[0]['memberuid']['count']; ++$i)
				{
					$justthese = array("cn","uid","uidnumber");
			
					// Montagem dinamica do filtro
					$filter="(&(phpgwAccountType=u)(|";
					for ($k=0; (($k<10) && ($i<$entry[0]['memberuid']['count'])); ++$k)
					{
						$filter .= "(uid=".$entry[0]['memberuid'][$i].")";
						++$i;
					}
					$i--;
					$filter .= "))";
			
					$search = ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
					$user_entry = ldap_get_entries($this->ldap, $search);

					for ($j=0; $j<$user_entry['count']; ++$j)
					{
						$result['memberuid_info'][$user_entry[$j]['uid'][0]]['cn'] = $user_entry[$j]['cn'][0];
						$result['memberuid_info'][$user_entry[$j]['uid'][0]]['uidnumber'] = $user_entry[$j]['uidnumber'][0];
						$result['memberuid_info'][$user_entry[$j]['uid'][0]]['type'] = 'u';
					}
				}
		
				// Checamos e-mails que não fazem parte do expresso.
				// Criamos um array temporario
				$tmp_array = array();
				if($result['memberuid_info'])
					foreach ($result['memberuid_info'] as $uid => $user_data)
					{
						$tmp_array[] = $uid;
					}
		
				if($entry[0]['memberuid']) {
					// Retira o count do array
					array_shift($entry[0]['memberuid']);
					// Vemos a diferença
					$array_diff = array_diff($entry[0]['memberuid'], $tmp_array);
					// Incluimos no resultado			
					foreach ($array_diff as $index=>$uid)
					{
						$result['memberuid_info'][$uid]['cn'] = $uid;
					}
				}
		
				// Samba
				for ($i=0; $i<$entry[0]['objectclass']['count']; ++$i)
				{
					if ($entry[0]['objectclass'][$i] == 'sambaGroupMapping')
						$result['sambaGroup'] = true;

					$a_tmp = explode("-", $entry[0]['sambasid'][0]);
					array_pop($a_tmp);
					$result['sambasid'] = implode("-", $a_tmp);
				}
				return $result;
			}
		}
	}	
	
	function get_maillist_info($uidnumber)
	{
		/* folling referral connection */
		$ldap_conn_following_ref = ldap_connect($_SESSION['phpgw_info']['expresso']['cc_ldap_server']['host']);
		if ($ldap_conn_following_ref)
		{
			ldap_set_option($ldap_conn_following_ref, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($ldap_conn_following_ref, LDAP_OPT_REFERRALS, 1);

			if ( ($_SESSION['phpgw_info']['expresso']['cc_ldap_server']['acc'] != '') && ($_SESSION['phpgw_info']['expresso']['cc_ldap_server']['pw'] != '') )
				ldap_bind($ldap_conn_following_ref, $_SESSION['phpgw_info']['expresso']['cc_ldap_server']['acc'], $_SESSION['phpgw_info']['expresso']['cc_ldap_server']['pw']);
		}
		
		foreach ($this->manager_contexts as $index=>$context)
		{
			$filter="(&(phpgwAccountType=l)(uidNumber=".$uidnumber."))";
			$search = ldap_search($this->ldap, $context, $filter);
			$entry = ldap_get_entries($this->ldap, $search);
			
			if ($entry['count'])
			{
				//Pega o dn do setor do usuario.
				$entry[0]['dn'] = strtolower($entry[0]['dn']);
				$sector_dn_array = explode(",", $entry[0]['dn']);
                $sector_dn_array_count = count($sector_dn_array);
				for($i=1; $i<$sector_dn_array_count; ++$i)
					$sector_dn .= $sector_dn_array[$i] . ',';
				//Retira ultimo pipe.
				$sector_dn = substr($sector_dn,0,(strlen($sector_dn) - 1));
			
				$result['context']				= $sector_dn;
				$result['uidnumber']			= $entry[0]['uidnumber'][0];
				$result['uid']					= strtolower($entry[0]['uid'][0]);
				$result['cn']					= $entry[0]['cn'][0];
				$result['mail']					= $entry[0]['mail'][0];
				$result['description']			= utf8_decode($entry[0]['description'][0]);
				$result['accountStatus']		= $entry[0]['accountstatus'][0];
				$result['phpgwAccountVisible']	= $entry[0]['phpgwaccountvisible'][0];
			
				//Members
				for ($i=0; $i<$entry[0]['mailforwardingaddress']['count']; ++$i)
				{
					$justthese = array("cn", "uidnumber", "uid", "phpgwaccounttype", "mail");
				
					// Montagem dinamica do filtro, para nao ter muitas conexoes com o ldap
					$filter="(&(|(phpgwAccountType=u)(phpgwAccountType=l))(|";
					for ($k=0; (($k<10) && ($i<$entry[0]['mailforwardingaddress']['count'])); ++$k)
					{
						$filter .= "(mail=".$entry[0]['mailforwardingaddress'][$i].")";
						++$i;
					}
					$i--;
					$filter .= "))";
				
					$search = ldap_search($ldap_conn_following_ref, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
					$user_entry = ldap_get_entries($ldap_conn_following_ref, $search);
									
					for ($j=0; $j<$user_entry['count']; ++$j)
					{
						$result['mailForwardingAddress_info'][$user_entry[$j]['mail'][0]]['uid'] = $user_entry[$j]['uid'][0];
						$result['mailForwardingAddress_info'][$user_entry[$j]['mail'][0]]['cn'] = $user_entry[$j]['cn'][0];
						$result['mailForwardingAddress_info'][$user_entry[$j]['mail'][0]]['type'] = $user_entry[$j]['phpgwaccounttype'][0];
						$result['mailForwardingAddress'][] = $user_entry[$j]['mail'][0];
					}
				}

				// Emails não encontrados no ldap
				array_shift($entry[0]['mailforwardingaddress']); //Retira o count do array
				$missing_emails = array_diff($entry[0]['mailforwardingaddress'], $result['mailForwardingAddress']);
				
				// Incluimos estes no resultado
				foreach ($missing_emails as $index=>$mailforwardingaddress)
				{
					$result['mailForwardingAddress_info'][$mailforwardingaddress]['uid'] = $mailforwardingaddress;
					$result['mailForwardingAddress_info'][$mailforwardingaddress]['cn'] = 'E-Mail nao encontrado';
					$result['mailForwardingAddress'][] = $mailforwardingaddress;
				}
				
				ldap_close($ldap_conn_following_ref);
				return $result;
			}
		}
	}	

		

	function group_exist($gidnumber)
	{
		$justthese = array("cn");
		$filter="(&(phpgwAccountType=g)(gidNumber=".$gidnumber."))";
		$search = ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
				
		$entry = ldap_get_entries($this->ldap, $search);
		if ($entry['count'] == 0)
			return false;
		else
			return true;
	}

	function gidnumbers2cn($gidnumbers)
	{
		$result = array();
		if (count($gidnumbers))
		{
			$justthese = array("cn");
			$i = 0;
			foreach ($gidnumbers as $gidnumber)
			{
				$filter="(&(phpgwAccountType=g)(gidNumber=".$gidnumber."))";
				$search = ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
				
				$entry = ldap_get_entries($this->ldap, $search);
				if ($entry['count'] == 0)
					$result['groups_info'][$i]['cn'] = '_' . $this->functions->lang('group only exist on DB, but does not exist on ldap');
					
				else
					$result['groups_info'][$i]['cn'] = $entry[0]['cn'][0];
				$result['groups_info'][$i]['gidnumber'] = $gidnumber;
			
				/* o gerente pode excluir um grupo de um usuario onde este grupo esta em outra OU ? */
				/* é o mesmo que o manager editar um grupo de outra OU */
				$result['groups_info'][$i]['group_disabled'] = 'true';
				foreach ($this->manager_contexts as $index=>$context)
				{
					if (strpos(strtolower($entry[0]['dn']), strtolower($context)))
					{
						$result['groups_info'][$i]['group_disabled'] = 'false';
					}
				}

				++$i;
			}
		}
		return $result;
	}

	function uidnumber2uid($uidnumber)
	{
		$justthese = array("uid");
		$filter="(&(|(phpgwAccountType=u)(phpgwAccountType=l))(uidNumber=".$uidnumber."))";
		$search = ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
		$entry = ldap_get_entries($this->ldap, $search);
		return $entry[0]['uid'][0];
	}

	function uidnumber2mail($uidnumber)
	{
		$justthese = array("mail");
		$filter="(&(|(phpgwAccountType=u)(phpgwAccountType=l))(uidNumber=".$uidnumber."))";
		$search = ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
		$entry = ldap_get_entries($this->ldap, $search);
		return $entry[0]['mail'][0];
	}
	
	function search_user($params)
	{
		$search = $params['search'];
		$justthese = array("cn","uid", "mail");
    	$users_list=ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_context'], "(&(phpgwAccountType=u) (|(cn=*$search*)(mail=$search*)) )", $justthese);
    	
    	if (ldap_count_entries($this->ldap, $users_list) == 0)
    	{
    		$return['status'] = 'false';
    		$result['msg'] = $this->functions->lang('Any result was found') . '.';
    		return $return;
    	}
    	
    	ldap_sort($this->ldap, $users_list, "cn");
    	
    	$entries = ldap_get_entries($this->ldap, $users_list);
    	    	
		$options = '';
		for ($i=0; $i<$entries['count']; ++$i)
		{
			$options .= "<option value=" . $entries[$i]['uid'][0] . ">" . $entries[$i]['cn'][0] . " (".$entries[$i]['mail'][0].")" . "</option>";
		}
    	
    	return $options;		
	}
}
?>
