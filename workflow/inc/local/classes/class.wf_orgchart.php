<?php
/**
 * Classe que permite aos processos workflow fazer consultas ao Organograma
 *
 * @author Sidnei Augusto Drovetto Junior - drovetto@gmail.com
 * @version 1.2
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package Workflow
 * @subpackage local
 */
class wf_orgchart
{
	/**
	 * @var object $db Link para o Banco de Dados do Workflow.
	 * @access private
	 */
	var $db;
	var $ldap;

	/**
	 * Construtor da classe wf_orgchart
	 * @return object
	 * @access public
	 */
	function wf_orgchart()
	{
		$this->db = &Factory::getInstance('WorkflowObjects')->getDBWorkflow()->Link_ID;
		$this->db->setFetchMode(ADODB_FETCH_ASSOC);

		$this->ldap = Factory::getInstance('CachedLDAP');
	}

	/**
	 * Busca uma organiza��o pelo seu ID.
	 *
	 * Este m�todo ir� procurar uma organiza��o na tabela de organiza��es, pelo seu ID, e retornar� seu dados b�sicos.
	 * @param int $organizationID O ID da organiza��o.
	 * @return array Uma array associativa contendo os atributos de uma organiza��o:
	 * - organizacao_id
	 * - nome: o nome abreviado da organiza��o
	 * - descri��o: o nome completo da organiza��o
	 * - url_imagem: a url onde se encontra o gr�fico da organiza��o
	 * - ativa: se a organiza��o est� ativa ou n�o
	 * - sitio: a url da p�gina web da organiza��o
	 * @access public
	 */
	function getOrganization($organizationID)
	{
		$query = "SELECT organizacao_id, nome, descricao, url_imagem, ativa, sitio" .
				 "  FROM organizacao" .
				 " WHERE (organizacao_id = ?)";

		$result = $this->db->query($query, array($organizationID));
		if (!$result)
			return false;

		$output = $result->fetchRow();

		return $output;
	}

	/**
	 * Busca uma organiza��o pelo seu nome.
	 *
	 * Este m�todo ir� buscar os dados b�sicos de uma organiza��o, procurando pela sua sigla.
	 * @param string $name A sigla da organiza��o.
	 * @return array Uma array associativa contendo os atributos de uma organiza��o:
	 * - organizacao_id
	 * - nome: a sigla da organiza��o
	 * - descricao: o nome completo
	 * - url_imagem: a url onde se encontra o gr�fico da organiza��o
	 * - ativa: se a organiza��o est� ativa ou n�o
	 * - sitio: a url da p�gina web da organiza��o
	 * @access public
	 */
	function getOrganizationByName($name)
	{
		$query = "SELECT organizacao_id, nome, descricao, url_imagem, ativa, sitio" .
				 "  FROM organizacao" .
				 " WHERE (UPPER(nome) = UPPER(?))";

		$result = $this->db->query($query, array($name));
		if (!$result)
			return false;

		$output = $result->fetchRow();

		return $output;
	}

	/**
	 * Lista todos os telefones �teis de uma organiza��o.
	 *
	 * Este m�todo ir� listar a tabela telefone.
	 * @param int $organizationID O ID da organiza��o.
	 * @return array Uma array de arrays associativas contendo a lista dos telefones de uma organiza��o:
	 * - telefone_id
	 * - descricao
	 * - organizacao_id
	 * @access public
	 */
	function getOrganizationTelephones($organizationID)
	{
		$query = "SELECT telefone_id, descricao, organizacao_id" .
				 "  FROM telefone" .
				 "	WHERE organizacao_id = ?";

		$result = $this->db->query($query, array((int) $organizationID));
		if (!$result)
			return false;

		$output = $result->GetArray(-1);

		return $output;
	}

	/**
	 * Busca os funcion�rios de uma organiza��o
	 *
	 * Este m�todo ir� buscar na tabela de funcion�rios, todos os funcion�rios que pertencem � organiza��o solicitada.
	 * @param int $organizationID O ID da organiza��o.
	 * @param boolean $searchLdap True, caso seja necess�rio buscar no LDAP os dados dos usu�rios. Ou false, caso contr�rio.
	 * @param boolean $onlyActiveUsers true para retornar somente usu�rios ativos e false caso contr�rio
	 * @return array Uma array seq�encial contendo os funcion�rios de uma organiza��o. Cada linha do array conter�:
	 * - organizacao_id
	 * - funcionario_id: uidNumber do funcion�rio
	 * - localidade_id
	 * - localidade_descricao
	 * - area_id
	 * - area_sigla
	 * - centro_custo_id
	 * - nm_centro_custo: n�mero do centro de custo
	 * - centro_custo_descricao
	 * - nome: nome do funcion�rio (quando busca no Ldap)
	 * - email: email do funcion�rio (quando busca no Ldap)
	 * - telefone: telefone do funcion�rio (quando busca no Ldap)
	 * - uid: uid do funcion�rio (quando busca no Ldap)
	 * @access public
	 */
	function getOrganizationEmployees($organizationID, $searchLdap = false, $onlyActiveUsers = false)
	{
		$query = "SELECT f.organizacao_id, " .
				 "       f.funcionario_id, " .
				 "       l.localidade_id, " .
				 "       l.descricao AS localidade_descricao, " .
				 "       a.area_id, " .
				 "       a.sigla AS area_sigla, " .
				 "       a.descricao AS area_descricao, " .
				 "       c.centro_custo_id, " .
				 "       c.nm_centro_custo, " .
				 "       c.descricao AS centro_custo_descricao " .
				 " FROM funcionario f " .
				 "  INNER JOIN funcionario_status fs " .
				 "  ON (f.funcionario_status_id = fs.funcionario_status_id) " .
				 "  INNER JOIN area a " .
				 "  ON (f.area_id = a.area_id) " .
				 "  INNER JOIN localidade l " .
				 "  ON (f.localidade_id = l.localidade_id) " .
				 "  LEFT OUTER JOIN centro_custo c " .
				 "  ON (COALESCE(f.centro_custo_id, l.centro_custo_id, a.centro_custo_id) = c.centro_custo_id) " .
				 " WHERE " .
				 "	f.organizacao_id = ? ";

		$bindValues = array($organizationID);

		// Se desejar somente retornar usu�rios que est�o ativos
		if($onlyActiveUsers){
			$query .=" AND fs.exibir = 'S' ";
		}

		$query .= " ORDER BY f.funcionario_id";

		$result = $this->db->query($query, $bindValues);
		if (!$result)
			return false;

		$output = $result->GetArray(-1);

		if($searchLdap){
			$output = $this->searchEmployeeDataInLdap($output);
		}

		return $output;
	}

	/**
	 * Busca as �reas de uma organiza��o
	 *
	 * Este m�todo ir� buscar na tabela de �reas, todas as �reas que pertencem � organiza��o solicitada.
	 * @param int $organizationID O ID da organiza��o.
	 * @param int $onlyActiveAreas false= recupera todas as �reas; true= recupera somente as �reas ativas.
	 * @return array Uma array seq�encial contendo as �reas de uma organiza��o. Cada linha do array conter�:
	 * - organizacao_id
	 * - area_id
	 * - area_status_id: corresponde ao n�vel hier�rquico da area
	 * - superior_area_id: o id da �rea acima da atual
	 * - centro_custo_id: o id do centro de custo da area
	 * - titular_funcionario_id: o id do funcionario titular da �rea. Corresponde ao uidNumber do funcion�rio no cat�logo Ldap.
	 * - substituto_funcionario_id: o id do funcionario que est� substituindo o titular temporariamente
	 * - sigla: sigla da area
	 * - descri��o: nome completo da area
	 * - ativa: indicativo de situa��o da area, sendo 's' ativa, e 'n' inativa
	 * - auxiliar_funcionario_id: id da secret�ria da �rea
	 * @access public
	 */
	function getOrganizationAreas($organizationID, $onlyActiveAreas = false)
	{
		$query = "SELECT a.organizacao_id, a.area_id, a.area_status_id, " .
				 "       a.superior_area_id, a.centro_custo_id, a.titular_funcionario_id, " .
				 "       a.sigla, a.descricao, a.ativa, a.auxiliar_funcionario_id, " .
				 "		 s.funcionario_id as substituto_funcionario_id " .
				 " FROM area a " .
				 "  LEFT OUTER JOIN substituicao s " .
				 "  ON ((a.area_id = s.area_id) AND (CURRENT_DATE BETWEEN s.data_inicio AND s.data_fim)) " .
				 " WHERE " .
				 "	(a.organizacao_id = ?) ";
		// Se desejar somente retornar as �reas que est�o ativas
		if($onlyActiveAreas)
			$query .=" AND a.ativa = 'S' ";

		$query .= " ORDER BY a.sigla, a.descricao";

		$result = $this->db->query($query, array($organizationID));
		if (!$result)
			return false;

		$output = $result->GetArray(-1);

		return $output;
	}

	/**
	 * Searches for all the supervisors of an organization.
	 *
	 * This method will search in table areas for all the supervisors and replacement in the organization.
	 * @param int $organizationID the ID of the Organization.
	 * @return array Uma array seq�encial contendo as �reas de uma organiza��o e seus titulares e substitutos. Cada linha do array conter�:
	 * - area_id
	 * - titular_funcionario_id: o id do funcionario titular da �rea. Corresponde ao uidNumber do funcion�rio no cat�logo Ldap.
	 * - substituto_funcionario_id: o id do funcionario que est� substituindo o titular temporariamente
	 * @access public
	 */
	function getOrganizationSupervisors($organizationID) {
		$query = "  SELECT
						a.titular_funcionario_id,
						s.funcionario_id as substituto_funcionario_id,
						a.area_id
					FROM
						area a
						LEFT OUTER JOIN substituicao s
						ON ((a.area_id = s.area_id) AND (CURRENT_DATE BETWEEN s.data_inicio AND s.data_fim))
					WHERE
						a.titular_funcionario_id is not null
						and a.ativa = 'S'
						AND a.organizacao_id = ?
					GROUP BY
						a.titular_funcionario_id,
						s.funcionario_id,
						a.area_id";
		$result = $this->db->query($query, array($organizationID));
		$output = $result->GetArray(-1);
		return $output;
	}

	/**
	 * Busca os status de �rea de uma organiza��o.
	 *
	 * O status de �rea deve ser compreendido como um n�vel hir�rquico das �reas da organiza��o.
	 * Por exemplo: presid�ncia, assessoria, diretoria, ger�ncia, divis�o, etc.
	 * @param int $organizationID O ID da organiza��o.
	 * @return array Uma array de arrays associativas contendo os atributos dos status de �rea. Cada linha do array conter�:
	 * - area_status_id
	 * - organiza��o_id
	 * - descri��o
	 * - n�vel: a posi��o hier�rquica do n�vel no organograma. Por exemplo: 1 - presidencia, 2 - assessoria, etc
	 * @access public
	 */
	function getOrganizationAreaStatus($organizationID)
	{
		$query = "SELECT area_status_id, organizacao_id, descricao, nivel" .
				 "  FROM area_status" .
				 " WHERE (organizacao_id = ?)";

		$result = $this->db->query($query, array($organizationID));
		if (!$result)
			return false;

		$output = $result->GetArray(-1);

		return $output;
	}

	/**
	 * Busca as localidades de uma organiza��o.
	 *
	 * As localidades de uma organiza��o representam o local f�sico de trabalho dos funcion�rios.
	 * @param int $organizationID O ID da organiza��o.
	 * @return array Uma array de arrays associativas contendo os atributos das localidades. Cada linha do array conter�:
	 * - organizacao_id
	 * - localidade_id
	 * - centro_custo_id
	 * - descricao: o nome simplificado localidade
	 * - empresa: o nome completo da localidade
	 * - endere�o: o logradouro da empresa, com o n�mero
	 * - complemento: dado adicional do endere�o
	 * - cep: c�digo de endere�amento postal, m�scara nnnnnn-nnn
	 * - bairro: nome do bairro
	 * - cidade: nome da cidade
	 * - uf: unidade da federa��o
	 * @access public
	 */
	function getOrganizationLocals($organizationID)
	{
		$query = "SELECT organizacao_id, localidade_id, centro_custo_id, descricao, empresa,
							endereco, complemento, cep, bairro, cidade, uf" .
				 "  FROM localidade" .
				 " WHERE (organizacao_id = ?)";

		$result = $this->db->query($query, array($organizationID));
		if (!$result)
			return false;

		$output = $result->GetArray(-1);

		return $output;
	}

	/**
	 * Busca os centros de custo de uma organiza��o.
	 *
	 * Este m�todo retornar� todos os centros de custo de uma organiza��o.
	 * Centros de custo s�o como c�digos cont�beis para faturamento de servi�os.
	 * @param int $organizationID O ID da organiza��o.
	 * @return array Uma array de arrays associativas contendo os atributos dos centros de custo. Cada linha do array conter�:
	 * - organizacao_id
	 * - centro_custo_id
	 * - nm_centro_custo: n�mero do centro de custo
	 * - grupo: estrutura numerica a qual o centro de custo pertence
	 * - descri��o: nome do centro de custo
	 * @access public
	 */
	function getOrganizationCostCenters($organizationID)
	{
		$query = "SELECT organizacao_id, centro_custo_id, nm_centro_custo, grupo, descricao" .
				 "  FROM centro_custo" .
				 " WHERE (organizacao_id = ?)" .
				 " ORDER BY descricao";

		$result = $this->db->query($query, array($organizationID));
		if (!$result)
			return false;

		$output = $result->GetArray(-1);

		return $output;
	}

	/**
	 * Lista todas as categorias poss�veis para um funcion�rio em uma organiza��o.
	 *
	 * Este m�todo listar� a tabela de categorias.
	 * Por exemplo: funcion�rio, estagi�rio, terceirizado, etc.
	 * @param int $organizationID O ID da organiza��o.
	 * @return array Uma array de arrays associativas contendo os atributos das categorias. Cada linha do array conter�:
	 * - funcionario_categoria_id: o id da categoria
	 * - descricao
	 * - organizacao_id
	 * @access public
	 */
	function getOrganizationEmployeeCategories($organizationID)
	{
		$query = "SELECT funcionario_categoria_id, descricao, organizacao_id" .
				 "  FROM funcionario_categoria" .
				 "	WHERE organizacao_id = ?" .
				 "  ORDER BY funcionario_categoria_id";

		$result = $this->db->query($query, array((int) $organizationID));
		if (!$result)
			return false;

		$output = $result->GetArray(-1);

		return $output;
	}

	/**
	 * Lista todas as organiza��es do Organograma.
	 *
	 * Este m�todo ir� listar a tabela de organiza��es
	 * O modelo de dados do organograma foi constru�do para abrigar mais de uma organiza��o
	 * @return um array de arrays associativas contendo a lista de organiza��es. Cada linha do array conter�:
	 * - organizacao_id
	 * - nome: sigla da organizacao
	 * - descricao
	 * - url_imagem: a url onde se encontra o gr�fico da organiza��o
	 * - ativa: se a organiza��o est� ativa ou n�o
	 * - sitio: a url da p�gina web da organiza��o
	 * @access public
	 */
	function getOrganizations()
	{
		$query = "SELECT organizacao_id, nome, descricao, url_imagem, ativa, sitio" .
				 "  FROM organizacao ORDER BY nome";

		$result = $this->db->query($query);
		if (!$result)
			return false;

		$output = $result->GetArray(-1);

		return $output;
	}


	/**
	 * Busca uma �rea pelo seu ID.
	 *
	 * Este m�todo ir� retornar os dados de uma �rea buscando pelo seu ID.
	 * @param int $areaID O ID da �rea.
	 * @return array Uma array associativa contendo os atributos de uma �rea:
	 * - organizacao_id
	 * - area_id
	 * - area_status: o n�vel hier�rquico da �rea
	 * - superior_area_id: o id da �rea acima da atual
	 * - centro_custo_id: o centro de custo da �rea
	 * - titular_funcionario_id: o id do chefe da �rea
	 * - substituto_funcionario_id: o id do funcionario que esta substituindo o titular temporariamente
	 * - sigla
	 * - descicao
	 * - ativa: indicativo de situa��o da �rea: 's' ativa, 'n' inativa
	 * - auxiliar_funcionario_id: o id da secret�ria da �rea
	 * @access public
	 */
	function getArea($areaID)
	{
		$query = "SELECT a.organizacao_id, a.area_id, a.area_status_id, " .
				 "       a.superior_area_id, a.centro_custo_id, a.titular_funcionario_id, " .
				 "       a.sigla, a.descricao, a.ativa, a.auxiliar_funcionario_id, " .
				 "		 s.funcionario_id as substituto_funcionario_id " .
				 " FROM area a " .
				 "  LEFT OUTER JOIN substituicao s " .
				 "  ON ((a.area_id = s.area_id) AND (CURRENT_DATE BETWEEN s.data_inicio AND s.data_fim)) " .
				 " WHERE " .
				 "	(a.area_id = ?) " .
				 " ORDER BY a.sigla, a.descricao";


		$result = $this->db->query($query, array($areaID));
		if (!$result)
			return false;

		$output = $result->fetchRow();

		return $output;
	}

	/**
	 * Busca uma �rea pela sua sigla.
	 *
	 * Este m�todo retornar� os atributos de uma �rea buscando pela sua sigla.
	 * @param string $acronym A sigla da �rea.
	 * @param int $organizationID O id da organiza��o
	 * @return array Uma array associativa contendo os atributos de uma �rea:
	 * - organizacao_id
	 * - area_id
	 * - area_status: o n�vel hier�rquico da �rea
	 * - superior_area_id: o id da �rea acima da atual
	 * - centro_custo_id: o centro de custo da �rea
	 * - titular_funcionario_id: o id do chefe da �rea
	 * - substituto_funcionario_id: o id do funcionario que esta substituindo o titular temporariamente
	 * - sigla
	 * - descicao
	 * - ativa: indicativo de situa��o da �rea: 's' ativa, 'n' inativa
	 * - auxiliar_funcionario_id: o id da secret�ria da �rea
	 * @access public
	 */
	function getAreaByName($acronym, $organizationID = 1)
	{
		$query = "SELECT a.organizacao_id, a.area_id, a.area_status_id, " .
				 "       a.superior_area_id, a.centro_custo_id, a.titular_funcionario_id, " .
				 "       a.sigla, a.descricao, a.ativa, a.auxiliar_funcionario_id, " .
				 "		 s.funcionario_id as substituto_funcionario_id " .
				 " FROM area a " .
				 "  LEFT OUTER JOIN substituicao s " .
				 "  ON ((a.area_id = s.area_id) AND (CURRENT_DATE BETWEEN s.data_inicio AND s.data_fim)) " .
				 " WHERE " .
				 "	(UPPER(a.sigla) = UPPER(?)) " .
				 " AND " .
				 "	(a.organizacao_id = ?) " .
				 " ORDER BY a.sigla, a.descricao";


		$result = $this->db->query($query, array($acronym, (int) $organizationID));
		if (!$result)
			return false;

		$output = $result->fetchRow();

		return $output;
	}

	/**
	 * Busca as �reas que possuem um determinado status de �rea.
	 *
	 * Este m�todo ir� retornar todas as �reas cujo status (n�vel hier�rquico) seja o solicitado.
	 * @param int $areaStatusID O ID do status de �rea.
	 * @return array Uma array de arrays associativas contendo os atributos de uma �rea. Cada linha do array conter�:
	 * - organizacao_id
	 * - area_id
	 * - area_status: o n�vel hier�rquico da �rea
	 * - superior_area_id: o id da �rea acima da atual
	 * - centro_custo_id: o centro de custo da �rea
	 * - titular_funcionario_id: o id do chefe da �rea
	 * - substituto_funcionario_id: o id do funcionario que esta substituindo o titular temporariamente
	 * - sigla
	 * - descicao
	 * - ativa: indicativo de situa��o da �rea: 's' ativa, 'n' inativa
	 * - auxiliar_funcionario_id: o id da secret�ria da �rea
	 * @access public
	 */
	function getAreaByStatus($areaStatusID)
	{
		$result = array();

		$query = "SELECT a.organizacao_id, a.area_id, a.area_status_id, " .
				 "       a.superior_area_id, a.centro_custo_id, a.titular_funcionario_id, " .
				 "       a.sigla, a.descricao, a.ativa, a.auxiliar_funcionario_id, " .
				 "		 s.funcionario_id as substituto_funcionario_id " .
				 " FROM area a " .
				 "  LEFT OUTER JOIN substituicao s " .
				 "  ON ((a.area_id = s.area_id) AND (CURRENT_DATE BETWEEN s.data_inicio AND s.data_fim)) " .
				 " WHERE " .
				 "	(a.area_status_id = ?) " .
				 " ORDER BY a.sigla, a.descricao";



		$result = $this->db->query($query, array($areaStatusID));
		if (!$result)
			return false;

		$output = $result->GetArray(-1);

		return $output;
	}

	/**
	 * Busca v�rias �reas atrav�s de uma array de IDs
	 *
	 * Este m�todo ir� buscar de uma vez s� os dados de mais de uma �rea.
	 * @param array $areaIDs Array com os IDs das �reas
	 * @return array Um array de arrays associativos contendo os atributos de v�rias �reas. Cada linha do array conter�:
	 * - organizacao_id
	 * - area_id
	 * - area_status: o n�vel hier�rquico da �rea
	 * - superior_area_id: o id da �rea acima da atual
	 * - centro_custo_id: o centro de custo da �rea
	 * - titular_funcionario_id: o id do chefe da �rea
	 * - substituto_funcionario_id: o id do funcionario que esta substituindo o titular temporariamente
	 * - sigla
	 * - descicao
	 * - ativa: indicativo de situa��o da �rea: 's' ativa, 'n' inativa
	 * - auxiliar_funcionario_id: o id da secret�ria da �rea
 	 * @access public
	 */
	function getAreas($areaIDs)
	{
		if (!is_array($areaIDs))
			return false;

		$areas = implode(', ', $areaIDs);

		$query = "SELECT a.organizacao_id, a.area_id, a.area_status_id, " .
				 "       a.superior_area_id, a.centro_custo_id, a.titular_funcionario_id, " .
				 "       a.sigla, a.descricao, a.ativa, a.auxiliar_funcionario_id, " .
				 "		 s.funcionario_id as substituto_funcionario_id " .
				 " FROM area a " .
				 "  LEFT OUTER JOIN substituicao s " .
				 "  ON ((a.area_id = s.area_id) AND (CURRENT_DATE BETWEEN s.data_inicio AND s.data_fim)) " .
				 " WHERE " .
				 "	(a.area_id IN ($areas)) " .
				 " ORDER BY a.sigla, a.descricao";

		// A execu��o � realizada sem o segundo par�metro pois este n�o pode estar entre aspas
		$result = $this->db->query($query);
		if (!$result)
			return false;

		$output = $result->GetArray(-1);
		return $output;
	}

	/**
	 * Return all areas that the employee is a supervisor.
	 *
	 * Search in the organization for all areas that the employee is a supervisor.
	 * @param int $employeeID The ID of employee
	 * @return array Array containing all the areas that the employeee is a supervisor.
	 * @access public
	 */
	function getSupervisorAreas($employeeID) {

		if (!$employeeID) {
			return false;
		}

		$query = "SELECT
						a.area_id
					FROM
						area a
						LEFT OUTER JOIN substituicao s ON ((a.area_id = s.area_id)
						AND (CURRENT_DATE BETWEEN s.data_inicio AND s.data_fim))
					WHERE
						a.titular_funcionario_id = ? OR
						s.funcionario_id = ?
					GROUP BY
						a.area_id";

		$result = $this->db->query($query, array($employeeID,$employeeID));
		if (!$result)
			return false;

		$output = $result->GetArray(-1);
		return $output;
	}

	/**
	 * Busca o ID da �rea superior a uma dada �rea.
	 *
	 * Este m�todo ir� buscar a �rea imediatamente superior � solicitada, ou ent�o subir� na hierarquia at� chegar no n�vel solicitado.
	 * @param int $areaID O ID da �rea da qual se quer saber a �rea superior.
	 * @param int $areaStatusID O ID do status de �rea (n�vel) da �rea pai. Utilizar -1 caso se queira a �rea imediatamente superior.
	 * - Por exemplo: �rea atual est� no n�vel 5 (Divis�o) e se quer buscar a �rea de nivel 3 (Diretoria).
	 * @return int O ID da �rea que � superior � �rea informada.
	 * @access public
	 */
	function getParentAreaID($areaID, $areaStatusID = -1)
	{
		$query  = "SELECT area_id, area_status_id" .
				  "  FROM area" .
				  " WHERE area_id = (SELECT superior_area_id" .
				  "                    FROM area" .
				  "                   WHERE (area_id = ?))";

		$result = $this->db->query($query, array($areaID));
		if (!$result)
			return false;

		$output = $result->fetchRow();
		if (!$output)
			return false;

		if (($areaStatusID == -1) || ($output['area_status_id'] == $areaStatusID))
			return $output['area_id'];
		else
			return $this->getParentAreaID($output['area_id'], $areaStatusID);
	}

	/**
	 * Busca as �reas abaixo de uma determinada �rea.
	 *
	 * Este m�todo ir� buscar as �reas imediatamente inferiores � solicitada, n�o descendo nos pr�ximos n�veis da hierarquia.
	 * @param int $parentAreaID O ID da �rea da qual se quer saber as �reas imediatamente inferiores.
	 * @param boolean $onlyActiveAreas Valor l�gico que, caso verdadeiro, faz com que o m�todo retorne somente as �reas ativas
	 * @return array Um array de arrays associativos contendo os atributos de v�rias �reas. Cada linha do array conter�:
	 * - organizacao_id
	 * - area_id
	 * - area_status: o n�vel hier�rquico da �rea
	 * - superior_area_id: o id da �rea acima da atual
	 * - centro_custo_id: o centro de custo da �rea
	 * - titular_funcionario_id: o id do chefe da �rea
	 * - substituto_funcionario_id: o id do funcionario que esta substituindo o titular temporariamente
	 * - sigla
	 * - descicao
	 * - ativa: indicativo de situa��o da �rea: 's' ativa, 'n' inativa
	 * - auxiliar_funcionario_id: o id da secret�ria da �rea
	 * @access public
	 */
	function getSubAreasByParentAreaID($parentAreaID, $onlyActiveAreas = false)
	{
		$query = "SELECT a.organizacao_id, a.area_id, a.area_status_id, " .
				 "       a.superior_area_id, a.centro_custo_id, a.titular_funcionario_id, " .
				 "       a.sigla, a.descricao, a.ativa, a.auxiliar_funcionario_id, " .
				 "		 s.funcionario_id as substituto_funcionario_id " .
				 " FROM area a " .
				 "  LEFT OUTER JOIN substituicao s " .
				 "  ON ((a.area_id = s.area_id) AND (CURRENT_DATE BETWEEN s.data_inicio AND s.data_fim)) " .
				 " WHERE " .
				 "	(a.superior_area_id = ?) ";


		if ($onlyActiveAreas){
			$query .= " AND ativa = 'S'";
		}
		$query .= " ORDER BY a.sigla, a.descricao";

		$result = $this->db->query($query, array($parentAreaID));
		if (!$result)
			return false;

		$output = $result->GetArray(-1);
		return $output;
	}

	/**
	 * Busca um status de �rea pelo seu ID.
	 *
	 * Procura na tabela de status de �rea (n�vel hier�rquico de �reas) o registro que corresponde ao ID solicitado.
	 * @param int $areaStatusID O ID do status de �rea.
	 * @return array Uma array associativa contendo os atributos de um status de �rea:
	 * - area_status_id
	 * - organizacao_id
	 * - descricao: nome do n�vel hier�rquivo. Por exemplo: presid�ncia, assessoria, diretoria, ger�ncia, etc.
	 * - nivel: valor num�rico que identifica o n�vel: 1, 2, 3, ...
	 * @access public
	 */
	function getAreaStatus($areaStatusID)
	{
		$query = "SELECT area_status_id, organizacao_id, descricao, nivel" .
				 "  FROM area_status" .
				 " WHERE (area_status_id = ?)";

		$result = $this->db->query($query, array($areaStatusID));
		if (!$result)
			return false;

		$output = $result->fetchRow();

		return $output;
	}

	/**
	 * Busca um status de �rea pelo seu nome.
	 *
	 * Este m�todo ir� retornar os dados de um status, procurando o registro na tabela atrav�s do seu nome.
	 * @param string $description O nome do status de �rea.
	 * @param int $organizationID O id da organiza��o.
	 * @return array Uma array associativa contendo os atributos de um status de �rea:
	 * - area_status_id
	 * - organizacao_id
	 * - descricao: nome do n�vel hier�rquivo. Por exemplo: presid�ncia, assessoria, diretoria, ger�ncia, etc.
	 * - nivel: valor num�rico que identifica o n�vel: 1, 2, 3, ...
	 * @access public
	 */
	function getAreaStatusByName($description, $organizationID = 1)
	{
		$query = "SELECT area_status_id, organizacao_id, descricao, nivel" .
				 "  FROM area_status" .
				 " WHERE (UPPER(descricao) = UPPER(?) and (organizacao_id = ?))";

		$result = $this->db->query($query, array($description, (int) $organizationID));
		if (!$result)
			return false;

		$output = $result->fetchRow();

		return $output;
	}

	/**
	 * Busca um status de �rea pelo seu n�vel.
	 *
	 * Este m�todo ir� retornar os dados de um status, procurando o registro na tabela atrav�s do seu n�vel.
	 * @param int $level O n�vel do status de �rea.
	 * @param int $organizationID O id da organiza��o.
	 * @return array Uma array associativa contendo os atributos de um status de �rea:
	 * - area_status_id
	 * - organizacao_id
	 * - descricao: nome do n�vel hier�rquivo. Por exemplo: presid�ncia, assessoria, diretoria, ger�ncia, etc.
	 * - nivel: valor num�rico que identifica o n�vel: 1, 2, 3, ...
	 * @access public
	 */
	function getAreaStatusByLevel($level, $organizationID = 1)
	{
		$query = "SELECT area_status_id, organizacao_id, descricao, nivel" .
				 " FROM area_status" .
				 " WHERE (nivel = ?) AND (organizacao_id = ?)";

		$result = $this->db->query($query, array((int) $level, (int) $organizationID));
		if (!$result)
			return false;

		$output = $result->fetchRow();

		return $output;
	}

	/**
	 * Busca o ID do titular de uma �rea.
	 *
	 * Este m�todo busca uma �rea e retorna o atributo titular_funcionario_id
	 * @param int $areaID O ID da �rea.
	 * @return int O ID do titular da �rea.
	 * @access public
	 */
	function getAreaSupervisorID($areaID)
	{
		$area = $this->getArea($areaID);
		if (!$area)
			return false;

		return $area['titular_funcionario_id'];
	}

	/**
	 * Busca o ID do substituto de uma �rea.
	 *
	 * Este m�todo ir� buscar uma �rea e retornar o atributo substituto_funcionario_id.
	 * Note que o substituro � um campo opcional na �rea e poder� retornar vazio.
	 * @param int $areaID O ID da �rea.
	 * @return int O ID do substituto da �rea.
	 * @access public
	 */
	function getAreaBackupSupervisorID($areaID)
	{
		$area = $this->getArea($areaID);
		if (!$area)
			return false;

		return $area['substituto_funcionario_id'];
	}

	/**
	 * Busca o ID do auxiliar administrativo de uma �rea.
	 *
	 * Este m�todo busca uma �rea e retorna o atributo auxiliar_funcionario_id
	 * Nem todas as �reas possuem funcion�rios auxiliares (secret�rias)
	 * @param int $areaID O ID da �rea.
	 * @return int O ID do auxiliar administrativo da �rea.
	 * @access public
	 */
	function getAreaAssistantID($areaID)
	{
		$area = $this->getArea($areaID);
		if (!$area)
			return false;

		return $area['auxiliar_funcionario_id'];
	}

	/**
	 * Busca o ID do titular atual de uma �rea.
	 *
	 * Este m�todo ir� buscar uma �rea e caso haja um substituto, este ser� o titular atual; caso contr�rio, o titular atual � o pr�prio titular da �rea.
	 * @param int $areaID O ID da �rea.
	 * @return int O ID do titular atual da �rea, podendo ser um dos campos abaixo:
	 * - substituto_funcionario_id
	 * - titular_funcionario_id
	 * @access public
	 */
	function getAreaCurrentSupervisorID($areaID)
	{
		$area = $this->getArea($areaID);
		if (!$area)
			return false;

		return is_null($area['substituto_funcionario_id']) ? $area['titular_funcionario_id'] : $area['substituto_funcionario_id'];
	}

	/**
	 * Busca o ID do respons�vel administrativo atual de uma �rea.
	 *
	 * A preced�ncia para definir quem � o respons�vel administrativo �: auxiliar administrativo, substituto e titular.
	 * @param int $areaID O ID da �rea.
	 * @return int O ID do respons�vel administrativo da �rea, podendo ser um dos atributos abaixo:
	 * - auxiliar_funcionario_id
	 * - substituto_funcionario_id
	 * - titular_funcionario_id
	 * @access public
	 */
	function getAreaCurrentAdministrativeResponsibleID($areaID)
	{
		$area = $this->getArea($areaID);
		if (!$area)
			return false;

		return !is_null($area['auxiliar_funcionario_id']) ? $area['auxiliar_funcionario_id'] : (!is_null($area['substituto_funcionario_id']) ? $area['substituto_funcionario_id'] : $area['titular_funcionario_id']);
	}

	/**
	 * Busca uma localidade pelo seu ID.
	 *
	 * Este m�todo listar� os atributos e uma localidade. Lembrando que localidade tamb�m pode ser entendida como o local f�sico de trabalho.
	 * @param int $localID O ID da localidade.
	 * @return array Uma array associativa contendo os atributos de uma localidade:
	 * - organizacao_id
	 * - localidade_id
	 * - centro_custo_id
	 * - descricao
	 * - empresa: o nome completo da localidade
	 * - endere�o: o logradouro da empresa, com o n�mero
	 * - complemento: dado adicional do endere�o
	 * - cep: c�digo de endere�amento postal, m�scara nnnnnn-nnn
	 * - bairro: nome do bairro
	 * - cidade: nome da cidade
	 * - uf: unidade da federa��o
	 * @access public
	 */
	function getLocal($localID)
	{
		$query = "SELECT organizacao_id, localidade_id, centro_custo_id, descricao,
							empresa, endereco, complemento, cep, bairro, cidade, uf" .
				 "  FROM localidade" .
				 " WHERE (localidade_id = ?)";

		$result = $this->db->query($query, array($localID));
		if (!$result)
			return false;

		$output = $result->fetchRow();

		return $output;
	}

	/**
	 * Busca uma localidade pelo seu nome.
	 *
	 *
	 * @param string $description O nome da localidade.
	 * @param int $organizationID O id da organiza��o.
	 * @return array Uma array associativa contendo os atributos de uma localidade:
	 * - organizacao_id
	 * - localidade_id
	 * - centro_custo_id
	 * - descricao
	 * - empresa: o nome completo da localidade
	 * - endere�o: o logradouro da empresa, com o n�mero
	 * - complemento: dado adicional do endere�o
	 * - cep: c�digo de endere�amento postal, m�scara nnnnnn-nnn
	 * - bairro: nome do bairro
	 * - cidade: nome da cidade
	 * - uf: unidade da federa��o
	 * @access public
	 */
	function getLocalByName($description, $organizationID = 1)
	{
		$query = "SELECT organizacao_id, localidade_id, centro_custo_id, descricao,
							empresa, endereco, complemento, cep, bairro, cidade, uf" .
				 "  FROM localidade" .
				 " WHERE (UPPER(descricao) = UPPER(?) and (organizacao_id = ?))";

		$result = $this->db->query($query, array($description, (int) $organizationID));
		if (!$result)
			return false;

		$output = $result->fetchRow();

		return $output;
	}

	/**
	 * Busca um centro de custo pelo seu ID.
	 *
	 * Este m�todo ir� mostrar os atributos de um centro de custo, buscando na tabela pelo seu id.
	 * @param int $costCenterID O ID do centro de custo.
	 * @return array Uma array associativa contendo os atributos de um centro de custo:
	 * - organizacao_id
	 * - centro_custo_id
	 * - nm_centro_custo: n�mero do centro de custo
	 * - grupo: estrutura numerica a qual o centro de custo pertence
	 * - descri��o: nome do centro de custo
	 * @access public
	 */
	function getCostCenter($costCenterID)
	{
		$query = "SELECT organizacao_id, centro_custo_id, nm_centro_custo, grupo, descricao" .
				 "  FROM centro_custo" .
				 " WHERE (centro_custo_id  = ?)";

		$result = $this->db->query($query, array($costCenterID));
		if (!$result)
			return false;

		$output = $result->fetchRow();

		return $output;
	}

	/**
	 * Busca um centro de custo pelo seu n�mero.
	 *
	 * Este m�todo retornar� os atributos de um centro de custo buscando pelo seu ID.
	 * @param int $number O n�mero do centro de custo.
	 * @param ind $organizationID O id da organiza��o
	 * @return array Uma array associativa contendo os atributos de um centro de custo:
	 * - organizacao_id
	 * - centro_custo_id
	 * - nm_centro_custo: n�mero do centro de custo
	 * - grupo: estrutura numerica a qual o centro de custo pertence
	 * - descri��o: nome do centro de custo
	 * @access public
	 */
	function getCostCenterByNumber($number, $organizationID = 1)
	{
		$query = "SELECT organizacao_id, centro_custo_id, nm_centro_custo, grupo, descricao" .
				 "  FROM centro_custo" .
				 " WHERE ((nm_centro_custo = ?) and (organizacao_id = ?))";

		$result = $this->db->query($query, array($number, (int) $organizationID));
		if (!$result)
			return false;

		$output = $result->fetchRow();

		return $output;
	}

	/**
	 * Busca um centro de custo pelo seu nome.
	 *
	 * Este m�todo ir� retornar os atributos de um centro de custo buscando pelo seu nome.
	 * @param string $description O nome do centro de custo.
	 * @param int $organizationID O id da organiza��o
	 * @return array Uma array associativa contendo os atributos de um centro de custo.
	 * - organizacao_id
	 * - centro_custo_id
	 * - nm_centro_custo: n�mero do centro de custo
	 * - grupo: estrutura numerica a qual o centro de custo pertence
	 * - descri��o: nome do centro de custo
	 * 	 * @access public
	 */
	function getCostCenterByName($description, $organizationID = 1)
	{
		$query = "SELECT organizacao_id, centro_custo_id, nm_centro_custo, grupo, descricao" .
				 "  FROM centro_custo" .
				 " WHERE (UPPER(descricao) = UPPER(?) and (organizacao_id = ?))";

		$result = $this->db->query($query, array($description, (int) $organizationID));
		if (!$result)
			return false;

		$output = $result->fetchRow();

		return $output;
	}

	/**
	 * Busca as informa��es de categoria a partir do ID.
	 *
	 * Este m�todo ir� retornar os atributos de uma linha da tabela de categorias de funcion�rios.
	 * @param int $categoryID O ID da categoria.
	 * @return array Uma array associativa contendo os atributos da categoria:
	 * - funcionario_categoria_id
	 * - descricao
	 * - organizacao_id
	 * @access public
	 */
	function getEmployeeCategory($categoryID)
	{
		$query  = "SELECT funcionario_categoria_id, descricao, organizacao_id" .
				  "  FROM funcionario_categoria" .
				  " WHERE (funcionario_categoria_id = ?)";

		$result = $this->db->query($query, array($categoryID));
		if (!$result)
			return false;

		$output = $result->fetchRow();

		return $output;
	}

	/**
	 * Busca uma categoria a partir da sua descri��o.
	 *
	 * Este m�todo ir� mostrar os atributos de uma categoria, buscando pela sua descri��o.
	 * @param string $description O nome da categoria.
	 * @param int $organizationID O id da organiza��o
	 * @return array Uma array associativa contendo os atributos de uma categoria:
	 * - funcionario_categoria_id
	 * - descricao
	 * - organizacao_id
	 * @access public
	 */
	function getEmployeeCategoryByName($description, $organizationID = 1)
	{
		$query = "SELECT funcionario_categoria_id, descricao, organizacao_id" .
				 "  FROM funcionario_categoria" .
				 " WHERE (UPPER(descricao) = UPPER(?) and (organizacao_id = ?))";

		$result = $this->db->query($query, array($description, (int) $organizationID));
		if (!$result)
			return false;

		$output = $result->fetchRow();

		return $output;
	}

	/**
	 * Lista todos os cargos de uma organiza��o.
	 *
	 * Este m�todo ir� listar a tabela localidade.
	 * @param int $organizationID O ID da organiza��o.
	 * @return array Uma array de arrays associativas contendo a lista dos cargos de uma organiza��o:
	 * - cargo_id
	 * - descricao
	 * - organizacao_id
	 * @access public
	 */
	function getJobTitles($organizationID)
	{
		$query = "SELECT cargo_id, descricao, organizacao_id" .
				 "  FROM cargo" .
				 "	WHERE organizacao_id = ?" .
				 "  ORDER BY cargo_id";

		$result = $this->db->query($query, array((int) $organizationID));
		if (!$result)
			return false;

		$output = $result->GetArray(-1);

		return $output;
	}

	/**
	 * Busca as informa��es de cargo a partir do ID.
	 *
	 * Este m�todo ir� listar os atributos de um cargo, buscando na tabela a partir do seu ID.
	 * @param int $jobTitleID O ID do cargo.
	 * @return array Uma array associativa contendo os atributos do cargo.
	 * - cargo_id
	 * - descricao
	 * - organizacao_id
	 * @access public
	 */
	function getJobTitle($jobTitleID)
	{
		$query  = "SELECT cargo_id, descricao, organizacao_id" .
				  "  FROM cargo" .
				  " WHERE (cargo_id = ?)";

		$result = $this->db->query($query, array($jobTitleID));
		if (!$result)
			return false;

		$output = $result->fetchRow();

		return $output;
	}

	/**
	 * Busca um cargo a partir da sua descri��o.
	 *
	 * Este m�todo ir� mostrar os atributos de um cargo, buscando pela sua descri��o.
	 * @param string $description O nome do cargo.
	 * @param int $organizationID O id da organiza��o.
	 * @return array Uma array associativa contendo os atributos de um cargo:
	 * - cargo_id
	 * - descricao
	 * - organizacao_id
	 * @access public
	 */
	function getJobTitleByName($description, $organizationID = 1)
	{
		$query = "SELECT cargo_id, descricao, organizacao_id" .
				 "  FROM cargo" .
				 " WHERE (UPPER(descricao) = UPPER(?) and (organizacao_id = ?))";

		$result = $this->db->query($query, array($description, (int) $organizationID));
		if (!$result)
			return false;

		$output = $result->fetchRow();

		return $output;
	}

	/**
	 * Busca um funcion�rio pelo seu ID.
	 *
	 * Este m�todo retorna os atributos de um funcion�rio buscando pelo se id.
	 * O ID corresponde ao uidNumber do funcion�rio no cat�logo LDAP.
	 * Se necessitar de outros atributos como o nome, cpf, email, matr�cula, � necess�rio fazer uma consulta ao Ldap.
	 * @param int $employeeID O ID do funcion�rio.
	 * @return array Uma array associativa contendo os atributos de um funcion�rio:
	 * - funcionario_id
	 * - area_id
	 * - localidade_id
	 * - centro_custo_id
	 * - organizacao_id
	 * - funcionario_status_id
	 * - cargo_id
	 * - nivel: o n�vel num�rico dentro do cargo
	 * - funcionario_categoria_id
	 * - titulo: nome pelo qual o funcion�rio e reconhecido na organiza��o, por exemplo: gerente comercial
	 * @access public
	 */
	function getEmployee($employeeID)
	{
		if (!is_numeric($employeeID))
			return false;

		$query  = "SELECT funcionario_id, area_id, localidade_id, centro_custo_id, organizacao_id,
							funcionario_status_id, cargo_id, nivel, funcionario_categoria_id, titulo" .
				  "  FROM funcionario" .
				  " WHERE (funcionario_id = ?)";

		$result = $this->db->query($query, array($employeeID));
		if (!$result)
			return false;

		$output = $result->fetchRow();

		return $output;
	}

	/**
	 * Busca lista de funcion�rios pela sua localidade.
	 *
	 * Este m�todo busca todos os funcion�rios de uma localidade, ou, caso a localidade n�o seja passada, retorna todos os funcion�rios.
	 * @param int $localID O ID da localidade.
	 * @param int $organizationID O ID da organiza��o.
	 * @param boolean $searchLdap True, caso seja necess�rio buscar no LDAP os dados dos usu�rios. Ou false, caso contr�rio.
	 * @param string $external Null, caso deseje-se recuperar localidades externas e internas � organiza��o. 'S', para recuperar apenas as externas e 'N', para apenas as internas.
	 * @return array Uma array contendo os dados dos us�rios e sua localidade
	 * @access public
	 */
	function getEmployeesByLocalID($localID = 0, $organizationID = 1, $searchLdap = false, $external = null)
	{
		if(!is_numeric($localID) || !is_numeric($organizationID))
			return false;

		$query = "SELECT DISTINCT " .
				 " l.organizacao_id, " .
				 " l.localidade_id, " .
				 " l.descricao AS localidade_descricao, " .
				 " f.funcionario_id, " .
				 " f.area_id, " .
				 " a.sigla AS area_sigla " .
				 "FROM funcionario f " .
				 "INNER JOIN localidade l " .
				 "ON (f.localidade_id = l.localidade_id) " .
				 "INNER JOIN area a " .
				 "ON (f.area_id = a.area_id) " .
				 "INNER JOIN funcionario_status fs " .
				 "ON (f.funcionario_status_id = fs.funcionario_status_id) " .
				 "WHERE (l.organizacao_id = ?) AND (fs.exibir = 'S') ";

		$param[] = $organizationID;
		if(!empty($localID)){
			$query  .= " AND l.localidade_id = ? ";
			$param[] = $localID;
		}

		if(!empty($external) && ($external == 'S' || $external == 'N')){
			$query  .= " AND l.externa = ? ";
			$param[] = $external;
		}

		$query .= "ORDER BY l.descricao, a.sigla ";

		$result = $this->db->query($query, $param);
		if (!$result)
			return false;

		$output = $result->GetArray(-1);

		if($searchLdap){
			$output = $this->searchEmployeeDataInLdap($output);
		}

		return $output;
	}

	/**
	 * Percorre o array passado por par�metro, buscando os dados dos funcion�rios no Ldap. Adiciona estes dados no array e o retorna.
	 *
	 * @param array $output Array contendo os usu�rios que dever�o ser consultados no Ldap
	 * @return array Retorna o mesmo array passado por par�metro, adicionados os dados dos usu�rios buscados no Ldap
	 */
	function searchEmployeeDataInLdap($output)
	{
		if(is_array($output)){
			foreach($output AS $k => $value){
				if(is_numeric($value['funcionario_id'])){
					$user_data = $this->ldap->getEntryByID($value['funcionario_id']);
					$output[$k]['nome']     = $user_data['cn'];
					$output[$k]['email']    = $user_data['mail'];
					$output[$k]['telefone'] = $user_data['telephonenumber'];
					$output[$k]['uid']      = $user_data['uid'];
				}
			}
		}
		return $output;
	}

	/**
	 * Busca lista de funcion�rios de uma �rea pelo ID da �rea.
	 *
	 * Este m�todo retornar� todos os funcion�rios de uma �rea.
	 * @param int $areaID O ID da �rea.
	 * @param boolean $onlyActiveUsers true para retornar somente usu�rios ativos e false caso contr�rio
	 * @return array Uma array sequencial de arrays associativos contendo contendo o ID dos funcion�rios de uma �rea:
	 * - funcionario_id
	 * @access public
	 */
	function getEmployeesAreaID($areaID, $onlyActiveUsers = false)
	{
		if($onlyActiveUsers){
			$query = "SELECT funcionario_id " .
					 "FROM funcionario " .
					 "INNER JOIN funcionario_status " .
					 "ON (funcionario.funcionario_status_id = funcionario_status.funcionario_status_id) " .
					 "WHERE (area_id = ?) AND (funcionario_status.exibir = 'S')";
		} else {
			$query = "SELECT funcionario_id " .
				     "FROM funcionario " .
					 "WHERE (area_id = ?)";
		}

		$result = $this->db->query($query, array($areaID));
		if (!$result)
			return false;

		$output = $result->GetArray(-1);

		return $output;
	}

	/**
	 * Busca um status de funcion�rio pelo seu ID.
	 *
	 * Este m�todo ir� retornar os dados de um status de funcion�rio, buscando pelo ID solicitado.
	 * @param int $employeeStatusID O ID do status.
	 * @return array Uma array associativa contendo os atributos de um status de funcion�rio:
	 * - funcionario_status_id
	 * - descricao
	 * - exibir: indicativo booleado 'S' funcion�rio ser� listado no organograma, 'N' funcion�rio ficar� oculto na aba organograma.
	 * @access public
	 */
	function getEmployeeStatus($employeeStatusID)
	{
		$query = "SELECT funcionario_status_id, descricao, exibir " .
				 "  FROM funcionario_status" .
				 " WHERE (funcionario_status_id = ?)";

		$result = $this->db->query($query, array($employeeStatusID));
		if (!$result)
			return false;

		$output = $result->fetchRow();

		return $output;
	}

	/**
	 * Busca o ID da �rea (podendo ser de outros n�veis) de um funcion�rio.
	 *
	 * Este m�todo ir� retornar o ID da �rea do funcion�rio. Far� uma busca para cima na hierarquia se for solicitado.
	 * @param int $employeeID O ID do funcion�rio do qual se quer saber a �rea.
	 * @param int $areaStatusID O ID do status de �rea (n�vel) da �rea do funcion�rio que se quer. Utilizar -1 caso se queira a �rea imediata do funcion�rio.
	 * - Por exemplo: o funcion�rio est� em uma �rea de status 5 (Divis�o) e se quer a �rea superior de n�vel 3 (Diretoria).
	 * @return int O ID da �rea do funcion�rio.
	 * @access public
	 */
	function getEmployeeAreaID($employeeID, $areaStatusID = -1)
	{
		$employee = $this->getEmployee($employeeID);
		if (!$employee)
			return false;

		/* requer a �rea do funcion�rio */
		if ($areaStatusID == -1)
			return $employee['area_id'];
		else
		{
			/* verifica se a �rea do funcion�rio j� est� no n�vel solicitado */
			$currentArea = $this->getArea($employee['area_id']);
			if ($currentArea['area_status_id'] == $areaStatusID)
				return $currentArea['area_id'];
			else
				return $this->getParentAreaID($currentArea['area_id'], $areaStatusID);
		}
	}

	/**
	 * Busca o ID da localidade de um funcion�rio.
	 *
	 * Este m�todo buscar� o funcion�rio pelo seu ID e retornar� o ID da sua localidade.
	 * @param int $employeeID O ID do funcion�rio.
	 * @return int O ID da localidade do funcion�rio.
	 * @access public
	 */
	function getEmployeeLocalID($employeeID)
	{
		$employee = $this->getEmployee($employeeID);
		if (!$employee)
			return false;

		return $employee['localidade_id'];
	}

	/**
	 * Busca o ID do status de um funcion�rio.
	 *
	 * Este m�todo buscar� um funcion�rio pelo seu ID e retornar� o ID de status do funcion�rio.
	 * Status de funcion�rio corresponde � sua situa��o: ativo, desligado, etc.
	 * @param int $employeeID O ID do funcion�rio.
	 * @return int O ID do status do funcion�rio.
	 * @access public
	 */
	function getEmployeeStatusID($employeeID)
	{
		$employee = $this->getEmployee($employeeID);
		if (!$employee)
			return false;

		return $employee['funcionario_status_id'];
	}

	/**
	 * Busca o ID do centro de custo de um funcion�rio.
	 *
	 * Este m�todo ir� buscar um funcion�rio pelo seu ID e retornar� o ID do centro de custo do funcion�rio.
	 * O centro de custo n�o � obrigat�rio por funcion�rio. Neste caso, se necess�rio, busque o centro de custo da �rea ou da localidade.
	 * @param int $employeeID O ID do funcion�rio.
	 * @return int O ID do centro de custo do funcion�rio.
	 * @access public
	 */
	function getEmployeeCostCenterID($employeeID)
	{
		$employee = $this->getEmployee($employeeID);
		if (!$employee)
			return false;

		$costCenterID = $employee['centro_custo_id'];
		if(!empty($costCenterID)){
			return $costCenterID;
		} else {
			$employeeArea = $this->getArea($employee['area_id']);
			return $employeeArea['centro_custo_id'];
		}
	}

	/**
	 * Busca o ID do cargo de um funcion�rio.
	 *
	 * Este m�todo buscar� um funcion�rio pelo seu ID e retornar� o ID do cargo do funcion�rio.
	 * @param int $employeeID O ID do funcion�rio.
	 * @return int O ID do cargo do funcion�rio.
	 * @access public
	 */
	function getEmployeeJobTitleID($employeeID)
	{
		$employee = $this->getEmployee($employeeID);
		if (!$employee)
			return false;

		return $employee['cargo_id'];
	}

	/**
	 * Busca o ID da categoria de um funcion�rio.
	 *
	 * Este m�todo buscar� um funcion�rio pelo seu ID e retornar� o ID da categoria do funcion�rio.
	 * A categoria corresponde ao tipo de v�nculo do funcion�rio com a organizacao. Por exemplo:
	 * - funcion�rio
	 * - estagi�rio
	 * - terceirizado, etc
	 * @param int $employeeID O ID do funcion�rio.
	 * @return int O ID do categoria do funcion�rio.
	 * @access public
	 */
	function getEmployeeCategoryID($employeeID)
	{
		$employee = $this->getEmployee($employeeID);
		if (!$employee)
			return false;

		return $employee['funcionario_categoria_id'];
	}

	/**
	 * Busca o n�vel de um funcion�rio.
	 *
	 * Este m�todo buscar� o funcion�rio pelo seu ID e retornar� o n�vel do funcion�rio dentro do cargo.
	 * Geralmente um cargo (por exemplo: auxiliar tecnico) � composto por n�veis de evolu��o na carreira: 1,2,3,...
	 * @param int $employeeID O ID do funcion�rio.
	 * @return int A quantidade de n�veis do funcion�rio.
	 * @access public
	 */
	function getEmployeeLevel($employeeID)
	{
		$employee = $this->getEmployee($employeeID);
		if (!$employee)
			return false;

		if ($employee['nivel'] === null)
			return false;

		return $employee['nivel'];
	}

	/**
	 * Busca o ID do titular da �rea de um funcion�rio.
	 *
	 * Este m�todo buscar� o titular da �rea do funcion�rio, e poder� subir na hierarquia, buscando o titular de �reas superiores � �rea do funcion�rio.
	 * @param int $employeeID O ID do funcion�rio.
	 * @param int $areaStatusID O ID do status de �rea (n�vel) da �rea do funcion�rio que se quer o titular. Utilizar -1 caso se queira o titular da �rea imediata do funcion�rio.
	 * @return int O ID do titular da �rea do funcion�rio:
	 * - titular_funcionario_id
	 * @access public
	 */
	function getEmployeeSupervisorID($employeeID, $areaStatusID = -1)
	{
		return $this->getAreaSupervisorID($this->getEmployeeAreaID($employeeID, $areaStatusID));
	}

	/**
	 * Busca o ID do titular atual da �rea do funcion�rio.
	 *
	 * Caso haja um substituto, este ser� o titular atual; caso contr�rio, o titular atual � o pr�prio titular da �rea.
	 * @param int $employeeID O ID do funcion�rio.
	 * @param int $areaStatusID O ID do status de �rea (n�vel) da �rea do funcion�rio que se quer o titular atual. Utilizar -1 caso se queira o titular atual da �rea imediata do funcion�rio.
	 * @return int O ID do titular atual da �rea do funcion�rio:
	 * - titular_funcion�rio_id ou
	 * - substituto_funcionario_id
	 * @access public
	 */
	function getEmployeeCurrentSupervisorID($employeeID, $areaStatusID = -1)
	{
		return $this->getAreaCurrentSupervisorID($this->getEmployeeAreaID($employeeID, $areaStatusID));
	}

	/**
	 * Busca o ID do respons�vel administrativo atual de uma �rea.
	 *
	 * A preced�ncia para definir quem � o respons�vel administrativo �: auxiliar administrativo, substituto e titular.
	 * @param int $employeeID O ID do funcion�rio.
	 * @param int $areaStatusID O ID do status de �rea (n�vel) da �rea do funcion�rio que se quer o respons�vel administrativo. Utilizar -1 caso se queira o respons�vel administrativo da �rea imediata do funcion�rio.
	 * @return int O ID do respons�vel administrativo atual da �rea do funcion�rio:
	 * - titular_funcion�rio_id ou
	 * - substituto_funcionario_id ou
	 * - auxiliar_funcionario_id
	 * @access public
	 */
	function getEmployeeCurrentAdministrativeResponsibleID($employeeID, $areaStatusID = -1)
	{
		return $this->getAreaCurrentAdministrativeResponsibleID($this->getEmployeeAreaID($employeeID, $areaStatusID));
	}
}
?>
