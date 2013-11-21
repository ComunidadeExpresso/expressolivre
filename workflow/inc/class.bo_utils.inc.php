<?php
/**************************************************************************\
* eGroupWare                                                 			   *
* http://www.egroupware.org                                                *
* --------------------------------------------                             *
*  This program is free software; you can redistribute it and/or modify it *
*  under the terms of the GNU General Public License as published by the   *
*  Free Software Foundation; either version 2 of the License, or (at your  *
*  option) any later version.                                              *
\**************************************************************************/

require_once 'class.bo_ajaxinterface.inc.php';

/**
 * @package Workflow
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @author Sidnei Augusto Drovetto Jr. - drovetto@gmail.com
 */
class bo_utils extends bo_ajaxinterface
{
	/**
	 * @var object $db
	 * @access public
	 */
	var $db;
	/**
	 * Construtor da classe bo_utils
	 * @return object
	 * @access public
	 */
	function bo_utils()
	{
		parent::bo_ajaxinterface();
		$this->db =& Factory::getInstance('WorkflowObjects')->getDBExpresso();
	}

	/**
	 * Retorna as cidades de um estado
	 * @param array $params parametros
	 * @return array Array com IDs e nomes das cidades
	 * @access public
	 */
	function get_cities($params)
	{
		$output = array();
		$output['target'] = $params['target'];
		$state_id = $params['state_id'];
		$sql = "SELECT id_city, city_name FROM phpgw_cc_city WHERE id_state = $state_id ORDER BY city_name";
		$result = $this->db->query($sql);
		$output['cities'] = array();
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
			$output['cities'][] = array(
									'id' => $row['id_city'],
									'name' => $row['city_name']);
		return $output;
	}

	/**
	 * Retorna a lista de registros encontrados no LDAP cujo cn contenha o parâmetro digitado
	 * @param array $params parametros
	 * @return array Array com o nome da combo onde devem ser carregados os resultados da busca, com
	 *				 um array com IDs e names dos registros encontrados e/ou uma mensagem de erro.
	 * @access public
	 */
	function search_ldap_users_by_cn($params)
	{ 
		// parâmetro a ser procurado no ldap
		$cn = trim(preg_replace('/ +/', ' ', $params['cn']));

		$output           = array();
		$output['target'] = $params['target'];
		$output['values'] = array();
		$output['msg']    = "";

		// verifica se o nome digitado contém apenas letras e/ou espaços
		if(preg_match('/^[a-z -]*$/i', $cn)){
			if (strlen($cn) < 3)
			{
				$output['msg'] = 'Por favor, utilize pelo menos 3 caracteres em sua busca.';
				return $output;
			}
			// atributo a ser retornado como id da option, se não for passado este parâmetro, usar 'dn'
			$ret_id   = empty($params['id']) ? 'dn' : $params['id'];

			// atributo a ser retornado como name da option, se não for passado este parâmetro, usar 'cn'
			$ret_name = empty($params['name']) ? 'cn' : $params['name'];

			$ret_complement = empty($params['complement']) ? '' : $params['complement'];

			// According to the ldap selected, format de config params to be extracted
			if ($params['useCCParams'] == true || $params['useCCParams'] == "true")
			{
				$ldap_indexes = array('module' => 'contactcenter',
										'host' => 'cc_ldap_host0',
										'basedn' => 'cc_ldap_context0',
										'user' => 'cc_ldap_browse_dn0',
										'passwd' => 'cc_ldap_pw0');
			}
			else
			{
				$ldap_indexes = array('module' => 'workflow',
										'host' => 'ldap_host',
										'basedn' => 'ldap_user_context',
										'user' => 'ldap_user',
										'passwd' => 'ldap_password');

			}
			$ajaxConfig = &Factory::newInstance('ajax_config', $ldap_indexes['module']);
			$config = $ajaxConfig->read_repository();

			$ldapconfig['host'] = $config[$ldap_indexes['host']];
			$ldapconfig['basedn'] = $config[$ldap_indexes['basedn']];
			$usuario = $config[$ldap_indexes['user']];
			$senha = $config[$ldap_indexes['passwd']];

			$referrals = ($params['useCCParams'] == "true") ? 1 : $config['ldap_follow_referrals'];

			if($usuario != "" && $senha != ""){
				$ds = ldap_connect($ldapconfig['host']);

				ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
				ldap_set_option($ds, LDAP_OPT_REFERRALS, $referrals);

				$lbind = @ldap_bind($ds, $usuario, $senha);

				$filter     = '(&(uid=*)(phpgwAccountType=u)(!(phpgwAccountVisible=-1))(cn=*' . $cn . '*))';

				if($ret_complement != '')
					$attrib_ret = array('cn', $ret_id, $ret_name, $ret_complement);
				else
					$attrib_ret = array('cn', $ret_id, $ret_name);

				$r = ldap_search($ds, $ldapconfig['basedn'], $filter, $attrib_ret, 0, 0, 5);

				if($r){
					if(ldap_count_entries($ds, $r) == 0){
						$output['msg'] = 'Não foram encontrados registros.';
					}
					elseif(ldap_count_entries($ds, $r) < 200){

						$result = ldap_get_entries($ds, $r);

						foreach($result as $value){ 
							if( ($value[$ret_name][0] != '') && ($value[$ret_complement][0] != '') ){
									$complement = " >> " . $value[$ret_complement][0];
									$output['values'][] = array(
																 'id'   => $value[$ret_id],
																 'name' => trim(ucwords(strtolower($value[$ret_name][0]))) . $complement
															   ); 

							}
						elseif($value[$ret_name][0] != ''){
								$output['values'][] = array(
															 'id'   => $value[$ret_id],
															 'name' => trim(ucwords(strtolower($value[$ret_name][0])))
														   ); 
							}
						else{
								$output['values'][] = array(
															 'id'   => '',
															 'name' => "-- Selecione uma Opção --"
														   ); 
							}

						}

						foreach ($output['values'] as $key => $value){
							$nome[$key] = $value['name'];
							$ids[$key] = $value['id'];
						}
						// faz ordenação de um array multidimensional
						array_multisort($nome, SORT_ASC, $ids, SORT_ASC, $output['values']);
					}
					else{
						$output['msg'] = 'Foram encontrados mais de 200 registros, por favor, refine sua pesquisa.';
					}
				}
				else{
					$output['msg'] = 'Não foram encontrados registros.';
				}

				ldap_close($ds);
			}
		}
		else{
			$output['msg'] = 'Não digite caracteres especiais nem números. Apenas letras e espaços são permitidos.';
		} 
		return $output;
	}
}
?>
