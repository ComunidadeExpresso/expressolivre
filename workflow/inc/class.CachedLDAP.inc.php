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
* Gera um cache do LDAP (em banco de dados) para não perder informações no caso de exclusão de um funcionário
* @author Sidnei Augusto Drovetto Junior - drovetto@gmail.com
* @version 1.0
* @license http://www.gnu.org/copyleft/gpl.html GPL
* @package Workflow
*/
class CachedLDAP
{
	/**
	* @var int $OPERATION_MODE_NORMAL Modo de operação normal (acessa primeiro o BD e, se necessário, acessa o LDAP e atualiza o BD)
	* @access public
	*/
	public $OPERATION_MODE_NORMAL = 0;

	/**
	* @var int $OPERATION_MODE_LDAP Modo de operação LDAP (acessa o LDAP e atualiza o BD)
	* @access public
	*/
	public $OPERATION_MODE_LDAP = 1;

	/**
	* @var int $OPERATION_MODE_DATABASE Modo de operação banco de dados (acessa somente o BD)
	* @access public
	*/
	public $OPERATION_MODE_DATABASE = 2;

	/**
	* @var int $OPERATION_MODE_LDAP_DATABASE Modo de operação LDAP e banco de dados. Acessa primeiro o LDAP e, somente se o usuário não existir no LDAP que o banco de dados será consultado.
	* @access public
	*/
	public $OPERATION_MODE_LDAP_DATABASE = 3;

	/**
	* @var string $userContext Contexto do usuário (LDAP)
	* @access protected
	*/
	protected $userContext;

	/**
	* @var string $groupContext Contexto do grupo (LDAP)
	* @access protected
	*/
	protected $groupContext;

	/**
	* @var resource $dataSource Conexão com o LDAP
	* @access protected
	*/
	protected $dataSource;

	/**
	* @var int $operationMode Modo de operação da classe
	* @access private
	*/
	private $operationMode;

	/**
	* @var array $entryAttributes Os atributos que serão buscados (LDAP ou Banco de Dados)
	* @access private
	*/
	private $entryAttributes = array('uid', 'cn', 'givenname', 'mail', 'sn', 'accountstatus', 'uidnumber', 'dn', 'employeenumber', 'cpf', 'telephonenumber');

	/**
	 * @var array $entryAttributesLDAP Attributes thats only exists in LDAP.
	 * The attributes mobile and homePhone are not present in databaseCache, because they may have more
	 * This is not the very best approach because the best solution should be store all attributes
	 * from Ldap into databaseCache
	 * than one value
	 * @access private
	 * */
	private $entryAttributesLDAP = array('mobile','homephone');

	/**
	* @var resource $DBLink Link com o banco de dados
	* @access protected
	*/
	protected $DBLink = null;

	/**
	* @var int $cacheDays O número de dias antes do cache ser renovado
	* @access private
	*/
	private $cacheDays = 2;

	/**
	* Estabelece uma conexão com o LDAP
	* @return void
	* @access protected
	*/
	protected function loadLDAP()
	{
		/* check if the information was already loaded */
		if (!empty($this->userContext))
			return;

		/* load the information and establish a connection */
		$tmpLDAP =& Factory::getInstance('WorkflowLDAP');
		$this->userContext  = $tmpLDAP->getUserContext();
		$this->groupContext = $tmpLDAP->getGroupContext();
		$this->dataSource =& Factory::getInstance('WorkflowObjects')->getLDAP();
	}

	/**
	* Estabelece uma conexão com o Banco de Dados
	* @return void
	* @access protected
	*/
	protected function loadDB()
	{
		/* check if the information was already loaded */
		if (!is_null($this->DBLink))
			return;

		/* establish a connection */
		$this->DBLink =& Factory::getInstance('WorkflowObjects')->getDBGalaxia()->Link_ID;
	}

	/**
	* Executa uma query no LDAP e, retorna o seu resultado
	* @param string $ldapQuery A query do LDAP que será executada
	* @return mixed Uma array associativa caso o usuário seja encontrado ou false caso contrário
	* @access private
	*/
	private function runLDAP($ldapQuery)
	{
		/* load the information and establish the connection */
		$this->loadLDAP();

		$ldapfields = array();

		// Merge the arrays os attributes from databaseCache and Ldap
		$ldapfields = array_merge($this->entryAttributes,$this->entryAttributesLDAP);

		/* perform the search */
		$resourceIdentifier = ldap_search($this->dataSource, $this->userContext, $ldapQuery, $ldapfields);
		$entries = ldap_get_entries($this->dataSource, $resourceIdentifier);

		/* check the returned data */
		if ($entries['count'] != 1)
			return false;

		/* format the output */
		$output = array();
		foreach ($ldapfields as $attribute)
			if ($attribute == 'dn' or $attribute == 'mobile' or $attribute == 'homePhone')
				// Retrieve all occurrencies of mobile and homePhone
				$output[$attribute] = $entries[0][$attribute];
			else
				// Retrieve first occurrence of other attributes
				$output[$attribute] = $entries[0][$attribute][0];

		/* insert the timestamp of the last update */
		$output['last_update'] = mktime();

		return $output;
	}

	/**
	* Executa uma query no Banco de Dados para encontrar um usuário
	* @param string $bdQuery A cláusula WHERE da query
	* @param array $bindVariables Valores que substituirão o pontos de interrogação na query
	* @return mixed Uma array associativa caso o usuário seja encontrado ou false caso contrário
	* @access private
	*/
	private function runBD($bdQuery, $bindVariables)
	{
		/* establish the connection */
		$this->loadDB();

		/* perform the search */
		$resultSet = $this->DBLink->query('SELECT ' . implode(', ', $this->entryAttributes) . ', EXTRACT(EPOCH FROM last_update) AS last_update FROM egw_wf_user_cache WHERE ' . $bdQuery, $bindVariables);

		return $resultSet->fetchRow();
	}

	/**
	* Atualiza, em Banco de Dados, os atributos de um usuário
	* @param mixed $entry Uma array associativa contendo os atributos de um usuário (pode ser um boolean false)
	* @param string $bdQuery A cláusula WHERE da query
	* @param array $bindVariables Valores que substituirão o pontos de interrogação na query
	* @return void
	* @access private
	*/
	private function updateDB($entry, $bdQuery, $bindVariables)
	{
		/* establish the connection */
		$this->loadDB();


		if ($entry == false)
		{
			/* the user doesn't exist in LDAP anymore */
			$this->DBLink->query('UPDATE egw_wf_user_cache SET last_update = NULL WHERE ' . $bdQuery, $bindVariables);
		}
		else
		{
			/* unset the timestamp */
			unset($entry['last_update']);
			
			//UNSET ALL THE ATRIBUTES THAT WILL NOT BE STORAGED IN DB.
			foreach ($this->entryAttributesLDAP as $unsetAttribute) {
				unset($entry[$unsetAttribute]);	
			}

			/* insert/update the user info */
			$this->DBLink->query('DELETE FROM egw_wf_user_cache WHERE (uidnumber = ?)', array($entry['uidnumber']));
			$this->DBLink->query('INSERT INTO egw_wf_user_cache (' . implode(', ', array_keys($entry)) . ') VALUES (' . implode(', ', array_fill(0, count($entry), '?')) . ')', array_values($entry));
		}
	}

	/**
	* Faz a busca pelo usuário. Levando-se em conta o modo de operação selecionado
	* @param string $ldapQuery A query do LDAP que será executada
	* @param string $bdQuery A cláusula WHERE da query (se for utilizado o Banco de Dados)
	* @param array $bindVariables Valores que substituirão o pontos de interrogação na query de Banco de Dados
	* @return mixed Uma array associativa caso o usuário seja encontrado ou false caso contrário
	* @access private
	*/
	private function run($ldapQuery, $bdQuery, $bindVariables)
	{
		/* LDAP operation mode: load the user info in LDAP and then update the user info in the Database */
		if (($this->operationMode == $this->OPERATION_MODE_LDAP) || ($this->operationMode == $this->OPERATION_MODE_LDAP_DATABASE))
		{
			$entry = $this->runLDAP($ldapQuery);
			$this->updateDB($entry, $bdQuery, $bindVariables);

			/* return only the LDAP info when in LDAP operation mode, or if the user was found (when using the LDAP_DATABASE operation mode) */
			if (($this->operationMode == $this->OPERATION_MODE_LDAP) || (($this->operationMode == $this->OPERATION_MODE_LDAP_DATABASE) && ($entry !== false)))
				return $entry;
		}

		/* load the user information from the Database */
		$entry = $this->runBD($bdQuery, $bindVariables);

		/* return the database info when in LDAP_DATABASE operation mode (since the user wasn't found in the LDAP) */
		if ($this->operationMode == $this->OPERATION_MODE_LDAP_DATABASE)
			return $entry;

		/* normal operation mode: verify if the information in the Database is still valid; otherwise check it in LDAP */
		if ($this->operationMode == $this->OPERATION_MODE_NORMAL)
		{
			/* if the user was found in the Database and if the user doesn't exist in LDAP anymore or the information is updated, then return the info. Otherwise update it with LDAP info */
			if (($entry !== false) && ((is_null($entry['last_update'])) || (($entry['last_update'] + ($this->cacheDays * 24 * 60 * 60)) > mktime())))
				return $entry;

			/* we need to load info from the LDAP and update the Database record */
			$newEntry = $this->runLDAP($ldapQuery);
			$this->updateDB($newEntry, $bdQuery, $bindVariables);

			/* return the information from LDAP if the user was found; otherwise return the entry from the database */
			if ($newEntry !== false)
				return $newEntry;
			else
				return $entry;
		}

		return $entry;
	}

	/**
	* Construtor da classe CachedLDAP
	* @return object Objeto da classe CachedLDAP
	* @access public
	*/
	function CachedLDAP()
	{
		/* set the default operation mode */
		$this->setOperationMode($this->OPERATION_MODE_NORMAL);
	}

	/**
	* Define o modo como a classe vai operar (se busca somente no LDAP, no Banco de Dados, etc.)
	* @param int $operationMode O modo de operação da classe
	* @return void
	* @access public
	*/
	function setOperationMode($operationMode)
	{
		/* make sure it's an integer */
		$operationMode = (int) $operationMode;
		$availableOptions = array(
			$this->OPERATION_MODE_LDAP,
			$this->OPERATION_MODE_DATABASE,
			$this->OPERATION_MODE_NORMAL,
			$this->OPERATION_MODE_LDAP_DATABASE);

		/* check if the value is valid and set it */
		if (in_array($operationMode, $availableOptions, true))
			$this->operationMode = $operationMode;
	}

	/**
	* Prepara os parâmetros para buscar o usuário
	* @param string $field O nome do campo pelo qual a busca será feita
	* @param string $value O valor que será utilizao na busca
	* @return mixed Uma array associativa caso o usuário seja encontrado ou false caso contrário
	* @access private
	*/
	private function getEntry($field, $value)
	{
		/* build the queries */
		$ldapQuery = "(&({$field}={$value})(phpgwaccounttype=u))";
		$bdQuery = "({$field} = ?)";
		$bindVariables = array($value);

		return $this->run($ldapQuery, $bdQuery, $bindVariables);
	}

	/**
	* Busca um usuário pelo seu ID
	* @param int $userID O ID do usuário
	* @return mixed Uma array associativa caso o usuário seja encontrado ou false caso contrário
	* @access public
	*/
	function getEntryByID($userID)
	{
		return $this->getEntry('uidnumber', (int) $userID);
	}

	/**
	* Busca um usuário pelo seu CPF
	* @param string $CPF O CPF do usuário
	* @return mixed Uma array associativa caso o usuário seja encontrado ou false caso contrário
	* @access public
	*/
	function getEntryByCPF($CPF)
	{
		return $this->getEntry('cpf', $CPF);
	}

	/**
	* Busca um usuário pelo seu e-mail
	* @param string $email O e-mail do usuário
	* @return mixed Uma array associativa caso o usuário seja encontrado ou false caso contrário
	* @access public
	*/
	function getEntryByEmail($email)
	{
		return $this->getEntry('mail', $email);
	}

	/**
	* Busca um usuário pela sua matrícula
	* @param int $employNumber A matrícula do usuário
	* @return mixed Uma array associativa caso o usuário seja encontrado ou false caso contrário
	* @access public
	*/
	function getEntryByEmployeeNumber($employNumber)
	{
		return $this->getEntry('employeenumber', (int) $employNumber);
	}

	/**
	* Busca um usuário pelo seu uid
	* @param string $uid O uid do usuário
	* @return mixed Uma array associativa caso o usuário seja encontrado ou false caso contrário
	* @access public
	*/
	function getEntryByUid($uid)
	{
		return $this->getEntry('uid', $uid);
	}

}
?>
