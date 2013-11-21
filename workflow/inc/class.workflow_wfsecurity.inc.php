<?php
	require_once 'common.inc.php';
	// include galaxia's configuration tailored to egroupware
	require_once('engine/config.egw.inc.php');

	require_once(GALAXIA_LIBRARY . SEP . 'src' . SEP . 'common' . SEP . 'WfSecurity.php');
	/**
	 * @package Workflow
	 * @license http://www.gnu.org/copyleft/gpl.html GPL
	 */
	class workflow_wfsecurity extends WfSecurity
	{
		/**
		 * Construtor da classe workflow_wfsecurity
		 * @access public
		 * @return object
		 */
		function workflow_wfsecurity()
		{
			parent::WfSecurity(Factory::getInstance('WorkflowObjects')->getDBGalaxia()->Link_ID);
		}
	}
?>
