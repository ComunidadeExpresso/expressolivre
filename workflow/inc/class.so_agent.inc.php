<?php
/**************************************************************************\
* eGroupWare - Workflow Agent's SO-layer (storage-object)                  *
* http://www.egroupware.org                                                *
* (c) 2005 by Regis leroy <regis.leroy@glconseil.com>                      *
* --------------------------------------------                             *
*  This program is free software; you can redistribute it and/or modify it *
*  under the terms of the GNU General Public License as published by the   *
*  Free Software Foundation; either version 2 of the License, or (at your  *
*  option) any later version.                                              *
\**************************************************************************/


/**
 * Abstract Class to store/read all agents data
 *
 * Creation and deletion of agents records are done by the workflow engine, not
 * by this class and her childs.
 *
 * @package Workflow
 * @author regis.leroy@glconseil.com
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 */

class so_agent
{
	//public functions

	/**
	 * @var array $public_functions Array of public functions
	 * @access public
	 */
	var $public_functions = array(
		'read'	=> true,
		'save'	=> true,
	);
	/**
	 * @var array $wf_table
	 * @access public
	 */
	var $wf_table = 'egw_wf_agent_';
	/**
	 * @var string $agent_table
	 * @access public
	 */
	var $agent_table = '';

	// link to the global db-object

	/**
	 * @var object $db objeto para conexao do banco de dados
	 * @access public
	 */
	var $db;

	/**
	 * Constructor of the so_agent class
	 * do not forget to call it (parent::so_agent();) in child classes
	 * @access public
	 * @return object
	 */

	function so_agent()
	{
		$this->db =& Factory::getInstance('WorkflowObjects')->getDBGalaxia();
	}

	/**
	 * @abstract read all agent datas from the database
	 * @param int $agent_id int id of the entry to read
	 * @return array array/boolean array with column => value pairs or false if entry not found
	 */
	function read($agent_id)
	{
		return false;
	}

	/**
	 * @abstract save all agent datas to the database
	 * @param int $agent_id int id of the entry to save
	 * @param array $datas is an array containing columns => value pairs which will be saved for this agent
	 * @return true if everything was ok, false else
	 */
	function save($agent_id, &$datas)
	{
		return false;
	}
}
