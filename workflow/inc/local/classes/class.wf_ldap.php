<?php

/**
 * Class for basic operations with user accounts on LDAP to be used in activities only
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package Workflow
 * @subpackage local
 */
class wf_ldap
{
	/**
	 * @var $ds resource recurso
	 * @access public
	 */
	var $ds;

	/**
	 * @var $user_context string contexto do usuário
	 * @access public
	 */
	var $user_context  = '';

	/**
	 * @var $group_context string contexto do grupo
	 * access public
	 */
	var $group_context = '';

	/**
	 * @var object $cachedLDAP Objeto da classe CachedLDAP
	 * access private
	 */
	private $cachedLDAP;

	/**
	 *  Construtor da classe wf_ldap Inicializa a conexão com o LDAP
	 *  @return object
	 *  @access public
	 */
	function wf_ldap()
	{
		$tmpLDAP = Factory::getInstance('WorkflowLDAP');
		$this->user_context  = $tmpLDAP->getUserContext();
		$this->group_context = $tmpLDAP->getGroupContext();

		$this->ds = &Factory::getInstance('WorkflowObjects')->getLDAP();

		$this->cachedLDAP = &Factory::getInstance('wf_cached_ldap');
		$this->cachedLDAP->setOperationMode($this->cachedLDAP->OPERATION_MODE_LDAP);
	}

	/**
	 *  Busca registros do LDAP de acordo com o o id e tipo de conta passados como parametro
	 *  @param int $account_id ID do usuário ou grupo.
	 *  @param string $account_type Tipo de conta ("u" usuario,"g" grupo)
	 *  @return array Informações do(s) registro(s) encontrado(s).
	 *  @access public
	 */
	function get_entry($account_id, $account_type = "u")
	{
		if ($account_type == "u")
		{
			return $this->cachedLDAP->getEntryByID($account_id);
		}
		elseif ($account_type == "g")
		{
			$sri = ldap_search($this->ds, $this->group_context, '(&(gidnumber=' . (int)$account_id . ')(phpgwaccounttype=g))');
			$allvalues = ldap_get_entries($this->ds, $sri);
			return array( 'dn' => $allvalues[0]['dn'],
				'memberuid' => $allvalues[0]['memberuid'],
				'gidnumber' => $allvalues[0]['gidnumber'][0],
				'cn' => $allvalues[0]['cn'][0]
			);
		}
		else
		{
			return false;
		}

	}

	/**
	 *  Busca um registro do LDAP de acordo com o CPF.
	 *  @param int $cpf CPF do usuário.
	 *  @return array Informações do registro encontrado.
	 *  @acess public
	 */
	function get_entry_by_cpf($cpf)
	{
		return $this->cachedLDAP->getEntryByCPF($cpf);
	}

	/**
	 *  Busca um registro do LDAP de acordo com o e-mail.
	 *  @param string $email E-mail do usuário.
	 *  @return array Informações do registro encontrado.
	 *  @acess public
	 */
	function get_entry_by_email($email)
	{
		return $this->cachedLDAP->getEntryByEmail($email);
	}

	/**
	 *  Busca um registro do LDAP de acordo com a matricula.
	 *  @param int $matricula Matrícula do usuário.
	 *  @return array Informações do registro encontrado.
	 *  @acess public
	 */
	function get_entry_by_matricula($matricula)
	{
		return $this->cachedLDAP->getEntryByEmployeeNumber($matricula);
	}

	/**
	 *  Busca um registro do LDAP de acordo com o uid.
	 *  @param string $uid Uid do usuário.
	 *  @return array Informações do registro encontrado.
	 *  @acess public
	 */
	function get_entry_by_uid($uid)
	{
		return $this->cachedLDAP->getEntryByUid($uid);
	}


	/**
	 *  Busca nomes de uma array de IDs.
	 *  @param array $IDs Os IDs dos quais se quer o nome.
	 *  @return array Uma array contento os IDs e os nomes encontrados.
	 *  @acess public
	 */
	function getNames($IDs)
	{
		if (!is_array($IDs))
			return false;

		/* faz a busca para encontrar os nomes */
		$resourceIdentifier = ldap_search($this->ds, $this->user_context, "(|" . implode('', array_map(create_function('$a', 'return "(uidnumber={$a})";'), array_unique($IDs))) . ")", array('cn', 'uidnumber'));
		$result = ldap_get_entries($this->ds, $resourceIdentifier);

		/* prepara a saída */
		$output = array();
		for ($i = 0; $i < $result['count']; ++$i)
			$output[] = array(
				'id' => $result[$i]['uidnumber'][0],
				'name' => $result[$i]['cn'][0]);

		/* retorna os IDs/nomes */
		return $output;
	}

	/**
	 * Termina a conexão com o LDAP
	 * @return void
	 * @acess public
	 */
	function close()
	{
		ldap_close($this->ds);
	}
}
?>
