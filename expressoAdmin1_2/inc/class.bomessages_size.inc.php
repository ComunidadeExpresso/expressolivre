<?php
	/***********************************************************************************\
	* Expresso Administra��o															*
	* by Prognus Software Livre (prognus@prognus.com.br, airton@prognus.com.br)      	*
	* ----------------------------------------------------------------------------------*
	*  This program is free software; you can redistribute it and/or modify it			*
	*  under the terms of the GNU General Public License as published by the			*
	*  Free Software Foundation; either version 2 of the License, or (at your			*
	*  option) any later version.														*
	\***********************************************************************************/

include_once('class.functions.inc.php');
include_once('class.ldap_functions.inc.php');
include_once('class.somessages_size.inc.php');
require_once 'class.db_functions.inc.php';
include_once(PHPGW_API_INC.'/class.common.inc.php');


	class bomessages_size
	{
		var $so;
		var $dbFunctions;
		var $functions;
		var $ldap_functions;
		var $manager_contexts;
		var $current_config;
		
		/**
         * Construtor
         */
		function bomessages_size()
		{					
			$this->so = new somessages_size();
			$this->dbFunctions = new db_functions();
			$this->ldap_functions = new ldap_functions();			
			$this->functions = new functions();
			$common = new common();
			
			/*
			if ((!empty($GLOBALS['phpgw_info']['server']['ldap_master_host'])) &&
				(!empty($GLOBALS['phpgw_info']['server']['ldap_master_root_dn'])) &&
				(!empty($GLOBALS['phpgw_info']['server']['ldap_master_root_pw'])) )
			{
				$this->ldap_functions = $common->ldapConnect($GLOBALS['phpgw_info']['server']['ldap_master_host'],
															$GLOBALS['phpgw_info']['server']['ldap_master_root_dn'],
															$GLOBALS['phpgw_info']['server']['ldap_master_root_pw']
															);
			}
			else
			{
				$this->ldap_functions = $common->ldapConnect();
			}*/
		}
		
	
		/**
         * @abstract Cria uma nova regra.
		 * @params array params com as informa��es do formul�rio com os dados da nova regra.
         * @return mixed retorna um status informando se a opera��o foi conclu�da com sucesso e uma mensagem de erro ou sucesso.
         */
		function create_rule($params)
		{			
			/* In�cio da valida��o dos campos do form */
			
			/* Verifica se o nome da regra foi preenchida */
			if(empty($params['rule_name']))
			{
				$result['status'] = false;
				$result['msg']  = $this->functions->lang('Field rule name is empty');		
				return $result;
			} 
			/* Verifica se o valor m�ximo da mensagem foi configurado */
			if (empty($params['max_messages_size']))
			{
				$result['status'] = false;
				$result['msg']  = $this->functions->lang('Field message size is empty');	
				return $result;
			}
			/* Verifica se esse valor � um n�mero */
			if((!preg_match("/^[0-9]+$/i", $params['max_messages_size'])))
			{
				$result['status'] = false;
                                                        $result['msg']  = $this->functions->lang('Field size must be a number');
				return $result;
			
			}
			/* Verifica se no nome da regra existe o caracter % (n�o permitido) */
			if(strpos($params['rule_name'],"%"))
			{
				$result['status'] = false;
				$result['msg']  = $this->functions->lang('Invalid character in name.');
				return $result;
			}
			/* Verifica se j� existe uma regra com o mesmo nome */
			if($this->so->get_rule($params['rule_name'])) 
			{	
				$result['status'] = false;
				$result['msg']  = $this->functions->lang('Rule name already in use.');
				return $result;	
			}
			/* Verifica se selecionou algum usu�rio pra nova regra */				
			if(!$params['owners'])
                return array('status' => false , 'msg' => 'no groups or user selecteds');
			
			/* Fim da valida��o dos campos do form */
			
			
			foreach ($params['owners'] as $user_selected)
            {
                
				$check_user_selected = explode(',', $user_selected);
				$user_selected = $check_user_selected[0];
				$type_user = $check_user_selected[1];

				$fields = array(
                            'email_user' => $user_selected,
                            'configuration_type' => 'MessageMaxSize',
                            'email_max_recipient' => $params['max_messages_size'],
                            'email_user_type' => $type_user,
							'email_recipient' => $params['rule_name']
							);

                if(!$this->so->insert_rule_db($fields))
                    return array('status' => false , 'msg' => 'Error on insert');
				else
					$this->dbFunctions->write_log('Rule message size created', $userInRuleDB);
            }
			return array('status' => true);
		}
		
		
		/**
         * @abstract Salva a regra padr�o de tamanho de mensagens.
		 * @params params - informa��o do novo tamanho padr�o.
         * @return retorna um status informando se a opera��o foi conclu�da com sucesso e uma mensagem de erro ou sucesso.
         */      
		function save_default_rule($params)
		{
			if(!$this->so->insert_default_rule_2($params['default_max_size']))
				return array('status' => false, 'msg' => 'Error on insert default rule');
			else 
				$this->dbFunctions->write_log('Default size rule message saved', $params['default_max_size']);
				
			return array('status' => true, 'msg' => 'Default rule insert ok!');
		}
		

		/**
         * @abstract Salva uma regra que foi aberta para edi��o.
		 * @params params - informa��es da regra vindas do formul�rio.
         * @return retorna um status informando se a opera��o foi conclu�da com sucesso e uma mensagem de erro ou sucesso.
         */
		function save_rule($params)
		{
			$usuarios = array();
			foreach($params['owners'] as $i=>$value)
			{
				$usuarios[] = $value;
			}
			
			$users = $this->so->get_users_by_rule($params['original_rule_name']);	

			/* In�cio da valida��o dos campos do form */
			
			/* Verifica se o nome da regra foi preenchida */
			if(empty($params['rule_name']))
			{
				$result['status'] = false;
				$result['msg']  = $this->functions->lang('Field rule name is empty');		
				return $result;
			} 
			/* Verifica se o valor m�ximo da mensagem foi configurado */
			if (empty($params['max_messages_size']))
			{
				$result['status'] = false;
				$result['msg']  = $this->functions->lang('Field message size is empty');	
				return $result;
			}
			/* Verifica se esse valor � um n�mero */
			if((!preg_match('/^[0-9]+$/i', $params['max_messages_size'])))
			{
				$result['status'] = false;
				$result['msg']  = $this->functions->lang('Field size must be a number');
				return $result;
			
			}
			/* Verifica se no nome da regra existe o caracter % (n�o permitido) */
			if(strpos($params['rule_name'],"%"))
			{
				$result['status'] = false;
				$result['msg']  = $this->functions->lang('Invalid character in name.');
				return $result;
			}
			/* Fim da valida��o dos campos do form */	

			$rule_name = $users[0]['email_recipient'];
			$max_size  = $users[0]['email_max_recipient'];
			
			// Deleto os usu�rios da regra que est� sendo editada pra inserir novamente pra ter um controle dos usu�rios exclu�dos e adicionados.
			if (!($this->so->remove_rule($rule_name))) 
			{
				return array('status' => false , 'msg' => 'Error on delete a parada!');
			} 
	
			foreach ($usuarios as $user_selected)
            {     
				$check_user_selected = explode(',', $user_selected);
				$user_selected = $check_user_selected[0];
				$type_user = $check_user_selected[1];
			
                $fields = array(
                            'email_user' => $user_selected,
                            'configuration_type' => 'MessageMaxSize',
                            'email_max_recipient' => $params['max_messages_size'],
                            'email_user_type' => $type_user,
							'email_recipient' => $params['rule_name']
                            );
        
				if(!$this->so->insert_rule_db($fields))
                    return array('status' => false , 'msg' => 'Error on insert');
				else
					$this->dbFunctions->write_log('Rule message size created', $userInRuleDB);	
            }
			return array('status' => true, 'msg' => 'Salvo com sucessso!!');
		}
		
		
		/**
         * @abstract Recupera o valor da regra padr�o.
         * @return retorna o valor da regra padr�o.
         */
		function get_default_rule()
		{
			$return = $this->so->get_default_rule();
			if (!$return)
				return false;
			
			foreach($return as $i=>$value)
			{			
				return $value['config_value'];
			}				

		}
		
		
		/**
         * @abstract Deleta uma regra.
		 * @params params['rule_name'] - nome da regra que deseja excluir
         * @return retorna um status informando se a opera��o foi conclu�da com sucesso e uma mensagem de erro ou sucesso.
         */
		function delete_rule($params)
		{
			if (!($this->so->remove_rule($params['rule_name']))) 
				return array('status' => false , 'msg' => 'Error on delete!');
			else
				$this->dbFunctions->write_log('Rule message size removed', $params['rule_name']);	
				
			return array('status' => true , 'msg' => 'Rule successful deleted!');
		}
		
		
		/**
         * @abstract Busca todas as regras cadastradas.
         * @return array com as informa��es das regras cadastradas
         */
		function get_all_rules()
		{
			$return = $this->so->get_all_rules();
			return $return;
		}
		
		
	    /**
         * @abstract Busca as regras cadastradas por usu�rio.
		 * @params input - parte do nome do usu�rio que deseja encontrar a regra a que ele pertence.
         * @return array com as informa��es das regras cadastradas que batem com o padr�o da busca.
         */
		function get_rules_by_user($params)
		{
			if(!empty($params['input']))
				$rules = $this->so->get_rules($params['input'], true);
			else
				$rules = $this->get_all_rules();
				
			$rules_tr = '';

            $rules_count = count($rules);
			for ($i = 0; $i<$rules_count; ++$i)
			{
				$name_link = (string)str_replace(" ", "%", $rules[$i]['email_recipient']);
				$rules_tr .= "<tr class='normal' onMouseOver=this.className='selected' onMouseOut=this.className='normal'><td onClick=javascript:edit_messages_size('" . $name_link . "')>" . $rules[$i]['email_recipient'] . "</td><td onClick=edit_messages_size('$name_link')>" . $rules[$i]['email_max_recipient'] . " MB</td><td align='center' onClick=delete_messages_size('$name_link')><img HEIGHT='16' WIDTH='16' src=./expressoAdmin1_2/templates/default/images/delete.png></td></tr>";
			}
			
			$return['status'] = 'true';
			$return['trs'] = $rules_tr;			
			return $return;		
		}
		
		
		/**
         * @abstract Busca as regras cadastradas por nome de regra.
		 * @params input - parte do nome da regra que deseja encontrar.
         * @return array com as informa��es das regras cadastradas que batem com o padr�o da busca.
         */
		function get_rules($params)
		{
			if($params['input']!='')
				$rules = $this->so->get_rules($params['input']);
			else
				$rules = $this->get_all_rules();
			
			$rules_tr = '';

            $rules_count = count($rules);
			for ($i = 0; $i<$rules_count; ++$i)
			{
				$name_link = (string)str_replace(" ", "%", $rules[$i]['email_recipient']);
				$rules_tr .= "<tr class='normal' onMouseOver=this.className='selected' onMouseOut=this.className='normal'><td onClick=javascript:edit_messages_size('" . $name_link . "')>" . $rules[$i]['email_recipient'] . "</td><td onClick=edit_messages_size('$name_link')>" . $rules[$i]['email_max_recipient'] . " MB</td><td align='center' onClick=delete_messages_size('$name_link')><img HEIGHT='16' WIDTH='16' src=./expressoAdmin1_2/templates/default/images/delete.png></td></tr>";
			}
			
			$return['status'] = 'true';
			$return['trs'] = $rules_tr;			
			return $return;		
		}
		
		
		/**
         * @abstract Pega usu�rios de uma regra.
		 * @params params['rule_name'] - nome da regra que deseja os usu�rios.
         * @return retorna os usu�rios dessa regra. Se o par�metro option for true, ele retorna j� formatado com os options.
         */
		function get_users_by_rule($params, $option=true) 
		{
			$return = $this->so->get_users_by_rule($params['name_rule']);

			if($return)
			{
				if($option)
				{
					if (count($return))
					{
						foreach ($return as $i => $value)
						{	
							if($return[$i]['email_user_type'] == 'G')
							{
								$gid_grupo = $return[$i]['email_user'];
								$cn = $this->ldap_functions->get_group_cn_by_gidnumber($return[$i]['email_user']);
								$options .= '<option value="'.$gid_grupo.',G">'.$cn.'('.$gid_grupo.')</option>';
							}
							else
							{
								$uid_usuario = $return[$i]['email_user'];
								$cn = $this->ldap_functions->get_user_cn_by_uid($return[$i]['email_user']);							
								$options .= '<option value="'.$uid_usuario.',U">'.$cn.'('.$uid_usuario.')</option>';
							}
						}
					}
					$return[0]['options'] = $options;
					$return[0]['status'] = true;
					$return[0]['msg'] = "Retornou alguma coisa do banco. Agora � s� tratar de chamar o modal.";
                
					return $return[0];
				}
				else // Retornar os usu�rios mas n�o com formata��o de option
				{
				 	$users;
					if (count($return))
					{
						foreach ($return as $i => $value)
						{
							$users .= $return[$i]['email_user'] . "%";
							//array_push($users, $return[$i]['email_user']);
						}
					}

					
					$return[0]['users'] = $users;
					$return[0]['status'] = true;
					$return[0]['msg'] = "Retornou alguma coisa do banco. Agora � s� tratar de chamar o modal.";	
					return $return[0];
				}
					
			} 
			else  
				return array('status' => false , 'msg' => 'Error on read rule');
		}
		
		
	    /** @abstract Busca usuarios de um contexto.
		 *	@params array parrams que vem com as informa��es do contexto e outros dados vindos do formul�rio.
		 *  @reutn retorna os usu�rios j� com as op��es do select pra preencher a tela modal com os usu�rios dispon�veis.
		 */
		function get_available_users($params)
		{
			$context= $params['context'];
			$justthese = array("cn", "uid");
			$filter = "&(phpgwAccountType=u)(!(phpgwAccountVisible=-1))";
	
			if( $params['sentence'] ) 
				$filter .= "(cn=*${params[sentence]}*)";
	
			if ($this->ldap_functions)
			{
				$sr=ldap_search($this->ldap_functions, $context, "($filter)", $justthese);
				$entries = ldap_get_entries($this->ldap_functions, $sr);			
	
				for ($i=0; $i<$entries["count"]; ++$i){
						$u_tmp[$entries[$i]["uid"][0]] = $entries[$i]["cn"][0];
				}
	
				natcasesort($u_tmp);
	
				$i = 0;
				$users = array();
				$options = array();
	
				if (count($u_tmp))
				{
					foreach ($u_tmp as $uidnumber => $cn)
					{
						$options[] = '"'.$uidnumber.'"'.':'.'"'.utf8_decode($cn).'"';
					}
					unset($u_tmp);
				}			
				return "{".implode(',',$options)."}";
			}
		}
		
		
	    /** @abstract Busca usuarios e grupos de um contexto.
		 *	@params array parrams que vem com as informa��es do contexto e outros dados vindos do formul�rio.
		 *  @reutn retorna os usu�rios j� com as op��es do select pra preencher a tela modal com os usu�rios dispon�veis.
		 */
		function get_available_users_and_groups($params)
        {

            $this->ds = $this->ldap_functions->ldapMasterConnect();

             //Monta lista de Grupos e Usuarios
	        $users = Array();
	        $groups = Array();
	        $user_context= $this->ldap_functions->ldap_context;
	        
	        $filtro =utf8_encode($params['filter']);
	        $context =utf8_encode($params['context']);

        	if ($this->ds)
	        {
	            $justthese = array("gidNumber","cn","dn");
	            $sr=ldap_search($this->ds, $context, ("(&(phpgwaccounttype=g)(!(phpgwaccountvisible=-1))(cn=*$filtro*))"),$justthese);
                    $info = ldap_get_entries($this->ds, $sr);

	            for ($i=0; $i<$info["count"]; ++$i)
	                $groups[$uids=$info[$i]["gidnumber"][0]] = Array('name'    =>    $uids=$info[$i]["cn"][0], 'type'    =>    g);

                    $justthese = array("phpgwaccountvisible","uidNumber","cn","uid");
	                 $sr=ldap_search($this->ds, $context, ("(&(phpgwaccounttype=u)(!(phpgwaccountvisible=-1))(phpgwaccountstatus=A)(|(cn=*$filtro*)(mail=$filtro*)))"),$justthese);
	          
	            $info = ldap_get_entries($this->ds, $sr);

	            for ($i=0; $i<$info["count"]; ++$i)
	            {
	                if ($info[$i]["phpgwaccountvisible"][0] == '-1')
              	      continue;
                	$users[$uids=$info[$i]["uidnumber"][0]] = Array('name'    =>    $uids=$info[$i]["cn"][0], 'type'    =>    u,'uid' => $info[$i]["uid"][0]);
	            }
	        }
	        ldap_close($this->ds);

        	@asort($users);
	        @reset($users);
	        @asort($groups);
	        @reset($groups);
	        $user_options ='';
	        $group_options ='';
                $user_options2 ='';
	        $group_options2 ='';

            foreach($groups as $id => $group_array) {
                    $group_options .= '<option  value="'.$id.'">'.utf8_decode($group_array['name']).'</option>'."\n";
                    $group_options2 .= '<option  value="'.$id.',G">'.utf8_decode($group_array['name']).'</option>'."\n";
            }
            foreach($users as $id => $user_array) {
                    $user_options .= '<option  value="'.utf8_decode($user_array['uid']).'">'.utf8_decode($user_array['name']).'</option>'."\n";
                    $user_options2 .= '<option  value="'.utf8_decode($user_array['uid']).',U">'.utf8_decode($user_array['name']).'</option>'."\n";
                
            }

            return array("users" => $user_options, "groups" => $group_options , "users2" => $user_options2, "groups2" => $group_options2);
        }
		
		
		function get_available_users_and_groups2($params)
		{
			$returnLDAP = $this->ldap_functions->get_available_users3($params);

			$return = array();
			$return['users'] = $returnLDAP['users2'];
			$return['groups'] = $returnLDAP['groups2'];

			return $return;
		}
		
		
	} // end class bomessages_size
?>
