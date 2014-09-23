<?php
/**
*
* Copyright (C) 2011 Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
*
* This program is free software; you can redistribute it and/or modify it under
* the terms of the GNU Affero General Public License version 3 as published by
* the Free Software Foundation with the addition of the following permission
* added to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED
* WORK IN WHICH THE COPYRIGHT IS OWNED BY FUNAMBOL, FUNAMBOL DISCLAIMS THE
* WARRANTY OF NON INFRINGEMENT  OF THIRD PARTY RIGHTS.
*
* This program is distributed in the hope that it will be useful, but WITHOUT
* ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
* details.
*
* You should have received a copy of the GNU Affero General Public License
* along with this program; if not, see www.gnu.org/licenses or write to
* the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
* MA 02110-1301 USA.
*
* This code is based on the OpenXchange Connector and on the Prognus pSync
* Connector both developed by the community and licensed under the GPL
* version 2 or above as published by the Free Software Foundation.
*
* You can contact Prognus Software Livre headquarters at Av. Tancredo Neves,
* 6731, PTI, Bl. 05, Esp. 02, Sl. 10, Foz do Iguaçu - PR - Brasil or at
* e-mail address prognus@prognus.com.br.
*
* Descrição rápida do arquivo
*
* Arquivo responsável pela manipulação dos filtros
*
* @package    filters
* @license    http://www.gnu.org/copyleft/gpl.html GPL
* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
* @version    1.0
* @sponsor    Caixa Econômica Federal
* @since      Arquivo disponibilizado na versão 2.4
*/

use prototype\api\Config as Config;

/**
* Classe responsável pela manipulação dos filtros.
*
*
* @package    prototype
* @license    http://www.gnu.org/copyleft/gpl.html GPL
* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
* @author     Airton Bordin Junior <airton@prognus.com.br>
* @author     Gustavo Pereira dos Santos <gustavo@prognus.com.br>
* @version    1.0
* @since      Classe disponibilizada na versão 2.4
*/
class FilterMapping
{
	var $service;
	var $msgs_apply = array();
	/**
	* Método que cria o ID da regra que está sendo criada.
	*
	* @license    http://www.gnu.org/copyleft/gpl.html GPL
	* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	* @sponsor    Caixa Econômica Federal
	* @author     Airton Bordin Junior <airton@prognus.com.br>
	* @author	  Gustavo Pereira dos Santos <gustavo@prognus.com.br>	
	* @author	  Natan Fonseca <natan@prognus.com.br>	
	* @param      <$uri> 
	* @param      <$result> 
	* @param      <$criteria> 
	* @param      <$original> 
	* @access     <public>
	*/
	public function makeId(&$uri , &$result , &$criteria , $original) {
		$result['id'] = $uri['id'];
	}
	
	
	/**
	* Método que formata o Script de acordo com a sintaxe do Sieve.
	*
	* @license    http://www.gnu.org/copyleft/gpl.html GPL
	* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	* @sponsor    Caixa Econômica Federal
	* @author     Airton Bordin Junior <airton@prognus.com.br>
	* @author	  Gustavo Pereira dos Santos <gustavo@prognus.com.br>	
	* @param      <Array> <$rules> <Array com as regras do usuário>
	* @return     <Regra de acordo com a sintaxe do Sieve>
	* @access     <public>
	*/
	public function formatSieveScript( $rules )
    {
		$require_fileinto = $require_flag = $require_reject = $require_vacation = $require_body = $require_imapflag = $vacation = $startswith = $endswith = false;
		$script_rules = $script_header = $script_criteria = $vacation_action = "";
		$i = 0;
		
		foreach( $rules as $name => $data )
		{	
			if( $data['enabled'] == 'false' )
				continue;
				
			if(array_key_exists("block", $data)) 
			{
				/* Usado na opção Bloquear usuário do ExpressoMail */
				if($data['block']) {
					($i >0) ? $script_match = 'elsif anyof' : $script_match = 'if anyof';
					$script_match = $script_match . "(address :is \"from\" [\"" .$data['name'] . "\"]) {\r\n";  
					$script_match .= "fileinto \"INBOX/Spam\"; \r\n}\r\n";
					$script_rules .= $script_match;
					$script_match = "";
					$script_criteria = "";
					$require_fileinto = true;
					++$i;
					continue;
				}
			}
				
			$vacation = false;
			$criteria = $data['criteria'];
			$action   = $data['actions'];
			
			($i >0 && $verifyNextRule == 'false') ? $script_match = 'els' : $script_match = '';
			$data['isExact'] == 'false' ?  $script_match .= 'if anyof (' : $script_match .= 'if allof (';

			$verifyNextRule = 'false';
			
			if( is_array($criteria) )
			foreach ($criteria as $j => $value)
			{					
				if ($criteria[$j]['operator'] == '!*') $script_criteria .= "not ";
				
				switch(strtoupper($criteria[$j]['field'])) {
					case 'TO':    
					case 'CC':
						$criteria[$j]['field'] = "[\"To\", \"TO\", \"Cc\", \"CC\"]"; 
						$script_criteria .= "address :";
						break;
					case 'FROM':
						$criteria[$j]['field'] = "\"" . $criteria[$j]['field'] . "\"";
						$script_criteria .= "address :";
						break;
					case 'SIZE':	
						$criteria[$j]['field'] = '';
						$script_criteria .= "size :";
						break;
					case 'SUBJECT':
						$criteria[$j]['field'] = "\"" . $criteria[$j]['field'] . "\"";
						$script_criteria .= "header :";
						if($criteria[$j]['operator'] == "$") {
							$criteria[$j]['value'] = "" . $criteria[$j]['value'] . "\", \"*" . base64_encode($criteria[$j]['value']) . "";
							break;
						}
						if($criteria[$j]['operator'] == "^") {
							$criteria[$j]['value'] = "" . $criteria[$j]['value'] . "*\", \"" . base64_encode($criteria[$j]['value']) . "";
							break;
						}
						$criteria[$j]['value'] = "" . $criteria[$j]['value'] . "\", \"" . base64_encode($criteria[$j]['value']) . "";
						break;
					case 'BODY':
						$criteria[$j]['field'] = '';
						$script_criteria .= "body :";
						$require_body = true;
						break;
					case 'VACATION':
						continue;
					case 'HASATTACHMENT':
						$criteria[$j]['field'] = '';
						$script_criteria .= "body :";
						$criteria[$j]['operator'] = "^^";
						$require_body = true;
						break;
					default:
						$script_criteria .= "header :";
						break;
				}
				
				switch ($criteria[$j]['operator']) {
					case '>':
						$criteria[$j]['operator'] = "over";
						$criteria[$j]['value'] = $criteria[$j]['value'] . "K";
						break;
					case '<':
						$criteria[$j]['operator'] = "under";
						$criteria[$j]['value'] = $criteria[$j]['value'] . "K";
						break;
					case '=':
						$criteria[$j]['operator'] = "is";
						$criteria[$j]['value'] = "[\"" . $criteria[$j]['value'] . "\"]";
						break;
					case '*':
						$criteria[$j]['operator'] = "contains";
						$criteria[$j]['value'] = "[\"" . $criteria[$j]['value'] . "\"]";
						break;						
					case '^':
						$criteria[$j]['operator'] = "matches";
						$criteria[$j]['value'] = "[\"" . $criteria[$j]['value'] . "*\"]";
						$startswith = true;
						break;
					case '^^':
						$criteria[$j]['operator'] = "raw :matches";
						$criteria[$j]['value'] = "[\"*filename=*\"]";
						$startswith = true;
						break;
					case '$':
						$criteria[$j]['operator'] = "matches";
						$criteria[$j]['value'] = "[\"*" . $criteria[$j]['value'] . "\"]";
						$endswith = true;
						break;
					case '!*':
						$criteria[$j]['operator'] = "contains";
						$criteria[$j]['value'] = "[\"" . $criteria[$j]['value'] . "\"]";
						break;
				}
				
				if ($criteria[$j]['field'] == "" || $criteria[$j]['field'] == "\"subject\"" || $startswith || $endswith)
				{
					$script_criteria .= $criteria[$j]['operator'] . " " . $criteria[$j]['field'] . " " . $criteria[$j]['value'] . ", "; 
					$startswith = $endswith = false;
				}
				else
					$script_criteria .= $criteria[$j]['operator'] . " " . $criteria[$j]['field'] . " " . $criteria[$j]['value'] . ", ";
			}
			$script_criteria = substr($script_criteria,0,-2);
			$script_criteria .= ")"; 

			$action_addFlag = '';
			
			if( is_array($action) )
			foreach ($action as $k => $value)
			{
				switch ($action[$k]['type']) {
					case 'setflag':
						$require_flag = true;
						$action[$k]['parameter'] = "\\\\" . $action[$k]['parameter'];
						break;
					case 'addflag':	
						$require_flag = true;
						$action_addFlag = "addflag \"" . $action[$k]['parameter'] . "\";\r\n ";
						break;
					case 'redirect':
						break;
					case 'reject':
						$require_reject = true;
						break;
					case 'fileinto':
						$require_fileinto = true;
						$action[$k]['parameter'] = mb_convert_encoding($action[$k]['parameter'], "UTF7-IMAP","UTF-8, ISO-8859-1, UTF7-IMAP"); 
						break;
					case 'vacation':
						$require_vacation = true;
						$action[$k]['parameter'] = "\"" . $action[$k]['parameter'] . "\"";
						$vacation_action = '  :subject "=?ISO-8859-1?Q?Fora_do_Escrit=F3rio?=" :days 1 :addresses ["'.config::me('mail').'"] ' . $action[$k]['parameter'] . ";";
						$vacation = true;
						continue;
					case 'discard':
						break;
				}
				if($action[$k]['type']=='discard') { //Old rules could have it, so, we keep as before untill it be saved
					$script_action .= $action[$k]['type'].";\r\n";
				}
				elseif ($vacation == false && $action[$k]['type'] != 'addflag') $script_action .= $action[$k]['type'] . " \"" . $action[$k]['parameter'] . "\";\r\n ";
			}
			
			/* ATENÇÃO: Colocar sempre o comando addflag antes de qualquer outro no caso de ações compostas no Sieve */
			if ($action_addFlag != '') $script_action = $action_addFlag . $script_action; 
			
			$script_action = "{\r\n " . $script_action . "}";
			$action_addFlag = '';
			if($vacation == false)
				$script_rules .= $script_match . $script_criteria . $script_action . "\r\n";

			if($data['id'] != "vacation")
				++$i;
			$script_match = "";
			$script_criteria = "";	
			$script_action = "";
			$data['applyMessages'] = "";	

			$verifyNextRule = $data['verifyNextRule'];	
		}

		if($require_reject || $require_fileinto || $require_vacation || $require_body || $require_flag)
		{
			/* Para habilitar as funções desejadas, edite a diretiva sieve_extensions no arquivo de configuração "/etc/imapd.conf" */
			$script_header .= "require [";
			$require_reject ? $script_header .= "\"reject\", " : ""; 
			$require_fileinto ? $script_header .= "\"fileinto\", " : ""; 
			$require_vacation? $script_header .= "\"vacation\", " : "";  
			$require_flag ? $script_header .= "\"imapflags\", " : "";  
			$require_body ? $script_header .= "\"body\", " : "";  
			$script_header = substr($script_header,0,-2);
			$script_header .= "];\r\n";
		}

		if( $vacation_action )
		  $script_rules .= "vacation" . $vacation_action . "\r\n";

		foreach ($rules as &$values) {						
			if($values['applyMessages'])
				$this->msgs_apply[] = $values['applyMessages'];
			$values['applyMessages'] = array();
		}
		
		$json_data = json_encode($rules);
		$script_begin = "#Filtro gerado por Expresso Livre\r\n\r\n";
		$content = $script_begin . $script_header . $script_rules . "\r\n\r\n#PseudoScript#" . "\r\n#" . $json_data;

		return( $content );
	}
	
	/**
	* Método que lê e faz o parser dos filtros antigos
	*
	* @license    http://www.gnu.org/copyleft/gpl.html GPL
	* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	* @sponsor    Caixa Econômica Federal
	* @author     Airton Bordin Junior <airton@prognus.com.br>
	* @author	  Gustavo Pereira dos Santos <gustavo@prognus.com.br>	
	* @param      <$scriptName> <Regras do usuário>
	* @return     <Regra do usuário parseada>
	* @access     <public>
	*/
	public function readOldScript($scriptName) 
	{
        // Recebe o conteúdo do array;
        $lines = array();
        $lines = preg_split("/\n/", $scriptName);

        // Pega o tamanho da regra na primeira do script;
        $size_rule = array_shift($lines);

        // Recebe o tamanho do script, pela primeira linha;
        //$this->size = trim($size_rule);

        // Verifica a composição do script; */
        $line = array_shift($lines);

        // Variaveis para a regra e o campo ferias;
        $regexps = array('##PSEUDO', '#rule', '#vacation', '#mode');
        $retorno['rule'] = array();

        $line = array_shift($lines);
        while (isset($line)) { 
            foreach ($regexps as $regp) {
                if (preg_match("/$regp/i", $line)) { 
                    // Recebe todas as regras criadas no servidor;
                    if (preg_match("/#rule&&/i", $line)) {
                        $retorno['rule'][] = ltrim($line) . "\n";                          
                    }
					if(preg_match("/#vacation/i",$line)) {
						$retorno['vacation'] = true;
					}
                }
            }
            // Pega a proxima linha do sript;
            $line = array_shift($lines);
        }
        return $retorno;
    }
	
	
	
	
	/**
	* Método que faz o parsing do Script Sieve, transformando em Array.
	*
	* @license    http://www.gnu.org/copyleft/gpl.html GPL
	* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	* @sponsor    Caixa Econômica Federal
	* @author     Airton Bordin Junior <airton@prognus.com.br>
	* @author	  Gustavo Pereira dos Santos <gustavo@prognus.com.br>	
	* @param      <String> <$script> <Script Sieve com as regras do usuário>
	* @return     <Regras do usuário em Array>
	* @access     <public>
	*/
	public function parseSieveScript( $script )
	{
		$old_rule = strripos($script, "##PSEUDO script start");
		/* Tem regra antiga */
		if($old_rule) {
			$parsed_rule = $this->readOldScript($script);
			$old_rules = array(); 
			$i_return = 0;
			foreach ($parsed_rule['rule'] as $i => $value) {
				$array_rule = explode("&&", $parsed_rule['rule'][$i]);
				
				$action_type = array();
				$action_parameter = array();
				$criteria_value = array();
				$criteria_operator = array();
				$criteria_field = array();
					
				$i_criteria = 0;
				$i_action = 0;

				/* TO-DO: Ver as actions possíveis além de reject e fileinto */
				switch($array_rule[6]) {
					case 'reject':
						$action_type[$i_action] = 'reject';
						$action_parameter[$i_action] = $array_rule[7];
						++$i_action;
						break;
					case 'discard':
						$action_type[$i_action] = 'discard';
						$action_parameter[$i_action] = "";
						++$i_action;
						break;
					case 'folder':
						$action_type[$i_action] = 'fileinto';
						$action_parameter[$i_action] = $array_rule[7];
						++$i_action;
						break;
					case 'flagged':
						$action_type[$i_action] = 'setflag';
						$action_parameter[$i_action] = 'flagged';
						++$i_action;
						break;
					case 'address': 
						$action_type[$i_action] = 'redirect';
						$action_parameter[$i_action] = $array_rule[7];
						++$i_action;
						break;
					/* Somente para tratar casos em que a ação não é suportada */
					default:	
						$action_type[$i_action] = 'setflag';
						$action_parameter[$i_action] = 'flagged';
						++$i_action;
						break;
					// Recuperar o cyrus_delimiter para forçar um fileinto para INBOX/trash
					//case 'discard':
						//$action_type[$i_action] = 'fileinto';
						//$action_parameter[$i_action] =;
						//$i_action++;
						//break;
				}
				if($array_rule[3] != "") {
					$criteria_value[$i_criteria] = mb_convert_encoding ($array_rule[3],'UTF-8');
					$criteria_operator[$i_criteria] = '=';
					$criteria_field[$i_criteria] = 'from';
					++$i_criteria;
				} 
				if($array_rule[4] != "") {
					$criteria_value[$i_criteria] = mb_convert_encoding ($array_rule[4],'UTF-8');
					$criteria_operator[$i_criteria] = '=';
					$criteria_field[$i_criteria] = 'to';
					++$i_criteria;
				} 
				if($array_rule[5] != "") {
					$criteria_value[$i_criteria] = mb_convert_encoding ($array_rule[5],'UTF-8');
					$criteria_operator[$i_criteria] = '=';
					$criteria_field[$i_criteria] = 'subject';
					++$i_criteria;
				}
				$old_retorno = array();
				$old_retorno['isExact']  = true;
				$old_retorno['name'] = 'regra_migrada_' . $array_rule[1];
				
				$old_retorno['criteria'] = array();				
				foreach($criteria_value as $j => $value) {
					$old_retorno['criteria'][$j] = array();
					$old_retorno['criteria'][$j]['value'] = $criteria_value[$j];
					$old_retorno['criteria'][$j]['operator'] = $criteria_operator[$j];
					$old_retorno['criteria'][$j]['field'] = $criteria_field[$j];
				}
				
				$old_retorno['actions'] = array();				
				foreach($action_parameter as $j => $value) {
					$old_retorno['actions'][$j] = array();
					$old_retorno['actions'][$j]['parameter'] = $action_parameter[$j];
					$old_retorno['actions'][$j]['type'] = $action_type[$j];
				}
				
				$old_retorno['enabled'] = ($array_rule[2] == 'ENABLED') ? 'true' : 'false';
				$old_retorno['id'] = 'Regra_migrada_' . $i_return;
				$old_retorno['applyMessages']  = '';

				$old_rules[$i_return] = $old_retorno;
				++$i_return;
			}
			if(isset($parsed_rule["vacation"])) {
				$old_retorno = array();
				$old_retorno['isExact']  = "false";
				$old_retorno['name'] = 'vacation';
				$old_retorno['id'] = 'vacation';
				$old_retorno['enabled'] = 'true';
				$old_retorno['applyMessages'] = array();
				$old_retorno['isExact'] = "false";
				$old_retorno['actions'] = array();			
				$old_retorno['actions'][0] = array();
				$old_retorno['actions'][0]['parameter'] = "";
				$old_retorno['actions'][0]['type'] = "vacation";
				$old_retorno['criteria'] = array();
				$old_retorno['criteria'][0] = array();
				$old_retorno['criteria'][0]['value'] = "vacation";
				$old_retorno['criteria'][0]['operator'] = "";
				$old_retorno['criteria'][0]['field'] = "vacation";
				$old_rules[] = $old_retorno;
			}			
			return $old_rules;
		} 
		/* Não tem regra antiga */
		$pos = strripos($script, "#PseudoScript#");
		$pseudo_script = substr( $script, $pos+17 );

		$return = json_decode( $pseudo_script, true );
	
		return $return;
	}

	var $rules = false;

	/**
	* Construtor da classe.
	*
	* @license    http://www.gnu.org/copyleft/gpl.html GPL
	* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	* @sponsor    Caixa Econômica Federal
	* @author     Airton Bordin Junior <airton@prognus.com.br>
	* @author	  Gustavo Pereira dos Santos <gustavo@prognus.com.br>	
	* @access     <public>
	*/
	public function __construct()
	{ 
		$this->service = Controller::service("Sieve");
	}

	
	/**
	* Método que recupera as regras do usuário.
	*
	* @license    http://www.gnu.org/copyleft/gpl.html GPL
	* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	* @sponsor    Caixa Econômica Federal
	* @author     Airton Bordin Junior <airton@prognus.com.br>
	* @author	  Gustavo Pereira dos Santos <gustavo@prognus.com.br>	
	* @return     <Regras do usuário>
	* @access     <public>
	*/
	public function getRules()
	{
		$this->rules = Controller::find( array( 'concept' => 'filter' ) );

		if( !$this->rules ) {
			$this->rules = array();
		}
		return( $this->rules );
	}

	
	/**
	* Método que aplica o filtro para as mensagens do usuário.
	*
	* @license    http://www.gnu.org/copyleft/gpl.html GPL
	* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	* @sponsor    Caixa Econômica Federal
	* @author     Airton Bordin Junior <airton@prognus.com.br>
	* @author	  Gustavo Pereira dos Santos <gustavo@prognus.com.br>	
	* @param      <$uri> 
	* @param      <$result> 
	* @param      <$criteria> 
	* @param      <$original> 
	* @access     <public>
	*/
	public function applySieveFilter( &$uri , &$result , &$criteria , $original  )
	{
		$rule_apply = array(); 
			
		$filter = Controller::read($uri);
		$filter_ = $this->parseSieveScript($filter['content']);
		
		foreach ($filter_ as $f_) {
			if($f_['id'] == $uri['id']) { 
				$rule_apply	= $f_;
			}
		}
				
		$actions = array();
		$actions['type'] = $rule_apply['actions'][0]['type'];
		$actions['parameter'] = $rule_apply['actions'][0]['parameter'];

		$actions['keep'] = is_array($rule_apply['actions'][1]);
		if ($actions['keep'])
			$actions['value'] = $rule_apply['actions'][0]['parameter'];

		//$messages = $rule_apply['applyMessages'];
		$messages = $this->msgs_apply[0];
		$this->msgs_apply = array();
			
		$imap = Controller::service( 'Imap' );
		$imap->apliSieveFilter($messages, $actions); 
		return $result;
	}

	
	/**
	* Método que lê o script do usuário.
	*
	* @license    http://www.gnu.org/copyleft/gpl.html GPL
	* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	* @sponsor    Caixa Econômica Federal
	* @author     Airton Bordin Junior <airton@prognus.com.br>
	* @author	  Gustavo Pereira dos Santos <gustavo@prognus.com.br>	
	* @param      <$uri> 
	* @param      <$result> 
	* @param      <$criteria> 
	* @param      <$original> 
	* @return     <Script do usuário>
	* @access     <public>
	*/
	public function readUserScript( &$uri , &$params , &$criteria , $original )
	{  
		$uri['id'] = $this->service->config['user'];
	}
  
  
	/**
	* Método que seta o script do usuário.
	*
	* @license    http://www.gnu.org/copyleft/gpl.html GPL
	* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	* @sponsor    Caixa Econômica Federal
	* @author     Airton Bordin Junior <airton@prognus.com.br>
	* @author	  Gustavo Pereira dos Santos <gustavo@prognus.com.br>	
	* @param      <$uri> 
	* @param      <$result> 
	* @param      <$criteria> 
	* @param      <$original> 
	* @return     <Script do usuário>
	* @access     <public>
	*/
	public function setRule( &$uri , &$params , &$criteria , $original  )
	{
		if( !$this->rules )
			$this->rules = $this->getRules();



		if(isset($params['id'])) {
			$uri['id'] = $params['id'];
			$checkfor = 'id';
		}
		else {
			$uri['id'] = $params['id'] = urlencode($params['name']);
			$checkfor = 'name';
		}

	    $i = 0;

	    for( ; isset($this->rules[$i]) && $this->rules[$i][$checkfor] !== $params['id']; ++$i );

	    $this->rules[$i] = array_merge( ( isset($this->rules[$i]) ? $this->rules[$i] : array() ), $params );

	    $params = array( 'name' => $this->service->config['user'],
			     'content' => $this->formatSieveScript( $this->rules ),
			     'active' => true );
	}

	
	/**
	* Método que deleta o script do usuário.
	*
	* @license    http://www.gnu.org/copyleft/gpl.html GPL
	* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	* @sponsor    Caixa Econômica Federal
	* @author     Airton Bordin Junior <airton@prognus.com.br>
	* @author	  Gustavo Pereira dos Santos <gustavo@prognus.com.br>	
	* @param      <$uri> 
	* @param      <$result> 
	* @param      <$criteria> 
	* @param      <$original> 
	* @access     <public>
	*/
	public function deleteRule( &$uri, &$params, &$criteria, $original )
	{
		if( !$this->rules ) {	
			$this->rules = $this->getRules();
		}	  
		$params['id'] = $uri['id'];

		$rules = array();

		foreach( $this->rules as $i => $rule )
			if( $rule['id'] !== $uri['id'] )
				$rules[] = $this->rules[$i];

		$this->rules = $rules;
		
		$uri['id'] = '';

		$params = array( 'name' => $this->service->config['user'],
			   'content' => $this->formatSieveScript( $this->rules ),
			   'active' => true );

		$URI = Controller::URI( $uri['concept'], $this->service->config['user'] );
		$this->service->update( $URI, $params );
	
		return( false );
	}

	
	/**
	* Método que pega o script do usuário.
	*
	* @license    http://www.gnu.org/copyleft/gpl.html GPL
	* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	* @sponsor    Caixa Econômica Federal
	* @author     Airton Bordin Junior <airton@prognus.com.br>
	* @author	  Gustavo Pereira dos Santos <gustavo@prognus.com.br>	
	* @param      <$uri> 
	* @param      <$result> 
	* @param      <$criteria> 
	* @param      <$original> 
	* @return     <Script do usuário>
	* @access     <public>
	*/
	public function getSieveRule( &$uri , &$params , &$criteria , $original )
	{	  
		$script = $this->parseSieveScript( $params['content'] );

		foreach( $script as $i => $rule )
			if(is_array ($rule['name']) && is_array($original['id']))
			if( $rule['name'] === $original['id'] )
				return( $params = $rule );
	}

	
	/**
	* Método que lista as regras do usuário.
	*
	* @license    http://www.gnu.org/copyleft/gpl.html GPL
	* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	* @sponsor    Caixa Econômica Federal
	* @author     Airton Bordin Junior <airton@prognus.com.br>
	* @author	  Gustavo Pereira dos Santos <gustavo@prognus.com.br>	
	* @param      <$uri> 
	* @param      <$result> 
	* @param      <$criteria> 
	* @param      <$original> 
	* @return     <Regras do usuário>
	* @access     <public>
	*/
	public function listSieveRules( &$uri , &$params , &$criteria , $original  )
	{
		$return = $params = $this->parseSieveScript( $params[0]['content'] ); 
		return( $return );
	}

	/** 
	 * Método que insere no ldap as informações do vacation 
	 * 
	 * @license    http://www.gnu.org/copyleft/gpl.html GPL 
	 * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br) 
	 * @sponsor     Caixa Econômica Federal 
	 * @author     Cristiano Corrêa Schmidt 
	 * @param      <$uri> 
	 * @param      <$result> 
	 * @param      <$criteria> 
	 * @param      <$original> 
	 * @return     <void> 
	 * @access     public 
	 */ 
	public function verifyVacationRule( &$uri , &$params , &$criteria , $original  ) 
	{ 
	    if( $original['properties']['id'] === 'vacation' ) 
	    { 

	        $user = Controller::read(array('concept' => 'user' , 'id' => config::me('uidNumber') , 'service' => 'OpenLDAP')); 
	        $ldapConf = Config::service('OpenLDAP', 'config'); 
	        $con = ldap_connect( $ldapConf['host'] ); 
	        ldap_set_option( $con,LDAP_OPT_PROTOCOL_VERSION, 3 ); 
	        ldap_bind( $con, $ldapConf['user'], $ldapConf['password']); 

	        $info = array(); 
	        if(!in_array('Vacation', $user['objectClass'])) 
	                $info['objectClass'] = 'Vacation'; 

	        $info['vacationActive'] = strtoupper($original['properties']['enabled']); 

	        if(isset($original['properties']['actions']) && isset($original['properties']['actions'][0]['parameter'])) 
	                $info['vacationInfo']   = $original['properties']['actions'][0]['parameter']; 
	        else if( !isset($user['vacationInfo']) ) 
	        { 
	            $rules = $this->getRules(); 
	            if(is_array($rules)) 
	                foreach ($rules as $rule) 
	                if($rule['id'] === 'vacation') 
	                	$info['vacationInfo'] = $rule['actions'][0]['parameter']; 
	        } 

	        if(!in_array('Vacation', $user['objectClass'])) 
	                ldap_mod_add ( $con , $user['dn'] ,  $info ); 
	        else 
	                ldap_modify ( $con , $user['dn'] ,  $info ); 


	        ldap_close($con); 

	    } 
	 
	}

	/** 
	 * Método que remove do ldap as informações do vacation 
	 * 
	 * @license    http://www.gnu.org/copyleft/gpl.html GPL 
	 * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br) 
	 * @sponsor     Caixa Econômica Federal 
	 * @author     Cristiano Corrêa Schmidt 
	 * @param      <$uri> 
	 * @param      <$result> 
	 * @param      <$criteria> 
	 * @param      <$original> 
	 * @return     <void> 
	 * @access     public 
	 */ 
	public function deleteVacationRule( &$uri , &$params , &$criteria , $original  ) 
	{         
	    if( $original['URI']['id'] === 'vacation' ) 
	    { 
	        $user = Controller::read(array('concept' => 'user' , 'id' => config::me('uidNumber') , 'service' => 'OpenLDAP')); 
	        $ldapConf = Config::service('OpenLDAP', 'config'); 
	        $con = ldap_connect( $ldapConf['host'] ); 
	        ldap_set_option( $con,LDAP_OPT_PROTOCOL_VERSION, 3 ); 
	        ldap_bind( $con, $ldapConf['user'], $ldapConf['password']); 
	        $info = array(); 
	        $info['vacationActive'] = 'FALSE'; 
	        $info['vacationInfo'] = ""; 
	        ldap_modify ( $con , $user['dn'] ,  $info ); 
	        ldap_close($con); 
	    } 
	}
}
