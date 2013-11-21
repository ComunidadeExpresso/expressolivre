<?php
	/**********************************************************************************\
	* Expresso Administra��o                 									      *
	* by Joao Alfredo Knopik Junior (joao.alfredo@gmail.com, jakjr@celepar.pr.gov.br) *
	* --------------------------------------------------------------------------------*
	*  This program is free software; you can redistribute it and/or modify it		  *
	*  under the terms of the GNU General Public License as published by the		  *
	*  Free Software Foundation; either version 2 of the License, or (at your		  *
	*  option) any later version.													  *
	\**********************************************************************************/
	
	include_once('class.ldap_functions.inc.php');
	include_once('class.db_functions.inc.php');
	include_once('class.functions.inc.php');
	
	class maillist
	{
		var $ldap_functions;
		var $db_functions;
		var $functions;
		var $current_config;
		
		
		function maillist()
		{
			$this->ldap_functions = new ldap_functions;
			$this->db_functions = new db_functions;
			$this->functions = new functions;
			$this->current_config = $_SESSION['phpgw_info']['expresso']['listAdmin']; 
		}
		
		function validate_fields($params)
		{
			return $this->ldap_functions->validate_fields_maillist($params);
		}
		
		function create($params)
		{
			// Verifica o acesso do gerente
	/*		if (!$this->functions->check_acl($_SESSION['phpgw_info']['expresso']['user']['account_lid'], 'add_maillists'))
			{
				$return['status'] = false;
				$return['msg'] = 'Voc� n�o tem acesso para adicionar listas de email.';
				return $return;
			}
	*/		

			$return['status'] = true;
			
			//Retira os uids duplicados se existir
			$array_tmp = array();
			$array_tmp = array_unique($params['members']);
			$params['members'] = $array_tmp;
			
				// Pega ID do BD e incrementa de 1 (vai ser o uidnumber da lista)
			$id = (($this->db_functions->get_next_id()) + 1);
			
			// Incrementa o id no BD.
			$this->db_functions->increment_id($id,'accounts');

			//DN do repositorio das listas de e-mail; usado para gravacao de listas manuais
			$dnListas = $this->current_config['dn_listas'];
			//Dominio do servidor de listas; usado na composicao do endereco de e-mail das listas
			$dominioListas = $this->current_config['dominio_listas'];

			// Cria array para incluir no LDAP
			//$dn = 'uid=' . $params['uid'] . ',' . $params['context'];
			$dn = 'uid=' . $params['uid'] . ',' . $dnListas;
			
			$maillist_info = array();
			$maillist_info['uid']					= $params['uid'];  
			$maillist_info['givenName']				= 'MailList';
			$maillist_info['sn']					= $params['uid'];
			$maillist_info['cn']					= $params['uid'];
			
			$maillist_info['homeDirectory']				= '/home/false';
			$maillist_info['loginShell']				= '/bin/false';
			$maillist_info['mail']					= $params['uid'] . "@" . $dominioListas;
			$maillist_info['description']				= $params['description'];
			
			$expressoAdmin 						= $GLOBALS['phpgw_info']['server']['header_admin_user'];
			//Obtem uidnumber baseado no uid do usuario expresso-admin
			$admUidnumber						= $this->ldap_functions->uid2uidnumber($expressoAdmin);
			//Obten email baseado no uidnumber do usuario expresso-admin
			$adminlista						= $this->ldap_functions->uidnumber2mail($admUidnumber);

			//Pega o id do usuario atual da sessao
			$usuarioAtual 						= $_SESSION['phpgw_info']['expresso']['user']['account_lid'];
			//Pega o uidnumber do usuario atual
			$uidUsuarioAtual 					= $this->ldap_functions->uid2uidnumber($usuarioAtual);
			//Pega o email do usuario atual
			$mailUsuarioAtual 					= $this->ldap_functions->uidnumber2mail($uidUsuarioAtual);

			//adiciona o expresso-admin como administrador padrao da lista no campo admlista 
			$adm_info['admlista'][0] 				= $adminlista;
//			system('echo "admlista: '.$GLOBALS['phpgw_info']['server']['header_admin_user'].'">>/tmp/teste.log');
			//adiciona o usuario corrente como administrador da lista
			$adm_info['admlista'][1] 				= $mailUsuarioAtual;

			//verifica se o usuario logado (corrente) eh igual ao administrador padrao (expresso-admin)
			if($adm_info['admlista'][0] == $adm_info['admlista'][1]) {
				$maillist_info['admlista'][0] = $adm_info['admlista'][0];
			}else {
				$maillist_info['admlista'][0] = $adm_info['admlista'][0];
				$maillist_info['admlista'][1] = $adm_info['admlista'][1];
			}


			//Modifica a geracao de senha pro mailman
			$senhaCripto                                            = $params['listPass'];
                        $maillist_info['listPass']				= $senhaCripto;
//			system('echo "listPass: '.$senhaCripto.'">>/tmp/teste.log');
                        //$maillist_info['listPass']				= 'senha';
			$maillist_info['objectClass'][0]			= 'posixAccount';
			$maillist_info['objectClass'][1]			= 'inetOrgPerson';
			$maillist_info['objectClass'][2]			= 'shadowAccount';
			//$maillist_info['objectClass'][3]			= 'qmailuser';
			$maillist_info['objectClass'][3]			= 'phpgwAccount';
			$maillist_info['objectClass'][4]			= 'top';
			$maillist_info['objectClass'][5]			= 'person';
			$maillist_info['objectClass'][6]			= 'organizationalPerson';		
			$maillist_info['objectClass'][7]                        = 'mailman';		
			//$maillist_info['phpgwAccountExpires']		        = '-1';
			//$maillist_info[$GLOBALS['phpgw_info']['server']['atributoexpiracao']] = '-1';
			if(isset($GLOBALS['phpgw_info']['server']['atributoexpiracao']))
					{
					$maillist_info[$GLOBALS['phpgw_info']['server']['atributoexpiracao']] = '-1';
					}
				else
					{
					$maillist_info['phpgwaccountexpires'] = '-1';
					}
			$maillist_info['phpgwAccountType']			= 'l';
			$maillist_info['phpgwAccountStatus']			= 'A';
			$maillist_info['uidnumber']				= $id;
			$maillist_info['gidnumber']				= '0';
			$maillist_info['deliveryMode']				= 'forwardOnly';
			
			if ($params['accountStatus'] == 'on')
				$maillist_info['accountStatus'] = 'active';
			
			if ($params['phpgwAccountVisible'] == 'on')
				$maillist_info['phpgwAccountVisible'] = '-1';

			if ($params['defaultMemberModeration'] == 'on')
                                $maillist_info['defaultMemberModeration'] = '1';

			foreach($params['members'] as $index=>$uidnumber)
			{
				$this->db_functions->write_log($params['context'],",",$params['cn']);
				$mail = $this->ldap_functions->uidnumber2mail($uidnumber);

				//Este if foi adicionado para tratar a situacao de uma lista ser criada com usuario(s) externo(s)
				if($mail == '')
				{
					//adiciona ao vetor o(s) usuario(s) externo(s)
					$maillist_info['mailForwardingAddress'][] = $uidnumber;
				} else{	
					//adiciona ao vetor o(s) usuario(s) exitente(s) no RHDS
					$maillist_info['mailForwardingAddress'][] = $mail;
				}

				//$maillist_info['mailForwardingAddress'][] = $mail;
				$this->db_functions->write_log("Adicionado usuario $mail a lista ".$params['cn']." no momento da cria��o",$dn,$uidnumber,'','');
			}
			$result = $this->ldap_functions->ldap_add_entry($dn, $maillist_info);			

			if (!$result['status'])
			{
				$return['status'] = false;
				$return['msg'] .= $result['msg'];
			}
			
			if ($return['status'] == true)
			{
				$this->db_functions->write_log('Criado lista de email','',$dn,'','');
			}
			
			$opts = null;
			$obj = null;
			return $return;
		}
		
		function save($new_values)
		{
			// Verifica o acesso do gerente
	/*		if (!$this->functions->check_acl($_SESSION['phpgw_info']['expresso']['user']['account_lid'], 'edit_maillists'))
			{
				$return['status'] = false;
				$return['msg'] = 'Voc� n�o tem acesso para editar listas de email.';
				return $return;
			}
*/
			if ($params['accountAdm'] == 'on'){
                                $teste = 'A';
				$return['msg'] = '$teste';
                        }

			$return['status'] = true;
			
			//Retira os uids duplicados se existir
			$array_tmp = array();
			$array_tmp = array_unique($new_values['members']);
			$new_values['members'] = $array_tmp;
	                
			$old_values = $this->get_info($new_values['uidnumber'], $new_values['manager_context']);
			$diff = array_diff($new_values, $old_values);
			
			$dn = 'uid=' . $old_values['uid'] . ',' . $old_values['context'];
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// RENAME
/*			if ($diff['context'] || $diff['uid'])
			{
				$newrdn = 'uid=' . $new_values['uid'];
				$newparent = $new_values['context'];
				$result =  $this->ldap_functions->change_user_context($dn, $newrdn, $newparent);
				if (!$result['status'])
				{
					$return['status'] = false;
					$return['msg'] .= $result['msg'];
				}
				else
				{
					$dn = $newrdn . ',' . $newparent;
					$old_dn = $old_values['uid'];
					$this->db_functions->write_log("Renomeado login da lista de $old_dn para $dn",'',$dn,$old_values['uid'],'');
				}
			}*/
			
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// REPLACE MAIL || CN || SN
			if ($new_values['mail'] != $old_values['mail'])
			{
				$ldap_mod_replace['mail'] = $new_values['mail'];
				$this->db_functions->write_log('Modificado email da lista para ' . $new_values['mail'],'',$dn,'','');
			}

			if ($new_values['listPass'] != $old_values['listPass'])
                        {
//                              $tmpPassword     = '{md5}' . base64_encode(pack("H*",md5($new_values['userPassword'])));
				$senhaCripto     = $new_values['listPass'];
                                $tmpPassword     = encriptar($senhaCripto);
				$ldap_mod_replace['listPass'] = $tmpPassword;
                                $this->db_functions->write_log('Modificado senha da lista para ' . $tmpPassword ,'',$dn,'','');
                        }

			if ($new_values['cn'] != $old_values['cn'])
			//if ($diff['uid'])
			{
				$ldap_mod_replace['cn'] = $new_values['uid'];
				$ldap_mod_replace['sn'] = $new_values['uid'];
		//		$ldap_mod_replace['uid'] = $new_values['uid'];
				$this->db_functions->write_log("Modificado common name da lista $dn",'',$dn,'','');
			}
			
	/*		if ($diff['sn'])
			{
				$ldap_mod_replace['uid'] = $new_values['uid'];
			}
	*/		
			if (count($ldap_mod_replace))
			{
				$result = $this->ldap_functions->replace_user_attributes($dn, $ldap_mod_replace);
				if (!$result['status'])
				{
					$return['status'] = false;
					$return['msg'] .= $result['msg'];
				}
			}
			
			//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// REMOVE ATTRS
			if (($old_values['accountStatus'] == 'active') && ($new_values['accountStatus'] != 'on'))
				$ldap_remove['accountStatus']	= array();
			
			if (($old_values['defaultMemberModeration'] == '1') && ($new_values['defaultMemberModeration'] != 'on'))
                                $ldap_remove['defaultMemberModeration']   = array();		

				
			if (($old_values['phpgwAccountVisible'] == '-1') && ($new_values['phpgwAccountVisible'] != 'on'))
				$ldap_remove['phpgwAccountVisible']	= array();
			
			if (count($ldap_remove))
			{
				$result = $this->ldap_functions->remove_user_attributes($dn, $ldap_remove);
				if (!$result['status'])
				{
					$return['status'] = false;
					$return['msg'] .= $result['msg'];
				}
			}
			
			//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// ADD ATTRS
			if (($old_values['accountStatus'] != 'active') && ($new_values['accountStatus'] == 'on'))
				$ldap_add['accountStatus']	= 'active';

			if (($old_values['defaultMemberModeration'] != '1') && ($new_values['defaultMemberModeration'] == 'on'))
                                $ldap_add['defaultMemberModeration']      = '1';	
			
		
			if (($old_values['phpgwAccountVisible'] != '-1') && ($new_values['phpgwAccountVisible'] == 'on'))
				$ldap_add['phpgwAccountVisible'] = '-1';
			
			if (count($ldap_add))
			{
				$result = $this->ldap_functions->add_user_attributes($dn, $ldap_add);
				if (!$result['status'])
				{
					$return['status'] = false;
					$return['msg'] .= $result['msg'];
				}
			}
			
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// USERS

			if (!$new_values['members'])
				$new_values['members'] = array();
			if (!$old_values['members'])
				$old_values['members'] = array();

			$add_users = array_diff($new_values['members'], $old_values['members']);

			$remove_users = array_diff($old_values['members'], $new_values['members']);
			
			if (count($add_users)>0)
			{
				$array_emails_add = array();
				foreach($add_users as $uidnumber)
				{
					$mail = $this->ldap_functions->uidnumber2mail($uidnumber);
					usleep(100000);

					//caso nao exista o e-mail do usuario informado (usuario externo);
					if(empty($mail)){
						$mail = $uidnumber;
					}

					$array_emails_add[] = $mail;
					$this->db_functions->write_log("Adicionado usuario $mail $teste a lista",$dn,$uidnumber,'','');
				}
					$this->ldap_functions->add_user2maillist($new_values['uidnumber'], $array_emails_add);
			}
			if (count($remove_users)>0)
			{
				$array_emails_remove = array();
				foreach($remove_users as $uidnumber)
				{
					$mail = $this->ldap_functions->uidnumber2mail($uidnumber);
					
					//caso nao exista o e-mail do usuario informado (usuario externo);
					if (empty($mail))
						$mail = $uidnumber;
					
					$array_emails_remove[] = $mail;
					$this->db_functions->write_log("Removido usuario $mail da lista",$dn,$uidnumber,'','');
				}
				$this->ldap_functions->remove_user2maillist($new_values['uidnumber'], $array_emails_remove);
				//$this->ldap_functions->remove_user2maillist_adm($new_values['uidnumber'], $array_emails_remove);

			}
			
			return $return;
		}

		function save_adm($new_values) // Funcao usada para salvar alteracoes de administradores de listas
		{
			// Verifica o acesso do gerente
	/*			if (!$this->functions->check_acl($_SESSION['phpgw_info']['expresso']['user']['account_lid'], 'adm_maillists'))
			{
				$return['status'] = false;
				$return['msg'] = 'Voc� n�o tem acesso para editar listas de email.';
				return $return;
			}
*/
			$return['status'] = true;
			
			//Retira os uids duplicados se existir
			$array_tmp = array();
			$array_tmp = array_unique($new_values['members']);
			$new_values['members'] = $array_tmp;
	                
			$old_values = $this->get_adm_info($new_values['uidnumber'], $new_values['manager_context']);
			$diff = array_diff($new_values, $old_values);
			
			$dn = 'uid=' . $old_values['uid'] . ',' . $old_values['context'];
			//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// ADD ATTRS
			if (($old_values['accountStatus'] != 'active') && ($new_values['accountStatus'] == 'on'))
				$ldap_add['accountStatus']	= 'active';
			
			if (($old_values['phpgwAccountVisible'] != '-1') && ($new_values['phpgwAccountVisible'] == 'on'))
				$ldap_add['phpgwAccountVisible'] = '-1';
			
			if (count($ldap_add))
			{
				$result = $this->ldap_functions->add_user_attributes($dn, $ldap_add);
				if (!$result['status'])
				{
					$return['status'] = false;
					$return['msg'] .= $result['msg'];
				}
			}
			
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// USERS

			if (!$new_values['members'])
				$new_values['members'] = array();
			if (!$old_values['members'])
				$old_values['members'] = array();

			$add_users = array_diff($new_values['members'], $old_values['members']);

			$remove_users = array_diff($old_values['members'], $new_values['members']);
			
			if (count($add_users)>0)
			{
				$array_emails_add = array();
				foreach($add_users as $uidnumber)
				{
					$mail = $this->ldap_functions->uidnumber2mail($uidnumber);
					$array_emails_add[] = $mail;
					$this->db_functions->write_log("Adicionado usuario $mail $teste a lista",$dn,$uidnumber,'','');
				}
					$this->ldap_functions->add_user2maillist_adm($new_values['uidnumber'], $array_emails_add);
			}
			if (count($remove_users)>0)
			{
				$array_emails_remove = array();
				foreach($remove_users as $uidnumber)
				{
					$mail = $this->ldap_functions->uidnumber2mail($uidnumber);
					
					// N�o achei o email do usu�rio no ldap.
					if (empty($mail))
						$mail = $uidnumber;
					
					$array_emails_remove[] = $mail;
					$this->db_functions->write_log("Removido usuario $mail da lista",$dn,$uidnumber,'','');
				}
				$this->ldap_functions->remove_user2maillist_adm($new_values['uidnumber'], $array_emails_remove);

			}
			
			return $return;
		}		

		
		function save_scl($new_values)
		{
			// Verifica o acesso do gerente
	/*		if (!$this->functions->check_acl($_SESSION['phpgw_info']['expresso']['user']['account_lid'], 'edit_maillists'))
			{
				$return['status'] = false;
				$return['msg'] = 'Voc� n�o tem acesso para editar listas de email.';
				return $return;
			}
	*/		
			$return['status'] = true;

			//Retira os uids duplicados se existir
			$array_tmp = array();
			$array_tmp = array_unique($new_values['members']);
			$new_values['members'] = $array_tmp;
			
			$old_values = $this->get_scl_info($new_values['uidnumber'], $new_values['manager_context']);
			$diff = array_diff($new_values, $old_values);
			$dn = $old_values['dn'];
			
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// ADD ATTRS
			if (($new_values['participantCanSendMail'] == 'on') && ($old_values['participantCanSendMail'] == ''))
			{
				$ldap_add['participantCanSendMail'] = "TRUE";
				$this->db_functions->write_log("Ativado participantCanSendMail da SCL da lista de email " . $new_values['mail'],'','','','');
			}
			if (($new_values['accountRestrictive'] == 'on') && ($old_values['accountRestrictive'] == ''))
			{
				$ldap_add['accountRestrictive'] = "mailListRestriction";
				$ldap_add['accountDeliveryMessage']	= 'OK';
				$this->db_functions->write_log("Ativado mailListRestriction da SCL da lista de email " . $new_values['mail'],'','','','');
			}
			if (count($ldap_add))
			{
				$result = $this->ldap_functions->add_user_attributes($dn, $ldap_add);
				if (!$result['status'])
				{
					$return['status'] = false;
					$return['msg'] .= $result['msg'];
				}
			}
			
			//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// REMOVE ATTRS
			if (($new_values['participantCanSendMail'] != 'on') && ($old_values['participantCanSendMail'] == 'TRUE'))
			{
				$ldap_remove['participantCanSendMail']	= array();
				$this->db_functions->write_log("Desativado participantCanSendMail da SCL da lista de email " . $new_values['mail'],'','','','');
			}
			if (($new_values['accountRestrictive'] != 'on') && ($old_values['accountRestrictive'] == 'mailListRestriction'))
			{
				$ldap_remove['accountRestrictive']	= array();
				$ldap_remove['accountDeliveryMessage']	= array();
				$this->db_functions->write_log("Desativado restri��o (mailListRestriction) da SCL da lista de email " . $new_values['mail'],'','','','');
			}
			if (count($ldap_remove))
			{
				$result = $this->ldap_functions->remove_user_attributes($dn, $ldap_remove);
				if (!$result['status'])
				{
					$return['status'] = false;
					$return['msg'] .= $result['msg'];
				}
			}
			
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// USERS

			if (!$new_values['members'])
				$new_values['members'] = array();
			if (!$old_values['members'])
				$old_values['members'] = array();

			$add_users = array_diff($new_values['members'], $old_values['members']);
			$remove_users = array_diff($old_values['members'], $new_values['members']);
			
			if (count($add_users)>0)
			{
				$array_emails_add = array();
				foreach($add_users as $uidnumber)
				{
					$mail = $this->ldap_functions->uidnumber2mail($uidnumber);
					$array_emails_add[] = $mail;
					$this->db_functions->write_log("Adicionado usuario $mail a SCL da lista $dn",'',$uidnumber,'','');
				}
				$result = $this->ldap_functions->add_user2maillist_scl($dn, $array_emails_add);
				if (!$result['status'])
				{
					$return['status'] = false;
					$return['msg'] .= $result['msg'];
				}
				
			}
			
			if (count($remove_users)>0)
			{
				$array_emails_remove = array();
				foreach($remove_users as $uidnumber)
				{
					$mail = $this->ldap_functions->uidnumber2mail($uidnumber);
					$array_emails_remove[] = $mail;
					$this->db_functions->write_log("Removido usuario $mail da SCP da lista $dn",'',$uidnumber,'','');
				}
				$result = $this->ldap_functions->remove_user2maillist_scl($dn, $array_emails_remove);
				if (!$result['status'])
				{
					$return['status'] = false;
					$return['msg'] .= $result['msg'];
				}
			}
			
			return $return;
		}				
		
		function get_info($uidnumber, $context)
		{
			$maillist_info_ldap = $this->ldap_functions->get_maillist_info($uidnumber, $context);
			return $maillist_info_ldap;
		}

		function get_adm_info($uidnumber, $context) //Funcao que usa get_adm_maillist_info para trazer dados dos administradores
							    // de listas;
		{
			$maillist_info_ldap = $this->ldap_functions->get_adm_maillist_info($uidnumber, $context);
			return $maillist_info_ldap;
		}


		function get_scl_info($uidnumber, $context)
		{
			$maillist_info_ldap = $this->ldap_functions->get_maillist_scl_info($uidnumber, $context);
			return $maillist_info_ldap;
		}
		
		function delete($params)
		{
			// Verifica se usuario atual pertence ao grupo de administradores
			// de listas para ter permissao de excluir a lista de email
			$usuarioAtual = $_SESSION['phpgw_info']['expresso']['user']['account_lid'];
			if ($this->ldap_functions->is_user_listAdmin($usuarioAtual) != 1)
			{
				$return['status'] = false;
				$return['msg'] .= 'Voc� n�o tem acesso para excluir listas de email.';
				$return['msg'] .= ' - oi';
				return $return;
			}

			$return['status'] = true;

			$uidnumber = $params['uidnumber'];
			$uid = $this->ldap_functions->uidnumber2uid($uidnumber);

			//LDAP
			$result_ldap = $this->ldap_functions->delete_maillist($uidnumber);
			if (!$result_ldap['status'])
			{
				$return['status'] = false;
				$return['msg'] .= $result_ldap['msg'];
			}
			
			if ($return['status'] == true)
			{
				$this->db_functions->write_log('Deletado lista de email','',$uid,'','');
			}
			
			return $return;	
		}		
		
		//Rotina que visa atualizar o Mailman com base na cria��o de nova lista no RHDS, em tempo real
		//Utiliza socket para disparar um HTTP Post remoto
		function synchronize_mailman($dados_lista){
			//porta do servidor de acesso via rede
			$porta_mailman = $this->current_config['porta_mailman']; //$GLOBALS['phpgw_info']['server']['porta_mailman'];
			//endere�o IP do servidor mailman
			$host_mailman = $this->current_config['host_mailman']; //$GLOBALS['phpgw_info']['server']['host_mailman'];
			//endereco da URL para o script PHP que faz a atualizacao no recurso remoto
			//$url_mailman = '/servidor-listas/mailman_request.php';
			$url_mailman = $this->current_config['url_mailman']; //$GLOBALS['phpgw_info']['server']['url_mailman'];
			//o nome da lista passado como parametro no array associativo (algo tipo "uid=lista-X")
			$lista_mailman = $dados_lista['uid'];
			$op_mailman = $dados_lista['op'];
			
			if(strlen($lista_mailman) <= 0){
				$msg_socket = "[ERROSOCKET00] " . date("d/m/Y-G:i:s") . " -[".$_SERVER['REMOTE_ADDR']."]"."-[".$_SERVER['REQUEST_URI']."]"."-[".$_SERVER['SCRIPT_NAME']."]:"." Sincronismo MailMan-RHDS falhou ao adicionar nova lista: Motivo= nome da lista(\"$lista_mailman\") invalido.";
				$retorno['status'] = false;
				$retorno['msg'] = $msg_socket; 
				//Grava no logger o erro
				$log = `/usr/bin/logger -p local5.notice -t Sinc-Listas-Mailman-RHDS '$msg_socket'`;
    			return $retorno;				
			}
			
			//Cria o socket TCP/IP
			$socket = socket_create(AF_INET, SOCK_STREAM, 0);
			
			$retorno['status'] = true;
			
			//Em caso de erro, retorna um vetor serializado contendo (falso, string erro)
			if ($socket < 0) {
				$msg_socket = "[ERROSOCKET01] " . date("d/m/Y-G:i:s") . " -[".$_SERVER['REMOTE_ADDR']."]"."-[".$_SERVER['REQUEST_URI']."]"."-[".$_SERVER['SCRIPT_NAME']."]:"." Sincronismo MailMan-RHDS falhou ao adicionar nova lista: php socket_create() falhou. Motivo= " . socket_strerror($socket);;
				$retorno['status'] = false;
				$retorno['msg'] = $msg_socket; 
				//Grava no logger o erro
				$log = `/usr/bin/logger -p local5.notice -t Sinc-Listas-Mailman-RHDS '$msg_socket'`;
    			return $retorno;
			}
			
			//Estabelece a conex�o com o recurso
			$resultado = socket_connect($socket, $host_mailman, $porta_mailman);
			
			//Em caso de erro, retorna um vetor serializado contendo (falso, string erro)
			if ($resultado < 0) {
				$msg_socket = "[ERROSOCKET02] " .date("d/m/Y-G:i:s") . " -[".$_SERVER['REMOTE_ADDR']."]"."-[".$_SERVER['REQUEST_URI']."]"."-[".$_SERVER['SCRIPT_NAME']."]:"." Sincronismo MailMan-RHDS falhou ao adicionar nova lista: php socket_connect() [$resultado] falhou. Motivo=  " . socket_strerror($resultado);
				$retorno['status'] = false;
				$retorno['msg'] = $msg_socket; 
				//Grava no logger o erro
				$log = `/usr/bin/logger -p local5.notice -t Sinc-Listas-Mailman-RHDS '$msg_socket'`;
    			return $retorno;				
			}			
			
			//Envia o dado "lid", contendo o nome da lista recebido como parametro da funcao, e "op=1" (nova lista)
			$post_lista = "uid=".$lista_mailman."&op=".$op_mailman;
			
			//Define o tipo de mensagem que vai ser disparada via socket: no caso, um HTTP Post
			$dados_envio_socket = "POST $url_mailman HTTP/1.0\r\n";
			$dados_envio_socket .= "Host: $host_mailman\r\n";
			$dados_envio_socket .= "Accept: text/xml,application/xml,application/xhtml+xml,text/html,text/plain;\r\n";
			$dados_envio_socket .= "Accept-Charset: ISO-8859-1,utf-8;\r\n";
			$dados_envio_socket .= "Accept-Language: pt-br,en-us,en;\r\n";
			$dados_envio_socket .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$dados_envio_socket .= "Connection: close\r\n";
			$dados_envio_socket .= "Content-Length: " . strlen($post_lista) . "\r\n";
			$dados_envio_socket .= "\r\n";
			$dados_envio_socket .= "$post_lista\r\n";
			
			//String que cont�m a sa�da do processamento remoto
			$dados_saida_socket = '';
			
			//Efetiva os dados de entrada para o processamento no socket remoto
			if (socket_write ($socket, $dados_envio_socket, strlen($dados_envio_socket))){
				while ($leitura_socket  = socket_read($socket, 2048)) {
    				$dados_saida_socket .= $leitura_socket;
				}
			}
			else{
				$msg_socket = "[ERROSOCKET03] " .date("d/m/Y-G:i:s") . " -[".$_SERVER['REMOTE_ADDR']."]"."-[".$_SERVER['REQUEST_URI']."]"."-[".$_SERVER['SCRIPT_NAME']."]:"." Sincronismo MailMan-RHDS falhou ao adicionar nova lista: php socket_write() [$dados_envio_socket] falhou. Motivo=  " . socket_strerror(socket_last_error());
				$retorno['status'] = false;
				$retorno['msg'] = $msg_socket; 
				//Grava no logger o erro
				$log = `/usr/bin/logger -p local5.notice -t Sinc-Listas-Mailman-RHDS '$msg_socket'`;
    			return $retorno;					
			}
			
			//Captura a partir da string total da resposta do socket, a sec��o serializada a ser tratada
			$vet_dados_saida = explode('@@', $dados_saida_socket);
			if(count($vet_dados_saida[1]) > 0){
				$dados_serializados_saida = unserialize($vet_dados_saida[1]);
			}
			else{
				$msg_socket = "[ERROSOCKET04] " .date("d/m/Y-G:i:s") . " -[".$_SERVER['REMOTE_ADDR']."]"."-[".$_SERVER['REQUEST_URI']."]"."-[".$_SERVER['SCRIPT_NAME']."]:"." Sincronismo MailMan-RHDS falhou ao adicionar nova lista: php resposta do socket nula/invalida. Conteudo=  " . $dados_saida_socket; 
				$retorno['status'] = false;
				$retorno['msg'] = $msg_socket;
				//Grava no logger o erro
				$log = `/usr/bin/logger -p local5.notice -t Sinc-Listas-Mailman-RHDS '$msg_socket'`;
				return $retorno;
			}

			//Encerra a conex�o com o recurso remoto
			socket_close($socket);	
			
			//Retorna um array contendo (true, dados serializados de processamento remoto)
			return $dados_serializados_saida;		
		}
		//Usada para fazer rebind em caso de escrita em slave (quando volta uma referral)
	}
?>
