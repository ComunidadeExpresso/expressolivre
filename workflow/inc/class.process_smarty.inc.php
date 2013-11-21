<?php
	// include galaxia's configuration tailored to egroupware
	define('SMART_DIR',PHPGW_SERVER_ROOT.'/workflow/inc/smarty/');
	require_once('engine/config.egw.inc.php');
	require_once('smarty/Smarty.class.php');
	/**
	 * @package Workflow
	 * @license http://www.gnu.org/copyleft/gpl.html GPL
	 */
	class process_smarty extends Smarty
	{
		function process_smarty()
		{
			$this->Smarty();
		}
	}
?>