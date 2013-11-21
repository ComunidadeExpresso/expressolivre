<?php
/**
 * Creates a picker interface based on the items supplied.
 * @param string $items Picker options.
 * @param string $containerNumber Identification for retrieving the menu.
 * @return void
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package Workflow 
 * @subpackage local 
 * @access public 
 */
function wf_set_generic_select($items, $containerNumber = 0)
{
	$sessionSection = "generic_select";
	$digest = md5($_SERVER['REQUEST_URI']);
	$_SESSION[$sessionSection][$digest][$containerNumber] = $items;
}
?>
