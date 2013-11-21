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

/**
 * @package Workflow
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @author Rodrigo Daniel C de Lira - rodrigo.lira@gmail.com
 * @author Sidnei Augusto Drovetto Jr. - drovetto@gmail.com
 */
require_once dirname(__FILE__) . SEP . 'class.ui_ajaxinterface.inc.php';
require_once 'engine' . SEP . 'config.ajax.inc.php';

class ui_adminsource extends ui_ajaxinterface
{
	/**
	 * @var array $public_functions
	 * @access public
	 */
	var $public_functions = array(
		'form'	=> true,
	);

	/**
	 * @var array $workflow_acl
	 * @access public
	 */
	var $workflow_acl;

	/**
	 * Constructor
	 * @access public
	 * @return object
	 */
	function ui_adminsource()
	{
		$this->workflow_acl = Factory::getInstance('workflow_acl');
		$denyAccess = true;
		if ($this->workflow_acl->checkWorkflowAdmin($_SESSION['phpgw_info']['workflow']['account_id']))
		{
			/* the user is an Expresso/Workflow admin */
			$denyAccess = false;
		}
		else
		{
			if ($GLOBALS['phpgw']->acl->check('admin_workflow', 1, 'workflow'))
			{
				$pid = (int) $_GET['p_id'];
				/* check if the user can admin the informed process */
				$denyAccess = !$this->workflow_acl->check_process_access($_SESSION['phpgw_info']['workflow']['account_id'], $pid);
			}
		}

		if ($denyAccess)
		{
			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();
			echo lang('access not permitted');
			$GLOBALS['phpgw']->log->message('F-Abort, Unauthorized access to workflow.ui_adminprocesses');
			$GLOBALS['phpgw']->log->commit();
			$GLOBALS['phpgw']->common->phpgw_exit();
		}
	}

	/**
	 * Show user interface admin source form
	 * @access public
	 * @return object
	 */
	function form()
	{
		$smarty = Factory::getInstance('workflow_smarty', false);
		$smarty->setHeader(workflow_smarty::SHOW_HEADER | workflow_smarty::SHOW_NAVIGATION_BAR | workflow_smarty::SHOW_FOOTER, $GLOBALS['phpgw_info']['apps']['workflow']['title'] . ' - ' . lang('Admin Processes Sources'));

		$javaScripts = $this->get_common_js();
		$javaScripts .= $this->get_js_link('workflow','jscode', 'prototype');
		$javaScripts .= $this->get_js_link('workflow','adminsource', 'php_folder');
		$javaScripts .= $this->get_js_link('workflow','adminsource', 'templates_folder');
		$javaScripts .= $this->get_js_link('workflow','adminsource', 'resources_folder');
		$javaScripts .= $this->get_js_link('workflow','adminsource', 'includes_folder');
		$javaScripts .= $this->get_js_link('workflow','jscode', 'lightbox');
		$javaScripts .= $this->get_js_link('workflow','adminsource', 'main');

		$css = $this->get_common_css();
		$css .=  $this->get_css_link('lb');

		$tabs = array(
			'Atividades',
			'Includes',
			'Templates',
			'Resources'
		);

		$smarty->assign('header', $smarty->expressoHeader);
		$smarty->assign('footer', $smarty->expressoFooter);
		$smarty->assign('txt_loading', lang('loading'));
		$smarty->assign('javaScripts', $javaScripts);
		$smarty->assign('css', $css);
		$smarty->assign('tabs', $tabs);
		$smarty->assign('processID', $_GET['p_id']);
		$smarty->display('adminsource.tpl');
	}
}
?>
