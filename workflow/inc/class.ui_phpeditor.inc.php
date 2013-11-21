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

require_once dirname(__FILE__) . SEP . 'engine' . SEP . 'config.ajax.inc.php';
require_once dirname(__FILE__) . SEP . 'class.ui_ajaxinterface.inc.php';
/**
 * @package Workflow
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @author Rodrigo Daniel C Lira - rodrigo.lira@gmail.com
 * @author Sidnei Augusto Drovetto Junior - drovetto@gmail.com
 */
class ui_phpeditor extends ui_ajaxinterface
{
	/**
	 * @var array $public_functions public functions
	 * @access public
	 */
	var $public_functions = array(
		'form'	=> true,
	);

	/**
	 * @var object $bo
	 * @access public
	 */
	var $bo;

	protected $HTMLFile;
	protected $type;

	/**
	 * Constructor
	 * @access public
	 * @return object
	 */
	function ui_phpeditor()
	{
		$this->bo = Factory::getInstance('bo_editor');
		$this->loadVariables();
	}

	protected function loadVariables()
	{
		$this->HTMLFile = 'editor.html';
		$this->type = 'php';
	}

	/**
	 * Build php editor form
	 * @access public
	 * @return void
	 */
	function form()
	{
		$smarty = Factory::getInstance('workflow_smarty', false);
		$processManager = &Factory::newInstance('ProcessManager');
		$proccessInfo = $processManager->get_process($_GET['proc_id']);

		$javaScripts = $this->get_common_js();
		$javaScripts .= $this->get_js_link('workflow','phpeditor', 'main');

		$css = $this->get_common_css();

		$fileData = $this->bo->get_source($proccessInfo['wf_normalized_name'],$_REQUEST['file_name'],$_REQUEST['type']);

		$smarty->assign('type', $this->type);
		$smarty->assign('HTMLFile', $this->HTMLFile);
		$smarty->assign('javaScripts', $javaScripts);
		$smarty->assign('css', $css);
		$smarty->assign('txt_loading', lang('loading'));
		$smarty->assign('processName', $_GET['proc_name']);
		$smarty->assign('fileName', $_GET['file_name']);
		$smarty->assign('tipoCodigo', $_GET['type']);
		$smarty->assign('processID', $_GET['proc_id']);
		$smarty->assign('activityId', $_GET['activity_id']);
		$smarty->assign('processNameVersion', "{$proccessInfo['wf_name']} (v{$proccessInfo['wf_version']})");
		$smarty->assign('fileData', $fileData);

		$smarty->display('editor.tpl');
	}
}
?>
