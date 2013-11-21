<?php
	// include galaxia's configuration tailored to egroupware
	require_once('engine/config.egw.inc.php');

	require_once(GALAXIA_LIBRARY . SEP . 'src' . SEP . 'API' . SEP . 'Process.php');
	/**
	 * @package Workflow
	 * @license http://www.gnu.org/copyleft/gpl.html GPL
	 */
	class workflow_process extends Process
	{   /**
		 * Construtor da classe workflow_process
		 * @access public
		 * @return object
		 */
		function workflow_process()
		{
			parent::Process(Factory::getInstance('WorkflowObjects')->getDBGalaxia()->Link_ID);
		}
	}
?>
