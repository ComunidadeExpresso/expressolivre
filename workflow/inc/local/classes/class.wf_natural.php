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

require_once(PHPGW_SERVER_ROOT.SEP.'workflow'.SEP.'inc'.SEP.'natural'.SEP.'class.natural.php');

/**
 * Mainframe connection to workflow
 *
 * TODO - This class should be removed from here. Its based on a not
 * public protocol, thus cannot be used for everybody.
 *
 * @package Workflow
 * @subpackage local
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @author Everton Flávio Rufino Seára
 */
class wf_natural extends Natural
{

	/**
	 * @var object Log Object
	 * @access private
	 */
	private $logger = null;

	function __construct()
	{
		parent::Natural();

		$natconf = array(
					'mainframe_ip'			=> '',
					'mainframe_port'		=> '',
					'mainframe_key'			=> '',
					'mainframe_password'	=> '',
					'mainframe_environment'	=> ''
		);

		$nat_conf_values = &Factory::getInstance('workflow_wfruntime')->getConfigValues($natconf);

		$this->setIPAddress($nat_conf_values['mainframe_ip']);
		$this->setServerPort($nat_conf_values['mainframe_port']);
		$this->setKey($nat_conf_values['mainframe_key']);
		$this->setPassword($nat_conf_values['mainframe_password']);
		$this->setApplication($nat_conf_values['mainframe_environment']);

		$this->logger = &Factory::getInstance('Logger', array('file'));
	}

	/**
	 * This method MUST be called before using execute method
	 * It specifies the natural sub-program to be accessed.
	 *
	 * @param Object $obj Object that specifies natural sub-program properties
	 * @return void
	 */
	public function configure($obj)
	{
		$this->obj = $obj;

		$this->initialize($obj->name);

		if ($obj->server != NULL){
			$this->setIPAddress($obj->server);
		}
		if ($obj->port != NULL){
			$this->setServerPort($obj->port);
		}
		if ($obj->key != NULL){
			$this->setKey($obj->key);
		}
		if ($obj->password != NULL){
			$this->setPassword($obj->password);
		}
		if ($obj->environment != NULL){
			$this->setApplication($obj->environment);
		}
		if ($obj->logon != NULL){
			$this->setLogon($obj->logon);
		}
		if ($obj->system != NULL){
			$this->setSystem($obj->system);
		}
		if ($obj->rc != NULL){
			$this->setRC($obj->rc);
		}
	}

	/**
	 * Method for accessing and retrieving data from mainframe
	 * @return bool
	 */
	public function execute($inputParams = "")
	{
		// execute action and log wasted time
		$totalTime = microtime(true);
		$result = parent::execute($inputParams);
		$totalTime = microtime(time) - $totalTime;
		$log = sprintf("WF_NATURAL [subprogram=%s] [time=%ss]",
					$this->obj->name,
					number_format($totalTime,3)
					);
		$this->logger->debug($log);
		return $result;
	}
}
?>
