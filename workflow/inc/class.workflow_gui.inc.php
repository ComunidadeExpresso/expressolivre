<?php
	require_once 'common.inc.php';
	// include galaxia's configuration tailored to egroupware
	require_once('engine/config.egw.inc.php');

	require_once(GALAXIA_LIBRARY . SEP . 'src' . SEP . 'GUI' . SEP . 'GUI.php');

	/**
	 * @package Workflow
	 * @license http://www.gnu.org/copyleft/gpl.html GPL
	 */
	class workflow_gui extends GUI
	{  /**
		 * Construtor da classe workflow_gui
		 * @access public
		 * @return object
		 */
		function workflow_gui()
		{
			parent::GUI(Factory::getInstance('WorkflowObjects')->getDBGalaxia()->Link_ID);
		}
	}
?>
