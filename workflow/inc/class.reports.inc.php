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

$lib_folder = dirname(__FILE__) . SEP . 'report' . SEP;

require_once(dirname(__FILE__) . SEP . 'class.ui_ajaxinterface.inc.php');
require_once($lib_folder . 'xajax/xajax.inc.php');
include($lib_folder . 'includes/php/listagem/cad_listagem.xajax.php');
include($lib_folder . 'includes/classes/Formulario.class.php');
include($lib_folder . 'includes/classes/Listagem.class.php');

/**
* Monta a interface inicial da Lista de Controle de Acesso
*
* @package Workflow
* @author Jair Pereira - pereira.jair@gmail.com
* @license http://www.gnu.org/copyleft/gpl.html GPL
*/
class reports extends ui_ajaxinterface
{
	/**
	* Relaciona os m�todos p�blicos da classe
	*
	* @var array $public_functions
	* @access private
	*/
	var $public_functions = array(
		'form'	=> true,
		'view'	=> true,
	);

	/**
	* Verifica permiss�o do usu�rio atual para rodar a lista de controle de acesso
	* Caso n�o tenha permiss�o, emite mensagem e encerra execu��o
	*
	* @return void
	* @access public
	*/
	function reports()
	{
		if (!Factory::getInstance('workflow_acl')->checkWorkflowAdmin($GLOBALS['phpgw_info']['user']['account_id']))
		{
			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();
			echo lang('access not permitted');
			$GLOBALS['phpgw']->log->message('F-Abort, Unauthorized access to workflow.reports.form');
			$GLOBALS['phpgw']->log->commit();
			$GLOBALS['phpgw']->common->phpgw_exit();
		}
	}

	/**
	* Monta a p�gina inicial da interface de controle de acesso, a partir do template, e
	* executa o m�todo inicial JavaScript para preencher dadas
	*
	* @return void
	* @access public
	*/
	function form()
	{
		$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['workflow']['title'] . ' - ' . lang('Reports');
		$GLOBALS['phpgw_info']['flags'] = array('noheader' => false, 'nonavbar' => false, 'currentapp' => 'workflow');
		$smarty = Factory::getInstance('workflow_smarty');

		$javaScripts = $this->get_common_js();
		$css = $this->get_common_css();

		$smarty->assign('header', $smarty->expressoHeader);
		$smarty->assign('footer', $smarty->expressoFooter);
		$smarty->assign('txt_loading', lang("loading"));
		$smarty->assign('javaScripts', $javaScripts);
		
		$lib_folder = dirname(__FILE__) . SEP . 'report' . SEP;
		
		ob_start();
		include($lib_folder . 'cad_listagem.php');
		$html = ob_get_contents();
		ob_end_clean();
		
		$smarty->assign('css', $css);
		$smarty->assign('reports_content', $html);
		
		$smarty->display('reports.tpl');
		
	}
	
	function view()
	{
		$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['workflow']['title'] . ' - ' . lang('Reports');
		$GLOBALS['phpgw_info']['flags'] = array('noheader' => false, 'nonavbar' => false, 'currentapp' => 'workflow');
		$smarty = Factory::getInstance('workflow_smarty');

		$javaScripts = $this->get_common_js();
		$css = $this->get_common_css();

		$smarty->assign('header', $smarty->expressoHeader);
		$smarty->assign('footer', $smarty->expressoFooter);
		$smarty->assign('txt_loading', lang("loading"));
		$smarty->assign('javaScripts', $javaScripts);
		
		$lib_folder = dirname(__FILE__) . SEP . 'report' . SEP;
		
		ob_start();
		include($lib_folder . 'includes/php/listagem/lst_view.php');
		$html = ob_get_contents();
		ob_end_clean();
		
		$smarty->assign('css', $css);
		$smarty->assign('reports_content', $html);
		
		$smarty->display('reports.tpl');
		
	}
}
?>
