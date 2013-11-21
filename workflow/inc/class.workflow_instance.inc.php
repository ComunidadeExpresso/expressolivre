<?php
	require_once 'common.inc.php';
	// include galaxia's configuration tailored to egroupware
	if (!defined('GALAXIA_LIBRARY'))
		require_once 'engine/config.egw.inc.php';

	require_once(GALAXIA_LIBRARY . SEP . 'src' . SEP . 'API' . SEP . 'Instance.php');
	/**
	 * @package Workflow
	 * @license http://www.gnu.org/copyleft/gpl.html GPL
	 */
	class workflow_instance extends Instance
	{   /**
		 * Construtor da classe workflow_Instance
		 * @access public
		 * @return object
		 */
		function workflow_Instance()
		{
			parent::Instance(Factory::getInstance('WorkflowObjects')->getDBGalaxia()->Link_ID);
		}
	}
?>
