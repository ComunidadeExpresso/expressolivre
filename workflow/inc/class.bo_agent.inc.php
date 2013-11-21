<?php
/**************************************************************************\
* eGroupWare Workflow - Agents Connector - business objects layer          *
* ------------------------------------------------------------------------ *
* This program is free software; you can redistribute it and/or modify it  *
* under the terms of the GNU General Public License as published           *
* by the Free Software Foundation; either version 2 of the License, or     *
* any later version.                                                       *
\**************************************************************************/

/**
 * This allows the Workflow Engine to connect to various agents
 * Agents are external elements for the workflow. It could be
 * email systems, filesystems, calendars, what you want.
 * Use this class to make childrens like, for example in the
 * class.bo_agent_mail_smtp.inc.php for the mail_smtp susbsytem
 * 
 * @package Workflow
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @author regis.leroy@glconseil.com
 */
class bo_agent
{		
	/**
	 * @var array $error the local error storage
	 * @access public
	 */
	var $error = Array();
	/**
	 * @var object $activity the activity object we are working with at runtime (reference)
	 * @access public
	 */	
	var $activity = null;
	/**
	 * @var object $instance the instance object we are working with at runtime (reference)
	 * @access public
	 */	
	var $instance = null;
	/**
	 * @var object $process the process object is used for process level configuration
	 * @access public
	 */	
	var $process = null;
	/**
	 * @var array $conf
	 * @access public
	 */	
	var $conf;
	// define theses values in your child class ----------------------------------------------
	
	/**
	 * @var string $title Agent title
	 * @access public
	 */	
	var $title='';
	/**
	 * @var string $description Agent description
	 * @access public
	 */	
	var $description='';
	/**
	 * @var string $help Agent help
	 * @access public
	 */	
	var $help='';
	/**
	 * @var object $so_agent derived so object. i.e.: for foo agent it is an so_agent_foo
	 * @access public
	 */	
	var $so_agent;
	/**
	 * @var integer $agent_id Agent id
	 * @access public
	 */	
	var $agent_id;
	/**
	 *  @var array $fields the fields which are saved at admin time and just changed at runtime (without saving)
	 *  @access public
	 */
	var $fields = Array();	
	/**
	 *  @var array $ProcessConfigurationFieldsdefault the config fields which are at process level , key is the config option and value is the
	 *  default value
	 *  @access public
	 */
	var $ProcessConfigurationFieldsdefault = Array();
	/**
	 * @var array $showProcessConfigurationFieldsdefault the config fields which are at process level , key is the config option and value is an
	 * associative array with keys:
	 * - 'title' for an helper/title line, not a conf value in fact
	 * - 'text' for a text input
	 * - 'yesno' to select between true or false
	 * - an array for a list of select key => value pairs
	 * @access public
	 */
	var $showProcessConfigurationFieldsdefault = Array();
	
	/**
	 *  Constructor
	 *  
	 *  @access public
	 *  @return object
	 */
	function bo_agent()
	{
		
	}
	
	/**
	 * You should always call this function after failed operations on a workflow object to obtain messages
	 * @param array $as_array if true the result will be send as an array of errors or an empty array. Else, if you do not give any parameter 
	 * or give a false parameter you will obtain a single string which can be empty or will contain error messages with <br /> html tags.
	 * @return mixed single string,array of errors or an empty array
	 * @access public
	 */
	 function get_error($as_array=false) 
	 {
	 	if ($as_array)
		 {
		 	return $this->error;
		 }
		 $result_str = implode('<br />',$this->error);
		 $this->error= Array();
		 return $result_str;
		}
	
	/**
	 *  Get the title
	 * 
	 *  @return string title
	 *  @access public
	 */
	function getTitle()
	{
		return $this->title;
	}
	
	/**
	 *  Get the description
	 * 
	 *  @return string description
	 *  @access public
	 */
	
	function getDescription()
	{
		return $this->description;
	}
	
	/**
	 *  Get the help
	 * 
	 *  @return string help
	 *  @access public
	 */
	function getHelp()
	{
		return $this->help;
	}

	/**
	  * Factory: Load the agent values stored somewhere in the agent object and retain the agent id
	  *
	  * @param int $agent_id is the agent id
	  * @param bool $really_load boolean, true by default, if false the data wont be loaded from database and
	  * the only thing done by this function is storing the agent_id (usefull if you know you wont need actual data)
	  * @return bool false if the agent cannot be loaded, true else
	  * @access public
	 */
	function load($agent_id, $really_load=true)
	{
		$this->agent_id = $agent_id;
		return true;
	}

	/**
	  * Save the agent datas
	  *
	  * @return bool false if the agent cannot be saved, true else
	  * @access public
	 */
	function save()
	{
		return true;
	}
	
	/**
	 * Function called at runtime to permit association with the instance and the activity
	 * we store references to theses objects
	 * @return void
	 * @access public
	 */
	function runtime(&$instance, &$activity)
	{
		$this->instance =& $instance;
		$this->activity =& $activity;
	}

	/**
	* Return the agent fields in different forms
	* @param int $result_type :
	* - 1 the result is an array containing the field names
	* - 2 the result is an array containing fields names => value pairs
    * - 3 the result is an array containing fields names => field array pairs, the field array is an associative array
	*   containing all infos about the field with $key => $value pairs.
	* @return array an array, the form depends on the parameter $result_type
	* @access public
	*/
	function get($result_type)
	{
		switch ($result_type)
		{
			case 1:
				return array_keys($this->fields);
				break;
			case 2:
				$res = Array();
				foreach ($this->fields as $key => $value)
				{
					$res[$key] = html_entity_decode($value['value']);
				}
				return $res;
				break;
			default :
				return $this->fields;
		}
	}

	/**
	 * Affect some values to some of the agent's fields
	 * @param array $datas is an array containing fields => value pairs
	 * @return bool false if one or more value cannot be affected, true else
	 * @access public
	 */
	function set(&$datas)
	{
		foreach ($datas as $key => $value)
		{
			if ( (isset($this->fields[$key])) && (is_array($this->fields[$key])) )
			{
				$this->fields[$key]['value'] = htmlentities($value);
			}
			else
			{
				return false;
			}
		}
		return true;
	}


	/**
	 * this function tell the engine which process level options have to be set
	 *
	 * for the agent. Theses options will be initialized for all processes by the engine
	 * and can be different for each process.
	 * @return array an array which can be empty
	 * @access public
	 */
	function listProcessConfigurationFields()
	{
		return $this->showProcessConfigurationFields;
	}
	
	/**
	 * This function retrieve process level configuration otpions set by the engine
	 * for the agent. Theses conf values are cached locally for the object life duration
	 * @param int $wf_p_id is the process id
	 * @param bool $force is false by default, if true we retrieve theses config values even if the $conf
	 * 	local cache is already set
	 * @return array an associative array which can be empty
	 * @access public
	 */
	function getProcessConfigurationFields($wf_p_id, $force=false)
	{
		if ($force || (!(isset($this->conf))) )
		{
			if (!(isset($this->process)))
			{
				$this->process = Factory::getInstance('workflow_process');
				$this->process->getProcess($wf_p_id);
			}
			$this->conf = $this->process->getConfigValues($this->ProcessConfigurationFieldsdefault);
		}
		return $this->conf;
	}
	/**
	 * this function lists activity level options avaible for the agent
	 *
	 * @return array an associative array which can be empty
	 * @access public
	 */
	function getAdminActivityOptions ()
	{
		return (Array(Array()));
	}
}
?>
