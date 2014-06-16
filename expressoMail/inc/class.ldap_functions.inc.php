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
		
require_once(dirname(__FILE__).'/../../services/class.servicelocator.php');
include_once("class.imap_functions.inc.php");
include_once("class.functions.inc.php");

function ldapRebind($ldap_connection, $ldap_url)
{
	@ldap_bind($ldap_connection, $_SESSION['phpgw_info']['expressomail']['ldap_server']['acc'],$_SESSION['phpgw_info']['expressomail']['ldap_server']['pw']);
}

class ldap_functions
{
	var $ds;
	var $ldap_host;
	var $ldap_context;
	var $imap;
	var $external_srcs;
	var $max_result;
	var $functions;
	var $ldapService;
	
	function ldap_functions(){
	// todo: Page Configuration for External Catalogs.
		@include("../contactcenter/setup/external_catalogs.inc.php");
		$this->ldapService = ServiceLocator::getService('ldap');
		if(isset($external_srcs))
		$this->external_srcs = $external_srcs;
		$this->max_result = $this->ldapService->limit;
		$this->functions = new functions();
	}
	// Using ContactCenter configuration.
	function ldapConnect($refer = false,$catalog = 0){
		if ($catalog > 0 && is_array($this->external_srcs)){
			$this->ldap_host 	= $this->external_srcs[$catalog]['host'];
			$this->ldap_context = $this->external_srcs[$catalog]['dn'];
			$this->bind_dn 		= $this->external_srcs[$catalog]['acc'];
			$this->bind_dn_pw 	= $this->external_srcs[$catalog]['pw'];
			$this->object_class = $this->external_srcs[$catalog]['obj'];
			$this->base_dn 		= $this->external_srcs[$catalog]['dn'];
			$this->branch 		= $this->external_srcs[$catalog]['branch'];
		}else {
			$this->ldap_host 	= $_SESSION['phpgw_info']['expressomail']['ldap_server']['host'];
			$this->ldap_context = $_SESSION['phpgw_info']['expressomail']['ldap_server']['dn'];
			$this->bind_dn = $_SESSION['phpgw_info']['expressomail']['ldap_server']['acc'];
			$this->bind_dn_pw = $_SESSION['phpgw_info']['expressomail']['ldap_server']['pw'];
			$this->branch = 'ou';
		}

		$this->ds = ldap_connect($this->ldap_host);
		ldap_set_option($this->ds, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($this->ds, LDAP_OPT_REFERRALS, $refer);
		if ($refer)	{
			ldap_set_rebind_proc($this->ds, ldapRebind);
		}
		@ldap_bind($this->ds,$this->bind_dn,$this->bind_dn_pw );
	}

	//Teste jakjr retornando o DS
	function ldapConnect2($refer = false){
		$ds = ldap_connect($_SESSION['phpgw_info']['expressomail']['ldap_server']['host']);

		if (!$ds)
			return false;

		ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($ds, LDAP_OPT_REFERRALS, $refer);
		if ($refer)
			ldap_set_rebind_proc($ds, 'rebind');
		@ldap_bind($ds, $_SESSION['phpgw_info']['expressomail']['ldap_server']['acc'],$_SESSION['phpgw_info']['expressomail']['ldap_server']['pw']);

		return $ds;
	}


	// usa o host e context do setup.
	function ldapRootConnect($refer = false){
		$this->ldap_host 	= $_SESSION['phpgw_info']['expressomail']['server']['ldap_host'];
		$this->ldap_context = $_SESSION['phpgw_info']['expressomail']['server']['ldap_context'];

		if( isset($_SESSION['phpgw_info']['expressomail']['server']['ldap_master_host']) &&
			isset($_SESSION['phpgw_info']['expressomail']['server']['ldap_master_root_dn']) &&
			isset($_SESSION['phpgw_info']['expressomail']['server']['ldap_master_root_pw']) &&
			$_SESSION['phpgw_info']['expressomail']['server']['ldap_master_host'] &&
			$_SESSION['phpgw_info']['expressomail']['server']['ldap_master_root_dn'] &&
			$_SESSION['phpgw_info']['expressomail']['server']['ldap_master_root_pw']) {
			$this->ds = ldap_connect($_SESSION['phpgw_info']['expressomail']['server']['ldap_master_host']);
			ldap_set_option($this->ds, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($this->ds, LDAP_OPT_REFERRALS,0);
			ldap_bind($this->ds, $_SESSION['phpgw_info']['expressomail']['server']['ldap_master_root_dn'], $_SESSION['phpgw_info']['expressomail']['server']['ldap_master_root_pw']);
		}else{
			$this->ds = ldap_connect($this->ldap_host);
			ldap_set_option($this->ds, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($this->ds, LDAP_OPT_REFERRALS, $refer);
			ldap_bind($this->ds, $_SESSION['phpgw_info']['expressomail']['server']['ldap_root_dn'],$_SESSION['phpgw_info']['expressomail']['server']['ldap_root_pw']);
		}
	}

	function quicksearch($params)
	{
		include_once("class.functions.inc.php");
		$functions = new functions;

		$field		= $params['field'];
		$ID			= $params['ID'];
		
		$ldapService = ServiceLocator::getService('ldap');
		$filter =  $ldapService->getSearchFilter($params['search_for']);

		$contacts_result = array();
		$contacts_result['field'] = $field;
		$contacts_result['ID'] = $ID;
		$search_for = utf8_encode($params['search_for']);
		
		if($_SESSION['phpgw_info']['user']['preferences']['expressoMail']['extended_info'])
			$extendedinfo=true;
		else
			$extendedinfo=false;

		// follow the referral
		$this->ldapConnect(true);

		if ($this->ds)
		{
			$ldapService->connection = $this->ds;
			if($extendedinfo)
				$justthese = array("cn", "mail", "telephonenumber", "uid","uidNumber", "mobile", "phpgwaccountvisible", "employeenumber", "ou");
			else 
				$justthese = array("cn", "mail", "telephoneNumber", "phpgwAccountVisible", "uidNumber","uid");
			$types = false;

			if( $field == 'null' || $ID == 'null' )
 			{
				$justthese[] = "jpegphoto";
				$types = 'u';
			}

		$filter = $ldapService->getSearchFilter( $params['search_for'], $types );

		$sr=@ldap_search($this->ds, $this->ldap_context, $filter, $justthese, 0, $this->max_result);

		if(!$sr)
			return null;

		$count_entries = ldap_count_entries($this->ds,$sr);

		$info = ldap_get_entries($this->ds, $sr);

	    // New search only on user sector
	    if ($count_entries == $this->max_result)
	    {
			$overload = $count_entries;
		}
		else
		{
			$catalogsNum=count($this->external_srcs);
				for ($i=0; $i<=$catalogsNum; ++$i)	{
					if ($this->external_srcs[$i]["quicksearch"]) { 
						$this->ldapConnect(true,$i);
						$filter="(|(cn=*$search_for*)(mail=*$search_for*))";
						if($extendedinfo)
							$justthese = array("cn", "mail", "telephonenumber", "uid","uidNumber", "mobile", "phpgwaccountvisible", "employeenumber", "ou");
						else 
							$justthese = array("cn", "mail", "telephoneNumber", "phpgwAccountVisible","uidNumber", "uid");
						$sr=@ldap_search($this->ds, $this->ldap_context, $filter, $justthese, 0, $this->max_result+1);
						if(!$sr)
							return null;
						$count_entries = ldap_count_entries($this->ds,$sr);
						$search = ldap_get_entries($this->ds, $sr);
						for ($j=0; $j<$search["count"]; ++$j) {
							$info[] = $search[$j];
						}
						$info["count"] = count($info)-1;
					}
				}
			}

		$tmp = array();
		$tmp_users_from_user_org = array();

		for ($i=0; $i<$info["count"]; ++$i)
		{
			$key = $info[$i]["mail"][0] . '%' . $info[$i]["telephonenumber"][0] . '%'. $info[$i]["mobile"][0] . '%' . $info[$i]["uid"][0] . '%' . $info[$i]["jpegphoto"]['count'] . '%' . $info[$i]["employeenumber"][0] . '%' . 	$info[$i]["ou"][0];

			if (/*(!$quickSearch_only_in_userSector) &&*/ preg_match("/$user_sector_dn/i", $info[$i]['dn']))
			{
				$tmp_users_from_user_org[$key] = utf8_decode($info[$i]["cn"][0]);
				continue;
			}

			$tmp[$key] = utf8_decode($info[$i]["cn"][0]);
		}

			natcasesort($tmp_users_from_user_org);
			natcasesort($tmp);

			if (($field != 'null') && ($ID != 'null'))
			{
				$i = 0;

				$tmp = array_merge($tmp, $tmp_users_from_user_org);
				natcasesort($tmp);

				foreach ($tmp as $info => $cn)
				{
					$contacts_result[$i] = array();
					$contacts_result[$i]["cn"] = $cn;
					list ($contacts_result[$i]["mail"], $contacts_result[$i]["phone"], $contacts_result[$i]["mobile"], $contacts_result[$i]["uid"], $contacts_result[$i]["jpegphoto"], $contacts_result[$i]["employeenumber"], $contacts_result[$i]["ou"]) = preg_split('/%/', $info);
					++$i;
				}
				$contacts_result['quickSearch_only_in_userSector'] = $quickSearch_only_in_userSector;
				$contacts_result['maxResult'] = $ldapService->limit;
			}
			else
			{
				$options_users_from_user_org = '';
				$options = '';

	    
				$i = 0;
				foreach ($tmp_users_from_user_org as $info => $cn)
				{
					$contacts_result[$i] = array();
					$options_users_from_user_org .= $this->make_quicksearch_card($info, $cn);
					++$i;
				}

	    
				foreach ($tmp as $info => $cn)
				{
					$contacts_result[$i] = array();
					$options .= $this->make_quicksearch_card($info, $cn);
					++$i;
				}


					if (($options_users_from_user_org != '') && ($options != ''))
					{
						$head_option0 =
							'<tr class="quicksearchcontacts_unselected">' .
								'<td colspan="2" width="100%" align="center" style="background:#EEEEEE"><B>' .
									$this->functions->getLang('Users from your organization') . '</B> ['.count($tmp_users_from_user_org).']';
								'</td>' .
							'</tr>';

						$head_option1 =
							'<tr class="quicksearchcontacts_unselected">' .
								'<td colspan="2" width="100%" align="center" style="background:#EEEEEE"><B>' .
									$this->functions->getLang('Users from others organizations') . '</B> ['.count($tmp).']';
								'</td>' .
							'</tr>';
					}
		    
		    $head_option = '';

		    if( $overload )
		    $head_option = '<tr class="quicksearchcontacts_unselected">' .
				    '<td colspan="2" width="100%" align="center" style="background:#EEEEEE; color: red;"><B>' .str_replace('%1', $this->max_result, $this->functions->getLang('More than %1 results. Please, try to refine your search.')) . '</B> '.
				    '</td>' .
				    '</tr>';

		    $contacts_result = $head_option.$head_option0 . $options_users_from_user_org . $head_option1. $options;

				}
			}

		ldap_close($this->ds);
		return $contacts_result;
	}

	
	 /**
        * Método que faz o roteamento entre os métodos de busca (Catálogo pessoal, global e todos)
        * @license    http://www.gnu.org/copyleft/gpl.html GPL
        * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
        * @sponsor    Caixa Econômica Federal
        * @author     Prognus Software Livre <airton@prognus.com.br | prognus@prognus.com.br>
        * @param      <array> <$param> <parametros vindos do cliente>
        */
        function quicksearchcontact($params)
        {
            if(array_key_exists('Type', $params)){
                return $this->quickSearch($params);
            }
            
            $modal = false;
            if($params['catalog'])
                $modal = true;
             
            include_once dirname(__FILE__). '/../../header.inc.php';
            
            if($modal)
            {
                if($params['catalog'] == "global")
                {        
                    return $this->quickSearchGlobal($params);
                }
                else
                {
                    if($params['catalog'] == "personal")
                        return $this->quickSearchPersonal($params);
                    else
                        return $this->quickSearchAll($params);
                }
            }
            else
            {
                if($_SESSION['phpgw_info']['user']['preferences']['expressoMail']['catalog_search'] == "global")
                {
                    return $this->quickSearchGlobal($params);
                } 
                else
                {    
                    if($_SESSION['phpgw_info']['user']['preferences']['expressoMail']['catalog_search'] == "personal")
                        return $this->quickSearchPersonal($params);                   
                    else
						//Veirifica se quem chamou foi o campo de pesquisa rápida Expresso_Mail
						if(($params['field'] == 'null') && ($params['ID'] == 'null'))
							return $this->quickSearch($params);
						else
							return $this->quickSearchAll($params);
                }
            }
        }
        
        

        /**
        * Método que faz a busca de usuários em todos os catálogos
        * @license    http://www.gnu.org/copyleft/gpl.html GPL
        * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
        * @sponsor    Caixa Econômica Federal
        * @author     ProgquickSearchAllnus Software Livre <airton@prognus.com.br | prognus@prognus.com.br>
        * @param      <array> <$param> <parametros vindos do cliente>
        * @return     <array> <$retorno> <Array com os usuários de todos os catálogos, de acordo com o parâmetro>
        */
        function quickSearchAll($params)
        {
            $retorno_personal = $this->quickSearchPersonal($params);
            $retorno_global   = $this->quickSearchGlobal($params);
            //$retorno = $retorno_personal + $retorno_global;
            if ($retorno_global){
                $retorno = array_merge($retorno_personal, $retorno_global);
            }else{
                $retorno = $retorno_personal;
            }

			$retorno['type_catalog'] = "A";
			$retorno['search_for'] = $params['search_for'];
            return $retorno;
        }
        
        
        /**
        * Método que faz a busca de usuários no Catálogo Pessoal
        * @license    http://www.gnu.org/copyleft/gpl.html GPL
        * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
        * @sponsor    Caixa Econômica Federal
        * @author     Prognus Software Livre <airton@prognus.com.br | prognus@prognus.com.br>
        * @param      <array> <$param> <parametros vindos do cliente>
        * @return     <array> <$retorno> <Array com os usuários do Catálogo Pessoal, de acordo com o parâmetro>
        */
        function quickSearchPersonal($params, $all=false) 
        {
            $results = array();
            $DBService = ServiceLocator::getService('db');
            $results   = $DBService->search_contacts($params['search_for']);
			$results2 = array();
            $results2   = $DBService->search_groups($params['search_for']);
            
            if(is_array($results)){
            	if(is_array($results2)){
            		$results   = array_merge($results, $results2);
            	}
            }
            else if(is_array($results2)){
            	$results = $results2;
            }
            
            if(!$all)
                $results['type_catalog'] = "P";
			
			foreach($results as $i=>$value)
				$results[$i]['type_contact'] = "P";
			
			$results['search_for'] = $params['search_for'];
			
			return $results;
        } 
        
        
        /**
        * Método que faz a busca de usuários no Catálogo Geral
        * @license    http://www.gnu.org/copyleft/gpl.html GPL
        * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
        * @sponsor    Caixa Econômica Federal
        * @author     Prognus Software Livre <airton@prognus.com.br | prognus@prognus.com.br>
        * @param      <array> <$param> <parametros vindos do cliente>
        * @return     <array> <$retorno> <Array com os usuários do Catálogo Global, de acordo com o parâmetro>
        */
        function quickSearchGlobal($params, $all=false)
        {              
            include_once dirname(__FILE__). '/../../header.inc.php';
            $c = CreateObject('phpgwapi.config','contactcenter');
            $all_data = $c->read_repository();
            
            $searchable_fields = array();           
            foreach($all_data as $index => $value)
            {
                $parts = explode('_', $index);
                if (is_numeric($parts[3]) && $parts[1]=='attribute')
                {    
                    if($parts[2] == 'searchable' /*&& $value == 'true'*/)
                        $searchable_fields[$all_data['cc_attribute_ldapname_' . $parts[3]]] = $all_data['cc_attribute_name_' . $parts[3]];   
                }
            }    
            include_once("class.functions.inc.php");
            $functions      = new functions;
            $field	    = $params['field'];
            $ID             = $params['ID'];
            $ldapService    = ServiceLocator::getService('ldap');
            $filter         = $ldapService->getSearchFilter($params['search_for']);
  
            $contacts_result          = array();
            $contacts_result['field'] = $field;
            $contacts_result['ID']    = $ID;
            $search_for               = utf8_encode($params['search_for']);
            
            $this->ldapConnect(true);
            if ($this->ds)
            {
                $ldapService->connection = $this->ds;
                $campos_extras = '';
                $justthese = array("cn", "mail", "telephonenumber", "mobile", "phpgwaccountvisible", "uid", "employeenumber", "ou","vacationActive","vacationInfo");
                foreach($searchable_fields as $fields_ldap => $value_field)
                {   
                    $campos_extras .= $fields_ldap . "|" . $value_field . "#";
                    array_push($justthese, $fields_ldap);   
                } 
                $types = false;

                $campos_extras = substr($campos_extras,0,-1);
                
                if($field == 'null' || $ID == 'null')
                {
                    $justthese[] = "jpegphoto";
                    $types = 'u';
                }  
                $filter = $ldapService->getSearchFilter($params['search_for'], $types);
                // Retirei o this->max_result, que limitava a busca. Agora ta retornando tudo amigo.
                $sr=@ldap_search($this->ds, $this->ldap_context, $filter, $justthese, 0, $this->max_result);
                 
                if(!$sr)
                    return null;
                
                $count_entries = ldap_count_entries($this->ds,$sr);
                $info          = ldap_get_entries($this->ds, $sr);
                $info_return   = $info;
                //if($campos_extras != '')
                //array_push($info_return, $campos_extras);
			}
			ldap_close($this->ds);      

			//Busca em Catalagos externos
			$catalogsNum=count($this->external_srcs);
			for ($i=0; $i<=$catalogsNum; ++$i)	{
				if ($this->external_srcs[$i]["quicksearch"]) 
				{
					$this->ldapConnect(true,$i);
					$filter="(|(cn=*$search_for*)(mail=*$search_for*))";
					$justthese = array("cn", "mail", "telephoneNumber", "mobile", "phpgwAccountVisible", "uid","employeeNumber", "ou","vacationActive","vacationInfo"); 
					$sr=@ldap_search($this->ds, $this->ldap_context, $filter, $justthese, 0, $this->max_result+1);
					if(!$sr)
						return null;
					$count_entries = ldap_count_entries($this->ds,$sr);
					$search = ldap_get_entries($this->ds, $sr);
					for ($j=0; $j<$search["count"]; ++$j) {
						$search[$j]['isExternal'] = true;
                        $info_return[] = $search[$j];
					}
					$info_return["count"] = count($info_return)-1;
				}
			}
			//---------------------------------------------------------------//

            if($all == false)
                $info_return['type_catalog'] = "G";
            
			$info_return['extra_ldap_fields'] = $campos_extras; 
			
			foreach($info_return as &$value){
				if (is_array($value))
					$value['type_contact'] = "G";
				//Converte a descrição dos filtros para ISO8859 corrigindo inconsitências com caractéres especiais
				if(isset($value['vacationinfo']) && isset($value['vacationinfo'][0]) && $value['vacationinfo'][0] != '')
					$value['vacationinfo'][0] = $this->toISO8859($value['vacationinfo'][0]);
			}

			$info_return['search_for'] = $params['search_for'];
            return $info_return;
        } 
	
    /*Converte um parametro de UTF-8 para ISO8859*/ 
    function toISO8859($data) 
    { 
        if(!is_array($data)) 
          return mb_convert_encoding( $data , 'ISO-8859-1' , 'UTF-8 , ISO-8859-1' ); 
        $return = array(); 
        foreach ($data as $i => $v) 
          $return[$this->toISO8859($i)] = $this->toISO8859($v); 
        return $return; 
    } 

	function make_quicksearch_card($info, $cn)
	{
		include_once("class.functions.inc.php");
		$functions = new functions;

		$contacts_result = array();
		$contacts_result["cn"] = $cn;
		if($_SESSION['phpgw_info']['user']['preferences']['expressoMail']['extended_info'])
		    $extendedinfo=true;
		else
		    $extendedinfo=false;

		list ($contacts_result["mail"], $contacts_result["phone"], $contacts_result["mobile"], $contacts_result["uid"], $contacts_result["jpegphoto"], $contacts_result["employeenumber"], $contacts_result["ou"]) = preg_split('/%/', $info);

		if ($contacts_result['jpegphoto'])
			$photo_link = '<img src="./inc/show_user_photo.php?mail='.$contacts_result['mail'].'">';
		else
			$photo_link = '<img src="./templates/default/images/photo.jpg">';

		$phoneUser = $contacts_result['phone'];
		$mobileUser = $contacts_result["mobile"];
		if($mobileUser){
			$phoneUser .= " / $mobileUser";
		}
		
		if($_SESSION['phpgw_info']['user']['preferences']['expressoMail']['voip_enabled']) {
			$phoneUser = $contacts_result['phone'];
			if($phoneUser)
				$phoneUser = '<a title="'.$this->functions->getLang("Call to Comercial Number").'" href="#" onclick="InfoContact.connectVoip(\''.$phoneUser.'\',\'com\')">'.$phoneUser.'</a>';
			if($mobileUser)
				$phoneUser .= ' / <a title="'.$this->functions->getLang("Call to Mobile Number").'" href="#" onclick="InfoContact.connectVoip(\''.$mobileUser.'\',\'mob\')">'.$mobileUser.'</a>';
		}

		$empNumber = $contacts_result["employeenumber"];
		if($empNumber) {
			$empNumber = "$empNumber - ";
		}
		$ou = $contacts_result["ou"];
		if($ou) {
			$ou = "<br/>$ou" ;
		}

		// Begin: nickname, firstname and lastname for QuickAdd.
		$fn = $contacts_result["cn"];
		$array_name = explode(" ", $fn);
		if(count($array_name) > 1){			
			$fn = $array_name[0];
			array_shift($array_name);
			$sn = implode(" ",$array_name);
		}
		// End:
		$option =
			'<tr class="quicksearchcontacts_unselected">' .
				'<td class="cc" width="1%">' .
					'<a title="'.$this->functions->getLang("Write message").'" onClick="javascript:QuickSearchUser.create_new_message(\''.$contacts_result["cn"].'\', \''.$contacts_result["mail"].'\', \''.$contacts_result["uid"].'\')">' .
						$photo_link .
					'</a>' .
				'</td>' .
				'<td class="cc">' .
					'<span name="cn">' . ($empNumber != "" ? $empNumber : $uid) . $contacts_result['cn'] . '</span>' . '<br />' .
					'<a title="'.$functions->getLang("Write message").'" onClick="javascript:QuickSearchUser.create_new_message(\''.$contacts_result["cn"].'\', \''.$contacts_result["mail"].'\', \''.$contacts_result["uid"].'\')">' .
						'<font color=blue>' .
						'<span name="mail">' . $contacts_result['mail'] . '</span></a></font>'.
						'<img src="templates/default/images/user_card.png" style="cursor: pointer;" title="'.$this->functions->getLang("Add Contact").'" onclick="javascript:connector.loadScript(\'ccQuickAdd\');ccQuickAddOne.showList(\''.$fn.','.$fn.','.$sn.','.$contacts_result["mail"].'\')">'.
					'<br />' .
					$phoneUser .
					$ou .
				'</td>' .
				'</tr>';
		return $option;
	}

	function get_catalogs(){
		$catalogs = array();
		$catalogs[0] = $this->functions->getLang("Global Catalog");
		if($this->external_srcs)
			foreach ($this->external_srcs as $key => $valor ){
			$catalogs[$key] = $valor['name'];
		}
		return $catalogs;
	}
	function get_organizations($params){
		$organizations = array();
		$params['referral']?$referral = $params['referral']:$referral = false;
		$cat = $params['catalog'];

		$this->ldapConnect($referral,$cat);

			if($this->branch != '') {
				$filter="(&(".$this->branch."=*)(!(phpgwAccountVisible=-1)))";
				$justthese = array("$this->branch");
			$sr = ldap_list($this->ds, $this->ldap_context, $filter, $justthese);
			$info = ldap_get_entries($this->ds, $sr);

			if($info["count"] == 0)
			{
			    $organizations[0]['ou'] = $this->ldap_context;
			}

			for ($i=0; $i<$info["count"]; ++$i)
				$organizations[$i] = $info[$i]["ou"][0];

			ldap_close($this->ds);
			sort($organizations);
		return $organizations;
			}else{
			return null;
	}
	}
	function get_organizations2($params){
		$organizations = array();
		$referral = $params['referral'];
		$this->ldapRootConnect($referral);
		if ($this->ds) {
			$filter="(&(objectClass=organizationalUnit)(!(phpgwAccountVisible=-1)))";
			$justthese = array("ou");
			$sr = ldap_list($this->ds, $this->ldap_context, $filter, $justthese);
			$info = ldap_get_entries($this->ds, $sr);


			if($info["count"] == 0)
			{
			    $organizations[0]['ou'] = $this->ldap_context;
			    $organizations[0]['dn'] = $this->ldap_context;
			}
			else{
			    for ($i=0; $i<$info["count"]; ++$i)
			    {
				    $organizations[$i]['ou'] = $info[$i]["ou"][0];
				    $organizations[$i]['dn'] = $info[$i]["dn"];
			    }
			}
			ldap_close($this->ds);
			sort($organizations);
		}
		return $organizations;
	}
	//Busca usuarios de um contexto e ja retorna as options do select - usado por template serpro;
	function search_users($params)
        {
	        $owner = $_SESSION['phpgw_info']['expressomail']['user']['owner'];
			$ldapService = ServiceLocator::getService('ldap');
			$ldapService->connect($_SESSION['phpgw_info']['expressomail']['server']['ldap_host'],
			$_SESSION['phpgw_info']['expressomail']['server']['ldap_root_dn'],
			$_SESSION['phpgw_info']['expressomail']['server']['ldap_root_pw']);

			$groups = $ldapService->accountSearch($params['filter'], array("gidNumber","cn", 'uid'), $params['context'] , 'g', 'cn'); 
			$users = $ldapService->accountSearch($params['filter'], array("uidNumber","cn", 'uid'), $params['context'] , 'u', 'cn');
			$compartilhadas = $ldapService->accountSearch($params['filter'], array("uidNumber","cn",'uid'), $params['context'] , 's', 'cn');
			

			$group_options = array();
			$user_options  = array();
			$shared_options = array();

			foreach($groups as $group)
	        {
	                	$group_options[] = '"'.$group['gidnumber'].'U'.'":"'.$group['cn'].' ('.$group['uid'].')"';
	        }
	        foreach($users as $user) 
	            {
	            		if($owner != $user['uidnumber'])  
			                $user_options[] = '"'.$user['uidnumber'].'U'.'":"'.$user['cn'].' ('.$user['uid'].')"';
		        }	
        		foreach($compartilhadas as $shared)
                	{
	            		if($owner != $shared['uidnumber'])  
			                $shared_options[] = '"'.$shared['uidnumber'].'U'.'":"'.$shared['cn'].' ('.$shared['uid'].')"';
	            }
	
			$user_options = '{'.implode( ',', $user_options ).'}';
			$group_options = '{'.implode( ',', $group_options ).'}';
	                $shared_options = '{'.implode( ',', $shared_options ).'}';

	         return '{"users":'.$user_options.',"groups":'.$group_options.',"shared":'. $shared_options .'}';
        }

	function catalogsearch($params)
	{
		$ldapService = ServiceLocator::getService('ldap');
		$filter =  $ldapService->getSearchFilter($params['search_for'],array('u','l','s')); 
		
		$catalog = $params['catalog'];
		$error = False;

		//if($_SESSION['phpgw_info']['user']['preferences']['expressoMail']['extended_info'])
		    //$extendedinfo=true;
		//else
		    //$extendedinfo=false;


		$this->ldapConnect(true,$catalog);

		$params['organization'] == 'all' ? $user_context = $this->ldap_context :$user_context = $this->branch."=".$params['organization'].",".$this->ldap_context;

		if ($this->ds) {
				$justthese = array("cn", "mail", "phpgwaccounttype", "phpgwAccountVisible", "employeeNumber", "ou");
			$sr=@ldap_search($this->ds, $user_context, $filter, $justthese, 0, $ldapService->limit+1);

			if(!$sr)
				return null;
			$count_entries = ldap_count_entries($this->ds,$sr);
			if ($count_entries > $ldapService->limit){ 
				$info = null;
				$error = True;
			}
			else
				$info = ldap_get_entries($this->ds, $sr);

			ldap_close($this->ds);

			$u_tmp = array();
			$g_tmp = array();

			for ($i=0; $i<$info["count"]; ++$i){
				if((!$catalog==0)||(strtoupper($info[$i]["phpgwaccounttype"][0]) == 'U') && ($info[$i]["phpgwaccountvisible"][0] != '-1'))
					//aqui eh feita a concatenacao do departamento ao cn;
					$u_tmp[$info[$i]["mail"][0]] = utf8_decode($info[$i]["cn"][0]). '%' . $info[$i]["ou"][0];
				if((!$catalog==0)||(strtoupper($info[$i]["phpgwaccounttype"][0]) == 'L') && ($info[$i]["phpgwaccountvisible"][0] != '-1'))
					$g_tmp[$info[$i]["mail"][0]] = utf8_decode($info[$i]["cn"][0]);
			}

			natcasesort($u_tmp);
			natcasesort($g_tmp);

			$i = 0;
			$users = array();

			foreach ($u_tmp as $mail => $cn){

				$tmp = explode("%", $cn); //explode o cn pelo caracter "%" e joga em $tmp;
				$name = $tmp[0]; //pega o primeiro item (cn) do vetor resultante do explode acima;
				$department = $tmp[1]; //pega o segundo item (ou) do vetor resultanto do explode acima;
				$users[++$i] = array("name" => $name, "email" => $mail, "department" => $department);

			}
			unset($u_tmp);

			$i = 0;
			$groups = array();

			foreach ($g_tmp as $mail => $cn){
				$groups[++$i] = array("name" => $cn, "email" => $mail);
			}
			unset($g_tmp);

			return  array('users' => $users, 'groups' => $groups, 'error' => $error,'maxResult' => $ldapService->limit);
		}else
		return null;
	}

	function get_emails_ldap(){

		$result['mail']= array();
		$result['mailalter']= array();
		$user = $_SESSION['phpgw_info']['expressomail']['user']['account_lid'];
		$this->ldapRootConnect(false);
		if ($this->ds) {
			$filter="uid=".$user;
			$justthese = array("mail","mailAlternateAddress");
			$sr = ldap_search($this->ds,$this->ldap_context, $filter, $justthese);
			$ent = ldap_get_entries($this->ds, $sr);
			ldap_close($this->ds);

			for ($i=0; $i<$ent["count"]; ++$i){
				$result['mail'][] = $ent[$i]["mail"][0];
				$result['mailalter'][] = $ent[$i]["mailalternateaddress"][0];
			}
		}
		return $result;
	}

	//Busca usuarios de um contexto e ja retorna as options do select;
	function get_available_users($params)
    {
        $this->ldapRootConnect();
        //Monta lista de Grupos e Usuarios
        $users = Array();
        $groups = Array();
        $user_context= $params['context'];
        $owner = $_SESSION['phpgw_info']['expressomail']['user']['owner'];

        if ($this->ds)
        {
            $justthese = array("gidNumber","cn");
            if ($params['type'] == 'search')
                $sr=ldap_search($this->ds, $user_context, ("(&(cn=*)(phpgwaccounttype=g)(!(phpgwaccountvisible=-1)))"),$justthese);
            else
                $sr=ldap_list($this->ds, $user_context, ("(&(cn=*)(phpgwaccounttype=g)(!(phpgwaccountvisible=-1)))"),$justthese);
            $info = ldap_get_entries($this->ds, $sr);
            for ($i=0; $i<$info["count"]; ++$i)
                $groups[$uids=$info[$i]["gidnumber"][0]] = Array('name'    =>    $uids=$info[$i]["cn"][0], 'type'    =>    g);
            $justthese = array("phpgwaccountvisible","uidNumber","cn");
            if ($params['type'] == 'search')
                $sr=ldap_search($this->ds, $user_context, ("(&(cn=*)(phpgwaccounttype=u)(!(phpgwaccountvisible=-1)))"),$justthese);
            else
                $sr=ldap_list($this->ds, $user_context, ("(&(cn=*)(phpgwaccounttype=u)(!(phpgwaccountvisible=-1)))"),$justthese);

            $info = ldap_get_entries($this->ds, $sr);
            for ($i=0; $i<$info["count"]; ++$i)
            {
                if ($info[$i]["phpgwaccountvisible"][0] == '-1')
                    continue;
                $users[$uids=$info[$i]["uidnumber"][0]] = Array('name'    =>    $uids=$info[$i]["cn"][0], 'type'    =>    u);
            }
        }
        ldap_close($this->ds);

        @asort($users);
        @reset($users);
        @asort($groups);
        @reset($groups);
        $user_options ='';
        $group_options ='';

        foreach($groups as $id => $user_array) {
                $newId = $id.'U';
                $group_options .= '<option  value="'.$newId.'">'.utf8_decode($user_array['name']).'</option>'."\n";
        }
        foreach($users as $id => $user_array) {
            if($owner != $id){
                $newId = $id.'U';
                $user_options .= '<option  value="'.$newId.'">'.utf8_decode($user_array['name']).'</option>'."\n";
            }
        }
        return array("users" => $user_options, "groups" => $group_options);
    }

	//Busca usuarios de um contexto e ja retorna as options do select;
	function get_available_users2($params)
	{
		$ldapService = ServiceLocator::getService('ldap'); 
		$ldapService->connect($_SESSION['phpgw_info']['expressomail']['server']['ldap_host'],
		$_SESSION['phpgw_info']['expressomail']['server']['ldap_root_dn'],
		$_SESSION['phpgw_info']['expressomail']['server']['ldap_root_pw']);

		$entries = $ldapService->accountSearch($params['sentence'], array('cn', 'uid'), $params['context'], 'u', 'cn');

		$options = array();

		foreach ($entries as $value) 
			$options[] = '"'.$value['uid'].'"'.':'.'"'.$value['cn'].'"';

		return "{".implode(',',$options)."}";		
				}

	function uid2cn($uid)
	{
		// do not follow the referral
		$this->ldapRootConnect(false);
		if ($this->ds)
		{
			$filter="(&(phpgwAccountType=u)(uid=$uid))";
			$justthese = array("cn");
			$sr=@ldap_search($this->ds, $this->ldap_context, $filter, $justthese);
			if(!$sr)
				return false;
			$info = ldap_get_entries($this->ds, $sr);
			return utf8_decode($info[0]["cn"][0]);
		}
		return false;
	}
	function uidnumber2uid($uidnumber)
	{
		// do not follow the referral
		$this->ldapRootConnect(false);
		if ($this->ds)
		{
			$filter="(&(phpgwAccountType=u)(uidnumber=$uidnumber))";
			$justthese = array("uid");
			$sr=@ldap_search($this->ds, $this->ldap_context, $filter, $justthese);
			if(!$sr)
				return false;
			$info = ldap_get_entries($this->ds, $sr);
			return $info[0]["uid"][0];
		}
		return false;
	}
	function getSharedUsersFrom($params){
		$filter = '';
        $i = 0;         
        //Added to save if must save sent messages in shared folder
        $acl_save_sent_in_shared = array();
       
        if($params['uids']) {
                $uids = explode(";",$params['uids']);
                $this->imap = new imap_functions();                     
                foreach($uids as $index => $uid){
                        $params = array();
                        //Added to save if user has create permission
                        $acl_create_message = array();
                        $acl = $this->imap->getacltouser($uid ,true);
  
                        if ( preg_match("/p/",$acl )){                         
                            $filter .= "(uid=$uid)";                                                    
                            $acl_save_sent_in_shared[ $i ] =$uid;
                            ++$i;
                                                                       
                        }                                                       
                }                       
        }
		
		$this->ldapRootConnect(false);
		if ($this->ds) {
			$justthese = array("cn","mail","uid");
			if($filter) {
				$filter="(&(|(phpgwAccountType=u)(phpgwAccountType=s))(|$filter))";
				$sr		=	ldap_search($this->ds, $this->ldap_context, $filter, $justthese);
				ldap_sort($this->ds,$sr,"cn");
				$info 	= 	ldap_get_entries($this->ds, $sr);
				$var = print_r($acl_save_sent_in_shared, true);				
				for ($i = 0;$i < $info["count"]; ++$i){
					$info[$i]['cn'][0] = utf8_decode($info[$i]['cn'][0]);
					//verify if user has permission to save sent messages in a shared folder
					if ( in_array( $info[$i]['uid'][0],$acl_save_sent_in_shared) ){						
						$info[$i]['save_shared'][0] = 'y';
					} else $info[$i]['save_shared'][0] = 'n';
				}
			}

			$info['myname'] = $_SESSION['phpgw_info']['expressomail']['user']['fullname'];

			//Find institucional_account.
			$filter="(&(phpgwAccountType=i)(mailForwardingAddress=".$_SESSION['phpgw_info']['expressomail']['user']['email']."))";
			$sr	= ldap_search($this->ds, $this->ldap_context, $filter, $justthese);
			##
			# @AUTHOR Rodrigo Souza dos Santos
			# @DATE 2008/09/17
			# @BRIEF Changing to ensure that the variable session is always with due value.
			##
			if(ldap_count_entries($this->ds,$sr))
			{
				ldap_sort($this->ds,$sr,"cn");
				$result = ldap_get_entries($this->ds, $sr);
				for ($j = 0;$j < $result["count"]; ++$j){
					$result[$j]['cn'][0] = utf8_decode($result[$j]['cn'][0]);
					$result[$j]['mail'][0] = $result[$j]['mail'][0];
					$result[$j]['save_shared'][0] = 'n';
					$info[(int)$info['count']] = $result[$j];
					$info['count'] = (int)$info['count'] + 1;			
				}
			}

			$_SESSION['phpgw_info']['expressomail']['user']['shared_mailboxes'] = $info;

			return $info;
		}
	}

	function getUserByEmail($params)
	{
		$expires = 60*60*24*30; /* 30 days */
		header("Cache-Control: maxage=".$expires);
		header("Pragma: public");
		header("Expires: ".gmdate('D, d M Y H:i:s', time()+$expires));	
		$filter="(&(phpgwAccountType=u)(mail=" . $params['email'] . "))";
		$ldap_context = $_SESSION['phpgw_info']['expressomail']['ldap_server']['dn'];
		if($_SESSION['phpgw_info']['user']['preferences']['expressoMail']['extended_info'])
		    $extendedinfo=true;
		else
		    $extendedinfo=false;

		if($extendedinfo)
		    $justthese = array("cn","uid","telephoneNumber","jpegPhoto","mobile","ou","employeeNumber");
		else
		    $justthese = array("cn","uid","telephoneNumber","jpegPhoto");

		// Follow the referral
		$ds = $this->ldapConnect2(true);
		if ($ds)
		{
			$sr=@ldap_search($ds, $ldap_context, $filter, $justthese);

			if (!$sr)
				return null;

			$entry = ldap_first_entry($ds, $sr);

			if($entry) {
				$obj =  array("cn" => utf8_decode(current(ldap_get_values($ds, $entry, "cn"))),
						  "email" => $params['email'],
						  "uid" => ldap_get_values($ds, $entry, "uid"),
						  "type" => "global",
						  "mobile" =>  @ldap_get_values($ds, $entry, "mobile"),
						  "telefone" =>  @ldap_get_values($ds, $entry, "telephonenumber"),
						  "ou" =>  @ldap_get_values($ds, $entry, "ou"),
						  "employeeNumber" =>  @ldap_get_values($ds, $entry, "employeeNumber")
					);

				$_SESSION['phpgw_info']['expressomail']['contact_photo'] = @ldap_get_values_len($ds, $entry, "jpegphoto");
				ldap_close($ds);
				return $obj;
			}
		}
		return null;
	}
	
	function uid2uidnumber($uid)
	{
		// do not follow the referral
		$this->ldapRootConnect(false);
		if ($this->ds)
		{
			$filter="(&(phpgwAccountType=u)(uid=$uid))";
			$justthese = array("uidnumber");
			$sr=@ldap_search($this->ds, $this->ldap_context, $filter, $justthese);
			if(!$sr)
				return false;
			$info = ldap_get_entries($this->ds, $sr);
			return $info[0]["uidnumber"][0];
		}
		return false;
	}
	
	function get_user_groups($uid)
	{

		$organizations = array();
	
		$this->ldapRootConnect();

                $justthese = array("gidnumber","cn");
                $filter="(&(phpgwAccountType=g)(memberuid=".$uid."))";

                $search = ldap_search($this->ds, $this->ldap_context, $filter, $justthese);

                $result = array();
                $entries = ldap_get_entries($this->ds, $search);


                for ($i=0; $i<$entries['count']; ++$i)
                {
                        $result[ $entries[$i]['gidnumber'][0] ] = $entries[$i]['cn'][0];
                }

		return $result;
	}
        
       function getMailByUid($pUid)
        {
                // do not follow the referral
                $this->ldapRootConnect(false);
                if ($this->ds)
                {
                        $filter="(&(|(phpgwAccountType=u)(phpgwAccountType=s)(phpgwAccountType=l))(uid=$pUid))";
                        $justthese = array("mail");
                        $sr=@ldap_search($this->ds, $this->ldap_context, $filter, $justthese);
                        if(!$sr)
                                return false;
                        $info = ldap_get_entries($this->ds, $sr);


                        return utf8_decode($info[0]["mail"][0]);
                }
                return false;
        } 
  
        function mail2uid($mail)
	{
                if(!$this-ds)
                    $this->ldapRootConnect(false);

                $filter="(&(|(phpgwAccountType=u)(phpgwAccountType=s)(phpgwAccountType=i)(phpgwAccountType=g))(mail=$mail))";
                $justthese = array("uid");
                $sr=@ldap_search($this->ds, $this->ldap_context, $filter, $justthese);
                if(!$sr)
                    return false;
                $info = ldap_get_entries($this->ds, $sr);
                return $info[0]["uid"][0];
	}

        
        /**
         * Retorna as contas compartilhas
         * @param string $toaddress emails
         * @param string $ccaddress emails
         * @param string $ccoaddress emails
         * @param array $groups array com os grupos que o usuario pertence
         * @return array
         */
        function returnSharedsAccounts($toaddress,$ccaddress,$ccoaddress)
        {

          $arrayAllAddres = array();
          $arrayAllAddres =  array_merge($arrayAllAddres , explode(',',$toaddress));
          $arrayAllAddres =  array_merge($arrayAllAddres, explode(',',$ccaddress));
          $arrayAllAddres = array_merge($arrayAllAddres, explode(',',$ccoaddress));

          $mailsArray = array();

           foreach ($arrayAllAddres as $toAddres)
           {

               if(strchr($toAddres,'@') && strchr($toAddres,'<') && strchr($toAddres,'>'))
               {
                    $alias = substr($toAddres, strpos($toAddres,'<'), strpos($toAddres,'>'));
                    $alias = str_replace('<','', str_replace('>','',$alias));
                    array_push($mailsArray, $alias);
               }
               else if(strchr($toAddres,'@'))
               {
                    array_push($mailsArray, $toAddres);
               }
           }
           $arraySharedAccounts = array();

           $conexao = $this->ldapConnect2(true);


           $mailFilter = '';

           foreach ($mailsArray as $mail)
             $mailFilter .= '(|(mail='.$mail.')(mailAlternateAddress='.$mail.'))';


           $filter = '(&(phpgwAccountType=s)(|'.$mailFilter.') )';
           $ldap_context = $_SESSION['phpgw_info']['expressomail']['server']['ldap_context'];
           $justthese = array('cn','uid','mail');

           if ($conexao)
           {
                $search = @ldap_search($conexao, $ldap_context, $filter, $justthese);
                if($search)
                {
                    $results = ldap_get_entries($conexao, $search);

                    foreach ($results as $result)
                    {
                        if($result['mail'][0])
                            array_push($arraySharedAccounts, $result['mail'][0]);
                    }
                }

           }

           return $arraySharedAccounts;
        }
        /**
        * Verifica se um email é uma conta compartilhada
        *
        * @license    http://www.gnu.org/copyleft/gpl.html GPL
        * @author     Cons?rcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
        * @author     Cristiano Corrêa Schmidt
        * @param      String $mail Email a ser verificado
        * @return     bolean
        * @access     public
        */
        function isSharedAccountByMail($mail)
        {
            $return = false;
            $conexao = $this->ldapConnect2(true);
            $filter = '(&(phpgwAccountType=s)(mail='.$mail.'))';
            $ldap_context = $_SESSION['phpgw_info']['expressomail']['server']['ldap_context'];
            $justthese = array('cn','uid','mail');
            if ($conexao)
            {
                $search = @ldap_search($conexao, $ldap_context, $filter, $justthese);
                if(ldap_count_entries ($conexao , $search))
                    $return = true;
            }

            return $return;
        }

        function save_telephoneNumber($params){
        	$return = array();
        	if($_SESSION['phpgw_info']['user']['preferences']['expressoMail']['blockpersonaldata']){
        		$return['error'] = $this->functions->getLang("You can't modify your Commercial Telephone.");
        		return $return;
        	}
        	$old_telephone = 0;
        	$pattern = '/\([0-9]{2,3}\)[0-9]{4}-[0-9]{4}$/';
        	if ((strlen($params['number']) != 0) && (!preg_match($pattern, $params['number'])))
        		{
        		$return['error'] = $this->functions->getLang('The format of telephone number is invalid');
        		return $return;
        	}
        	if($params['number'] != $_SESSION['phpgw_info']['user']['preferences']['expressoMail']['telephone_number']) {
        		$old_telephone = $_SESSION['phpgw_info']['user']['preferences']['expressoMail']['telephone_number'];
        		$this->ldapRootConnect(false);
        		if(strlen($_SESSION['phpgw_info']['user']['preferences']['expressoMail']['telephone_number']) == 0) {
        			$info['telephonenumber'] = $params['number'];
        			$result = @ldap_mod_add($this->ds, $_SESSION['phpgw_info']['expressomail']['user']['account_dn'], $info);
        		}
        		else {
        			$info['telephonenumber'] = $params['number'];
        			$result = @ldap_mod_replace($this->ds, $_SESSION['phpgw_info']['expressomail']['user']['account_dn'], $info);
        		}
        		$_SESSION['phpgw_info']['user']['preferences']['expressoMail']['telephone_number'] = $info['telephonenumber'];
				// 	Log updated telephone number by user action
				include_once('class.db_functions.inc.php');
				$db_functions = new db_functions();
    			$db_functions->write_log('modified user telephone',"User changed its own telephone number in preferences $old_telephone => ".$info['telephonenumber']);
    			unset($info['telephonenumber']);
    		}
    		return $return['ok'] = true;
    	}        
}
?>
