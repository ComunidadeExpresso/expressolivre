<?php
/**************************************************************************\
* eGroupWare                                                               *
* http://www.egroupware.org                                                *
* --------------------------------------------                             *
*  This program is free software; you can redistribute it and/or modify it *
*  under the terms of the GNU General Public License as published by the   *
*  Free Software Foundation; either version 2 of the License, or (at your  *
*  option) any later version.                                              *
\**************************************************************************/

require_once 'common.inc.php';
require_once 'class.bo_ajaxinterface.inc.php';

/**
 * @package Workflow
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @author Sidnei Augusto Drovetto Jr. - drovetto@gmail.com
 * @author Rodrigo Daniel C de Lira - rodrigo.lira@gmail.com
 */
class bo_participants extends bo_ajaxinterface
{
	/**
	 * @var resource $ldap Conexão com o LDAP
	 * @access public
	 */
	var $ldap;

	/**
	 * Construtor da classe bo_participants
	 * @return object
	 * @access public
	 */
	function bo_participants()
	{
		$useCCParams = (isset($_REQUEST['useCCParams']) && $_REQUEST['useCCParams'] !== 'false') ? (bool)$_REQUEST['useCCParams'] : false;

		$this->ldap = &Factory::getInstance('WorkflowLDAP', $useCCParams);
	}

	/**
	 * Busca as organizações do LDAP
	 * @return array A lista de organizações
	 * @access public
	 */
	function getOrganizations()
	{
		return $this->ldap->getOrganizations();
	}

	/**
	 * Busca as entidades (usuários, grupos ou listas públicas) de uma organização
	 * @param array $params Array que contém parâmetros necessários para fazer a busca (podem ser advindos de Ajax)
	 * @param bool $raw Se false, indica que os dados devem ser retornados em código HTML (via Ajax), se true indica que os dados devem ser retornados em forma de array
	 * @return mixed A lista de entidades em formato de array ou em formato de string HTML para construção de combo box
	 * @access public
	 */
	function getEntities($params, $raw = false)
	{
		if (preg_match('/^[a-z0-9_\- =,]+$/i', $params['context']) < 1)
		{
			if ($raw)
				return array();
			else
				return '';
		}

		if($params['id'] === 'mail')
		{
			$id = 'mail';
			$usePreffix = false;
		}
		else
		{
			$id = 'id';
			$usePreffix = ($params['usePreffix'] == true) ? true : false;
		}

		$output = array();
		$entities = $params['entities'];

		/* se requisitado, carrega os usuários */
		if (strpos($entities, 'u') !== false)
		{
			$preffix = ($usePreffix) ? 'u' : '';
			$ents = $this->ldap->getUsers($params['context'], $params['onlyVisibleAccounts']);
			foreach ($ents as $ent)
				$output[$preffix . $ent[$id]] = $ent['name'];
		}

		/* se requisitado, carrega os grupos */
		if (strpos($entities, 'g') !== false)
		{
			$preffix = ($usePreffix) ? 'g' : '';
			$ents = $this->ldap->getGroups($params['context']);
			foreach ($ents as $ent)
				$output[$preffix . $ent[$id]] = $ent['name'];
		}

		/* se requisitado, carrega as listas públicas */
		if (strpos($entities, 'l') !== false)
		{
			$preffix = ($usePreffix) ? 'l' : '';
			$ents = $this->ldap->getPublicLists($params['context']);
			foreach ($ents as $ent)
				$output[$preffix . $ent[$id]] = $ent['name'];
		}

		if (!$raw)
			$output = implode("\n", array_map(create_function('$a,$b', 'return \'<option value="\' . $a . \'">\' . $b . \'</option>\';'), array_keys($output), array_values($output)));

		return $output;
	}

	/**
	 * Busca os setores de um dado contexto
	 * @param array $params Array que contém parâmetros necessários para fazer a busca (podem ser advindos de Ajax)
	 * @param bool $raw Se false, indica que os dados devem ser retornados em código HTML (via Ajax), se true indica que os dados devem ser retornados em forma de array
	 * @return mixed A lista de setores em formato de array ou em formato de string HTML para construção de combo box
	 * @access public
	 */
	function getSectors($params, $raw = false)
	{
		if (preg_match('/^[a-z0-9_\- ]+$/i', $params['organization']) < 1)
		{
			if ($raw)
				return array();
			else
				return array('sectors' => '', 'participants' => '');
		}

		$output = array('ou=' . $params['organization'] . ',' . $this->ldap->getLDAPContext() => $params['organization']);
		$sectorList = $this->ldap->getSectors($params['organization'], true);
		foreach ($sectorList as $sector)
			$output[$sector['dn']] = str_repeat('&nbsp;', 3 * $sector['level']) . $sector['ou'];

		if (!$raw)
		{
			$newOutput['sectors'] = implode("\n", array_map(create_function('$a,$b', 'return \'<option value="\' . $a . \'">\' . $b . \'</option>\';'), array_keys($output), array_values($output)));
			reset($output);
			$params['context'] = key($output);
			$newOutput['participants'] = $this->getEntities($params, false);
			$output = $newOutput;
		}

		return $output;
	}

	/**
	 * Efetua uma busca no catálogo LDAP (completo)
	 * @param array $params Array que contém parâmetros necessários para fazer a busca (podem ser advindos de Ajax)
	 * @param bool $raw Se false, indica que os dados devem ser retornados em código HTML (via Ajax), se true indica que os dados devem ser retornados em forma de array
	 * @return mixed Os registros encontrados, em formato de array ou em formato de string HTML para construção de combo box
	 * @access public
	 */
	function globalSearch($params, $raw = false)
	{
		$entities = $params['entities'];

		$searchTerm = $params['searchTerm'];

		if (strlen(str_replace(' ', '', $searchTerm)) < 3)
			return array('warnings' => array('Utilize ao menos três caracteres em sua busca.'));

		if (preg_match('/^[a-z0-9_\- =,]+$/i', $searchTerm) < 1)
			return array('warnings' => array('O parâmetro de busca é inválido.'));

		$searchTerm = '*' . str_replace(' ', '*', trim($searchTerm)) . '*';

		$searchUsers = (strpos($entities, 'u') !== false);
		$searchGroups = (strpos($entities, 'g') !== false);
		$searchLists = (strpos($entities, 'l') !== false);

		$onlyVisibleAccounts = $params['onlyVisibleAccounts'];

		/* faz a busca */
		$output = array();
		$output['participants'] = $this->ldap->search($searchTerm, $searchUsers, $searchGroups, $searchLists, null, $onlyVisibleAccounts);

		/* limita os resultados e define uma mensagem que será exibida */
		$participantsCount = count($output['participants']);
		if ($participantsCount > 200)
		{
			$participantsCount = 200;
			$output['participants'] = array_slice($output['participants'], 0, 200);
			$output['warnings'][] = 'Sua busca foi limitada a 200 registros.';
		}
		else
		{
			if ($participantsCount === 0)
				$output['warnings'][] = 'Nenhum registro encontrado.';
			else
				if ($participantsCount > 1)
					$output['warnings'][] = $participantsCount . ' registros encontrados';

		}

		/* se necessário, gera a saída em formato HTML */
		if (!$raw)
		{
			if($params['id'] === 'mail')
			{
				$id = 'mail';
				$usePreffix = false;
			}
			else
			{
				$id = 'id';
				$usePreffix = ($params['usePreffix'] == true) ? true : false;
			}

			$newOutput = array();
			foreach ($output['participants'] as $row)
			{
				$organization = str_replace('ou=', '', implode('/', array_reverse(array_filter(explode(',', $row['dn']), create_function('$a', 'return (substr($a, 0, 2) == "ou");')))));
				$key = ($usePreffix ? $row['type'] : '') . $row[$id];
				$value = "{$row['name']} ({$organization})";
				$newOutput[$key] = $value;
			}

			$newOutput = implode("\n", array_map(create_function('$a,$b', 'return \'<option value="\' . $a . \'">\' . $b . \'</option>\';'), array_keys($newOutput), array_values($newOutput)));
			$output['participants'] = $newOutput;
		}

		return $output;
	}
}
