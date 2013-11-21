<?php
require_once(GALAXIA_LIBRARY.SEP.'src'.SEP.'API'.SEP.'BaseActivity.php');
/**
 * Handles activities of type 'standalone'
 * 
 * @package Galaxia
 * @license http://www.gnu.org/copyleft/gpl.html GPL 
 */
class Standalone extends BaseActivity {
	/**
	 * Constructor
	 * 
	 * @param object $db ADOdb
	 * @return object Class instance
	 * @access public
	 */
	function Standalone()
	{
	 	parent::Base();
		$this->child_name = 'Standalone';
	}
}
?>
