<?php
/**************************************************************************\
* eGroupWare                                                               *
* http://www.egroupware.org                                                *
* --------------------------------------------                             *
*  This program is free software; you can redistribute it and/or modify it *
*  under the terms of the GNU General Public License as published by the   *
*  Free Software Foundation; either version 2 of the License, or (at your  *
*  option) any later version.                                              *
\**************************************************************************/

require_once dirname(__FILE__) . SEP . 'common.inc.php';

/**
 * @package Workflow
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @author Sidnei Augusto Drovetto Junior
 */
class ui_generic_select
{
	/**
	 * @var array $public_functions public functions
	 * @access public
	 */
	var $public_functions = array(
        'form'  => true
    );

    /**
     * @var int $containerNumber
     * @access public
     */
	var $containerNumber;
	/**
	 * @var $sessionSection
	 * @access public
	 */
	var $sessionSection;
	/**
	 * @var $target_element
	 * @access public
	 */
	var $targetElement;
	/**
	 * @var $selected
	 * @access public
	 */
	var $selected;
	/**
	 * @var $digest
	 * @access public
	 */
	var $digest;

	function readPostedData()
	{
		$this->sessionSection = "generic_select";
		$this->targetElement = isset($_GET['target_element']) ? $_GET['target_element'] : 'user_list';
		$this->containerNumber = isset($_GET['container_number']) ? $_GET['container_number'] : 0;
		$this->selected = isset($_GET['selected']) ? $_GET['selected'] : -1;
		$this->digest = isset($_GET['digest']) ? $_GET['digest'] : "false";
	}

	/**
	 * Constructor
	 * @access public
	 * @return void
	 */
	function ui_generic_select()
	{
		$this->readPostedData();
	}

	/**
	 * Build the generic select form
	 * @access public
	 * @return void
	 */
	function form()
	{
		$smarty = Factory::getInstance('workflow_smarty', false);
		$smarty->setHeader(workflow_smarty::SHOW_HEADER | workflow_smarty::SHOW_FOOTER);

		$javaScripts = '<script src="' . $GLOBALS['phpgw_info']['server']['webserver_url'] . SEP . 'workflow' . SEP . 'js' . SEP . 'jscode' . SEP . 'generic_select.js' . '" type="text/javascript"></script>';

		/* pass the variables to Smarty */
		$smarty->assign('list', $_SESSION[$this->sessionSection][$this->digest][$this->containerNumber]);
		$smarty->assign('selected', $this->selected);
		$smarty->assign('targetElement', $this->targetElement);
		$smarty->assign('javaScripts', $javaScripts);
		$smarty->assign('lang_to_Search', lang('search'));
		$smarty->assign('lang_Close', lang('Close'));
		$smarty->assign('lang_Add', lang('Add'));
		$smarty->assign('header', $smarty->expressoHeader);
		$smarty->assign('footer', $smarty->expressoFooter);
		$smarty->display('generic_select.tpl');
	}
}
?>
