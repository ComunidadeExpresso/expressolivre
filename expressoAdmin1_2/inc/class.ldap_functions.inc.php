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

if(!defined('PHPGW_INCLUDE_ROOT'))
    define('PHPGW_INCLUDE_ROOT', __DIR__ . '/../../');

if(!defined('PHPGW_API_INC'))
    define('PHPGW_API_INC', __DIR__ . '/../../phpgwapi/inc');

include_once(PHPGW_API_INC.'/class.common.inc.php');
include_once('class.functions.inc.php');
include_once('class.db_functions.inc.php');

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
        var $db_functions;
	
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
		
                $this->db_functions = new db_functions();
		$this->functions = new functions;
		$manager_acl = $this->functions->read_acl($_SESSION['phpgw_info']['expresso']['user']['account_lid']);
		$this->manager_contexts = $manager_acl['contexts'];
	}

	function noAccess( $context, $target, $label = false )
		{
	    if( !$label )
		$label = str_replace( '_', ' ', $target );
			
	    if (!$this->functions->check_acl( $_SESSION['phpgw_info']['expresso']['user']['account_lid'], $target ))
			{
				$return['status'] = false;
		$return['msg'] = $this->functions->lang("You do not have right to $label") . ".";
				return $return;
			}
			
			$access_granted = false;

			foreach ($this->manager_contexts as $idx=>$manager_context)
			{
		    if (stristr($context, $manager_context))
				{
					$access_granted = true;
					break;
				}
			}
			
			if (!$access_granted)
			{
				$return['status'] = false;				
				$return['msg'] = $this->functions->lang('You do not have access to this organization') . ".";							
				return $return;
			}			

	    return( false );
	}
	
	function create_institutional_accounts($params)
	{
		/* Begin: Access verification */
		$forbidden = $this->noAccess( $params['context'], 'add_institutional_accounts', 'create institutional accounts' );

		if( $forbidden )
		    return( $forbidden );
// 		if (!$this->functions->check_acl($_SESSION['phpgw_info']['expresso']['user']['account_lid'], 'add_institutional_accounts'))
// 		{
// 			$return['status'] = false;
// 			$return['msg'] = $this->functions->lang('You do not have right to create institutional accounts') . ".";
// 			return $return;
// 		}
// 		
// 		$access_granted = false;
// 		foreach ($this->manager_contexts as $idx=>$manager_context)
// 		{
// 			if (stristr($params['context'], $manager_context))
// 			{
// 				$access_granted = true;
// 				break;
// 			}
// 		}
// 		if (!$access_granted)
// 		{
// 			$return['status'] = false;
// 			$return['msg'] = $this->functions->lang('You do not have access to this organization') . ".";
// 			return $return;
// 		}
			/* End: Access verification */
	
		/* Begin: Validation */
		if ( (empty($params['cn'])) || (empty($params['mail'])) )
		{
			$result['status'] = false;
			$result['msg']  = $this->functions->lang('Field mail or name is empty');
			return $result;
		}

		if (! preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)+$/i', $params['mail']) )
		{
			$result['status'] = false;
			$result['msg']  = $this->functions->lang('Field mail is not formed correcty') . '.';
			return $result;
		}

		if (empty($params['desc']))
		{
			$result['status'] = false;
			$result['msg']  = $this->functions->lang('DESCRIPTION field is empty') . '.';
			return $result;
		}
		
		$uid = 'institutional_account_' . $params['mail'];
		$dn = "uid=$uid," . $params['context'];

		$filter = "(mail=".$params['mail'].")";
		$justthese = array("cn");
		$search = @ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
		$entries = @ldap_get_entries($this->ldap,$search);
		if ($entries['count'] != 0)
		{
			$result['status'] = false;
			$result['msg'] = $this->functions->lang('Field mail already in use');
			return $result;
		}
		/* End: Validation */
						
		$info = array();
		$info['cn']					= $params['cn'];
		$info['sn']					= $params['cn'];
		$info['uid']				= $uid;
		$info['mail']				= $params['mail'];
		$info['description']		= iconv("ISO-8859-1","UTF-8//TRANSLIT",$params['desc']);
		$info['phpgwAccountType']	= 'i';
		$info['objectClass'][]		= 'inetOrgPerson';
		$info['objectClass'][]		= 'phpgwAccount';
		$info['objectClass'][]		= 'top';
		$info['objectClass'][]		= 'person';
		$info['objectClass'][]		= 'qmailUser';
		$info['objectClass'][]		= 'organizationalPerson';
		
		if ($params['accountStatus'] == 'on')
		{
			$info['accountStatus'] = 'active';
		}
		if ($params['phpgwAccountVisible'] == 'on')
		{
			$info['phpgwAccountVisible'] = '-1';
		}
		
		if (!empty($params['owners']))
		{
			foreach($params['owners'] as $index=>$uidnumber)
			{
				$info['mailForwardingAddress'][] = $this->uidnumber2mail($uidnumber);
			}
		}		
		
		$result = array();
		if (!@ldap_add ( $this->ldap, $dn, $info ))
		{
			$result['status'] = false;
			$result['msg']  = $this->functions->lang('Error on function') . ' ldap_functions->create_institutional_accounts';
			$result['msg'] .= "\n" . $this->functions->lang('Server return') . ': ' . ldap_error($this->ldap);
		}
		else
			$result['status'] = true;
		
		return $result;
	}
	
	function save_institutional_accounts($params)
	{
		/* Begin: Access verification */
		$forbidden = $this->noAccess( $params['context'], 'edit_institutional_accounts' );

		if( $forbidden )
		    return( $forbidden );
// 		if (!$this->functions->check_acl($_SESSION['phpgw_info']['expresso']['user']['account_lid'], 'edit_institutional_accounts'))
// 		{
// 			$return['status'] = false;
// 			$return['msg'] = $this->functions->lang('You do not have right to edit institutional accounts') . ".";
// 			return $return;
// 		}
// 		$access_granted = false;
// 		foreach ($this->manager_contexts as $idx=>$manager_context)
// 		{
// 			if (stristr($params['context'], $manager_context))
// 			{
// 				$access_granted = true;
// 				break;
// 			}
// 		}
// 		if (!$access_granted)
// 		{
// 			$return['status'] = false;
// 			$return['msg'] = $this->functions->lang('You do not have access to this organization') . ".";
// 			return $return;
// 		}
		/* End: Access verification */
				
			/* Begin: Validation */
			if ( (empty($params['cn'])) || (empty($params['mail'])) )
			{
				$result['status'] = false;
			$result['msg']  = $this->functions->lang('Field mail or name is empty') . '.';
			return $result;
		}

		if (! preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)+$/i', $params['mail']) )
		{
			$result['status'] = false;
			$result['msg']  = $this->functions->lang('Field mail is not formed correcty') . '.';
			return $result;
		}

        $institutional_accounts = ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_context'], ('('.substr($params['anchor'], 0 , strpos($params['anchor'],','))).')');
        $old  = ldap_get_entries($this->ldap, $institutional_accounts);
        $oldOwners = array();


        foreach($old[0]['mailforwardingaddress'] as $mailP)
        {
            $tmp = $this->mailforwardingaddress2uidnumber($mailP);
            $oldOwners[$tmp['uidnumber']] = true;
        }

        if (!empty($params['owners']))
        {
            foreach($params['owners'] as $index => $uidnumber)
            {
                if(array_key_exists($uidnumber, $oldOwners))
                    unset( $oldOwners[$uidnumber] );
                else
                    $this->functions->write_log("User added from the institutional account",'USER: '.$uidnumber.' - SHARED ACCOUNT: '.$params['anchor']);
            }
        }

        if(count($oldOwners) > 0)
            foreach($oldOwners as $i=>$v )
            {
                $this->functions->write_log("User removed from the institutional account",'USER: '.$i.' - SHARED ACCOUNT: '.$params['anchor']);
            }



		$uid = 'institutional_account_' . $params['mail'];
		$dn = strtolower("uid=$uid," . $params['context']);
		$anchor = strtolower($params['anchor']);

		$filter = "(mail=".$params['mail'].")";
		$justthese = array("cn");
		$search = @ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
		$entries = @ldap_get_entries($this->ldap,$search);
		
		if ( ($entries['count'] > 1) || (($entries['count'] == 1) && ($entries[0]['dn'] != $anchor)) )
		{
			$result['status'] = false;
			$result['msg'] = $this->functions->lang('Field mail already in use.');
			return $result;
		}
		/* End: Validation */
		
		$result = array();
		$result['status'] = true;
		
		if ($anchor != $dn)
		{
			if (!@ldap_rename($this->ldap, $anchor, "uid=$uid", $params['context'], true))
			{
				$result['status'] = false;
				$result['msg']  = $this->functions->lang('Error on function') . ' ldap_functions->save_institutional_accounts: ldap_rename';
				$result['msg'] .= "\n" . $this->functions->lang('Server return') . ': ' . ldap_error($this->ldap);
			}
		}
		
		$info = array();
		$info['cn']					= $params['cn'];
		$info['sn']					= $params['cn'];
		$info['uid']				= $uid;
		$info['mail']				= $params['mail'];
		
		if ($params['accountStatus'] == 'on')
			$info['accountStatus'] = 'active';
		else
			$info['accountStatus'] = array();
		
		if ($params['phpgwAccountVisible'] == 'on')
			$info['phpgwAccountVisible'] = '-1';
		else
			$info['phpgwAccountVisible'] = array();
		
		if ($params['desc'] != '')
			$info['description'] = utf8_encode($params['desc']);
		else
			$info['description'] = array();
		
		if (!empty($params['owners']))
		{
			foreach($params['owners'] as $index => $uidnumber)
			{
				$mailForwardingAddress = $this->uidnumber2mail($uidnumber);
				if ($mailForwardingAddress != '')
					$info['mailForwardingAddress'][] = $mailForwardingAddress;
			}
		}
		else
			$info['mailForwardingAddress'] = array();
		
		if (!@ldap_modify ( $this->ldap, $dn, $info ))
		{
			$result['status'] = false;
			$result['msg']  = $this->functions->lang('Error on function') . ' ldap_functions->save_institutional_accounts: ldap_modify';
			$result['msg'] .= "\n" . $this->functions->lang('Server return') . ': ' . ldap_error($this->ldap);
		}


        $this->functions->write_log('Update institutional account','Old DN:'.$params['anchor'].' New DN '.$dn);

		return $result;
	}

	function save_shared_accounts($params)
	{
		/* Begin: Access verification */
		$forbidden = $this->noAccess( $params['context'], 'edit_shared_accounts' );

		if( $forbidden )
		    return $forbidden;
		
		/* Begin: Validation */
		if(!$params['desc'])
		{
		    $result['status'] = false;
		    $result['msg']  = $this->functions->lang('Field description is empty');	return $result;

		}

		if ( (empty($params['cn'])) || (empty($params['mail'])) )
		{
			$result['status'] = false;
			$result['msg']  = $this->functions->lang('Field mail or name is empty') . '.';
			return $result;
		}

		if (! preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)+$/i', $params['mail']) )
		{
			$result['status'] = false;
			$result['msg']  = $this->functions->lang('Field mail is not formed correcty') . '.';
			return $result;
		}			

		$dnReal = "uid=".$params['uid']."," . $params['context'];
		$dn = strtolower("uid=".$params['uid']."," . $params['context']);
		$anchor = strtolower($params['anchor']);

		
		$filter = "(mail=".$params['mail'].")";
		$justthese = array("cn","uidnumber");
		$search = @ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
		$entries = @ldap_get_entries($this->ldap,$search);

		//DEBUG: Alteracao para compatibilizar com LDAP da CAIXA.
		// estas funcoes do ExpressoAdmin nao levam em consideracao o DN do objeto encontrado
		// e o "codigo" assume que o DN comeca com UID quando na CAIXA comeca com CN.

		// Se for encontrado somente um objeto e este estiver diferente do ANCHOR, entao pega o DN 
		// do resultado da busca
		if ( ($entries['count'] == 1) && (strtolower(utf8_decode($entries[0]['dn'])) != $anchor) )
		{
			// Forca o DN
			$dn = strtolower (utf8_decode($entries[0]['dn']));
			$dnReal = $entries[0]['dn'];
			$anchor = $dn;
		}
		
		if ( ($entries['count'] > 1) || (($entries['count'] == 1) && (strtolower(utf8_decode($entries[0]['dn'])) != $anchor)) )
		{
			$result['status'] = false;
			$result['msg'] = $this->functions->lang('Field mail already in use.');
			return $result;
		}
		/* End: Validation */

		$result = array();
		$result['status'] = true;              

		if ($anchor != $dn)
		{
			if (!@ldap_rename($this->ldap, $params['anchor'], "uid={$params['uid']}", $params['context'], true))
			{
				$result['status'] = false;
				$result['msg']  = $this->functions->lang('Error on function') . ' ldap_functions->save_shared_accounts: ldap_rename';
				$result['msg'] .= "\n" . $this->functions->lang('Server return') . ': ' . ldap_error($this->ldap);
			}
		}
		
		$info = array();
		$info['cn']		= utf8_encode($params['cn']);
		$info['sn']		= utf8_encode($params['cn']);
		$info['uid']		= $params['uid'];
		$info['mail']		= $params['mail'];


		$del = array();

		if( isset($params['mailalternateaddress']) && $params['mailalternateaddress'] )
			$info['mailalternateaddress'] = $params['mailalternateaddress'];
		else
		    $del['mailalternateaddress'] = array();

		if ( isset($params['accountStatus']) && $params['accountStatus'] == 'on')
			$info['accountStatus'] = 'active';
		else
			$info['accountStatus'] = array();
		
		if (isset($params['phpgwAccountVisible']) && $params['phpgwAccountVisible'] == 'on')
			$info['phpgwAccountVisible'] = '-1';
		else
			$info['phpgwAccountVisible'] = array();
		
		if (isset($params['desc']) && $params['desc'] != '')
			$info['description'] = utf8_encode($params['desc']);
		else
			$info['description'] = array();

		ldap_set_option ($this->ldap, LDAP_OPT_PROTOCOL_VERSION, 3);

		if( !empty( $del ) )
		    @ldap_mod_del( $this->ldap, $dnReal, $del );

		if (!@ldap_modify ( $this->ldap,$dnReal, $info ))
		{
			$result['status'] = false;
			$result['msg']  = $this->functions->lang('Error on function') . ' ldap_functions->save_shared_accounts: ldap_modify';
			$result['msg'] .= "\n" . $this->functions->lang('Server return') . ': ' . ldap_error($this->ldap);
		}
		//print_r($info);echo "Teste $dn".$result['msg'];exit();
		return $result;
	}

	function create_shared_accounts($params)
	{
		/* Begin: Access verification */

		$forbidden = $this->noAccess( $params['context'], 'add_shared_accounts', 'create shared accounts' );

		if( $forbidden )
		    return( $forbidden );

		/* End: Access verification */

			
		/* Begin: Validation */

		if(!$params['desc'])
		{
		    $result['status'] = false;
		    $result['msg']  = $this->functions->lang('Field description is empty');	return $result;

		}

		if ( (empty($params['cn'])) || (empty($params['mail'])) )
		{
			$result['status'] = false;
				$result['msg']  = $this->functions->lang('Field mail or name is empty');	return $result;
			}
	
			if (! preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)+$/i', $params['mail']) )
			{
				$result['status'] = false;
				$result['msg']  = $this->functions->lang('Field mail is not formed correcty') . '.';
				return $result;
			}			                  
			$dn = "uid={$params['uid']}," . $params['context'];
                        $filter = "(mail=".$params['mail'].")";
			$justthese = array("cn");
			$search = @ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
			$entries = @ldap_get_entries($this->ldap,$search);
			if ($entries['count'] != 0)
			{
			$result['status'] = false;
			$result['msg'] = $this->functions->lang('Field mail already in use');
			return $result;
		}

		// Leio o ID a ser usado na criação do objecto. Esta função já incrementa o ID no BD.
		$next_id = ($this->db_functions->get_next_id('accounts'));
		if ((!is_numeric($next_id['id'])) || (!$next_id['status']))
		{
			$return['status'] = false;
			$return['msg'] = $this->functions->lang('problems getting user id') . ".\n" . $id['msg'];
			return $return;
		}
		else
		{
			$id = $next_id['id'];
			}

			/* End: Validation */
							
			$info = array();
		$info['cn']					= utf8_encode($params['cn']);
		$info['sn']					= utf8_encode($params['cn']);
		$info['uidnumber']					= $id;
		$info['homeDirectory']				= '/dev/null';
		$info['gidNumber']					= '1000';
			$info['uid']				= $params['uid'];
			$info['mail']				= $params['mail'];
			$info['description'] = utf8_encode($params['desc']);			
			$info['phpgwAccountType']	= 's';
		$info['objectClass'][0]		= 'inetOrgPerson';
		$info['objectClass'][1]		= 'phpgwAccount';
		$info['objectClass'][2]		= 'top';
		$info['objectClass'][3]		= 'person';
		$info['objectClass'][4]		= 'qmailUser';
		$info['objectClass'][5]		= 'organizationalPerson';
		$info['objectClass'][6]		= 'posixAccount';
			
			if ($params['accountStatus'] == 'on')
			{
				$info['accountStatus'] = 'active';
			}
			if ($params['phpgwAccountVisible'] == 'on')
			{
				$info['phpgwAccountVisible'] = '-1';
			}
		if( !empty( $params['mailalternateaddress'] ) )
			$info['mailalternateaddress'] = $params['mailalternateaddress'];
			
			/*if (!empty($params['owners']))
			{
				foreach($params['owners'] as $index=>$uidnumber)
				{
					$info['mailForwardingAddress'][] = $this->uidnumber2mail($uidnumber);
				}
			}*/
			$result = array();
			//print_r($info);exit();
			if (!@ldap_add ( $this->ldap, $dn, $info ))
			{
				$result['status'] = false;
				$result['msg']  = $this->functions->lang('Error on function') . ' ldap_functions->create_shared_accounts';
				$result['msg'] .= "\n" . $this->functions->lang('Server return') . ': ' . ldap_error($this->ldap);
			}
			else{
				$result['status'] = true;							
			}
			return $result;
		}
	
	/* expressoAdmin: email lists : deve utilizar o ldap Host Master com o usuario e senha do CC*/
	/* ldap connection following referrals and using Master config, from setup */
	function ldapMasterConnect()
	{
		/*
		$common = new common();
		if ( (!empty($GLOBALS['phpgw_info']['server']['ldap_master_host'])) &&
			 (!empty($GLOBALS['phpgw_info']['server']['ldap_master_root_dn'])) &&
			 (!empty($GLOBALS['phpgw_info']['server']['ldap_master_root_pw'])) )
		{
			$ldap_connection = ldap_connect($GLOBALS['phpgw_info']['server']['ldap_master_host']);
			ldap_set_option($ldap_connection, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($ldap_connection, LDAP_OPT_REFERRALS, true);
			ldap_set_rebind_proc($ldap_connection, ldapRebind);
			ldap_bind($ldap_connection, $GLOBALS['phpgw_info']['server']['ldap_master_root_dn'], $GLOBALS['phpgw_info']['server']['ldap_master_root_pw']);
		}
		else
		{
			$ldap_connection = $common->ldapConnect($GLOBALS['phpgw_info']['server']['ldap_host'],
											   $GLOBALS['phpgw_info']['server']['ldap_root_dn'],
											   $GLOBALS['phpgw_info']['server']['ldap_root_pw'], true);
		}
		
		// If success, return follow_referral connection. Else, return normal connection.
		if ($ldap_connection)
			return $ldap_connection;
		else
			return $this->ldap;
		*/
		
		// Este if é para utilizar o master. (para replicação)
		if ( (!empty($GLOBALS['phpgw_info']['server']['ldap_master_host'])) && ($ldap_connection = ldap_connect($GLOBALS['phpgw_info']['server']['ldap_master_host'])) )
		{
			ldap_set_option($ldap_connection, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($ldap_connection, LDAP_OPT_REFERRALS, true);
			ldap_set_rebind_proc($ldap_connection, ldapRebind);
			if ( ($_SESSION['phpgw_info']['expresso']['cc_ldap_server']['acc'] != '') && ($_SESSION['phpgw_info']['expresso']['cc_ldap_server']['pw'] != '') )
			{
				if ( ! ldap_bind($ldap_connection, $_SESSION['phpgw_info']['expresso']['cc_ldap_server']['acc'], $_SESSION['phpgw_info']['expresso']['cc_ldap_server']['pw']) )
				{
					return false;
				}
			}
			return $ldap_connection;
		}
		else
		{
			$ldap_connection = ldap_connect($GLOBALS['phpgw_info']['server']['ldap_host']);
			if ($ldap_connection)
			{
				ldap_set_option($ldap_connection,LDAP_OPT_PROTOCOL_VERSION,3);
				ldap_set_option($ldap_connection, LDAP_OPT_REFERRALS, true);
				if ( ldap_bind($ldap_connection, $GLOBALS['phpgw_info']['server']['ldap_root_dn'], $GLOBALS['phpgw_info']['server']['ldap_root_pw']) )
					return $ldap_connection;
			}
		}
		
		return false;
	}
		
	function validate_fields($params)
	{
		/* ldap connection following referals and using Contac Center config*/
		if (is_array($_SESSION['phpgw_info']['expresso']['cc_ldap_server']))
		{
			$ldap_connection = ldap_connect($_SESSION['phpgw_info']['expresso']['cc_ldap_server']['host']);
			if ($ldap_connection)
			{
				ldap_set_option($ldap_connection, LDAP_OPT_PROTOCOL_VERSION, 3);
				ldap_set_option($ldap_connection, LDAP_OPT_REFERRALS, true);
				
				if ( ($_SESSION['phpgw_info']['expresso']['cc_ldap_server']['acc'] != '') && ($_SESSION['phpgw_info']['expresso']['cc_ldap_server']['pw'] != '') )
					ldap_bind($ldap_connection, $_SESSION['phpgw_info']['expresso']['cc_ldap_server']['acc'], $_SESSION['phpgw_info']['expresso']['cc_ldap_server']['pw']);
				$context = $_SESSION['phpgw_info']['expresso']['cc_ldap_server']['dn'];
			}
			else
			{
				$result['status'] = false;
				$result['msg'] = $this->functions->lang('Connection with ldap fail') . ".";
				return $result;
			}
		}
		else
		{
			$ldap_connection = ldap_connect($GLOBALS['phpgw_info']['server']['ldap_host']);
			ldap_set_option($ldap_connection,LDAP_OPT_PROTOCOL_VERSION,3);
			ldap_set_option($ldap_connection, LDAP_OPT_REFERRALS, true);
			ldap_bind($ldap_connection, $GLOBALS['phpgw_info']['server']['ldap_root_dn'], $GLOBALS['phpgw_info']['server']['ldap_root_pw']);
			$context = $GLOBALS['phpgw_info']['server']['ldap_context'];
		}
		
		$result['status'] = true;
		
		$params = unserialize($params['attributes']);
		$type = $params['type'];
		$uid = $params['uid'];
		$mail = $params['mail'];
		$mailalternateaddress = $params['mailalternateaddress'];
		$cpf = $params['cpf'];
				
		if ($_SESSION['phpgw_info']['expresso']['global_denied_users'][$uid])
		{
			$result['status'] = false;
			$result['msg'] = $this->functions->lang('this login can not be used because is a system account') . ".";
			return $result;
		}
		
		if (($type == 'create_user') || ($type == 'rename_user')) 
		{
			if ($this->current_config['expressoAdmin_prefix_org'] == 'true')
			{
				//Obtenho UID sem a organização. Na criação o uid já vem sem a organização
				$tmp_uid_without_org = preg_split('/-/', $params['uid']);
				$tmp_reverse_uid_without_org = array_reverse($tmp_uid_without_org);
				array_pop($tmp_reverse_uid_without_org);
				$uid_without_org = implode("-", $tmp_reverse_uid_without_org);
				$filter = "(&(|(phpgwAccountType=u)(phpgwAccountType=l))(|(uid=$uid)(uid=$uid_without_org)))";
			}
			else
			{
				$filter = "(&(|(phpgwAccountType=u)(phpgwAccountType=l))(uid=$uid))";
			}
			/*
			//UID
			if (($type == 'rename_user') && ($this->current_config['expressoAdmin_prefix_org'] == 'true'))
			{
				//Obtenho UID sem a organização. Na criação o uid já vem sem a organização
				$tmp_uid_without_org = preg_split('/-/', $params['uid']);
				$tmp_reverse_uid_without_org = array_reverse($tmp_uid_without_org);
				array_pop($tmp_reverse_uid_without_org);
				$uid_without_org = implode("-", $tmp_reverse_uid_without_org);
				$filter = "(&(|(phpgwAccountType=u)(phpgwAccountType=l))(|(uid=$uid)(uid=$uid_without_org)))";
			}
			else
			{
				$filter = "(&(|(phpgwAccountType=u)(phpgwAccountType=l))(uid=$uid))";
			}
			*/
			
			$justthese = array("uid", "mail", "cn");
			$search = ldap_search($ldap_connection, $context, $filter, $justthese);
			$count_entries = ldap_count_entries($ldap_connection,$search);
			if ($count_entries > 0)
			{
				$entries = ldap_get_entries($ldap_connection, $search);
				
				for ($i=0; $i<$entries['count']; ++$i)
				{
					$users .= $entries[$i]['cn'][0] . ' - ' . $entries[$i]['mail'][0] . "\n";
				}
				
				$result['status'] = false;
				$result['msg'] = $this->functions->lang('this login is already used by') . ":\n" . $users;
				return $result;
			}

			// GRUPOS
			$filter = "(&(phpgwAccountType=g)(cn=$uid))";
			$justthese = array("cn");
			$search = ldap_search($ldap_connection, $context, $filter, $justthese);
			$count_entries = ldap_count_entries($ldap_connection,$search);
			if ($count_entries > 0)
			{
				$result['status'] = false;
				$result['msg'] = $this->functions->lang('This login is being used by a group') . ".";
				return $result;
			}
			
			
			// UID em outras organizações, pesquiso apenas na maquina local e se utilizar prefix_org
			if ($this->current_config['expressoAdmin_prefix_org'] == 'true')
			{
				$ldap_connection2 = ldap_connect($GLOBALS['phpgw_info']['server']['ldap_host']);
				ldap_set_option($ldap_connection2,LDAP_OPT_PROTOCOL_VERSION,3);
				ldap_set_option($ldap_connection2, LDAP_OPT_REFERRALS, false);
				ldap_bind($ldap_connection2, $GLOBALS['phpgw_info']['server']['ldap_root_dn'], $GLOBALS['phpgw_info']['server']['ldap_root_pw']);
				$context = $GLOBALS['phpgw_info']['server']['ldap_context'];
				
				//Obtenho UID sem a organização
				/*
				$tmp_uid_without_org = preg_split('/-/', $params['uid']);
				if (count($tmp_uid_without_org) < 2)
				{
					$result['status'] = false;
					$result['msg'] = 'Novo login sem organização.';
					return $result;
				}
				$tmp_reverse_uid_without_org = array_reverse($tmp_uid_without_org);
				array_pop($tmp_reverse_uid_without_org);
				$uid_without_org = implode("-", $tmp_reverse_uid_without_org);
				*/
				
				$filter = "(ou=*)";
				$justthese = array("ou");
				$search = ldap_list($ldap_connection2, $context, $filter, $justthese);
				$entries = ldap_get_entries($ldap_connection2	,$search);
				
				foreach ($entries as $index=>$org)
				{
					$organization = $org['ou'][0];
					$organization = strtolower($organization);
				
					$filter = "(&(|(phpgwAccountType=u)(phpgwAccountType=l))(uid=$organization-$uid))";
					
					$justthese = array("uid");
					$search = ldap_search($ldap_connection2, $context, $filter, $justthese);
					$count_entries = ldap_count_entries($ldap_connection2,$search);
					if ($count_entries > 0)
					{
						$result['status'] = false;
						$result['msg'] = $this->functions->lang('this login is already used by a user in another organization') . ".";
						ldap_close($ldap_connection2);
						return $result;
					}
				}
				ldap_close($ldap_connection2);
			}
		}
		
		if ($type == 'rename_user')
		{
			return $result;
		}
		
		// MAIL
		$filter = "(&(|(phpgwAccountType=u)(phpgwAccountType=l))(|(mail=$mail)(mailalternateaddress=$mail)))";
		$justthese = array("mail", "uid");
		$search = ldap_search($ldap_connection, $context, $filter, $justthese);
		$entries = ldap_get_entries($ldap_connection,$search);
		if ($entries['count'] == 1){
			if ($entries[0]['uid'][0] != $uid){
				$result['status'] = false;
				$result['msg'] = $this->functions->lang('this email address is being used by 1 user') . ": " . $entries[0]['uid'][0];
				return $result;
			}
		}
		else if ($entries['count'] > 1){
			$result['status'] = false;
			$result['msg'] = $this->functions->lang('this email address is being used by 2 or more users') . ".";
			return $result;
		}
		
		// MAILAlternateAddress
		$filter = "(&(|(phpgwAccountType=u)(phpgwAccountType=l))(|(mail=$mailalternateaddress)(mailalternateaddress=$mailalternateaddress)))";
		$justthese = array("mail", "uid");
		$search = ldap_search($ldap_connection, $context, $filter, $justthese);
		$entries = ldap_get_entries($ldap_connection,$search);
		if ($entries['count'] == 1){
			if ($entries[0]['uid'][0] != $uid){
				$result['status'] = false;
				$result['msg'] = $this->functions->lang('alternative email is being used by 1 user') . ": " . $entries[0]['uid'][0];
				return $result;
			}
		}
		else if ($entries['count'] > 1){
			$result['status'] = false;
			$result['msg'] = $this->functions->lang('alternative email is being used by 2 or more users') . ".";
			return $result;
		}

		//Begin: Check CPF, only if the manager has access to this field.
		if ($this->functions->check_acl($_SESSION['phpgw_session']['session_lid'], 'manipulate_corporative_information'))
		{
			if (!empty($cpf))
			{
				if (!$this->functions->checkCPF($cpf))
				{
					$result['status'] = false;
					$result['msg'] = $this->functions->lang('Field CPF is invalid') . '.';
					return $result;
				}
				else
				{
					//retira caracteres que não são números.
					$cpf = preg_replace('/[^0-9]/', '', $cpf);
				
					$local_ldap_connection = ldap_connect($GLOBALS['phpgw_info']['server']['ldap_host']);
					if ($ldap_connection)
					{
						ldap_set_option($local_ldap_connection, LDAP_OPT_PROTOCOL_VERSION, 3);
						ldap_set_option($local_ldap_connection, LDAP_OPT_REFERRALS, false);
						ldap_bind($local_ldap_connection, $GLOBALS['phpgw_info']['server']['ldap_root_dn'], $GLOBALS['phpgw_info']['server']['ldap_root_pw']);
					}
					else
					{
						$result['status'] = false;
						$result['msg'] = $this->functions->lang('Connection with ldap fail') . ".";
						return $result;
					}
				
					$filter = "(&(phpgwAccountType=u)(cpf=$cpf))";
					$justthese = array("cn","uid" ,"mail");
					$search = ldap_search($local_ldap_connection, $context, $filter, $justthese);
					$entries = ldap_get_entries($local_ldap_connection,$search);
				
					if ( ($entries['count'] != 1) && (strcasecmp($uid, $entries[0]['uid'][0]) != 0) )
					{
						if ($entries['count'] > 0)
						{
							$result['question'] = $this->functions->lang('Field CPF used by') . ":\n";
								for ($i=0; $i<$entries['count']; ++$i)
									{
										if (strcasecmp($uid, $entries[$i]['uid'][0]) != 0)
									$result['question'] .= "- " . $entries[$i]['cn'][0] ." - ".$entries[$i]['uid'][0] ." - ".$entries[$i]['mail'][0] . "\n";
									}
									$result['question'] .= $this->functions->lang("Do you want to continue anyway") . "?";
									return $result;
						}
					}
					ldap_close($local_ldap_connection);
				}
			}
			else if ($this->current_config['expressoAdmin_cpf_obligation']) 
			{
				$result['status'] = false;
				$result['msg'] = $this->functions->lang('Field CPF must be completed') . '.';
				return $result;
			}
		}
		//End: Check CPF

		return $result;
	}
	
	function generate_login($params) {
		$params = unserialize($params['attributes']);
		$context = $GLOBALS['phpgw_info']['server']['ldap_context'];
		$justthese = array("uid");
		$i=1;
		$login = array("status" => False,"msg" => lang("Login generator disabled"));
		
		if( (isset($this->current_config['expressoAdmin_loginGenScript'])) && 
				($this->current_config['expressoAdmin_loginGenScript'])) {
			
			include_once "if.login.inc.php";
			include_once "class.".$this->current_config['expressoAdmin_loginGenScript'].
					".inc.php";

			$classe = new ReflectionClass($this->current_config['expressoAdmin_loginGenScript']);
					
			if(!$classe->implementsInterface('login'))
			{
				return array(
					"status" => False,
					"msg" => lang("Login interface not implemented (contact suport)")
				);
			}

			$login = $classe->newInstance()->generate_login($params["first_name"],$params["second_name"],$this->ldap);
			
			/*
				If login exists, it concatenates a number to the end.
				resulting in a new login
			 */
			$i = 1;
			while($i < 1000) // Limit of 1000 equal names
			{
				$search = ldap_search($this->ldap, $context, "(uid=".$login.")", $justthese);
				if (ldap_count_entries($this->ldap,$search) == 0)
					break;
				else
				{
					if ($i > 1) // If login have a number, remove the number and put the new one
						$login=substr($login,0,strlen($login)-strlen($i)).$i;
					else
						$login.=$i;
					++$i;
				}
			}
		}
		
		return array('status'=>true,'msg' => $login);
	}
	function validate_fields_group($params)
	{
		/* ldap connection following referals and using Contac Center config*/
		if (is_array($_SESSION['phpgw_info']['expresso']['cc_ldap_server']))
		{
			$ldap_connection = ldap_connect($_SESSION['phpgw_info']['expresso']['cc_ldap_server']['host']);
			if ($ldap_connection)
			{
				ldap_set_option($ldap_connection, LDAP_OPT_PROTOCOL_VERSION, 3);
				ldap_set_option($ldap_connection, LDAP_OPT_REFERRALS, true);
				if ( ($GLOBALS['phpgw_info']['expresso']['cc_ldap_server']['acc'] != '') && ($GLOBALS['phpgw_info']['expresso']['cc_ldap_server']['pw'] != '') )
					ldap_bind($ldap_connection, $GLOBALS['phpgw_info']['expresso']['cc_ldap_server']['acc'], $GLOBALS['phpgw_info']['expresso']['cc_ldap_server']['pw']);
				$context = $_SESSION['phpgw_info']['expresso']['cc_ldap_server']['dn'];
			}
			else
			{
				$result['status'] = false;
				$result['msg'] = $this->functions->lang('Connection with ldap fail') . ".";
				return $result;
			}
		}
		else
		{
			$ldap_connection = ldap_connect($GLOBALS['phpgw_info']['server']['ldap_host']);
			ldap_set_option($ldap_connection,LDAP_OPT_PROTOCOL_VERSION,3);
			ldap_set_option($ldap_connection, LDAP_OPT_REFERRALS, true);
			ldap_bind($ldap_connection, $GLOBALS['phpgw_info']['server']['ldap_root_dn'], $GLOBALS['phpgw_info']['server']['ldap_root_pw']);
			$context = $GLOBALS['phpgw_info']['server']['ldap_context'];
		}

		$cn = $params['cn'];
		$result['status'] = true;
		
		if ($_SESSION['phpgw_info']['expresso']['global_denied_groups'][$cn])
		{
			$result['status'] = false;
			$result['msg'] = $this->functions->lang('This group name can not be used because is a System Account') . ".";
			return $result;
		}
		
		// CN
		$filter = "(&(phpgwAccountType=g)(cn=$cn))";
		$justthese = array("cn");
		$search = ldap_search($ldap_connection, $context, $filter, $justthese);
		$count_entries = ldap_count_entries($ldap_connection,$search);
		if ($count_entries > 0)
		{
			$result['status'] = false;
			$result['msg'] = $this->functions->lang('This name is already used') . ".";
			return $result;
		}
		
		// UID
		$filter = "(&(|(phpgwAccountType=u)(phpgwAccountType=l))(uid=$cn))";
		$justthese = array("uid");
		$search = ldap_search($ldap_connection, $context, $filter, $justthese);
		$count_entries = ldap_count_entries($ldap_connection,$search);
		if ($count_entries > 0)
		{
			$result['status'] = false;
			$result['msg'] = $this->functions->lang('This grupo name is already used by an user') . ".";
			return $result;
		}
		
		return $result;	
	}
	
	function validate_fields_maillist($params)
	{
		/* ldap connection following referals and using Contac Center config*/
		if (is_array($_SESSION['phpgw_info']['expresso']['cc_ldap_server']))
		{
			$ldap_connection = ldap_connect($_SESSION['phpgw_info']['expresso']['cc_ldap_server']['host']);
			if ($ldap_connection)
			{
				ldap_set_option($ldap_connection, LDAP_OPT_PROTOCOL_VERSION, 3);
				ldap_set_option($ldap_connection, LDAP_OPT_REFERRALS, true);
				if ( ($GLOBALS['phpgw_info']['expresso']['cc_ldap_server']['acc'] != '') && ($GLOBALS['phpgw_info']['expresso']['cc_ldap_server']['pw'] != '') )
					ldap_bind($ldap_connection, $GLOBALS['phpgw_info']['expresso']['cc_ldap_server']['acc'], $GLOBALS['phpgw_info']['expresso']['cc_ldap_server']['pw']);
				$context = $_SESSION['phpgw_info']['expresso']['cc_ldap_server']['dn'];
			}
			else
			{
				$result['status'] = false;
				$result['msg'] = $this->functions->lang('Connection with ldap fail') . ".";
				return $result;
			}
		}
		else
		{
			$ldap_connection = ldap_connect($GLOBALS['phpgw_info']['server']['ldap_host']);
			ldap_set_option($ldap_connection,LDAP_OPT_PROTOCOL_VERSION,3);
			ldap_set_option($ldap_connection, LDAP_OPT_REFERRALS, true);
			ldap_bind($ldap_connection, $GLOBALS['phpgw_info']['server']['ldap_root_dn'], $GLOBALS['phpgw_info']['server']['ldap_root_pw']);
			$context = $GLOBALS['phpgw_info']['server']['ldap_context'];
		}
		
		$uid = $params['uid'];
		$mail = $params['mail'];
		$result['status'] = true;
		
		if ($_SESSION['phpgw_info']['expresso']['global_denied_users'][$uid])
		{
			$result['status'] = false;
			$result['msg'] = $this->functions->lang('This LOGIN can not be used because is a System Account') . ".";
			return $result;
		}
		
		// UID
		$filter = "(&(phpgwAccountType=l)(uid=$uid))";
		$justthese = array("uid");
		$search = ldap_search($ldap_connection, $context, $filter, $justthese);
		$count_entries = ldap_count_entries($ldap_connection,$search);
		if ($count_entries > 0)
		{
			$result['status'] = false;
			$result['msg'] = $this->functions->lang('this email list login is already used') . ".";
			return $result;
		}
		
		// MAIL
		$filter = "(&(|(phpgwAccountType=u)(phpgwAccountType=l))(|(mail=$mail)(mailalternateaddress=$mail)))";
		$justthese = array("mail");
		$search = ldap_search($ldap_connection, $context, $filter, $justthese);
		$count_entries = ldap_count_entries($ldap_connection,$search);
		if ($count_entries > 0)
		{
			$result['status'] = false;
			$result['msg'] = $this->functions->lang('this email address is already used') . ".";
			return $result;
		}
		
		return $result;	
	}

	function get_available( $params, $justthese = false, $targetTypes = false )
	{
	    $search = $params['sentence'] ? $params['sentence'] : $params['filter'];
		
	    if( !$justthese )
		$justthese = array('cn', 'uidNumber','uid');
    	
	    if( !$targetTypes )
		$targetTypes = 'u';
    	
	    $ldapService = ServiceLocator::getService('ldap');
			
	    $entries = $ldapService->accountSearch( $search, $justthese, $params['context'], $targetTypes, 'cn' );

	    return( $entries );
	}
			
	function get_options( $entries, $label, $value = false, $uid = false )
		{
	    if( !$value )
		$value = $label;

	    $options = '';
	    foreach( $entries as  $entry )
			{
		if( $uid )
		    $entry[$label] .= '('.$entry[$uid].')';

		$options .= '<option value='.$entry[$value].'>'.$entry[$label].'</option>';
		}

    	return $options;
	}
	
	function get_json( $entries, $label, $value = false, $uid = false )
	{
	    if( !$value )
		$value = $label;

            $options = array();

	    foreach( $entries as  $entry )
	    {
		if( $uid )
		    $entry[$label] .= '('.$entry[$uid].')';

                 $options[] = '"'.$entry[$value].'"'.':'.'"'.$entry[$label].'"';

			}

             return "{".implode(',',$options)."}";
	}


	//Busca usuários de um contexto e já retorna as options do select;
	function get_available_users($params)
				{
             $entries = $this->get_available($params );

//              $options = '';
//              foreach ($entries as  $value)
//                  $options .= '<option value='.$value['uidnumber'].'>'.$value['cn'].'('.$value['uid'].')</option>';

             return $this->get_options( $entries, 'cn', 'uidnumber', 'uid' );
	}

        //Busca usuários e contas compartilhadas de um contexto e já retorna as options do select;
	function get_available_users_and_shared_acounts($params)
	{
             $entries = $this->get_available($params, array('cn', 'uidNumber','uid'), array('u','s') );
//              $options = '';
//              foreach ($entries as  $value)
//                  $options .= '<option value='.$value['uidnumber'].'>'.$value['cn'].'('.$value['uid'].')</option>';
		
             return $this->get_options( $entries, 'cn', 'uidnumber', 'uid' );
	}
		
	function get_available_users2($params)
	{
             $entries = $this->get_available($params, array('cn', 'uid') );
		
//              $options = array();
//              foreach ($entries as $value)
//                  $options[] = '"'.$value['uid'].'"'.':'.'"'.$value['cn'].'('.$value['uid'].')'.'"';
		
             return $this->get_json( $entries, 'cn', 'uid', 'uid' );
	}

        function get_available_users3($params)
		{
             $ldapService = ServiceLocator::getService('ldap');
             $groups = $ldapService->accountSearch($params['filter'], array('gidNumber','cn','uid'), $params['context'], 'g', 'cn');
             $users = $ldapService->accountSearch($params['filter'], array('uidNumber','cn','uid'), $params['context'], 'u', 'cn');

             $user_options ='';
             $group_options ='';
             $user_options2 ='';
             $group_options2 ='';

             foreach($groups as  $group)
             {
                $group_options .= '<option  value="'.$group['gidnumber'].'">'.$group['cn'].' ('.$group['gidnumber'].')</option>'."\n";
                $group_options2 .= '<option  value="'.$group['gidnumber'].',G">'.$group['cn'].' ('.$group['gidnumber'].')</option>'."\n";
		}

             foreach($users as $user)
             {
                $user_options .= '<option  value="'.$user['uid'].'">'.$user['cn'].' ('.$user['uid'].')</option>'."\n";
                $user_options2 .= '<option  value="'.$user['uid'].',U">'.$user['cn'].' ('.$user['uid'].')</option>'."\n";

             }

            return array("users" => $user_options, "groups" => $group_options , "users2" => $user_options2, "groups2" => $group_options2);
        }

   	/**
         * @abstract Busca usuários de um contexto e retorna já formatado com as options do select.
         * @params array params com as informações do formulário com os dados do contexto e etc.
         * @return array com os usuários do contexto já com a formatação das options do select.
         */
	function get_available_users_messages_size($params)
			{
/*             $ldapService = ServiceLocator::getService('ldap');
             $entries = $ldapService->accountSearch($params['sentence'], array('cn', 'uidNumber','uid'), $params['context'], 'u', 'cn');
             $options = array();

             foreach ($entries as $value)
                 $options[] = '"'.$value['uid'].'"'.':'.'"'.$value['cn'].'('.$value['uid'].')'.'"';*/
		 
	    $entries = $this->get_available( $params, array('cn', 'uid') );

             return $this->get_json( $entries, 'cn', 'uid','uid' );
			}

	/**
         * @abstract Busca usuários e grupos de um contexto e retorna já formatado com as options do select.
         * @params array params com as informações do formulário com os dados do contexto e etc.
         * @return array com os usuários do contexto já com a formatação das options do select.
         */
	function get_available_users_and_groups_messages_size($params)
	{
             $ldapService = ServiceLocator::getService('ldap');
             $groups = $ldapService->accountSearch($params['sentence'], array('gidNumber','cn','uid'), $params['context'], 'g', 'cn');
             $users = $ldapService->accountSearch($params['sentence'], array('cn','uid'), $params['context'], 'u', 'cn');
             $optionsUsers = array();
             $optionsGroups = array();
             foreach ($users as $value)
                 $optionsUsers[] = '"'.$value['uid'].'"'.':'.'"'.$value['cn'].'('.$value['uid'].')'.'"';

             foreach ($groups as $value)
                 $optionsGroups[] = '"'.$value['gidNumber'].'"'.':'.'"'.$value['cn'].'('.$value['uid'].')'.'"';

             return "{".implode(',',$optionsUsers).implode(',',$optionsGroups)."}";
	}

  
	//Busca usuários e listas de um contexto e já retorna as options do select;
	function get_available_users_and_maillist($params)
			{

             $ldapService = ServiceLocator::getService('ldap');
             $users = $ldapService->accountSearch($params['sentence'], array('cn', 'mail','uid'), $params['context'], 'u', 'cn');
             $listas = $ldapService->accountSearch($params['sentence'], array('cn', 'mail','uid'), $params['context'], 'l', 'cn');
             $options = '';
             if(count($listas) > 0)
				{
                 $options .= '<option  value="-1" disabled>------------------------------&nbsp;&nbsp;&nbsp;&nbsp;'.$this->functions->lang('email lists').'&nbsp;&nbsp;&nbsp;&nbsp;------------------------------ </option>'."\n";
                 foreach ($listas as  $value)
                    $options .= '<option value='.$value['mail'].'>'.$value['cn'].'('.$value['uid'].')</option>';
				}

             if(count($users) > 0)
             {
                $options .= '<option  value="-1" disabled>-----------------------------&nbsp;&nbsp;&nbsp;&nbsp;'.$this->functions->lang('users').'&nbsp;&nbsp;&nbsp;&nbsp;---------------------------- </option>'."\n";
                foreach ($users as  $value)
                    $options .= '<option value='.$value['mail'].'>'.$value['cn'].'('.$value['uid'].')</option>';
			}
             
             return $options;
		}
		
	function get_available_groups($params)
		{
//              $ldapService = ServiceLocator::getService('ldap');
//              $entries = $ldapService->accountSearch($params['sentence'], array('cn', 'gidnumber'), $params['context'], 'g', 'cn');
//              $options = '';
// 
//              foreach ($entries as  $value)
//                  $options .= '<option value='.$value['gidnumber'].'>'.$value['cn'].'</option>';
		 
	    $entries = $this->get_available( $params, array( 'cn', 'gidNumber' ), 'g' );

             return $this->get_options( $entries, 'cn', 'gidnumber' );
		}
			
	function get_available_maillists($params)
	{
            $options = '';
            $context = $params['context'];

            //Exibe todas as listas
            if (empty($params['sentence']))
            {
                    $params['sentence'] = '.';                       
            }
            $maillists_info = $this->functions->get_list('maillists', $params['sentence'],(array) $context);

            foreach ($maillists_info as $maillist) 
            {
                $options .= "<option value=" . $maillist['uid'] . ">" . $maillist['uid'] . " (" . $maillist['email'] . ")" . "</option>";
            }

            return $options;
	}

        function get_available_institutional_account($params)
	{
            if ( !$ldapMasterConnect = $this->ldapMasterConnect() )
                    return false;

            $filtro =utf8_encode($params['filter']);
            $context =utf8_encode($params['context']);//adicionado
            $justthese = array("uid","mail","uidNumber");
            $maillists=ldap_list($ldapMasterConnect, $context, ("(&(phpgwAccountType=i)(mail=*$filtro*))"), $justthese);
            ldap_sort($ldapMasterConnect, $maillists, "uid");
    	
            $entries = ldap_get_entries($ldapMasterConnect, $maillists);
    	    	
		$options = '';
		for ($i=0; $i<$entries['count']; ++$i)
		{
                            $options .= "<option value=" . $entries[$i]['mail'][0] . ">" . $entries[$i]['mail'][0] . "</option>";
		}
    	
            ldap_close($ldapMasterConnect);


    	return $options;		
	}
	
        function get_available_shared_account($params)
	{
		if ( !$ldapMasterConnect = $this->ldapMasterConnect() )
			return false;
		
            $filtro =utf8_encode($params['filter']);
            $context =utf8_encode($params['context']);//adicionado
		$justthese = array("uid","mail","uidNumber");
            $maillists=ldap_search($ldapMasterConnect, $context, ("(&(phpgwAccountType=s)(mail=*$filtro*))"), $justthese);
    	ldap_sort($ldapMasterConnect, $maillists, "uid");
    	
    	$entries = ldap_get_entries($ldapMasterConnect, $maillists);
    	
		$options = '';			
		for ($i=0; $i<$entries['count']; ++$i)
		{
                            $options .= "<option value=" . $entries[$i]['mail'][0] . ">" . $entries[$i]['mail'][0] . "</option>";
		}
    	
    	ldap_close($ldapMasterConnect);
    	return $options;
	}
	
	function ldap_add_entry($dn, $entry)
	{
		$result = array();
		if (!@ldap_add ( $this->ldap, $dn, $entry ))
		{
			$result['status']		= false;
			$result['error_number']	= ldap_errno($this->ldap);
			$result['msg']			= $this->functions->lang('Error on function') . " ldap_functions->ldap_add_entry ($dn)" . ".\n" . $this->functions->lang('Server returns') . ': ' . ldap_errno($this->ldap) . ldap_error($this->ldap);
		}
		else
			$result['status'] = true;
		
		return $result;
	}
	
	function ldap_save_photo($dn, $pathphoto, $photo_exist=false)
	{
		$fd = fopen($pathphoto, "r");
		$fsize = filesize($pathphoto);
		$jpegStr = fread($fd, $fsize);
		fclose ($fd);
		$attrs['jpegPhoto'] = $jpegStr;
			
		if ($photo_exist)
			$res = @ldap_mod_replace($this->ldap, $dn, $attrs);
		else
			$res = @ldap_mod_add($this->ldap, $dn, $attrs);
			
		if ($res)
		{
			$result['status'] = true;
		}
		else
		{
			$result['status'] = false;
			$result['msg'] = $this->functions->lang('Error on function') . " ldap_functions->ldap_save_photo ($dn)" . ".\n" . $this->functions->lang('Server returns') . ': ' . ldap_error($this->ldap);
		}
		
		return $result;
	}
	
	function ldap_remove_photo($dn)
	{
		$attrs['jpegPhoto'] = array();
		$res = ldap_mod_del($this->ldap, $dn, $attrs);
		
		if ($res)
		{
			$result['status'] = true;
		}
		else
		{
			$result['status'] = false;
			$result['msg'] = $this->functions->lang('Error on function') . " ldap_functions->ldap_remove_photo ($dn)" . ".\n" . $this->functions->lang('Server returns') . ': ' . ldap_error($this->ldap);
		}
		
		return $result;
	}	
	
	// Pode receber tanto um único memberUid quanto um array de memberUid's
	function add_user2group($gidNumber, $memberUid)
	{
		$filter = "(&(phpgwAccountType=g)(gidNumber=$gidNumber))";
		$justthese = array("dn");
		$search = ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
		$entry = ldap_get_entries($this->ldap, $search);
		$group_dn = $entry[0]['dn'];
		$attrs['memberUid'] = $memberUid;
		
		$res = @ldap_mod_add($this->ldap, $group_dn, $attrs);
		
		if ($res)
		{
			$result['status'] = true;
		}
		else
		{
			$result['status'] = false;
			$result['msg'] = $this->functions->lang('Error on function') . " ldap_functions->add_user2group ($group_dn)" . ".\n" . $this->functions->lang('Server returns') . ': ' . ldap_error($this->ldap);
		}
		return $result;
	}
	
	function remove_user2group($gidNumber, $memberUid)
	{
		$filter = "(&(phpgwAccountType=g)(gidNumber=$gidNumber))";
		$justthese = array("dn");
		$search = ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
		$entry = ldap_get_entries($this->ldap, $search);
		$group_dn = $entry[0]['dn'];
		$attrs['memberUid'] = $memberUid;
		$res = @ldap_mod_del($this->ldap, $group_dn, $attrs);
		
		if ($res)
		{
			$result['status'] = true;
		}
		else
		{
			$result['status'] = false;
			$result['msg'] = $this->functions->lang('Error on function') . " ldap_functions->remove_user2group ($group_dn)" . ".\n" . $this->functions->lang('Server returns') . ': ' . ldap_error($this->ldap);
		}
		return $result;
	}
	
	function add_user2maillist($uid, $mail)
	{
		if ( !$ldapMasterConnect = $this->ldapMasterConnect() )
		{
			$result['status'] = false;
			$result['msg'] = $this->functions->lang('Ldap connection fail') . ".\n" . $this->functions->lang('Server returns') . ': ' . ldap_error($ldapMasterConnect);
			return $result;
		}
			
		$filter = "(&(phpgwAccountType=l)(uid=$uid))";
		$justthese = array("dn");
		$search = ldap_search($ldapMasterConnect, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
		$entry = ldap_get_entries($ldapMasterConnect, $search);
		$group_dn = $entry[0]['dn'];
		$attrs['mailForwardingAddress'] = $mail;
		$res = @ldap_mod_add($ldapMasterConnect, $group_dn, $attrs);
		
		if ($res)
		{
			$result['status'] = true;
		}
		else
		{
			$result['status'] = false;
			if (ldap_errno($ldapMasterConnect) == '50')
			{
				$result['msg'] =	$this->functions->lang('Error on the function') . ' ldap_functions->add_user2maillist' . ".\n" .
									$this->functions->lang('The user used for record on LPDA, must have write access') . ".\n";
									$this->functions->lang('The user') . ' ' . $_SESSION['phpgw_info']['expresso']['cc_ldap_server']['acc'] . ' ' . $this->functions->lang('does not have this access') . ".\n";
									$this->functions->lang('Edit Global Catalog Config, in the admin module, and add an user with write access') . ".\n";
			}					 
			else
				$result['msg'] = $this->functions->lang('Error on function') . " ldap_functions->add_user2maillist ($group_dn)" . ".\n" . $this->functions->lang('Server returns') . ': ' . ldap_error($ldapMasterConnect);
		}
		
		ldap_close($ldapMasterConnect);
		return $result;
	}
	
	function add_user2maillist_scl($dn, $array_emails)
	{
		$attrs['mailSenderAddress'] = $array_emails;
		
		$res = @ldap_mod_add($this->ldap, $dn, $attrs);
		
		if ($res)
		{
			$result['status'] = true;
		}
		else
		{
			$result['status'] = false;
			$result['msg'] = $this->functions->lang('Error on function') . " ldap_functions->add_user2maillist_scp ($dn)" . ".\n" . $this->functions->lang('Server returns') . ': ' . ldap_error($this->ldap);
		}
		return $result;
	}

	function remove_user2maillist($uid, $mail)
	{
		if ( !$ldapMasterConnect = $this->ldapMasterConnect() )
		{
			$result['status'] = false;
			$result['msg'] = $this->functions->lang('Ldap connection fail') . ".\n" . $this->functions->lang('Server returns') . ': ' . ldap_error($ldapMasterConnect);
			return $result;
		}
		
		$filter = "(&(phpgwAccountType=l)(uid=$uid))";
		$justthese = array("dn");
		$search = ldap_search($ldapMasterConnect, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
		$entry = ldap_get_entries($ldapMasterConnect, $search);
		$group_dn = $entry[0]['dn'];
		$attrs['mailForwardingAddress'] = $mail;
		$res = @ldap_mod_del($ldapMasterConnect, $group_dn, $attrs);
		
		if ($res)
		{
			$result['status'] = true;
		}
		else
		{
			$result['status'] = false;
			if (ldap_errno($ldapMasterConnect) == '50')
			{
				$result['msg'] =	$this->functions->lang('Error on the function') . ' ldap_functions->remove_user2maillist' . ".\n" .
									$this->functions->lang('The user used for record on LPDA, must have write access') . ".\n";
									$this->functions->lang('The user') . ' ' . $_SESSION['phpgw_info']['expresso']['cc_ldap_server']['acc'] . ' ' . $this->functions->lang('does not have this access') . ".\n";
									$this->functions->lang('Edit Global Catalog Config, in the admin module, and add an user with write access') . ".\n";
			}					 
			else
				$result['msg'] = $this->functions->lang('Error on function') . " ldap_functions->remove_user2maillist ($group_dn)" . ".\n" . $this->functions->lang('Server returns') . ': ' . ldap_error($ldapMasterConnect);
		}
		ldap_close($ldapMasterConnect);
		return $result;
	}

	function remove_user2maillist_scl($dn, $array_emails)
	{
		$attrs['mailSenderAddress'] = $array_emails;
		$res = @ldap_mod_del($this->ldap, $dn, $attrs);
		
		if ($res)
		{
			$result['status'] = true;
		}
		else
		{
			$result['status'] = false;
			$result['msg'] = $this->functions->lang('Error on function') . " ldap_functions->remove_user2maillist_scp ($dn)" . ".\n" . $this->functions->lang('Server returns') . ': ' . ldap_error($this->ldap);
		}
		return $result;
	}

	function replace_user2maillists($new_mail, $old_mail)
	{
		$filter = "(&(phpgwAccountType=l)(mailforwardingaddress=$old_mail))";
		$justthese = array("dn");
		$search = ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
		$entries = ldap_get_entries($this->ldap, $search);
		$result['status'] = true;
		for ($i=0; $i<$entries['count']; ++$i)
		{
			$attrs['mailforwardingaddress'] = $old_mail;
			$res1 = @ldap_mod_del($this->ldap, $entries[$i]['dn'], $attrs);
			$attrs['mailforwardingaddress'] = $new_mail;
			$res2 = @ldap_mod_add($this->ldap, $entries[$i]['dn'], $attrs);
		
			if ((!$res1) || (!$res2))
			{
				$result['status'] = false;
				$result['msg'] = $this->functions->lang('Error on function') . " ldap_functions->replace_user2maillists ($dn)" . ".\n" . $this->functions->lang('Server returns') . ': ' . ldap_error($this->ldap);
			}
		}
		
		return $result;
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
				$result['sn']					= utf8_decode($entry[0]['sn'][0]);
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
    				$result['groups_ldap'][ $entries[$i]['gidnumber'][0] ] = utf8_decode($entries[$i]['cn'][0]);
    			}
			}
		}
		if (is_array($result))
			return $result;
		else
			return false;
	}


        function get_user_cn_by_uid($uid)
	{
            foreach ($this->manager_contexts as $index=>$context)
            {

                    $justthese = array("cn");
                    $filter="(&(phpgwAccountType=u)(uid=".$uid."))";
                    $search = ldap_search($this->ldap, $context, $filter, $justthese);
                    $entry = ldap_get_entries($this->ldap, $search);
                    if($entry)
                        return utf8_decode($entry[0]['cn'][0]);
            }
            return null;
	}

        function get_group_cn_by_gidnumber($gidnumber)
	{
            foreach ($this->manager_contexts as $index=>$context)
		{
		
                    $justthese = array("cn");
                    $filter="(&(phpgwAccountType=g)(gidNumber=".$gidnumber."))";
                    $search = ldap_search($this->ldap, $context, $filter, $justthese);
                    $entry = ldap_get_entries($this->ldap, $search);
                    if($entry)
                        return utf8_decode($entry[0]['cn'][0]);
			}
            return null;
		}
		
	
        function get_user_info_by_uid($uid)
	{
		foreach ($this->manager_contexts as $index=>$context)
		{
			$filter="(&(phpgwAccountType=u)(uid=".$uid."))";
                        $justthese = array("cn");
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
                                $result['cn']					= utf8_decode($entry[0]['cn'][0]);
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
    				$result['groups_ldap'][ $entries[$i]['gidnumber'][0] ] = utf8_decode($entries[$i]['cn'][0]);
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
					$filter="(&(|(phpgwAccountType=u)(phpgwAccountType=s))(|";
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

	function get_maillist_scl_info($uidnumber)
	{
		foreach ($this->manager_contexts as $index=>$context)
		{
			$filter="(&(phpgwAccountType=l)(uidNumber=$uidnumber))";
			$search = ldap_search($this->ldap, $context, $filter);
			$entry = ldap_get_entries($this->ldap, $search);

			if ($entry['count'])
			{
                $sector_dn = '';
				//Pega o dn do setor do usuario.
				$entry[0]['dn'] = strtolower($entry[0]['dn']);
				$sector_dn_array = explode(",", $entry[0]['dn']);
                $sector_dn_array_count = count($sector_dn_array);
				for($i=1; $i<$sector_dn_array_count; ++$i)
					$sector_dn .= $sector_dn_array[$i] . ',';
				//Retira ultimo pipe.
				$sector_dn = substr($sector_dn,0,(strlen($sector_dn) - 1));
		
				$result['dn']						= $entry[0]['dn'];
				$result['context']					= $sector_dn;
				$result['uidnumber']				= $entry[0]['uidnumber'][0];
				$result['uid']						= $entry[0]['uid'][0];
				$result['cn']						= $entry[0]['cn'][0];
				$result['mail']						= $entry[0]['mail'][0];
				$result['accountStatus']			= $entry[0]['accountstatus'][0];
				$result['phpgwAccountVisible']		= $entry[0]['phpgwaccountvisible'][0];
				$result['accountRestrictive']		= $entry[0]['accountrestrictive'][0];
				$result['participantCanSendMail']	= $entry[0]['participantcansendmail'][0];
		
				//Senders
				for ($i=0; $i<$entry[0]['mailsenderaddress']['count']; ++$i)
				{
					$justthese = array("cn", "uidnumber", "uid", "mail");
					$filter="(&(phpgwAccountType=u)(mail=".$entry[0]['mailsenderaddress'][$i]."))";
					$search = ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
					$user_entry = ldap_get_entries($this->ldap, $search);
			
					$result['senders_info'][$user_entry[0]['mail'][0]]['uid'] = $user_entry[0]['uid'][0];
					$result['senders_info'][$user_entry[0]['mail'][0]]['cn'] = $user_entry[0]['cn'][0];
					$result['members'][] = $user_entry[0]['mail'][0];
				}
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
			$justthese = array("cn","uid");
			$i = 0;
			foreach ($gidnumbers as $gidnumber)
			{
				$filter="(&(phpgwAccountType=g)(gidNumber=".$gidnumber."))";
				$search = ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
				
				$entry = ldap_get_entries($this->ldap, $search);
				if ($entry['count'] == 0)
					$result['groups_info'][$i]['cn'] = '_' . $this->functions->lang('group only exist on DB, but does not exist on ldap');
					
				else
				{
					$result['groups_info'][$i]['uid'] = $entry[0]['uid'][0];
					$result['groups_info'][$i]['cn'] = $entry[0]['cn'][0];
				}
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
		$filter="(&(|(phpgwAccountType=u)(phpgwAccountType=s)(phpgwAccountType=l))(uidNumber=".$uidnumber."))";
		$search = ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
		$entry = ldap_get_entries($this->ldap, $search);
		return $entry[0]['uid'][0];
	}
	
	function uid2cn($uid)
	{
		$justthese = array("cn");
		$filter="(&(|(phpgwAccountType=u)(phpgwAccountType=l))(uid=".$uid."))";
		$search = ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
		$entry = ldap_get_entries($this->ldap, $search);
		return utf8_decode($entry[0]['cn'][0]);
	}

        function uid2uidnumber($uid)
	{
		$justthese = array("uidNumber");
		$filter="(&(|(phpgwAccountType=u)(phpgwAccountType=s)(phpgwAccountType=l))(uid=".$uid."))";
		$search = ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
		$entry = ldap_get_entries($this->ldap, $search);
		return $entry[0]['uidnumber'][0];
	}

	function uidnumber2mail($uidnumber)
	{
		$justthese = array("mail");
		$filter="(&(|(phpgwAccountType=u)(phpgwAccountType=l))(uidNumber=".$uidnumber."))";
		$search = ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
		$entry = ldap_get_entries($this->ldap, $search);
		return $entry[0]['mail'][0];
	}

	function get_associated_domain($params)
	{
			$justthese = array("associatedDomain");
			$filter="(objectClass=domainRelatedObject)";;
			$context = $params['context'];
			$search = ldap_search($this->ldap,$context, $filter, $justthese);
			$entry = ldap_get_entries($this->ldap, $search);
			return isset($entry[0]['associateddomain']) ?  $entry[0]['associateddomain'][0] : false;
        }
	
	function change_user_context($dn, $newrdn, $newparent)
	{
		if (!ldap_rename ( $this->ldap, $dn, $newrdn, $newparent, true ))
		{
			$return['status'] = false;
			$return['msg'] = $this->functions->lang('Error on function') . " ldap_functions->change_user_context ($dn)" . ".\n" . $this->functions->lang('Server returns') . ': ' . ldap_error($this->ldap);
		}
		else
			$return['status'] = true;
		
		return $return;
	}
	
	function replace_user_attributes($dn, $ldap_mod_replace)
	{
		if (!@ldap_mod_replace ( $this->ldap, $dn, $ldap_mod_replace ))
		{
			$return['status'] = false;
			$return['error_number'] = ldap_errno($this->ldap);
			$return['msg'] = $this->functions->lang('Error on function') . " ldap_functions->replace_user_attributes ($dn)" . ".\n" . $this->functions->lang('Server returns') . ': ' . ldap_error($this->ldap);
		}
		else
			$return['status'] = true;
		
		return $return;
	}
	
	function add_user_attributes($dn, $ldap_add)
	{
		if (!@ldap_mod_add ( $this->ldap, $dn, $ldap_add ))
		{
			$return['status'] = false;
			$return['error_number'] = ldap_errno($this->ldap);
			$return['msg'] = $this->functions->lang('Error on function') . " ldap_functions->add_user_attributes ($dn)" . ".\n" . $this->functions->lang('Server returns') . ': ' . ldap_error($this->ldap);
		}
		else
			$return['status'] = true;
		
		return $return;
	}
	
	function remove_user_attributes($dn, $ldap_remove)
	{
		if (!@ldap_mod_del ( $this->ldap, $dn, $ldap_remove ))
		{
			$return['status'] = false;
			$return['msg'] = $this->functions->lang('Error on function') . " ldap_functions->remove_user_attributes ($dn)" . ".\n" . $this->functions->lang('Server returns') . ': ' . ldap_error($this->ldap);
		}
		else
			$return['status'] = true;
		
		return $return;
	}
	
	function set_user_password($uid, $password)
	{
		$justthese = array("userPassword");
		$filter="(&(phpgwAccountType=u)(uid=".$uid."))";
		$search = ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
	    $entry = ldap_get_entries($this->ldap, $search);
		$dn = $entry[0]['dn'];
		$userPassword = $entry[0]['userpassword'][0];
		$ldap_mod_replace['userPassword'] = $password;
		$this->replace_user_attributes($dn, $ldap_mod_replace);
		return $userPassword;
	}
	
	function delete_user($user_info)
	{
		// Verifica acesso do gerente (OU) ao tentar deletar um usuário.
		$manager_access = false;
		foreach ($this->manager_contexts as $index=>$context)
		{
			if ( (strpos(strtolower($user_info['context']), strtolower($context))) || (strtolower($user_info['context']) == strtolower($context)) )
			{
				$manager_access = true;
				break;
			}
		}
		if (!$manager_access)
		{
			$return['status'] = false;
			$result['msg'] = $this->functions->lang('You do not have access to delete this user') . ".";
			return $return;
		}
		
		$return['status'] = true;
		$return['msg'] = "";
				
		// GROUPS
		$attrs = array();
		$attrs['memberuid'] = $user_info['uid'];
		
		if (count($user_info['groups_info']))
		{
			foreach ($user_info['groups_info'] as $group_info)
			{
				$gidnumber = $group_info['gidnumber'];
				$justthese = array("dn");
				$filter="(&(phpgwAccountType=g)(gidnumber=".$gidnumber."))";
				$search = ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
	    		$entry = ldap_get_entries($this->ldap, $search);
				$dn = $entry[0]['dn'];

				if (!@ldap_mod_del($this->ldap, $dn, $attrs))
				{
					$return['status'] = false;
					$result['msg'] = $this->functions->lang('Error on function') . " ldap_functions->delete_user from group ($dn)" . ".\n" . $this->functions->lang('Server returns') . ': ' . ldap_error($this->ldap);
				}
			}
		}
		
		//INSTITUTIONAL ACCOUNTS
		$attrs = array();
		$attrs['mailForwardingAddress'] = $user_info['mail'];
		
		$justthese = array("dn");
		$filter="(&(phpgwAccountType=i)(mailforwardingaddress=".$user_info['mail']."))";
		$search = ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
	    $entries = ldap_get_entries($this->ldap, $search);
		
		for ($i=0; $i<$entries['count']; ++$i)
		{
			if ( !@ldap_mod_del($this->ldap, $entries[$i]['dn'], $attrs) )
			{
				$result['status'] = false;
				$result['msg'] = $this->functions->lang('Error on function') . " ldap_functions->delete_user, institutional accounts ($dn)" . ".\n" . $this->functions->lang('Server returns') . ': ' . ldap_error($this->ldap);
			}
		}
		
		// MAILLISTS
		$attrs = array();
		$attrs['mailForwardingAddress'] = $user_info['mail'];
		
		if (count($user_info['maillists_info']))
		{
			
			if ( !$ldapMasterConnect = $this->ldapMasterConnect() )
			{
				$return['status'] = false;
				$result['msg'] = $this->functions->lang('Connection with ldap_master fail') . ".\n" . $this->functions->lang('Server returns') . ': ' . ldap_error($this->ldap);
				return $return;
			}
			
			foreach ($user_info['maillists_info'] as $maillists_info)
			{
				$uid = $maillists_info['uid'];
				$justthese = array("dn");
				$filter="(&(phpgwAccountType=l)(uid=".$uid."))";
				$search = ldap_search($ldapMasterConnect, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
	    		$entry = ldap_get_entries($ldapMasterConnect, $search);
				$dn = $entry[0]['dn'];
			
				if (!@ldap_mod_del($ldapMasterConnect, $dn, $attrs))
				{
					$return['status'] = false;
					if (ldap_errno($ldapMasterConnect) == '50')
					{
						$result['msg'] =	$this->functions->lang('Error on the function') . ' ldap_functions->add_user2maillist' . ".\n" .
											$this->functions->lang('The user used for record on LPDA, must have write access') . ".\n";
											$this->functions->lang('The user') . ' ' . $_SESSION['phpgw_info']['expresso']['cc_ldap_server']['acc'] . ' ' . $this->functions->lang('does not have this access') . ".\n";
											$this->functions->lang('Edit Global Catalog Config, in the admin module, and add an user with write access') . ".\n";
					}
					else
						$result['msg'] = $this->functions->lang('Error on function') . " ldap_functions->delete_user, email lists ($dn)" . ".\n" . $this->functions->lang('Server returns') . ': ' . ldap_error($ldapMasterConnect);
				}
			}
			ldap_close($ldapMasterConnect);
		}
			
		// UID
		$dn = "uid=" . $user_info['uid'] . "," . $user_info['context'];
		if (!@ldap_delete($this->ldap, $dn))
		{
			$return['status'] = false;
			$result['msg'] = $this->functions->lang('Error on function') . " ldap_functions->delete_user, email lists ($dn)" . ".\n" . $this->functions->lang('Server returns') . ': ' . ldap_error($ldapMasterConnect);
		}
		/* jakjr */
		return $return;
	}
	
	function delete_maillist($uidnumber, $mail)
	{
		$return['status'] = true;
		
		$justthese = array("dn");
		
		// remove listas dentro de listas
		$filter="(&(phpgwAccountType=l)(mailForwardingAddress=".$mail."))";
		$search = ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
		$entry = ldap_get_entries($this->ldap, $search);
		$attrs['mailForwardingAddress'] = $mail;
		for ($i=0; $i<=$entry['count']; ++$i)
	    {
			$dn = $entry[$i]['dn'];
	    	@ldap_mod_del ( $this->ldap, $dn,  $attrs);
	    }
		
		$filter="(&(phpgwAccountType=l)(uidnumber=".$uidnumber."))";
		$search = ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
   		$entry = ldap_get_entries($this->ldap, $search);
		$dn = $entry[0]['dn'];
		
		if (!@ldap_delete($this->ldap, $dn))
		{
			$return['status'] = false;
			$result['msg'] = $this->functions->lang('Error on function') . " ldap_functions->delete_maillist ($dn)" . ".\n" . $this->functions->lang('Server returns') . ': ' . ldap_error($this->ldap);
		}
		
		return $return;
	}

	function delete_group($gidnumber)
	{
		$return['status'] = true;
		
		$justthese = array("dn");
		$filter="(&(phpgwAccountType=g)(gidnumber=".$gidnumber."))";
		$search = ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
   		$entry = ldap_get_entries($this->ldap, $search);
		$dn = $entry[0]['dn'];
		
		if (!@ldap_delete($this->ldap, $dn))
		{
			$return['status'] = false;
			$result['msg'] = $this->functions->lang('Error on function') . " ldap_functions->delete_group ($dn)" . ".\n" . $this->functions->lang('Server returns') . ': ' . ldap_error($this->ldap);
		}
		
		return $return;
	}

	function check_access_to_renamed($uid)
	{
		$justthese = array("dn");
		$filter="(&(phpgwAccountType=u)(uid=$uid))";
		
		foreach ($this->manager_contexts as $index=>$context)
		{
			$search = ldap_search($this->ldap, $context, $filter, $justthese);
			$entry = ldap_get_entries($this->ldap, $search);
			if ($entry['count'])
				return true;
		}
	    return false;
	}

	function check_rename_new_uid($uid)
	{
		if ( !$ldapMasterConnect = $this->ldapMasterConnect() )
			return false;
		
		$justthese = array("dn");
		$filter="(&(phpgwAccountType=u)(uid=$uid))";
		
		$search = ldap_search($ldapMasterConnect, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
		$count_entries = @ldap_count_entries($ldapMasterConnect, $search);
		
		if ($count_entries)
			return false;
			
		return true;
	}
	
	function rename_uid($uid, $new_uid)
	{
		$return['status'] = true;
		
		$justthese = array("dn");
		$filter="(&(phpgwAccountType=u)(uid=".$uid."))";
		$search = ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
	    $entry = ldap_get_entries($this->ldap, $search);
		$dn = $entry[0]['dn'];
		
		$explode_dn = ldap_explode_dn($dn, 0);
		$rdn = "uid=" . $new_uid;

		$parent = array();
		for ($j=1; $j<(count($explode_dn)-1); ++$j)
			$parent[] = $explode_dn[$j];
		$parent = implode(",", $parent);
		
		$return['new_dn'] = $rdn . ',' . $parent;
			
		if (!@ldap_rename($this->ldap, $dn, $rdn, $parent, true))
		{
			$return['status'] = false;
			$result['msg'] = $this->functions->lang('Error on function') . " ldap_functions->rename_uid ($dn)" . ".\n" . $this->functions->lang('Server returns') . ': ' . ldap_error($this->ldap);
		}
		
		//Grupos
		$justthese = array("dn");
		$filter="(&(phpgwAccountType=g)(memberuid=".$uid."))";
		$search = ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
	    $entry = ldap_get_entries($this->ldap, $search);
    	$array_mod_add['memberUid'] = $new_uid;
    	$array_mod_del['memberUid'] = $uid;

	    for ($i=0; $i<=$entry['count']; ++$i)
	    {
	    	$dn = $entry[$i]['dn'];
	    	@ldap_mod_add ( $this->ldap, $dn,  $array_mod_add);
	    	@ldap_mod_del ( $this->ldap, $dn,  $array_mod_del);
	    }
		return $return;
	}

	function rename_cn($cn, $new_cn)
	{
		$return['status'] = true;
		
		$justthese = array("dn");
		$filter="(&(phpgwAccountType=g)(uid=".$cn."))";
		$search = ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
	    $entry = ldap_get_entries($this->ldap, $search);
		$dn = $entry[0]['dn'];
		
		$explode_dn = ldap_explode_dn($dn, 0);
		$rdn = "cn=" . $new_cn;

		$parent = array();
		for ($j=1; $j<(count($explode_dn)-1); ++$j)
			$parent[] = $explode_dn[$j];
		$parent = implode(",", $parent);
		
		$return['new_dn'] = $rdn . ',' . $parent;
			
		if (!@ldap_rename($this->ldap, $dn, $rdn, $parent, false))
		{
			$return['status'] = false;
		}
		
		return $return;
	}
	
	function exist_sambadomains($contexts, $sambaDomainName)
	{
		$justthese = array("dn");
		$filter="(&(objectClass=sambaDomain)(sambaDomainName=$sambaDomainName))";
		
		foreach ($contexts as $index=>$context)
		{
			$search = ldap_search($this->ldap, $context, $filter, $justthese);
		    $entry = ldap_get_entries($this->ldap, $search);
	    
			if ($entry['count'])
				return true;
		}
		return false;
	}
	
	// Primeiro nilvel de organização.
	function exist_sambadomains_in_context($params)
	{
		$dn = $GLOBALS['phpgw_info']['server']['ldap_context'];
		$array_dn = ldap_explode_dn ( $dn, 0 );
		
		$context = $params['context'];
		$array_context = ldap_explode_dn ( $context, 0 );
		
		// Pego o setor no caso do contexto ser um sub-setor.
		if (($array_dn['count']+1) < ($array_context['count']))
		{
			// inverto o array_dn para poder retirar o count
			$array_dn_reverse  = array_reverse ( $array_dn, false );
			
			//retiro o count
			array_pop($array_dn_reverse);
			
			//incluo o setor no dn
			array_push ( $array_dn_reverse,  $array_context[ $array_context['count'] - 1 - $array_dn['count']]);
			
			// Volto a ordem natural
			$array_dn  = array_reverse ( $array_dn_reverse, false );
			
			// Implodo
			$context = implode ( ",", $array_dn );
		}
		
		$justthese = array("dn","sambaDomainName");
		$filter="(objectClass=sambaDomain)";
		$search = ldap_list($this->ldap, $context, $filter, $justthese);
	    $entry = ldap_get_entries($this->ldap, $search);
	    
   	    for ($i=0; $i<$entry['count']; ++$i)
	    {
			$return['sambaDomains'][$i] = $entry[$i]['sambadomainname'][0];
	    }
	    
		if ($entry['count'])
			$return['status'] = true;
		else
			$return['status'] = false;
			
		return $return;
	}
	function exist_domain_name_sid($sambadomainname, $sambasid)
	{
		$context = $GLOBALS['phpgw_info']['server']['ldap_context'];

		$justthese = array("dn","sambaDomainName");
		$filter="(&(objectClass=sambaDomain)(sambaSID=$sambasid)(sambaDomainName=$sambadomainname))";
		$search = ldap_search($this->ldap, $context, $filter, $justthese);
	    $count_entries = ldap_count_entries($this->ldap, $search);
		
	    if ($count_entries > 0)
	    	return true;
	    else
	    	return false;
		}
		
	function add_sambadomain($sambadomainname, $sambasid, $context)
	{
		$result = array();
		
		$dn 								= "sambaDomainName=$sambadomainname,$context";
		$entry['sambaSID'] 					= $sambasid;
		$entry['objectClass'] 				= 'sambaDomain';
		$entry['sambaAlgorithmicRidBase']	= '1000';
		$entry['sambaDomainName']			= $sambadomainname;
		
		if (!@ldap_add ( $this->ldap, $dn, $entry ))
			{
			$return['status'] = false;
			$result['msg'] = $this->functions->lang('Error on function') . " ldap_functions->add_sambadomain ($dn)" . ".\n" . $this->functions->lang('Server returns') . ': ' . ldap_error($this->ldap);
		}
		else
			$return['status'] = true;
		
		return $return;
	}
		
	function delete_sambadomain($sambadomainname)
	{
		$return['status'] = true;
		$filter="(sambaDomainName=$sambadomainname)";
		$search = ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter);
	    $entry = ldap_get_entries($this->ldap, $search);
		
	 	if ($entry['count'] != 0)
		{
			$dn = $entry[0]['dn'];
			
			if (!@ldap_delete($this->ldap, $dn))
			{
				$return['status'] = false;
				$result['msg'] = $this->functions->lang('Error on function') . " ldap_functions->delete_sambadomain ($sambadomainname)" . ".\n" . $this->functions->lang('Server returns') . ': ' . ldap_error($this->ldap);
			}
		}
		
		return $return;
	}
	
	function search_user($params)
		{
             $ldapService = ServiceLocator::getService('ldap');
             $entries = $ldapService->accountSearch($params['search'], array('cn','uid', "mail"), $params['context'], 'u', 'cn');

             if (count($entries) == 0)
             {
                    $return['status'] = 'false';
                    $return['msg'] = $this->functions->lang('Any result was found') . '.';
                    return $return;
		}
             $options = '';

             foreach ($entries as  $value)
                 $options .= '<option value='.$value['uid'].'>'.$value['cn'].'('.$value['mail'].')'.'</option>';
    
    	return $options;		
	}
	
	function get_institutional_accounts($params)
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
	    	$institutional_accounts = ldap_search($this->ldap, $context, ("(&(phpgwAccountType=i)(|(mail=$input*)(cn=*$input*)))"), $justthese);
    		$entries = ldap_get_entries($this->ldap, $institutional_accounts);
    	    	
			for ($i=0; $i<$entries['count']; ++$i)
			{
				$tr = "<tr class='normal' onMouseOver=this.className='selected' onMouseOut=this.className='normal'><td onClick=edit_institutional_account('".$entries[$i]['uid'][0]."')>" . utf8_decode($entries[$i]['cn'][0]) . "</td><td onClick=edit_institutional_account('".$entries[$i]['uid'][0]."')>" . $entries[$i]['mail'][0] . "</td><td align='center' onClick=delete_institutional_accounts('".$entries[$i]['uid'][0]."')><img HEIGHT='16' WIDTH='16' src=./expressoAdmin1_2/templates/default/images/delete.png></td></tr>";
				$trs[$tr] = utf8_decode($entries[$i]['cn'][0]);
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
	
	function get_institutional_account_data($params)
	{
		if (!$this->functions->check_acl($_SESSION['phpgw_info']['expresso']['user']['account_lid'], 'edit_institutional_accounts'))
		{
			$return['status'] = false;
			$return['msg'] = $this->functions->lang('You do not have right to edit institutional accounts') . ".";
			return $return;
		}
		
		$uid = $params['uid'];
		//$justthese = array("accountStatus", "phpgwAccountVisible", "cn", "mail", "mailForwardingAddress", "description");
				
    	$institutional_accounts = ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_context'], ("(&(phpgwAccountType=i)(uid=$uid))"));
    	$entrie = ldap_get_entries($this->ldap, $institutional_accounts);
		
		if ($entrie['count'] != 1)
		{
			$return['status'] = 'false';
			$result['msg'] = $this->functions->lang('Problems loading datas') . '.';
		}
		else
		{
			$tmp_user_context = preg_split('/,/', utf8_decode($entrie[0]['dn']));
			$tmp_reverse_user_context = array_reverse($tmp_user_context);
			array_pop($tmp_reverse_user_context);
			$return['user_context'] = implode(",", array_reverse($tmp_reverse_user_context));
			
			$return['status'] = 'true';
			$return['accountStatus']		= $entrie[0]['accountstatus'][0];
			$return['phpgwAccountVisible']	= $entrie[0]['phpgwaccountvisible'][0];
			$return['cn']					= utf8_decode($entrie[0]['cn'][0]);
			$return['mail']					= $entrie[0]['mail'][0];
			$return['description']			= utf8_decode($entrie[0]['description'][0]);

			if ($entrie[0]['mailforwardingaddress']['count'] > 0)
			{
				$a_cn = array();
				for ($i=0; $i<$entrie[0]['mailforwardingaddress']['count']; ++$i)
				{
					$tmp = $this->mailforwardingaddress2uidnumber($entrie[0]['mailforwardingaddress'][$i]);
					if (!$tmp) {}
					else
						$a_cn[$tmp['uidnumber']] = $tmp['cn'].'('.$tmp['uid'].')';
				}
				natcasesort($a_cn);
				foreach($a_cn as $uidnumber => $cn)
				{
					$return['owners'] .= '<option value='. $uidnumber .'>' . $cn . '</option>';
				}
			}
		}
		
		return $return;
	}
	function get_shared_accounts($params)
		{
		if (!$this->functions->check_acl($_SESSION['phpgw_info']['expresso']['user']['account_lid'], 'list_shared_accounts'))
		{
			$return['status'] = false;
			$return['msg'] = $this->functions->lang('You do not have right to list shared accounts') . ".";
			return $return;
		}

		$input = $params['input'];
		$justthese = array("cn", "dn", "mail", "uid");
		$trs = array();
				
		foreach ($this->manager_contexts as $idx=>$context)
		{
	    	$institutional_accounts = ldap_search($this->ldap, $context, ("(&(phpgwAccountType=s)(|(mail=$input*)(cn=*$input*)))"), $justthese);
    		$entries = ldap_get_entries($this->ldap, $institutional_accounts);
    	    	
			for ($i=0; $i<$entries['count']; ++$i)
			{
				$tr = "<tr class='normal' onMouseOver=this.className='selected' onMouseOut=this.className='normal'><td onClick=edit_shared_account('".$entries[$i]['uid'][0]."')>" . utf8_decode($entries[$i]['cn'][0]) . "</td><td onClick=edit_shared_account('".$entries[$i]['uid'][0]."')>" . utf8_decode($entries[$i]['cn'][0]). " (" . $entries[$i]['uid'][0] . ")" . "</td><td onClick=edit_shared_account('".$entries[$i]['uid'][0]."')>" . $entries[$i]['mail'][0] . "<td align='center' onClick=delete_shared_accounts('".$entries[$i]['uid'][0]."','".$entries[$i]['mail'][0]."')><img HEIGHT='16' WIDTH='16' src=./expressoAdmin1_2/templates/default/images/delete.png></td></tr>";
				$trs[$tr] = utf8_decode($entries[$i]['cn'][0]);
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
	
	function get_shared_account_data($params)
	{
		if (!$this->functions->check_acl($_SESSION['phpgw_info']['expresso']['user']['account_lid'], 'edit_shared_accounts'))
		{
			$return['status'] = false;
			$return['msg'] = $this->functions->lang('You do not have right to edit an shared accounts') . ".";
			return $return;
		}
		
		$uid = $params['uid'];
		//$justthese = array("accountStatus", "phpgwAccountVisible", "cn", "mail", "mailForwardingAddress", "description");
				
    	$shared_accounts = ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_context'], ("(&(phpgwAccountType=s)(uid=$uid))"));
    	$entrie = ldap_get_entries($this->ldap, $shared_accounts);
		
		if ($entrie['count'] != 1)
		{
			$return['status'] = 'false';
			$result['msg'] = $this->functions->lang('Problems loading datas') . '.';
		}
		else
		{
			$tmp_user_context = preg_split('/,/', $entrie[0]['dn']);
			$tmp_reverse_user_context = array_reverse($tmp_user_context);
			array_pop($tmp_reverse_user_context);
			$return['user_context'] = implode(",", array_reverse($tmp_reverse_user_context));

			$return['status'] = 'true';
			$return['accountStatus']		= isset($entrie[0]['accountstatus']) ? $entrie[0]['accountstatus'][0] : null;
			$return['phpgwAccountVisible']	= isset($entrie[0]['phpgwaccountvisible']) ? $entrie[0]['phpgwaccountvisible'][0] : null;
			$return['cn']			= utf8_decode($entrie[0]['cn'][0]);
			$return['mail']					= $entrie[0]['mail'][0];
			$return['description']			= utf8_decode($entrie[0]['description'][0]);
			$return['dn']			= utf8_decode($entrie[0]['dn']);
			$return['mailalternateaddress']	= isset($entrie[0]['mailalternateaddress']) ?  $entrie[0]['mailalternateaddress'] : null;
		}

		return $return;
	}		
	function mailforwardingaddress2uidnumber($mail)
	{
		$justthese = array("uidnumber","cn", "uid");
    	$search = ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_context'], ("(&(phpgwAccountType=u)(mail=$mail))"), $justthese);
    	$entrie = ldap_get_entries($this->ldap, $search);
		if ($entrie['count'] != 1)
			return false;
		else
		{
			$return['uidnumber'] = $entrie[0]['uidnumber'][0];
			$return['cn'] = utf8_decode($entrie[0]['cn'][0]);
			$return['uid'] = $entrie[0]['uid'][0];
			return $return;
		}
	}

	function uid2mailforwardingaddress($uid)
	{
		$justthese = array("mail");
    	$search = ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_context'], ("(&(phpgwAccountType=u)(uid=$uid))"), $justthese);
    	$entrie = ldap_get_entries($this->ldap, $search);
		if ($entrie['count'] != 1)
			return false;
		else
		{
			$return['mail'] = $entrie[0]['mail'][0];
			return $return;
		}
	}
	
	function delete_institutional_account_data($params)
	{
		if (!$this->functions->check_acl($_SESSION['phpgw_info']['expresso']['user']['account_lid'], 'remove_institutional_accounts'))
		{
			$return['status'] = false;
			$return['msg'] = $this->functions->lang('You do not have right to delete institutional accounts') . ".";
			return $return;
		}

		$uid = $params['uid'];
		$return['status'] = true;
				
		$justthese = array("cn");
    	$search = ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_context'], ("(&(phpgwAccountType=i)(uid=$uid))"), $justthese);
    	$entrie = ldap_get_entries($this->ldap, $search);
		if ($entrie['count'] > 1)
		{
			$return['status'] = false;
			$return['msg']  = $this->functions->lang('More then one uid was found');
			return $return;
		}		
		if ($entrie['count'] == 0)
		{
			$return['status'] = false;
			$return['msg']  = $this->functions->lang('No uid was found');
			return $return;
		}		
		
		$dn = utf8_decode($entrie[0]['dn']);
		if (!@ldap_delete($this->ldap, $dn))
		{
			$return['status'] = false;
			$return['msg']  = $this->functions->lang('Error on function') . " ldap_functions->delete_institutional_accounts: ldap_delete";
			$return['msg'] .= "\n" . $this->functions->lang('Server return') . ': ' . ldap_error($this->ldap);
			return $return;
		}

        $this->db_functions->write_log('Removed institutional account',$dn);

		return $return;
	}
	
	function replace_mail_from_institutional_account($newMail, $oldMail)
	{
		$filter = "(&(phpgwAccountType=i)(mailforwardingaddress=$oldMail))";
		$justthese = array("dn");
		$search = ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
		$entries = ldap_get_entries($this->ldap, $search);
		$result['status'] = true;
		for ($i=0; $i<$entries['count']; ++$i)
		{
			$attrs['mailforwardingaddress'] = $oldMail;
			$res1 = @ldap_mod_del($this->ldap, $entries[$i]['dn'], $attrs);
			$attrs['mailforwardingaddress'] = $newMail;
			$res2 = @ldap_mod_add($this->ldap, $entries[$i]['dn'], $attrs);
		
			if ((!$res1) || (!$res2))
			{
				$result['status'] = false;
				$return['msg']  = $this->functions->lang('Error on function') . " ldap_functions->replace_mail_from_institutional_account.";
			}
		}
		
		return $result;
	}
        function delete_shared_account_data($params)
	{
		if (!$this->functions->check_acl($_SESSION['phpgw_info']['expresso']['user']['account_lid'], 'delete_shared_accounts'))
		{
			$return['status'] = false;
			$return['msg'] = $this->functions->lang('You do not have right to delete shared accounts') . ".";
			return $return;
		}                
		$uid = $params['uid'];
		$return['status'] = true;

		$justthese = array("cn");
    	$search = ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_context'], "(&(phpgwAccountType=s)(uid=$uid))", $justthese);

    	$entrie = ldap_get_entries($this->ldap, $search);
        
		if ($entrie['count'] > 1)
		{
			$return['status'] = false;
			$return['msg']  = $this->functions->lang('More then one uid was found');
			return $return;
		}
		if ($entrie['count'] == 0)
		{
			$return['status'] = false;
			$return['msg']  = $this->functions->lang('No uid was found');
			return $return;
		}

		$dn = $entrie[0]['dn'];
		if (!@ldap_delete($this->ldap, $dn))
		{
			$return['status'] = false;
			$return['msg']  = $this->functions->lang('Error on function') . " ldap_functions->delete_shared_accounts: ldap_delete";
			$return['msg'] .= "\n" . $this->functions->lang('Server return') . ': ' . ldap_error($this->ldap);
			return $return;
		}

		return $return;
	}		

}
?>
