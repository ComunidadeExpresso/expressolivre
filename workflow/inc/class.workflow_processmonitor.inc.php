<?php
	require_once 'common.inc.php';
	// include galaxia's configuration tailored to egroupware
	require_once('engine/config.egw.inc.php');

	require_once(GALAXIA_LIBRARY . SEP . 'src' . SEP . 'ProcessMonitor' . SEP . 'ProcessMonitor.php');
	/**
	 * @package Workflow
	 * @license http://www.gnu.org/copyleft/gpl.html GPL
	 */
	class workflow_processmonitor extends ProcessMonitor
	{   /**
		 * Construtor da classe workflow_processmonitor
		 * @access public
		 * @return object
		 */
		function workflow_processmonitor()
		{
			parent::ProcessMonitor(Factory::getInstance('WorkflowObjects')->getDBGalaxia()->Link_ID);
		}
	}
?>
