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
 * @author Mauricio Luiz Viani - viani@celepar.pr.gov.br
 * @author Sidnei Augusto Drovetto Jr. - drovetto@gmail.com
 */
class ui_userinterface extends ui_ajaxinterface
{
	/**
	 * @var array public_functions
	 * @access public
	 */
	var $public_functions = array(
		'draw'	=> true,
		'printArea' => true
	);

	/**
	 * Constructor
	 * @access public
	 * @return object
	 */
	function ui_userinterface() {

	}

	/**
	 * Draw the user interface
	 * @param int $tabIndex
	 */
	function draw($tabIndex = null)
	{
		$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['workflow']['title'];
		$GLOBALS['phpgw_info']['flags'] = array('noheader' => false, 'nonavbar' => false, 'currentapp' => 'workflow');
		$smarty = Factory::getInstance('workflow_smarty');

		// Check if workflow config is ok
		if (count($errors = $this->_checkWorkflowConfig()))
		{
			$smarty->assign('header', $smarty->expressoHeader);
			$smarty->assign('footer', $smarty->expressoFooter);
			$smarty->assign('errors', $errors);
			$smarty->display('notworking.tpl');
			return false;
		}

		$this->set_wf_session();

		if (is_null($tabIndex))
			$tabIndex = 1;

		$javaScripts = $this->get_common_js();
		$javaScripts .= $this->get_js_link('workflow','scriptaculous', 'prototype');
		$javaScripts .= $this->get_js_link('workflow','userinterface', 'main');
		$javaScripts .= $this->get_js_link('workflow','jscode', 'tigra_menu');
		$javaScripts .= $this->get_js_link('workflow','userinterface', 'common_functions');
		$javaScripts .= $this->get_js_link('workflow','userinterface', 'inbox');
		$javaScripts .= $this->get_js_link('workflow','userinterface', 'inbox_group');
		$javaScripts .= $this->get_js_link('workflow','userinterface', 'inbox_actions');
		$javaScripts .= $this->get_js_link('workflow','userinterface', 'processes');
		$javaScripts .= $this->get_js_link('workflow','userinterface', 'instances');
		$javaScripts .= $this->get_js_link('workflow','userinterface', 'instances_group');
		$javaScripts .= $this->get_js_link('workflow','userinterface', 'externals');
		$javaScripts .= $this->get_js_link('workflow','userinterface', 'orgchart');
		$javaScripts .= $this->get_js_link('workflow','scriptaculous', 'scriptaculous', 'load=effects');
		$javaScripts .= $this->get_js_link('workflow','experience', 'experience');
		$javaScripts .= $this->get_js_link('workflow','experience', 'experience.panorama');

		$css = $this->get_common_css();
		$css .= $this->get_css_link('userinterface');
		$css .= $this->get_css_link('orgchart');
		$css .= $this->get_css_link('experience.panorama');

		$tabs = array(
			'Tarefas Pendentes',
			'Processos',
			'Acompanhamento',
			'Aplicações Externas',
			'Organograma'
		);

		$smarty->assign('header', $smarty->expressoHeader);
		$smarty->assign('footer', $smarty->expressoFooter);
		$smarty->assign('txt_loading', lang("loading"));
		$smarty->assign('javaScripts', $javaScripts);
		$smarty->assign('css', $css);
		$smarty->assign('tabs', $tabs);
		$smarty->assign('startTab', $tabIndex);

		$smarty->display('userinterface.tpl');
	}

	/**
	 * Check if workflow config is ok
	 * @param void
	 * @access private
	 * @return array Errors that were found
	 */
	private function _checkWorkflowConfig()
	{
		$errors = array();

		// Get a connection to db workflow and galaxia (module)
		if (Factory::getInstance('WorkflowObjects')->getDBWorkflow()->Error)
			$errors[] = 'Unable to connect to database Workflow';

		if ($errormsg = Factory::getInstance('WorkflowObjects')->getDBGalaxia()->Error)
			$errors[] = 'Unable to connect to database Galaxia';

		return $errors;
	}

	function printArea()
	{
		/* set some session variables */
		$this->set_wf_session();

		/* create some objects */
		$so = &Factory::getInstance('so_userinterface');
		$smarty = &Factory::getInstance('workflow_smarty');

		/* get the user's organization */
		$organizationInfo = $so->getUserOrganization($_SESSION['phpgw_info']['workflow']['account_id']);
		if ($organizationInfo === false)
			return false;

		$organizationID = $organizationInfo['organizacao_id'];
		$areaID = (int) $_REQUEST['areaID'];

		/* load the entire orgchart */
		$areaStack = $so->getHierarchicalArea($organizationID, null, 0);

		/* if requested, load only one area */
		if ($areaID !== 0)
		{
			$selectedArea = null;
			while (($currentArea = array_pop($areaStack)) !== null)
			{
				if ($currentArea['area_id'] == $areaID)
				{
					$selectedArea = $currentArea;
					break;
				}

				foreach ($currentArea['children'] as &$child)
					$areaStack[] = $child;
			}

			if (is_null($selectedArea))
				return false;

			$areaStack = array(&$selectedArea);
		}
		else
		{
			$areaStack = array_reverse($areaStack);
			$smarty->assign('organizationName', $organizationInfo['descricao']);
		}

		//Verifica a permissão do usuário
		$so->_checkAccess($organizationID);

		/* make the array flat (for a simpler handling) */
		$flatAreas = array();
		while (count($areaStack) > 0)
		{
			$currentArea = &$areaStack[count($areaStack) - 1];
			unset($areaStack[count($areaStack) - 1]);

			$currentArea['children'] = array_reverse($currentArea['children']);
			foreach ($currentArea['children'] as &$item)
			{
				$item['orgchartPath'] = $currentArea['orgchartPath'] . $currentArea['sigla'] . ' &rarr; ';
				$areaStack[count($areaStack)] = &$item;
			}
			unset($currentArea['children']);
			
			$employees = $so->getAreaEmployees($currentArea['area_id'], $organizationID);
			if (is_array($employees))
				$currentArea['employees'] = $employees['employees'];
			else
				$currentArea['employees'] = array();
			$flatAreas[] = $currentArea;
		}

		/* get the CSS and JS links */
		$javaScripts = $this->get_js_link('workflow', 'jquery', 'jquery-1.2.6');
		$javaScripts .= $this->get_js_link('workflow', 'userinterface', 'orgchartPrint');

		$css = $this->get_css_link('orgchartPrint', 'print');
		$css .= $this->get_css_link('orgchartPrintPreview');

		/* pass variables to smarty */
		$smarty->assign('areasJson', json_encode($flatAreas));
		$smarty->assign('javaScripts', $javaScripts);
		$smarty->assign('css', $css);

		/* render the page */
		$smarty->display('orgchartPrint.tpl');
	}
}
?>
