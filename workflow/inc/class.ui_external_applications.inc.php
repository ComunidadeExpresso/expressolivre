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

require_once dirname(__FILE__) . SEP . 'class.ui_ajaxinterface.inc.php';
/**
 * @package Workflow
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @author Sidnei Augusto Drovetto Jr. - drovetto@gmail.com
 */
class ui_external_applications extends ui_ajaxinterface
{
	/**
	 * @var array $public_functions
	 * @access public
	 */
	var $public_functions = array(
		'draw' => true,
		'upload_image' => true
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
	function ui_external_applications()
	{
	}

	/**
	 * Draw external applications interface
	 * @return void
	 * @access public
	 */
	function draw()
	{
		if (!Factory::getInstance('workflow_acl')->checkWorkflowAdmin($GLOBALS['phpgw_info']['user']['account_id']))
		{
			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();
			echo lang('access not permitted');
			$GLOBALS['phpgw']->log->message('F-Abort, Unauthorized access to workflow.ui_orgchart');
			$GLOBALS['phpgw']->log->commit();
			$GLOBALS['phpgw']->common->phpgw_exit();
		}

		$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['workflow']['title'];
		$GLOBALS['phpgw_info']['flags'] = array('noheader' => false, 'nonavbar' => false, 'currentapp' => 'workflow');
		$smarty = Factory::getInstance('workflow_smarty');

		$this->set_wf_session();

		$javaScripts = $this->get_common_js();
		$javaScripts .= $this->get_js_link('workflow','scriptaculous', 'prototype');
		$javaScripts .= $this->get_js_link('workflow','scriptaculous', 'scriptaculous', 'load=effects');
		$javaScripts .= $this->get_js_link('workflow','external_applications', 'main');

		$css = $this->get_common_css();
		$css .= $this->get_css_link('external_applications');

		$smarty->assign('header', $smarty->expressoHeader);
		$smarty->assign('footer', $smarty->expressoFooter);
		$smarty->assign('txt_loading', lang("loading"));
		$smarty->assign('javaScripts', $javaScripts);
		$smarty->assign('css', $css);
		$smarty->display('external_applications.tpl');
	}

	/**
	 * Upload image
	 * @access public
	 * @return void
	 */
	function upload_image()
	{
		if (isset($_FILES['image_tmp']))
		{
			$data = array(
				'name' => $_FILES['image_tmp']['name'],
				'contents' => file_get_contents($_FILES['image_tmp']['tmp_name']));
			$data = base64_encode(serialize($data));
			$output = '<html><head><title>-</title ></head><body>';
			$output .= '<script language="JavaScript" type="text/javascript">' . "\n";
			$output .= 'window.parent.document.getElementById(\'image\').value = \'' . $data . '\';' . "\n";
			$output .= 'window.parent.document.getElementById(\'buttonSave\').onclick();' . "\n";
			$output .= '</script></body></html >';
			echo $output;
		}
	}
}
?>
