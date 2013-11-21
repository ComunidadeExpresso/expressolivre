<?php
require_once(GALAXIA_LIBRARY.SEP.'src'.SEP.'API'.SEP.'BaseActivity.php');
/**
 * Handles activities of type 'switch'
 * 
 * @package Galaxia
 * @license http://www.gnu.org/copyleft/gpl.html GPL 
 */
class SwitchActivity extends BaseActivity 
{
	/**
	 * Constructor
	 * 
	 * @param object $db ADOdb
	 * @return object Class instance
	 * @access public
	 */
	function SwitchActivity()
	{
	   parent::Base();
	   $this->child_name = 'Switch';
	}
}
?>
