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

/**
 * Implements common LDAP methods
 * @author Sidnei Augusto Drovetto Jr. - drovetto@gmail.com
 * @version 1.0
 * @package Workflow
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 */
class WorkflowLDAP
{
	/**
	 * @var resource $dataSource Recurso de conexão com o LDAP
	 * @access private
	 */
	private $dataSource;

	/**
	 * @var string $userContext Contexto do usuário
	 * @access private
	 */
	private $userContext;

	/**
	 * @var string $groupContext Contexto do grupo
	 * @access private
	 */
	private $groupContext;

	/**
	 * @var string $ldapContext Contexto do LDAP
	 * @access private
	 */
	private $ldapContext;

	/**
	 * Construtor da classe
	 * @return object
	 * @access public
	 */
	function WorkflowLDAP($useCCParams = false)
	{
		$this->dataSource =& Factory::getInstance('WorkflowObjects')->getLDAP($useCCParams);

		/* get the required parameters */
		$info = (isset($GLOBALS['phpgw_info']['server']['ldap_context'])) ?
			$GLOBALS['phpgw_info']['server'] :
			$_SESSION['phpgw_info']['workflow']['server'];
		$ldapConfigValues = galaxia_get_config_values(array('ldap_user_context' => '', 'ldap_group_context' => ''));
		if (empty($ldapConfigValues['ldap_user_context']))
			$ldapConfigValues['ldap_user_context'] = $info['ldap_context'];
		if (empty($ldapConfigValues['ldap_group_context']))
			$ldapConfigValues['ldap_group_context'] = $info['ldap_group_context'];
		$this->userContext = $ldapConfigValues['ldap_user_context'];
		$this->groupContext = $ldapConfigValues['ldap_group_context'];
		$this->ldapContext = $ldapConfigValues['ldap_user_context'];

		$this->cache = array(
			'getEntities' => array(),
			'getUserGroups' => array(),
			'getGroupUsers' => array(),
			'getOrganizations' => array(),
			'getNames' => array()
		);
	}

	/**
	 * Checa se um método, e seus parâmetros, já estãoe em cache
	 * @param string $methodName O nome do método que se quer verificar
	 * @param string $parameters Parâmetros passados para o método (serializados)
	 * @return bool true se foi encontrada uma versão em cache dos dados solicitados e false caso contrário
	 * @access private
	 */
	private function checkCache($methodName, $parameters)
	{
		return isset($this->cache[$methodName][$parameters]);
	}

	/**
	 * Pega a informação, em cache, de um método e seus parâmetros
	 * @param string $methodName O nome do método que está em cache
	 * @param string $parameters Parâmetros passados para o método (serializados)
	 * @return mixed O retorno do método usando os parâmetros passados
	 * @access private
	 */
	private function getCache($methodName, $parameters)
	{
		return $this->cache[$methodName][$parameters];
	}

	/**
	 * Coloca em cache um método e seus parâmetros
	 * @param string $methodName O nome do método
	 * @param string $parameters Parâmetros passados para o método (serializados)
	 * @param mixed $output Saída do método usando os parâmetros passados
	 * @return mixed A saída igual ao parâmetro $output
	 * @access private
	 */
	private function setCache($methodName, $parameters, $output)
	{
		$this->cache[$methodName][$parameters] = $output;
		return $output;
	}

	/**
	 * Faz consultas ao LDAP
	 * @param string $context Contexto das entidades
	 * @param string $filter Filtro utilizado para selecionar as entidades desejadas
	 * @param array $elements Array dos campos que se deseja obter
	 * @param bool $depthSearch Indica se a busca deve incluir sub-árvores ou não
	 * @param string $elementSort O elemento pelo qual se quer ordenar o resultado
	 * @return mixed Array do resultado. Ou false em caso de erro
	 * @access private
	 */
	private function runLDAP($context, $filter, $elements, $depthSearch = false, $elementSort = null)
	{
		if ($depthSearch)
			$resourceIdentifier = ldap_search($this->dataSource, $context, $filter, $elements);
		else
			$resourceIdentifier = ldap_list($this->dataSource, $context, $filter, $elements);

		if (!$resourceIdentifier)
			return false;

		if (!is_null($elementSort))
			ldap_sort($this->dataSource, $resourceIdentifier, $elementSort);

		$output = ldap_get_entries($this->dataSource, $resourceIdentifier);
		return $output;
	}

	/**
	 * Faz consultas ao LDAP (inclusive com campos binários)
	 * @param string $context Contexto das entidades
	 * @param string $filter Filtro utilizado para selecionar as entidades desejadas
	 * @param array $elements Array dos campos que se deseja obter
	 * @param bool $depthSearch Indica se a busca deve incluir sub-árvores ou não
	 * @param string $elementSort O elemento pelo qual se quer ordenar o resultado
	 * @return mixed Array do resultado. Ou false em caso de erro
	 * @access private
	 */
	private function runBinaryLDAP($context, $filter, $elements, $depthSearch = false, $elementSort = null)
	{
		if ($depthSearch)
			$resourceIdentifier = ldap_search($this->dataSource, $context, $filter, $elements);
		else
			$resourceIdentifier = ldap_list($this->dataSource, $context, $filter, $elements);

		if (!$resourceIdentifier)
			return false;

		if (!is_null($elementSort))
			ldap_sort($this->dataSource, $resourceIdentifier, $elementSort);

		$entry = ldap_first_entry($this->dataSource, $resourceIdentifier);
		if (!$entry)
			return array();

		$output = array();
		$counter = 0;
		do
		{
			$attributes = ldap_get_attributes($this->dataSource, $entry);
			for ($i = 0; $i < $attributes['count']; ++$i)
				$output[$counter][$attributes[$i]] = ldap_get_values_len($this->dataSource, $entry, $attributes[$i]);

			++$counter;
		} while ($entry = ldap_next_entry($this->dataSource, $entry));

		return $output;
	}

	/**
	 * Busca entidades (usuários, grupos ou listas públicas) do LDAP
	 * @param string $context Contexto das entidades
	 * @param string $filter Filtro utilizado para selecionar as entidades desejadas
	 * @param array $elements Array dos campos que representa, nesta ordem: o ID, o nome e o e-mail da entidade
	 * @param char $type O tipo da entidade: 'u' para usuários; 'g' para grupos; e 'l' para listas públicas
	 * @param bool $depthSearch Indica se a busca deve incluir sub-árvores ou não
	 * @return array Array das entidades
	 * @access private
	 */
	private function getEntities($context, $filter, $elements, $type, $depthSearch = false)
	{
		/* check if the required information is in cache */
		$methodName = 'getEntities';
		$parameters = serialize(func_get_args());
		if ($this->checkCache($methodName, $parameters))
			return $this->getCache($methodName, $parameters);

		$result = $this->runLDAP($context, $filter, $elements, $depthSearch, $elements[1]);

		$output = array();
		for ($i = 0; $i < $result['count']; ++$i)
		{
			$output[] = array(
				'id' => $result[$i][$elements[0]][0],
				'name' => $result[$i][$elements[1]][0],
				'type' => $type,
				'mail' => $result[$i][$elements[2]][0]);
		}

		/* store the information in cache and return the output */
		return $this->setCache($methodName, $parameters, $output);
	}

	/**
	 * Retorna os grupos de um usuário
	 * @param int $userID O ID do usuário
	 * @return array Array dos grupos
	 * @access public
	 */
	function getUserGroups($userID)
	{
		/* do not perform any search if the user is '*' */
		if ($userID == '*')
			return array();

		/* check if the required information is in cache */
		$methodName = 'getUserGroups';
		$parameters = serialize(func_get_args());
		if ($this->checkCache($methodName, $parameters))
			return $this->getCache($methodName, $parameters);

		/* check for error in connection */
		if (!$this->dataSource)
			return false;

		/* first, get the UID */
		$resourceIdentifier = ldap_search($this->dataSource, $this->userContext, "(&(phpgwaccounttype=u)(uidnumber={$userID}))", array('uid'));
		$result = ldap_get_entries($this->dataSource, $resourceIdentifier);
		if (!isset($result[0]['uid'][0]))
			return false;
		$userLogin = $result[0]['uid'][0];

		/* initialize some variables */
		$output = array();

		/* search the LDAP tree */
		$resourceIdentifier = ldap_search($this->dataSource, $this->groupContext, "(&(phpgwaccounttype=g)(memberuid={$userLogin}))", array('gidnumber'));
		$result = ldap_get_entries($this->dataSource, $resourceIdentifier);
		for ($i = 0; $i < $result['count']; ++$i)
			$output[] = $result[$i]['gidnumber'][0];

		/* store the information in cache and return the output */
		return $this->setCache($methodName, $parameters, $output);
	}

	/**
	 * Retorna os usuários de um grupo
	 * @param int $groupID O ID do grupo
	 * @return array Array dos usuários
	 * @access public
	 */
	function getGroupUsers($groupID)
	{
		/* check if the required information is in cache */
		$methodName = 'getGroupUsers';
		$parameters = serialize(func_get_args());
		if ($this->checkCache($methodName, $parameters))
			return $this->getCache($methodName, $parameters);

		/* check for error in connection */
		if (!$this->dataSource)
			return false;

		/* first, we get the UIDs that are members of the group */
		$resourceIdentifier = ldap_search($this->dataSource, $this->ldapContext, "(&(phpgwaccounttype=g)(gidnumber={$groupID}))", array('memberuid'));
		$result = ldap_get_entries($this->dataSource, $resourceIdentifier);
		if (!isset($result[0]['memberuid'][0]))
			return false;

		$userLogins = $result[0]['memberuid'];
		unset($userLogins['count']);

		/* load the user information ten users at a time. This approach was proven to be faster on some systems */
		$result = array();
		while (count($userLogins) > 0)
		{
			$selectedUserLogins = array_splice($userLogins, 0, 10);
			$resourceIdentifier = ldap_search($this->dataSource, $this->ldapContext, "(&(phpgwaccounttype=u)(|" . implode('', array_map(create_function('$a', 'return "(uid={$a})";'), $selectedUserLogins)) . "))", array('cn', 'uidnumber'));
			$result = array_merge($result, ldap_get_entries($this->dataSource, $resourceIdentifier));
			unset($result['count']);
		}

		$output = array();
		$userCount = count($result);
		for ($i = 0; $i < $userCount; ++$i)
			$output[] = array(
				'account_id' => $result[$i]['uidnumber'][0],
				'account_name' => $result[$i]['cn'][0]);

		/* sort the result */
		usort($output, create_function('$a,$b', 'return strcasecmp($a[\'account_name\'],$b[\'account_name\']);'));

		/* store the information in cache and return the output */
		return $this->setCache($methodName, $parameters, $output);
	}

	/**
	 * Retorna as organizações do nível raiz
	 * @return array Array de organizações
	 * @access public
	 */
	function getOrganizations()
	{
		/* check if the required information is in cache */
		$methodName = 'getOrganizations';
		$parameters = serialize(func_get_args());
		if ($this->checkCache($methodName, $parameters))
			return $this->getCache($methodName, $parameters);

		/* check for error in connection */
		if (!$this->dataSource)
			return false;

		/* load the sectors of level 0 as organizations, then format the data */
		$output = array_map(create_function('$a', 'return $a[\'ou\'];'), $this->getSectors());

		/* store the information in cache and return the output */
		return $this->setCache($methodName, $parameters, $output);
	}

	/**
	 * Retorna os setores de um determinado contexto
	 * @param string $organization A organização a partir da qual a busca deve inciar. Utilize null para o nível raiz
	 * @param bool $recursive Indica se a busca deve ser recursiva ou não. true indica que deve ser recursiva e false que não deve ser recursiva
	 * @param string $contextBase A base do contexto. Utilize null para o contexto padrão
	 * @param int $level Indica o nível atual de recursão
	 * @return array Array de setores
	 * @access public
	 */
	function getSectors($organization = null, $recursive = false, $contextBase = null, $level = 0)
	{
		/* determines the context in which the search will be performed */
		$context = (!is_null($contextBase) ? $contextBase : $this->ldapContext);
		$context = (!is_null($organization) ? 'ou=' . $organization . ',' : '') . $context;

		/* search for the sectors */
		$resourceIdentifier = @ldap_list($this->dataSource, $context, '(objectClass=organizationalUnit)', array('ou'));
		if ($resourceIdentifier === false)
			return false;

		ldap_sort($this->dataSource, $resourceIdentifier, 'ou');
		$result = ldap_get_entries($this->dataSource, $resourceIdentifier);

		/* collect the data */
		$output = array();
		for ($i = 0; $i < $result['count']; ++$i)
		{
			$output[] = array(
				'dn' => $result[$i]['dn'],
				'ou' => $result[$i]['ou'][0],
				'level' => $level);

			/* if requested, perform a recursive search */
			if ($recursive)
				$output = array_merge($output, $this->getSectors($result[$i]['ou'][0], $recursive, $context, $level + 1));
		}

		return $output;
	}

	/**
	 * Retorna os usuários de um determinado contexto
	 * @param string $context O contexto a partir do qual a busca deve começar
	 * @return array Array dos usuários encontrados
	 * @access public
	 */
	function getUsers($context, $onlyVisibleAccounts = true)
	{
		$filter = '(phpgwaccounttype=u)';
		if($onlyVisibleAccounts)
			$filter = '(&' . $filter . '(!(phpgwAccountVisible=-1)))';

		$elements = array('uidnumber', 'cn', 'mail');
		return $this->getEntities($context, $filter, $elements, 'u', false);
	}

	/**
	 * Retorna os grupos de um determinado contexto
	 * @param string $context O contexto a partir do qual a busca deve começar
	 * @return array Array dos grupos encontrados
	 * @access public
	 */
	function getGroups($context)
	{
		$filter = '(phpgwaccounttype=g)';
		$elements = array('gidnumber', 'cn', 'mail');
		return $this->getEntities($context, $filter, $elements, 'g', false);
	}

	/**
	 * Retorna listas públicas de um determinado contexto
	 * @param string $context O contexto a partir do qual a busca deve começar
	 * @return array Array das listas públicas encontradas
	 * @access public
	 */
	function getPublicLists($context)
	{
		$filter = '(phpgwaccounttype=l)';
		$elements = array('uidnumber', 'cn', 'mail');
		return $this->getEntities($context, $filter, $elements, 'l', false);
	}

	/**
	 * Retornar informação sobre um usuário
	 * @param int $userID O ID do usuário
	 * @return mixed Array contento informação sobre um usuário ou false se o usuário não for encontrado
	 * @access public
	 */
	function getUserInfo($userID)
	{
		$filter = '(&(phpgwaccounttype=u)(uidnumber=' . $userID . '))';
		$elements = array('uidnumber', 'cn', 'mail');
		$output = $this->getEntities($this->ldapContext, $filter, $elements, 'u', true);
		if (count($output) == 1)
			return $output[0];
		else
			return false;
	}

	function getUserPicture($userID)
	{
		$userID = (int) $userID;
		$filter = '(&(phpgwaccounttype=u)(uidnumber=' . $userID . '))';
		$elements = array('jpegPhoto');
		$result = $this->runBinaryLDAP($this->ldapContext, $filter, $elements, true);

		if (isset($result[0]['jpegPhoto'][0]))
			return $result[0]['jpegPhoto'][0];

		return false;
	}

	/**
	 * Retrieves information about a group
	 * @param int $userID The ID of the group
	 * @return mixed Array containing information about a group or false if the group is not found
	 * @access public
	 */
	function getGroupInfo($groupID)
	{
		$filter = '(&(phpgwaccounttype=g)(gidnumber=' . $groupID . '))';
		$elements = array('gidnumber', 'cn', 'mail');
		$output = $this->getEntities($this->ldapContext, $filter, $elements, 'g', true);
		if (count($output) == 1)
			return $output[0];
		else
			return false;
	}

	/**
	 * Retorna o tipo de uma entidade
	 * @param int $entityID O ID da entidade
	 * @return string O tipo da entidade ('g' para grupo e 'u' para usuário)
	 * @access public
	 */
	function getEntityType($entityID)
	{
		if ($entityID == '*')
			return false;

		$output = $this->getUserInfo($entityID);
		if (is_array($output))
			return 'u';
		else
		{
			$output = $this->getGroupInfo($entityID);
			if (is_array($output))
				return 'g';
		}

		return false;
	}

	/**
	 * Retorna o nome de uma entidade
	 * @param int $entityID O ID da entidade
	 * @return string O nome da entidade
	 * @access public
	 */
	function getName($entityID)
	{
		if ($entityID == '*')
			return '*';
		$output = $this->getUserInfo($entityID);
		if (is_array($output))
			return $output['name'];
		else
		{
			$output = $this->getGroupInfo($entityID);
			if (is_array($output))
				return $output['name'];
		}
	}

	/**
	 * Retorna os nomes de uma entidade
	 * @param array $entitiesID Os IDs das entidades
	 * @return string Os nomes das entidades
	 * @access public
	 */
	function getNames($entitiesID)
	{

		/* if parameter is not array make a new array with value in entitiesID */
		if(!is_array($entitiesID))
			$entitiesID = array($entitiesID);

		/* check if the required information is in cache */
		$methodName = 'getNames';
		$parameters = serialize(func_get_args());
		if ($this->checkCache($methodName, $parameters))
			return $this->getCache($methodName, $parameters);

		/* check for error in connection */
		if (!$this->dataSource)
			return false;

		/* check for '*' */
		$asteriskIndex = array_search('*', $entitiesID);
		if ($asteriskIndex !== false)
			unset($entitiesID[$asteriskIndex]);

		/* load the entity information ten entities at a time. This approach was proven to be faster on some systems */
		$result = array();

		while (count($entitiesID) > 0)
		{
			$selectedEntitiesID = array_splice($entitiesID, 0, 10);

			/* if parameter is null make array without values */
			if(!is_array($entitiesID))
				$entitiesID = array();

			// Search for all entries that uidnumber or gidnumber matches the arguments
			// and that account type is user, list or group
			$resourceIdentifier = ldap_search(
				$this->dataSource,
				$this->ldapContext,
				sprintf(
					'(&(|%s)(|(phpgwaccounttype=u)(phpgwaccounttype=l)(phpgwaccounttype=g)))',
					implode(
						'',
						array_map(
							create_function('$a', 'return "(uidnumber={$a})(&(gidnumber={$a})(phpgwaccounttype=g))";'),
							array_unique($selectedEntitiesID)
						)
					)
				),
				array('cn', 'uidnumber', 'gidnumber', 'phpgwaccounttype')
			);

			$entries = ldap_get_entries($this->dataSource, $resourceIdentifier);
			if (is_array($entries)) {
				$result = array_merge($result, $entries);
			}
			
			unset($result['count']);
		}

		$output = array();
		$entityCount = count($result);
		for ($i = 0; $i < $entityCount; ++$i)
			$output[] = array(
				'id' => ($result[$i]['phpgwaccounttype'][0] == 'g') ? $result[$i]['gidnumber'][0] : $result[$i]['uidnumber'][0],
				'name' => $result[$i]['cn'][0]);

		if ($asteriskIndex !== false)
			$output[] = array('id' => '*', 'name' => '*');

		/* sort the result */
		usort($output, create_function('$a,$b', 'return strcasecmp($a[\'name\'],$b[\'name\']);'));

		/* store the information in cache and return the output */
		return $this->setCache($methodName, $parameters, $output);
	}

	/**
	 * Faz uma busca (em todo o catálogo) por um nome
	 * @param string $searchTerm O termo que está sendo procurado (pode conter '*')
	 * @param bool $includeUsers Indica se na busca devem ser incluídos os registros referentes a usuários (true por padrão)
	 * @param bool $includeGroups Indica se na busca devem ser incluídos os registros referentes a grupos (false por padrão)
	 * @param bool $includeLists Indica se na busca devem ser incluídos os registros referentes a listas (false por padrão)
	 * @return array Uma array contendo os registros encontrados
	 * @access public
	 */
	function search($searchTerm, $includeUsers = true, $includeGroups = false, $includeLists = false, $context = null, $onlyVisibleAccounts = true)
	{
		if (!($includeUsers || $includeGroups || $includeLists))
			return false;

		if (is_null($context))
			$context = $this->ldapContext;

		/* check for error in connection */
		if (!$this->dataSource)
			return false;

		$entityFilter = array();
		if ($includeUsers)
			$entityFilter[] = '(phpgwaccounttype=u)';
		if ($includeGroups)
			$entityFilter[] = '(phpgwaccounttype=g)';
		if ($includeLists)
			$entityFilter[] = '(phpgwaccounttype=l)';

		if (count($entityFilter) > 1)
			$entityFilter = '(|' . implode('', $entityFilter) . ')';
		else
			$entityFilter = $entityFilter[0];

		if($onlyVisibleAccounts)
			$filter = "(&{$entityFilter}(cn={$searchTerm})(!(phpgwAccountVisible=-1)))";
		else
			$filter = "(&{$entityFilter}(cn={$searchTerm}))";

		$resourceIdentifier = ldap_search($this->dataSource, $context, $filter, array('cn', 'uidnumber', 'gidnumber', 'phpgwaccounttype', 'mail'));
		ldap_sort($this->dataSource, $resourceIdentifier, 'cn');
		$result = ldap_get_entries($this->dataSource, $resourceIdentifier);

		$output = array();
		for ($i = 0; $i < $result['count']; ++$i)
			$output[] = array(
				'id' => ($result[$i]['phpgwaccounttype'][0] == 'g') ? $result[$i]['gidnumber'][0] : $result[$i]['uidnumber'][0],
				'name' => $result[$i]['cn'][0],
				'mail' => $result[$i]['mail'][0],
				'type' => $result[$i]['phpgwaccounttype'][0],
				'dn' => $result[$i]['dn']
			);

		/* store the information in cache and return the output */
		return $output;
	}

	/**
	 * Retorna o contexto de usuários do LDAP
	 * @return string O contexto de usuários do LDAP
	 * @access public
	 */
	function getUserContext()
	{
		return $this->userContext;
	}

	/**
	 * Retorna o contexto de grupos do LDAP
	 * @return string O contexto de grupos do LDAP
	 * @access public
	 */
	function getGroupContext()
	{
		return $this->groupContext;
	}

	/**
	 * Retorna o contexto do LDAP
	 * @return string O contexto do LDAP
	 * @access public
	 */
	function getLDAPContext()
	{
		return $this->ldapContext;
	}

	/**
	 * Retorna a organização de um DN
	 * @param mixed $DN O DN do usuário
	 * @return mixed Uma string contendo a organização ou false caso não seja achada a organização
	 * @access public
	 */
	public function getOrganizationFromDN($DN)
	{
		$userContext = str_replace(' ', '', $this->userContext);
		$DN = str_replace(' ', '', $DN);
		$userInfo = array_reverse(explode(',', substr($DN, 0, - (strlen($userContext) + 1))));
		foreach ($userInfo as $attributePair)
		{
			list($name, $value) = explode('=', $attributePair, 2);
			if ($name === 'ou')
				return $value;
		}

		return false;
	}
}
?>
