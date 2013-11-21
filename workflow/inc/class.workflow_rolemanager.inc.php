<?php
	require_once 'common.inc.php';
	// include galaxia's configuration tailored to egroupware
	require_once('engine/config.egw.inc.php');

	require_once(GALAXIA_LIBRARY . SEP . 'src' . SEP . 'ProcessManager' . SEP . 'RoleManager.php');
	/**
	 * @package Workflow
	 * @license http://www.gnu.org/copyleft/gpl.html GPL
	 */
	class workflow_rolemanager extends RoleManager
	{   /**
		 * Construtor da classe workflow_rolemanager
		 * @access public
		 * @return object
		 */
		function workflow_rolemanager()
		{
			parent::RoleManager(Factory::getInstance('WorkflowObjects')->getDBGalaxia()->Link_ID);
		}
	}
?>
