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
 * Camada Business para Mover Inst�ncias.
 * @package Workflow
 * @author Sidnei Augusto Drovetto Jr. - drovetto@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 */
class bo_move_instances extends bo_ajaxinterface
{
	/**
	 * @var object $so Acesso � camada model.
	 * @access private
	 */
	private $so;


	/**
	 * Construtor da classe bo_move_instances
	 * @return object
	 * @access public
	 */
	function bo_move_instances()
	{
		parent::bo_ajaxinterface();
		$this->so = &Factory::getInstance('so_move_instances');
	}

	/**
	 * Carrega a lista de todos os processos que o usu�rio tem direito.
	 * @return array Lista dos processos.
	 * @access public
	 */
	function loadProcesses()
	{
		$output = $this->so->loadProcesses();
		$this->disconnect_all();

		return $output;
	}

	/**
	 * Carrega a lista de todos as atividades dos processos que ter�o as inst�ncias movidas.
	 * @param array $params Uma array contendo os par�metros necess�rios para buscar as atividades dos processos.
	 * @return array Lista das atividades de cada processo e um pr�-relacionamento das atividades.
	 * @access public
	 */
	function loadActivities($params)
	{
		$output['from'] = $this->so->loadProcessActivities($params['from']);
		$output['to'] = $this->so->loadProcessActivities($params['to']);
		$output['pre-match'] = $this->so->matchActivities($output['from'], $output['to'], 80);
		$this->disconnect_all();

		return $output;
	}

	/**
	 * Move as inst�ncias de um processo para outro.
	 * @param array $params Uma array contendo os par�metros necess�rios para mover as inst�ncias.
	 * @return bool TRUE em caso de sucesso e FALSE caso contr�rio.
	 * @access public
	 */
	function moveInstances($params)
	{
		$JSON = &Factory::newInstance('Services_JSON');

		/* convert the mappgin element to array */
		$params['activityMappings'] = array_map("get_object_vars", get_object_vars($JSON->decode($params['activityMappings'])));

		$active = ($params['active'] == 'on');
		$completed = ($params['completed'] == 'on');

		return $this->so->moveInstances($params['from'], $params['to'], $params['activityMappings'], $active, $completed);
	}
}
?>
