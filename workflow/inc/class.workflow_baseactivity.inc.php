<?php
	require_once 'common.inc.php';
	// include galaxia's configuration tailored to egroupware
	require_once('engine/config.egw.inc.php');

	require_once(GALAXIA_LIBRARY . SEP . 'src' . SEP . 'API' . SEP . 'BaseActivity.php');
	/**
	 * @package Workflow
	 * @license http://www.gnu.org/copyleft/gpl.html GPL
	 */	 
	class workflow_baseactivity extends BaseActivity
	{   /**
		 * Construtor da classe workflow_baseactivity
		 * @access public
		 * @return object
		 */
		function workflow_baseactivity()
		{
			parent::BaseActivity(Factory::getInstance('WorkflowObjects')->getDBGalaxia()->Link_ID);
		}
	}
?>
