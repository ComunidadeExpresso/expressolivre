<?php

/**************************************************************************\
* eGroupWare                                                 *
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
 */
class ui_orgchart extends ui_ajaxinterface
{
	/**
	 * @var array $public_functions
	 * @access public
	 */
	var $public_functions = array(
		'draw'	=> true,
		'graph'	=> true
	);
	/**
	 * @var array $workflow_acl
	 * @access public
	 */
	var $workflow_acl;
	/**
	 * Construtor
	 * @access public
	 */
	function ui_orgchart()
	{
	}
	/**
	 * Draw the orgchart admin interface
	 * @return void
	 * @access public
	 */
	function draw($tab_index = "")
	{
		$this->workflow_acl = Factory::getInstance('workflow_acl');

		$isAdmin = $this->workflow_acl->checkWorkflowAdmin($GLOBALS['phpgw_info']['user']['account_id']);
		$isOrgchartManager = $this->workflow_acl->checkUserGroupAccessToType('ORG', $GLOBALS['phpgw_info']['user']['account_id'],0);

		if (!($isAdmin || $isOrgchartManager))
		{
			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();
			echo lang('access not permitted');
			$GLOBALS['phpgw']->log->message('F-Abort, Unauthorized access to workflow.ui_orgchart');
			$GLOBALS['phpgw']->log->commit();
			$GLOBALS['phpgw']->common->phpgw_exit();
		}

		$GLOBALS['phpgw_info']['flags'] = array('noheader' => false, 'nonavbar' => false, 'currentapp' => 'workflow');
		$smarty = Factory::getInstance('workflow_smarty');

		$this->set_wf_session();

		$javaScripts = $this->get_common_js();
		$javaScripts .= $this->get_js_link('workflow','scriptaculous', 'prototype');
		$javaScripts .= $this->get_js_link('workflow','orgchart', 'main');
		$javaScripts .= $this->get_js_link('workflow','orgchart', 'utils');
		$javaScripts .= $this->get_js_link('workflow','orgchart', 'organization');
		$javaScripts .= $this->get_js_link('workflow','orgchart', 'organizations');
		$javaScripts .= $this->get_js_link('workflow','scriptaculous', 'lightbox');
		$javaScripts .= $this->get_js_link('workflow','jscode', 'participants');

		$css = $this->get_common_css();
		$css .= $this->get_css_link('orgchart');
		$css .= $this->get_css_link('lightbox');

		$smarty->assign('header', $smarty->expressoHeader);
		$smarty->assign('footer', $smarty->expressoFooter);
		$smarty->assign('txt_loading', lang('loading'));
		$smarty->assign('javaScripts', $javaScripts);
		$smarty->assign('tabIndex', $tab_index);
		$smarty->assign('css', $css);
		$smarty->display('orgchart.tpl');
	}

	/**
	 * Show the graph
	 * @access public
	 * @return void 
	 */
	function graph()
	{
		if (!isset($_GET['organizationID']))
			die();
		$organizationID = $_GET['organizationID'];
		if (is_numeric($organizationID))
			$organizationID = (int) $organizationID;
		else
			die();

		$config_values = Factory::getInstance('config', 'workflow');
		$config_values->read_repository();
		$conf_db = $config_values->config_data;
		$db = Factory::getInstance('db');
		$db->connect(
			$conf_db['database_name'],
			$conf_db['database_host'],
			$conf_db['database_port'],
			$conf_db['database_admin_user'],
			$conf_db['database_admin_password'],
			$conf_db['database_type']
		);
		$db = $db->Link_ID;
		$attributes = array();
		$attributes['ranksep'] = '1.5 equally';
		$attributes['rankdir'] = 'LR';
		$graph = &Factory::getInstance('Process_GraphViz', true, $attributes);


		/**
		 * Get Hierarchical Area
		 * @param organizationID 
		 * @param $parent 
		 * @param $depth
		 * @param $db
		 * @param $graph
		 * @return 
		 */
		function getHierarchicalArea($organizationID, $parent, $depth, $db, $graph)
		{
			/* orgchart graph configuration */
			$color='black';
			$fillcolor='lightblue2'; //blue TLS values
			$fontsize = '10';
			$color = '0.25,1,0.28'; #dark green in TLS values
			$arrowsize = 0.8;

			if (is_null($parent))
				$result = $db->query("SELECT area_id, sigla, titular_funcionario_id FROM area WHERE (superior_area_id IS NULL) AND (organizacao_id = ?) AND (ativa = 'S') ORDER BY sigla", array($organizationID));
			else
				$result = $db->query("SELECT area_id, sigla, titular_funcionario_id FROM area WHERE (superior_area_id = ?) AND (ativa = 'S') ORDER BY sigla", array($parent));

			$output = $result->GetArray(-1);

			if (is_null($parent) && (count($output) == 0))
				return false;

			$ldap = &Factory::getInstance('WorkflowLDAP');

			for ($i = 0; $i < count($output); ++$i)
			{
				for ($j = 0; $j < $result->_numOfFields; ++$j)
					unset($output[$i][$j]);
				if ($output[$i]['titular_funcionario_id'] != '')
				{
					$supervisor = $ldap->getName($output[$i]['titular_funcionario_id']);
					$supervisor = str_replace(array(" da ", " de ", " do ", " das ", " dos "), " ", $supervisor);
					$supervisorArray = explode(' ', $supervisor);
					$supervisorName = '';
                    $supervisorArray_count = count($supervisorArray);
					for ($j = 0; $j < $supervisorArray_count; ++$j)
						$supervisorName .= (($j == 0) || ($j == count($supervisorArray) - 1)) ? $supervisorArray[$j] . ' ' : $supervisorArray[$j][0] . '. ';
					$supervisorName = trim(str_replace("Junior", "Jr", $supervisorName));
				}
				else
					$supervisorName = '';
				/* add the area box */
				$graph->addNode($output[$i]['area_id'],array(
					'URL'		=> $output[$i]['area_id'],
					'label'		=> $output[$i]['sigla'] . '\n' . $supervisorName,
					'shape'		=> 'box',
					'color'		=> $color,
					'fillcolor'	=> $fillcolor,
					'style'		=> 'filled',
					'fontsize'	=> $fontsize,
					'fontname'	=> 'serif'
					)
				);

				/* add the conection between areas */
				if (!is_null($parent))
					$graph->addEdge(array($parent => $output[$i]['area_id']), array('color'=>$color,arrowsize=>$arrowsize));
				$output[$i]['children'] = getHierarchicalArea($organizationID, $output[$i]['area_id'], $depth + 1, $db, $graph);
				$output[$i]['depth'] = $depth;
			}

			return $output;
		}


		$result = getHierarchicalArea($organizationID, null, 0, $db, $graph);
		if ($result !== false)
			$graph->image('png', '/tmp/grafico');
			else
				die();
	}
}
?>
