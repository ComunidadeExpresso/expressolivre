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

require_once(dirname(__FILE__) . SEP . 'class.ui_ajaxinterface.inc.php');
/**
 * @package Workflow
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @author Sidnei Augusto Drovetto Jr. - drovetto@gmail.com
 */
class ui_monitors extends ui_ajaxinterface
{
	/**
	 * @var $public_functions public functions
	 * @access public
	 */
	var $public_functions = array(
		'form'	=> true
	);

	/**
	 * @var $workflow_acl object for check access rights of workflow
	 * @access public
	 */
	var $workflow_acl;

	function ui_monitors()
	{
		$this->set_wf_session();
		$this->workflow_acl = Factory::getInstance('workflow_acl');

		if (!($this->workflow_acl->checkUserGroupAccessToType('MON', $_SESSION['phpgw_info']['workflow']['account_id']) || ($this->workflow_acl->checkWorkflowAdmin($_SESSION['phpgw_info']['workflow']['account_id']))))
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
	 * Draw the interface
	 * @access public
	 */
	function form()
	{
		$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['workflow']['title'] . ' - ' . lang('%1 monitoring');
		$GLOBALS['phpgw_info']['flags'] = array('noheader' => false, 'nonavbar' => false, 'currentapp' => 'workflow');
		$smarty = Factory::getInstance('workflow_smarty');

		$javaScripts = $this->get_common_js();
		$javaScripts .= $this->get_js_link('workflow','scriptaculous', 'prototype');
		$javaScripts .= $this->get_js_link('workflow','jscalendar', 'calendar');
		$javaScripts .= $this->get_js_link('workflow','jscalendar', 'calendar-br');
		$javaScripts .= $this->get_js_link('workflow','jscalendar', 'calendar-setup');
		$javaScripts .= $this->get_js_link('workflow','jscalendar', 'calendar-input');
		$javaScripts .= $this->get_js_link('workflow','monitors', 'general');
		$javaScripts .= $this->get_js_link('workflow','monitors', 'processes');
		$javaScripts .= $this->get_js_link('workflow','monitors', 'instances');
		$javaScripts .= $this->get_js_link('workflow','monitors', 'properties');
		$javaScripts .= $this->get_js_link('workflow','monitors', 'filters');
		$javaScripts .= $this->get_js_link('workflow','monitors', 'massActions');
		$javaScripts .= $this->get_js_link('workflow','userinterface', 'inbox_actions');
		$javaScripts .= $this->get_js_link('workflow','userinterface', 'common_functions');
		$javaScripts .= $this->get_js_link('workflow','jscode', 'participants');
		$javaScripts .= $this->get_js_link('workflow','nano', 'JSON');
		$javaScripts .= $this->get_js_link('workflow','scriptaculous', 'lightbox');

		$css = $this->get_common_css();
		$css .= $this->get_css_link('monitors');
		$css .= $this->get_css_link('lightbox');
		$css .= '<link rel="stylesheet" type="text/css" media="all" href="workflow/js/jscalendar/calendar-blue.css">';

		$tabs = array('Processos');

		$smarty->assign('header', $smarty->expressoHeader);
		$smarty->assign('footer', $smarty->expressoFooter);
		$smarty->assign('txt_loading', lang("loading"));
		$smarty->assign('javaScripts', $javaScripts);
		$smarty->assign('css', $css);
		$smarty->assign('tabs', $tabs);
		$smarty->display('monitors.tpl');
	}
}
?>
