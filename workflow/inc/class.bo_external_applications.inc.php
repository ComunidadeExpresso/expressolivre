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
 * Camada Business das Aplica��es Externas.
 * @package Workflow
 * @author Sidnei Augusto Drovetto Jr. - drovetto@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 */
class bo_external_applications extends bo_ajaxinterface
{
	/**
	 * @var object Acesso � camada Model das Aplica��es Externas.
	 * @access private
	 */
	private $so;

	/**
	 * Construtor da classe so_orgchart
	 * @return object
	 */
	function bo_external_applications()
	{
		parent::bo_ajaxinterface();
		$this->so = &Factory::getInstance('so_external_applications');
	}

	/**
	 * Lista todas as aplica��es externas
	 * @return array Lista de aplica��es externas
	 * @access public
	 */
	function getExternalApplications()
	{
		$result = $this->so->getExternalApplications();
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Busca informa��es sobre uma aplica��o externa
	 * @param array $params Uma array contendo os par�metros necess�rios para encontrar as informa��es de uma aplica��o externa (Ajax)
	 * @return bool TRUE se a a��o foi conclu�da com �xito e FALSE caso contr�rio
	 * @access public
	 */
	function getExternalApplication($params)
	{
		$result = $this->so->getExternalApplication((int) $params['external_application_id']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Adiciona uma aplica��o externa
	 * @param array $params Uma array contendo os par�metros necess�rios para adicionar uma aplica��o externa (Ajax)
	 * @return bool TRUE se a a��o foi conclu�da com �xito e FALSE caso contr�rio
	 * @access public
	 */
	function addExternalApplication($params)
	{
		$result = $this->so->addExternalApplication($params['name'], $params['description'], $params['address'], $params['image'], $params['authentication'], $params['post'], $params['intranet_only']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Atualiza uma aplica��o externa
	 * @param array $params Uma array contendo os par�metros necess�rios para atualizar uma aplica��o externa (Ajax)
	 * @return bool TRUE se a a��o foi conclu�da com �xito e FALSE caso contr�rio
	 * @access public
	 */
	function updateExternalApplication($params)
	{
		$result = $this->so->updateExternalApplication($params['external_application_id'], $params['name'], $params['description'], $params['address'], $params['image'], $params['authentication'], $params['post'], $params['remove_current_image'], $params['intranet_only']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Remove uma aplica��o externa
	 * @param array $params Uma array contendo os par�metros necess�rios para remover uma aplica��o externa (Ajax)
	 * @return bool TRUE se a a��o foi conclu�da com �xito e FALSE caso contr�rio
	 * @access public
	 */
	function removeExternalApplication($params)
	{
		$result = $this->so->removeExternalApplication((int) $params['external_application_id']);
		$this->disconnect_all();

		return $result;
	}
}
?>
