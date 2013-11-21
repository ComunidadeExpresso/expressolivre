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

require_once(dirname(__FILE__) . SEP . 'class.so_agent.inc.php');

/**
 * Class to store/read all agents data
 *
 * Creation and deletion of agents records are done by the workflow engine, not
 * by this class and her childs.
 *
 * @package Workflow
 * @author regis.leroy@glconseil.com
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 */
class so_agent_mail_smtp extends so_agent
{
	 /**
	  * Constructor of the so_agent class
	  * 
	  * @return object
	  * @access public
	  */
	function so_agent_mail_smtp()
	{
		parent::so_agent();
		$this->agent_table = $this->wf_table.'mail_smtp';
	}
	
	/**
	 * Read all agent datas from the database
	 * @param $agent_id int id of the entry to read
	 * @return mixed array/boolean array with column => value pairs or false if entry not found
	 */
	function read($agent_id)
	{
		//perform the query
		$this->db->select($this->agent_table,'*',array('wf_agent_id'=>$agent_id),__LINE__,__FILE__, 'workflow');
		
		while (($row = $this->db->row(true)))
		{
			return $row;
		}

		return false;
	}

	/**
	 * @abstract save all agent datas to the database
	 * @param $agent_id int id of the entry to save
	 * @param $datas is an array containing columns => value pairs which will be saved for this agent
	 * @return bool true if everything was ok, false else
	 */
	function save($agent_id, &$datas)
	{
		$this->db->update($this->agent_table,$datas,array('wf_agent_id'=>$agent_id),__LINE__,__FILE__, 'workflow');
		return false;
	}
}
