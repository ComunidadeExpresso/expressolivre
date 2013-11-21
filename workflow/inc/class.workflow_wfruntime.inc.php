<?php
	require_once 'common.inc.php';
	// include galaxia's configuration tailored to egroupware
	require_once('engine/config.egw.inc.php');

	require_once(GALAXIA_LIBRARY . SEP . 'src' . SEP . 'common' . SEP . 'WfRuntime.php');
	/**
	 * @package Workflow
	 * @license http://www.gnu.org/copyleft/gpl.html GPL
	 */
	class workflow_wfruntime extends WfRuntime
	{
		/**
		 * Construtor da classe workflow_wfruntime
		 * @access public
		 * @return object
		 */
		function workflow_wfruntime()
		{
			parent::WfRuntime(Factory::getInstance('WorkflowObjects')->getDBGalaxia()->Link_ID);
		}
	}
?>
