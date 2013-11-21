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
	 * Busca uma organização pelo seu ID.
	 *
	 * Este método irá procurar uma organização na tabela de organizações, pelo seu ID, e retornará seu dados básicos.
	 * @param int $organizationID O ID da organização.
	 * @return array Uma array associativa contendo os atributos de uma organização:
	 * - organizacao_id
	 * - nome: o nome abreviado da organização
	 * - descrição: o nome completo da organização
	 * - url_imagem: a url onde se encontra o gráfico da organização
	 * - ativa: se a organização está ativa ou não
	 * - sitio: a url da página web da organização
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
	 * Busca uma organização pelo seu nome.
	 *
	 * Este método irá buscar os dados básicos de uma organização, procurando pela sua sigla.
	 * @param string $name A sigla da organização.
	 * @return array Uma array associativa contendo os atributos de uma organização:
	 * - organizacao_id
	 * - nome: a sigla da organização
	 * - descricao: o nome completo
	 * - url_imagem: a url onde se encontra o gráfico da organização
	 * - ativa: se a organização está ativa ou não
	 * - sitio: a url da página web da organização
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
	 * Lista todos os telefones úteis de uma organização.
	 *
	 * Este método irá listar a tabela telefone.
	 * @param int $organizationID O ID da organização.
	 * @return array Uma array de arrays associativas contendo a lista dos telefones de uma organização:
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
	 * Busca os funcionários de uma organização
	 *
	 * Este método irá buscar na tabela de funcionários, todos os funcionários que pertencem à organização solicitada.
	 * @param int $organizationID O ID da organização.
	 * @param boolean $searchLdap True, caso seja necessário buscar no LDAP os dados dos usuários. Ou false, caso contrário.
	 * @param boolean $onlyActiveUsers true para retornar somente usuários ativos e false caso contrário
	 * @return array Uma array seqüencial contendo os funcionários de uma organização. Cada linha do array conterá:
	 * - organizacao_id
	 * - funcionario_id: uidNumber do funcionário
	 * - localidade_id
	 * - localidade_descricao
	 * - area_id
	 * - area_sigla
	 * - centro_custo_id
	 * - nm_centro_custo: número do centro de custo
	 * - centro_custo_descricao
	 * - nome: nome do funcionário (quando busca no Ldap)
	 * - email: email do funcionário (quando busca no Ldap)
	 * - telefone: telefone do funcionário (quando busca no Ldap)
	 * - uid: uid do funcionário (quando busca no Ldap)
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

		// Se desejar somente retornar usuários que estão ativos
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
	 * Busca as áreas de uma organização
	 *
	 * Este método irá buscar na tabela de áreas, todas as áreas que pertencem à organização solicitada.
	 * @param int $organizationID O ID da organização.
	 * @param int $onlyActiveAreas false= recupera todas as áreas; true= recupera somente as áreas ativas.
	 * @return array Uma array seqüencial contendo as áreas de uma organização. Cada linha do array conterá:
	 * - organizacao_id
	 * - area_id
	 * - area_status_id: corresponde ao nível hierárquico da area
	 * - superior_area_id: o id da área acima da atual
	 * - centro_custo_id: o id do centro de custo da area
	 * - titular_funcionario_id: o id do funcionario titular da área. Corresponde ao uidNumber do funcionário no catálogo Ldap.
	 * - substituto_funcionario_id: o id do funcionario que está substituindo o titular temporariamente
	 * - sigla: sigla da area
	 * - descrição: nome completo da area
	 * - ativa: indicativo de situação da area, sendo 's' ativa, e 'n' inativa
	 * - auxiliar_funcionario_id: id da secretária da área
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
		// Se desejar somente retornar as áreas que estão ativas
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
	 * @return array Uma array seqüencial contendo as áreas de uma organização e seus titulares e substitutos. Cada linha do array conterá:
	 * - area_id
	 * - titular_funcionario_id: o id do funcionario titular da área. Corresponde ao uidNumber do funcionário no catálogo Ldap.
	 * - substituto_funcionario_id: o id do funcionario que está substituindo o titular temporariamente
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
	 * Busca os status de área de uma organização.
	 *
	 * O status de área deve ser compreendido como um nível hirárquico das áreas da organização.
	 * Por exemplo: presidência, assessoria, diretoria, gerência, divisão, etc.
	 * @param int $organizationID O ID da organização.
	 * @return array Uma array de arrays associativas contendo os atributos dos status de área. Cada linha do array conterá:
	 * - area_status_id
	 * - organização_id
	 * - descrição
	 * - nível: a posição hierárquica do nível no organograma. Por exemplo: 1 - presidencia, 2 - assessoria, etc
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
	 * Busca as localidades de uma organização.
	 *
	 * As localidades de uma organização representam o local físico de trabalho dos funcionários.
	 * @param int $organizationID O ID da organização.
	 * @return array Uma array de arrays associativas contendo os atributos das localidades. Cada linha do array conterá:
	 * - organizacao_id
	 * - localidade_id
	 * - centro_custo_id
	 * - descricao: o nome simplificado localidade
	 * - empresa: o nome completo da localidade
	 * - endereço: o logradouro da empresa, com o número
	 * - complemento: dado adicional do endereço
	 * - cep: código de endereçamento postal, máscara nnnnnn-nnn
	 * - bairro: nome do bairro
	 * - cidade: nome da cidade
	 * - uf: unidade da federação
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
	 * Busca os centros de custo de uma organização.
	 *
	 * Este método retornará todos os centros de custo de uma organização.
	 * Centros de custo são como códigos contábeis para faturamento de serviços.
	 * @param int $organizationID O ID da organização.
	 * @return array Uma array de arrays associativas contendo os atributos dos centros de custo. Cada linha do array conterá:
	 * - organizacao_id
	 * - centro_custo_id
	 * - nm_centro_custo: número do centro de custo
	 * - grupo: estrutura numerica a qual o centro de custo pertence
	 * - descrição: nome do centro de custo
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
	 * Lista todas as categorias possíveis para um funcionário em uma organização.
	 *
	 * Este método listará a tabela de categorias.
	 * Por exemplo: funcionário, estagiário, terceirizado, etc.
	 * @param int $organizationID O ID da organização.
	 * @return array Uma array de arrays associativas contendo os atributos das categorias. Cada linha do array conterá:
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
	 * Lista todas as organizações do Organograma.
	 *
	 * Este método irá listar a tabela de organizações
	 * O modelo de dados do organograma foi construído para abrigar mais de uma organização
	 * @return um array de arrays associativas contendo a lista de organizações. Cada linha do array conterá:
	 * - organizacao_id
	 * - nome: sigla da organizacao
	 * - descricao
	 * - url_imagem: a url onde se encontra o gráfico da organização
	 * - ativa: se a organização está ativa ou não
	 * - sitio: a url da página web da organização
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
	 * Busca uma área pelo seu ID.
	 *
	 * Este método irá retornar os dados de uma área buscando pelo seu ID.
	 * @param int $areaID O ID da área.
	 * @return array Uma array associativa contendo os atributos de uma área:
	 * - organizacao_id
	 * - area_id
	 * - area_status: o nível hierárquico da área
	 * - superior_area_id: o id da área acima da atual
	 * - centro_custo_id: o centro de custo da área
	 * - titular_funcionario_id: o id do chefe da área
	 * - substituto_funcionario_id: o id do funcionario que esta substituindo o titular temporariamente
	 * - sigla
	 * - descicao
	 * - ativa: indicativo de situação da área: 's' ativa, 'n' inativa
	 * - auxiliar_funcionario_id: o id da secretária da área
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
	 * Busca uma área pela sua sigla.
	 *
	 * Este método retornará os atributos de uma área buscando pela sua sigla.
	 * @param string $acronym A sigla da área.
	 * @param int $organizationID O id da organização
	 * @return array Uma array associativa contendo os atributos de uma área:
	 * - organizacao_id
	 * - area_id
	 * - area_status: o nível hierárquico da área
	 * - superior_area_id: o id da área acima da atual
	 * - centro_custo_id: o centro de custo da área
	 * - titular_funcionario_id: o id do chefe da área
	 * - substituto_funcionario_id: o id do funcionario que esta substituindo o titular temporariamente
	 * - sigla
	 * - descicao
	 * - ativa: indicativo de situação da área: 's' ativa, 'n' inativa
	 * - auxiliar_funcionario_id: o id da secretária da área
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
	 * Busca as áreas que possuem um determinado status de área.
	 *
	 * Este método irá retornar todas as áreas cujo status (nível hierárquico) seja o solicitado.
	 * @param int $areaStatusID O ID do status de área.
	 * @return array Uma array de arrays associativas contendo os atributos de uma área. Cada linha do array conterá:
	 * - organizacao_id
	 * - area_id
	 * - area_status: o nível hierárquico da área
	 * - superior_area_id: o id da área acima da atual
	 * - centro_custo_id: o centro de custo da área
	 * - titular_funcionario_id: o id do chefe da área
	 * - substituto_funcionario_id: o id do funcionario que esta substituindo o titular temporariamente
	 * - sigla
	 * - descicao
	 * - ativa: indicativo de situação da área: 's' ativa, 'n' inativa
	 * - auxiliar_funcionario_id: o id da secretária da área
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
	 * Busca várias áreas através de uma array de IDs
	 *
	 * Este método irá buscar de uma vez só os dados de mais de uma área.
	 * @param array $areaIDs Array com os IDs das áreas
	 * @return array Um array de arrays associativos contendo os atributos de várias áreas. Cada linha do array conterá:
	 * - organizacao_id
	 * - area_id
	 * - area_status: o nível hierárquico da área
	 * - superior_area_id: o id da área acima da atual
	 * - centro_custo_id: o centro de custo da área
	 * - titular_funcionario_id: o id do chefe da área
	 * - substituto_funcionario_id: o id do funcionario que esta substituindo o titular temporariamente
	 * - sigla
	 * - descicao
	 * - ativa: indicativo de situação da área: 's' ativa, 'n' inativa
	 * - auxiliar_funcionario_id: o id da secretária da área
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

		// A execução é realizada sem o segundo parâmetro pois este não pode estar entre aspas
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
	 * Busca o ID da área superior a uma dada área.
	 *
	 * Este método irá buscar a área imediatamente superior à solicitada, ou então subirá na hierarquia até chegar no nível solicitado.
	 * @param int $areaID O ID da área da qual se quer saber a área superior.
	 * @param int $areaStatusID O ID do status de área (nível) da área pai. Utilizar -1 caso se queira a área imediatamente superior.
	 * - Por exemplo: área atual está no nível 5 (Divisão) e se quer buscar a área de nivel 3 (Diretoria).
	 * @return int O ID da área que é superior à área informada.
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
	 * Busca as áreas abaixo de uma determinada área.
	 *
	 * Este método irá buscar as áreas imediatamente inferiores à solicitada, não descendo nos próximos níveis da hierarquia.
	 * @param int $parentAreaID O ID da área da qual se quer saber as áreas imediatamente inferiores.
	 * @param boolean $onlyActiveAreas Valor lógico que, caso verdadeiro, faz com que o método retorne somente as áreas ativas
	 * @return array Um array de arrays associativos contendo os atributos de várias áreas. Cada linha do array conterá:
	 * - organizacao_id
	 * - area_id
	 * - area_status: o nível hierárquico da área
	 * - superior_area_id: o id da área acima da atual
	 * - centro_custo_id: o centro de custo da área
	 * - titular_funcionario_id: o id do chefe da área
	 * - substituto_funcionario_id: o id do funcionario que esta substituindo o titular temporariamente
	 * - sigla
	 * - descicao
	 * - ativa: indicativo de situação da área: 's' ativa, 'n' inativa
	 * - auxiliar_funcionario_id: o id da secretária da área
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
	 * Busca um status de área pelo seu ID.
	 *
	 * Procura na tabela de status de área (nível hierárquico de áreas) o registro que corresponde ao ID solicitado.
	 * @param int $areaStatusID O ID do status de área.
	 * @return array Uma array associativa contendo os atributos de um status de área:
	 * - area_status_id
	 * - organizacao_id
	 * - descricao: nome do nível hierárquivo. Por exemplo: presidência, assessoria, diretoria, gerência, etc.
	 * - nivel: valor numérico que identifica o nível: 1, 2, 3, ...
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
	 * Busca um status de área pelo seu nome.
	 *
	 * Este método irá retornar os dados de um status, procurando o registro na tabela através do seu nome.
	 * @param string $description O nome do status de área.
	 * @param int $organizationID O id da organização.
	 * @return array Uma array associativa contendo os atributos de um status de área:
	 * - area_status_id
	 * - organizacao_id
	 * - descricao: nome do nível hierárquivo. Por exemplo: presidência, assessoria, diretoria, gerência, etc.
	 * - nivel: valor numérico que identifica o nível: 1, 2, 3, ...
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
	 * Busca um status de área pelo seu nível.
	 *
	 * Este método irá retornar os dados de um status, procurando o registro na tabela através do seu nível.
	 * @param int $level O nível do status de área.
	 * @param int $organizationID O id da organização.
	 * @return array Uma array associativa contendo os atributos de um status de área:
	 * - area_status_id
	 * - organizacao_id
	 * - descricao: nome do nível hierárquivo. Por exemplo: presidência, assessoria, diretoria, gerência, etc.
	 * - nivel: valor numérico que identifica o nível: 1, 2, 3, ...
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
	 * Busca o ID do titular de uma área.
	 *
	 * Este método busca uma área e retorna o atributo titular_funcionario_id
	 * @param int $areaID O ID da área.
	 * @return int O ID do titular da área.
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
	 * Busca o ID do substituto de uma área.
	 *
	 * Este método irá buscar uma área e retornar o atributo substituto_funcionario_id.
	 * Note que o substituro é um campo opcional na área e poderá retornar vazio.
	 * @param int $areaID O ID da área.
	 * @return int O ID do substituto da área.
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
	 * Busca o ID do auxiliar administrativo de uma área.
	 *
	 * Este método busca uma área e retorna o atributo auxiliar_funcionario_id
	 * Nem todas as áreas possuem funcionários auxiliares (secretárias)
	 * @param int $areaID O ID da área.
	 * @return int O ID do auxiliar administrativo da área.
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
	 * Busca o ID do titular atual de uma área.
	 *
	 * Este método irá buscar uma área e caso haja um substituto, este será o titular atual; caso contrário, o titular atual é o próprio titular da área.
	 * @param int $areaID O ID da área.
	 * @return int O ID do titular atual da área, podendo ser um dos campos abaixo:
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
	 * Busca o ID do responsável administrativo atual de uma área.
	 *
	 * A precedência para definir quem é o responsável administrativo é: auxiliar administrativo, substituto e titular.
	 * @param int $areaID O ID da área.
	 * @return int O ID do responsável administrativo da área, podendo ser um dos atributos abaixo:
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
	 * Este método listará os atributos e uma localidade. Lembrando que localidade também pode ser entendida como o local físico de trabalho.
	 * @param int $localID O ID da localidade.
	 * @return array Uma array associativa contendo os atributos de uma localidade:
	 * - organizacao_id
	 * - localidade_id
	 * - centro_custo_id
	 * - descricao
	 * - empresa: o nome completo da localidade
	 * - endereço: o logradouro da empresa, com o número
	 * - complemento: dado adicional do endereço
	 * - cep: código de endereçamento postal, máscara nnnnnn-nnn
	 * - bairro: nome do bairro
	 * - cidade: nome da cidade
	 * - uf: unidade da federação
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
	 * @param int $organizationID O id da organização.
	 * @return array Uma array associativa contendo os atributos de uma localidade:
	 * - organizacao_id
	 * - localidade_id
	 * - centro_custo_id
	 * - descricao
	 * - empresa: o nome completo da localidade
	 * - endereço: o logradouro da empresa, com o número
	 * - complemento: dado adicional do endereço
	 * - cep: código de endereçamento postal, máscara nnnnnn-nnn
	 * - bairro: nome do bairro
	 * - cidade: nome da cidade
	 * - uf: unidade da federação
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
	 * Este método irá mostrar os atributos de um centro de custo, buscando na tabela pelo seu id.
	 * @param int $costCenterID O ID do centro de custo.
	 * @return array Uma array associativa contendo os atributos de um centro de custo:
	 * - organizacao_id
	 * - centro_custo_id
	 * - nm_centro_custo: número do centro de custo
	 * - grupo: estrutura numerica a qual o centro de custo pertence
	 * - descrição: nome do centro de custo
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
	 * Busca um centro de custo pelo seu número.
	 *
	 * Este método retornará os atributos de um centro de custo buscando pelo seu ID.
	 * @param int $number O número do centro de custo.
	 * @param ind $organizationID O id da organização
	 * @return array Uma array associativa contendo os atributos de um centro de custo:
	 * - organizacao_id
	 * - centro_custo_id
	 * - nm_centro_custo: número do centro de custo
	 * - grupo: estrutura numerica a qual o centro de custo pertence
	 * - descrição: nome do centro de custo
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
	 * Este método irá retornar os atributos de um centro de custo buscando pelo seu nome.
	 * @param string $description O nome do centro de custo.
	 * @param int $organizationID O id da organização
	 * @return array Uma array associativa contendo os atributos de um centro de custo.
	 * - organizacao_id
	 * - centro_custo_id
	 * - nm_centro_custo: número do centro de custo
	 * - grupo: estrutura numerica a qual o centro de custo pertence
	 * - descrição: nome do centro de custo
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
	 * Busca as informações de categoria a partir do ID.
	 *
	 * Este método irá retornar os atributos de uma linha da tabela de categorias de funcionários.
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
	 * Busca uma categoria a partir da sua descrição.
	 *
	 * Este método irá mostrar os atributos de uma categoria, buscando pela sua descrição.
	 * @param string $description O nome da categoria.
	 * @param int $organizationID O id da organização
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
	 * Lista todos os cargos de uma organização.
	 *
	 * Este método irá listar a tabela localidade.
	 * @param int $organizationID O ID da organização.
	 * @return array Uma array de arrays associativas contendo a lista dos cargos de uma organização:
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
	 * Busca as informações de cargo a partir do ID.
	 *
	 * Este método irá listar os atributos de um cargo, buscando na tabela a partir do seu ID.
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
	 * Busca um cargo a partir da sua descrição.
	 *
	 * Este método irá mostrar os atributos de um cargo, buscando pela sua descrição.
	 * @param string $description O nome do cargo.
	 * @param int $organizationID O id da organização.
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
	 * Busca um funcionário pelo seu ID.
	 *
	 * Este método retorna os atributos de um funcionário buscando pelo se id.
	 * O ID corresponde ao uidNumber do funcionário no catálogo LDAP.
	 * Se necessitar de outros atributos como o nome, cpf, email, matrícula, é necessário fazer uma consulta ao Ldap.
	 * @param int $employeeID O ID do funcionário.
	 * @return array Uma array associativa contendo os atributos de um funcionário:
	 * - funcionario_id
	 * - area_id
	 * - localidade_id
	 * - centro_custo_id
	 * - organizacao_id
	 * - funcionario_status_id
	 * - cargo_id
	 * - nivel: o nível numérico dentro do cargo
	 * - funcionario_categoria_id
	 * - titulo: nome pelo qual o funcionário e reconhecido na organização, por exemplo: gerente comercial
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
	 * Busca lista de funcionários pela sua localidade.
	 *
	 * Este método busca todos os funcionários de uma localidade, ou, caso a localidade não seja passada, retorna todos os funcionários.
	 * @param int $localID O ID da localidade.
	 * @param int $organizationID O ID da organização.
	 * @param boolean $searchLdap True, caso seja necessário buscar no LDAP os dados dos usuários. Ou false, caso contrário.
	 * @param string $external Null, caso deseje-se recuperar localidades externas e internas à organização. 'S', para recuperar apenas as externas e 'N', para apenas as internas.
	 * @return array Uma array contendo os dados dos usários e sua localidade
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
	 * Percorre o array passado por parâmetro, buscando os dados dos funcionários no Ldap. Adiciona estes dados no array e o retorna.
	 *
	 * @param array $output Array contendo os usuários que deverão ser consultados no Ldap
	 * @return array Retorna o mesmo array passado por parâmetro, adicionados os dados dos usuários buscados no Ldap
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
	 * Busca lista de funcionários de uma área pelo ID da área.
	 *
	 * Este método retornará todos os funcionários de uma área.
	 * @param int $areaID O ID da área.
	 * @param boolean $onlyActiveUsers true para retornar somente usuários ativos e false caso contrário
	 * @return array Uma array sequencial de arrays associativos contendo contendo o ID dos funcionários de uma área:
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
	 * Busca um status de funcionário pelo seu ID.
	 *
	 * Este método irá retornar os dados de um status de funcionário, buscando pelo ID solicitado.
	 * @param int $employeeStatusID O ID do status.
	 * @return array Uma array associativa contendo os atributos de um status de funcionário:
	 * - funcionario_status_id
	 * - descricao
	 * - exibir: indicativo booleado 'S' funcionário será listado no organograma, 'N' funcionário ficará oculto na aba organograma.
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
	 * Busca o ID da área (podendo ser de outros níveis) de um funcionário.
	 *
	 * Este método irá retornar o ID da área do funcionário. Fará uma busca para cima na hierarquia se for solicitado.
	 * @param int $employeeID O ID do funcionário do qual se quer saber a área.
	 * @param int $areaStatusID O ID do status de área (nível) da área do funcionário que se quer. Utilizar -1 caso se queira a área imediata do funcionário.
	 * - Por exemplo: o funcionário está em uma área de status 5 (Divisão) e se quer a área superior de nível 3 (Diretoria).
	 * @return int O ID da área do funcionário.
	 * @access public
	 */
	function getEmployeeAreaID($employeeID, $areaStatusID = -1)
	{
		$employee = $this->getEmployee($employeeID);
		if (!$employee)
			return false;

		/* requer a área do funcionário */
		if ($areaStatusID == -1)
			return $employee['area_id'];
		else
		{
			/* verifica se a área do funcionário já está no nível solicitado */
			$currentArea = $this->getArea($employee['area_id']);
			if ($currentArea['area_status_id'] == $areaStatusID)
				return $currentArea['area_id'];
			else
				return $this->getParentAreaID($currentArea['area_id'], $areaStatusID);
		}
	}

	/**
	 * Busca o ID da localidade de um funcionário.
	 *
	 * Este método buscará o funcionário pelo seu ID e retornará o ID da sua localidade.
	 * @param int $employeeID O ID do funcionário.
	 * @return int O ID da localidade do funcionário.
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
	 * Busca o ID do status de um funcionário.
	 *
	 * Este método buscará um funcionário pelo seu ID e retornará o ID de status do funcionário.
	 * Status de funcionário corresponde à sua situação: ativo, desligado, etc.
	 * @param int $employeeID O ID do funcionário.
	 * @return int O ID do status do funcionário.
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
	 * Busca o ID do centro de custo de um funcionário.
	 *
	 * Este método irá buscar um funcionário pelo seu ID e retornará o ID do centro de custo do funcionário.
	 * O centro de custo não é obrigatório por funcionário. Neste caso, se necessário, busque o centro de custo da área ou da localidade.
	 * @param int $employeeID O ID do funcionário.
	 * @return int O ID do centro de custo do funcionário.
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
	 * Busca o ID do cargo de um funcionário.
	 *
	 * Este método buscará um funcionário pelo seu ID e retornará o ID do cargo do funcionário.
	 * @param int $employeeID O ID do funcionário.
	 * @return int O ID do cargo do funcionário.
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
	 * Busca o ID da categoria de um funcionário.
	 *
	 * Este método buscará um funcionário pelo seu ID e retornará o ID da categoria do funcionário.
	 * A categoria corresponde ao tipo de vínculo do funcionário com a organizacao. Por exemplo:
	 * - funcionário
	 * - estagiário
	 * - terceirizado, etc
	 * @param int $employeeID O ID do funcionário.
	 * @return int O ID do categoria do funcionário.
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
	 * Busca o nível de um funcionário.
	 *
	 * Este método buscará o funcionário pelo seu ID e retornará o nível do funcionário dentro do cargo.
	 * Geralmente um cargo (por exemplo: auxiliar tecnico) é composto por níveis de evolução na carreira: 1,2,3,...
	 * @param int $employeeID O ID do funcionário.
	 * @return int A quantidade de níveis do funcionário.
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
	 * Busca o ID do titular da área de um funcionário.
	 *
	 * Este método buscará o titular da área do funcionário, e poderá subir na hierarquia, buscando o titular de áreas superiores à área do funcionário.
	 * @param int $employeeID O ID do funcionário.
	 * @param int $areaStatusID O ID do status de área (nível) da área do funcionário que se quer o titular. Utilizar -1 caso se queira o titular da área imediata do funcionário.
	 * @return int O ID do titular da área do funcionário:
	 * - titular_funcionario_id
	 * @access public
	 */
	function getEmployeeSupervisorID($employeeID, $areaStatusID = -1)
	{
		return $this->getAreaSupervisorID($this->getEmployeeAreaID($employeeID, $areaStatusID));
	}

	/**
	 * Busca o ID do titular atual da área do funcionário.
	 *
	 * Caso haja um substituto, este será o titular atual; caso contrário, o titular atual é o próprio titular da área.
	 * @param int $employeeID O ID do funcionário.
	 * @param int $areaStatusID O ID do status de área (nível) da área do funcionário que se quer o titular atual. Utilizar -1 caso se queira o titular atual da área imediata do funcionário.
	 * @return int O ID do titular atual da área do funcionário:
	 * - titular_funcionário_id ou
	 * - substituto_funcionario_id
	 * @access public
	 */
	function getEmployeeCurrentSupervisorID($employeeID, $areaStatusID = -1)
	{
		return $this->getAreaCurrentSupervisorID($this->getEmployeeAreaID($employeeID, $areaStatusID));
	}

	/**
	 * Busca o ID do responsável administrativo atual de uma área.
	 *
	 * A precedência para definir quem é o responsável administrativo é: auxiliar administrativo, substituto e titular.
	 * @param int $employeeID O ID do funcionário.
	 * @param int $areaStatusID O ID do status de área (nível) da área do funcionário que se quer o responsável administrativo. Utilizar -1 caso se queira o responsável administrativo da área imediata do funcionário.
	 * @return int O ID do responsável administrativo atual da área do funcionário:
	 * - titular_funcionário_id ou
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
