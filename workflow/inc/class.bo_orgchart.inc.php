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

require_once('class.bo_ajaxinterface.inc.php');
/**
 * Camada Business do Organograma.
 * @package Workflow
 * @author Sidnei Augusto Drovetto Jr. - drovetto@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 */
class bo_orgchart extends bo_ajaxinterface
{
	/**
	 * @var object $so Acesso à camada Model do Organograma.
	 * @access private
	 */
	private $so;

	/**
	 * Verifica se o valor de uma variável pode ser considerado NULL. Se sim, retorna NULL caso contrário retorna o próprio valor passado.
	 * @param mixed $value O valor que será verificado.
	 * @return mixed Será retornado o valor passado no parâmetro ou NULL.
	 * @access private
	 */
	private function _nullReplace($value)
	{
		if (($value == 'NULL') || ($value == ''))
			return null;
		else
			return $value;
	}
	/**
	 * Construtor da classe bo_orgchart
	 * @return object
	 * @access public
	 */
	function bo_orgchart()
	{
		parent::bo_ajaxinterface();
		$this->so = &Factory::getInstance('so_orgchart');
	}

	/**
	 * Lista todas as organizações do Organograma.
	 * @return array Lista de organizações.
	 * @access public
	 */
	function listOrganization()
	{
		$result = $this->so->getOrganizations();
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Adiciona uma organização.
	 * @param array $params Uma array contendo os parâmetros necessários para adicionar uma organização (Ajax).
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function addOrganization($params)
	{
		$result = $this->so->addOrganization($params['nome'], $params['descricao'], $params['url_imagem'], $params['ativa'], $params['sitio']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Atualiza uma organização.
	 * @param array $params Uma array contendo os parâmetros da organização que serão modificados (Ajax).
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function updateOrganization($params)
	{
		$result = $this->so->updateOrganization($params['nome'], $params['descricao'], $params['url_imagem'], $params['ativa'], $params['organizacao_id'], $params['sitio']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Remove uma organização.
	 * @param array $params Uma array contendo o ID da organização que será excluída (Ajax).
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function removeOrganization($params)
	{
		$result = $this->so->removeOrganization($params['organizacao_id']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Lista os possíveis status dos funcionários.
	 * @param array $params Uma array contendo o ID da organização de onde os status dos empregados serão listados (Ajax).
	 * @return array Lista dos possíveis status dos empregados.
	 * @access public
	 */
	function listEmployeeStatus($params)
	{
		$result = $this->so->getEmployeeStatus($params['organizacao_id']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Adiciona um Status de funcionário.
	 * @param array $params Uma array contendo os parâmetros necessários para adicionar um status de funcionário (Ajax).
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function addEmployeeStatus($params)
	{
		$result = $this->so->addEmployeeStatus($params['organizacao_id'], $params['descricao'], $params['exibir']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Atualiza um Status de funcionário.
	 * @param array $params Uma array contendo os parâmetros do status de funcionário que podem ser alterados (Ajax).
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function updateEmployeeStatus($params)
	{
		$result = $this->so->updateEmployeeStatus($params['funcionario_status_id'], $params['organizacao_id'], $params['descricao'], $params['exibir']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Remove um Status de funcionário.
	 * @param array $params Uma array contendo a organização e o ID do status de funcionário que será excluído (Ajax).
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function removeEmployeeStatus($params)
	{
		$result = $this->so->removeEmployeeStatus($params['funcionario_status_id'], $params['organizacao_id']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Lista as possíveis categorias de uma organização.
	 * @param array $params Uma array contendo o ID da organização de onde as categorias serão listadas (Ajax).
	 * @return array Lista dos possíveis categorias.
	 * @access public
	 */
	function listEmployeeCategory($params)
	{
		$result = $this->so->getEmployeeCategory($params['organizacao_id']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Adiciona uma categoria.
	 * @param array $params Uma array contendo os parâmetros necessários para adicionar uma categoria (Ajax).
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function addEmployeeCategory($params)
	{
		$result = $this->so->addEmployeeCategory($params['organizacao_id'], $params['descricao']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Atualiza uma categoria.
	 * @param array $params Uma array contendo os parâmetros da categoria que podem ser alterados (Ajax).
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function updateEmployeeCategory($params)
	{
		$result = $this->so->updateEmployeeCategory($params['funcionario_categoria_id'], $params['organizacao_id'], $params['descricao']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Remove uma categoria.
	 * @param array $params Uma array contendo o ID da categoria que será excluída e de sua organização (Ajax).
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function removeEmployeeCategory($params)
	{
		$result = $this->so->removeEmployeeCategory($params['funcionario_categoria_id'], $params['organizacao_id']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Lista os possíveis cargos de uma organização.
	 * @param array $params Uma array contendo o ID da organização de onde os cargos serão listados (Ajax).
	 * @return array Lista dos possíveis cargos.
	 * @access public
	 */
	function listJobTitle($params)
	{
		$result = $this->so->getJobTitle($params['organizacao_id']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Adiciona um cargo.
	 * @param array $params Uma array contendo os parâmetros necessários para adicionar um cargo (Ajax).
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function addJobTitle($params)
	{
		$result = $this->so->addJobTitle($params['organizacao_id'], $params['descricao']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Atualiza um cargo.
	 * @param array $params Uma array contendo os parâmetros do cargo que podem ser alterados (Ajax).
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function updateJobTitle($params)
	{
		$result = $this->so->updateJobTitle($params['cargo_id'], $params['organizacao_id'], $params['descricao']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Remove um cargo.
	 * @param array $params Uma array contendo o ID do cargo que será excluído e de sua organização (Ajax).
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function removeJobTitle($params)
	{
		$result = $this->so->removeJobTitle($params['cargo_id'], $params['organizacao_id']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Lista os possíveis status de área.
	 * @param array $params Uma array contendo o ID da organização de onde os status de área serão listados (Ajax).
	 * @return array Lista dos possíveis status de área.
	 * @access public
	 */
	function listAreaStatus($params)
	{
		$result = $this->so->getAreaStatus($params['organizacao_id']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Adiciona um status de área.
	 * @param array $params Uma array contendo os parâmetros necessários para adicionar um status de área (Ajax).
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function addAreaStatus($params)
	{
		$result = $this->so->addAreaStatus($params['organizacao_id'], $params['descricao'], $params['nivel']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Atualiza um status de área.
	 * @param array $params Uma array contendo os parâmetros do status de área que podem ser alterados (Ajax).
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function updateAreaStatus($params)
	{
		$result = $this->so->updateAreaStatus($params['area_status_id'], $params['organizacao_id'], $params['descricao'], $params['nivel']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Remove um status de área.
	 * @param array $params Uma array contendo o ID do status de área que será excluído e de sua organização (Ajax).
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function removeAreaStatus($params)
	{
		$result = $this->so->removeAreaStatus($params['area_status_id'], $params['organizacao_id']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Lista os centros de custo de uma organização.
	 * @param array $params Uma array contendo o ID da organização dos centros de custo (Ajax).
	 * @return array Lista dos centros de custo de uma organização.
	 * @access public
	 */
	function listCostCenter($params)
	{
		$result = $this->so->getCostCenter($params['organizacao_id']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Adiciona um centro de custo.
	 * @param array $params Uma array contendo os parâmetros necessários para adicionar um centro de custo (Ajax).
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function addCostCenter($params)
	{
		$result = $this->so->addCostCenter($params['organizacao_id'], $params['nm_centro_custo'], $params['descricao'], $params['grupo']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Atualiza um centro de custo.
	 * @param array $params Uma array contendo os parâmetros do centro de custo que podem ser alterados (Ajax).
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function updateCostCenter($params)
	{
		$result = $this->so->updateCostCenter($params['organizacao_id'], $params['nm_centro_custo'], $params['descricao'], $params['grupo'], $params['centro_custo_id']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Remove um centro de custo.
	 * @param array $params Uma array contendo o ID do centro de custo que será excluído e de sua organização (Ajax).
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function removeCostCenter($params)
	{
		$result = $this->so->removeCostCenter($params['centro_custo_id'], $params['organizacao_id']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Lista as localidades de uma organização.
	 * @param array $params Uma array contendo o ID da organização das localidades (Ajax).
	 * @return array Lista das localidades de uma organização.
	 * @access public
	 */
	function listLocal($params)
	{
		$result = $this->so->getLocal($params['organizacao_id']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Adiciona uma localidade.
	 * @param array $params Uma array contendo os parâmetros necessários para adicionar uma localidade (Ajax).
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function addLocal($params)
	{
		extract( $params );
		$result = $this->so->addLocal($organizacao_id, $this->_nullReplace($centro_custo_id), $descricao, $empresa, $endereco, $complemento, $cep, $bairro, $cidade, $uf, $externa );
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Atualiza uma localidade.
	 * @param array $params Uma array contendo os parâmetros da localidade que podem ser alterados (Ajax).
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function updateLocal($params)
	{
		extract( $params );
		$result = $this->so->updateLocal($organizacao_id, $this->_nullReplace($centro_custo_id), $descricao, $localidade_id, $empresa, $endereco, $complemento, $cep, $bairro, $cidade, $uf, $externa );
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Remove uma localidade.
	 * @param array $params Uma array contendo o ID da localidade que será excluída e de sua organização (Ajax).
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function removeLocal($params)
	{
		$result = $this->so->removeLocal($params['localidade_id'], $params['organizacao_id']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Lista os funcionários de uma área.
	 * @param array $params Uma array contendo o ID da organização dos funcionários e de uma área desta organização (Ajax).
	 * @return array Lista dos funcionários de uma área.
	 * @access public
	 */
	function listAreaEmployee($params)
	{
		$result = $this->so->getAreaEmployee($params['area_id'], $params['organizacao_id']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Lista de funcionários que satisfazem um determinados critério de busca.
	 * @param array $params Uma array contendo o ID da organização dos funcionários e o critério de busca (Ajax).
	 * @return array Lista das localidades de uma organização.
	 * @access public
	 */
	function searchEmployee($params)
	{
		if (!preg_match('/^([[:alnum:] ]+)$/', $params['search_term']))
			die(serialize("Parametro de busca inválido"));
		$result = $this->so->searchEmployee($params['search_term'], $params['organizacao_id']);
		$this->disconnect_all();

		usort($result, create_function('$a,$b', 'return strcasecmp($a[\'funcionario_id_desc\'],$b[\'funcionario_id_desc\']);'));
		return $result;
	}

	/**
	 * Adiciona um funcionário.
	 * @param array $params Uma array contendo os parâmetros necessários para adicionar um funcionário (Ajax).
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function addEmployee($params)
	{
		$result = $this->so->addEmployee($params['funcionario_id'], $params['organizacao_id'], $params['area_id'], $this->_nullReplace($params['centro_custo_id']), $params['localidade_id'], $params['funcionario_status_id'], $this->_nullReplace($params['cargo_id']), $this->_nullReplace($params['nivel']), $this->_nullReplace($params['funcionario_categoria_id']), $params['titulo'], $params['apelido'], $params['funcao'], $params['data_admissao']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Atualiza funcionário.
	 * @param array $params Uma array contendo os parâmetros do funcionário que podem ser alterados (Ajax).
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function updateEmployee($params)
	{
		$result = $this->so->updateEmployee($params['funcionario_id'], $params['organizacao_id'], $params['area_id'], $this->_nullReplace($params['centro_custo_id']), $params['localidade_id'], $params['funcionario_status_id'], $this->_nullReplace($params['cargo_id']), $this->_nullReplace($params['nivel']), $this->_nullReplace($params['funcionario_categoria_id']), $params['titulo'], $params['apelido'], $params['funcao'], $params['data_admissao']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Remove um funcionário.
	 * @param array $params Uma array contendo o ID do funcionário que será excluído e de sua organização (Ajax).
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function removeEmployee($params)
	{
		$result = $this->so->removeEmployee($params['funcionario_id'], $params['organizacao_id']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Lista as áreas de uma organização.
	 * @param array $params Uma array contendo o ID da organização das áreas (Ajax).
	 * @return array Lista das áreas de uma organização.
	 * @access public
	 */
	function listArea($params)
	{
		$result = $this->so->getArea($params['organizacao_id'], $params['area_id']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Lista, hierarquicamente, as áreas de uma organização.
	 * @param array $params Uma array contendo o ID da organização das áreas (Ajax).
	 * @return array Lista hierárquica das áreas de uma organização.
	 * @access public
	 */
	function listHierarchicalArea($params)
	{
		$result = $this->so->getHierarchicalArea($params['organizacao_id'], null, 0);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Adiciona uma área.
	 * @param array $params Uma array contendo os parâmetros necessários para adicionar uma área (Ajax).
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function addArea($params)
	{
		$result = $this->so->addArea($params['centro_custo_id'], $params['organizacao_id'], $params['area_status_id'], $this->_nullReplace($params['titular_funcionario_id']), $this->_nullReplace($params['superior_area_id']), $params['sigla'], $params['descricao'], $params['ativa'], $this->_nullReplace($params['auxiliar_funcionario_id']));
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Atualiza uma área.
	 * @param array $params Uma array contendo os parâmetros da área que podem ser alterados (Ajax).
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function updateArea($params)
	{
		$result = $this->so->updateArea($params['centro_custo_id'], $params['organizacao_id'], $params['area_status_id'], $this->_nullReplace($params['titular_funcionario_id']), $this->_nullReplace($params['superior_area_id']), $params['sigla'], $params['descricao'], $params['ativa'], $this->_nullReplace($params['auxiliar_funcionario_id']), $params['area_id']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Remove uma área.
	 * @param array $params Uma array contendo o ID da área que será excluído e de sua organização (Ajax).
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function removeArea($params)
	{
		$result = $this->so->removeArea($params['area_id'], $params['organizacao_id']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Busca informações sobre um funcionário.
	 * @param array $params Uma array contendo o ID do funcionário cujas informações serão extraídas e de sua organização (Ajax).
	 * @return array Informações sobre o funcionário.
	 * @access public
	 */
	function getEmployeeInfo($params)
	{
		$result = $this->so->getEmployeeInfo((int) $params['funcionario_id'], (int) $params['organizacao_id']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Busca informações sobre uma área.
	 * @param array $params Uma array contendo o ID da área cujas informações serão extraídas e de sua organização (Ajax).
	 * @return array Informações sobre a área.
	 * @access public
	 */
	function getAreaInfo($params)
	{
		$result = $this->so->getAreaInfo((int) $params['area_id'], (int) $params['organizacao_id']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Lista todos os telefones da organização.
	 * @param array $params Uma array contendo o ID da organização de onde os telefones serão listados (Ajax).
	 * @return array Lista de telefones da organização.
	 * @access public
	 */
	function listTelephones( $params )
	{
		$result = $this -> so -> getTelephones( $params[ 'organizacao_id' ] );
		$this -> disconnect_all( );

		return $result;
	}

	/**
	 * Adiciona um telefone a uma organização.
	 * @param array $params Uma array contendo os parâmetros necessários para adicionar um telefone (Ajax).
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function addTelephones( $params )
	{
		$result = $this -> so -> addTelephone( $params[ 'organizacao_id' ], $params[ 'descricao' ], $params[ 'numero' ] );
		$this->disconnect_all( );

		return $result;
	}

	/**
	 * Atualiza um telefone de uma organização.
	 * @param array $params Uma array contendo os parâmetros de telefone da organização que podem ser alterados (Ajax).
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function updateTelephones( $params )
	{
		$result = $this -> so -> updateTelephone( $params[ 'organizacao_id' ], $params[ 'telefone_id' ], $params[ 'descricao' ], $params[ 'numero' ] );
		$this->disconnect_all( );

		return $result;
	}

	/**
	 * Remove um telefone de uma organização.
	 * @param array $params Uma array contendo o ID do telefone que será excluído e de sua organização (Ajax).
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário.
	 * @access public
	 */
	function removeTelephones( $params )
	{
		$result = $this -> so -> removeTelephone( $params[ 'organizacao_id' ], $params[ 'telefone_id' ] );
		$this->disconnect_all( );

		return $result;
	}

	/**
	 * Add a substitution to an specific area
	 * @param array $params An array filled by the substitution parameters
	 * @return bool TRUE if we are successfull, FALSE otherwise
	 * @access public
	 */
	function addSubstitution( $params )
	{
		$result = $this -> so -> addSubstitution( $params[ 'organizacao_id' ], $params[ 'area_id' ], $params[ 'substituto_funcionario_id' ], $params[ 'data_inicio' ], $params[ 'data_fim' ] );
		$this->disconnect_all( );

		return $result;
	}

	/**
	 * Update a substitution
	 * @param array $params An array filled by the substitution parameters
	 * @return bool TRUE if we are successfull, FALSE otherwise
	 * @access public
	 */
	function updateSubstitution( $params )
	{
		$result = $this -> so -> updateSubstitution( $params[ 'organizacao_id' ], $params[ 'area_id' ], $params[ 'substituto_funcionario_id' ], $params[ 'data_inicio' ], $params[ 'data_fim' ], $params['substituicao_id'] );
		$this->disconnect_all( );

		return $result;
	}

	/**
	 * List the substitutions for a given area.
	 * @param array $params An array containing the areaID
	 * @return array Substitution's list
	 * @access public
	 */
	function listSubstitution( $params )
	{
		$result = $this -> so -> getSubstitutions( $params['organizacao_id'], $params[ 'area_id' ] );
		$this -> disconnect_all( );

		return $result;
	}

	/**
	 * Remove a substitution
	 * @param array $params An array containing a substitutionID
	 * @return bool
	 * @access public
	 */
	function removeSubstitution( $params )
	{
		$result = $this -> so -> removeSubstitution( $params['organizacao_id'], $params[ 'substituicao_id' ] );
		$this -> disconnect_all( );

		return $result;
	}
}
?>
