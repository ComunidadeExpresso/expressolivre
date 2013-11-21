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
	 * @var object $so Acesso � camada Model do Organograma.
	 * @access private
	 */
	private $so;

	/**
	 * Verifica se o valor de uma vari�vel pode ser considerado NULL. Se sim, retorna NULL caso contr�rio retorna o pr�prio valor passado.
	 * @param mixed $value O valor que ser� verificado.
	 * @return mixed Ser� retornado o valor passado no par�metro ou NULL.
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
	 * Lista todas as organiza��es do Organograma.
	 * @return array Lista de organiza��es.
	 * @access public
	 */
	function listOrganization()
	{
		$result = $this->so->getOrganizations();
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Adiciona uma organiza��o.
	 * @param array $params Uma array contendo os par�metros necess�rios para adicionar uma organiza��o (Ajax).
	 * @return bool TRUE se a a��o foi conclu�da com �xito e FALSE caso contr�rio.
	 * @access public
	 */
	function addOrganization($params)
	{
		$result = $this->so->addOrganization($params['nome'], $params['descricao'], $params['url_imagem'], $params['ativa'], $params['sitio']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Atualiza uma organiza��o.
	 * @param array $params Uma array contendo os par�metros da organiza��o que ser�o modificados (Ajax).
	 * @return bool TRUE se a a��o foi conclu�da com �xito e FALSE caso contr�rio.
	 * @access public
	 */
	function updateOrganization($params)
	{
		$result = $this->so->updateOrganization($params['nome'], $params['descricao'], $params['url_imagem'], $params['ativa'], $params['organizacao_id'], $params['sitio']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Remove uma organiza��o.
	 * @param array $params Uma array contendo o ID da organiza��o que ser� exclu�da (Ajax).
	 * @return bool TRUE se a a��o foi conclu�da com �xito e FALSE caso contr�rio.
	 * @access public
	 */
	function removeOrganization($params)
	{
		$result = $this->so->removeOrganization($params['organizacao_id']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Lista os poss�veis status dos funcion�rios.
	 * @param array $params Uma array contendo o ID da organiza��o de onde os status dos empregados ser�o listados (Ajax).
	 * @return array Lista dos poss�veis status dos empregados.
	 * @access public
	 */
	function listEmployeeStatus($params)
	{
		$result = $this->so->getEmployeeStatus($params['organizacao_id']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Adiciona um Status de funcion�rio.
	 * @param array $params Uma array contendo os par�metros necess�rios para adicionar um status de funcion�rio (Ajax).
	 * @return bool TRUE se a a��o foi conclu�da com �xito e FALSE caso contr�rio.
	 * @access public
	 */
	function addEmployeeStatus($params)
	{
		$result = $this->so->addEmployeeStatus($params['organizacao_id'], $params['descricao'], $params['exibir']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Atualiza um Status de funcion�rio.
	 * @param array $params Uma array contendo os par�metros do status de funcion�rio que podem ser alterados (Ajax).
	 * @return bool TRUE se a a��o foi conclu�da com �xito e FALSE caso contr�rio.
	 * @access public
	 */
	function updateEmployeeStatus($params)
	{
		$result = $this->so->updateEmployeeStatus($params['funcionario_status_id'], $params['organizacao_id'], $params['descricao'], $params['exibir']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Remove um Status de funcion�rio.
	 * @param array $params Uma array contendo a organiza��o e o ID do status de funcion�rio que ser� exclu�do (Ajax).
	 * @return bool TRUE se a a��o foi conclu�da com �xito e FALSE caso contr�rio.
	 * @access public
	 */
	function removeEmployeeStatus($params)
	{
		$result = $this->so->removeEmployeeStatus($params['funcionario_status_id'], $params['organizacao_id']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Lista as poss�veis categorias de uma organiza��o.
	 * @param array $params Uma array contendo o ID da organiza��o de onde as categorias ser�o listadas (Ajax).
	 * @return array Lista dos poss�veis categorias.
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
	 * @param array $params Uma array contendo os par�metros necess�rios para adicionar uma categoria (Ajax).
	 * @return bool TRUE se a a��o foi conclu�da com �xito e FALSE caso contr�rio.
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
	 * @param array $params Uma array contendo os par�metros da categoria que podem ser alterados (Ajax).
	 * @return bool TRUE se a a��o foi conclu�da com �xito e FALSE caso contr�rio.
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
	 * @param array $params Uma array contendo o ID da categoria que ser� exclu�da e de sua organiza��o (Ajax).
	 * @return bool TRUE se a a��o foi conclu�da com �xito e FALSE caso contr�rio.
	 * @access public
	 */
	function removeEmployeeCategory($params)
	{
		$result = $this->so->removeEmployeeCategory($params['funcionario_categoria_id'], $params['organizacao_id']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Lista os poss�veis cargos de uma organiza��o.
	 * @param array $params Uma array contendo o ID da organiza��o de onde os cargos ser�o listados (Ajax).
	 * @return array Lista dos poss�veis cargos.
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
	 * @param array $params Uma array contendo os par�metros necess�rios para adicionar um cargo (Ajax).
	 * @return bool TRUE se a a��o foi conclu�da com �xito e FALSE caso contr�rio.
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
	 * @param array $params Uma array contendo os par�metros do cargo que podem ser alterados (Ajax).
	 * @return bool TRUE se a a��o foi conclu�da com �xito e FALSE caso contr�rio.
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
	 * @param array $params Uma array contendo o ID do cargo que ser� exclu�do e de sua organiza��o (Ajax).
	 * @return bool TRUE se a a��o foi conclu�da com �xito e FALSE caso contr�rio.
	 * @access public
	 */
	function removeJobTitle($params)
	{
		$result = $this->so->removeJobTitle($params['cargo_id'], $params['organizacao_id']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Lista os poss�veis status de �rea.
	 * @param array $params Uma array contendo o ID da organiza��o de onde os status de �rea ser�o listados (Ajax).
	 * @return array Lista dos poss�veis status de �rea.
	 * @access public
	 */
	function listAreaStatus($params)
	{
		$result = $this->so->getAreaStatus($params['organizacao_id']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Adiciona um status de �rea.
	 * @param array $params Uma array contendo os par�metros necess�rios para adicionar um status de �rea (Ajax).
	 * @return bool TRUE se a a��o foi conclu�da com �xito e FALSE caso contr�rio.
	 * @access public
	 */
	function addAreaStatus($params)
	{
		$result = $this->so->addAreaStatus($params['organizacao_id'], $params['descricao'], $params['nivel']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Atualiza um status de �rea.
	 * @param array $params Uma array contendo os par�metros do status de �rea que podem ser alterados (Ajax).
	 * @return bool TRUE se a a��o foi conclu�da com �xito e FALSE caso contr�rio.
	 * @access public
	 */
	function updateAreaStatus($params)
	{
		$result = $this->so->updateAreaStatus($params['area_status_id'], $params['organizacao_id'], $params['descricao'], $params['nivel']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Remove um status de �rea.
	 * @param array $params Uma array contendo o ID do status de �rea que ser� exclu�do e de sua organiza��o (Ajax).
	 * @return bool TRUE se a a��o foi conclu�da com �xito e FALSE caso contr�rio.
	 * @access public
	 */
	function removeAreaStatus($params)
	{
		$result = $this->so->removeAreaStatus($params['area_status_id'], $params['organizacao_id']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Lista os centros de custo de uma organiza��o.
	 * @param array $params Uma array contendo o ID da organiza��o dos centros de custo (Ajax).
	 * @return array Lista dos centros de custo de uma organiza��o.
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
	 * @param array $params Uma array contendo os par�metros necess�rios para adicionar um centro de custo (Ajax).
	 * @return bool TRUE se a a��o foi conclu�da com �xito e FALSE caso contr�rio.
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
	 * @param array $params Uma array contendo os par�metros do centro de custo que podem ser alterados (Ajax).
	 * @return bool TRUE se a a��o foi conclu�da com �xito e FALSE caso contr�rio.
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
	 * @param array $params Uma array contendo o ID do centro de custo que ser� exclu�do e de sua organiza��o (Ajax).
	 * @return bool TRUE se a a��o foi conclu�da com �xito e FALSE caso contr�rio.
	 * @access public
	 */
	function removeCostCenter($params)
	{
		$result = $this->so->removeCostCenter($params['centro_custo_id'], $params['organizacao_id']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Lista as localidades de uma organiza��o.
	 * @param array $params Uma array contendo o ID da organiza��o das localidades (Ajax).
	 * @return array Lista das localidades de uma organiza��o.
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
	 * @param array $params Uma array contendo os par�metros necess�rios para adicionar uma localidade (Ajax).
	 * @return bool TRUE se a a��o foi conclu�da com �xito e FALSE caso contr�rio.
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
	 * @param array $params Uma array contendo os par�metros da localidade que podem ser alterados (Ajax).
	 * @return bool TRUE se a a��o foi conclu�da com �xito e FALSE caso contr�rio.
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
	 * @param array $params Uma array contendo o ID da localidade que ser� exclu�da e de sua organiza��o (Ajax).
	 * @return bool TRUE se a a��o foi conclu�da com �xito e FALSE caso contr�rio.
	 * @access public
	 */
	function removeLocal($params)
	{
		$result = $this->so->removeLocal($params['localidade_id'], $params['organizacao_id']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Lista os funcion�rios de uma �rea.
	 * @param array $params Uma array contendo o ID da organiza��o dos funcion�rios e de uma �rea desta organiza��o (Ajax).
	 * @return array Lista dos funcion�rios de uma �rea.
	 * @access public
	 */
	function listAreaEmployee($params)
	{
		$result = $this->so->getAreaEmployee($params['area_id'], $params['organizacao_id']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Lista de funcion�rios que satisfazem um determinados crit�rio de busca.
	 * @param array $params Uma array contendo o ID da organiza��o dos funcion�rios e o crit�rio de busca (Ajax).
	 * @return array Lista das localidades de uma organiza��o.
	 * @access public
	 */
	function searchEmployee($params)
	{
		if (!preg_match('/^([[:alnum:] ]+)$/', $params['search_term']))
			die(serialize("Parametro de busca inv�lido"));
		$result = $this->so->searchEmployee($params['search_term'], $params['organizacao_id']);
		$this->disconnect_all();

		usort($result, create_function('$a,$b', 'return strcasecmp($a[\'funcionario_id_desc\'],$b[\'funcionario_id_desc\']);'));
		return $result;
	}

	/**
	 * Adiciona um funcion�rio.
	 * @param array $params Uma array contendo os par�metros necess�rios para adicionar um funcion�rio (Ajax).
	 * @return bool TRUE se a a��o foi conclu�da com �xito e FALSE caso contr�rio.
	 * @access public
	 */
	function addEmployee($params)
	{
		$result = $this->so->addEmployee($params['funcionario_id'], $params['organizacao_id'], $params['area_id'], $this->_nullReplace($params['centro_custo_id']), $params['localidade_id'], $params['funcionario_status_id'], $this->_nullReplace($params['cargo_id']), $this->_nullReplace($params['nivel']), $this->_nullReplace($params['funcionario_categoria_id']), $params['titulo'], $params['apelido'], $params['funcao'], $params['data_admissao']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Atualiza funcion�rio.
	 * @param array $params Uma array contendo os par�metros do funcion�rio que podem ser alterados (Ajax).
	 * @return bool TRUE se a a��o foi conclu�da com �xito e FALSE caso contr�rio.
	 * @access public
	 */
	function updateEmployee($params)
	{
		$result = $this->so->updateEmployee($params['funcionario_id'], $params['organizacao_id'], $params['area_id'], $this->_nullReplace($params['centro_custo_id']), $params['localidade_id'], $params['funcionario_status_id'], $this->_nullReplace($params['cargo_id']), $this->_nullReplace($params['nivel']), $this->_nullReplace($params['funcionario_categoria_id']), $params['titulo'], $params['apelido'], $params['funcao'], $params['data_admissao']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Remove um funcion�rio.
	 * @param array $params Uma array contendo o ID do funcion�rio que ser� exclu�do e de sua organiza��o (Ajax).
	 * @return bool TRUE se a a��o foi conclu�da com �xito e FALSE caso contr�rio.
	 * @access public
	 */
	function removeEmployee($params)
	{
		$result = $this->so->removeEmployee($params['funcionario_id'], $params['organizacao_id']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Lista as �reas de uma organiza��o.
	 * @param array $params Uma array contendo o ID da organiza��o das �reas (Ajax).
	 * @return array Lista das �reas de uma organiza��o.
	 * @access public
	 */
	function listArea($params)
	{
		$result = $this->so->getArea($params['organizacao_id'], $params['area_id']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Lista, hierarquicamente, as �reas de uma organiza��o.
	 * @param array $params Uma array contendo o ID da organiza��o das �reas (Ajax).
	 * @return array Lista hier�rquica das �reas de uma organiza��o.
	 * @access public
	 */
	function listHierarchicalArea($params)
	{
		$result = $this->so->getHierarchicalArea($params['organizacao_id'], null, 0);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Adiciona uma �rea.
	 * @param array $params Uma array contendo os par�metros necess�rios para adicionar uma �rea (Ajax).
	 * @return bool TRUE se a a��o foi conclu�da com �xito e FALSE caso contr�rio.
	 * @access public
	 */
	function addArea($params)
	{
		$result = $this->so->addArea($params['centro_custo_id'], $params['organizacao_id'], $params['area_status_id'], $this->_nullReplace($params['titular_funcionario_id']), $this->_nullReplace($params['superior_area_id']), $params['sigla'], $params['descricao'], $params['ativa'], $this->_nullReplace($params['auxiliar_funcionario_id']));
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Atualiza uma �rea.
	 * @param array $params Uma array contendo os par�metros da �rea que podem ser alterados (Ajax).
	 * @return bool TRUE se a a��o foi conclu�da com �xito e FALSE caso contr�rio.
	 * @access public
	 */
	function updateArea($params)
	{
		$result = $this->so->updateArea($params['centro_custo_id'], $params['organizacao_id'], $params['area_status_id'], $this->_nullReplace($params['titular_funcionario_id']), $this->_nullReplace($params['superior_area_id']), $params['sigla'], $params['descricao'], $params['ativa'], $this->_nullReplace($params['auxiliar_funcionario_id']), $params['area_id']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Remove uma �rea.
	 * @param array $params Uma array contendo o ID da �rea que ser� exclu�do e de sua organiza��o (Ajax).
	 * @return bool TRUE se a a��o foi conclu�da com �xito e FALSE caso contr�rio.
	 * @access public
	 */
	function removeArea($params)
	{
		$result = $this->so->removeArea($params['area_id'], $params['organizacao_id']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Busca informa��es sobre um funcion�rio.
	 * @param array $params Uma array contendo o ID do funcion�rio cujas informa��es ser�o extra�das e de sua organiza��o (Ajax).
	 * @return array Informa��es sobre o funcion�rio.
	 * @access public
	 */
	function getEmployeeInfo($params)
	{
		$result = $this->so->getEmployeeInfo((int) $params['funcionario_id'], (int) $params['organizacao_id']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Busca informa��es sobre uma �rea.
	 * @param array $params Uma array contendo o ID da �rea cujas informa��es ser�o extra�das e de sua organiza��o (Ajax).
	 * @return array Informa��es sobre a �rea.
	 * @access public
	 */
	function getAreaInfo($params)
	{
		$result = $this->so->getAreaInfo((int) $params['area_id'], (int) $params['organizacao_id']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Lista todos os telefones da organiza��o.
	 * @param array $params Uma array contendo o ID da organiza��o de onde os telefones ser�o listados (Ajax).
	 * @return array Lista de telefones da organiza��o.
	 * @access public
	 */
	function listTelephones( $params )
	{
		$result = $this -> so -> getTelephones( $params[ 'organizacao_id' ] );
		$this -> disconnect_all( );

		return $result;
	}

	/**
	 * Adiciona um telefone a uma organiza��o.
	 * @param array $params Uma array contendo os par�metros necess�rios para adicionar um telefone (Ajax).
	 * @return bool TRUE se a a��o foi conclu�da com �xito e FALSE caso contr�rio.
	 * @access public
	 */
	function addTelephones( $params )
	{
		$result = $this -> so -> addTelephone( $params[ 'organizacao_id' ], $params[ 'descricao' ], $params[ 'numero' ] );
		$this->disconnect_all( );

		return $result;
	}

	/**
	 * Atualiza um telefone de uma organiza��o.
	 * @param array $params Uma array contendo os par�metros de telefone da organiza��o que podem ser alterados (Ajax).
	 * @return bool TRUE se a a��o foi conclu�da com �xito e FALSE caso contr�rio.
	 * @access public
	 */
	function updateTelephones( $params )
	{
		$result = $this -> so -> updateTelephone( $params[ 'organizacao_id' ], $params[ 'telefone_id' ], $params[ 'descricao' ], $params[ 'numero' ] );
		$this->disconnect_all( );

		return $result;
	}

	/**
	 * Remove um telefone de uma organiza��o.
	 * @param array $params Uma array contendo o ID do telefone que ser� exclu�do e de sua organiza��o (Ajax).
	 * @return bool TRUE se a a��o foi conclu�da com �xito e FALSE caso contr�rio.
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
