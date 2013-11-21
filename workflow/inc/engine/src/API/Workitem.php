<?php
require_once (GALAXIA_LIBRARY.SEP.'src'.SEP.'common'.SEP.'Base.php');
/**
 * Represents workitems
 * 
 * @package Galaxia
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @todo Implement this class 
 */
class Workitem extends Base 
{
	/**
	 * @var object $instance
	 * @access public
	 */
	var $instance;
	/**
	 * @var array $properties
	 * @access public
	 */	
	var $properties=Array();
	/**
	 * @var int $started
	 * @access public
	 */
	var $started;
	/**
	 * @var int $ended
	 * @access public
	 */
	var $ended;
	/**
	 * @var object $activity
	 * @access public
	 */
	var $activity;  
}
?>
