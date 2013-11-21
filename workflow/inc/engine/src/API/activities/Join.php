<?php
require_once(GALAXIA_LIBRARY.SEP.'src'.SEP.'API'.SEP.'BaseActivity.php');
/**
 * Handles activities of type 'join'
 * 
 * @package Galaxia
 * @license http://www.gnu.org/copyleft/gpl.html GPL 
 */
class Join extends BaseActivity {
	/**
	 * Constructor
	 * 
	 * @param object $db ADOdb
	 * @return object Class instance
	 * @access public
	 */
	function Join()
	{
	 	parent::Base();
		$this->child_name = 'Join';
	}
}
?>
