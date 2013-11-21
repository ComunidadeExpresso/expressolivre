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
 * Camada Model do Organograma.
 * @package Workflow
 * @author Sidnei Augusto Drovetto Jr. - drovetto@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 */
class so_orgchart
{
	/**
	 * @var bool True se o usuário for administrador do expresso.
	 * @access private
	 */
	private $isAdmin;

	/**
	 * @var int ID do usuário logado no Expresso
	 * @access private
	 */
	private $userID;

	/**
	 * @var object Link para a ACL do Workflow.
	 * @access private
	 */
	private $acl;

	/**
	 * @var object Link para o Banco de Dados do Workflow.
	 * @access private
	 */
	private $db;

	/**
	 * @var bool Indica se alguns métodos desta classe poderão ser chamados por métodos externos
	 * @access private
	 */
	private $externalCalls = false;

	/**
	 * Checa se o usuário possui acesso ao Organograma ou permissão para modificar determinada organização.
	 * @param int $organizationID O ID da organização do Orgranograma.
	 * @param bool $checkType Indica se a checagem não depende do ID da organização.
	 * @param bool $safeMethod Indica que a checagem pode ser ignorada quando chamada por outras partes do módulo Workflow
	 * @return void
	 * @access private
	 */
	private function _checkAccess($organizationID = null, $checkType = false, $safeMethod = false)
	{
		/* the user is an administrator */
		if ($this->isAdmin)
			return true;

		if ($safeMethod)
			if ($this->externalCalls)
				return true;

		$authorized = false;
		if ($checkType)
			$authorized = $this->acl->checkUserGroupAccessToType('ORG', $this->userID);
		else
		{
			if (!is_numeric($organizationID))
				$authorized = false;
			else
				$authorized = $this->acl->checkUserGroupAccessToResource('ORG', $this->userID, (int) $organizationID);
		}

		if (!$authorized)
			$this->endExecution("Você não tem permissão para executar este procedimento!");
	}

	/**
	 * Finaliza a execução e envia uma mensagem serializada (para ser exibida no retorno do Ajax).
	 * @param mixed A mensagem que será exibida. Pode ser uma array de mensagens ou uma string.
	 * @return void
	 * @access private
	 */
	private function endExecution($message)
	{
		if (!is_array($message))
			$message = array($message);

		die(serialize(implode("\n", $message)));
	}

	/**
	 * Define que alguns métodos desta classe poderão ser chamados.
	 * @param bool $satus O status. true para permitir e false para restringir.
	 * @return void
	 * @access public
	 */
	public function setExternalCalls($status)
	{
		$this->externalCalls = ($status === true);
	}

	/**
	 * Verifica se houve erro em alguma query do Banco de Dados.
	 * @param object $result O resultado de alguma query
	 * @return void
	 * @access private
	 */
	private function _checkError($result)
	{
		if ($result === false)
			die(serialize("Ocorreu um erro ao se tentar executar a operação solicitada."));
	}

	/**
	 * Construtor da classe so_orgchart
	 * @return object
	 */
	function so_orgchart()
	{
		$this->userID = $_SESSION['phpgw_info']['workflow']['account_id'];
		$this->isAdmin = $_SESSION['phpgw_info']['workflow']['user_is_admin'];
		$this->acl = &$GLOBALS['ajax']->acl;
		$this->db = &Factory::getInstance('WorkflowObjects')->getDBWorkflow()->Link_ID;
		$this->db->SetFetchMode(ADODB_FETCH_ASSOC);
	}

	/**
	 * Lista todas as organizações do Organograma.
	 * @return array Lista de organizações.
	 * @access public
	 */
	function getOrganizations()
	{
		$this->_checkAccess(null, true);

		if ($this->isAdmin)
			$query = "SELECT organizacao_id, nome, descricao, ativa, url_imagem, sitio FROM organizacao ORDER BY nome";
		else
		{
			$organizations = $this->acl->getUserGroupPermissions("ORG", $this->userID, 0);
			$organizations[] = -1;
			$query = "SELECT organizacao_id, nome, descricao, ativa, url_imagem, sitio FROM organizacao WHERE (organizacao_id IN (" . implode(',', $organizations)  . ")) ORDER BY nome";
		}
		$result = $this->db->query($query);
		$this->_checkError($result);

		$output = $result->GetArray(-1);

		for ($i = 0; $i < count($output); ++$i)
			for ($j = 0; $j < $result->_numOfFields; ++$j)
				unset($output[$i][$j]);

		return $output;
	}

	/**
	 * Adiciona uma organização.
	 * @param string $name O nome da organização.
	 * @param string $description A descrição da organização.
	 * @param string $imageURL O caminho da imagem que representa o organograma da organização.
	 * @param char $active 'S' se a organização estiver ativa e 'N' caso contrário.
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function addOrganization($name, $description, $imageURL, $active, $siteURL)
	{
		$this->_checkAccess(null, true);

		$query = "INSERT INTO organizacao(nome, descricao, url_imagem, ativa, sitio) VALUES(?, ?, ?, ?, ?)";
		$result = $this->db->query($query, array($name, $description, $imageURL, $active, $siteURL));
		$this->_checkError($result);

		return (($result === false) ? false : true);
	}

	/**
	 * Atualiza as informações sobre uma organização.
	 * @param string $name O nome da organização.
	 * @param string $description A descrição da organização.
	 * @param string $imageURL O caminho da imagem que representa o organograma da organização.
	 * @param char $active 'S' se a organização estiver ativa e 'N' caso contrário.
	 * @param int $organizationID O ID da organização do Orgranograma.
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function updateOrganization($name, $description, $imageURL, $active, $organizationID, $siteURL)
	{
		$this->_checkAccess($organizationID);

		$query = "UPDATE organizacao SET nome = ?, descricao = ?, url_imagem = ?, ativa = ?, sitio = ? WHERE (organizacao_id = ?)";
		$result = $this->db->query($query, array($name, $description, $imageURL, $active, $siteURL, $organizationID));
		$this->_checkError($result);

		return (($result === false) ? false : true);
	}

	/**
	 * Remove uma organização.
	 * @param int $organizationID O ID da organização do Orgranograma.
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function removeOrganization($organizationID)
	{
		$this->_checkAccess($organizationID);

		$query = "DELETE FROM organizacao WHERE (organizacao_id = ?)";
		$result = $this->db->query($query, array($organizationID));
		$this->_checkError($result);

		return (($result === false) ? false : true);
	}

	/**
	 * Lista os possíveis status dos funcionários.
	 * @param int $organizationID O ID da organização do Orgranograma.
	 * @return array Lista dos possíveis status dos empregados.
	 * @access public
	 */
	function getEmployeeStatus($organizationID)
	{
		$this->_checkAccess($organizationID);

		$query = "SELECT funcionario_status_id, descricao, exibir, organizacao_id FROM funcionario_status WHERE (organizacao_id = ?) ORDER BY descricao";
		$result = $this->db->query($query, array($organizationID));
		$this->_checkError($result);

		$output = $result->GetArray(-1);

		return $output;
	}

	/**
	 * Adiciona um Status de funcionário.
	 * @param int $organizationID O ID da organização do Orgranograma.
	 * @param string $description A descrição do status.
	 * @param char $show 'S' se o funcionário será exibido na interface de organograma do usuário ou 'N' caso contrário.
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function addEmployeeStatus($organizationID, $description, $show)
	{
		$this->_checkAccess($organizationID);

		$query = "INSERT INTO funcionario_status(organizacao_id, descricao, exibir) VALUES(?, ?, ?)";
		$result = $this->db->query($query, array($organizationID, $description, $show));
		$this->_checkError($result);

		return (($result === false) ? false : true);
	}

	/**
	 * Atualiza um Status de funcionário.
	 * @param int $employeeStatusID O ID do status de funcionário.
	 * @param int $organizationID O ID da organização do Orgranograma.
	 * @param string $description A descrição do status.
	 * @param char $show 'S' se o funcionário será exibido na interface de organograma do usuário ou 'N' caso contrário.
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function updateEmployeeStatus($employeeStatusID, $organizationID, $description, $show)
	{
		$this->_checkAccess($organizationID);

		$query = "UPDATE funcionario_status SET descricao = ?, exibir = ? WHERE (funcionario_status_id = ?) AND (organizacao_id = ?)";
		$result = $this->db->query($query, array($description, $show, $employeeStatusID, $organizationID));
		$this->_checkError($result);

		return (($result === false) ? false : true);
	}

	/**
	 * Remove um Status de funcionário.
	 * @param int $employeeStatusID O ID do status de funcionário.
	 * @param int $organizationID O ID da organização do Orgranograma.
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function removeEmployeeStatus($employeeStatusID, $organizationID)
	{
		$this->_checkAccess($organizationID);

		$query = "DELETE FROM funcionario_status WHERE (funcionario_status_id = ?) AND (organizacao_id = ?)";
		$result = $this->db->query($query, array($employeeStatusID, $organizationID));
		$this->_checkError($result);

		return (($result === false) ? false : true);
	}

	/**
	 * Lista as possíveis categorias de uma organização.
	 * @param int $organizationID O ID da organização.
	 * @return array Lista das possíveis categorias de uma organização.
	 * @access public
	 */
	function getEmployeeCategory($organizationID)
	{
		$this->_checkAccess($organizationID);

		$query = "SELECT funcionario_categoria_id, organizacao_id, descricao FROM funcionario_categoria WHERE (organizacao_id = ?) ORDER BY descricao";
		$result = $this->db->query($query, array($organizationID));
		$this->_checkError($result);

		$output = $result->GetArray(-1);
		for ($i = 0; $i < count($output); ++$i)
			for ($j = 0; $j < $result->_numOfFields; ++$j)
				unset($output[$i][$j]);

		return $output;
	}

	/**
	 * Adiciona uma categoria.
	 * @param int $organizationID O ID da organização.
	 * @param string $description A descrição da categoria.
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function addEmployeeCategory($organizationID, $description)
	{
		$this->_checkAccess($organizationID);

		$query = "INSERT INTO funcionario_categoria(organizacao_id, descricao) VALUES(?, ?)";
		$result = $this->db->query($query, array($organizationID, $description));
		$this->_checkError($result);

		return (($result === false) ? false : true);
	}

	/**
	 * Atualiza uma categoria.
	 * @param int $employeeCategoryID O ID da categoria.
	 * @param int $organizationID O ID da organização.
	 * @param string $description A descrição da categoria.
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function updateEmployeeCategory($employeeCategoryID, $organizationID, $description)
	{
		$this->_checkAccess($organizationID);

		$query = "UPDATE funcionario_categoria SET descricao = ? WHERE (funcionario_categoria_id = ?) AND (organizacao_id = ?)";
		$result = $this->db->query($query, array($description, $employeeCategoryID, $organizationID));
		$this->_checkError($result);

		return (($result === false) ? false : true);
	}

	/**
	 * Remove uma categoria.
	 * @param int $employeeCategoryID O ID da categoria.
	 * @param int $organizationID O ID da organização.
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function removeEmployeeCategory($employeeCategoryID, $organizationID)
	{
		$this->_checkAccess($organizationID);

		$query = "DELETE FROM funcionario_categoria WHERE (funcionario_categoria_id = ?) AND (organizacao_id = ?)";
		$result = $this->db->query($query, array($employeeCategoryID, $organizationID));
		$this->_checkError($result);

		return (($result === false) ? false : true);
	}

	/**
	 * Lista os possíveis cargos de uma organização.
	 * @param int $organizationID O ID da organização.
	 * @return array Lista dos possíveis cargos de uma organização.
	 * @access public
	 */
	function getJobTitle($organizationID)
	{
		$this->_checkAccess($organizationID);

		$query = "SELECT cargo_id, organizacao_id, descricao FROM cargo WHERE (organizacao_id = ?) ORDER BY descricao";
		$result = $this->db->query($query, array($organizationID));
		$this->_checkError($result);

		$output = $result->GetArray(-1);

		for ($i = 0; $i < count($output); ++$i)
			for ($j = 0; $j < $result->_numOfFields; ++$j)
				unset($output[$i][$j]);

		return $output;
	}

	/**
	 * Adiciona um cargo.
	 * @param int $organizationID O ID da organização.
	 * @param string $description A descrição do cargo.
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function addJobTitle($organizationID, $description)
	{
		$this->_checkAccess($organizationID);

		$query = "INSERT INTO cargo(organizacao_id, descricao) VALUES(?, ?)";
		$result = $this->db->query($query, array($organizationID, $description));
		$this->_checkError($result);

		return (($result === false) ? false : true);
	}

	/**
	 * Atualiza um cargo.
	 * @param int $jobTitleID O ID do cargo.
	 * @param int $organizationID O ID da organização.
	 * @param string $description A descrição do cargo.
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function updateJobTitle($jobTitleID, $organizationID, $description)
	{
		$this->_checkAccess($organizationID);

		$query = "UPDATE cargo SET descricao = ? WHERE (cargo_id = ?) AND (organizacao_id = ?)";
		$result = $this->db->query($query, array($description, $jobTitleID, $organizationID));
		$this->_checkError($result);

		return (($result === false) ? false : true);
	}

	/**
	 * Remove um cargo.
	 * @param int $jobTitleID O ID do cargo.
	 * @param int $organizationID O ID da organização.
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function removeJobTitle($jobTitleID, $organizationID)
	{
		$this->_checkAccess($organizationID);

		$query = "DELETE FROM cargo WHERE (cargo_id = ?) AND (organizacao_id = ?)";
		$result = $this->db->query($query, array($jobTitleID, $organizationID));
		$this->_checkError($result);

		return (($result === false) ? false : true);
	}

	/**
	 * Lista os possíveis status das áreas de uma organização.
	 * @param int $organizationID O ID da organização.
	 * @return array Lista dos possíveis status das áreas de uma organização.
	 * @access public
	 */
	function getAreaStatus($organizationID)
	{
		$this->_checkAccess($organizationID);

		$query = "SELECT area_status_id, organizacao_id, descricao, nivel FROM area_status WHERE organizacao_id = ? ORDER BY descricao";
		$result = $this->db->query($query, array($organizationID));
		$this->_checkError($result);

		$output = $result->GetArray(-1);

		for ($i = 0; $i < count($output); ++$i)
			for ($j = 0; $j < $result->_numOfFields; ++$j)
				unset($output[$i][$j]);

		return $output;
	}

	/**
	 * Adiciona um status de área.
	 * @param int $organizationID O ID da organização.
	 * @param string $description A descrição do status.
	 * @param int $level O nível do status.
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function addAreaStatus($organizationID, $description, $level)
	{
		$this->_checkAccess($organizationID);

		$query = "INSERT INTO area_status(organizacao_id, descricao, nivel) VALUES(?, ?, ?)";
		$result = $this->db->query($query, array($organizationID, $description, $level));
		$this->_checkError($result);

		return (($result === false) ? false : true);
	}

	/**
	 * Atualiza um status de área.
	 * @param int $areaStatusID O ID do status da área.
	 * @param int $organizationID O ID da organização.
	 * @param string $description A descrição do status.
	 * @param int $level O nível do status.
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function updateAreaStatus($areaStatusID, $organizationID, $description, $level)
	{
		$this->_checkAccess($organizationID);

		$query = "UPDATE area_status SET descricao = ?, nivel = ? WHERE (area_status_id = ?) AND (organizacao_id = ?)";
		$result = $this->db->query($query, array($description, $level, $areaStatusID, $organizationID));
		$this->_checkError($result);

		return (($result === false) ? false : true);
	}

	/**
	 * Remove um status de área.
	 * @param int $areaStatusID O ID do status da área.
	 * @param int $organizationID O ID da organização.
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function removeAreaStatus($areaStatusID, $organizationID)
	{
		$this->_checkAccess($organizationID);

		$query = "DELETE FROM area_status WHERE (area_status_id = ?) AND (organizacao_id = ?)";
		$result = $this->db->query($query, array($areaStatusID, $organizationID));
		$this->_checkError($result);

		return (($result === false) ? false : true);
	}

	/**
	 * Lista os centros de custo de uma organização.
	 * @param int $organizationID O ID da organização.
	 * @return array Lista dos centros de custo de uma organização.
	 * @access public
	 */
	function getCostCenter($organizationID)
	{
		$this->_checkAccess($organizationID);

		$query = "SELECT organizacao_id, centro_custo_id, nm_centro_custo, descricao, grupo FROM centro_custo WHERE organizacao_id = ? ORDER BY descricao";
		$result = $this->db->query($query, array($organizationID));
		$this->_checkError($result);

		$output = $result->GetArray(-1);

		for ($i = 0; $i < count($output); ++$i)
			for ($j = 0; $j < $result->_numOfFields; ++$j)
				unset($output[$i][$j]);

		return $output;
	}

	/**
	 * Adiciona um centro de custo.
	 * @param int $organizationID O ID da organização.
	 * @param int $number O número do centro de custo.
	 * @param string $description A descrição do centro de custo.
	 * @param string $group O grupo do centro de custo.
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function addCostCenter($organizationID, $number, $description, $group)
	{
		$this->_checkAccess($organizationID);

		$query = "INSERT INTO centro_custo(organizacao_id, nm_centro_custo, descricao, grupo) VALUES(?, ?, ?, ?)";
		$result = $this->db->query($query, array($organizationID, $number, $description, $group));
		$this->_checkError($result);

		return (($result === false) ? false : true);
	}

	/**
	 * Atualiza um centro de custo.
	 * @param int $organizationID O ID da organização.
	 * @param int $number O número do centro de custo.
	 * @param string $description A descrição do centro de custo.
	 * @param string $group O grupo do centro de custo.
	 * @param int $costCenterID O ID do centro de custo.
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function updateCostCenter($organizationID, $number, $description, $group, $costCenterID)
	{
		$this->_checkAccess($organizationID);

		$query = "UPDATE centro_custo SET organizacao_id = ?, nm_centro_custo = ?, descricao = ?, grupo = ? WHERE (centro_custo_id = ?)";
		$result = $this->db->query($query, array($organizationID, $number, $description, $group, $costCenterID));
		$this->_checkError($result);

		return (($result === false) ? false : true);
	}

	/**
	 * Remove um centro de custo.
	 * @param int $costCenterID O ID do centro de custo.
	 * @param int $organizationID O ID da organização.
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function removeCostCenter($costCenterID, $organizationID)
	{
		$this->_checkAccess($organizationID);

		$query = "DELETE FROM centro_custo WHERE (centro_custo_id = ?) AND (organizacao_id = ?)";
		$result = $this->db->query($query, array($costCenterID, $organizationID));
		$this->_checkError($result);

		return (($result === false) ? false : true);
	}

	/**
	 * Lista as localidade de uma organização.
	 * @param int $organizationID O ID da organização.
	 * @return array Lista das localidades de uma organização.
	 * @access public
	 */
	function getLocal($organizationID)
	{
		$this->_checkAccess($organizationID);

		$query_fields = 'organizacao_id, localidade_id, centro_custo_id, descricao, empresa, endereco, complemento, cep, bairro, cidade, uf, externa';
		$query = "SELECT {$query_fields} FROM localidade WHERE organizacao_id = ? ORDER BY descricao";
		$result = $this->db->query($query, array($organizationID));
		$this->_checkError($result);

		$output = $result->GetArray(-1);

		for ($i = 0; $i < count($output); ++$i){
			for ($j = 0; $j < $result->_numOfFields; ++$j)
				unset($output[$i][$j]);
			$output[$i]['centro_custo_id'] = empty($output[$i]['centro_custo_id']) ? 'NULL' : $output[$i]['centro_custo_id'];
		}

		return $output;
	}

	/**
	 * Adiciona uma localidade.
	 * @param int $organizationID O ID da organização.
	 * @param int $costCenterID O ID do centro de custo.
	 * @param string $description A descrição da localidade.
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function addLocal($organizationID, $costCenter, $description, $company, $address, $complement, $zipCode, $neighborhood, $city, $state, $external )
	{
		$this->_checkAccess($organizationID);

		$query = "INSERT INTO localidade(organizacao_id, centro_custo_id, descricao, empresa, endereco, complemento, cep, bairro, cidade, uf, externa) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
		$result = $this->db->query($query, array($organizationID, $costCenter, $description, $company, $address, $complement, $zipCode, $neighborhood, $city, $state, $external ));
		$this->_checkError($result);

		return (($result === false) ? false : true);
	}

	/**
	 * Atualiza uma localidade.
	 * @param int $organizationID O ID da organização.
	 * @param int $costCenterID O ID do centro de custo.
	 * @param string $description A descrição da localidade.
	 * @param int $localID O ID da localidade.
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function updateLocal($organizationID, $costCenter, $description, $localID, $company, $address, $complement, $zipCode, $neighborhood, $city, $state, $external )
	{
		$this->_checkAccess($organizationID);

		$query = "UPDATE localidade SET organizacao_id = ?, centro_custo_id = ?, descricao = ?, empresa = ?, endereco = ?, complemento = ?, cep = ?, bairro = ?, cidade = ?, uf = ?, externa = ? WHERE (localidade_id = ?)";
		$result = $this->db->query($query, array($organizationID, $costCenter, $description, $company, $address, $complement, $zipCode, $neighborhood, $city, $state, $external, $localID) );
		$this->_checkError($result);

		return (($result === false) ? false : true);
	}

	/**
	 * Remove uma localidade.
	 * @param int $organizationID O ID da organização.
	 * @param int $localID O ID da localidade.
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function removeLocal($localID, $organizationID)
	{
		$this->_checkAccess($organizationID);

		$query = "DELETE FROM localidade WHERE (localidade_id = ?) AND (organizacao_id = ?)";
		$result = $this->db->query($query, array($localID, $organizationID));
		$this->_checkError($result);

		return (($result === false) ? false : true);
	}

	/**
	 * Lista os funcionários de uma determinada área da organização.
	 * @param int $areaID O ID da área.
	 * @param int $organizationID O ID da organização.
	 * @return array Lista dos funcionários de uma determinada área da organização.
	 * @access public
	 */
	function getAreaEmployee($areaID, $organizationID)
	{
		$this->_checkAccess($organizationID);

		/* gather some info from the area */
		$areaInfo = $this->db->query('SELECT COALESCE(a.titular_funcionario_id, -1) AS titular_funcionario_id, COALESCE(s.funcionario_id, -1) AS substituto_funcionario_id FROM area a LEFT OUTER JOIN substituicao s ON ((a.area_id = s.area_id) AND (CURRENT_DATE BETWEEN s.data_inicio AND s.data_fim)) WHERE (a.organizacao_id = ?) AND (a.area_id = ?)', array($organizationID, $areaID))->GetArray(-1);
		if (empty($areaInfo))
			return false;
		$areaInfo = $areaInfo[0];
		$supervisors = '{' . implode(', ', $areaInfo) . '}';

		$query = "SELECT funcionario_id, funcionario_status_id, centro_custo_id, localidade_id, organizacao_id, area_id, cargo_id, nivel, funcionario_categoria_id, titulo, funcao, to_char(data_admissao,'DD/MM/YYYY') as data_admissao, apelido FROM funcionario WHERE ((area_id = ?) AND (organizacao_id = ?)) OR (funcionario_id = ANY (?))";
		$result = $this->db->query($query, array($areaID, $organizationID, $supervisors));
		$this->_checkError($result);

		$output = $result->GetArray(-1);
		$cachedLDAP = Factory::newInstance('CachedLDAP');
		$cachedLDAP->setOperationMode($cachedLDAP->OPERATION_MODE_LDAP_DATABASE);
        $output_count = count($output);
		for ($i = 0; $i < $output_count; ++$i)
		{
			$output[$i]['funcionario_id_desc'] = '';
			$output[$i]['uid'] = '';

			if (in_array($output[$i]['funcionario_id'], $areaInfo))
				$output[$i]['chief'] = ($output[$i]['funcionario_id'] == $areaInfo['titular_funcionario_id']) ? 1 : 2;

			if (($entry = $cachedLDAP->getEntryByID($output[$i]['funcionario_id'])))
			{
				$output[$i]['funcionario_id_desc'] = $entry['cn'];
				$output[$i]['uid'] = $entry['uid'];
				$output[$i]['removed'] = is_null($entry['last_update']);
			}
		}

		usort($output, create_function('$a,$b', 'return strcasecmp($a[\'funcionario_id_desc\'],$b[\'funcionario_id_desc\']);'));
		return $output;
	}

	/**
	 * Procura por funcionários de acordo com um termo de busca.
	 * @param string $searchTerm O termo de busca. Pode ser referente ao ID do funcionário ou ao nome do mesmo.
	 * @param int $organizationID O ID da organização.
	 * @return array Lista dos funcionários que satisfazem o critério de busca.
	 * @access public
	 */
	function searchEmployee($searchTerm, $organizationID)
	{
		$organizationID = (int) $organizationID;
		$this->_checkAccess($organizationID);

		/* initialize some variables */
		$output = array();
		$unifiedResult = array();

		/* FIXME - this piece of code should use the new CacheLdap class */
		if (is_numeric($searchTerm))
		{
			$searchTerm = (int) $searchTerm;
			$ldapSearch = "(&(|(employeenumber={$searchTerm})(uidnumber={$searchTerm}))(phpgwaccounttype=u))";
			$DBSearch = "SELECT uidnumber, cn, uid, last_update FROM egw_wf_user_cache WHERE (employeenumber = ?) OR (uidnumber = ?)";
			$DBValues = array($searchTerm, $searchTerm);
		}
		else
		{
			$ldapSearch = "(&(cn=*{$searchTerm}*)(phpgwaccounttype=u))";
			$DBSearch = "SELECT uidnumber, cn, uid, last_update FROM egw_wf_user_cache WHERE (cn ILIKE ?)";
			$DBValues = array("%{$searchTerm}%");
		}

		/* search for the $searchTerm in the LDAP */
		$ldap = &Factory::getInstance('WorkflowObjects')->getLDAP();
		$list = @ldap_search($ldap, Factory::getInstance('WorkflowLDAP')->getLDAPContext(), $ldapSearch, array('uidnumber', 'cn', 'uid'));
		if ($list === false)
			die(serialize("O sistema de busca não pode ser utilizado nesta organização."));
		$entries = ldap_get_entries($ldap, $list);
		for ($i=0; $i < $entries['count']; ++$i)
			$unifiedResult[$entries[$i]['uidnumber'][0]] = array('name' => $entries[$i]['cn'][0], 'uid' => $entries[$i]['uid'][0], 'removed' => false);

		/* search for the $searchTerm in the DB */
		$resultSet = Factory::getInstance('WorkflowObjects')->getDBGalaxia()->Link_ID->query($DBSearch, $DBValues)->GetArray(-1);
		foreach ($resultSet as $row)
			if (!isset($unifiedResult[$row['uidnumber']]))
				$unifiedResult[$row['uidnumber']] = array('name' => $row['cn'], 'uid' => $row['uid'], 'removed' => is_null($row['last_update']));

		/* check if any result was found */
		if (count($unifiedResult) < 1)
			return $output;

		/* load employee information */
		$query = "SELECT f.funcionario_id, f.funcionario_status_id, f.centro_custo_id, f.localidade_id, f.organizacao_id, f.area_id, f.cargo_id, f.nivel, f.funcionario_categoria_id, f.titulo, f.apelido, f.funcao, to_char(f.data_admissao, 'DD/MM/YYYY') as data_admissao, a.sigla AS area_sigla FROM funcionario f, area a WHERE (f.area_id = a.area_id) AND (f.organizacao_id = $organizationID) AND (f.funcionario_id IN (" . implode(',', array_keys($unifiedResult))  ."))";
		$result = $this->db->query($query);
		$this->_checkError($result);

		$output = $result->GetArray(-1);

		for ($i = 0; $i < count($output); ++$i)
		{
			for ($j = 0; $j < $result->_numOfFields; ++$j)
				unset($output[$i][$j]);
			$output[$i]['funcionario_id_desc'] = $unifiedResult[$output[$i]['funcionario_id']]['name'];
			$output[$i]['uid'] = $unifiedResult[$output[$i]['funcionario_id']]['uid'];
			$output[$i]['removed'] = $unifiedResult[$output[$i]['funcionario_id']]['removed'];
		}

		return $output;
	}


	/**
	 * Valida se o formato da data está correto..
	 * @param $date data a ser validada.
	 **/

	function validateDate($date)
	{
		$date_pattern = '/^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])\/[12][0-9]{3}$/';

		if (!preg_match($date_pattern, $date))
			$this->endExecution("Formato inválido para data (dd/mm/aaaa).");
	}

	/**
	 * Adiciona um funcionário.
	 * @param int $employeeID O ID do funcionário.
	 * @param int $organizationID O ID da organização.
	 * @param int $areaID O ID da área.
	 * @param int $costCenterID O ID do centro de custo.
	 * @param int $localID O ID da localidade.
	 * @param int $employeeStatusID O ID do status do funcionário.
	 * @param int $jobTitleID O ID do cargo do funcionário.
	 * @param int $level O nível do cargo do funcionário.
	 * @param int $title O título do funcionário.
 	 * @param int $nickname O apelido do funcionário.
 	 * @param int $jobDesc A descrição do cargo (função).
 	 * @param int $admDate Data de admissão do funcionário.
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function addEmployee($employeeID, $organizationID, $areaID, $costCenterID, $localID, $employeeStatusID, $jobTitleID, $level, $employeeCategoryID, $title, $nickname, $jobDesc, $admDate)
	{
		$this->_checkAccess($organizationID);
		if ($admDate!='')
		{
			$this->validateDate($admDate);
			$admission_date = implode('-', array_reverse(explode('/', $admDate)));
		}
		else
		{
			$admission_date=NULL;
		}

		$query = 'SELECT area.sigla FROM funcionario, area WHERE (funcionario.area_id = area.area_id) AND (funcionario.funcionario_id = ?)';
		if (($row = $this->db->query($query, $employeeID)->fetchRow()))
		{
			$errors = array(
				"O funcionário \"" . Factory::getInstance('WorkflowLDAP')->getName($employeeID) . "\" já pertença à área \"{$row['sigla']}\".",
				'-----------------',
				'Caso você queira colocá-lo na área selecionada, siga o procedimento: faça uma busca por seu nome, clique para editá-lo e, troque pela área desejada.'
			);
			$this->endExecution($errors);
		}

		$query = "INSERT INTO funcionario(funcionario_id, organizacao_id, area_id, centro_custo_id, localidade_id, funcionario_status_id, cargo_id, nivel, funcionario_categoria_id, titulo, apelido, funcao, data_admissao) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
		$result = $this->db->query($query, array($employeeID, $organizationID, $areaID, $costCenterID, $localID, $employeeStatusID, $jobTitleID, $level, $employeeCategoryID, $title, $nickname, $jobDesc, $admission_date));
		$this->_checkError($result);

		return (($result === false) ? false : true);
	}

	/**
	 * Atualiza o funcionário.
	 * @param int $employeeID O ID do funcionário.
	 * @param int $organizationID O ID da organização.
	 * @param int $areaID O ID da área.
	 * @param int $costCenterID O ID do centro de custo.
	 * @param int $localID O ID da localidade.
	 * @param int $employeeStatusID O ID do status do funcionário.
	 * @param int $jobTitleID O ID do cargo do funcionário.
	 * @param int $level O nível do cargo do funcionário.
	 * @param int $employeeCategoryID O ID da categoria do funcionário.
	 * @param int $title O título do funcionário.
 	 * @param int $nickname O apelido do funcionário.
 	 * @param int $jobDesc A descrição do cargo (função).
 	 * @param int $admDate Data de admissão do funcionário.
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function updateEmployee($employeeID, $organizationID, $areaID, $costCenterID, $localID, $employeeStatusID, $jobTitleID, $level, $employeeCategoryID, $title, $nickname, $jobDesc, $admDate)
	{
		$this->_checkAccess($organizationID);
		if ($admDate!='')
		{
			$this->validateDate($admDate);
			$admission_date = implode('-', array_reverse(explode('/', $admDate)));
		}
		else
		{
			$admission_date=NULL;
		}

		$query = "UPDATE funcionario SET area_id = ?, centro_custo_id = ?, localidade_id = ?, funcionario_status_id = ?, cargo_id = ?, nivel = ?, funcionario_categoria_id = ?, titulo = ?, apelido = ?, funcao = ?, data_admissao =? WHERE (funcionario_id = ?) AND (organizacao_id = ?)";
			
		$result = $this->db->query($query, array($areaID, $costCenterID, $localID, $employeeStatusID, $jobTitleID, $level, $employeeCategoryID, $title,$nickname, $jobDesc, $admission_date, $employeeID, $organizationID));
		
		$this->_checkError($result);
		
		return (($result === false) ? false : true);
	}

	/**
	 * Remove um funcionário.
	 * @param int $employeeID O ID do funcionário.
	 * @param int $organizationID O ID da organização.
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function removeEmployee($employeeID, $organizationID)
	{
		$this->_checkAccess($organizationID);

		$query = 'SELECT DISTINCT(a.sigla) FROM area a LEFT OUTER JOIN substituicao s USING (area_id) WHERE (? IN (a.titular_funcionario_id, s.funcionario_id, a.auxiliar_funcionario_id))';
		$areas = array();
		$resultSet = $this->db->query($query, $employeeID);
		while (($row = $resultSet->fetchRow()))
			$areas[] = $row['sigla'];
		if (count($areas) > 0)
		{
			$errors = array(
				"O funcionário \"" . Factory::getInstance('WorkflowLDAP')->getName($employeeID) . "\" é titular, substituto, já participou de substituição ou é auxiliar administrativo das seguintes áreas: " . implode(", ", $areas),
				'-----------------',
				'Se você quiser excluir este funcionário, precisa removê-lo dos "cargos" que ele possui nas áreas citadas.'
			);
			$this->endExecution($errors);
		}

		$query = "DELETE FROM funcionario WHERE (funcionario_id = ?) AND (organizacao_id = ?)";
		$result = $this->db->query($query, array($employeeID, $organizationID));
		$this->_checkError($result);

		return (($result === false) ? false : true);
	}

	/**
	 * Lista as áreas de uma organização.
	 * @param int $organizationID O ID da organização.
	 * @return array Lista das áreas de uma organização.
	 * @access public
	 */
	function getArea($organizationID, $areaID = -1)
	{
		$this->_checkAccess($organizationID);

		$output = array();
		$values = array($organizationID);

		// if we are looking for a specific area
		$area_condition = "";
		if (($areaID != -1) && !empty($areaID)) {
			$area_condition = " AND a.area_id = ? ";
			$values[]= $areaID;
		}

		$query = "SELECT a.area_id, a.centro_custo_id, a.organizacao_id, a.area_status_id, a.titular_funcionario_id, a.superior_area_id, a.sigla, a.descricao, a.ativa, a.auxiliar_funcionario_id, s.funcionario_id as substituto_funcionario_id FROM area a LEFT OUTER JOIN substituicao s ON ((a.area_id = s.area_id) AND (CURRENT_DATE BETWEEN s.data_inicio AND s.data_fim)) WHERE organizacao_id = ? " . $area_condition . " ORDER BY sigla";
		$result = $this->db->query($query, $values);
		$this->_checkError($result);

		$ldap = &Factory::getInstance('WorkflowLDAP');
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			for ($j = 0; $j < $result->_numOfFields; ++$j)
				unset($row[$j]);
			$row['substituto_funcionario_id_desc'] = ($row['substituto_funcionario_id'] != '') ? $ldap->getName($row['substituto_funcionario_id']) : '';
			$row['titular_funcionario_id_desc'] = ($row['titular_funcionario_id'] != '') ? $ldap->getName($row['titular_funcionario_id']) : '';
			$row['auxiliar_funcionario_id_desc'] = ($row['auxiliar_funcionario_id'] != '') ? $ldap->getName($row['auxiliar_funcionario_id']) : '';
			$row['superior_area_id'] = empty($row['superior_area_id']) ? 'NULL' : $row['superior_area_id'];
			$output[] = $row;
		}

		return $output;
	}

	/**
	 * Lista, hierarquicamente, as áreas de uma organização.
	 * @param int $organizationID O ID da organização.
	 * @param int $parent O ID da área superior (ou NULL para buscar todas as áreas).
	 * @param int $depth O nível hierárquico da área (profundidade do nó na árvore do Organograma).
	 * @return array Lista hierárquica das áreas de uma organização.
	 * @access public
	 */
	function getHierarchicalArea($organizationID, $parent, $depth)
	{
		$this->_checkAccess($organizationID);

		if (is_null($parent)){
			$query = "SELECT a.area_id, a.sigla, a.titular_funcionario_id FROM area a";
			$query .=" INNER JOIN area_status a_s ON (a_s.area_status_id = a.area_status_id)";
			$query .=" WHERE (a.superior_area_id IS NULL) AND (a.organizacao_id = ?) AND (a.ativa = 'S') ORDER BY a_s.nivel, a.sigla";
			$result = $this->db->query($query, array($organizationID));
		} else {
			$query = "SELECT a.area_id, a.sigla, a.titular_funcionario_id FROM area a";
			$query .=" INNER JOIN area_status a_s ON (a_s.area_status_id = a.area_status_id)";
			$query .=" WHERE (a.superior_area_id = ?) AND (a.ativa = 'S') ORDER BY a_s.nivel, a.sigla";
			$result = $this->db->query($query, array($parent));
		}

		$this->_checkError($result);

		$output = $result->GetArray(-1);

		for ($i = 0; $i < count($output); ++$i)
		{
			for ($j = 0; $j < $result->_numOfFields; ++$j)
				unset($output[$i][$j]);

			$output[$i]['children'] = $this->getHierarchicalArea($organizationID, $output[$i]['area_id'], $depth + 1);
			$output[$i]['depth'] = $depth;
		}

		return $output;
	}

	/**
	 * Adiciona uma área em uma organização.
	 * @param int $costCenterID O ID do centro de custo.
	 * @param int $organizationID O ID da organização.
	 * @param int $areaStatusID O ID do status da área.
	 * @param int $supervisorID O ID do funcionário que é superior da área.
	 * @param int $superiorAreaID O ID da área que é superior a que está sendo adicionada (NULL caso não possua área superior).
	 * @param string $acronym A sigla da área.
	 * @param string $description A descrição da área.
	 * @param char $active 'S' se a área estiver ativa e 'N' caso contrário.
	 * @param int $assistantID O ID do funcionário que está auxiliando o superior da área.
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function addArea($costCenterID, $organizationID, $areaStatusID, $supervisorID, $superiorAreaID, $acronym, $description, $active, $assistantID)
	{
		$this->_checkAccess($organizationID);

		$checkEmployees = array($supervisorID, $assistantID);
		$errors = array();
		foreach ($checkEmployees as $checkEmployee)
		{
			if (is_null($checkEmployee))
				continue;

			$query = 'SELECT 1 FROM funcionario WHERE (funcionario_id = ?)';
			if (!$this->db->query($query, $checkEmployee)->fetchRow())
				$errors[] = "O funcionário \"" . Factory::getInstance('WorkflowLDAP')->getName($checkEmployee) . "\" não está vinculado a uma área.";
		}

		if (count($errors) > 0)
		{
			$errors[] = '-----------------';
			$errors[] = 'Se você está iniciando a construção de um organograma, crie as áreas sem titulares/substitutos/auxiliares administrativos e, adicione os funcionários a elas. Só então, adicione os titulares, substitutos, etc. A razão disto, é que estes "cargos" só podem ser ocupados por pessoas que estão vinculadas a alguma área.';
			$this->endExecution($errors);
		}

		$query = "INSERT INTO area(centro_custo_id, organizacao_id, area_status_id, titular_funcionario_id, superior_area_id, sigla, descricao, ativa, auxiliar_funcionario_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
		$result = $this->db->query($query, array($costCenterID, $organizationID, $areaStatusID, $supervisorID, $superiorAreaID, $acronym, $description, $active, $assistantID));
		$this->_checkError($result);

		return (($result === false) ? false : true);
	}

	/**
	 * Atualiza a área de uma organização.
	 * @param int $costCenterID O ID do centro de custo.
	 * @param int $organizationID O ID da organização.
	 * @param int $areaStatusID O ID do status da área.
	 * @param int $supervisorID O ID do funcionário que é superior da área.
	 * @param int $superiorAreaID O ID da área que é superior a que está sendo atualizada (NULL caso não possua área superior).
	 * @param string $acronym A sigla da área.
	 * @param string $description A descrição da área.
	 * @param char $active 'S' se a área estiver ativa e 'N' caso contrário.
	 * @param int $areaID O ID da área.
	 * @param int $assistantID O ID do funcionário que está auxiliando o superior da área.
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function updateArea($costCenterID, $organizationID, $areaStatusID, $supervisorID, $superiorAreaID, $acronym, $description, $active, $assistantID, $areaID)
	{
		$this->_checkAccess($organizationID);

		$checkEmployees = array($supervisorID, $assistantID);
		$errors = array();
		foreach ($checkEmployees as $checkEmployee)
		{
			if (is_null($checkEmployee))
				continue;

			$query = 'SELECT 1 FROM funcionario WHERE (funcionario_id = ?)';
			if (!$this->db->query($query, $checkEmployee)->fetchRow())
				$errors[] = "O funcionário \"" . Factory::getInstance('WorkflowLDAP')->getName($checkEmployee) . "\" não está vinculado a uma área.";
		}

		if (count($errors) > 0)
		{
			$errors[] = '-----------------';
			$errors[] = 'Somente funcionários que estão vinculados a alguma área podem ser colocados na posição de titular ou auxiliar administrativo.';
			$this->endExecution($errors);
		}

		$query = "UPDATE area SET centro_custo_id = ?, organizacao_id = ?, area_status_id = ?, titular_funcionario_id = ?, superior_area_id = ?, sigla = ?, descricao = ?, ativa = ?, auxiliar_funcionario_id = ? WHERE (area_id = ?)";
		$result = $this->db->query($query, array($costCenterID, $organizationID, $areaStatusID, $supervisorID, $superiorAreaID, $acronym, $description, $active, $assistantID, $areaID));
		$this->_checkError($result);

		return (($result === false) ? false : true);
	}

	/**
	 * Remove a área de uma organização.
	 * @param int $areaID O ID da área.
	 * @param int $organizationID O ID da organização.
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function removeArea($areaID, $organizationID)
	{
		$this->_checkAccess($organizationID);

		$query = "DELETE FROM area WHERE (area_id = ?) AND (organizacao_id = ?)";
		$result = $this->db->query($query, array($areaID, $organizationID));
		$this->_checkError($result);

		return (($result === false) ? false : true);
	}

	/**
	 * Busca informações sobre um funcionário.
	 * @param array $params Uma array contendo o ID do funcionário cujas informações serão extraídas e de sua organização (Ajax).
	 * @param int $employeeID O ID do funcionário.
	 * @param int $organizationID O ID da organização.
	 * @return array Informações sobre o funcionário.
	 * @access public
	 */
	function getEmployeeInfo($employeeID, $organizationID)
	{
		$this->_checkAccess($organizationID, false, true);

		/**
		 * This is so wrong.. We should always use the factory to
		 * instantiate stuff. Besides, module class should not
		 * use process classes; the correct is to do the inverse.
		 */
		require_once dirname(__FILE__) . '/local/classes/class.wf_orgchart.php';
		$orgchart = new wf_orgchart();

		$outputInfo = array();

		$cachedLDAP = Factory::newInstance('CachedLDAP');
		$cachedLDAP->setOperationMode($cachedLDAP->OPERATION_MODE_LDAP_DATABASE);

		/* here we need fresh information. Let's access ldap first */
		$employeeEntry = $cachedLDAP->getEntryByID($employeeID);

		if ($entry === false)
			return array('error' => 'Funcionário não encontrado.');

		$employeeInfo				= $orgchart->getEmployee($employeeID);
		$employeeStatusInfo			= $orgchart->getEmployeeStatus($employeeInfo['funcionario_status_id']);
		$account_id					= $_SESSION['phpgw_info']['workflow']['account_id'];

		$mobile 	= '';
		$homePhone  = '';
		
		/*
		 * Check if the current user can view the mobile and homePhone of the employee
		 * This condition is true if the current user is the same user that's being retrieved
		 */
		$authorized = $this->acl->checkUserGroupAccessToResource('ORG', $account_id, (int) $organizationID, 1); 
		if (($account_id == $employeeID) || ($authorized)) {
			$mobile 	= $employeeEntry['mobile'];
			$homePhone 	= $employeeEntry['homephone'];
		}

		$outputInfo[] = array(
			'name' => 'Mobile',
			'value' => ( ! empty( $mobile ) ? $mobile : '' ) );

		$outputInfo[] = array(
			'name' => 'homePhone',
			'value' => ( ! empty( $homePhone ) ? $homePhone : '' ) );


		$outputInfo[] = array(
			'name' => 'Nome',
			'value' => $employeeEntry['cn']);

		$outputInfo[] = array(
			'name' => 'Telefone',
			'value' => ( ! empty( $employeeEntry['telephonenumber'] ) ? $employeeEntry['telephonenumber'] : '' ) );

		if (!empty($employeeEntry['employeenumber']))
		{
			$outputInfo[] = array(
				'name' => 'Matrícula',
				'value' => $employeeEntry['employeenumber']);
		}

		$outputInfo[] = array(
			'name' => 'UIDNumber',
			'value' => $employeeID);

		$outputInfo[] = array(
			'name' => 'Status',
			'value' => $employeeStatusInfo['descricao']);

		if (!empty($employeeInfo['funcionario_categoria_id']))
		{
			$categoryInfo = $orgchart->getEmployeeCategory($employeeInfo['funcionario_categoria_id']);
			$outputInfo[] = array(
				'name' => 'Vínculo',
				'value' => $categoryInfo['descricao']);
		}

		$titulo = NULL;
		if ( !empty( $employeeInfo['titulo'] ) )
		{
			$titulo = $employeeInfo['titulo'];
		}

		$outputInfo[] = array(
			'name' => 'Título',
			'value' => ( $titulo ? $titulo : '' )
		);

		$cargo = NULL;
		if ( !empty($employeeInfo['cargo_id']) )
		{
			$jobTitleInfo = $orgchart->getJobTitle($employeeInfo['cargo_id']);
			$cargo = $jobTitleInfo['descricao'];
		}

		$outputInfo[] = array(
			'name' => 'Cargo',
			'value' => ( $cargo ? $cargo : '' )
		);

		$nivel = NULL;
		if ( !empty($employeeInfo['nivel']) )
		{
			$nivel = $employeeInfo['nivel'];
		}

		$outputInfo[] = array(
			'name' => 'Nível',
			'value' => ( $nivel ? $nivel : '' )
		);

		$areaInfo = $orgchart->getArea($employeeInfo['area_id']);
		$outputInfo[] = array(
			'name' => 'Área',
			'value' => $areaInfo['sigla']);

		$outputInfo[] = array(
			'name' => 'ÁreaID',
			'value' => $employeeInfo['area_id']);

		$localInfo = $orgchart->getLocal($employeeInfo['localidade_id']);
		$outputInfo[] = array(
			'name' => 'Localidade',
			'value' => $localInfo['descricao']);

		$outputInfo[] = array(
			'name' => 'Empresa',
			'value' => ( ! empty( $localInfo['empresa'] ) ? $localInfo['empresa'] : '') );;

		$outputInfo[] = array(
			'name' => 'Endereço',
			'value' => ( ! empty( $localInfo['endereco'] ) ? $localInfo['endereco'] : '') );

		$outputInfo[] = array(
			'name' => 'Complemento',
			'value' => ( ! empty( $localInfo['complemento'] ) ? $localInfo['complemento'] : '') );;

		$outputInfo[] = array(
			'name' => 'Cep',
			'value' => ( ! empty( $localInfo['cep'] ) ? $localInfo['cep'] : '') );

		$outputInfo[] = array(
			'name' => 'Bairro',
			'value' => ( ! empty( $localInfo['bairro'] ) ? $localInfo['bairro'] : '') );

		$outputInfo[] = array(
			'name' => 'Cidade',
			'value' => ( ! empty( $localInfo['cidade'] ) ? $localInfo['cidade'] : '') );

		$outputInfo[] = array(
			'name' => 'UF',
			'value' => ( ! empty( $localInfo['uf'] ) ? $localInfo['uf'] : '') );

		if (!empty($employeeInfo['centro_custo_id']))
			$costCenterInfo = $orgchart->getCostCenter($employeeInfo['centro_custo_id']);
		else
			$costCenterInfo = $orgchart->getCostCenter($areaInfo['centro_custo_id']);
		$outputInfo[] = array(
			'name' => 'Centro de Custo',
			'value' => $costCenterInfo['descricao']);

		$outputInfo[] = array(
			'name' => 'e-mail',
			'value' => $employeeEntry['mail']);

		$organizationInfo = $orgchart->getOrganization( $employeeInfo['organizacao_id'] );

		$outputInfo[] = array(
			'name' => 'sitio',
			'value' => $organizationInfo['sitio']);

		return array('info' => $outputInfo);
	}

	/**
	 * Busca informações sobre uma área.
	 * @param array $params Uma array contendo o ID da área cujas informações serão extraídas e de sua organização (Ajax).
	 * @param int $areaID O ID da área.
	 * @param int $organizationID O ID da organização.
	 * @return array Informações sobre o funcionário.
	 * @access public
	 */
	function getAreaInfo($areaID, $organizationID)
	{
		$this->_checkAccess($organizationID, false, true);

		$areaID = (int) $areaID;
		$organizationID = (int) $organizationID;

		require_once dirname(__FILE__) . '/local/classes/class.wf_orgchart.php';
		$orgchart = new wf_orgchart();

		$outputInfo = array();
		$areaInfo = $orgchart->getArea($areaID);

		$outputInfo[] = array(
			'name' => 'Nome',
			'value' => $areaInfo['descricao']
		);

		$cachedLDAP = Factory::newInstance('CachedLDAP');
		$cachedLDAP->setOperationMode($cachedLDAP->OPERATION_MODE_LDAP_DATABASE);
		if (!empty($areaInfo['titular_funcionario_id']))
		{
			$employeeInfo = $cachedLDAP->getEntryByID($areaInfo['titular_funcionario_id']);
			$outputInfo[] = array(
				'name' => 'Titular',
				'value' => $employeeInfo['cn']
			);
		}

		if (!empty($areaInfo['substituto_funcionario_id']))
		{
			$employeeInfo = $cachedLDAP->getEntryByID($areaInfo['substituto_funcionario_id']);
			$outputInfo[] = array(
				'name' => 'Substituto',
				'value' => $employeeInfo['cn']
			);
		}

		if (!empty($areaInfo['auxiliar_funcionario_id']))
		{
			$employeeInfo = $cachedLDAP->getEntryByID($areaInfo['auxiliar_funcionario_id']);
			$outputInfo[] = array(
				'name' => 'Auxiliar Administrativo',
				'value' => $employeeInfo['cn']
			);
		}

		$outputInfo[] = array(
			'name' => 'No. de Funcionários',
			'value' => $this->db->GetOne("SELECT COUNT(*) FROM funcionario f, funcionario_status s WHERE (s.funcionario_status_id = f.funcionario_status_id) AND (s.exibir = 'S') AND (f.area_id = ?) AND (f.organizacao_id = ?)", array($areaID, $organizationID))
		);

		return array('info' => $outputInfo);
	}

	/**
	 * Lista todos os telefones da organização.
	 * @return array Lista de telefones da organização.
	 * @access public
	 */
	function getTelephones( $organizationID )
	{
		$this -> _checkAccess( $organizationID );

		$query = "SELECT organizacao_id, telefone_id, descricao, numero FROM telefone WHERE organizacao_id = ? ORDER BY descricao";
		$result = $this -> db -> query( $query, array( $organizationID ) );
		$this -> _checkError( $result );

		$output = $result->GetArray(-1);

		for ($i = 0; $i < count($output); ++$i)
			for ($j = 0; $j < $result->_numOfFields; ++$j)
				unset($output[$i][$j]);

		return $output;
	}

	/**
	 * Adiciona um telefone a uma organização.
	 * @param int $organizationID O ID da organização.
	 * @param string $description A descrição da localidade.
	 * @param string $number String com os números de telefones.
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function addTelephone( $organizationID, $description, $number )
	{
		$this->_checkAccess( $organizationID );

		$query = "INSERT INTO telefone( organizacao_id, descricao, numero ) VALUES( ?, ?, ? )";
		$result = $this -> db -> query( $query, array( $organizationID, $description, $number ) );
		$this -> _checkError( $result );

		return (($result === false) ? false : true);
	}

	/**
	 * Remove um telefone.
	 * @param int $organizationID O ID da organização.
	 * @param int $telephoneID O ID do telefone.
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function removeTelephone( $organizationID, $telephoneID )
	{
		$this->_checkAccess( $organizationID );

		$query = "DELETE FROM telefone WHERE (telefone_id = ?) AND (organizacao_id = ?)";
		$result = $this -> db -> query( $query, array( $telephoneID, $organizationID ) );
		$this -> _checkError( $result );

		return ( ( $result === false ) ? false : true );
	}

	/**
	 * Atualiza um telefone.
	 * @param int $organizationID O ID da organização.
	 * @param int $telephoneID O ID do telefone.
	 * @param string $description A descrição do telefone.
	 * @param string $number String com os números de telefones.
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function updateTelephone( $organizationID, $telephoneID, $description, $number )
	{
		$this->_checkAccess( $organizationID );

		$query = "UPDATE telefone SET descricao = ?, numero = ? WHERE (telefone_id = ?)";
		$result = $this -> db -> query( $query, array( $description, $number, $telephoneID ) );
		$this->_checkError( $result );

		return ( ( $result === false ) ? false : true );
	}

	/**
	 * Validate start and end dates for a substitution
	 * @param int $areaID Area's ID.
	 * @param string $date_start Substitution's start date.
	 * @param string $date_start Substitution's end date.
	 * @return bool
	 * @access private
	 */
	function validateSubstitutionDates($areaID, $date_start, $date_end, $substitutionID = -1)
	{
		/* TODO
		* I'm not supose to be here.. (date validations speaking)
		* move me to some validation class!
		*/

		/* validating dates */
		$date_pattern = '/^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])\/[12][0-9]{3}$/';

		if (!preg_match($date_pattern, $date_start))
			$this->endExecution("Formato inválido para data de início.");
		if (!preg_match($date_pattern, $date_end))
			$this->endExecution("Formato inválido para data de término. ");

		$date_start_arr = explode('/', $date_start);
		$date_end_arr = explode('/', $date_end);

		/* is it a gregorian date? */
		if (!checkdate($date_start_arr[1], $date_start_arr[0], $date_start_arr[2]))
			$this->endExecution("Data de início inválida.");
		if (!checkdate($date_end_arr[1], $date_end_arr[0], $date_end_arr[2]))
			$this->endExecution("Data de término inválida. ");

		/* is date_end greater then date_start? */
		if (mktime(0,0,0, $date_start_arr[1], $date_start_arr[0], $date_start_arr[2]) >= mktime(0,0,0, $date_end_arr[1], $date_end_arr[0], $date_end_arr[2]))
			$this->endExecution("A data de término deve ser maior que a data de início.");

		/* preparing dates to database */
		$date_start = implode('-', array_reverse($date_start_arr));
		$date_end = implode('-', array_reverse($date_end_arr));

		/* checking if there is a substitution in conflict with these dates */
		$query  = "SELECT * FROM substituicao WHERE ";
		$query .= "	area_id = ? ";
		$query .= " AND ";
		$query .= " 	substituicao_id != ? ";
		$query .= " AND ";
		$query .= " 	(";
		$query .= "		(? BETWEEN data_inicio AND data_fim) ";
		$query .= "	OR ";
		$query .= " 		(? BETWEEN data_inicio AND data_fim)";
		$query .= "	OR ";
		$query .= " 		(data_inicio BETWEEN ? AND ?)";
		$query .= "	) ";

		// raise an error if there is any record
		if ($row = $this->db->query($query, array( $areaID, $substitutionID, $date_start, $date_end, $date_start, $date_end ))->fetchRow())
		{
			$row['data_inicio'] = implode('/', array_reverse(explode('-', $row['data_inicio'])));
			$row['data_fim'] = implode('/', array_reverse(explode('-', $row['data_fim'])));
			$this->endExecution('Já existe uma substituição no período de '. $row['data_inicio'] . ' a ' . $row['data_fim']);
		}
		return true;
	}

	/**
	 * Add a substitution.
	 * @param int $organizationID Organization's ID.
	 * @param int $areaID Area's ID.
	 * @param int $substituteID Substitute's employee ID.
	 * @param string $date_start Substitution's start date.
	 * @param string $date_start Substitution's end date.
	 * @return bool
	 * @access public
	 */
	function addSubstitution( $organizationID, $areaID, $substituteID, $date_start, $date_end )
	{
		$this->_checkAccess( $organizationID );

		if (!$this->validateSubstitutionDates($areaID, $date_start, $date_end))
			return false;

		/* formating dates */
		$date_start = implode('-', array_reverse(explode('/', $date_start)));
		$date_end = implode('-', array_reverse(explode('/', $date_end)));

		$query = "INSERT INTO substituicao (area_id, funcionario_id, data_inicio, data_fim) VALUES (?, ?, ?, ?)";
		$result = $this -> db -> query( $query, array( $areaID, $substituteID, $date_start, $date_end ) );
		$this->_checkError( $result );

		return ( ( $result === false ) ? false : true );
	}

	/**
	 * Update a substitution.
	 * @param int $organizationID Organization's ID.
	 * @param int $areaID Area's ID.
	 * @param int $substituteID Substitute's employee ID.
	 * @param string $date_start Substitution's start date.
	 * @param string $date_start Substitution's end date.
	 * @return bool
	 * @access public
	 */
	function updateSubstitution( $organizationID, $areaID, $substituteID, $date_start, $date_end, $substitutionID )
	{
		$this->_checkAccess( $organizationID );

		if (!$this->validateSubstitutionDates($areaID, $date_start, $date_end, $substitutionID))
			return false;

		/* formating dates */
		$date_start = implode('-', array_reverse(explode('/', $date_start)));
		$date_end = implode('-', array_reverse(explode('/', $date_end)));

		$query = "UPDATE substituicao SET funcionario_id = ?, data_inicio = ?, data_fim = ? WHERE substituicao_id = ?";
		$result = $this -> db -> query( $query, array( $substituteID, $date_start, $date_end, $substitutionID ) );
		$this->_checkError( $result );

		return ( ( $result === false ) ? false : true );
	}

	/**
	 * List all the substituions for a given area
	 * @return array List of the substitutions
	 * @access public
	 */
	function getSubstitutions( $organizationID, $areaID )
	{
		$this -> _checkAccess( $organizationID );

		/* we must join area table to get organizacao_id */
		$query = "SELECT s.*, a.organizacao_id FROM substituicao s INNER JOIN area a USING(area_id) WHERE area_id = ? ORDER BY data_inicio DESC";
		$result = $this -> db -> query( $query, array( $areaID ) );
		$this -> _checkError( $result );

		/* we must query ldap to get full user names. In workflow db we just store uids */
		$cachedLDAP = Factory::getInstance('CachedLDAP');

		$output = $result->GetArray(-1);

		for ($i = 0; $i < count($output); ++$i) {
			for ($j = 0; $j < $result->_numOfFields; ++$j)
				unset($output[$i][$j]);

			/* including substitute full name */
			$ldap_result = $cachedLDAP->getEntryByID($output[$i]['funcionario_id']);
			$output[$i]['substituto_funcionario_id'] = $output[$i]['funcionario_id'];
			$output[$i]['substituto_funcionario_id_desc'] = $ldap_result['cn'];

			/* formating dates */
			$output[$i]['data_inicio'] = implode('/', array_reverse(explode('-', $output[$i]['data_inicio'])));
			$output[$i]['data_fim'] = implode('/', array_reverse(explode('-', $output[$i]['data_fim'])));
		}
		return $output;
	}

	/**
	 * Remove a substitution
	 * @param int $organizationID Organization's ID
	 * @param int $telephoneID Substitution's ID
	 * @return bool
	 * @access public
	 */
	function removeSubstitution( $organizationID, $substitutionID )
	{
		$this->_checkAccess( $organizationID );

		$query = "DELETE FROM substituicao WHERE substituicao_id = ?";
		$result = $this -> db -> query( $query, array( $substitutionID ) );
		$this -> _checkError( $result );

		return ( ( $result === false ) ? false : true );
	}
}
?>
