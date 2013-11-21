<?php
require_once(GALAXIA_LIBRARY.SEP.'src'.SEP.'API'.SEP.'BaseActivity.php');
/**
 * Handles activities of type 'start'
 * 
 * @package Galaxia
 * @license http://www.gnu.org/copyleft/gpl.html GPL 
 */
class Start extends BaseActivity {
	/**
	 * Constructor
	 * 
	 * @param object $db ADOdb
	 * @return object Class instance
	 * @access public
	 */
	function Start()
	{
	 	parent::Base();
		$this->child_name = 'Start';
	}
}
?>
