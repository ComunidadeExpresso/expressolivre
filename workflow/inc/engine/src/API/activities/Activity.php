<?php
require_once(GALAXIA_LIBRARY.SEP.'src'.SEP.'API'.SEP.'BaseActivity.php');
/**
 * Handles activities of type 'activity'
 * 
 * @package Galaxia
 * @license http://www.gnu.org/copyleft/gpl.html GPL 
 */
class Activity extends BaseActivity 
{
	/**
	 * Constructor
	 * 
	 * @param object &$db ADOdb
	 * @return object Activity instance
	 * @access public
	 */
	function Activity()
	{
		parent::Base();
		$this->child_name = 'Activity';
	}

}
?>
