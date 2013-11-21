<?php
define('PHPGW_INCLUDE_ROOT','../');
define('PHPGW_API_INC','../phpgwapi/inc');	
include_once(PHPGW_API_INC.'/class.common.inc.php');

class ldap_functions
{
	var $ldap;
	var $ldap_write;
	var $current_config;
	
	function ldap_functions()
		{
		$GLOBALS['phpgw_info']['server'] = $_SESSION['phpgw_info']['expresso']['server'];
		$this->current_config = $_SESSION['phpgw_info']['expresso']['listAdmin'];
		$common = new common();
		$this->ldap = $common->ldapConnect();
		//Para haver escrita em diretorio escravo eh necessario que se faca login na api com a opcao de seguir referrals. Foi feita aqui a divisao entre escrita e leitura pois a leitura ldap fica bem mais lenta quando configurada para seguir referrals.
                /*
		if($GLOBALS['phpgw_info']['server']['diretorioescravo'])
			{
			$this->ldap_write = $common->ldapConnect("","","",true);
			}
			else
			{
			$this->ldap_write = $this->ldap;
			}

                 *
                 */
                if ( (!empty($GLOBALS['phpgw_info']['server']['ldap_master_host'])) &&
			 (!empty($GLOBALS['phpgw_info']['server']['ldap_master_root_dn'])) &&
			 (!empty($GLOBALS['phpgw_info']['server']['ldap_master_root_pw'])) )
                        {
                            $this->ldap_write = $common->ldapConnect($GLOBALS['phpgw_info']['server']['ldap_master_host'],
											   $GLOBALS['phpgw_info']['server']['ldap_master_root_dn'],
											   $GLOBALS['phpgw_info']['server']['ldap_master_root_pw']);
                        }
                        else
                        {
                            $this->ldap_write = $common->ldapConnect();
                        }

                 }
	function validate_fields($params)
	{
		$params = unserialize($params['attributes']);
		$type = $params['type'];
		$context = $GLOBALS['phpgw_info']['server']['ldap_context'];
		$uid = $params['uid'];
		$mail = $params['mail'];
		$mailalternateaddress = $params['mailalternateaddress'];

		$result['status'] = true;
		
		if ($_SESSION['phpgw_info']['expresso']['global_denied_users'][$uid])
		{
			$result['status'] = false;
			$result['msg'] = 'Este LOGIN n�o pode ser usado pois � uma conta de sistema.';
			return $result;
		}
		
		if (($type == 'create_user') || ($type == 'rename_user')) 
		{
			// UID
			$filter = "(&(|(phpgwAccountType=u)(phpgwAccountType=l))(uid=$uid))";
			$justthese = array("uid");
			$search = ldap_search($this->ldap, $context, $filter, $justthese);
			$count_entries = ldap_count_entries($this->ldap,$search);
			if ($count_entries > 0)
			{
				$result['status'] = false;
				$result['msg'] = 'LOGIN j� esta sendo usado.';
				return $result;
			}

			// GRUPOS
			$filter = "(&(phpgwAccountType=g)(cn=$uid))";
			$justthese = array("cn");
			$search = ldap_search($this->ldap, $context, $filter, $justthese);
			$count_entries = ldap_count_entries($this->ldap,$search);
			if ($count_entries > 0)
			{
				$result['status'] = false;
				$result['msg'] = 'LOGIN do usu�rio j� esta sendo usado por um grupo.';
				return $result;
			}
		
			// UID em outras organiza��es
			//Quando tento retirar as organiza��es pelo expressoAdmin d� este erro.
			$filter = "(ou=*)";
			$justthese = array("ou");
			$search = ldap_list($this->ldap, $context, $filter, $justthese);
			$entries = ldap_get_entries($this->ldap,$search);
			foreach ($entries as $index=>$org)
			{
				$organization = $org['ou'][0];
				$organization = strtolower($organization);
				
				$filter = "(&(|(phpgwAccountType=u)(phpgwAccountType=l))(uid=$organization-$uid))";
				$justthese = array("uid");
				$search = ldap_search($this->ldap, $context, $filter, $justthese);
				$count_entries = ldap_count_entries($this->ldap,$search);
				if ($count_entries > 0)
				{
					$result['status'] = false;
					$result['msg'] = 'LOGIN j� esta sendo usado por outro usu�rio em outra organiza��o.';
					return $result;
				}
			}
		}
		
		if ($type == 'rename_user')
		{
			return $result;
		}
		
		// MAIL
		$filter = "(&(|(phpgwAccountType=u)(phpgwAccountType=l))(|(mail=$mail)(mailalternateaddress=$mail)))";
		$justthese = array("mail", "uid");
		$search = ldap_search($this->ldap, $context, $filter, $justthese);
		$entries = ldap_get_entries($this->ldap,$search);
		if ($entries['count'] == 1){
			if ($entries[0]['uid'][0] != $uid){
				$result['status'] = false;
				$result['msg'] = 'E-MAIL est� sendo usado por 1 usu�rio: ' . $entries[0]['uid'][0];
				//ldap_close($this->ldap);
				return $result;
			}
		}
		else if ($entries['count'] > 1){
			$result['status'] = false;
			$result['msg'] = 'E-MAIL est� sendo usado por de 2 ou mais usu�rios.';
			//ldap_close($this->ldap);
			return $result;
		}
		
		// MAILAlternateAddress
		$filter = "(&(|(phpgwAccountType=u)(phpgwAccountType=l))(|(mail=$mailalternateaddress)(mailalternateaddress=$mailalternateaddress)))";
		$justthese = array("mail", "uid");
		$search = ldap_search($this->ldap, $context, $filter, $justthese);
		$entries = ldap_get_entries($this->ldap,$search);
		if ($entries['count'] == 1){
			if ($entries[0]['uid'][0] != $uid){
				$result['status'] = false;
				$result['msg'] = "E-MAIL alternativo est� sendo usado por 1 usu�rio: " . $entries[0]['uid'][0];
				//ldap_close($this->ldap);
				return $result;
			}
		}
		else if ($entries['count'] > 1){
			$result['status'] = false;
			$result['msg'] = 'E-MAIL alternativo est� sendo usado por 2 ou mais usu�rios.';
			return $result;
		}

		return $result;
	}
	
	function validate_fields_maillist($params)
	{
		$context = $GLOBALS['phpgw_info']['server']['ldap_context'];
		$uid = $params['uid'];
		$mail = $params['mail'];
		$result['status'] = true;
		
		if ($_SESSION['phpgw_info']['expresso']['global_denied_users'][$uid])
		{
			$result['status'] = false;
			$result['msg'] = 'Este LOGIN n�o pode ser usado pois � uma conta de sistema.';
			return $result;
		}
		
		// UID
		$filter = "(&(phpgwAccountType=l)(uid=$uid))";
		$justthese = array("uid");
		$search = ldap_search($this->ldap, $context, $filter, $justthese);
		$count_entries = ldap_count_entries($this->ldap,$search);
		if ($count_entries > 0)
		{
			$result['status'] = false;
			$result['msg'] = 'LOGIN da lista j� est� sendo usado.';
			return $result;
		}
		
		// MAIL
		/*
		$filter = "(&(|(phpgwAccountType=u)(phpgwAccountType=l))(|(mail=$mail)(mailalternateaddress=$mail)))";
		$justthese = array("mail");
		$search = ldap_search($this->ldap, $context, $filter, $justthese);
		$count_entries = ldap_count_entries($this->ldap,$search);
		if ($count_entries > 0)
		{
			$result['status'] = false;
			$result['msg'] = 'E-MAIL da lista j� est� sendo usado.';
			return $result;
		}*/
		
		return $result;	
	}

	//Busca usu�rios de um contexto e j� retorna as options do select;
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

	//Busca usu�rios e listas de um contexto e j� retorna as options do select;
	function get_available_users_and_maillist($params)
	{

		$context = $params['context'];
		$recursive = $params['recursive'];
		
		//Usado para retirar a pr�pria lista das possibilidades de inclus�o.
		$denied_uidnumber = $params['denied_uidnumber'];
		
		$justthese = array("cn", "uidNumber");
		$users_filter="(phpgwAccountType=u)";
		$lists_filter = $denied_uidnumber == '' ? "(phpgwAccountType=l)" : "(&(phpgwAccountType=l)(!(uidnumber=$denied_uidnumber)))";
		
		$users = Array();
		$lists = Array();		

		if ($recursive == 'true')
		{
			$lists_search = ldap_search($this->ldap, $context, $lists_filter, $justthese);
			$users_search = ldap_search($this->ldap, $context, $users_filter, $justthese);
		}
		else
		{
			$lists_search = ldap_list($this->ldap, $context, $lists_filter, $justthese);
			$users_search = ldap_list($this->ldap, $context, $users_filter, $justthese);
		}
		
		$lists_entries = ldap_get_entries($this->ldap, $lists_search);
		for ($i=0; $i<$lists_entries["count"]; ++$i)
		{
			$l_tmp[$lists_entries[$i]["uidnumber"][0]] = $lists_entries[$i]["cn"][0];
		}
			
		if (count($l_tmp))
			natcasesort($l_tmp);
			
		$i = 0;
		$lists = array();
		
		$options .= '<option  value="-1" disabled>------------------------------&nbsp;&nbsp;&nbsp;&nbsp;Listas&nbsp;&nbsp;&nbsp;&nbsp;------------------------------ </option>'."\n";	
		if (count($l_tmp))
		{
			foreach ($l_tmp as $uidnumber => $cn)
			{
				$options .= "<option value=$uidnumber>$cn</option>";
			}
			unset($l_tmp);
		}
		
		$users_entries = ldap_get_entries($this->ldap, $users_search);
		for ($i=0; $i<$users_entries["count"]; ++$i)
		{
			$u_tmp[$users_entries[$i]["uidnumber"][0]] = $users_entries[$i]["cn"][0];
		}
			
		if (count($u_tmp))
			natcasesort($u_tmp);
			
		$i = 0;
		$users = array();
		
		$options .= '<option  value="-1" disabled>-----------------------------&nbsp;&nbsp;&nbsp;&nbsp;Usuarios&nbsp;&nbsp;&nbsp;&nbsp;---------------------------- </option>'."\n";	
		if (count($u_tmp))
		{
			foreach ($u_tmp as $uidnumber => $cn)
			{
				$options .= "<option value=$uidnumber class='line-above'>$cn</option>";
			}
			unset($u_tmp);
		}
			
   		return $options;
	}

	//Funcao que busca apenas os usuarios de um contexto e ja retorna as options do select;
	function get_available_users_only($params)
	{

		$context = $params['context'];
		$recursive = $params['recursive'];
		$filtro = $params['filtro'];
		
		$justthese = array("cn", "uidNumber");
//		$users_filter="(phpgwAccountType=u)";
$users_filter="(&(phpgwAccountType=u)(cn=*$filtro*))";
		
		$users = Array();

		if ($recursive == 'true')
		{
			$users_search = ldap_search($this->ldap, $context, $users_filter, $justthese);
		}
		else
		{
			$users_search = ldap_list($this->ldap, $context, $users_filter, $justthese);
		}
		
		$users_entries = ldap_get_entries($this->ldap, $users_search);
		for ($i=0; $i<$users_entries["count"]; ++$i)
		{
			$u_tmp[$users_entries[$i]["uidnumber"][0]] = $users_entries[$i]["cn"][0];
		}
			
		if (count($u_tmp))
			natcasesort($u_tmp);
			
		$i = 0;
		$users = array();
		
		$options .= '<option  value="-1" disabled>-----------------------------&nbsp;&nbsp;&nbsp;&nbsp;Usuarios&nbsp;&nbsp;&nbsp;&nbsp;---------------------------- </option>'."\n";	
		if (count($u_tmp))
		{
			foreach ($u_tmp as $uidnumber => $cn)
			{
				$options .= "<option value=$uidnumber class='line-above'>$cn</option>";
			}
			unset($u_tmp);
		}
			
   		return $options;
	}


	function is_user_listAdmin($uid)
	{
		$cn_listadmin = $this->current_config['name_listadmin'];
		$justthese = array("memberUid");
		//$filter = "(&(phpgwAccountType=g)(cn=listadmin)(memberuid=$uid))";
		$filter = "(&(phpgwAccountType=g)(cn=$cn_listadmin)(memberuid=$uid))";
		$dn = $this->current_config['dn_listadmin'];
		$search = ldap_search($this->ldap,$dn,$filter, $justthese);
		$entry = ldap_get_entries($this->ldap, $search);
		$i = 0;
		for($i = 0; $i <= $entry[0]['memberuid']['count']; ++$i)
		{
			if($entry[0]['memberuid'][$i] == $uid)
			{
				$return = 1;
			}
		}
		return $return;
	}





	function search_users_only($params)
	{

		$context = $GLOBALS['phpgw_info']['server']['ldap_context']; //$params['context'];
		$filtro = $params['filtro'];
		$tipo = $params['tipo'];
		//      $recursive = $params['recursive'];

		$justthese = array("cn", "uidNumber", "mail");

		if($tipo == "adm_maillist")
		{
			$users_filter="(&(phpgwAccountType=u)(phpgwAccountStatus=A)(|(cn=*$filtro*)(mail=*$filtro*)))";
		}
		else
		{
			$users_filter="(&(phpgwAccountStatus=A)(|(cn=*$filtro*)(mail=*$filtro*)))";
		}

		$users = Array();

	//      if ($recursive == 'true')
	//      {
		$users_search = ldap_search($this->ldap, $context, $users_filter, $justthese);
	//      }
	//      else
	//      {
	//              $users_search = ldap_list($this->ldap, $context, $users_filter, $justthese);
	//      }

		$users_entries = ldap_get_entries($this->ldap, $users_search);
		for ($i=0; $i<$users_entries["count"]; ++$i)
		{
				$u_tmp[$users_entries[$i]["uidnumber"][0]] = $users_entries[$i]["cn"][0] . " [" .$users_entries[$i]["mail"][0] . "]";
		}

		if(count($u_tmp) == 0) {
			$options .= '<option  value="-1" disabled>------&nbsp;&nbsp;&nbsp;USUARIO INVALIDO OU NAO CRIADO NO EXPRESSO&nbsp;&nbsp;&nbsp;------</option>'."\n";
		}

		if (count($u_tmp))
			natcasesort($u_tmp);

		$i = 0;
		$users = array();

		$options .= '<option  value="-1" disabled>-----------------------------&nbsp;&nbsp;&nbsp;&nbsp;Usuarios&nbsp;&nbsp;&nbsp;&nbsp;---------------------------- </option>'."\n";
		if (count($u_tmp))
		{
			foreach ($u_tmp as $uidnumber => $cn)
			{
				$options .= "<option value=$uidnumber class='line-above'>$cn</option>";
			}

			unset($u_tmp);
		}

		return $options;
	}


	function get_available_groups($params)
	{
	$context = $params['context'];
	$justthese = array("cn", "gidNumber");
    	$groups_list=ldap_search($this->ldap, $context, ("(phpgwAccountType=g)"), $justthese);
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
		$context = $params['context'];
		$justthese = array("uid","mail","uidNumber");
    	$maillists=ldap_search($this->ldap, $context, ("(phpgwAccountType=l)"), $justthese);
    	ldap_sort($this->ldap, $maillists, "uid");
    	
    	$entries = ldap_get_entries($this->ldap, $maillists);
    	
		$options = '';			
		for ($i=0; $i<$entries['count']; ++$i)
		{
			$options .= "<option value=" . $entries[$i]['uidnumber'][0] . ">" . $entries[$i]['uid'][0] . " </option>";
		}
    	return $options;		
	}
	
	function ldap_add_entry($dn, $entry)
	{
		$result = array();
		if (!@ldap_add ($this->ldap_write, $dn, $entry ))
		{
			$result['status'] = false;
			$result['msg'] = "Erro na funcao ldap_functions->ldap_add_entry ($dn).\nRetorno do servidor:" . ldap_error($this->ldap_write);
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
			$res = @ldap_mod_replace($this->ldap_write, $dn, $attrs);
		else
			$res = @ldap_mod_add($this->ldap_write, $dn, $attrs);
			
		if ($res)
		{
			$result['status'] = true;
		}
		else
		{
			$result['status'] = false;
			$result['msg'] = "Erro na funcao ldap_functions->ldap_save_photo ($dn).\nRetorno do servidor:" . ldap_error($this->ldap_write);
		}
		return $result;
	}
	
	function ldap_remove_photo($dn)
	{
		$attrs['jpegPhoto'] = array();
		$res = ldap_mod_del($this->ldap_write, $dn, $attrs);
		if ($res)
		{
			$result['status'] = true;
		}
		else
		{
			$result['status'] = false;
			$result['msg'] = "Erro na funcao ldap_functions->ldap_remove_photo ($dn).\nRetorno do servidor:" . ldap_error($this->ldap_write);
		}
		return $result;
	}	
	
	// Pode receber tanto um �nico memberUid quanto um array de memberUid's
	function add_user2group($gidNumber, $memberUid)
	{
		$filter = "(&(phpgwAccountType=g)(gidNumber=$gidNumber))";
		$justthese = array("dn");
		$search = ldap_search($this->ldap_write, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
		$entry = ldap_get_entries($this->ldap_write, $search);
		$group_dn = $entry[0]['dn'];
		$attrs['memberUid'] = $memberUid;
		
		$res = @ldap_mod_add($this->ldap_write, $group_dn, $attrs);
		
		if ($res)
		{
			$result['status'] = true;
		}
		else
		{
			$result['status'] = false;
			$result['msg'] = "Erro na funcao ldap_functions->add_user2group ($memberUid).\nRetorno do servidor:" . ldap_error($this->ldap_write);
		}
		return $result;
	}
	
	function remove_user2group($gidNumber, $memberUid)
	{
		$filter = "(&(phpgwAccountType=g)(gidNumber=$gidNumber))";
		$justthese = array("dn");
		$search = ldap_search($this->ldap_write, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
		$entry = ldap_get_entries($this->ldap_write, $search);
		$group_dn = $entry[0]['dn'];
		$attrs['memberUid'] = $memberUid;
		$res = @ldap_mod_del($this->ldap_write, $group_dn, $attrs);
		
		/*echo 'usuarios recebidos para remocao no ldap';
		echo '<pre>';
		print_r($memberUid);*/
		
		if ($res)
		{
			$result['status'] = true;
		}
		else
		{
			$result['status'] = false;
			$result['msg'] = "Erro na funcao ldap_functions->remove_user2group ($memberUid).\nRetorno do servidor:" . ldap_error($this->ldap_write);
		}
		return $result;
	}
	
	function add_user2maillist($uidNumber, $mail)
	{
		$filter = "(&(phpgwAccountType=l)(uidNumber=$uidNumber))";
		$justthese = array("dn");
		$search = ldap_search($this->ldap_write, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
		$entry = ldap_get_entries($this->ldap_write, $search);
		$group_dn = $entry[0]['dn'];
		echo "teste...";

		$attrs['mailForwardingAddress'] = $mail;
		$res = ldap_mod_add($this->ldap_write, $group_dn, $attrs);
                
		if ($res)
		{
			$result['status'] = true;
		}
		else
		{
			$result['status'] = false;
			$result['msg'] = "Erro na funcao ldap_functions->add_user2maillist ($mail).\nRetorno do servidor:" . ldap_error($this->ldap_write);
		}
		return $result;
	}
	
	function add_user2maillist_adm($uidNumber, $mail)
        {
		$result['msg'] = "Entre adiciona adm";
		$filter = "(&(phpgwAccountType=l)(uidNumber=$uidNumber))";
		$justthese = array("dn");
		$search = ldap_search($this->ldap_write, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
		$entry = ldap_get_entries($this->ldap_write, $search);
		$group_dn = $entry[0]['dn'];

		$attrs['admlista'] = $mail;
		$res = ldap_mod_add($this->ldap_write, $group_dn, $attrs);

		if ($res)
		{
				$result['status'] = true;
		}
		else
		{
				$result['status'] = false;
				$result['msg'] = "Erro na funcao ldap_functions->add_user2maillist ($mail).\nRetorno do servidor:" . ldap_error($this->ldap_write);
		}
		return $result;
        }


	
	function add_user2maillist_scl($dn, $array_emails)
	{
		$attrs['naomoderado'] = $array_emails;
		$res = @ldap_mod_add($this->ldap_write, $dn, $attrs);
		if ($res)
		{
			$result['status'] = true;
		}
		else
		{
			$result['status'] = false;
			$result['msg'] = "Erro na funcao ldap_functions->add_user2maillist_scl ($dn).\n\nRetorno do servidor:" . ldap_error($this->ldap_write);
		}
		return $result;
	}

	function remove_user2maillist($uidNumber, $mail)
	{
		$filter = "(&(phpgwAccountType=l)(uidNumber=$uidNumber))";
		$justthese = array("dn");
		$search = ldap_search($this->ldap_write, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
		$entry = ldap_get_entries($this->ldap_write, $search);
		$group_dn = $entry[0]['dn'];
		$attrs['mailForwardingAddress'] = $mail;
		$res = @ldap_mod_del($this->ldap_write, $group_dn, $attrs);
		
		if ($res)
		{
			$result['status'] = true;
		}
		else
		{
			$result['status'] = false;
			$result['msg'] = "Erro na funcao ldap_functions->remove_user2maillist ($mail).\n\nRetorno do servidor:" . ldap_error($this->ldap_write);
		}
		return $result;
	}
	
	function remove_user2maillist_adm($uidNumber, $mail)
        {
		$filter = "(&(phpgwAccountType=l)(uidNumber=$uidNumber))";
		$justthese = array("dn");
		$search = ldap_search($this->ldap_write, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
		$entry = ldap_get_entries($this->ldap_write, $search);
		$group_dn = $entry[0]['dn'];
		$attrsAdm['admlista'] = $mail;
		$res = @ldap_mod_del($this->ldap_write, $group_dn, $attrsAdm);
		if ($res)
		{
				$result['status'] = true;
		}
		else
		{
				$result['status'] = false;
				$result['msg'] = "Erro na funcao ldap_functions->remove_user2maillist ($mail).\n\nRetorno do servidor:" . ldap_error($this->ldap_write);
		}
		return $result;
        }

	function remove_user2maillist_scl($dn, $array_emails)
	{
		$attrs['naomoderado'] = $array_emails;
		$res = @ldap_mod_del($this->ldap_write, $dn, $attrs);
		
		if ($res)
		{
			$result['status'] = true;
		}
		else
		{
			$result['status'] = false;
			$result['msg'] = "Erro na funcao ldap_functions->remove_user2maillist_scp ($dn).\n\nRetorno do servidor:" . ldap_error($this->ldap_write);
		}
		return $result;
	}

	function replace_user2maillists($new_mail, $old_mail)
	{
		$filter = "(&(phpgwAccountType=l)(mailforwardingaddress=$old_mail))";
		$justthese = array("dn");
		$search = ldap_search($this->ldap_write, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
		$entries = ldap_get_entries($this->ldap_write, $search);
		$result['status'] = true;
		for ($i=0; $i<$entries['count']; ++$i)
		{
			$attrs['mailforwardingaddress'] = $old_mail;
			$res1 = @ldap_mod_del($this->ldap_write, $entries[$i]['dn'], $attrs);
			$attrs['mailforwardingaddress'] = $new_mail;
			$res2 = @ldap_mod_add($this->ldap_write, $entries[$i]['dn'], $attrs);
		
			if ((!$res1) || (!$res2))
			{
				$result['status'] = false;
				$result['msg'] = "Erro na funcao ldap_functions->replace_user2maillists ($old_mail).\nRetorno do servidor:" . ldap_error($this->ldap_write);
			}
		}
		return $result;
	}
	
	function get_user_info($uidnumber, $context, $serpro=false)
	{
		if ($serpro)
			$filter="(&(objectclass=".$GLOBALS['phpgw_info']['server']['atributousuarios'].")(uidNumber=".$uidnumber."))";
			else
			$filter="(&(phpgwAccountType=u)(uidNumber=".$uidnumber."))";
		//Precisa identificar cada atributo, pois em alguns ldaps os atributos nao sao devolvidos se nao fores explicitamente identificados na pesquisa
		$justthese = array("dn","ou","uid","uidnumber","gidnumber","departmentnumber","givenname","sn","telephonenumber","mobile","phpgwaccountstatus","phpgwaccountvisible","accountstatus","mail","defaultmembermoderation","admlista","listPass","listPass","mailalternateaddress","mailforwardingaddress","deliverymode","userpasswordrfc2617","cn","objectclass",$GLOBALS['phpgw_info']['server']['atributoexpiracao']);
		$search = ldap_search($this->ldap, $context, $filter, $justthese);
		//$search = ldap_search($this->ldap, $context, $filter);
		$entry = ldap_get_entries($this->ldap, $search);

		//Pega o dn do setor do usuario.
		$entry[0]['dn'] = strtolower($entry[0]['dn']);
		$sector_dn_array = explode(",", $entry[0]['dn']);
        $sector_dn_array_count = count($sector_dn_array);
		for($i=1; $i<$sector_dn_array_count; ++$i)
			$sector_dn .= $sector_dn_array[$i] . ',';
		//Retira ultimo pipe.
		$sector_dn = substr($sector_dn,0,(strlen($sector_dn) - 1));
	
	    $result['dn']               = $entry[0]['dn'];
	    $result['ou']               = $entry[0]['ou'][0];
		$result['context']			= $sector_dn;
		$result['uid']				= $entry[0]['uid'][0];
		$result['uidnumber']		= $entry[0]['uidnumber'][0];
		$result['gidnumber']		= $entry[0]['gidnumber'][0];
		$result['departmentnumber']	= $entry[0]['departmentnumber'][0];
		$result['givenname']		= $entry[0]['givenname'][0];
		$result['sn']				= $entry[0]['sn'][0];
		$result['telephonenumber']	= $entry[0]['telephonenumber'][0];
                $result['mobile']	= $entry[0]['mobile'][0];
		$result['phpgwaccountstatus']	= $entry[0]['phpgwaccountstatus'][0];
		$result['phpgwaccountvisible']	= $entry[0]['phpgwaccountvisible'][0];
		$result['accountstatus']	= $entry[0]['accountstatus'][0];
		$result['mail']				= $entry[0]['mail'][0];
		$result['defaultMemberModeration']   = $entry[0]['defaultmembermoderation'][0];
		$result['admlista']                  = $entry[0]['admlista'][0];
		$result['listPass']			= $entry[0]['listPass'][0];
		$result['mailalternateaddress']	= $entry[0]['mailalternateaddress'][0];
		$result['mailforwardingaddress']= $entry[0]['mailforwardingaddress'][0];
		$result['deliverymode']		= $entry[0]['deliverymode'][0];
		$result['userPasswordRFC2617']	= $entry[0]['userpasswordrfc2617'][0];
		$result['cn']				= $entry[0]['cn'][0];
		$result['phpgwaccount']		= false;
		if(isset($GLOBALS['phpgw_info']['server']['atributoexpiracao']))
			$result[$GLOBALS['phpgw_info']['server']['atributoexpiracao']]	= $entry[0][$GLOBALS['phpgw_info']['server']['atributoexpiracao']][0];
			else
			$result['phpgwaccountexpires'] = $entry[0]['phpgwaccountexpires'][0];
		//objectclass phpgwaccount
		foreach ($entry[0]['objectclass'] as $objectclass)
			{
			if  (strcasecmp($objectclass, 'phpgwaccount') == 0)
				$result['phpgwaccount']  = true;
			}
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
		
		// MailLists
		$justthese = array("uid","mail","uidnumber");
		$filter="(&(phpgwAccountType=l)(mailforwardingaddress=".$result['mail']."))";
		$search = ldap_search($this->ldap, $context, $filter, $justthese);
    	ldap_sort($this->ldap, $search, "uid");
    	$entries = ldap_get_entries($this->ldap, $search);

    	for ($i=0; $i<$entries['count']; ++$i)
    	{
    		$result['maillists_info'][$i]['uidnumber'] = $entries[$i]['uidnumber'][0];
    		$result['maillists_info'][$i]['uid'] = $entries[$i]['uid'][0];
    		$result['maillists_info'][$i]['mail'] = $entries[$i]['mail'][0];
    		$result['maillists'][] = $entries[$i]['uidnumber'][0];
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
		$result['groups'][ $entries[$i]['gidnumber'][0] ] = $entries[$i]['gidnumber'][0];
		$result['groups_info'][ $entries[$i]['gidnumber'][0] ] = $entries[$i]['gidnumber'][0];
    	}
		return $result;		
	}
	
	function get_group_info($gidnumber, $context)
	{
		$filter="(&(phpgwAccountType=g)(gidNumber=".$gidnumber."))";
		$search = ldap_search($this->ldap, $context, $filter);
		$entry = ldap_get_entries($this->ldap, $search);

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
		
		// Retira o count do array
		array_shift($entry[0]['memberuid']);
		
		// Checamos e-mails que n�o fazem parte do expresso.
		// Criamos um array temporario
		$tmp_array = array();
		foreach ($result['memberuid_info'] as $uid => $user_data)
		{
			$tmp_array[] = $uid;
		}

		// Vemos a diferen�a
		$array_diff = array_diff($entry[0]['memberuid'], $tmp_array);
		
		// Incluimos no resultado
		foreach ($array_diff as $index=>$uid)
		{
			$result['memberuid_info'][$uid]['cn'] = $uid;
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
	
	function get_maillist_info($uidnumber, $context)
	{
		$filter="(&(phpgwAccountType=l)(uidNumber=".$uidnumber."))";
		$search = ldap_search($this->ldap, $context, $filter);
		$entry = ldap_get_entries($this->ldap, $search);
	
		//Pega o dn do setor do usuario.
		$entry[0]['dn'] = strtolower($entry[0]['dn']);
		$sector_dn_array = explode(",", $entry[0]['dn']);
        $sector_dn_array_count = count($sector_dn_array);
		for($i=1; $i<$sector_dn_array_count; ++$i)
			$sector_dn .= $sector_dn_array[$i] . ',';
		//Retira ultimo pipe.
		$sector_dn = substr($sector_dn,0,(strlen($sector_dn) - 1));

		$result['context']			= $sector_dn;
		$result['uidnumber']			= $entry[0]['uidnumber'][0];
		$result['uid']				= $entry[0]['uid'][0];
		$result['cn']				= $entry[0]['cn'][0];
		$result['admlista']			= $entry[0]['admlista'][0];
		$result['defaultMemberModeration']      = $entry[0]['defaultmembermoderation'][0];
		$result['listPass']			= $entry[0]['listPass'][0];
		$result['mail']				= $entry[0]['mail'][0];
		$result['accountStatus']		= $entry[0]['accountstatus'][0];
		$result['accountAdm']                	= $entry[0]['accountAdm'][0];
		$result['phpgwAccountVisible']		= $entry[0]['phpgwaccountvisible'][0];
		$result['description']			= $entry[0]['description'][0];
			
		//Members
		for ($i=0; $i<$entry[0]['mailforwardingaddress']['count']; ++$i)
		{
			$justthese = array("cn", "uidnumber", "uid", "phpgwaccounttype", "mail");
				
			// Montagem dinamica do filtro
			$filter="(&(|(phpgwAccountType=u)(phpgwAccountType=l))(|";
			for ($k=0; (($k<10) && ($i<$entry[0]['mailforwardingaddress']['count'])); ++$k)
			{
				$filter .= "(mail=".$entry[0]['mailforwardingaddress'][$i].")";
				++$i;
			}
			$i--;
			$filter .= "))";
				
			$search = ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
			$user_entry = ldap_get_entries($this->ldap, $search);
				
			for ($j=0; $j<$user_entry['count']; ++$j)
			{
				$result['members_info'][$user_entry[$j]['uidnumber'][0]]['uid'] = $user_entry[$j]['uid'][0];
				$result['members_info'][$user_entry[$j]['uidnumber'][0]]['cn'] = $user_entry[$j]['cn'][0];
				$result['members_info'][$user_entry[$j]['uidnumber'][0]]['type'] = $user_entry[$j]['phpgwaccounttype'][0];
				$result['members_info'][$user_entry[$j]['uidnumber'][0]]['mail'] = $user_entry[$j]['mail'][0];
				$result['members'][] = $user_entry[$j]['uidnumber'][0];
			}
		}

		// Retira o count do array
		array_shift($entry[0]['mailforwardingaddress']);

		// Checamos e-mails que n�o fazem parte do expresso.
		// Criamos um array temporario
		$tmp_array = array();
		foreach ($result['members_info'] as $uid => $user_data)
		{
			$tmp_array[] = $user_data['mail'];
		}

		// Vemos a diferen�a
		$array_diff = array_diff($entry[0]['mailforwardingaddress'], $tmp_array);

		// Incluimos no resultado
		foreach ($array_diff as $index=>$mailforwardingaddress)
		{
			$result['members_info'][$mailforwardingaddress]['uid'] = $mailforwardingaddress;
			//$result['members_info'][$mailforwardingaddress]['cn'] = 'E-Mail nao encontrado';
			$result['members_info'][$mailforwardingaddress]['cn'] = '';
			$result['members_info'][$mailforwardingaddress]['mailforwardingaddress'] = $mailforwardingaddress;
			$result['members'][] = $mailforwardingaddress;
		}
		return $result;	
	}	

	function get_adm_maillist_info($uidnumber, $context) // Funcao que coleta as informacoes dos administradores de listas no LDAP
	{
		$filter="(&(phpgwAccountType=l)(uidNumber=".$uidnumber."))";
		$search = ldap_search($this->ldap, $context, $filter);
		$entry = ldap_get_entries($this->ldap, $search);
	
		//Pega o dn do setor do usuario.
		$entry[0]['dn'] = strtolower($entry[0]['dn']);
		$sector_dn_array = explode(",", $entry[0]['dn']);
        $sector_dn_array_count = count($sector_dn_array);
		for($i=1; $i<$sector_dn_array_count; ++$i)
			$sector_dn .= $sector_dn_array[$i] . ',';
		//Retira ultimo pipe.
		$sector_dn = substr($sector_dn,0,(strlen($sector_dn) - 1));
			
		$result['context']			= $sector_dn;
		$result['uidnumber']			= $entry[0]['uidnumber'][0];
		$result['uid']				= $entry[0]['uid'][0];
		$result['cn']				= $entry[0]['cn'][0];
		$result['listPass']			= $entry[0]['listPass'][0];
		$result['mail']				= $entry[0]['mail'][0];
		$result['accountStatus']		= $entry[0]['accountstatus'][0];
		$result['accountAdm']                	= $entry[0]['accountAdm'][0];
		$result['phpgwAccountVisible']		= $entry[0]['phpgwaccountvisible'][0];
		$result['description']			= $entry[0]['description'][0];

		//Members
		for ($i=0; $i<$entry[0]['admlista']['count']; ++$i)
		{
			$justthese = array("cn", "uidnumber", "uid", "phpgwaccounttype", "mail", "admlista");
				
			// Montagem dinamica do filtro
			$filter="(&(|(phpgwAccountType=u)(phpgwAccountType=l))(|";
			for ($k=0; (($k<10) && ($i<$entry[0]['admlista']['count'])); ++$k)
			{
				$filter .= "(mail=".$entry[0]['admlista'][$i].")";
				++$i;
			}
			$i--;
			$filter .= "))";
				
			$search = ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
			$user_entry = ldap_get_entries($this->ldap, $search);
				
			for ($j=0; $j<$user_entry['count']; ++$j)
			{
				$result['members_info'][$user_entry[$j]['uidnumber'][0]]['uid'] = $user_entry[$j]['uid'][0];
				$result['members_info'][$user_entry[$j]['uidnumber'][0]]['cn'] = $user_entry[$j]['cn'][0];
				$result['members_info'][$user_entry[$j]['uidnumber'][0]]['type'] = $user_entry[$j]['phpgwaccounttype'][0];
				$result['members_info'][$user_entry[$j]['uidnumber'][0]]['mail'] = $user_entry[$j]['mail'][0];
				$result['members'][] = $user_entry[$j]['uidnumber'][0];
			}
		}

		// Retira o count do array
		array_shift($entry[0]['admlista']);

		// Checamos e-mails que n�o fazem parte do expresso.
		// Criamos um array temporario
		$tmp_array = array();
		foreach ($result['members_info'] as $uid => $user_data)
		{
			$tmp_array[] = $user_data['mail'];
		}

		// Vemos a diferen�a
		$array_diff = array_diff($entry[0]['admlista'], $tmp_array);

		// Incluimos no resultado
		foreach ($array_diff as $index=>$admlista)
		{
			$result['members_info'][$admlista]['uid'] = $admlista;
			//$result['members_info'][$admlista]['cn'] = 'E-Mail nao encontrado';
			$result['members_info'][$admlista]['cn'] = '';
			$result['members_info'][$admlista]['admlista'] = $admlista;
			$result['members'][] = $admlista;
		}
		return $result;	
	}	


	function get_maillist_scl_info($uidnumber, $context)
	{
		$filter="(&(phpgwAccountType=l)(uidNumber=".$uidnumber."))";
		$search = ldap_search($this->ldap, $context, $filter);
		$entry = ldap_get_entries($this->ldap, $search);

		//Pega o dn do setor do usuario.
		$entry[0]['dn'] = strtolower($entry[0]['dn']);
		$sector_dn_array = explode(",", $entry[0]['dn']);
        $sector_dn_array_count = count($sector_dn_array);
		for($i=1; $i<$sector_dn_array_count; ++$i)
			$sector_dn .= $sector_dn_array[$i] . ',';
		//Retira ultimo pipe.
		$sector_dn = substr($sector_dn,0,(strlen($sector_dn) - 1));
		
		$result['dn']				= $entry[0]['dn'];
		$result['context']			= $sector_dn;
		$result['uidnumber']		        = $entry[0]['uidnumber'][0];
		$result['uid']				= $entry[0]['uid'][0];
		$result['cn']				= $entry[0]['cn'][0];
		$result['mail']				= $entry[0]['mail'][0];
		$result['accountStatus']	        = $entry[0]['accountstatus'][0];
		$result['accountAdm']                   = $entry[0]['accountAdm'][0];
		$result['phpgwAccountVisible']		= $entry[0]['phpgwaccountvisible'][0];
		$result['accountRestrictive']		= $entry[0]['accountrestrictive'][0];
		$result['participantCanSendMail']	= $entry[0]['participantcansendmail'][0];
		$result['description']			= $entry[0]['description'][0];
		
		//Senders
		//for ($i=0; $i<$entry[0]['mailsenderaddress']['count']; ++$i)
		//Recupera a relacao de usuario nao moderados no Mailman (podem enviar e-mails para uma lista de e-mail sem precisar da
		//autorizacao do moderador;
		for ($i=0; $i<$entry[0]['naomoderado']['count']; ++$i)
		{
			$justthese = array("cn", "uidnumber", "uid", "mail");
			$filter="(&(phpgwAccountType=u)(mail=".$entry[0]['naomoderado'][$i]."))";
			$search = ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
			$user_entry = ldap_get_entries($this->ldap, $search);
			
			$result['senders_info'][$user_entry[0]['uidnumber'][0]]['uid'] = $user_entry[0]['uid'][0];
			$result['senders_info'][$user_entry[0]['uidnumber'][0]]['cn'] = $user_entry[0]['cn'][0];
			$result['senders_info'][$user_entry[0]['uidnumber'][0]]['mail'] = $user_entry[0]['mail'][0];
			$result['members'][] = $user_entry[0]['uidnumber'][0];
		}

		return $result;
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

	function gidnumbers2cn($gidnumbers, $context)
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
					$result['groups_info'][$i]['cn'] = '_Grupo existe no BD mas n�o no LDAP';
				else
					$result['groups_info'][$i]['cn'] = $entry[0]['cn'][0];
				$result['groups_info'][$i]['gidnumber'] = $gidnumber;
				
				if (!strpos(strtolower($entry[0]['dn']), strtolower($context)))
					$result['groups_info'][$i]['group_disabled'] = 'true';
				else
					$result['groups_info'][$i]['group_disabled'] = 'false';
				
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

	function uid2uidnumber($uid)
	{
	       $justthese = array("uidnumber");
	       $filter="(&(|(phpgwAccountType=u)(phpgwAccountType=l))(uid=".$uid."))";
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

	
	function change_user_context($dn, $newrdn, $newparent)
	{
		if (!ldap_rename ( $this->ldap_write, $dn, $newrdn, $newparent, true ))
		{
			$return['status'] = false;
			$return['msg'] = 'Erro em ldap_funcitons->change_user_context: ' . ldap_error($this->ldap_write);
		}
		else
			$return['status'] = true;
		return $return;
	}
	
	function replace_user_attributes($dn, $ldap_mod_replace)
	{
		if (!@ldap_mod_replace ( $this->ldap_write, $dn, $ldap_mod_replace ))
		{
			$return['status'] = false;
			$return['msg'] = 'Erro em ldap_funcitons->replace_user_attributes: ' . ldap_error($this->ldap_write);
		}
		else
			$return['status'] = true;
		return $return;
	}
	
	function add_user_attributes($dn, $ldap_add)
	{
		if (!@ldap_mod_add ( $this->ldap_write, $dn, $ldap_add ))
		{
			$return['status'] = false;
			$return['msg'] = 'Erro em ldap_funcitons->add_user_attributes: ' . ldap_error($this->ldap_write);
		}
		else
			$return['status'] = true;
		
		return $return;
	}
	
	function remove_user_attributes($dn, $ldap_remove)
	{
		if (!@ldap_mod_del ( $this->ldap_write, $dn, $ldap_remove ))
		{
			$return['status'] = false;
			$return['msg'] = 'Erro em ldap_funcitons->remove_user_attributes: ' . ldap_error($this->ldap_write);
		}
		else
			$return['status'] = true;
		
		return $return;
	}
	
	function set_user_password($uid, $password)
	{
		$justthese = array("userPassword");
		$filter="(&(phpgwAccountType=u)(uid=".$uid."))";
		$search = ldap_search($this->ldap_write, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
		$entry = ldap_get_entries($this->ldap_write, $search);
		$dn = $entry[0]['dn'];
		$userPassword = $entry[0]['userpassword'][0];
		$ldap_mod_replace['userPassword'] = $password;
		$this->replace_user_attributes($dn, $ldap_mod_replace);
		return $userPassword;
	}

	function set_user_expires($uid, $expires)
	{
		$justthese = array(" ");
		$filter="(&(phpgwAccountType=u)(uid=".$uid."))";
		$search = ldap_search($this->ldap_write, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
		$entry = ldap_get_entries($this->ldap_write, $search);
		$dn = $entry[0]['dn'];
		if(isset($GLOBALS['phpgw_info']['server']['atributoexpiracao']))
			{
			if(substr($GLOBALS['phpgw_info']['server']['atributoexpiracao'],-1,1)=="Z")
				{
				###quando a data de expiracao estah no formato yyyymmddhhmmssZ
				$ldap_mod_replace[$GLOBALS['phpgw_info']['server']['atributoexpiracao']] = strftime("%Y%m%d%H%M%SZ", $expires);
				}
				else
				{
				###Outro atributo ldap que, assim como o phpgwaccounttype, tambem contem hora em formato unix
				$ldap_mod_replace[$GLOBALS['phpgw_info']['server']['atributoexpiracao']] = $expires;
				}
			}
		else
			{
			$ldap_mod_replace['phpgwaccountexpires'] = $expires;
			}
		//$ldap_mod_replace['phpgwaccountexpires'] = $expires;
		$this->replace_user_attributes($dn, $ldap_mod_replace);
		return true;
	}
	
	function delete_user($user_info)
	{
		$return['status'] = true;
		
		// GROUPS
		$attrs = array();
		$attrs['memberUid'] = $user_info['uid'];
		if (count($user_info['groups_info']))
		{
			foreach ($user_info['groups_info'] as $group_info)
			{
				$gidnumber = $group_info['gidnumber'];
				$justthese = array("dn");
				$filter="(&(phpgwAccountType=g)(gidnumber=".$gidnumber."))";
				$search = ldap_search($this->ldap_write, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
	    		$entry = ldap_get_entries($this->ldap_write, $search);
				$dn = $entry[0]['dn'];
			
				if (!@ldap_mod_del($this->ldap_write, $dn, $attrs))
				{
					$return['status'] = false;
					$return['msg'] .= 'Erro em ldap_funcitons->delete_user, grupos: ' . ldap_error($this->ldap_write);
				}
			}
		}
			
		// MAILLISTS
		$attrs = array();
		$attrs['mailForwardingAddress'] = $user_info['mail'];
		if (count($user_info['maillists_info']))
		{
			foreach ($user_info['maillists_info'] as $maillists_info)
			{
				$uidnumber = $maillists_info['uidnumber'];
				$justthese = array("dn");
				$filter="(&(phpgwAccountType=l)(uidnumber=".$uidnumber."))";
				$search = ldap_search($this->ldap_write, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
	    		$entry = ldap_get_entries($this->ldap_write, $search);
				$dn = $entry[0]['dn'];
			
				if (!@ldap_mod_del($this->ldap_write, $dn, $attrs))
				{
					$return['status'] = false;
					$return['msg'] .= 'Erro em ldap_funcitons->delete_user, listas de email: ' . ldap_error($this->ldap_write);
				}
			}
		}
			
		// UID
		$dn = "uid=" . $user_info['uid'] . "," . $user_info['context'];
		if (!@ldap_delete($this->ldap_write, $dn))
		{
			$return['status'] = false;
			$return['msg'] .= 'Erro em ldap_funcitons->delete_user, listas de email: ' . ldap_error($this->ldap_write);
		}
		
		return $return;
	}
function delete_user_group($user,$groups)
        {
        $return['status'] = true;
        $attrs = array();
        $attrs['memberUid'] = $user['uid'];
	if ($groups['groups'])
		{
                foreach ($groups['groups'] as $gidnumber)
                         {
	                 $justthese = array("dn");
	                 $filter="(&(phpgwAccountType=g)(gidnumber=".$gidnumber."))";
	                 $search = ldap_search($this->ldap_write, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
                         $entry = ldap_get_entries($this->ldap_write, $search);
                         $dn = $entry[0]['dn'];
                         if (!@ldap_mod_del($this->ldap_write, $dn, $attrs))
                                {
                                $return['status'] = false;
	                        $return['msg'] .= 'Erro em ldap_funcitons->delete_user, grupos: ' . ldap_error($this->ldap_write);
	                        }
              		}
		}
        return $return;
        }
function delete_maillist($uidnumber)
	{
		$return['status'] = true;
		
		$justthese = array("dn");
		$filter="(&(phpgwAccountType=l)(uidnumber=".$uidnumber."))";
		$search = ldap_search($this->ldap_write, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
   		$entry = ldap_get_entries($this->ldap_write, $search);
		$dn = $entry[0]['dn'];
		
		if (!@ldap_delete($this->ldap_write, $dn))
		{
			$return['status'] = false;
			$return['msg'] .= 'Erro em ldap_funcitons->delete_maillist, listas de email: ' . ldap_error($this->ldap_write);
		}
		
		return $return;
	}

	function delete_group($gidnumber)
	{
		$return['status'] = true;
		
		$justthese = array("dn");
		$filter="(&(phpgwAccountType=g)(gidnumber=".$gidnumber."))";
		$search = ldap_search($this->ldap_write, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
   		$entry = ldap_get_entries($this->ldap_write, $search);
		$dn = $entry[0]['dn'];
		
		if (!@ldap_delete($this->ldap_write, $dn))
		{
			$return['status'] = false;
			$return['msg'] .= 'Erro em ldap_funcitons->delete_maillist, listas de email: ' . ldap_error($this->ldap_write);
		}
		
		return $return;
	}

	
	function rename_uid($uid, $new_uid)
	{
		$return['status'] = true;
		
		$justthese = array("dn");
		$filter="(&(phpgwAccountType=u)(uid=".$uid."))";
		$search = ldap_search($this->ldap_write, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
	    $entry = ldap_get_entries($this->ldap_write, $search);
		$dn = $entry[0]['dn'];
		
		$explode_dn = ldap_explode_dn($dn, 0);
		$rdn = "uid=" . $new_uid;

		$parent = array();
		for ($j=1; $j<(count($explode_dn)-1); ++$j)
			$parent[] = $explode_dn[$j];
		$parent = implode(",", $parent);
		
		$return['new_dn'] = $rdn . ',' . $parent;
			
		if (!@ldap_rename($this->ldap_write, $dn, $rdn, $parent, false))
		{
			$return['status'] = false;
			$return['msg'] .= 'Erro em ldap_funcitons->rename_uid: ' . ldap_error($this->ldap_write);
		}
		
		//Grupos
		$justthese = array("dn");
		$filter="(&(phpgwAccountType=g)(memberuid=".$uid."))";
		$search = ldap_search($this->ldap_write, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
	    $entry = ldap_get_entries($this->ldap_write, $search);
    	$array_mod_add['memberUid'] = $new_uid;
    	$array_mod_del['memberUid'] = $uid;

	    for ($i=0; $i<=$entry['count']; ++$i)
	    {
	    	$dn = $entry[$i]['dn'];
	    	@ldap_mod_add ( $this->ldap_write, $dn,  $array_mod_add);
	    	@ldap_mod_del ( $this->ldap_write, $dn,  $array_mod_del);
	    }
		return $return;
	}

	function rename_cn($cn, $new_cn)
	{
		$return['status'] = true;
		
		$justthese = array("dn");
		$filter="(&(phpgwAccountType=g)(uid=".$cn."))";
		$search = ldap_search($this->ldap_write, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
	    $entry = ldap_get_entries($this->ldap_write, $search);
		$dn = $entry[0]['dn'];
		
		$explode_dn = ldap_explode_dn($dn, 0);
		$rdn = "cn=" . $new_cn;

		$parent = array();
		for ($j=1; $j<(count($explode_dn)-1); ++$j)
			$parent[] = $explode_dn[$j];
		$parent = implode(",", $parent);
		
		$return['new_dn'] = $rdn . ',' . $parent;
			
		if (!@ldap_rename($this->ldap_write, $dn, $rdn, $parent, false))
		{
			$return['status'] = false;
		}
		
		return $return;
	}
/*
	function rename_departmentnumber($old_dp, $new_dp)
	{
		$return['status'] = true;
		
		$justthese = array("dn");
		$filter="(&(phpgwAccountType=u)(departmentnumber=".$old_dp."))";
		$search = ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter, $justthese);
	    $entry = ldap_get_entries($this->ldap, $search);
		
	    for ($i=0; $i<=$entry['count']; ++$i)
	    {
	    	$dn = strtolower($entry[$i]['dn']);
	    	$ldap_mod_replace = array();
	    	$ldap_mod_replace['departmentnumber'] = $new_dp;
	    	@ldap_mod_replace ( $this->ldap, $dn,  $ldap_mod_replace);
			//if (!@ldap_mod_replace ( $this->ldap, $dn,  $ldap_mod_replace))
			//{
			//	$return['status'] = false;
			//	$return['msg'] .= 'Erro em ldap_funcitons->rename_departmentnumber: ' . ldap_error($this->ldap);
			//}
	    }
		return $return;
	}
*/
/*
	function get_sambadomains($context)
	{
		$return['status'] = true;
		$return['sambaDomains'] = array();
		
		$justthese = array("sambaSID","sambaDomainName");
		$filter="(objectClass=sambaDomain)";
		$search = ldap_search($this->ldap, $context, $filter, $justthese);
	    $entry = ldap_get_entries($this->ldap, $search);
		
	    for ($i=0; $i<$entry['count']; ++$i)
	    {
			$return['sambaDomains'][$i]['samba_domain_sid'] = $entry[$i]['sambasid'][0];
			$return['sambaDomains'][$i]['samba_domain_name'] = $entry[$i]['sambadomainname'][0];
			$return['sambaDomains'][$i]['samba_domain_dn'] = $entry[$i]['dn'];
	    }
	    
		return $return;
	}
*/
	function exist_sambadomains($context, $sambaDomainName)
	{
		$justthese = array("dn");
		$filter="(&(objectClass=sambaDomain)(sambaDomainName=$sambaDomainName))";
		$search = ldap_search($this->ldap_write, $context, $filter, $justthese);
	    $entry = ldap_get_entries($this->ldap_write, $search);
	    
		if ($entry['count'])
			return true;
		else
			return false;
	}
	
	// Primeiro nilvel de organiza��o.
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
		$search = ldap_list($this->ldap_write, $context, $filter, $justthese);
	    $entry = ldap_get_entries($this->ldap_write, $search);
	    
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
	
	function add_sambadomain($sambadomainname, $sambasid, $context)
	{
		$result = array();
		
		$dn 								= "sambaDomainName=$sambadomainname,$context";
		$entry['sambaSID'] 					= $sambasid;
		$entry['objectClass'] 				= 'sambaDomain';
		$entry['sambaAlgorithmicRidBase']	= '1000';
		$entry['sambaDomainName']			= $sambadomainname;
		
		if (!@ldap_add ( $this->ldap_write, $dn, $entry ))
		{
			$return['status'] = false;
			$return['msg'] = "Erro na funcao ldap_functions->add_sambadomain ($dn).\nRetorno do servidor: " . ldap_error($this->ldap_write);
		}
		else
			$return['status'] = true;
		
		return $return;
	}
	
	function delete_sambadomain($sambadomainname)
	{
		$return['status'] = true;
		$filter="(sambaDomainName=$sambadomainname)";
		$search = ldap_search($this->ldap_write, $GLOBALS['phpgw_info']['server']['ldap_context'], $filter);
	    $entry = ldap_get_entries($this->ldap_write, $search);
	 
	 	if ($entry['count'] != 0)
	    {
			$dn = $entry[0]['dn'];
			
			if (!@ldap_delete($this->ldap_write, $dn))
			{
				$return['status'] = false;
				$return['msg'] .= "Erro em ldap_functions->delete_sambadomain ($sambadomainname).\nRetorno do servidor: " . ldap_error($this->ldap_write);
			}
	    }
	    
		return $return;
	}
}
//Geracao de senha criptografada pro Mailman

	function encriptar($string)
		{
		$key='expresso-livre';
		$result = '';
		for($i=1; $i<=strlen($string); ++$i)
		{
			$char = substr($string, $i-1, 1);
			$keychar = substr($key, ($i % strlen($key))-1, 1);
			$char = chr(ord($char)+ord($keychar));
			$result.=$char;
		}
		return $result;
		}

?>
