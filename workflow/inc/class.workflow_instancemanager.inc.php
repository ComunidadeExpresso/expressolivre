<?php
	require_once 'common.inc.php';
	// include galaxia's configuration tailored to egroupware
	require_once('engine/config.egw.inc.php');

	require_once(GALAXIA_LIBRARY . SEP . 'src' . SEP . 'ProcessManager' . SEP . 'InstanceManager.php');
	/**
	 * @package Workflow
	 * @license http://www.gnu.org/copyleft/gpl.html GPL
	 */
	class workflow_instancemanager extends InstanceManager
	{  /**
		 * Construtor da classe workflow_instancemanager
		 * @access public
		 * @return object
		 */
		function workflow_instancemanager()
		{
			parent::InstanceManager(Factory::getInstance('WorkflowObjects')->getDBGalaxia()->Link_ID);
		}
	}
?>
