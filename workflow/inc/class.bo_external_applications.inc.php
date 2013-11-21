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
 * Camada Business das Aplicações Externas.
 * @package Workflow
 * @author Sidnei Augusto Drovetto Jr. - drovetto@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 */
class bo_external_applications extends bo_ajaxinterface
{
	/**
	 * @var object Acesso à camada Model das Aplicações Externas.
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
	 * Lista todas as aplicações externas
	 * @return array Lista de aplicações externas
	 * @access public
	 */
	function getExternalApplications()
	{
		$result = $this->so->getExternalApplications();
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Busca informações sobre uma aplicação externa
	 * @param array $params Uma array contendo os parâmetros necessários para encontrar as informações de uma aplicação externa (Ajax)
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário
	 * @access public
	 */
	function getExternalApplication($params)
	{
		$result = $this->so->getExternalApplication((int) $params['external_application_id']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Adiciona uma aplicação externa
	 * @param array $params Uma array contendo os parâmetros necessários para adicionar uma aplicação externa (Ajax)
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário
	 * @access public
	 */
	function addExternalApplication($params)
	{
		$result = $this->so->addExternalApplication($params['name'], $params['description'], $params['address'], $params['image'], $params['authentication'], $params['post'], $params['intranet_only']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Atualiza uma aplicação externa
	 * @param array $params Uma array contendo os parâmetros necessários para atualizar uma aplicação externa (Ajax)
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário
	 * @access public
	 */
	function updateExternalApplication($params)
	{
		$result = $this->so->updateExternalApplication($params['external_application_id'], $params['name'], $params['description'], $params['address'], $params['image'], $params['authentication'], $params['post'], $params['remove_current_image'], $params['intranet_only']);
		$this->disconnect_all();

		return $result;
	}

	/**
	 * Remove uma aplicação externa
	 * @param array $params Uma array contendo os parâmetros necessários para remover uma aplicação externa (Ajax)
	 * @return bool TRUE se a ação foi concluída com êxito e FALSE caso contrário
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
